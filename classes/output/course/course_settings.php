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
 * Course settings renderable.
 *
 * @package local_metadata
 * @copyright  2016 The POET Group
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\output\course;

defined('MOODLE_INTERNAL') || die;

class course_settings implements \renderable {

    public $course;
    public $data;

    public function __construct($course = null) {
        global $COURSE, $DB;

        $this->course = ($course === null) ? $COURSE : $course;

        $this->data = [];
        // If user is "admin" fields are displayed regardless.
        $update = has_capability('moodle/course:create', \context_course::instance($this->course->id));

        if ($categories = $DB->get_records('local_metadata_category', ['contextlevel' => CONTEXT_COURSE], 'sortorder ASC')) {
            foreach ($categories as $category) {
                if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {

                    // Check first if *any* fields will be displayed.
                    $display = false;
                    foreach ($fields as $field) {
                        if ($field->visible != PROFILE_VISIBLE_NONE) {
                            $display = true;
                        }
                    }

                    // Display the header and the fields.
                    if ($display || $update) {
                        $this->data[$category->id]['categoryname'] = format_string($category->name);
                        foreach ($fields as $field) {
                            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
                            $this->data[$category->id][$field->id] = new $newfield($field->id, $this->course->id);
                        }
                    }
                }
            }
        }
    }
}