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

defined('MOODLE_INTERNAL') || die();

/**
 * Generator class for metadata unit tests.
 *
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_metadata_generator extends testing_module_generator {

    /**
     * Create a specified metadata field.
     * @param int $contextlevel
     * @param string $shortname
     * @param string $name
     * @param int $categoryid
     * @param string $datatype
     * @return bool|int
     */
    public function create_metadata_field($contextlevel, $shortname, $name, $categoryid = 1, $datatype = 'text') {
        global $DB;

        return $DB->insert_record('local_metadata_field', [
            'contextlevel' => $contextlevel, 'shortname' => $shortname, 'name' => $name,
            'categoryid' => $categoryid, 'datatype' => $datatype]);
    }

    /**
     * Create actual metadata.
     * @param int $fieldid
     * @param int $instanceid
     * @param string $data
     * @return bool|int
     */
    public function create_metadata($fieldid, $instanceid, $data = '') {
        global $DB;

        return $DB->insert_record('local_metadata', ['fieldid' => $fieldid, 'instanceid' => $instanceid, 'data' => $data]);
    }
}