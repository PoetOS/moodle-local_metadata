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
 * Base class for the customisable metadata fields.
 *
 * @package local_metadata
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\fieldtype;

defined('MOODLE_INTERNAL') || die;

class define_base {

    /** @var int */
    protected $contextlevel;

    /**
     * Constructor method.
     * @param int $fieldid id of the profile from the local_metadata_field table
     * @param int $instanceid id of the user for whom we are displaying data
     */
    public function __construct($contextlevel = CONTEXT_USER) {
        $this->contextlevel = $contextlevel;
    }

    /**
     * Prints out the form snippet for creating or editing a profile field
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form(&$form) {
        $form->addElement('header', '_commonsettings', get_string('profilecommonsettings', 'admin'));
        $this->define_form_common($form);

        $form->addElement('header', '_specificsettings', get_string('profilespecificsettings', 'admin'));
        $this->define_form_specific($form);
    }

    /**
     * Prints out the form snippet for the part of creating or editing a profile field common to all data types.
     *
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form_common(&$form) {

        $strrequired = get_string('required');

        $form->addElement('text', 'shortname', get_string('profileshortname', 'admin'), 'maxlength="100" size="25"');
        $form->addRule('shortname', $strrequired, 'required', null, 'client');
        $form->setType('shortname', PARAM_ALPHANUM);

        $form->addElement('text', 'name', get_string('profilename', 'admin'), 'size="50"');
        $form->addRule('name', $strrequired, 'required', null, 'client');
        $form->setType('name', PARAM_TEXT);

        $form->addElement('editor', 'description', get_string('profiledescription', 'admin'), null, null);

        $form->addElement('selectyesno', 'required', get_string('profilerequired', 'admin'));

        $form->addElement('selectyesno', 'locked', get_string('profilelocked', 'admin'));

        $form->addElement('selectyesno', 'forceunique', get_string('profileforceunique', 'admin'));

        $form->addElement('selectyesno', 'signup', get_string('profilesignup', 'admin'));

        $choices = [];
        $choices[PROFILE_VISIBLE_NONE]    = get_string('profilevisiblenone', 'admin');
        $choices[PROFILE_VISIBLE_PRIVATE] = get_string('profilevisibleprivate', 'admin');
        $choices[PROFILE_VISIBLE_ALL]     = get_string('profilevisibleall', 'admin');
        $form->addElement('select', 'visible', get_string('profilevisible', 'admin'), $choices);
        $form->addHelpButton('visible', 'profilevisible', 'admin');
        $form->setDefault('visible', PROFILE_VISIBLE_ALL);

        $choices = local_metadata_list_categories($this->contextlevel);
        $form->addElement('select', 'categoryid', get_string('profilecategory', 'admin'), $choices);
    }

    /**
     * Prints out the form snippet for the part of creating or editing a profile field specific to the current data type.
     * @param moodleform $form instance of the moodleform class
     */
    public function define_form_specific($form) {
        // Do nothing - overwrite if necessary.
    }

    /**
     * Validate the data from the add/edit profile field form.
     *
     * Generally this method should not be overwritten by child classes.
     *
     * @param stdClass|array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function define_validate($data, $files) {

        $data = (object)$data;
        $err = [];

        $err += $this->define_validate_common($data, $files);
        $err += $this->define_validate_specific($data, $files);

        return $err;
    }

    /**
     * Validate the data from the add/edit profile field form that is common to all data types.
     *
     * Generally this method should not be overwritten by child classes.
     *
     * @param stdClass|array $data from the add/edit profile field form
     * @param array $files
     * @return  array    associative array of error messages
     */
    public function define_validate_common($data, $files) {
        global $DB;

        $err = [];

        // Check the shortname was not truncated by cleaning.
        if (empty($data->shortname)) {
            $err['shortname'] = get_string('required');

        } else {
            // Fetch field-record from DB.
            $field = $DB->get_record('local_metadata_field', ['shortname' => $data->shortname]);
            // Check the shortname is unique.
            if ($field && ($field->id <> $data->id)) {
                $err['shortname'] = get_string('profileshortnamenotunique', 'admin');
            }
            // NOTE: since 2.0 the shortname may collide with existing fields in $USER because we load these fields into
            // $USER->profile array instead.
        }

        // No further checks necessary as the form class will take care of it.
        return $err;
    }

    /**
     * Validate the data from the add/edit profile field form
     * that is specific to the current data type
     * @param array $data
     * @param array $files
     * @return  array    associative array of error messages
     */
    public function define_validate_specific($data, $files) {
        // Do nothing - overwrite if necessary.
        return [];
    }

    /**
     * Alter form based on submitted or existing data
     * @param moodleform $mform
     */
    public function define_after_data(&$mform) {
        // Do nothing - overwrite if necessary.
    }

    /**
     * Add a new profile field or save changes to current field
     * @param array|stdClass $data from the add/edit profile field form
     */
    public function define_save($data) {
        global $DB;

        $data = $this->define_save_preprocess($data); // Hook for child classes.

        $old = false;
        if (!empty($data->id)) {
            $old = $DB->get_record('local_metadata_field', ['id' => (int)$data->id]);
        }

        // Check to see if the category has changed.
        if (!$old || ($old->categoryid != $data->categoryid)) {
            $data->sortorder = $DB->count_records('local_metadata_field', ['categoryid' => $data->categoryid]) + 1;
        }

        if (empty($data->id)) {
            unset($data->id);
            $data->id = $DB->insert_record('local_metadata_field', $data);
        } else {
            $DB->update_record('local_metadata_field', $data);
        }
    }

    /**
     * Preprocess data from the add/edit profile field form before it is saved.
     *
     * This method is a hook for the child classes to overwrite.
     *
     * @param array|stdClass $data from the add/edit profile field form
     * @return array|stdClass processed data object
     */
    public function define_save_preprocess($data) {
        // Do nothing - overwrite if necessary.
        return $data;
    }

    /**
     * Provides a method by which we can allow the default data in local_metadata_define_* to use an editor
     *
     * This should return an array of editor names (which will need to be formatted/cleaned)
     *
     * @return array
     */
    public function define_editors() {
        return [];
    }
}
