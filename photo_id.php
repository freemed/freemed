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
		// Show widget
		$display_buffer .= "
		<DIV ALIGN=\"CENTER\">
		"._("Attach Photographic Identification")."
		</DIV>
		<DIV ALIGN=\"CENTER\">
		<FORM METHOD=\"POST\" NAME=\"form\" ENCTYPE=\"multipart/form-data\" ACTION=\"".page_name()."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" VALUE=\"1000000\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"upload\">
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
