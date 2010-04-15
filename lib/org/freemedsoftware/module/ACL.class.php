<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

class ACL extends SupportModule {
	// __("Access Control Lists")
	var $MODULE_NAME = "Access Control Lists";
	var $MODULE_VERSION = "0.8.0.1";
	var $MODULE_DESCRIPTION = "Access Control Lists give granular access control to every part of the FreeMED system. This module is a wrapper for the phpgacl package.";

	var $MODULE_HIDDEN = true;
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "a6ac7151-bc5a-4ae1-853a-cec0b53a2ea6";

	var $table_name = 'acl';

	public function __construct ( ) {
		$this->_SetMetaInformation('global_config_vars', array ( 'acl_enable', 'acl_patient' ) );
		$this->_SetMetaInformation('global_config', array (
			__("Enable ACL System") =>
			'html_form::select_widget("acl_enable", '.
				'array ( '.
					'"'.__("no").'" => "0", '.
					'"'.__("yes").'" => "1", '.
				') '.
			')',

			__("Enable Patient ACLs") =>
			'html_form::select_widget("acl_patient", '.
				'array ( '.
					'"'.__("no").'" => "0", '.
					'"'.__("yes").'" => "1", '.
				') '.
			')'

		) );

		// Set appropriate handlers for dealing with ACLs
		$this->_SetHandler('PatientAdd', 'acl_patient_add');
		$this->_SetHandler('UserAdd', 'acl_user_add');
		
		parent::__construct();
	} // end constructor ACL

	// Method: acl_patient_add
	//
	//	Handler for dealing with adding ACLs for a patient when
	//	they are added to the system. This should allow default
	//	access for a patient based on simple information.
	//
	// Parameters:
	//
	//	$id - Record ID for the new patient record.
	//
	//	$current_user - (optional) Whether or not to add the current
	//	user to the ACL list. Defaults to true.
	//
	public function acl_patient_add ( $pid, $current_user = true ) {
		global $this_user;

		// Create ACL manipulation class (cached, of course)
		$acl = $this->acl_object();

		// Create an AXO object
		$axo = $acl->add_object(
			'patient', // AXO group name
			'Patient '.$pid, // Proper name ??
			'patient_'.$pid, // ACL identifier
			$pid, // display order
			0, // hidden
			'AXO' // identify this as an AXO
		);
		//print "made new object with axo = ".$axo."<br/>\n";

		// If this fails, we die out here
		if (!$axo) { trigger_error(__("Failed to create patient AXO ACL control object."), E_ERROR); }
		// Create user object if it doesn't exist yet
		$this_user = freemed::user_cache( );

		$_pat = $GLOBALS['sql']->get_link( 'patient', $pid );

		// Get ptpcp, ptphy{1,2,3,4}, ptdoc and add their respective
		// user numbers to the ACL.
		$to_add = array (
			$this->get_user_from_phy($_pat['ptpcp']),
			$this->get_user_from_phy($_pat['ptdoc']),
			$this->get_user_from_phy($_pat['ptphy1']),
			$this->get_user_from_phy($_pat['ptphy2']),
			$this->get_user_from_phy($_pat['ptphy3']),
			$this->get_user_from_phy($_pat['ptphy4'])
		);
		if ($current_user) { $to_add[] = $this_user->user_number; }

		// Make sure there are no zeros
		foreach ($to_add AS $v) { if ($v) { $u[$v] = $v; } }
		foreach ($u AS $v) { $users[] = 'user_'.$v; }

		// This is a *nasty* hack, but otherwise we loop forever.
		//include_once(dirname(__FILE__).'/patient_acl.emr.module.php');

		// Add the current user to have access
		//print "access for"; print_r($users); print "<br/>\n";
		module_function('patientacl', 'add_acl', array(
			$pid,
			array (
				'add',
				'view',
				'modify',
				'delete'
			),
			$users,
			$this->acl_object()
		));

		// Send back the appropriate ACL id (AXO)
		return $axo;
	} // end method acl_patient_add

