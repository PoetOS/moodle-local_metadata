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
 * @copyright 2016 The POET Group
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('metadatafolder', get_string('metadata', 'local_metadata')));

    $settings = new admin_settingpage('local_metadata', get_string('settings'));
    if ($ADMIN->fulltree) {
        $item = new admin_setting_configcheckbox('local_metadata/usermetadataenabled',
            new lang_string('usermetadataenabled', 'local_metadata'), '', 0);
        $settings->add($item);
        $item = new admin_setting_configcheckbox('local_metadata/coursemetadataenabled',
            new lang_string('coursemetadataenabled', 'local_metadata'), '', 1);
        $settings->add($item);
        $item = new admin_setting_configcheckbox('local_metadata/modulemetadataenabled',
            new lang_string('modulemetadataenabled', 'local_metadata'), '', 0);
        $settings->add($item);
    }
    $ADMIN->add('metadatafolder', $settings);

    $ADMIN->add('metadatafolder', new admin_externalpage('metadatauser', get_string('usermetadata', 'local_metadata'),
        new moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_USER]), ['moodle/site:config']));

    $ADMIN->add('metadatafolder', new admin_externalpage('metadatacourse', get_string('coursemetadata', 'local_metadata'),
        new moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_COURSE]), ['moodle/site:config']));

    $ADMIN->add('metadatafolder', new admin_externalpage('metadatamodule', get_string('modulemetadata', 'local_metadata'),
        new moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_MODULE]), ['moodle/site:config']));

    // Add the settings page to the user setttings menu, if enabled.
    if (get_config('local_metadata', 'usermetadataenabled') == 1) {
        $ADMIN->add('users', new admin_externalpage('users_metadata', get_string('usermetadata', 'local_metadata'),
                new moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_USER]), ['moodle/site:config']));
    }

    // Add the settings page to the course setttings menu, if enabled.
    if (get_config('local_metadata', 'coursemetadataenabled') == 1) {
        $ADMIN->add('courses', new admin_externalpage('courses_metadata', get_string('coursemetadata', 'local_metadata'),
            new moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_COURSE]), ['moodle/site:config']));
    }

    // Add the settings page to the activity modules settings menu, if enabled.
    // Add the settings page to the course setttings menu, if enabled.
    if (get_config('local_metadata', 'modulemetadataenabled') == 1) {
        $ADMIN->add('modsettings',
            new admin_externalpage('modules_metadata', get_string('modulemetadata', 'local_metadata'),
                new moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_MODULE]), ['moodle/site:config']),
            'managemodulescommon');
    }

    $settings = null;
}