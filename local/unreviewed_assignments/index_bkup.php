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
 * Lists all assignment submissions that require grading.
 *
 * @package   local_unreviewed_assignments
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once('../../config.php');

require_once("{$CFG->dirroot}/local/unreviewed_assignments/forms/filter.php");

require_login();

require_capability('local/unreviewed_assignments:view', context_system::instance());

$course = $DB->get_record('course', array('id' => 1), '*', MUST_EXIST);

$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_unreviewed_assignments'));
$PAGE->set_heading($course->fullname);
$PAGE->set_url(new moodle_url('/local/unreviewed_assignments/index.php'));
$PAGE->set_pagelayout('standard');

$city = optional_param('city', null, PARAM_TEXT);
$category = optional_param('category', null, PARAM_INT);

$wheres = array();
if (!empty($city)) {
    $wheres[] = "u.city = '$city'";
}
if (!empty($category)) {
    $wheres[] = "c.category = '$category'";
}

$where = '';
if ($wheres) {
    $where = ' AND ' . implode(' AND ', $wheres);
}

// Gets active submissions that require grading. This means the
// submission modification time is more recent than the grading
// modification time and the status is SUBMITTED.
$sql = "SELECT s.id, s.assignment, a.name, s.userid, u.firstname, u.lastname, cm.id cmid, c.id courseid, c.shortname,
    s.attemptnumber as sattemptnumber, g.attemptnumber as gattemptnumber
    FROM {assign_submission} s
    JOIN {assign} a ON a.id = s.assignment
    JOIN {user} u ON s.userid = u.id
    JOIN {course_modules} cm ON cm.instance = s.assignment
    JOIN {course} c ON c.id = cm.course
    JOIN {modules} m ON cm.module = m.id
    LEFT JOIN {assign_grades} g ON
        s.assignment = g.assignment AND
        s.userid = g.userid AND
        s.attemptnumber = g.attemptnumber
    WHERE m.name = 'assign'
    AND s.status = 'submitted'
    AND (
        s.timemodified > g.timemodified
        OR g.timemodified IS NULL
        OR g.grade IS NULL
        OR g.grade < 0
    )
    AND (
        s.attemptnumber = g.attemptnumber
        OR g.attemptnumber IS NULL
    )
    AND s.latest = 1
    AND c.visible = 1
	AND u.deleted <>1
    $where
    ORDER BY s.attemptnumber DESC, c.shortname";


$data = $DB->get_records_sql($sql);

$total = count($data);

$result = array();
foreach ($data as $row) {
    /**
     * Group the submissions by group and course module (cmid)
     *
     * This creates the following array:
     *
     * array(
     *   [course1] = array(
     *     [cmid1] => array(
     *       [submission1] => 'value',
     *       [submission2] => 'value',
     *     ),
     *   ),
     *   [course2] = array(
     *     [cmid2] => array(
     *       [submission1] => 'value',
     *       [submission2] => 'value',
     *   ),
     * )
     */
    $result[$row->shortname][$row->cmid][$row->id] = $row;
}

ksort($result);

$ordered_result = array();
foreach ($result as $coursename => $assignments) {
    foreach ($assignments as $assignmentid => $submissions) {
        foreach ($submissions as $submissionid => $submission) {
            $modinfo = get_fast_modinfo($submission->courseid);

            // The actual user-defined order of the assignments on the course.
            $correct_order = array_keys($modinfo->get_cms());

            // Order the results by the actual order.
            foreach ($correct_order as $id) {
                if (!isset($result[$coursename][$id][$submissionid])) {
                    // This module is not included in the data, so it is
                    // most likely not an assignment and must be skipped.
                    continue;
                    break;
                }

                $key = array_search($id, $correct_order);

                $ordered_result[$coursename][$key][] = $result[$coursename][$id][$submissionid];

                break;
            }
        }
    }
}

$list = '';
foreach ($ordered_result as $coursename => $assignments) {
    ksort($assignments);

    foreach ($assignments as $assignment) {
        $sublist = '';
        foreach ($assignment as $submission) {
            $sublist .= "<li>{$submission->firstname} {$submission->lastname}</li>";
        }

        $assignment_link = $OUTPUT->action_link(
            "/mod/assign/view.php?id={$submission->cmid}&action=grading",
            $submission->name
        );

        $count = count($assignment);

        $list .= "<li>$assignment_link ($count)<ul>$sublist</ul></li>";
    }
}

$mform = new local_unreviewed_assignments_form(null, array(), 'get');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('total', 'local_unreviewed_assignments', array('total' => $total)));
$mform->display();
echo "<ul>$list</ul>";
echo $OUTPUT->footer();
