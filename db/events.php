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
 * @author Mike Churchward <mike.churchward@poetgroup.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2016 POET
 */

$observers = [
    ['eventname' => '\core\event\course_deleted',
     'callback' => '\local_metadata\observer::course_deleted'
    ],
    ['eventname' => '\core\event\user_deleted',
     'callback' => '\local_metadata\observer::user_deleted'
    ],
    ['eventname' => '\core\event\course_module_deleted',
     'callback' => '\local_metadata\observer::course_module_deleted'
    ],
    // Default event observer. This will dispatch events according to metadata types.
//    ['eventname' => '*',
//     'callback' => '\local_metadata\observer::all_events',
//     'priority' => 9999,
//    ],
];