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

freemed::connect ();

$this_user = CreateObject('FreeMED.User');

if (!freemed::user_flag(USER_ROOT)) {  // if not admin...
	$display_buffer .= "$page_name :: access denied\n";
	template_display();
//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"user.php|user access failed, user is not admin");}	
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"user.php|user $user_to_log manage users");}	

// *** main action loop ***
// (default action is "view")

switch($action) { // master action switch
 case "mod":
 case "add":
 case "modform":
 case "addform":
  // create new notebook
  $book = CreateObject('PHP.notebook', 
    array ("action", "id"),
	NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR
  );
  
  if ($action=="modform") {
    if (empty($id)) {
      $action="addform";
    } else {
     $page_title = __("Modify User");
    }
  } // first modform if

  if ($id==1) {
    $display_buffer .= __("You cannot modify admin!");
    template_display();
  }
  
  $book->set_submit_name(($action=="addform") ? __("Add") : __("Modify"));
  
  if ( ($action=="modform") AND (!$book->been_here()) ) { // catch the empty ID

	// grab record number "id"
	$r = freemed::get_link_rec ($_REQUEST['id'], $table_name);

	// Pull into the global scope
	foreach ($r AS $k => $v) {
		global ${$k};
		${$k} = stripslashes($v);
	}

	// expand the arrays
	$userphy    = sql_expand ( $userphy );
	$userfac    = sql_expand ( $userfac );
	$userphygrp = sql_expand ( $userphygrp );

	// make sure default & verify are the same, so no errors
	$userpassword1 = $userpassword2 = $userpassword;

	// Use userlevel to determine flags
	if ($userlevel > 0) {
		$power = 0; unset ($_userlevel);
		while (pow(2,$power) <= $userlevel) {
			// Check and add if so
			if (pow(2, $power) & $userlevel) {
				// Add it...
				$_userlevel[(pow(2,$power))] = pow(2,$power);
			}
			// Increment the current power...
			$power++;
		} // end looping...

		// Pass _userlevel to userlevel
		$userlevel = $_userlevel;
	} else {
		// Kludge for html_form::checkbox_widget to detect array
		$userlevel = array ( 0 );
	}

  } // second modform if
  
  if ($action=="addform") {
    $page_title =  __("Add User");
    if (!$book->been_here()) {
      global $userlevel; $userlevel = array( -1 );
    }
  } // addform if
  
  // now the body
  
  $phy_q = "SELECT * FROM physician WHERE phyref='no' ".
           "ORDER BY phylname,phyfname";
  $phy_r = $sql->query($phy_q);

  // fetch all in-house docs
  
  $book->add_page(
    __("User"),
    array ( 
      "username", "userpassword", "userpassword1", "userpassword2", 
      "userdescrip", "userlevel", "usertype", "userrealphy"
    ),
	html_form::form_table(array(

		__("Username") =>
		html_form::text_widget("username", 16),

		__("Password") =>
		"<INPUT TYPE=PASSWORD NAME=\"userpassword1\" SIZE=17 MAXLENGTH=16 
     VALUE=\"".prepare($userpassword1)."\">",
   
		__("Password (Verify)") =>
		"<INPUT TYPE=PASSWORD NAME=\"userpassword2\" SIZE=17 MAXLENGTH=16 
     VALUE=\"".prepare($userpassword2)."\">",

		__("Description") =>
		html_form::text_widget("userdescrip", 20, 50),

		__("User level") =>
		html_form::checkbox_widget(
			"userlevel",
			USER_ADMIN,
			"Administrator"
		).
		"<BR>\n".
		html_form::checkbox_widget(
			"userlevel",
			USER_DATABASE,
			"Database Access"
		).
		"<BR>\n".
		html_form::checkbox_widget(
			"userlevel",
			USER_DELETE,
			"Delete Permission"
		).
		"<BR>\n".
		html_form::checkbox_widget(
			"userlevel",
			USER_DISABLED,
			"Disabled/Locked Out"
		),
    
		__("User type") =>
		html_form::select_widget(
			"usertype",
			array(
				__("Physician") => "phy",
				__("Miscellaneous") => "misc"
			)
		),

		__("Actual Physician") =>
		freemed_display_selectbox($phy_r, "#phylname#, #phyfname#", "userrealphy")

	))
  );

  $book->add_page(
    __("Authorize"),
    array (
      "userfac", "userphy", "userphygrp"
    ),
    "
  <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR><TD COLSPAN=2>
    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER>
      <B>".__("Authorized facilities")." :
        </B>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
      ".freemed::multiple_choice (
	"SELECT CONCAT(psrname, ' (', psrcity, ', ', psrstate, ')') AS myfac FROM facility ORDER BY myfac", "myfac", "userfac", fm_join_from_array($userfac))."
    </TD></TR>
    </TABLE>
   </TD></TR>
   
   <TR><TD>
    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=0
     VALIGN=MIDDLE ALIGN=CENTER>
    <TR><TD ALIGN=CENTER>
      <B>".__("Authorized physicians")."</B>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
      ".freemed::multiple_choice ("SELECT * FROM physician ORDER BY phylname, 
        phyfname, phymname", "##phylname##, ##phyfname## ##phymname##", "userphy", 
		fm_join_from_array($userphy))."
    </TD></TR>
    </TABLE>
   
   </TD><TD>

    <TABLE BORDER=0 CELLSPACING=5 CELLPADDING=0
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER>
    <B>".__("Authorized physician groups")."</B>
    </TD></TR>
    <TR><TD ALIGN=CENTER>
      ".freemed::multiple_choice ("SELECT * FROM phygroup ORDER BY
        phygroupname", "phygroupname", "userphygrp", 
		fm_join_from_array($userphygrp))."
    </TD></TR>
    </TABLE>
   </TD></TR>

  </TABLE>
   "
  );

	// Handle "Cancel"
	if ($book->is_cancelled()) {
		Header("Location: ".$page_name);
		die("");
	}

  if (!( $book->is_done() )) {
    $display_buffer .= "<center>\n".$book->display()."</center>\n";
  } else { // now the add/mod code itself

	// Assemble flags
	$flags = 0;
	if (is_array($userlevel)) {
		foreach($userlevel AS $k => $v) {
			$flags |= $v;
		}
	}


	//Fred Trotter
	// in either case below we need the md5hash
	// of the password

	$md5_pass=md5($userpassword1);


    if ($action=="mod" || $action=="modform") {
	// Figure out whether we changed the password, or whether it is just
	// being re-passed
	if (strlen($userpassword1)==32) {
		// Length 32 = passed MD5 password, pass as is
		$_pass = $userpassword1;
	} else {
		// Otherwise use the hash
		$_pass = $md5_pass;
	}
    
      $display_buffer .= "
        <div ALIGN=\"CENTER\">
        ".__("Modifying")." . . . 
      ";
        // build update query:
        // only set the values that need to be
        // changed... for example, don't set the
        // creation date in a modify. also,
        // remember the commas...
	$query = $sql->update_query($table_name,
		array (
			"username"     => $username,
			"userpassword" => $_pass,
			"userdescrip"  => $userdescrip,
			"userlevel"    => ($flags+0),
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
        <div ALIGN=\"CENTER\">
        ".__("Adding")." . . . 
      ";
	$query = $sql->insert_query ( $table_name,
		array (
			"username"     => $username,
			"userpassword" => $md5_pass,
			"userdescrip"  => $userdescrip,
			"userlevel"    => ($flags+0),
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
        ".__("Error")." !
	<B>(".__("Passwords must match").")</B>
      ";
      template_display();
    } // if the passwords _don't_ match...

    if ($id != 1)
      $result = $sql->query($query); // execute query
    else $display_buffer .= __("You cannot modify admin!");

    if ($result) {
      $display_buffer .= " <B>".__("Done")."</B> ";
      $refresh_location = "user.php";
    } else {
      $display_buffer .= "<B>".__("Error")." [$query]</B>\n"; 
    } // end of error reporting clause
    $display_buffer .= "
    	</div>
	<p/>
        <div ALIGN=\"CENTER\">
        <A HREF=\"$page_name\"
         >".__("Go back to user menu")."</A>
        </div>
    ";
	// Set automatic refresh
	$refresh = $page_name;
  
  } // if 'done'

 break;

 case "del":
	$page_title = __("Deleting User");

    // select only "id" record, and delete
  if ($id != 1)
    $result = $sql->query("DELETE FROM $table_name ".
    	"WHERE id='".addslashes($id)."'");
  else { // if we tried to delete admin!!!
    $display_buffer .= "
      <b><center>".__("You cannot delete admin!")."</center></b>
    ";
    template_display();
  }

  $display_buffer .= "
    <P ALIGN=CENTER>
    $record_name ".__("Deleted")."
    <br/>
    <br/>
    <a HREF=\"$page_name?action=view\"
     >".__("Go back to user menu")."</a>
  ";

	// Set automatic refresh
	$refresh = $page_name."?action=view";
  
 break;

 default:
  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

	// TODO: MIGRATE THIS TO freemed_display_itemlist FUNCTION
	//       OR MAKE IT A MODULE, INHEIRITING FROM THE MAINTENANCE
	//       MODULE

  $query = "SELECT * FROM ".addslashes($table_name)." ".
	"ORDER BY ".addslashes($order_field);

  $result = $sql->query($query);
  if ($result) {
    $page_title = __("Users");

    $display_buffer .= "
     <TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
      ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#777777\">
     <TR><TD ALIGN=CENTER>
      <FONT SIZE=\"+1\" COLOR=\"#ffffff\">".__("Users")."</FONT>
     </TD></TR>

     <TR><TD>
    ";

    $display_buffer .= freemed_display_actionbar($page_name, "admin.php");

    $display_buffer .= "
     </TD></TR>
     <TR><TD>
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR CLASS=\"reverse\">
       <TD>".__("Username")."</TD>
       <TD>".__("Action")."</TD>
      </TR>
    "; // header of box

    while ($r = $sql->fetch_array($result)) {
      $display_buffer .= "
        <TR CLASS=\"".freemed_alternate()."\">
        <TD>".prepare($r['username'])."</TD>
        <TD>
      ";

        // don't allow add or delete on admin...
      if ($r[id] != 1) 
        $display_buffer .= "
         <a class=\"button\" HREF=\"$page_name?id=".$r['id']."&action=modform\"
         ><small>".__("MOD")."</small></a>
         <a class=\"button\" HREF=\"$page_name?id=".$r['id']."&action=del\"
         ><small>".__("DEL")."</small></a>
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
    $display_buffer .= "\n<B>".__("No record found with that criteria.")."</B>\n";
  }

} // end master action switch

template_display();

?>
