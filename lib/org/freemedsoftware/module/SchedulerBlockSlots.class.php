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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class SchedulerBlockSlots extends SupportModule {

	var $MODULE_NAME = "Scheduler Block Slots";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "d2593fd4-d2c5-408e-b107-2c524ae44a9c";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.1';

	var $table_name = "scheduler_block_slots";

	var $variables = array (
		'sbshour',
		'sbsminute',
		'sbsduration',
		'sbdate',
		'sbsprovider',
		'sbsprovidergroup',
		'user'
	);

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
	} // end constructor Callin

	protected function add_pre ( &$data ) {
		unset($data['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	} // end method add_pre

	protected function mod_pre ( &$data ) {
		unset($data['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	} // end method add_pre

	// Method: GetAll
	//
	//	Get array of all groups records.
	//
	// Returns:
	//
	//	array of Hashes.
	public function GetAll () {
		freemed::acl_enforce( 'admin', 'write' );
		$q = "select sbs.id, sbs.sbshour as starthour,sbs.sbsminute as startmin,sbs.sbsduration as duration,sbs.sbdate as date,".
			"sbs.sbsprovider,sbs.sbsprovidergroup,sbs.stamp as entered_on,u.userdescrip as entered_by,".
			"concat(p.phylname,' ,',p.phyfname,' ',p.phymname) as provider,pg.phygroupname as provider_group ".
			"from scheduler_block_slots sbs ".
			"left join physician p on p.id = sbs.sbsprovider ".
			"left join phygroup pg on pg.id = sbs.sbsprovidergroup ".
			"left join user u on u.id = sbs.user";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll
	
	// Method: GetBlockTimings
	//
	//	Get array of all group records.
	//
	// Returns:
	//
	//	array of Hashes.
	public function GetBlockedTimeSlots ($providerid,$date=null) {
		
		freemed::acl_enforce( 'scheduling', 'write' );
		
		$providerGroups = CreateObject('org.freemedsoftware.module.ProviderGroups');
		$providerGroupsIds = $providerGroups->getGroupIds($providerid);
		 	
		$q = "select sbshour,sbsminute,sbsduration from scheduler_block_slots where sbsprovider=".$GLOBALS['sql']->quote( $providerid ).($date?" and sbdate='".$date."'":"");
		foreach($providerGroupsIds as $ids){
			$q = $q . " or sbsprovidergroup = ".$ids['id'] ;
		}
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll
	
}

register_module('SchedulerBlockSlots');

?>
