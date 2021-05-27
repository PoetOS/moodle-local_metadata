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

namespace local_metadata\forms;

use moodleform;

defined('MOODLE_INTERNAL') || die();

/**
 * This file contains the Field Form used for profile fields.
 *
 * @package local_metadata
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_form extends moodleform {

    /** @var profile_define_base $field */
    public $field;

    /**
     * Define the form
     */
    public function definition () {
        $mform = $this->_form;

        // Everything else is dependant on the data type.
        $datatype = $this->_customdata['datatype'];
        $newfield = "\\metadatafieldtype_{$datatype}\\define";
        $this->field = new $newfield($this->_customdata['contextlevel']);

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'contextlevel');
        $mform->setType('contextlevel', PARAM_INT);
        $mform->addElement('hidden', 'action', 'editfield');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'datatype', $datatype);
        $mform->setType('datatype', PARAM_ALPHA);

        $this->field->define_form($mform);

        $this->add_action_buttons(true);
    }


    /**
     * Alter definition based on existing or submitted data
     */
    public function definition_after_data () {
        $mform = $this->_form;
        $this->field->define_after_data($mform);
    }


    /**
     * Perform some moodle validation.
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        return $this->field->define_validate($data, $files);
    }

    /**
     * Returns the defined editors for the field.
     * @return mixed
     */
    public function editors() {
        return $this->field->define_editors();
    }
}


