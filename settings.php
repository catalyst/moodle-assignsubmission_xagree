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
 * Plugin administration pages are defined here.
 *
 * @package     assignsubmission_xagree
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configcheckbox('assignsubmission_xagree/default',
                   new lang_string('default', 'assignsubmission_xagree'),
                   new lang_string('default_help', 'assignsubmission_xagree'), 0));

$settings->add(new admin_setting_configtextarea('assignsubmission_xagree/agreement',
                   new lang_string('defaultagreement', 'assignsubmission_xagree'),
                   new lang_string('defaultagreement_help', 'assignsubmission_xagree'), '', PARAM_RAW));
