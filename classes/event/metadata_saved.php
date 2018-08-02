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
 * Subplugin info class.
 *
 * @package local_metadata
 * @author Tim St.Clair <tim.stclair@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * metadata_saved event emitter
 *
 * emit an event when data is entered which contains all the field names and their values for this instance
 * the observers of this event will have to decice whether they handle data for the supplied context
 *
 * triggered by: lib.php -> local_metadata_save_data()
 */

namespace local_metadata\event;

defined('MOODLE_INTERNAL') || die();


class metadata_saved extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public function get_description() {
		return "The user with id '{$this->userid}' saved metadata for course with id '{$this->contextinstanceid}'.";
    }

    public static function get_name() {
		return 'Local Metadata CRUD save event'; // TODO ad to langpack
    }

    public function get_url() {
        return new \moodle_url('/local/metadata/index.php');
    }
}