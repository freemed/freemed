<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class InsuranceCompanyModule extends MaintenanceModule {

	var $MODULE_NAME = "Insurance Companies";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.4.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

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
		"inscox12id",
		// Billing related information
		"inscodefoutput",
		"inscodefformat",
		"inscodeftarget",
		"inscodefformate",
		"inscodeftargete"
	);

	var $widget_hash = '##insconame## (##inscocity##, ##inscostate##)';

	function InsuranceCompanyModule ( ) {
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
			'inscox12id' => SQL__VARCHAR(32),
			'inscodefoutput' => SQL__ENUM(array('electronic', 'paper')),
			'inscodefformat' => SQL__VARCHAR(50),
			'inscodeftarget' => SQL__VARCHAR(50),
			'inscodefformate' => SQL__VARCHAR(50),
			'inscodeftargete' => SQL__VARCHAR(50),
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor InsuranceCompanyModule

	function form () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$book = CreateObject('PHP.notebook',
				array ("action", "_auth", "id", "module", "return"),
				NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH);

		// Create billing connection for target and format selects
		$rbe = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$rbe->Login(freemed::config_value('remitt_user'), freemed::config_value('remitt_pass'));

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
	 ),html_form::form_table(array(
   
    __("Company Name (full)") =>
    html_form::text_widget('insconame', 50),

    __("Company Name (on forms)") =>
    html_form::text_widget('inscoalias', 30),

    __("Address Line 1") =>
    html_form::text_widget('inscoaddr1', 30),

    __("Address Line 2") =>
    html_form::text_widget('inscoaddr2', 30),

    __("City").", ".
	__("State")." ".
        __("Zip") =>
    html_form::text_widget('inscocity', 20).'<b>,</b> '.
    html_form::text_widget('inscostate', 3).' '.
    html_form::text_widget('inscozip', 10),

    __("Contact Phone") =>
    fm_phone_entry ("inscophone"),
  
    __("Fax Number") =>
    fm_phone_entry ("inscofax"),

    __("Email Address") =>
    html_form::text_widget('inscoemail', 50),

    __("Web Site")."(<I>http://insco.com</I>)" =>
    html_form::text_widget('inscowebsite', 100)

  )));

  $remitt_up = $rbe->GetServerStatus();
	
  $book->add_page(
   __("Internal Information"),
   array("inscoid", "inscogroup", "inscotype", "inscoassign", "inscomod",
	'inscox12id', 'inscodefoutput', 'inscodefformat', 'inscodeftarget', 
	'inscodefformate', 'inscodeftargete' ),
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

    __("X12 Payer Id Code") =>
    html_form::text_widget('inscox12id', 25),

    __("Insurance Modifiers") =>
    freemed::multiple_choice ("SELECT * FROM insmod
      ORDER BY insmoddesc", "insmoddesc", "inscomod",
      $inscomod, false),

	__("Default Output") =>
	html_form::select_widget(
		'inscodefoutput',
		array(
			__("electronic") => 'electronic',
			__("paper") => 'paper'
		)
	),

	__("Default Paper Billing Format") =>
	( $remitt_up ?
	html_form::select_widget('inscodefformat', $rbe->ListOptions('Render', 'XSLT', 'Paper', 'payerxml')) :
	"<b>".__("REMITT Server not running")."</b>".
	"<input type=\"hidden\" name=\"inscodefformat\" value=\"".prepare($GLOBALS['inscodefformat'])."\" />" ),

	__("Default Paper Billing Target") =>
	( $remitt_up ?
	html_form::select_widget('inscodeftarget', $rbe->ListPlugins('Transport')) :
	"<b>".__("REMITT Server not running")."</b>".
	"<input type=\"hidden\" name=\"inscodeftarget\" value=\"".prepare($GLOBALS['inscodeftarget'])."\" />" ),

	__("Default Electronic Billing Format") =>
	( $remitt_up ?
	html_form::select_widget('inscodefformate', $rbe->ListOptions('Render', 'XSLT', NULL, 'payerxml')) :
	"<b>".__("REMITT Server not running")."</b>".
	"<input type=\"hidden\" name=\"inscodefformate\" value=\"".prepare($GLOBALS['inscodefformate'])."\" />" ),

	__("Default Electronic Billing Target") =>
	( $remitt_up ?
	html_form::select_widget('inscodeftargete', $rbe->ListPlugins('Transport')) :
	"<b>".__("REMITT Server not running")."</b>".
	"<input type=\"hidden\" name=\"inscodeftargete\" value=\"".prepare($GLOBALS['inscodeftargete'])."\" />" )

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
			<td><input type=\"TEXT\" name=\"inscoidmap[".$i_r['id']."][local10d]\"
				size=\"20\" maxlength=\"24\"
				value=\"".prepare($inscoidmap[$i_r['id']]['local10d'])."\" /></td>
			<td><input type=\"TEXT\" name=\"inscoidmap[".$i_r['id']."][local19]\"
				size=\"20\" maxlength=\"24\"
				value=\"".prepare($inscoidmap[$i_r['id']]['local19'])."\" /></td>
			<td><input type=\"TEXT\" name=\"inscoidmap[".$i_r['id']."][local24k]\"
				size=\"20\" maxlength=\"24\"
				value=\"".prepare($inscoidmap[$i_r['id']]['local24k'])."\" /></td>
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
				<td><b><abbr TITLE=\"".__("HCFA Form Space 10d")."\"
					>".__("Local Use 10d")."</abbr></b></td>
				<td><b><abbr TITLE=\"".__("HCFA Form Space 19")."\"
					>".__("Local Use 19")."</abbr></b></td>
				<td><b><abbr TITLE=\"".__("HCFA Form Space 24k")."\"
					>".__("Local Use 24k")."</abbr></b></td>
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
	} // end method form

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
			ITEMLIST_MOD|ITEMLIST_VIEW|ITEMLIST_DEL
		);
	} // end method view

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Version 0.3
		//
		//	Move phyidmap to be mapped in insco table (inscoidmap)
		//
		if (!version_check ( $version, '0.3' )) {
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscoidmap TEXT AFTER inscomod'
			);
		}

		// Version 0.3.1
		//
		//	Add inscodefformat and inscodeftarget mappings
		//
		if (!version_check ( $version, '0.3.4.1' )) {
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodefformate VARCHAR(50) AFTER inscodeftarget'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodeftargete VARCHAR(50) AFTER inscodefformatE'
			);
		}

		// Version 0.3.3 (Actual update from old module name - HACK)
		//
		//	Add inscodef{format,target}e for electronic mappings
		//
		if ($GLOBALS['sql']->results($GLOBALS['sql']->query("SELECT * FROM module WHERE module_name='Insurance Company Maintenance'"))) {
			// Remove stale entry
			$GLOBALS['sql']->query(
				'DELETE FROM module WHERE '.
				'module_name=\'Insurance Company Maintenance\''
			);
			// Make changes
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodefformate VARCHAR(50) AFTER inscodeftarget'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodeftargete VARCHAR(50) AFTER inscodefformate'
			);
		}

		// Version 0.4
		//
		//	Add inscox12id for 837p/remitt
		//
		if (!version_check ( $version, '0.4' )) {
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscox12id VARCHAR(32) AFTER inscoidmap'
			);
			$GLOBALS['sql']->query( 'UPDATE '.$this->table_name.' SET inscox12id=\'\' WHERE id>0');
		}

		// Version 0.4.1
		//
		//	Add inscodefoutput for remitt
		//
		if (!version_check ( $version, '0.4.1' )) {
			$GLOBALS['sql']->query(
				'ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN inscodefoutput ENUM(\'electronic\', \'paper\') AFTER inscox12id'
			);
			$GLOBALS['sql']->query( 'UPDATE '.$this->table_name.' SET inscodefoutput=\'electronic\' WHERE id>0');
		}

	} // end method _update

} // end class InsuranceCompanyModule

register_module ("InsuranceCompanyModule");

?>
