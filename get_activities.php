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
 * Handles getting the modules of the course by the type selected.
 *
 * @package     mod_page
 * @copyright   2024 Nikolay <nikolaypn2002@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_login();

global $COURSE, $DB, $CFG;

// Retrieve and validate 'type' and 'courseid' from request parameters.
$type = required_param('type', PARAM_PLUGIN);
$courseid = required_param('courseid', PARAM_INT);

// Establish the context for the specified course.
$context = context_course::instance($courseid);
$PAGE->set_context($context);

// Ensure the user has permission to manage activities.
try {
    if (!has_capability('moodle/course:manageactivities', $context)) {
        throw new moodle_exception('nopermissions', 'error', '', 'manage activities');
    }
} catch (moodle_exception $e) {
    \core\notification::error(get_string($e->errorcode, $e->module));
}

// Include necessary library for course-related operations.
require_once($CFG->dirroot . '/course/lib.php');

$activities = [];

// If a specific module type is requested, filter and gather its details.
if ($type) {
    $modinfo = get_fast_modinfo($courseid);
    $cms = $modinfo->get_cms();
    foreach ($cms as $cm) {
        // Include only the specified type of module that is visible to the user.
        if ($cm->modname === $type && $cm->uservisible) {
            $activities[] = [
                'id' => $cm->instance,
                'name' => format_string($cm->name),
            ];
        }
    }
}

// Debug information for troubleshooting.
$debug_info = [
    'type_received' => $type,
    'activities' => $activities,
    'courseid' => $courseid,
];

// Respond with activities and debug information in JSON format.
header('Content-Type: application/json');
echo json_encode([
    'data' => $activities,
    'debug' => $debug_info,
]);
exit;
