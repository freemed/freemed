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
      extract(freemed_get_link_rec ($id,$this->table_name));
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

    <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
     VALIGN=MIDDLE ALIGN=CENTER>

    <TR>
    <TD ALIGN=RIGHT WIDTH=\"50%\">
      "._("Code")." ("._("ICD9").") : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9code\" SIZE=10 MAXLENGTH=6 
     VALUE=\"".prepare($icd9code)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>"._("Meta Description")." : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdmetadesc\" SIZE=10 MAXLENGTH=30
     VALUE=\"".prepare($icdmetadesc)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>
      "._("Code")." ("._("ICD10").") : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10code\" SIZE=10 MAXLENGTH=7
     VALUE=\"".prepare($icd10code)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>
      "._("Description")." ("._("ICD9").") : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9descrip\" SIZE=20 MAXLENGTH=45
     VALUE=\"".prepare($icd9descrip)."\"></TD>
    </TR>
    
    <TR>
    <TD ALIGN=RIGHT>
      "._("Description")." ("._("ICD10").") : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10descrip\" SIZE=20 MAXLENGTH=45
     VALUE=\"".prepare($icd10descrip)."\"></TD>
    </TR>

    <!-- date of entry = $cur_date -->

    <TR>
    <TD ALIGN=RIGHT>
      "._("Diagnosis Related Groups")." : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icddrg\" SIZE=20 MAXLENGTH=45
     VALUE=\"".prepare($icddrg)."\"></TD>
    </TR>

    <!-- initially, number of times used is 0 -->
    <INPUT TYPE=HIDDEN NAME=\"icdnum\" VALUE=\"0\">

    <TR>
    <TD ALIGN=RIGHT>"._("Amount Billed")." : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdamt\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($icdamt)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>"._("Amount Collected")." : </TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdcoll\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($icdcoll)."\">
    </TR>

    </TABLE>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
      ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER></FORM>
  ";

  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name?module=$module&action=view\"
     >".( ($action=="addform") ?
      _("Abandon Addition") : _("Abandon Modification") )."</A>
    </CENTER>
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
				_("Code")			=> 	"icd9code",
				_("Description")	=>	"icd9descrip"
			),
			array ("", _("NO DESCRIPTION")), "", "t_page"
		);
	} // end function icdMaintenance->view

} // end class icdMaintenance

register_module ("icdMaintenance");

} // end if not defined

?>
