<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Images.* namespace

class Images {

	function attach($params) {
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
		
		//$_param = $params->getParam(0);
	
		// Parse scalar parameters
		$patient_id = $params["patient_id"];
		$date = $params["date"];
		$category = $params["category"];
		$desc = $params["description"];
	
		// If everything worked, return true
		if ($patient_id < 1) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', true, 'boolean')
			);
		}

		// Get array of images
		$images = $params["images"];

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
		$result = $sql->query($query);
	
		// Set names properly
		$last_record = $sql->last_record($result, "images");
		$query = $sql->update_query(
			"images",
			array("imagefile" => $patient_id.".".$last_record.".djvu"),
			array("id" => $last_record)
		);
		$result = $sql->query($query);
	
		// Process images
		unset($djvu);
		foreach ($images AS $__garbage => $file) {
			// Write image to temporary file
			$tempname = tempnam("/tmp", "xmlrpcimg");
			$original = fopen($tempname.".jpg", "w");
			fwrite($original, $file);
			fclose($original);
	
			// Convert to PBM
			$command = "`which convert` ".
				freemed::secure_filename($tempname).".jpg ".
				$tempname.".pbm";
			//print "PBM: $command\n";
			exec($command);
			unlink($tempname.".jpg");
			
			// Convert to DJVU
			$command = "`which cjb2` ".
				$tempname.".pbm ".
				$tempname.".djvu";
			//print "DJVU: $command\n";
			exec($command);
			unlink($tempname.".pbm");
	
			// Add to stack	
			$djvu[] = $tempname.".djvu";	
		}
	
		// Compile into DJVU final file
		$command = "`which djvm` -c ".PHYSICAL_LOCATION."/img/store/".
			$patient_id.".".$last_record.".djvu ".
			join (" ", $djvu);
		//print "command = $command\n";
		exec($command);

		// Just in cast the prefix is a file, kill that too...
		@unlink($tempname);

		// Remove temporary DJVU files
		foreach ($djvu as $__garbage => $my_file) {
			unlink($djvu.".djvu");
		}

		// If everything worked, return true
		return CreateObject('PHP.xmlrpcresp',
			CreateObject('PHP.xmlrpcval', true, 'boolean')
		);
	} // end method attach

	function get($patient, $id) {
		global $sql;

		// Secure parameters
		$patient = freemed::secure_filename($patient);
		$id      = freemed::secure_filename($id     );

		// Assemble file name
		$imagefilename = "img/store/".$patient.".".$id.".djvu";

		// Read file
		if (!($fp = fopen($imagefilename, "r"))) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', false, 'boolean')
			);
		} else {
			$buffer = "";
			while (!feof($fp)) {
				$buffer .= fgets($fp, 4096);
			}
			fclose($fp);
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', $buffer, 'base64')
			);
		}
	} // end method get

} // end class Images

?>
