<?php
	// $Id$
	// $Author$

$page_name = "patient_lookup.php";
include_once("lib/freemed.php");

//----- Open database, authenticate, etc
freemed::connect ();
$this_user = CreateObject('FreeMED.User');

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"patientlookup.php|user $user_to_log views patient list GLOBAL ACCESS");}	

//----- Check for process
if ($action==__("Search")) {
	$GLOBALS['__freemed']['on_load'] = 'process';
}

//----- Form header
$display_buffer .= "<CENTER><form NAME=\"lookup\" ACTION=\"".$page_name."\" ".
	"METHOD=\"POST\">\n";

//----- Master action switch
switch ($action) {
	case __("Search"):
	// Perform query

//	if ( empty($last_name) and empty($first_name) and
//		empty(html_form::combo_assemble("city")) ) break;

	unset ( $wheres );
	if (!empty($last_name))
		$wheres[] = "LCASE(ptlname) LIKE '".addslashes(
			strtolower($last_name))."%'";
	if (!empty($first_name))
		$wheres[] = "LCASE(ptfname) LIKE '".addslashes(
			strtolower($first_name))."%'";
	if (!empty($city))
		$wheres[] = "ptcity = '".addslashes(
			html_form::combo_assemble("city"))."'";

	// smart query
	if (!empty($smart)) {
		// decide if we're last, first or first last
        	if (!(strpos($_REQUEST['smart'], ',')===false)) {
        	        // last, first
        	        list ($last, $first) = explode(',', $_REQUEST['smart']);
        	        $last = trim($last);
        	        $first = trim($first);
        	} else {
        	        // first last
        	        list ($first, $last) = explode(' ', $_REQUEST['smart']);
        	}
		unset($wheres);
		$wheres[] = "ptfname LIKE '".addslashes($first)."%'";
		$wheres[] = "ptlname LIKE '".addslashes($last)."%'";
	}

	$query = "SELECT * FROM patient WHERE ".implode(" AND ", $wheres).
		"ORDER BY ptlname, ptfname, ptcity";
	$result = $sql->query($query);

	// If no results, die right here
	if (!$sql->results($result)) {
		$display_buffer .= __("No patients found with that criteria!");
		break;
	}

	// Handle immediate passing and closing
	if ($sql->num_rows($result)==1) {
		$r = $sql->fetch_array($result);
		$display_buffer .= "
		<script LANGUAGE=\"Javascript\">
		function process () {
			var our_value = '".prepare($r[id])."'

			// Pass the variable
			opener.document.".prepare($formname).".".
			prepare($varname).".value = our_value
			
			// Show an alert with the patients' name
			//var x = alert ('The patient should be '+".
				"'".$r[ptfname].' '.
				$r[ptlname]."');

			// Submit name to null
			opener.document.".prepare($formname).".".prepare($submitname).
			".value = ''
			// Submit the form
			opener.document.forms.".prepare($formname).".submit();
			
			// Close the window
			window.self.close()
		}
		</script>
		We should be '".$r['ptfname'].' '.$r['ptlname']."'.
		";
		
		// Add to pick list
		$pick_list[(stripslashes($r['ptlname'].", ".
			$r['ptfname']." [".
			$r['ptid']."] (".
			$r['ptcity'].", ".
			$r['ptstate'].")"))] = $r['id'];
	} else { // end handling only one result
		unset($pick_list);

		// Display pick list of results
		while ($r = $sql->fetch_array($result)) {
			$pick_list[(stripslashes($r['ptlname'].", ".
				$r['ptfname']." [".
				$r['ptid']."] (".
				$r['ptcity'].", ".
				$r['ptstate'].")"))] = $r['id'];
		} // end looping through results
	}

	$display_buffer .= "
		<script LANGUAGE=\"Javascript\">
		function my_process () {
			// Pass the variable
			opener.document.".prepare($formname).".".prepare($varname).
			".value = document.lookup.list.value

			// Submit name to null
			opener.document.".prepare($formname).".".prepare($submitname).
			".value = ''

			// Submit the form
			opener.document.".prepare($formname).".submit()
			
			// Close the window
			window.self.close()
		}
		</SCRIPT>
		</script>
		<div ALIGN=\"CENTER\" CLASS=\"patient_search\" ".
			"style=\"height: 100%; text-valign: middle;\">
		<br/>
		".__("Select a patient from the following list:")."
		".html_form::select_widget(
			"list",	$pick_list
		)."<br/>
		<input TYPE=\"BUTTON\" NAME=\"select\" class=\"button\" ".
		"VALUE=\"".__("Select")."\" ".
		"onClick=\"my_process(); return true;\"/>
		<input type=\"BUTTON\" NAME=\"cancel\" class=\"button\" ".
		"VALUE=\"".__("Cancel")."\" ".
		"onClick=\"window.self.close(); return true;\" />
		<a href=\"patient_lookup.php?varname=$varname&formname=$formname&submitname=$submitname\" ".
		"class=\"button\">".__("Back")."</a>
		</div>
	";
	break;

	default:
	$display_buffer .= "
		<div ALIGN=\"CENTER\" CLASS=\"infobox\">
		<input TYPE=\"HIDDEN\" NAME=\"varname\" VALUE=\"".prepare($varname)."\" />
		<input TYPE=\"HIDDEN\" NAME=\"formname\" VALUE=\"".prepare($formname)."\" />
		<input TYPE=\"HIDDEN\" NAME=\"submitname\" VALUE=\"".prepare($submitname)."\" />
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".__("Search")."\" />
		<div ALIGN=\"CENTER\" style=\"height: 100%;\" ".
			"class=\"patient_search\">
		".html_form::form_table(array(
			__("Smart Lookup") =>
			html_form::text_widget(
				"smart", 40
			),
			
			__("Last Name") =>
			html_form::text_widget(
				"last_name", 20
			),
			
			__("First Name") =>
			html_form::text_widget(
				"first_name", 20
			),

			__("City") =>
			html_form::combo_widget(
				"city",
				$sql->distinct_values(
					"patient", "ptcity"
				)
			)
		))."
		<input TYPE=\"SUBMIT\" VALUE=\"".__("Search")."\" ".
			"class=\"button\"/>
		<input type=\"BUTTON\" NAME=\"cancel\" class=\"button\" ".
		"VALUE=\"".__("Cancel")."\" ".
		"onClick=\"window.self.close(); return true;\" />
		</div>
	";
	break;
} // end switch

//----- End of form
$display_buffer .= "</form>\n";

//----- Display template
$GLOBALS['__freemed']['no_template_display'] = true;
template_display();

?>
