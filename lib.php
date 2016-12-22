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
 * @author Mike Churchward <mike.churchward@poetgroup.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2016 POET
 */

// Group context was dropped between 1.8 and 1.9. Use the old definition here.
define('CONTEXT_GROUP', 60);

// Cohort context has never existed. Define it here using the '9000' category.
define('CONTEXT_COHORT', 9000);

// Current contexts available. Woud be better handled in a main class and subplugin structure.
global $LOCALMETADATACONTEXTS;
$LOCALMETADATACONTEXTS = [
    CONTEXT_USER => 'user',
    CONTEXT_COURSE => 'course',
    CONTEXT_MODULE => 'module',
    CONTEXT_GROUP => 'group',
    CONTEXT_COHORT => 'cohort',
];


function local_metadata_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;

        default:
            return null;
    }
}

/**
 * Loads user profile field data into the user object.
 * @param stdClass $user
 */
function local_metadata_load_data($instance, $contextlevel) {
    global $DB;

    if ($fields = $DB->get_records('local_metadata_field', ['contextlevel' => $contextlevel])) {
        foreach ($fields as $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $formfield = new $newfield($field->id, $instance->id);
            $formfield->edit_load_instance_data($instance);
        }
    }
}

/**
 * Print out the customisable categories and fields for a users profile
 *
 * @param moodleform $mform instance of the moodleform class
 * @param int $instanceid id of user whose profile is being edited.
 */
function local_metadata_definition($mform, $instanceid = 0, $contextlevel) {
    global $DB;

    // If user is "admin" fields are displayed regardless.
    $update = has_capability('moodle/user:update', context_system::instance());

    if ($categories = $DB->get_records('local_metadata_category', ['contextlevel' => $contextlevel], 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {

                // Check first if *any* fields will be displayed.
                $display = false;
                foreach ($fields as $field) {
                    if ($field->visible != PROFILE_VISIBLE_NONE) {
                        $display = true;
                    }
                }

                // Display the header and the fields.
                if ($display || $update) {
                    $mform->addElement('header', 'category_'.$category->id, format_string($category->name));
                    foreach ($fields as $field) {
                        $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
                        $formfield = new $newfield($field->id, $instanceid);
                        $formfield->edit_field($mform);
                    }
                }
            }
        }
    }
}

/**
 * Adds profile fields to instance edit forms.
 * @param moodleform $mform
 * @param int $instanceid
 */
function local_metadata_definition_after_data($mform, $instanceid, $contextlevel) {
    global $DB;

    $instanceid = ($instanceid < 0) ? 0 : (int)$instanceid;

    if ($fields = $DB->get_records('local_metadata_field', ['contextlevel' => $contextlevel])) {
        foreach ($fields as $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $formfield = new $newfield($field->id, $instanceid);
            $formfield->edit_after_data($mform);
        }
    }
}

/**
 * Validates profile data.
 * @param stdClass $new
 * @param array $files
 * @return array
 */
function local_metadata_validation($new, $files, $contextlevel) {
    global $DB;

    if (is_array($new)) {
        $new = (object)$new;
    }

    $err = [];
    if ($fields = $DB->get_records('local_metadata_field', ['contextlevel' => $contextlevel])) {
        foreach ($fields as $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $formfield = new $newfield($field->id, $new->id);
            $err += $formfield->edit_validate_field($new, $files);
        }
    }
    return $err;
}

/**
 * Saves profile data for a instance.
 * @param stdClass $new
 */
function local_metadata_save_data($new, $contextlevel) {
    global $DB;

    if ($fields = $DB->get_records('local_metadata_field', ['contextlevel' => $contextlevel])) {
        foreach ($fields as $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $formfield = new $newfield($field->id, $new->id);
            $formfield->edit_save_data($new);
        }
    }
}

/**
 * Display profile fields.
 * @param int $instanceid
 */
function local_metadata_display_fields($instanceid, $contextlevel, $returnonly=false) {
    global $DB;

    $output = '';
    if ($categories = $DB->get_records('local_metadata_category', ['contextlevel' => $contextlevel], 'sortorder ASC')) {
        foreach ($categories as $category) {
            if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {
                foreach ($fields as $field) {
                    $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
                    $formfield = new $newfield($field->id, $instanceid);
                    if ($formfield->is_visible() && !$formfield->is_empty()) {
                        $output .= html_writer::tag('dt', format_string($formfield->field->name));
                        $output .= html_writer::tag('dd', $formfield->display_data());
                    }
                }
            }
        }
    }

    if (!$returnonly) {
        echo $output;
    } else {
        return $output;
    }
}

/**
 * Retrieves a list of profile fields that must be displayed in the sign-up form.
 * Specific to user profiles.
 *
 * @return array list of profile fields info
 * @since Moodle 3.2
 */
