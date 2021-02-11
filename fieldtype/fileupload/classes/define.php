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
 * Fileupload profile field
 *
 * @package   profilefield_fileupload
 * @copyright
 * @license
 */

namespace metadatafieldtype_fileupload;

defined('MOODLE_INTERNAL') || die;

/**
 * Class local_metadata_define_fileupload
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class define extends \local_metadata\fieldtype\define_base {

    /**
     * Add elements for creating/editing a fileupload profile field.
     *
     * @param moodleform $form
     */
    public function define_form_specific($form) {
        $form->addElement('filemanager', 'image', 'Uploadfile');

    }
}


