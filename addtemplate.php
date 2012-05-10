<?php

// This file created by Eugene Shwab <chillofoctober@gmail.com
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
		$mform->addElement('html','<div style="position:relative;text-align:right"><a href="templates.php?id='.$COURSE->id.'&amp;mode=0">Back to templates</a></div>');
		$mform->addElement('text','template_name','template name',' value="'.$this->set_name($headfoot->name,'').'"');
		$mform->addElement('editor', 'advanced_grade_header', 'header')->setValue(array('text' => $this->set_name($headfoot->header,'')) );
		$mform->setType('advanced_grade_header', PARAM_RAW);
		$opts='style="width:50px;"';
		$opts1='style="width:100px;"';
		$mform->addElement('html', '<br><br><table style="width:350px;text-align:center;margin-left:110px;"><tr><td style="width:150px;">column name</td><td>length</td><td style="width:50px;">order</td></tr><tr><td>');
		$mform->addElement('text','counter_name','',$opts1.' value="'.$this->set_name($fieldsarr[1]['name'],'#').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','counter_length','',$opts.' value='.$fieldsarr[1]['length']);
		$mform->addElement('html','</td><td>');
		$options = array('0'=>'no','1'=>1, '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6);
		$mform->addElement('select','counter_order','',$options)->setSelected($fieldsarr[1]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','firstname_name','',$opts1.' value="'.$this->set_name($fieldsarr[2]['name'],'firstname').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','firstname_length','',$opts.' value='.$fieldsarr[2]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','firstname_order','',$options)->setSelected($fieldsarr[2]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','lastname_name','',$opts1.' value="'.$this->set_name($fieldsarr[3]['name'],'lastname').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','lastname_length','',$opts.' value='.$fieldsarr[3]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','lastname_order','',$options)->setSelected($fieldsarr[3]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','idnumber_name','',$opts1.' value="'.$this->set_name($fieldsarr[4]['name'],'idnumber').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','idnumber_length','',$opts.' value='.$fieldsarr[4]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','idnumber_order','',$options)->setSelected($fieldsarr[4]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','institution_name','',$opts1.' value="'.$this->set_name($fieldsarr[5]['name'],'institution').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','institution_length','',$opts.' value='.$fieldsarr[5]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','institution_order','',$options)->setSelected($fieldsarr[5]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','department_name','',$opts1.' value="'.$this->set_name($fieldsarr[6]['name'],'department').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','department_length','',$opts.' value='.$fieldsarr[6]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','department_order','',$options)->setSelected($fieldsarr[6]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','email_name','',$opts1.' value="'.$this->set_name($fieldsarr[7]['name'],'email').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','email_length','',$opts.' value='.$fieldsarr[7]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','email_order','',$options)->setSelected($fieldsarr[7]['number']);
		$mform->addElement('html','</td></tr><tr><td>');
		$mform->addElement('text','empty_name','',$opts1.' value="'.$this->set_name($fieldsarr[8]['name'],'empty').'"');
		$mform->addElement('html','</td><td>');
		$mform->addElement('text','empty_length','',$opts.' value='.$fieldsarr[8]['length']);
		$mform->addElement('html','</td><td>');
		$mform->addElement('select','empty_order','',$options)->setSelected($fieldsarr[8]['number']);
		$mform->addElement('html','</td></tr></table>');
		
		$mform->addElement('editor', 'advanced_grade_footer', 'footer')->setValue(array('text'=>$this->set_name($headfoot->footer,'')));
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
