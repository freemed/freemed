<?php
	// $Id$
	// $Author$

// Class: FreeMED.Remitt
//
//	Communication and document creation class for interfacing with
//	a REMITT server.
//
class Remitt {

	var $ref; // references hash
	var $_connection; // XMLRPC connection

	function Remitt ( $server ) {
		$this->protocol = 'http';
		$port = freemed::config_value('remitt_port'); 
		if (!$port) { $port = '7688'; }
		$this->_connection = CreateObject('PHP.xmlrpc_client', '/RPC2', $server, $port);
		// TODO: set credentials ...
		
	} // end constructor

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
	function GetStatus ( $unique ) {
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		$status = $this->_call(
			'Remitt.Interface.GetStatus',
			array(
				CreateObject('PHP.xmlrpcval', $unique, 'string')
			)
		);
		switch ($status) {
			case -1: return NULL;
			case -2: return NULL;
			default: return $status;
		} // end switch status
	} // end method GetStatus

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
	function Login ( $username, $password ) {
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
		$message = CreateObject('PHP.xmlrpcmsg',
			'Remitt.Interface.SystemLogin',
			array(
				CreateObject('PHP.xmlrpcval', $username, 'string'),
				CreateObject('PHP.xmlrpcval', $password, 'string')
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
	function ProcessBill ( $billkey, $render, $transport ) {
		$billkey_hash = unserialize(freemed::get_link_field($billkey, 'billkey', 'billkey'));
		// For now, just use the first ones ... FIXME FIXME FIXME
		$bc = $bs = $ch = 1;
		$xml = $this->RenderXML($billkey_hash['procedures'], $bc, $bs, $ch);
		//print "length of xml = ".strlen($xml)."<br/>\n";
		$this->_connection->SetCredentials(
			$_SESSION['remitt']['sessionid'],
			$_SESSION['remitt']['key']
		);
		//print "calling with ( ..., XSLT, $render, $transport ) <br/>\n";
		$output = $this->_call(
			'Remitt.Interface.Execute',
			array(
				CreateObject('PHP.xmlrpcval', $xml, 'base64'),
				CreateObject('PHP.xmlrpcval', 'XSLT', 'string'),
				CreateObject('PHP.xmlrpcval', $render, 'string'),
				CreateObject('PHP.xmlrpcval', $transport, 'string')
			)
		);
		return $output;
	} // end method ProcessBill

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
	function StoreBillKey ( $billkey ) {
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
	
	// Method: RenderXML
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
	function RenderXML ( $_procedures, $bc=1, $bs=1, $ch=1 ) {
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
		$buffer .= "<remitt doctype=\"request\">\n";

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
		$bcobj = freemed::get_link_rec($bc, 'bcontact');
		$buffer .= "\n\t<!-- billing contact $bc -->\n\n".
			"<billingcontact>\n".
			$this->_tag('name', $bcobj['bcname'], true).
			$this->_addr('address', $bcobj['bcaddr'], $bsobj['bccity'], $bsobj['bsctate'], $bsobj['bczip']).
			$this->_phone('phone', $bcobj['bcphone']).
			$this->_tag('tin', $bcobj['bctin'], true).
			$this->_tag('etin', $bcobj['bcetin'], true).
			"</billingcontact>\n\n";

		// Handle billing service
		$bsobj = freemed::get_link_rec($bs, 'bservice');
		$buffer .= "\n\t<!-- billing service $bs -->\n\n".
			"<billingservice>\n".
			$this->_tag('name', $bsobj['bsname'], true).
			$this->_addr('address', $bsobj['bsaddr'], $bsobj['bscity'], $bsobj['bsstate'], $bsobj['bszip']).
			$this->_phone('phone', $bsobj['bsphone']).
			$this->_tag('tin', $bsobj['bstin'], true).
			$this->_tag('etin', $bsobj['bsetin'], true).
			"</billingservice>\n\n";

		// Handle clearinghouse
		$chobj = freemed::get_link_rec($ch, 'clearinghouse');
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
	} // end method RenderXML

	function _RenderDiagnosis ( $diagnosis ) {
		if (!(strpos($diagnosis, ',') === false)) {
			list ($eoc, $diag) = explode (',', $diagnosis);
		} else {
			// Fudge eoc for non-existing one
			$eoc = 0;
			$diag = $diagnosis;
		}

		// Get records from keys
		$e = freemed::get_link_rec($eoc, 'eoc');
		$d = freemed::get_link_rec($diag, 'icd9');

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

	function _RenderFacility ( $facility ) {
		$f = freemed::get_link_rec($facility, 'facility');
		$buffer .= "<facility id=\"".htmlentities($facility)."\">\n";

		$buffer .= $this->_tag('name', $f['psrname'], true);
		$buffer .= $this->_addr('address', $f['psraddr1'],
			$f['psrcity'], $f['psrstate'], $f['psrzip']);
		$buffer .= $this->_phone('phone', $f['psrphone']);
		$buffer .= $this->_tag('description', $f['psrnote'], true);
		$buffer .= $this->_tag('hcfacode', !$f['psrpos'] ? 11 : freemed::get_link_field($f['psrpos'], 'pos', 'posname'), true);
		$buffer .= $this->_tag('x12code', !$f['psrpos'] ? 11 : freemed::get_link_field($f['psrpos'], 'pos', 'posname'), true);

		$buffer .= "</facility>\n";
		return $buffer;
	} // end method _RenderFacility

	function _RenderInsured ( $insured ) {
		$i = freemed::get_link_rec($insured, 'coverage');
		$buffer .= "<insured id=\"".htmlentities($insured)."\">\n";

		$p = freemed::get_link_rec($i['covpatient'], 'patient');

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

	function _RenderPatient ( $patient ) {
		$p = freemed::get_link_rec($patient, 'patient');
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

	function _RenderPayer ( $payer ) {
		$p = freemed::get_link_rec($payer, 'insco');
		$buffer .= "<payer id=\"".htmlentities($payer)."\">\n";

		$buffer .= $this->_tag('name', $p['insconame'], true);
		$buffer .= $this->_addr('address', $p['inscoaddr1'], $p['inscocity'], $p['inscostate'], $p['inscozip']);
		$buffer .= $this->_phone('phone', $p['inscophone']);
		$buffer .= $this->_tag('x12claimtype', 'HM', true); // fix

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

	function _RenderPractice ( $facility ) {
		$f = freemed::get_link_rec($facility, 'physician');
		$buffer .= "<practice id=\"".htmlentities($facility)."\">\n";

		// loop through payers that are in the system
		foreach ($this->ref['insco'] as $i) {
			// loop through providers
			$_i = freemed::get_link_rec($i, 'insco');
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

	function _RenderProvider ( $provider ) {
		$p = freemed::get_link_rec($provider, 'physician');
		$buffer .= "<provider id=\"".htmlentities($provider)."\">\n";

		$buffer .= $this->_name('name', $p['phylname'], $p['phyfname'], $p['phymname']);
		$buffer .= $this->_addr('address', $p['phyaddr1a'], $p['phycitya'], $p['phystatea'], $p['phyzipa']);
		$buffer .= $this->_phone('phone', $p['phyphonea']);
		$buffer .= $this->_tag('socialsecuritynumber', $p['physsn'], true);
		$buffer .= $this->_tag('tin', $p['physsn'], true);
		$buffer .= $this->_tag('ipn', $p['phyupin'], true);
	
		$buffer .= "</provider>\n";
		return $buffer;		
	} // end method _RenderProvider

	function _RenderProcedure ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');

		$buffer .= "<procedure id=\"".htmlentities($procedure)."\">\n".
			$this->_tag('cpt4code', freemed::get_link_field($p['proccpt'], 'cpt', 'cptcode'), true).
			$this->_tag('cpt5code', freemed::get_link_field($p['proccpt'], 'cpt', 'cptcode'), true).
			$this->_tag('cptcob', '0', true).
			$this->_tag('cptcharges', $p['proccharges'], true).
			$this->_tag('cptcount', 1, true).
			$this->_tag('cptemergency', '0', true).
			$this->_tag('cptepsdt', '0', true).
			$this->_tag('cptmodifier', freemed::get_link_field($p['proccptmod'], 'cptmod', 'cptmod'), true).
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
		$coverage = freemed::get_link_rec($p['proccov'.$covnum], 'coverage');
		$buffer .= $this->_tag('insuredkey', $p['proccov'.$covnum], true);
		//print "Should have added $coverage as coverage<br/>\n";
		$this->_AddDependency('coverage', $p['proccov'.$covnum]);
		$buffer .= $this->_tag('payerkey', $coverage['covinsco'], true);
		$this->_AddDependency('insco', $coverage['covinsco']);

		// Handle second key
		if ($covnum != 0) {
			$covnum++; 
			if ($covnum < 1 or $covnum > 4) { $covnum = 0; }
		}
		$coverage = freemed::get_link_rec($p['proccov'.$covnum], 'coverage');
		$buffer .= $this->_tag('secondpayerkey', $coverage['covinsco'], 'coverage');
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

		// Get id map
		$map = unserialize(freemed::get_link_field($p['procinsco'],
			'insco', 'inscoidmap'));

		// Figure out type of service
		$cptobj = freemed::get_link_rec($p['proccpt'], 'cpt');
		$hash = explode (':', $cptobj['cpttos']);
		if ($hash[$coverage['covinsco']] > 0) {
			$tos = freemed::get_link_field($hash[$coverage['covinsco']], 'tos', 'tosname');
		} else {
			$tos = freemed::get_link_field($cptobj['cptdeftos'], 'tos', 'tosname');
		}

		// Various resubmission codes, etc
		$buffer .=
			$this->_tag('medicaidresubmissioncode', $p['procmedicaidresub'], true).
			$this->_tag('medicaidoriginalreference', $p['procmedicaidresub'], true).
			$this->_tag('hcfalocaluse19', $map[$p['procphysician']]['local19'], true).
			$this->_tag('hcfalocaluse10d', $map[$p['procphysician']]['local10d'], true).
			$this->_tag('amountpaid', $p['procamtpaid'], true).
			$this->_tag('providerkey', $p['procphysician'], true).
			$this->_tag('facilitykey', $p['procpos'], true).
			$this->_tag('practicekey', $p['procphysician'], true).
			$this->_tag('typeofservice', $tos, true).
			'';
		$this->_AddDependency('physician', $p['procphysician']);
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

		$e = freemed::get_link_rec($eoc, 'eoc');
		$buffer .= $this->_tag('ishospitalized', $e['eochospital'] == 1 ? '1' : '0', true);
		$buffer .= $this->_date('dateofhospitalstart', $e['eochosadmdt']);
		$buffer .= $this->_date('dateofhospitalend', $e['eochosdischrgdt']);

		$buffer .= "</procedure>\n";
		return $buffer;
	} // end method RenderProcedure

	function _isPayerX ( $payer, $mod ) {
		$i = freemed::get_link_rec($payer, 'insco');
		$mods = explode (':', $i['inscomod']);
		if (!is_array($mods)) { $mods = array ( $mods ); }
		$q = $GLOBALS['sql']->query('SELECT * FROM insmod '.
			'WHERE insmod = \''.addslashes($mod).'\'');
		$r = $GLOBALS['sql']->fetch_array($q);
		foreach ($mods AS $k => $v) {
			if ($v == $r['id']) {
				return true;
			}
		} 
		return false;
	} // end method _isPayerX

	function _AddDependency($table, $id) {
		// Make sure no duplicates
		$this->ref[$table][$id] = $id;
	}

	function _addr ( $tag, $a, $c, $s, $z) {
		return $this->_tag($tag,
			"\t".$this->_tag('streetaddress', $a, true).
			"\t".$this->_tag('city', $c, true).
			"\t".$this->_tag('state', $s, true).
			"\t".$this->_tag('zipcode', $z, true),
			false);
	} // end method _addr

	function _date ( $name, $sqlvalue ) {
		list ($y, $m, $d) = explode ('-', $sqlvalue);
		if (strlen($y) != 4) { $y = '0000'; $m = '00'; $d = '00'; }
		return $this->_tag($name,
			$this->_tag('year', $y, true).
			$this->_tag('month', $m, true).
			$this->_tag('day', $d, true),
			false);
	} // end method _date

	function _name ( $tag, $l, $f, $m = '' ) {
		return $this->_tag($tag,
			"\t".$this->_tag('last', $l, true).
			"\t".$this->_tag('first', $f, true).
			"\t".$this->_tag('middle', $m, true),
			false);
	} // end method _name

	function _phone ( $tag, $phone ) {
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

	function _tag ( $tag, $value, $inner = false ) {
		return "<".htmlentities($tag).">". ( !$inner ? "\n" : "" ) .
			( $inner ?  trim(htmlentities(stripslashes($value))) : stripslashes($value) ).
			"</".htmlentities($tag).">\n";
	} // end method _tag

	// Method: _autoserialize
	//
	//	Automagically determines what kind of resource this is
	//	supposed to be and creates a PHP.xmlrpcval object to
	//	wrap it in.
	//
	// Parameters:
	//
	//	$mixed - Original object, any type
	//
	// Returns:
	//
	//	PHP.xmlrpcval object
	//
	function _autoserialize ( $mixed ) {
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
				'PHP.xmlrpcval',
				$ele,
				$this->_is_struct($ele) ? 'struct' : 'array'
			);
		} elseif (strpos($mixed, '<') !== false) {
			// This is an *awful* way to check for binary data,
			// and is probably broken in a thousand ways.
			return CreateObject (
				'PHP.xmlrpcval',
				$mixed,
				'base64'
			);
		} else {
			// Otherwise, use PHP auto-typing
			$type = (is_integer($mixed) ? 'int' : gettype($mixed));
			return CreateObject (
				'PHP.xmlrpcval',
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
	function _call ( $method, $parameters = NULL, $debug = false ) {
		// Form proper message object
		if ($parameters != NULL) {
			$message = CreateObject(
				'PHP.xmlrpcmsg',
				$method,
				( is_array($parameters) ? $parameters : array($parameters) )
			);
		} else {
			$message = CreateObject(
				'PHP.xmlrpcmsg',
				$method
			);
		}

		// If we're debugging, we set the debug flag
		$this->_connection->setDebug ( $debug );

		// Dispatch message to server
		$response = $this->_connection->send (
			$message,
			0,
			$this->protocol
		);

		// Deserialize response
		return $response->deserialize();
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
	function _is_struct ( $var ) {
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
