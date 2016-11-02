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
 * Renderer base class.
 *
 * @package local_metadata
 * @copyright  2016 The POET Group
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\output;

defined('MOODLE_INTERNAL') || die;

class renderer extends \plugin_renderer_base {

    public function render_category_table(category_table $categorytable) {
        global $DB;

        $output = '';
        foreach ($categorytable->categories as $category) {
            $table = new \html_table();
            $table->head  = [get_string('profilefield', 'admin'), get_string('edit')];
            $table->align = ['left', 'right'];
            $table->width = '95%';
            $table->attributes['class'] = 'generaltable profilefield';
            $table->data = [];

            if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {
                foreach ($fields as $field) {
                    $table->data[] = [format_string($field->name), $this->field_icons($field, $category->contextlevel)];
                }
            }

            $displayname = new categoryname($category);
            $output .= $this->heading($displayname->render($this) .' '.$this->category_icons($category), 4);
            if (count($table->data)) {
                $output .= \html_writer::table($table);
            } else {
                $output .= $this->notification($strnofields);
            }
        } // End of $categories foreach.

        return $output;
    }

    /**
     * Create a string containing the editing icons for the user profile fields
     * @param stdClass $field the field object
     * @return string the icon string
     */
    private function field_icons($field, $contextlevel) {
        global $DB;

        $fieldcount = $DB->count_records('local_metadata_field', ['categoryid' => $field->categoryid]);

        $url = new \moodle_url('/local/metadata/index.php',
            ['id' => $field->id, 'contextlevel' => $contextlevel, 'sesskey' => sesskey()]);
        $editstr = '';

        // Edit.
        $url->param('action', 'editfield');
        $editstr .= $this->action_icon($url, new \pix_icon('t/edit', get_string('edit')), null,
            array('class' => 'action-icon action_edit'));

        // Delete.
        $url->param('action', 'deletefield');
        $editstr .= $this->action_icon($url, new \pix_icon('t/delete', get_string('delete')), null,
            array('class' => 'action-icon action_delete'));

        // Move up.
        if ($field->sortorder > 1) {
            $url->param('action', 'movefield');
            $url->param('dir', 'up');
            $editstr .= $this->action_icon($url, new \pix_icon('t/up', get_string('moveup')), null,
                array('class' => 'action-icon action_moveup'));
        } else {
            $editstr .= $this->action_icon(null, new \pix_icon('spacer', ''), null,
                array('class' => 'action-icon action_spacer'));
        }

        // Move down.
        if ($field->sortorder < $fieldcount) {
            $url->param('action', 'movefield');
            $url->param('dir', 'down');
            $editstr .= $this->action_icon($url, new \pix_icon('t/down', get_string('movedown')), null,
                array('class' => 'action-icon action_movedown'));
        } else {
            $editstr .= $this->action_icon(null, new \pix_icon('spacer', ''), null,
                array('class' => 'action-icon action_spacer'));
        }

        return $editstr;
    }

    /**
     * Create a string containing the editing icons for the user profile categories
     * @param stdClass $category the category object
     * @return string the icon string
     */
    private function category_icons($category) {
        global $DB;

        $categorycount = $DB->count_records('local_metadata_category', ['contextlevel' => $category->contextlevel]);
        $fieldcount    = $DB->count_records('local_metadata_field', ['categoryid' => $category->id]);

        $url = new \moodle_url('/local/metadata/index.php',
            ['id' => $category->id, 'contextlevel' => $category->contextlevel, 'sesskey' => sesskey()]);
        $editstr = '';

        // Delete.
        // Can only delete the last category if there are no fields in it.
        if (($categorycount > 1) || ($fieldcount == 0)) {
            $url->param('action', 'deletecategory');
            $editstr .= $this->action_icon($url, new \pix_icon('t/delete', get_string('delete')), null,
                array('class' => 'action-icon action_delete'));
        }

        // Move up.
        if ($category->sortorder > 1) {
            $url->param('action', 'movecategory');
            $url->param('dir', 'up');
            $editstr .= $this->action_icon($url, new \pix_icon('t/up', get_string('moveup')), null,
                array('class' => 'action-icon action_moveup'));
        }

        // Move down.
        if ($category->sortorder < $categorycount) {
            $url->param('action', 'movecategory');
            $url->param('dir', 'down');
            $editstr .= $this->action_icon($url, new \pix_icon('t/down', get_string('movedown')), null,
                array('class' => 'action-icon action_movedown'));
        }

        return $editstr;
    }
}