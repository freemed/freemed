<?php
	// $Id$
	// $Author$
	// note: Physician Group Calendar
	// lic : GPL, v2

LoadObjectDependency('FreeMED.CalendarModule');

class GroupCalendar extends CalendarModule {

	var $MODULE_NAME = "Group Calendar";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

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

	function GroupCalendar () {
		// Set MetaInformation
		$this->_SetMetaInformation('global_config_vars', array(
			'cal1',
			'cal2',
			'cal3',
			'cal4',
			'cal5',
			'cal6',
			'cal7',
			'cal8',
		));
		$this->_SetMetaInformation('global_config', array(
			__("Calendar Category")." 1" =>	
			'html_form::text_widget("cal1", 20, 50)',

			__("Calendar Category")." 2" =>	
			'html_form::text_widget("cal2", 20, 50)',

			__("Calendar Category")." 3" =>	
			'html_form::text_widget("cal3", 20, 50)',

			__("Calendar Category")." 4" =>	
			'html_form::text_widget("cal4", 20, 50)',

			__("Calendar Category")." 5" =>	
			'html_form::text_widget("cal5", 20, 50)',

			__("Calendar Category")." 6" =>	
			'html_form::text_widget("cal6", 20, 50)',

			__("Calendar Category")." 7" =>	
			'html_form::text_widget("cal7", 20, 50)',

			__("Calendar Category")." 8" =>	
			'html_form::text_widget("cal8", 20, 50)'
		));

		// Run constructor
		$this->CalendarModule();
	} // end constructor GroupCalendar	

	function mark_array() {
		$mark[__("Travel")] = "0";
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
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Create scheduler object
		$scheduler = CreateObject('FreeMED.Scheduler');

		// Create user object
		if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

		// Check for selected date
		global $selected_date;
		if (!isset($selected_date)) $selected_date = date("Y-m-d");
		if (!isset($mark)) { global $mark; $mark = 0; }

		// Set page title
		global $page_title; $page_title = __("Group Calendar");

		// Get facility for current room
		global $my_facility;
		$my_facility = $_COOKIE["default_facility"];

		// Determine if a physician group is set, if not, default
		global $group;
		if ($group < 1) {
			// Find first group that is defined for this fac
			$query = "SELECT * FROM phygroup ".
				( $my_facility > 0 ?
				"WHERE phygroupfac='".
				addslashes($my_facility)."'" : "" );
			$result = $sql->query($query);
			if ($sql->results($result)) {
				$r = $sql->fetch_array($result);
				$group = $r[id];
			} else {
				// print "LOST. KLUDGED.<BR>\n";
				$group = 1; // KLUDGE
			}
		}

		// Select all the physicians in the group
		$my_group = freemed::get_link_rec($group, "phygroup");
		if (strpos($my_group['phygroupdocs'], ':') > 0) {
			$physician_list = explode(":", $my_group[phygroupdocs]);
		} elseif (strpos($my_group['phygroupdocs'], ',') > 0) {
			$physician_list = explode(",", $my_group[phygroupdocs]);
		} else {
			$physician_list = array($my_group['phygroupdocs']);
		}

		// Map all physicians in this group
		unset($map); 
		$this->display_columns = 0;
		foreach ($physician_list AS $_garbage_ => $phy) {
			if ($phy>0) {
			// Create map
				$map[$phy] = $scheduler->multimap("SELECT * FROM ".
					"scheduler WHERE calphysician='".
					addslashes($phy)."' AND caldateof='".
					addslashes($selected_date)."'");
				$physicians[] = $phy;

				// Increment the multimap counter
				//print count($map[$phy])." columns for phy #$phy<br/>\n";
				$this->display_columns += count($map[$phy]);

				// Ensure that a single column is displayed
				if (count($map[$phy]) == 0) {
					$map[$phy][0] = $scheduler->map_init();
				}
			} // end if phy>0
		}

		// Create "other" map
		$map[0] = $scheduler->multimap(
				"SELECT * FROM scheduler WHERE ".
				"calphysician='0' AND ".
				"caldateof='".addslashes($selected_date)."' "
				//"AND calfacility='".
				//addslashes($my_facility)."'"
		);
		$physicians[] = 0;
		$this->display_columns += count($map[0]);
		if (count($map[0]) == 0) {
			$map[0][0] = $scheduler->map_init();
		}

		// Finally display the calendar
		$display_buffer .= $this->displayCalendar($physicians, $map);
	} // end function GroupCalendar->view

