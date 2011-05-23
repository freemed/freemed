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

// Class: org.freemedsoftware.core.Parser_835XML
//
//	REMITT-exported 835 XML parser
//
class Parser_835XML {

	var $options;
	var $message;

	// Method: Parser_835XML constructor
	//
	// Parameters:
	//
	//	$message - Text of message
	//
	//	$options - (optional) Additional options to be passed
	//	to the parser. This is an associative array.
	//
	public function __construct ( $_message, $_options = NULL ) {
		syslog(LOG_INFO, '835XML parser|Created 835XML parser object');
	
		// Assume separator is a pipe
		if (is_array($_options)) {
			$this->options = $_options;
		}
		$this->message = $_message;
	} // end constructor Parser_835XML
	
	// Method: Handle
	//
	//	Method to be called by other parts of the program to execute
	//	the action associated with the provided message type.
	//
	// Returns:
	//
	//	Output of the specified handler.
	//
	public function Handle() {
		syslog(LOG_INFO, '835XML parser|Entered Handle()');
		if ($this->options['testmode']) {
			$this->debug("Using 'testmode'");
		}
		$xml = simplexml_load_string( $this->message );
		foreach ( $this->enforceXmlArray( $xml->payers->payer ) AS $payer ) {
			$this->debug("Processing payer $payer");
			$this->ProcessPayer( $payer );
		}
		syslog(LOG_INFO, '835XML parser|Leaving Handle()');
		return 1; // OKAY
	} // end method Handle

	protected function debug( $s ) {
		if ($this->options['debug']) {
			print $s . PHP_EOL;
		}
	} // end method debug

	protected function enforceXmlArray( $src ) {
		if ( is_array( $src ) ) {
			return $src;
		} else {
			return array( $src );
		}
	} // end method enforceXmlArray

	protected function ProcessPayer( $payer ) {
		switch ( $payer['idQualifier'] ) {

			case 'EO':
			// EO = employer id (not actually supported at the moment)
			$this_payer = $this->GetPayerByField( 'inscox12id', $payer['idNumber'] );
			break; // EO

			case 'XV':
			// XV = Health Care Financing Administration National Plan ID
			$this_payer = $this->GetPayerByField( 'inscox12id', $payer->idNumber );
			break; // XV

			default:
			$this_payer = NULL;
			break;

		}
		if ($this_payer == NULL) {
			syslog(LOG_ERR, "Could not identify " . $payer['idNumber'] . " (" . $payer['idQualifier'] . ")");
			if ( $this->options['testmode'] ) {
				$this_payer = array (
					  'insconame' => 'TEST INSCO'
					, 'id' => 1
				);
			} else {
				return false;
			}
		}

		// Process payer
		foreach ( $this->enforceXmlArray( $payer->payees->payee ) AS $payee ) {
			$this->ProcessPayee( $this_payer, $payee );
		}
	} // end method ProcessPayer

	protected function ProcessPayee( $payer, $payee ) {
		switch ( $payee['idQualifier'] ) {

			case 'FI':
			// FI = Federal Taxpayer's ID (SSN)
			$this_payer = $this->GetPayeeByField( 'physsn', $payee['idNumber'] );
			break; // EO

			case 'XV':
			// XX = Health Care Financing Administration National Provider ID
			$this_payee = $this->GetPayeeByField( 'phynpi', $payee['idNumber'] );
			break; // XV

			default:
			$this_payee = NULL;
			break;

		}
		if ($this_payee == NULL) {
			syslog(LOG_ERR, "Could not identify " . $payee['idNumber'] . " (" . $payee['idQualifier'] . ")");
			if ( $this->options['testmode'] ) {
				$this_payee = array (
					  'phylname' => 'LAST'
					, 'phyfname' => 'FNAME'
					, 'id' => 1
				);
			} else {
				return false;
			}
		}

		// Process payer
		foreach ( $payee->providerClaimGroups->providerClaimGroup AS $group ) {
			foreach ( $group->claimPayments->claimPayment AS $payment ) {
				$this->ProcessPayment( $payer, $this_payee, $payment );
			}
			foreach ( $group->claimAdjustments->claimAdjustment AS $adjustment ) {
				$this->ProcessAdjustment( $payer, $this_payee, $adjustment);
			}
		}
	} // end method ProcessPayee

	protected function ProcessAdjustment( $payer, $payee, $adjustment ) {
		$statusCode = $adjustment->claimCode;
		$note = $adjustment->claimStatus;
	} // end method ProcessAdjustment

	protected function ProcessPayment( $payer, $payee, $payment ) {
		$statusCode = $payment->claimCode;
		$note = $payment->claimStatus;
		$amount = $payment->claimPaidAmount;
	} // end method ProcessPayment

	// Method: FindPatient
	//
	// Parameters:
	//
	//	$payerId - insco.id field to identify payer
	//
	//	$qualifier - 835 qualifier (34, HN, etc)
	//
	//	$identifier - Identification number
	//
	// Returns:
	//
	//	Associative array containing patient record.
	//
	protected function FindPatient( $payerId, $qualifier, $identifier ) {
		switch ( $qualifier ) {
			case '34': // 34 = Social Security Number
			$query = "SELECT * FROM patient WHERE ptssn = " . $GLOBALS['sql']->quote( $identifier ) . " AND ptarchive = 0 LIMIT 1";
			break; // 34

			case 'MI': // MI = Member Identification Number
			$query = "SELECT pt.* FROM patient pt LEFT OUTER JOIN coverage c ON c.covpatient = pt.id WHERE covinsco=" . $GLOBALS['sql']->quote( $payerId ) . " AND c.covinsno = " . $GLOBALS['sql']->quote( $identifier ) . " AND pt.ptarchive = 0 LIMIT 1";
			break; // MI

			default:
			syslog(LOG_ERROR, "FindPatient| Unknown qualifier '$qualifier'");
			break;
		}

		$this->debug( $sql );
		$results = $GLOBALS['sql']->queryRow( $query );
		return $results;
	} // end method FindPatient

	protected function GetPayeeByField( $field, $idNumber ) {
		$sql = "SELECT * FROM physician WHERE ${field} = " . $GLOBALS['sql']->quote( $idNumber ) . " LIMIT 1";
		$this->debug( $sql );
		$result = $GLOBALS['sql']->queryRow( $sql );
		return $result;
	} // end method GetPayeeByField

	protected function GetPayerByField( $field, $idNumber ) {
		$sql = "SELECT * FROM insco WHERE ${field} = " . $GLOBALS['sql']->quote( $idNumber ) . " LIMIT 1";
		$this->debug( $sql );
		$result = $GLOBALS['sql']->queryRow( $sql );
		return $result;
	} // end method GetPayerByField

} // end class Parser_835XML

?>
