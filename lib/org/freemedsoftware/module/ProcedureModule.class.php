<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class ProcedureModule extends EMRModule {

	var $MODULE_NAME = "Procedure";
	var $MODULE_VERSION = "0.5.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "e8191c41-cd13-4297-8271-f95e1d1ddd85";

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name  = "procrec";
	var $record_name = "Procedure";
	var $patient_field = "procpatient";
	var $proc_fields = array(
		"procpatient",
		"proceoc",
		"proccpt",
		"proccptmod",
		"proccptmod2",
		"proccptmod3",
		"procdiag1",
		"procdiag2",
		"procdiag3",
		"procdiag4",
		"proccharges",      
		"procunits",
		"procvoucher",
		"procphysician",
		"procdt",		
		"procpos",
		"proccomment",
		"procbalorig",
		"procbalcurrent",	
		"procamtpaid",	
		"procbilled",
		"procbillable",
		"procauth",
		"proccert",
		"procrefdoc",
		"procrefdt",			
		"proccurcovid",     
		"proccurcovtp",    
		"proccov1",       
		"proccov2",      
		"proccov3",     
		"proccov4",
		"procclmtp",
		'procmedicaidref',
		'procmedicaidresub',
		'proclabcharges',
		'procslidingscale',
		'proctosoverride',
		'user'
	);    

	function ProcedureModule () {
		// Set vars for patient management
		$this->list_view = array (
			__("Date")    => "procdt",
			__("CPT")     => "proccpt:cpt:cptcode",
			__("Comment") => "proccomment",
			__("Charges") => "_charges"
		);
		$this->summary_query = array (
			"ROUND(procbalorig,2) AS _charges"
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'proceoc');
		$this->_SetAssociation('RulesModule');
		$this->_SetMetaInformation('RuleType', "Billing"); // __("Billing")
		$this->_SetMetaInformation('RuleInterface', "RuleInterface");

		$this->acl = array ( 'emr', 'bill' );

		// Call parent constructor
		parent::__construct( );
	} // end constructor 

	protected function add_pre ( &$data ) {
		$data['proccurcovtp'] = ( ($data['proccov4']) ? WORKCOMP : 0 );
		$data['proccurcovtp'] = ( ($data['proccov3']) ? TERTIARY : 0 );
		$data['proccurcovtp'] = ( ($data['proccov2']) ? SECONDARY : 0 );
		$data['proccurcovtp'] = ( ($data['proccov1']) ? PRIMARY : 0 );
		$data['proccurcovid'] = ( ($data['proccov4']) ? $data['proccov4'] : 0 );
		$data['proccurcovid'] = ( ($data['proccov3']) ? $data['proccov3'] : 0 );
		$data['proccurcovid'] = ( ($data['proccov2']) ? $data['proccov2'] : 0 );
		$data['proccurcovid'] = ( ($data['proccov1']) ? $data['proccov1'] : 0 );
		$data['proccharges'] = $data['procbalorig'];
		$data['procbalcurrent'] = $data['procbalorig'];
		$data['procamtpaid'] = 0;
		$data['procbilled'] = 0;
		$data['user'] = freemed::user_cache()->user_number;
	} // end add_pre

	function add_post ( $id, &$data ) {
		// Add to Claimlog
		$claimlog = CreateObject('org.freemedsoftware.api.ClaimLog');
		$claimlog->log_event(
			$id,
			array (
				'action' => __("Create"),
				'comment' => __("Procedure created")
			)
		);

		// Commit to ledger
		$query = $GLOBALS['sql']->insert_query(
			'payrec',
			array(
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => '0000-00-00',
				'payrecpatient' => $data['patient'],
				'payrecdt' => $data['procdt'],
				'payreccat' => PROCEDURE,
				'payrecproc' => $id,
				'payrecsource' => $data['proccurcovtp'],
				'payreclink' => $data['proccurcovid'],
				'payrectype' => '0',
				'payrecnum' => '',
				'payrecamt' => $data['procbalorig'],
				'payrecdescrip' => $data['proccomment'],
				'payreclock' => 'unlocked'
			)
		);
		$result = $GLOBALS['sql']->query ($query);

		// updating patient diagnoses
		$query = $GLOBALS['sql']->update_query(
			'patient',
			array(
				'ptdiag1' => $data['procdiag1'],
				'ptdiag2' => $data['procdiag2'],
				'ptdiag3' => $data['procdiag3'],
				'ptdiag4' => $data['procdiag4']
			), array ('id' => $data['patient'])
		);
		$result = $GLOBALS['sql']->query( $query );

		// Deduct from authorization, if there is one
		// specified
		if ($data['procauth'] > 0) {
			$a = CreateObject('org.freemedsoftwae.core.Authorizations');
			// Check for valid first
			if ( $a->valid( $data['procauth'], $data['procdt'] ) ) {
				if ($a->use_authorization($_REQUEST['procauth'])) {
				} else {
				} // end checking if use auth success
			} else {
				// If not valid, display error
			} // end checking for valid
		} // end checking for use auth
	} // end method add_post
	
	protected function mod_pre ( &$data ) {	
		$data['proccurcovtp'] = ( ($data['proccov4']) ? WORKCOMP : 0 );
		$data['proccurcovtp'] = ( ($data['proccov3']) ? TERTIARY : 0 );
		$data['proccurcovtp'] = ( ($data['proccov2']) ? SECONDARY : 0 );
		$data['proccurcovtp'] = ( ($data['proccov1']) ? PRIMARY : 0 );
		$data['proccurcovid'] = ( ($data['proccov4']) ? $data['proccov4'] : 0 );
		$data['proccurcovid'] = ( ($data['proccov3']) ? $data['proccov3'] : 0 );
		$data['proccurcovid'] = ( ($data['proccov2']) ? $data['proccov2'] : 0 );
		$data['proccurcovid'] = ( ($data['proccov1']) ? $data['proccov1'] : 0 );
		$data['procbalcurrent'] = $data['procbalorig'];
		$data['procamtpaid'] = 0;
		$data['procbilled'] = 0;
		$data['user'] = freemed::user_cache()->user_number;
				
		// Save old record for authorization update
		$tmp = $GLOBALS['sql']->get_link( 'procrec', $data['id'] );
		$data['procauthsaved'] = $tmp['procauth'];
	} // end method mod_pre

	protected function mod_post ( &$data ) {
		$query = $GLOBALS['sql']->update_query(
			'payrec',
			array(
				'payrecdtmod' => date('Y-m-d'),
				'payrecdt' => $data['procdt'],
				'payrecsource' => $data['proccurcovtp'],
				'payreclink' => $data['proccurcovid'],
				'payrectype' => '0',
				'payrecnum' => '',
				'payrecamt' => $data['procbalorig'],
				'payrecdescrip' => $data['proccomment'],
				'payreclock' => 'unlocked'
			),
			array (
				'payrecproc' => $data['id'],
				'payreccat' => PROCEDURE,
				'payrectype' => '0'
			)
		);
		$result = $GLOBALS['sql']->query( $query );

		// updating patient diagnoses
		$query = $GLOBALS['sql']->update_query(
			'patient',
			array(
				'ptdiag1' => $data['procdiag1'],
				'ptdiag2' => $data['procdiag2'],
				'ptdiag3' => $data['procdiag3'],
				'ptdiag4' => $data['procdiag4']
			), array ('id' => $data['patient'])
		);
		$result = $GLOBALS['sql']->query( $query );

		// Check if authorization changed
		if ($data['procauth'] != $data['procauthsaved']) {
			$a = CreateObject('org.freemedsoftware.core.Authorizations');
			// Try to remove old authorization
			if ($data['procauthsaved'] > 0) {
				$a->replace_authorization($data['procauthsaved']);
			}
			if ($data['procauth'] > 0) {
				if ( $a->valid( $data['procauth'], $data['procdt'] ) ) {
					if ( $a->use_authorization( $data['procauth'] ) ) {
					} else {
					} // end checking if use auth success
				} else {
					// If not valid, display error
				} // end checking for valid
			} // end seeing if something should be added
		} // end checking for updated authorization
	} // end mod_post
	
	protected function del_pre ( $id ) {	
		$query = "DELETE FROM payrec WHERE payrecproc='".addslashes($id)."'";
		$result = $GLOBALS['sql']->query ($query);
		return true;
	} // end method del_pre

	// Method: GetAuthorizations
	//
	//	Get authorizations for specified patient ID
	//
	// Parameters:
	//
	//	$patid - Patient ID number
	//
	// Returns:
	//
	//	Hash of authorizations
	//
	public function GetAuthorizations ( $patid ) {
		$res = $GLOBALS['sql']->queryAll ("SELECT * FROM authorizations WHERE authpatient='".addslashes($patid)."'");
		foreach ( $res AS $r ) {
			$auth["${r['authdtbegin']} - ${r['authdtend']} (${r['authvisitsremain']} / ${r['authvisits']})"] = $r['id'];
		} // end foreach
		return $auth;
	} // end method GetAuthorizations

	// Method: CalculateCharge
	//
	//	Calculate amount of charge in system
	//
	// Parameters:
	//
	//	$covid - Record id for active coverage
	//
	//	$procunits - Number of units of specified procedure
	//
	//	$cptid - Record id for procedural code
	//
	//	$phyid - Record id for provider
	//
	//	$patid - Record id for patient
	//
	// Returns:
	//
	//	Calculated amount of charge.
	//
	public function CalculateCharge ( $covid, $procunits, $cptid, $phyid, $patid ) {
		global $display_buffer;
		// id of coverage record, cpt record, physician record
		// and patient record

		// charge calculation routine lies here
		//   charge = units * relative_value(cpt) * 
		//            base_value(physician/provider)
		//   standard_fee = standard_fee [insurance co] unless 0 then
		//                = default_standard_fee
		//  (we display "standard fee" as what the bastards (insurance companies)
		//   are actually going to pay -- be sure to check for divide by zeros...)

		// step one:
		//   calculate the standard fee
		//if ($covid==0)
		//		return 0;
		$primary = CreateObject('org.freemedsoftware.core.Coverage', $covid);
		$insid = $primary->local_record[covinsco];

		$cpt_code = $GLOBALS['sql']->get_link( 'cpt', $cptid ); // cpt code
		$cpt_code_fees = unserialize($cpt_code["cptstdfee"]);
		$cpt_code_stdfee = $cpt_code_fees[$insid]; // grab proper std fee
		if (empty($cpt_code_stdfee) or ($cpt_code_stdfee==0))
		$cpt_code_stdfee = $cpt_code["cptdefstdfee"]; // if none, do default
		$cpt_code_stdfee = bcadd ($cpt_code_stdfee, 0, 2);

		// step two:
		//   grab the relative value from the CPT db
		$relative_value = $cpt_code["cptrelval"];
		if ($debug) $display_buffer .= " (relative_value = \"$relative_value\")\n";

		// step three:
		//   calculate the base value
		$internal_type  = $cpt_code ["cpttype"]; // grab internal type
		if ($debug) 
		$display_buffer .= " (inttype = $internal_type) (procphysician = $procphysician) ";
		$this_physician = $GLOBALS['sql']->get_link( 'physician', $physid );
		$charge_map     = fm_split_into_array($this_physician ["phychargemap"]);
		$base_value     = $charge_map [$internal_type];
		if ($debug) $display_buffer .= "<BR>base value = \"$base_value\"\n";

		// step four:
		//   check for patient discount percentage
		$this_patient = CreateObject('org.freemedsoftware.core.Patient', $patid);
		$percentage = $this_patient->local_record["ptdisc"];
		if ($percentage>0) { $discount = $percentage / 100; }
		else              { $discount = 0;                 }
		if ($debug) $display_buffer .= "<BR>discount = \"$discount\"\n";

		// step five:
		//   calculate formula...
		$charge = ($base_value * $procunits * $relative_value) - $discount; 
		if ($charge == 0)
		$charge = $cpt_code_stdfee;
		if ($debug) $display_buffer .= " (charge = \"$charge\") \n";

		// step six:
		//   adjust values to proper precision
		$charge = bcadd ($charge, 0, 2);
		return $charge;
	} // end method CalculateCharge

	// Method: RuleInterface
	//
	//	Associated method to provide interface for billing rules
	//
	// Parameters:
	//
	//	$clause - 'if' or 'then'
	//
	// Returns:
	//
	//	Array with the following array type as each element:
	//	* [0] - field name
	//	* [1] - equivalence / assignment choices (array)
	//	* [2] - widget
	//
	function RuleInterface ( $type ) {
		switch ( $type ) {
			case 'if':
			$if[] = array (
				'procpos',
				array ( '=', '!=' ),
				module_function('FacilityModule', 'widget', array('procpos')),
				__("Facility")
			);
			$if[] = array (
				'proccpt',
				array ( '=', '!=' ),
				module_function('CptMaintenance', 'widget', array('proccpt')),
				__("CPT Code")
			);
			$if[] = array (
				'proccptmod',
				array ( '=', '!=' ),
				module_function('CptModifiersMaintenance', 'widget', array('proccptmod')),
				__("CPT Modifier")
			);
			$if[] = array (
				'proccptmod',
				array ( '=', '!=' ),
				module_function('CptModifiersMaintenance', 'widget', array('proccptmod2')),
				__("CPT Modifier")." 2"
			);
			$if[] = array (
				'proccptmod',
				array ( '=', '!=' ),
				module_function('CptModifiersMaintenance', 'widget', array('proccptmod3')),
				__("CPT Modifier")." 3"
			);
			return $if;
			break;

			case 'then':
			$then[] = array (
				'proccharges',
				array ( '=' ),
				html_form::text_widget('proccharges', 20),
				__("Charges")
			);
			$then[] = array (
				'proctosoverride',
				array ( '=' ),
				module_function('TypeOfServiceMaintenance', 'widget', array('proctosoverride')),
				__("Type of Service")
			);
			return $then;
			break;
		}
	} // end method RuleInterface

	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.3
		//
		//	Added medicaid resubmission and reference codes
		//	Added outside lab charges
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN procmedicaidref VARCHAR(20) AFTER procclmtp');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN procmedicaidresub VARCHAR(20) AFTER procmedicaidref');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN proclabcharges REAL AFTER procmedicaidresub');
		}

		// Version 0.4
		//
		//	Added procedure status (procstatus)
		//
		if (!version_check($version, '0.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN procstatus INT UNSIGNED AFTER proclabcharges');
		}

		// Version 0.4.1
		//
		//	procstatus is now a varchar(50)
		//
		if (!version_check($version, '0.4.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN procstatus '.
				'procstatus VARCHAR(50)');
		}

		// Version 0.4.2
		//
		//	add procslidingscale
		//
		if (!version_check($version, '0.4.2')) {
			$sql->query('ALTER TABLE '.$this->table_name.' ADD COLUMN procslidingscale CHAR(1)');
			$sql->query('UPDATE '.$this->table_name.' SET procslidingscale=\'\' WHERE id>0');
		}

		// Version 0.4.3
		//
		//	add proctosoverride
		//
		if (!version_check($version, '0.4.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' ADD COLUMN proctosoverride INT UNSIGNED AFTER procslidingscale');
			$sql->query('UPDATE '.$this->table_name.' SET proctosoverride=0 WHERE id>0');
		}

		// Version 0.5.0
		//
		//	add proccptmod{2,3}
		//
		if (!version_check($version, '0.5.0')) {
			$sql->query('ALTER TABLE '.$this->table_name.' ADD COLUMN proccptmod2 INT UNSIGNED AFTER proccptmod');
			$sql->query('ALTER TABLE '.$this->table_name.' ADD COLUMN proccptmod3 INT UNSIGNED AFTER proccptmod2');
			$sql->query('UPDATE '.$this->table_name.' SET proccptmod2=0,proccptmod3=0 WHERE id>0');
		}
	} // end method _update

} // end class ProcedureModule

register_module ("ProcedureModule");

?>
