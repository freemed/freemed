<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.Remitt
//
//	Communication and document creation class for interfacing with
//	a REMITT server.
//
class Remitt {

	var $ref; // references hash
	var $_cache; // result cache
	var $_connection; // XMLRPC connection

	public function __construct ( ) {
		$this->protocol = 'http';
		$this->server = freemed::config_value('remitt_server');
		$this->port = freemed::config_value('remitt_port'); 
		if (!$this->port) { $this->port = '7688'; }
		$this->_connection = CreateObject('org.freemedsoftware.core.xmlrpc_client', '/RPC2', $this->server, $this->port);
		// TODO: set credentials ...
		
	} // end constructor

	// Method: GetFileList
	//
	// Parameters:
	//
	//	$type - Type of files
	//
	//	$criteria - Type of criteria (ex: years)
	//
	//	$value - Criteria
	//
	// Returns:
	//
	//	Array of files
	//
	public function GetFileList ( $type, $criteria, $value ) {
		$results = $this->_call(
			'Remitt.Interface.FileList',
			array (
				CreateObject('org.freemedsoftware.core.xmlrpcval', $type, 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $criteria, 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $value, 'string')
			),
			false
		);
		return $results;
	} // end method GetFileList

	// Method: GetProtocolVersion
	//
	//	Retrieves the protocol revision being used by a REMITT
	//	server. Is supported from REMITT version 0.1+.
	//
	// Returns:
	//
	//	Version number.
	//
	public function GetProtocolVersion ( ) {
		if (!$this->GetServerStatus()) { return NULL; }
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		$version = $this->_call( 'Remitt.Interface.ProtocolVersion' );
		return $version;
	} // end method GetProtocolVersion

	// Method: GetServerStatus
	//
	//	Determine if the REMITT server is up.
	//
	// Returns:
	//
	//	true if up, false if not
	//
	public function GetServerStatus ( ) {
		if (@fsockopen($this->server, $this->port, $_err, $_str, 10)) {
			return true;
		} else {
			return false;
		}
	} // end method GetServerStatus

	// Method: GetStatus
	//
	//	Retrieves the current status of a REMITT billing run by
	//	its unique identifier.
	//
	// Parameters:
	//
	//	$unique - Unique identifier
	//
	// Returns:
	//
	//	NULL meaning still in process, or name of result file.
	//
	public function GetStatus ( $unique ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		$status = $this->_call(
			'Remitt.Interface.GetStatus',
			array(
				CreateObject('org.freemedsoftware.core.xmlrpcval', $unique, 'string')
			)
		);
		switch ($status) {
			case -1: return NULL;
			case -2: return NULL;
			default: return $status;
		} // end switch status
	} // end method GetStatus

	// Method: ListOptions
	//
	//	Wrapper for Remitt.Interface.ListOptions
	//
	// Parameters:
	//
	//	$type - Plugin type (Render, Translation, Transport)
	//
	//	$plugin - Name of the plugin to query
	//
	//	$media - (optional) Electronic or Paper. If neither is
	//	specified, defaults to all media forms. (Default is NULL)
	//
	//	$format - (optional) Input XML format. Defaults to
	//	NULL, which disables the qualification.
	//
	// Returns:
	//
	//	Array of available options for the specified plugin
	//
	public function ListOptions ( $type, $plugin, $media = NULL, $format = NULL ) {
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);

		if (!isset($this->_cache['ListOptions'][$type][$plugin]['x_'.$format])) {
			if ($format) {
				$this->_cache['ListOptions'][$type][$plugin]['x_'.$format] = $this->_call(
					'Remitt.Interface.ListOptions',
					array(
						CreateObject('org.freemedsoftware.core.xmlrpcval', $type, 'string'),
						CreateObject('org.freemedsoftware.core.xmlrpcval', $plugin, 'string'),
						CreateObject('org.freemedsoftware.core.xmlrpcval', $format, 'string')
					)
				);
			} else {
				$this->_cache['ListOptions'][$type][$plugin]['x_'.$format] = $this->_call(
					'Remitt.Interface.ListOptions',
					array(
						CreateObject('org.freemedsoftware.core.xmlrpcval', $type, 'string'),
						CreateObject('org.freemedsoftware.core.xmlrpcval', $plugin, 'string')
					)
				);
			}
		}

