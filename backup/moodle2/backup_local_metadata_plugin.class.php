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
 *
 * @package   local_metadata
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/** Database:
 *
 * local_metadata(id, instanceid, fieldid, data, dataformat)
 */
defined('MOODLE_INTERNAL') || die();
/**
 * Provides the information to backup metadata
 */

class backup_local_metadata_plugin extends backup_local_plugin
{
    // public function define_plugin_structure($connection)
    // {
    //     global $CFG;
    //     $fp = fopen($CFG->dataroot . '/test.txt', 'a+');
    //     fwrite($fp, $connection . PHP_EOL);
    //     fclose($fp);
    //     parent::define_plugin_structure($connection);
    // }

    /**
     * Returns the format information to attach to module element
     */
    protected function define_module_plugin_structure()
    {
        return $this->build_structure(backup::VAR_MODID, 'module');
    }

    /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure()
    {
        return $this->build_structure(backup::VAR_COURSEID, 'course');
    }

    /**
     * Returns the format information to attach to module element
     */
    protected function define_groups_plugin_structure()
    {
        var_dump('Hello from groups');
        die;
    }

    protected function build_structure($id, $name = '')
    {
        $plugin = $this->get_plugin_element();
        $name = trim($name) !== '' ? $name . '_' : '';
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $pluginelement = new backup_nested_element($name . 'metadata', ['id'], ['instanceid', 'fieldid', 'data', 'dataformat']);
        $pluginelement->set_source_table('local_metadata', ['instanceid' => $id]);
        $pluginwrapper->add_child($pluginelement);
        $plugin->add_child($pluginwrapper);
        return $plugin;
    }
}
