<?php
 // $Id$
 // $Author$
 // lic : GPL

 // obligatory initialization garbage
$page_name = "patient_image_handler.php";
include ("lib/freemed.php");

 // authenticate user cookie
freemed_open_db ();

//----- Clean all variables
$patient = freemed::secure_filename($patient);
$id      = freemed::secure_filename($id     );

//----- Assemble proper file name
$imagefilename = "img/store/".$patient.".".$id.".jpg";

if (!file_exists($imagefilename)) { die(""); }

// display header for content type
Header ("Content-Type: image/jpeg");

// display the actual image data
print readfile($imagefilename);

?>
