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

	function cptModifiersMaintenance () {
		// run constructor
		$this->freemedMaintenanceModule();
	} // end constructor cptModifiersMaintenance

	function add () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
  echo "
    <P>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO ".$this->table_name." VALUES ( ".
    "'$cptmod', '$cptmoddescrip', NULL ) ";

  $result = $sql->query($query);

  if ($result) echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
   <P>
    <CENTER>
     <A HREF=\"$this->page_name?$_auth&module=$module\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";
	} // end function cptModifiersMaintenance->add()

	function form () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
  switch ($action) { // inner switch
    case "addform":
     break;

    case "modform":
     if ($id<1) die ("NO ID");
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

	function mod () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

  echo "
    <P>
    <$STDFONT_B>"._("Modifying")." ... 
  ";

  $query = "UPDATE ".$this->table_name." SET ".
    "cptmod        = '".addslashes($cptmod)."',       ".
    "cptmoddescrip = '".addslashes($cptmoddescrip)."' ". 
    "WHERE id='".addslashes($id)."'";

  $result = $sql->query($query);

  if ($result) echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
   <P>
    <CENTER>
     <A HREF=\"$this->page_name?$_auth&module=$module\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";
	} // end function cptModifiersMaintenance->mod()


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
