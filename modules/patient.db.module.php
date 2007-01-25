<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class PatientModule extends SupportModule {

	var $MODULE_NAME = "Patient Module";
	var $MODULE_VERSION = "0.7.4";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "fac54abe-dc93-4c0a-b912-8099f5be14f4";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.7.2';

	var $table_name = "patient";

	var $acl_category = 'emr';

	var $variables = array (
		'ptdtadd',
		'ptdtmod',
		'ptdoc',
		'ptrefdoc',
		'ptpcp',
		'ptphy1',
		'ptphy2',
		'ptphy3',
		'ptphy4',
		'ptsalut',
		'ptlname',
		'ptmaidenname',
		'ptfname',
		'ptmname',
		'ptsuffix',
		'ptaddr1',
		'ptaddr2',
		'ptcity',
		'ptstate',
		'ptzip',
		'ptcountry',
		'pthphone',
		'ptwphone',
		'ptmphone',
		'ptfax',
		'ptemail',
		'ptsex',
		'ptdob',
		'ptssn',
		'ptdmv',
		'ptstatus',
		'ptid',
		'ptdiag1',
		'ptdiag2',
		'ptdiag3',
		'ptdiag4',
		'ptmarital',
		'ptempl',
		'ptnextofkin',
		'ptpharmacy',
		'ptrace',
		'ptreligion',
		'ptarchive',
		'iso'
	);

	public function __construct ( ) {
		// __("Patient Module")

		// DEBUG TESTING: $this->defeat_acl = true;

		// Call parent constructor
		parent::__construct ( );
	} // end constructor PatientModule

	protected function add_pre ( &$data ) {
		$s = CreateObject('org.freemedsoftware.api.Scheduler');

		// Handle DOB
		$data['ptdob'] = $s->ImportDate( $data['ptdob'] );

		// Split city, state zip if it's one field
		if ($data['ptcsz']) {
			if (preg_match("/([^,]+), ([A-Z]{2}) (.*)/i", $data['ptcsz'], $reg)) {
				$data['ptcity'] = $reg[1];
				$data['ptstate'] = $reg[2];
				$data['ptzip'] = $reg[3];
			}
		}
	} // end method add_pre

	// Method: Search
	//
	//	Search for patients matching criteria.
	//
	// Parameters:
	//
	//	$criteria - Search string.
	//
	//	$type - Type of search. Supported values:
	//	* contains - Substring search for any terms
	//	* letter - Search using first letter of first or last name
	//	* smart - "Smart" search as "first last" or "last, first"
	//	* soundex - Soundex search on term as first or last name
	//
	//	$limit - (optional) Limit to how many results returned.
	//	Defaults to 10.
	//
	// Returns:
	//
	//	Array values with keys equalling the patient id
	//
	public function Search ( $criteria, $type, $limit = 10 ) {
		$where[] = "ptarchive+0 != '1'";
		switch ( $type ) {
			case 'letter':
			$where[] = "UCASE(SUBSTR(ptlname, 1, 1) LIKE '".addslashes(strtoupper(substr($criteria, 0, 1)))."%'";
			break; // letter

			case 'contains':
			$where[] = "(
				ptlname LIKE '".addslashes($criteria)."%' OR 
				ptfname LIKE '".addslashes($criteria)."%' OR 
				ptdob LIKE '".addslashes($criteria)."%' OR 
				ptid LIKE '".addslashes($criteria)."%'
				)";
			break; // contains

			case 'soundex':
			$where[] = "(
				SOUNDEX(ptlname) = SOUNDEX('".addslashes($criteria)."') OR
				SOUNDEX(ptfname) = SOUNDEX('".addslashes($criteria)."')
				)";
			break; // soundex

			case 'smart':
			if ( ! ( strpos ( $criteria, ',' ) === false ) ) {
				// last, first
				list ( $last, $first ) = explode( ',', $criteria );
				$last = trim( $last );
				$first = trim( $first );
			} else {
				// first last
				list ( $first, $last ) = explode( ' ', $criteria );
			}
			$where[] = "UCASE(ptlname) LIKE '".addslashes(strtoupper($last))."%'";
			$where[] = "UCASE(ptfname) LIKE '".addslashes(strtoupper($first))."%'";
			break; // smart
		} // end switch

		// Figure this out based on WHERE clauses
		$query = "SELECT id AS k, CONCAT(ptlname, ', ', ptfname, ' (', ptid, ') [', ptdob, ']') AS v FROM patient WHERE ".join(' AND ', $where)." LIMIT ".($limit+0);
		$result = $GLOBALS['sql']->queryAll( $query );
		foreach ($result AS $r) {
			$return[$r['k']] = $r['v'];
		}
		return $return;
	} // end method Search

	// Use _update to update table definitions with new versions
	function _update () {
		global $sql;

		$version = freemed::module_version($this->MODULE_NAME);
		/* 
			// Example of how to upgrade with ALTER TABLE
			// Successive instances change the structure of the table
			// into whatever its current version is, without having
			// to reload the table at all. This pulls in all of the
			// changes a version at a time. (You can probably use
			// REMOVE COLUMN as well, but I'm steering away for now.)

		if (!version_check($version, '0.1.0')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptglucose INT UNSIGNED AFTER id');
		}
		if (!version_check($version, '0.1.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN somedescrip TEXT AFTER ptglucose');
		}
		if (!version_check($version, '0.1.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN fakefield AFTER ptglucose');
		}
		*/

		// Version 0.6.1
		//
		//	Added HL7 race and religion fields (ptrace,ptreligion)
		//
		if (!version_check($version, '0.6.1')) {
			// HL7-compliant race field
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptrace INT UNSIGNED AFTER pttimestamp');
			// HL7-compliant religion field
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptreligion INT UNSIGNED AFTER pttimestamp');
		} // end 0.6.1 upgrade

		// Version 0.6.2
		//
		//	Added patient archive flag (ptarchive)
		//
		if (!version_check($version, '0.6.2')) {
			// Archive
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptarchive INT UNSIGNED AFTER ptreligion');
			// Stupid mysql needs everything to be set to 0
			// by default.
			$sql->query('UPDATE '.$this->table_name.' SET ptarchive=\'0\'');
		} // end 0.6.2 upgrade

		// Version 0.7.0
		//
		//	Added patient pharmacy information
		//
		if (!version_check($version, '0.7.0')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptpharmacy INT UNSIGNED AFTER ptops');
		} // end 0.7.0 upgrade

		// Version 0.7.1
		//
		//	Added patient salutation (Dr, Mr, Mrs, Ms)
		//
		if (!version_check($version, '0.7.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptsalut VARCHAR(8) AFTER ptbudg');
		} // end 0.7.1 upgrade

		// Version 0.7.2
		//
		//	Add indexes to speed searches on large databases
		//
		if (!version_check($version, '0.7.2')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD INDEX(ptlname), '.
				'ADD INDEX(ptfname), '.
				'ADD INDEX(ptid)');
		} // end 0.7.2 upgrade

		// Version 0.7.3
		//
		//	Add maiden name (ptmaidenname)
		//
		if (!version_check($version, '0.7.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptmaidenname VARCHAR(50) AFTER ptlname');
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'ptmaidenname=\'\' WHERE id > 0');
		} // end 0.7.3 upgrade

		// Version 0.7.4
		//
		//	Add unknown option for ptmarital
		//	Add suffix
		//
		if (!version_check($version, '0.7.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				"CHANGE COLUMN ".
				"ptmarital ptmarital ENUM('single', 'married', 'divorced', 'separated', 'widowed', 'unknown')");
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptsuffix VARCHAR(10) AFTER ptmname');
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'ptsuffix=\'\' WHERE id > 0');
		} // end 0.7.4 upgrade
	} // end function _update
}

register_module('PatientModule');

?>
