<?php
$_['heading_title'] = 'ProgRoman - CityManager+GeoIP ' . \progroman\CityManager\CityManager::VERSION;

// Entry
$_['entry_default_city'] = 'Город по-умолчанию<span class="help" data-toggle="tooltip" title="Устанавливается, если город не определен по IP, по-умолчанию, не привязывается к основному домену"></span>';
$_['entry_use_geoip'] = 'Определять город по IP<span class="help" data-toggle="tooltip" title="Включить определение города по IP-адресу"></span>';
$_['entry_use_fullname_city'] = 'Добавлять район к городу<span class="help" data-toggle="tooltip" title="Например, \'Одинцово\' или \'Одинцовский р-н, Одинцово\'"></span>';
$_['entry_replace_blanks'] = 'Включить замену в title, keywords, description<span class="help" data-toggle="tooltip" title="Шаблоны: %CITY% - город<br>%ZONE% - регион<br>%COUNTRY% - страна<br>%MSG_key% - геосообщение с ключом key"></span>';
$_['entry_integration_simple'] = 'Интеграция с модулем "Простая регистрация и заказ Simple"<span class="help" data-toggle="tooltip" title="1)Использовать базу IP модуля 2)Использовать базу городов в автозаполнеии города в Simple">';
$_['entry_use_cookie'] = 'Сохранять регион в cookie';
$_['entry_popup_cookie_time'] = 'Показывать "Угадали город"';
$_['entry_key'] = 'Ключ';
$_['entry_zone'] = 'Город / регион / страна';
$_['entry_city'] = 'Город';
$_['entry_sort'] = 'Сортировка';
$_['entry_value'] = 'Значение';
$_['entry_subdomain'] = 'Поддомен';
$_['entry_country'] = 'Страна';
$_['entry_currency'] = 'Валюта';
$_['entry_disable_autoredirect'] = 'Отключить авторедирект при первом заходе<span class="help" data-toggle="tooltip" title="Переход на поддомен только при выборе города в попапе"></span>';
$_['entry_domain'] = 'Основной домен <span class="help" data-toggle="tooltip" title="Укажите, если отличается от текущего"></span>';
$_['entry_license'] = 'Лицензия<span class="help" data-toggle="tooltip" title="Ключ, выданный автором"></span>';
$_['entry_status'] = 'Статус';
$_['entry_sub_enabled'] = 'Включено<span class="help" data-toggle="tooltip" title="Отключите, если не используете данный функционал, чтобы избежать дополнительных запросов к БД"></span>';
$_['entry_enabled_redirects'] = 'Включить редиректы';

$_['text_customer_groups_info'] = 'Функционал доступен только для GeoIP Pro!';
$_['text_databases'] = 'Базы данных';
$_['text_database_cities'] = 'Базы городов';
$_['text_popup_cities'] = 'Города для попапа выбора города';
$_['text_regions_info'] = 'Зайдите в разделы "Локализация / Страны", "Локализация / Регионы", убедитесь, что перечисленные ниже страны и регионы добавлены со статусом "Включено"';
$_['text_regions_info_success'] = 'Все в порядке. Дополнительных настроек не требуется.';
$_['text_no_relative_countries'] = 'Не найдены совпадения для стран';
$_['text_no_relative_zones'] = 'Не найдены совпадения для регионов';
$_['text_module'] = 'Модули';
$_['text_success'] = 'Настройки модуля обновлены!';
$_['text_license'] = 'Не указана лицензия';

$_['text_every_visit'] = 'При каждом новом визите';
$_['text_day'] = 'Раз в сутки';
$_['text_week'] = 'Раз в неделю';
$_['text_month'] = 'Раз в месяц';
$_['text_year'] = 'Раз в год';

$_['tab_popup'] = 'Попапы';
$_['tab_messages'] = 'Геосообщения';
$_['tab_redirects'] = 'Поддомены';
$_['tab_currencies'] = 'Валюта';
$_['tab_regions'] = 'Регионы';
$_['tab_groups'] = 'Группы покупателей';

$_['error_permission'] = 'У Вас нет прав для управления этим модулем!';
$_['error_key'] = 'Поле должно содержать латинские буквы, цифры и знаки "-", "_"';
$_['error_fias'] = 'Укажите зону';
$_['error_subdomain'] = 'Укажите поддомен в виде: http://abc.site.com/ или http://site.com/path/to/';
$_['error_currency_country'] = 'Укажите страну';
$_['error_currency_code'] = 'Укажите валюту';
$_['error_license'] = 'Модуль не активирован, получите лицензионный ключ у автора модуля';
$_['error_unzip'] = 'Zip архив не удалось открыть';
$_['error_create_file'] = 'Не удалось создать файл %s';
$_['error_read_file'] = 'Не удалось открыть файл %s';
$_['error_create_dir'] = 'Не удалось создать директорию %s';
$_['error_upload'] = 'Не удалось загрузить файл, ответ сервера %s';
$_['error_bug'] = 'Неизвестная ошибка: %s. Сообщите автору модуля';

$_['text_base_ip_name'] = 'База IP-адресов';
$_['text_not_loaded'] = 'Не загружена';
$_['text_there_is_a_new_version'] = 'Есть новая версия';
$_['text_latest_version_installed'] = 'Да';//'Установлена последняя версия';
$_['text_load'] = 'Загрузить';
$_['text_loading'] = 'Загрузка';
$_['text_sxgeo_manual_upload'] = 'Загрузите файл вручную: <a href="http://sypexgeo.net/files/SxGeoCity_utf8.zip" target="_blank">скачайте zip-файл</a>, разархивируйте в %s';
$_['text_unzip'] = 'Распаковка';
$_['text_query'] = 'Выполнение SQL-запроса';
$_['text_clear'] = 'Удаление временных файлов';
$_['text_delete'] = 'Удаление';
$_['text_database_uploaded'] = 'База данных успешно загружена';
$_['text_end'] = 'Завершено';
$_['text_installed_full'] = 'Полная версия';
$_['text_installed_cities'] = 'Только города';