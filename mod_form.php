<?php

/**
 * YouTubeStream
 *
 * @package    mod_ytstream
 * @copyright  2020 Zaytcev.com <zaydisk@yandex.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

ytstream_last_url_save();

class mod_ytstream_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('yts_name', 'ytstream'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $mform->addElement('header', 'ytstreamfieldset', get_string('yts_fieldset', 'ytstream'));

        $params = ytstream_get_params();

        // Field access_token
        $checkAccessToken = ytstream_check_token($params);

        // Field select type
        $mform->addElement('select', 'type', get_string('yts_type', 'ytstream'),
            array(
                'video'  => get_string('yts_type_video', 'ytstream'),
                'stream' => get_string('yts_type_stream', 'ytstream'),
            )
        );
        $mform->setDefault('type', 'video');

        // Field url
        $mform->addElement('text', 'url', get_string('yts_url', 'ytstream'), ['size' => '64', 'placeholder' => get_string('yts_url_placeholder', 'ytstream')]);
        $mform->hideIf('url', 'type', 'neq', 'video');

        if ($checkAccessToken == false) {

            $authUrl = ytstream_get_auth_link($params);

            $str = '<a href=' . $authUrl . '>' . get_string('yts_access_token_link', 'ytstream') . '</a>';

            $group = [];
            $group[] =& $mform->createElement('static', 'access_token', get_string('yts_access_token', 'ytstream'), $str);
            $mform->addGroup($group, 'formgroup', '', ' ', false);
            $mform->hideIf('formgroup', 'type', 'neq', 'stream');

        } else {

            // Field title
            $mform->addElement('text', 'title', get_string('yts_title', 'ytstream'), ['size' => '64', 'placeholder' => get_string('yts_title_placeholder', 'ytstream')]);
            //$mform->addRule('title', null, 'required', null, 'client');
            $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
            $mform->hideIf('title', 'type', 'neq', 'stream');
            
            // Field description
            $mform->addElement('textarea', 'description', get_string('yts_description', 'ytstream'), [
                'cols' => '100',
                'rows' => '6',
                'placeholder' => get_string('yts_description_placeholder', 'ytstream')
            ]);
            //$mform->addRule('description', null, 'required', null, 'client');
            $mform->hideIf('description', 'type', 'neq', 'stream');

            // Field time_start
            $mform->addElement('date_time_selector', 'time_start', get_string('yts_time_start', 'ytstream'));
            $mform->hideIf('time_start', 'type', 'neq', 'stream');

            // Fiele notification
            $mform->addElement('select', 'remind', get_string('yts_remind', 'ytstream'),
                array(
                    0  => get_string('yts_remind_none', 'ytstream'),
                    15 => get_string('yts_remind_15', 'ytstream'),
                    30 => get_string('yts_remind_30', 'ytstream'),
                    60 => get_string('yts_remind_60', 'ytstream'),
                    120 => get_string('yts_remind_120', 'ytstream'),
                    180 => get_string('yts_remind_180', 'ytstream'),
                    240 => get_string('yts_remind_240', 'ytstream'),
                )
            );
            $mform->setDefault('remind', 60);
            $mform->hideIf('remind', 'type', 'neq', 'stream');
        }

        // Add standard grading elements.
        //$this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
