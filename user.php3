<?php
  # file: user.php3
  # note: user module...
  # code: again, so lazy....
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL
  # 
  # please note that you _can_ remove the comments down below,
  # but everything above here should remain untouched. please
  # do _not_ remove my name or address from this file, since I
  # have worked very hard on it. the license must also always
  # remain GPL.                                     -- jeff b

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name  ="user.php3";           // for help info, later
  $db_name    ="user";                // get this from jeff
  $record_name="User";                // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="id";                  // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** setting _ref cookie ***
    // if you are going to be "chaining" out from this
    // function and want users to be able to return to
    // it, uncomment this and it will set the cookie to
    // return people using the bar.
  //SetCookie("_ref", $page_name, time()+$_cookie_expire);

    // *** authorizing user ***

  freemed_open_db ($LoginCookie); // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

$this_user = new User ($LoginCookie);

if ($this_user->user_number != 1)  // if not root...
  DIE ("$page_name :: access denied");

// *** main action loop ***
// (default action is "view")

if ($action=="addform") {
  freemed_display_box_top ("$Add $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$User_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=username SIZE=17 MAXLENGTH=16
     VALUE=\"$username\">
    <BR>

    <$STDFONT_B>$Password : <$STDFONT_E>
    <INPUT TYPE=PASSWORD NAME=userpassword1 SIZE=17 MAXLENGTH=16 
     VALUE=\"$userpassword\">
    <BR>
    <$STDFONT_B>$Password ($Verify) : <$STDFONT_E>
    <INPUT TYPE=PASSWORD NAME=userpassword2 SIZE=17 MAXLENGTH=16 
     VALUE=\"$userpassword\">
    <BR>

    <$STDFONT_B>$Description : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=userdescrip SIZE=20 MAXLENGTH=50
     VALUE=\"$userdescrip\">
    <P>

    <TABLE WIDTH=100% BORDER=0 CELLSPACING=5 CELLPADDING=0
     VALIGN=CENTER ALIGN=CENTER><TR>
    <TD ALIGN=CENTER WIDTH=50%><CENTER>

    <$STDFONT_B><B>$Authorized_facilities : </B><$STDFONT_E>
    </CENTER></TD><TD WIDTH=50%>&nbsp;</TD></TR>
    <TR><TD ALIGN=CENTER><CENTER>
  ";
  freemed_multiple_choice ("SELECT * FROM $database.facility ORDER BY
    psrname", "psrname", "userfac", $userfac);
  echo "
    </CENTER></TD><TD>&nbsp;</TD></TR>

    <TR>
    <TD WIDTH=50% ALIGN=CENTER><CENTER>
    <$STDFONT_B><B>$Authorized_physicians</B><$STDFONT_E>
    </CENTER></TD>

    <TD WIDTH=50% ALIGN=CENTER><CENTER>
    <$STDFONT_B><B>$Authorized_phy_groups</B><$STDFONT_E>
    </CENTER></TD></TR><TR><TD ALIGN=CENTER><CENTER>
  ";

  freemed_multiple_choice ("SELECT * FROM $database.physician ORDER BY
    phylname, phyfname, phymname", "phylname:phyfname", "userphy", $userphy);

  echo "
    </CENTER></TD><TD ALIGN=CENTER><CENTER>
  ";

  freemed_multiple_choice ("SELECT * FROM $database.phygroup ORDER BY
    phygroupname", "phygroupname", "userphygrp", $userphygrp);

  echo "
    </CENTER></TD></TR></TABLE>
    <P>

    <$STDFONT_B>$User_level : <$STDFONT_E>
    <SELECT NAME=\"userlevel\">
      <OPTION VALUE=\"0\" $_ul_0>$Locked_out
      <OPTION VALUE=\"1\" $_ul_1>$Undefined
      <OPTION VALUE=\"2\" $_ul_2>$Undefined
      <OPTION VALUE=\"3\" $_ul_3>$Undefined
      <OPTION VALUE=\"4\" $_ul_4>$Undefined
      <OPTION VALUE=\"5\" $_ul_5>$Delete_privs
      <OPTION VALUE=\"6\" $_ul_6>$Undefined
      <OPTION VALUE=\"7\" $_ul_7>$Undefined
      <OPTION VALUE=\"8\" $_ul_8>$Undefined
      <OPTION VALUE=\"9\" $_ul_9>$Superuser
    </SELECT>

    <P>
    <$STDFONT_B>$User_type : <$STDFONT_E>
    <SELECT NAME=\"usertype\">
      <OPTION VALUE=\"phy\"  $_ut_phy>$Physician
      <OPTION VALUE=\"misc\" $_ut_misc>$Miscellaneous
    </SELECT>
    <P>

    <$STDFONT_B>$Actual_physician : <$STDFONT_E>
    <SELECT NAME=\"userrealphy\">
  ";
  freemed_display_physicians ($userrealphy, "no");
  echo "
    </SELECT>
    <P> 

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <BR><BR>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <BR><BR>
    <$STDFONT_B>$Adding . . .  
  ";

  if (count ($userfac) > 0)
    $userfac_s    = join (":", $userfac);
   else $userfac_s = $userfac;
  if (count ($userphy) > 0)
    $userphy_s    = join (":", $userphy);
   else $userphy_s = $userphy;
  if (count ($userphygrp) > 0)
    $userphygrp_s = join (":", $userphygrp);
   else $userphygrp_s = $userphygrp;

  if ($userpassword1==$userpassword2) 
    $query = "INSERT INTO $database.$db_name VALUES ( ".
      "'$username',      ".
      "'$userpassword1', ".
      "'$userdescrip',   ".
      "'$userlevel',     ".
      "'$usertype',      ".
      "'$userfac_s',     ".
      "'$userphy_s',     ".
      "'$userphygrp_s',  ".
      "'$userrealphy',   ".
      " NULL ) ";
  else {
    echo "
      <P>
      <CENTER>
      <$STDFONT_B><B>$Passwords_must_match</B><$STDFONT_E>
      </CENTER>
      <P>
      <CENTER>
      <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"username\"    VALUE=\"$username\">
       <INPUT TYPE=HIDDEN NAME=\"userdescrip\" VALUE=\"$userdescrip\">
       <INPUT TYPE=HIDDEN NAME=\"userphygrp\"  VALUE=\"$userphygrp\">
       <INPUT TYPE=HIDDEN NAME=\"userlevel\"   VALUE=\"$userlevel\">
       <INPUT TYPE=HIDDEN NAME=\"usertype\"    VALUE=\"$usertype\">
       <INPUT TYPE=HIDDEN NAME=\"userrealphy\" VALUE=\"$userrealphy\">
       <INPUT TYPE=SUBMIT VALUE=\" $Try_Again \">
      </FORM>
      </CENTER>
      <P>
    ";
    freemed_display_box_bottom ();
    freemed_display_html_bottom ();
    DIE ("");
  }

    // query the db with new values
  $result = fdb_query($query);

  if ($debug==1) {
    echo "\n<BR><BR><B>$Query_result :</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>$Query_string :</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>$Actual_query_result :</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done .</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  }

  echo "
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>$Go_back_to_user_menu<$STDFONT_E></A>
    </CENTER>
  ";
  freemed_display_box_bottom (); // display the bottom of the box
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>$Please_use_the_modify_form $record_name!</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_main_menu</A>
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
  if ($debug==1) {
    echo " <B>$Result</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

    // this dumps the result of the query (the record to
    // be modified) into the variables with those names,
    // for easy use by us.
  $username     = $r["username"    ];
  $userpassword = $r["userpassword"];
  $userdescrip  = $r["userdescrip" ];
  $userlevel    = $r["userlevel"   ];
  $usertype     = $r["usertype"    ]; // 19990909
  $userfac      = $r["userfac"     ];
  $userphy      = $r["userphy"     ];
  $userphygrp   = $r["userphygrp"  ];
  $userrealphy  = $r["userrealphy" ]; // 19990929

  switch ($userlevel) {
    case 9:          $_ul_9 = "SELECTED"; break;
    case 8:          $_ul_8 = "SELECTED"; break;
    case 7:          $_ul_7 = "SELECTED"; break;
    case 6:          $_ul_6 = "SELECTED"; break;
    case 5:          $_ul_5 = "SELECTED"; break;
    case 4:          $_ul_4 = "SELECTED"; break;
    case 3:          $_ul_3 = "SELECTED"; break;
    case 2:          $_ul_2 = "SELECTED"; break;
    case 1:          $_ul_1 = "SELECTED"; break;
    case 0: default: $_ul_0 = "SELECTED"; break;
  } // end of userlevel switch

  switch ($usertype) {
    case "phy" :           $_ut_phy  = "SELECTED"; break;
    case "misc": default:  $_ut_misc = "SELECTED"; break;
  } // end of usertype switch

  //freemed_display_box_top ("$Modify $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$User_name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=username SIZE=17 MAXLENGTH=16
     VALUE=\"$username\">
    <BR>

    <$STDFONT_B>$Password : <$STDFONT_E>
    <INPUT TYPE=PASSWORD NAME=userpassword1 SIZE=17 MAXLENGTH=16 
     VALUE=\"$userpassword\">
    <BR>
    <$STDFONT_B>$Password ($Verify) : <$STDFONT_E>
    <INPUT TYPE=PASSWORD NAME=userpassword2 SIZE=17 MAXLENGTH=16 
     VALUE=\"$userpassword\">
    <BR>

    <$STDFONT_B>$Description<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=userdescrip SIZE=20 MAXLENGTH=50
     VALUE=\"$userdescrip\">
    <BR>
    <TABLE WIDTH=100% BORDER=0 CELLSPACING=5 CELLPADDING=0
     VALIGN=CENTER ALIGN=CENTER><TR>
    <TD ALIGN=CENTER WIDTH=50%><CENTER>

    <$STDFONT_B><B>$Authorized_facilities : </B><$STDFONT_E>
    </CENTER></TD><TD WIDTH=50%>&nbsp;</TD></TR>
    <TR><TD ALIGN=CENTER><CENTER>
  ";
  freemed_multiple_choice ("SELECT * FROM $database.facility ORDER BY
    psrname", "psrname", "userfac", $userfac);
  echo "
    </CENTER></TD><TD>&nbsp;</TD></TR>

    <TR>
    <TD WIDTH=50% ALIGN=CENTER><CENTER>
    <$STDFONT_B><B>$Authorized_physicians</B><$STDFONT_E>
    </CENTER></TD>

    <TD WIDTH=50% ALIGN=CENTER><CENTER>
    <$STDFONT_B><B>$Authorized_phy_groups</B><$STDFONT_E>
    </CENTER></TD></TR><TR><TD ALIGN=CENTER><CENTER>
  ";

  freemed_multiple_choice ("SELECT * FROM $database.physician ORDER BY
    phylname, phyfname, phymname", "phylname:phyfname", "userphy", $userphy);

  echo "
    </CENTER></TD><TD ALIGN=CENTER><CENTER>
  ";

  freemed_multiple_choice ("SELECT * FROM $database.phygroup ORDER BY
    phygroupname", "phygroupname", "userphygrp", $userphygrp);

  echo "
    </CENTER></TD></TR></TABLE>
    <P>

    <$STDFONT_B>$User_level : <$STDFONT_E>
    <SELECT NAME=\"userlevel\">
      <OPTION VALUE=\"0\" $_ul_0>!!! Locked Out !!!
      <OPTION VALUE=\"1\" $_ul_1>UNDEFINED
      <OPTION VALUE=\"2\" $_ul_2>UNDEFINED
      <OPTION VALUE=\"3\" $_ul_3>UNDEFINED
      <OPTION VALUE=\"4\" $_ul_4>UNDEFINED
      <OPTION VALUE=\"5\" $_ul_5>Delete Privs
      <OPTION VALUE=\"6\" $_ul_6>UNDEFINED
      <OPTION VALUE=\"7\" $_ul_7>UNDEFINED
      <OPTION VALUE=\"8\" $_ul_8>UNDEFINED
      <OPTION VALUE=\"9\" $_ul_9>SuperUser/Admin
    </SELECT>

    <P>
    <$STDFONT_B>$User_type<$STDFONT_E>
    <SELECT NAME=\"usertype\">
     <OPTION VALUE=\"phy\"  $_ut_phy>$Physician
     <OPTION VALUE=\"misc\" $_ut_misc>$Miscellaneous
    </SELECT>
    <P>

    <$STDFONT_B>$Actual_physician : <$STDFONT_E>
    <SELECT NAME=\"userrealphy\">
  ";
  freemed_display_physicians ($userrealphy, "no");
  echo "
    </SELECT>
    <P>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_addition</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  if (count ($userfac) > 0)
    $userfac_s    = join (":", $userfac);
   else $userfac_s = $userfac;
  if (count ($userphy) > 0)
    $userphy_s    = join (":", $userphy);
   else $userphy_s = $userphy;
  if (count ($userphygrp) > 0)
    $userphygrp_s = join (":", $userphygrp);
   else $userphygrp_s = $userphygrp;

    // build update query:
    // only set the values that need to be
    // changed... for example, don't set the
    // creation date in a modify. also,
    // remember the commas...
  $query = "UPDATE $database.$db_name SET ".
    "username     = '$username',      ".
    "userpassword = '$userpassword1', ".
    "userdescrip  = '$userdescrip',   ".
    "userlevel    = '$userlevel',     ".
    "usertype     = '$usertype',      ". // 19990909
    "userfac      = '$userfac_s',     ".
    "userphy      = '$userphy_s',     ".
    "userphygrp   = '$userphygrp_s',  ". 
    "userrealphy  = '$userrealphy'    ". // 19990929
    "WHERE id='$id'";

  if ($userpassword1 != $userpassword2) {
    echo "
      $Error !<$STDFONT_E>
      <P>
      <B><CENTER>$Passwords_must_match</CENTER></B>
    ";
    freemed_display_box_bottom ();
    freemed_display_html_bottom ();
    DIE (""); // kill us! kill us!    ya were in Columbine weren't you?
  } // if the passwords _don't_ match...

  if ($id != 1)
    $result = fdb_query($query); // execute query
  else echo "You_cannot_modify_root !";

  if ($debug==1) {
    echo "\n<BR><BR><B>$Query_result :</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>$Query_string :</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>$Actual_query_result :</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done .</B><$STDFONT_E>
      <P>

      <CENTER><A HREF=\"$page_name?$_auth\"
       ><$STDFONT_B>$Go_back_to_user_menu<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
  } else {
    echo ("<B>$Error ($result)</B>\n"); 
  } // end of error reporting clause

  freemed_display_box_bottom (); // display box bottom 
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  if ($id != 1)
    $result = fdb_query("DELETE FROM $database.$db_name
      WHERE (id = \"$id\")");
  else { // if we tried to delete root!!!
    echo "
      <B><CENTER>$You_cannot_delete_root !</CENTER></B>
    ";
    freemed_display_box_bottom ();
    freemed_display_bottom_links ($record_name, $page_name, $_ref);
    freemed_display_html_bottom ();
    DIE("");
  }

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
    <BR>
  ";
  if ($debug==1) {
    echo "
      <BR><B>$Result :</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update / $Delete $Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $database.$db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name $Maintenance", $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // and comment this line:
    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$User_name</B></TD>
       <TD><B>&nbsp;</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $username = $r["username"];
      // $value_b = $r["value_b"];
      $id      = $r["id"     ];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$username</TD>
        <TD><I><!-- nothing yet here -->&nbsp;</I></TD>
        <TD>
      ";

        // don't allow add or delete on root...
      if ($id != 1) 
        echo "
         <A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show actions...
      else echo "&nbsp; \n";

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

    freemed_display_actionbar ($page_name, $_ref);

    freemed_display_box_bottom (); // display bottom of the box
    freemed_display_bottom_links ($record_name, $page_name, $_ref);

  } else {
    echo "\n<B>$No $record_name $Found_with_that_criteria.</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
