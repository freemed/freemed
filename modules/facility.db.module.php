<?php
 // $Id$
 // note: facility database functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       small mods by max k <amk@span.ch>
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class FacilityMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Facility Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		Facilities are used by FreeMED to describe locations where 
		services are performed. Any physician/provider can do work 
		at one or more of these facilities.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Facility";
	var $table_name     = "facility";
	var $order_by       = "psrname";

	var $variables = array (
		"psrname",
		"psraddr1",
		"psraddr2",
		"psrcity",
		"psrstate",
		"psrzip",
		"psrcountry",
		"psrnote",
		"psrdefphy",
		"psrphone",
		"psrfax",
		"psremail",
		"psrein",
		"psrintext",
		"psrpos"
	);

	function FacilityMaintenance () {
		// Create table definition
		$this->table_definition = array (
			'psrname' => SQL__VARCHAR(100),
			'psraddr1' => SQL__VARCHAR(50),
			'psraddr2' => SQL__VARCHAR(50),
			'psrcity' => SQL__VARCHAR(50),
			'psrstate' => SQL__CHAR(3),
			'psrzip' => SQL__CHAR(10),
			'psrcountry' => SQL__VARCHAR(50),
			'psrnote' => SQL__VARCHAR(40),
			'psrdateentry' => SQL__DATE,
			'psrdefphy' => SQL__INT_UNSIGNED(0),
			'psrphone' => SQL__VARCHAR(16),
			'psrfax' => SQL__VARCHAR(16),
			'psremail' => SQL__VARCHAR(25),
			'psrein' => SQL__VARCHAR(9),
			'psrintext' => SQL__INT_UNSIGNED(0),
			'psrpos' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Run constructor
		$this->MaintenanceModule();
	} // end constructor FacilityMaintenance

	function add () { $this->form(); }

	function mod () { $this->form(); }

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

  $book = CreateObject('PHP.notebook', array ("action", "id", "module"),
    NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR);

  // for either add or modify form
  if (!$book->been_here()) {
   switch ($action) { // internal case 
    case "addform": // internal addform
     $next_action = "add";
     break; // end internal addform
    case "modform": // internal modform
     foreach ($this->variables AS $k => $v) { global ${$k}; }
     $next_action = "mod";
     $r = freemed::get_link_rec ($id, $this->table_name);
     extract ($r);
     break;
   } // end internal case
  } // end if not been here

  $book->add_page (__("Primary Information"),
    array (
	"psrname", "psraddr1", "psraddr2",
	"psrcity", "psrstate", "psrzip", "psrcountry"
    ),
    html_form::form_table ( array (
      __("Facility Name") =>
	html_form::text_widget("psrname", 30, 100),

      __("Address (Line 1)") =>
	html_form::text_widget("psraddr1", 20, 50),

      __("Address (Line 2)") =>
	html_form::text_widget("psraddr2", 20, 50),

      __("City, State, Zip") =>
      html_form::text_widget("psrcity", 10, 15)." ".
      html_form::text_widget("psrstate", 3)." ".
      html_form::text_widget("psrzip", 10),

      __("Country") =>
      html_form::country_pulldown("psrcountry")
     ) )
    );

    $book->add_page (
      __("Details"),
      array (
        "psrnote", "psrdefphy", "psrein", "psrintext", "psrpos"
      ),
      html_form::form_table ( array (
        __("Description") =>
	html_form::text_widget("psrnote", 20, 40),

        __("Default Provider") =>
	freemed_display_selectbox (
          $sql->query("SELECT * FROM physician WHERE phylname != '' ".
	  	"ORDER BY phylname,phyfname"),
	  "#phylname#, #phyfname#",
	  "psrdefphy" 
	),
        __("POS Code") =>
	freemed_display_selectbox (
          $sql->query("SELECT * FROM pos ORDER BY posname,posdescrip"),
	  "#posname#, #posdescrip#",
	  "psrpos" 
	),
	
        __("Employer Identification Number") =>
	html_form::text_widget("psrein", 9),

        __("Internal or External Facility") =>
	html_form::select_widget(
		"psrintext",
		array (
			__("Internal") => "0",
			__("External") => "1"
		)
	)

      ) )
    );

    $book->add_page (__("Contact"),
      array (
        array_merge(phone_vars("psrphone"), phone_vars("psrfax")), "psremail"
      ),
      html_form::form_table ( array (
        __("Phone Number") =>
        fm_phone_entry ("psrphone"),

      __("Fax Number") =>
      fm_phone_entry ("psrfax"),

        __("Email Address") =>
	html_form::text_widget('psremail', 25)

     ) )
    );

  if (!$book->is_done()) {
    $display_buffer .= $book->display();
  } else {
    $psrphone = fm_phone_assemble("psrphone");
    $psrfax   = fm_phone_assemble("psrfax");
    switch ($action) { // internal action switch
     case "addform": case "add":
      $this->_add();
      break; // end addform

     case "modform": case "mod":
      $this->_mod();
		break;

    } // end of internal action switch
  } // end checking if book is displayed
	} // end function FacilityMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY psrname,psrnote"
			),
			$this->page_name,
			array (
				__("Name")         => "psrname",
				__("Description")  => "psrnote"
			),
			array ("", " ")
		);
	} // end function FacilityMaintenance->view()

	//----- XML-RPC Methods
	function picklist () {
		global $sql;
		$result = $sql->query("SELECT * FROM ".$this->table_name." ".
			"ORDER BY ".$this->order_fields);
		if (!$sql->results($result)) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', 'error', 'string')
			);
		}
		return rpc_generate_sql_hash(
			$this->table_name,
			array (
				"name" => 'psrname',
				"city" => 'psrcity',
				"state" => 'psrstate',
				"id"
			),
			"ORDER BY ".$this->order_fields
		);
	} // end function FacilityMaintenance->picklist

} // end class FacilityMaintenance

register_module ("FacilityMaintenance");

?>
