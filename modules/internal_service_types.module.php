<?php
 // $Id$
 // note: (description of this module here)
 // lic : GPL

if (!defined("__INTERNAL_SERVICE_TYPES_MODULE_PHP__")) {

define (__INTERNAL_SERVICE_TYPES_MODULES_PHP__, true);

class internalServiceTypesMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Internal Service Types Maintenance";
	var $MODULE_VERSION = "0.1";

	var $table_name     = "intservtype";
	var $record_name    = "Internal Service Type";
	var $order_field    = "intservtype";
 
	var $variables      = array (
		"intservtype"
	); 

	function addform () { $this->view(); }

	function modform () {
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $this->record_name!</B>
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
  $result = $sql->query("SELECT * FROM $this->table_name WHERE
    (id='".addslashes($id)."')");

  $r = $sql->fetch_array($result);
  extract ($r);

  echo "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    <CENTER>
    <$STDFONT_B>"._($this->record_name)." : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"intservtype\" SIZE=25 MAXLENGTH=50 
     VALUE=\"".prepare($intservtype)."\">
    </CENTER>
 
    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";

  echo "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name?$_auth&module=$module\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function internalSericeTypesMaintenance->modform()

	function view () {
		global $sql;
		echo freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ORDER BY $order_field"),
			$this->page_name,
			array (
				_($this->record_name)	=>	"intservtype"
			)
		);
 
		echo "
    <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
     CELLSPACING=0 CELLPADDING=3>
    <TR VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$this->page_name\" METHOD=POST
     ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
     <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
     <INPUT TYPE=TEXT NAME=\"intservtype\" SIZE=20
      MAXLENGTH=50></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR>
    </TABLE>
		";
	} // end function internalServiceTypesMaintenance->view() 

} // end class internalServiceTypesMaintenance

register_module ("internalServiceTypesMaintenance");

} // end if defined

?>
