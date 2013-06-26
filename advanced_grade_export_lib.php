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

require_once($CFG->dirroot.'/lib/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once 'advanced_grade_export_form.php';

/**
 * Base export class
 */
abstract class grade_export_abstract {

    public $plugin; // plgin name - must be filled in subclasses!

    public $grade_items; // list of all course grade items
    public $groupid;     // groupid, 0 means all groups
    public $course;      // course object
    public $columns;     // array of grade_items selected for export

    public $previewrows;     // number of rows in preview
    public $export_letters;  // export letters
    public $export_feedback; // export feedback
    public $userkey;         // export using private user key

    public $updatedgradesonly; // only export updated grades
    public $displaytype; // display type (e.g. real, percentages, letter) for exports
    public $decimalpoints; // number of decimal points for exports

	// advanced_grade_vars begin
	public $advanced_grade_header;
	public $advanced_grade_footer;
	public $exp_cols;
	public $sel_itemids;
	public $ed_itemids;
	// advanced_grade_vars end
    /**
     * Constructor should set up all the private variables ready to be pulled
     * @access public
     * @param object $course
     * @param int $groupid id of selected group, 0 means all
     * @param string $itemlist comma separated list of item ids, empty means all
     * @param boolean $export_feedback
     * @param boolean $export_letters
     * @note Exporting as letters will lead to data loss if that exported set it re-imported.
     */
    public function grade_export_abstract($course, $groupid=0, $itemlist='', $export_feedback=false, $updatedgradesonly = false, $displaytype = GRADE_DISPLAY_TYPE_REAL, $decimalpoints = 2,$advanced_grade_header, $advanced_grade_footer, $exp_cols, $sel_itemids='', $ed_itemids='') {
        $this->course = $course;
        $this->groupid = $groupid;
        $this->grade_items = grade_item::fetch_all(array('courseid'=>$this->course->id));
		//		print_r($this->grade_items);
        //Populating the columns here is required by /grade/export/(whatever)/export.php
        //however index.php, when the form is submitted, will construct the collection here
        //with an empty $itemlist then reconstruct it in process_form() using $formdata
		$this->columns = array();
        if (!empty($itemlist)) {
            if ($itemlist=='-1') {
                //user deselected all items
            } else {
                $itemids = explode(',', $itemlist);
                // remove items that are not requested
                foreach ($itemids as $itemid) {
                    if (array_key_exists($itemid, $this->grade_items)) {
                        $this->columns[$itemid] =& $this->grade_items[$itemid];
                    }
                }
            }
        } else {
		  /* I think the commented code is same as code written later            
						foreach ($this->grade_items as $itemid=>$unused) {
                $this->columns[$itemid] =& $this->grade_items[$itemid];
				}*/
		  $this->columns=$this->grade_items;
        }
		//		print_r($this->columns);
        $this->export_feedback = $export_feedback;
        $this->userkey         = '';
        $this->previewrows     = false;
        $this->updatedgradesonly = $updatedgradesonly;

        $this->displaytype = $displaytype;
        $this->decimalpoints = $decimalpoints;
		$this->advanced_grade_header=$advanced_grade_header;
		$this->advanced_grade_footer=$advanced_grade_footer;
		$this->exp_cols=$exp_cols;
		$this->exp_cols[0][0]='';
		$this->exp_cols[0][1]='';
		$this->exp_cols[0][2]='';
		$this->sel_itemids=$sel_itemids;
		$this->ed_itemids=$ed_itemids;
    
     }
    /**
     * Init object based using data from form
     * @param object $formdata
     */
    function process_form($formdata) {
        global $USER;

        $this->columns = array();
		$this->sel_itemids=array();
		$this->ed_itemids=array();
        if (!empty($formdata->itemids)) {
            if ($formdata->itemids=='-1') {
                //user deselected all items
            } else {
                foreach ($formdata->itemids as $itemid=>$selected) {
                    if ($selected and array_key_exists($itemid, $this->grade_items)) {
                        $this->columns[$itemid] =& $this->grade_items[$itemid];
						if ($formdata->sel_itemids[$itemid]>0)
						  {
							$this->sel_itemids[$itemid]=$formdata->sel_itemids[$itemid];
							$this->ed_itemids[$itemid]=$formdata->ed_itemids[$itemid];
							//$this->ed_itemids[$formdata->ed_itemids[$itemid]]=$itemid;
						  }
					}
				}
			}
		}
		/*	for what this code?
			else {
            foreach ($this->grade_items as $itemid=>$unused) {
                $this->columns[$itemid] =& $this->grade_items[$itemid];
            }
			}*/
		//print_r($this->sel_itemids);
		
        if (isset($formdata->key)) {
            if ($formdata->key == 1 && isset($formdata->iprestriction) && isset($formdata->validuntil)) {
                // Create a new key
                $formdata->key = create_user_key('grade/export', $USER->id, $this->course->id, $formdata->iprestriction, $formdata->validuntil);
            }
            $this->userkey = $formdata->key;
        }

        if (isset($formdata->export_letters)) {
            $this->export_letters = $formdata->export_letters;
        }

        if (isset($formdata->export_feedback)) {
            $this->export_feedback = $formdata->export_feedback;
        }

        if (isset($formdata->previewrows)) {
            $this->previewrows = $formdata->previewrows;
        }
		if (isset($formdata->advanced_grade_header['text'])) {
		  $this->advanced_grade_header=$formdata->advanced_grade_header['text'];
		  }
		if (isset($formdata->advanced_grade_footer['text'])) {
		  $this->advanced_grade_footer=$formdata->advanced_grade_footer['text'];
		}
		
    }

    /**
     * Update exported field in grade_grades table
     * @return boolean
     */
    public function track_exports() {
        global $CFG;

        /// Whether this plugin is entitled to update export time
        if ($expplugins = explode(",", $CFG->gradeexport)) {
            if (in_array($this->plugin, $expplugins)) {
                return true;
            } else {
                return false;
          }
        } else {
            return false;
        }
    }

    /**
     * Returns string representation of final grade
     * @param $object $grade instance of grade_grade class
     * @return string
     */
    public function format_grade($grade) {
        return grade_format_gradevalue($grade->finalgrade, $this->grade_items[$grade->itemid], false, $this->displaytype, $this->decimalpoints);
    }

    /**
     * Returns the name of column in export
     * @param object $grade_item
     * @param boolena $feedback feedback colum
     * &return string
     */
    public function format_column_name($grade_item, $feedback=false) {
        if ($grade_item->itemtype == 'mod') {
            $name = get_string('modulename', $grade_item->itemmodule).get_string('labelsep', 'langconfig').$grade_item->get_name();
        } else {
            $name = $grade_item->get_name();
        }

        if ($feedback) {
            $name .= ' ('.get_string('feedback').')';
        }

        return strip_tags($name);
    }

    /**
     * Returns formatted grade feedback
     * @param object $feedback object with properties feedback and feedbackformat
     * @return string
     */
    public function format_feedback($feedback) {
        return strip_tags(format_text($feedback->feedback, $feedback->feedbackformat));
    }

    /**
     * Implemented by child class
     */
    public abstract function print_grades();

    /**
     * Returns array of parameters used by dump.php and export.php.
     * @return array
     */
    function get_export_params($groupid='') {
        $itemids = array_keys($this->columns);
        $itemidsparam = implode(',', $itemids);
		$exp_cols_string='';
		$sel_itemids_string='';
		$ed_itemids_string='';
		$sel_keys=array_keys($this->sel_itemids);
		foreach ($this->exp_cols as $i=>$expcols)
		  {
			//			if (isset($this->exp_cols[$i]))
			$exp_cols_string.=$i.','.$expcols[0].','.$expcols[1].','.$expcols[2].';';
		  }
			//		  			else
		foreach ($sel_keys as $key) {
				//					if ($this->sel_itemids[$key]==$i)
		  $sel_itemids_string.=$key.','.$this->sel_itemids[$key].';';
		  if ($this->ed_itemids[$key]!='') $ed_itemids_string.=$key.','.$this->ed_itemids[$key].';';
		}
		  
        if (empty($itemidsparam)) {
            $itemidsparam = '-1';
        }

        $params = array('id'                =>$this->course->id,
                        'groupid'           =>$groupid,
                        'itemids'           =>$itemidsparam,
                        'export_letters'    =>$this->export_letters,
                        'export_feedback'   =>$this->export_feedback,
                        'updatedgradesonly' =>$this->updatedgradesonly,
                        'displaytype'       =>$this->displaytype,
                        'decimalpoints'     =>$this->decimalpoints,
						'advanced_grade_header'        =>$this->advanced_grade_header,
						'advanced_grade_footer'        =>$this->advanced_grade_footer,
						'exp_cols_string'   =>$exp_cols_string,
						'sel_itemids'       =>$sel_itemids_string,
						'ed_itemids'        =>$ed_itemids_string
						);

        return $params;
    }

    /**
     * Either prints a "Export" box, which will redirect the user to the download page,
     * or prints the URL for the published data.
     * @return void
     */
    function print_continue($groupid='',$groupname='') {
	  global $CFG, $OUTPUT;

        $params = $this->get_export_params($groupid);

        if (!$this->userkey)       // this button should trigger a download prompt
            echo $groupname.$OUTPUT->single_button(new moodle_url('/grade/export/'.$this->plugin.'/export.php', $params), get_string('download', 'admin'));
		else {
            $paramstr = '';
            $sep = '?';
            foreach($params as $name=>$value) {
                $paramstr .= $sep.$name.'='.$value;
                $sep = '&';
            }

            $link = $CFG->wwwroot.'/grade/export/'.$this->plugin.'/dump.php'.$paramstr.'&key='.$this->userkey;

            echo get_string('download', 'admin').': ' . html_writer::link($link, $link);
        }
    }

	public function print_for_groups()
	{
	  global $OUTPUT, $DB;

	  echo $OUTPUT->heading(get_string('export', 'grades'));
	  echo $OUTPUT->container_start('gradeexportlink');
	  $result=$DB->get_records('groups',array('courseid'=>$this->course->id),null,'id, name');
	  $this->print_continue('',get_string('all','core'));
	  foreach ($result as $group) {
		$this->print_continue($group->id,$group->name);
	  }
	  echo $OUTPUT->container_end();
	}
}

/**
 * This class is used to update the exported field in grade_grades.
 * It does internal buffering to speedup the db operations.
 */
class advanced_grade_export_update_buffer {
    public $update_list;
    public $export_time;

    /**
     * Constructor - creates the buffer and initialises the time stamp
     */
    public function grade_export_update_buffer() {
        $this->update_list = array();
        $this->export_time = time();
    }

    public function flush($buffersize) {
        global $CFG, $DB;

        if (count($this->update_list) > $buffersize) {
            list($usql, $params) = $DB->get_in_or_equal($this->update_list);
            $params = array_merge(array($this->export_time), $params);

            $sql = "UPDATE {grade_grades} SET exported = ? WHERE id $usql";
            $DB->execute($sql, $params);
            $this->update_list = array();
        }
    }

    /**
     * Track grade export status
     * @param object $grade_grade
     * @return string $status (unknow, new, regrade, nochange)
     */
    public function track($grade_grade) {

        if (empty($grade_grade->exported) or empty($grade_grade->timemodified)) {
            if (is_null($grade_grade->finalgrade)) {
                // grade does not exist yet
                $status = 'unknown';
            } else {
                $status = 'new';
                $this->update_list[] = $grade_grade->id;
            }

        } else if ($grade_grade->exported < $grade_grade->timemodified) {
            $status = 'regrade';
            $this->update_list[] = $grade_grade->id;

        } else if ($grade_grade->exported >= $grade_grade->timemodified) {
            $status = 'nochange';

        } else {
            // something is wrong?
            $status = 'unknown';
        }

        $this->flush(100);

        return $status;
    }

    /**
     * Flush and close the buffer.
     */
    public function close() {
        $this->flush(0);
    }
}

