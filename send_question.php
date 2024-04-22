<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/Exception.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/PHPMailer.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/SMTP.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $DB, $USER, $PAGE, $COURSE;

// Verifies that the user is logged in and has permission to send mail.
require_login();
$cmid = required_param('id', PARAM_INT);

if (!isloggedin() || isguestuser()) {
    $url = new moodle_url('/mod/page/view.php', array('id' => $cmid));
    redirect($url, get_string('nopermissions', 'page'), null, \core\output\notification::NOTIFY_ERROR);
}

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
    $htmlMessageBody = nl2br($messagebody); // Convert line breaks to <br> for HTML.

    // Obtain the user based on the email.
    $user = $DB->get_record('user', array('email' => $teacheremail));

    if ($user) {
        $isTeacher = $DB->get_record_sql(
            'SELECT * FROM {role_assignments} AS ra
                                      JOIN {context} AS c ON ra.contextid = c.id
                                      JOIN {role} AS r ON ra.roleid = r.id
                                      WHERE r.shortname IN (?, ?)
                                      AND c.contextlevel = ?
                                      AND ra.userid = ?',
            array('editingteacher', 'teacher', CONTEXT_COURSE, $user->id)
        );

        if (empty($isTeacher) || !isset($isTeacher->roleid)) {
            // The user is not a teacher.
            $url = new moodle_url('/mod/page/view.php', array('id' => $cmid));
            redirect($url, get_string('teacheremailnotvalid', 'page'), null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        // The user was not found with the email address provided.
        $url = new moodle_url('/mod/page/view.php', array('id' => $cmid));
        redirect($url, get_string('teacheremailnotvalid', 'page'), null, \core\output\notification::NOTIFY_ERROR);
    }

    $mail = new PHPMailer(true);

    try {
        // Separates SMTP host and port
        list($smtphost, $smtpport) = explode(':', $CFG->smtphosts . ':'); // Add ':' at the end to ensure that explode always returns an array of at least 2 elements.
        $smtpport = $smtpport ?: 587; // Uses port 587 as default if not specified.

        // Server configuration.
        $mail->isSMTP();
        $mail->Host = $smtphost; // Host SMTP.
        $mail->SMTPAuth = true;
        $mail->Username = $CFG->smtpuser;
        $mail->Password = $CFG->smtppass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtpport; // Port SMTP.

        // Configures UTF-8 encoding.
        $mail->CharSet = 'UTF-8';

        // Senders and addressees.
        $mail->setFrom($CFG->noreplyaddress, 'Page Mail Sender');
        $mail->addAddress($teacheremail); // Add the teacher.
        $mail->addCC($USER->email); // Add the student in CC.

        // Contents
        $mail->isHTML(true); // Set email format to HTML.
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessageBody;
        $mail->AltBody = strip_tags($messagebody);
        $mail->send();
        redirect(new moodle_url('/mod/page/view.php', array('id' => $cmid)), get_string('emailsent', 'page'), null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (Exception $e) {
        $url = new moodle_url('/mod/page/view.php', array('id' => $cmid));
        redirect($url, get_string('emailsenderror', 'page'), null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    $url = new moodle_url('/mod/page/view.php', array('id' => $cmid));
    redirect($url, get_string('invalidformdata', 'page'), null, \core\output\notification::NOTIFY_ERROR);
}
