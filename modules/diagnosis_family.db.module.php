<?php
 // $Id$
 // note: diagnosis family module
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class DiagnosisFamilyMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Diagnosis Family Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		Diagnosis families are part of FreeMED's attempt to
		make practice management more powerful through outcomes
		management. Diagnosis families are used to group
		diagnoses more intelligently, allowing FreeMED to
		analyze treatment patterns.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "diagfamily";
	var $record_name    = "Diagnosis Family";
	var $order_field    = "dfname, dfdescrip";

	var $variables      = array (
		"dfname",
		"dfdescrip"
	);

	function DiagnosisFamilyMaintenance () {
		$this->MaintenanceModule();
	} // end constructor DiagnosisFamilyMaintenance 

	//function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;


    // grab record number "id"
  $result = $sql->query("SELECT * FROM $this->table_name ".
    "WHERE ( id = '$id' )");

  $r = $sql->fetch_array($result); // dump into array r[]
  extract ($r);

  $display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\"  >

    <CENTER><TABLE CELLSPACING=0 CELLPADDING=3 BORDER=0>

    <TR>
     <TD ALIGN=RIGHT>"._("Name")." : </TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"dfname\" SIZE=20 MAXLENGTH=100
      VALUE=\"".prepare($dfname)."\"></TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>"._("Description")." : </TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"dfdescrip\" SIZE=30 MAXLENGTH=100
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
    <A HREF=\"$this->page_name?action=view\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function DiagnosisFamilyMaintenance->modform

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ( "SELECT * FROM $this->table_name ".
				"ORDER BY $this->order_field"),
		$this->page_name,
		array (
		_("Name")		=>	"dfname",
		_("Description")	=>	"dfdescrip"
		),
		array ("", _("NO DESCRIPTION")), "", "t_page"
		);
	} // end function DiagnosisFamilyMaintenance->view

	
	function addform() {
		global $display_buffer;
		//global $module;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= "
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

} // end class DiagnosisFamilyMaintenance

register_module ("DiagnosisFamilyMaintenance");

?>
