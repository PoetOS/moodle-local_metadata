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

defined('MOODLE_INTERNAL') || die;

// Required for non-standard context constants definition.
require_once($CFG->dirroot.'/local/metadata/lib.php');
global $LOCALMETADATACONTEXTS;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('metadatafolder', get_string('metadata', 'local_metadata')));

    // Create a settings page and add an enable setting for each metadata context type.
    $settings = new admin_settingpage('local_metadata', get_string('settings'));
    if ($ADMIN->fulltree) {
        foreach ($LOCALMETADATACONTEXTS as $contextname) {
            $item = new admin_setting_configcheckbox('local_metadata/'.$contextname.'metadataenabled',
                new lang_string($contextname.'metadataenabled', 'local_metadata'), '', 0);
            $settings->add($item);
        }
    }
    $ADMIN->add('metadatafolder', $settings);

    // Create a new external settings page for each metadata context type data definitions.
    foreach ($LOCALMETADATACONTEXTS as $contextlevel => $contextname) {
        $ADMIN->add('metadatafolder',
            new admin_externalpage('metadata'.$contextname, get_string($contextname.'metadata', 'local_metadata'),
                new moodle_url('/local/metadata/index.php', ['contextlevel' => $contextlevel]), ['moodle/site:config']));

        // Add context settings to specific context settings pages (if possible).
        if ((get_config('local_metadata', $contextname.'metadataenabled') == 1) &&
            file_exists($CFG->dirroot.'/local/metadata/classes/output/'.$contextname)) {
            $contextclass = "\\local_metadata\\output\\{$contextname}\\context_handler";
            $contexthandler = new $contextclass();
            $contexthandler->add_settings_to_context_page($ADMIN);
        }
    }

    $settings = null;
}