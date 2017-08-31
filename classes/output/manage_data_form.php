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
 * Metadata management form.
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;

class manage_data_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('hidden', 'action', $this->_customdata->action);
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'contextlevel', $this->_customdata->contextlevel);
        $mform->setType('contextlevel', PARAM_INT);
        $mform->addElement('hidden', 'id', $this->_customdata->instance->id);
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

        // Finally set the current form data.
        $this->set_data($this->_customdata->instance);
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
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