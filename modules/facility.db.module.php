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
			'psrname' => SQL_VARCHAR(100),
			'psraddr1' => SQL_VARCHAR(50),
			'psraddr2' => SQL_VARCHAR(50),
			'psrcity' => SQL_VARCHAR(50),
			'psrstate' => SQL_CHAR(3),
			'psrzip' => SQL_CHAR(10),
			'psrcountry' => SQL_VARCHAR(50),
			'psrnote' => SQL_VARCHAR(40),
			'psrdateentry' => SQL_DATE,
			'psrdefphy' => SQL_INT_UNSIGNED(0),
			'psrphone' => SQL_VARCHAR(16),
			'psrfax' => SQL_VARCHAR(16),
			'psremail' => SQL_VARCHAR(25),
			'psrein' => SQL_VARCHAR(9),
			'psrintext' => SQL_INT_UNSIGNED(0),
			'psrpos' => SQL_INT_UNSIGNED(0),
			'id' => SQL_SERIAL
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
     while(list($k,$v)=each($this->variables))
     {
            global $$v;
            //$display_buffer .= "$k $v<BR>";
     }
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
      "<INPUT TYPE=TEXT NAME=\"psrcity\" SIZE=10 MAXLENGTH=15
       VALUE=\"".prepare($psrcity)."\">
      <INPUT TYPE=TEXT NAME=\"psrstate\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($psrstate)."\">
      <INPUT TYPE=TEXT NAME=\"psrzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($psrzip)."\">",

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
        "<INPUT TYPE=TEXT NAME=\"psrnote\" SIZE=20 MAXLENGTH=40
         VALUE=\"".prepare($psrnote)."\">",

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

} // end class FacilityMaintenance

register_module ("FacilityMaintenance");

?>
