<?php
 // $Id$
 // note: internal attributes for insurance companies
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class InsuranceModifiersMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Insurance Modifiers Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name  ="insmod";
	var $record_name="Insurance Modifiers";
	var $order_field="insmoddesc";
 
	var $variables = array (
		"insmod",
		"insmoddesc"
	);
 
	function InsuranceModifiersMaintenance () {
		$this->MaintenanceModule();
	} // end constructor InsuranceModifiersMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$r = freemed::get_link_rec ($id, $this->table_name);
		extract ($r);

		$display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\">

    ".html_form::form_table ( array (

    _("Modifier") =>
    "<INPUT TYPE=TEXT NAME=\"insmod\" SIZE=16 MAXLENGTH=15 
     VALUE=\"".prepare($insmod)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"insmoddesc\" SIZE=20 MAXLENGTH=50 
     VALUE=\"".prepare($insmoddesc)."\">"

    ) )."

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
     ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
		";

		$display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name?module=$module&action=view\"
     >"._("Abandon ".( ($action=="addform") ? "Addition" : "Modification" )).
     "</A>
    </CENTER>
		";
	} // end function InsuranceModifiersMaintenance->modform()

	function view () {
		global $display_buffer;
		global $sql, $module;

		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ".
				"ORDER BY $this->order_field"),
			$this->page_name,
			array (
				_("Modifier")		=>	"insmod",
				_("Description")	=>	"insmoddesc"
			),
			array (
				"",
				_("NO DESCRIPTION")
			)
		);  
		$display_buffer .= "
   <CENTER>
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
    <TR>
     <TD>"._("Modifier")."</TD>
     <TD>"._("Description")."</TD>
     <TD>&nbsp;</TD>
    </TR>
    <TR VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$this->page_name\" METHOD=POST
     ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
      <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
     <INPUT TYPE=TEXT NAME=\"insmod\" SIZE=15
      MAXLENGTH=16></TD>
    <TD VALIGN=CENTER>
     <INPUT TYPE=TEXT NAME=\"insmoddesc\" SIZE=20
      MAXLENGTH=50></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR>
   </TABLE>
   </CENTER>
		";
	} // end function InsuranceModifiersMaintenance->view()

} // end class InsuranceModifiersMaintenance

register_module("InsuranceModifiersMaintenance");

?>
