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

defined('MOODLE_INTERNAL') || die;

/**
 * Base class for the customisable metadata fields.
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Reorder the profile fields within a given category starting at the field at the given startorder.
 */
function local_metadata_reorder_fields() {
    global $DB;

    if ($categories = $DB->get_records('local_metadata_category')) {
        foreach ($categories as $category) {
            $i = 1;
            if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {
                foreach ($fields as $field) {
                    $f = new stdClass();
                    $f->id = $field->id;
                    $f->sortorder = $i++;
                    $DB->update_record('local_metadata_field', $f);
                }
            }
        }
    }
}

/**
 * Reorder the profile categoriess starting at the category at the given startorder.
 * @param int $contextlevel The context of category to work on.
 */
function local_metadata_reorder_categories($contextlevel) {
    global $DB;

    $i = 1;
    if ($categories = $DB->get_records('local_metadata_category', ['contextlevel' => $contextlevel], 'sortorder ASC')) {
        foreach ($categories as $cat) {
            $c = new stdClass();
            $c->id = $cat->id;
            $c->sortorder = $i++;
            $DB->update_record('local_metadata_category', $c);
        }
    }
}

/**
 * Delete a profile category
 * @param int $id of the category to be deleted
 * @return bool success of operation
 */
function local_metadata_delete_category($id) {
    global $DB;

    // Retrieve the category.
    if (!$category = $DB->get_record('local_metadata_category', ['id' => $id])) {
        print_error('invalidcategoryid');
    }

    if (!$categories = $DB->get_records('local_metadata_category', ['contextlevel' => $category->contextlevel], 'sortorder ASC')) {
        print_error('nocate', 'debug');
    }

    unset($categories[$category->id]);

    if (!count($categories)) {
        return false; // We can not delete the last category.
    }

    // Does the category contain any fields.
    if ($DB->count_records('local_metadata_field', ['categoryid' => $category->id])) {
        if (array_key_exists($category->sortorder - 1, $categories)) {
            $newcategory = $categories[$category->sortorder - 1];
        } else if (array_key_exists($category->sortorder + 1, $categories)) {
            $newcategory = $categories[$category->sortorder + 1];
        } else {
            $newcategory = reset($categories); // Get first category if sortorder broken.
        }

        $sortorder = $DB->count_records('local_metadata_field', ['categoryid' => $newcategory->id]) + 1;

        if ($fields = $DB->get_records('local_metadata_field', ['categoryid' => $category->id], 'sortorder ASC')) {
            foreach ($fields as $field) {
                $f = new stdClass();
                $f->id = $field->id;
                $f->sortorder = $sortorder++;
                $f->categoryid = $newcategory->id;
                $DB->update_record('local_metadata_field', $f);
            }
        }
    }

    // Finally we get to delete the category.
    $DB->delete_records('local_metadata_category', ['id' => $category->id]);
    local_metadata_reorder_categories($category->contextlevel);
    return true;
}

/**
 * Deletes a profile field.
 * @param int $id
 */
function local_metadata_delete_field($id) {
    global $DB;

    // Remove any user data associated with this field.
    if (!$DB->delete_records('local_metadata', ['fieldid' => $id])) {
        print_error('cannotdeletecustomfield');
    }

    // Note: Any availability conditions that depend on this field will remain,
    // but show the field as missing until manually corrected to something else.

    // Need to rebuild course cache to update the info.
    rebuild_course_cache(0, true);

    // Try to remove the record from the database.
    $DB->delete_records('local_metadata_field', ['id' => $id]);

    // Reorder the remaining fields in the same category.
    local_metadata_reorder_fields();
}

/**
 * Change the sort order of a field
 *
 * @param int $id of the field
 * @param string $move direction of move
 * @return bool success of operation
 */
