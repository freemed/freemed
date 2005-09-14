<?php
 // $Id$
 // desc: misc useful file functions
 // code: jeff b (jeff@ourexchange.net)
 // lic : LGPL

if (!defined("__FILE_TOOLS_PHP__")) {

define('__FILE_TOOLS_PHP__', true);

define('FILE_RECEIVE_OVERWRITE',	1);
define('FILE_RECEIVE_ASSTRING',		2);

function file_magic ($filename) {
	// return the magic portion from the "file" command
	if (!is_file ($filename)) die ("file_magic :: file does not exist");
	$hash       = exec ("file \"$filename\"");
	$hash_array = explode ($filename.":", $hash);
	return trim ($hash_array[1]);
} // end function file_magic

function file_size ($filename) {
	if (!is_file ($filename))
		die ("file_size :: file does not exist");
	clearstatcache(); // prevent PHP from choking
	$file_stat = stat($filename);
	return $file_stat[7];
} // end function file_size

function human_readable_filesize ($filename) {
	// calculate file size (in human readable format)
	if (!is_file ($filename) and !is_integer($filename)) {
		die ("human_readable_filesize :: file does not exist");
	}

	if (($filename+0)>0) {
		$file_size_num = $filename + 0;
	} else {
		$file_size_num = file_size ($filename);
	}
	if (($file_size_num > 1024) and ($file_size_num < (1024*750))) {
		return number_format (($file_size_num / (1024)), 3)."k ".
			"($file_size_num bytes)";
	} elseif ( ($file_size_num > (1024*750)) and
		($file_size_num < (1024*1024*750)) ) {
		return number_format (($file_size_num / (1024*1024)), 3)."M ".
			"($file_size_num bytes)";
	} else {
		return $file_size_num_bytes." bytes"; 
	}
} // end function human_readable_filesize

function human_readable_time ($total_time) {
	$h = $m = $s = 0;
	if ($total_time <= 0) return "0s"; // catch divide by zero errors
	$m = floor ($total_time / 60);
	$s = floor ($total_time % 60);
	if ($m <= 0) return $s."s"; // drop seconds, if you can
	$h = floor ($m / 60);
	$m = floor ($m % 60);
	if ($h <= 0) return $m."m ".$s."s"; // drop minutes and seconds
	return $h."h ".$m."m ".$s."s";
} // end function human_readable_time

function transfer_time ($filename, $linespeed) {
	// linespeed is 14.4 for 14400, etc... (kbps)
	// returns transfer time in seconds
	if (($filename+0) > 0) {
		$file_size = $filename;
	} else {
		$file_size = file_size ($filename);
	}
	$bps       = ($linespeed * 1000) / 8;
	return ceil (($file_size / $bps));
} // end function transfer_time

function file_widget ($varname) {
	$buffer = "<INPUT TYPE=FILE NAME=\"".prepare($varname)."\">\n";
	return $buffer;
} // end function file_widget

function file_receive ($varname, $destination, $options = 0) {
	global ${$varname}, ${$varname."_name"},
		${$varname."_size"}, ${$varname."_type"};

	// exit if no file is received
	if (${$varname} == "none") return false;

	// set local filename to base name of returned value
	$filename = basename(${$varname."_name"});

	// get upload path
	$upload_path = get_cfg_var ("upload_tmp_dir")."/".$filename;

	// handle FILE_RECEIVED_ASSTRING
	if ($options & FILE_RECEIVED_ASSTRING) {
		$file_handle = fopen ($upload_path, "r");
		$file_contents = fread ($file_handle, filesize($upload_path));
		fclose ($upload_path);
		return $file_contents;
	} // end handling FILE_RECEIVED_ASSTRING

	// if the file is already there and no overwrite, return false
	if (file_exists($upload_path) and
		!($options & FILE_RECEIVE_OVERWRITE)) return false;

	// actual copy
	copy ("$upload_path", $destination); 

	// return all happy
	return true;
} // end function file_receive

} // end if not defined

?>
