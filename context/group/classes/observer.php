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
 * @subpackage metadatacontext_group
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

namespace metadatacontext_group;

defined('MOODLE_INTERNAL') || die();

/**
 * Local metadatacontext_group event handler.
 */
class observer {
    /**
     * Triggered via group_deleted event.
     * - Removes group metadata
     *
     * @param \core\event\group_deleted $event
     * @return bool true on success
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        return \local_metadata\observer::delete_metadata(CONTEXT_GROUP, $event->objectid);
    }
}
