<?php
namespace progroman\CityManager\DatabaseFileAction;

use DB\MySQLi;
use progroman\Common\Registry;

/**
 * Class ActionDownloadFile Действие с файлом-базой
 * Формирует кнопку и обрабатывает действие по нажатию на нее
 * @package progroman\CityManager\ActionDownloadFile
 * @author Roman Shipilov (ProgRoman) <mr.progroman@yandex.ru>
 */
abstract class DatabaseFileAction {

    /** @var string title кнопки */
    protected $name;

    /** @var string Картинка кнопки */
    protected $icon;

    /** @var string CSS-класс кнопки */
    protected $css_class = 'btn-default';

    /** @var string Текстовый статус при нажатии на кнопку */
    protected $loading_text;

    /** @var array Дополнительные параметры для передачи в контроллер при нажатии */
    protected $params = [];

    /** @var array lang-база для поддержки мультиязычности */
    protected $lang = [];

    /** @var string Сервер-источник файлов-баз */
    protected $progroman_server;

    /** @var array Основная информация о модуле для передачи на сервер */
    static protected $citymanager_info;

    public function __construct($lang = []) {
        if ($lang) {
            $this->lang = $lang;
        }

        $this->progroman_server = defined('PRMN_DEV_MODE') ? 'http://test.loc' : 'http://opencart.progroman.ru';
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     * @return $this
     */
    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getCssClass() {
        return $this->css_class;
    }

    /**
     * @param string $css_class
     * @return $this
     */
    public function setCssClass($css_class) {
        $this->css_class = $css_class;
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params) {
        $this->params = $params;
        return $this;
    }

    public function getActionId() {
        $parts = explode('\\', static::class);
        $params = '';
        if ($this->params) {
            $pairs = [];
            foreach ($this->params as $key => $value) {
                $pairs[] = $key . '=' . $value;
            }

            $params = '/' . implode(',', $pairs);
        }

        return strtolower(end($parts)) . $params;
    }

    /**
     * @return string
     */
    public function getLoadingText() {
        return $this->loading_text;
    }

    abstract public function step($step, $params = []);

    protected function lang($key) {
        return isset($this->lang[$key]) ? $this->lang[$key] : $key;
    }

    static public function setCityManagerInfo($module_name, $version, $license) {
        self::$citymanager_info['module_name'] = $module_name;
        self::$citymanager_info['version'] = $version;
        self::$citymanager_info['license'] = $license;
    }

    protected function makeParamsForProgromanServer() {
        return 'module=' . strtolower(str_replace(' ', '', self::$citymanager_info['module_name']))
            . '&v=' . self::$citymanager_info['version']
            . '&license=' . self::$citymanager_info['license']
            . '&host=' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Загружает файл с сервера-источника
     * @param string $type Тип файла
     * @param string $dest Путь к файлу для сохранения
     * @throws \Exception
     */
    protected function uploadFile($type, $dest) {
        set_time_limit(0);
        $file = fopen($dest, 'w+');
        if (!$file) {
            throw new \Exception(sprintf($this->lang('error_create_file'), $dest));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->progroman_server . '/download/' . $type . '/?' . $this->makeParamsForProgromanServer());
        curl_setopt($curl, CURLOPT_FILE, $file);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_exec($curl);

        if (($code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) != 200) {
            fclose($file);
            unlink($dest);

            throw new \Exception(sprintf($this->lang('error_upload'), $code));
        }

        curl_close($curl);
        fclose($file);
    }

    /**
     * Распаковка zip-архива
     * @param string $zip_file Путь к архиву
     * @param string $extract_dir Папка для распаковки
     * @throws \Exception
     */
    protected function unzipFile($zip_file, $extract_dir) {
        if (!file_exists($zip_file)) {
            throw new \Exception(sprintf($this->lang('error_create_file'), $zip_file));
        }

        $zip = new \ZipArchive();
        if (!$zip->open($zip_file)) {
            throw new \Exception($this->lang('error_unzip'));
        }

        $zip->extractTo($extract_dir);
        $zip->close();
    }

    /**
     * Возвращает объект базы данных
     * @return MySQLi
     */
    protected function getDb() {
        return Registry::instance()->get('db');
    }

    /**
     * Выполняет запросы из SQL-файла
     * @param $filename
     * @throws \Exception
     */
    protected function queryFromFile($filename) {
        if (!is_readable($filename) || !($sql = file_get_contents($filename))) {
            throw new \Exception(sprintf($this->lang('error_read_file'), $filename));
        }

        ini_set('pcre.backtrack_limit', 10240000);
        preg_match_all("#(.*);\s*$#Usm", $sql, $matches);

        if (isset($matches[1])) {
            $db = $this->getDb();
            foreach ($matches[1] as $query) {
                $query = trim($query);
                if ($query) {
                    $db->query($query);
                }
            }
        }
    }

    /**
     * Рекурсивно удаляет папку с файлами
     * @param $dir
     * @return bool
     */
    protected function rmdir($dir) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rmdir($path) : unlink($path);
        }

        return rmdir($dir);
    }
}