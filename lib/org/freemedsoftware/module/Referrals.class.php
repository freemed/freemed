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

class Referrals extends EMRModule {

	var $MODULE_NAME = "Referral";
	var $MODULE_VERSION = "0.8.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "03633733-9ec0-4535-b233-83a1686318ff";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name    = "referrals";
	var $patient_field = "refpatient";
	var $order_by      = "refstamp DESC";
	var $widget_hash   = "##refstamp##"; 

	var $variables = array (
		'refpatient',
		'refprovorig',
		'refprovdest',
		'refstamp',
		'refdx',
		'refpayor',
		'refcoverage',
		'refreasons',
		'refstatus',
		'refurgency',
		'refentered',
		'refapptblob',
		'refdirection',
		'refpayorapproval',
		'refcomorbids',
		'user'
	);

	public function __construct () {
		$this->_SetAssociation( 'EmrModule' );

		// Call parent constructor
		parent::__construct();
	} // end constructor Referrals

	function add_pre ( &$data ) {
		$this_user = freemed::user_cache();
		$data['refentered'] = $this_user->user_number;
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: GetAllActiveByPatient
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function GetAllActiveByPatient ( $patient ) {
		$q = "SELECT r.id AS id, CONCAT(p.phyfname, IF(LENGTH(p.phymname)>0, CONCAT(' ', p.phymname, ' '), ' '), p.phylname) AS provider, r.refdirection AS direction, r.refstamp AS stamp, DATE_FORMAT(r.refstamp, '%m/%d/%Y') AS stamp_mdy FROM ".$this->table_name." r LEFT OUTER JOIN physician p ON IF(r.refdirection='inbound',r.refprovorig,r.refprovdest)=p.id WHERE r.refpatient=".($patient+0)." AND r.refstatus=0";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAllActiveByPatient

} // end class Referrals

register_module('Referrals');

?>
