<?php
 // $Id$
 // note: patient authorizations module
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       adam b (gdrago23@yahoo.com)
 // lic : GPL, v2

if (!defined("__AUTHORIZATIONS_MODULE_PHP__")) {

define (__AUTHORIZATIONS_MODULE_PHP__, true);

class authorizationsModule extends freemedEMRModule {

	var $MODULE_NAME    = "Insurance Authorizations";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		Insurance authorizations are used to track whether
		a patient is authorized by his or her insurance
		company for service during a particular period of
		time. If you do not use insurance support in
		FreeMED, this module is not needed.
	";

	var $record_name    = "Authorizations";
	var $table_name     = "authorizations";
	var $patient_field  = "authpatient";

	var $variables = array (
		"authdtmod",
		"authdtbegin",
		"authdtend",
		"authnum",
		"authtype",
		"authprov",
		"authprovid",
		"authinsco",
		"authvisits",
		"authvisitsused",
		"authvisitsremain",
		"authcomment",
		"authpatient",
		"authdtadd"
	);

	function authorizationsModule () {
		$this->freemedEMRModule();
 
		// Set vars for patient management summary
		$this->summary_vars = array (
			_("From") => "authdtbegin",
			_("To")   => "authdtend"
		);
	} // end constructor authorizationsModule

	function form () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

     switch ($action) { // internal action switch
      case "addform":
       // do nothing
       break; // end internal addform
      case "modform":
       if (($patient<1) OR (empty($patient))) {
         $display_buffer .= _("You must select a patient.")."\n";
         template_display ();
       }
       $r = freemed::get_link_rec ($id, $this->table_name);
	foreach ($r AS $k => $v) {
		global ${$k};
		${$k} = stripslashes($v);
	}
       extract ($r);
       break; // end internal modform
     } // end internal action switch

     $pnotesdt     = $cur_date;

     $display_buffer .= "
       <P>

       <FORM ACTION=\"$this->page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"".
         ( ($action=="addform") ? "add" : "mod" )."\">
       <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"".prepare($id)."\">
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
       <INPUT TYPE=HIDDEN NAME=\"authpatient\" VALUE=\"".prepare($patient)."\">
       <INPUT TYPE=HIDDEN NAME=\"module\"  VALUE=\"".prepare($module)."\">

	";

	$display_buffer .= html_form::form_table(array(
		_("Starting Date") =>
		date_entry("authdtbegin"),

		_("Ending Date") =>
		date_entry("authdtend"),

		_("Authorization Number") =>
		html_form::text_widget("authnum", 25),

		_("Authorization Type") =>
		html_form::select_widget(
			"authtype",
			array(
				_("NONE SELECTED") => "0",
				_("physician") => "1",
				_("insurance company") => "2",
				_("certificate of medical neccessity") => "3",
				_("surgical") => "4",
				_("worker's compensation") => "5",
				_("consulatation") => "6"
			)
		),

		_("Authorizing Provider") =>
		freemed_display_selectbox (
		$sql->query("SELECT * FROM physician ORDER BY phylname,phyfname"),
		"#phylname#, #phyfname#", "authprov"),

		_("Provider Identifier") =>
		html_form::text_widget("authprovid", 20, 15),

		_("Authorizing Insurance Company") =>
		freemed_display_selectbox ( 
		$sql->query("SELECT * FROM insco ORDER BY insconame,inscostate,inscocity"),
		"#insconame# (#inscocity#,#inscostate#)", "authinsco"),

		_("Number of Visits") =>
		fm_number_select ("authvisits", 0, 100),

		_("Used Visits") =>
		fm_number_select ("authvisitsused", 0, 100),

		_("Remaining Visits") =>
		fm_number_select ("authvisitsremain", 0, 100),

		_("Comment") =>
		html_form::text_widget("authcomment", 30, 100)

	));
 
	$display_buffer .= "
       <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"  ".
         ( ($action=="addform") ? _("Add") : _("Modify"))."  \">
       <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">
       </CENTER>
       </FORM>
     ";
	} // end function authorizationsModule->form()

	function add () {
		global $authpatient, $authdtbegin, $authdtend, $authdtadd, $cur_date, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend   = fm_date_assemble("authdtend");
		$authdtadd   = $cur_date;
		$authpatient = $patient;
		$this->_add();
	} // end function authorizationsModule->add()

	function mod () {
		global $authpatient, $authdtbegin, $authdtend, $authdtmod, $cur_date, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend   = fm_date_assemble("authdtend");
		$authdtmod    = $cur_date;
		$authpatient = $patient;
		$this->_mod();
	} // end function authorizationsModule->mod()

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

     $query = "SELECT * FROM $this->table_name
        WHERE (authpatient='".addslashes($patient)."')
        ORDER BY authdtbegin,authdtend";
     $result = $sql->query ($query);
     $rows = ( ($result > 0) ? $sql->num_rows ($result) : 0 );

     if ($rows < 1) {
       $display_buffer .= "
         <P>
         <CENTER>
         "._("This patient has no authorizations.")."
         </CENTER>
         <P>
         <CENTER>
         <A HREF=\"$this->page_name?action=addform&module=$module&patient=$patient\"
          >"._("Add")." "._("$record_name")."</A>
         <B>|</B>
         <A HREF=\"manage.php?id=$patient\"
          >"._("Manage Patient")."</A>
         </CENTER>
         <P>
       ";
       template_display();
     } // if there are none...

       // or else, display them...
     $display_buffer .= "
       <P>
     ".
     freemed_display_itemlist (
       $result,
       $this->page_name,
       array (
         "Dates" => "authdtbegin",
	 "<FONT COLOR=\"#000000\">_</FONT>" => 
	    "", // &nbsp; doesn't work, dunno why
	 "&nbsp;"  => "authdtend"
       ),
       array ("", "/", "")
     );
	} // end function authorizationsModule->view()

} // end class authorizationsModule

register_module ("authorizationsModule");

} // end if defined

?>
