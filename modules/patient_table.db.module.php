<?php
	// $Id$
	// $Author$
	// note: stub module for patient table definition

LoadObjectDependency('_FreeMED.MaintenanceModule');

class PatientTable extends MaintenanceModule {

	var $MODULE_NAME = 'Patient Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.7.2';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.7.2';

	var $table_name = "patient";

	function PatientTable () {
		$this->table_definition = array (
			'ptdtadd' => SQL__DATE,
			'ptdtmod' => SQL__DATE,
			'ptbal' => SQL__REAL,
			'ptbalfwd' => SQL__REAL,
			'ptunapp' => SQL__REAL,
			'ptdoc' => SQL__VARCHAR(150),
			'ptrefdoc' => SQL__VARCHAR(150),
			'ptpcp' => SQL__VARCHAR(150),
			'ptphy1' => SQL__VARCHAR(150),
			'ptphy2' => SQL__VARCHAR(150),
			'ptphy3' => SQL__VARCHAR(150),
			'ptphy4' => SQL__VARCHAR(150),
			// TODO: ptbilltype should be SQL__NOT_NULL (test this)
			'ptbilltype' => SQL__ENUM(array('sta', 'mon', 'chg')),
			'ptbudg' => SQL__REAL,
			'ptsalut' => SQL__VARCHAR(8),
			'ptlname' => SQL__VARCHAR(50),
			'ptfname' => SQL__VARCHAR(50),
			'ptmname' => SQL__VARCHAR(50),
			'ptaddr1' => SQL__VARCHAR(45),
			'ptaddr2' => SQL__VARCHAR(45),
			'ptcity' => SQL__VARCHAR(45),
			'ptstate' => SQL__VARCHAR(20),
			'ptzip' => SQL__CHAR(10),
			'ptcountry' => SQL__VARCHAR(50),
			'pthphone' => SQL__VARCHAR(16),
			'ptwphone' => SQL__VARCHAR(16),
			'ptfax' => SQL__VARCHAR(16),
			'ptemail' => SQL__VARCHAR(80),
			'ptsex' => SQL__ENUM(array('m', 'f', 't')),
			'ptdob' => SQL__DATE,
			'ptssn' => SQL__VARCHAR(9),
			'ptdmv' => SQL__VARCHAR(15),
			'ptdtlpay' => SQL__DATE,
			'ptamtlpay' => SQL__REAL,
			'ptpaytype' => SQL__INT_UNSIGNED(0),
			'ptdtbill' => SQL__DATE,
			'ptamtbill' => SQL__REAL,
			'ptstatus' => SQL__INT_UNSIGNED(0),
			'ptytdchg' => SQL__REAL,
			'ptar' => SQL__REAL,
			'ptextinf' => SQL__TEXT,
			'ptdisc' => SQL__REAL,
			'ptdol' => SQL__DATE,
			'ptdiag1' => SQL__INT_UNSIGNED(0),
			'ptdiag2' => SQL__INT_UNSIGNED(0),
			'ptdiag3' => SQL__INT_UNSIGNED(0),
			'ptdiag4' => SQL__INT_UNSIGNED(0),
			'ptid' => SQL__VARCHAR(10),
			'pthistbal' => SQL__REAL,
			'ptmarital' => SQL__ENUM(array(
				'single', 'married', 'divorced', 'separated', 'widowed'
				)),
			'ptempl' => SQL__ENUM(array('y', 'n')),
			'ptemp1' => SQL__INT_UNSIGNED(0),
			'ptemp2' => SQL__INT_UNSIGNED(0),
			'ptguar' => SQL__TEXT,
			'ptrelguar' => SQL__TEXT,
			'ptguarstart' => SQL__TEXT,
			'ptguarend' => SQL__TEXT,
			'ptins' => SQL__TEXT,
			'ptinsno' => SQL__TEXT,
			'ptinsgrp' => SQL__TEXT,
			'ptinsstart' => SQL__TEXT,
			'ptinsend' => SQL__TEXT,
			'ptnextofkin' => SQL__TEXT,
			'ptblood' => SQL__CHAR(3),
			'ptdep' => SQL__INT_UNSIGNED(0),
			'ptins1' => SQL__INT_UNSIGNED(0),
			'ptins2' => SQL__INT_UNSIGNED(0),
			'ptins3' => SQL__INT_UNSIGNED(0),
			'ptreldep' => SQL__CHAR(1),
			'ptinsno1' => SQL__VARCHAR(50),
			'ptinsno2' => SQL__VARCHAR(50),
			'ptinsno3' => SQL__VARCHAR(50),
			'ptinsgrp1' => SQL__VARCHAR(50),
			'ptinsgrp2' => SQL__VARCHAR(50),
			'ptinsgrp3' => SQL__VARCHAR(50),
			'ptdead' => SQL__INT_UNSIGNED(0),
			'ptdeaddt' => SQL__DATE,
			'pttimestamp' => SQL__TIMESTAMP(16),
			'ptemritimestamp' => SQL__TIMESTAMP(16),
			'ptemriversion' => SQL__BLOB,
			'ptallergies' => SQL__TEXT,
			'ptquickmeds' => SQL__TEXT,
			'ptproblems' => SQL__TEXT,
			'ptcproblems' => SQL__TEXT,
			'ptops' => SQL__TEXT,
			'ptpharmacy' => SQL__INT_UNSIGNED(0),
			'ptrace' => SQL__INT_UNSIGNED(0),
			'ptreligion' => SQL__INT_UNSIGNED(0),
			'ptarchive' => SQL__INT_UNSIGNED(0),
			'iso' => SQL__VARCHAR(15),
			'id' => SQL__SERIAL
		);
		// Define all indices
		$this->table_keys = array ( 
			'id',
			'ptlname', 'ptfname',
			'ptid'
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor PatientTable

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
	} // end function _update
}

register_module('PatientTable');

?>
