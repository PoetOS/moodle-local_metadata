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

namespace local_metadata\plugininfo;

defined('MOODLE_INTERNAL') || die();

/**
 * Subplugin info class.
 *
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright 2017 onwards Mike Churchward (mike.churchward@poetopensource.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadatacontext extends \core\plugininfo\base {
    /**
     * Define whether the plugin can uninstalled or not.
     * @return bool
     */
    public function is_uninstall_allowed() {
        return true;
    }
}
