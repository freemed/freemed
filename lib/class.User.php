<?php
	// $Id$
	// $Author$

// TODO migrate all password setting to use setPassword
//	write checkPassword
//	migrate all password checking to use checkPassword 
// 	verify that there are no md5 calls outside of this class!!


// class: FreeMED.User
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
	function User ($param=NULL) {
		if ($param == NULL) {
			// Check to see if XML-RPC or session data
			if ($_SESSION['authdata']['user']) {
				$this->user_number = $_SESSION['authdata']['user']; 
			} else {
				$this->user_number = $GLOBALS['__freemed']['basic_auth_id'];
			}
		} else {
			$this->user_number = $param;
		}

		// Check for cached copy
		if (!isset($GLOBALS['__freemed']['cache']['user'][$this->user_number])) {
			// Retrieve copy
			$this->local_record = freemed::get_link_rec (
				$this->user_number, "user"
			);

			// Store in the cache
			$GLOBALS['__freemed']['cache']['user'][$this->user_number] = $this->local_record;
		} else {
			// Pull copy from the cache
			$this->local_record = $GLOBALS['__freemed']['cache']['user'][$this->user_number];
		}

		$this->user_name    = stripslashes($this->local_record["username"]);
		$this->user_descrip = stripslashes($this->local_record["userdescrip"]);
		$this->user_level   = $this->local_record["userlevel"  ];
		$this->user_phy     = $this->local_record["userrealphy"];
		$this->perms_fac    = $this->local_record["userfac"    ]; 
		$this->perms_phy    = $this->local_record["userphy"    ];
		$this->perms_phygrp = $this->local_record["userphygrp" ];

		// special root stuff
		if ($this->user_number == 1) $this->user_level = 9;

		// Map configuration vars
		$this->manage_config = unserialize($this->local_record['usermanageopt']);
	} // end function User

	// method: getDescription
	//
	//	Retrieve description of current user. (Usually their name)
	//
	// Returns:
	//
	//	Description of the current user object.
	//
	function getDescription ($no_parameters = "") {
		if (empty($this->user_descrip)) return __("(no description)");
		return ($this->user_descrip);
	} // end function getDescription

	function getLevel ($no_parameters = "") {
		return ($this->user_level)+0;
	} // end function getLevel

	function getPhysician ($no_parameters = "") {
		return ($this->user_phy)+0;
	} // end function getPhysician

	// Method: getFaxesInQueue
	//
	//	Get list of faxes in queue to check
	//
	// Returns:
	//
	//	Array of fax ids, or NULL if there are none
	function getFaxesInQueue ( ) {
		if (is_array($_SESSION['fax_queue'])) {
			foreach ($_SESSION['fax_queue'] AS $k => $v) {
				if ($k and $v['id']) { $r[$k] = $v['id']; }
			}
			if (is_array($r)) { return $r; }
		}
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
	function getFaxDescription ( $fid ) {
		return $_SESSION['fax_queue'][$fid]['info'];
	} // end method getFaxDescription

	// Method: setFaxInQueue
	//
	//	Set a fax to be in the queue to check.
	//
	// Parameters:
	//
	//	$fid - Fax id
	//
	//	$info - (optional) Textual description of fax to be stored in queue.
	//
	function setFaxInQueue ( $fid, $info = NULL ) {
		$_SESSION['fax_queue'][$fid]['id'] = $fid;
		$_SESSION['fax_queue'][$fid]['info'] = $info;
	} // end method setFaxInQueue

	// Method: faxNotify
	//
	//	Create Javascript alerts for finished faxes.
	//
	// Returns:
	//
	//	Javascript code (in SCRIPT tags) or NULL if nothing.
	//
	function faxNotify ( ) {
		if (!($fax = $this->getFaxesInQueue())) { return ''; }
		$f = CreateObject('_FreeMED.Fax');
		foreach ($fax AS $k => $v) {
			$st = $f->State($k);
			if ($st == 1) {
				$messages[] = sprintf(
					__("Fax job %d to %s (%s) finished."),
					$k, $f->GetNumberFromId($k),
					$this->getFaxDescription($k)
					);
				unset($_SESSION['fax_queue'][$k]);
			} elseif (is_array($st) and $st[0] == -1) {
				$messages[] = sprintf(
					__("Fax job %d (%s) failed with '%s'."),
					$k, $f->GetNumberFromId($k), $st[1]
					);
				unset($_SESSION['fax_queue'][$k]);
			}
		}

		// Create Javascript notification if there is any
		if (is_array($messages)) {
			$final = join('\n', $messages);
			return "<script language=\"javascript\">\n".
				"alert('".addslashes($final)."');\n".
				"</script>\n";
		}
	} // end method faxNotify

	// Method: getName
	//
	//	Retrieves the user name. This is their login name.
	//
	// Returns:
	//
	//	User name for user.
	//
	function getName ($no_parameters = "") {
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
	function isPhysician ($no_parameters = "") {
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
	function setPassword ($password, $user_id) {
		global $sql;

		if ($user_id == "") {
			if((LOGLEVEL<1)||LOG_ERRORS){syslog(LOG_INFO,"class.User.php|setPassword| no user id!!");}
			return false;
		}

		$md5_password=md5($password);
		
		$my_query = $sql->update_query(
			"user",
			array (
				"userpassword" => $md5_password
			), array ("id" => $user_id)
		);
		if((LOGLEVEL<1)||LOG_SQL){syslog(LOG_INFO,"setPassword query=$my_query");}	

		$result = $sql->query($my_query);
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
	function getManageConfig ($key) {
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
	function setManageConfig ($new_key, $new_val) {
		// Now, set extra value(s)
		$this->manage_config["$new_key"] = $new_val;

		// Set part of record
		$query = $GLOBALS['sql']->update_query(
			'user',
			array(
				'usermanageopt' => serialize($this->manage_config)
			), array ('id' => $this->user_number)
		);
		$result = $GLOBALS['sql']->query($query);
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
	function newMessages () {
		global $sql;
		$result = $sql->query("SELECT * FROM messages WHERE ".
			"msgfor='".addslashes($this->user_number)."' AND ".
			"msgread='0' AND msgtag=''");
		if (!$sql->results($result)) return false;
		return $sql->num_rows($result);
	} // end function newMessages

	// Method: init
	//
	//	Creates user database table and populates it with
	//	required data. This is not "default" or "useful
	//	starting" data, it is the data that is required
	//	to run FreeMED.
	//
	// Parameters:
	//
	//	$adminpassword - New administrative password.
	//
	function init($adminpassword) {
		global $sql;

		// Database Clean
		$result=$sql->query("DROP TABLE user"); 

		// Database Rebuild
		$result = $sql->query($sql->create_table_query(
			'user',
			array(
				'username' => SQL__NOT_NULL(SQL__VARCHAR(16)),
				'userpassword' => SQL__NOT_NULL(SQL__VARCHAR(32)),
				'userdescrip' => SQL__VARCHAR(50),
				'userlevel' => SQL__BLOB,
				'usertype' => SQL__ENUM (array(
					"phy",
					"misc",
					"super"
				)),
				'userfac' => SQL__BLOB,
				'userphy' => SQL__BLOB,
				'userphygrp' => SQL__BLOB,
				'userrealphy' => SQL__INT_UNSIGNED(0),
				'usermanageopt' => SQL__BLOB,
				'id' => SQL__SERIAL
			), array ('id', 'username')
		));

		// Required Data!!

		$result = $sql->query($sql->insert_query(
			"user",
			array (
	    			"username" => "admin",
				"userpassword" => $adminpassword,
				"userdescrip" => __("Administrator"),
				"userlevel" => "admin",
				"usertype" => "misc",
				"userfac" => "-1",
				"userphy" => "-1",
				"userphygrp" => "-1",
				"userrealphy" => "0",
				"usermanageopt" => 'a:6:{s:1:" ";N;s:22:"automatic_refresh_time";s:0:"";s:15:"display_columns";s:1:"3";s:17:"num_summary_items";s:1:"1";s:17:"static_components";a:6:{s:12:"appointments";a:2:{s:6:"static";s:12:"appointments";s:5:"order";i:5;}s:14:"custom_reports";a:2:{s:6:"static";s:14:"custom_reports";s:5:"order";i:5;}s:19:"medical_information";a:2:{s:6:"static";s:19:"medical_information";s:5:"order";i:5;}s:9:" messages";a:2:{s:6:"static";s:9:" messages";s:5:"order";i:5;}s:19:"patient_information";a:2:{s:6:"static";s:19:"patient_information";s:5:"order";i:5;}s:21:"photo_id__action_last";a:2:{s:6:"static";s:21:"photo_id__action_last";s:5:"order";i:5;}}s:18:"modular_components";a:20:{s:12:"appointments";a:2:{s:6:"static";s:12:"appointments";s:5:"order";i:5;}s:14:"custom_reports";a:2:{s:6:"static";s:14:"custom_reports";s:5:"order";i:5;}s:19:"medical_information";a:2:{s:6:"static";s:19:"medical_information";s:5:"order";i:5;}s:9:" messages";a:2:{s:6:"static";s:9:" messages";s:5:"order";i:5;}s:19:"patient_information";a:2:{s:6:"static";s:19:"patient_information";s:5:"order";i:5;}s:21:"photo_id__action_last";a:2:{s:6:"static";s:21:"photo_id__action_last";s:5:"order";i:5;}s:15:"AllergiesModule";a:2:{s:6:"module";s:15:"AllergiesModule";s:5:"order";i:5;}s:21:"ChronicProblemsModule";a:2:{s:6:"module";s:21:"ChronicProblemsModule";s:5:"order";i:5;}s:21:"CurrentProblemsModule";a:2:{s:6:"module";s:21:"CurrentProblemsModule";s:5:"order";i:5;}s:13:"EpisodeOfCare";a:2:{s:6:"module";s:13:"EpisodeOfCare";s:5:"order";i:5;}s:20:"AuthorizationsModule";a:2:{s:6:"module";s:20:"AuthorizationsModule";s:5:"order";i:5;}s:13:"LettersModule";a:2:{s:6:"module";s:13:"LettersModule";s:5:"order";i:5;}s:15:"QuickmedsModule";a:2:{s:6:"module";s:15:"QuickmedsModule";s:5:"order";i:5;}s:22:"PatientCoveragesModule";a:2:{s:6:"module";s:22:"PatientCoveragesModule";s:5:"order";i:5;}s:13:"PatientImages";a:2:{s:6:"module";s:13:"PatientImages";s:5:"order";i:5;}s:13:"PaymentModule";a:2:{s:6:"module";s:13:"PaymentModule";s:5:"order";i:5;}s:18:"PrescriptionModule";a:2:{s:6:"module";s:18:"PrescriptionModule";s:5:"order";i:5;}s:24:"PreviousOperationsModule";a:2:{s:6:"module";s:24:"PreviousOperationsModule";s:5:"order";i:5;}s:15:"ProcedureModule";a:2:{s:6:"module";s:15:"ProcedureModule";s:5:"order";i:5;}s:13:"ProgressNotes";a:2:{s:6:"module";s:13:"ProgressNotes";s:5:"order";i:5;}}}'
	    		)
	    	));

		return $result;
	} // end method init

} // end class User

?>