function local_metadata_get_signup_fields() {
    global $DB;

    $profilefields = [];
    // Only retrieve required custom fields (with category information)
    // results are sort by categories, then by fields.
    $sql = "SELECT uf.id as fieldid, ic.id as categoryid, ic.name as categoryname, uf.datatype
                FROM {local_metadata_field} uf
                JOIN {local_metadata_category} ic
                ON uf.categoryid = ic.id AND uf.signup = 1 AND uf.visible<>0
                WHERE uf.contextlevel = ?
                ORDER BY ic.sortorder ASC, uf.sortorder ASC";

    if ($fields = $DB->get_records_sql($sql, [CONTEXT_USER])) {
        foreach ($fields as $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $fieldobject = new $newfield($field->fieldid);

            $profilefields[] = (object)[
                'categoryid' => $field->categoryid,
                'categoryname' => $field->categoryname,
                'fieldid' => $field->fieldid,
                'datatype' => $field->datatype,
                'object' => $fieldobject
            ];
        }
    }
    return $profilefields;
}

/**
 * Adds code snippet to a moodle form object for custom profile fields that
 * should appear on the signup page
 * Specific to user profiles.
 * @param moodleform $mform moodle form object
 */
function local_metadata_signup_fields($mform) {

    if ($fields = local_metadata_get_signup_fields()) {
        foreach ($fields as $field) {
            // Check if we change the categories.
            if (!isset($currentcat) || $currentcat != $field->categoryid) {
                 $currentcat = $field->categoryid;
                 $mform->addElement('header', 'category_'.$field->categoryid, format_string($field->categoryname));
            };
            $field->object->edit_field($mform);
        }
    }
}

/**
 * Returns an object with the custom profile fields set for the given user
 * Specific to user profiles.
 * @param integer $instanceid
 * @param bool $onlyinuserobject True if you only want the ones in $USER.
 * @return stdClass
 */
function local_metadata_user_record($instanceid, $onlyinuserobject = true) {
    global $DB;

    $usercustomfields = new \stdClass();

    if ($fields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_USER])) {
        foreach ($fields as $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $formfield = new $newfield($field->id, $instanceid);
            if (!$onlyinuserobject || $formfield->is_instance_object_data()) {
                $usercustomfields->{$field->shortname} = $formfield->data;
            }
        }
    }

    return $usercustomfields;
}

/**
 * Obtains a list of all available custom profile fields, indexed by id.
 *
 * Some profile fields are not included in the user object data (see
 * local_metadata_user_record function above). Optionally, you can obtain only those
 * fields that are included in the user object.
 *
 * To be clear, this function returns the available fields, and does not
 * return the field values for a particular user.
 *
 * @param bool $onlyinuserobject True if you only want the ones in $USER
 * @return array Array of field objects from database (indexed by id)
 * @since Moodle 2.7.1
 */
function local_metadata_get_custom_fields($contextlevel, $onlyinuserobject = false) {
    global $DB;

    // Get all the fields.
    $fields = $DB->get_records('local_metadata_field', ['contextlevel' => $contextlevel], 'id ASC');

    // If only doing the user object ones, unset the rest.
    if ($onlyinuserobject) {
        foreach ($fields as $id => $field) {
            $newfield = "\\local_metadata\\metadata\\{$field->datatype}\\metadata";
            $formfield = new $newfield();
            if (!$formfield->is_instance_object_data()) {
                unset($fields[$id]);
            }
        }
    }

    return $fields;
}

/**
 * Does the user have all required custom fields set?
 *
 * Internal, to be exclusively used by {@link user_not_fully_set_up()} only.
 *
 * Note that if users have no way to fill a required field via editing their
 * profiles (e.g. the field is not visible or it is locked), we still return true.
 * So this is actually checking if we should redirect the user to edit their
 * profile, rather than whether there is a value in the database.
 *
 * @param int $instanceid
 * @return bool
 */
