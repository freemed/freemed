<?php
  # file: facility.php3
  # note: facility database functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # small mods by max k <amk@span.ch>
  # lic : GPL, v2

  $page_name  ="facility.php3";
  $record_name="Facility (POS)";
  $db_name    ="facility";

  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie);
  freemed_display_html_top ();
  freemed_display_banner ();

if ($action=="addform") {

  freemed_display_box_top ("$Add $record_name", $page_name);

  if ($debug) {
    echo "
      date = ($cur_date)<BR>
    ";
  }
  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">

    <$STDFONT_B>$Facility_Name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrname SIZE=20 MAXLENGTH=25
     VALUE=\"$psrname\">
    <BR>

    <$STDFONT_B>$Address_Line_1 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psraddr1 SIZE=20 MAXLENGTH=25
     VALUE=\"$psraddr1\">
    <BR>
    <$STDFONT_B>$Address_Line_2 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psraddr2 SIZE=20 MAXLENGTH=25
     VALUE=\"$psraddr2\">
    <BR>

    <$STDFONT_B>$City : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrcity SIZE=10 MAXLENGTH=15
     VALUE=\"$psrcity\">
    <BR>
    <$STDFONT_B>$State : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrstate SIZE=4 MAXLENGTH=3
     VALUE=\"$psrstate\">
   
     <$STDFONT_B>$Zip : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrzip SIZE=11 MAXLENGTH=10
     VALUE=\"$psrzip\">
    <BR>

     <$STDFONT_B>$Country : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrcountry SIZE=20 MAXLENGTH=50
     VALUE=\"$psrcountry\">
    <BR>

    <$STDFONT_B>$Description_Note : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrnote SIZE=20 MAXLENGTH=40
     VALUE=\"$psrnote\">
    <BR>

    <$STDFONT_B>$Default_Physician : <$STDFONT_E>
    <SELECT NAME=\"psrdefphy\">
  ";

  freemed_display_physicians ($psrdefphy);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>$Phone_Number : <$STDFONT_E>
  ";
  fm_phone_entry ("psrphone");
  echo "
    <BR>
    <$STDFONT_B>$Fax_Number : <$STDFONT_E>
  ";
  fm_phone_entry ("psrfax");
  echo "
    <BR>

    <$STDFONT_B>$Email_Address : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"psremail\" SIZE=25 MAXLENGTH=25
     VALUE=\"$psremail\">
    <P>

    <$STDFONT_B>Employer Identification Number : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"psrein\" SIZE=10 MAXLENGTH=9
     VALUE=\"".fm_prep($psrein)."\">
    <P>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top ("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $psrdateentry = $cur_date; // set to current date

  // 19990924 -- add slashes for certain fields
  $psrname  = addslashes ($psrname );
  $psraddr1 = addslashes ($psraddr1);
  $psraddr2 = addslashes ($psraddr2);
  $psrnote  = addslashes ($psrnote);

  $query = "INSERT INTO $database.facility VALUES ( ".
    "'$psrname',    '$psraddr1',     ".
    "'$psraddr2',   '$psrcity',      ".
    "'$psrstate',   '$psrzip',       ".
    "'$psrcountry',                  ".
    "'$psrnote',    '$psrdateentry', ".
    "'$psrdefphy', ".
    "'".fm_phone_assemble("psrphone")."',     ".
    "'".fm_phone_assemble("psrfax").  "',     ".
    "'$psremail',  '".addslashes($psrein)."', ".
    " NULL ) ";

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
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

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

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
     </CENTER>

     <P>
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

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

  $psrname      = $r["psrname"     ];
  $psraddr1     = $r["psraddr1"    ];
  $psraddr2     = $r["psraddr2"    ];
  $psrcity      = $r["psrcity"     ];
  $psrstate     = $r["psrstate"    ];
  $psrzip       = $r["psrzip"      ];
  $psrcountry   = $r["psrcountry"  ];  // 19991005
  $psrnote      = $r["psrnote"     ];
  $psrdateentry = $r["psrdateentry"];
  $psrdefphy    = $r["psrdefphy"   ];
  $psrphone     = $r["psrphone"    ];
  $psrfax       = $r["psrfax"      ];
  $psremail     = $r["psremail"    ];

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Date_of_Entry : <$STDFONT_E>
    $psrdateentry<BR>

    <$STDFONT_B>$Facility_Name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrname SIZE=20 MAXLENGTH=25
     VALUE=\"$psrname\">
    <BR>

    <$STDFONT_B>$Address_Line_1 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psraddr1 SIZE=20 MAXLENGTH=25
     VALUE=\"$psraddr1\">
    <BR>
    <$STDFONT_B>$Address_Line_2 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psraddr2 SIZE=20 MAXLENGTH=25
     VALUE=\"$psraddr2\">
    <BR>

    <$STDFONT_B>$City : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrcity SIZE=10 MAXLENGTH=15
     VALUE=\"$psrcity\">
    <BR>
    <$STDFONT_B>$State : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrstate SIZE=4 MAXLENGTH=3
     VALUE=\"$psrstate\">
    <$STDFONT_B>$Zip : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrzip SIZE=11 MAXLENGTH=10
     VALUE=\"$psrzip\">
    <BR>
    <$STDFONT_B>$Country : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrcountry SIZE=20 MAXLENGTH=50
     VALUE=\"$psrcountry\">
    <BR>
    <$STDFONT_B>$Description_Note : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psrnote SIZE=20 MAXLENGTH=40
     VALUE=\"$psrnote\">
    <BR>

    <$STDFONT_B>$Default_Physician : <$STDFONT_E>
    <SELECT NAME=\"psrdefphy\">
  ";

  freemed_display_physicians ($psrdefphy);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>$Phone_Number : <$STDFONT_E>
  ";
  fm_phone_entry ("psrphone");
  echo "
    <BR>
    <$STDFONT_B>$Fax_Number : <$STDFONT_E>
  ";
  fm_phone_entry ("psrfax");
  echo "
    <BR>

    <$STDFONT_B>$Email_Address : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=psremail SIZE=25 MAXLENGTH=25
     VALUE=\"$psremail\">
    <P>

    <$STDFONT_B>Employer Identification Number : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"psrein\" SIZE=10 MAXLENGTH=9
     VALUE=\"".fm_prep($r[psrein])."\">

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom ();

  echo "
     <BR><BR>
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
    "psrname     ='".addslashes($psrname).         "', ".
    "psraddr1    ='".addslashes($psraddr1).        "', ".
    "psraddr2    ='".addslashes($psraddr2).        "', ".
    "psrcity     ='".addslashes($psrcity).         "', ".
    "psrstate    ='".addslashes($psrstate).        "', ".
    "psrzip      ='".addslashes($psrzip).          "', ".
    "psrcountry  ='".addslashes($psrcountry).      "', ".
    "psrnote     ='".addslashes($psrnote).         "', ".
    "psrdefphy   ='".addslashes($psrdefphy).       "', ".
    "psrphone    ='".fm_phone_assemble("psrphone")."', ".
    "psrfax      ='".fm_phone_assemble("psrfax").  "', ".
    "psremail    ='".addslashes($psremail).        "', ". 
    "psrein      ='".addslashes($psrein).          "'  ".
    "WHERE id='$id'";

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
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

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

} elseif ($action=="del") {

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

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Return_to $record_name $Menu</A>
    <P>
    <A HREF=\"main.php3?$_auth\"
     >$Return_to_the_Main_Menu</A>
    </CENTER>
  ";

} else {  // view is now the default action

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

}   

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
