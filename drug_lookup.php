<?php
	// $Id$
	// $Author$

$page_name = "drug_lookup.php";
include_once("lib/freemed.php");
include_once("lib/class.rxlist.php");

//----- Open database, authenticate, etc
freemed_open_db ();
$this_user = CreateObject('FreeMED.User');

//----- Check for process
if ($action==_("Search")) {
	$display_buffer .= "<BODY onLoad=\"process(); return true;\">\n";
}

//----- Form header
$display_buffer .= "<CENTER><FORM NAME=\"lookup\" ACTION=\"".$page_name."\" ".
	"METHOD=\"POST\">\n";

//----- Master action switch
switch ($action) {
	case _("Search"):

	// Perform query
	unset($list);
	if (!empty($drug)) {
		$list = RxList::get_list($drug);
	}

	// If no results, die right here
	if (!isset($drug) or (count($list) < 1)) {
		$display_buffer .= _("No drugs found with that criteria!");
		break;
	}

	// Handle immediate passing and closing
	if (count($list) == 1) {
		$display_buffer .= "
		<SCRIPT LANGUAGE=\"Javascript\">
		function process () {
			var our_value = '".prepare($drug[0])."'

			// Pass the variable
			opener.document.".prepare($formname).".".
			prepare($varname).".value = our_value
			
			// Submit name to null
			opener.document.".prepare($formname).".".prepare($submitname).
			".value = ''
			// Submit the form
			opener.document.forms.".prepare($formname).".submit();
			
			// Close the window
			window.self.close()
		}
		</SCRIPT>
		We should be '".prepare($drug[0])."'.
		";
		
		// Add to pick list
		$pick_list = $list;
	} else { // end handling only one result
		unset($pick_list);
		$pick_list = $list;
	}

	$display_buffer .= "
		<SCRIPT LANGUAGE=\"Javascript\">
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
		<div ALIGN=\"CENTER\" CLASS=\"infobox\">
		".html_form::select_widget(
			"list",	$pick_list
		)."
		<input TYPE=\"BUTTON\" NAME=\"select\" ".
		"VALUE=\"Select\" onClick=\"my_process(); return true;\">
		</div>
	";
	break;

	default:
	$display_buffer .= "
		<INPUT TYPE=\"HIDDEN\" NAME=\"varname\" VALUE=\"".prepare($varname)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"formname\" VALUE=\"".prepare($formname)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"submitname\" VALUE=\"".prepare($submitname)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\""._("Search")."\">
		<div ALIGN=\"CENTER\" CLASS=\"infobox\">
			"._("Drug")." :
			<input TYPE=\"TEXT\" NAME=\"drug\" ".
			"VALUE=\"".prepare($drug)."\">
			<input TYPE=\"SUBMIT\" NAME=\"action\" ".
			"VALUE=\""._("Search")."\">
		</div>
	";
	break;
} // end switch

//----- End of form
$display_buffer .= "</FORM>\n";

//----- Display template
$GLOBALS['__freemed']['no_template_display'] = true;
template_display();

?>