	// Method: UserAdd
	//
	//	Add an ACL ARO object in the "individual" section of the
	//	ARO objects for the specified user.
	//
	// Parameters:
	//
	//	$id - Record ID for the user in question
	//
	public function UserAdd ( $id ) {
		freemed::acl_enforce( 'admin', 'write' );

		// Get the user record in question to play with
		$user = CreateObject('org.freemedsoftware.core.User', $id);

		// Create ACL manipulation class
		$acl = $this->acl_object();

		// Create an ARO object
		$acl_id = $acl->add_object(
			'user', // ARO group name
			$user->local_record['username'], // Proper name
			$id, // ACL identifier
			0, // display order
			0, // hidden
			'ARO' // identify this as an ARO
		);
		//print "made new object with acl_id = ".$acl_id."<br/>\n";

		// Send back the new ID, in case we need it for anything
		return $acl_id;
	} // end method UserAdd

	// Method: UserDel
	//
	//	Add an ACL ARO object in the "individual" section of the
	//	ARO objects for the specified user.
	//
	// Parameters:
	//
	//	$id - Record ID for the user in question
	//
	public function UserDel ( $id ) {
		freemed::acl_enforce( 'admin', 'delete' );
		// Create ACL manipulation class
		$acl = $this->acl_object();

		$aro_id = $acl->get_object_id('user',$id,'ARO');

		// delete an ARO object
		$acl_result = $acl->del_object(
			$aro_id, // ACL identifier
			'ARO', // identify this as an ARO
			true // Erase all references from all acl tables
		);

		// Send back true if deleted successfully or false in failure
		
		return $acl_result;
	} // end method UserAdd

	// Method: AddUserToGroup
	//
	// Parameters:
	//
	//	$user - User ID
	//
	//	$group - Group ID
	//
	// Returns:
	//
	//	Boolean, successful.
	//
	public function AddUserToGroup ( $user, $group ) {
		freemed::acl_enforce( 'admin', 'write' );

		$o = $this->acl_object( );
		return $o->add_group_object(
			$group, // $group_id,
			'user', // $object_section_value,
			$user, // $object_value, 
			'ARO'
		);
	} // end method AddUserToGroup

	// Method: RemoveUserFromGroup
	//
	// Parameters:
	//
	//	$user - User ID
	//
	//	$group - Group ID
	//
	// Returns:
	//
	//	Boolean, successful.
	//
	public function RemoveUserFromGroup ( $user, $group ) {
		freemed::acl_enforce( 'admin', 'delete' );

		$o = $this->acl_object( );
		return $o->del_group_object(
			$group, // $group_id,
			'user', // $object_section_value,
			$user, // $object_value, 
			'ARO'
		);
	} // end method RemoveUserFromGroup

	// Method: acl_object
	//
	//	Simple way to get complex ACL API object
	//
	// Returns:
	//
	//	ACL API object
	//
	private function acl_object ( ) {
		static $_obj;
		if (!isset($_obj)) {
		LoadObjectDependency ('org.freemedsoftware.acl.gacl');
		$_obj = CreateObject('org.freemedsoftware.acl.gacl_api',
			array (
				// Unfortunately, we duplicate to avoid
				// security risks from the global array having
				// database information.
				'db_type' => 'mysql',
				'db_host' => DB_HOST,
				'db_user' => DB_USER,
				'db_password' => DB_PASSWORD,
				'db_name' => DB_NAME,
				'db_table_prefix' => 'acl_',
				// Caching and security
				'debug' => false,
				'caching' => true,
				'force_cache_expire' => true,
				'cache_expire_time' => 600
			)
		);
		}
		return $_obj;
	} // end method acl_object

	// Method: get_user_from_phy
	//
	//	Lookup user by provider, with caching
	//
	// Parameters:
	//
	//	$phy - Provider id
	//
	// Returns:
	//
	//	User id
	//
	private function get_user_from_phy ( $phy ) {
		static $_cache;
		if (!$phy) { return 0; }
		if (!isset($_cache[$phy])) {	
			$select = "SELECT * FROM user WHERE userrealphy='".addslashes($phy)."' AND usertype='phy'";
			$query = $GLOBALS['sql']->queryAll($select);
			foreach ($query AS $r) {
				$_cache[$phy] = $r['id'];
			}
		}
		return $_cache[$phy];
	} // end method get_user_from_phy

