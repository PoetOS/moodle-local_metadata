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
 * @subpackage metadatacontext_module
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

namespace metadatacontext_module;

defined('MOODLE_INTERNAL') || die();

/**
 * Local metadatacontext_module event handler.
 */
class observer {
    /**
     * Triggered via course_module_deleted event.
     * - Removes module metadata
     *
     * @param \core\event\course_module_deleted $event
     * @return bool true on success
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        return \local_metadata\observer::delete_metadata(CONTEXT_MODULE, $event->objectid);
    }
}
