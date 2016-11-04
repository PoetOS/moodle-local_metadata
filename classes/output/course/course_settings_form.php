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
 * @author Mike Churchward <mike.churchward@poetgroup.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2016 The POET Group
 */

/**
 * Course settings form.
 *
 * @package local_metadata
 * @copyright  2016 The POET Group
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\output\course;

require_once($CFG->libdir . '/formslib.php');

use moodleform;

defined('MOODLE_INTERNAL') || die();

class course_settings_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition () {
        global $USER, $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'action', 'coursesettings');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'contextlevel', CONTEXT_COURSE);
        $mform->setType('contextlevel', PARAM_INT);
        $mform->addElement('hidden', 'id', $this->_customdata->course->id);
        $mform->setType('id', PARAM_INT);

        $data = $this->_customdata->data;
        foreach ($data as $catid => $category) {
            foreach ($category as $index => $item) {
                if ($index == 'categoryname') {
                    $mform->addElement('header', 'category_'.$catid, $category['categoryname']);
                } else {
                    $item->edit_field($mform);
                }
            }
        }
        $this->add_action_buttons(true);

        // Finally set the current form data
        $this->set_data($this->_customdata->course);
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $data  = (object)$data;

        return $errors;
    }

    public function add_element($arg1, $arg2=null, $arg3=null, $arg4=null) {
        $this->_form->addElement($arg1, $arg2, $arg3, $arg4);
    }

    public function __get($name) {
        if ($name == 'mform') {
            return $this->_form;
        }
    }
}