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
	var $order_field = "calhour,calminute";

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

		$this->SetProcessType("ADMIN");

		$this->month($month,$year);
		echo "<CENTER><B>Calendar For: $this->month_name, $this->year</B></CENTER><br>\n";
		$this->draw(array("cellspacing" => "2" , "cellpadding" => "2" ,
                      "top_row_align" => "center" , "table_height" => "300px" ,
                      "top_row_cell_height" => 20 , "bgcolor" => "#cccccc" ,
                      "row_align" => "left" , "row_valign" => "top" ,
                      "font_size" => "-1") );

		echo "<CENTER>";
		echo "<A HREF=\"$this->page_name?$_auth".
			"&action=view&module=$module&month=$this->prevmonth&year=$this->prevyear\">Prev</A>";
		echo "&nbsp;";
		echo "<A HREF=\"$this->page_name?$_auth".
			"&action=view&module=$module&month=$this->nextmonth&year=$this->nextyear\">Next</A>";
		echo "</CENTER>";

		


	} // end function module->view

	function form () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
	} // end function AdminCalendar->form

	function display () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		//echo "year $year  month $month day $day<BR>";
		//echo "in display<BR>";

		$query = "SELECT id,calfacility,calroom,calpatient,calphysician,".
				"CONCAT(calhour,\":\",calminute) as caltime FROM ".$this->table_name.
                " WHERE MONTH(caldateof)='".$month."' AND".
                " YEAR(caldateof)='".$year."' AND".
				" DAYOFMONTH(caldateof)='".$day."'".
                " ORDER BY caltime";

		$result = $sql->query($query);

		if (!$result)
			trigger_error("Error reading scheduler table",E_ERROR);

		//echo "$query<BR>";
		echo freemed_display_itemlist (
            $result,
            $this->page_name,
            array (
                _("Time")  => "caltime",
                _("Room")  => "calroom",
				_("Facility") => "calfacility",
                _("Patient") => "calpatient",
				_("Physician")  => "calphysician"
            ),
            array ("", "", "", ""),
			array(
				  "",
				  "room"    => "roomname",
				  "facility" => "psrname",
				  "patient" => "ptlname",
				  "physician" => "phylname"
			     )	
        );
		
	} // end display

} // end of class AdminCalendar

register_module ("AdminCalendar");

} // end of "if defined"

?>
