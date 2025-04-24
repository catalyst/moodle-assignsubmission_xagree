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

namespace assignsubmission_xagree;

use core\hook\output\before_standard_top_of_body_html_generation;
use html_writer;
use moodle_url;

/**
 * Allows the plugin to add any elements to the top of the body.
 *
 * @package    assignsubmission_xagree
 * @copyright  2025 Sumaiya Javed <sumaiya.javed@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Add the link to download checked submissions to the top of the body.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook): void {
        global $PAGE, $OUTPUT, $DB;


        if (during_initial_install() || isset($CFG->upgraderunning) || PHPUNIT_TEST) {
            // Do nothing during installation or upgrade.
            return;
        }

        if (!isloggedin()) {
            return;
        }

        try {

            if ($PAGE->url->compare(new moodle_url('/mod/assign/view.php'), URL_MATCH_BASE)) {
                $action = optional_param('action', '', PARAM_TEXT);
                if ($action == 'grading' && has_capability('mod/assign:grade', $PAGE->context)) {
                    if ($DB->record_exists('assignsubmission_xagree', ['cmid' => $PAGE->context->instanceid, 'agree' => 1])) {
                        $url = new moodle_url('/mod/assign/submission/xagree/downloadall.php', array('id' => $PAGE->context->instanceid));
                        $button = $OUTPUT->single_button($url, get_string('downloadallchecked', 'assignsubmission_xagree'));
                        $PAGE->set_button($PAGE->button . html_writer::div($button), 'xagree-downloadall');
                    }
                }
            }
        } catch (\dml_read_exception $e) {
            // During upgrades, no change.
            return;
        }
    }
}
