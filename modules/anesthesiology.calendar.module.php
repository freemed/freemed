<?php
 // $Id$
 // $Author$
 // note: Anesthesiology Calendar Module /w Admin
 // lic : GPL, v2

if (!defined("__ANESTH_CALENDAR_MODULE_PHP__")) {

define ('__ANESTH_CALENDAR_MODULE_PHP__', true);

class anesthCalendar extends freemedCalendarModule {

	var $MODULE_NAME = "Anesthesiology Calendar";
	var $MODULE_VERSION = "0.1";
	var $ICON = "img/karm.gif";

	var $record_name = "Scheduler";
	var $table_name  = "anesth";

	var $variables = array (
		"andate",
		"anphysician",
		"anfacility"
	);

	function anesthCalendar () {
		// run constructor
		$this->freemedCalendarModule();
	} // end constructor anesthCalendar	

	function view () {
		global $display_buffer, $anfacility;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global ${$k};

		// Check for calendar modification
		//if ($submit=="travelbook") $this->bulk_book();
		if ($submit=="book") $this->single_book();

		// Check for calendar deletions
		if ($submit=="delete") $this->delete_date();

		// For extra space, turn off template
		global $no_template_display; $no_template_display = true;

		// Create user object
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Check for set anfacility
		if (!isset($anfacility)) {
			$anfacility = $SESSION['default_facility'];
		}

		// Set page title
		global $page_title; $page_title = _("Group Calendar");

		// Grab the form and display it.
		$display_buffer .= $this->displayForm();
	} // end function anesthCalendar->view

	function displayForm ( ) {
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
		<TD COLSPAN=\"2\"><B>"._("Anesthesiology Scheduler")."</B> for
		<INPUT TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"selected_date\" VALUE=\"".prepare($selected_date)."\">
			".freemedCalendar::refresh_select(
				"anfacility",
				freemed::query_to_array(
					"SELECT CONCAT(psrname,' (',".
					"psrcity,', ',psrstate,')') AS k,".
					"id AS v FROM facility ".
					"ORDER BY psrname,psrstate,psrcity"
				)
			)."
		</TD></TR>
		</TABLE>
		</TD>
		<TD>".fc_generate_calendar_mini(
				$selected_date,
				"module_loader.php?".
					"module=".urlencode($module)."&".
					"anfacility=".urlencode($anfacility)."&".
					"action=".urlencode($action)
		)."</TD></TR>
		</TABLE>
		</DIV>
		<BR>

		<!-- full calendar -->
		<DIV ALIGN=\"CENTER\">
		<TABLE WIDTH=\"100%\" CELLSPACING=\0\" CELLPADDING=\"2\" ".
		"BORDER=\"0\" CLASS=\"calendar\">
		<TR><TD>
		"._("Book")."
		".html_form::select_widget("anphysician",
			freemed::query_to_array(
				"SELECT CONCAT(phylname,', ',".
				"phyfname) AS k,".
				"id AS v FROM physician ".
				"WHERE phyanesth='1' ".
				"ORDER BY phylname, phyfname"
			)
		)." "._("on selected date")."
		</TD>
		<TD>
		<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"book\">
		</FORM>
		</TD></TR>
		</TABLE>
		</DIV>
		<BR><BR>
		<DIV ALIGN=\"CENTER\">
		<A HREF=\"calendar.php\">"._("Calendar")."</A> |
		<A HREF=\"main.php\">"._("Return to Main Menu")."</A>
		</DIV>
		";

		return $buffer;
	} // end function anesthCalendar->displayForm

	// ----- "sub-action" section

	// Book single day of anesthesiologist
	function single_book() {
		foreach ($GLOBALS AS $k => $v) global ${$k};

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
	} // end function anesthCalendar->single_book

	function delete_date() {
		// Globalize
		foreach ($GLOBALS AS $k => $v) global ${$k};

		// Delete selected entry
		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'";
		$result = $sql->query($query);
	} // end function anesthCalendar->delete_date

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
	} // end function anesthCalendar->bulk_book

} // end class anesthCalendar

register_module ("anesthCalendar");

} // end if !defined

?>
