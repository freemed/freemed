<?php
  // $Id$
  // note: Insurance Coverage types database module
  // code: Fred Forester (fforest@netcarrier.com) -- modified a lot
  // lic : GPL, v2

if (!defined("__COVERAGE_TYPES_MODULE_PHP__")) {

define (__COVERAGE_TYPES_MODULE_PHP__, true);

class covtypesMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME = "Insurance Coverage Types";
	var $MODULE_VERSION = "0.1";

	var $record_name = "Coverage Types";
	var $table_name  = "covtypes";
	var $order_field = "covtpname,covtpdescrip";

	var $variables = array (
			"covtpname",
			"covtpdescrip",
			"covtpdtadd",
			"covtpdtmod"
	);

	function covtypesMaintenance () {
		// run constructor
		$this->freemedMaintenanceModule();
		global $covtpdtmod;
		$covtpdtmod = $GLOBALS["cur_date"];
	} // end constructor covtypesMaintenance	

	function view () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		echo freemed_display_itemlist (
			$sql->query("SELECT covtpname,covtpdescrip,id FROM ".$this->table_name.
				" ORDER BY ".prepare($this->order_field)),
			$this->page_name,
			array (
				_("Code") => "covtpname",
				_("Description") => "covtpdescrip"
			),
			array ("", _("NO DESCRIPTION")), "", "t_page"
		);
	} // end function module->view

	function form () {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
  		if ($action=="modform") { 
    		$result = $sql->query("SELECT covtpname,covtpdescrip FROM $this->table_name
				WHERE ( id = '$id' )");
			$r = $sql->fetch_array($result); // dump into array r[]
			extract ($r);
		} // if loading values

		// display itemlist first
		$this->view ();

		echo "
			<FORM ACTION=\"$this->page_name\" METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=\"covtpdtadd\"".prepare($cur_date)."\">
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
			($action=="modform" ? "mod" : "add")."\">";
		if ($action=="modform")
			echo "<INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">";

		echo "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLPADDING=2 CELLSPACING=2>
			<TR><TD ALIGN=RIGHT>
			 <$STDFONT_B>"._("Coverage Type")." : <$STDFONT_E>
			</TD><TD ALIGN=LEFT>
			 <INPUT TYPE=TEXT NAME=\"covtpname\" SIZE=20 MAXLENGTH=75
 			  VALUE=\"".prepare($covtpname)."\">
			</TD></TR>

			<TR><TD ALIGN=RIGHT>
			 <$STDFONT_B>"._("Description")." : <$STDFONT_E>
			</TD><TD ALIGN=LEFT>
			 <INPUT TYPE=TEXT NAME=\"covtpdescrip\" SIZE=25 MAXLENGTH=200
			  VALUE=\"".prepare($covtpdescrip)."\">
			</TD></TR>

			<TR><TD ALIGN=CENTER COLSPAN=2>
			 <INPUT TYPE=SUBMIT VALUE=\"".(
			 ($action=="modform") ? _("Modify") : _("Add"))."\">
			 <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
			 </FORM>
			</TD></TR>
			</TABLE>
		";
		if ($action=="modform") echo "
			<P>
			<CENTER><$STDFONT_B>
			<A HREF=\"$this->page_name?$_auth&module=$module&action=view\"
			>"._("Abandon Modification")."</A>
			<$STDFONT_E></CENTER>
			";
	} // end function covtypesMaintenance->form

} // end of class covtypesMaintenance

register_module ("covtypesMaintenance");

} // end of "if defined"

?>
