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

// Class: org.freemedsoftware.module.Signatures
class Signatures {

	protected $map = array (
		  'patient' => 1
		, 'module' => 1
		, 'module_field' => 1
		, 'oid' => 1
		, 'user' => 1	
	);

	public function __construct ( ) { } 

	// Method: GetSignature
	//
	//	Connect with device and get signatures and then save into db
	//
	// Parameters:
	//
	//	$patient     - patient id
	//
	//	$module      - module name 
	//
	//	$oid 	     - module record id 
	//
	//	$moduleField - Module Field
	//		
	// Returns:
	//
	//	Output the .
	//
	public function GetSignatureFromDevice ( $patient, $module,$module_field=NULL, $oid=NULL ) {
		//@TODO connect with device to get signature image and store into signature table
		return 1;
	} // end method GetSignatureFromDevice
	
	public function updateOid($id, $oid){
		$query = "UPDATE signature set oid=".$GLOBALS['sql']->quote( $oid )." where id=".$GLOBALS['sql']->quote( $id );		
		$return = $GLOBALS['sql']->query($query);
		return $return?true:false;
	}	
	
	// Method: GetSignatureImageById
	//
	//	Serve signature image back to the browser using
	//	<outputImage>
	//
	// Parameters:
	//
	//	$id - Signature table id
	//
	public function GetSignatureImageById ( $id ) {
		$query = "SELECT data FROM signature WHERE id = ".$GLOBALS['sql']->quote( $id );
		$r = $GLOBALS['sql']->queryRow( $query );
		$this->outputImage($r);
	} // end method GetSignatureImageById
	

	// Method: getSignatureId
	//
	//	Serve signature id w.r.t jobid
	//
	// Parameters:
	//
	//	$job_id - Signature table jobid
	//
	public function getSignatureId ( $job_id ) {
		$query = "SELECT id FROM signature WHERE collector_jobid = ".$GLOBALS['sql']->quote( $job_id );
		$r = $GLOBALS['sql']->queryRow( $query );
		return $r['id'];
	} // end method GetSignatureImageById	
	

	// Method: GetSignatureImage
	//
	//	Serve signature image back to the browser using
	//	<outputImage>
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$module - Module name
	//
	//	$oid - Original record id
	//
	//	$module_field - (optional)
	//
	public function GetSignatureImage ( $patient, $module, $oid=NULL, $module_field=NULL ) {
		$query = "SELECT data FROM signature WHERE patient = ".$GLOBALS['sql']->quote( $patient )." AND module=".$GLOBALS['sql']->quote( $module );
		if ($oid) {
			$query = $query." AND oid=".$GLOBALS['sql']->quote( $oid);
		}
		if ($module_field) {
			$query .= " AND module_field=".$GLOBALS['sql']->quote( $module_field);
		}
		$r = $GLOBALS['sql']->queryRow( $query );
		$this->outputImage($r);
	} // end method GetSignatureImage

	// Method: requestSignature
	//
	//	Create new signature request and dispatch to SHIM workstation.
	//
	// Parameters:
	//
	//	$type - Type of signature that is being recorded.
	//	( 'patient' is for a patient, etc )
	//
	//	$patient -
	//
	//	$module -
	//
	//	$module_field - (optional)
	//
	//	$oid - (optional)
	//	
	// Return:
	//
	//	Hash containg Job identifier on SHIM workstation, or 0 if it fails to enqueue AND signature id.
	//
	public function requestSignature ( $type, $patient, $module, $module_field=NULL , $oid=NULL ) {
		
		switch ( $type ) {
			case 'patient':
			$pObj = CreateObject('org.freemedsoftware.core.Patient', $patient);
			$displayInformation = "Patient : " . $pObj->fullName();
			break;

			default: // die out if unrecognized
			return 0;
			break;
		}

		$sc = $this->getSoapClient( $workstation );

		syslog(LOG_INFO, "SoapClient created, shoehorning parameters");
		$params = (object) array('displayInformation' => $displayInformatiuon);
		syslog(LOG_INFO, "shoehorned parameters");
		$jobId = $sc->requestSignature( $params )->return;
		syslog(LOG_INFO, "shim returned ".$return);

		if (!is_object($this_user)) $this_user = CreateObject('org.freemedsoftware.core.User');
		$query = $GLOBALS['sql']->insert_query(
			'signature',
			array(
				  'patient' => $patient
				, 'module' => $module
				, 'module_field' => $module_field
				, 'oid' => $oid
				, 'collector_location' => ''
				, 'collector_jobid' => $jobId
				, 'user' => $this_user->user_number
			)
		);

		 
		$result = $GLOBALS['sql']->query ( $query );

		$new_id = $GLOBALS['sql']->lastInsertId( 'signature', 'id' );
		
		return array('signature_id' => ''.$new_id
				, 'job_id' => ''.$jobId);
	} // end method requestSignature

