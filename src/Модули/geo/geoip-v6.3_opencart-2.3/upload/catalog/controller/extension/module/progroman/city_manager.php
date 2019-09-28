<?php

use \progroman\CityManager\CityManager;
use \progroman\CityManager\Driver\Sypex;
use progroman\Common\DocumentProxy;

/**
 * Class ControllerExtensionModuleProgromanCityManager
 * @property ModelExtensionModuleProgromanFias $model_extension_module_progroman_fias
 * @property ModelExtensionModuleProgromanCityManager $model_extension_module_progroman_city_manager
 * @property \progroman\CityManager\CityManager $progroman_city_manager
 * @author Roman Shipilov (ProgRoman) <mr.progroman@yandex.ru>
 */
class ControllerExtensionModuleProgromanCityManager extends Controller {

    public function index($params = []) {
        if (!$this->isModuleEnabled()) {
            return '';
        }

        $this->language->load('extension/module/progroman/city_manager');
        $city = $this->getCityName();
        $url = isset($params['url']) ? $params['url'] : $this->request->server['REQUEST_URI'];
        $data['city'] = $city ? $city : $this->language->get('text_unknown');
        $data['text_zone'] = $this->language->get('text_zone');
        $data['confirm'] = $this->loadController('confirm', ['url' => $url]);

        return $this->loadView('content', $data);
    }

    public function init() {
        $json = [];
        $json['content'] = $this->loadController('', ['url' => $this->request->get['url']]);
        $json['messages'] = $this->progroman_city_manager->getMessages();

        $this->response->setOutput(json_encode($json));
    }

    public function confirm($params) {
        $this->language->load('extension/module/progroman/city_manager');

        $city = $this->getCityName();
        $key = $this->progroman_city_manager->getSessionKey();
        $cookie_key = $this->progroman_city_manager->getCookieKey('confirm');
        $confirm_region = empty($this->session->data[$key]['show_confirm']) && empty($this->request->cookie[$cookie_key]);

        if ($confirm_region && $city) {
            $data = [
                'city' => $city,
                'text_your_city' => $this->language->get('text_your_city'),
                'text_guessed' => $this->language->get('text_guessed'),
                'text_yes' => $this->language->get('text_yes'),
                'text_no' => $this->language->get('text_no'),
                'confirm_redirect' => $this->progroman_city_manager->getRedirectUrlForManual($params['url'])
            ];

            return $this->loadView('confirm', $data);
        }
    }

    public function cities() {
        $this->language->load('extension/module/progroman/city_manager');
        $data['text_search'] = $this->language->get('text_search');
        $data['text_search_placeholder'] = $this->language->get('text_search_placeholder');
        $data['text_choose_region'] = $this->language->get('text_choose_region');

        $this->load->model('extension/module/progroman/city_manager');
        $cities = $this->model_extension_module_progroman_city_manager->getCities();
        $count_columns = 3;
        $data['columns'] = $cities ? array_chunk($cities, ceil(count($cities) / $count_columns)) : [];

        $this->response->setOutput($this->loadView('cities', $data));
    }

    public function search() {
        $json = [];
        if (!empty($this->request->get['term'])) {
            $this->load->model('extension/module/progroman/fias');
            $search = preg_replace('#^(город|село|поселок)\s#', '', $this->request->get['term']);
            $json = $this->model_extension_module_progroman_fias->findFiasByName($search);
        }

        $this->response->setOutput(json_encode($json));
    }

    public function save() {
        $fias_id = isset($this->request->get['fias_id']) ? $this->request->get['fias_id'] : 0;
        $success = $fias_id && $this->progroman_city_manager->setFias($fias_id) ? 1 : 0;
        $this->response->setOutput(json_encode(['success' => $success]));
    }

    /**
     * Отмечаем, что "Угадали" показан
     */
    public function confirmShown() {
        $this->session->data[$this->progroman_city_manager->getSessionKey()]['show_confirm'] = 1;
        $settings = $this->config->get('progroman_cm_setting');
        $time = !empty($settings['popup_cookie_time']) ? $settings['popup_cookie_time'] : 0;
        $this->progroman_city_manager->setCookie($this->progroman_city_manager->getCookieKey('confirm'), 1, $time);
    }

    private function getCityName() {
        if ($popup_city_name = $this->progroman_city_manager->getPopupCityName()) {
            return $popup_city_name;
        }

        if ($short_city_name = $this->progroman_city_manager->getShortCityName()) {
            return $short_city_name;
        }

        if ($city_name = $this->progroman_city_manager->getCityName()) {
            return $city_name;
        }

        if ($zone_name = $this->progroman_city_manager->getZoneName()) {
            return $zone_name;
        }

        if ($country_name = $this->progroman_city_manager->getCountryName()) {
            return $country_name;
        }

        return false;
    }

    /**
     * Включаем модуль
     */
    public function startup() {
        \progroman\Common\Registry::instance()->setRegistry($this->registry);
        $city_manager = CityManager::instance();
        $this->registry->set('progroman_city_manager', $city_manager);

        if ($this->isModuleEnabled()) {
            $settings = $this->config->get('progroman_cm_setting');

            // Включаем определение по IP
            if (!empty($settings['use_geoip'])) {
                // Для теста: Москва 94.25.169.110, Тамбов 193.34.14.221, Воронеж 217.118.95.92, Казань 217.66.24.13, Раменское 91.214.97.249
                $sypex = new Sypex();
                $sypex->setSxgeoPath(CityManager::getSxgeoPath());
                CityManager::addDriver($sypex);
            }

            $city_manager->defineLocation();
            $city_manager->setCurrency();
            $city_manager->saveInSession();
        }
    }

    /**
     * Загружаем скрипты и шаблоны
     */
    public function load() {
        if ($this->isModuleEnabled()) {
            // Загружаем контроллер модуля для передачи в шаблоны
            $this->registry->set('prmn_cmngr', $this->loadController());

            $settings = $this->config->get('progroman_cm_setting');

            // Включаем замену в title
            if (!empty($settings['replace_blanks'])) {
                $document = new DocumentProxy();
                $document->copy($this->document);
                $this->registry->set('document', $document);
            }

            if (VERSION >= 2) {
                $this->document->addScript('catalog/view/javascript/progroman/jquery.progroman.autocomplete.js');
            }

            $this->document->addScript('catalog/view/javascript/progroman/jquery.progroman.city-manager.js');
            $this->document->addStyle('catalog/view/javascript/progroman/progroman.city-manager.css');
        } else {
            $this->registry->set('prmn_cmngr', '');
        }
    }

    private function isModuleEnabled() {
        return $this->config->get(VERSION < 3 ? 'progroman_cm_status' : 'module_progroman_city_manager_status');
    }

    private function loadView($view, $data = []) {
        $view = 'extension/module/progroman/city_manager/' . $view;

        if (VERSION < 2) {
            $view = '/template/' . $view . '.tpl';
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $view)) {
                $this->template = $this->config->get('config_template') . $view;
            } else {
                $this->template = 'default' . $view;
            }

            $this->data = $data;
            return $this->render();
        } elseif (VERSION < '2.2') {
            $view = '/template/' . $view . '.tpl';
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $view)) {
                return $this->load->view($this->config->get('config_template') . $view, $data);
            } else {
                return $this->load->view('default' . $view, $data);
            }
        } else {
            return $this->load->view($view, $data);
        }
    }

    private function loadController($name = '', $data = []) {
        $path = 'extension/module/progroman/city_manager' . ($name ? '/' . $name : '');

        if (VERSION < 2) {
            return $this->getChild($path, $data);
        } else {
            return $this->load->controller($path, $data);
        }
    }
}
