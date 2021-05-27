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

namespace metadatacontext_course\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderer class for course context. Override anything needed.
 *
 * @package metadatacontext_course
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \local_metadata\output\renderer {

    /**
     * Category table renderer.
     *
     * @param category_table $categorytable renderable object.
     */
    public function render_category_table(\local_metadata\output\category_table $categorytable) {
        $output = parent::render_category_table($categorytable);
        if (get_config('metadatacontext_course', 'metadataenabled') == 0) {
            $output = $this->notification(get_string('metadatadisabled', 'metadatacontext_course')) . $output;
        }
        return $output;
    }

    /**
     * Course settings renderer.
     *
     * @param manage_data $coursesettings renderable object.
     * @return string
     */
    public function render_manage_data(manage_data $coursesettings): string {
        $this->page->set_title($coursesettings->instance->shortname . ': ' . get_string('metadatatitle', 'metadatacontext_course'));
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(get_string('metadatatitle', 'metadatacontext_course'));
        if ($coursesettings->saved) {
            $output .= $this->notification(get_string('metadatasaved', 'local_metadata'), 'success');
        }
        $output .= $coursesettings->form->render();
        $output .= $this->footer();

        return $output;
    }
}