	// Method: UserGroups
	//
	//	Get list of user groups (AROs)
	//
	// Returns:
	//
	//	Array of array [ key, value ]
	//
	public function UserGroups ($returnHashes=FALSE ) {
		
		$acl = $this->acl_object();
		$raw = $acl->sort_groups('ARO');
		
		foreach ($raw[0] AS $key => $value) {
			if ($value=='Users') { $users = $key; }
		}
		if (!isset($users)) { trigger_error("Should never get here!", E_USER_ERROR); }
		foreach ( $raw[$users] AS $k => $v ) {
			if($returnHashes)
				$return[] = array('id'=>''.$k,'groupname'=>$v);
			else
				$return[] = array ( $v, $k );
		}
		
		return $return;
	} // end method UserGroups


	// Method: GetUserGroups
	//
	//	Get list of  user groups (AROs)
	//
	//param
	//      userId
	//
	// Returns:
	//
	//	Array of array [ key, value ]
	//
	public function GetUserGroups ($userId ) {
		$acl = $this->acl_object();
		$aro_id = $acl->get_object_id('user',$userId,'ARO');
		$return = $acl->get_object_groups($aro_id);
		
	
	return $return;
	}
	// Method: GetUserGroupNames
	//
	//	Get list of  user group names(AROs)
	//
	//param
	//      userId
	//
	// Returns:
	//
	//	Array of array [ key, value ]
	//
	public function GetUserGroupNames ($userId ) {
		
		$acl = $this->acl_object();
		$aro_id = $acl->get_object_id('user',$userId,'ARO');
		$return = $this->getNames($aro_id);
		
	
		return $return;
	}
	
	private function getNames($aro_id)
	{
		$q="SELECT gm.group_id,g.name FROM acl_groups_aro_map gm left join acl_aro_groups g on g.id = gm.group_id  WHERE gm.aro_id=".$GLOBALS['sql']->quote( $aro_id );
		return $GLOBALS['sql']->queryAll( $q );	
	}
	
	
	public function getIDByName($name)
	{
		
		$q="SELECT g.id FROM acl_aro_groups g WHERE g.name='".$GLOBALS['sql']->quote( $aro_id )."'";
		
		
		$result= $GLOBALS['sql']->queryAll( $q );	
		
		if(count($result)==0)
		{
			return 0;
		}
		else
		{
			
			return $result[0]['id']+0;
		}
	}
	// end method UserGroups

	// Method: UserInGroup
	//
	//	Determine if the specified user is a member of the 
	//	ACL ARO group specified.
	//
	// Parameters:
	//
	//	$user - User ID
	//
	//	$group - ACL ARO group ID
	//
	// Returns:
	//
	//	Boolean, membership status.
	//
	public function UserInGroup( $user, $group ) {
		$obj = $this->acl_object( );
		$items = $obj->get_group_objects( $group, 'ARO' );
		if ( is_array( $items['user'] ) ) {
			foreach ( $items['user'] AS $i ) {
				if ( $i == $user ) { return true; }
			}
		}

		// All else fails, return false
		return false;
	} // end method UserInGroup

	function _drop_old_tables () {
		$tables = array (
			'acl',
			'acl_sections',
			'acl_seq',
			'aco',
			'aco_map',
			'aco_sections',
			'aco_sections_seq',
			'aco_seq',
			'aro',
			'aro_groups',
			'aro_groups_id_seq',
			'aro_groups_map',
			'aro_map',
			'aro_sections',
			'aro_sections_seq',
			'aro_seq',
			'axo',
			'axo_groups',
			'axo_groups_id_seq',
			'axo_groups_map',
			'axo_map',
			'axo_sections',
			'axo_sections_seq',
			'axo_seq',
			'groups_aro_map',
			'groups_axo_map',
			'phpgacl'
		);
		foreach ($tables AS $t) {
			$GLOBALS['sql']->query('DROP TABLE acl_'.$t);
		}
	} // end method _drop_old_tables
	
	
	// Method: GetAllPermissions
	//
	//	Get list of all Permissions (ACOs)
	//
	// Returns:
	//
	//	Array of Hashes with value as sub array
	//
	public function GetAllPermissions() {
		freemed::acl_enforce( 'admin', 'read' );
		$acl = $this->acl_object();
		$raw = $acl->get_objects(NULL,1,'ACO');
		
		return $raw;
	} // end method GetAllPermissions

