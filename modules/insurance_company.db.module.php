<?php
 // $Id$
 // note: insurance company database services
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class InsuranceCompanyMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Insurance Company Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Insurance Company";
	var $table_name = "insco";

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

	function InsuranceCompanyMaintenance() {
		// Table definition
		$this->table_definition = array (
			'inscodtadd' => SQL__DATE,
			'inscodtmod' => SQL__DATE,
			'insconame' => SQL__NOT_NULL(SQL__VARCHAR(50)),
			'inscoalias' => SQL__VARCHAR(30),
			'inscoaddr1' => SQL__VARCHAR(45),
			'inscoaddr2' => SQL__VARCHAR(45),
			'inscocity' => SQL__VARCHAR(30),
			'inscostate' => SQL__CHAR(3),
			'inscozip' => SQL__VARCHAR(10),
			'inscophone' => SQL__VARCHAR(16),
			'inscofax' => SQL__VARCHAR(16),
			'inscocontact' => SQL__VARCHAR(100),
			'inscoid' => SQL__CHAR(10),
			'inscowebsite' => SQL__VARCHAR(100),
			'inscoemail' => SQL__VARCHAR(50),
			'inscogroup' => SQL__INT_UNSIGNED(0),
			'inscotype' => SQL__INT_UNSIGNED(0),
			'inscoassign' => SQL__INT_UNSIGNED(0),
			'inscomod' => SQL__TEXT,
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor InsuranceCompanyMaintenance

	function form () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$book = CreateObject('PHP.notebook',
				array ("action", "_auth", "id", "module"),
				NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH);

	if (!$book->been_here()) {
    switch ($action) {
      case "addform":
        // no prep work here
        break; // end of addform
      case "modform":
		// need this before extract or modform wont display
        // existing data on page 2
		reset($this->variables);
		while(list($k,$v)=each($this->variables))
        {
           global $$v;
        } 

        $r = freemed::get_link_rec ($id, $this->table_name);
        extract ($r);
	$inscomod = fm_make_string_array($inscomod); // ensure 17 is 17:
        break; // end of modform
    } // end inner action switch
  } // end checking if been here

	// Handle arrays in inscomod. This is such an incredible kludge.
	// TODO: Debug why freemed_multiple_choice does not work right here.
	if (is_array($inscomod)) {
		$inscomod = join(':', $inscomod);
	}

  $book->add_page(
   __("Contact Information"),
   array("insconame", "inscoalias", "inscoaddr1", "inscoaddr2",
         "inscocity", "inscostate", "inscozip",
	 phone_vars ("inscophone"),
	 phone_vars ("inscofax"),
         "inscoemail", "inscowebsite"
	 ),"
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
   
    <TR>
    <TD ALIGN=RIGHT>".__("Company Name (full)")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"insconame\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($insconame)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Company Name (on forms)")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoalias\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($inscoalias)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Address Line 1")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoaddr1\" SIZE=30 MAXLENGTH=30
     VALUE=\"".prepare($inscoaddr1)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Address Line 2")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoaddr2\" SIZE=30 MAXLENGTH=30
     VALUE=\"".prepare($inscoaddr2)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("City").", ".__("State")."
      ".__("Zip")." : </TD>
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
    <TD ALIGN=RIGHT>".__("Contact Phone")." : </TD>
    <TD ALIGN=LEFT>".fm_phone_entry ("inscophone")."</TD>
    </TR>
  
    <TR>
    <TD ALIGN=RIGHT>".__("Fax Number")." : </TD>
    <TD ALIGN=LEFT>".fm_phone_entry ("inscofax")."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Email Address")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoemail\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($inscoemail)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Web Site")."
      (<I>http://insco.com</I>) : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscowebsite\" SIZE=15 MAXLENGTH=100
     VALUE=\"".prepare($inscowebsite)."\"></TD>
    </TR>

    </TABLE>
  ");

	
  $book->add_page(
   __("Internal Information"),
   array("inscoid", "inscogroup", "inscotype", "inscoassign", "inscomod" ),"
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
   
    <TR>
    <TD ALIGN=RIGHT>".__("NEIC ID")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoid\" SIZE=11 MAXLENGTH=10
     VALUE=\"".prepare($inscoid)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Insurance Group")." : </TD>
    <TD ALIGN=LEFT>".freemed_display_selectbox(
      $sql->query("SELECT * FROM inscogroup ORDER BY inscogroup"),
      "#inscogroup#", "inscogroup")."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Insurance Type")." : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscotype\" SIZE=10 MAXLENGTH=30
     VALUE=\"".prepare($inscotype)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>Insurance Assign? : </TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoassign\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($inscoassign)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>".__("Insurance Modifiers")." : </TD>
    <TD ALIGN=LEFT>".freemed::multiple_choice ("SELECT * FROM insmod
      ORDER BY insmoddesc", "insmoddesc", "inscomod",
      $inscomod, false)."</TD>
    </TR>

    </TABLE>
  "); 
		// Handle cancel
		if ($book->is_cancelled()) {
			Header("Location: ".$this->page_name."?".
				"module=".urlencode($this->MODULE_CLASS));
			die("");
		}
  
		if (!$book->is_done()) {
			$display_buffer .= $book->display();
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
	} // end function InsuranceCompanyMaintenance->form()

	function view () { 
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ORDER BY insconame"),
			$this->page_name,
			array (
				__("Name")	=>	"insconame",
				__("City")	=>	"inscocity",
				__("State")	=>	"inscostate",
				__("Group")	=>	"inscogroup"
			),
			array ("", "", ""),
			array("","","","inscogroup" => "inscogroup"),
			"", "",
			ITEMLIST_MOD|ITEMLIST_VIEW
		);
	} // end function InsuranceCompanyMaintenance->view()

} // end class InsuranceCompanyMaintenance

register_module ("InsuranceCompanyMaintenance");

?>
