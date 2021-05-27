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

namespace metadatacontext_course;

defined('MOODLE_INTERNAL') || die;

/**
 * Course metadata context handler class..
 *
 * @package metadatacontext_course
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class context_handler extends \local_metadata\context\context_handler {

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     * @param int $contextname The name of this context (must be static - no language string).
     */
    public function __construct($instanceid = null, $contextlevel = null, $contextname = '') {
        return parent::__construct($instanceid, CONTEXT_COURSE, 'course');
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    public function get_instance() {
        global $DB;
        if (empty($this->instance)) {
            if (!empty($this->instanceid)) {
                $this->instance = $DB->get_record('course', ['id' => $this->instanceid], '*', MUST_EXIST);
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
            if (!empty($this->instanceid)) {
                $this->context = \context_course::instance($this->instanceid);
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
        global $PAGE;
        if (empty($this->instanceid)) {
            if (isset($PAGE->course->id)) {
                $this->instanceid = $PAGE->course->id;
            } else {
                $this->instanceid = false;
            }
        }
        return $this->instanceid;
    }

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        return new \moodle_url('/course/view.php', ['id' => $this->instanceid]);
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    public function require_access() {
        require_login($this->instance);
        require_capability('moodle/course:update', $this->context);
        return true;
    }

    /**
     * Implement if specific context settings can be added to a context settings page (e.g. user preferences).
     * @param \admin_root $navmenu
     * @return bool
     */
    public function add_settings_to_context_menu(\admin_root $navmenu): bool {
        // Add the settings page to the course settings menu.
        $navmenu->add('courses', new \admin_externalpage('metadatacontext_courses',
            get_string('metadatatitle', 'metadatacontext_course'),
            new \moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_COURSE]), ['moodle/site:config']));
        return true;
    }

    /**
     * Hook function to extend the course settings navigation.
     * @param \navigation_node $parentnode
     * @param \stdClass $course
     * @param \stdClass $context
     */
    public function extend_navigation_course(\navigation_node $parentnode, \stdClass $course, \stdClass $context) {
        if ((get_config('metadatacontext_course', 'metadataenabled') == 1) &&
            has_capability('moodle/course:create', $context)) {
            $strmetadata = get_string('metadatatitle', 'metadatacontext_course');
            $url = new \moodle_url('/local/metadata/index.php',
                ['id' => $course->id, 'action' => 'coursedata', 'contextlevel' => CONTEXT_COURSE]);
            $metadatanode = \navigation_node::create($strmetadata, $url, \navigation_node::NODETYPE_LEAF,
                'metadata', 'metadata', new \pix_icon('i/settings', $strmetadata)
            );
            $parentnode->add_node($metadatanode);
        }
    }
}