<?php

// This file created by Eugene Shwab <chillofoctober@gmail.com
// for advanced grade export plugin for moodle

require_once '../../../config.php';
require_once 'advanced_grade_export_lib.php';
//require_once 'advanced_grade_export.php';
require_once 'addtemplate.php';
require_once 'template_updater.php';

$id = required_param('id', PARAM_INT); // course id
$mode=required_param('mode',PARAM_INT);
$tid=optional_param('tid',0,PARAM_INT); // template mode

$PAGE->set_url('/grade/export/advanced_grade_export/index.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/advanced_grade_export:view', $context);

print_grade_page_head($COURSE->id, 'export', 'advanced_grade_export', 'Templates for ' . get_string('pluginname', 'gradeexport_advanced_grade_export'));

$groupmode    = groups_get_course_groupmode($course);   // Groups are being used
$currentgroup = groups_get_course_group($course, true);
if ($groupmode == SEPARATEGROUPS and !$currentgroup and !has_capability('moodle/site:accessallgroups', $context)) {
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    die;
}

// template code
$mform = new addtemplate(null, array('publishing' => true, 'tid'=>$tid, 'mode'=>$mode));
$updater= new template_updater($id);

if ($data = $mform->get_data()) {
  $fields=array($data->counter_order=>array('counter',$data->counter_name,$data->counter_length),
			  $data->firstname_order=>array('firstname',$data->firstname_name,$data->firstname_length),
			  $data->lastname_order=>array('lastname',$data->lastname_name,$data->lastname_length),
			  $data->idnumber_order=>array('idnumber',$data->idnumber_name,$data->idnumber_length), 
			  $data->institution_order=>array('institution',$data->institution_name,$data->institution_length), 
			  $data->department_order=>array('department',$data->department_name,$data->department_length), 
			  $data->email_order=>array('email',$data->email_name,$data->email_length),
			  $data->empty_order=>array('empty',$data->empty_name,$data->empty_length));
}

switch ($mode)
  {
  case 0:
	echo "&nbsp;&nbsp;";
	$updater->read();
	break;
  case 1:
	$mform->display();
    break;
  case 2:
	$updater->add($data->template_name,$data->advanced_grade_header['text'],$data->advanced_grade_footer['text'],$fields);
	$updater->read();
	break;
  case 3:
	$updater->update($tid,$data->template_name,$data->advanced_grade_header['text'],$data->advanced_grade_footer['text'],$fields);
	$updater->read();
	break;
  case 4:
	$updater->delete($tid);
	$updater->read();
	break;
   }

// end template code

echo $OUTPUT->footer();