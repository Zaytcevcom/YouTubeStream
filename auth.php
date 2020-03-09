<?php

/**
 * YouTubeStream
 *
 * @package    mod_ytstream
 * @copyright  2020 Zaytcev.com <zaydisk@yandex.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once(__DIR__ . '/lib.php');

ytstream_auth(ytstream_get_params());

header('Location: ' . ytstream_last_url_get());
?>