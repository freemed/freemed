<?php
 # file: patient_image_handler.php3
 # desc: handles images in the patimg table
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 // obligatory initialization garbage
 $page_name = "patient_image_handler.php3";
 $db_name   = "patimg";
 include ("lib/freemed.php");
 include ("lib/API.php");

 // authenticate user cookie
 freemed_open_db ($LoginCookie);

 // determine what we are getting, and grab it
 if ($id>0) {
   $result = fdb_query ("SELECT * FROM $db_name
                         WHERE id='$id'");
   $proper = fdb_fetch_array ($result);
 } elseif ($patient>0) {
   // gets latest picture of patient
   $result = fdb_query ("SELECT * FROM $db_name
                         WHERE pipatient='$patient'
                         ORDER BY pidate DESC");
   $proper = fdb_fetch_array ($result);
 } // end if...then determining type

 // display header for content type
 Header ("Content-Type: image/jpeg");

 // display the actual image data
 echo stripslashes($proper["pidata"]);

 // close database connection
 freemed_close_db ();
?>
