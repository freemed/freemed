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

// TODO migrate all password setting to use setPassword
//	write checkPassword
//	migrate all password checking to use checkPassword 
// 	verify that there are no md5 calls outside of this class!!


// Class: org.freemedsoftware.core.User
//
//	Class container for FreeMED user information.
//
class User {
	var $local_record;                 // local record
	var $user_number;                  // user number (id)
	var $user_level;                   // user level (0..9)
	var $user_name;                    // name of the user
	var $user_descrip;                 // user description
	var $user_phy;                     // number of physician 
	var $manage_config; // configuration for patient management
	var $perms_fac, $perms_phy, $perms_phygrp;

	// Method: User constructor
	//
	// Parameters:
	//
	//	$param - (optional) Specify user identification number.
	//	If not specified, the system will default to using
	//	cookie session data to supply it.
	//
	function __construct ($param=NULL) {
		if (defined('SKIP_SQL_INIT')) { return false; }
		global $__freemed;

		if ($param == NULL) {
			// Check to see if XML-RPC or session data
			if (!defined('SESSION_DISABLE')) {
				$authdata = HTTP_Session2::get( 'authdata' );
			}
			if (is_array($authdata) && array_key_exists('user', $authdata)) {
				$this->user_number = $authdata['user']; 
			} else {
				if (is_array($__freemed)) {
					$this->user_number = $__freemed['basic_auth_id'];
				}
			}
		} else {
			$this->user_number = $param;
		}

		if ($this->user_number === NULL) {
			$this->user_level = 0;
			return;
		}

		// Check for cached copy
		if (!isset($GLOBALS['__freemed']['cache']['user'][$this->user_number])) {
			// Retrieve copy
			$this->local_record = $GLOBALS['sql']->get_link ( 'user', $this->user_number );

			// Store in the cache
			$GLOBALS['__freemed']['cache']['user'][$this->user_number] = $this->local_record;
		} else {
			// Pull copy from the cache
			$this->local_record = $GLOBALS['__freemed']['cache']['user'][$this->user_number];
		}

		if ( $this->local_record instanceof PEAR_Error ) {
			return false;
		}

		if (array_key_exists('username', $this->local_record)) {
		$this->user_name    = stripslashes($this->local_record["username"]);
		$this->user_descrip = stripslashes($this->local_record["userdescrip"]);
		$this->user_level   = $this->local_record["userlevel"  ];
		$this->user_phy     = $this->local_record["userrealphy"];
		$this->perms_fac    = $this->local_record["userfac"    ]; 
		$this->perms_phy    = $this->local_record["userphy"    ];
		$this->perms_phygrp = $this->local_record["userphygrp" ];
		}

		// special root stuff
		if ($this->user_number == 1) $this->user_level = 9;

		// Map configuration vars
		$this->manage_config = unserialize(array_key_exists('usermanageopt', $this->local_record) ? $this->local_record['usermanageopt'] : "");
	} // end constructor

	// Method: getDescription
	//
	//	Retrieve description of current user. (Usually their name)
	//
	// Returns:
	//
	//	Description of the current user object.
	//
	public function getDescription ($no_parameters = "") {
		if (empty($this->user_descrip)) return __("(no description)");
		return ($this->user_descrip);
	} // end function getDescription

	// Method: getPhysician
	//
	//	Get the provider associated with the current user object.
	//
	// Returns:
	//
	//	Record id of provider record, if one exists, otherwise
	//	zero.
	//
	public function getPhysician ($no_parameters = "") {
		return ($this->user_phy)+0;
	} // end function getPhysician

	// Method: CachedACL
	//
	//	Cached simple ACL lookups.
	//
	// Parameters:
	//
	//	$category - 
	//
	//	$permission -
	//
	// Returns:
	//
	//	Boolean.
	//
	public function CachedACL( $category, $permission ) {
		static $cache;
		$key = "${category}:${permission}";

		if ( !isset( $cache[ $key ] ) ) {
			$cache[ $key ] = freemed::acl( $category, $permission );
		}

		return $cache[ $key ];
	} // end method CachedACL

