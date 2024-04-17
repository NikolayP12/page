<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

//$context = context_system::instance();
global $DB, $USER, $PAGE, $COURSE;

// Verifica que el usuario ha iniciado sesión y tiene permiso para enviar el correo.
require_login();

if (!isloggedin() || isguestuser()) {
    throw new moodle_exception('nopermissions', 'error', '', 'send email');
}

// Establecer el contexto del módulo de curso.
$cmid = required_param('id', PARAM_INT); // ID del módulo del curso.
$cm = get_coursemodule_from_id('page', $cmid, 0, false, MUST_EXIST); // Asegúrate de que el 'page' coincide con el tipo de módulo.
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$context = context_module::instance($cmid);

$PAGE->set_context($context);
$PAGE->set_url('/mod/page/send_question.php', array('id' => $cm->id));
$PAGE->set_course($course);
$PAGE->set_cm($cm);


// Verifica que el formulario se haya enviado y la sesión sea válida.
if (data_submitted() && confirm_sesskey()) {
    $teacheremail = required_param('teacheremail', PARAM_EMAIL);
    $subject = required_param('subject', PARAM_TEXT);
    $messagebody = required_param('messagebody', PARAM_RAW);

    // Busca el objeto del usuario destinatario usando el correo electrónico proporcionado.
    $teacher = $DB->get_record('user', array('email' => $teacheremail));

    // Verifica si se encontró el usuario.
    if (!$teacher) {
        throw new moodle_exception('invalidemail');
    }

    $emailuser = new stdClass();
    $emailuser->email = $teacheremail;

    //$userfrom = core_user::get_support_user();  // Utiliza el usuario de soporte de Moodle como remitente.
    $userfrom = core_user::get_noreply_user();
    $userto = $teacher;

    // Añade el correo del estudiante en CC.
    $extraheaders = array('Cc' => $USER->email);
    error_log('Correo del alumno: ' . $USER->email);

    // Envía el correo.
    $success = email_to_user($userto, $userfrom, $subject, $messagebody, $messagebody, '', '', $extraheaders);

    if ($success) {
        // Redirecciona al módulo page con un mensaje de éxito.
        redirect(new moodle_url('/mod/page/view.php', array('id' => $cmid)), get_string('emailsent', 'page'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // Maneja el error en el envío.
        throw new moodle_exception('emailsenderror', 'page');
    }
} else {
    // Si el formulario no se ha enviado o la sesión no es válida, redirige al usuario.
    throw new moodle_exception('invalidformdata');
}
