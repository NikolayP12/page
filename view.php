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
 * Page module version information
 *
 * @package mod_page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/mod/page/lib.php');
require_once($CFG->dirroot . '/mod/page/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

global $PAGE;
//$PAGE->requires->css(new moodle_url('/mod/page/page_style.css'));
$PAGE->requires->css('/mod/page/page_style.css');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

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

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/page:view', $context);

// Completion and trigger events.
page_view($page, $course, $cm, $context);

$PAGE->set_url('/mod/page/view.php', array('id' => $cm->id));

$options = empty($page->displayoptions) ? [] : (array) unserialize_array($page->displayoptions);

$activityheader = ['hidecompletion' => false];
if (empty($options['printintro'])) {
    $activityheader['description'] = '';
}

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
echo $OUTPUT->header();
$conceptTitle = get_string('concepttitle', 'page');
echo $OUTPUT->heading($conceptTitle, 3);
$content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $context->id, 'mod_page', 'content', $page->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $page->contentformat, $formatoptions);
echo html_writer::div($content, 'box-style');
//echo $OUTPUT->box($content, "generalbox center clearfix");

// Inserción del código para mostrar los conceptos relacionados
if (!empty($page->relatedconcepts)) {
    $relatedconceptsTitle = get_string('relatedconceptstitle', 'page');
    echo $OUTPUT->heading($relatedconceptsTitle, 3, array('class' => 'space-between-style'));


    $relatedconceptscontent = file_rewrite_pluginfile_urls($page->relatedconcepts, 'pluginfile.php', $context->id, 'mod_page', 'relatedconcepts', $page->revision);
    $relatedconceptscontent = format_text($relatedconceptscontent, $page->relatedconceptsformat, $formatoptions);
    // Contenedor con clase específica para el estilo
    echo html_writer::div($relatedconceptscontent, 'box-style');
}

// Inserción del código para mostrar el contenido de "Ruta de aprendizaje"
if (!empty($page->learningpath)) {
    $learningPathTitle = get_string('learningpathtitle', 'page');
    echo $OUTPUT->heading($learningPathTitle, 3, array('class' => 'space-between-style'));


    $learningpathcontent = file_rewrite_pluginfile_urls($page->learningpath, 'pluginfile.php', $context->id, 'mod_page', 'learningpath', $page->revision);
    $learningpathcontent = format_text($learningpathcontent, $page->learningpathformat, $formatoptions);
    // Contenedor con clase específica para el estilo
    echo html_writer::div($learningpathcontent, 'box-style');
}

// Filtra los correos electrónicos de los profesores
$teacherRoles = ['editingteacher', 'teacher'];

// Prepara una lista separada por comas de los 'shortname' de roles, para usar en la consulta SQL.
$placeholders = implode(',', array_fill(0, count($teacherRoles), '?'));

// Consulta SQL para obtener los correos electrónicos de los profesores en el curso.
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

// Convierte los resultados en una cadena de correos electrónicos separada por comas.
$teacherInfoString = '';
foreach ($teachers as $teacher) {
    $teacherFullName = fullname($teacher); // Utiliza la función fullname() de Moodle para formatear el nombre completo
    $teacherInfoString .= "• {$teacherFullName}: {$teacher->email}<br>";
}

// Añade un título para el formulario
echo $OUTPUT->heading(get_string('sendyourquestion', 'page'), 4, array('class' => 'space-between-style'));
// Empieza el formulario
// Asegúrate de que el script se cargue correctamente. Esta línea puede ir al final del archivo PHP o donde mejor consideres según tu estructura.
echo '<script src="' . new moodle_url('/mod/page/accordion.js') . '"></script>';

// Comienza el contenedor del acordeón
echo '<div class="accordion-container">';

// Este es el título del acordeón, el cual los usuarios pueden clickear para expandir o colapsar el contenido
echo '<h2 class="accordion-title">' . get_string('dropdownform', 'page') . '</h2>';

// Este es el contenido del acordeón, el cual se mostrará/ocultará cuando se haga clic en el título del acordeón
echo '<div class="accordion-content">';

// Aquí empieza tu formulario
echo '<form action="send_question.php" method="post" class="custom-question-form">';
echo '<input type="hidden" name="sesskey" value="' . s(sesskey()) . '"/>'; // Añade el sesskey al formulario para seguridad
echo '<input type="hidden" name="id" value="' . $cm->id . '"/>';
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
echo '<button type="submit">' . get_string('send', 'page') . '</button>';
echo '</form>';

// Cierra el contenido y el contenedor del acordeón
echo '</div>'; // Cierre de .accordion-content
echo '</div>'; // Cierre de .accordion-container


if (!isset($options['printlastmodified']) || !empty($options['printlastmodified'])) {
    $strlastmodified = get_string("lastmodified");
    echo html_writer::div("$strlastmodified: " . userdate($page->timemodified), 'modified');
}

echo $OUTPUT->footer();
