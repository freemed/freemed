<?php
 // $Id$
 // $Author$
 // note: Physician Group Calendar
 // lic : GPL, v2

if (!defined("__GROUP_CALENDAR_MODULE_PHP__")) {

define ('__GROUP_CALENDAR_MODULE_PHP__', true);

class groupCalendar extends freemedCalendarModule {

	var $MODULE_NAME = "Group Calendar";
	var $MODULE_VERSION = "0.1";
	var $ICON = "img/karm.gif";

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

	function groupCalendar () {
		// run constructor
		$this->freemedCalendarModule();
	} // end constructor groupCalendar	

	function mark_array() {
		$mark[_("Travel")] = "0";
		for ($i=1;$i<=8;$i++) {
			$val = freemed::config_value("cal" . $i);
			if (!empty($val)) {
				$mark[$val] = "$i";
			}
		}
		return $mark;
	} // end function mark_array

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global ${$k};

		// Check for calendar modification
		if ($submit=="travelbook") $this->travel_book();

		// Check for calendar deletions
		if ($submit=="delappt") $this->delete_appt();
		if ($submit=="delanesth") $this->delete_anesth();

		// For extra space, turn off template
		global $no_template_display; $no_template_display = true;

		// Create user object
		if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

		// Check for selected date
		global $selected_date;
		if (!isset($selected_date)) $selected_date = date("Y-m-d");
		if (!isset($mark)) { global $mark; $mark = 0; }

		// Set page title
		global $page_title; $page_title = _("Group Calendar");

		// Get facility for current room
		global $my_facility;
		$my_facility = $SESSION["default_facility"];

		// Determine if a physician group is set, if not, default
		global $group;
		if ($group < 1) {
			// Find first group that is defined for this fac
			$query = "SELECT * FROM phygroup ".
				"WHERE phygroupfac='".
				addslashes($my_facility)."'";
			$result = $sql->query($query);
			if ($sql->results($result)) {
				$r = $sql->fetch_array($result);
				$group = $r[id];
			} else {
				print "LOST. KLUDGED.<BR>\n";
				$group = 1; // KLUDGE
			}
		}

		// Select all the physicians in the group
		$my_group = freemed::get_link_rec($group, "phygroup");
		$physician_list = explode(":", $my_group[phygroupdocs]);

		// Map all physicians in this group
		unset($map);
		foreach ($physician_list AS $_garbage_ => $phy) {
			if ($phy>0) {
			// Create map
			$map[$phy] = freemedCalendar::map("SELECT * FROM ".
				"scheduler WHERE calphysician='".
				addslashes($phy)."' AND caldateof='".
				addslashes($selected_date)."'");
			$physicians[] = $phy;
			} // end if phy>0
		}

		// Create "other" map
		$map[0] = freemedCalendar::map(
				"SELECT * FROM scheduler WHERE ".
				"calphysician='0' AND ".
				"caldateof='".addslashes($selected_date)."' "
				//"AND calfacility='".
				//addslashes($my_facility)."'"
		);
		$physicians[] = 0;

		// Finally display the calendar
		$display_buffer .= $this->displayCalendar($physicians, $map);
	} // end function groupCalendar->view

	function displayCalendar ($physicians, $map) {
		// Globalize everything
		foreach ($GLOBALS AS $k => $v) global ${$k};

		global $selected_date, $template, $mark;
		if (empty($selected_date)) $selected_date = date("Y-m-d");

		// Display header
		$buffer .= "
		<!-- mini calendar -->
		<DIV ALIGN=\"CENTER\">
		<TABLE WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\2\" ".
		"BORDER=\"0\">
		<TR>
		<TD ALIGN=\"LEFT\" VALIGN=\"TOP\" CLASS=\"thinbox\">
		<FORM ACTION=\"module_loader.php\" METHOD=\"POST\">
		<TABLE BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\"
		 WIDTH=\"100%\">
		<TR>
		<TD COLSPAN=\"2\"><B>"._("Group Calendar")."</B> for
		<INPUT TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"selected_date\" VALUE=\"".prepare($selected_date)."\">
			".html_form::select_widget(
				"group",
				freemed::query_to_array(
					"SELECT phygroupname AS k,".
					"id AS v FROM phygroup ".
					"ORDER BY phygroupname"
				),
				array('refresh' => true)
			)."
		</TD></TR>
		<TR><TD ALIGN=\"CENTER\" COLSPAN=\"2\">
		<B>".fm_date_print($selected_date)."</B><BR>
		<I>(".(count($physicians)-1)." "._("physicians").")</I>
		</TD></TR>
		<TR>
		<TD>"._("Mark as")."</TD>
		<TD>".html_form::select_widget(
			"mark", $this->mark_array(), array('refresh'=>true)
		)."</TD>
		</TR>
		</TABLE>
		</FORM>
		</TD>
		<TD>".fc_generate_calendar_mini(
				$selected_date,
				"module_loader.php?".
					"module=".urlencode($module)."&".
					"group=".urlencode($group)."&".
					"action=".urlencode($action)."&".
					"mark=".urlencode($mark)
		)."</TD></TR>
		</TABLE>
		</DIV>
		<BR>
		";

		// Add anesthesia display if that module is installed
		if (check_module("anesthCalendar")) {
			// Check for someone covering this day
			$anquery = "SELECT * FROM anesth WHERE ".
				"andate='".addslashes($selected_date)."' AND ".
				"anfacility='".addslashes($SESSION['default_facility'])."'";
			$anresult = $sql->query($anquery);
			if ($sql->results($anresult)) {
				$buffer .= "<div CLASS=\"reverse\">\n".
					"<b>"._("Anesthesiology Coverage").
					"</b> : ";
				unset($cov);
				while ($anr = $sql->fetch_array($anresult)) {
					$my_phy = CreateObject('FreeMED.Physician', $anr['anphysician']);
					$cov[] = $my_phy->fullName().
					"<A HREF=\"module_loader.php?".
					"module=".urlencode($this->MODULE_CLASS)."&".
					"action=".urlencode($action)."&".
					"selected_date=".urlencode($selected_date)."&".
					"group=".urlencode($group)."&".
					"facility=".urlencode($my_facility)."&".
					"id=".urlencode($my_phy->id)."&".
					"submit=delanesth#hour".$c_hour."\"".
					"><IMG SRC=\"lib/template/$template/img/cal_x.png\" BORDER=\"0\"></A>\n";
				}
				$buffer .= join(", ", $cov);
				$buffer .= "</div>\n";
			}
		}

		$buffer .= "
		<!-- full calendar -->
		<div ALIGN=\"CENTER\">
		<table WIDTH=\"100%\" CELLSPACING=\0\" CELLPADDING=\"2\" ".
		"BORDER=\"0\" CLASS=\"calendar\">
		<TR><TD COLSPAN=\"2\">&nbsp;</TD>
		";
		foreach ($physicians AS $k => $v) {
			if ($k!=1)
				$p[$k] = CreateObject('FreeMED.Physician', $v);
			$buffer .= "<td ALIGN=\"CENTER\"><b>".
				($v!=0 ? $p[$k]->fullName() : _("Other") ).
				"</b></td>\n";
		}
		$buffer .= "</TR>\n";

		// Loop through hours
		for ($c_hour=freemed::config_value("calshr");
				$c_hour<freemed::config_value("calehr");
				$c_hour++) {
			// Beginning of hour row
			$buffer .= "
			<TR><TD VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"4\" ".
			"CLASS=\"calcell_hour\" WIDTH=\"7%\"
			><A NAME=\"hour$c_hour\" /><B>".
			freemedCalendar::display_hour($c_hour)."</B></TD>
			";

			for ($c_min="00"; $c_min<60; $c_min+=15) {
				// Create map index
				$idx = $c_hour.":".$c_min;

				// Start with table headers
				$buffer .= "
				".( ($c_min>0) ? "<TR>" : "" ).
				"<TD>:".$c_min."</TD>
				";

				// Loop through physicians
				foreach ($physicians AS $_g => $this_phy) {
					
					// If there is an event, display
					if ($map[$this_phy][$idx][span] == 0) {
						// skip this
					} elseif ($map[$this_phy][$idx][link] != 0) {
						$buffer .= "<TD COLSPAN=\"1\" ".
							"ROWSPAN=\"".$map[$this_phy][$idx][span]."\" ".
							"ALIGN=\"LEFT\" ".
							"CLASS=\"calmark".($map[$this_phy][$idx][mark]+0)."\">".
							freemedCalendar::event_calendar_print(
								$map[$this_phy][$idx][link]
							)."<A HREF=\"module_loader.php?".
							"module=".urlencode($this->MODULE_CLASS)."&".
							"action=".urlencode($action)."&".
							"selected_date=".urlencode($selected_date)."&".
							"group=".urlencode($group)."&".
							"id=".$map[$this_phy][$idx][link]."&".
							"submit=delappt#hour".$c_hour."\"
							><IMG SRC=\"lib/template/$template/img/cal_x.png\" BORDER=\"0\"></A>".
							"</TD>\n";
					} else {
						// Handle empty event
						$buffer .= "<TD COLSPAN=\"1\" CLASS=\"cell\" ALIGN=\"LEFT\" VALIGN=\"MIDDLE\">\n";
						$check = array (
							15 => "0_15",
							30 => "0_30",
							45 => "0_45",
							60 => "1_00"
						);
						foreach ($check as $k => $v) {
							if (freemedCalendar::map_fit(
									$map[$this_phy],
									$idx,
									$k)) { 
								$buffer .= "<A HREF=\"".
									"module_loader.php?".
									"module=".$this->MODULE_CLASS."&".
									"selected_date=".urlencode($selected_date)."&".
									"group=".urlencode($group)."&".
									"physician=".urlencode($this_phy)."&".
									"duration=".urlencode($k)."&".
									"hour=".urlencode($c_hour)."&".
									"minute=".urlencode($c_min)."&".
									"mark=".urlencode($mark)."&".
									"submit=travelbook#hour".
									$c_hour."\"".
									"><IMG SRC=\"lib/template/$template/img/cal_".
									$v.".png\" BORDER=\"0\"></A>";
							} // end if fit
						} // end foreach
						$buffer .= "</TD>\n";
					}

				} // end loop thru physicians

			} // end loop through minutes

			// End of hour/row
			$buffer .= "</TR>\n";
		} // end loop through hours

		// Display footer
		$buffer .= "
		</TABLE>
		</DIV>
		<BR><BR>
		<DIV ALIGN=\"CENTER\">
		<A HREF=\"calendar.php\">"._("Calendar")."</A> |
		<A HREF=\"main.php\">"._("Return to Main Menu")."</A>
		</DIV>
		";

		return $buffer;
	} // end function groupCalendar->displayCalendar

	// ----- "sub-action" section

	function delete_anesth() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};

		// Delete selected entry
		$query = "DELETE FROM anesth ".
			"WHERE andate='".addslashes($selected_date)."' AND ".
			"anphysician='".addslashes($id)."' AND ".
			"anfacility='".addslashes($facility)."'";
		$result = $sql->query($query);
	} // end function groupCalendar->delete_anesth

	function delete_appt() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};

		// Delete selected entry
		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'";
		$result = $sql->query($query);
	} // end function groupCalendar->delete_appt

	function mark_lookup($var) {
		global ${$var};

		switch (${$var}) {
			case 1: case 2: case 3: case 4:
			case 5: case 6: case 7: case 8:
			return freemed::config_value("cal".${$var});
			break;

			default: return _("Travel"); break;
		}

	} // end function groupCalendar->mark_lookup

	function travel_book() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};
		global $mark;

		// Insert a travel entry in the appropriate spot
		$query = $sql->insert_query(
			$this->table_name,
			array(
				"caldateof" => $selected_date,
				"calphysician" => $physician,
				"calduration" => $duration,
				"calhour" => $hour,
				"calminute" => $minute,
				"calfacility" => ( 
					$physician==0 ?
					$my_facility :
					"0" ),
				"calroom" => "0",
				"calpatient" => "0",
				"calprenote" => $this->mark_lookup("mark"),
				"calmark" => $mark
			)
		);
		$result = $sql->query($query);
	} // end function groupCalendar->travel_book

} // end class groupCalendar

register_module ("groupCalendar");

} // end if !defined

?>
