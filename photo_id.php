<?php
 // $Id$
 // $Author$
 // note: photographic identification maintenance
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");

//----- Login and authenticate
freemed_open_db ();

//----- Set page title
$page_title = __("Photographic ID Maintenance");

//----- Add page to stack
//page_push();

switch ($action) {
	case "remove":
		// Clean variables
		$patient = freemed::secure_filename($patient);

		// Form filename
		$imagefilename = "img/store/".$patient.".identification.jpg";

		// If it exists, unlink it
		if (file_exists($imagefilename)) {
			// Remove it
			unlink($imagefilename);
		} else {
			// Do nothing if it's not there
		}

		// Return to management
		$refresh = "manage.php?id=".urlencode($patient);
		break; // end of remove action

	case "upload":
		// Use API for upload
		$imagefilename = freemed::store_image(
			$patient,
			"userfile",
			"identification"
		);
		if (!$imagefilename) {
			$display_buffer .= "
			<div ALIGN=\"CENTER\">
			".__("Failed to attach the image.")."
			</div>
			";
			break;
		} else {
			$refresh = "manage.php?id=".urlencode($patient);
			break;
		}
		break; // end case upload

	default:
		// Browser check
		$browser = CreateObject('PHP.browser_detect');
		$IEupload = false;
		if ($browser->BROWSER=="IE") { $IEupload = true; }

		// Show widget
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		".__("Attach Photographic Identification")."
		</div>
		<div ALIGN=\"CENTER\">
		<form METHOD=\"POST\" NAME=\"form\" ENCTYPE=\"multipart/form-data\" ACTION=\"".page_name()."\" NAME=\"myform\">
		<input TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" VALUE=\"10000000\"/>
		<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"upload\"/>
		";
		if ($IEupload) {
			$display_buffer .= "
			<script LANGUAGE=\"VBScript\">
			<!--
			Sub ScanControl_ScanComplete(FileName)
				document.myform.userfile.focus()
				document.myform.ScanControl.PasteName()
			End Sub
			-->
			</script>
			<object ID=\"ScanControl\"
			CLASSID=\"CLSID:4A72D130-BBAD-45BD-AB11-E506466200EA\"
			CODEBASE=\"./lib/webscanner.cab#version=1,0,0,20\">
			</object><br/>
			";
		}
		$display_buffer .= "
		<input TYPE=\"FILE\" NAME=\"userfile\"/>
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Attach Image")."\"/>
		</form>
		</div>
		";
		break; // end default
}

//----- Display template
template_display();

?>
