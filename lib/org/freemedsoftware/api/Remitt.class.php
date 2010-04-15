<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

	protected $ref; // references hash
	protected $_cache; // result cache

	public function __construct ( ) {
		$this->url = freemed::config_value('remitt_url');
		$this->username = freemed::config_value('remitt_user'); 
		$this->password = freemed::config_value('remitt_pass'); 
	} // end constructor

	// Method: GetFile
	//
	// Parameters:
	//
	//	$type - Category of data
	//
	//	$filename - Filename to retrieve
	//
	//	$serve - (option) Directly serve data, instead of
	//	returning contents. Defaults to false.
	//
	public function GetFile ( $type, $filename, $serve = false ) {
		$sc = $this->getSoapClient( );
		$params = (object) array(
			  'category' => $type
			, 'filename' => $filename
		);
		$out = ( $sc->getFile( $params )->return );
		if ($serve) {
			switch (true) {
				case (substr($serve,0,4) == '%PDF'):
				Header("Content-type: application/x-pdf");
				break;
			}
			print($out);
			die();
		}
		return $out;
	} // end method GetFile

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
		$sc = $this->getSoapClient( );
		$params = (object) array(
			  'category' => $type
			, 'criteria' => $criteria
			, 'value' => $value
		);
		$return = (array)( $sc->getFileList( $params )->return );
		if ($return['filename'] != '') {
			return array($return);
		}
		return $return;
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
		$sc = $this->getSoapClient( );
		$params = (object) array( );
		return ( $sc->getProtocolVersion( $params )->return );
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
		return true; // FIXME, actual testing, please!
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
	//	Integer, indicating status:
	//	* 0: completed
	//	* 1: validation
	//	* 2: render
	//	* 3: translation
	//	* 4: transmission
	//	* 5: unknown
	//
	public function GetStatus ( $unique ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		$sc = $this->getSoapClient( );
		$params = (object) array(
			'jobId' => $unique
		);
		return ( $sc->getStatus( $params )->return );
	} // end method GetStatus

	// Method: GetBulkStatus
	//
	//	Retrieves the current statuses of REMITT billing runs by
	//	their unique identifiers.
	//
	// Parameters:
	//
	//	$uniques - Array of unique identifiers
	//
	// Returns:
	//
	//	Array of integers, indicating status:
	//	* 0: completed
	//	* 1: validation
	//	* 2: render
	//	* 3: translation
	//	* 4: transmission
	//	* 5: unknown
	//
	public function GetBulkStatus ( $uniques ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		$sc = $this->getSoapClient( );
		$params = (object) array(
			'jobIds' => $uniques
		);
		return ( $sc->getBulkStatus( $params )->return );
	} // end method GetBulkStatus

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
		$sc = $this->getSoapClient( );

		if (!isset($this->_cache['ListOptions'][$type][$plugin]['x_'.$format])) {
			if ($format) {
				$params = (object) array(
					  'pluginclass' => $plugin
					, 'qualifyingoption' => ''
				);
				$this->_cache['ListOptions'][$type][$plugin]['x_'.$format] = $sc->getPluginOptions( $params )->return;
			} else {
				$params = (object) array(
					  'pluginclass' => $plugin
					, 'qualifyingoption' => ''
				);
				$this->_cache['ListOptions'][$type][$plugin]['x_'.$format] = $sc->getPluginOptions( $params )->return;
			}
		}

		// Process into nice form for select widgets
		foreach ($this->_cache['ListOptions'][$type][$plugin]['x_'.$format] AS $k => $v) {
			$r[$v] = $v;
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
		$params = (object) array( 'category' => $type );
		if (!isset($this->_cache['ListPlugins'][$type])) {
			$sc = $this->getSoapClient( );
			$this->_cache['ListPlugins'][$type] = 
				( $sc->getPlugins( $params )->return );
		}
		return ( is_array($this->_cache['ListPlugins'][$type]) ? $this->_cache['ListPlugins'][$type] : array( $this->_cache['ListPlugins'][$type] ) );
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
	public function ListOutputMonths ( $year ) {
		$sc = $this->getSoapClient( );
		$params = (object) array(
			'targetYear' => (int) $year
		);
		$months = $sc->getOutputMonths( $params )->return;
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
		$sc = $this->getSoapClient( );
		$params = (object) array( );
		$years = $sc->getOutputYears( $params )->return;
		return $years;
	} // end method ListOutputYears
			
	// Method: ProcessBill
	//
	//	Wraps insertPayload functionality on REMITT server to
	//	send payerxml payload for billing.
	//
	// Parameters:
	//
	//	$billkey -
	//
	//	$render - Render "option", in this case XSLT stylesheet
	//
	//	$transportPlugin -
	//
	//	$transportOption -
	//
	// Returns:
	//
	//	Integer, REMITT internal processing queue number.
	//
	public function ProcessBill ( $billkey, $render, $transportPlugin, $transportOption ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		$billkey_hash = unserialize(freemed::get_link_field($billkey, 'billkey', 'billkey'));
		$xml = $this->RenderPayerXML($billkey, $billkey_hash['procedures'], $billkey_hash['contact'], $billkey_hash['service'], $billkey_hash['clearinghouse']);
		$sc = $this->getSoapClient( );
		$params = (object) array(
			  'inputPayload' => $xml
			, 'renderPlugin' => 'org.remitt.plugin.render.XsltPlugin'
			, 'renderOption' => $render
			, 'transportPlugin' => $transportPlugin
			, 'transportOption' => $transportOption
		);
		return $sc->insertPayload( $params )->return;
	} // end method ProcessBill

	// Method: ProcessStatement
	public function ProcessStatement ( $procedures ) {
		if (!$this->GetServerStatus()) {
			trigger_error(__("The REMITT server is not running."), E_USER_ERROR);
		}
		// For now, just use the first ones ...
		$xml = $this->RenderStatementXML($procedures);
		//print "length of xml = ".strlen($xml)."<br/>\n";
		$sc = $this->getSoapClient( );
		$params = (object) array(
			  'inputPayload' => $xml
			, 'renderPlugin' => 'org.remitt.plugin.render.XsltPlugin'
			, 'renderOption' => 'statement'
			, 'transportPlugin' => 'org.remitt.plugin.transmission.StoreFile'
			, 'transportOption' => ''
		);
		return $sc->insertPayload( $params )->return;
	} // end method ProcessStatement

	// Method: StoreBillKey
	//
	//	Stores billing data in a temporary key table.
	//
	// Parameters:
	//
	//	$billkey - Data to be serialized, hash containing:
	//	* procedures - Array of procedure ids
	//	* clearinghouse - Id of billing clearinghouse (optional)
	//	* contact - Id of billing contact (optional)
	//	* service - Id of billing service (optional)
	//
	// Returns:
	//
	//	Table key for billkey
	//
	public function StoreBillKey ( $billkey ) {
		$bk = CreateObject('org.freemedsoftware.module.BillKey');
		$data['billkeydate']=date( 'Y-m-d' );
		$data['billkey']=serialize( $billkey );
		$data['bkprocs']=$billkey['procedures'];
		$id=$bk->add($data);		
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
	//	$billkey - Unique bill key identifier
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
	public function RenderPayerXML ( $billkey, $_procedures, $bc=1, $bs=1, $ch=1 ) {
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
			$this->_tag('billinguid', $billkey, true).
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
			$this->_tag('name', $bcobj['bcfname'] . ' ' . $bcobj['bclname'], true).
			$this->_addr('address', $bcobj['bcaddr'], $bcobj['bccity'], $bcobj['bcstate'], $bcobj['bczip']).
			$this->_phone('phone', $bcobj['bcphone']).
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
		$buffer .= $this->_tag('npi', $f['psrnpi'], true);
		$buffer .= $this->_tag('taxonomy', $f['psrtaxonomy'], true);
		$buffer .= $this->_tag('clia', $f['psrclia'], true);

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
		$buffer .= $this->_tag('instype', freemed::get_link_field($i['covinstp'], 'covtypes', 'covtpname'), true);
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

	protected function _RenderPractice ( $practice ) {
		$f = $GLOBALS['sql']->get_link( 'practice', $practice );
		$buffer .= "<practice id=\"".htmlentities($practice)."\">\n";

		// loop through payers that are in the system
		foreach ($this->ref['insco'] as $i) {
			// loop through providers
			$_i = $GLOBALS['sql']->get_link( 'insco', $i );
			$map = unserialize($_i['inscoidmap']);
			foreach ($this->ref['physician'] as $p) {
				if ($p and $i) {
				$pid = "";
				$pgroup = "";
				if (is_array($map)) {
					if (is_array($map[$p])) {
						$pid = $map[$p]['id'];
						$pgroup = $map[$p]['group'];
					}
				}
				$buffer .= "<id payer=\"".htmlentities($i).
					"\" ".
					"physician=\"".htmlentities($p)."\">".
					htmlentities($pid).
					"</id>\n";
				$buffer .= "<groupid ".
					"payer=\"".htmlentities($i)."\" ".
					"physician=\"".htmlentities($p)."\">".
					htmlentities($pgroup).
					"</groupid>\n";
				}
			}
		}

		$buffer .= $this->_tag('name', $f['pracname'], true);
		$buffer .= $this->_addr('address', $f['addr1a'],
			$f['citya'], $f['statea'], $f['zipa']);
		$buffer .= $this->_phone('phone', $f['phonea']);
		//$buffer .= $this->_tag('x12id', $f['x12id'], true);
		//$buffer .= $this->_tag('x12idtype', $f['x12idtype'], true);
		$buffer .= $this->_tag('ein', $f['ein'], true);
		$buffer .= $this->_tag('npi', $f['pracnpi'], true);

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
		$buffer .= $this->_tag('npi', $p['phynpi'], true);
	
		$buffer .= "</provider>\n";
		return $buffer;		
	} // end method _RenderProvider

	protected function _RenderProcedure ( $procedure ) {
		$p = $GLOBALS['sql']->get_link( 'procrec', $procedure );

		// Aadded new tags to use in the patient statement MD 05-03-2008
		$query = "SELECT payrecamt, payreccat, payrecsource, payreclink, payrecdt FROM payrec WHERE payrecproc='".$procedure."'"; 
		$pay_result = $GLOBALS['sql']->queryAll($query);

		foreach($pay_result AS $r) {
			$payrecdt     = $r["payrecdt"];
			$payrecamt    = $r["payrecamt"];
			$payreclink   = $r["payreclink"];
			$ins_id = freemed::get_link_field($payreclink, 'coverage', 'covinsco');
			$ins_name = freemed::get_link_field($ins_id, 'insco', 'insconame');
			$payrecsource = $r["payrecsource"];
			$payreccat    = $r["payreccat"];

			if  ($payreccat==11 and $payrecsource==0) {
				$copay = $payrecamt;
				$copay_dt = $payrecdt;
			}
			if ($payreccat==0 and $payrecsource==1) {
				$pri_pay += $payrecamt;
				$pri_pay_dt = $payrecdt;
				$pri_name = $ins_name;
			} elseif  ($payreccat==0 and $payrecsource==2) {
				$sec_pay += $payrecamt;
				$sec_pay_dt = $payrecdt;
				$sec_name = $ins_name;
			} elseif  ($payreccat==0 and $payrecsource==3) {
				$tet_pay += $payrecamt;
				$tet_pay_dt = $payrecdt;
				$tet_name = $ins_name;
			} elseif  (($payreccat==0 or $payreccat==11) and $payrecsource==0) {
				$pat_pay += $payrecamt;
				$pat_pay_dt = $payrecdt;
			}
			elseif  ($payreccat==1 and $payrecsource==5) {
				$mng_adj += $payrecamt;
				$mng_adj_dt = $payrecdt;
			}
			else ;		  
		} // wend
		
		$payhistory=NULL;
		if  ($pat_pay > 0) {
			$payhistory = "Patient $".$pat_pay." (".$pat_pay_dt."); ";
		}
		if  ($pri_pay > 0) {
			$payhistory.= substr($pri_name,0,14)." $".$pri_pay." (".$pri_pay_dt."); ";
		}
		if  ($sec_pay > 0) {
			$payhistory.= substr($sec_name,0,14)." $".$sec_pay." (".$sec_pay_dt."); ";
		}
		if  ($tet_pay > 0) {
			$payhistory.= substr($tet_name,0,6)." $".$tet_pay." (".$tet_pay_dt."); ";
		}
		if  ($mng_adj > 0) {
			$payhistory.= " Doctor adj $".$mng_adj." (".$mng_adj_dt."); ";
		}
		if  ($payhistory==NULL) {
			$payhistory = "No payment has been received for this service";
		} else {
			$payhistory = ucwords("Paid By: ".strtolower($payhistory));
		}

		$buffer .= "<procedure id=\"".htmlentities($procedure)."\">\n".
			$this->_tag('cpt4code', freemed::get_link_field($p['proccpt'], 'cpt', 'cptcode'), true).
			$this->_tag('cpt5code', freemed::get_link_field($p['proccpt'], 'cpt', 'cptcode'), true).
			$this->_tag('cptdescription', freemed::get_link_field($p['proccpt'], 'cpt', 'cptnameint'), true).
			$this->_tag('cptcob', '0', true).
			$this->_tag('cptcharges', $p['procbalorig'], true). //replaced proccharges with procbalorig
			$this->_tag('allowcharges', $p['proccharges'], true). //added new tag
			$this->_tag('comment', $p['proccomment'], true). //added new tag
			$this->_tag('pripay', $pri_pay, true). //added new tags for total paid by primary
			$this->_date('pripaydt', $pri_pay_dt). //added new tag for date pay by primary
			$this->_tag('pripayname', $pri_name, true). //added new tag for primary name
			$this->_tag('patpay', $pat_pay, true). //added new tag for total paid by patient
			$this->_date('patpaydt', $pat_pay_dt). //added new tag for patient pay date
			$this->_tag('copay', $copay, true). //added new tag for copayment
			$this->_date('copaydt', $copay_dt). //added new tag for copayment date
			$this->_tag('payhistory', $payhistory, true). //added new tag for pay history comment
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
		switch ($p['proccurcovtp']) {
			case 2:   $covnum = 1; break;
			case 3:   $covnum = 1; break;
			case 4:   $covnum = 1; break;
			case 1:   $covnum = 2; break;
			default:  $covnum = 0; break;
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

		// Get provider record
		$provider = $GLOBALS['sql']->get_link( 'physician', $p['procphysician'] );

		$hcfalocaluse19 = '';
		$hcfalocaluse10d = '';
		$hcfalocaluse24k = '';
		if (is_array($map)) {
			if (is_array($map[$p['procphysician']])) {

				$hcfalocaluse19 = $map[$p['procphysician']]['local19'];
				$hcfalocaluse10d = $map[$p['procphysician']]['local10d'];
				$hcfalocaluse24k =  $map[$p['procphysician']]['local24k'];
			}
		}

		// Various resubmission codes, etc
		$buffer .=
			$this->_tag('medicaidresubmissioncode', $p['procmedicaidresub'], true).
			$this->_tag('medicaidoriginalreference', $p['procmedicaidresub'], true).
			$this->_tag('hcfalocaluse19', $hcfalocaluse19, true).
			$this->_tag('hcfalocaluse10d', $hcfalocaluse10d, true).
			$this->_tag('hcfalocaluse24k', $hcfalocaluse24k, true).
			$this->_tag('amountpaid', (double) $p['procamtpaid'], true).
			$this->_tag('providerkey', $p['procphysician'], true).
			$this->_tag('referringproviderkey', $p['procrefdoc'], true).
			$this->_tag('facilitykey', $p['procpos'], true).
			$this->_tag('practicekey', $provider['phypractice'], true).
			$this->_tag('typeofservice', $tos, true).
			'';
		$this->_AddDependency('physician', $p['procphysician']);
		$this->_AddDependency('physician', $p['procrefdoc']);
		$this->_AddDependency('practice', $provider['phypractice']);
		$this->_AddDependency('facility', $p['procpos']);

		// Authorizations
		$buffer .= $this->_tag('priorauth', freemed::get_link_field($p['procauth'], 'authorizations', 'authnum'), true);
		$this->_AddDependency('authorizations', $p['procauth']);

		// isOutsideLab
		$buffer .= $this->_tag('isoutsidelab', ( $p['proclabcharges'] > 0 ? '1' : '0' ), true);
		$buffer .= $this->_tag('outsidelabcharges', $p['proclabcharges'] + 0, true);

		$buffer .= $this->_date('dateofservicestart', $p['procdt']);
		switch ( $procdtend ) {
			case '': case '0000-00-00':
			$buffer .= $this->_date('dateofserviceend', $p['procdt']);
			break;

			default:
			$buffer .= $this->_date('dateofserviceend', $p['procdtend']);
			break;
		}
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

	// Method: getCachedWSDL
	//
	// Returns:
	//
	//	Get cache file name for WSDL
	//
	protected function getCachedWSDL ( ) {
		$cached_name = PHYSICAL_LOCATION . '/data/wsdl/remitt.wsdl';

		if ( ! file_exists( $cached_name ) ) {
			syslog(LOG_INFO, "caching wsdl from " . $this->url);
			preg_match('@^([a-z]+://)([A-Za-z0-9:/]+)@i', $this->url, $m);
			$url = $m[1] . $this->username . ':' . $this->password . '@' . $m[2]; 
			syslog(LOG_INFO, "url = $url");
			file_put_contents( $cached_name, file_get_contents($url) );
		}

		return $cached_name;
	} // end method getCachedWSDL

	protected function getSoapClient() {
		$sc = new SoapClient( $this->getCachedWSDL(), array(
			  'login' => $this->username
			, 'password' => $this->password
			, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
			, 'location' => $this->url . "?wsdl"
		));
		return $sc;
	} // end method getSoapClient

} // end class Remitt

?>