		// Process into nice form for select widgets
		foreach ($this->_cache['ListOptions'][$type][$plugin]['x_'.$format] AS $k => $v) {
			if (($media == NULL) or ($v['Media'] == $media)) {
				$r[$v['Description']] = $k;
			}
		}
		return $r;
	} // end method ListOptions
			
	// Method: ListPlugins
	//
	//	Wrapper for Remitt.Interface.ListPlugins
	//
	// Parameters:
	//
	//	$type - Plugin type (Render, Translation, Transport)
	//
	// Returns:
	//
	//	Array of available plugins
	//
	public function ListPlugins ( $type ) {
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);

		if (!isset($this->_cache['ListPlugins'][$type])) {
			$this->_cache['ListPlugins'][$type] = $this->_call(
				'Remitt.Interface.ListPlugins',
				array(
					CreateObject('org.freemedsoftware.core.xmlrpcval', $type, 'string')
				)
			);
		}
		return $this->_cache['ListPlugins'][$type];
	} // end method ListPlugins
			
	// Method: ListOutputMonths
	//
	//	Wrapper for Remitt.Interface.GetOutputMonths
	//
	// Parameters:
	//
	//	$year - (optional) Year to list months for
	//
	// Returns:
	//
	//	Hash of years => number of output files available.
	//
	public function ListOutputMonths ( $year = NULL ) {
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		if ($year) {
			$months = $this->_call( 
				'Remitt.Interface.GetOutputMonths',
				array (
					CreateObject('org.freemedsoftware.core.xmlrpcval', $year, 'string'),
				)
			);
		} else {
			$months = $this->_call( 'Remitt.Interface.GetOutputMonths' );
		}
		if (!is_array($months)) { $months = array ( $months ); }
		return $months;
	} // end method ListOutputMonths
			
	// Method: ListOutputYears
	//
	//	Wrapper for Remitt.Interface.GetOutputYears
	//
	// Returns:
	//
	//	Hash of years => number of output files available.
	//
	public function ListOutputYears ( ) {
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		$years = $this->_call( 'Remitt.Interface.GetOutputYears' );
		if (!is_array($years)) { $years = array ( $years ); }
		return $years;
	} // end method ListOutputYears
			
	// Method: Login
	//
	//	Logs into the Remitt server, and stores authentication
	//	data received in the session.
	//
	// Parameters:
	//
	//	$username - Username to be passed to the Remitt server
	//
	//	$password - Password to be passed to the Remitt server
	//
	public function Login ( $username, $password ) {
/*
		// Check for session data
		if ($_SESSION['remitt']) { 
			// Set credentials properly
			$this->_connection->SetCredentials(
				$_SESSION['remitt']['sessionid'],
				$_SESSION['remitt']['key']
			);

			// Skip the rest
			return false; 
		}
*/

		// Otherwise, attempt to establish credentials
		//print "Logging in with $username and $password<br/>\n";
		$this->_connection->SetCredentials($username, $password);
		$message = CreateObject('org.freemedsoftware.core.xmlrpcmsg',
			'Remitt.Interface.SystemLogin',
			array(
				CreateObject('org.freemedsoftware.core.xmlrpcval', $username, 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $password, 'string')
			)
		);
		$response_raw = $this->_connection->send(
			$message,
			0,
			$this->protocol
		);
		$response = $response_raw->deserialize();
		$_SESSION['remitt'] = $response;

		// Set credentials properly
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		//print "Got this: "; print_r($response); print "<br/>\n";

		// Return to program
		return true;
	} // end method Login

	// Method: ProcessBill
	public function ProcessBill ( $billkey, $render, $transport ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		$billkey_hash = unserialize(freemed::get_link_field($billkey, 'billkey', 'billkey'));
		// For now, just use the first ones ... FIXME FIXME FIXME
		$bc = $bs = $ch = 1;
		$xml = $this->RenderPayerXML($billkey_hash['procedures'], $bc, $bs, $ch);
		//print "length of xml = ".strlen($xml)."<br/>\n";
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		//print "calling with ( ..., XSLT, $render, $transport ) <br/>\n";
		$output = $this->_call(
			'Remitt.Interface.Execute',
			array(
				CreateObject('org.freemedsoftware.core.xmlrpcval', $xml, 'base64'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', 'XSLT', 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $render, 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $transport, 'string')
			)
		);
		return $output;
	} // end method ProcessBill

	// Method: ProcessStatement
	public function ProcessStatement ( $procedures ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		// For now, just use the first ones ... FIXME FIXME FIXME
		$xml = $this->RenderStatementXML($procedures);
		//print "length of xml = ".strlen($xml)."<br/>\n";
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		//print "calling with ( ..., XSLT, $render, $transport ) <br/>\n";
		$output = $this->_call(
			'Remitt.Interface.Execute',
			array(
				CreateObject('org.freemedsoftware.core.xmlrpcval', $xml, 'base64'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', 'XSLT', 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', 'statement', 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', 'PDF', 'string')
			)
		);
		return $output;
	} // end method ProcessStatement

	// Method: StoreBillKey
	//
	//	Stores billing data in a temporary key table.
	//
	// Parameters:
	//
	//	$billkey - Data to be serialized
	//
	// Returns:
	//
	//	Table key for billkey
	//
	public function StoreBillKey ( $billkey ) {
		$query = $GLOBALS['sql']->insert_query (
			'billkey',
			array(
				'billkeydate' => date('Y-m-d'),
				'billkey' => serialize($billkey)
			)
		);
		$result = $GLOBALS['sql']->query($query);
		//print "query= $query<br/>\n";
		$id = $GLOBALS['sql']->last_record($result, 'billkey');
		syslog(LOG_INFO, 'Remitt.StoreBillKey| created key '.$id);
		return $id;
	} // end method StoreBillKey
	
	// Method: RenderPayerXML
	//
	//	Renders procedure entries into XML file to be transmitted
	//	to REMITT server.
	//
	// Parameters:
	//
	//	$procedures - Array of procedure id keys to be processed.
	//
	//	$bc - Billing contact id. Defaults to 1.
	//
	//	$bs - Billing service id. Default to 1.
	//
	//	$ch - Clearinghouse id. Defaults to 1.
	//
	// Returns:
	//
	//	Text of XML file.
	//
	public function RenderPayerXML ( $_procedures, $bc=1, $bs=1, $ch=1 ) {
		// Sanitize and fold procedures array
		if (is_array($_procedures)) {
			foreach ($_procedures AS $k => $v) {
				if (is_array($v)) {
					$procedures = array_merge($procedures, $v);
				} else {
					$procedures[] = $v;
				}
			}
		} else {
			$procedures = array ( $_procedures );
		}

		$buffer .= "<?xml version=\"1.0\"?>\n";

		// Create master document element
		$buffer .= "<remitt doctype=\"payerxml\">\n";

		// global information
		$buffer .= "\n\t<!-- global information -->\n\n";
		$buffer .= $this->_tag('global',
			$this->_tag('generator',
				$this->_tag('program', PACKAGENAME, true).
				$this->_tag('version', VERSION, true),
			false).
			$this->_date('currentdate', date('Y-m-d')).
			$this->_tag('currenttime',
				$this->_tag('hour', date('H'), true).
				$this->_tag('minute', date('i'), true),
			false),
			false);

		// Handle billing service
		$bcobj = $GLOBALS['sql']->get_link( 'bcontact', $bc );
		$buffer .= "\n\t<!-- billing contact $bc -->\n\n".
			"<billingcontact>\n".
			$this->_tag('name', $bcobj['bcname'], true).
			$this->_addr('address', $bcobj['bcaddr'], $bsobj['bccity'], $bsobj['bsctate'], $bsobj['bczip']).
			$this->_phone('phone', $bcobj['bcphone']).
			$this->_tag('tin', $bcobj['bctin'], true).
			$this->_tag('etin', $bcobj['bcetin'], true).
			"</billingcontact>\n\n";

		// Handle billing service
		$bsobj = $GLOBALS['sql']->get_link( 'bservice', $bs );
		$buffer .= "\n\t<!-- billing service $bs -->\n\n".
			"<billingservice>\n".
			$this->_tag('name', $bsobj['bsname'], true).
			$this->_addr('address', $bsobj['bsaddr'], $bsobj['bscity'], $bsobj['bsstate'], $bsobj['bszip']).
			$this->_phone('phone', $bsobj['bsphone']).
			$this->_tag('tin', $bsobj['bstin'], true).
			$this->_tag('etin', $bsobj['bsetin'], true).
			"</billingservice>\n\n";

		// Handle clearinghouse
		$chobj = $GLOBALS['sql']->get_link( 'clearinghouse', $ch );
		$buffer .= "\n\t<!-- clearinghouse $ch -->\n\n".
			"<clearinghouse>\n".
			$this->_tag('name', $chobj['chname'], true).
			$this->_addr('address', $chobj['chaddr'], $chobj['chcity'], $chobj['chstate'], $chobj['chzip']).
			$this->_phone('phone', $chobj['chphone']).
			$this->_tag('etin', $chobj['chetin'], true).
			$this->_tag('x12gssenderid', $chobj['chx12gssender'], true).
			$this->_tag('x12gsreceiverid', $chobj['chx12gsreceiver'], true).
			"</clearinghouse>\n\n";

		// Render all objects (from procedures on) to buffer,
		// and loop to check that they all have XML representations
		// in a hash before proceeding to generate

		$_proc = ( is_array($procedures) ? $procedures :
				array($procedures) );

		foreach ($_proc as $proc) {
			if ($proc) {
			$buffer .= "\n\t<!-- procedure $proc -->\n\n".
				$this->_RenderProcedure($proc).
				"\n";
			}
		}

		foreach ($this->ref['patient'] as $pat) {
			if ($pat) {
			$buffer .= "\n\t<!-- patient $pat -->\n\n".
				$this->_RenderPatient($pat).
				"\n";
			}
		}

		// Loop through all providers
		//$this->ref[$table][$id] = $id;
		foreach ($this->ref['physician'] as $prov) {
			if ($prov) {
			$buffer .= "\n\t<!-- provider $prov -->\n\n".
				$this->_RenderProvider($prov).
				"\n";
			}
		}

		foreach ($this->ref['facility'] as $fac) {
			if ($fac) {
			$buffer .= "\n\t<!-- facility $fac -->\n\n".
				$this->_RenderFacility($fac).
				"\n";
			}
		}

		//print "cov ref array = "; print_r($this->ref['coverage']); print "<br/>\n";
		foreach ($this->ref['coverage'] as $cov) {
			//print "Should have rendered $cov as coverage<br/>\n";
			if ($cov) {
			$buffer .= "\n\t<!-- insured $cov -->\n\n".
				$this->_RenderInsured($cov).
				"\n";
			}
		}

		foreach ($this->ref['insco'] as $pay) {
			if ($pay) {
			$buffer .= "\n\t<!-- payer $pay -->\n\n".
				$this->_RenderPayer($pay).
				"\n";
			}
		}

		foreach ($this->ref['practice'] as $prac) {
			$buffer .= "\n\t<!-- practice $prac -->\n\n".
				$this->_RenderPractice($prac).
				"\n";
		}

		foreach ($this->ref['diagnosis'] as $diag) {
			$buffer .= "\n\t<!-- diagnosis $diag -->\n\n".
				$this->_RenderDiagnosis($diag).
				"\n";
		}

		// Closing tag
		$buffer .= "</remitt>\n";

		return $buffer;
	} // end method RenderPayerXML

	// Method: RenderStatementXML
	//
	//	Renders procedure entries into XML file to be transmitted
	//	to REMITT server for patient statement billing
	//
	// Parameters:
	//
	//	$procedures - Array of procedure id keys to be processed.
	//
	// Returns:
	//
	//	Text of XML file.
	//
	public function RenderStatementXML ( $_procedures ) {
		// Sanitize and fold procedures array
		if (is_array($_procedures)) {
			foreach ($_procedures AS $k => $v) {
				if (is_array($v)) {
					$procedures = array_merge($procedures, $v);
				} else {
					$procedures[] = $v;
				}
			}
		} else {
			$procedures = array ( $_procedures );
		}

		$buffer .= "<?xml version=\"1.0\"?>\n";

		// Create master document element
		$buffer .= "<remitt doctype=\"statementxml\">\n";

		// global information
		$buffer .= "\n\t<!-- global information -->\n\n";
		$buffer .= $this->_tag('global',
			$this->_tag('generator',
				$this->_tag('program', PACKAGENAME, true).
				$this->_tag('version', VERSION, true),
			false).
			$this->_date('currentdate', date('Y-m-d')).
			$this->_tag('currenttime',
				$this->_tag('hour', date('H'), true).
				$this->_tag('minute', date('i'), true),
			false),
			false);

		// Render all objects (from procedures on) to buffer,
		// and loop to check that they all have XML representations
		// in a hash before proceeding to generate

		$_proc = ( is_array($procedures) ? $procedures :
				array($procedures) );

		foreach ($_proc as $proc) {
			if ($proc) {
			$buffer .= "\n\t<!-- procedure $proc -->\n\n".
				$this->_RenderProcedure($proc).
				"\n";
			}
		}

		foreach ($this->ref['patient'] as $pat) {
			if ($pat) {
			$buffer .= "\n\t<!-- patient $pat -->\n\n".
				$this->_RenderPatient($pat).
				"\n";
			}
		}

		// Loop through all providers
		//$this->ref[$table][$id] = $id;
		foreach ($this->ref['physician'] as $prov) {
			if ($prov) {
			$buffer .= "\n\t<!-- provider $prov -->\n\n".
				$this->_RenderProvider($prov).
				"\n";
			}
		}

		foreach ($this->ref['facility'] as $fac) {
			if ($fac) {
			$buffer .= "\n\t<!-- facility $fac -->\n\n".
				$this->_RenderFacility($fac).
				"\n";
			}
		}

		foreach ($this->ref['practice'] as $prac) {
			$buffer .= "\n\t<!-- practice $prac -->\n\n".
				$this->_RenderPractice($prac).
				"\n";
		}

		foreach ($this->ref['diagnosis'] as $diag) {
			$buffer .= "\n\t<!-- diagnosis $diag -->\n\n".
				$this->_RenderDiagnosis($diag).
				"\n";
		}

		// Closing tag
		$buffer .= "</remitt>\n";

		return $buffer;
	} // end method RenderStatementXML

	protected function _RenderDiagnosis ( $diagnosis ) {
		if (!(strpos($diagnosis, ',') === false)) {
			list ($eoc, $diag) = explode (',', $diagnosis);
		} else {
			// Fudge eoc for non-existing one
			$eoc = 0;
			$diag = $diagnosis;
		}

		// Get records from keys
		$e = $GLOBALS['sql']->get_link( 'eoc', $eoc );
		$d = $GLOBALS['sql']->get_link( 'icd9', $diag );

		$buffer .= "<diagnosis id=\"".htmlentities($diagnosis)."\">\n";

		$buffer .= $this->_tag('icd9code', $d['icd9code'], true);
		$buffer .= $this->_tag('icd10code', $d['icd10code'], true);
		$buffer .= $this->_tag('relatedtohcfa', $e['eocrelothercomment'], true);
		$buffer .= $this->_tag('isrelatedtoautoaccident', ($e['eocrelauto'] == 'yes'), true);
		$buffer .= $this->_tag('autoaccidentstate', $e['eocrelautostpr'], true);
		$buffer .= $this->_tag('isrelatedtootheraccident', ($e['eocrelother'] == 'yes'), true);
		$buffer .= $this->_tag('isrelatedtoemployment', ($e['eocrelemp'] == 'yes'), true);
		$buffer .= $this->_date('dateofonset', $e['eocstartdate']);
		$buffer .= $this->_date('dateoffirstoccurence', $e['eocstartdate']);

		$buffer .= "</diagnosis>\n";
		return $buffer;
	} // end method _RenderDiagnosis

	protected function _RenderFacility ( $facility ) {
		$f = $GLOBALS['sql']->get_link( 'facility', $facility );
		$buffer .= "<facility id=\"".htmlentities($facility)."\">\n";

		$buffer .= $this->_tag('name', $f['psrname'], true);
		$buffer .= $this->_addr('address', $f['psraddr1'],
			$f['psrcity'], $f['psrstate'], $f['psrzip']);
		$buffer .= $this->_phone('phone', $f['psrphone']);
		$buffer .= $this->_tag('description', $f['psrnote'], true);
		$buffer .= $this->_tag('hcfacode', !$f['psrpos'] ? 11 : freemed::get_link_field($f['psrpos'], 'pos', 'posname'), true);
		$buffer .= $this->_tag('x12code', !$f['psrpos'] ? 11 : freemed::get_link_field($f['psrpos'], 'pos', 'posname'), true);
		$buffer .= $this->_tag('ein', $f['psrein'], true);

		$buffer .= "</facility>\n";
		return $buffer;
	} // end method _RenderFacility

	protected function _RenderInsured ( $insured ) {
		$i = $GLOBALS['sql']->get_link( 'coverage', $insured );
		$buffer .= "<insured id=\"".htmlentities($insured)."\">\n";

		$p = $GLOBALS['sql']->get_link( 'patient', $i['covpatient'] );

		// Handle not self covered
		$buffer .= $this->_tag('relationship', $i['covrel'], true);
		if ($i['covrel'] != 'S') {
			$buffer .= $this->_name('name', $i['covlname'], $i['covfname'], $i['covmname']);
			$buffer .= $this->_addr('address', $i['covaddr1'], $i['covcity'], $i['covstate'], $i['covzip']);
			$buffer .= $this->_phone('phone', $i['covphone']);
			$buffer .= $this->_date('dateofbirth', $i['covdob']);
			$buffer .= $this->_tag('sex', strtolower($i['covsex']), true);
		} else {
			// Self
			$buffer .= $this->_name('name', $p['ptlname'], $p['ptfname'], $p['ptmname']);
			$buffer .= $this->_addr('address', $p['ptaddr1'], $p['ptcity'], $p['ptstate'], $p['ptzip']);
			$buffer .= $this->_phone('phone', $p['pthphone']);
			$buffer .= $this->_date('dateofbirth', $p['ptdob']);
			$buffer .= $this->_tag('sex', strtolower($p['ptsex']), true);
		}

		// Common stuff
		$buffer .= $this->_tag('id', $i['covpatinsno'], true);
		$buffer .= $this->_tag('planname', $i['covplanname'], true);
		$buffer .= $this->_tag('groupname', $i['covplanname'], true);
		$buffer .= $this->_tag('groupnumber', $i['covpatgrpno'], true);
		$buffer .= $this->_tag('isemployed', (!empty($i['covemployer']))+0, true);
		$buffer .= $this->_tag('employername', $i['covemployer'], true);
		$buffer .= $this->_tag('isstudent', !empty($i['covschool']), true);
		$buffer .= $this->_tag('schoolname', $i['covschool'], true);
		$buffer .= $this->_tag('isassigning', ($i['covisassigning'] > 0), true);

		$buffer .= "</insured>\n";
		return $buffer;
	} // end method _RenderInsured

	protected function _RenderPatient ( $patient ) {
		$p = $GLOBALS['sql']->get_link( 'patient', $patient );
		$buffer .= "<patient id=\"".htmlentities($patient)."\">\n";

		$buffer .= $this->_name('name', $p['ptlname'], $p['ptfname'], $p['ptmname']);
		$buffer .= $this->_addr('address', $p['ptaddr1'],
			$p['ptcity'], $p['ptstate'], $p['ptzip']);
		$buffer .= $this->_phone('phone', $p['pthphone']);
		$buffer .= $this->_tag('sex', strtolower($p['ptsex']), true);
		$buffer .= $this->_tag('socialsecuritynumber', $p['ptssn'], true);
		$buffer .= $this->_tag('isdead', ($p['ptdead'] == 1)+0, true);
		$buffer .= $this->_date('dateofbirth', $p['ptdob']);
		$buffer .= $this->_date('dateofdeath', $p['ptdeaddt']);
		$buffer .= $this->_tag('ispregnant', ($p['ptpreg'] == 'pregnant')+0, true);
		$buffer .= $this->_tag('issingle', ($p['ptmarital'] == 'single')+0, true);
		$buffer .= $this->_tag('ismarried', ($p['ptmarital'] == 'married')+0, true);
		$buffer .= $this->_tag('ismaritalotherhcfa', ( ($p['ptmarital'] != 'married') and ($p['ptmarital'] != 'single') )+0, true);
		$buffer .= $this->_tag('isemployed', (
				( $p['ptempl'] != 'y' ) and
				( $p['ptempl'] != 'p' ) and
				( $p['ptempl'] != 'm' ) and
				( $p['ptempl'] != 's' ) )+0, true);
		$buffer .= $this->_tag('isfulltimestudent', 0, true); // fixme
		$buffer .= $this->_tag('isparttimestudent', 0, true); // fixme

		// fixme: coveragecount needs to be in procedure

		$buffer .= $this->_tag('referringprovider', $p['ptrefdoc'], true);
		$this->_AddDependency('physician', $p['ptrefdoc']);

		$buffer .= $this->_tag('account', $p['ptid'], true);

		$buffer .= "</patient>\n";
		return $buffer;
	} // end method _RenderPatient

	protected function _RenderPayer ( $payer ) {
		$p = $GLOBALS['sql']->get_link( 'insco', $payer );
		$buffer .= "<payer id=\"".htmlentities($payer)."\">\n";

		$buffer .= $this->_tag('name', $p['inscoalias'], true);
		$buffer .= $this->_addr('address', $p['inscoaddr1'], $p['inscocity'], $p['inscostate'], $p['inscozip']);
		$buffer .= $this->_phone('phone', $p['inscophone']);
		$buffer .= $this->_tag('x12claimtype', 'HM', true); // fix
		$buffer .= $this->_tag('x12id', $p['inscox12id'], true);

		// IsX functions for payer types
		$x = false;
		$buffer .= $this->_tag('ismedicare', ($this->_isPayerX($payer, 'MA') or $this->_isPayerX($payer, 'MB')) + 0, true);
		$x |= ($this->_isPayerX($payer, 'MA') or $this->_isPayerX($payer, 'MB'));
		$buffer .= $this->_tag('ischampus', ($this->_isPayerX($payer, 'CH') + 0), true);
		$x |= ($this->_isPayerX($payer, 'CH'));
		$buffer .= $this->_tag('ischampusva', ($this->_isPayerX($payer, 'CH') + 0), true);
		$x += ($this->_isPayerX($payer, 'CH'));
		$buffer .= $this->_tag('ismedicaid', ($this->_isPayerX($payer, 'MC') + 0), true);
		$x |= ($this->_isPayerX($payer, 'MC'));
		$buffer .= $this->_tag('isbcbs', ($this->_isPayerX($payer, 'BL') + 0), true);
		$x |= ($this->_isPayerX($payer, 'BL'));
		$buffer .= $this->_tag('isfeca', ($this->_isPayerX($payer, 'FI') + 0), true);
		$x |= ($this->_isPayerX($payer, 'FI'));
		$buffer .= $this->_tag('isotherhcfa', ((!$x) + 0), true);
	
		$buffer .= "</payer>\n";
		return $buffer;		
	} // end method _RenderPayer

	protected function _RenderPractice ( $facility ) {
		$f = $GLOBALS['sql']->get_link( 'physician', $facility );
		$buffer .= "<practice id=\"".htmlentities($facility)."\">\n";

		// loop through payers that are in the system
		foreach ($this->ref['insco'] as $i) {
			// loop through providers
			$_i = $GLOBALS['sql']->get_link( 'insco', $i );
			$map = unserialize($_i['inscoidmap']);
			foreach ($this->ref['physician'] as $p) {
				if ($p and $i) {
				$buffer .= "<id payer=\"".htmlentities($i).
					"\" ".
					"physician=\"".htmlentities($p)."\">".
					htmlentities($map[$p]['id']).
					"</id>\n";
				$buffer .= "<groupid ".
					"payer=\"".htmlentities($i)."\" ".
					"physician=\"".htmlentities($p)."\">".
					htmlentities($map[$p]['group']).
					"</groupid>\n";
				}
			}
		}

		$buffer .= $this->_tag('name', $f['phypracname'], true);
		$buffer .= $this->_addr('address', $f['phyaddr1a'],
			$f['phycitya'], $f['phystatea'], $f['phyzipa']);
		$buffer .= $this->_phone('phone', $f['phyphonea']);
			// FIXME: THESE ARE NOT RIGHT ANYMORE
		$buffer .= $this->_tag('x12id', $f['psrx12id'], true);
		$buffer .= $this->_tag('x12idtype', $f['psrx12idtype'], true);
		$buffer .= $this->_tag('ein', ( $f['phypracein'] ? $f['phypracein'] : $f['physsn'] ), true);

		$buffer .= "</practice>\n";
		return $buffer;
	} // end method _RenderPractice

	protected function _RenderProvider ( $provider ) {
		$p = $GLOBALS['sql']->get_link( 'physician', $provider );
		$buffer .= "<provider id=\"".htmlentities($provider)."\">\n";

		$buffer .= $this->_name('name', $p['phylname'], $p['phyfname'], $p['phymname']);
		$buffer .= $this->_addr('address', $p['phyaddr1a'], $p['phycitya'], $p['phystatea'], $p['phyzipa']);
		$buffer .= $this->_phone('phone', $p['phyphonea']);
		$buffer .= $this->_tag('socialsecuritynumber', $p['physsn'], true);
		$buffer .= $this->_tag('tin', $p['physsn'], true);
		$buffer .= $this->_tag('ipn', $p['phyupin'], true);
		$buffer .= $this->_tag('clia', $p['phyclia'], true);
		$buffer .= $this->_tag('dea', $p['phydea'], true);
	
		$buffer .= "</provider>\n";
		return $buffer;		
	} // end method _RenderProvider

	protected function _RenderProcedure ( $procedure ) {
		$p = $GLOBALS['sql']->get_link( 'procrec', $procedure );

		$buffer .= "<procedure id=\"".htmlentities($procedure)."\">\n".
			$this->_tag('cpt4code', freemed::get_link_field($p['proccpt'], 'cpt', 'cptcode'), true).
			$this->_tag('cpt5code', freemed::get_link_field($p['proccpt'], 'cpt', 'cptcode'), true).
			$this->_tag('cptdescription', freemed::get_link_field($p['proccpt'], 'cpt', 'cptnameint'), true).
			$this->_tag('cptcob', '0', true).
			$this->_tag('cptcharges', $p['proccharges'], true).
			$this->_tag('cptcount', 1, true).
			$this->_tag('cptemergency', '0', true).
			$this->_tag('cptepsdt', '0', true).
			$this->_tag('cptmodifier', freemed::get_link_field($p['proccptmod'], 'cptmod', 'cptmod'), true).
			// Optional extra cpt modifiers
			$this->_tag('cptmodifier2', freemed::get_link_field($p['proccptmod2'], 'cptmod', 'cptmod'), true).
			$this->_tag('cptmodifier3', freemed::get_link_field($p['proccptmod3'], 'cptmod', 'cptmod'), true).
			$this->_tag('cptunits', $p['procunits'], true).
			$this->_tag('weightgrams', '0', true);
		$this->_AddDependency('cpt', $p['proccpt']);

		// Handle "array" of diagnoses/eoc
		$e = explode (':', $p['proceoc']);
		$eoc = $e[0];	
		for ($i=1; $i<=4; $i++) {
			if ($p['procdiag'.$i] > 0) {
				$buffer .= $this->_tag('diagnosiskey', $eoc.','.$p['procdiag'.$i], true); 
				$this->_AddDependency('diagnosis', $eoc.','.$p['procdiag'.$i]);
			}
		}
		$buffer .= $this->_tag('patientkey', $p['procpatient'], true);
		$this->_AddDependency('patient', $p['procpatient']);
	
		// Handle payer key
		switch ($p['proccurcovtp']) {
			case '1': $covnum = 1; break;
			case '2': $covnum = 2; break;
			case '3': $covnum = 3; break;
			case '4': $covnum = 4; break;
			default:  $covnum = 0; break;
		}
		$coverage = $GLOBALS['sql']->get_link( 'coverage', $p['proccov'.$covnum] );
		$buffer .= $this->_tag('insuredkey', $p['proccov'.$covnum], true);
		//print "Should have added $coverage as coverage<br/>\n";
		$this->_AddDependency('coverage', $p['proccov'.$covnum]);
		$buffer .= $this->_tag('payerkey', $coverage['covinsco'], true);
		$this->_AddDependency('insco', $coverage['covinsco']);

		// Get id map (while we still have the primary coverage)
		$map = unserialize(freemed::get_link_field($coverage['covinsco'], 'insco', 'inscoidmap'));

		// Handle second key
		if ($covnum != 0) {
			$covnum++; 
			if ($covnum < 1 or $covnum > 4) { $covnum = 0; }
		}
		$buffer .= $this->_tag('secondinsuredkey', $p['proccov'.$covnum], true);
		$this->_AddDependency('coverage', $p['proccov'.$covnum]);
		$coverage = $GLOBALS['sql']->get_link( 'coverage', $p['proccov'.$covnum] );
		$buffer .= $this->_tag('secondpayerkey', $coverage['covinsco'], true);
		$this->_AddDependency('insco', $coverage['covinsco']);

		// Get other insured key
		switch ($p['proccurcovtp']) {
			case '2': $covnum = 3; break;
			case '3': $covnum = 4; break;
			case '4': $covnum = 0; break;
			case '1': default: $covnum = 2; break;
		}
		$buffer .= $this->_tag('otherinsuredkey', $coverage['covinsco'], 'coverage');
		$this->_AddDependency('insco', $coverage['covinsco']);

		// Figure out type of service
		$cptobj = $GLOBALS['sql']->get_link( 'cpt', $p['proccpt'] );
		$hash = unserialize($cptobj['cpttos']);
		if ($hash[$coverage['covinsco']] > 0) {
			$tos = freemed::get_link_field($hash[$coverage['covinsco']], 'tos', 'tosname');
		} else {
			$tos = freemed::get_link_field($cptobj['cptdeftos'], 'tos', 'tosname');
		}

		// Check for TOS override from procedure record
		if ($p['proctosoverride'] > 0) { $tos = $p['proctosoverride']; }

		// Various resubmission codes, etc
		$buffer .=
			$this->_tag('medicaidresubmissioncode', $p['procmedicaidresub'], true).
			$this->_tag('medicaidoriginalreference', $p['procmedicaidresub'], true).
			$this->_tag('hcfalocaluse19', $map[$p['procphysician']]['local19'], true).
			$this->_tag('hcfalocaluse10d', $map[$p['procphysician']]['local10d'], true).
			$this->_tag('hcfalocaluse24k', $map[$p['procphysician']]['local24k'], true).
			$this->_tag('amountpaid', $p['procamtpaid'], true).
			$this->_tag('providerkey', $p['procphysician'], true).
			$this->_tag('referringproviderkey', $p['procrefdoc'], true).
			$this->_tag('facilitykey', $p['procpos'], true).
			$this->_tag('practicekey', $p['procphysician'], true).
			$this->_tag('typeofservice', $tos, true).
			'';
		$this->_AddDependency('physician', $p['procphysician']);
		$this->_AddDependency('physician', $p['procrefdoc']);
		$this->_AddDependency('practice', $p['procphysician']);
		$this->_AddDependency('facility', $p['procpos']);

		// Authorizations
		$buffer .= $this->_tag('priorauth', freemed::get_link_field($p['procauth'], 'authorizations', 'authnum'), true);
		$this->_AddDependency('authorizations', $p['procauth']);

		// isOutsideLab
		$buffer .= $this->_tag('isoutsidelab', ( $p['proclabcharges'] > 0 ? '1' : '0' ), true);
		$buffer .= $this->_tag('outsidelabcharges', $p['proclabcharges'] + 0, true);

		$buffer .= $this->_date('dateofservicestart', $p['procdt']);
		$buffer .= $this->_date('dateofserviceend', $p['procdt']);
		$buffer .= $this->_tag('aging', (strtotime(date("Y-m-d")) - strtotime($p['procdt'])) / (60 * 60 * 24), true);

		$e = $GLOBALS['sql']->get_link( 'eoc', $eoc );
		$buffer .= $this->_tag('ishospitalized', $e['eochospital'] == 1 ? '1' : '0', true);
		$buffer .= $this->_date('dateofhospitalstart', $e['eochosadmdt']);
		$buffer .= $this->_date('dateofhospitalend', $e['eochosdischrgdt']);

		$buffer .= "</procedure>\n";
		return $buffer;
	} // end method RenderProcedure

	protected function _isPayerX ( $payer, $mod ) {
		$i = $GLOBALS['sql']->get_link( 'insco', $payer );
		$mods = explode (':', $i['inscomod']);
		if (!is_array($mods)) { $mods = array ( $mods ); }
		$r = $GLOBALS['sql']->queryRow('SELECT * FROM insmod WHERE insmod = \''.addslashes($mod).'\'');
		foreach ($mods AS $k => $v) {
			if ($v == $r['id']) {
				return true;
			}
		} 
		return false;
	} // end method _isPayerX

	protected function _AddDependency($table, $id) {
		// Make sure no duplicates
		$this->ref[$table][$id] = $id;
	}

	protected function _addr ( $tag, $a, $c, $s, $z) {
		return $this->_tag($tag,
			"\t".$this->_tag('streetaddress', $a, true).
			"\t".$this->_tag('city', $c, true).
			"\t".$this->_tag('state', $s, true).
			"\t".$this->_tag('zipcode', $z, true),
			false);
	} // end method _addr

	protected function _date ( $name, $sqlvalue ) {
		list ($y, $m, $d) = explode ('-', $sqlvalue);
		if (strlen($y) != 4) { $y = '0000'; $m = '00'; $d = '00'; }
		return $this->_tag($name,
			$this->_tag('year', $y, true).
			$this->_tag('month', $m, true).
			$this->_tag('day', $d, true),
			false);
	} // end method _date

	protected function _name ( $tag, $l, $f, $m = '' ) {
		return $this->_tag($tag,
			"\t".$this->_tag('last', $l, true).
			"\t".$this->_tag('first', $f, true).
			"\t".$this->_tag('middle', $m, true),
			false);
	} // end method _name

	protected function _phone ( $tag, $phone ) {
		$a = substr($phone, 0, 3);
		$n = substr($phone, 3, 7);
		$e = substr($phone, 10, 4);
		return $this->_tag($tag,
			"\t".$this->_tag('country', '1', true).
			"\t".$this->_tag('area', $a, true).
			"\t".$this->_tag('number', $n, true).
			"\t".$this->_tag('extension', $e, true),
			false);
	} // end method _phone

	protected function _tag ( $tag, $value, $inner = false ) {
		return "<".htmlentities($tag).">". ( !$inner ? "\n" : "" ) .
			( $inner ?  trim(htmlentities(stripslashes($value))) : stripslashes($value) ).
			"</".htmlentities($tag).">\n";
	} // end method _tag

	// Method: _autoserialize
	//
	//	Automagically determines what kind of resource this is
	//	supposed to be and creates a org.freemedsoftware.core.xmlrpcval object to
	//	wrap it in.
	//
	// Parameters:
	//
	//	$mixed - Original object, any type
	//
	// Returns:
	//
	//	org.freemedsoftware.core.xmlrpcval object
	//
	protected function _autoserialize ( $mixed ) {
		// Handle already serialized
		if (is_object($mixed) and method_exists($mixed, 'serialize')) {
			return $mixed;
		}

		if (is_array($mixed)) {
			// If dealing with an array, recursively figure out
			@reset($mixed);
			while(list($k, $v) = @each($mixed)) {
				$ele[$k] = $this->_autoserialize($v);
			}
			// Determine if struct or array as we return it
			return CreateObject (
				'org.freemedsoftware.core.xmlrpcval',
				$ele,
				$this->_is_struct($ele) ? 'struct' : 'array'
			);
		} elseif (strpos($mixed, '<') !== false) {
			// This is an *awful* way to check for binary data,
			// and is probably broken in a thousand ways.
			return CreateObject (
				'org.freemedsoftware.core.xmlrpcval',
				$mixed,
				'base64'
			);
		} else {
			// Otherwise, use PHP auto-typing
			$type = (is_integer($mixed) ? 'int' : gettype($mixed));
			return CreateObject (
				'org.freemedsoftware.core.xmlrpcval',
				$mixed,
				$type
			);
		}
	} // end method _autoserialize

	// Method: _call
	//
	//	Call Remitt server with the specified parameters. This
	//	should only be used by internal Remitt methods to
	//	complete abstraction.
	//
	// Parameters:
	//
	//	$method - Method on the Remitt server to call
	//
	//	$parameters - (optional) Array of parameters to call
	//	$method with. Defaults to NULL.
	//
	//	$debug - (optional) Whether debug code should be shown.
	//	Defaults to false.
	//
	// Returns:
	//
	//	Reply to call as PHP variable.
	//
	protected function _call ( $method, $parameters = NULL, $debug = false ) {
		// Form proper message object
		if ($parameters != NULL) {
			$message = CreateObject(
				'org.freemedsoftware.core.xmlrpcmsg',
				$method,
				( is_array($parameters) ? $parameters : array($parameters) )
			);
		} else {
			$message = CreateObject(
				'org.freemedsoftware.core.xmlrpcmsg',
				$method
			);
		}
		if ($debug) { print_r($message); }

		// If we're debugging, we set the debug flag
		$this->_connection->setDebug ( $debug );

		// Dispatch message to server
		$response = $this->_connection->send (
			$message,
			0,
			$this->protocol
		);
		if ($debug) { print_r($response); }

		// Deserialize response
		$d = $response->deserialize();

		// Handle faults
		if ($response->fn) {
			trigger_error(__("XML-RPC Fault:")." ".$d['faultCode']." (".$d['faultString'].")", E_USER_ERROR);
		} else {
			// If there is no fault, return as usual
			return $d;
		}
	} // end method _call

	// Method: _is_struct
	//
	//	Determines if in an array is a structure (associative array)
	//	or a regular array.
	//
	// Parameters:
	//
	//	$var - Variable to be typed
	//
	// Returns:
	//
	//	Boolean, true if $var is an associative array, false if it
	//	is not.
	//
	protected function _is_struct ( $var ) {
		// Catch non-array instance
		if (!is_array($var)) return false;

		// If there are non-numeric keys, it is a structure, otherwise
		// default to false.
		foreach ($var AS $k => $v) {
			if (!is_integer($k)) return true;
		}
		return false;
	} // end method _is_struct

} // end class Remitt

?>
