<?php
  # file: icd9.php3
  # note: icd9 codes database functions
  # code: used the template 
  #       mark l (lesswin@ibm.net) -- template
  # lic : GPL, v2

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="icd9.php3";              // for help info, later
  $db_name  ="icd9";                   // get this from jeff
  $record_name="ICD9 Code";            // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="icd9code,icdnum";

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

// *** main action loop ***
// (default action is "view")
if ($action=="addform") {

  freemed_display_box_top ("$Add $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
     VALIGN=MIDDLE ALIGN=CENTER>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD9_Code : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9code\" SIZE=10 MAXLENGTH=6 
     VALUE=\"$icd9code\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>Meta Description : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdmetadesc\" SIZE=10 MAXLENGTH=30
     VALUE=\"$icdmetadesc\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD10_Code : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10code\" SIZE=10 MAXLENGTH=7
     VALUE=\"$icd10code\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD9_Description : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9descrip\" SIZE=20 MAXLENGTH=45
     VALUE=\"$icd9descrip\"></TD>
    </TR>
    
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD10_Description : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10descrip\" SIZE=20 MAXLENGTH=45
     VALUE=\"$icd10descrip\"></TD>
    </TR>

    <!-- date of entry = $cur_date -->

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Diagnosis_Related_Groups : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icddrg\" SIZE=20 MAXLENGTH=45
     VALUE=\"$icddrg\"></TD>
    </TR>

    <!-- initially, number of times used is 0 -->
    <INPUT TYPE=HIDDEN NAME=\"icdnum\" VALUE=\"0\">

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Amount_Billed : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdamt\" SIZE=10 MAXLENGTH=12
     VALUE=\"$icdamt\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Amount_Collected : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdcoll\" SIZE=10 MAXLENGTH=12
     VALUE=\"$icdcoll\">
    </TR>

    </TABLE>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\" $Reset \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

    // build the query to MySQL:
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $database.$db_name VALUES ( ".
    "'".addslashes($icd9code)."',      ".
    "'".addslashes($icd10code)."',     ".
    "'".addslashes($icd9descrip)."',   ".
    "'".addslashes($icd10descrip)."',  ".
    "'".addslashes($icdmetadesc)."',   ".
    "'$icdng',                         ".
    "'$icddrg',                        ".
    "'$icdnum',                        ".
    "'$icdamt',                        ".
    "'$icdcoll',                       ".
    " NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B></TT>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  freemed_display_box_bottom (); // display the bottom of the box

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = fdb_query("SELECT * FROM $database.$db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

  $icd9code      = $r["icd9code"    ];
  $icd10code     = $r["icd10code"   ];
  $icd9descrip   = $r["icd9descrip" ];
  $icd10descrip  = $r["icd10descrip"];
  $icdmetadesc   = $r["icdmetadesc" ];
  $icdng         = $r["icdng"       ];
  $icddrg        = $r["icddrg"      ];
  $icdamt        = bcadd($r["icdamt"], 0,2);
  $icdcoll       = bcadd($r["icdcoll"],0,2);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"$id\">

    <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
     VALIGN=MIDDLE ALIGN=CENTER>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD9_Code : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd9code\" SIZE=10 MAXLENGTH=6 
     VALUE=\"$icd9code\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>Meta Description : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdmetadesc\" SIZE=31 MAXLENGTH=30
     VALUE=\"$icdmetadesc\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD10_Code : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10code\" SIZE=10 MAXLENGTH=7 
     VALUE=\"$icd10code\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD9_Description : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=icd9descrip SIZE=46 MAXLENGTH=45
     VALUE=\"$icd9descrip\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$ICD10_Description : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icd10descrip\" SIZE=46 MAXLENGTH=45
     VALUE=\"$icd10descrip\"></TD>
    </TD>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Date_of_Entry : <$STDFONT_E></TD>
    <TD>".fm_prep($icdng)."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Diagnosis_Related_Groups : <$STDFONT_E></TD>
    <TD><!-- <INPUT TYPE=TEXT NAME=\"icddrg\" SIZE=20 MAXLENGTH=45
     VALUE=\"$icddrg\">-->NOT IMPLEMENTED!</TD>
    </TR>
   
    <TR> 
    <TD ALIGN=RIGHT><$STDFONT_B>$Number_of_Times_Used : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdnum\" SIZE=10 MAXLENGTH=12
     VALUE=\"$icdnum\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Amount_Billed : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdamt\" SIZE=10 MAXLENGTH=12
     VALUE=\"$icdamt\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>$Amount_Collected : <$STDFONT_E></TD>
    <TD><INPUT TYPE=TEXT NAME=\"icdcoll\" SIZE=10 MAXLENGTH=12
     VALUE=\"$icdcoll\"></TD>
    </TR>

    </TABLE>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $database.$db_name SET ".
    "icd9code    ='$icd9code',    ".
    "icd10code   ='$icd10code',   ".
    "icd9descrip ='$icd9descrip', ".
    "icd10descrip='$icd10descrip',".
    "icdmetadesc ='$icdmetadesc', ". // newly added meta description
    "icddrg      ='$icddrg',      ".
    "icdng       ='$icdng',       ".
    "icdnum      ='$icdnum',      ".
    "icdamt      ='$icdamt',      ".
    "icdcoll     ='$icdcoll'      ". 
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  freemed_display_box_bottom (); // display box bottom 

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $database.$db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> deleted<I>.
    <BR>
  ";
  if ($debug) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...
  $query = "SELECT * FROM $database.$db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // and comment this line:
    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$ICD9_Code</B></TD>
       <TD><B>$ICD9_Description</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $icd9code    = fm_prep($r["icd9code"]);
      $icd9descrip = fm_prep($r["icd9descrip"]);
      $id          = fm_prep($r["id"]);

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$icd9code</TD>
        <TD><I>$icd9descrip</I>&nbsp;</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($user)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // then comment this:
    freemed_display_actionbar ($page_name, $_ref);

    freemed_display_box_bottom (); // display bottom of the box

  } else {
    echo "\n<B>$No_Record_Found</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
