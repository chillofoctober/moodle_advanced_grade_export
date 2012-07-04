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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->libdir.'/formslib.php';

class advanced_grade_export_form extends moodleform {
    function definition() {
        global $CFG, $COURSE, $USER, $DB;

        $mform =& $this->_form;
        $tid=optional_param('tid',0,PARAM_INT);
        if (isset($this->_customdata)) {  // hardcoding plugin names here is hacky
            $features = $this->_customdata;
        } else {
            $features = array();
        }
        // begin advanced grade elements
		$mform->addElement('header','advanced_grade_template', get_string('template','gradeexport_advanced_grade_export'));
        echo '<div style="position:absolute;z-index:100;top:160px;left:85%;margin-right:10px;"><a href="templates.php?id='.$COURSE->id.'&amp;mode=0">'
            .get_string('go_to_templates','gradeexport_advanced_grade_export').'</a><br>';
        $result = $DB->get_records('advanced_grade_export_template',array('course'=>$COURSE->id),null,'name,id');
        echo '<ul>';
        foreach ($result as $name=>$id)
          {
            echo "<li><a href=index.php?id=".$COURSE->id."&amp;tid=".$id->id.">".$name."</a>";
            echo "</li>";
          }
        echo '</ul></div>';

        $headfoot=$DB->get_record('advanced_grade_export_template',array('id'=>$tid),'*');
        $fields=$DB->get_records('advanced_grade_export_template_fields',array('templateid'=>$tid));
        $names=$DB->get_records('advanced_grade_export_fields_type');
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
		
		$mform->addElement('editor', 'advanced_grade_header', get_string('header','gradeexport_advanced_grade_export'))->setValue(array('text' => $this->set_name($headfoot->header,'')) );;
		$mform->setType('advanced_grade_header', PARAM_RAW);
        $opts='style="width:50px;"';
		$opts1='style="width:100px;"';
        $options1 = array('0'=>get_string('no','gradeexport_advanced_grade_export'),'1'=>1, '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6, '7'=>7, '8'=>8);
        $mform->addElement('html', '<br><br><table style="width:350px;text-align:center;margin-left:110px;"><tr><td style="width:150px;">'.
            get_string('column_name','gradeexport_advanced_grade_export').'</td><td>'.get_string('length','gradeexport_advanced_grade_export').
            '</td><td style="width:70px;">'.get_string('order','gradeexport_advanced_grade_export').'</td></tr>');
		for ($i=1; $i <9 ; $i++) { 
            $mform->addElement('html','<tr><td>');
            $mform->addElement('text',$names[$i]->name.'_name','',$opts1.'value="'.$this->set_name($fieldsarr[$i]['name'],get_string($names[$i]->name, 'gradeexport_advanced_grade_export')).'"');
            $mform->addElement('html','</td><td>');
            $mform->addElement('text',$names[$i]->name.'_length','',$opts.' value='.$fieldsarr[$i]['length']);     
            $mform->addElement('html','</td><td>');
            $mform->addElement('select',$names[$i]->name.'_order','',$options1,'onchange="choosedOpts(this)"')->setSelected($fieldsarr[$i]['number']);
            $mform->addElement('html','</td></tr>');
        }
		$mform->addElement('html','</table>');
		
		$mform->addElement('editor', 'advanced_grade_footer', get_string('footer','gradeexport_advanced_grade_export'))->setValue(array('text'=>$this->set_name($headfoot->footer,'')));
		$mform->setType('advanced_grade_footer', PARAM_RAW);
			// end advanced grade elements
		$mform->addElement('header', 'options', get_string('options', 'grades'));

        $mform->addElement('advcheckbox', 'export_feedback', get_string('exportfeedback', 'grades'));
        $mform->setDefault('export_feedback', 0);

        $options = array('10'=>10, '20'=>20, '100'=>100, '1000'=>1000, '100000'=>100000);
        $mform->addElement('select', 'previewrows', get_string('previewrows', 'grades'), $options);

        if (!empty($features['updategradesonly'])) {
            $mform->addElement('advcheckbox', 'updatedgradesonly', get_string('updatedgradesonly', 'grades'));
        }
        /// selections for decimal points and format, MDL-11667, defaults to site settings, if set
        //$default_gradedisplaytype = $CFG->grade_export_displaytype;
        $options = array(GRADE_DISPLAY_TYPE_REAL       => get_string('real', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER     => get_string('letter', 'grades'));

        $mform->addElement('select', 'display', get_string('gradeexportdisplaytype', 'grades'), $options);
        $mform->setDefault('display', $CFG->grade_export_displaytype);

        //$default_gradedecimals = $CFG->grade_export_decimalpoints;
        $options = array(0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
        $mform->addElement('select', 'decimals', get_string('gradeexportdecimalpoints', 'grades'), $options);
        $mform->setDefault('decimals', $CFG->grade_export_decimalpoints);
        $mform->disabledIf('decimals', 'display', 'eq', GRADE_DISPLAY_TYPE_LETTER);

        if (!empty($features['includeseparator'])) {
            $radio = array();
            $radio[] = &MoodleQuickForm::createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
            $radio[] = &MoodleQuickForm::createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
            $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
            $mform->setDefault('separator', 'comma');
        }

        if (!empty($CFG->gradepublishing) and !empty($features['publishing'])) {
            $mform->addElement('header', 'publishing', get_string('publishing', 'grades'));
            $options = array(get_string('nopublish', 'grades'), get_string('createnewkey', 'userkey'));
            $keys = $DB->get_records_select('user_private_key', "script='grade/export' AND instance=? AND userid=?",
                            array($COURSE->id, $USER->id));
            if ($keys) {
                foreach ($keys as $key) {
                    $options[$key->value] = $key->value; // TODO: add more details - ip restriction, valid until ??
                }
            }
            $mform->addElement('select', 'key', get_string('userkey', 'userkey'), $options);
            $mform->addHelpButton('key', 'userkey', 'userkey');
            $mform->addElement('static', 'keymanagerlink', get_string('keymanager', 'userkey'),
                    '<a href="'.$CFG->wwwroot.'/grade/export/keymanager.php?id='.$COURSE->id.'">'.get_string('keymanager', 'userkey').'</a>');

            $mform->addElement('text', 'iprestriction', get_string('keyiprestriction', 'userkey'), array('size'=>80));
            $mform->addHelpButton('iprestriction', 'keyiprestriction', 'userkey');
            $mform->setDefault('iprestriction', getremoteaddr()); // own IP - just in case somebody does not know what user key is

            $mform->addElement('date_time_selector', 'validuntil', get_string('keyvaliduntil', 'userkey'), array('optional'=>true));
            $mform->addHelpButton('validuntil', 'keyvaliduntil', 'userkey');
            $mform->setDefault('validuntil', time()+3600*24*7); // only 1 week default duration - just in case somebody does not know what user key is

            $mform->disabledIf('iprestriction', 'key', 'noteq', 1);
            $mform->disabledIf('validuntil', 'key', 'noteq', 1);
        }

        $mform->addElement('header', 'gradeitems', get_string('gradeitemsinc', 'grades'));

        $switch = grade_get_setting($COURSE->id, 'aggregationposition', $CFG->grade_aggregationposition);

        // Grab the grade_seq for this course
        $gseq = new grade_seq($COURSE->id, $switch);

        if ($grade_items = $gseq->items) {
            $needs_multiselect = false;
            $canviewhidden = has_capability('moodle/grade:viewhidden', get_context_instance(CONTEXT_COURSE, $COURSE->id));

			$mform->addElement('html','<table class="grade_elements">');
            foreach ($grade_items as $grade_item) {
                // Is the grade_item hidden? If so, can the user see hidden grade_items?
                if ($grade_item->is_hidden() && !$canviewhidden) {
                    continue;
                }

                if (!empty($features['idnumberrequired']) and empty($grade_item->idnumber)) {
                    $mform->addElement('advcheckbox', 'itemids['.$grade_item->id.']', $grade_item->get_name(), get_string('noidnumber', 'grades'));
                    $mform->hardFreeze('itemids['.$grade_item->id.']');
                } else {
				  $mform->addElement('html','<tr><td style="width:250px;">');
				  $mform->addElement('advcheckbox', 'itemids['.$grade_item->id.']', $grade_item->get_name(), null, array('group' => 1));
                    $mform->setDefault('itemids['.$grade_item->id.']', 1);
					
					$mform->addElement('html','</td><td style="width:100px">');
					$mform->addElement('text','ed_'.'itemids['.$grade_item->id.']','');;
					$mform->addElement('html','</td><td style="width:30px">');
					$mform->addElement('select','sel_'.'itemids['.$grade_item->id.']','',$options1,'onchange="choosedOpts(this)"');
					$mform->addElement('html','</td></tr>');
                    $needs_multiselect = true;
                }
            }
			$mform->addElement('html','</table>');

            if ($needs_multiselect) {
                $this->add_checkbox_controller(1, null, null, 0); // 1st argument is group name, 2nd is link text, 3rd is attributes and 4th is original value
            }
        }

        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);
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