	// Method: GetUserPermissions
	//
	//	Get list of this this user Permissions (ACOs)
	//
	// Returns:
	//
	//	Array of Hashes with value as sub array
	//
	public function GetUserPermissions($user_id) {
		$userGroups =  $this->GetUserGroups($user_id);
		$blockedSections = $this->GetBlockedACOs($user_id);
		
		$allowedSections = $this->GetAllowedACOs($user_id);
		foreach($userGroups as  $group){
			$groupPermissions = $this->GetGroupPermissions($group);
			foreach($groupPermissions as  $section=>$values){
				if($blockedSections[$section])
					$return[$section] = $this->getModulesPermissionsBits($blockedSections[$section],'1');	
				else
					$return[$section] = $this->getModulesPermissionsBits($values);
			}

		}
		
		
		foreach($allowedSections as  $section=>$values)
				$return[$section] = $this->getModulesPermissionsBits($values);
	
		
		return $return;
	} // end method GetAllPermissions
	public function getModulesPermissionsBits($sectionValues,$defaultValue='0'){
		$reverseValue = $defaultValue=='0'?'1':'0';
		$bit['read']=$defaultValue;
		$bit['write']=$defaultValue;
		$bit['delete']=$defaultValue;
		$bit['modify']=$defaultValue;
		$bit['lock']=$defaultValue;
		
		foreach($sectionValues AS $value)
		    $bit[$value] = $reverseValue;
		return $bit['read'].$bit['write'].$bit['delete'].$bit['modify'].$bit['lock'];
	}

	// Method: AddGroupWithPermissions
	//
	//	Add new Group with permissions (ACOs) 
	//
	// Returns:
	//
	//	true if Added successfully 
	//	
	public function AddGroupWithPermissions ($data,$aco_array) {
		freemed::acl_enforce( 'acl', 'write' );
		$o = $this->acl_object( );
		$name = $data['groupName'];
		$aro_group_ids = array($o->add_group($name, $name, 10));
		$success = $o->add_acl($aco_array, NULL, $aro_group_ids, NULL, NULL, 1, 1, 1, $name.' Access', 'user');
		return $aro_group_ids[0];
		
	} // end method AddGroupWithPermissions

	// Method: AddGroupWithPermissions
	//
	//	Add new Group with permissions (ACOs) 
	//
	// Returns:
	//
	//	true if Added successfully 
	//	
	public function AddMorePermissions ($group_id,$aco_array) {
		freemed::acl_enforce( 'acl', 'write' );
		$acl = $this->acl_object( );
		$aro_group_ids = array($group_id);
		
		$acl_id = $acl->get_group_acl_id($group_id);
		
		return $acl->append_acl($acl_id, NULL, $aro_group_ids, NULL, NULL, $aco_array) ;
		
	} // end method AddGroupWithPermissions

	// Method: ModGroupWithPermissions
	//
	//	Modify existing group with new provided ACOs
	//
	// Returns:
	//
	//	true if Modified successfully 
	//	
	public function ModGroupWithPermissions ($data,$aco_array) {
		freemed::acl_enforce( 'acl', 'modify' );
		$acl = $this->acl_object( );
		$name = $data['groupName'];
		$group_id = $data['groupId'];
		
		$isModified = $acl->edit_group($group_id, $name, $name, 10);
		if($isModified){
			$acl_id = $acl->get_group_acl_id($group_id);
			$isModified = $acl->edit_acl($acl_id, $aco_array,NULL, array($group_id),NULL, NULL, 1, 1, 1, $name.' Access', 'user');
		}
		
		return $isModified;
		
	} // end method ModGroupWithPermissions

