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
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

/**
 * Module metadata context handler class..
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatacontext_module;

defined('MOODLE_INTERNAL') || die;

class context_handler extends \local_metadata\context\context_handler {

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     * @param int $contextname The name of this context (must be static - no language string).
     */
    public function __construct($instanceid = null, $contextlevel = null, $contextname = '') {
        return parent::__construct($instanceid, CONTEXT_MODULE, 'module');
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    public function get_instance() {
        global $DB;
        if (empty($this->instance)) {
            if (!empty($this->instanceid)) {
                $cmsql = 'SELECT cm.*, m.name ' .
                         'FROM {course_modules} cm ' .
                         'INNER JOIN {modules} m ON cm.module = m.id ' .
                         'WHERE cm.id = ?';
                if (!($this->instance = $DB->get_record_sql($cmsql, ['id' => $this->instanceid], MUST_EXIST))) {
                    print_error('invalidcoursemodule');
                }
            } else {
                $this->instance = new \stdClass();
                $this->instance->id = 0;
            }
        }
        return $this->instance;
    }

    /**
     * Return a Moodle page layout.
     * @return string The layout name.
     */
    public function get_layout() {
        return 'incourse';
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle context.
     */
    public function get_context() {
        if (empty($this->context)) {
            if (!empty($this->instanceid)) {
                $this->context = \context_module::instance($this->instanceid);
            } else {
                $this->context = false;
            }
        }
        return $this->context;
    }

    /**
     * Return the instance id of the currently accessed context. Used by page displays (filter). Must be handled by the implementing
     * class.
     * @return int|boolean Instance id or false if not determined.
     */
    public function get_instanceid_from_currentcontext() {
        global $PAGE;
        if (empty($this->instanceid)) {
            if (isset($PAGE->cm->id)) {
                $this->instanceid = $PAGE->cm->id;
            } else {
                $this->instanceid = false;
            }
        }
        return $this->instanceid;
    }

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        return new \moodle_url('/mod/'.$this->instance->name.'/view.php', ['id' => $this->instanceid]);
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    public function require_access() {
        require_login($this->instance->course, true, $this->instance);
        require_capability('moodle/course:manageactivities', $this->context);
        return true;
    }

    /**
     * Implement if specific context settings can be added to a context settings page (e.g. user preferences).
     */
    public function add_settings_to_context_menu($navmenu) {
        // Add the settings page to the activity modules settings menu, if enabled.
        $navmenu->add('modsettings',
            new \admin_externalpage('metadatacontext_modules', get_string('metadatatitle', 'metadatacontext_module'),
                new \moodle_url('/local/metadata/index.php', ['contextlevel' => $this->contextlevel]), ['moodle/site:config']),
            'managemodulescommon');
        return true;
    }

    /**
     * Hook function that is called when settings blocks are being built.
     */
    public function extend_settings_navigation($settingsnav, $context) {
        global $PAGE;

        if ($context->contextlevel == $this->contextlevel) {
            // Only add this settings item on non-site course pages.
            if ($PAGE->course && ($PAGE->course->id != 1) &&
                (get_config('metadatacontext_module', 'metadataenabled') == 1) &&
                has_capability('moodle/course:manageactivities', $context)) {

                if ($settingnode = $settingsnav->find('modulesettings', \settings_navigation::TYPE_SETTING)) {
                    $strmetadata = get_string('metadatatitle', 'metadatacontext_module');
                    $url = new \moodle_url('/local/metadata/index.php',
                        ['id' => $context->instanceid, 'action' => 'moduledata', 'contextlevel' => $this->contextlevel]);
                    $metadatanode = \navigation_node::create(
                        $strmetadata,
                        $url,
                        \navigation_node::NODETYPE_LEAF,
                        'metadata',
                        'metadata',
                        new \pix_icon('i/settings', $strmetadata)
                    );
                    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                        $metadatanode->make_active();
                    }
                    $settingnode->add_node($metadatanode);
                }
            }
        }
    }

    /**
     * Hook function called when a module form is loaded and add metadata form elements for the module
     */
    public function coursemodule_standard_elements($formwrapper, $mform) {
        global $DB;

        // The instance id of the module is stored in $formwrapper.
        if ($cm = $formwrapper->get_coursemodule()) {
            $this->instanceid = $cm->id;
        } else {
            $this->instanceid = 0;
        }
        $dataoutput = new output\manage_data($this->get_instance(), $this->contextlevel);

        foreach ($dataoutput->data as $categoryid => $fieldgroup) {
            foreach ($fieldgroup as $groupid => $fielddata) {
                if ($groupid === 'categoryname') {
                    $mform->addElement('header', 'local_metadata_category_' . $categoryid, $fielddata);
                } else {
                    $fielddata->edit_field_add($mform);
                }
            }
        }
        local_metadata_load_data($this->get_instance(), $this->contextlevel);
        $formwrapper->set_data($this->instance);
    }

    /**
     * Hook function called when a module form is saved and insert/update metadata in the database for the module
     */
    public function coursemodule_edit_post_actions($data, $course) {
        global $DB;

        if ($fields = $DB->get_records('local_metadata_field', ['contextlevel' => $this->contextlevel])) {
            foreach ($fields as $field) {
                // The id received in $data is not the one from course module but from the related module (url, label, etc...)
                // instead. To get the right id and pass it to $formfield so it's saved, a new object is created with the course
                // module id used by the metadata.
                $newfield = "\\metadatafieldtype_{$field->datatype}\\metadata";
                $datachunk = new \stdClass();
                $datachunk->id = $data->coursemodule;
                $datachunk->{'local_metadata_field_' . $field->shortname} = $data->{'local_metadata_field_' . $field->shortname};
                $formfield = new $newfield($field->id, $datachunk->id);
                $formfield->edit_save_data($datachunk);
            }
        }
        return $data;
    }
}