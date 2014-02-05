<?php  //$Id: upgrade.php,v 1.2.0 2013/06/26 $

// This file keeps track of upgrades to 
// the advanced_grade_export module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_gradeexport_advanced_grade_export_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); /// loads ddl manager and xmldb classes
    $result = true;

	/// And upgrade begins here. For each one, you'll need one 
	/// block of code similar to the next one. Please, delete 
	/// this comment lines once this file start handling proper
	/// upgrade code.

	if ($oldversion < 2013270601) { //New version in version.php
	  $table = new xmldb_table('advanced_grade_export_template');
	  
	  $field = new xmldb_field('updatedat');
	  $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'footer');
	  $dbman->add_field($table, $field); 
	  

	  $field = new xmldb_field('userid');
	  $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'updatedat');
	  $dbman->add_field($table, $field); 
	  
	  upgrade_plugin_savepoint(true, 2013270601, 'gradeexport', 'advanced_grade_export');
	}
    return $result;
}

?>
