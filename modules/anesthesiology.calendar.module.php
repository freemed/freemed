<?php
 // $Id$
 // $Author$
 // note: Anesthesiology Calendar Module /w Admin
 // lic : GPL, v2

LoadObjectDependency('FreeMED.CalendarModule');

class AnesthCalendar extends CalendarModule {

	var $MODULE_NAME = "Anesthesiology Calendar";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $ICON = "img/karm.gif";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Scheduler";
	var $table_name  = "anesth";

	var $variables = array (
		"andate",
		"anphysician",
		"anfacility"
	);

	function AnesthCalendar () {
		// run constructor
		$this->CalendarModule();
	} // end constructor AnesthCalendar	

	function view () {
		global $display_buffer, $anfacility;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Check for calendar modification
		//if ($submit=="travelbook") $this->bulk_book();
		if ($submit==__("Book")) $this->single_book();

		// Check for calendar deletions
		if ($submit==__("Delete")) $this->delete_date();

		// For extra space, turn off template
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Create user object
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Check for set anfacility
		if (!isset($anfacility)) {
			$anfacility = $_SESSION['default_facility'];
		}

		// Set page title
		global $page_title; $page_title = __("Group Calendar");

		// Grab the form and display it.
		$display_buffer .= $this->displayForm();
	} // end function AnesthCalendar->view

	function displayForm ( ) {
		// Globalize everything
		foreach ($GLOBALS AS $k => $v) global ${$k};

		global $selected_date, $template, $mark, $scheduler;
		if (empty($selected_date)) $selected_date = date("Y-m-d");

		if (!is_object($scheduler)) $scheduler = CreateObject('FreeMED.Scheduler');

		// Display header
		$buffer .= "
		<!-- mini calendar -->
		<div ALIGN=\"CENTER\">
		<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\2\" ".
		"BORDER=\"0\">
		<tr>
		<td ALIGN=\"LEFT\" VALIGN=\"TOP\" CLASS=\"thinbox\">
		<form ACTION=\"module_loader.php\" METHOD=\"POST\">
		<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\"
		 WIDTH=\"100%\">
		<tr>
		<td COLSPAN=\"2\"><b>".__("Anesthesiology Scheduler")."</b> for
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"selected_date\" VALUE=\"".prepare($selected_date)."\"/>
			".html_form::select_widget(
				"anfacility",
				freemed::query_to_array(
					"SELECT CONCAT(psrname,' (',".
					"psrcity,', ',psrstate,')') AS k,".
					"id AS v FROM facility ".
					"ORDER BY psrname,psrstate,psrcity"
				),
				array('refresh' => true)
			)."
		</td></tr>
		</table>
		</td>
		<td>".$scheduler->generate_calendar_mini(
				$selected_date,
				"module_loader.php?".
					"module=".urlencode($module)."&".
					"anfacility=".urlencode($anfacility)."&".
					"action=".urlencode($action)
		)."</td></tr>
		</table>
		</div>
		<br/>

		<!-- full calendar -->
		<div ALIGN=\"CENTER\">
		<table WIDTH=\"100%\" CELLSPACING=\0\" CELLPADDING=\"2\" ".
		"BORDER=\"0\" CLASS=\"calendar\">
		<tr><td>
		".__("Book")."
		".html_form::select_widget("anphysician",
			freemed::query_to_array(
				"SELECT CONCAT(phylname,', ',".
				"phyfname) AS k,".
				"id AS v FROM physician ".
				"WHERE phyanesth='1' ".
				"ORDER BY phylname, phyfname"
			)
		)." ".__("on selected date")."
		</td>
		<td>
		<input class=\"button\" TYPE=\"SUBMIT\" NAME=\"submit\" ".
			"VALUE=\"".__("Book")."\"/>
		</form>
		</td></tr>
		</table>
		</div>
		<p/>
		".template::link_bar(array(
			__("Calendar") => "calendar.php",
			__("Return to Main Menu") => "main.php"
		));

		return $buffer;
	} // end function AnesthCalendar->displayForm

	// ----- "sub-action" section

	// Book single day of anesthesiologist
	function single_book() {
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Determine if day is already booked for this person,
		// if so, change it.
		$result = $sql->query("SELECT * FROM $this->table_name ".
			"WHERE andate='".addslashes($selected_date)."' AND ".
			"anphysician='".addslashes($anphysician)."'");
		if ($sql->results($result)) {
			$old = $sql->fetch_array($result);
			$result = $sql->query($sql->update_query(
				$this->table_name,
				array (
					"anfacility" => $anfacility
				),
				array ( "id" => $old[id] )
			));
		} else {
			$result = $sql->query($sql->insert_query(
				$this->table_name,
				array (
					"andate" => $selected_date,
					"anphysician" => $anphysician,
					"anfacility" => $anfacility
				)
			));
		}
	} // end function AnesthCalendar->single_book

	function delete_date() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};

		// Delete selected entry
		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'";
		$result = $sql->query($query);
	} // end function AnesthCalendar->delete_date

	function bulk_book() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};
		global $mark;

		// Insert a travel entry in the appropriate spot
		$query = $sql->insert_query(
			$this->table_name,
			array(
			)
		);
		$result = $sql->query($query);
	} // end function AnesthCalendar->bulk_book

} // end class AnesthCalendar

register_module ("AnesthCalendar");

?>
