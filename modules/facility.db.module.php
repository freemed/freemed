<?php
 // $Id$
 // note: facility database functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       small mods by max k <amk@span.ch>
 // lic : GPL, v2

if (!defined("__FACILITY_MODULE_PHP__")) {

define (__FACILITY_MODULE_PHP__, true);

class facilityMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Facility Maintenance";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		Facilities are used by FreeMED to describe locations where 
		services are performed. Any physician/provider can do work 
		at one or more of these facilities.
	";

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

	function facilityMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor facilityMaintenance

	function add () { $this->form(); }

	function mod () { $this->form(); }

	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

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

  $book->add_page (_("Primary Information"),
    array (
	"psrname", "psraddr1", "psraddr2",
	"psrcity", "psrstate", "psrzip", "psrcountry"
    ),
    html_form::form_table ( array (
      _("Facility Name") =>
	html_form::text_widget("psrname", 30, 100),

      _("Address (Line 1)") =>
	html_form::text_widget("psraddr1", 20, 50),

      _("Address (Line 2)") =>
	html_form::text_widget("psraddr2", 20, 50),

      _("City, State, Zip") =>
      "<INPUT TYPE=TEXT NAME=\"psrcity\" SIZE=10 MAXLENGTH=15
       VALUE=\"".prepare($psrcity)."\">
      <INPUT TYPE=TEXT NAME=\"psrstate\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($psrstate)."\">
      <INPUT TYPE=TEXT NAME=\"psrzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($psrzip)."\">",

      _("Country") =>
      html_form::country_pulldown("psrcountry")
     ) )
    );

    $book->add_page (
      _("Details"),
      array (
        "psrnote", "psrdefphy", "psrein", "psrintext", "psrpos"
      ),
      html_form::form_table ( array (
        _("Description") =>
        "<INPUT TYPE=TEXT NAME=\"psrnote\" SIZE=20 MAXLENGTH=40
         VALUE=\"".prepare($psrnote)."\">",

        _("Default Provider") =>
	freemed_display_selectbox (
          $sql->query("SELECT * FROM physician ORDER BY phylname,phyfname"),
	  "#phylname#, #phyfname#",
	  "psrdefphy" 
	),
        _("POS Code") =>
	freemed_display_selectbox (
          $sql->query("SELECT * FROM pos ORDER BY posname,posdescrip"),
	  "#posname#, #posdescrip#",
	  "psrpos" 
	),
	
        _("Employer Identification Number") =>
	html_form::text_widget("psrein", 9),

        _("Internal or External Facility") =>
	html_form::select_widget(
		"psrintext",
		array (
			_("Internal") => "0",
			_("External") => "1"
		)
	)

      ) )
    );

    $book->add_page (_("Contact"),
      array (
        array_merge(phone_vars("psrphone"), phone_vars("psrfax")), "psremail"
      ),
      html_form::form_table ( array (
        _("Phone Number") =>
        fm_phone_entry ("psrphone"),

      _("Fax Number") =>
      fm_phone_entry ("psrfax"),

        _("Email Address") =>
        "<INPUT TYPE=TEXT NAME=\"psremail\" SIZE=25 MAXLENGTH=25
         VALUE=\"".prepare($psremail)."\">"

     ) )
    );

  if (!$book->is_done()) {
    $display_buffer .= $book->display();
    $display_buffer .= "
       <P>
       <CENTER>
       <A HREF=\"$this->page_name?module=$module\"
        >"._("Abandon ".( ($action=="addform") ? "Addition" : "Modification" ))
         ."</A>
       </CENTER>
    ";
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
	} // end function facilityMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT * FROM $this->table_name ".
				"ORDER BY psrname,psrnote"),
			$this->page_name,
			array (
				_("Name")         => "psrname",
				_("Description")  => "psrnote"
			),
			array ("", " ")
		);
	} // end function facilityMaintenance->view()

} // end class facilityMaintenance

register_module ("facilityMaintenance");

} // end if not defined

?>
