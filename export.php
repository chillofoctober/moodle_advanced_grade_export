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

require_once '../../../config.php';
require_once 'advanced_grade_export_lib.php';
require_once 'advanced_grade_export.php';

$id                = required_param('id', PARAM_INT); // course id
$groupid           = optional_param('groupid', 0, PARAM_INT);
$itemids           = required_param('itemids', PARAM_RAW);
$export_feedback   = optional_param('export_feedback', 0, PARAM_BOOL);
$updatedgradesonly = optional_param('updatedgradesonly', false, PARAM_BOOL);
$displaytype       = optional_param('displaytype', $CFG->grade_export_displaytype, PARAM_INT);
$decimalpoints     = optional_param('decimalpoints', $CFG->grade_export_decimalpoints, PARAM_INT);
$advanced_grade_header        = optional_param('advanced_grade_header',0,PARAM_CLEANHTML);
$advanced_grade_footer        = optional_param('advanced_grade_footer',0,PARAM_CLEANHTML);
$exp_cols_string   = required_param('exp_cols_string',PARAM_RAW);


$pos=0;
$i=1;
$delimiter=',';
//$exp_cols[0][0]='';
while($pos < strlen($exp_cols_string))
  {
	for ($j=0;$j<3;$j++)
	  {
		if ($j==2) { $delimiter=';'; }
		$pos1=strpos($exp_cols_string,$delimiter,$pos);
		$exp_cols[$i][$j]=substr($exp_cols_string,$pos,$pos1-$pos);
		$pos=$pos1+1;
	  }
	$delimiter=',';
	$i++;
  }

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/advanced_grade_export:view', $context);

if (groups_get_course_groupmode($COURSE) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_is_member($groupid, $USER->id)) {
        print_error('cannotaccessgroup', 'grades');
    }
}

// print all the exported data here
$export = new advanced_grade_export($course, $groupid, $itemids, $export_feedback, $updatedgradesonly, $displaytype, $decimalpoints,$advanced_grade_header,$advanced_grade_footer,$exp_cols );
$export->print_grades();


