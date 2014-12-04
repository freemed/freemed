<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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
		'userfname',
		'userlname',
		'usermname',
		'usertitle',
		'userlevel',
		'usertype',
		'userfac',
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

	// Method: GetCurrentProvider
	//
	//	Determine provider record associated with current user.
	//
	// Returns:
	//
	//	Integer, 0 if there is none.
	//
	public function GetCurrentProvider ( ) {
		$q = "SELECT IFNULL(userrealphy,0) FROM user WHERE id = ".( $this->user->user_number + 0 );
		return (int) $GLOBALS['sql']->queryOne( $q );
	} // end method GetCurrentProvider

	// Method: CheckDuplicate
	//
	//	Check for duplicate user record in the system by username.
	//
	// Parameters:
	//
	//	$username - Username
	//
	// Returns:
	//
	//	Boolean.
	//
	public function CheckDuplicate( $username ) {
		$q = "SELECT COUNT(*) FROM user WHERE username = " . $GLOBALS['sql']->quote( $username );
		return ((int) $GLOBALS['sql']->queryOne( $q )) > 0;
	} // end method CheckDuplicate

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
	public function GetUsers ( $param = '',$usertype='' ) {
		$criteria = addslashes( $param );
		if (!(strpos($criteria, ',') === false)) {
			list ($last, $first) = explode( ',', $criteria);
		} else {
			if (!(strpos($criteria, ' ') === false)) {
				list ($first, $last) = explode( ' ', $criteria );
			} else {
				$either = $criteria;
			}
		}
		$last = trim( $last );
		$first = trim( $first );
		$either = trim( $either );

		if ($first and $last) {
			$q[] = "( ptlname LIKE '".addslashes($userlname)."%' AND ".
				" userfname LIKE '".addslashes($first)."%' )";
		} elseif ($first) {
                	$q[] = "userfname LIKE '".addslashes($first)."%'";
		} elseif ($last) {
                	$q[] = "userlname LIKE '".addslashes($last)."%'";
		} else {
			$q[] = "userfname LIKE '".addslashes($either)."%'";
			$q[] = "userlname LIKE '".addslashes($either)."%'";
		}
		$condition="";
		$temp="";
		$temp=join(' OR ', $q);
		if($temp!='' && $temp!=NULL)
			$condition=" WHERE (".$temp.") ";
		if($usertype!=""){
			if($condition==""){
					$condition=" WHERE usertype='".$temp."' ";
			}
			else{
				$condition=$condition." AND usertype='".$usertype."' ";
			}
		}
		
		$q = "SELECT CONCAT(userfname,' ',usermname,' ',userlname,', ',usertitle) AS description, u.id AS id FROM user u ".$condition." ORDER BY u.userdescrip";
		//return $q;
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
				syslog( LOG_ERR, "Invalid method called ${v['method']}" );
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
		$user_id = $this->user->user_number;
		
		$usermodules = module_function( 'ACL', 'GetUserPermissions', array ( $user_id ) );
		
		$this->SetConfigValue('usermodules',$usermodules);
		$this->GetUserLeftNavigationMenu();
		$this->GetUserTheme();
		if (is_array($this->user->manage_config)) {
			return $this->user->manage_config;
		} else {
			return array();
		}
		
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
		freemed::acl_enforce( 'admin', 'write' );
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
		freemed::acl_enforce( 'admin', 'write' );

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
		$q = "SELECT id, username, userdescrip, userlevel, usertype, userfac, userphy, userphygrp, userrealphy, usermanageopt, useremail, usersms, usersmsprovider FROM user WHERE".( $criteria_field ? " ${criteria_field} LIKE '".$GLOBALS['sql']->escape( $criteria )."%'  AND" : "" )." id>1 ORDER BY username LIMIT ${limit}";	

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
	public function add ( $data,$blockedACOs=NULL,$allowedACOs=NULL ) {
		freemed::acl_enforce( 'admin', 'write' );
		
		$ourdata = (array) $data;
		$this->add_pre( $ourdata );
		$GLOBALS['sql']->load_data( $ourdata );
		
		$query = $GLOBALS['sql']->insert_query (
			'user',
			$this->variables
		);
		$result = $GLOBALS['sql']->query ( $query );

		$new_id = $GLOBALS['sql']->lastInsertId( 'user', 'id' );

		// Create user ACL object
		module_function( 'ACL', 'UserAdd', array( $new_id ) );

		if($ourdata['useracl'] && !is_array( $ourdata['useracl'] ) ){
			$ourdata['useracl'] = explode(",",$ourdata['useracl']);
		}
		
	
		// ACL routine for adding all ACL groups
		if ( is_array( $ourdata['useracl'] ) ) {
			foreach ( $ourdata['useracl'] AS $acl ) {
				$o = module_function( 'ACL', 'AddUserToGroup', array ( $new_id, $acl ) );
			}
		}
			
		// ACL routine for adding all blockedACOs 
		if ($blockedACOs ) {
			 $blockedACOsSuccess = module_function( 'ACL', 'AddBlockedACOs', array ( $new_id, $blockedACOs ) );
		}
		
		// ACL routine for adding all allowedACOs 
		if ($allowedACOs ) {
			 $allowedACOsSuccess = module_function( 'ACL', 'AddAllowedACOs', array ( $new_id, $allowedACOs ) );
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
		freemed::acl_enforce( 'admin', 'write' );
		
		// Protect admin user
		if ( $id + 0 == 1 ) { return false; }

		$this->del_pre( $id + 0 );
		$query = "DELETE FROM user WHERE id = '".addslashes( $id+0 )."'";
		$result = $GLOBALS['sql']->query ( $query );

		// delete user ACL object
		module_function( 'ACL', 'UserDel', $id);
		module_function( 'ACL', 'DelBlockedACOs', $id);
		module_function( 'ACL', 'DelAllowedACOs', $id);

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
	public function mod ( $data,$blockedACOs=NULL,$allowedACOs=NULL ) {
		freemed::acl_enforce( 'admin', 'write' );

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
		$tempVariables = $this->variables;
		if(!$data['userpassword']) // remove password from variables if no need to change the password
			unset($tempVariables[1]);
		$this->mod_pre( $ourdata );
		$GLOBALS['sql']->load_data( $ourdata );
		$result =  $GLOBALS['sql']->query($GLOBALS['sql']->update_query (
				'user',
				$tempVariables,
				array ( "id" => $data['id'] )
			));
		
		if($data['useracl']){
			if(!is_array( $data['useracl'] )){
				$data['useracl'] = explode(",",$data['useracl']);
			}
		}
		else {
			$data['useracl'] = array($data['useracl']);
		}
		
		// Create user ACL object If not already exists in ACL tables
		module_function( 'ACL', 'UserAdd', array( $data['id'] ) );
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
					$abc = $abc.':rm:';
					$o = module_function( 'ACL', 'RemoveUserFromGroup', array ( $data['id'], $group[1] ) );
				}
			}
		}
		
		
		// ACL routine for adding all blockedACOs 
		if ($blockedACOs ) {
		 	 module_function( 'ACL', 'DelBlockedACOs', $data['id']);
			 $blockedACOsSuccess = module_function( 'ACL', 'AddBlockedACOs', array ( $data['id'], $blockedACOs ) );
		}else{//else remove blocked permission if exists
			module_function( 'ACL', 'DelBlockedACOs', $data['id']);
		}
		
		// ACL routine for adding all allowedACOs 
		if ($allowedACOs ) {
			 module_function( 'ACL', 'DelAllowedACOs', $data['id']);
			 $allowedACOsSuccess = module_function( 'ACL', 'AddAllowedACOs', array ( $data['id'], $allowedACOs ) );
		}else{//else remove blocked permission if exists
			module_function( 'ACL', 'DelAllowedACOs', $data['id']);
		}

		return $result ? true : false;
	} // end function mod

	private function mod_pre ( $data ) { 
		// Handle MD5 hashing
		if ($data['userpassword'] && strlen($data['userpassword']) != 32 ) {
			$data['userpassword'] = md5( $data['userpassword'] );
		}
	}
	
	public function getRel(){
		return freemed::religion_picklist();
	}

	// Method: GetUserTheme
	//
	//	Get list of left Navigation options 
	//
	// Returns:
	//
	//	user theme.
	//
	public function GetUserTheme ( ) {
		$user = freemed::user_cache();

		$theme = $user->getManageConfig('Theme');
		
		if(!$theme)
			$user->setManageConfig( 'Theme', 'chrome' );
		
		return $theme;
	} // end method GetConfigSections

	// Method: GetUserLeftNavigationMenu
	//
	//	Get list of left Navigation options 
	//
	// Returns:
	//
	//	Array of menu options.
	//
	public function GetUserLeftNavigationMenu ( ) {
		$user = freemed::user_cache();
		$user_level = $user->local_record['userlevel'];

		
		$userLeftNavigationMenu = $user->getManageConfig('LeftNavigationMenu');
		
		
		////////System menu/////////
		$this->checkSystemMenu($userLeftNavigationMenu);
		////////////////////////////
		
		////////Patient menu/////////
		$this->checkPatientMenu($userLeftNavigationMenu);
		////////////////////////////////
		
		
		////////Documents menu/////////
		$this->checkDocumentsMenu($userLeftNavigationMenu);
		////////////////////////////////
		
		
		////////Billing menu/////////
		$this->checkBillingMenu($userLeftNavigationMenu);
		////////////////////////////////
		
		
		////////Reporting menu/////////
		$this->checkReportingMenu($userLeftNavigationMenu);
		////////////////////////////////
		
		////////Utilities menu/////////	
		$this->checkUtilitiesMenu($userLeftNavigationMenu);
		////////////////////////////////
		
		
		$user->setManageConfig( 'LeftNavigationMenu', $userLeftNavigationMenu );

		
		return  $userLeftNavigationMenu;//$userLeftNavigationMenu;
	} // end method GetConfigSections



	public function getPermissionsBits($read=0,$write=0,$modify=0,$delete=0,$show=0){
		if($read || $write || $modify || $delete)
			return $read.$write.$modify.$delete.$show;
		
		return false;	
	}

	public function getShowBit($read=0,$write=0,$modify=0,$delete=0,$show=0){
		return $read|$write|$modify|$delete|$show;
	}

	// Method: checkSystemMenu
	//
	//param	
	//      userLeftNavigationMenu passed by reference 
	//
	public function checkSystemMenu (&$userLeftNavigationMenu ) {
		$SystemAccessOptionsDB = $userLeftNavigationMenu['System'];
		
		$SystemAccessOptions['Dashboard'] = 1;
		
		//Schedulaing Group stuff
		$schedulerTableWrite   = freemed::acl( 'SchedulerTable', 'write' )?1:0;
		$schedulerTableRead    = freemed::acl( 'SchedulerTable', 'read' )?1:0;
		$schedulerTableDelete  = freemed::acl( 'SchedulerTable', 'delete' )?1:0;
		$schedulerTableModify  = freemed::acl( 'SchedulerTable', 'modify' )?1:0;
		$schedulerTable        = $this->getShowBit($schedulerTableRead,$schedulerTableWrite,$schedulerTableModify,$schedulerTableDelete);
		$SystemAccessOptions['Scheduler'] = $schedulerTable;
		if(!$SystemAccessOptions['Scheduler'])
			unset($SystemAccessOptions['Scheduler']);

		//Schedulaing Group stuff
		$messagesWrite   = freemed::acl( 'SchedulerTable', 'write' )?1:0;
		$messagesRead    = freemed::acl( 'SchedulerTable', 'read' )?1:0;
		$messagesDelete  = freemed::acl( 'SchedulerTable', 'delete' )?1:0;
		$messagesModify  = freemed::acl( 'SchedulerTable', 'modify' )?1:0;
		$messages        = $this->getShowBit($messagesRead,$messagesWrite,$messagesModify,$messagesDelete);		
		$SystemAccessOptions['Messages'] = $messages;
		if(!$SystemAccessOptions['Messages'])
			unset($SystemAccessOptions['Messages']);
		
		if(strlen(serialize($SystemAccessOptions)) != (strlen(serialize($SystemAccessOptionsDB))-13))
			$userLeftNavigationMenu['System'] = $SystemAccessOptions;
	} // end method checkSystemMenu

	 // This will be uncommented when New ACL Implementation will be enabled
	public function checkPatientMenu (&$userLeftNavigationMenu ) {
		$PatientAccessOptionsDB =  $userLeftNavigationMenu['Patient'];
		
		//Patient sub-menu stuff
		$emrRead       =  freemed::acl( 'emr', 'read' )?1:0;
		$emrWrite      =  freemed::acl( 'emr', 'write' )?1:0;
		$emrModify     =  freemed::acl( 'emr', 'modify' )?1:0;
		$emrDelete     =  freemed::acl( 'emr', 'delete' )?1:0;
		$patientSearch =  $this->getShowBit($emrRead,0,0,0,0);
		$patientForm   =  $this->getShowBit($emrRead,$emrWrite,$emrModify,$emrDelete);
		
		//Patient Group stuff
		$groupWrite   = freemed::acl( 'CalendarGroup', 'write' )?1:0;
		$groupRead    = freemed::acl( 'CalendarGroup', 'read' )?1:0;
		$groupDelete  = freemed::acl( 'CalendarGroup', 'delete' )?1:0;
		$groupModify  = freemed::acl( 'CalendarGroup', 'modify' )?1:0;
		$group        = $this->getShowBit($groupRead,$groupWrite,$groupModify,$groupDelete);
		
		//Patient Group stuff
		$callinWrite   = freemed::acl( 'Callin', 'write' )?1:0;
		$callinRead    = freemed::acl( 'Callin', 'read' )?1:0;
		$callinDelete  = freemed::acl( 'Callin', 'delete' )?1:0;
		$callinModify  = freemed::acl( 'Callin', 'modify' )?1:0;
		$callin        = $this->getShowBit($callinRead,$callinWrite,$callinModify,$callinDelete);
		
		//RXRefill stuff
		$rxRefillWrite  = freemed::acl( 'RxRefillRequest', 'write' )?1:0;
		
		//Patient Tag stuff
		$patientTagRead  = freemed::acl( 'PatientTag', 'read' )?1:0;
		
		
		if($patientForm || $patientSearch || $group || $callin || $rxRefillWrite || $patientTagWrite){

			$PatientAccessOptions['Search']      =  $patientSearch;
			if(!$PatientAccessOptions['Search'])
				unset($PatientAccessOptions['Search']);
			
			$PatientAccessOptions['New Patient'] =  $patientForm;
			if(!$PatientAccessOptions['New Patient'])
				unset($PatientAccessOptions['New Patient']);
			
			$PatientAccessOptions['Groups']      =  $group;
			if(!$PatientAccessOptions['Groups'])
				unset($PatientAccessOptions['Groups']);
				
			$PatientAccessOptions['Call In']     =  $callin;
			if(!$PatientAccessOptions['Call In'])
				unset($PatientAccessOptions['Call In']);
				
			$PatientAccessOptions['Rx Refill']   =  $rxRefillWrite;
			if(!$PatientAccessOptions['Rx Refill'])
				unset($PatientAccessOptions['Rx Refill']);
				
			$PatientAccessOptions['Tag Search']  =  $patientTagRead;
			if(!$PatientAccessOptions['Tag Search'])
				unset($PatientAccessOptions['Tag Search']);
				
			if(strlen(serialize($PatientAccessOptions)) != (strlen(serialize($PatientAccessOptionsDB))-13))
				$userLeftNavigationMenu['Patient'] = $PatientAccessOptions;
		}
		else
		 unset($userLeftNavigationMenu['Patient']);
		
	} // end method checkPatientMenu
	

	// Method: checkDocumentsMenu
	//
	//param	
	//      userLeftNavigationMenu passed by reference 
	//
	public function checkDocumentsMenu (&$userLeftNavigationMenu ) {
		$DocumentsAccessOptionsDB = $userLeftNavigationMenu['Documents'];

		//Unfiled Documents stuff
		$unfiledRead    = freemed::acl( 'UnfiledDocuments', 'read' )?1:0;
		$unfiledWrite   = freemed::acl( 'UnfiledDocuments', 'write' )?1:0;
		$unfiledModify  = freemed::acl( 'UnfiledDocuments', 'modify' )?1:0;
		$unfiledDelete  = freemed::acl( 'UnfiledDocuments', 'delete' )?1:0;
		$unfiled        = $this->getShowBit($unfiledRead,$unfiledWrite,$unfiledModify,$unfiledDelete);

		//Unread Documents stuff
		$unReadRead    = freemed::acl( 'UnreadDocuments', 'read' )?1:0;
		$unReadWrite   = freemed::acl( 'UnreadDocuments', 'write' )?1:0;
		$unReadModify  = freemed::acl( 'UnreadDocuments', 'modify' )?1:0;
		$unReaddDelete = freemed::acl( 'UnreadDocuments', 'delete' )?1:0;
		$unRead        = $this->getShowBit($unReadRead,$unReadWrite,$unReadModify,$unReaddDelete);
		
		if($unfiled || $unRead){
			
			$DocumentsAccessOptions['Unfiled'] = $unfiled;
			if(!$DocumentsAccessOptions['Unfiled'])
				unset($DocumentsAccessOptions['Unfiled']);	
			
			$DocumentsAccessOptions['Unread'] = $unRead;
			if(!$DocumentsAccessOptions['Unread'])
				unset($DocumentsAccessOptions['Unread']);	
			
			if(strlen(serialize($DocumentsAccessOptions)) != (strlen(serialize($DocumentsAccessOptionsDB))-13))
			$userLeftNavigationMenu['Documents'] = $DocumentsAccessOptions;
	
		}else
			unset($userLeftNavigationMenu['Documents']);			
	} // end method checkDocumentsMenu
	

	// This will be uncommented when New ACL Implementation will be enabled
	// Method: checkBillingMenu
	//
	//param	
	//      userLeftNavigationMenu passed by reference 
	//
	public function checkBillingMenu (&$userLeftNavigationMenu ) {
		$BillingAccessOptionsDB = $userLeftNavigationMenu['Billing'];
		
		//Account Recievable Stuff
		$accountReceivable = $this->getShowBit(1,1,1,1,1);
		
		// Claims Manager Stuff
		$claimLogWrite = freemed::acl( 'ClaimLogTable', 'write' )?1:0;
		$claimLogRead  = freemed::acl( 'ClaimLogTable', 'read' )?1:0;
		$claimLog      =  $this->getShowBit($claimLogRead,$claimLogWrite,0,0,0 );
		
		// Remitt Billing Stuff
		$remittBillingTransportWrite = freemed::acl( 'RemittBillingTransport', 'write' )?1:0;
		$remittBillingTransportRead  = freemed::acl( 'RemittBillingTransport', 'read' )?1:0;
		$remittBillingTransport      =  $this->getShowBit($remittBillingTransportRead,$remittBillingTransportWrite,0,0,0 );

		// Super Billing Stuff
		$superBillRead  = freemed::acl( 'RemittBillingTransport', 'read' )?1:0;
		$superBill      =  $this->getShowBit($superBillRead,0,0,0,0 );		
		
		
		if($claimLog || $remittBillingTransport || $superBill){
			
			$BillingAccessOptions['Accounts Receivable']     =  $accountReceivable;
			if(!$BillingAccessOptions['Accounts Receivable'])
				unset($BillingAccessOptions['Accounts Receivable']);
				
			$BillingAccessOptions['Claims Manager']     =  $claimLog;
			if(!$BillingAccessOptions['Claims Manager'])
				unset($BillingAccessOptions['Claims Manager']);
				
			$BillingAccessOptions['Remitt Billing']     =  $remittBillingTransport;	
			if(!$BillingAccessOptions['Remitt Billing'])
				unset($BillingAccessOptions['Remitt Billing']);	

			$BillingAccessOptions['Remitt Billing']     =  $remittBillingTransport;	
			if(!$BillingAccessOptions['Remitt Billing'])
				unset($BillingAccessOptions['Remitt Billing']);	

			$BillingAccessOptions['Super Bills']     =  $remittBillingTransport;	
			if(!$BillingAccessOptions['Super Bills'])
				unset($BillingAccessOptions['Super Bills']);	

			if(strlen(serialize($BillingAccessOptions)) != (strlen(serialize($BillingAccessOptionsDB))-13))
				$userLeftNavigationMenu['Billing'] = $BillingAccessOptions;
		}else
		    unset($userLeftNavigationMenu['Billing']);
	} // end method checkBillingMenu
	
	

	// This will be uncommented when New ACL Implementation will be enabled
	// Method: checkReportingMenu
	//
	//param	
	//      userLeftNavigationMenu passed by reference 
	//
	public function checkReportingMenu (&$userLeftNavigationMenu ) {
		$ReportingAccessOptionsDB = $userLeftNavigationMenu['Reporting'];
		$reportEngineRead  = freemed::acl( 'reporting', 'read' )?1:0;
		$reportEngineWrite = freemed::acl( 'reporting', 'write' )?1:0;
		$reportEngine      = $this->getShowBit($reportEngineRead,$reportEngineWrite,0,0,0 );		
		
		$reportingPrintLogRead  = freemed::acl( 'ReportingPrintLog', 'read' )?1:0;
		$reportingPrintLogWrite = freemed::acl( 'ReportingPrintLog', 'delete' )?1:0;
		$reportingPrintLog      = $this->getShowBit($reportingPrintLogRead,0,0,$reportingPrintLogWrite,0 );		
		
		if($reportEngine || $reportingPrintLog){
			
			$ReportingAccessOptions['Reporting Engine'] = $reportEngine;
			if(!$ReportingAccessOptions['Reporting Engine'])
				unset($ReportingAccessOptions['Reporting Engine']);	
			
			$ReportingAccessOptions['Reporting Log'] = $reportingPrintLog;
			if(!$ReportingAccessOptions['Reporting Log'])
				unset($ReportingAccessOptions['Reporting Log']);	
			
			if(strlen(serialize($ReportingAccessOptions)) != (strlen(serialize($ReportingAccessOptionsDB))-13))
				$userLeftNavigationMenu['Reporting'] = $ReportingAccessOptions;
	
		}else
			unset($userLeftNavigationMenu['Reporting']);
	} // end method checkReportingMenu
	
	
	// Method: checkUtilitiesMenu
	//
	//param	
	//      userLeftNavigationMenu passed by reference 
	//
	public function checkUtilitiesMenu (&$userLeftNavigationMenu ) {
		//Tools stuff
		$toolsRead = freemed::acl( 'Tools', 'read' )?1:0;
		$toolsWrite = freemed::acl( 'Tools', 'write' )?1:0;
		$toolsModify = freemed::acl( 'Tools', 'modify' )?1:0;

		$tools = $this->getShowBit($toolsRead,$toolsWrite,$toolsModify);
		
		//Admin stuff
		$adminRead   = freemed::acl( 'admin', 'read' )?1:0;
		$adminWrite  = freemed::acl( 'admin', 'write' )?1:0;
		$adminDelete = freemed::acl( 'admin', 'delete' )?1:0;
		$adminModify = freemed::acl( 'admin', 'modify' )?1:0;
		$admin = $this->getShowBit($adminRead,$adminWrite,$adminDelete,$adminModify);
		
		//ACL stuff
		$aclRead   = freemed::acl( 'acl', 'read' )?1:0;
		$aclWrite  = freemed::acl( 'acl', 'write' )?1:0;
		$aclDelete = freemed::acl( 'acl', 'delete' )?1:0;
		$aclModify = freemed::acl( 'acl', 'modify' )?1:0;
		$acl = $this->getShowBit($aclRead,$aclWrite,$aclDelete,$aclModify);
		
		if($tools || $admin || $acl){
			$UtilitiesAccessOptionsDB = $userLeftNavigationMenu['Tools'];
			
			$UtilitiesAccessOptions['Tools']         = $tools;
			if(!$UtilitiesAccessOptions['Tools'])
				unset($UtilitiesAccessOptions['Tools']);
			
	   	        $UtilitiesAccessOptions['Support Data']         = $admin;
	   	        if(!$UtilitiesAccessOptions['Support Data'])
				unset($UtilitiesAccessOptions['Support Data']);

	   	        $UtilitiesAccessOptions['Field Checker']         = $admin;
	   	        if(!$UtilitiesAccessOptions['Field Checker'])
				unset($UtilitiesAccessOptions['Field Checker']);
			
			$UtilitiesAccessOptions['User Management']      = $admin;
			if(!$UtilitiesAccessOptions['User Management'])
				unset($UtilitiesAccessOptions['User Management']);
			
			$UtilitiesAccessOptions['System Configuration'] = $admin;
			if(!$UtilitiesAccessOptions['System Configuration'])
				unset($UtilitiesAccessOptions['System Configuration']);
			
			$UtilitiesAccessOptions['DB Administration']    = $admin;
			if(!$UtilitiesAccessOptions['DB Administration'])
				unset($UtilitiesAccessOptions['DB Administration']);
				
			$UtilitiesAccessOptions['ACL']    = $acl;
			if(!$UtilitiesAccessOptions['ACL'])
				unset($UtilitiesAccessOptions['ACL']);	
				
			if(strlen(serialize($UtilitiesAccessOptions)) != (strlen(serialize($UtilitiesAccessOptionsDB))-13))
			    $userLeftNavigationMenu['Utilities'] = $UtilitiesAccessOptions;
		} 
		else
			unset($userLeftNavigationMenu['Utilities']);
	} // end method checkUtilitiesMenu
	
	//Method: getDashBoardDetails
	//		
	//	
	//return
	//        hashes containing unread messages count,unfiled documents count etc 
	public function getDashBoardDetails(){
		// Messages
		$messages = createObject("org.freemedsoftware.module.MessagesModule");
		$return['unreadMsgs'] = $messages->UnreadMessages()."";
		// unfiled documents
		$unfiledDocuments = createObject("org.freemedsoftware.module.UnfiledDocuments");
		$return['unfiledDocuments'] = $unfiledDocuments->GetCount()."";
		//Action Items
		$actionItems = createObject("org.freemedsoftware.api.ActionItems");
		$return['actionItems'] = $actionItems->getActionItemsCount()."";
		return $return;
	} // end getDashBoardDetails method
	
	//Method: GetUserType
	//
	//return
	//       return string containging usertype
	public function GetUserType(){
		return $this->user->local_record['usertype'];
	}//end method GetUserType
	
	//Method: CheckDupilcate
	//
	//return
	//       return boolean 
	public function CheckDupilcate($user_name){
		$q="select id from user where username = ".$GLOBALS['sql']->quote($user_name);
		$return = $GLOBALS['sql']->queryAll( $q );
		return $return?true:false;
	}//end method CheckDupilcate	
	
} // end class UserInterface

?>
