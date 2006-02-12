<?php
 // $Id$
 // note: physician status db functions
 // lic : GPL

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ProviderStatusMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Provider Status Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Provider Status";
	var $table_name     = "phystatus";

	var $variables = array ( "phystatus" );

	function ProviderStatusMaintenance () {
		// For i18n: __("Provider Status Maintenance")

		// Define database table
		$this->table_definition = array (
			"phystatus" => SQL__VARCHAR(30),
			"id" => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end contructor ProviderStatusMaintenance

	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

  $r = freemed::get_link_rec ($id, $this->table_name);
  extract ($r);

  $display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($_REQUEST["module"])."\"> 
    <INPUT TYPE=HIDDEN NAME=\"return\" VALUE=\"".prepare($_REQUEST["return"])."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    ".html_form::form_table ( array (
      __("Status") =>
     "<INPUT TYPE=TEXT NAME=\"phystatus\" SIZE=20 MAXLENGTH=20
       VALUE=\"".prepare($phystatus)."\">"
    ) )."

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".__("Modify")." \">
    <INPUT TYPE=RESET  VALUE=\"".__("Clear")."\">
    </CENTER></FORM>
  ";


  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name?module=$module\"
     >".__("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function ProviderStatusMaintenance->form()

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
  		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT phystatus,id FROM $this->table_name ".
				"ORDER BY phystatus"),
			$this->page_name,
			array (
				__("Status") => "phystatus" 
 			),
			array (
				""
			)
		);
    
		$display_buffer .= "
    <TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=3>
    <TR CLASS=\"".freemed_alternate()."\" VALIGN=\"CENTER\">
    <TD VALIGN=\"CENTER\"><FORM ACTION=\"$this->page_name\" METHOD=\"POST\">
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($GLOBALS["module"])."\">
    	<INPUT NAME=\"phystatus\" LENGTH=20 MAXLENGTH=30></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"".__("Add")."\"></FORM></TD>
    </TR></TABLE>

    <P>
		";
	} // end function ProviderStatusMaintenance->view()

} // end class ProviderStatusMaintenance

register_module ("ProviderStatusMaintenance");

?>
