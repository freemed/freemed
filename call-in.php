<?php
	// $Id$
	// desc: module for call-in patients

$page_name = "call-in.php";          // page name
include ("lib/freemed.php");           // global variables
$record_name = __("Call In");          // name of record
$db_name = "callin";                  // database name

freemed::connect ();
$this_user = CreateObject('FreeMED.User');

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"call-in.php|user $user_to_log views callin");}	

if ($_REQUEST['submit'] == __("Cancel")) {
	if ($_REQUEST['action'] == 'add') {
		Header('Location: call-in.php');
		$refresh = 'call-in.php';
	} else {
		Header('Location: main.php');
		$refresh = "main.php";
	}
	template_display();
}

switch ($action) {

 case "addform":
  // Set page title
  $page_title = __("Add")." ".$record_name;

  // Push onto stack
  page_push();

  // Check for default physician
  $ciphysician = ($this_user->isPhysician() ? $this_user->getPhysician() :
    0 );

  // ... continue ...
  if (strlen($citookcall)<1) {
    $citookcall = $this_user->getDescription();
  } // if there wasn't one passed to us...
  $display_buffer .= "
    <p/>
    <form ACTION=\"$page_name\" METHOD=\"POST\">
     <input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"add\"/>

    <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\"
     VALIGN=\"MIDDLE\" ALIGN=\"CENTER\"><tr><td>

      <!-- form fitting box for both tables -->

    <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\"
     VALIGN=\"TOP\" ALIGN=\"CENTER\">
    <tr VALIGN=\"TOP\"><td COLSPAN=\"2\" ALIGN=\"CENTER\" VALIGN=\"TOP\">
      <b>".__("Name")."</b>
    </td></tr>
    <tr>
     <td WIDTH=\"30%\" ALIGN=\"RIGHT\">".__("Last")."</td>
     <td><INPUT TYPE=TEXT NAME=\"cilname\" SIZE=20 MAXLENGTH=50
          VALUE=\"".prepare($cilname)."\"></td>
    </tr>
    <tr>
     <td WIDTH=\"30%\" ALIGN=\"RIGHT\">".__("First")."</td>
     <td><INPUT TYPE=TEXT NAME=\"cifname\" SIZE=\"20\" MAXLENGTH=\"50\"
          VALUE=\"".prepare($cifname)."\"></td>
    </tr>
    <tr>
     <td WIDTH=\"30%\" ALIGN=\"RIGHT\">".__("Middle")."</td>
     <td><INPUT TYPE=TEXT NAME=\"cimname\" SIZE=20 MAXLENGTH=50
          VALUE=\"$cimname\"></td>
    </tr>
    </table>

    </td><td>

    <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\" VALIGN=\"TOP\"
     ALIGN=\"CENTER\" CLASS=\"reverse\">
    <tr><td COLSPAN=\"2\" ALIGN=\"CENTER\">
     <b>".__("Contact Information")."</b>
    </td></tr>
    <tr>
     <td WIDTH=\"40%\" ALIGN=\"RIGHT\">".__("Home Phone")." &nbsp;</td>
     <td>".fm_phone_entry('cihphone', -1, false)."</td>
    </tr>
    <tr>
     <td WIDTH=\"40%\" ALIGN=\"RIGHT\">".__("Work Phone")." &nbsp;</td>
     <td>".fm_phone_entry('ciwphone', -1, false)."</td>
    </tr>
    <tr>
     <td WIDTH=\"40%\" ALIGN=\"RIGHT\">".__("Took Call")." &nbsp;</td>
    <td><input TYPE=\"TEXT\" NAME=\"citookcall\" SIZE=\"25\" MAXLENGTH=\"50\"
      VALUE=\"".prepare($citookcall)."\"/></td>
    </tr>
    </table>

     <!-- now, end of form fitting table... -->
    </td></tr></table>

    <p/>
    ";
    
    if (!isset($cifacility)) $cifacility=$_SESSION['default_facility']; 
      // doesn't seem to hurt, but doesn't seem to do anything...
   
    $display_buffer .= "
    <table WIDTH=\"100%\" BORDER=\"0\" ALIGN=\"CENTER\" VALIGN=\"CENTER\"
     CELLSPACING=\"0\" CELLPADDING=\"5\">
     <tr>
      <td ALIGN=\"RIGHT\">".__("Date of Birth")."</td>
      <td>".fm_date_entry("cidob", true)."</td>
     </tr>
     <tr>
      <td ALIGN=\"RIGHT\">".__("Complaint")."</td>
      <td><textarea NAME=\"cicomplaint\" ROWS=\"4\" COLS=\"40\"
           WRAP=\"VIRTUAL\">".prepare($cicomplaint)."</textarea>
      </td>
     </tr>
     <tr>
      <td ALIGN=\"RIGHT\">".__("Facility")."</td>
      <td>
      ".freemed_display_selectbox (
      $sql->query("SELECT * FROM facility ORDER BY psrname,psrnote"),
      "#psrname# [#psrnote#]", "cifacility")."
      </td>
     </tr>
     <tr>
      <td ALIGN=\"RIGHT\">".__("Physician")."</td>
      <td>
    ";

    if ($ciphysician < 1) {
      $ciphysician = freemed::get_link_field ($default_facility, "facility",
        "psrdefphy");
    }

    $display_buffer .= "
    ".freemed_display_selectbox(
		$sql->query("SELECT * FROM physician WHERE phylname != '' ".
				"ORDER BY phylname, phyfname"),
		"#phylname#, #phyfname#", "ciphysician")."
      </td>
    </tr>
    </table>
    <p/>
    <div ALIGN=\"CENTER\">
     <input class=\"button\" name=\"submit\" TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\"/>
     <input class=\"button\" TYPE=\"RESET\" VALUE=\"".__("Clear")."\"/>
     <input class=\"button\" name=\"submit\" TYPE=\"SUBMIT\" VALUE=\"".__("Cancel")."\"/>
    </div>
    </form>
    <p/>
  ";
  break;

 case "add":
  $page_title = __("Adding")." ".$record_name;
  $display_buffer .= "\n".__("Adding")." ".$record_name." ... \n";
  $query = $sql->insert_query(
  	"callin",
	array (
		'cilname',
		'cifname',
		'cimname',
		'cihphone' => fm_phone_assemble('cihphone'),
		'ciwphone' => fm_phone_assemble('ciwphone'),
		'cidob' => fm_date_assemble('cidob'),
		'cicomplaint',
		'cidatestamp' => date('Y-m-d'),
		'cifacility' => $default_facility,
		'ciphysician',
		'citookcall',
		'cipatient' => '0'
	)
  );
  $result = $sql->query ($query);

  if ($result) $display_buffer .= __("done");
   else $display_buffer .= __("ERROR");
  $display_buffer .= " 
    <p/>
    <div ALIGN=\"CENTER\">
     <a HREF=\"patient.php\">Patient Menu</a> |
     <A HREF=\"call-in.php\">Call In Menu</a> |
     <A HREF=\"main.php\">".__("Return to the Main Menu")."</a>
    </div>
    <p/>
  ";
  break;

 case "view":
 case "display":
  $page_title = $record_name." ".__("View/Manage");
  $query   = "SELECT * FROM scheduler WHERE
              ((calpatient='$id') AND (caltype='temp'))
              ORDER BY caldateof, calhour, calminute";
  $result  = $sql->query ($query);
  $rows    = $sql->num_rows ($result);
  $ciname  = freemed::get_link_rec ($id, "callin");
  $cilname = $ciname ["cilname"];
  $cifname = $ciname ["cifname"];
  $cimname = $ciname ["cimname"];
  $display_buffer .= "
    <table WIDTH=\"100%\" CLASS=\"reverse\" CELLSPACING=\"0\" CELLPADDING=\"2\"
     VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
    <tr><td ALIGN=\"CENTER\" class=\"reverse\">
      <b>$cilname, $cifname $cimname</b> : $rows ".__("Appointments")."
    </td></tr>
    </table>
    <p/>
    <a HREF=\"show_appointments.php?patient=$id&type=temp\"
     >".__("Show Today's Appointments")."</a>
    <p/>
    <a HREF=\"show_appointments.php?patient=$id&type=temp&show=all\"
     >".__("Show All Appointments")."</a>
    <p/>
    <a HREF=\"main.php\"
     >".__("Return to the Main Menu")."</a>
    <p/>
  ";
  break;

	// deletes should fall through
 case "del":
 if ($id>0) {
	$sql->query("DELETE FROM callin WHERE id='".addslashes($id)."'");
 }
 // no break

 default:
  // Set page title
  $page_title = $record_name;
  
  // Push onto stack
  page_push();

	// Make sure that we "return" to the main menu
	$_ref = $__ref = "main.php";

  $display_buffer .= template::link_bar(array(
		__("Old") =>
		"$page_name?type=old",
		__("All") =>
		"$page_name?type=all",
		__("Current") =>
		"$page_name?type=cur"
	))."<p/>\n";

  $display_buffer .= freemed_display_actionbar ($page_name);

  $display_buffer .= "
    <table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"3\" VALIGN=\"CENTER\"
     ALIGN=\"CENTER\" CLASS=\"".freemed_alternate()."\">
    <tr>
     <td><b>".__("Name")."</b></td>
     <td><b>".__("Date of Call")."</b></td>
     <td><b>".__("Home/Work Phone")."</b></td>
     <td><b>".__("Action")."</b></td>
    </tr> 
  ";

    // checks to make sure this hasn't been entered yet...
  switch ($type) {
    case "old":          $__type_call_in__ = "cipatient > 0";  break;
    case "all":          $__type_call_in__ = "0 = 0"; break;
    case "cur": default: $__type_call_in__ = "cipatient = 0";  break;
  } // end checking for type...

  $result = $sql->query ("SELECT * FROM $db_name
             WHERE ($__type_call_in__)
             ORDER BY cidatestamp, cilname, cifname, cimname");

  while ($r = $sql->fetch_array ($result)) {
    extract ($r);

    if (freemed::check_access_for_facility ($cifacility)) {

    if (strlen($cimname)>0) $ci_comma = ", ";
     else $ci_comma = " ";
    $cihphone_raw = $r["cihphone"];
    if (strlen($cihphone_raw)>6)
      $cihphone = "H: " .
                  substr ($cihphone_raw, 0, 3) . "-" .
                  substr ($cihphone_raw, 3, 3) . "-" .
                  substr ($cihphone_raw, 6, 4);
      else $cihphone = "";
    $ciwphone_raw = $r["ciwphone"]; 
    if (strlen($ciwphone_raw)>6)
      $ciwphone = "W: " .
                  substr ($ciwphone_raw, 0, 3) . "-" .
                  substr ($ciwphone_raw, 3, 3) . "-" .
                  substr ($ciwphone_raw, 6, 4);
      else $ciwphone = "";
    if ((strlen($ciwphone)>0) and (strlen($cihphone)>0))
      $ciphonesep = "<br/>";
    else $ciphonesep = " ";

    $display_buffer .= "
      <tr CLASS=\"".freemed_alternate()."\">
       <td>$cilname, $cifname$ci_comma $cimname</td>
       <td>$cidatestamp</td>
       <td>$ciwphone $ciphonesep $cihphone&nbsp;</td>
       <td align=\"left\">
    ";

     // display the convert link
    $display_buffer .= template::link_bar(array(
        __("Enter") =>
     "patient.php?action=addform".
        "&ptfname=".rawurlencode ($cifname).
        "&ptlname=".rawurlencode ($cilname).
        "&ptmname=".rawurlencode ($cimname).
        "&pthphone1=".rawurlencode (substr($cihphone_raw, 0, 3)).
        "&pthphone2=".rawurlencode (substr($cihphone_raw, 3, 3)).
        "&pthphone3=".rawurlencode (substr($cihphone_raw, 6, 4)).
        "&ptwphone1=".rawurlencode (substr($ciwphone_raw, 0, 3)).
        "&ptwphone2=".rawurlencode (substr($ciwphone_raw, 3, 3)).
        "&ptwphone3=".rawurlencode (substr($ciwphone_raw, 6, 4)).
        "&ptdob1=".rawurlencode (substr($cidob, 0, 4)).
        "&ptdob2=".rawurlencode (substr($cidob, 5, 2)).
        "&ptdob3=".rawurlencode (substr($cidob, 8, 2)).
        "&ci="     . $id,

      __("View") =>
     "$page_name?action=display&id=$id",

      __("Book") =>
     "book_appointment.php?action=&".
      "patient=$id&type=temp",

      __("Delete") =>
	"call-in.php?id=".urlencode($id)."&action=del"
    ), array('align' => 'LEFT'));

    $display_buffer .= "
        </td>
      </tr>
      <tr>
        <td COLSPAN=\"4\" CLASS=\"infobox\" ALIGN=\"CENTER\"><i>".
	prepare($cicomplaint)."</i></td>
      </tr>
    ";

    } // if there was no access for the facility

    $cihphone = "";
    $ciwphone = "";
  } // end while

  $display_buffer .= "
    </table>
  "; // end of the table

  $display_buffer .= freemed_display_actionbar ($page_name);

  $display_buffer .= "<p/>\n".template::link_bar(array(
		__("Old") =>
		"$page_name?type=old",
		__("All") =>
		"$page_name?type=all",
		__("Current") =>
		"$page_name?type=cur"
	));
  break;

} // end master switch

template_display();

?>
