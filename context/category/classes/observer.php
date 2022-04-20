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

namespace metadatacontext_category;

/**
 * Local metadatacontext_category event handler.
 * @package metadatacontext_category
 * @subpackage metadatacontext_category
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */
class observer {
    /**
     * Triggered via course_category_deleted event.
     * - Removes category metadata
     *
     * @param \core\event\course_category_deleted $event
     * @return bool true on success
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event): bool {
        return \local_metadata\observer::delete_metadata(CONTEXT_COURSECAT, $event->objectid);
    }
}