	// Method: getFaxesInQueue
	//
	//	Get list of faxes in queue to check
	//
	// Returns:
	//
	//	Array of fax ids, or NULL if there are none
	public function getFaxesInQueue ( ) {
		$query = "SELECT * FROM faxstatus WHERE fsuser='".addslashes($this->user_number)."'";
		$result = $GLOBALS['sql']->queryAll($query);
		foreach ($result AS $r) {
			if ($r['fsid']) { $f[$r['id']] = $r['id']; }
		}
		if (is_array($f)) { return $f; } 
		return NULL;
	} // end method getFaxesInQueue

	// Method: getFaxDescription
	//
	//	Gives stored description of fax in queue by id number.
	//
	// Parameters:
	//
	//	$fid - Fax ID
	//
	// Returns:
	//
	//	String containing description
	//
	public function getFaxDescription ( $fid ) {
		$r = $this->getFaxDetails ( $fid );
		return sprintf(__("Faxed to %s"), $r['fsdestination']);
	} // end method getFaxDescription

	// Method: getFaxDetails
	//
	// Parameters:
	//
	//	$fid - Fax ID
	//
	// Returns:
	//
	//	Associative array
	//
	public function getFaxDetails ( $fid ) {
		$query = "SELECT * FROM faxstatus WHERE id='".addslashes($fid)."'";
		$result = $GLOBALS['sql']->queryRow($query);
		return $result;
	} // end method getFaxDetails

	// Method: setFaxInQueue
	//
	//	Set a fax to be in the queue to check.
	//
	// Parameters:
	//
	//	$fid - Fax id
	//
	//	$patient - Patient id
	//
	//	$number - Destination number
	//
	//	$module - Module id
	//
	//	$record - Record id
	//
	//	$info - (optional) Textual description of fax to be stored in queue.
	//
	public function setFaxInQueue ( $fid, $patient, $number, $module=NULL, $record=NULL, $info = NULL ) {
		$q = $GLOBALS['sql']->insert_query(
			'faxstatus',
			array(
				'fsid' => $fid,
				'fsmodule' => $module,
				'fsrecord' => $record,
				'fsuser' => $this->user_number,
				'fsdestination' => $number,
				'fsstatus' => '',
				'fspatient' => $patient,
			)
		);
		$GLOBALS['sql']->query($q);
	} // end method setFaxInQueue

	// Method: removeFaxFromQueue
	//
	// Parameters:
	//
	//	$id - Fax id (fid), not record id
	//
	public function removeFaxFromQueue ( $id ) {
		$q = "DELETE FROM faxstatus WHERE id='".addslashes($id)."'";
		$GLOBALS['sql']->query( $q );
	} // end method removeFaxFromQueue

	// Method: faxNotify
	//
	//	Create Javascript alerts for finished faxes.
	//
	// Returns:
	//
	//	Javascript code (in SCRIPT tags) or NULL if nothing.
	//
	public function faxNotify ( ) {
		if (!($fax = $this->getFaxesInQueue())) { return ''; }
		$f = CreateObject( 'org.freemedsoftware.core.Fax' );
		foreach ($fax AS $k => $v) {
			$d = $this->getFaxDetails( $k );
			$st = $f->State($d['fsid']);
			if ($st == 1) {
				$tmp = sprintf(
					__("Fax job %d to %s successful."),
					$d['fsid'], $f->GetNumberFromId($d['fsid'])
					);
				$messages[$tmp] = $tmp;
				if ($d['fsmodule']) {
					$_cache = freemed::module_cache();
					module_function(
						'annotations',
						'createAnnotation',
						array(
							$d['fsmodule'],
							$d['fsrecord'],
							sprintf(__("Faxed to %s"), $f->GetNumberFromId($d['fsid']))
						)
					);
				}
				$remove[$k] = $k;
			} elseif (is_array($st) and $st[0] == -1) {
				$messages[] = sprintf(
					__("Fax job %d (%s) failed with '%s'."),
					$d['fsid'], $f->GetNumberFromId($d['fsid']), $st[1]
					);
				$remove[$k] = $k;
			}
		}

		// Create Javascript notification if there is any
		if (is_array($messages)) {
			$final = join('\n', $messages);
			$return = "<script language=\"javascript\">\n".
				"alert('".addslashes($final)."');\n".
				"</script>\n";
		}

		// Remove at the end, in case multiples use the same ID
		foreach ($remove AS $k) {
			$this->removeFaxFromQueue($k);
		}

		return $return;
	} // end method faxNotify

