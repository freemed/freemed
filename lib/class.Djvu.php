<?php
	// $Id$
	// $Author$

class Djvu {

	var $filename;

	function Djvu ( $filename ) {
		$this->filename = $filename;
	} // end constructor

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
} // end class Djvu

?>
