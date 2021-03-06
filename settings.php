<?php

/**
 * YouTubeStream
 *
 * @package    mod_ytstream
 * @copyright  2020 Zaytcev.com <zaydisk@yandex.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ytstream/lib.php');

$pagetitle = get_string('modulename', 'ytstream');

$ytstream_settings = new admin_settingpage('modsettingytstream', $pagetitle, 'moodle/site:config');

if ($ADMIN->fulltree) {

    // Application settings
    $ytstream_settings->add(new admin_setting_heading('ytstream_settings_app_header', get_string('configrequire_app_header', 'ytstream'), ''));

    // Description
    $ytstream_settings->add(new admin_setting_heading(
        'ytstreamintro',
        '',
        get_string('config_intro', 'ytstream') . '<br><br>' . get_string('configrequire_uri', 'ytstream')  . ' <b>' . ytstream_get_auth_uri() . '</b>'
    ));

    // client id
    $ytstream_settings->add(new admin_setting_configtext(
        'ytstream/client_id',
        get_string('require_client_id', 'ytstream'),
        '',
        '',
        PARAM_TEXT,
        70
    ));

    // client secret
    $ytstream_settings->add(new admin_setting_configtext(
        'ytstream/client_secret',
        get_string('require_client_secret', 'ytstream'),
        '',
        '',
        PARAM_TEXT,
        70
    ));

    // Mail settings
    $ytstream_settings->add(new admin_setting_heading('ytstream_settings_mail_header', get_string('configrequire_email_header', 'ytstream'), ''));

    // Email subject
    $ytstream_settings->add(new admin_setting_configtext(
        'ytstream/email_subject',
        get_string('require_email_subject', 'ytstream'),
        '',
        '',
        PARAM_TEXT,
        70
    ));

    // Email message
    $ytstream_settings->add(new admin_setting_configtextarea(
        'ytstream/email_message',
        get_string('require_email_message', 'ytstream'),
        '',
        '',
        PARAM_CLEANHTML
    ));
    

}

if (empty($reportsbyname) && empty($rulesbyname)) {
    $ADMIN->add('modsettings', $ytstream_settings);
}

$settings = null;
