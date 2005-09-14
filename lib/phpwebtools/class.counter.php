<?php
 // $Id$
 // code: Vamsi Nath <php@vamsi.net>, Boz@musheen.com
 // lic : LGPL

if (!defined("__CLASS_COUNTER_PHP__")) {

define ('__CLASS_COUNTER_PHP__', true);

class counter {

	function get_count($PHP_SELF) {

	  // Strip off file path and just get filename of the page the 
	  // counter appears on
	  $base_name = basename ("$PHP_SELF");
	  $dir_name = dirname ("$PHP_SELF");

	  // Define base 'counter' directory where the count files will be stored.
	  $count_file_root = HTTPD_ROOT . "/" . "counters/" . $GLOBALS["SERVER_NAME"];
	  $count_file_base = $base_name . "_count";
	  $count_file = "$count_file_root" . "$dir_name/" . "$count_file_base";

	  // If counter directory doesn't exist, create it.
	  if( !is_dir ("$count_file_root" . "$dir_name") ) {
	  	mkdir ("$count_file_root" .  "$dir_name", 0700);
	  }

	  // Check to see if counter file exists
	  if (file_exists("$count_file")) {
    
	    $file = file($count_file); // Store contents of counter file in array

	    $split = split(",",$file[0], 2);  // Retrieve file count
	    $count = chop($split[1]);  // Chop off whitespace
	    ++$count;

	    // Now let's write the updated count back to the counter file    
	    $tf = @fopen("$count_file","w+");
	    fwrite($tf,"$count_file,");
	    fwrite($tf,"$count");
	    @fclose($count_file);
	    return $count;
    
	  } else {

	    $tf = @fopen("$count_file","w+");
	    if (!$tf) echo "not opened! <BR>\n";

	    fwrite($tf,"$count_file,");
	    fwrite($tf,"1");
	    @fclose("$count_file");
	    $count = "1";
	    return $count;
	  }

	} // end function get_count

} // end class counter

//   SAMPLE CODE:
//  Return count value to be displayed on page
//  $num = counter::get_count($PHP_SELF);
//  echo "$num";

} // end if not defined

?>
