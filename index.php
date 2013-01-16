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

$id = required_param('id', PARAM_INT); // course id

$PAGE->set_url('/grade/export/advanced_grade_export/index.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/advanced_grade_export:view', $context);

print_grade_page_head($COURSE->id, 'export', 'advanced_grade_export', get_string('pluginname', 'gradeexport_advanced_grade_export'));

if (!empty($CFG->gradepublishing)) {
    $CFG->gradepublishing = has_capability('gradeexport/advanced_grade_export:publish', $context);
}

$mform = new advanced_grade_export_form(null, array('publishing' => true));

$groupmode    = groups_get_course_groupmode($course);   // Groups are being used
$currentgroup = groups_get_course_group($course,true);

if ($groupmode == SEPARATEGROUPS and !$currentgroup and !has_capability('moodle/site:accessallgroups', $context)) {
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    die;
}

// process post information
if ($data = $mform->get_data()) {
  $exp_cols=array($data->counter_order=>array('counter',$data->counter_name,$data->counter_length),
				  $data->firstname_order=>array('firstname',$data->firstname_name,$data->firstname_length),
				  $data->lastname_order=>array('lastname',$data->lastname_name,$data->lastname_length),
				  $data->idnumber_order=>array('idnumber',$data->idnumber_name,$data->idnumber_length), 
				  $data->institution_order=>array('institution',$data->institution_name,$data->institution_length), 
				  $data->department_order=>array('department',$data->department_name,$data->department_length), 
				  $data->email_order=>array('email',$data->email_name,$data->email_length),
				  $data->empty_order=>array('empty',$data->empty_name,$data->empty_length));
  
  $export = new advanced_grade_export($course, $currentgroup, '', false, false, $data->display, $data->decimals, $data->advanced_grade_header['text'],$data->advanced_grade_footer['text'],$exp_cols);

    // print the grades on screen for feedbacks
	$export->process_form($data);
	$export->print_for_groups();
	$export->display_my_preview();
    echo $OUTPUT->footer();
    exit;
}

//groups_print_course_menu($course, 'index.php?id='.$id);

echo '<div class="clearer"></div>';
$mform->display();
echo '<script src="advanced_grade_export.js"></script>';
//echo '<script>set_order(this);</script>';

echo $OUTPUT->footer();

