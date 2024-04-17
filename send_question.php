<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/Exception.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/PHPMailer.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/SMTP.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $DB, $USER, $PAGE, $COURSE;

// Verifica que el usuario ha iniciado sesión y tiene permiso para enviar el correo.
require_login();

if (!isloggedin() || isguestuser()) {
    throw new moodle_exception('nopermissions', 'error', '', 'send email');
}

$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('page', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$context = context_module::instance($cmid);

$PAGE->set_context($context);
$PAGE->set_url('/mod/page/send_question.php', array('id' => $cmid));
$PAGE->set_course($course);
$PAGE->set_cm($cm);

if (data_submitted() && confirm_sesskey()) {
    $teacheremail = required_param('teacheremail', PARAM_EMAIL);
    $subject = required_param('subject', PARAM_TEXT);
    $messagebody = required_param('messagebody', PARAM_RAW);

    $teacher = $DB->get_record('user', array('email' => $teacheremail));
    if (!$teacher) {
        throw new moodle_exception('invalidemail');
    }

    $mail = new PHPMailer(true);

    try {
        // Separar el host SMTP y el puerto
        list($smtphost, $smtpport) = explode(':', $CFG->smtphosts . ':'); // Añade ':' al final para asegurar que explode siempre devuelva un array de al menos 2 elementos
        $smtpport = $smtpport ?: 587; // Usar el puerto 587 como predeterminado si no se especifica

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = $smtphost; // Host SMTP
        $mail->SMTPAuth = true;
        $mail->Username = $CFG->smtpuser;
        $mail->Password = $CFG->smtppass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtpport; // Puerto SMTP

        // Remitentes y destinatarios
        $mail->setFrom($CFG->noreplyaddress, 'From');
        $mail->addAddress($teacheremail); // Añade al profesor
        $mail->addCC($USER->email); // Añade al alumno en CC

        // Contenido
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $messagebody;
        $mail->AltBody = strip_tags($messagebody);

        $mail->send();
        redirect(new moodle_url('/mod/page/view.php', array('id' => $cmid)), get_string('emailsent', 'page'), null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (Exception $e) {
        throw new moodle_exception('emailsenderror', 'page');
    }
} else {
    throw new moodle_exception('invalidformdata');
}
