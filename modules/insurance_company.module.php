<?php
 // $Id$
 // note: insurance company database services
 // lic : GPL, v2

if (!defined("__INSURANCE_COMPANY_MODULE_PHP__")) {

define (__INSURANCE_COMPANY_MODULE_PHP__, true);

class insuranceCompanyMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME	= "Insurance Company Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name 	= "Insurance Company";
	var $table_name     = "insco";

	var $variables		= array (
		"inscodtmod",
		"insconame",
		"inscoalias",
		"inscoaddr1",
		"inscoaddr2",
		"inscocity",
		"inscostate",
		"inscozip",
		"inscophone",
		"inscofax",
		"inscocontact",
		"inscoid",
		"inscowebsite",
		"inscoemail",
		"inscogroup",
		"inscotype",
		"inscoassign",
		"inscomod"
	);

	function form () {
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$book = new notebook ( array ("action", "_auth", "id", "module"),
			NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH);

	if (!$book->been_here()) {
    switch ($action) {
      case "addform":
        // no prep work here
        break; // end of addform
      case "modform":
        $r = freemed_get_link_rec ($id, $this->table_name);
        extract ($r); 
        break; // end of modform
    } // end inner action switch
  } // end checking if been here

  $book->add_page(
   _("Contact Information"),
   array("insconame", "inscoalias", "inscoaddr1", "inscoaddr2",
         "inscocity", "inscostate", "inscozip",
	 phone_vars ("inscophone"),
	 phone_vars ("inscofax"),
         "inscoemail", "inscowebsite"
	 ),"
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
   
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Company Name (full)")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"insconame\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($insconame)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Company Name (on forms)")." : 
      <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoalias\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($inscoalias)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Address Line 1")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoaddr1\" SIZE=30 MAXLENGTH=30
     VALUE=\"".prepare($inscoaddr1)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Address Line 2")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoaddr2\" SIZE=30 MAXLENGTH=30
     VALUE=\"".prepare($inscoaddr2)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("City").", "._("State")."
      "._("Zip")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>  
    <INPUT TYPE=TEXT NAME=\"inscocity\" SIZE=20 MAXLENGTH=20
     VALUE=\"".prepare($inscocity)."\"><B>,</B>
    <INPUT TYPE=TEXT NAME=\"inscostate\" SIZE=4 MAXLENGTH=3
     VALUE=\"".prepare($inscostate)."\">
    <INPUT TYPE=TEXT NAME=\"inscozip\" SIZE=10 MAXLENGTH=10
     VALUE=\"".prepare($inscozip)."\">
    </TD> 
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Contact Phone")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>".fm_phone_entry ("inscophone")."</TD>
    </TR>
  
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Fax Number")."<$STDFONT_E></TD>
    <TD ALIGN=LEFT>".fm_phone_entry ("inscofax")."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Email Address")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoemail\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($inscoemail)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Web Site")."
      (<I>http://insco.com</I>)<$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscowebsite\" SIZE=15 MAXLENGTH=100
     VALUE=\"".prepare($inscowebsite)."\"></TD>
    </TR>

    </TABLE>
  ");

  $book->add_page(
   _("Internal Information"),
   array(""),"
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
   
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("NEIC ID")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoid\" SIZE=11 MAXLENGTH=10
     VALUE=\"".prepare($inscoid)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Insurance Group")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>".freemed_display_selectbox(
      $sql->query("SELECT inscogroup FROM inscogroup ORDER BY inscogroup"),
      "#inscogroup#", "inscogroup")."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Insurance Type")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscotype\" SIZE=10 MAXLENGTH=30
     VALUE=\"".prepare($inscotype)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>Insurance Assign? : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoassign\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($inscoassign)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Insurance Modifiers")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>".freemed_multiple_choice ("SELECT * FROM insmod
      ORDER BY insmoddesc", "insmoddesc", "inscomod",
      $inscomod, false)."</TD>
    </TR>

    </TABLE>
  "); 
  
		if (!$book->is_done()) {
			echo $book->display();
		} else {
			switch ($action) {
				case "add": case "addform":
				$inscodtadd = $cur_date; // set date added to current
				$inscodtmod = $cur_date; // set date modified to current
				$GLOBALS["inscophone"] = fm_phone_assemble("inscophone");
				$GLOBALS["inscofax"]   = fm_phone_assemble("inscofax");
				$this->_add();
				break; // end add/addform

				case "mod": case "modform":
				//$inscodtadd = $cur_date; // set date added to current
				$inscodtmod = $cur_date; // set date modified to current
				$GLOBALS["inscophone"] = fm_phone_assemble("inscophone");
				$GLOBALS["inscofax"]   = fm_phone_assemble("inscofax");
				$this->_mod();
				break; // end add/addform
			} // end switch
		} // end if book is done
	} // end function insuranceCompanyMaintenance->form()

	function view () { 
		global $sql;
		echo freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ORDER BY insconame"),
			$this->page_name,
			array (
				_("Name")	=>	"insconame",
				_("City")	=>	"inscocity",
				_("State")	=>	"inscostate",
				_("Group")	=>	"inscogroup"
			),
			array ("", "", ""),
			"", "", "",
			ITEMLIST_MOD|ITEMLIST_VIEW
		);
	} // end function insuranceCompanyMaintenance->view()

} // end class insuranceCompanyMaintenance

register_module ("insuranceCompanyMaintenance");

} // end if not defined

?>
