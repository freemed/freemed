<?php
 // $Id$
 // note: patient status functions
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class patientStatusMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Patient Status Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name 	= "Patient Status";
	var $table_name		= "ptstatus";

	var $variables 		= array (
		"ptstatus",
		"ptstatusdescrip"
	);

	function PatientStatusMaintenance () {
		$this->MaintenanceModule();
	} // end constructor PatientStatusMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		 // grab record number "id"
  		$result = $sql->query("SELECT * FROM $this->table_name WHERE
    		(id='".addslashes($id)."')");

  $r = $sql->fetch_array($result);
  extract ($r);
		$display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    ".html_form::form_table ( array (

    _("Status") =>
    "<INPUT TYPE=TEXT NAME=\"ptstatus\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($ptstatus)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"ptstatusdescrip\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($ptstatusdescrip)."\">"

    ) )."

    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
     <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER>

    </FORM>
  ";

  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function PatientStatusMaintenance->modform()

	function view () {
		global $display_buffer;
		global $sql;
 		$display_buffer .= freemed_display_itemlist (
 			$sql->query (
				"SELECT ptstatusdescrip,ptstatus,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ptstatusdescrip,ptstatus"
			),
			$this->page_name,
			array (
				_("Status")			=>	"ptstatus",
				_("Description")	=>	"ptstatusdescrip"
			),
			array (
				"", _("NO DESCRIPTION")
			)
		);  
		$this->_addform();
	} // end function PatientStatusMaintenance->view()

	function _addform () {
		global $display_buffer;
		global $module;
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"add\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		".html_form::form_table ( array (
			_("Status") =>
				html_form::text_widget ("ptstatus", 2),
			_("Description") =>
				html_form::text_widget ("ptstatusdescrip", 20)
		) )."
		<br/>	
		<input TYPE=\"SUBMIT\" VALUE=\""._("Add")."\"/>
		</form>
		</div>
		";
	} // end function PatientStatusMaintenance->addform()

} // end class PatientStatusMaintenance

register_module ("PatientStatusMaintenance");

?>
