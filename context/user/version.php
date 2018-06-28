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
 * Metadata user context plugin version info.
 *
 * @package local_metadata
 * @subpackage metadatacontext_user
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright 2017 onwards Mike Churchward (mike.churchward@poetopensource.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2017070103;
$plugin->release   = 'BETA3.3.4 (Build 2018062800)';
$plugin->maturity  = MATURITY_BETA;
$plugin->requires  = 2016052300; // Requires this Moodle version.
$plugin->component = 'metadatacontext_user'; // Full name of the plugin (used for diagnostics).
