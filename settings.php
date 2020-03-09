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
        get_string('configrequire_client_id', 'ytstream'),
        '',
        PARAM_TEXT
    ));

    // client secret
    $ytstream_settings->add(new admin_setting_configtext(
        'ytstream/client_secret',
        get_string('require_client_secret', 'ytstream'),
        get_string('configrequire_client_secret', 'ytstream'),
        '',
        PARAM_TEXT
    ));

}

if (empty($reportsbyname) && empty($rulesbyname)) {
    $ADMIN->add('modsettings', $ytstream_settings);
}

$settings = null;
