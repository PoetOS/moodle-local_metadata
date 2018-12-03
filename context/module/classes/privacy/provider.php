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

namespace metadatacontext_module\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements
// This plugin does not store any personal user data.
    \core_privacy\local\metadata\null_provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns meta data about this system.
     *
     * @return  string
     */
    public static function _get_reason() {
        return 'privacy:metadata';
    }
}