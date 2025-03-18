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
 * Add link to main navigation.
 *
 * @package   local_unreviewed_assignments
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/lib.php');
function local_unreviewed_assignments_extend_navigation( global_navigation $navigation) {
    GLOBAL $PAGE,$ADMIN;

	if (!isloggedin()) {
        return;
    }

    if (!has_capability('local/unreviewed_assignments:view', context_system::instance())) {
        return;
    }
	  $unreviewed_assignmentnode = navigation_node::create(get_string('pluginname', 'local_unreviewed_assignments'),
                                        new moodle_url('/local/unreviewed_assignments/index.php'), // We have to add a URL to the course node,
                                                                                        // otherwise the node wouldn't be added to
                                                                                        // the flat navigation by Boost.
                                                                                        // There is no better choice than the course
                                                                                        // home page.
                        global_navigation::TYPE_ROOTNODE,
                        null,
                        'localboostnavigationcoursesections',
                        null);

    
	$unreviewed_assignmentnode->showinflatnavigation = true;
	$navigation->add_node($unreviewed_assignmentnode);
	//$unreviewed_assignmentnode->make_active();
	//print_r($navigation);
	//print_r($navigation);
}
