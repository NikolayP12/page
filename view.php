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
 * Handles where and how the fields of the Page module are displayed
 *
 * @package     mod_page
 * @copyright   2009 Petr Skoda (http://skodak.org)
 * @copyright   2024 Nikolay <nikolaypn2002@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/mod/page/lib.php');
require_once($CFG->dirroot . '/mod/page/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

global $PAGE, $USER;
$PAGE->requires->css('/mod/page/page_style.css');

// Retrieve the Course Module ID and Page instance ID from the request.
$id      = optional_param('id', 0, PARAM_INT);
$p       = optional_param('p', 0, PARAM_INT);
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

// Load the page object or course module based on the provided ID.
if ($p) {
    if (!$page = $DB->get_record('page', array('id' => $p))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('page', $page->id, $page->course, false, MUST_EXIST);
} else {
    if (!$cm = get_coursemodule_from_id('page', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $page = $DB->get_record('page', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Ensure the user is logged in and has the necessary capabilities.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/page:view', $context);

// Handle view actions such as logging and triggering events.
page_view($page, $course, $cm, $context);

// Set up the page URL and layout
$PAGE->set_url('/mod/page/view.php', array('id' => $cm->id));

// Deserialize display options if set
$options = empty($page->displayoptions) ? [] : (array) unserialize_array($page->displayoptions);

$activityheader = ['hidecompletion' => false];
if (empty($options['printintro'])) {
    $activityheader['description'] = '';
}
// Configure the page layout based on display settings
if ($inpopup and $page->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname . ': ' . $page->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->add_body_class('limitedwidth');
    $PAGE->set_title($course->shortname . ': ' . $page->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($page);
    if (!$PAGE->activityheader->is_title_allowed()) {
        $activityheader['title'] = "";
    }
}

$PAGE->activityheader->set_attrs($activityheader);
// Output the page header.
echo $OUTPUT->header();

// Display the page content.
$conceptTitle = get_string('concepttitle', 'page');
echo $OUTPUT->heading($conceptTitle, 3);
$content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $context->id, 'mod_page', 'content', $page->revision);

$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$formatoptions->filter = false; // Disables the filters for the field 'content'.

$content = format_text($content, $page->contentformat, $formatoptions);
echo html_writer::div($content, 'box-style');

$formatoptions->filter = true; // Enables the filters for the rest of the fields.

// Display related concepts if available
if (!empty($page->relatedconcepts)) {
    $relatedconceptsTitle = get_string('relatedconceptstitle', 'page');
    echo $OUTPUT->heading($relatedconceptsTitle, 3, array('class' => 'space-between-style'));
    $relatedconceptscontent = file_rewrite_pluginfile_urls($page->relatedconcepts, 'pluginfile.php', $context->id, 'mod_page', 'relatedconcepts', $page->revision);
    $relatedconceptscontent = format_text($relatedconceptscontent, $page->relatedconceptsformat, $formatoptions);
    echo html_writer::div($relatedconceptscontent, 'box-style');
}

// Display learning path if available
if (!empty($page->learningpath)) {
    $learningPathTitle = get_string('learningpathtitle', 'page');
    echo $OUTPUT->heading($learningPathTitle, 3, array('class' => 'space-between-style'));
    $learningpathcontent = file_rewrite_pluginfile_urls($page->learningpath, 'pluginfile.php', $context->id, 'mod_page', 'learningpath', $page->revision);
    $learningpathcontent = format_text($learningpathcontent, $page->learningpathformat, $formatoptions);
    echo html_writer::div($learningpathcontent, 'box-style');
}

// Filter teachers' e-mails
$teacherRoles = ['editingteacher', 'teacher'];

// Prepare a comma-separated list of role shortnames for use in the SQL query.
$placeholders = implode(',', array_fill(0, count($teacherRoles), '?'));

// SQL query to get the e-mails of the teachers of the course.
$sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, 
        u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
        FROM {user} u
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {role} r ON ra.roleid = r.id
        WHERE ctx.contextlevel = ?
        AND ctx.instanceid = ?
        AND r.shortname IN ($placeholders)";

$params = array_merge([CONTEXT_COURSE, $course->id], $teacherRoles);
$teachers = $DB->get_records_sql($sql, $params);
// Convert the results into a comma-separated string of emails.
$teacherInfoString = '';
foreach ($teachers as $teacher) {
    $teacherFullName = fullname($teacher);
    $teacherInfoString .= "• {$teacherFullName}: {$teacher->email}<br>";
}

$formatoptions->filter = false; // Disables the filters for the form.

// Display the email form
echo $OUTPUT->heading(get_string('sendyourquestion', 'page'), 4, array('class' => 'space-between-style'));
echo '<script src="' . new moodle_url('/mod/page/javascript/accordion.js') . '"></script>';
echo '<script src="' . new moodle_url('/mod/page/javascript/form_storage.js') . '"></script>';

echo '<div class="accordion-container">';
echo '<h2 class="accordion-title">' . get_string('dropdownform', 'page') . '</h2>';
echo '<div class="accordion-content">';
echo '<form action="send_question.php" method="post" class="custom-question-form clearfix">';
echo '<input type="hidden" name="sesskey" value="' . s(sesskey()) . '"/>';
echo '<input type="hidden" name="id" value="' . $cm->id . '"/>';
echo '<input type="hidden" id="pageid" name="pageid" value="' . $id . '"/>';
echo '<input type="hidden" id="userid" name="userid" value="' . $USER->id . '"/>';
echo '<div>';
echo '<label for="teachersemails">' . get_string('availableteachers', 'page') . '</label>';
echo '<p class="teacheremails">' . $teacherInfoString . '</p>';
echo '</div>';
echo '<div>';
echo '<label for="teacheremail">' . get_string('teacheremail', 'page') . '</label>';
echo '<input type="email" id="teacheremail" name="teacheremail" required>';
echo '</div>';
echo '<div>';
echo '<label for="subject">' . get_string('subject', 'page') . '</label>';
echo '<input type="text" id="subject" name="subject" required>';
echo '</div>';
echo '<div>';
echo '<label for="messagebody">' . get_string('messagebody', 'page') . '</label>';
echo '<textarea id="messagebody" name="messagebody" rows="10" required></textarea>';
echo '</div>';
echo '<div class = "button-container">';
echo '<button type="submit">' . get_string('send', 'page') . '</button>';
echo '</div>';
echo '</form>';
echo '</div>'; // Closing .accordion-content
echo '</div>'; // Closing .accordion-container

// Display last modified date if required
if (!isset($options['printlastmodified']) || !empty($options['printlastmodified'])) {
    $strlastmodified = get_string("lastmodified");
    echo html_writer::div("$strlastmodified: " . userdate($page->timemodified), 'modified');
}
echo $OUTPUT->footer();
