<?php
  // $Id$
  // note: calendar admin
  // code: fred forester fforest@netcarrier.com
  // lic : GPL, v2

if (!defined("__VIEW_CALENDAR_MODULE_PHP__")) {

define (__VIEW_CALENDAR_MODULE_PHP__, true);

class ViewCalendar extends freemedCalendarModule {

	var $MODULE_NAME = "Calendar View";
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

	function ViewCalendar () {
		// run constructor
		$this->freemedCalendarModule();
	} // end constructor ViewCalendar	

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
	} // end function ViewCalendar->form

	function display () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
		//global $ptname, $phname, $facname, $roomname, $time, $prenote, $postnote;

		//echo "id $id<BR>";
		$query = "SELECT * FROM scheduler WHERE id='".prepare($id)."'";
		$result = $sql->query($query);	
		
		if (!$result)
		{
			echo "Error<BR>";
			trigger_error("DB Error reading scheduler table",E_ERROR);
		}

		$row = $sql->fetch_array($result);
		//echo "row id $row[id]<BR>";
		if ($row[calpatient] != 0)
		{
			$this_patient = new Patient($row[calpatient]);
			$ptname = $this_patient->fullname();
			//echo "$ptname<BR>";
		}
		if ($row[calphysician] != 0)
		{
			$this_physician = new Physician($row[calphysician]);
			$phname = $this_physician->fullname();
		}
		if ($row[calfacility] != 0)
		{
			$fac = freemed_get_link_rec($row[calfacility],"facility");
			$facname = $fac[psrname];
		}

		if ($row[calroom] != 0)
		{
			$room = freemed_get_link_rec($row[calroom],"room");
			$roomname = $room[roomname];
		}

		$prenote = $row[calprenote];
		$postnote = $row[calpostnote];

		if (empty($prenote))
			$prenote = _("None");

		if (empty($postnote))
			$postnote = _("None");

		$calminute = $row["calminute"];
		$calhour = $row["calhour"];

		//if ($calminute==0) $calminute="00";

		// time checking/creation if/else clause
		//if ($row["calhour"]<12)
		//	$_time = $row["calhour"].":".$calminute." AM";
		//elseif ($row["calhour"]==12)
		//	$_time = $row["calhour"].":".$calminute." PM";
		//else
		//	$_time = ($r["calhour"]-12).":".$calminute." PM";
		$time = fc_get_time_string($calhour,$calminute);
		

		$data = "";	
		$data = html_form::form_table(array (
									_("Patient") => $ptname,
									_("Physician") => $phname,
									_("Facility") => $facname,
									_("Room") => $roomname,
									_("Time") => $time,
									_("Pre Note") => $prenote,
									_("Post Note") => $postnote)
									);
		echo "$data";
	
	} // end display

} // end of class ViewCalendar

register_module ("ViewCalendar");

} // end of "if defined"

?>
