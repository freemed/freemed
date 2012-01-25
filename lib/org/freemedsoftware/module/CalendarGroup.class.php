<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

class CalendarGroup extends SupportModule {

	var $MODULE_NAME = "Calendar Group";
	var $MODULE_VERSION = "0.8.4";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "0a71b8e8-b15c-4166-a665-2b8ac71e06d5";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "calgroup";

        var $widget_hash = '##groupname##, (##grouplength## members)';

	var $variables = array (
		'groupname',
		'groupfacility',
		'groupfrequency',
		'grouplength',
		'groupmembers',
	);

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
	} // end constructor Callin

	protected function add_pre ( &$data ) {
	} // end method add_pre
	
	// Method: GetAll
	//
	//	Get array of all groups records.
	//
	// Returns:
	//
	//	array of Hashes.
	public function GetAll () {
		freemed::acl_enforce( 'emr', 'read' );
		$q = "SELECT cg.id,cg.groupname,cg.grouplength,cg.groupfrequency, CONCAT(f.psrname,' ',f.psrnote,' (',f.psrcity,',',f.psrstate,')') as groupfacility  FROM calgroup cg  left outer join facility f on f.id=cg.groupfacility";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll
	
	// Method: GetDetailedRecord
	//
	//            Get detailed record of specified group.
	//
	// Parameters:
	//
	//	$id - group ID
	//	
	//
	// Returns:
	//
	//	hash
	//
	public function GetDetailedRecord( $id ) {
		freemed::acl_enforce( 'emr', 'read' );
		$q = "SELECT cg.id,cg.groupname,cg.grouplength,cg.groupfrequency,cg.groupmembers,cg.groupfacility as facility, CONCAT(f.psrname,' ',f.psrnote,' (',f.psrcity,',',f.psrstate,')') as groupfacility  FROM calgroup cg  left outer join facility f on f.id=cg.groupfacility where cg.id=".$GLOBALS['sql']->quote( $id );
		$groupResult = $GLOBALS['sql']->queryRow( $q );
		if($groupResult){
			$members = $groupResult['groupmembers'];
			$q2 = "select CONCAT(pa.ptlname, ', ', pa.ptfname, IF(LENGTH(pa.ptmname)>0,CONCAT(' ',pa.ptmname),''), IF(LENGTH(pa.ptsuffix)>0,CONCAT(' ',pa.ptsuffix),''), ' (', pa.ptid, ')') AS patient from patient pa where pa.id in (".$members.")";
			$membersResult = $GLOBALS['sql']->queryAll( $q2 );
			$allMembers="";
			
			foreach($membersResult As $mem){
				$allMembers=$allMembers.$mem['patient']."\n";
			}
			$groupResult['groupmembersName'] = $allMembers;
			
		}
		return $groupResult;
	} // end method GetDetailedRecord
	
}

register_module('CalendarGroup');

?>
