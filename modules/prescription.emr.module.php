<?php
 // $Id$
 // note: prescription db/module functions
 // lic : GPL

if (!defined("__PRESCRIPTION_MODULE_PHP__")) {

define(__PRESCRIPTION_MODULE_PHP__, true);

class prescriptionModule extends freemedEMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_VERSION = 0.1;

	var $record_name    = "Prescription";
	var $table_name     = "rx";
	var $patient_field  = "rxpatient";

  // note: for whoever wants to do this module -- prescription info depends
  // on drug info in a separate db, which hasn't been done yet. the display
  // action shows it in the browser window for printout. other than that,
  // you're on your own...                                           -jb-

	function prescriptionModule () {
		$this->freemedEMRModule();
		$this->summary_vars = array (
			"Date From" => "rxdtfrom",
			"Crypto Key" => "rxmd5"
		);
		// Specialized query bits
		$this->summary_query = array (
			"MD5(id) AS rxmd5"
		);
	} // end constructor prescriptionModule

	function add ()    { $this->old_main(); }
	function mod ()    { $this->old_main(); }
	function form ()   { $this->old_main(); }
	function view ()   { $this->old_main(); }

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
    case "addform":
    case "modform":
      $rxdtfrom = $cur_date;
      $display_buffer .= "
        <FORM ACTION=\"".$this->page_name."\" METHOD=POST>
        <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
        <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
        <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"".(
		($action=="addform") ? "add" : "mod" )."\">
        <INPUT TYPE=HIDDEN NAME=\"rxpatient\" VALUE=\"".prepare($patient)."\">

        "._("Drug")." : ";

      $rx_r = $sql->query("SELECT * FROM frmlry ORDER BY trdmrkname");
      $display_buffer .= freemed_display_selectbox (
        $rx_r, "#trdmrkname#", "rxdrug"
      )."

        <P>

        "._("Dosage")." :
        <INPUT TYPE=TEXT NAME=\"rxdosage\" VALUE=\"$rxdosage\"
         SIZE=20 MAXLENGTH=100>
        <P>

        "._("Starting Date")." :
     ".fm_date_entry("rxdtfrom")."
        <P>

        "._("Duration")." (In Days, 0 = Infinite) :
        <INPUT TYPE=TEXT NAME=\"rxduration\" VALUE=\"$rxduration\"
         SIZE=5 MAXLENGTH=5>
        <P>

        "._("Refills")." :
        <INPUT TYPE=TEXT NAME=\"rxrefills\" VALUE=\"$rxrefills\"
         SIZE=5 MAXLENGTH=4>
        <P>

        "._("Substitution")." :
        <SELECT NAME=\"rxsubstitute\">
         <OPTION VALUE=\"may not subsitute\">$May_Not_Substitute
         <OPTION VALUE=\"may substitute\"   >$May_Substitute
        </SELECT>
        <P>

        <CENTER>
        <INPUT TYPE=SUBMIT VALUE=\" "._("Add")." \">
        <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
        </CENTER>
        </FORM>
      ";
      break;
    case "add":
      $display_buffer .= "
        <P><B>"._("Adding")." . . . </B>
      ";
      $rxdtadd = $cur_date;
      //$rxdtfrom = $rxdtfrom_y. "-". $rxdtfrom_m. "-". $rxdtfrom_d;
      $rxdtfrom = fm_date_assemble("rxdtfrom");
      $query = "INSERT INTO ".$this->table_name." VALUES (
        '$rxdtadd',
        '$rxdtmod',
        '$rxpatient',
        '$rxdtfrom',
        '$rxduration',
        '$rxdrug',
        '$rxdosage',
        '$rxrefills',
        '$rxsubstitute',
        '$rxmd5sum',
        NULL ) ";
      $result = $sql->query ($query);
      if (DEBUG) $display_buffer .= "<BR>query = \"$query\", result = \"$result\"<BR>";
      if ($result) $display_buffer .= "\n<B>"._("done").".</B>\n";
       else $display_buffer .= "\n<B>"._("ERROR")."</B>\n";
      $display_buffer .= "
        <P>
        <CENTER>
        <A HREF=\"$this->page_name?module=$module&patient=$patient\"
         >"._("Manage Prescriptions")."</A> |
        <A HREF=\"manage.php?id=$patient\"
         >"._("Manage Patient")."</A>
        </CENTER>
        <P>
      ";
      break;
    default:
      $ptlname = freemed_get_link_field ($patient, "patient", "ptlname");
      $ptfname = freemed_get_link_field ($patient, "patient", "ptfname");
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
          $drug = freemed_get_link_field ($rxdrug, "frmlry", "trdmrkname");
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
