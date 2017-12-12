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
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

/**
 * General metadata context handler class..
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\context;

defined('MOODLE_INTERNAL') || die;

abstract class context_handler {

    public $instanceid;
    protected $instance;
    protected $contextname;
    protected $contextlevel;
    protected $context;

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     * @param int $contextname The name of this context (must be static - no language string).
     */
    public function __construct($instanceid = null, $contextlevel = null, $contextname = '') {
        $this->instanceid = $instanceid;
        $this->contextlevel = $contextlevel;
        $this->contextname = $contextname;
    }

    /**
     * Factory function to return a context object.
     * @param string $contextname The name of a valid subplugin context.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     * @return object The object for the specified context subplugin.
     */
    static public function factory($contextname, $instanceid = null, $contextlevel = null) {
        // Since get_plugin_list already caches the list, don't worry about multiple calls.
        $contextplugins = \core_component::get_plugin_list('metadatacontext');
        if (isset($contextplugins[$contextname])) {
            $contextclass = "\\metadatacontext_{$contextname}\\context_handler";
            return new $contextclass($instanceid, $contextlevel);
        } else {
            throw new \moodle_exception('errorcontextnotfound', 'local_metadata', null, ['contextname' => $contextname]);
        }
    }

    /**
     * Return context subplugin enabled status.
     * @param string $contextname The name of a valid subplugin context.
     * @return boolean Enabled status.
     */
    static public function is_enabled($contextname) {
        return get_config('metadatacontext_'.$contextname, 'metadataenabled') == 1;
    }

    /**
     * Return an array of subplugin names for all subplugins.
     * @return array Names of all subplugins.
     */
    static public function all_subplugin_names() {
        $pluginnames = [];
        $contextplugins = \core_component::get_plugin_list('metadatacontext');
        foreach ($contextplugins as $contextname => $contextlocation) {
            $pluginnames[] = $contextname;
        }
        return $pluginnames;
    }

    /**
     * Return an array of empty subplugin objects for all subplugins.
     * @return array Objects for all subplugins.
     */
    static public function all_subplugins() {
        $plugins = [];
        $pluginnames = self::all_subplugin_names();
        foreach ($pluginnames as $contextname) {
            $plugins[] = self::factory($contextname);
        }
        return $plugins;
    }

    /**
     * Return an array of empty subplugin objects for all enabled subplugins.
     * @return array Objects for all enabled subplugins.
     */
    static public function all_enabled_subplugins() {
        $plugins = self::all_subplugins();
        foreach ($plugins as $index => $contexthandler) {
            if (!self::is_enabled($contexthandler->contextname)) {
                unset($plugins[$index]);
            }
        }
        return $plugins;
    }

    /**
     * Find the instance id of the context type from the most appropriate Moodle context.
     * @var string $contextname The name of the context type.
     * @return int|boolean The instance id determined; false if not found.
     */
    static public function find_instanceid($contextname) {
        try {
            $contexthandler = self::factory($contextname);
            $instanceid = $contexthandler->get_instanceid_from_currentcontext();
        } catch (\moodle_exception $e) {
            debugging('Exception detected when using metadata filter: ' . $e->getMessage(), DEBUG_NORMAL, $e->getTrace());
            $instanceid = false;
        }
        return $instanceid;
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    abstract public function get_instance();

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle context.
     */
    abstract public function get_context();

    /**
     * Return the instance id of the currently accessed context. Used by page displays (filter). Must be handled by the implementing
     * class.
     * @return int|boolean Instance id or false if not determined.
     */
    abstract public function get_instanceid_from_currentcontext();

    /**
     * Return the context level.
     * @return int The metadata context level.
     */
    public function get_contextlevel() {
        return $this->contextlevel;
    }

    /**
     * Return the context name.
     * @return int The metadata context level.
     */
    public function get_contextname() {
        return $this->contextname;
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    abstract public function require_access();

    /**
     * Return a Moodle page layout. Defaults to "admin".
     * @return string The layout name.
     */
    public function get_layout() {
        return 'admin';
    }

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        return new \moodle_url('/');
    }

    /**
     * Magic method for getting properties.
     * @param string $name
     * @return mixed
     * @throws \coding_exception
     */
    public function __get($name) {
        $allowed = ['instance', 'contextlevel', 'context', 'contextname'];
        if (in_array($name, $allowed)) {
            return $this->{'get_'.$name}();
        } else {
            throw new \coding_exception($name.' is not a publicly accessible property of '.get_class($this));
        }
    }

    /**
     * Implement if specific context settings can be added to a context settings menu (e.g. site admin / users).
     * @param object $navmenu The Moodle navmenu to add the settings link to.
     */
    public function add_settings_to_context_menu($navmenu) {
        return false;
    }

    /**
     * Implement extend_settings_navigation hook if general administration navigation entries are required.
     *
     */
    public function extend_settings_navigation($settingsnav, $context) {
        return true;
    }

    /**
     * Implement myprofile_navigation hook function that is called when user profile page is being built.
     */
    public function myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
        return true;
    }

    /**
     * Implement extend_navigation_course hook function to extend the course settings navigation.
     */
    public function extend_navigation_course($parentnode, $course, $context) {
        return true;
    }

    /**
     * Implement extend_navigation_user_settings hook function to extend the navigation for user settings node.
     *
     * @param navigation_node $navigation  The navigation node to extend
     * @param stdClass        $user        The user object
     * @param context         $usercontext The context of the user
     * @param stdClass        $course      The course to object for the tool
     * @param context         $coursecontext     The context of the course
     */
    public function extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
        return true;
    }

    /**
     * Implement coursemodule_standard_elements hook function to insert metadata form elements in the native module form
     * @param moodleform $formwrapper The moodle quickforms wrapper object.
     * @param MoodleQuickForm $mform The actual form object (required to modify the form).
     */
    public function coursemodule_standard_elements($formwrapper, $mform) {
        return true;
    }

    /**
     * Hook the add/edit of the course module.
     *
     * @param stdClass $data Data from the form submission.
     * @param stdClass $course The course.
     */
    public function coursemodule_edit_post_actions($data, $course) {
        return $data;
    }
}