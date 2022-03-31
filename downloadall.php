<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Download script for checked assignment submissions.
 *
 * @package   assignsubmission_xagree
 * @copyright 2022 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/assign:grade', $context);
$userids = [];
$users = $DB->get_records('assignsubmission_xagree', ['cmid' => $cm->id, 'agree' => 1]);
foreach ($users as $user) {
    $userids[] = $user->userid;
}

if (empty($userids)) {
    $url = new moodle_url('/mod/assign/view.php', ['id' => $cm->id]);
    throw new moodle_exception('nocheckedassignments', 'assignsubmission_xagree', $url);
}

// Use reflection to trigger core download_submissions with userlist.
$r = new ReflectionMethod('assign', 'download_submissions');
$r->setAccessible(true);
$r->invoke(new assign($context, $cm, $course), $userids);
