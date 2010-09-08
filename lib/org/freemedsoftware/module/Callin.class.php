<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class Callin extends SupportModule {

	var $MODULE_NAME = "Call-in";
	var $MODULE_VERSION = "0.7";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "5f4e5de0-58fa-495e-84a6-9c35a7f7e816";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "callin";

        var $widget_hash = '##cilname##, ##cifname## ##cimname##';

	var $archive_field = "ciarchive";

	var $archive_check = "1";

	var $variables = array (
		'cilname',
		'cifname',
		'cimname',
		'cihphone',
		'ciwphone',
		'cidob',
		'cicomplaint',
		'cifacility',
		'ciphysician',
		'ciisinsured',
		'coveffdt',
		'covinsco',
		'covpatinsno',
		'covpatgrpno',
		'covtype',
		'covstatus',
		'covrel',
		'covlname',
		'covfname',
		'covmname',
		'covaddr1',
		'covaddr2',
		'covcity',
		'covstate',
		'covzip',
		'covdob',
		'covsex',
		'covssn',
		'covinstp',
		'covprovasgn',
		'covbenasgn',
		'covrelinfo',
		'covrelinfodt',
		'covplanname',
		'covisassigning',
		'covschool',
		'covemployer',
		'covcopay',
		'covdeduct',
		'ciuser',
		'citookcall',
		'cipatient',
		'ciarchive'
	);

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
		if($this->archive_field)
			$this->archive_check = "(".$this->archive_field." IS NULL OR ".$this->archive_field."=0)";
	} // end constructor Callin

	protected function add_pre ( &$data ) {
		$data['ciuser'] = freemed::user_cache()->user_number;
	} // end method add_pre

	protected function mod_pre ( &$data ) {
		$data['ciuser'] = freemed::user_cache()->user_number;
	} // end method mod_pre

	// Method: GetAll
	//
	//	Get array of all call-in patient records.
	//
	// Parameters:
	//
	//	$id - Database ID
	//
	// Returns:
	//
	//	Hash.
	public function GetAll () {
		freemed::acl_enforce( 'emr', 'read' );
		$q = "SELECT CONCAT(cilname, ', ', cifname, ' ', cimname) AS name, cicomplaint AS complaint, citookcall AS took_call, cidatestamp AS call_date, DATE_FORMAT(cidatestamp, '%m/%d/%Y %H:%m:%s') AS call_date_mdy, cihphone AS phone_home, ciwphone AS phone_work, id FROM callin WHERE ".$this->archive_check." ORDER BY cidatestamp DESC";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll

	// Method: GetAllWithInsurance
	//
	//	Get array of all call-in patient records.
	//
	// Parameters:
	//
	//	$id - Database ID
	//
	// Returns:
	//
	//	Hash.
	public function GetAllWithInsurance ($criteria=NULL) {
		freemed::acl_enforce( 'emr', 'read' );
		$conditions = "";
		if($criteria!=NULL){
			if($criteria['cilname'])
				$conditions=$conditions.($conditions?" AND ":" ")."ci.cilname like '%".$criteria['cilname']."%'";
			if($criteria['cifname'])
				$conditions=$conditions.($conditions?" AND ":" ")."ci.cilname like '%".$criteria['cilname']."%'";
			if($criteria['id'])
				$conditions=$conditions.($conditions?" AND ":" ")."ci.id =".$GLOBALS['sql']->quote($criteria['id']);
			if(!$criteria['ciarchive'])
				$conditions=$conditions.($conditions?" AND ":" ").$this->archive_check;
		}else
			$conditions=$this->archive_check;
		if(!$conditions)
			$conditions = 1;
		
		$q = "SELECT CONCAT(ci.cilname, ', ', ci.cifname, ' ', ci.cimname) AS name, ci.cicomplaint AS complaint, ci.citookcall AS took_call, ci.cidatestamp AS call_date,ci.ciarchive as archive, DATE_FORMAT(ci.cidatestamp, '%m\/%d\/%Y %H:%m:%s') AS call_date_mdy, CONCAT(CASE WHEN ci.cihphone!='' then CONCAT('(H)',ci.cihphone) ELSE '' END, CASE WHEN ci.ciwphone!='' THEN CONCAT(' (W)',ci.ciwphone) ELSE '' END) as contact_phone, ci.id, CONCAT( insci.insconame, ' (', insci.inscocity, ', ', insci.inscostate, ')') AS coverage FROM callin ci LEFT JOIN insco insci on insci.id = ci.covinsco and ci.ciisinsured=1 WHERE ".$conditions." ORDER BY ci.cidatestamp DESC";		
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAllWithInsurance
	
	// Method: GetDetailedRecord
	//
	//	Get detailed record of call-in patient.
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetDetailedRecord( $id) {
		freemed::acl_enforce( 'emr', 'read' );
		$q = "SELECT CONCAT(c.cilname, ', ', c.cifname, ' ', c.cimname) AS name, c.cilname AS lastname, cifname AS firstname, cimname AS middlename, c.cicomplaint AS complaint, c.citookcall AS took_call, c.cidatestamp AS call_date"
		.", DATE_FORMAT(c.cidatestamp, '%m/%d/%Y') AS call_date_mdy,c.cidob AS dob, c.cihphone AS phone_home, c.ciwphone AS phone_work, c.id ,f.psrname as facility, f.id as facilityid"
		.",ph.id AS physicianid, CONCAT(ph.phylname, ', ', ph.phyfname, ' ', ph.phymname) AS physician "
		."FROM callin c LEFT OUTER JOIN facility f ON c.cifacility=f.id LEFT OUTER JOIN physician ph ON c.ciphysician=ph.id where c.id=".$id." AND ".$this->archive_check;
		return $GLOBALS['sql']->queryRow( $q );
	} // end method GetDetailedRecord
	
	// Method: GetDetailedRecordWithIntake
	//
	//	Get detailed record of call-in patient.
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetDetailedRecordWithIntake( $id) {
		freemed::acl_enforce( 'emr', 'read' );
		
		$id = $GLOBALS['sql']->quote($id);
		
		$q = "select * FROM callin c where c.id=".$id." AND ".$this->archive_check;
		$return = $GLOBALS['sql']->queryRow( $q );
		$q = "select tii.id as treatment_id,tii.* from treatment_initial_intake tii where tii.intaketype = 'callin' and tii.patient = ".$id;
		$r = $GLOBALS['sql']->queryRow( $q );
		//return $r;
		if($r){
			$return	= array_merge($r,$return);
		}
		return $return;
	} // end method GetDetailedRecord

}
	
register_module('Callin');

?>
