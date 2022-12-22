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
 * @subpackage metadatacontext_group
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

/**
 * Test class for group metadatacontext events.
 *
 * @package local_metadata
 * @subpackage metadatacontext_group
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_metadata
 * @group metadatacontext_group
 */

class metadatacontext_group_event_testcase extends advanced_testcase {

    /**
     * Setup tasks.
     */
    public function setUp(): void {
        $this->generator = $this->getDataGenerator()->get_plugin_generator('local_metadata');
        $this->course = [];
        $this->course[] = $this->getDataGenerator()->create_course();
        $this->group = [];
        $this->group[] = $this->getDataGenerator()->create_group(['courseid' => $this->course[0]->id]);
        $this->group[] = $this->getDataGenerator()->create_group(['courseid' => $this->course[0]->id]);

        parent::setUp();
    }

    /**
     * Performs unit tests for group deleted event.
     */
    public function test_groupdeleted() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        // Don't declare CONTEXT_GROUP as this distorts the test
        $contextgroup = 60;

        // Create a custom field of textarea type.
        $id1 = $this->generator->create_metadata_field($contextgroup, 'frogdesc', 'Description of frog');
        $this->generator->create_metadata($id1, $this->group[0]->id, 'Leopard frog');
        $this->generator->create_metadata($id1, $this->group[1]->id, 'Bullfrog');

        // Confirm expected data.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(2, $DB->count_records('local_metadata'));
        $this->assertEquals(1, $DB->count_records('local_metadata', ['instanceid' => $this->group[0]->id]));

        // Deleting the group should trigger the event.
        groups_delete_group($this->group[0]);

        // Check the field data has been deleted.
        $this->assertEquals(1, $DB->count_records('local_metadata_field'));
        $this->assertEquals(1, $DB->count_records('local_metadata'));
        $this->assertEquals(0, $DB->count_records('local_metadata', ['instanceid' => $this->group[0]->id]));
    }
}
