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
 * Form for filtering the assignment list by course category and city.
 *
 * @package   local_unreviewed_assignments
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir. '/coursecatlib.php');

class local_unreviewed_assignments_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $categories = array(get_string('choose'));
        $categories += coursecat::make_categories_list();

        $cities = $DB->get_records_sql('SELECT DISTINCT city FROM {user} WHERE city != ""');

        $city_options[''] = get_string('choose');
        foreach ($cities as $entry) {
            $city_options[$entry->city] = $entry->city;
        }

        $mform->addElement('select', 'category', get_string('category'), $categories, null);
        $mform->addElement('select', 'city', get_string('city'), $city_options, null);

        // Do not display cancel button (1st param), Change submit button label (2nd param).
        $this->add_action_buttons(false, get_string('filter'));
    }
}