	// Method: getJobStatus
	//
	//	Determine status of signature job which has been dispatched to a SHIM
	//	workstation.
	//
	// Parameters:
	//
	//	$jobId - Integer job identified as returned by <requestSignature>.
	//
	// Return:
	//
	//	Textual status
	//	* COMPLETE : job has finished
	//	* PENDING : job is currently executing
	//	* ERROR : job has completed with an error
	//	* NEW : job is queued
	//
	public function getJobStatus ( $jobId ) {
		
		$sc = $this->getSoapClient( $workstation );
		return $sc->getJobStatus( (object) array( 'requestId' => (int) $jobId ) )->return;
	
	} // end method getJobStatus

	// Method: recordSignatureFromWorkstation
	//
	//	Request signature information from a workstation. Should only be called
	//	after a job has been verified as status COMPLETE. Information is updated
	//	in signature table.
	//
	// Parameters:
	//
	//	$signatureId - signature table id
	//
	//	$workstation - workstation id
	//
	//	$jobId - SHIM workstation internal id for job
	//
	protected function recordSignatureFromWorkstation ( $signatureId, $workstation, $jobId ) {
		$sc = $this->getSoapClient( $workstation );
		$item = $sc->getJobItem( (object) array ( 'requestId' => $jobId ) )->return;
		$q = "UPDATE signature SET collector_finished = TRUE, data = ".$GLOBALS['sql']->quote($item->signatureImage)." WHERE id = ".$GLOBALS['sql']->quote((int)$signatureId);
		$GLOBALS['sql']->query( $q );
	} // end method recordSignatureFromWorkstation

	// Method: outputImage
	//
	//	Serve $data as an image back to the browser
	//
	// Parameters:
	//
	//	$data - Binary data
	//	
	protected function outputImage( $data ) {
		if ( ! array( $data ) ) {
			syslog( LOG_INFO, get_class($this)."| could not resolve file for ${id}" );
			return false;
		}
		Header( "Content-Type: image" );
		header( "Content-Length: " .(string)( strlen( $data['data'] ) ) );
		//header( "Content-Transfer-Encoding: binary" );
		print( $data['data'] );
		die();
	} // end method outputImage

	// Method: getSoapClient
	//
	//	Return soap client instance for SHIM workstation services.
	//
	// Parameters:
	//
	//	$workstation - Workstation identifier.
	//
	// Returns:
	//
	//	<SoapClient> instance.
	//
	protected function getSoapClient ( $workstation ) {
		// FIXME : figure out shim workstation address here!
		$url = "http://localhost:8080/shim/services/ShimService?wsdl";
		$username = "Administrator";
		$password = "password";
		$wsdl = $this->getCachedWSDL( );
		syslog(LOG_INFO, "wsdl = $wsdl");
		syslog(LOG_INFO, "creating SoapClient instance to $url with $username/$password");
		syslog(LOG_INFO, "proxy location = ".str_replace('?wsdl', '', $url));
		$sc = new SoapClient( $wsdl, array(
			  'login' => $username
			, 'password' => $password
			, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
			, 'location' => str_replace('?wsdl', '', $url) // force this to work through proxies
		));
		return $sc;
	} // end method getSoapClient

	protected function getCachedWSDL ( ) {
                $url = PHYSICAL_LOCATION . '/data/wsdl/ShimService.wsdl';
                file_put_contents( $cached_name, file_get_contents($url) );
                return $cached_name;
	} // end method getCachedWSDL

} // end class Signatures

?>
