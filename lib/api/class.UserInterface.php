<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.api.UserInterface
//
//	User manipulation routines.
//
class UserInterface {

	protected $user;

	public function __construct ( ) {
		$this->user = CreateObject('org.freemedsoftware.core.User');
	}

	// Method: GetCurrentUsername
	//
	//	Determine the username for the current user.
	//
	// Returns:
	//
	//	String.
	//
	public function GetCurrentUsername ( ) {
		return $this->user->getDescription();
	} // end method GetCurrentUsername

	// Method: GetUsers
	//
	//	Get picklist formatted user information.
	//
	// Parameters:
	//
	//	$param - Substring to search for. Defaults to ''.
	//
	// Returns:
	//
	//	Array of arrays containing ( user description, id ).
	//
	public function GetUsers ( $param = '' ) {
		$q = "SELECT u.userdescrip AS description, u.id AS id FROM user u WHERE u.userdescrip LIKE '".addslashes( $param )."%' ORDER BY u.userdescrip";
		$res = $GLOBALS['sql']->queryAll( $q );
		foreach ( $res AS $r ) {
			$return[] = array ( $r['description'], $r['id'] );
		}
		return $return;
	} // end method GetUsers

	// Method: GetEMRConfiguration
	public function GetEMRConfiguration ( ) {
		return $this->user->manage_config;
	} // end method GetEMRConfiguration

	public function GetNewMessages ( ) {
		return $this->user->newMessages();
	} // end method GetNewMessages

	// Method: SetConfigValue
	//
	//	Set user configurable variable.
	//
	// Parameters:
	//
	//	$key - Configuration key
	//
	//	$value - Configuration value
	//
	public function SetConfigValue ( $key, $value ) {
		return $this->user->setManageConfig ( $key, $value );
	} // end method SetConfigValue

} // end class UserInterface

?>