	// Method: getName
	//
	//	Retrieves the user name. This is their login name.
	//
	// Returns:
	//
	//	User name for user.
	//
	public function getName ($no_parameters = "") {
		return ($this->user_name);
	} // end function getName

	// method: isPhysician
	//
	//	Determines if the user is classified as a physician/provider.
	//
	// Returns:
	//
	//	Boolean, true if they are a physician/provider.
	//
	public function isPhysician ($no_parameters = "") {
		return ($this->user_phy != 0);
	} // end function isPhysician

	// Method: setPassword
	//
	//	Set password for specified user id
	//
	// Parameters:
	//
	//	$password - New password
	//
	//	$user_id - Id of user record
	//
	public function setPassword ( $password, $user_id = 0 ) {
		$id = ($user_id == 0) ? $this->user_number : $user_id;

		$my_query = $GLOBALS['sql']->update_query(
			"user",
			array (
				"userpassword" => md5( $password )
			), array ( "id" => $id )
		);
		if((LOGLEVEL<1)||LOG_SQL){syslog(LOG_INFO,"setPassword query=$my_query");}	
		$result = $GLOBALS['sql']->query($my_query);
	} // end function setPassword

	// Method: getManageConfig
	//
	//	Retrieve a user configuration variable by key.
	//
	// Parameters:
	//
	//	$key - Configuration key to retrieve.
	//
	// Returns:
	//
	//	Value of the specified key.
	//
	public function getManageConfig ($key) {
		return $this->manage_config["$key"];
	} // end function getManageConfig

	// Method: getManageConfig
	//
	//	Set a user configuration variable by key to a particular
	//	value.
	//
	// Parameters:
	//
	//	$key - Configuration key to set.
	//
	//	$val - Configuration value to set.
	//
	public function setManageConfig ($new_key, $new_val) {
		// Now, set extra value(s)
		$this->manage_config["$new_key"] = $new_val;

		// Set part of record
		$query = $GLOBALS['sql']->update_query(
			'user',
			array(
				'usermanageopt' => serialize( $this->manage_config )
			), array ( 'id' => (int)$this->user_number )
		);
		$result = $GLOBALS['sql']->query( $query );
		return true;
	} // end function setManageConfig

	// Method: newMessages
	//
	//	Determines how many new unread messages exist in the system
	//	for this user.
	//
	// Returns:
	//
	//	Number of unread messages in the system for this user.
	//
	public function newMessages ( ) {
		$result = $GLOBALS['sql']->queryAll(
			"SELECT * FROM messages WHERE ".
			"msgfor='".addslashes($this->user_number)."' AND ".
			"msgread='0' AND msgtag=''");
		return count($result);
	} // end function newMessages

