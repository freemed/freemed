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

switch($action) {
 case "add":
  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $degdate = $cur_date; // set to current date

  $query = "INSERT INTO degrees VALUES ( ".
    "'$degdegree',  ".
    "'$degname',    ".
    "'$degdate',    ".
    " NULL ) ";

  $result = fdb_query($query);

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
 break;

 case "display":
 case "modform":
 case "addform":
  if ($action=="addform")
    freemed_display_box_top("$Add $record_name", $page_name);
  else {
    freemed_display_box_top("$Modify $record_name", $page_name);
    $result = fdb_query("SELECT * FROM degrees ".
      "WHERE ( id = '$id' )");
    $r = fdb_fetch_array($result); // dump into array r[]
    $degdegree   = $r["degdegree"];
    $degname     = $r["degname"  ];
    $degdate     = $r["degdate"  ];
  } // modform fetching

  echo "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR><TD ALIGN=RIGHT>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".(($action=="modform") ? 
                                                   "mod" : "add")."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >
    <INPUT TYPE=HIDDEN NAME=\"_ref\" VALUE=\"$_ref\">

  ".(($action=="modform") ? "
    <$STDFONT_B>$Date_Last_Modified : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    $degdate
   </TD></TR>
   <TR><TD ALIGN=RIGHT>
  " : "")."
    <$STDFONT_B>$Degree_Title (<I>$Degree_Title_Example</I>) : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degname SIZE=30 MAXLENGTH=50
     VALUE=\"$degname\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Degree_Name (<I>$Degree_Name_Example</I>) : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degdegree SIZE=11 MAXLENGTH=10
     VALUE=\"$degdegree\">
   </TD></TR>

   <TR><TD COLSPAN=2 ALIGN=CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"".($action=="addform" ? "Add" : "Update")." \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
   </TD></TR>

   <TR><TD COLSPAN=2 ALIGN=CENTER>
     <$STDFONT_B><A HREF=\"$page_name?$_auth\">Abandon ".($action=="addform" ?
              "Addition" : "Modification")."</A><$STDFONT_E>
   </TD></TR>
    
   </FORM>
   </TABLE>
  ";
  freemed_display_box_bottom ();

 break;
 case "mod":

  freemed_display_box_top("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE degrees SET ".
    "degdegree ='$degdegree',  ".
    "degname   ='$degname',    ".
    "degdate   ='$cur_date'    ". 
    "WHERE id='$id'";

  $result = fdb_query($query);

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

 break;
 case "del":

  freemed_display_box_top ("$Deleting $record_name", $page_name);

  $result = fdb_query("DELETE FROM degrees
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted</I>.
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

 break;
 default:
  $query = "SELECT * FROM degrees ".
    "ORDER BY degdegree,degname";
  $result = fdb_query($query);
  freemed_display_box_top ("$record_name", $_ref);
  echo freemed_display_itemlist(
    $result,
    "degrees.php3", //$page_name,
    array (
      "Degree" => "degdegree",
      "Description" => "degname"
    ),
    array ( "", "" )
  );
  freemed_display_box_bottom ();
 break;
} // master action switch

freemed_close_db (); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
