<?php
 // $Id$
 // lic : GPL

LoadObjectDependency('FreeMED.MaintenanceModule');

class InternalServiceTypesMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Internal Service Types Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
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
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

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
  $r = freemed::get_link_rec($id, $this->table_name);
  foreach ($r AS $k => $v) {
    global ${$k};
    ${$k} = stripslashes($v);
  }

  $display_buffer .= "
    <p/>
    <FORM ACTION=\"$this->page_name\" METHOD=\"POST\">
    <input TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"/> 
    <input TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"/> 
    <input TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"/>

    <div ALIGN=\"CENTER\">
    "._($this->record_name)." :
    ".html_form::text_widget('intservtype', 25, 50)."
    </div>
 
    <p/>
    <div ALIGN=\"CENTER\">
    <input TYPE=\"SUBMIT\" VALUE=\" "._("Modify")." \"/>
    <input TYPE=\"RESET\" VALUE=\""._("Clear")."\"/>
    </div></form>
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
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		$display_buffer .= freemed_display_itemlist (
			$GLOBALS['sql']->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".$this->order_field
			),
			$this->page_name,
			array (
				_($this->record_name)	=>	"intservtype"
			),
			array("")
		);
 
		$display_buffer .= "
		<table CLASS=\"reverse\" WIDTH=\"100%\" BORDER=\"0\"
		 CELLSPACING=\"0\" CELLPADDING=\"3\">
		<tr VALIGN=\"CENTER\">
		<td VALIGN=\"CENTER\"><form ACTION=\"$this->page_name\" METHOD=\"POST\"
		 ><input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"add\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\">
		".html_form::text_widget('intservtype', 20, 50)."</td>
		<td VALIGN=\"CENTER\">
		<input TYPE=\"SUBMIT\" VALUE=\""._("Add")."\"/></form></td>
		</tr>
		</table>
		";
	} // end function InternalServiceTypesMaintenance->view() 

} // end class InternalServiceTypesMaintenance

register_module ("InternalServiceTypesMaintenance");

?>
