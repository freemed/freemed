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
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		//$display_buffer .= "pat $current_patient<BR>";
		if (!$jumpdate)
			$jumpdate = $cur_date;

		if (isset($jumpdate_y))
			$jumpdate = fm_date_assemble("jumpdate");

		//$display_buffer .= "jumpdate $jumpdate<BR>";

		$year  = substr($jumpdate,0,4);
		$month = substr($jumpdate,5,2);
		$day   = substr($jumpdate,8,2);

		$this->month($month,$year);
		$display_buffer .= "<CENTER><B>Calendar for $this->month_name $this->year</B></CENTER><br>\n";
		$this->draw();

		$nextdate = $this->nextyear."-".$this->nextmonth."-01";
		$prevdate = $this->prevyear."-".$this->prevmonth."-01";

		// prev  and next
		$display_buffer .= "<CENTER>";
		$display_buffer .= "<A HREF=\"$this->page_name?".
			"action=view&module=$module&jumpdate=$prevdate\">Prev</A>";
		$display_buffer .= "&nbsp;";
		$display_buffer .= "<A HREF=\"$this->page_name".
			"?action=view&module=$module&jumpdate=$nextdate\">Next</A>";
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
	} // end function ViewCalendar->form

	// display the pop window when an event is clicked on the calendar
	function display () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
		//global $ptname, $phname, $facname, $roomname, $time, $prenote, $postnote;

		$GLOBALS['__freemed']['no_template_display'] = true;

		$query = "SELECT * FROM scheduler WHERE id='".prepare($id)."'";
		$result = $sql->query($query);	
		
		if (!$result)
		{
			$display_buffer .= "Error<BR>";
			trigger_error("DB Error reading scheduler table",E_ERROR);
		}

		$row = $sql->fetch_array($result);
		//$display_buffer .= "row id $row[id]<BR>";
		if ($row[calpatient] != 0)
		{
			$this_patient = CreateObject('FreeMED.Patient', $row[calpatient]);
			$ptname = $this_patient->fullname();
			//$display_buffer .= "$ptname<BR>";
		}
		if ($row[calphysician] != 0)
		{
			$this_physician = CreateObject('FreeMED.Physician', $row[calphysician]);
			$phname = $this_physician->fullname();
		}
		if ($row[calfacility] != 0)
		{
			$fac = freemed::get_link_rec($row[calfacility],"facility");
			$facname = $fac[psrname];
		}

		if ($row[calroom] != 0)
		{
			$room = freemed::get_link_rec($row[calroom],"room");
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
		$display_buffer .= "<div CLASS=\"letterbox\">$data</div>\n";
		$display_buffer .= "<div ALIGN=\"CENTER\">\n".
			"<a HREF=\"javascript:window.close();\">".
			_("Close")."</a>\n".
			"</div>\n";
	
	} // end display

} // end of class ViewCalendar

register_module ("ViewCalendar");

} // end of "if defined"

?>
