<?php
 // $Id$
 // desc: handles images in the patimg table
 // lic : GPL

 // obligatory initialization garbage
 $page_name  = "patient_image_handler.php";
 $table_name = "patimg";
 include ("lib/freemed.php");
 include ("lib/API.php");

 // authenticate user cookie
 freemed_open_db ($LoginCookie);

 // determine what we are getting, and grab it
 if ($id>0) {
   $result = $sql->query ("SELECT * FROM ".$table_name."
                         WHERE id='".addslashes($id)."'");
   $proper = $sql->fetch_array ($result);
 } elseif ($patient>0) {
   // gets latest picture of patient
   $result = $sql->query ("SELECT * FROM ".$table_name."
                         WHERE pipatient='".addslashes($patient)."'
                         ORDER BY pidate DESC");
   $proper = $sql->fetch_array ($result);
 } // end if...then determining type

 // display header for content type
 Header ("Content-Type: image/jpeg");

 // display the actual image data
 echo stripslashes($proper["pidata"]);

 // close database connection
 freemed_close_db ();
?>
