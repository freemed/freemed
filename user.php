<?php
  // $Id$
  // note: user module...
  // code: adam b (gdrago23@yahoo.com) -- near-complete rewrite
  // lic : GPL
  
$page_name   = "user.php";
$table_name  = "user";
$record_name = "User";
$order_field = "id";

include_once("lib/freemed.php");         // load global variables

    // *** authorizing user ***

freemed_open_db ();

$this_user = new User ();

if ($this_user->user_number != 1) {  // if not root...
	$display_buffer .= "$page_name :: access denied\n";
	template_display();
}

// *** main action loop ***
// (default action is "view")

switch($action) { // master action switch
 case "mod":
 case "add":
 case "modform":
 case "addform":
  // create new notebook
  $book = new notebook(
    array ("action", "id"),
	NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR
  );
  
  if ($action=="modform") {
    if (empty($id)) {
      $action="addform";
    } else {
     $page_title = _("Modify")." "._($record_name);
    }
  } // first modform if

  if ($id==1) {
    $display_buffer .= _("You cannot modify root!");
    template_display();
  }
  
  $book->set_submit_name(($action=="addform") ? _("Add") : _("Modify"));
  
  if ( ($action=="modform") AND (!$book->been_here()) ) { // catch the empty ID

      // grab record number "id"
    $r = freemed_get_link_rec ($id, $table_name);
    extract ($r);

	// expand the arrays
	$userphy    = sql_expand ( $userphy );
	$userfac    = sql_expand ( $userfac );
	$userphygrp = sql_expand ( $userphygrp );

	// make sure default & verify are the same, so no errors
    $userpassword1 = $userpassword2 = $userpassword;

  } // second modform if
  
  if ($action=="addform") {
    $page_title =  _("Add")." "._($record_name);
  } // addform if
  
  // now the body
  
  $phy_q = "SELECT * FROM physician WHERE phyref='no' ".
           "ORDER BY phylname,phyfname";
  $phy_r = $sql->query($phy_q);

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
    "._("Username")." :
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"username\" SIZE=17 MAXLENGTH=16
     VALUE=\"".prepare($username)."\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    "._("Password")." : 
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=PASSWORD NAME=\"userpassword1\" SIZE=17 MAXLENGTH=16 
     VALUE=\"".prepare($userpassword1)."\">
   </TD></TR>
   
   <TR><TD ALIGN=RIGHT>
    "._("Password (Verify)")." :
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=PASSWORD NAME=\"userpassword2\" SIZE=17 MAXLENGTH=16 
     VALUE=\"".prepare($userpassword2)."\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    "._("Description")." : 
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"userdescrip\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($userdescrip)."\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    "._("User level")." : 
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
    "._("User type")." :
   </TD><TD ALIGN=LEFT>
    <SELECT NAME=\"usertype\">
      <OPTION VALUE=\"phy\"  ".(($usertype=="phy") ? "SELECTED" : "").">
              "._("Physician")."
      <OPTION VALUE=\"misc\"  ".(($usertype=="misc") ? "SELECTED" : "").">
              "._("Miscellaneous")."
    </SELECT>
   </TD></TR>
    
   <TR><TD ALIGN=RIGHT>
    "._("Actual Physician").":
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
      <B>"._("Authorized facilities")." :
        </B>
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
      <B>"._("Authorized physicians")."</B>
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
    <B>"._("Authorized physician groups")."</B>
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
    $display_buffer .= "<CENTER>\n".$book->display();
    $display_buffer .= "
      <A HREF=\"$page_name\">"._("Abandon ".
       (($action=="add" OR $action=="addform") ? "Addition" : "Modification") )
      ." </A>
    </CENTER>\n";
  } else { // now the add/mod code itself
    if ($action=="mod" || $action=="modform") {
      $display_buffer .= "
        <P ALIGN=CENTER>
        "._("Modifying")." . . . 
      ";
        // build update query:
        // only set the values that need to be
        // changed... for example, don't set the
        // creation date in a modify. also,
        // remember the commas...
	$query = $sql->update_query($table_name,
		array (
			"username"     => $username,
			"userpassword" => $userpassword1,
			"userdescrip"  => $userdescrip,
			"userlevel"    => $userlevel,
			"usertype"     => $usertype,
			"userfac"      => sql_squash($userfac),
			"userphy"      => sql_squash($userphy),
			"userphygrp"   => sql_squash($userphygrp),
			"userrealphy"  => $userrealphy
		),
		array ( "id" => $id )
	);

    } else { // now the "add" guts
  
      $display_buffer .= "
        <P ALIGN=CENTER>
        "._("Adding")." . . . 
      ";
	$query = $sql->insert_query ( "$table_name",
		array (
			"username"     => $username,
			"userpassword" => $userpassword1,
			"userdescrip"  => $userdescrip,
			"userlevel"    => $userlevel,
			"usertype"     => $usertype,
			"userfac"      => sql_squash($userfac),
			"userphy"      => sql_squash($userphy),
			"userphygrp"   => sql_squash($userphygrp),
			"userrealphy"  => $userrealphy
		)
	);
    } // 'add' guts

    if ($userpassword1 != $userpassword2) {
      $display_buffer .= "
        "._("Error")." !
	<B>("._("Passwords must match").")</B>
      ";
      template_display();
    } // if the passwords _don't_ match...

    if ($id != 1)
      $result = $sql->query($query); // execute query
    else $display_buffer .= _("You cannot modify root!");

    if ($result) {
      $display_buffer .= "
        <B>"._("Done")."</B>
      ";
    } else {
      $display_buffer .= "<B>"._("Error")." [$query]</B>\n"; 
    } // end of error reporting clause
    $display_buffer .= "
        <P ALIGN=CENTER>
        <A HREF=\"$page_name\"
         >"._("Go back to user menu")."</A>
        <P>
    ";
  
  } // if 'done'

 break;

 case "del":
	$page_title = _("Deleting")." "._($record_name);

    // select only "id" record, and delete
  if ($id != 1)
    $result = $sql->query("DELETE FROM $table_name ".
    	"WHERE id='".addslashes($id)."'");
  else { // if we tried to delete root!!!
    $display_buffer .= "
      <B><CENTER>"._("You cannot delete root!")."</CENTER></B>
    ";
    template_display();
  }

  $display_buffer .= "
    <P ALIGN=CENTER>
    $record_name "._("Deleted")."
    <BR>
    <BR>
    <A HREF=\"$page_name?action=view\"
     >"._("Go back to user menu")."</A>
  ";
 break;

 default:
  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

	// TODO: MIGRATE THIS TO freemed_display_itemlist FUNCTION
	//       OR MAKE IT A MODULE, INHEIRITING FROM THE MAINTENANCE
	//       MODULE

  $query = "SELECT * FROM $table_name ORDER BY $order_field";

  $result = $sql->query($query);
  if ($result) {
    $page_title = _($record_name);

    $display_buffer .= "
     <TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#777777\">
     <TR><TD ALIGN=CENTER>
      <FONT SIZE=\"+1\" COLOR=\"#ffffff\">"._("Users")."</FONT>
     </TD></TR>

     <TR><TD>
    ";

    $display_buffer .= freemed_display_actionbar($page_name, "admin.php");

    $display_buffer .= "
     </TD></TR>
     <TR><TD>
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR BGCOLOR=\"#000000\">
       <TD><FONT COLOR=\"#dddddd\">"._("Username")."</TD></FONT>
       <TD><FONT COLOR=\"#dddddd\">"._("Action")."</TD></FONT>
      </TR>
    "; // header of box

    while ($r = $sql->fetch_array($result)) {
      $display_buffer .= "
        <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
        <TD>".prepare($r[username])."</TD>
        <TD>
      ";

        // don't allow add or delete on root...
      if ($r[id] != 1) 
        $display_buffer .= "
         <A HREF=
         \"$page_name?id=$r[id]&action=modform\"
         ><FONT SIZE=-1>"._("MOD")."</FONT></A>
          &nbsp;
          <A HREF=\"$page_name?id=$r[id]&action=del\"
          ><FONT SIZE=-1>"._("DEL")."</FONT></A>
        "; // show actions...
      else $display_buffer .= "&nbsp; \n";

      $display_buffer .= "
        </TD></TR>
      ";

    } // while there are no more

    $display_buffer .= "
      </TABLE>
      ".freemed_display_actionbar ($page_name, "admin.php")."
     </TD></TR>
    </TABLE>
    ";

    if (strlen($_ref)<5) {
      $_ref="main.php";
    } // if no ref, then return to home page...

  } else {
    $display_buffer .= "\n<B>"._("No record found with that criteria.")."</B>\n";
  }

} // end master action switch

template_display();

?>
