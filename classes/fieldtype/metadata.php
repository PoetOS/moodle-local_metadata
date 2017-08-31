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

class metadata {

    // These 2 variables are really what we're interested in.
    // Everything else can be extracted from them.

    /** @var int */
    public $fieldid;

    /** @var int */
    public $instanceid;

    /** @var stdClass */
    public $field;

    /** @var string */
    public $inputname;

    /** @var mixed */
    public $data;

    /** @var string */
    public $dataformat;

    /** @var string */
    protected $name;

    /**
     * Constructor method.
     * @param int $fieldid id of the profile from the local_metadata_field table
     * @param int $instanceid id of the instance for whom we are displaying data
     * @param object $fielddata optional data for the field object.
     */
    public function __construct($fieldid=0, $instanceid=0, $fielddata=null) {
        $this->set_fieldid($fieldid);
        $this->set_instanceid($instanceid);
        $this->load_data($fielddata);

        // Set the name for display. The fieldtype subplugin should have the same name as the namespace.
        $classname = get_class($this);
        $subpluginname = substr($classname, 0, strrpos($classname, '\\'));
        if (strpos($subpluginname, 'metadatafieldtype_') !== false) {
            $this->name = get_string('displayname', $subpluginname);
        } else {
            $this->name = '-- unknown --';
        }
    }

    /**
     * Abstract method: Adds the profile field to the moodle form class
     * @abstract The following methods must be overwritten by child classes
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_add($mform) {
        print_error('mustbeoveride', 'debug', '', 'edit_field_add');
    }

    /**
     * Display the data for this field
     * @return string
     */
    public function display_data() {
        $options = new \stdClass();
        $options->para = false;
        return format_text($this->data, FORMAT_MOODLE, $options);
    }

    /**
     * Print out the form field in the edit profile page
     * @param moodleform $mform instance of the moodleform class
     * @return bool
     */
    public function edit_field($mform) {
        if (($this->field->visible != PROFILE_VISIBLE_NONE) ||
            (($this->field->contextlevel == CONTEXT_USER) && has_capability('moodle/user:update', \context_system::instance()))) {

            $this->edit_field_add($mform);
            $this->edit_field_set_default($mform);
            $this->edit_field_set_required($mform);
            return true;
        }
        return false;
    }

    /**
     * Tweaks the edit form
     * @param moodleform $mform instance of the moodleform class
     * @return bool
     */
    public function edit_after_data($mform) {
        if (($this->field->visible != PROFILE_VISIBLE_NONE) ||
            (($this->field->contextlevel == CONTEXT_USER) && has_capability('moodle/user:update', \context_system::instance()))) {
            $this->edit_field_set_locked($mform);
            return true;
        }
        return false;
    }

    /**
     * Saves the data coming from form
     * @param stdClass $new data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    public function edit_save_data($new) {
        global $DB;

        if (!isset($new->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        $data = new \stdClass();

        $new->{$this->inputname} = $this->edit_save_data_preprocess($new->{$this->inputname}, $data);

        $data->instanceid  = $new->id;
        $data->fieldid = $this->field->id;
        $data->data    = $new->{$this->inputname};

        if ($dataid = $DB->get_field('local_metadata', 'id', ['instanceid' => $data->instanceid, 'fieldid' => $data->fieldid])) {
            $data->id = $dataid;
            $DB->update_record('local_metadata', $data);
        } else {
            $DB->insert_record('local_metadata', $data);
        }
    }

    /**
     * Validate the form field from profile page
     *
     * @param stdClass $new
     * @return  string  contains error message otherwise null
     */
    public function edit_validate_field($new) {
        global $DB;

        $errors = [];
        // Get input value.
        if (isset($new->{$this->inputname})) {
            if (is_array($new->{$this->inputname}) && isset($new->{$this->inputname}['text'])) {
                $value = $new->{$this->inputname}['text'];
            } else {
                $value = $new->{$this->inputname};
            }
        } else {
            $value = '';
        }

        // Check for uniqueness of data if required.
        if ($this->is_unique() && (($value !== '') || $this->is_required())) {
            $data = $DB->get_records_sql('
                    SELECT id, instanceid
                      FROM {local_metadata}
                     WHERE fieldid = ?
                       AND ' . $DB->sql_compare_text('data', 255) . ' = ' . $DB->sql_compare_text('?', 255),
                    [$this->field->id, $value]);
            if ($data) {
                $existing = false;
                foreach ($data as $v) {
                    if ($v->instanceid == $new->id) {
                        $existing = true;
                        break;
                    }
                }
                if (!$existing) {
                    $errors[$this->inputname] = get_string('valuealreadyused');
                }
            }
        }
        return $errors;
    }

