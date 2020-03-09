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

$id = optional_param('id', 0, PARAM_INT);
$y = optional_param('y', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('ytstream', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $ytstream = $DB->get_record('ytstream', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($y) {
    $ytstream = $DB->get_record('ytstream', array('id' => $y), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $ytstream->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('ytstream', $ytstream->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_ytstream\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $ytstream);
$event->trigger();

$PAGE->set_url('/mod/ytstream/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($ytstream->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($ytstream->name), 2);

echo '<br><iframe width="720px" height="405px" src="https://www.youtube.com/embed/' . $ytstream->url . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

echo '<br>' . get_string('yts_view_link', 'ytstream') . ': ' . ytstream_gen_url($ytstream->url);

if ($ytstream->type == 'stream') {
	echo ', ' . get_string('yts_view_time_start', 'ytstream') . ': ' . date('d.m.Y H:i', $ytstream->time_start);
	echo '<br><br><b>' . $ytstream->title . '</b>';
	echo '<br>' . $ytstream->description;
}

// Finish the page.
echo $OUTPUT->footer();
