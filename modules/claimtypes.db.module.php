<?php
  // $Id$
  // note: Insurance Claim types database module
  // code: Fred Forester (fforest@netcarrier.com) -- modified a lot
  // lic : GPL, v2

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ClaimtypesMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Insurance Claim Types";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Claim Types";
	var $table_name  = "claimtypes";
	var $order_field = "clmtpname,clmtpdescrip";

	var $variables = array (
			"clmtpname",
			"clmtpdescrip",
			"clmtpdtadd",
			"clmtpdtmod"
	);

	function ClaimtypesMaintenance () {
		global $clmtpdtmod;
		$clmtpdtmod = date("Y-m-d");

		// Define table definition
		$this->table_definition = array (
			'clmtpname' => SQL__VARCHAR(5),
			'clmtpdescrip' => SQL__VARCHAR(60),
			'clmtpdtadd' => SQL__DATE,
			'clmtpdtmod' => SQL__DATE,
			'id' => SQL__SERIAL
		);

		// Run constructor
		$this->MaintenanceModule();
	} // end constructor ClaimtypesMaintenance	

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT clmtpname,clmtpdescrip,id FROM ".$this->table_name.
				" ORDER BY ".prepare($this->order_field)),
			$this->page_name,
			array (
				__("Code") => "clmtpname",
				__("Description") => "clmtpdescrip"
			),
			array ("", __("NO DESCRIPTION")), "", "t_page"
		);
	} // end function module->view

	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
  		if ($action=="modform") { 
    		$result = $sql->query("SELECT clmtpname,clmtpdescrip FROM $this->table_name
				WHERE ( id = '$id' )");
			$r = $sql->fetch_array($result); // dump into array r[]
			extract ($r);
		} // if loading values

		// display itemlist first
		$this->view ();

		$display_buffer .= "
			<FORM ACTION=\"$this->page_name\" METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=\"clmtpdtadd\"".prepare($cur_date)."\">
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
			($action=="modform" ? "mod" : "add")."\">";
		if ($action=="modform")
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">";

		$display_buffer .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLPADDING=2 CELLSPACING=2>
			<TR><TD ALIGN=RIGHT>
			 ".__("Claim Type")." :
			</TD><TD ALIGN=LEFT>
			 <INPUT TYPE=TEXT NAME=\"clmtpname\" SIZE=20 MAXLENGTH=75
 			  VALUE=\"".prepare($clmtpname)."\">
			</TD></TR>

			<TR><TD ALIGN=RIGHT>
			 ".__("Description")." :
			</TD><TD ALIGN=LEFT>
			 <INPUT TYPE=TEXT NAME=\"clmtpdescrip\" SIZE=25 MAXLENGTH=200
			  VALUE=\"".prepare($clmtpdescrip)."\">
			</TD></TR>

			<TR><TD ALIGN=CENTER COLSPAN=2>
			 <INPUT TYPE=SUBMIT VALUE=\"".(
			 ($action=="modform") ? __("Modify") : __("Add"))."\">
			 <INPUT TYPE=RESET  VALUE=\"".__("Remove Changes")."\">
			 </FORM>
			</TD></TR>
			</TABLE>
		";
		if ($action=="modform") $display_buffer .= "
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?module=$module&action=view\"
			>".__("Abandon Modification")."</A>
			</CENTER>
			";
	} // end function ClaimtypesMaintenance->form

} // end of class ClaimtypesMaintenance

register_module ("ClaimtypesMaintenance");

?>
