<?php
 // $Id$
 // note: cpt modifier functions
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class CptModifiersMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "CPT Modifiers Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "CPT Modifiers";
	var $table_name     = "cptmod";

	var $variables		= array (
		"cptmod",
		"cptmoddescrip"
	);

	function CptModifiersMaintenance () {
		global $display_buffer;
			// run constructor
		$this->MaintenanceModule();
			// table definition (inside constructor, as outside definitions
			// do NOT allow function calls)
		$this->table_definition = array (
			"cptmod"		=>	SQL_CHAR(2),
			"cptmoddescrip"		=>	SQL_VARCHAR(50),
			"id"			=>	SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);
		if ($debug) {
		global $sql;$display_buffer .= "query = \"".$sql->create_table_query(
			$this->table_name, $this->table_definition).
			"\"<BR>\n";
		} // end if $debug
	} // end constructor CptModifiersMaintenance

	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
  switch ($action) { // inner switch
    case "addform":
     break;

    case "modform":
     if ($id<1) trigger_error ("NO ID", E_USER_ERROR);
     $r = freemed::get_link_rec ($id, $this->table_name);
     extract ($r);
     break;
  } // end inner switch

  $display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
      ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"module\"   VALUE=\"".prepare($module)."\"  >
   ".html_form::form_table ( array (
    _("Modifier") =>
    "<INPUT TYPE=TEXT NAME=\"cptmod\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($cptmod)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"cptmoddescrip\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($cptmoddescrip)."\">"
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
     >"._("Abandon ".
       ( ($action=="addform") ? "Addition" : "Modification" ))."</A>
    </CENTER>
  ";
	} // end function CptModifiersMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= "View ";
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT cptmod,cptmoddescrip,id FROM $this->table_name ".
		((strlen($_s_val)>0)
		 ? "WHERE 
		   $_s_field = '$_s_val' OR
		   $_s_field LIKE '%$_s_val' OR
		   $_s_field LIKE '$_s_val%' OR
		   $_s_field LIKE '%$_s_val%'" 
		 
		 : "")."
                ORDER BY cptmod,cptmoddescrip"
		 ),
    $this->page_name,
    array (
	_("Modifier")		=>	"cptmod",
	_("Description")	=>	"cptmoddescrip"
    ),
    array ("", _("NO DESCRIPTION"))
  );
	} // end function CptModifiersMaintenance->view()

} // end class CptModifiersMaintenance

register_module ("CptModifiersMaintenance");

?>