	function displayCalendar ($physicians, $map) {
		// Globalize everything
		foreach ($GLOBALS AS $k => $v) global ${$k};

		global $selected_date, $template, $mark, $scheduler;
		if (empty($selected_date)) $selected_date = date("Y-m-d");

		if (!is_object($scheduler)) $scheduler = CreateObject('FreeMED.Scheduler');

		// Display header
		$buffer .= "
		<!-- mini calendar -->
		<div align=\"CENTER\">
		<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\2\" ".
		"BORDER=\"0\">
		<tr>
		<td ALIGN=\"LEFT\" VALIGN=\"TOP\" CLASS=\"thinbox\">
		<form ACTION=\"module_loader.php\" METHOD=\"POST\">
		<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\"
		 WIDTH=\"100%\">
		<tr>
		<td COLSPAN=\"2\"><b>".__("Group Calendar")."</b>"." ".__("for")."
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"selected_date\" VALUE=\"".prepare($selected_date)."\"/>
			".html_form::select_widget(
				"group",
				freemed::query_to_array(
					"SELECT phygroupname AS k,".
					"id AS v FROM phygroup ".
					"ORDER BY phygroupname"
				),
				array('refresh' => true)
			)."
		</td></tr>
		<tr><td ALIGN=\"CENTER\" COLSPAN=\"2\">
		<b>".fm_date_print($selected_date)."</b><br/>
		<i>(".(count($physicians)-1)." ".__("physicians").")</i>
		</td></tr>
		<tr>
		<td>".__("Mark as")."</td>
		<td>".html_form::select_widget(
			"mark", $this->mark_array(), array('refresh'=>true)
		)."</td>
		</tr>
		</table>
		</form>
		</td>
		<td>".$scheduler->generate_calendar_mini(
				$selected_date,
				"module_loader.php?".
					"module=".urlencode($module)."&".
					"group=".urlencode($group)."&".
					"action=".urlencode($action)."&".
					"mark=".urlencode($mark)
		)."</td></tr>
		</table>
		</div>
		<br/>
		";

		// Add anesthesia display if that module is installed
		if (check_module("AnesthCalendar")) {
			// Check for someone covering this day
			$anquery = "SELECT * FROM anesth WHERE ".
				"andate='".addslashes($selected_date)."' AND ".
				"anfacility='".addslashes($_SESSION['default_facility'])."'";
			$anresult = $sql->query($anquery);
			if ($sql->results($anresult)) {
				$buffer .= "<div CLASS=\"reverse\">\n".
					"<b>".__("Anesthesiology Coverage").
					"</b> : ";
				unset($cov);
				while ($anr = $sql->fetch_array($anresult)) {
					$my_phy = CreateObject('FreeMED.Physician', $anr['anphysician']);
					$cov[] = $my_phy->fullName().
					"<a HREF=\"module_loader.php?".
					"module=".urlencode($this->MODULE_CLASS)."&".
					"action=".urlencode($action)."&".
					"selected_date=".urlencode($selected_date)."&".
					"group=".urlencode($group)."&".
					"facility=".urlencode($my_facility)."&".
					"id=".urlencode($my_phy->id)."&".
					"submit=delanesth#hour".$c_hour."\"".
					"><img SRC=\"lib/template/$template/img/cal_x.png\" BORDER=\"0\"/></a>\n";
				}
				$buffer .= join(", ", $cov);
				$buffer .= "</div>\n";
			}
		}

		$buffer .= "
		<!-- full calendar -->
		<div ALIGN=\"CENTER\">
		<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\" ".
		"BORDER=\"0\" CLASS=\"calendar\">
		<tr><td COLSPAN=\"2\">&nbsp;</td>
		";
		foreach ($physicians AS $k => $v) {
			if ($v >= 0) {
				$p[$k] = CreateObject('FreeMED.Physician', $v);
			}
			$buffer .= "<td ALIGN=\"LEFT\" ".
			"STYLE=\"border: 1px solid; \" ".
			"COLSPAN=\"".count($map[$v])."\"><b>".
			($v!=0 ? $p[$k]->fullName() : __("Other") ).
			"</b></td>\n";
		}
		$buffer .= "</tr>\n";

		// Loop through hours
		for ($c_hour=freemed::config_value("calshr");
				$c_hour<freemed::config_value("calehr");
				$c_hour++) {
			// Beginning of hour row
			$buffer .= "
			<tr><td VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"4\" ".
			"CLASS=\"calcell_hour\" WIDTH=\"7%\"
			><a NAME=\"hour$c_hour\" /><b>".
			$scheduler->display_hour($c_hour)."</b></td>
			";

			for ($c_min="00"; $c_min<60; $c_min+=15) {
				// Create map index
				$idx = $c_hour.":".$c_min;

				// Start with table headers
				$buffer .= "
				".( ($c_min>0) ? "<tr>" : "" ).
				"<td>:".$c_min."</td>
				";

				// Loop through physicians
				foreach ($physicians AS $_g => $this_phy) {
					foreach ($map[$this_phy] AS $map_key => $cur_map) {
					// If there is an event, display
					$event = false;
					if (($cur_map[$idx]['span']+0) == 0) {
						// skip this
						$event = true;
					} elseif (($cur_map[$idx]['link']+0) != 0) {
						$event = true;
						$buffer .= "<td COLSPAN=\"1\" ".
							"ROWSPAN=\"".$cur_map[$idx]['span']."\" ".
							"ALIGN=\"LEFT\" ".
							"CLASS=\"calmark".($cur_map[$idx]['mark']+0)."\">".
							$scheduler->event_calendar_print(
								$cur_map[$idx]['link']
							).html_form::confirm_link_widget(
								"module_loader.php?".
								"module=".urlencode($this->MODULE_CLASS)."&".
								"action=".urlencode($action)."&".
								"selected_date=".urlencode($selected_date)."&".
								"group=".urlencode($group)."&".
								"id=".$cur_map[$idx]['link']."&".
								"submit=delappt#hour".$c_hour,
								"<img SRC=\"lib/template/$template/img/cal_x.png\" BORDER=\"0\" ".
								"alt=\"".__("DEL")."\"/>",
								array(
									'confirm_text' =>
									__("Are you sure you want to remove this booking?"),
									'text' => __("Delete")
								)
							)."</td>\n";
					} else {
						// Handle empty event
						$buffer .= "<td COLSPAN=\"1\" CLASS=\"cell\" ALIGN=\"LEFT\" VALIGN=\"MIDDLE\">\n";
						$check = array (
							15 => "0:15",
							30 => "0:30",
							45 => "0:45",
							60 => "1:00"
						);
						foreach ($check as $k => $v) {
							if ($scheduler->map_fit( $cur_map, $idx, $k)) {
								$buffer .= template::link_button($v,
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
									$c_hour, 
									array('type' => 'button_small')).
									"&nbsp;";
							} // end if fit
						} // end foreach
						$buffer .= "</td>\n";
					} // end if/else
					} // end for each current map
				} // end loop thru physicians

			} // end loop through minutes

			// End of hour/row
			$buffer .= "</tr>\n";
		} // end loop through hours

		// Display footer
		$buffer .= "
		</table>
		</div>
		<br/><br/>
		<div ALIGN=\"CENTER\">
		".template::link_button(__("Calendar"), "calendar.php")."
		".template::link_button(__("Select a Patient"), "patient.php")."
		".template::link_button(__("Return to Main Menu"), "main.php")."
		</div>
		";

		return $buffer;
	} // end function GroupCalendar->displayCalendar

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
	} // end function GroupCalendar->delete_anesth

	function delete_appt() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};

		// Delete selected entry
		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'";
		$result = $sql->query($query);
	} // end function GroupCalendar->delete_appt

	function mark_lookup($var) {
		global ${$var};

		switch (${$var}) {
			case 1: case 2: case 3: case 4:
			case 5: case 6: case 7: case 8:
			return freemed::config_value("cal".${$var});
			break;

			default: return __("Travel"); break;
		}

	} // end function GroupCalendar->mark_lookup

	function travel_book() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};
		global $mark, $scheduler;

		if (!is_object($scheduler)) $scheduler = CreateObject('FreeMED.Scheduler');

		// Insert a travel entry in the appropriate spot
		$result = $scheduler->set_appointment(
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

		return $result;
	} // end function GroupCalendar->travel_book

} // end class GroupCalendar

register_module ("GroupCalendar");

?>
