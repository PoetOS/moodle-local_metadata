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

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Unit tests for local/metadata/lib.php.
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @group local_metadata
 */
class local_metadatalib_testcase extends advanced_testcase {
    /**
     * Tests profile_get_custom_fields function and checks it is consistent
     * with profile_user_record.
     */
    public function test_get_custom_fields() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        // Add a custom field of textarea type.
        $id1 = $DB->insert_record('local_metadata_field', array(
                'contextlevel' => CONTEXT_USER, 'shortname' => 'frogdesc', 'name' => 'Description of frog',
                'categoryid' => 1, 'datatype' => 'textarea'));

        // Check the field is returned.
        $result = local_metadata_get_custom_fields(CONTEXT_USER);
        $this->assertArrayHasKey($id1, $result);
        $this->assertEquals('frogdesc', $result[$id1]->shortname);

        // Textarea types are not included in user data though, so if we
        // use the 'only in user data' parameter, there is still nothing.
        $this->assertArrayNotHasKey($id1, local_metadata_get_custom_fields(CONTEXT_USER, true));

        // Check that profile_user_record returns same (no) fields.
        $this->assertObjectNotHasAttribute('frogdesc', local_metadata_user_record($user->id));

        // Check that profile_user_record returns all the fields when requested.
        $this->assertObjectHasAttribute('frogdesc', local_metadata_user_record($user->id, false));

        // Add another custom field, this time of normal text type.
        $id2 = $DB->insert_record('local_metadata_field', array(
                'contextlevel' => CONTEXT_USER, 'shortname' => 'frogname',
                'name' => 'Name of frog', 'categoryid' => 1, 'datatype' => 'text'));

        // Check both are returned using normal option.
        $result = local_metadata_get_custom_fields(CONTEXT_USER);
        $this->assertArrayHasKey($id2, $result);
        $this->assertEquals('frogname', $result[$id2]->shortname);

        // And check that only the one is returned the other way.
        $this->assertArrayHasKey($id2, local_metadata_get_custom_fields(CONTEXT_USER, true));

        // Check profile_user_record returns same field.
        $this->assertObjectHasAttribute('frogname', local_metadata_user_record($user->id));

        // Check that profile_user_record returns all the fields when requested.
        $this->assertObjectHasAttribute('frogname', local_metadata_user_record($user->id, false));
    }

    /**
     * Make sure that all profile fields can be initialised without arguments.
     */
    public function test_default_constructor() {
        global $CFG;
        require_once($CFG->dirroot . '/local/metadata/definelib.php');
        $datatypes = local_metadata_list_datatypes();
        foreach ($datatypes as $datatype => $datatypename) {
            $classname = "metadatafieldtype_{$datatype}\\metadata";
            $newdatatype = new $classname();
            $this->assertNotNull($newdatatype);
        }
    }

}
