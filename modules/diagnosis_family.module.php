<?php
 // $Id$
 // note: diagnosis family module
 // lic : GPL, v2

if (!defined("__DIAGNOSIS_FAMILY_MODULE_PHP__")) {

define (__DIAGNOSIS_FAMILY_MODULE_PHP__, true);

class diagnosisFamilyMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Diagnosis Family Maintenance";
	var $MODULE_VERSION = "0.1";

	var $table_name     = "diagfamily";
	var $record_name    = "Diagnosis Family";
	var $order_field    = "dfname, dfdescrip";

	var $variables      = array (
		"dfname",
		"dfdescrip"
	);

	function diagnosisFamilyMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor diagnosisFamilyMaintenance 

	function addform () { $this->view(); }

	function modform () {
		foreach ( $GLOBALS as $k => $v ) global $$k;

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <P>
    ";

    echo "
      <CENTER>
      <A HREF=\"main.php?$_auth\"
       >"._("Return to the Main Menu")."</A>
      </CENTER>
    ";
    DIE("");
  }

    // grab record number "id"
  $result = $sql->query("SELECT * FROM $this->table_name ".
    "WHERE ( id = '$id' )");

  $r = $sql->fetch_array($result); // dump into array r[]
  extract ($r);

  echo "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\"  >

    <CENTER><TABLE CELLSPACING=0 CELLPADDING=3 BORDER=0>

    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Name")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"dfname\" SIZE=20 MAXLENGTH=100
      VALUE=\"".prepare($dfname)."\"></TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Description")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"dfdesrip\" SIZE=30 MAXLENGTH=100
      VALUE=\"".prepare($dfdescrip)."\"></TD>
    </TR>

    <TR>
     <TD ALIGN=CENTER COLSPAN=2>
     <CENTER>
      <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
      <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
     </CENTER>
     </TD>
    </TR>
    </TABLE></CENTER>

    </FORM>

    <P>
    <CENTER>
    <A HREF=\"$this->page_name?$_auth&action=view\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function diagnosisFamilyMaintenance->modform

	function view () {
		echo freemed_display_itemlist (
			$sql->query ( "SELECT * FROM $this->table_name ".
				"ORDER BY $this->order_field"),
		$this->page_name,
		array (
		_("Name")		=>	"dfname",
		_("Description")	=>	"dfdescrip"
		),
		array ("", _("NO DESCRIPTION")), "", "t_page"
		);
	} // end function diagnosisFamilyMaintenance->view

	function _addform() {
		global $module, $_auth;
		echo "
			<FORM ACTION=\"$this->page_name\" METHOD=POST>
			<CENTER>
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
			<CENTER>
		".html_form::form_table ( array (
			_("Name") =>
				html_form::text_widget ("dfname", 100),
			_("Description") =>
				html_form::text_widget ("dfdescrip", 100)
		) )."
			</CENTER>
			<BR>
			<CENTER>
			<INPUT TYPE=SUBMIT VALUE=\""._("ADD")."\">
			</CENTER>
			</FORM>
		";
	} // end function _addform

} // end class diagnosisFamilyMaintenance

register_module ("diagnosisFamilyMaintenance");

} // end if not defined
 
?>
