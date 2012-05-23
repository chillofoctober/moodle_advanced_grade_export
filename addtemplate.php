<?php

// This file created by Eugene Shwab <chillofoctober@gmail.com>
// for Moodle advanced grade export plugin

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->libdir.'/formslib.php';

class addtemplate extends moodleform
{
  function definition() {
        global $CFG, $COURSE, $USER, $DB;
		
        $mform =& $this->_form;
        if (isset($this->_customdata)) {  // hardcoding plugin names here is hacky
            $features = $this->_customdata;
        } else {
            $features = array();
        }
		$this->_customdata['tid']>0?$md=3:$md=2;
		$headfoot=$DB->get_record('advanced_grade_export_template',array('id'=>$this->_customdata['tid']),'*');
		$names=$DB->get_records('advanced_grade_export_fields_type');
		$fields=$DB->get_records('advanced_grade_export_template_fields',array('templateid'=>$this->_customdata['tid']));
		$fieldsarr=array();
		foreach ($fields as $field)
		{
		  $tmparr=get_object_vars($field);
		  $fieldsarr[$tmparr['type']]=$tmparr;
		}
		for ($i=1;$i<9;$i++)
		  if (!isset($fieldsarr[$i]))
			{
			  $this->fields_array_parameters($fieldsarr[$i]);
			}
        // begin advanced grade elements
		$mform->addElement('html','<div style="position:relative;text-align:right"><a href="templates.php?id='.$COURSE->id.'&amp;mode=0">'.
			get_string('back_to_templates','gradeexport_advanced_grade_export').'</a></div>');
		$mform->addElement('text','template_name',get_string('template_name','gradeexport_advanced_grade_export'),' value="'.$this->set_name($headfoot->name,'').'"');
		$mform->addElement('editor', 'advanced_grade_header', get_string('header','gradeexport_advanced_grade_export'))->setValue(array('text' => $this->set_name($headfoot->header,'')) );
		$mform->setType('advanced_grade_header', PARAM_RAW);
		$opts='style="width:50px;"';
		$opts1='style="width:100px;"';
		$options = array('0'=>get_string('no','gradeexport_advanced_grade_export'),'1'=>1, '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6);
		$mform->addElement('html', '<br><br><table style="width:350px;text-align:center;margin-left:110px;"><tr><td style="width:150px;">'.
			get_string('column_name','gradeexport_advanced_grade_export').'</td><td>'.get_string('length','gradeexport_advanced_grade_export').
			'</td><td style="width:70px;">'.get_string('order','gradeexport_advanced_grade_export').'</td></tr>');
		for ($i=1; $i <9 ; $i++) { 
            $mform->addElement('html','<tr><td>');
            $mform->addElement('text',$names[$i]->name.'_name','',$opts1.'value="'.$this->set_name($fieldsarr[$i]['name'],get_string($names[$i]->name,'gradeexport_advanced_grade_export')).'"');
            $mform->addElement('html','</td><td>');
            $mform->addElement('text',$names[$i]->name.'_length','',$opts.' value='.$fieldsarr[$i]['length']);     
            $mform->addElement('html','</td><td>');
            $mform->addElement('select',$names[$i]->name.'_order','',$options)->setSelected($fieldsarr[$i]['number']);
            $mform->addElement('html','</td></tr>');
        }
		$mform->addElement('html','</table>');
		
		$mform->addElement('editor', 'advanced_grade_footer', get_string('footer','gradeexport_advanced_grade_export'))->setValue(array('text'=>$this->set_name($headfoot->footer,'')));
		$mform->setType('advanced_grade_footer', PARAM_RAW);
			// end advanced grade elements
		$mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);
		$mform->addElement('hidden','mode',$md);
		$mform->setType('mode',PARAM_INT);
		$mform->addElement('hidden','tid',$this->_customdata['tid']);
		$mform->setType('tid',PARAM_INT);
        $this->add_action_buttons(false, get_string('submit'));
  }
 
  function fields_array_parameters(&$fields)
  {
	$fields['length']=100;
	$fields['number']='no';
  }
  function set_name(&$name,$altname)
  {
	$name=isset($name)?$name:$altname;
	return $name;
  }
}
