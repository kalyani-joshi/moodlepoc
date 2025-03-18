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
 * CLI cron
 *
 * This script looks through all the module directories for cron.php files
 * and runs them.  These files can contain cleanup functions, email functions
 * or anything that needs to be run on a regular basis.
 *
 * @package    core
 * @subpackage cli
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/cronlib.php');

$date = date('Format String', time());
$con =mysqli_connect('127.0.0.1','kauppiasval', 'h8t0yovycqt8maaq','devkauppiasvalmennus');
$myArray = $con->query ("SELECT * FROM devkauppiasvalmennus.mdl_assign_grades where id > 10000 and grade=-1.00000 and grader != -1;");

if($myArray->num_rows){
	$fp = fopen("../deleted_files/deleted_gradebook_item_$date.csv", 'w');
	foreach ($myArray as $line) {
		fputcsv($fp, $line,";");
		fseek($fp, -1, SEEK_CUR);
		fwrite($fp, "\r\n");
}

	fclose($fp);
	
	$runpurge = $con->query ("update devkauppiasvalmennus.mdl_assign_grades set grade=0.00000 where id>10000 and grade=-1.00000 and grader != -1;");
}

?>