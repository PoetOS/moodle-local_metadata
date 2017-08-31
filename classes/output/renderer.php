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
 * Renderer base class.
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\output;

defined('MOODLE_INTERNAL') || die;

class renderer extends \plugin_renderer_base {

    /**
     * Category table renderer.
     *
     * @param category_table $categorytable renderable object.
     */
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
                $output .= $this->notification(get_string('profilenofieldsdefined', 'admin'));
            }
        } // End of $categories foreach.

        return $output;
    }

    /**
     * Data creation renderer.
     *
     * @param data_creation $datacreation renderable object.
     */
    public function render_data_creation(data_creation $datacreation) {
        $output = '';
        $output .= \html_writer::empty_tag('hr');
        $output .= \html_writer::start_tag('div', ['class' => 'profileditor']);

        // Create a new field link.
        $options = local_metadata_list_datatypes();
        $popupurl = new \moodle_url('/local/metadata/index.php',
            ['id' => 0, 'action' => 'editfield', 'contextlevel' => $datacreation->contextlevel]);
        $output .= $this->single_select($popupurl, 'datatype', $options, '',
            ['' => get_string('choosedots')], 'newfieldform', ['label' => get_string('profilecreatefield', 'admin')]);

        // Add a div with a class so themers can hide, style or reposition the text.
        $output .= \html_writer::start_tag('div', ['class' => 'adminuseractionhint']);
        $output .= get_string('or', 'lesson');
        $output .= \html_writer::end_tag('div');

        // Create a new category link.
        $options = ['action' => 'editcategory', 'contextlevel' => $datacreation->contextlevel];
        $output .= $this->single_button(new \moodle_url('/local/metadata/index.php', $options),
            get_string('profilecreatecategory', 'admin'));

        $output .= \html_writer::end_tag('div');

        return $output;
    }

    /**
     * Create a string containing the editing icons for the user profile fields
     * @param stdClass $field the field object
     * @return string the icon string
     */
    protected function field_icons($field, $contextlevel) {
        global $DB;

        $output = '';

        $fieldcount = $DB->count_records('local_metadata_field', ['categoryid' => $field->categoryid]);

        $url = new \moodle_url('/local/metadata/index.php',
            ['id' => $field->id, 'contextlevel' => $contextlevel, 'sesskey' => sesskey()]);
        $output = '';

        // Edit.
        $url->param('action', 'editfield');
        $output .= $this->action_icon($url, new \pix_icon('t/edit', get_string('edit')), null,
            ['class' => 'action-icon action_edit']);

        // Delete.
        $url->param('action', 'deletefield');
        $output .= $this->action_icon($url, new \pix_icon('t/delete', get_string('delete')), null,
            ['class' => 'action-icon action_delete']);

        // Move up.
        if ($field->sortorder > 1) {
            $url->param('action', 'movefield');
            $url->param('dir', 'up');
            $output .= $this->action_icon($url, new \pix_icon('t/up', get_string('moveup')), null,
                ['class' => 'action-icon action_moveup']);
        } else {
            $output .= $this->action_icon(null, new \pix_icon('spacer', ''), null,
                ['class' => 'action-icon action_spacer']);
        }

        // Move down.
        if ($field->sortorder < $fieldcount) {
            $url->param('action', 'movefield');
            $url->param('dir', 'down');
            $output .= $this->action_icon($url, new \pix_icon('t/down', get_string('movedown')), null,
                ['class' => 'action-icon action_movedown']);
        } else {
            $output .= $this->action_icon(null, new \pix_icon('spacer', ''), null,
                ['class' => 'action-icon action_spacer']);
        }

        return $output;
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
        $output = '';

        // Delete.
        // Can only delete the last category if there are no fields in it.
        if (($categorycount > 1) || ($fieldcount == 0)) {
            $url->param('action', 'deletecategory');
            $output .= $this->action_icon($url, new \pix_icon('t/delete', get_string('delete')), null,
                ['class' => 'action-icon action_delete']);
        }

        // Move up.
        if ($category->sortorder > 1) {
            $url->param('action', 'movecategory');
            $url->param('dir', 'up');
            $output .= $this->action_icon($url, new \pix_icon('t/up', get_string('moveup')), null,
                ['class' => 'action-icon action_moveup']);
        }

        // Move down.
        if ($category->sortorder < $categorycount) {
            $url->param('action', 'movecategory');
            $url->param('dir', 'down');
            $output .= $this->action_icon($url, new \pix_icon('t/down', get_string('movedown')), null,
                ['class' => 'action-icon action_movedown']);
        }

        return $output;
    }
}