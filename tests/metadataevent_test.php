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
 * Test class for metadata events.
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_metadata
 */
class local_metadataevent_testcase extends advanced_testcase {

    /**
     * Setup tasks.
     */
    public function setUp() {
        $this->generator = $this->getDataGenerator()->get_plugin_generator('local_metadata');
        $this->course = [];
        $this->user = [];
        $this->module = [];
        $this->course[] = $this->getDataGenerator()->create_course();
        $this->course[] = $this->getDataGenerator()->create_course();
        $this->user[] = $this->getDataGenerator()->create_user();
        $this->user[] = $this->getDataGenerator()->create_user();
        $this->module[] = $this->getDataGenerator()->create_module('forum', ['course' => $this->course[0]->id],
            ['groupmode' => VISIBLEGROUPS]);
        $this->module[] = $this->getDataGenerator()->create_module('forum', ['course' => $this->course[1]->id],
            ['groupmode' => VISIBLEGROUPS]);

        parent::setUp();
    }

    /**
     * Performs unit tests for course deleted event.
     */
    public function test_coursedeleted() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a custom field of textarea type.
        $id1 = $this->generator->create_metadata_field(CONTEXT_COURSE, 'frogdesc', 'Description of frog');
        $this->generator->create_metadata($id1, $this->course[0]->id, 'Leopard frog');
        $this->generator->create_metadata($id1, $this->course[1]->id, 'Bullfrog');

        // Confirm expected data.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(2, $DB->count_records('local_metadata'));
        $this->assertEquals(1, $DB->count_records('local_metadata', ['instanceid' => $this->course[0]->id]));

        // Deleting the course should trigger the event.
        delete_course($this->course[0]->id, false);

        // Check the field data has been deleted.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(1, $DB->count_records('local_metadata'));
        $this->assertEquals(0, $DB->count_records('local_metadata', ['instanceid' => $this->course[0]->id]));
    }

    /**
     * Performs unit tests for user deleted event.
     */
    public function test_userdeleted() {
        global $DB;

        $this->resetAfterTest(true);

        // Add a custom field of textarea type.
        $id1 = $this->generator->create_metadata_field(CONTEXT_USER, 'haircolour', 'Colour of your hair');
        $this->generator->create_metadata($id1, $this->user[0]->id, 'Blonde');
        $this->generator->create_metadata($id1, $this->user[1]->id, 'Red');

        // Confirm expected data.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(2, $DB->count_records('local_metadata'));
        $this->assertEquals(1, $DB->count_records('local_metadata', ['instanceid' => $this->user[0]->id]));

        // Deleting the user should trigger the event.
        delete_user($this->user[0]);

        // Check the field data has been deleted.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(1, $DB->count_records('local_metadata'));
        $this->assertEquals(0, $DB->count_records('local_metadata', ['instanceid' => $this->user[0]->id]));
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