<?php
  // file: user.php3
  // note: user module...
  // code: jeff b (jeff@univrel.pr.uconn.edu) -- template
  //       adam b (gdrago23@yahoo.com) -- near-complete rewrite
  // lic : GPL
  
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

switch($action) { // master action switch
 case "mod":
 case "add":
 case "modform":
 case "addform":
  $book = new notebook( 
    array ("action", "_auth", "id", "been_here"), true);
  
  if ($action=="modform") {
    if (empty($id)) {
      $action="addform";
    } else {
    freemed_display_box_top ( _("Modify")." $record_name",
      $page_name );
    }
  } // first modform if
  
  $book->set_submit_name(($action=="addform") ? _("Add") : _("Modify"));
  
  if (($action=="modform") AND (!$been_here)) { // catch the empty ID
    $been_here=1;
      // grab record number "id"
    $r = freemed_get_link_rec($id, $db_name);
    $username     = $r["username"    ];
    $userpassword = $r["userpassword"];
    $userdescrip  = $r["userdescrip" ];
    $userlevel    = $r["userlevel"   ];
    $usertype     = $r["usertype"    ]; // 19990909
    $userfac      = $r["userfac"     ];
    $userphy      = $r["userphy"     ];
    $userphygrp   = $r["userphygrp"  ];
    $userrealphy  = $r["userrealphy" ]; // 19990929

    $userpassword1 = $userpassword2 = $userpassword;
  } // second modform if
  
  if ($action=="addform") {
    freemed_display_box_top ( _("Add $record_name"), $page_name );
  } // addform if
  
  // now the body
  
  $phy_q = "SELECT * FROM physician WHERE phyref='no' ".
           "ORDER BY phylname,phyfname";
  $phy_r = fdb_query($phy_q);
  // fetch all in-house docs
  
  $book->add_page(
    _("User"),
    array ( 
      "username", "userpassword", "userpassword1", "userpassword2", 
      "userdescrip", "userlevel", "usertype", "userrealphy"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   
   
   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Username")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=username SIZE=17 MAXLENGTH=16
     VALUE=\"$username\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Password")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=PASSWORD NAME=userpassword1 SIZE=17 MAXLENGTH=16 
     VALUE=\"$userpassword1\">
   </TD></TR>
   
   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Password (Verify)")." :
     <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=PASSWORD NAME=userpassword2 SIZE=17 MAXLENGTH=16 
     VALUE=\"$userpassword2\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Description")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=userdescrip SIZE=20 MAXLENGTH=50
     VALUE=\"$userdescrip\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("User level")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <SELECT NAME=\"userlevel\">
      <OPTION VALUE=\"0\" ".(($userlevel==0) ? "SELECTED" : "")
        .">"._("Locked out")."
      <OPTION VALUE=\"1\" ".(($userlevel==1) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"2\" ".(($userlevel==2) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"3\" ".(($userlevel==3) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"4\" ".(($userlevel==4) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"5\" ".(($userlevel==5) ? "SELECTED" : "")
        .">"._("Delete privileges")."
      <OPTION VALUE=\"6\" ".(($userlevel==6) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"7\" ".(($userlevel==7) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"8\" ".(($userlevel==8) ? "SELECTED" : "")
        .">"._("Undefined")."
      <OPTION VALUE=\"9\" ".(($userlevel==9) ? "SELECTED" : "")
        .">"._("Superuser")."
    </SELECT>
   </TD></TR>
    
   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("User type")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <SELECT NAME=\"usertype\">
      <OPTION VALUE=\"phy\"  ".(($usertype=="phy") ? "SELECTED" : "").">
              "._("Physician")."
      <OPTION VALUE=\"misc\"  ".(($usertype=="misc") ? "SELECTED" : "").">
              "._("Miscellaneous")."
    </SELECT>
   </TD></TR>
    
   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Actual Physician").": <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox($phy_r, "#phylname#, #phyfname#", "userrealphy")."
   </TD></TR>
   
   </TABLE>
    "
  );

  $book->add_page(
    _("Authorize"),
    array (
      "userfac", "userphy", "userphygrp"
    ),
    "
  <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR><TD COLSPAN=2>
    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER>
      <$STDFONT_B><B>"._("Authorized facilities")." :
        </B><$STDFONT_E>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
      ".freemed_multiple_choice ("SELECT * FROM facility ORDER BY
        psrname", "psrname", "userfac", fm_join_from_array($userfac))."
    </TD></TR>
    </TABLE>
   </TD></TR>
   
   <TR><TD>
    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=0
     VALIGN=MIDDLE ALIGN=CENTER>
    <TR><TD ALIGN=CENTER>
      <$STDFONT_B><B>"._("Authorized physicians")."</B><$STDFONT_E>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
      ".freemed_multiple_choice ("SELECT * FROM physician ORDER BY phylname, 
        phyfname, phymname", "phylname:phyfname", "userphy", 
	fm_join_from_array($userphy))."
    </TD></TR>
    </TABLE>
   
   </TD><TD>

    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=0
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER>
    <$STDFONT_B><B>"._("Authorized physician groups")."</B><$STDFONT_E>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
      ".freemed_multiple_choice ("SELECT * FROM phygroup ORDER BY
        phygroupname", "phygroupname", "userphygrp", 
	fm_join_from_array($userphygrp))."
    </TD></TR>
    </TABLE>
   </TD></TR>

  </TABLE>
   "
  );

  if (!( $book->is_done() )) {
    echo "<CENTER>\n";
    $book->display();
    echo "
     <$STDFONT_B>
      <A HREF=\"$page_name?$_auth\">"._("Abandon ".
       (($action=="add" OR $action=="addform") ? "Addition" : "Modification") )
      ." </A>
     <$STDFONT_E>
    </CENTER>\n";
  } else { // now the add/mod code itself
  // assemble the arrays
   /* if (count ($userfac) > 0)
      $userfac_s    = join (":", $userfac);
     else $userfac_s = $userfac;
    if (count ($userphy) > 0)
      $userphy_s    = join (":", $userphy);
     else $userphy_s = $userphy;
    if (count ($userphygrp) > 0)
      $userphygrp_s = join (":", $userphygrp);
     else $userphygrp_s = $userphygrp;
   */
    if ($action=="mod" || $action=="modform") {
      echo "
        <P ALIGN=CENTER>
        <$STDFONT_B>"._("Modifying")." . . . 
      ";
        // build update query:
        // only set the values that need to be
        // changed... for example, don't set the
        // creation date in a modify. also,
        // remember the commas...
      $query = "UPDATE $db_name SET ".
        "username     = '".addslashes($username)."',      ".
        "userpassword = '".addslashes($userpassword1)."', ".
        "userdescrip  = '".addslashes($userdescrip)."',   ".
        "userlevel    = '".addslashes($userlevel)."',     ".
        "usertype     = '".addslashes($usertype)."',      ". // 19990909
        "userfac      = '".addslashes($userfac_s)."',     ".
        "userphy      = '".addslashes($userphy_s)."',     ".
        "userphygrp   = '".addslashes($userphygrp_s)."',  ". 
        "userrealphy  = '".addslashes($userrealphy)."'    ". // 19990929
        "WHERE id='".addslashes($id)."'";
    } else { // now the "add" guts
  
      echo "
        <P ALIGN=CENTER>
        <$STDFONT_B>"._("Adding")." . . . 
      ";
      $query = "INSERT INTO $db_name VALUES ( ".
        "'".addslashes($username)."',      ".
        "'".addslashes($userpassword1)."', ".
        "'".addslashes($userdescrip)."',   ".
        "'".addslashes($userlevel)."',     ".
        "'".addslashes($usertype)."',      ".
        "'".addslashes($userfac_s)."',     ".
        "'".addslashes($userphy_s)."',     ".
        "'".addslashes($userphygrp_s)."',  ".
        "'".addslashes($userrealphy)."',   ".
        " NULL ) ";
    } // 'add' guts

    if ($userpassword1 != $userpassword2) {
      echo "
        "._("Error")." !
	<B>("._("Passwords must match").")</B>
	<$STDFONT_E>
      ";
      freemed_display_box_bottom ();
      freemed_display_html_bottom ();
      DIE (""); // kill us! kill us!    ya were in Columbine weren't you?
    } // if the passwords _don't_ match...

    if ($id != 1)
      $result = fdb_query($query); // execute query
    else echo _("You cannot modify root!");

    if ($result) {
      echo "
        <B>"._("Done")."</B><$STDFONT_E>
      ";
    } else {
      echo "<B>"._("Error")." [$query]</B><$STDFONT_E>\n"; 
    } // end of error reporting clause
    echo "
        <P ALIGN=CENTER>
        <A HREF=\"$page_name?$_auth\"
         ><$STDFONT_B>"._("Go back to user menu")."<$STDFONT_E></A>
        <P>
    ";
  
  } // if 'done'

  freemed_display_box_bottom (); // show the bottom of the box
 break;

 case "del":
  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  if ($id != 1)
    $result = fdb_query("DELETE FROM $db_name
      WHERE (id = \"$id\")");
  else { // if we tried to delete root!!!
    echo "
      <B><CENTER>"._("You cannot delete root!")."</CENTER></B>
    ";
    freemed_display_box_bottom ();
    freemed_display_html_bottom ();
    DIE("");
  }

  echo "
    <P ALIGN=CENTER>
    $record_name "._("Deleted")."
    <BR>
    <BR>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("Go back to user menu")."</A>
  ";
  freemed_display_box_bottom ();
 break;

 default:
  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name "._("Maintenance"));

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    echo "
     <TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#777777\">
     <TR><TD ALIGN=CENTER>
      <$STDFONT_B SIZE=+1 COLOR=\"#ffffff\">"._("Users")."<$STDFONT_E>
     </TD></TR>

     <TR><TD>
    ";

    echo freemed_display_actionbar($page_name, $_ref);

    echo "
     </TD></TR>
     <TR><TD>
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR BGCOLOR=\"#000000\">
       <TD><$STDFONT_B COLOR=\"#dddddd\">"._("Username")."</TD><$STDFONT_E>
       <TD><$STDFONT_B COLOR=\"#dddddd\">"._("Action")."</TD><$STDFONT_E>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {
      $id       = $r["id"     ];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD><$STDFONT_B>".fm_prep($r[username])."<$STDFONT_E></TD>
        <TD>
      ";

        // don't allow add or delete on root...
      if ($id != 1) 
        echo "
         <A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>"._("MOD")."</FONT></A>
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>"._("DEL")."</FONT></A>
        "; // show actions...
      else echo "&nbsp; \n";

      echo "
        </TD></TR>
      ";

    } // while there are no more

    echo "
      </TABLE>
      ".freemed_display_actionbar ($page_name, $_ref)."
     </TD></TR>
    </TABLE>
    ";

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    freemed_display_box_bottom (); // display bottom of the box

  } else {
    echo "\n<B>$No $record_name $Found_with_that_criteria.</B>\n";
  }

} // end master action switch

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
