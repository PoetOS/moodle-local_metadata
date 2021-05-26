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
 * Metadata textarea fieldtype plugin version info.
 *
 * @package local_metadata
 * @subpackage metadatafieldtype_textarea
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright 2017 onwards Mike Churchward (mike.churchward@poetopensource.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2021053100;
$plugin->release   = '3.11.0 (Build 2021053100)';
$plugin->maturity  = MATURITY_STABLE;
$plugin->requires  = 2021051700; // Moodle 3.11 release and upwards.
$plugin->component = 'metadatafieldtype_textarea'; // Full name of the plugin (used for diagnostics).
