<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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
	var $variables = array(
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
		"procdtend",	
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
		$data['user'] = freemed::user_cache()->user_number;
	} // end add_pre

	function add_post ( $id, $data ) {
		// Add to Claimlog
		$claimlog = CreateObject('org.freemedsoftware.api.ClaimLog');
		$claimlog->log_event(
			$id,
			array (
				'action' => __("Create"),
				'comment' => __("Procedure created")
			)
		);

		// Deduct from authorization, if there is one
		// specified
		if ($data['procauth'] > 0) {
			$a = CreateObject('org.freemedsoftware.api.Authorizations');
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
		$data['user'] = freemed::user_cache()->user_number;
	} // end method mod_pre
	
	protected function mod_post ( $data ) {
		// Check if authorization changed
		if ($data['procauth'] != $data['procauthsaved']) {
			$a = CreateObject('org.freemedsoftware.api.Authorizations');
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
		$charge = $cpt_code_stdfee*$procunits;
		if ($debug) $display_buffer .= " (charge = \"$charge\") \n";

		// step six:
		//   adjust values to proper precision
		$charge = bcadd ($charge, 0, 2);
		return $charge+0;
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
	
	public function getProcedureInfo($patient){
		$query="SELECT pr.id AS Id, pr.procdt AS proc_date,CONCAT(cpt.cptcode,' ',cptnameint) AS proc_code, CONCAT(cm.cptmod,' ',cm.cptmoddescrip) AS proc_mod, pr.proccomment AS comment ".
		"FROM ".$this->table_name." pr LEFT OUTER JOIN cpt ON cpt.id =pr.proccpt LEFT OUTER JOIN cptmod cm ON cm.id=pr.proccptmod ".
		"WHERE pr.procpatient=".$GLOBALS['sql']->quote( $patient )." ORDER BY pr.id DESC";
		return $GLOBALS['sql']->queryAll( $query );
	}
	
	public function getLastProc($patient){
		$query="select * FROM procrec where procpatient=".$GLOBALS['sql']->quote( $patient )." order by id DESC limit 1";
		return $GLOBALS['sql']->queryRow( $query );
	}
	
	public function getProcByID($id){
		$query="select * FROM procrec WHERE id=".$GLOBALS['sql']->quote( $id );
		return $GLOBALS['sql']->queryRow( $query );
	}
	
	public function getCoverages($id){
		$query1="select c.id AS Id, CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate, ')') AS payer FROM procrec pr ".
				"LEFT OUTER JOIN coverage c ON pr.proccov1 = c.id ".
				"LEFT OUTER JOIN insco i ON c.covinsco = i.id ".
				"WHERE pr.id=".$GLOBALS['sql']->quote( $id );
		$result1 = $GLOBALS['sql']->queryRow( $query1 );
		$query2="select c.id AS Id,CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate, ')') AS payer FROM procrec pr ".
				"LEFT OUTER JOIN coverage c ON pr.proccov2 = c.id ".
				"LEFT OUTER JOIN insco i ON c.covinsco = i.id ".
				"WHERE pr.id=".$GLOBALS['sql']->quote( $id );
		$result2 = $GLOBALS['sql']->queryRow( $query2 );
		$query3="select c.id AS Id,CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate, ')') AS payer FROM procrec pr ".
				"LEFT OUTER JOIN coverage c ON pr.proccov3 = c.id ".
				"LEFT OUTER JOIN insco i ON c.covinsco = i.id ".
				"WHERE pr.id=".$GLOBALS['sql']->quote( $id );
		$result3 = $GLOBALS['sql']->queryRow( $query3 );
		$query4="select c.id AS Id,CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate, ')') AS payer FROM procrec pr ".
				"LEFT OUTER JOIN coverage c ON pr.proccov4 = c.id ".
				"LEFT OUTER JOIN insco i ON c.covinsco = i.id ".
				"WHERE pr.id=".$GLOBALS['sql']->quote( $id );
		$result4 = $GLOBALS['sql']->queryRow( $query4 );
		
		if($result1['payer']!=null){
			$i=count($data);
			$data[$i]['id']="1";
			$data[$i]['payer']=$result1['payer'];
			$data[$i]['type']="Primary";
		}
		if($result2['payer']!=null){	
			$i=count($data);
			$data[$i]['id']="2";
			$data[$i]['payer']=$result2['payer'];
			$data[$i]['type']="Secondary";
		}
		if($result3['payer']!=null){
			$i=count($data);
			$data[$i]['id']="3";
			$data[$i]['payer']=$result3['payer'];
			$data[$i]['type']="Tertiary";
		}
		if($result4['payer']!=null){
			$i=count($data);
			$data[$i]['id']="4";
			$data[$i]['payer']=$result4['payer'];
			$data[$i]['payer']="Work Comp";
		}
		return $data;
	}
	
	public function getNonZeroBalProcs($patient){
		$query="SELECT id AS id, procdt AS dos, proccharges AS charge, procamtpaid AS payment,procbalcurrent AS arrear ".
		"FROM ".$this->table_name." WHERE procpatient=".$GLOBALS['sql']->quote( $patient )." AND procbalcurrent>0 ORDER BY procdt DESC";
		return $GLOBALS['sql']->queryAll( $query );
	}
	
	public function getTotalArrears($patient){
		$query="SELECT sum(procbalcurrent) AS tarrears ".
		"FROM ".$this->table_name." WHERE procpatient=".$GLOBALS['sql']->quote( $patient )." AND procbalcurrent>0 ORDER BY procdt DESC";
		return $GLOBALS['sql']->queryRow( $query );
	}
	
	public function getPatientProcHistory($patient){
		$query="SELECT i.icd9code AS icode, i.icd9descrip AS idesc, CONCAT(pr.procdt,' to ',IFNULL(pr.procdtend,'')) AS pdate FROM ".$this->table_name. " pr LEFT OUTER JOIN icd9 i ON i.id=pr.procdiag1 ".
		" WHERE procpatient=".$GLOBALS['sql']->quote( $patient )." AND procbalcurrent>0 ORDER BY procdt DESC";
		return $GLOBALS['sql']->queryAll( $query );
	}
} // end class ProcedureModule

register_module ("ProcedureModule");

?>