function local_metadata_move_field($id, $move) {
    global $DB;

    // Get the field object.
    if (!$field = $DB->get_record('local_metadata_field', ['id' => $id], 'id, sortorder, categoryid')) {
        return false;
    }
    // Count the number of fields in this category.
    $fieldcount = $DB->count_records('local_metadata_field', ['categoryid' => $field->categoryid]);

    // Calculate the new sortorder.
    if (($move == 'up') && ($field->sortorder > 1)) {
        $neworder = $field->sortorder - 1;
    } else if (($move == 'down') && ($field->sortorder < $fieldcount)) {
        $neworder = $field->sortorder + 1;
    } else {
        return false;
    }

    // Retrieve the field object that is currently residing in the new position.
    $params = ['categoryid' => $field->categoryid, 'sortorder' => $neworder];
    if ($swapfield = $DB->get_record('local_metadata_field', $params, 'id, sortorder')) {

        // Swap the sortorders.
        $swapfield->sortorder = $field->sortorder;
        $field->sortorder     = $neworder;

        // Update the field records.
        $DB->update_record('local_metadata_field', $field);
        $DB->update_record('local_metadata_field', $swapfield);
    }

    local_metadata_reorder_fields();
    return true;
}

/**
 * Change the sort order of a category.
 *
 * @param int $id of the category
 * @param string $move direction of move
 * @return bool success of operation
 */
function local_metadata_move_category($id, $move) {
    global $DB;
    // Get the category object.
    if (!($category = $DB->get_record('local_metadata_category', ['id' => $id], 'id, contextlevel, sortorder'))) {
        return false;
    }

    // Count the number of categories.
    $categorycount = $DB->count_records('local_metadata_category', ['contextlevel' => $category->contextlevel]);

    // Calculate the new sortorder.
    if (($move == 'up') && ($category->sortorder > 1)) {
        $neworder = $category->sortorder - 1;
    } else if (($move == 'down') && ($category->sortorder < $categorycount)) {
        $neworder = $category->sortorder + 1;
    } else {
        return false;
    }

    // Retrieve the category object that is currently residing in the new position.
    if ($swapcategory = $DB->get_record('local_metadata_category',
            ['contextlevel' => $category->contextlevel, 'sortorder' => $neworder], 'id, contextlevel, sortorder')) {

        // Swap the sortorders.
        $swapcategory->sortorder = $category->sortorder;
        $category->sortorder     = $neworder;

        // Update the category records.
        $DB->update_record('local_metadata_category', $category) && $DB->update_record('local_metadata_category', $swapcategory);
        return true;
    }

    return false;
}

/**
 * Retrieve a list of all the available data types
 * @return   array   a list of the datatypes suitable to use in a select statement
 */
function local_metadata_list_datatypes() {
    $datatypes = [];
    $fieldtypeplugins = core_component::get_plugin_list('metadatafieldtype');
    foreach ($fieldtypeplugins as $fieldtypename => $fieldtypelocation) {
        $classname = "\\metadatafieldtype_{$fieldtypename}\\metadata";
        $newdatatype = new $classname();
        $datatypes[$fieldtypename] = $newdatatype->name;
    }
    asort($datatypes);

    return $datatypes;
}

/**
 * Retrieve a list of categories and ids suitable for use in a form
 * @param int $contextlevel The context level to retrieve categories for.
 * @return   array
 */
function local_metadata_list_categories($contextlevel) {
    global $DB;
    $categories = $DB->get_records_menu('local_metadata_category', ['contextlevel' => $contextlevel], 'sortorder ASC', 'id, name');
    return array_map('format_string', $categories);
}

/**
 * Edit a category
 *
 * @param int $id
 * @param string $redirect
 */
