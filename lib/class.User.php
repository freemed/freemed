<?php
 // $Id$
 // $Author$

// TODO migrate all password setting to use setPassword
//	write checkPassword
//	migrate all password checking to use checkPassword 
// 	verify that there are no md5 calls outside of this class!!


// class User
class User {
	var $local_record;                 // local record
	var $user_number;                  // user number (id)
	var $user_level;                   // user level (0..9)
	var $user_name;                    // name of the user
	var $user_descrip;                 // user description
	var $user_phy;                     // number of physician 
	var $manage_config; // configuration for patient management
	var $perms_fac, $perms_phy, $perms_phygrp;

	function User ($param=NULL) {
		if ($param == NULL) {
			$this->user_number = $_SESSION['authdata']['user'];
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

		$this->user_name    = $this->local_record["username"   ];
		$this->user_descrip = $this->local_record["userdescrip"];
		$this->user_level   = $this->local_record["userlevel"  ];
		$this->user_phy     = $this->local_record["userrealphy"];
		$this->perms_fac    = $this->local_record["userfac"    ]; 
		$this->perms_phy    = $this->local_record["userphy"    ];
		$this->perms_phygrp = $this->local_record["userphygrp" ];

		// special root stuff
		if ($this->user_number == 1) $this->user_level = 9;

		// Map configuration vars
		$this->mapConfiguration();
	} // end function User

	function getDescription ($no_parameters = "") {
		if (empty($this->user_descrip)) return "(no description)";
		return ($this->user_descrip);
	} // end function getDescription

	function getLevel ($no_parameters = "") {
		return ($this->user_level)+0;
	} // end function getLevel

	function getPhysician ($no_parameters = "") {
		return ($this->user_phy)+0;
	} // end function getPhysician

	function getName ($no_parameters = "") {
		return ($this->user_name);
	} // end function getName

	function isPhysician ($no_parameters = "") {
		return ($this->user_phy != 0);
	} // end function isPhysician

	function setPassword($password,$user_id)
	{
	global $sql;

	if($user_id=="")
	{
	if((LOGLEVEL<1)||LOG_ERRORS){syslog(LOG_INFO,"class.User.php|setPassword| no user id!!");}
		return false;
	}


	$md5_password=md5($password);
		
	$my_query=$sql->update_query(
		"user",
		array ("userpassword" => $md5_password),
		array ("id" => $user_id)
		);
	if((LOGLEVEL<1)||LOG_SQL){syslog(LOG_INFO,"setPassword query=$my_query");}	


	$result = $sql->query($my_query);


	}


	function mapConfiguration () {
		// Start with usermanageopt
		$usermanageopt = $this->local_record["usermanageopt"];

		// Check if set...
		if (empty($usermanageopt)) return false;

		// Split out by "/"'s
		$usermanageopt_array = explode("/", $usermanageopt);

		// Pull pairs one by one
		foreach ($usermanageopt_array AS $garbage => $opt) {
			// Check if not empty..
			if (!empty($opt)) {
				// Explode pairs by "="
				list ($key, $val) = explode ("=", $opt);

				// Map to global manage_config map
				if ( !(strpos($val, ":") === false) ) {
					// Handle arrays
					$this->manage_config["$key"] =
						explode(":", $val);
				} else {
					// Handle scalar
					$this->manage_config["$key"] = $val;
				} // end mapping
			} // end checking for empties
		} // end looping through
	} // end function User->mapConfiguration

	function getManageConfig ($key) {
		return $this->manage_config["$key"];
	} // end function getManageConfig

	// Messages
	function newMessages () {
		global $sql;
		$result = $sql->query("SELECT * FROM messages WHERE ".
			"msgfor='".addslashes($this->user_number)."' AND ".
			"msgread='0'");
		if (!$sql->results($result)) return false;
		return $sql->num_rows($result);
	} // end function newMessages

	// Fred Trotter
	// creates database and populates it with "Required Data"
	// This is not "default" or "usefull starting" data
	// it is the data that is required to run FreeMED!!
	function init($adminpassword) {
		global $sql;

		// Database Clean
		$result=$sql->query("DROP TABLE user"); 

		// Database Rebuild
		$result = $sql->query($sql->create_table_query(
			'user',
			array(
				'username' => SQL_NOT_NULL(SQL_CHAR(16)),
				'userpassword' => SQL_NOT_NULL(SQL_CHAR(32)),
				'userdescrip' => SQL_VARCHAR(50),
				'userlevel' => SQL_INT_UNSIGNED(0),
				'usertype' => SQL_ENUM (array(
					"phy",
					"misc",
					"super"
				)),
				'userfac' => SQL_BLOB,
				'userphy' => SQL_BLOB,
				'userphygrp' => SQL_BLOB,
				'userrealphy' => SQL_INT_UNSIGNED(0),
				'usermanageopt' => SQL_BLOB,
				'id' => SQL_SERIAL
			), array ('id', 'username')
		));

		// Required Data!!

		$result = $sql->query($sql->insert_query(
			"user",
			array (
	    			"username" => "admin",
				"userpassword" => $adminpassword,
				"userdescrip" => __("Administrator"),
				"userlevel" => USER_ROOT,
				"usertype" => "misc",
				"userfac" => "-1",
				"userphy" => "-1",
				"userphygrp" => "-1",
				"userrealphy" => "0",
				"usermanageopt" => "'/automatic_refresh_time=/display_columns=3/num_summary_items=1/static_components=appointments:custom_reports:medical_information: messages:patient_information:photo_id__action_last=Static Components:appointments:custom_reports:medical_information:messages:patient_information:photo_id::/modular_components=AllergiesModule:ChronicProblemsModule:CurrentProblemsModule:EpisodeOfCare:AuthorizationsModule:LettersModule:QuickmedsModule:PatientCoveragesModule:PatientImages:PaymentModule:PrescriptionModule:PreviousOperationsModule:ProcedureModule:ProgressNotes'"
	    		)
	    	));

		return $result;
	} // end method User->init

} // end class User

?>
