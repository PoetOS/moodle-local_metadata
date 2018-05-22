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
 * Cohort metadata context handler class..
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatacontext_cohort;

defined('MOODLE_INTERNAL') || die;

// Cohort context has never existed. Define it here using the '9000' category.
define('CONTEXT_COHORT', 9000);

class context_handler extends \local_metadata\context\context_handler {

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in question.
     * @param int $contextlevel The context level for this metadata.
     * @param int $contextname The name of this context (must be static - no language string).
     */
    public function __construct($instanceid = null, $contextlevel = null, $contextname = '') {
        return parent::__construct($instanceid, CONTEXT_COHORT, 'cohort');
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    public function get_instance() {
        global $DB;
        if (empty($this->instance)) {
            if (!empty($this->instanceid)) {
                $this->instance = $DB->get_record('cohort', ['id' => $this->instanceid], '*', MUST_EXIST);
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
                $this->context = \context::instance_by_id($this->instance->contextid, MUST_EXIST);
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
            debugging('Must provide a cohort id.');
            $this->instanceid = false;
        }
        return $this->instanceid;
    }

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        return new \moodle_url('/cohort/edit.php', ['id' => $this->instanceid]);
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    public function require_access() {
        require_login();
        require_capability('moodle/cohort:manage', $this->context);
        return true;
    }

    /**
     * Implement if specific context settings can be added to a context settings page (e.g. Users / Accounts).
     */
    public function add_settings_to_context_menu($navmenu) {
        // Add the settings page to the cohorts settings menu, if enabled.
        $navmenu->add('accounts',
            new \admin_externalpage('metadatacontext_cohorts', get_string('metadatatitle', 'metadatacontext_cohort'),
                new \moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_COHORT]), ['moodle/site:config']),
            'cohorts');
        return true;
    }

    /**
     * Hook function that is called when settings blocks are being built.
     */
    public function extend_settings_navigation($settingsnav, $context) {
        global $PAGE;

        if ($PAGE->pagetype == 'cohort-edit') {
            // Context level is CONTEXT_SYSTEM.
            if ((get_config('metadatacontext_cohort', 'metadataenabled') == 1) &&
                has_capability('moodle/cohort:manage', $context)) {

                if ($settingnode = $settingsnav->find('cohorts', \settings_navigation::TYPE_SETTING)) {
                    $cohortid = $PAGE->url->param('id');
                    $this->instanceid = $cohortid;
                    $this->get_instance();
                    if (isset($this->instance) && ($this->instance !== false)) {
                        $strmetadata = get_string('instancemetadata', 'local_metadata', ['instancename' => $this->instance->name]);
                    } else {
                        $strmetadata = get_string('metadatatitle', 'metadatacontext_cohort');
                    }
                    $url = new \moodle_url('/local/metadata/index.php',
                        ['id' => $cohortid, 'action' => 'cohortdata', 'contextlevel' => CONTEXT_COHORT]);
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