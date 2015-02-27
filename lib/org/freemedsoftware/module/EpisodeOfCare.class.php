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

class EpisodeOfCare extends EMRModule {

	var $MODULE_NAME = "Episode of Care";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_DESCRIPTION = "Episode of care is another portion of FreeMED designed to help with outcomes management. Any patients' treatment can be described through episodes of care, which may span any range of time, and more than one epsiode of care can be used per visit.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "4e6c2841-f36d-47c0-a1c7-6816c7dc31d8";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Episode of Care";
	var $table_name     = "eoc";
	var $patient_field  = "eocpatient";

	var $widget_hash = "##eocdescrip## (##eocdatelastsimilar##)";

	var $variables		= array (
		"eocpatient",
		"eocdescrip",
		"eocstartdate",
		"eocdtlastsimilar",
		"eocreferrer",
		"eocfacility",
		"eocdiagfamily",
		"eocrelpreg",
		"eocrelemp",
		"eocrelauto",
		"eocrelother",
		"eocrelstpr",
		"eoctype",
		"eochospital",
		"eocrelautoname",
		"eocrelautoaddr1",
		"eocrelautoaddr2",
		"eocrelautocity",
		"eocrelautostpr",
		"eocrelautozip",
		"eocrelautocountry",
		"eocrelautocase",
		"eocrelautorcname",
		"eocrelautorcphone",
		"eocrelempname",
		"eocrelempaddr1",
		"eocrelempaddr2",
		"eocrelempcity",
		"eocrelempstpr",
		"eocrelempzip",
		"eocrelempcountry",
		"eocrelempfile",
		"eocrelemprcname",
		"eocrelemprcphone",
		"eocrelemprcemail",
		"eocrelpregcycle",
		"eocrelpreggravida",
		"eocrelpregpara",
		"eocrelpregmiscarry",
		"eocrelpregabort",
		"eocrelpreglastper",
		"eocrelpregconfine",
		"eocrelothercomment",
		"eocdistype",
		"eocdisfromdt",
		"eocdistodt",
		"eocdisworkdt",
		"eochosadmdt",
		"eochosdischrgdt",
		"eocrelautotime",
		"user"
	);

	public function __construct ( ) {
		// __("Episode of Care")
		// __("Episode of care is another portion of FreeMED designed to help with outcomes management. Any patients' treatment can be described through episodes of care, which may span any range of time, and more than one epsiode of care can be used per visit.")

		// Summary box for management
		$this->summary_vars = array (
			__("Orig") => "eocstartdate",
			__("Last") => "eocdtlastsimilar",
			__("Description") => "eocdescrip"
		);
		$this->summary_options = SUMMARY_VIEW;

		// Run parent constructor
		parent::__construct ( );
	} // end constructor EpisodeOfCare

	function widget ($varname, $patient) {
		global ${$varname};
		return freemed::multiple_choice(
			"SELECT id,eocdescrip,eocstartdate,eocdtlastsimilar ".
			"FROM ".$this->table_name." WHERE ".
			"eocpatient='".addslashes($patient)."'",
			"##eocdescrip## (##eocstartdate## ".__("to")." ".
				"##eocdtlastsimilar##)",
			$varname,
			${$varname},
			false
		);
	} // end method EpisodeOfCare->widget

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}
	
	public function getEOCValues($patient){
		$q="SELECT id AS Id, CONCAT(eocdescrip,'(',eocstartdate,' to ',eocdtlastsimilar,')') AS eoc_info FROM ".$this->table_name." WHERE eocpatient=".$GLOBALS['sql']->quote( $patient );
		return $GLOBALS['sql']->queryAll( $q );
	}
	
	public function getAllValues($patient){
		$q="SELECT * FROM ".$this->table_name." WHERE eocpatient=".$GLOBALS['sql']->quote( $patient );
		return $GLOBALS['sql']->queryAll( $q );
	}
	
	public function getHospitalizations($patient){
		$q="SELECT eochosadmdt AS admit_date, eochosdischrgdt AS disch_date FROM ".$this->table_name." WHERE eocpatient=".$GLOBALS['sql']->quote( $patient );
		return $GLOBALS['sql']->queryAll( $q );
	}

} // end class EpisodeOfCare

register_module ("EpisodeOfCare");

?>
