<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Images.* namespace

class Images {

	function attach($_params) {
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

		// Decide if we're dealing with an array or not
		$is_struct = false;
		foreach ($_params AS $k => $v) {
			if (!is_integer($k)) {
				$is_struct = true;
			}
		}

		if ($is_struct) {
			// If it's a structure, pass through
			$params = $_params;
		} else {
			foreach ($_params AS $k => $v) {
				// Recurse into individual pieces
				Images::attach($v);
			}
		}

		// Parse scalar parameters
		$patient_id = $params["patient_id"];
		$date = $params["date"];
		$category = $params["category"];
		$desc = $params["description"];
		$color = ( isset($params["color"]) ? $params['color'] : false );

		// If everything worked, return true
		if ($patient_id < 1) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', false, 'boolean')
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
		//print "query = $query\n";
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

			if (!$color) {
				// Convert to PBM and do B&W
				//$command = `which convert`." ".
				$command = "/usr/bin/convert \"".
					freemed::secure_filename($tempname).".jpg\" ".
					"\"".$tempname.".pbm\"";
				//print "PBM: $command\n";
				exec($command);
				syslog(LOG_INFO,"XMLRPC|$command");	
				//if (!file_exists($tempname.".pbm")) { syslog(LOG_INFO,"XMLRPC|previous command failed"); }
				//unlink($tempname.".jpg");
				
				// Convert to DJVU
				//$command = `which cjb2`." ".
					$command = "/usr/bin/cjb2 ".
					"\"".$tempname.".pbm\" ".
					"\"".$tempname.".djvu\"";
				//print "DJVU: $command\n";
				syslog(LOG_INFO,"XMLRPC|$command");	
				exec($command);
			} else {
				// Convert to DJVU in color
				$command = "/usr/bin/c44 ".
					"\"".freemed::secure_filename($tempname).".jpg\" ".
					"\"".freemed::secure_filename($tempname).".djvu\"";
				//print "DJVU: $command\n";
				syslog(LOG_INFO,"XMLRPC|$command");	
				exec($command);
			}

			if (!file_exists($tempname.".djvu")) { syslog(LOG_INFO,"XMLRPC|previous command failed"); }
			//unlink($tempname.".pbm");
	
			// Add to stack	
			$djvu[] = freemed::secure_filename($tempname).".djvu";	
		}

		// Make proper directory
		$mkdir_command = "mkdir -p ".PHYSICAL_LOCATION.'/'.
			dirname(
				freemed::image_filename(
					$patient_id,
					$last_record,
					'djvu'
				)
			);
		exec ($mkdir_command);
		syslog(LOG_INFO,"XMLRPC|$mkdir_command");	
	
		// Compile into DJVU final file
		//$command = `which djvm`.' -c '.PHYSICAL_LOCATION.'/'.
		$command = "/usr/bin/djvm ".' -c "'.PHYSICAL_LOCATION.'/'.
			freemed::image_filename(
				$patient_id,
				$last_record,
				'djvu'
			).'" '.join (' ', $djvu);
		//print "command = $command\n";
		exec($command);
		syslog(LOG_INFO,"XMLRPC|$command");	
		if (!file_exists(freemed::image_filename($patient_id, $last_record, 'djvu'))) { syslog(LOG_INFO,"XMLRPC|previous command failed"); }

		// Just in cast the prefix is a file, kill that too...
		//@unlink($tempname);

		// Remove temporary DJVU files
		foreach ($djvu as $__garbage => $my_file) {
			//unlink($djvu.".djvu");
		}

		// If everything worked, return true
		return CreateObject('PHP.xmlrpcresp',
			CreateObject('PHP.xmlrpcval', true, 'boolean')
		);
	} // end method attach

	// Method: get
	//
	//	Fetch image
	//
	// Parameters:
	//
	//	$patient - Record ID of patient
	//
	//	$id - images table ID or "identification"
	//
	//	$convert_to - (optional) String or boolean false. Determines
	//	format for optional conversion. Defaults to false.
	//	* bmp - Windows bitmap format
	//	* jpeg - JPEG image format
	//
	// Returns:
	//
	//	Image binary data
	//
	function get($patient, $id, $convert_to = false) {
		global $sql;

		// Secure parameters
		$patient = freemed::secure_filename($patient);
		$id      = freemed::secure_filename($id     );

		// Assemble file name
		$imagefilename = freemed::image_filename(
			$patient,
			$id,
			'djvu'
		);

		// Read file
		if (!($fp = fopen($imagefilename, "r"))) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', false, 'boolean')
			);
		} else {
			$buffer = "";
			if ($convert_to) {
				fclose ($fp);
				$temp = tempnam("/tmp", "fmImg");

				switch ($convert_to) {
					case 'bmp':
						$e = 'dib'; break;
					case 'jpg':
					case 'jpeg':
					default: 
						$e = 'jpg'; break;
				}

				// Convert to EPS then JPEG
				`/usr/bin/djvups -format=eps -color=yes "$imagefilename" > "$temp.eps"`;
				`/usr/bin/convert "$temp.eps" "$temp.$e"`;

				// Handle inability to open image
				if (!($fp = fopen($temp.".".$e, "r"))) {
					return CreateObject('PHP.xmlrpcresp',
						CreateObject('PHP.xmlrpcval', false, 'boolean')
					);
				}
				while (!feof($fp)) {
					$buffer .= fgets($fp, 4096);
				}
				fclose($fp);
				unlink($temp);
				unlink($temp.".eps");
				unlink($temp.".".$e);
				return CreateObject('PHP.xmlrpcresp',
					CreateObject('PHP.xmlrpcval', $buffer, 'base64')
				);
			} else {
				while (!feof($fp)) {
					$buffer .= fgets($fp, 4096);
				}
				fclose($fp);
				return CreateObject('PHP.xmlrpcresp',
					CreateObject('PHP.xmlrpcval', $buffer, 'base64')
				);
			} // end of checking for convert_to_jpeg
		}
	} // end method get

} // end class Images

?>
