<?php
 // $Id$
 // $Author$
 // note: stub module for patient table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class PatientTable extends MaintenanceModule {

	var $MODULE_NAME = 'Patient Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0';
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "patient";

	function PatientTable () {
		$this->table_definition = array (
			'ptdtadd' => SQL_DATE,
			'ptdtmod' => SQL_DATE,
			'ptbal' => SQL_REAL,
			'ptbalfwd' => SQL_REAL,
			'ptunapp' => SQL_REAL,
			'ptdoc' => SQL_VARCHAR(150),
			'ptrefdoc' => SQL_VARCHAR(150),
			'ptpcp' => SQL_VARCHAR(150),
			'ptphy1' => SQL_VARCHAR(150),
			'ptphy2' => SQL_VARCHAR(150),
			'ptphy3' => SQL_VARCHAR(150),
			'ptphy4' => SQL_VARCHAR(150),
			// TODO: ptbilltype should be SQL_NOT_NULL (test this)
			'ptbilltype' => SQL_ENUM(array('sta', 'mon', 'chg')),
			'ptbudg' => SQL_REAL,
			'ptlname' => SQL_VARCHAR(50),
			'ptfname' => SQL_VARCHAR(50),
			'ptmname' => SQL_VARCHAR(50),
			'ptaddr1' => SQL_VARCHAR(45),
			'ptaddr2' => SQL_VARCHAR(45),
			'ptcity' => SQL_VARCHAR(45),
			'ptstate' => SQL_VARCHAR(20),
			'ptzip' => SQL_CHAR(10),
			'ptcountry' => SQL_VARCHAR(50),
			'pthphone' => SQL_VARCHAR(16),
			'ptwphone' => SQL_VARCHAR(16),
			'ptfax' => SQL_VARCHAR(16),
			'ptemail' => SQL_VARCHAR(80),
			'ptsex' => SQL_ENUM(array('m', 'f', 't')),
			'ptdob' => SQL_DATE,
			'ptssn' => SQL_VARCHAR(9),
			'ptdmv' => SQL_VARCHAR(15),
			'ptdtlpay' => SQL_DATE,
			'ptamtlpay' => SQL_REAL,
			'ptpaytype' => SQL_INT_UNSIGNED(0),
			'ptdtbill' => SQL_DATE,
			'ptamtbill' => SQL_REAL,
			'ptstatus' => SQL_INT_UNSIGNED(0),
			'ptytdchg' => SQL_REAL,
			'ptar' => SQL_REAL,
			'ptextinf' => SQL_TEXT,
			'ptdisc' => SQL_REAL,
			'ptdol' => SQL_DATE,
			'ptdiag1' => SQL_INT_UNSIGNED(0),
			'ptdiag2' => SQL_INT_UNSIGNED(0),
			'ptdiag3' => SQL_INT_UNSIGNED(0),
			'ptdiag4' => SQL_INT_UNSIGNED(0),
			'ptid' => SQL_VARCHAR(10),
			'pthistbal' => SQL_REAL,
			'ptmarital' => SQL_ENUM(array(
				'single', 'married', 'divorced', 'separated', 'widowed'
				)),
			'ptempl' => SQL_ENUM(array('y', 'n')),
			'ptemp1' => SQL_INT_UNSIGNED(0),
			'ptemp2' => SQL_INT_UNSIGNED(0),
			'ptguar' => SQL_TEXT,
			'ptrelguar' => SQL_TEXT,
			'ptguarstart' => SQL_TEXT,
			'ptguarend' => SQL_TEXT,
			'ptins' => SQL_TEXT,
			'ptinsno' => SQL_TEXT,
			'ptinsgrp' => SQL_TEXT,
			'ptinsstart' => SQL_TEXT,
			'ptinsend' => SQL_TEXT,
			'ptnextofkin' => SQL_TEXT,
			'ptblood' => SQL_CHAR(3),
			'ptdep' => SQL_INT_UNSIGNED(0),
			'ptins1' => SQL_INT_UNSIGNED(0),
			'ptins2' => SQL_INT_UNSIGNED(0),
			'ptins3' => SQL_INT_UNSIGNED(0),
			'ptreldep' => SQL_CHAR(1),
			'ptinsno1' => SQL_VARCHAR(50),
			'ptinsno2' => SQL_VARCHAR(50),
			'ptinsno3' => SQL_VARCHAR(50),
			'ptinsgrp1' => SQL_VARCHAR(50),
			'ptinsgrp2' => SQL_VARCHAR(50),
			'ptinsgrp3' => SQL_VARCHAR(50),
			'ptdead' => SQL_INT_UNSIGNED(0),
			'ptdeaddt' => SQL_DATE,
			'pttimestamp' => SQL_TIMESTAMP(16),
			'ptemritimestamp' => SQL_TIMESTAMP(16),
			'ptemriversion' => SQL_BLOB,
			'ptallergies' => SQL_TEXT,
			'ptquickmeds' => SQL_TEXT,
			'ptproblems' => SQL_TEXT,
			'ptcproblems' => SQL_TEXT,
			'ptops' => SQL_TEXT,
			'iso' => SQL_VARCHAR(15),
			'id' => SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor PatientTable

	// Use _update to update table definitions with new versions
	function _update () {
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
	} // end function _update
}

register_module('PatientTable');

?>
