<?php
	// $Id$
	// $Author$

// Class: FreeMED.Djvu
//
//	Wrapper class to handle Djvu documents.
//
class Djvu {

	var $filename;

	// Constructor: Djvu
	//
	// Parameters:
	//
	//	$filename - Filename of source DjVu file.
	//
	function Djvu ( $filename ) {
		$this->filename = $filename;
		if (!file_exists($filename)) { die ("Djvu: file does not exist \"$filename\""); }
	} // end constructor

	// Method: NumberOfPages
	//
	// Returns:
	//
	//	Number of pages in the current Djvu document.
	//
	function NumberOfPages ( ) {
		$filename = $this->filename;

		// Get basic info dump from file
		$info = `djvudump "$filename" | grep "Document directory"`;

		// If no info, one page
		if (empty($info)) { return 1; }

		// Split down to page number
		$_h1 = explode(", ", $info);
		$_h2 = explode(" ", trim($_h1[1]));

		// Return next to last segment
		return $_h2[count($_h2)-2];
	} // end method NumberOfPages

	// Method: GetPage
	//
	//	Get page image
	//
	// Parameters:
	//
	//	$page - Page number to return
	//
	//	$contents - (optional) Boolean, return the contents instead of the
	//	filename. Defaults to false.
	//
	//	$force_ps - (optional) Boolean, force no JPEG conversion. Defaults
	//	to false.
	//
	// Returns:
	//
	//	Either JPEG image of file in string or name of temporary file.
	//
	function GetPage ( $page, $contents = false, $force_ps = false, $force_rotation = true ) {
		$filename = $this->filename;

		$s = $size."x".$size;

		$_t = tempnam('/tmp', 'fmdjvu');
		$rotate = $force_rotation ? " -rotate 90 " : "";
		if ($force_ps) {
			// No conversion ...
			$t = $_t.'.ps';
			$temp = `djvups -page=$page "$filename" | /usr/bin/convert - ${rotate} "$t"`;
		} else {
			// Force convert to JPEG
			$t = $_t.'.jpg';
			$temp = `djvups -page=$page "$filename" | /usr/bin/convert - ${rotate} "$t"`;
		}

		if ($contents) {
			ob_start();
			readfile($t);
			$c = ob_get_contents();
			ob_end_clean();

			unlink ($_t);
			unlink ($t);
		
			return $c;
		} else {
			unlink ($_t);
			return $t;
		}
	} // end method GetPage

	// Method: GetPageThumbnail
	//
	//	Get textual content of a page thumbnail.
	//
	// Parameters:
	//
	//	$page - Page number
	//
	//	$size - (optional) Maximum dimension of thumbnail. Defaults to 300 (px).
	//
	// Returns:
	//
	//	String containing JPEG thumbnail of specified page.
	//
	function GetPageThumbnail ( $page, $size=300 ) {
		$filename = $this->filename;

		$s = $size."x".$size;

		$_t = tempnam('/tmp', 'fmdjvu');
		$t = $_t.'.jpg';
		$temp = `djvups -page=$page "$filename" | convert - -scale $s "$t"`;
		ob_start();
		readfile($t);
		$contents = ob_get_contents();
		ob_end_clean();

		unlink ($_t);
		unlink ($t);
		
		return $contents;
	} // end method GetPageThumbnail

	// Method: StoredChunks
	//
	//	Get list of chunks contained in the parent DjVu file.
	//
	// Returns:
	//
	//	Array of chunk names.
	//
	function StoredChunks ( ) {
		$filename = $this->filename;

		$raw = `djvm -l "$filename"`;

		// deal by line
		$lines = explode("\n", $raw);

		foreach ($lines as $__garbage => $line) {
			if (eregi('PAGE #', $line)) {
				// Split out contents ....
				$_h1 = explode('#', $line);
				$_h2 = explode(' ', trim($_h1[1]));

				$pages[] = trim($_h2[count($_h2) - 1]);
			}
		}
		return $pages;
	} // end method StoredChunks

	// Method: ToPDF
	//
	//	Convert DjVu document to a PDF document.
	//
	// Parameters:
	//
	//	$to_file - (optional) Boolean, for the function to dump
	//	contents to a file, then return the file name. If false,
	//	returns a string containing the PDF file data. Defaults
	//	to false.
	//
	// Returns:
	//
	//	PDF file data or file name, depending on parameters
	//
	function ToPDF ( $to_file = false ) {
		$filename = $this->filename;

		$t = tempnam('/tmp', 'fmdjvu');
		for ($p=1; $p<=$this->NumberOfPages(); $p++) {
			// No conversion ...
			$temp_cmd = "djvups -page=$p \"$filename\" > \"".$t.".page".$p.".ps\"";
			$out = `$temp_cmd`;
			//syslog(LOG_INFO, "Djvu debug | $temp_cmd");
			
			$params[] = $t.'.page'.$p.'.ps';
		}
	
		// Merge with psmerge
		//$merge_temp_cmd = "psmerge -o".$t.".ps ".join(" ", $params);
		$merge_temp_cmd = "cat ".join(" ", $params)." > ".$t.".ps";
		$out = `$merge_temp_cmd`;
		//syslog(LOG_INFO, "Djvu debug | $merge_temp_cmd");

		// Convert to PDF
		$convert_temp_cmd = "ps2pdf $t.ps $t.pdf";
		$out = `$convert_temp_cmd`;
		//syslog(LOG_INFO, "Djvu debug | $convert_temp_cmd");

		// If this is sent to a file, skip the rest of the logic
		if ($to_file) {
			unlink ($t);
			unlink ($t.'.ps');
			foreach ($params AS $f) { unlink($f); }
			return $t.'.pdf';
		}

		ob_start();
		readfile($t.'.pdf');
		$c = ob_get_contents();
		ob_end_clean();

		unlink ($t);
		unlink ($t.'.ps');
		unlink ($t.'.pdf');
		foreach ($params AS $f) {
			unlink($f);
		}
		
		return $c;
	} // end method ToPDF

} // end class Djvu

?>
