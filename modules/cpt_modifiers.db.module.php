<?php
 // $Id$
 // note: cpt modifier functions
 // lic : GPL, v2

if (!defined("__CPT_MODIFIERS_MODULE_PHP__")) {

define (__CPT_MODIFIERS_MODULE_PHP__, true);

class cptModifiersMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "CPT Modifiers Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "CPT Modifiers";
	var $table_name     = "cptmod";

	var $variables		= array (
		"cptmod",
		"cptmoddescrip"
	);

	function cptModifiersMaintenance () {
			// run constructor
		$this->freemedMaintenanceModule();
			// table definition (inside constructor, as outside definitions
			// do NOT allow function calls)
		$this->table_definition = array (
			"cptmod"		=>	SQL_CHAR(2),
			"cptmoddescrip"	=>	SQL_VARCHAR(50),
			"id"			=>	SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);
		if ($debug) {
		global $sql;echo "query = \"".$sql->create_table_query(
			$this->table_name, $this->table_definition).
			"\"<BR>\n";
		} // end if $debug
	} // end constructor cptModifiersMaintenance

	function form () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
  switch ($action) { // inner switch
    case "addform":
     break;

    case "modform":
     if ($id<1) trigger_error ("NO ID", E_USER_ERROR);
     $r = freemed_get_link_rec ($id, $this->table_name);
     extract ($r);
     break;
  } // end inner switch

  echo "
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

  echo "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name?$_auth&module=$module&action=view\"
     >"._("Abandon ".
       ( ($action=="addform") ? "Addition" : "Modification" ))."</A>
    </CENTER>
  ";
	} // end function cptModifiersMaintenance->form()

	function view () {
		global $sql;
		echo "View ";
		echo freemed_display_itemlist (
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
	} // end function cptModifiersMaintenance->view()

} // end class cptModifiersMaintenance

register_module ("cptModifiersMaintenance");

} // end if defined

?>
