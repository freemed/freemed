<?php
 // $Id$
 // note: icd9 codes database functions
 // code: mark l (lesswin@ibm.net)
 //       jeff b (jeff@univrel.pr.uconn.edu) -- rewrite
 // lic : GPL, v2

if (!defined("__ICD_MODULE_PHP__")) {

define (__ICD_MODULE_PHP__, true);

class icdMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME 	 = "ICD Maintenance";
	var $MODULE_VERSION  = "0.1";

	var $table_name 	 = "icd9";
	var $record_name	 = "ICD9 Code";
	var $order_field	 = "icd9code,icdnum";

	var $variables		 = array (
		"icd9code",
		"icd10code",
		"icd9descrip",
		"icd10descrip",
		"icdmetadesc",
		"icddrg",
		"icdng",
		"icdnum",
		"icdamt",
		"icdcoll"
	);

	function icdMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor icdMaintenance

	function form () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;

		switch ($action) { // internal action switch
		case "addform":
			break;
		case "modform":
			if (!$been_here) {
			$r = freemed::get_link_rec ($id,$this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = stripslashes($v);
			}
			$icddrg = sql_expand($icddrg);
			$icdamt        = bcadd($icdamt, 0,2);
			$icdcoll       = bcadd($icdcoll,0,2);
			$been_here=1;
			}
    break;
  } // end internal action switch

	$display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
      ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\">
    <INPUT TYPE=HIDDEN NAME=\"module\"    VALUE=\"".prepare($module)."\">
	";

	$display_buffer .= html_form::form_table(array(
		_("Code")." ("._("ICD9").")" =>
		html_form::text_widget("icd9code", 10, 6),

		_("Meta Description") =>
		html_form::text_widget("icdmetadesc", 10, 30),

		_("Code")." ("._("ICD10").")" =>
		html_form::text_widget("icd10code", 10, 7),

		_("Description")." ("._("ICD9").")" =>
		html_form::text_widget("icd9descrip", 20, 45),
    
		_("Description")." ("._("ICD10").")" =>
		html_form::text_widget("icd10descrip", 20, 45),

		_("Diagnosis Related Groups") =>
		freemed_multiple_choice (
			"SELECT * FROM diagfamily ORDER BY dfname, dfdescrip",
			"dfname:dfdescrip",
			"icddrg",
			fm_join_from_array($icddrg)
		)
	));

	$display_buffer .= "
	<P>
	<CENTER>
    <!-- initially, number of times used is 0 -->
    <INPUT TYPE=HIDDEN NAME=\"icdnum\" VALUE=\"".prepare($icdnum)."\">

	<INPUT TYPE=SUBMIT VALUE=\" ".
      ( ($action=="addform") ? _("Add") : _("Modify") )." \">
	<INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">
	</CENTER></FORM>
		";
	} // end function icdMaintenance->form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ".
				"ORDER BY $this->order_field"),
			$this->page_name,
			array (
				_("Code")        => 	"icd9code",
				_("Description") =>	"icd9descrip"
			),
			array ("", _("NO DESCRIPTION")), "", "t_page"
		);
	} // end function icdMaintenance->view

} // end class icdMaintenance

register_module ("icdMaintenance");

} // end if not defined

?>
