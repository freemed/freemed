<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class InsuranceCompanyMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Insurance Company Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.2';

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
		"inscomod",
		"inscoidmap",
		// Billing related information
		"inscodefformat",
		"inscodeftarget"
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
			'inscoidmap' => SQL__TEXT,
			'inscodefformat' => SQL__VARCHAR(50),
			'inscodeftarget' => SQL__VARCHAR(50),
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

		// Create billing connection for target and format selects
		$freeb = CreateObject('FreeMED.FreeB_v1');

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
	$inscoidmap = unserialize($inscoidmap);
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
   array("inscoid", "inscogroup", "inscotype", "inscoassign", "inscomod",
	'inscodefformat', 'inscodeftarget' ),
	html_form::form_table(array(
    __("NEIC ID") =>
    "<INPUT TYPE=TEXT NAME=\"inscoid\" SIZE=11 MAXLENGTH=10
     VALUE=\"".prepare($inscoid)."\" />",

    __("Insurance Group") =>
    freemed_display_selectbox(
      $sql->query("SELECT * FROM inscogroup ORDER BY inscogroup"),
      "#inscogroup#", "inscogroup"),

    __("Insurance Type") =>
    "<INPUT TYPE=TEXT NAME=\"inscotype\" SIZE=10 MAXLENGTH=30
     VALUE=\"".prepare($inscotype)."\" />",

    __("Insurance Assign?") =>
    "<INPUT TYPE=TEXT NAME=\"inscoassign\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($inscoassign)."\" />",

    __("Insurance Modifiers") =>
    freemed::multiple_choice ("SELECT * FROM insmod
      ORDER BY insmoddesc", "insmoddesc", "inscomod",
      $inscomod, false),

	__("Default Billing Format") =>
	html_form::select_widget('inscodefformat', $freeb->FormatList()),

	__("Default Billing Target") =>
	html_form::select_widget('inscodeftarget', $freeb->TargetList())

		))
	);

		// Calculate insurance id mappings
		$i_phy = $sql->query('SELECT * FROM physician WHERE '.
			'phyref != \'yes\'');
		while ($i_r = $sql->fetch_array($i_phy)) {
			$map_buffer .= "
			<tr class=\"".freemed_alternate()."\">
			<td>".prepare($i_r['phylname'].', '.$i_r['phyfname'])."</td>
			<td><input type=\"TEXT\" name=\"inscoidmap[".$i_r['id']."][id]\"
				size=\"20\" maxlength=\"24\"
				value=\"".prepare($inscoidmap[$i_r['id']]['id'])."\" /></td>
			<td><input type=\"TEXT\" name=\"inscoidmap[".$i_r['id']."][group]\"
				size=\"20\" maxlength=\"24\"
				value=\"".prepare($inscoidmap[$i_r['id']]['group'])."\" /></td>
			</tr>
			";
		}

		// Add insurance company id mappings
		$book->add_page(
			__("ID Mappings"),
			array('inscoidmap'),
			"<center>
			<table border=\"0\" class=\"reverse\">
			<tr class=\"cell_hilite\">
				<td><b>".__("Provider")."</b></td>
				<td><b>".__("ID")."</b></td>
				<td><b>".__("Group ID")."</b></td>
			</tr>
			$map_buffer
			</table>
			</center>"
		);	

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
				$GLOBALS['inscoidmap'] = serialize($GLOBALS['inscoidmap']);
				$this->_add();
				break; // end add/addform

				case "mod": case "modform":
				//$inscodtadd = $cur_date; // set date added to current
				$inscodtmod = $cur_date; // set date modified to current
				$GLOBALS["inscophone"] = fm_phone_assemble("inscophone");
				$GLOBALS["inscofax"]   = fm_phone_assemble("inscofax");
				$GLOBALS['inscoidmap'] = serialize($GLOBALS['inscoidmap']);
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

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );
		// Version 0.3
		//	Move phyidmap to be mapped in insco table (inscoidmap)
		if (!version_check ( $version, '0.3' )) {
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscoidmap TEXT AFTER inscomod'
			);
		}
		// Version 0.3.1
		//	Add inscodefformat and inscodeftarget mappings
		if (!version_check ( $version, '0.3.1' )) {
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat'
			);
		}
	} // end method _update

} // end class InsuranceCompanyMaintenance

register_module ("InsuranceCompanyMaintenance");

?>
