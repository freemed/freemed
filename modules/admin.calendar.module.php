<?php
  // $Id$
  // note: calendar admin
  // code: fred forester fforest@netcarrier.com
  // lic : GPL, v2

if (!defined("__ADMIN_CALENDAR_MODULE_PHP__")) {

define (__ADMIN_CALENDAR_MODULE_PHP__, true);

class AdminCalendar extends freemedCalendarModule {

	var $MODULE_NAME = "Calendar Admin";
	var $MODULE_VERSION = "0.1";

	var $record_name = "Scheduler";
	var $table_name  = "scheduler";

	var $variables = array (
		"caldateof",
		"caltype",   
		"calhour",  
		"calminute",    
		"calduration", 
		"calfacility",
		"calroom",      
		"calphysician",
		"calpatient", 
		"calcptcode",
		"calstatus",
		"calprenote",  
		"calpostnote" 
	);

	function AdminCalendar () {
		// run constructor
		$this->freemedCalendarModule();
	} // end constructor AdminCalendar	

	function view () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
		
		if (!$year) $year = date("Y");
		if (!$month) $month = date("m");

		$this->month($month,$year);
		echo "<CENTER><B>Calendar For: $this->month_name, $this->year</B></CENTER><br>\n";
		$this->draw(array("cellspacing" => "2" , "cellpadding" => "2" ,
                      "top_row_align" => "center" , "table_height" => "300px" ,
                      "top_row_cell_height" => 20 , "bgcolor" => "#cccccc" ,
                      "row_align" => "left" , "row_valign" => "top" ,
                      "font_size" => "-1") );

		echo "<CENTER>";
		echo "<A HREF=\"$this->page_name?_auth=".prepare($_auth).
			"&action=view&module=$module&month=$this->prevmonth&year=$this->prevyear\">Prev</A>";
		echo "&nbsp;";
		echo "<A HREF=\"$this->page_name?_auth=".prepare($_auth).
			"&action=view&module=$module&month=$this->nextmonth&year=$this->nextyear\">Next</A>";
		echo "</CENTER>";

		


	} // end function module->view

	function form () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
	} // end function AdminCalendar->form

	function display () {
		echo "in display<BR>";
	} // end display

} // end of class AdminCalendar

register_module ("AdminCalendar");

} // end of "if defined"

?>
