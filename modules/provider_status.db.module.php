<?php
 // $Id$
 // note: physician status db functions
 // lic : GPL

if (!defined("__PROVIDER_STATUS_MODULE_PHP__")) {

define(__PROVIDER_STATUS_MODULE_PHP__, true);

class providerStatusMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Provider Status Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "Provider Status";
	var $table_name     = "phystatus";

	var $variables = array ( "phystatus" );

	function providerStatusMaintenance () {
		$this->freemedMaintenanceModule();

		$this->table_definition = array (
			"phystatus" => SQL_VARCHAR(30),
			"id" => SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);
	} // end contructor providerStatusMaintenance

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
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($GLOBAL["module"])."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    ".html_form::form_table ( array (
      _("Status") =>
     "<INPUT TYPE=TEXT NAME=\"phystatus\" SIZE=20 MAXLENGTH=20
       VALUE=\"".prepare($phystatus)."\">"
    ) )."

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";


  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name?module=$module\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function providerStatusMaintenance->form()

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
  		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT phystatus,id FROM $this->table_name ".
				"ORDER BY phystatus"),
			$this->page_name,
			array (
				_("Status") => "phystatus" 
 			),
			array (
				""
			)
		);
    
		$display_buffer .= "
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3>
    <TR BGCOLOR=\"".
      ($_alternate = freemed_bar_alternate_color ($_alternate))
    ."\" VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$this->page_name\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($GLOBALS["module"])."\">
    	<INPUT NAME=\"phystatus\" LENGTH=20 MAXLENGTH=30></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR></TABLE>

    <P>
		";
	} // end function providerStatusMaintenance->view()

} // end class providerStatusMaintenance

register_module ("providerStatusMaintenance");

} // end if defined

?>
