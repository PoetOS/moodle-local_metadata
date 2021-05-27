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

namespace metadatacontext_group\output;

defined('MOODLE_INTERNAL') || die;

/**
 * Group metadata management renderable.
 *
 * @package metadatacontext_group
 * @author Mike Churchward <mike.churchward@poetopensource.org>
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
    public function __construct($instance = null, $contextlevel=null, $action=null) {
        $action = ($action === null) ? 'groupdata' : $action;
        parent::__construct($instance, CONTEXT_GROUP, $action);
    }
}