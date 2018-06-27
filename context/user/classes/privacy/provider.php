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
 * @subpackage metadatacontext_user
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */

namespace metadatacontext_user\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin_provider interface.
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection $items The collection to add metadata to.
     * @return  collection  The array of metadata
     */
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {

        // Add all of the relevant tables and fields to the collection.
        $collection->add_database_table('local_metadata', [
            'instanceid' => 'privacy:metadata:userid',
            'fieldid' => 'privacy:metadata:fieldid',
            'data' => 'privacy:metadata:data',
        ], 'privacy:metadata:local_metadata');

        $collection->add_database_table('local_metadata_field', [
            'name' => 'privacy:metadata:fieldname',
            'description' => 'privacy:metadata:fielddescription',
        ], 'privacy:metadata:local_metadata_field');

        return $collection;
    }

    /**
     * For this plugin, the only context that is relevant is the specific user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
        $contextlist->add_user_context($userid);
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(\core_privacy\local\request\approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        $exportdata = new \stdClass();
        $exportdata->usermetadata = [];
        $metadatars = self::get_user_metdata_rs($user->id);
        foreach ($metadatars as $datum) {
            $exportdata->usermetadata[] = [
                get_string('metadataname', 'local_metadata') => $datum->name,
                get_string('metadatadescription', 'local_metadata') => $datum->description,
                get_string('metadatadata', 'local_metadata') => $datum->data,
            ];
        }
        $metadatars->close();

        $context = \context_user::instance($user->id);
        \core_privacy\local\request\writer::with_context($context)->export_data([get_string('metadatatitle',
            'metadatacontext_user')], $exportdata);
    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // The only relevant context for this function is the user, so nothing really to do here.
        return true;
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(\core_privacy\local\request\approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!($context instanceof \context_user) || ($context->instanceid != $userid)) {
                continue;
            }

            $metadatars = self::get_user_metdata_rs($userid);
            foreach ($metadatars as $datum) {
                $DB->delete_records('local_metadata', ['id' => $datum->id]);
            }
            $metadatars->close();
        }
    }

    /**
     * Helper function to get all user metadata as a recordset.
     *
     * @param int $userid The database id of the user.
     * @return moodle_recordset
     */
    private static function get_user_metdata_rs(int $userid): \moodle_recordset {
        global $DB;

        $sql = "SELECT lm.id, lm.data, lmf.name, lmf.description
                  FROM {local_metadata_field} lmf
            INNER JOIN {local_metadata} lm ON lm.fieldid = lmf.id
                 WHERE lmf.contextlevel = :contextlevel AND lm.instanceid = :userid";
        $params = ['contextlevel' => CONTEXT_USER, 'userid' => $userid];
        return $DB->get_recordset_sql($sql, $params);
    }
}