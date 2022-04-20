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

namespace local_metadata;

/**
 * Renderer class for course context. Override anything needed.
 *
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group local_metadata
 */
class metadata_test extends \advanced_testcase {

    /** @var \local_metadata\fieldtype\metadata  */
    protected $metadata;

    /**
     * Sets up the test cases.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->metadata = new \local_metadata\fieldtype\metadata();
    }

    /**
     * Performs unit tests for all services supported by the filter.
     *
     * Need to update this test to not contact external services.
     */
    public function test_metadata() {
        $this->resetAfterTest(true);
    }
}
