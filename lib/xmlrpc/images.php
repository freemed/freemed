<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Images.* namespace

function FreeMED_Images_attach($params) {
	// Parameters:
	// 	patient id,
	//	array(
	//		date,
	//		category,
	//		description,
	//		array ( episodes of care ), /* not yet */
	//		array(
	//			images
	//		)
	//	)

	global $sql;

	$_param = $params->getParam(0);

	// Parse scalar parameters
	$patient_id = $_param->structmem2scalarval("patient_id");
	$date = $_param->structmem2scalarval("date");
	$category = $_param->structmem2scalarval("category");
	$desc = $_param->structmem2scalarval("description");

	// Get array of images
	$_images = $_param->structmem("images");
	for ($idx=0; $idx<$_images->arraysize(); $idx++) {
		$element = $_images->arraymem($idx);
		$images[$idx] = $element->scalarval();
		unset($element);
	}

	// Create image entry
	$query = $sql->insert_query(
		"images",
		array(
			"imagedt" => $date,
			"imagetype" => $category,
			"imagepat" => $patient_id,
			"imagedesc" => $desc
		)
	);
//	$result = $sql->query($query);

	// Set names properly
	$last_record = $sql->last_record($result, "images");
	$query = $sql->update_query(
		"images",
		array("imagefile" => $patient_id.".".$last_record.".djvu"),
		array("id" => $last_record)
	);
//	$result = $sql->query($query);

	// Process images
	unset($djvu);
	foreach ($images AS $__garbage => $file) {
		// Write image to temporary file
		$tempname = tempnam("/tmp", "xmlrpcimg");
		$original = fopen($tempname.".jpg", "w");
		fwrite($original, $file);
		fclose($original);

		// Convert to PBM
		$command = "/usr/X11R6/bin/convert ".
			freemed::secure_filename($tempname).".jpg ".
			$tempname.".pbm";
		print "PBM: $command\n";
		exec($command);
//		unlink($tempname.".jpg");
		
		// Convert to DJVU
		$command = "/usr/bin/cjb2 ".
			$tempname.".pbm ".
			$tempname.".djvu";
		print "DJVU: $command\n";
		exec($command);
//		unlink($tempname.".pbm");

		// Add to stack	
		$djvu[] = $tempname.".djvu";	
	}

	// Compile into DJVU final file
	$command = "/usr/bin/djvm -c ".PHYSICAL_LOCATION."/img/store/".
		$patient_id.".".$last_record.".djvu ".
		join (" ", $djvu);
	print "command = $command\n";
	exec($command);

	// Remove temporary DJVU files
//	foreach ($djvu as $__garbage => $my_file) { unlink($djvu.".djvu"); }

	// If everything worked, return true
	return new xmlrpcresp(new xmlrpcval(true, "boolean"));
} rpc_register("FreeMED.Images.attach");

?>
