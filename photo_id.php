<?php
 // $Id$
 // $Author$
 // note: photographic identification maintenance
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");

//----- Login and authenticate
freemed_open_db ();

//----- Set page title
$page_title = _("Photographic ID Maintenance");

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
			<DIV ALIGN=\"CENTER\">
			"._("Failed to attach the image.")."
			</DIV>
			";
			break;
		} else {
			$refresh = "manage.php?id=".urlencode($patient);
			break;
		}
		break; // end case upload

	default:
		// Browser check
		$browser = new browser_detect();
		$IEupload = false;
		if ($browser->BROWSER=="IE") { $IEupload = true; }

		// Show widget
		$display_buffer .= "
		<DIV ALIGN=\"CENTER\">
		"._("Attach Photographic Identification")."
		</DIV>
		<DIV ALIGN=\"CENTER\">
		<FORM METHOD=\"POST\" NAME=\"form\" ENCTYPE=\"multipart/form-data\" ACTION=\"".page_name()."\" NAME=\"myform\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" VALUE=\"10000000\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"upload\">
		";
		if ($IEupload) {
			$display_buffer .= "
			<SCRIPT LANGUAGE=\"VBScript\">
			<!--
			Sub ScanControl_ScanComplete(FileName)
				document.myform.userfile.focus()
				document.myform.ScanControl.PasteName()
			End Sub
			-->
			</SCRIPT>
			<OBJECT ID=\"ScanControl\"
			CLASSID=\"CLSID:4A72D130-BBAD-45BD-AB11-E506466200EA\"
			CODEBASE=\"./lib/webscanner.cab#version=1,0,0,20\">
			</OBJECT><BR/>
			";
		}
		$display_buffer .= "
		<INPUT TYPE=\"FILE\" NAME=\"userfile\">
		<INPUT TYPE=\"SUBMIT\" VALUE=\""._("Attach Image")."\">
		</FORM>
		</DIV>
		";
		break; // end default
}

//----- Display template
template_display();

?>
