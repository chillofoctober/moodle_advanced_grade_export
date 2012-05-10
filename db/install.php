<?php

/**
 * This file replaces the legacy STATEMENTS section in db/install.xml,
 * lib.php/modulename_install() post installation hook and partially defaults.php
 *
 * @package    
 * @subpackage advanced_grade_export
 * @copyright  2012 Eugene Shwab <chillofoctober@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 */
function xmldb_gradeexport_advanced_grade_export_install() {
    global $DB;

	$result = true;
	$arr = array('counter','firstname','lastname','idnumber','institutuin','department','email','empty');
	foreach ($arr as $k) {
		unset($rec);
		$rec->name = $k;
		$result = $result && $DB->insert_record('advanced_grade_export_fields_type', $rec);
	}

	return $result;
}
