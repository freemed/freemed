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
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		$this->SetProcessType("ADMIN");

		if (!$jumpdate)
			$jumpdate = $cur_date;

		if (isset($jumpdate_y))
			$jumpdate = fm_date_assemble("jumpdate");

		//$display_buffer .= "jumpdate $jumpdate<BR>";

		$year  = substr($jumpdate,0,4);
		$month = substr($jumpdate,5,2);
		$day   = substr($jumpdate,8,2);

		$query = "SELECT *,CONCAT(LPAD(calhour,2,'0'),\":\",LPAD(calminute,2,'0')) as caltime from scheduler".
				 " WHERE YEAR(caldateof)='".prepare($year)."'".
				 " AND MONTH(caldateof)='".prepare($month)."'".
				 " ORDER BY calhour,calminute";

		//$display_buffer .= "$query<BR>";
		$result = $sql->query($query);

		$display_buffer .= freemed_display_itemlist (
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
		//$data = freemed_display_itemlist($result,
		//								$this->page_name,
		//								array("Time" => "caltime"),
		//								array(""));
		//$display_buffer .= "$data";
		$this->month($month,$year);
		//$display_buffer .= "<CENTER><B>Calendar For: $this->month_name, $this->year</B></CENTER><br>\n";
		//$this->draw(array("cellspacing" => "2" , "cellpadding" => "2" ,
        //              "top_row_align" => "center" , "table_height" => "300px" ,
        //              "top_row_cell_height" => 20 , "bgcolor" => "#cccccc" ,
        //              "row_align" => "left" , "row_valign" => "top" ,
        //              "font_size" => "-1") );

		$nextdate = $this->nextyear."-".$this->nextmonth."-01";
		$prevdate = $this->prevyear."-".$this->prevmonth."-01";

		// prev  and next
		$display_buffer .= "<CENTER>";
		$display_buffer .= "<A HREF=\"$this->page_name?".
			"action=view&module=$module&jumpdate=$prevdate\">Prev</A>";
		$display_buffer .= "&nbsp;";
		$display_buffer .= "<A HREF=\"$this->page_name?".
			"action=view&module=$module&jumpdate=$nextdate\">Next</A>";
		$display_buffer .= "</CENTER>";

		// allow jumping to any year or month
		$display_buffer .= "<CENTER>";
		$display_buffer .= "<FORM ACTION=\"$this->page_name\" METHOD=POST>".
			 "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">".
			 fm_date_entry("jumpdate").
			 "<INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">".
			 "</FORM></CENTER>";

		$display_buffer .= "<CENTER>";
		$display_buffer .= "<A HREF=\"calendar.php\">"._("Menu")."</A>";
		$display_buffer .= "</CENTER>";


	} // end function module->view

	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
	} // end function AdminCalendar->form

	function addform () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
		//$display_buffer .= "jumpdate $jumpdate<BR>";

		$display_buffer .= "pat $patient cur $current_patient<BR>";

		//$wizard = new wizard(
	} // end function AdminCalendar->form

	function display () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		//$display_buffer .= "year $year  month $month day $day<BR>";
		//$display_buffer .= "in display<BR>";

		$query = "SELECT id,calfacility,calroom,calpatient,calphysician,".
				"CONCAT(calhour,\":\",calminute) as caltime FROM ".$this->table_name.
                " WHERE MONTH(caldateof)='".$month."' AND".
                " YEAR(caldateof)='".$year."' AND".
				" DAYOFMONTH(caldateof)='".$day."'".
                " ORDER BY caltime";

		$result = $sql->query($query);

		if (!$result)
			trigger_error("Error reading scheduler table",E_ERROR);

		//$display_buffer .= "$query<BR>";
		$display_buffer .= freemed_display_itemlist (
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
