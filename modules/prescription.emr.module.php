<?php
 // $Id$
 // note: prescription db/module functions
 // lic : GPL

if (!defined("__PRESCRIPTION_MODULE_PHP__")) {

define(__PRESCRIPTION_MODULE_PHP__, true);

class prescriptionModule extends freemedEMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_VERSION = 0.2;
	var $MODULE_DESCRIPTION = "
		The prescription module allows prescriptions to be written 
		for patients from any drug in the local formulary or in the 
		Multum drug database (if access to that database is 
		available.";

	var $record_name    = "Prescription";
	var $table_name     = "rx";
	var $patient_field  = "rxpatient";

	function prescriptionModule () {
		$this->summary_vars = array (
			"Date From" => "rxdtfrom",
			"Drug" => "rxdrug"
			//"Crypto Key" => "rxmd5"
		);
		// Specialized query bits
		$this->summary_query = array (
			"MD5(id) AS rxmd5"
		);

		// Table definition
		$this->table_definition = array (
			"rxdtfrom" => SQL_DATE,
			"rxdrug" => SQL_VARCHAR(150),
			"rxform" => SQL_ENUM(array(
				"suspension",
				"tablet",
				"capsule",
				"solution"
				)),
			"rxdosage" => SQL_INT_UNSIGNED(0),
			"rxunit" => SQL_ENUM(array(
				"mg",
				"mg/1cc",
				"mg/2cc",
				"mg/3cc",
				"mg/4cc",
				"mg/5cc",
				"g"
				)),
			"rxinterval" => SQL_ENUM(array(
				"b.i.d.",
				"t.i.d.",
				"q.i.d.",
				"q. 3h",
				"q. 4h",
				"q. 5h",
				"q. 6h",
				"q. 8h"
				)),
			"rxpatient" => SQL_INT_UNSIGNED(0),
			"rxsubstitute" => SQL_ENUM(array(
				"may substitute", "may not substitute"
				)),
			"rxrefills" => SQL_INT_UNSIGNED(0),
			"rxperrefill" => SQL_INT_UNSIGNED(0),
			"rxnote" => SQL_TEXT,
			"id" => SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);

		$this->variables = array (
			"rxdtfrom" => date_assemble("rxdtfrom"),
			"rxdrug",
			"rxdosage",
			"rxunit",
			"rxinterval",
			"rxpatient",
			"rxsubstitute",
			"rxrefills",
			"rxperrefill",
			"rxnote"
		);
		$this->freemedEMRModule();
	} // end constructor prescriptionModule

	function form () {
		global $display_buffer, $sql, $action, $id, $patient,
			$return;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		// If modify, grab old record
		if (($action=="mod") or ($action=="modform")) {
			$r = freemed::get_link_rec($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = $v;
			}
		}

		// Create new notebook
		$book = new notebook (
			array ("module", "action", "id", "patient", "return"),
			NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH | NOTEBOOK_NOFORM
		);
		$book->set_submit_name(
			(
				( ($action=="add") or ($action=="addform") ) ?
				_("Add") :
				 _("Modify")
			)
		);

		// Add pages
		$book->add_page(
			_("Prescription"),
			array(
				"rxdtfrom",
				"rxdrug",
				"rxsize",
				"rxunit",
				"rxdosage",
				"rxform",
				"rxinterval",
				"rxrefills",
				"rxperrefill",
				"rxsubstitute"
			),
			html_form::form_table(array(
				_("Starting Date") =>
				date_entry("rxdtfrom"),

				_("Drug") =>
				freemed::drug_widget("rxdrug", "myform", "__action"),

				_("Medicine Units") =>
				html_form::text_widget(
					"rxsize", 10
				).
				html_form::select_widget(
					"rxunit",
					array(
						"mg" => "mg",
						"mg/1cc" => "mg/1cc",
						"mg/2cc" => "mg/2cc",
						"mg/3cc" => "mg/3cc",
						"mg/4cc" => "mg/4cc",
						"mg/5cc" => "mg/5cc",
						"g" => "g"
					)
				),

				_("Dosage") =>
				html_form::text_widget(
					"rxdosage", 10
				).
				" "._("in")." ".
				html_form::select_widget(
					"rxform",
					array(
						"suspension" => "suspension",
						"tablet" => "tablet",
						"capsule" => "capsule",
						"solution" => "solution"
					)
				)." ".
				html_form::select_widget(
					"rxinterval",
					array(
						"b.i.d." => "b.i.d.",
						"t.i.d." => "t.i.d.",
						"q.i.d." => "q.i.d.",
						"q. 3h",
						"q. 4h",
						"q. 5h",
						"q. 6h",
						"q. 8h"
					)
				),

				_("Refill") =>
				html_form::number_pulldown(
					"rxrefills", 0, 20
				)." / ".
				html_form::text_widget(
					"rxperrefill", 10
				)." "._("units"),

				_("Substitution") =>
				html_form::select_widget(
					"rxsubstitute",
					array (
					_("may not substitute") => "may not substitute",
					_("may substitute") => "may substitute"
					)
				)
			))
		);

		$book->add_page(
			_("Notes"),
			array(
				"rxnote"
			),
			"<DIV ALIGN=\"CENTER\">\n".
			html_form::text_area(
				"rxnote"
			).
			"</DIV>"
		);

		// Handle cancel
		if ($book->is_cancelled()) {
			if ($return=="manage") {
				Header("Location: manage.php?".
					"id=".urlencode($patient));
			} else {
				Header("Location: module_loader.php?module=".
					urlencode($module)."&".
					"patient=".urlencode($patient));
			}
			die("");
		}

		// If not done, display
		if (!$book->is_done()) {
			$display_buffer .= "<CENTER>\n";
			$display_buffer .= "<FORM NAME=\"myform\" ACTION=\"".
				$this->page_name."\" METHOD=\"POST\">\n";
			$display_buffer .= $book->display();
			$display_buffer .= "</FORM>\n";
			$display_buffer .= "</CENTER>\n";
			return true;
		}

		// Process notebook
		switch ($action) {
			case "add": case "addform":
			$this->prepare();
			$this->add();
			break;

			case "mod": case "modform":
			$this->prepare();
			$this->mod();
			break;
		}
	} // end function prescriptionModule->form

	function prepare () {
		// Common stuff between add/mod to prepare vars
		global $display_buffer,
			$rxpatient, $patient;
		$rxpatient = $patient;
	} // end function prescriptionModule->prepare

	function view () {
		global $display_buffer, $patient;
		foreach ($GLOBALS AS $k => $v) global ${$k};
		$display_buffer .= freemed_display_itemlist(
			$sql->query("SELECT *,".
				"CONCAT(rxdosage,' ',rxunit,' ',".
				"rxinterval) AS _dosage ".
				"FROM $this->table_name ".
				"WHERE rxpatient='".addslashes($patient)."' ".
				"ORDER BY rxdtfrom DESC"),
			$this->page_name,
			array(
				_("Date") => "rxdtfrom",
				_("Drug") => "rxdrug",
				_("Dosage") => "_dosage"
			),
			array("", _("NONE")),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL
		);
	} // end function prescriptionModule->view

	function old_main () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

  switch ($action) { // master action switch
    case "display":
      $display_buffer .= "
        <P>
        This function has NOT been implemented yet.<BR>
        Please wait and do not flame me yet -jeff
        <P>
      ";
      break;
    default:
      $display_buffer .= "
        <CENTER>
         <A HREF=\"$this->page_name?module=$module&patient=$patient&action=addform\"
         >"._("Add")." "._($record_name)."</A> |
         <A HREF=\"manage.php?id=$patient\"
         >"._("Manage Patient")."</A>
        </CENTER>
        <P>
      ";

      $query = $sql->query ("SELECT * FROM $this->table_name ".
		"WHERE rxpatient='".addslashes($patient)."'");
      $num_records = $sql->num_rows ($query);

      if ($num_records < 1) {
        // if there are no prescriptions yet
        $display_buffer .= "
          <TABLE WIDTH=100% BORDER=0 VALIGN=CENTER ALIGN=CENTER
           CELLSPACING=1 CELLPADDING=1 BGCOLOR=#000000><TR>
          <TD BGCOLOR=\"#000000\"><CENTER><FONT COLOR=\"#ffffff\">
          <B>No prescriptions for this patient</B></CENTER>
          </TD></TR></TABLE>
        ";
      } else {
        // or else, show them
        $display_buffer .= "
          <TABLE BORDER=1 CELLSPACING=1 CELLPADDING=1 ALIGN=CENTER
           BGCOLOR=#ffffff VALIGN=CENTER>
        "; // table header
        while ( $r = $sql->fetch_array ($query) ) {
          extract ($r);
          $drug = freemed::get_link_field ($rxdrug, "frmlry", "trdmrkname");
          $rxdtto       = $rxdtfrom;  // set to starting date
          if ($rxduration > 0) 
            for ($i=1; $i<$rxduration; $i++) 
              $rxdtto = freemed_get_date_next ($rxdtto); // increment date
          else
            $rxdtto = "unspecified";
          $display_buffer .= "
            <TR><TD>
             <A HREF=\"$this->page_name?module=".urlencode($module)."&patient=$patient&id=$id&action=display\"
              >".fm_date_print($rxdtfrom)." / 
                           ".fm_date_print($rxdtto)." </A>
              <B>[</B> <A HREF=
               \"$this->page_name?module=".urlencode($module)."&id=$rxdrug&action=modform\"
               ><I>$drug</I></A> <B>]</B>
            </TR></TD>
          "; 
        }
        $display_buffer .= "
          </TABLE>
        "; // end table
      }
      
      $display_buffer .= "
        <P>
        <CENTER>
         <A HREF=\"$this->page_name?module=".urlencode($module)."&patient=$patient&action=addform\"
         >"._("Add")." "._($record_name)."</A> |
         <A HREF=\"manage.php?id=$patient\"
         >"._("Manage Patient")."</A>
        </CENTER>
        <P>
      ";
      break;
  } // end master action switch

	} // end function prescriptionModule->main()

} // end class prescriptionModule

register_module ("prescriptionModule");

} // end if not defined

?>
