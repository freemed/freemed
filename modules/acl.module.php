<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ACL extends MaintenanceModule {
	// __("Access Control Lists")
	var $MODULE_NAME = 'Access Control Lists';
	var $MODULE_VERSION = '0.7.2';
	var $MODULE_DESCRIPTION = "Access Control Lists give granular access control to every part of the FreeMED system. This module is a wrapper for the phpgacl package.";

	var $MODULE_HIDDEN = true;
	var $MODULE_FILE = __FILE__;

	function ACL ( ) {
		$this->_SetMetaInformation('global_config_vars', array ( 'acl', 'acl_patient' ) );
		$this->_SetMetaInformation('global_config', array (
			__("Enable ACL System") =>
			'html_form::select_widget("acl", '.
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
		
		$this->MaintenanceModule();
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
	function acl_patient_add ( $pid ) {
		global $this_user;

		// Create ACL manipulation class (cached, of course)
		$acl = $this->_acl_object();

		// Only do this if patient ACLs are enabled
		//if (freemed::config_value('acl_patient') == 1) {
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
			if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }

			if (!$this_user->isPhysician()) {
				// TODO: Add ACL for current user
			}

			// Send back the appropriate ACL id (AXO)
			return $axo;
		//} // end if acl_patient
	} // end method acl_patient_add

	// Method: acl_user_add
	//
	//	Add an ACL ARO object in the "individual" section of the
	//	ARO objects for the specified user.
	//
	// Parameters:
	//
	//	$id - Record ID for the user in question
	//
	function acl_user_add ( $id ) {
		// Get the user record in question to play with
		$user = CreateObject('_FreeMED.User', $id);

		// Create ACL manipulation class
		$acl = $this->_acl_object();

		// Create an ARO object
		$acl_id = $acl->add_object(
			'individual', // ARO group name
			$user->local_record['userdescrip'], // Proper name
			'user_'.$id, // ACL identifier
			$id, // display order
			0, // hidden
			'ARO' // identify this as an ARO
		);
		//print "made new object with acl_id = ".$acl_id."<br/>\n";

		// Send back the new ID, in case we need it for anything
		return $acl_id;
	} // end method acl_user_add

	// Method: _acl_object
	//
	//	Simple way to get complex ACL API object
	//
	// Returns:
	//
	//	ACL API object
	//
	function _acl_object ( ) {
		static $_obj;
		if (!isset($_obj)) {
		$_obj = CreateObject('_ACL.gacl_api',
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
	} // end method _acl_object

	// Method: _setup
	//
	//	Overrides the default _setup method to wrap phpgacl's
	//	bizarre XML-based schema setup.
	//
	function _setup ( ) {
		// Load gacl_api instead of gacl, since we have to emulate
		// the admin module that they have. We don't load the
		// dependency, as it was loaded in lib/acl.php for the
		// global $acl object.
		$acl = $this->_acl_object();

		// Until we figure out what is going on, include this
		// verbatim
		include_once (ADODB_DIR.'/adodb-xmlschema.inc.php');

		// Create schema object and build query array
		$schema = new adoSchema ( &$acl->db, true );
		$orig_xml_file = 'lib/acl/schema.xml';
	
		// Translate XML to contain proper prefix
		$xml = $this->_file_get_contents($orig_xml_file);
		if (!$xml) {
			die('ACL: failed to read '.$orig_xml_file);
		}
		$xml = preg_replace('/#PREFIX#/i', 'acl_', $xml);

		// Build the actual SQL array
		$sql = $schema->ParseSchemaString($xml);

		// Execute the SQL schema that has been built
		$result = $schema->ExecuteSchema($sql, true);
		if ($result != 2) {
			print "ACL: table creation error<br/>\n";
		}

		// Cleanup
		$schema->Destroy();

		// Call _set_defaults
		$this->_set_defaults();
	} // end method _setup

	// Method: _set_defaults
	//
	//	Method used to set the default ACL values for a new
	//	FreeMED installation, since the ACL system is very
	//	complex and cannot use the default table information
	//	and methods.
	//
	function _set_defaults ( ) {
		$acl = $this->_acl_object();
		include_once (ADODB_DIR.'/adodb-xmlschema.inc.php');
		$schema = new adoSchema ( &$acl->db, true );
		$sql = $schema->ParseSchemaFile('lib/acl/freemed-acl-defaults.xml');
		$result = $schema->ExecuteSchema($sql, true);
		//print "result = "; print_r($result); print "<br/>\n";
		if ($result != 2) {
			//print "ACL: data import error<br/>\n";
		}
		$schema->Destroy();
	} // end method _set_defaults

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

	// ----- Internal helper functions
	function _file_get_contents ( $filename ) {
		if (function_exists('file_get_contents')) {
			return file_get_contents($filename);
		} else {
			$fp = fopen($filename, 'r');
			if ($fp) {
				while (!feof($fp)) {
					$buffer .= fgets($fp, 4096);
				}
				fclose ($fp);
				return $buffer;
			} else {
				return false;
			}
		}
	} // end method _file_get_contents

	function _file_put_contents ( $filename, $content ) {
		$fp = fopen($filename, 'w');
		if (!$fp) {
			die('ACL: unable to open '.$filename.' for writing');
		}
		fwrite($fp, $content);
		fclose($fp);
		return true;
	} // end method _file_put_contents

	function _update ( ) {
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.8.0')) {
			$this->_drop_old_tables();
			$this->_setup();

			// Go through all existing user records and make sure
			// that there are existing ACL ARO objects
			$result = $GLOBALS['sql']->query('SELECT * FROM user');
			if ($GLOBALS['sql']->num_rows($result) > 0) {
				// Only loop if there are users
				while ($r = $GLOBALS['sql']->fetch_array($result)) {
					//print "importing user ".$r['id']."<br/>\n";
					// For each user, add ACL record
					$u[] = $this->acl_user_add($r['id']);
				}
			}

			// Go through all existing patient records and make
			// sure that there are existing ACL AXO objects
			$result = $GLOBALS['sql']->query('SELECT * FROM patient');
			if ($GLOBALS['sql']->num_rows($result) > 0) {
				// Only loop if there are users
				while ($r = $GLOBALS['sql']->fetch_array($result)) {
					//print "importing patient ".$r['id']."<br/>\n";
					// For each user, add ACL record
					$p[] = $this->acl_patient_add($r['id']);
				}
			}
		}
	} // end method _update

} // end class ACL

register_module('ACL');

?>
