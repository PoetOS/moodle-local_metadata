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

/**
 * Test class for course module metadatacontext events.
 *
 * @package local_metadata
 * @subpackage metadatacontext_module
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_metadata
 * @group metadatacontext_module
 */

class metadatacontext_module_event_testcase extends advanced_testcase {

    /**
     * Setup tasks.
     */
    public function setUp() {
        $this->generator = $this->getDataGenerator()->get_plugin_generator('local_metadata');
        $this->course = [];
        $this->course[] = $this->getDataGenerator()->create_course();
        $this->course[] = $this->getDataGenerator()->create_course();
        $this->module = [];
        $this->module[] = $this->getDataGenerator()->create_module('forum', ['course' => $this->course[0]->id],
            ['groupmode' => VISIBLEGROUPS]);
        $this->module[] = $this->getDataGenerator()->create_module('forum', ['course' => $this->course[1]->id],
            ['groupmode' => VISIBLEGROUPS]);

        parent::setUp();
    }

    /**
     * Performs unit tests for course module deleted event.
     */
    public function test_coursemoduledeleted() {
        global $DB;

        $this->resetAfterTest(true);

        // Add a custom field of textarea type.
        $id1 = $this->generator->create_metadata_field(CONTEXT_MODULE, 'difficulty', 'Level of difficulty');
        $this->generator->create_metadata($id1, $this->module[0]->cmid, 'Beginner');
        $this->generator->create_metadata($id1, $this->module[1]->cmid, 'Expert');

        // Confirm expected data.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(2, $DB->count_records('local_metadata'));
        $this->assertEquals(1, $DB->count_records('local_metadata', ['instanceid' => $this->module[0]->cmid]));

        // Deleting the course module should trigger the event.
        course_delete_module($this->module[0]->cmid);

        // Check the field data has been deleted.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(1, $DB->count_records('local_metadata'));
        $this->assertEquals(0, $DB->count_records('local_metadata', ['instanceid' => $this->module[0]->cmid]));
    }
}