<?php
 // $Id$
 // note: prescription db/module functions
 // lic : GPL

if (!defined("__PRESCRIPTION_MODULE_PHP__")) {

define(__PRESCRIPTION_MODULE_PHP__, true);
include ("lib/module_emr.php");
//include ("lib/freemed.php");
//include ("lib/API.php");

class prescriptionModule extends freemedEMRModule {

	var $MODULE_NAME = "Prescription";
	var $MODULE_VERSION = 0.1;

	var $record_name = "Prescription";
	var $table_name  = "rx";

  // note: for whoever wants to do this module -- prescription info depends
  // on drug info in a separate db, which hasn't been done yet. the display
  // action shows it in the browser window for printout. other than that,
  // you're on your own...                                           -jb-

	function add ()    { $this->old_main(); }
	function mod ()    { $this->old_main(); }
	function form ()   { $this->old_main(); }
	function view ()   { $this->old_main(); }

	function old_main () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

  switch ($action) { // master action switch
    case "display":
      echo "
        <P>
        This function has NOT been implemented yet.<BR>
        Please wait and do not flame me yet -jeff
        <P>
      ";
      break;
    case "addform":
    case "modform":
      $rxdtfrom = $cur_date;
      echo "
        <FORM ACTION=\"$this->page_name\" METHOD=POST>
        <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
        <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
        <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"".(
		($action=="addform") ? "add" : "mod" )."\">
        <INPUT TYPE=HIDDEN NAME=\"rxpatient\" VALUE=\"".prepare($patient)."\">

        <$STDFONT_B>$Drug : <$STDFONT_E>
      ";

      $rx_r = $sql->query("SELECT * FROM frmlry ORDER BY trdmrkname");
      echo freemed_display_selectbox (
        $rx_r, "#trdmrkname#", "rxdrug"
      )."

        <P>

        <$STDFONT_B>Dosage : <$STDFONT_E>
        <INPUT TYPE=TEXT NAME=\"rxdosage\" VALUE=\"$rxdosage\"
         SIZE=20 MAXLENGTH=100>
        <P>

        <$STDFONT_B>"._("Starting Date")." : <$STDFONT_E>
     ".fm_date_entry("rxdtfrom")."
        <P>

        <$STDFONT_B>Duration (In Days, 0 = Infinite) : <$STDFONT_E>
        <INPUT TYPE=TEXT NAME=\"rxduration\" VALUE=\"$rxduration\"
         SIZE=5 MAXLENGTH=5>
        <P>

        <$STDFONT_B>Refills : <$STDFONT_E>
        <INPUT TYPE=TEXT NAME=\"rxrefills\" VALUE=\"$rxrefills\"
         SIZE=5 MAXLENGTH=4>
        <P>

        <$STDFONT_B>Substitution : <$STDFONT_E>
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
      echo "
        <P><$STDFONT_B><B>"._("Adding")." . . . </B><$STDFONT_E>
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
      if (DEBUG) echo "<BR>query = \"$query\", result = \"$result\"<BR>";
      if ($result) echo "\n<$STDFONT_B><B>"._("done").".</B><$STDFONT_E>\n";
       else echo "\n<$STDFONT_B><B>"._("ERROR")."</B><$STDFONT_E>\n";
      echo "
        <P>
        <CENTER>
        <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
         ><$STDFONT_B>Manage Prescriptions<$STDFONT_E></A> |
        <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
        </CENTER>
        <P>
      ";
      break;
    default:
      $ptlname = freemed_get_link_field ($patient, "patient", "ptlname");
      $ptfname = freemed_get_link_field ($patient, "patient", "ptfname");
      echo "
        <CENTER>
         <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient&action=addform\"
         ><$STDFONT_B>"._("Add")." "._($record_name)."<$STDFONT_E></A> |
         <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
        </CENTER>
        <P>
      ";

      $query = $sql->query ("SELECT * FROM $this->table_name ".
		"WHERE rxpatient='".addslashes($patient)."'");
      $num_records = $sql->num_rows ($query);

      if ($num_records < 1) {
        // if there are no prescriptions yet
        echo "
          <TABLE WIDTH=100% BORDER=0 VALIGN=CENTER ALIGN=CENTER
           CELLSPACING=1 CELLPADDING=1 BGCOLOR=#000000><TR>
          <TD BGCOLOR=#000000><CENTER><$STDFONT_B COLOR=#ffffff>
          <B>No prescriptions for this patient</B></CENTER>
          </TD></TR></TABLE>
        ";
      } else {
        // or else, show them
        echo "
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
          echo "
            <TR><TD>
             <A HREF=\"$page_name?$_auth&patient=$patient&id=$id&action=display\"
              ><$STDFONT_B>".fm_date_print($rxdtfrom)." / 
                           ".fm_date_print($rxdtto)." <$STDFONT_E></A>
              <B>[</B> <A HREF=
               \"$this->page_name?$_auth&module=$module&id=$rxdrug&action=modform\"
               ><$STDFONT_B><I>$drug</I><$STDFONT_E></A> <B>]</B>
            </TR></TD>
          "; 
        }
        echo "
          </TABLE>
        "; // end table
      }
      
      echo "
        <P>
        <CENTER>
         <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient&action=addform\"
         ><$STDFONT_B>"._("Add")." "._($record_name)."<$STDFONT_E></A> |
         <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
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
