<?php
use progroman\CityManager\CityManager;
use progroman\CityManager\DatabaseFile\BaseIP;
use progroman\CityManager\DatabaseFile\BaseCities;
use progroman\CityManager\DatabaseFileAction\DatabaseFileAction;

/**
 * Class ControllerExtensionModuleProgromanCityManager
 * @property \ModelSettingSetting model_setting_setting
 * @property \ModelExtensionModuleProgromanCityManager $model_citymanager
 * @author Roman Shipilov (ProgRoman) <mr.progroman@yandex.ru>
 */
class ControllerExtensionModuleProgromanCityManager extends Controller {
    private $error = [];

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->language($this->getModulePath());
        $this->load->model('extension/module/progroman_city_manager');
        $this->model_citymanager = $this->model_extension_module_progroman_city_manager;
        \progroman\Common\Registry::instance()->setRegistry($registry);
    }

    public function index() {
        $this->document->setTitle($this->language->get('heading_title'));

        $data['heading_title'] = $this->language->get('heading_title');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->makeUrl('common/dashboard')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_module'),
            'href' => $this->makeUrl($this->getExtensionPath())
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->makeModuleUrl()
        ];

        $data['general'] = $this->loadController($this->getModulePath() . '/general');

        $data['cancel'] = $this->makeUrl($this->getExtensionPath());
        $data['url_search'] = $this->makeModuleUrl('search');
        $data['url_redirects'] = $this->makeModuleUrl('redirects');
        $data['url_popups'] = $this->makeModuleUrl('popups');
        $data['url_messages'] = $this->makeModuleUrl('messages');
        $data['url_currencies'] = $this->makeModuleUrl('currencies');
        $data['url_zone_fias'] = $this->makeModuleUrl('zonefias');

        $data['settings'] = $this->config->get('progroman_cm_setting');
        $data['valid_license'] = !empty($data['settings']['geoip_license']) && CityManager::validLicense($data['settings']['geoip_license']);

        $data['header'] = $this->loadController('common/header');
        $data['footer'] = $this->loadController('common/footer');

        if (VERSION >= 2) {
            $data['column_left'] = $this->loadController('common/column_left');
        }

        $this->initLangVariables($data);

        $this->response->setOutput($this->loadView('index', $data));
	}

	public function general() {
        $data['action_general'] = $this->makeModuleUrl('savegeneral');
        $data['settings'] = $this->config->get('progroman_cm_setting');
        $data['status'] = $this->config->get(VERSION < 3 ? 'progroman_cm_status' : 'module_progroman_city_manager_status');

        if (isset($data['settings']['default_city'])) {
            $data['settings']['default_city_name'] = $this->model_citymanager->getFiasName($data['settings']['default_city']);
        }

        $data['base_action_url'] = $this->makeModuleUrl('baseaction');
        $data['bases_url'] = $this->makeModuleUrl('bases');

        $this->initLangVariables($data);

        return $this->loadView('general', $data);
    }

    public function bases() {
        $data['base_ip'] = new BaseIP($this->language->all());
        $data['download_files'] = [];
        foreach (CityManager::getCitiesBaseList() as $base) {
            $base_cities = isset($base['class']) ? new $base['class']($this->language->all()) : new BaseCities($this->language->all());
            $base_cities
                ->setName($base['name'])
                ->setCountry($base);

            $data['download_files'][] = $base_cities;
        }

        $this->initLangVariables($data);

        $output = $this->loadView('bases', $data);
        $this->response->setOutput($output);
    }

    public function popups() {
        $settings = $this->config->get('progroman_cm_setting');
        $data['action_popups'] = $this->makeModuleUrl('savepopups');
        $data['cities'] = $this->model_citymanager->getCities();
        $data['popup_cookie_time'] = isset($settings['popup_cookie_time']) ? (int)$settings['popup_cookie_time'] : 0;
        $data['cookie_time_values'] = [
            0 => $this->language->get('text_every_visit'),
            86400 => $this->language->get('text_day'),
            604800 => $this->language->get('text_week'),
            2592000 => $this->language->get('text_month'),
            31536000 => $this->language->get('text_year'),
        ];

        $this->initLangVariables($data);

        $this->response->setOutput($this->loadView('popup', $data));
    }

    public function messages() {
        $data['action_savemessage'] = $this->makeModuleUrl('savemessage');
        $data['action_removemessage'] = $this->makeModuleUrl('removemessage');
        $data['settings'] = $this->config->get('progroman_cm_setting');

        $page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;
        $filter_data = [
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin'),
        ];

        $total_messages = $this->model_citymanager->getTotalMessages();
        $data['messages'] = $this->model_citymanager->getMessages($filter_data);
        foreach ($data['messages'] as & $message) {
            $message['fias_name'] = $this->model_citymanager->getFiasName($message['fias_id']);
        }

        $pagination = new Pagination();
        $pagination->total = $total_messages;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->makeModuleUrl('messages', '&page={page}');
        $pagination->text = $this->language->get('text_pagination');

        $data['pagination'] = $pagination->render();
        $data['results'] = $this->config->get('config_limit_admin') > 0 ? sprintf($this->language->get('text_pagination'),
            ($total_messages) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0,
            ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_messages - $this->config->get('config_limit_admin'))) ? $total_messages : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')),
            $total_messages, ceil($total_messages / $this->config->get('config_limit_admin'))) : '';

        $this->initLangVariables($data);

        $this->response->setOutput($this->loadView('messages', $data));
    }

    public function redirects() {
        $data['action_redirects'] = $this->makeModuleUrl('saveredirects');
        $data['settings'] = $this->config->get('progroman_cm_setting');

        $data['redirects'] = $this->model_citymanager->getRedirects();
        foreach ($data['redirects'] as & $redirect) {
            $redirect['fias_name'] = $this->model_citymanager->getFiasName($redirect['fias_id']);
        }

        $this->initLangVariables($data);

        $this->response->setOutput($this->loadView('redirects', $data));
    }

    public function currencies() {
        $data['action_currencies'] = $this->makeModuleUrl('savecurrencies');
        $data['settings'] = $this->config->get('progroman_cm_setting');

        $this->load->model('localisation/currency');
        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();
        $data['cm_currencies'] = $this->model_citymanager->getCurrencies();

        $this->initLangVariables($data);

        $this->response->setOutput($this->loadView('currencies', $data));
    }

    public function zoneFias() {
        $data['countries'] = $this->model_citymanager->getNoRelativeCountries();
        $data['zones'] = $this->model_citymanager->getNoRelativeZones();

        $this->initLangVariables($data);

        $this->response->setOutput($this->loadView('zone_fias', $data));
    }

    public function search() {
        $json = [];
        if (isset($this->request->get['term'])) {
            $json = $this->model_citymanager->findFiasByName($this->request->get['term'], !empty($this->request->get['short']));
        }

        $this->sendJson($json);
    }

    public function saveGeneral() {
        $json = [];
        if ($this->hasPermission()) {
            if (!empty($this->request->post['progroman_cm_setting']['popup_cookie_time'])) {
                $this->request->post['progroman_cm_setting']['popup_cookie_time'] = (int)$this->request->post['progroman_cm_setting']['popup_cookie_time'];
            }

            $this->request->post['progroman_cm_setting']['geoip_license']
                = preg_replace('#\s#u', '', $this->request->post['progroman_cm_setting']['geoip_license']);

            if (!empty($this->request->post['progroman_cm_setting']['main_domain'])) {
                $this->request->post['progroman_cm_setting']['main_domain']
                    = rtrim(str_replace(['http://', 'https://'], '', $this->request->post['progroman_cm_setting']['main_domain']), '/');
            }

            // Чтобы не затереть все настройки
            // Переписываем только значения, которые есть в POST
            $this->load->model('setting/setting');
            $new = $this->request->post;
            $old = $this->model_setting_setting->getSetting('progroman_cm');
            if (!empty($old['progroman_cm_setting'])) {
                $new['progroman_cm_setting'] = array_merge($old['progroman_cm_setting'], $this->request->post['progroman_cm_setting']);
            }

            $this->model_setting_setting->editSetting('progroman_cm', $new);

            if (VERSION >= 3) {
                $this->model_setting_setting->editSetting('module_progroman_city_manager', $this->request->post);
            }
        } else {
            $json['warning'] = $this->error['warning'];
        }

        $json['license'] = !empty($this->request->post['progroman_cm_setting']['geoip_license'])
            && CityManager::validLicense($this->request->post['progroman_cm_setting']['geoip_license']);

        $this->sendJson($json);
    }

    public function savePopups() {
        $json = [];
        if ($this->hasPermission()) {
            if (isset($this->request->post['popup_cities'])) {
                foreach ($this->request->post['popup_cities'] as $key => $value) {
                    if (!(int)$value['fias_id']) {
                        $json['errors']['cities'][$key] = $this->language->get('error_fias');
                    }
                }

                if (empty($json['errors'])) {
                    $this->model_citymanager->clearCities();

                    if (!empty($this->request->post['popup_cities'])) {
                        $this->model_citymanager->editCities($this->request->post['popup_cities']);
                    }
                }
            } else {
                $this->model_citymanager->clearCities();
            }
        }

        $this->sendJson($json);
    }

    public function saveMessage() {
        $json = [];
        if ($this->hasPermission()) {
            if (empty($this->request->post['key']) || !preg_match('#^[a-zA-Z0-9_-]*$#', $this->request->post['key'])) {
                $json['errors']['key'] = $this->language->get('error_key');
            }

            $fias_id = isset($this->request->post['fias_id']) ? (int)$this->request->post['fias_id'] : 0;
            if (!$fias_id) {
                $json['errors']['fias']= $this->language->get('error_fias');
            }

            if (empty($json['errors'])) {
                if (!empty($this->request->post['id'])) {
                    $this->model_citymanager->editMessage($this->request->post['id'], $fias_id, $this->request->post['key'], $this->request->post['value']);
                } else {
                    $this->model_citymanager->addMessage($fias_id, $this->request->post['key'], $this->request->post['value']);
                }
            }
        }

        $this->sendJson($json);
    }

    public function removeMessage() {
        $json = [];
        if ($this->hasPermission()) {
            if (!empty($this->request->post['id'])) {
                $this->model_citymanager->removeMessage($this->request->post['id']);
            }
        }

        $this->sendJson($json);
    }

    public function saveRedirects() {
        $json = [];
        if ($this->hasPermission()) {
            if (isset($this->request->post['redirects'])) {
                $urlRegex = '#^http(?:s)?://(?:[a-zа-яё0-9]+(?:[\-a-zа-яё0-9]*[a-zа-яё0-9]+)?\.){0,}(?:[a-zа-яё0-9]+(?:[\-a-zа-яё0-9]*[a-zа-яё0-9]+)?){1,63}(?:\.[\-a-zа-яё0-9]{2,})+/(?:.*/)*$#u';
                foreach ($this->request->post['redirects'] as $key => & $value) {
                    if (!$value['url']) {
                        $json['errors']['subdomain'][$key] = $this->language->get('error_subdomain');
                    } else {
                        $value['url'] = $this->prepareDomainForRedirect($value['url']);
                        if (!preg_match($urlRegex, $value['url'])) {
                            $json['errors']['subdomain'][$key] = $this->language->get('error_subdomain');
                        }
                    }

                    if (!(int)$value['fias_id']) {
                        $json['errors']['fias'][$key] = $this->language->get('error_fias');
                    }
                }

                if (empty($json['errors'])) {
                    $this->model_citymanager->clearRedirects();

                    if (!empty($this->request->post['redirects'])) {
                        $this->model_citymanager->editRedirects($this->request->post['redirects']);
                    }
                }
            } else {
                $this->model_citymanager->clearRedirects();
            }
        }

        $this->sendJson($json);
    }

    public function saveCurrencies() {
        $json = [];
        if ($this->hasPermission()) {
            if (isset($this->request->post['currencies'])) {
                foreach ($this->request->post['currencies'] as $key => $value) {
                    if (!(int)$value['country_id']) {
                        $json['errors']['country'][$key] = $this->language->get('error_currency_country');
                    }

                    if (!$value['code']) {
                        $json['errors']['code'][$key] = $this->language->get('error_currency_code');
                    }
                }

                if (empty($json['errors'])) {
                    $this->model_citymanager->clearCurrencies();

                    if (!empty($this->request->post['currencies'])) {
                        $this->model_citymanager->editCurrencies($this->request->post['currencies']);
                    }
                }
            } else {
                $this->model_citymanager->clearCurrencies();
            }
        }

        $this->sendJson($json);
    }

    private function prepareDomainForRedirect($domain) {
        $domain = strtolower($domain);

        if (strpos($domain, 'http') !== 0) {
            $ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                || stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true || $_SERVER['SERVER_PORT'] == 443
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
                || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on');

            $domain = 'http' . ($ssl ? 's' : '') . '://' . $domain;
        }

        return rtrim($domain, '/') . '/';
    }

    private function hasPermission() {
        if (!$this->user->hasPermission('modify', $this->getModulePath())) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        $this->model_citymanager->install();
    }

    /**
     * Действие для файла базы данных
     */
    public function baseAction() {
        if (!$this->hasPermission()) {
            $json['error'] = $this->error['warning'];
            return $this->sendJson($json);
        }

        $settings = $this->config->get('progroman_cm_setting');
        if (empty($settings['geoip_license']) || !CityManager::validLicense($settings['geoip_license'])) {
            $json['error'] = $this->language->get('text_license');
            return $this->sendJson($json);
        }

        DatabaseFileAction::setCityManagerInfo(CityManager::MODULE_NAME, CityManager::VERSION, $settings['geoip_license']);

        $parts = explode('/', $this->request->get['action']);
        $class = 'progroman\CityManager\DatabaseFileAction\\' . array_shift($parts);

        $params = [];
        if ($parts) {
            foreach (explode(',', $parts[0]) as $part) {
                $param = explode('=', $part);
                $params[$param[0]] = $param[1];
            }
        }

        /** @var DatabaseFileAction $action */
        $action = new $class($this->language->all());
        $result = $action->step(!empty($this->request->get['step']) ? $this->request->get['step'] : false, $params);

        return $this->sendJson($result);
    }

    private function sendJson($json) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        return;
    }

    private function initLangVariables(&$args) {
        if (VERSION < 3) {
            $this->load->language($this->getModulePath());

            foreach ($this->language->all() as $key => $value) {
                if (!isset($args[$key])) {
                    $args[$key] = $value;
                }
            }
        }
    }

    private function makeUrl($route, $params = '') {
        $token = VERSION >= 3 ? 'user_token' : 'token';
        return $this->url->link($route, $token . '=' . $this->session->data[$token] . $params, 'SSL');
    }

    private function makeModuleUrl($action = '', $params = '') {
        $action = $action ? '/' . $action : '';
        return str_replace('&amp;', '&', $this->makeUrl($this->getModulePath() . $action, $params));
    }

    private function loadView($view, $data) {
        $view = $this->getModulePath() . '/' . $view;
        if (VERSION < '2.3') {
            $view .= '.tpl';
        }

        if (VERSION >= 2) {
            return $this->load->view($view, $data);
        } else {
            $this->template = $view;
            $this->data = $data;
            return $this->render();
        }
    }

    private function loadController($route) {
        if (VERSION >= 2) {
            return $this->load->controller($route);
        }

        $this->children[] = $route;

        return '';
    }

    private function getModulePath() {
        return (VERSION >= '2.3' ? 'extension/' : '') . 'module/progroman_city_manager';
    }

    private function getExtensionPath() {
        if (VERSION >= 3) {
            return 'marketplace/extension';
        } elseif (VERSION >= '2.3') {
            return 'extension/extension';
        } else {
            return 'extension/module';
        }
    }
}