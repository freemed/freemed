<?php
 // file: icd9.php3
 // note: icd9 codes database functions
 // code: mark l (lesswin@ibm.net)
 //       jeff b (jeff@univrel.pr.uconn.edu) -- rewrite
 // lic : GPL, v2

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="icd9.php3";              // for help info, later
  $db_name  ="icd9";                   // get this from jeff
  $record_name="ICD9 Code";            // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="icd9code,icdnum";

  include ("lib/freemed.php");         // load global variables
  include ("lib/API.php");  // API functions

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

// *** main action loop ***
// (default action is "view")
switch ($action) {
 case "addform": case "modform":
  switch ($action) { // internal action switch
   case "addform":
    break;
   case "modform":
    if (!$been_here) {
      extract(freemed_get_link_rec ($id,$db_name));
      $icdamt        = bcadd($icdamt, 0,2);
      $icdcoll       = bcadd($icdcoll,0,2);
      $been_here=1;
    }
    break;
  } // end internal action switch
  freemed_display_box_top (( ($action=="addform") ? _("Add") : _("Modify")).
    " "._($record_name));

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
      ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\">

    <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
     VALIGN=MIDDLE ALIGN=CENTER>

    <TR>
    <TD ALIGN=RIGHT WIDTH=\"50%\">
      <$STDFONT_B>"._("Code")." ("._("ICD9").") : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9code\" SIZE=10 MAXLENGTH=6 
     VALUE=\"".prepare($icd9code)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Meta Description")." : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdmetadesc\" SIZE=10 MAXLENGTH=30
     VALUE=\"".prepare($icdmetadesc)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Code")." ("._("ICD10").") : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10code\" SIZE=10 MAXLENGTH=7
     VALUE=\"".prepare($icd10code)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Description")." ("._("ICD9").") : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9descrip\" SIZE=20 MAXLENGTH=45
     VALUE=\"".prepare($icd9descrip)."\"></TD>
    </TR>
    
    <TR>
    <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Description")." ("._("ICD10").") : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10descrip\" SIZE=20 MAXLENGTH=45
     VALUE=\"".prepare($icd10descrip)."\"></TD>
    </TR>

    <!-- date of entry = $cur_date -->

    <TR>
    <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Diagnosis Related Groups")." : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icddrg\" SIZE=20 MAXLENGTH=45
     VALUE=\"".prepare($icddrg)."\"></TD>
    </TR>

    <!-- initially, number of times used is 0 -->
    <INPUT TYPE=HIDDEN NAME=\"icdnum\" VALUE=\"0\">

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Amount Billed")." : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdamt\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($icdamt)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Amount Collected")." : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdcoll\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($icdcoll)."\">
    </TR>

    </TABLE>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
      ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >".( ($action=="addform") ?
      _("Abandon Addition") : _("Abandon Modification") )."</A>
    </CENTER>
  ";
  break; // end add/mod form

 case "add":
  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P>
    <$STDFONT_B>"._("Adding")." . . . 
  ";

    // build the query to MySQL:
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $db_name VALUES ( ".
    "'".addslashes($icd9code)."',      ".
    "'".addslashes($icd10code)."',     ".
    "'".addslashes($icd9descrip)."',   ".
    "'".addslashes($icd10descrip)."',  ".
    "'".addslashes($icdmetadesc)."',   ".
    "'".addslashes($icdng)."',         ".
    "'".addslashes($icddrg)."',        ".
    "'".addslashes($icdnum)."',        ".
    "'".addslashes($icdamt)."',        ".
    "'".addslashes($icdcoll)."',       ".
    " NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($result) {
    echo "<B>"._("done").".</B><$STDFONT_E>\n";
  } else {
    echo "<B>"._("ERROR")." ($result)</B>\n"; 
  }

  freemed_display_box_bottom (); // display the bottom of the box
  break; // end action add

 case "mod":
  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P>
    <$STDFONT_B>"._("Modifying")." . . . 
  ";

  $query = "UPDATE $db_name SET ".
    "icd9code    ='".addslashes($icd9code)."',    ".
    "icd10code   ='".addslashes($icd10code)."',   ".
    "icd9descrip ='".addslashes($icd9descrip)."', ".
    "icd10descrip='".addslashes($icd10descrip)."',".
    "icdmetadesc ='".addslashes($icdmetadesc)."', ".
    "icddrg      ='".addslashes($icddrg)."',      ".
    "icdng       ='".addslashes($icdng)."',       ".
    "icdnum      ='".addslashes($icdnum)."',      ".
    "icdamt      ='".addslashes($icdamt)."',      ".
    "icdcoll     ='".addslashes($icdcoll)."'      ". 
    "WHERE id='".addslashes($id)."'";

  $result = fdb_query($query); // execute query

  if ($result)  echo _("done")."<$STDFONT_E>\n";
    else { echo _("ERROR")." ($result)<$STDFONT_E>\n"; } 

  freemed_display_box_bottom (); // display box bottom 
  break; // end action mod
 
 case "del":
  freemed_display_box_top (_("Deleting")." "._($record_name));

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = '".addslashes($id)."')");

  echo "
    <P>
    <I>"._($record_name)." <B>$id</B> "._("deleted")."<I>.
    <BR>
  ";

  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end action del

 default:
  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  freemed_display_box_top (_($record_name));
    
  echo freemed_display_itemlist (
    fdb_query("SELECT * FROM $db_name ORDER BY $order_field"),
    $page_name,
    array (
      _("Code")		=> 	"icd9code",
      _("Description")	=>	"icd9descrip"
    ),
    array ("", _("NO DESCRIPTION")), "", "t_page"
  );
  freemed_display_box_bottom (); // display bottom of the box
  break; // end default
} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
