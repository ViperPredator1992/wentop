<?php
namespace progroman\CityManager;

use progroman\CityManager\Driver\Sypex;

if (defined('PRMN_DEV_MODE')) {
    require_once 'core.php';
} elseif (version_compare(phpversion(), '7.1', '<')) {
    require_once 'core-encoded-php56.php';
} elseif (version_compare(phpversion(), '7.2', '<')) {
    require_once 'core-encoded-php71.php';
} else {
    require_once 'core-encoded-php72.php';
}

/**
 * Class CityManager
 * @package progroman\CityManager
 * @author Roman Shipilov (ProgRoman) <mr.progroman@yandex.ru>
 */
class CityManager extends Core {

    const VERSION = '6.3';
    const MODULE_NAME = 'GeoIP';

    protected static $instance;

    protected $dev_mode;

    public function setFias($fias_id) {
        $result = parent::setFias($fias_id);
        if ($result) {
            $this->forceSaveInSession();
        }

        return $result;
    }

    /**
     * Записывает адреса доставки и оплаты в сессию,
     * только если эти значения не были установлены ранее.
     * Не перезаписывает уже установленных значений.
     */
    public function saveInSession() {
        foreach ($this->getData() as $key => $value) {
            // OC 1.5
            if (empty($this->session->data['shipping_' . $key])) {
                $this->session->data['shipping_' . $key] = $value;
            }

            if (empty($this->session->data['payment_' . $key])) {
                $this->session->data['payment_' . $key] = $value;
            }

            if (empty($this->session->data['guest']['shipping'][$key])) {
                $this->session->data['guest']['shipping'][$key] = $value;
            }

            if (empty($this->session->data['guest']['payment'][$key])) {
                $this->session->data['guest']['payment'][$key] = $value;
            }

            // OC 2
            if (empty($this->session->data['payment_address'][$key])) {
                $this->session->data['payment_address'][$key] = $value;
            }

            if (empty($this->session->data['shipping_address'][$key])) {
                $this->session->data['shipping_address'][$key] = $value;
            }

            // Simple
            if (empty($this->session->data['simple']['payment_address'][$key])) {
                $this->session->data['simple']['payment_address'][$key] = $value;
            }

            if (empty($this->session->data['simple']['shipping_address'][$key])) {
                $this->session->data['simple']['shipping_address'][$key] = $value;
            }
        }
    }

    /**
     * Записывает адреса доставки и оплаты в сессию.
     * Используется, когда пользователь меняет регион вручную.
     */
    public function forceSaveInSession() {
        foreach ($this->getData() as $key => $value) {
            $this->session->data['payment_address'][$key]
                = $this->session->data['shipping_address'][$key]
                = $this->session->data['shipping_' . $key]
                = $this->session->data['payment_' . $key]
                = $this->session->data['guest']['shipping'][$key]
                = $this->session->data['guest']['payment'][$key]
                = $this->session->data['simple']['payment_address'][$key]
                = $this->session->data['simple']['shipping_address'][$key]
                = $value;
        }
    }

    private function getData() {
        return [
            'country_id' => $this->getCountryId(),
            'zone_id' => $this->getZoneId(),
            'postcode' => $this->getPostcode(),
            'city' => $this->getCityName()
        ];
    }

    protected function getBots() {
        return [
            'apis-google', 'mediapartners-google', 'adsbot', 'googlebot', 'yandex.com/bots', 'mail.ru_bot', 'stackrambler',
            'slurp', 'msnbot', 'bingbot', 'alexa.com'
        ];
    }

    /**
     * Возвращает папку для загрузки файлов
     * @return string
     */
    static public function getUploadDir() {
        return (defined('DIR_UPLOAD') ? DIR_UPLOAD : DIR_DOWNLOAD) . 'progroman';
    }

    static public function getSxgeoPath() {
        return self::getUploadDir() . '/SxGeoCity.dat';
    }

    static public function getSxgeoVersion() {
        return (new Sypex())->setSxgeoPath(self::getSxgeoPath())->getSxgeoVersion();
    }

    static public function getCitiesBaseList() {
        return [
            ['name' => 'Белоруссия', 'country_fias_id' => 300000, 'iso' => 'by'],
            ['name' => 'Казахстан', 'country_fias_id' => 500000, 'iso' => 'kz'],
            ['name' => 'РФ', 'class' => 'progroman\CityManager\DatabaseFile\BaseCitiesRu', 'country_fias_id' => 1, 'iso' => 'ru'],
            ['name' => 'Украина', 'class' => 'progroman\CityManager\DatabaseFile\BaseCitiesUa', 'country_fias_id' => 400000, 'iso' => 'ua'],
        ];
    }

    public function replaceBlanks($string) {
        $string = str_replace(['%CITY%', '%ZONE%', '%COUNTRY%'], [$this->getCityName(), $this->getZoneName(), $this->getCountryName()], $string);
        $string = preg_replace_callback('#%MSG_(.*?)%#', function($matches) {
            return $this->getMessage($matches[1]);
        }, $string);

        return $string;
    }

    public function checkIntegrationSimple() {
        return $this->setting('integration_simple');
    }

    public function autocompleteForSimple($term, $limit) {
        return $this->loadModel('fias')->autocompleteForSimple($term, $limit);
    }
}