function local_metadata_has_required_custom_fields_set($instanceid, $contextlevel) {
    global $DB;

    $sql = "SELECT f.id
              FROM {local_metadata_field} f
         LEFT JOIN {local_metadata} d ON (d.fieldid = f.id AND d.instanceid = ?)
             WHERE f.contextlevel = ? AND f.required = 1 AND f.visible > 0 AND f.locked = 0 AND d.id IS NULL";

    if ($DB->record_exists_sql($sql, [$instanceid, $contextlevel])) {
        return false;
    }

    return true;
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return local_metadata\output\inplace_editable
 */
function local_metadata_inplace_editable($itemtype, $itemid, $newvalue) {
    \external_api::validate_context(\context_system::instance());
    if ($itemtype === 'categoryname') {
        return local_metadata\output\categoryname::update($itemid, $newvalue);
    }
}

/**
 * Hook function that is called when settings blocks are being built.
 */
function local_metadata_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    if ($context->contextlevel == CONTEXT_MODULE) {
        // Only add this settings item on non-site course pages.
        if ($PAGE->course && ($PAGE->course->id != 1) &&
            (get_config('local_metadata', 'modulemetadataenabled') == 1) &&
            has_capability('moodle/course:manageactivities', $context)) {

            if ($settingnode = $settingsnav->find('modulesettings', settings_navigation::TYPE_SETTING)) {
                $strmetadata = get_string('metadatafor', 'local_metadata');
                $url = new moodle_url('/local/metadata/index.php',
                    ['id' => $context->instanceid, 'action' => 'moduledata', 'contextlevel' => CONTEXT_MODULE]);
                $metadatanode = navigation_node::create(
                    $strmetadata,
                    $url,
                    navigation_node::NODETYPE_LEAF,
                    'metadata',
                    'metadata',
                    new pix_icon('i/settings', $strmetadata)
                );
                if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                    $metadatanode->make_active();
                }
                $settingnode->add_node($metadatanode);
            }
        }
    } else if ($PAGE->pagetype == 'cohort-edit') {
        if ((get_config('local_metadata', 'cohortmetadataenabled') == 1) &&
            has_capability('moodle/cohort:manage', $context)) {

            if ($settingnode = $settingsnav->find('cohorts', settings_navigation::TYPE_SETTING)) {
                $strmetadata = get_string('metadatafor', 'local_metadata');
                $cohortid = $PAGE->url->param('id');
                $url = new moodle_url('/local/metadata/index.php',
                    ['id' => $cohortid, 'action' => 'cohortdata', 'contextlevel' => CONTEXT_COHORT]);
                $metadatanode = navigation_node::create(
                    $strmetadata,
                    $url,
                    navigation_node::NODETYPE_LEAF,
                    'metadata',
                    'metadata',
                    new pix_icon('i/settings', $strmetadata)
                );
                if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                    $metadatanode->make_active();
                }
                $settingnode->add_node($metadatanode);
            }
        }
    } else if ($PAGE->pagetype == 'group-group') {
        if ((get_config('local_metadata', 'groupmetadataenabled') == 1) &&
            has_capability('moodle/course:managegroups', $context)) {

            if ($settingnode = $settingsnav->find('groups', settings_navigation::TYPE_SETTING)) {
                $strmetadata = get_string('metadatafor', 'local_metadata');
                $groupid = $PAGE->url->param('id');
                $url = new moodle_url('/local/metadata/index.php',
                    ['id' => $groupid, 'action' => 'groupdata', 'contextlevel' => CONTEXT_GROUP]);
                $metadatanode = navigation_node::create(
                    $strmetadata,
                    $url,
                    navigation_node::NODETYPE_LEAF,
                    'metadata',
                    'metadata',
                    new pix_icon('i/settings', $strmetadata)
                );
                if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                    $metadatanode->make_active();
                }
                $settingnode->add_node($metadatanode);
            }
        }
    }
}

/**
 * Hook function that is called when user profile page is being built.
 */
function local_metadata_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (get_config('local_metadata', 'usermetadataenabled') == 1) {
        $content = local_metadata_display_fields($user->id, CONTEXT_USER, true);
        $node = new \core_user\output\myprofile\node('contact', 'metadata',
            get_string('metadata', 'local_metadata'), null, null, $content);
        $tree->add_node($node);
    }
}

/**
 * Hook function to extend the course settings navigation.
 */
function local_metadata_extend_navigation_course($parentnode, $course, $context) {
    if ((get_config('local_metadata', 'coursemetadataenabled') == 1) &&
        has_capability('moodle/course:create', $context)) {
        $strmetadata = get_string('metadata', 'local_metadata');
        $url = new moodle_url('/local/metadata/index.php',
            ['id' => $course->id, 'action' => 'coursedata', 'contextlevel' => CONTEXT_COURSE]);
        $metadatanode = navigation_node::create($strmetadata, $url, navigation_node::NODETYPE_LEAF,
            'metadata', 'metadata', new pix_icon('i/settings', $strmetadata)
        );
        $parentnode->add_node($metadatanode);
    }
}

/**
 * This function extends the navigation with the metadata for user settings node.
 *
 * @param navigation_node $navigation  The navigation node to extend
 * @param stdClass        $user        The user object
 * @param context         $usercontext The context of the user
 * @param stdClass        $course      The course to object for the tool
 * @param context         $coursecontext     The context of the course
 */
function local_metadata_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    global $USER, $SITE;

    if ((get_config('local_metadata', 'usermetadataenabled') == 1) &&
        (($USER->id == $user->id) || has_capability('moodle/user:editprofile', $usercontext))) {

        $strmetadata = get_string('usermetadata', 'local_metadata');
        $url = new moodle_url('/local/metadata/index.php',
            ['id' => $user->id, 'action' => 'userdata', 'contextlevel' => CONTEXT_USER]);
        $metadatanode = navigation_node::create($strmetadata, $url, navigation_node::NODETYPE_LEAF,
            'metadata', 'metadata', new pix_icon('i/settings', $strmetadata)
        );
        $navigation->add_node($metadatanode);
    }
}