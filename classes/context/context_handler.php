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

/**
 * General metadata context handler class..
 *
 * @package local_metadata
 * @copyright  2016 POET
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\context;

defined('MOODLE_INTERNAL') || die;

abstract class context_handler {

    public $instanceid;
    protected $instance;
    public $contextlevel;
    protected $context;

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     */
    public function __construct($instanceid = null, $contextlevel = null) {
        $this->instanceid = $instanceid;
        $this->contextlevel = $contextlevel;
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    abstract public function get_instance();

    /**
     * Return a Moodle page layout. Defaults to "admin".
     * @return string The layout name.
     */
    public function get_layout() {
        return 'admin';
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle context.
     */
    abstract public function get_context();

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        return new \moodle_url('/');
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    abstract public function require_access();

    /**
     * Magic method for getting properties.
     * @param string $name
     * @return mixed
     * @throws \coding_exception
     */
    public function __get($name) {
        $allowed = ['instance', 'context'];
        if (in_array($name, $allowed)) {
            return $this->{'get_'.$name};
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
}