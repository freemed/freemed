<?php
  # file: facility.php3
  # note: facility database functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # small mods by max k <amk@span.ch>
  # lic : GPL, v2

  $page_name  ="facility.php3";
  $record_name="Facility";
  $db_name    ="facility";

  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie);
  freemed_display_html_top ();
  freemed_display_banner ();

switch ($action) { // master action switch
 case "addform":
 case "modform":
  // for either add or modify form
  switch ($action) { // internal case 
   case "addform": // internal addform
    $this_action = "$Add";
    $next_action = "add";
    break; // end internal addform
   case "modform": // internal modform
    $this_action = "$Modify";
    $next_action = "mod";
    if (strlen($id)<1) {
      echo "
       <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
       </CENTER>
       <P>
      ";
      freemed_display_box_bottom ();
      echo "
        <CENTER>
        <A HREF=\"main.php3?$_auth\"
         >$Return_to_the_Main_Menu</A>
        </CENTER>
      ";
      DIE("");
    }

    $r = freemed_get_link_rec ($id, "facility");

    $psrname      = fm_prep($r["psrname"     ]);
    $psraddr1     = fm_prep($r["psraddr1"    ]);
    $psraddr2     = fm_prep($r["psraddr2"    ]);
    $psrcity      = fm_prep($r["psrcity"     ]);
    $psrstate     = fm_prep($r["psrstate"    ]);
    $psrzip       = fm_prep($r["psrzip"      ]);
    $psrcountry   = fm_prep($r["psrcountry"  ]);
    $psrnote      = fm_prep($r["psrnote"     ]);
    $psrdateentry = fm_prep($r["psrdateentry"]);
    $psrdefphy    = fm_prep($r["psrdefphy"   ]);
    $psrphone     = fm_prep($r["psrphone"    ]);
    $psrfax       = fm_prep($r["psrfax"      ]);
    $psremail     = fm_prep($r["psremail"    ]);
    $psrein       = fm_prep($r["psrein"      ]);
    $psrintext    = fm_prep($r["psrintext"   ]);
    break;
  } // end internal case

  freemed_display_box_top ("$this_action $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"$next_action\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"$id\"  >

    <TABLE BORDER=0 VALIGN=MIDDLE ALIGN=CENTER
     CELLSPACING=0 CELLPADDING=3>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Facility_Name : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrname\" SIZE=20 MAXLENGTH=25
      VALUE=\"$psrname\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Address_Line_1 : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psraddr1\" SIZE=20 MAXLENGTH=25
       VALUE=\"$psraddr1\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Address_Line_2 : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psraddr2\" SIZE=20 MAXLENGTH=25
       VALUE=\"$psraddr2\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$City : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrcity\" SIZE=10 MAXLENGTH=15
       VALUE=\"$psrcity\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$State : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrstate\" SIZE=4 MAXLENGTH=3
       VALUE=\"$psrstate\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Zip : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"$psrzip\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Country : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrcountry\" SIZE=20 MAXLENGTH=50
       VALUE=\"$psrcountry\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Description_Note : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrnote\" SIZE=20 MAXLENGTH=40
       VALUE=\"$psrnote\">
     </TD>
    </TR>
    <BR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Default Physician : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"psrdefphy\">
  ";

  freemed_display_physicians ($psrdefphy);

  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Phone_Number : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
  ";
  fm_phone_entry ("psrphone");
  echo "
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Fax_Number : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
  ";
  fm_phone_entry ("psrfax");
  echo "
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>$Email_Address : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psremail\" SIZE=25 MAXLENGTH=25
       VALUE=\"$psremail\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Employer Identification Number : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"psrein\" SIZE=10 MAXLENGTH=9
       VALUE=\"".fm_prep($psrein)."\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Internal or External Facility : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"psrintext\">
       <OPTION VALUE=\"0\" ".
        ( ($psrintext == 0) ? "SELECTED" : "" ).">Internal
       <OPTION VALUE=\"1\" ".
        ( ($psrintext == 1) ? "SELECTED" : "" ).">External
      </SELECT>
     </TD>
    </TR>

    </TABLE>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $this_action \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom ();

  echo "
     <P>
     <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >Abandon Add/Modify</A>
     </CENTER>
  ";
  break; // end of addform/modform action

 case "add": // add action
  freemed_display_box_top ("$Adding $record_name", $page_name);
  echo "
    <P>
    <$STDFONT_B>$Adding . . . <$STDFONT_E>
  ";
  $query = "INSERT INTO $database.facility VALUES (
    '".addslashes($psrname).         "',
    '".addslashes($psraddr1).        "',     
    '".addslashes($psraddr2).        "',
    '".addslashes($psrcity).         "',      
    '".addslashes($psrstate).        "',
    '".addslashes($psrzip).          "',       
    '".addslashes($psrcountry).      "',                 .
    '".addslashes($psrnote).         "',
    '".addslashes($cur_date).        "', 
    '".addslashes($psrdefphy).       "',
    '".fm_phone_assemble("psrphone")."',
    '".fm_phone_assemble("psrfax").  "',
    '".addslashes($psremail).        "',
    '".addslashes($psrein).          "',
    '".addslashes($psrintext).       "',
     NULL ) ";

  $result = fdb_query($query);
  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) { echo "<B>$Done.</B>\n"; }
     else      { echo "<B>$ERROR</B>\n"; }

  echo "
   <P>
   <CENTER>
    <A HREF=\"$page_name?$_auth&action=addform\"
    ><$STDFONT_B>Add Another $record_name<$STDFONT_E></A> <B>|</B>
    <A HREF=\"$page_name?$_auth&action=view\"
    ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end action add

 case "mod": // modify action
  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $database.$db_name SET 
    psrname     ='".addslashes($psrname).         "',
    psraddr1    ='".addslashes($psraddr1).        "',
    psraddr2    ='".addslashes($psraddr2).        "',
    psrcity     ='".addslashes($psrcity).         "',
    psrstate    ='".addslashes($psrstate).        "',
    psrzip      ='".addslashes($psrzip).          "',
    psrcountry  ='".addslashes($psrcountry).      "',
    psrnote     ='".addslashes($psrnote).         "',
    psrdefphy   ='".addslashes($psrdefphy).       "',
    psrphone    ='".fm_phone_assemble("psrphone")."',
    psrfax      ='".fm_phone_assemble("psrfax").  "',
    psremail    ='".addslashes($psremail).        "', 
    psrein      ='".addslashes($psrein).          "',
    psrintext   ='".addslashes($psrintext).       "' 
    WHERE id='$id'";

  $result = fdb_query($query);
  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) { echo "<B>$Done.</B>\n"; }
   else        { echo "<B>$ERROR</B>\n"; }

  echo "
   <P>
   <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
    ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A> <B>|</B>
    <A HREF=\"db_maintenance.php3?$_auth\"
    ><$STDFONT_B>Database Maintenance Menu<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end of modify action

 case "del": // delete action
  freemed_display_box_top ("$Deleting $record_name", $page_name);

  $result = fdb_query("DELETE FROM $database.facility
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
  ";
  if ($debug) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  } // debug code
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end of delete action

 default: // default action
  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $database.facility ".
    "ORDER BY psrname,psrnote";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name", $_ref);

    freemed_display_actionbar($page_name); // show action bar at top

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Facility_Name</B></TD>
       <TD><B>$Description_Note</B></TD>
       <TD><B>Int/Ext</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $psrname    = $r[psrname];
      $psrnote    = ( (!empty($r[psrnote])) ? $r[psrnote] : "&nbsp;" );
      $id         = $r["id"];

      $_alternate = freemed_bar_alternate_color ($_alternate);

      $id_mod = ( ($debug) ? " [$id]" : "" );

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$psrname</TD>
        <TD><I>$psrnote</I></TD>
        <TD>".
         ( ($r[psrintext] == 0) ? "Internal" : "External" ).
         "</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

    echo "
      </TABLE>
    ";
    freemed_display_actionbar($page_name); // display bar at bottom
    freemed_display_box_bottom ();

  } else {
    echo "\n<B>$No_Records_Found</B>\n";
  }

} // end of case statement  

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