	// Method: GetGroupPermissions
	//
	//	Gets the Permissions(ACOs objects) of passed group_id
	//
	// Returns:
	//
	//	array of hashes with sub hashes in values Map<key, Map<key,vlue>>
	//	
	public function GetGroupPermissions ($group_id) {
		$acl = $this->acl_object( );
		$group_acl_id = $acl->get_group_acl_id($group_id);
		$acl_Object = $acl->get_acl($group_acl_id);
		
		$acos=$acl_Object['aco'];
		return $acos;
		
	} // end method GetGroupPermissions

	// Method: DelGroupWithPermissions
	//
	//	Deletes group and its Permissions(ACOs Objects )
	//
	// Returns:
	//
	//	true if deleted successfully 
	//	
	public function DelGroupWithPermissions ($group_id) {
		freemed::acl_enforce( 'acl', 'delete' );
		$acl = $this->acl_object( );
		$group_acl_id = $acl->get_group_acl_id($group_id);
		$isGroupDeleted = $acl->del_group($group_id, FALSE);
		
		if($isGroupDeleted)
			$isGroupDeleted = $acl->del_acl($group_acl_id);
		
		return $isGroupDeleted;
		
	} // end method GetGroupPermissions
	
	// Method: AddBlockedACOs
	//
	//	Adds blocked acos in acl tables
	//
	//param
	//      userId         id from user table
	//	blockedACOs    hashes with sub arrays in values Map<key, String[]>
	// Returns:
	//
	//	boolean 
	//
	public function AddBlockedACOs ($userId,$blockedACOs ) {
		freemed::acl_enforce( 'admin', 'write' );
		$acl = $this->acl_object();
		return $acl->add_blocked_objects($userId,$blockedACOs);
	}

	// Method: DelBlockedACOs
	//
	//	deleted all related blocked acos from acl tables
	//
	//param
	//      userId         id from user table
	// Returns:
	//
	//	boolean 
	//
	public function DelBlockedACOs ($userId) {
		freemed::acl_enforce( 'admin', 'delete' );
		$acl = $this->acl_object();
		return $acl->del_blocked_objects($userId);
	}

	// Method: GetBlockedACOs
	//
	//	get all related blocked acos from acl tables
	//
	//param
	//      userId         id from user table
	// Returns:
	//
	//	array of hashes with su arrays as values 
	//
	public function GetBlockedACOs ($userId) {
		$acl = $this->acl_object();
		return $acl->get_blocked_objects($userId);
	}


	// Method: AddAllowedACOs
	//
	//	Adds allowed acos in acl tables
	//
	//param
	//      userId         id from user table
	//	allowedACOs    hashes with sub arrays in values Map<key, String[]>
	// Returns:
	//
	//	boolean 
	//
	public function AddAllowedACOs ($userId,$allowedACOs ) {
		freemed::acl_enforce( 'admin', 'write' );
		
		$acl = $this->acl_object();
		return $acl->add_allowed_objects($userId,$allowedACOs);
	}
	
	// Method: DelAllowedACOs
	//
	//	deleted all related allowed acos from acl tables
	//
	//param
	//      userId         id from user table
	// Returns:
	//
	//	boolean 
	//
	public function DelAllowedACOs ($userId) {
		freemed::acl_enforce( 'admin', 'delete' );
		$acl = $this->acl_object();
		return $acl->del_allowed_objects($userId);
	}
	
	// Method: GetAllowedACOs
	//
	//	get all related allowed acos from acl tables
	//
	//param
	//      userId         id from user table
	// Returns:
	//
	//	array of hashes with su arrays as values 
	//
	public function GetAllowedACOs ($userId) {
		$acl = $this->acl_object();
		return $acl->get_allowed_objects($userId);
	}
	
} // end class ACL

register_module('ACL');

?>
