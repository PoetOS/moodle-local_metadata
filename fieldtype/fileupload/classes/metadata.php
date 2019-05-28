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
 * Strings for component 'profilefield_fileupload', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   profilefield_fileupload
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatafieldtype_fileupload;

defined('MOODLE_INTERNAL') || die;

/**
 * Class local_metadata_field_fileupload
 *
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadata extends \local_metadata\fieldtype\metadata {
    /**
     * Add elements for editing the profile field value.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        // Create the form field.
        $fileupload = $mform->addElement('filemanager', $this->inputname, format_string($this->field->name), null,
                    array('subdirs' => 0, 'maxbytes' => '50', 'areamaxbytes' => 83886080, 'maxfiles' => 1,
                          'accepted_types' => array('*'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL));
    }

    public function edit_save_data($new) {
        global $DB;
        $array = json_decode(json_encode($new), true);
        $keys = array_keys($array);
        $itemid = file_get_submitted_draft_itemid($keys[1]);
        $this->dradt_item_id = $itemid;
        $context = \context_module::instance($new->id);
        parent::edit_save_data($new);
        if (!empty($itemid)) {
            file_save_draft_area_files($itemid, $context->id, 'local_metadata', 'imageupload',  $itemid, array());
        }
    }

    /**
     * Display the data for this field
     *
     * @return string HTML.
     */
    public function display_data() {
        return '<input disabled="disabled" type="filemanager" name="'.$this->inputname.'" />test';
    }


    /**
     * When passing the instance object to the form class for the edit page
     * we should load the key for the saved data
     *
     * Overwrites the base class method.
     *
     * @param stdClass $instance Instance object.
     */
    public function edit_load_instance_data($instance) {
        global $DB;
        $context = \context_module::instance($instance->id);
        $draftitemid = file_get_submitted_draft_itemid('imageupload');
        file_prepare_draft_area($draftitemid, $context->id, 'local_metadata', 'imageupload', $this->data, array(), null);
        $instance->{$this->inputname} = $draftitemid;
    }
}


