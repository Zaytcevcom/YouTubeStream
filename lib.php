<?php

/**
 * YouTubeStream
 *
 * @package    mod_ytstream
 * @copyright  2020 Zaytcev.com <zaydisk@yandex.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function ytstream_supports($feature) {

    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the ytstream into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $ytstream Submitted data from the form in mod_form.php
 * @param mod_ytstream_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted ytstream record
 */
function ytstream_add_instance(stdClass $ytstream, mod_ytstream_mod_form $mform = null) {
    global $DB;

    $ytstream->timecreated = time();
    $ytstream->is_remind = 0;

    /* *** */

    if ($ytstream->type == 'stream') {

        $params = ytstream_get_params();

        $time_end = $ytstream->time_start + 24 * 60 * 60;

        $utc = ytstream_get_utc();

        $ytstream->url = ytstream_create([
            'client_id'     => $params['client_id'],
            'client_secret' => $params['client_secret'],
            'access_token'  => $params['access_token'],
            'title'         => $ytstream->title,
            'description'   => $ytstream->description,
            'time_start'    => date('Y-m-d\TH:i:00' . $utc, $ytstream->time_start),
            'time_end'      => date('Y-m-d\TH:i:00' . $utc, $time_end),
            'privacy'       => 'public'
        ]);

    } else {
        $ytstream->url = ytstream_get_video_id($ytstream->url);
    }

    /* *** */

    $ytstream->id = $DB->insert_record('ytstream', $ytstream);

    ytstream_grade_item_update($ytstream);

    return $ytstream->id;
}

/**
 * Updates an instance of the ytstream in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $ytstream An object from the form in mod_form.php
 * @param mod_ytstream_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function ytstream_update_instance(stdClass $ytstream, mod_ytstream_mod_form $mform = null) {
    global $DB;

    $ytstream->timemodified = time();
    $ytstream->id = $ytstream->instance;
    
    $ytstream->is_remind = 0;

    $result = $DB->update_record('ytstream', $ytstream);

    ytstream_grade_item_update($ytstream);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every ytstream event in the site is checked, else
 * only ytstream events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function ytstream_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$ytstreams = $DB->get_records('ytstream')) {
            return true;
        }
    } else {
        if (!$ytstreams = $DB->get_records('ytstream', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($ytstreams as $ytstream) {
        // Create a function such as the one below to deal with updating calendar events.
        // ytstream_update_events($ytstream);
    }

    return true;
}

/**
 * Removes an instance of the ytstream from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function ytstream_delete_instance($id) {
    global $DB;

    if (!$ytstream = $DB->get_record('ytstream', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('ytstream', array('id' => $ytstream->id));

    ytstream_grade_item_delete($ytstream);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $ytstream The ytstream instance record
 * @return stdClass|null
 */
function ytstream_user_outline($course, $user, $mod, $ytstream) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $ytstream the module instance record
 */
