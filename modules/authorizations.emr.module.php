<?php
 // $Id$
 // note: patient authorizations module
 // code: jeff b (jeff@ourexchange.net)
 //       adam b (gdrago23@yahoo.com)
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class AuthorizationsModule extends EMRModule {

	var $MODULE_NAME    = "Insurance Authorizations";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_DESCRIPTION = "
		Insurance authorizations are used to track whether
		a patient is authorized by his or her insurance
		company for service during a particular period of
		time. If you do not use insurance support in
		FreeMED, this module is not needed.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

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

	function AuthorizationsModule () {
		// Table definition
		$this->table_definition = array (
			'authdtadd' => SQL__DATE,
			'authdtmod' => SQL__DATE,
			'authpatient' => SQL__INT_UNSIGNED(0),
			'authdtbegin' => SQL__DATE,
			'authdtend' => SQL__DATE,
			'authnum' => SQL__VARCHAR(25),
			'authtype' => SQL__INT_UNSIGNED(0),
			'authprov' => SQL__INT_UNSIGNED(0),
			'authprovid' => SQL__VARCHAR(20),
			'authinsco' => SQL__INT_UNSIGNED(0),
			'authvisits' => SQL__INT_UNSIGNED(0),
			'authvisitsused' => SQL__INT_UNSIGNED(0),
			'authvisitsremain' => SQL__INT_UNSIGNED(0),
			'authcomment' => SQL__VARCHAR(100),
			'id' => SQL__SERIAL
		);
	
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("From") => "authdtbegin",
			__("To")   => "authdtend"
		);

		// Run parent constructor
		$this->EMRModule();
	} // end constructor AuthorizationsModule

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

     switch ($action) { // internal action switch
      case "addform":
       // do nothing
       break; // end internal addform
      case "modform":
       if (($patient<1) OR (empty($patient))) {
         $display_buffer .= __("You must select a patient.")."\n";
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
       <INPUT TYPE=HIDDEN NAME=\"return\"  VALUE=\"".prepare($return)."\">

	";

	$display_buffer .= html_form::form_table(array(
		__("Starting Date") =>
		date_entry("authdtbegin"),

		__("Ending Date") =>
		date_entry("authdtend"),

		__("Authorization Number") =>
		html_form::text_widget("authnum", 25),

		__("Authorization Type") =>
		html_form::select_widget(
			"authtype",
			array(
				__("NONE SELECTED") => "0",
				__("physician") => "1",
				__("insurance company") => "2",
				__("certificate of medical neccessity") => "3",
				__("surgical") => "4",
				__("worker's compensation") => "5",
				__("consulatation") => "6"
			)
		),

		__("Authorizing Provider") =>
		freemed_display_selectbox (
		$sql->query("SELECT * FROM physician ORDER BY phylname,phyfname"),
		"#phylname#, #phyfname#", "authprov"),

		__("Provider Identifier") =>
		html_form::text_widget("authprovid", 20, 15),

		__("Authorizing Insurance Company") =>
		freemed_display_selectbox ( 
		$sql->query("SELECT * FROM insco ORDER BY insconame,inscostate,inscocity"),
		"#insconame# (#inscocity#,#inscostate#)", "authinsco"),

		__("Number of Visits") =>
		fm_number_select ("authvisits", 0, 100),

		__("Used Visits") =>
		fm_number_select ("authvisitsused", 0, 100),

		__("Remaining Visits") =>
		fm_number_select ("authvisitsremain", 0, 100),

		__("Comment") =>
		html_form::text_widget("authcomment", 30, 100)

	));
 
	$display_buffer .= "
       <div ALIGN=\"CENTER\">
       <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"  ".
         ( ($action=="addform") ? __("Add") : __("Modify"))."  \"/>
       <input TYPE=\"RESET\" VALUE=\" ".__("Clear")." \" class=\"button\"/>
	<input TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\" class=\"button\"/>
       </div>
       </form>
     ";
	} // end function AuthorizationsModule->form()

	function add () {
		global $authpatient, $authdtbegin, $authdtend, $authdtadd, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend   = fm_date_assemble("authdtend");
		$authdtadd   = date("Y-m-d");
		$authpatient = $patient;
		$this->_add();
	} // end function AuthorizationsModule->add()

	function mod () {
		global $authpatient, $authdtbegin, $authdtend, 
			$authdtmod, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend = fm_date_assemble("authdtend");
		$authdtmod = date("Y-m-d");
		$authpatient = $patient;
		$this->_mod();
	} // end function AuthorizationsModule->mod()

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				"WHERE (authpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY authdtbegin,authdtend"
			),
			$this->page_name,
			array (
				"Dates" => "authdtbegin",
				"<FONT COLOR=\"#000000\">_</FONT>" => 
					"", // &nbsp; doesn't work, dunno why
				"&nbsp;"  => "authdtend"
			),
			array ("", "/", "")
		);
	} // end function AuthorizationsModule->view()

} // end class AuthorizationsModule

register_module ("AuthorizationsModule");

?>
