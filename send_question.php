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
 * Handles sending emails from students to specific teachers.
 *
 * @package    mod_page
 * @copyright  2024 Nikolay <nikolaypn2002@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/Exception.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/PHPMailer.php');
require_once($CFG->dirroot . '/lib/phpmailer/src/SMTP.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Global variables are declared to bring context, user, and DB settings into scope.
global $DB, $USER, $PAGE, $COURSE;

// Ensures the user is logged in and has the necessary permissions to send an email.
require_login();
$cmid = required_param('id', PARAM_INT);

if (!isloggedin() || isguestuser()) {
    $url = new moodle_url('/mod/page/view.php', array('id' => $cmid, 'emailSent' => 'false'));
    redirect($url, get_string('nopermissions', 'page'), null, \core\output\notification::NOTIFY_ERROR);
}

// Retrieves the course module from the ID, checks it exists.
$cm = get_coursemodule_from_id('page', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$page = $DB->get_record('page', array('id' => $cm->instance), '*', MUST_EXIST);
$context = context_module::instance($cmid);

// Sets up the page context and URL for proper navigation and display.
$PAGE->set_context($context);
$PAGE->set_url('/mod/page/send_question.php', array('id' => $cmid));
$PAGE->set_course($course);
$PAGE->set_cm($cm);

// Check if data is submitted and the session key is confirmed to prevent CSRF attacks.
if (data_submitted() && confirm_sesskey()) {
    $teacheremail = required_param('teacheremail', PARAM_EMAIL);
    $subject = required_param('subject', PARAM_TEXT);
    $messagebody = required_param('messagebody', PARAM_RAW);
    $pageName = format_string($page->name);
    $headerMessage = get_string('emailfrompage', 'page');
    $introMessage = "<strong>" . nl2br($headerMessage) . "</strong>" .  nl2br($pageName);

    // Constructs the full HTML message body.
    $htmlMessageBody = "<em>" . nl2br($introMessage) . "</em><br><br>" . nl2br($messagebody);

    // Attempts to retrieve user details based on the provided email address.   
    $user = $DB->get_record('user', array('email' => $teacheremail));

    // Validates if the targeted email belongs to a teacher of the course.
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
            // Redirect with an error message if the user is not a teacher.
            $url = new moodle_url('/mod/page/view.php', array('id' => $cmid, 'emailSent' => 'false'));
            redirect($url, get_string('teacheremailnotvalid', 'page'), null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        // Redirect with an error message if no user with the provided email was found.
        $url = new moodle_url('/mod/page/view.php', array('id' => $cmid, 'emailSent' => 'false'));
        redirect($url, get_string('teacheremailnotvalid', 'page'), null, \core\output\notification::NOTIFY_ERROR);
    }

    $mail = new PHPMailer(true);

    try {
        // Setup SMTP server settings.
        list($smtphost, $smtpport) = explode(':', $CFG->smtphosts . ':'); // Add ':' at the end to ensure that explode always returns an array of at least 2 elements.
        $smtpport = $smtpport ?: 587; // Uses port 587 as default if not specified.

        // Configuring PHPMailer with Moodle's SMTP settings.
        $mail->isSMTP();
        $mail->Host = $smtphost; // Host SMTP.
        $mail->SMTPAuth = true;
        $mail->Username = $CFG->smtpuser;
        $mail->Password = $CFG->smtppass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtpport; // Port SMTP.
        // Sets UTF-8 character encoding for emails.
        $mail->CharSet = 'UTF-8';

        // Senders and addressees.
        $mail->setFrom($CFG->noreplyaddress, 'Page Mail Sender');
        $mail->addAddress($teacheremail); // Add the teacher.
        $mail->addCC($USER->email); // Add the student in CC.

        // Configures the email content, subject and body.
        $mail->isHTML(true); // Set email format to HTML.
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessageBody;
        $mail->AltBody = strip_tags($messagebody);

        // Sends the email and redirects to the view page with a success notification.
        $mail->send();
        redirect(new moodle_url('/mod/page/view.php', array('id' => $cmid, 'emailSent' => 'true')), get_string('emailsent', 'page'), null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (Exception $e) {
        // On mail error, redirect back to the view page with an error notification.
        $url = new moodle_url('/mod/page/view.php', array('id' => $cmid, 'emailSent' => 'false'));
        redirect($url, get_string('emailsenderror', 'page'), null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    // Redirect with a notification for invalid form data.
    $url = new moodle_url('/mod/page/view.php', array('id' => $cmid, 'emailSent' => 'false'));
    redirect($url, get_string('invalidformdata', 'page'), null, \core\output\notification::NOTIFY_ERROR);
}
