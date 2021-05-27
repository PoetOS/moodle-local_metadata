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

namespace metadatacontext_course\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Course metadata management renderable.
 *
 * @package metadatacontext_course
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_data extends \local_metadata\output\manage_data {

    /**
     * manage_data constructor.
     * @param null $instance
     * @param null $contextlevel
     * @param null $action
     */
    public function __construct($instance = null, $contextlevel = null, $action = null) {
        global $COURSE;

        $instance = ($instance === null) ? clone($COURSE) : $instance;
        $action = ($action === null) ? 'coursedata' : $action;
        parent::__construct($instance, CONTEXT_COURSE, $action);
    }
}