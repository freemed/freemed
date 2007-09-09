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

	protected $variables = array (
		'username',
		'userpassword',
		'userdescrip',
		'userlevel',
		'usertype',
		'userrealphy',
		'usermanageopt',
		'useremail',
		'usersms',
		'usersmsprovider'
	);

	public function __construct ( ) {
		$this->user = freemed::user_cache();
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

	// Method: Multicall
	//
	//	Utility method to perform multiple pipelined calls.
	//
	// Parameters:
	//
	//	$calls - Array of hashes containing:
	//	* method
	//	* parameters
	//
	// Returns:
	//
	//	Array of results.
	//
	public function Multicall ( $calls ) {
		if (!is_array($calls)) { return false; }
		$output = array( );
		foreach ( $calls AS $k => $v ) {
			$v = (array) $v;
			if ( substr($v['method'], 0, 25) == 'org.freemedsoftware.core.' ) {
				syslog( LOG_ERROR, "Invalid method called ${v['method']}" );
				return false;
			}
			if ( is_array( $v['parameters'] ) ) {
				$output[ $k ] = @call_user_func_array ( 'CallMethod', array_merge ( array ( $v['method'] ), $v['parameters'] ) );
			} else {
				$output[ $k ] = @CallMethod( $v['method'] );
			}
		}
		return $output;
	} // end method Multicall

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

	// Method: GetRecord
	//
	//	Get user record.
	//
	// Parameters:
	//
	//	$id - User record id
	//
	// Returns:
	//
	//	Associative array
	//
	public function GetRecord ( $id ) {
		freemed::acl_enforce( 'admin', 'config' );
		return $GLOBALS['sql']->queryRow( 'SELECT * FROM user WHERE id=' . $GLOBALS['sql']->quote( $id ).' AND id>1' );
	} // end method GetRecord

	// Method: GetRecords
	//
	//	Get list of records for the user table.
	//
	// Parameters:
	//
	//	$limit - (optional) Limit to maximum number of records to return
	//
	// Return:
	//
	//	Array of hashes.
	//
	public function GetRecords ( $limit = 100, $criteria_field = NULL, $criteria = NULL ) {
		freemed::acl_enforce( 'admin', 'config' );

		// Check to make sure that if $criteria_field is declared that it's valid
		$variables = array (
			'username',
			'userdescrip'
		);
		if ( $criteria_field and $criteria ) {
			$found = false;
			foreach ( $variables AS $v ) {
				if ( $v == $criteria_field ) { $found = true; }
			}
			if ( ! $found ) {
				syslog( LOG_INFO, "GetRecords| invalid value ${criteria_field} attempted for indexing value" );
				return false;
			}
		}
		$q = "SELECT * FROM user ".( $criteria_field ? " WHERE ${criteria_field} LIKE '".$GLOBALS['sql']->escape( $criteria )."%' " : "" )." AND id>1 ORDER BY username LIMIT ${limit}";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetRecords

	// Method: add
	//
	//	User addition routine.
	//
	// Parameters:
	//
	//	$_param - (optional) Associative array of values. If
	//	specified, _add will run quiet. The associative array
	//	is in the format of sql_name => sql_value.
	//
	// Returns:
	//
	//	Nothing if there are no parameters. If $_param is
	//	specified, _add will return the id number if successful
	//	or false if unsuccessful.
	//
	public function add ( $data ) {
		freemed::acl_enforce( 'admin', 'config' );

		$ourdata = (array) $data;
		$this->add_pre( &$ourdata );
		$GLOBALS['sql']->load_data( $ourdata );

		$query = $GLOBALS['sql']->insert_query (
			'user',
			$this->variables
		);
		$result = $GLOBALS['sql']->query ( $query );

		$new_id = $GLOBALS['sql']->lastInsertId( 'user', 'id' );

		// Create user ACL object
		module_function( 'ACL', 'UserAdd', array( $new_id ) );

		// ACL routine for adding all ACL groups
		if ( is_array( $ourdata['useracl'] ) ) {
			foreach ( $ourdata['useracl'] AS $acl ) {
				$o = module_function( 'ACL', 'AddUserToGroup', array ( $new_id, $acl ) );
			}
		}

		// Return user ID
		return $new_id;
	} // end function add

	private function add_pre ( $data ) { 
		// Handle MD5 hashing
		if ( strlen($data['userpassword']) != 32 ) {
			$data['userpassword'] = md5( $data['userpassword'] );
		}
	}

	// Method: del
	//
	//	User deletion id
	//
	// Parameters:
	//
	//	$_param - (optional) Id number for the record to
	//	be deleted. 
	//
	// Returns:
	//
	//	Nothing if there are no parameters. If $_param is
	//	specified, _del will return boolean true or false
	//	depending on whether it is successful.
	//
	// See Also:
	//	<del_pre>
	//
	public function del ( $id ) {
		freemed::acl_enforce( 'admin', 'config' );

		// Protect admin user
		if ( $id + 0 == 1 ) { return false; }

		$this->del_pre( $id + 0 );
		$query = "DELETE FROM user WHERE id = '".addslashes( $id+0 )."'";
		$result = $GLOBALS['sql']->query ( $query );
		return true;
	} // end function del

	protected function del_pre ( $id ) { }

	// Method: mod
	//
	//	User modification routine
	//
	// Parameters:
	//
	//	$data - Hash of data to pass.
	//
	// See Also:
	//	<mod_pre>
	//
	public function mod ( $data ) {
		freemed::acl_enforce( 'admin', 'config' );

		if ( is_array( $data ) ) {
			if ( !$data['id'] ) { return false; }
		} elseif ( is_object( $data ) ) {
			if ( ! $data->id ) { return false; }
		} else {
			return false;
		}

		$ourdata = (array) $data;

		// Protect admin user
		if ( $ourdata['id'] + 0 == 1 ) { return false; }

		$this->mod_pre( &$ourdata );
		$GLOBALS['sql']->load_data( $ourdata );
		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->update_query (
				'user',
				$this->variables,
				array ( "id" => $data['id'] )
			)
		);

		if ( is_array( $data['useracl'] ) ) {
			$groups = module_function ( 'ACL', 'UserGroups' );
			foreach ( $groups AS $group ) {
				$found = false;
				foreach ( $data['useracl'] AS $acl_id ) {
					if ( $group[1] == $acl_id ) { $found = true; }
				}
				
				$inThisGroup = module_function ( 'ACL', 'UserInGroup', array( $data['id'], $group[1] ) );
				if ( $found && !$inThisGroup ) {
					// Need to add
					$o = module_function( 'ACL', 'AddUserToGroup', array ( $data['id'], $group[1] ) );
				}
				if ( !$found && $inThisGroup ) {
					// Need to remove
					$o = module_function( 'ACL', 'RemoveUserFromGroup', array ( $data['id'], $group[1] ) );
				}
			}
		}

		return $result ? true : false;
	} // end function mod

	private function mod_pre ( $data ) { 
		// Handle MD5 hashing
		if ( strlen($data['userpassword']) != 32 ) {
			$data['userpassword'] = md5( $data['userpassword'] );
		}
	}

} // end class UserInterface

?>
