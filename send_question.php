<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

//$context = context_system::instance();
global $DB, $USER;

// Verifica que el usuario ha iniciado sesión y tiene permiso para enviar el correo.
require_login();
if (!isloggedin() || isguestuser()) {
    throw new moodle_exception('nopermissions', 'error', '', 'send email');
}

// Verifica que el formulario se haya enviado.
if (data_submitted() && confirm_sesskey()) {
    $cmid = required_param('id', PARAM_INT); // ID del módulo del curso.
    $teacheremail = required_param('teacheremail', PARAM_EMAIL); // Correo electrónico del profesor.
    $subject = required_param('subject', PARAM_TEXT); // Asunto del correo.
    $messagebody = required_param('messagebody', PARAM_RAW); // Cuerpo del mensaje.

    // Busca el objeto del usuario destinatario usando el correo electrónico proporcionado.
    $teacher = $DB->get_record('user', array('email' => $teacheremail));

    // Verifica si se encontró el usuario profesor.
    if (!$teacher) {
        print_error('invalidemail');
    }

    // Prepara el correo electrónico.
    $emailuser = new stdClass();
    $emailuser->email = $teacheremail; // El correo electrónico del profesor.
    //$emailuser->id = -99; // ID ficticio para el usuario de correo electrónico.

    $userfrom = core_user::get_support_user();  // Utiliza el usuario de soporte de Moodle como remitente.
    $userto = $teacher;

    // Añade el correo del estudiante en CC.
    $extraheaders = array('Cc' => $USER->email);

    // Envía el correo.
    $success = email_to_user($userto, $userfrom, $subject, $messagebody, $messagebody, '', '', $extraheaders);

    if ($success) {
        // Redirecciona al módulo page con un mensaje de éxito.
        redirect(new moodle_url('/mod/page/view.php', array('id' => $cmid)), get_string('emailsent', 'page'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // Maneja el error en el envío.
        print_error('emailsenderror', 'page');
    }
} else {
    // Si el formulario no se ha enviado o la sesión no es válida, redirige al usuario.
    print_error('invalidformdata');
}

/*
- Los puertos comunes son 25 para SMTP no cifrado, 465 para SMTPS (SMTP sobre SSL) y 587 para SMTP sobre STARTTLS.
- Para TLS, que es recomendado, usa el puerto 587.
- Para SSL, usa el puerto 465.








*/