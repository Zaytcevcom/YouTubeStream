<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'YouTube Stream';
$string['modulename'] = 'YouTube Stream';
$string['modulename_help'] = 'Модуль «YouTube Stream» позволяет встраивать видеозаписи с сервиса YouTube';

// Settings Page
$string['configrequire_app_header'] = 'Настройки приложения';
$string['config_intro'] = 'Для подключения модуля «YouTube Stream» укажите данные от YouTube приложения, через которое будет происходить взаимодействие с API.';
$string['configrequire_uri'] = 'В качестве разрешенного URI адреса в настройках приложения укажите:';

$string['require_client_id'] = 'ID приложения';
$string['require_client_secret'] = 'Секретный ключ';

$string['configrequire_email_header'] = 'Настройки email оповещений';
$string['require_email_subject'] = 'Тема сообщения';
$string['require_email_message'] = 'Шаблон сообщения';


// Settings Plagin
$string['yts_name'] = 'Название';

$string['yts_type'] = 'Тип';
$string['yts_type_video'] = 'Существующая видеозапись';
$string['yts_type_stream'] = 'Прямая трансляция';
$string['yts_url'] = 'Ссылка на видеозапись';
$string['yts_url_placeholder'] = 'Укажите ссылку на видеозапись';

$string['yts_fieldset'] = 'Настройки вебинара';
$string['yts_title'] = 'Наименование вебинара';
$string['yts_title_placeholder'] = 'Наименование YouTube трансляции';
$string['yts_description'] = 'Описание вебинара';
$string['yts_description_placeholder'] = 'Описание YouTube трансляции';
$string['yts_time_start'] = 'Дата и время начала';
$string['yts_time_end'] = 'Дата и время окончания';
$string['yts_remind'] = 'Уведомить участников о начале трансляции';
$string['yts_remind_none'] = 'Не уведомлять';
$string['yts_remind_15'] = 'Уведомить за 15 мин до начала';
$string['yts_remind_30'] = 'Уведомить за 30 мин до начала';
$string['yts_remind_60'] = 'Уведомить за час до начала';
$string['yts_remind_120'] = 'Уведомить за 2 часа до начала';
$string['yts_remind_180'] = 'Уведомить за 3 часа до начала';
$string['yts_remind_240'] = 'Уведомить за 4 часа до начала';

$string['yts_access_token'] = 'Ключ доступа YouTube';
$string['yts_access_token_link'] = 'Пожалуйста, авторизуйтесь через YouTube »';

// View page
$string['yts_view_link'] = 'Ссылка';
$string['yts_view_time_start'] = 'дата начала';
