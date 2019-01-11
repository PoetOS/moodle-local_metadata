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
 *
 * @package   local_metadata
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/** Database:
 *
 * local_metadata(id, instanceid, fieldid, data, dataformat).
 */
defined('MOODLE_INTERNAL') || die();
/**
 * Provides the information to restore metadata.
 */
class restore_local_metadata_plugin extends restore_local_plugin
{
    const TABLE = 'local_metadata';

    /**
     * Returns the format information to attach to module element.
     */
    protected function define_module_plugin_structure() {
        return $this->get_paths('module');
    }

    /**
     * Returns the format information to attach to course element.
     */
    protected function define_course_plugin_structure() {
        return $this->get_paths('course');
    }

    protected function get_paths($name) {
        $paths = [];
        $elename = trim($name) !== '' ? ($name . '_') : '';
        $elepath = $this->get_pathfor($elename . 'metadata');
        $paths[] = new restore_path_element($elename . 'metadata', $elepath);
        return $paths; // And we return the interesting paths.
    }

    public function process_module_metadata($data) {
        $data = (object)$data;
        $data->instanceid = $this->task->get_moduleid();
        $this->insert_into_db($data);
    }

    public function process_course_metadata($data) {
        global $DB;
        $data = (object)$data;
        $courseid = $this->task->get_courseid();
        if ($record = $DB->get_record(self::TABLE, ['instanceid' => $courseid, 'fieldid' => $data->fieldid])) {
            $data->id = $record->id;
            $data->instanceid = $courseid;
            $DB->update_record(self::TABLE, $data);
        } else {
            $data->instanceid = $courseid;
            $this->insert_into_db($data);
        }
    }

    protected function insert_into_db($data) {
        global $DB;
        $DB->insert_record(self::TABLE, $data);
    }
}
