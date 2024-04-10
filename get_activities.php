<?php

require_once('../../config.php');
require_login();
$context = context_system::instance();
global $COURSE;
// Asegúrate de validar los permisos del usuario para esta operación.
if (!has_capability('moodle/course:manageactivities', $context)) {
    throw new moodle_exception('nopermissions', 'error', '', 'manage activities');
}

$type = required_param('type', PARAM_PLUGIN); // Asegúrate de validar y sanear el tipo de módulo.
$courseid = required_param('courseid', PARAM_INT); // Asegúrate de validar y sanear el id de módulo.

global $DB, $CFG;
require_once($CFG->dirroot . '/course/lib.php');

$activities = [];

// Asegúrate de ajustar esta consulta para recuperar las actividades del tipo especificado.
if ($type) {
    $modinfo = get_fast_modinfo($courseid);
    $cms = $modinfo->get_cms();

    foreach ($cms as $cm) {
        // Filtrar por tipo de módulo.
        if ($cm->modname === $type && $cm->uservisible) {
            // Solo incluir actividades visibles para el usuario.
            $activities[] = [
                'id' => $cm->instance,
                'name' => format_string($cm->name),
            ];
        }
    }
}
$debug_info = [
    'type_received' => $type,
    'activities' => $activities,
    'courseid' => $courseid,
    'courseid' =>  $COURSE->fullname,
    // Añadir más información de depuración si es necesario
];

header('Content-Type: application/json');
echo json_encode([
    'data' => $activities,
    'debug' => $debug_info,
]);
exit;
