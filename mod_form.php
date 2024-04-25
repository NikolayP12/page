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
 * Handles the page mod_form, where the page is configured.
 *
 * @package     mod_page
 * @copyright   2009 Petr Skoda (http://skodak.org)
 * @copyright   2024 Nikolay <nikolaypn2002@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/page/locallib.php');
require_once($CFG->libdir . '/filelib.php');

global $COURSE;
class mod_page_mod_form extends moodleform_mod
{

    function definition()
    {
        global $CFG, $DB;
        global $PAGE;
        $PAGE->requires->js(new moodle_url('/mod/page/dropdown.js'));
        $PAGE->requires->css('/mod/page/page_style.css');

        $mform = $this->_form;

        $config = get_config('page');

        //-------------------------------------------------------
        // Hiddden element that offers the course ID.
        $mform->addElement('hidden', 'courseid', $this->current->course, '<div id="courseid"></div>');
        $mform->setType('courseid', PARAM_INT);

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'page'));
        $mform->addElement('editor', 'page', get_string('content', 'page'), null, page_get_editor_options($this->context));
        $mform->addRule('page', get_string('required'), 'required', null, 'client');

        //-------------------------------------------------------

        // Field for related concepts.
        $mform->addElement('header', 'relatedconceptssection', get_string('relatedconceptsheader', 'page'));
        $mform->addElement('editor', 'relatedconcepts_editor', get_string('relatedconcepts', 'page'), null, page_get_editor_options($this->context));
        $mform->addHelpButton('relatedconcepts_editor', 'conceptshelp', 'page');

        //-------------------------------------------------------

        // Header to specify the learning path section.
        $mform->addElement('header', 'learningpathsection', get_string('learningpathheader', 'page'));

        // The drop-down list of module types is added.
        $module_types = core_component::get_plugin_list('mod');
        $module_types_options = ['' => get_string('selecttype', 'page')]; // Initial option.
        foreach ($module_types as $module_type => $notused) {
            // The module name is used as the label.
            $module_types_options[$module_type] = get_string('pluginname', 'mod_' . $module_type);
        }

        // The drop-down is added to the form.
        $mform->addElement('select', 'moduletype', get_string('moduletype', 'page'), $module_types_options);
        $mform->setType('moduletype', PARAM_ALPHANUMEXT);
        $mform->setDefault('moduletype', '');
        $mform->addHelpButton('moduletype', 'moduletypehelp', 'page');

        // The dropdown for the module instances is added.
        $mform->addElement('select', 'moduleinstance', get_string('selectmodule', 'page'), []);
        $mform->setType('moduleinstance', PARAM_INT);
        $mform->disabledIf('moduleinstance', 'moduletype', 'eq', '');

        // The container for the selected modules is added.
        $mform->addElement('static', 'selectedmodules', get_string('selectedmodules', 'page'), '<div id="selected-modules-container"></div>');

        // The editor is added to be able to write the learning path.
        $mform->addElement('editor', 'learningpath_editor', get_string('learningpath', 'page'), null, page_get_editor_options($this->context));

        //-------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'page'), $options);
            $mform->setDefault('display', $config->display);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'page'), array('size' => 3));
            if (count($options) > 1) {
                $mform->hideIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'page'), array('size' => 3));
            if (count($options) > 1) {
                $mform->hideIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
        }

        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'page'));
        $mform->setDefault('printintro', $config->printintro);
        $mform->addElement('advcheckbox', 'printlastmodified', get_string('printlastmodified', 'page'));
        $mform->setDefault('printlastmodified', $config->printlastmodified);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(
                RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'page'),
                RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'page')
            );
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'page'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues)
    {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $defaultvalues['page']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['page']['text']   = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_page',
                'content',
                0,
                page_get_editor_options($this->context),
                $defaultvalues['content']
            );
            $defaultvalues['page']['itemid'] = $draftitemid;

            // Preprocessing for 'learningpath' 
            $draftitemidLearningPath = file_get_submitted_draft_itemid('learningpath_editor');
            $defaultvalues['learningpath_editor']['format'] = $defaultvalues['learningpathformat'];
            $defaultvalues['learningpath_editor']['text'] = file_prepare_draft_area(
                $draftitemidLearningPath,
                $this->context->id,
                'mod_page',
                'learningpath',
                0,
                page_get_editor_options($this->context),
                $defaultvalues['learningpath']
            );
            $defaultvalues['learningpath_editor']['itemid'] = $draftitemidLearningPath;

            // Preprocessing for 'relatedconcepts' 
            $draftitemidRelatedConcepts = file_get_submitted_draft_itemid('relatedconcepts_editor');
            $defaultvalues['relatedconcepts_editor']['format'] = $defaultvalues['relatedconceptsformat'];
            $defaultvalues['relatedconcepts_editor']['text'] = file_prepare_draft_area(
                $draftitemidRelatedConcepts,
                $this->context->id,
                'mod_page',
                'relatedconcepts',
                0,
                page_get_editor_options($this->context),
                $defaultvalues['relatedconcepts']
            );

            $defaultvalues['relatedconcepts_editor']['itemid'] = $draftitemidRelatedConcepts;
        }


        if (!empty($defaultvalues['displayoptions'])) {
            $displayoptions = (array) unserialize_array($defaultvalues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultvalues['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printlastmodified'])) {
                $defaultvalues['printlastmodified'] = $displayoptions['printlastmodified'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $defaultvalues['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $defaultvalues['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}