	// Method: CreateAdminUser
	//
	//	Creates user database table and populates it with
	//	required data. This is not "default" or "useful
	//	starting" data, it is the data that is required
	//	to run FreeMED.
	//
	// Parameters:
	//
	//	$adminuser - New administrative user
	//
	//	$adminpassword - New administrative password.
	//
	public function CreateAdminUser ( $adminuser, $adminpassword ) {
		// Sanity check ...
		$x = $GLOBALS['sql']->queryOne("SELECT COUNT(*) FROM user WHERE id=1");
		if ($x > 0) {
			syslog(LOG_INFO, "User.init| admin user already exists");
			return false;
		}

		syslog(LOG_INFO, "User.init| creating admin user");
		$query = $GLOBALS['sql']->insert_query(
			"user",
			array (
	    			"username" => "admin",
				"userpassword" => md5($adminpassword),
				"userdescrip" => __("Administrator"),
				"userlevel" => "admin",
				"usertype" => "misc",
				"userfac" => "-1",
				"userphy" => "-1",
				"userphygrp" => "-1",
				"userrealphy" => "0",
				"usermanageopt" => 'a:6:{s:1:" ";N;s:22:"automatic_refresh_time";s:0:"";s:15:"display_columns";s:1:"3";s:17:"num_summary_items";s:1:"1";s:17:"static_components";a:6:{s:12:"appointments";a:2:{s:6:"static";s:12:"appointments";s:5:"order";i:5;}s:14:"custom_reports";a:2:{s:6:"static";s:14:"custom_reports";s:5:"order";i:5;}s:19:"medical_information";a:2:{s:6:"static";s:19:"medical_information";s:5:"order";i:5;}s:9:" messages";a:2:{s:6:"static";s:9:" messages";s:5:"order";i:5;}s:19:"patient_information";a:2:{s:6:"static";s:19:"patient_information";s:5:"order";i:5;}s:21:"photo_id__action_last";a:2:{s:6:"static";s:21:"photo_id__action_last";s:5:"order";i:5;}}s:18:"modular_components";a:20:{s:12:"appointments";a:2:{s:6:"static";s:12:"appointments";s:5:"order";i:5;}s:14:"custom_reports";a:2:{s:6:"static";s:14:"custom_reports";s:5:"order";i:5;}s:19:"medical_information";a:2:{s:6:"static";s:19:"medical_information";s:5:"order";i:5;}s:9:" messages";a:2:{s:6:"static";s:9:" messages";s:5:"order";i:5;}s:19:"patient_information";a:2:{s:6:"static";s:19:"patient_information";s:5:"order";i:5;}s:21:"photo_id__action_last";a:2:{s:6:"static";s:21:"photo_id__action_last";s:5:"order";i:5;}s:15:"AllergiesModule";a:2:{s:6:"module";s:15:"AllergiesModule";s:5:"order";i:5;}s:21:"ChronicProblemsModule";a:2:{s:6:"module";s:21:"ChronicProblemsModule";s:5:"order";i:5;}s:21:"CurrentProblemsModule";a:2:{s:6:"module";s:21:"CurrentProblemsModule";s:5:"order";i:5;}s:13:"EpisodeOfCare";a:2:{s:6:"module";s:13:"EpisodeOfCare";s:5:"order";i:5;}s:20:"AuthorizationsModule";a:2:{s:6:"module";s:20:"AuthorizationsModule";s:5:"order";i:5;}s:13:"LettersModule";a:2:{s:6:"module";s:13:"LettersModule";s:5:"order";i:5;}s:15:"QuickmedsModule";a:2:{s:6:"module";s:15:"QuickmedsModule";s:5:"order";i:5;}s:22:"PatientCoveragesModule";a:2:{s:6:"module";s:22:"PatientCoveragesModule";s:5:"order";i:5;}s:13:"PatientImages";a:2:{s:6:"module";s:13:"PatientImages";s:5:"order";i:5;}s:13:"PaymentModule";a:2:{s:6:"module";s:13:"PaymentModule";s:5:"order";i:5;}s:18:"PrescriptionModule";a:2:{s:6:"module";s:18:"PrescriptionModule";s:5:"order";i:5;}s:24:"PreviousOperationsModule";a:2:{s:6:"module";s:24:"PreviousOperationsModule";s:5:"order";i:5;}s:15:"ProcedureModule";a:2:{s:6:"module";s:15:"ProcedureModule";s:5:"order";i:5;}s:13:"ProgressNotes";a:2:{s:6:"module";s:13:"ProgressNotes";s:5:"order";i:5;}}}'
	    		)
	    	);
		$result = $GLOBALS['sql']->query( $query );
		syslog(LOG_INFO, "User.init| result = ${result}");

		return $result;
	} // end method CreateAdminUser

} // end class User

?>
