<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

LoadObjectDependency( 'org.freemedsoftware.core.FPDF' );

// Class: org.freemedsoftware.core.FPDF_Report
//
//	FPDF derived class for report generation
//
class FPDF_Report extends FPDF {

	private $_keys;
	private $_maxlength;
	private $_totalsize;
	private $_reportName;
	private $_aSize;
	private $_timestamp;

	// Method: LoadData
	//
	//	Import a query from an SQL statement
	//
	// Parameters:
	//
	//	$reportName - Textual name of this report.
	//
	//	$query - SQL query text
	//
	public function LoadData( $reportName, $query ) {
		$q = $GLOBALS['sql']->queryAll( $query );

		$notset = false;
		foreach ($q AS $r) {
			// Unset integer keys
			foreach($r AS $k=>$v) {
				if (is_int($k)) {
					unset($r[$k]); 
				}
			}

			// Get all keys if we don't have them already
			if (!$notset) {
				foreach ($r AS $k => $v) { $keys[] = $k; }
				$notset = true;
			}

			// Get maximum field lengths
			foreach ( $r AS $k => $v ) {
				if ( $this->_maxlength[ $k ] < strlen( $v ) ) {
					$this->_maxlength[ $k ] = strlen( $v );
				}
			}

			// Add results
			$results[] = $r;			
		}

		// Make sure that keys aren't longer than values...
		foreach ( $keys AS $v ) {
			if ( $this->_maxlength[ $v ] < strlen( $v ) ) {
				$this->_maxlength[ $v ] = strlen( $v );
			}
		}

		// Get aggregate total of lengths
		$this->_totalsize = 0;
		foreach ( $this->_maxlength AS $k => $v ) {
			$this->_totalsize += $v;
		}

		$this->_keys  = $keys;
		$this->_cache = $results;
		$this->_reportName = $reportName;
	} // end method LoadData

	// Method: BuildTable
	//
	//	Internal method to construct table
	//
	protected function BuildTable ( ) {
		if ( !is_array( $this->_cache ) || !is_array( $this->_keys ) ) {
			return false;
		}

		$this->SetFont( 'Arial', '', 10 );
		foreach ( $this->_cache AS $results ) {
			foreach ( $results AS $k => $v ) {
				$this->Cell( $this->_aSize[ $k ], 6, $v, 'LR' );
			}
			$this->Ln( );
		}
	} // end method BuildTable

	protected function TableHeader ( ) {
		// Create adjustment table based on maximum length
		$ratio = ( ( $this->w - ($this->lMargin*2) ) / $this->_totalsize );
		foreach ( $this->_keys AS $v ) {
			$this->_aSize[ $v ] = ( $this->_maxlength[ $v ] * $ratio );
		}

		$this->SetFont( 'Arial', 'B', 10 );
		for ( $i=0; $i < count( $this->_keys ); $i++ ) {
			$this->Cell( $this->_aSize[ $this->_keys[$i] ], 7, $this->_keys[ $i ], 1, 0, 'C' );
		}
		$this->Ln( );
	}

	public function Header ( ) {
		$this->SetFont( 'Arial', 'B', 12 );
		$this->Cell( 0, 7, $this->_reportName, 'T', 1, 0, 'C' );
		$this->SetFont( 'Arial', 'I', 8 );
		$this->Cell( 0, 5, __("Generated on").' '.$this->_timestamp.' / '.
			__("Page").' '.$this->page, 'B', 1, 0, 'C' ); $this->Ln( );
		$this->TableHeader();
	}

	public function Footer ( ) {
		for ( $i=0; $i < count( $this->_keys ); $i++ ) {
			$this->Cell( $this->_aSize[ $this->_keys[$i] ], 0, '', 'T', 0, 'C' );
		}
	}

	// Method: Export
	//
	//	Export as CSV and die
	//
	public function Export ( ) {
		if (!isset($this->_cache)) { return false; }

		Header ("Content-type: application/x-pdf");
		Header ("Content-Disposition: inline; filename=\"".mktime().".pdf\"");

		$this->_timestamp = date( 'r' );

		$this->DefOrientation = 'L';
		$this->AddPage( );
		$this->BuildTable( );
		$this->Output( );

		die();
	} // end method Export

} // end class FPDF_Report

?>
