<?php
 // $Id$
 // note: letters of referral, etc
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

if (!defined("__LETTERS_EMR_MODULE_PHP__")) {

define ('__LETTERS_EMR_MODULE_PHP__', true);

class lettersModule extends freemedEMRModule {

	var $MODULE_NAME    = "Letters";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "Letters";
	var $table_name     = "letters";
	var $patient_field  = "letterpatient";
	var $summary_view_link = true;

	function lettersModule () {
		// Set vars for patient management summary
		$this->summary_vars = array (
			_("Date") => "letterdt",
			_("To")   => "letterto:physician"
		);

		// Variables for add/mod
		global $patient;
		$this->variables = array (
			"letterdt" => date_assemble("letterdt"),
			"letterfrom",
			"letterto",
			"lettertext",
			"letterpatient" => $patient
		);

		// The current table definition
		$this->table_definition = array (
			"letterdt" => SQL_DATE,
			"letterfrom" => SQL_VARCHAR(150),
			"letterto" => SQL_VARCHAR(150),
			"lettertext" => SQL_TEXT,
			"lettersent" => SQL_INT_UNSIGNED(0),
			"letterpatient" => SQL_INT_UNSIGNED(0),
			"id" => SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);
		$this->freemedEMRModule();
	} // end constructor lettersModule

	function form () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global ${$k};

		switch ($action) { // internal action switch
			case "addform":
				// Check for this_user object
				global $this_user;
				if (!is_object($this_user)) {
					$this_user = new User();
				}

				// If we're a physician, use us
				if ($this_user->isPhysician()) {
					global $letterfrom;
					$letterfrom = $this_user->user_phy;
				}
			break; // end internal addform
			case "modform":
			if (($patient<1) OR (empty($patient))) {
				$display_buffer .= _("You must select a patient.")."\n";
				template_display ();
			}
			$r = freemed::get_link_rec ($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = stripslashes($v);
			}
			extract ($r);
			break; // end internal modform
		} // end internal action switch

		$display_buffer .= "
		<P>
	<FORM ACTION=\"$this->page_name\" METHOD=POST>
	<INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"".
		( ($action=="addform") ? "add" : "mod" )."\">
	<INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"".prepare($id)."\">
	<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
	<INPUT TYPE=HIDDEN NAME=\"module\"  VALUE=\"".prepare($module)."\">
		";

		$display_buffer .= html_form::form_table(array(
		_("Date") =>
		date_entry("letterdt"),

		_("From") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician WHERE phyref='no' ".
				"ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterfrom"
		),
		

		_("To") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterto"
		),

		_("Text") =>
		html_form::text_area("lettertext", 20, 4),

		));
 
		$display_buffer .= "
       <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"  ".
         ( ($action=="addform") ? _("Add") : _("Modify"))."  \">
       <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">
       </CENTER>
       </FORM>
		";
	} // end function lettersModule->form

	function display () {
		global $display_buffer, $patient, $action, $id, $title,
			$return, $SESSION;

		global $print;
		if ($print) {
			global $no_template_display; $no_template_display=true;
		}

		$title = _("View Letter");

		// Get link record
		$record = freemed::get_link_rec($id, $this->table_name);

		// Resolve docs
		$from = new Physician ($record[letterfrom]);
		$to   = new Physician ($record[letterto]  );

		$display_buffer .= "
		<DIV ALIGN=\"CENTER\" CLASS=\"infobox\">
		<TABLE BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
		"CELLPADDING=\"2\">
		<TR>
			<TD ALIGN=\"RIGHT\" WIDTH=\"25%\">"._("Date")."</TD>
			<TD ALIGN=\"LEFT\" WIDTH=\"75%\">".$record[letterdt]."</TD>
		</TR>
		<TR>
			<TD ALIGN=\"RIGHT\">"._("From")."</TD>
			<TD ALIGN=\"LEFT\">".$from->fullName()."</TD>
		</TR>
		<TR>
			<TD ALIGN=\"RIGHT\">"._("To")."</TD>
			<TD ALIGN=\"LEFT\">".$to->fullName()."</TD>
		</TR>
		</TABLE>
		</DIV>
		<DIV ALIGN=\"LEFT\" CLASS=\"letterbox\">
		".stripslashes(str_replace("\n", "<BR>", htmlentities($record[lettertext])))."
		</DIV>
		<P>
		<DIV ALIGN=\"CENTER\">
		<A HREF=\"".( ($return=="manage") ? "manage.php?id=".
		$SESSION[current_patient] :
		"module_loader.php?module=".$this->MODULE_CLASS )."\"
		>".( ($return=="manage") ? _("Manage Patient") : _("back") ).
		"</A> | ".( !$print ?
		"<A HREF=\"module_loader.php?".
			"module=".urlencode($this->MODULE_CLASS)."&".
			"patient=".urlencode($patient)."&".
			"action=".urlencode($action)."&".
			"id=".urlencode($id)."&".
			"return=".urlencode($return)."&".
			"print=1\">"._("Print View")."</A>" :
		"<A HREF=\"module_loader.php?".
			"module=".urlencode($this->MODULE_CLASS)."&".
			"patient=".urlencode($patient)."&".
			"action=".urlencode($action)."&".
			"id=".urlencode($id)."&".
			"return=".urlencode($return)."&".
			"print=0\">"._("Standard View")."</A>"
		)."
		</DIV>
		";
	} // end function lettersModule->display

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

	$query = "SELECT * FROM ".$this->table_name." ".
		"WHERE (".$this->patient_field."='".addslashes($patient)."') ".
		"ORDER BY letterdt";
	$result = $sql->query ($query);
	$rows = ( ($result > 0) ? $sql->num_rows ($result) : 0 );

	if ($rows < 1) {
		$display_buffer .= "
         <P>
         <CENTER>
         "._("This patient has no letters.")."
         </CENTER>
         <P>
         <CENTER>
         <A HREF=\"$this->page_name?action=addform&module=$module&patient=$patient\"
          >"._("Add")." "._("$record_name")."</A>
         <B>|</B>
         <A HREF=\"manage.php?id=$patient\"
          >"._("Manage Patient")."</A>
         </CENTER>
         <P>
		";
		template_display();
	} // if there are none...

	// or else, display them...
	$display_buffer .= "
		<P>".
		freemed_display_itemlist (
			$result,
			$this->page_name,
			array (
				_("Date") => "letterdt",
				_("From") => "letterfrom",
				_("To") => "letterto"
			),
			array ("", "", ""),
			array (
				"",
				"physician" => "phylname",
				// This is a workaround because it relies on
				// key/value pairs, and it cannot have a
				// duplicate key without being *very* funny.
				"physician " => "phylname"
			)
		);
	} // end function lettersModule->view()

} // end class lettersModule

register_module ("lettersModule");

} // end if defined

?>
