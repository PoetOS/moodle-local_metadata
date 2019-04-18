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
     * Performs unit tests for course deleted event.
     */
    public function test_coursedeleted() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');

        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();

        // Add a custom field of textarea type.
        $id1 = $DB->insert_record('local_metadata_field', [
            'contextlevel' => CONTEXT_COURSE, 'shortname' => 'frogdesc', 'name' => 'Description of frog',
            'categoryid' => 1, 'datatype' => 'textarea']);

        $data = new \stdClass();
        $data->instanceid  = $course->id;
        $data->fieldid = $id1;
        $data->data    = 'Leopard frog';
        $DB->insert_record('local_metadata', $data);

        // Check the data is returned.
        local_metadata_load_data($course, CONTEXT_COURSE);
        $this->assertObjectHasAttribute('local_metadata_field_frogdesc', $course);
        $this->assertEquals('Leopard frog', $course->local_metadata_field_frogdesc['text']);

        // Deleting the course should trigger the event.
        delete_course($course->id, false);

        // Check the field data has been deleted.
        unset($course->local_metadata_field_frogdesc);
        local_metadata_load_data($course, CONTEXT_COURSE);
        $this->assertObjectNotHasAttribute('local_metadata_field_frogdesc', $course);
    }

    /**
     * Performs unit tests for user deleted event.
     */
    public function test_userdeleted() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');

        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();

        // Add a custom field of textarea type.
        $id1 = $DB->insert_record('local_metadata_field', [
            'contextlevel' => CONTEXT_USER, 'shortname' => 'haircolour', 'name' => 'Colour of your hair',
            'categoryid' => 1, 'datatype' => 'textarea']);

        $data = new \stdClass();
        $data->instanceid  = $user->id;
        $data->fieldid = $id1;
        $data->data    = 'Blonde';
        $DB->insert_record('local_metadata', $data);

        // Check the data is returned.
        local_metadata_load_data($user, CONTEXT_USER);
        $this->assertObjectHasAttribute('local_metadata_field_haircolour', $user);
        $this->assertEquals('Blonde', $user->local_metadata_field_haircolour['text']);

        // Deleting the user should trigger the event.
        delete_user($user);

        // Check the field data has been deleted.
        unset($user->local_metadata_field_haircolour);
        local_metadata_load_data($user, CONTEXT_USER);
        $this->assertObjectNotHasAttribute('local_metadata_field_haircolour', $user);
    }

    /**
     * Performs unit tests for course module deleted event.
     */
    public function test_coursemoduledeleted() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');

        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id], ['groupmode' => VISIBLEGROUPS]);

        // Add a custom field of textarea type.
        $id1 = $DB->insert_record('local_metadata_field', [
            'contextlevel' => CONTEXT_MODULE, 'shortname' => 'difficulty', 'name' => 'Level of difficulty',
            'categoryid' => 1, 'datatype' => 'textarea']);

        $data = new \stdClass();
        $data->instanceid  = $forum->cmid;
        $data->fieldid = $id1;
        $data->data    = 'Beginner';
        $DB->insert_record('local_metadata', $data);

        // Confirm the record is there.
        $this->assertTrue($DB->record_exists('local_metadata', ['fieldid' => $id1, 'instanceid' => $forum->cmid]));

        // Deleting the course module should trigger the event.
        course_delete_module($forum->cmid);

        // Check the field data has been deleted.
        $this->assertFalse($DB->record_exists('local_metadata', ['fieldid' => $id1, 'instanceid' => $forum->cmid]));
    }
}