function ytstream_user_complete($course, $user, $mod, $ytstream) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in ytstream activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function ytstream_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link ytstream_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function ytstream_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@link ytstream_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function ytstream_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function ytstream_cron() {
    global $DB;

    $time = time();

    $records = $DB->get_records_select('ytstream', 'type = "stream" && is_remind = 0 && time_start - remind * 60 <= ' . $time);

    if (count($records) == 0) {
        return true;
    }

    $config = get_config('ytstream');

    $fromUser = core_user::get_support_user();

    foreach ($records as $ytstream) {
        
        // Template
        $subject = (isset($config->email_subject)) ? $config->email_subject : '';
    	$message = (isset($config->email_message)) ? $config->email_message : '';

        // Subject
        $subject = preg_replace("/{title}/u", $ytstream->title, $subject);
        $subject = preg_replace("/{time_start}/u", date('d.m.Y H:i', $ytstream->time_start), $subject);

        // Message
        $message = preg_replace("/{title}/u", $ytstream->title, $message);
        $message = preg_replace("/{description}/u", $ytstream->description, $message);
        $message = preg_replace("/{time_start}/u", date('d.m.Y H:i', $ytstream->time_start), $message);

        // Get enrol of course
        $enrolOfCourse = $DB->get_records('enrol', array('courseid' => $ytstream->course));

        $enrol_ids = [];

        foreach ($enrolOfCourse as $value) {
            $enrol_ids[] = $value->id;
        }

        if (count($enrol_ids) == 0) {
            continue;
        }

        // Get users in course
        $usersInCource = $DB->get_records_list('user_enrolments', 'enrolid', $enrol_ids);

        $user_ids = [];

        foreach ($usersInCource as $value) {
            $user_ids[] = $value->userid;
        }

        if (count($user_ids) == 0) {
            continue;
        }

        // Get users info
        $users = $DB->get_records_list('user', 'id', $user_ids);

        foreach ($users as $user) {

            // Send email messages
            $success = email_to_user($user, $fromUser, $subject, $message);
        }

        $ytstream->is_remind = 1;

        $DB->update_record('ytstream', $ytstream);
        
    }

    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function ytstream_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of ytstream?
 *
 * This function returns if a scale is being used by one ytstream
 * if it has support for grading and scales.
 *
 * @param int $ytstreamid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given ytstream instance
 */
function ytstream_scale_used($ytstreamid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('ytstream', array('id' => $ytstreamid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of ytstream.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any ytstream instance
 */
function ytstream_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('ytstream', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given ytstream instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $ytstream instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function ytstream_grade_item_update(stdClass $ytstream, $reset = false) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($ytstream->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($ytstream->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax'] = $ytstream->grade;
        $item['grademin'] = 0;
    } else if ($ytstream->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid'] = -$ytstream->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/ytstream', $ytstream->course, 'mod', 'ytstream',
        $ytstream->id, 0, null, $item);
}

/**
 * Delete grade item for given ytstream instance
 *
 * @param stdClass $ytstream instance object
 * @return grade_item
 */
function ytstream_grade_item_delete($ytstream) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/ytstream', $ytstream->course, 'mod', 'ytstream',
        $ytstream->id, 0, null, array('deleted' => 1));
}

/**
 * Update ytstream grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $ytstream instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function ytstream_update_grades(stdClass $ytstream, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/ytstream', $ytstream->course, 'mod', 'ytstream', $ytstream->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function ytstream_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for ytstream file areas
 *
 * @package mod_ytstream
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function ytstream_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the ytstream file areas
 *
 * @package mod_ytstream
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the ytstream's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function ytstream_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* *** */

// Получение настроек для работы с API YouTube
function ytstream_get_params()
{

    $config = get_config('ytstream');

    return [
        'client_id'     => (isset($config->client_id)) ? $config->client_id : '',
        'client_secret' => (isset($config->client_secret)) ? $config->client_secret : '',
        'access_token'  => (isset($_SESSION['ytstream_access_token'])) ? $_SESSION['ytstream_access_token'] : ''
    ];
}

// Проверка валидности access_token
function ytstream_check_token($params)
{
    require_once __DIR__ . '/vendor/autoload.php';

    $client = new Google_Client();
    $client->setClientId($params['client_id']);
    $client->setClientSecret($params['client_secret']);
    $client->setScopes('https://www.googleapis.com/auth/youtube');

    $redirect = filter_var(ytstream_get_auth_uri(), FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');

    // Define an object that will be used to make all API requests.
    $youtube = new Google_Service_YouTube($client);

    $client->setAccessToken($params['access_token']);

    try {
        
        $result = $youtube->liveBroadcasts->listLiveBroadcasts('id,snippet', array('mine' => 'true'));

        return $result;

    } catch (Google_Service_Exception $e) {

        return false;
    
    } catch (Google_Exception $e) {
    
        return false;
    
    }
}

// Получение access_token
function ytstream_auth($params)
{
    require_once __DIR__ . '/vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId($params['client_id']);
    $client->setClientSecret($params['client_secret']);
    $client->setScopes('https://www.googleapis.com/auth/youtube');

    $redirect = filter_var(ytstream_get_auth_uri(), FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);

    if (isset($_GET['code'])) {
        $client->authenticate($_GET['code']);
    }

    $result = $client->getAccessToken();

    $_SESSION['ytstream_access_token'] = $result['access_token'];

    return $result;
}

// Получение ссылки для авторизации
function ytstream_get_auth_link($params)
{
    require_once __DIR__ . '/vendor/autoload.php';

    $client = new Google_Client();
    $client->setClientId($params['client_id']);
    $client->setClientSecret($params['client_secret']);
    $client->setScopes('https://www.googleapis.com/auth/youtube');

    $redirect = filter_var(ytstream_get_auth_uri(), FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');

    return $client->createAuthUrl();
}

function ytstream_create($params)
{
    require_once __DIR__ . '/vendor/autoload.php';

    $client = new Google_Client();
    $client->setClientId($params['client_id']);
    $client->setClientSecret($params['client_secret']);
    $client->setScopes('https://www.googleapis.com/auth/youtube');
    $redirect = filter_var(ytstream_get_auth_uri(), FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);

    // Define an object that will be used to make all API requests.
    $youtube = new Google_Service_YouTube($client);

    $client->setAccessToken($params['access_token']);

    try {
        // Create an object for the liveBroadcast resource's snippet. Specify values
        // for the snippet's title, scheduled start time, and scheduled end time.
        $broadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($params['title']);
        $broadcastSnippet->setDescription($params['description']);
        $broadcastSnippet->setScheduledStartTime($params['time_start']);

        if (isset($params['time_end'])) {
            $broadcastSnippet->setScheduledEndTime($params['time_end']);
        }

        // Create an object for the liveBroadcast resource's status, and set the
        // broadcast's status to "private".
        $status = new Google_Service_YouTube_LiveBroadcastStatus();
        $status->setPrivacyStatus($params['privacy']);

        // Create the API request that inserts the liveBroadcast resource.
        $broadcastInsert = new Google_Service_YouTube_LiveBroadcast();
        $broadcastInsert->setSnippet($broadcastSnippet);
        $broadcastInsert->setStatus($status);
        $broadcastInsert->setKind('youtube#liveBroadcast');

        // Execute the request and return an object that contains information
        // about the new broadcast.
        $broadcastsResponse = $youtube->liveBroadcasts->insert('snippet,status', $broadcastInsert, array());

        // Create an object for the liveStream resource's snippet. Specify a value
        // for the snippet's title.
        $streamSnippet = new Google_Service_YouTube_LiveStreamSnippet();
        $streamSnippet->setTitle($params['title']);
        $streamSnippet->setDescription($params['description']);

        // Create an object for content distribution network details for the live
        // stream and specify the stream's format and ingestion type.
        $cdn = new Google_Service_YouTube_CdnSettings();
        $cdn->setFormat("1080p");
        $cdn->setIngestionType('rtmp');

        // Create the API request that inserts the liveStream resource.
        $streamInsert = new Google_Service_YouTube_LiveStream();
        $streamInsert->setSnippet($streamSnippet);
        $streamInsert->setCdn($cdn);
        $streamInsert->setKind('youtube#liveStream');

        // Execute the request and return an object that contains information
        // about the new stream.
        $streamsResponse = $youtube->liveStreams->insert('snippet,cdn', $streamInsert, array());

        // Bind the broadcast to the live stream.
        $bindBroadcastResponse = $youtube->liveBroadcasts->bind(
        $broadcastsResponse['id'],'id,contentDetails',
        array(
          'streamId' => $streamsResponse['id'],
        ));

        return $broadcastsResponse['id'];

    } catch (Google_Service_Exception $e) {
        return false;
    } catch (Google_Exception $e) {
        return false;
    }   
}

// Разрешенный URI (Для приложения YouTube)
function ytstream_get_auth_uri() 
{
    global $DB, $CFG;
    return $CFG->wwwroot . '/mod/ytstream/auth.php';
}

// Сохранение последнего url адреса (для редиректа после авторизации YouTube)
function ytstream_last_url_save()
{
    $_SESSION['yts_last_url'] = ytstream_protocol() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Получение последнего сохраненного url адреса
function ytstream_last_url_get()
{
    return (isset($_SESSION['yts_last_url'])) ? $_SESSION['yts_last_url'] : '';
}

function ytstream_protocol()
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    return $protocol;
}

function ytstream_gen_url($str)
{
    return '<a href="https://youtu.be/' . $str . '" target="_blank">youtu.be/' . $str . '</a>';
}

function ytstream_get_video_id($url)
{
    $pos = strpos($url, '?v=');

    if ($pos === false) {

        // Короткая ссылка
        return explode('.be/', $url)[1];

    }

    // Полная ссылка
    return explode('&', explode('v=', $url)[1])[0];
}

function ytstream_get_utc()
{
	global $CFG;

	$timezone = new DateTimeZone($CFG->timezone);
    $transitions = array_slice($timezone->getTransitions(), -3, null, true);

    foreach (array_reverse($transitions, true) as $transition)
    {
        if ($transition['isdst'] == 1) {
            continue;
        }

        return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
    }

    return '+00:00';
}
