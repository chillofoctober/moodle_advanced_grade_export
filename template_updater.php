<?php

// This file created by Eugene Shwab <chillofoctober@gmail.com>
// for Moodle advanced grade export plugin

if (!defined('MOODLE_INTERNAL')) 
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page

	class template_updater{

	  /*	  private $name;
	  private $header;
	  private $footer;*/
	  private $id;
	  //	  private $fields;
	  
	  public function template_updater($template_id)
	  {
		$this->id=$template_id;
	  }

	  public function update($tid=0,$template_name, $template_header="", $template_footer="", $template_fields)
	  {
		global $DB;
	//	print_r($template_fields);
		$dataobj=new stdClass();
		$dataobj->id=$tid;
		$dataobj->name=$template_name;
		$dataobj->header=$template_header;
		$dataobj->footer=$template_footer;
		$transaction = $DB->start_delegated_transaction();
		try{
			$DB->update_record('advanced_grade_export_template',$dataobj);
			$DB->delete_records('advanced_grade_export_template_fields',array('templateid'=>$tid));
			$this->insert_fields($tid,$template_fields);
			$transaction->allow_commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback($e);
			echo get_string('error','gradeexport_advanced_grade_export');
			return;
		}
		echo get_string('success_update','gradeexport_advanced_grade_export').'<br><br>&nbsp;&nbsp;';
	  }

	  public function read()
	  {
		global $DB, $OUTPUT;
		$result = $DB->get_records_sql('SELECT name, id FROM {advanced_grade_export_template} WHERE course = ?', array('course'=>$this->id));
	  //	print_r($this->id);
		foreach ($result as $name=>$id)
		  {
			print "&nbsp;".$name."<a href=templates.php?id=".$this->id."&amp;mode=1&amp;tid=".$id->id.">";
			print '<img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall" alt="'.get_string('edit').'" title="'.get_string('edit').'" ></a>';
			print "<a href=templates.php?id=".$this->id."&amp;mode=4&amp;tid=".$id->id.">";
			print '<img src="'.$OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="'.get_string('delete').'" title="'.get_string('delete').'" ></a><br>&nbsp;&nbsp;';
		  }
		echo "<br><br>&nbsp;&nbsp;<a href='templates.php?id=".$this->id."&amp;mode=1'>".get_string('add_template','gradeexport_advanced_grade_export')."</a>";
	  }
	  
	  public function add($template_name, $template_header="", $template_footer="", $template_fields)
	  {
		global $DB;
		
		$id = $DB->get_field_sql('SELECT ifnull(max(id)+1,1) "maxid"  FROM {advanced_grade_export_template}');

		$record=new stdClass();
		$record->id=$id;
		$record->course=$this->id;
		$record->name=$template_name;
		$record->header=$template_header;
		$record->footer=$template_footer;
		$transaction = $DB->start_delegated_transaction();
		try {
			$DB->insert_record('advanced_grade_export_template',$record);
		// if it's overall first record $id=1 but maybe has deleted records
			$result=$DB->get_record_sql('SELECT max(id) "maxid", count(id) "countid"  FROM {advanced_grade_export_template}');
		// may be another record added in this time? check count(records)
		//	print_r($result);
			if ($result->countid == 1)
		  		$id=$result->maxid;
		// else $id stay without changes
			$this->insert_fields($id,$template_fields);
			$transaction->allow_commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback($e);
			echo get_string('error','gradeexport_advanced_grade_export');
			return;
		}
		echo get_string('success_save','gradeexport_advanced_grade_export').'<br><br>&nbsp;&nbsp;';

	  }
	  
	  public function delete($tid)
	  {
		global $DB;
		try {
		$transaction = $DB->start_delegated_transaction();
		$DB->delete_records('advanced_grade_export_template',array('id'=>$tid));
		$DB->delete_records('advanced_grade_export_template_fields',array('templateid'=>$tid));
		$transaction->allow_commit();
		}
		catch(Exception $e) {
		  $transaction->rollback($e);
		  echo get_string('error','gradeexport_advanced_grade_export');
		  return;
		}
		echo get_string('success_delete','gradeexport_advanced_grade_export').'<br><br>&nbsp;&nbsp;';
							
	  }

	  function insert_fields($tid,$fields)
	  {
	  	global $DB;
	  	$col_count=count($fields);
	  	$rec=new stdClass();
	  	$rec->templateid=$tid;
		$j=0;
	  	for ($i=1;$i<$col_count+$j;$i++)
		  {
			if (isset($fields[$i]))
			{
				$rec->name=$fields[$i][1];
				$rec->length=$fields[$i][2];
				$rec->number=$i;
				$rec->type=$DB->get_field_sql('select id from {advanced_grade_export_fields_type} where name="'.$fields[$i][0].'"');
				$DB->insert_record('advanced_grade_export_template_fields',$rec);
			} else {
				$j++;
			}
		  }
	  }

	}
