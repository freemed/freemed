<?php
  // $Id$
  // note: calendar admin
  // code: fred forester fforest@netcarrier.com
  // lic : GPL, v2

LoadObjectDependency('FreeMED.CalendarModule');

class ViewCalendar extends CalendarModule {

	var $MODULE_NAME = "Calendar View";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

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
		$this->CalendarModule();
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
		$display_buffer .= "<form ACTION=\"$this->page_name\" METHOD=\"POST\">\n";
		$display_buffer .= "<div align=\"CENTER\" valign=\"MIDDLE\">\n";
		$display_buffer .= "<a class=\"button\" HREF=\"$this->page_name?".
			"action=view&module=$module&jumpdate=$prevdate\">Prev</a>\n";
		$display_buffer .= "&nbsp;";
		$display_buffer .= "<a class=\"button\" HREF=\"$this->page_name".
			"?action=view&module=$module&jumpdate=$nextdate\">Next</a>\n";

		// allow jumping to any year or month
		$display_buffer .= 
			 "<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>\n".
			 fm_date_entry("jumpdate").
			 "<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\""._("Go")."\"/>\n".
			 "<a class=\"button\" href=\"calendar.php\">".
			 	_("Calendar")."</a>\n".
			 "</div></form>\n";
	} // end function module->view

	// display the pop window when an event is clicked on the calendar
	function display () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
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

		// Time formatting
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

?>
