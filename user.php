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

if (!freemed::acl('admin', 'user')) {
	//------HIPAA Logging
	$user_to_log=$_SESSION['authdata']['user'];
	if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"user.php|user access failed, user is not admin");}	
	trigger_error(__("You do not have access to do that."), E_USER_ERROR);
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"user.php|user $user_to_log manage users");}	

//----- Function for ACL widget
function my_acl_widget ( $varname ) {
	global ${$varname};
	$buffer .= "<select NAME=\"".$varname."[]\" SIZE=\"10\" MULTIPLE=\"multiple\">\n";

	// Grab from ACL
	$query = "SELECT id, section_value, value, order_value, name ".
		"FROM acl_aro ".
		"WHERE section_value='user'";
	$res = $GLOBALS['sql']->query($query);
	while ($r = $GLOBALS['sql']->fetch_array($res)) {
		$selected = false;
		if (is_array(${$varname})) {
			foreach (${$varname} AS $v) {
				if ($v == $r['value']) {
					$selected = true;
				}
			}
		}
		$buffer .= "<option value=\"".prepare($r['value'])."\" ".
			( $selected ? "SELECTED" : "" ).">".
			prepare($r['name']).
			"</option>\n";
	}
	
	$buffer .= "</select>\n";
	return $buffer;
} // end function my_acl_widget

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

	// Use userlevel to determine ACL AROs
	if (!(strpos($userlevel, ',') === false)) {
		// Explode out components if we found a comma
		$userlevel = explode (',', $userlevel);
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
      "userdescrip", "usertype", "userrealphy"
    ),
	html_form::form_table(array(

		__("Username") =>
		html_form::text_widget("username", 16),

		__("Password") =>
		"<input TYPE=\"PASSWORD\" NAME=\"userpassword1\" SIZE=\"33\" MAXLENGTH=\"32\" 
     VALUE=\"".prepare($userpassword1)."\"/>",
   
		__("Password (Verify)") =>
		"<input TYPE=\"PASSWORD\" NAME=\"userpassword2\" SIZE=\"33\" MAXLENGTH=\"32\" 
     VALUE=\"".prepare($userpassword2)."\"/>",

		__("Description") =>
		html_form::text_widget("userdescrip", 20, 50),

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

  $book->add_page (
	__("Access Control Lists"),
	array ( "userlevel" ),
	html_form::form_table(array(
		__("ACL") =>
		my_acl_widget ( 'userlevel' )
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
		$userlevel = join(',', $userlevel);
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
			"userlevel"    => $userlevel,
			"usertype"     => $usertype,
			"userfac"      => sql_squash(array_unique($userfac)),
			"userphy"      => sql_squash(array_unique($userphy)),
			"userphygrp"   => sql_squash(array_unique($userphygrp)),
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
	<div align=\"center\">
        ".__("Error")." !
	<b>(".__("Passwords must match").")</b>
	<p/>
	<a class=\"button\" href=\"user.php?action=modform&id=".urlencode($id)."\"
	>".__("Try Again")."</a>
	</div>
      ";
      template_display();
    } // if the passwords _don't_ match...

    if ($id != 1) {
      $result = $sql->query($query); // execute query
    } else { trigger_error(__("You cannot modify admin!"), E_USER_ERROR); }

    // Add breakpoints for user add and modify
    switch ($action) {
      case 'add': case 'addform':
      freemed::handler_breakpoint( 'UserAdd', array ( $sql->last_record($result) ) );
      break;

      case 'mod': case 'modform':
      freemed::handler_breakpoint( 'UserModify', array ( $_REQUEST['id'] ) );
      break;
    } // end breakpoint switch

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
    trigger_error(__("You cannot delete admin!"), E_USER_ERROR);
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
