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

require_once 'advanced_grade_export_lib.php';
require_once 'advanced_grade_export_form.php';

class advanced_grade_export extends grade_export {

    public $plugin = 'advanced_grade_export';

    /**
     * To be implemented by child classes
     */
    public function print_grades() {
        global $CFG;
		$export_tracking = $this->track_exports();

		echo "<html>";
		header("Content-type: application/msword");  
		echo "<body>".iconv('UTF-8','CP1251',$this->advanced_grade_header)."<br>";
		echo '<table style="border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;mso-padding-alt:0cm 5.4pt 0cm 5.4pt">';
        echo '<tr>';
		$col_count=count($this->exp_cols);
		for ($i=1;$i<$col_count;$i++)
		{
		  echo '<td width='.$this->exp_cols[$i][2].' style="border:solid windowtext 1.0pt;  mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$this->exp_cols[$i][1]).'</td>';
		}
        foreach ($this->columns as $grade_item) {
		  echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$this->format_column_name($grade_item)).'</td>';

            /// add a column_feedback column
            if ($this->export_feedback) {
			  echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$this->format_column_name($grade_item, true)).'</td>';
            }
        }
		echo '</tr>';

		    /// Print all the lines of data.
        $i = 0;
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            $i++;
            $user = $userdata->user;
			echo '<tr>';
			for ($i=1;$i<$col_count;$i++)
			  {
				switch ($this->exp_cols[$i][0])
				  {
				  case 'lastname':
					echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$user->lastname).'</td>';
					break;
				  case 'firstname':
					echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$user->firstname).'</td>';
					break;					
				  case 'idnumber':
					echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$user->idnumber).'</td>';
					break;
				  case 'department':
					echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$user->department).'</td>';
					break;
				  case 'institution':
					echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$user->institution).'</td>';
					break;
				  case 'email':
					echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.$user->email.'</td>';
					break;
				  }
			  }
            $j=5;
            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }

                $gradestr = $this->format_grade($grade);
                if (is_numeric($gradestr)) {
                    echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.$gradestr.'</td>';
                }
                else {
                    echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.$gradestr.'</td>';
                }

                // writing feedback if requested
                if ($this->export_feedback) {
				  echo '<td style="border:solid windowtext 1.0pt; mso-border-alt:solid windowtext .5pt">'.iconv('UTF-8','CP1251',$this->format_feedback($userdata->feedbacks[$itemid])).'</td>';
                }
            }
			echo '</tr>';
        }
        $gui->close();
        $geub->close();
		
		echo '</table>';
		echo iconv('UTF-8','CP1251',$this->advanced_grade_footer);
		echo "</body></html>";
        exit;
    }
	public  function display_my_preview($require_user_idnumber=false)
	{
        global $OUTPUT;
        echo $OUTPUT->heading(get_string('previewrows', 'grades'));
		echo $this->advanced_grade_header;
		echo '<table style="border-width:2px">';
        echo '<tr>';
		$col_count=count($this->exp_cols);
		for ($i=1;$i<$col_count;$i++)
		{
		  echo '<td width='.$this->exp_cols[$i][2].' style="border-width:2px">'.$this->exp_cols[$i][1].'</td>';
		}

        foreach ($this->columns as $grade_item) {
		    echo '<td style="border-width:2px">'.$this->format_column_name($grade_item).'</td>';

            /// add a column_feedback column
            if ($this->export_feedback) {
                echo '<td style="border-width:2px">'.$this->format_column_name($grade_item, true).'</td>';
            }
        }
        echo '</tr>';
        /// Print all the lines of data.

        $i = 0;
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            // number of preview rows
            if ($this->previewrows and $this->previewrows <= $i) {
                break;
            }
            $user = $userdata->user;
            if ($require_user_idnumber and empty($user->idnumber)) {
                // some exports require user idnumber so we can match up students when importing the data
                continue;
            }

            $gradeupdated = false; // if no grade is update at all for this user, do not display this row
            $rowstr = '';
            foreach ($this->columns as $itemid=>$unused) {
                $gradetxt = $this->format_grade($userdata->grades[$itemid]);

                // get the status of this grade, and put it through track to get the status
                $g = new grade_export_update_buffer();
                $grade_grade = new grade_grade(array('itemid'=>$itemid, 'userid'=>$user->id));
                $status = $g->track($grade_grade);

                if ($this->updatedgradesonly && ($status == 'nochange' || $status == 'unknown')) {
                    $rowstr .= '<td style="border-width:2px">'.get_string('unchangedgrade', 'grades').'</td>';
                } else {
                    $rowstr .= '<td style="border-width:2px">'.$gradetxt.'</td>';
                    $gradeupdated = true;
                }

                if ($this->export_feedback) {
                    $rowstr .=  '<td style="border-width:2px">'.$this->format_feedback($userdata->feedbacks[$itemid]).'</td>';
                }
            }

            // if we are requesting updated grades only, we are not interested in this user at all
            if (!$gradeupdated && $this->updatedgradesonly) {
                continue;
            }

            echo '<tr>';
			for ($i=1;$i<$col_count;$i++)
			  {
				switch ($this->exp_cols[$i][0])
				  {
				  case 'lastname':
					echo '<td style="border-width:2px">'.$user->lastname.'</td>';
					break;
				  case 'firstname':
					echo '<td style="border-width:2px">'.$user->firstname.'</td>';
					break;					
				  case 'idnumber':
					echo '<td style="border-width:2px">'.$user->idnumber.'</td>';
					break;
				  case 'department':
					echo '<td style="border-width:2px">'.$user->department.'</td>';
					break;
				  case 'institution':
					echo '<td style="border-width:2px">'.$user->institution.'</td>';
					break;
				  case 'email':
					echo '<td style="border-width:2px">'.$user->email.'</td>';
					break;
				  }

			  }
   //		    echo "<td>.$user->lastname</td><td>$user->firstname</td><td>$user->idnumber<td>$user->department</td>";
            echo $rowstr;
            echo "</tr>";

            $i++; // increment the counter
        }
        echo '</table>';
        $gui->close();
		echo $this->advanced_grade_footer;

	}

}

