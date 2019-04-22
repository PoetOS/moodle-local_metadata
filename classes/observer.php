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

namespace local_metadata;

defined('MOODLE_INTERNAL') || die();

/**
 * Local metadata event handler.
 */
class observer {

    /**
     * Triggered via any defined delete event.
     * - Dispatches metadata type specific event, if it exists.
     * - Currently only monitors "[context]_deleted" events.
     *
     * @param \core\event\* $event
     * @return bool true on success
     */
    public static function all_events($event) {
        $localobserver = substr(strrchr($event->eventname, '\\'), 1);
        if (method_exists('local_metadata\observer', $localobserver)) {
            return self::$localobserver($event);
        } else {
            return true;
        }
    }

    /**
     * Triggered via course_deleted event.
     * - Removes course metadata
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        return self::delete_metadata(CONTEXT_COURSE, $event->objectid);
    }

    /**
     * Triggered via module_deleted event.
     * - Removes module metadata
     *
     * @param \core\event\course_module_deleted $event
     * @return bool true on success
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        return self::delete_metadata(CONTEXT_MODULE, $event->objectid);
    }

    /**
     * Delete metadata for appropriate contextlevel fields.
     * - Removes user metadata
     *
     * @param \core\event\user_deleted $event
     * @return bool true on success
     */
    public static function delete_metadata($contextlevel, $instanceid) {
        global $DB;

        if (!empty($fields = $DB->get_records_select('local_metadata_field', 'contextlevel = ?', [$contextlevel], '', 'id'))) {
            $fieldids = array_keys($fields);
            list($sqlin, $params) = $DB->get_in_or_equal($fieldids);
            $params[] = $instanceid;
            $DB->delete_records_select('local_metadata', 'fieldid '.$sqlin.' AND instanceid = ?', $params);
        }
        return true;
    }
}