function local_metadata_edit_category($id, $redirect, $contextlevel) {
    global $DB, $OUTPUT;

    $categoryform = new local_metadata\forms\category_form();

    if (!($category = $DB->get_record('local_metadata_category', ['id' => $id]))) {
        $category = new stdClass();
        $category->contextlevel = $contextlevel;
    }
    $categoryform->set_data($category);

    if ($categoryform->is_cancelled()) {
        redirect($redirect);
    } else {
        if ($data = $categoryform->get_data()) {
            if (empty($data->id)) {
                unset($data->id);
                $data->sortorder = $DB->count_records('local_metadata_category', ['contextlevel' => $contextlevel]) + 1;
                $DB->insert_record('local_metadata_category', $data, false);
            } else {
                $DB->update_record('local_metadata_category', $data);
            }
            local_metadata_reorder_categories($category->contextlevel);
            redirect($redirect);

        }

        if (empty($id)) {
            $strheading = get_string('profilecreatenewcategory', 'admin');
        } else {
            $strheading = get_string('profileeditcategory', 'admin', format_string($category->name));
        }

        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $categoryform->display();
        echo $OUTPUT->footer();
    }

}

/**
 * Edit a profile field.
 *
 * @param int $id
 * @param string $datatype
 * @param string $redirect
 * @param int $contextlevel
 */
function local_metadata_edit_field($id, $datatype, $redirect, $contextlevel) {
    global $DB, $OUTPUT, $PAGE;

    if (!$field = $DB->get_record('local_metadata_field', ['id' => $id])) {
        $field = new stdClass();
        $field->contextlevel = $contextlevel;
        $field->datatype = $datatype;
        $field->description = '';
        $field->descriptionformat = FORMAT_HTML;
        $field->defaultdata = '';
        $field->defaultdataformat = FORMAT_HTML;
    }

    // Clean and prepare description for the editor.
    $field->description = clean_text($field->description, $field->descriptionformat);
    $field->description = ['text' => $field->description, 'format' => $field->descriptionformat, 'itemid' => 0];

    $fieldform = new local_metadata\forms\field_form(null, ['datatype' => $field->datatype, 'contextlevel' => $contextlevel]);

    // Convert the data format for.
    if (is_array($fieldform->editors())) {
        foreach ($fieldform->editors() as $editor) {
            if (isset($field->$editor)) {
                $field->$editor = clean_text($field->$editor, $field->{$editor.'format'});
                $field->$editor = ['text' => $field->$editor, 'format' => $field->{$editor.'format'}, 'itemid' => 0];
            }
        }
    }

    $fieldform->set_data($field);

    if ($fieldform->is_cancelled()) {
        redirect($redirect);

    } else {
        if ($data = $fieldform->get_data()) {
            $newfield = "\\metadatafieldtype_{$datatype}\\define";
            $formfield = new $newfield($contextlevel);

            // Collect the description and format back into the proper data structure from the editor.
            // Note: This field will ALWAYS be an editor.
            $data->descriptionformat = $data->description['format'];
            $data->description = $data->description['text'];

            // Check whether the default data is an editor, this is (currently) only the textarea field type.
            if (is_array($data->defaultdata) && array_key_exists('text', $data->defaultdata)) {
                // Collect the default data and format back into the proper data structure from the editor.
                $data->defaultdataformat = $data->defaultdata['format'];
                $data->defaultdata = $data->defaultdata['text'];
            }

            // Convert the data format for.
            if (is_array($fieldform->editors())) {
                foreach ($fieldform->editors() as $editor) {
                    if (isset($field->$editor)) {
                        $field->{$editor.'format'} = $field->{$editor}['format'];
                        $field->$editor = $field->{$editor}['text'];
                    }
                }
            }

            $formfield->define_save($data);
            local_metadata_reorder_fields();
            local_metadata_reorder_categories($contextlevel);
            redirect($redirect);
        }

        $datatypes = local_metadata_list_datatypes();

        if (empty($id)) {
            $strheading = get_string('profilecreatenewfield', 'admin', $datatypes[$datatype]);
        } else {
            $strheading = get_string('profileeditfield', 'admin', format_string($field->name));
        }

        // Print the page.
        $PAGE->navbar->add($strheading);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $fieldform->display();
        echo $OUTPUT->footer();
    }
}