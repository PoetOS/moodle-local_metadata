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

namespace local_metadata\output;

defined('MOODLE_INTERNAL') || die;

/**
 * General metadata management renderable.
 *
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_data implements \renderable {

    /** @var mixed|null  */
    public $instance;

    /** @var mixed|null  */
    public $contextlevel;

    /** @var mixed|null  */
    public $action;

    /** @var array  */
    public $data;

    /** @var object */
    public $form;

    /** @var bool  */
    public $saved = false;

    /**
     * manage_data constructor.
     * @param null $instance
     * @param null $contextlevel
     * @param null $action
     * @throws \dml_exception
     */
    public function __construct($instance = null, $contextlevel = null, $action = null) {
        global $DB;

        $this->instance = $instance;
        $this->contextlevel = $contextlevel;
        $this->action = $action;
        $this->data = [];

        if ($categories = $DB->get_records('local_metadata_category', ['contextlevel' => $this->contextlevel], 'sortorder ASC')) {
            foreach ($categories as $category) {
                if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {
                    // Display the header and the fields.
                    $this->data[$category->id]['categoryname'] = format_string($category->name);
                    foreach ($fields as $field) {
                        $newfield = "\\metadatafieldtype_{$field->datatype}\\metadata";
                        $this->data[$category->id][$field->id] = new $newfield($field->id, $this->instance->id);
                    }
                }
            }
        }
    }

    /**
     * Function to add a form to render within.
     *
     * @param \moodleform $form A moodleform object or child.
     */
    public function add_form($form) {
        $this->form = $form;
    }

    /**
     * Function to add a form to render within.
     *
     * @param bool $state
     */
    public function set_saved($state = true) {
        $this->saved = $state;
    }
}