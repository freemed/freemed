<?php
 // $Id$
 // lic : GPL

LoadObjectDependency('FreeMED.MaintenanceModule');

class InternalServiceTypesMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Internal Service Types Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "intservtype";
	var $record_name    = "Internal Service Type";
	var $order_field    = "intservtype";
 
	var $variables      = array (
		"intservtype"
	); 

	function InternalServiceTypesMaintenance() {
		$this->MaintenanceModule();
	} // end constructor InternalServiceTypesMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

  if (strlen($id)<1) {
    $display_buffer .= "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $this->record_name!</B>
     </CENTER>

     <P>
    ";

    $display_buffer .= "
      <CENTER>
      <A HREF=\"main.php\"
       >"._("Return to the Main Menu")."</A>
      </CENTER>
    ";
    template_display();
  }

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

    <CENTER>
    "._($this->record_name)." :
    <INPUT TYPE=TEXT NAME=\"intservtype\" SIZE=25 MAXLENGTH=50 
     VALUE=\"".prepare($intservtype)."\">
    </CENTER>
 
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
	} // end function InternalSericeTypesMaintenance->modform()

	function view () {
		global $display_buffer;
		global $sql;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ORDER BY $this->order_field"),
			$this->page_name,
			array (
				_($this->record_name)	=>	"intservtype"
			),
			array("")
		);
 
		$display_buffer .= "
    <TABLE CLASS=\"reverse\" WIDTH=\"100%\" BORDER=\"0\"
     CELLSPACING=\"0\" CELLPADDING=\"3\">
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
	} // end function InternalServiceTypesMaintenance->view() 

} // end class InternalServiceTypesMaintenance

register_module ("InternalServiceTypesMaintenance");

?>
