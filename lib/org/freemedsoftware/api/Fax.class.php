<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// Class: org.freemedsoftware.api.Fax
//
//	Fax handling functions.
//
class Fax {

	// Method: AddLocalFile
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
				'unfileddocuments',
				array (
					'uffdate' => date('Y-m-d'),
					// If we don't trim this, it will
					// cause lots of things to fail
					// later on...
					'ufffilename' => trim($filename)
				)
			)
		);
		syslog(LOG_INFO, "Fax.AddLocalFile| added fax $filename to unfiled faxes");
		return $result;
	} // end method AddLocalFile

} // end class Fax

?>
