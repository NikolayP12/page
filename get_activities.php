<?php

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