    /**
     * Sets the default data for the field in the form object
     * @param  moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_default($mform) {
        if (!empty($this->field->defaultdata)) {
            $mform->setDefault($this->inputname, $this->field->defaultdata);
        }
    }

    /**
     * Sets the required flag for the field in the form object
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_required($mform) {
        global $USER;
        // Handling for specific contexts. TODO - Abstract this.
        if ($this->is_required() &&
            (($this->field->contextlevel != CONTEXT_USER) ||
            ($this->instanceid == $USER->id || isguestuser()))) {
            $mform->addRule($this->inputname, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * HardFreeze the field if locked.
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked() &&
            (($this->field->contextlevel == CONTEXT_USER) && !has_capability('moodle/user:update', context_system::instance()))) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, $this->data);
        }
    }

    /**
     * Hook for child classess to process the data before it gets saved in database
     * @param stdClass $data
     * @param stdClass $datarecord The object that will be used to save the record
     * @return  mixed
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        return $data;
    }

    /**
     * Loads a instance object with data for this field ready for the edit profile
     * form
     * @param stdClass $instance a context object
     */
    public function edit_load_instance_data($instance) {
        if ($this->data !== null) {
            $instance->{$this->inputname} = $this->data;
        }
    }

    /**
     * Check if the field data should be loaded into the instance object
     * By default it is, but for field types where the data may be potentially
     * large, the child class should override this and return false
     * @return bool
     */
    public function is_instance_object_data() {
        return true;
    }

    /**
     * Accessor method: set the instanceid for this instance
     * @internal This method should not generally be overwritten by child classes.
     * @param integer $instanceid id from the instance table
     */
    public function set_instanceid($instanceid) {
        $this->instanceid = $instanceid;
    }

    /**
     * Accessor method: set the fieldid for this instance
     * @internal This method should not generally be overwritten by child classes.
     * @param integer $fieldid id from the local_metadata_field table
     */
    public function set_fieldid($fieldid) {
        $this->fieldid = $fieldid;
    }

    /**
     * Accessor method: Load the field record and instance data associated with the
     * object's fieldid and instanceis
     * @internal This method should not generally be overwritten by child classes.
     * @param object $fielddata Optional data for the field object.
     */
    public function load_data($fielddata=null) {
        global $DB;

        // If no data passed in, and we know the field id, get it from the database.
        if (($fielddata === null) && ($this->fieldid != 0)) {
            $fielddata = $DB->get_record('local_metadata_field', ['id' => $this->fieldid]);
        }
        if (!$fielddata) {
            $this->field = null;
            $this->inputname = '';
        } else {
            $this->field = $fielddata;
            $this->inputname = 'local_metadata_field_'.$fielddata->shortname;
        }

        if (!empty($this->field)) {
            if (!empty($fielddata->data)) {
                // If the instance data is included, just add it.
                $this->data = $fielddata->data;
                $this->dataformat = $fielddata->dataformat;
            } else {
                // Check for instance data and include it if present. Otherwise set to default.
                $params = ['instanceid' => $this->instanceid, 'fieldid' => $this->fieldid];
                if ($data = $DB->get_record('local_metadata', $params, 'data, dataformat')) {
                    $this->data = $data->data;
                    $this->dataformat = $data->dataformat;
                } else {
                    $this->data = $this->field->defaultdata;
                    $this->dataformat = FORMAT_HTML;
                }
            }
        } else {
            $this->data = null;
        }
    }

    /**
     * Check if the field data is visible to the current user
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_visible() {
        global $USER;

        switch ($this->field->visible) {
            case PROFILE_VISIBLE_ALL:
                return true;
            case PROFILE_VISIBLE_PRIVATE:
                if ($this->userid == $USER->id) {
                    return true;
                } else {
                    return (($this->field->contextlevel != CONTEXT_USER) ||
                            has_capability('moodle/user:viewalldetails', \context_user::instance($this->userid)));
                }
            default:
                return (($this->field->contextlevel != CONTEXT_USER) ||
                        has_capability('moodle/user:viewalldetails', \context_user::instance($this->userid)));
        }
    }

    /**
     * Check if the field data is considered empty
     * @internal This method should not generally be overwritten by child classes.
     * @return boolean
     */
    public function is_empty() {
        return (($this->data != '0') && empty($this->data));
    }

    /**
     * Check if the field is required on the edit profile page
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_required() {
        return (boolean)$this->field->required;
    }

    /**
     * Check if the field is locked on the edit profile page
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_locked() {
        return (boolean)$this->field->locked;
    }

    /**
     * Check if the field data should be unique
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_unique() {
        return (boolean)$this->field->forceunique;
    }

    /**
     * Check if the field should appear on the signup page
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_signup_field() {
        return (boolean)$this->field->signup;
    }

    /**
     * Return the field settings suitable to be exported via an external function.
     * By default it return all the field settings.
     *
     * @return array all the settings
     * @since Moodle 3.2
     */
    public function get_field_config_for_external() {
        return (array)$this->field;
    }

    /**
     * Return the field type and null properties.
     * This will be used for validating the data submitted by a user.
     *
     * @return array the param type and null property
     * @since Moodle 3.2
     */
    public function get_field_properties() {
        return [PARAM_RAW, NULL_NOT_ALLOWED];
    }

    /**
     * Magic method for getting properties.
     * @param string $name
     * @return mixed
     * @throws \coding_exception
     */
    public function __get($name) {
        $allowed = ['name'];
        if (in_array($name, $allowed)) {
            return $this->$name;
        } else {
            throw new \coding_exception($name.' is not a publicly accessible property of '.get_class($this));
        }
    }
}