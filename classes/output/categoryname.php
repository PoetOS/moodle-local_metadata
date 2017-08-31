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
 * Contains class local_metadata\output\categoryname
 *
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

namespace local_metadata\output;

defined('MOODLE_INTERNAL') || die;

use context_system;
use lang_string;

/**
 * Class to preapare a metadata category for display.
 *
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */
class categoryname extends \core\output\inplace_editable {

    /**
     * Constructor.
     *
     * @param \stdClass $tagcoll
     */
    public function __construct($category) {
        $editable = has_capability('moodle/user:update', context_system::instance());
        $edithint = new lang_string('profileeditcategory', 'admin', format_string($category->name));
        $value = $category->name;
        $name = format_string($category->name, true);
        $editlabel = new lang_string('profileeditcategory', 'admin', $name);
        parent::__construct('local_metadata', 'categoryname', $category->id, $editable, $name, $value, $edithint, $editlabel);
    }

    /**
     * Updates the value in database and returns itself, called from inplace_editable callback
     *
     * @param int $itemid
     * @param mixed $newvalue
     * @return \self
     */
    public static function update($itemid, $newvalue) {
        global $DB;
        require_capability('moodle/user:update', context_system::instance());
        $category = $DB->get_record('local_metadata_category', ['id' => $itemid], '*', MUST_EXIST);
        $category->name = $newvalue;
        $DB->update_record('local_metadata_category', $category);
        return new self($category);
    }
}
