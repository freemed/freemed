<?php
  # file: degrees.php3
  # note: physician degrees database functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name="degrees.php3";        // for help info, later
  $record_name="Physician Degree";  // actual name
  include "global.var.inc";         // global variables
  include "freemed-functions.inc";  // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top();
  freemed_display_banner();

if ($action=="addform") {

  freemed_display_box_top("$Add $record_name", $page_name); 

  if ($debug) {
    echo "
      date = ($cur_date)<BR>
    ";
  }
  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"_ref\" VALUE=\"$_ref\">

    <$STDFONT_B>$Degree_Title ($Degree_Title_Example) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=degname SIZE=20 MAXLENGTH=50
     VALUE=\"$degname\">
    <BR>

    <$STDFONT_B>$Degree_Name ($Degree_Name_Example) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=degdegree SIZE=11 MAXLENGTH=10
     VALUE=\"$degdegree\">
    <BR>
 
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom();

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $degdate = $cur_date; // set to current date

  $query = "INSERT INTO $database.degrees VALUES ( ".
    "'$degdegree',  ".
    "'$degname',    ".
    "'$degdate',    ".
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

  freemed_display_box_bottom();

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view&_ref=$_ref\"
      >$Return_to $record_name $Menu</A>
     <P>
     <A HREF=\"main.php3?$_auth\"
      >$Return_to_the_Main_Menu</A>
    </CENTER>
  ";

} elseif ($action=="modform") {

  freemed_display_box_top("$Modify $record_name", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
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

  $result = fdb_query("SELECT * FROM $database.degrees ".
    "WHERE ( id = '$id' )");

  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

  $degdegree   = $r["degdegree"];
  $degname     = $r["degname"  ];
  $degdate     = $r["degdate"  ];

  echo "
    <BR><BR>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >
    <INPUT TYPE=HIDDEN NAME=\"_ref\" VALUE=\"$_ref\">

    <$STDFONT_B>$Date_Last_Modified : <$STDFONT_E>
    $degdate
    <BR><BR>

    <$STDFONT_B>$Degree_Title (<I>$Degree_Title_Example<I>) : <$STDFONT_E>
    <BR>
    <INPUT TYPE=TEXT NAME=degname SIZE=30 MAXLENGTH=50
     VALUE=\"$degname\">
    <BR><BR>

    <$STDFONT_B>$Degree_Name (<I>$Degree_Name_Example</I>) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=degdegree SIZE=11 MAXLENGTH=10
     VALUE=\"$degdegree\">
    <BR>

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&_ref=$_ref&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

  freemed_display_box_top("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $degdate = $cur_date; // date stamp modified...

  $query = "UPDATE $database.degrees SET ".
    "degdegree ='$degdegree',  ".
    "degname   ='$degname',    ".
    "degdate   ='$degdate'     ". 
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

  freemed_display_box_bottom ();
  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&_ref=$_ref&action=view\"
    >$Return_to $record_name $Menu</A>
    </CENTER>
  ";

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

  $result = fdb_query("DELETE FROM $database.degrees
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
  ";
  if ($debug==1) {
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

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $database.degrees ".
    "ORDER BY degdegree,degname";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name", $_ref);

    freemed_display_actionbar ($page_name, $_ref); // show action bar at top

    echo "

      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Degree</B></TD>
       <TD><B>$Description</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

        // possibility of allowing selection from master
        // package options database whether to use/display
        // ICD9 or ICD10 codes...?
    
      $degname    = $r["degname"];
      $degdegree  = $r["degdegree"];
      $id         = $r["id"];

      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$degdegree</TD>
        <TD><I>$degname</I></TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform&_ref=$_ref\"
         ><FONT SIZE=-1>$MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($user)>$delete_level) {
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del&_ref=$_ref\"
          ><FONT SIZE=-1>$DEL$id_mod</FONT></A>
        "; // show delete
      }
      echo "
        </TD></TR>
      ";
    } // while there are no more
    echo "
      </TABLE>
    "; // end of table

    freemed_display_actionbar ($page_name, $_ref); // bottom bar

    freemed_display_box_bottom ();

  } else {
    freemed_display_box_top ("$record_name", $_ref);
    echo "
      <P>
      <CENTER>
      <B>$No_degrees_are_currently_in_the_database</B>
      </CENTER>
    ";
    freemed_display_box_bottom ();
  }

} 

freemed_close_db (); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
