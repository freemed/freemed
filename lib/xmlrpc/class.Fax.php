<?php
	// $Id$
	// $Author$

class Fax {

	// Method: FreeMED.Fax.AddLocalFile
	//
	//	Add a local file to the queue of unfiled faxes in this
	//	FreeMED installation.
	//
	// Parameters:
	//
	//	$filename - Simple filename (no path) of DjVu document in
	//	(freemeddir)/data/fax/unfiled/
	//
	// Returns:
	//
	//	Boolean, true or false based on success of operation
	//
	function AddLocalFile ( $filename ) {
		$result = $GLOBALS['sql']->query(
			$GLOBALS['sql']->insert_query(
				'unfiledfax',
				array (
					'uffdate' => date('Y-m-d'),
					// If we don't trim this, it will
					// cause lots of things to fail
					// later on...
					'ufffilename' => trim($filename)
				)
			)
		);
		syslog(LOG_INFO, "FreeMED.Fax.AddLocalFile| added fax $filename to unfiled faxes");
		return $result;
	} // end method FreeMED.Fax.AddLocalFile

} // end class Fax

?>
