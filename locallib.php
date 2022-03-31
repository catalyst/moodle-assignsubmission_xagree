<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * library class for xagree submission plugin extending submission plugin base class
 *
 * @package assignsubmission_xagree
 * @copyright   2022 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_xagree extends assign_submission_plugin {

    /**
     * Get the name of the online text submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_xagree');
    }


    /**
     * Get the settings for onlinetext submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;
        $defaultagreement = empty($this->get_config('agreement')) ?
                            get_config('assignsubmission_xagree', 'agreement') : $this->get_config('agreement');
        if (has_capability('assignsubmission/xagree:manage', context_course::instance($COURSE->id))) {
            $mform->addElement('textarea', 'assignsubmission_xagree_agreement',
                               get_string('pluginname', 'assignsubmission_xagree'));
        } else {
            $mform->addElement('hidden', 'assignsubmission_xagree_agreement');
        }
        $mform->setType('assignsubmission_xagree_agreement', PARAM_RAW);
        $mform->setDefault('assignsubmission_xagree_agreement', $defaultagreement);
        $mform->hideIf('assignsubmission_xagree_agreement',
                       'assignsubmission_xagree_enabled',
                       'notchecked');
    }

    /**
     * Save the settings for onlinetext submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        if (isset($data->assignsubmission_xagree_agreement)) {
            $this->set_config('agreement', $data->assignsubmission_xagree_agreement);
        }

        return true;
    }

    /**
     * Add form elements for settings
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $DB;
        if (!empty($this->get_config('enabled'))) {
            $mform->addElement('checkbox', 'xagree_agree', $this->get_config('agreement'));

            $params = ['cmid' => $this->assignment->get_course_module()->id, 'userid' => $submission->userid];
            $existingrecord = $DB->get_record('assignsubmission_xagree', $params);
            if (!empty($existingrecord)) {
                $mform->setDefault('xagree_agree', $existingrecord->agree);
            }
            // Re-arrange form elements to put this form element in the right place.
            $this->rearrange_elements($mform, ['lastmodified', 'submission header', 'submissionstatement', 'xagree_agree']);
        }
        return true;
    }

    /**
     * Helper function to rearrange elements using a $sortorder.
     *
     * @param moodle_form $mform
     * @param array $sortorder
     * @return void
     */
    public function rearrange_elements(&$mform, $sortorder) {
        if (!empty($sortorder)) {
            $originalelements = $mform->_elements;
            $namedelements = array();

            // Get list of elements using name as the key.
            $i = 0;
            foreach ($originalelements as $id => $element) {
                if (!empty($element->_attributes['name'])) {
                    $namedelements[$element->_attributes['name']] = $element;
                } else if (!empty($element->_name)) {
                    $namedelements[$element->_name] = $element;
                } else {
                    // This element didn't have a name - shouldn't happen but add it to the end so we don't lose it.
                    // Prefix with some random txt so that it doesn't conflict with a real field.
                    $namedelements['AF235A3'.$i] = $element;
                    $i++;
                }
            }
            $newelements = array();
            // Always add id as the first element.
            if (isset($namedelements['id'])) {
                $newelements[] = $namedelements['id'];
                unset($namedelements['id']);
            }

            // Get sorted fields.
            foreach ($sortorder as $item) {
                if (isset($namedelements[$item])) {
                    $newelements[] = $namedelements[$item];
                    unset($namedelements[$item]);
                }
            }
            // Now add the fields in the form that weren't included in the sortorder.
            foreach ($namedelements as $item) {
                $newelements[] = $item;
            }
            $mform->_elements = $newelements;
        }
    }

    /**
     * Save data to the database and trigger plagiarism plugin,
     * if enabled, to scan the uploaded content via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;
        if (empty($this->get_config('enabled'))) {
            return true;
        }

        if ($submission->userid <> $USER->id) {
            // Don't allow a teacher/admin to change this for the student.
            return true;
        }
        $params = ['cmid' => $this->assignment->get_course_module()->id, 'userid' => $submission->userid];
        $existingrecord = $DB->get_record('assignsubmission_xagree', $params);
        if (!isset($data->xagree_agree)) {
            $data->xagree_agree = 0;
        }
        if (!empty($existingrecord)) {
            if ($existingrecord->agree <> $data->xagree_agree) {
                $existingrecord->agree = (int)$data->xagree_agree;
                $DB->update_record('assignsubmission_xagree', $existingrecord);
            }
        } else {
            if (!empty($data->xagree_agree)) {
                $params['agree'] = (int)$data->xagree_agree;
                $DB->insert_record('assignsubmission_xagree', $params);
            }
        }
        return true;
    }

    /**
     * Return a list of the text fields that can be imported/exported by this plugin
     *
     * @return array An array of field names and descriptions. (name=>description, ...)
     */
    public function get_editor_fields() {
        return array('agreement' => get_string('pluginname', 'assignsubmission_agree'));
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assignsubmission_xagree',
                            array('cmid' => $this->assignment->get_course_module()->id));

        return true;
    }

    /**
     * No text is set for this plugin
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        return true;
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data The submission data
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
         return true;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of settings
     * @since Moodle 3.2
     */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }
}
