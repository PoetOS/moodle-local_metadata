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
 * Group metadata context handler class..
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatacontext_group;

defined('MOODLE_INTERNAL') || die;

// Group context was dropped between 1.8 and 1.9. Use the old definition here.
define('CONTEXT_GROUP', 60);

class context_handler extends \local_metadata\context\context_handler {

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     * @param int $contextname The name of this context (must be static - no language string).
     */
    public function __construct($instanceid = null, $contextlevel = null, $contextname = '') {
        return parent::__construct($instanceid, CONTEXT_GROUP, 'group');
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    public function get_instance() {
        global $DB;
        if (empty($this->instance)) {
            if (!empty($this->instanceid)) {
                $this->instance = $DB->get_record('groups', ['id' => $this->instanceid], '*', MUST_EXIST);
            } else {
                $this->instance = false;
            }
        }
        return $this->instance;
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle context.
     */
    public function get_context() {
        if (empty($this->context)) {
            if (!empty($this->instance)) {
                $this->context = \context_course::instance($this->instance->courseid);
            } else {
                $this->context = false;
            }
        }
        return $this->context;
    }

    /**
     * Return the instance id of the currently accessed context. Used by page displays (filter). Must be handled by the implementing
     * class.
     * @return int|boolean Instance id or false if not determined.
     */
    public function get_instanceid_from_currentcontext() {
        if (empty($this->instanceid)) {
            debugging('Must provide a group id.');
            $this->instanceid = false;
        }
        return $this->instanceid;
    }

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        return new \moodle_url('/group/group.php', ['id' => $this->instanceid, 'courseid' => $this->instance->courseid]);
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    public function require_access() {
        global $DB;
        $course = $DB->get_record('course', ['id' => $this->instance->courseid], '*', MUST_EXIST);
        require_login($course);
        require_capability('moodle/course:managegroups', $this->context);
        return true;
    }

    /**
     * Implement if specific context settings can be added to a context settings page (e.g. user preferences).
     */
    public function add_settings_to_context_menu($navmenu) {
        if (method_exists($navmenu, 'find') && $navmenu->find('groups', \settings_navigation::TYPE_SETTING)) {
            // Add the settings page to the groups settings menu, if enabled.
            $navmenu->add('groups', new \admin_externalpage('metadatacontext_groups',
                get_string('metadatatitle', 'metadatacontext_group'),
                new \moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_GROUP]), ['moodle/site:config']));
        }
        // Add the settings page to the course settings menu.
        $navmenu->add('courses', new \admin_externalpage('metadatacontext_groups',
            get_string('metadatatitle', 'metadatacontext_group'),
            new \moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_GROUP]), ['moodle/site:config']));
        return true;
    }

    /**
     * Hook function that is called when settings blocks are being built.
     */
    public function extend_settings_navigation($settingsnav, $context) {
        global $PAGE;
        if ($PAGE->pagetype == 'group-group') {
            // Context level is CONTEXT_COURSE.
            if ((get_config('metadatacontext_group', 'metadataenabled') == 1) &&
                has_capability('moodle/course:managegroups', $context)) {

                if ($settingnode = $settingsnav->find('groups', \settings_navigation::TYPE_SETTING)) {
                    $strmetadata = get_string('metadatatitle', 'metadatacontext_group');
                    $groupid = $PAGE->url->param('id');
                    $url = new \moodle_url('/local/metadata/index.php',
                        ['id' => $groupid, 'action' => 'groupdata', 'contextlevel' => CONTEXT_GROUP]);
                    $metadatanode = \navigation_node::create(
                        $strmetadata,
                        $url,
                        \navigation_node::NODETYPE_LEAF,
                        'metadata',
                        'metadata',
                        new \pix_icon('i/settings', $strmetadata)
                    );
                    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                        $metadatanode->make_active();
                    }
                    $settingnode->add_node($metadatanode);
                }
            }
        }
    }
}