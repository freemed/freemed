<?php
 // $Id$
 // desc: handles images in the patimg table
 // lic : GPL

 // obligatory initialization garbage
$page_name = "patient_image_handler.php";
include ("lib/freemed.php");
include ("lib/API.php");

// set image table properly
define ('TABLE_NAME', "patimg");

 // authenticate user cookie
freemed_open_db ();

 // determine what we are getting, and grab it
if ($id > 0) {
	$result = $sql->query ("SELECT * FROM ".TABLE_NAME." ".
		"WHERE id='".addslashes($id)."'");
	if (!$sql->results($result))
		die("No image found.");
	$proper = $sql->fetch_array ($result);
} elseif ($patient > 0) {
	// gets latest picture of patient
	$result = $sql->query ("SELECT * FROM ".TABLE_NAME." ".
		"WHERE pipatient='".addslashes($patient)."' ".
		"ORDER BY pidate DESC");
	if (!$sql->results($result))
		die("No image found.");
	$proper = $sql->fetch_array ($result);
} // end if...then determining type

// display header for content type
Header ("Content-Type: image/jpeg");

// display the actual image data
print stripslashes($proper["pidata"]);

?>
