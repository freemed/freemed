<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ACL extends MaintenanceModule {
	// __("Access Control Lists")
	var $MODULE_NAME = 'Access Control Lists';
	var $MODULE_VERSION = '0.7.0';
	var $MODULE_DESCRIPTION = "Access Control Lists give granular access control to every part of the FreeMED system. This module is a wrapper for the phpgacl package.";

	var $MODULE_HIDDEN = true;
	var $MODULE_FILE = __FILE__;

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
		$acl = CreateObject('_ACL.gacl_api',
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
				'caching' => true,
				'force_cache_expire' => true,
				'cache_expire_time' => 600
			)
		);

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
		$tmp_xml_file = tempnam('/tmp', 'acl_');
		$this->_file_put_contents($tmp_xml_file, $xml);

		// Build the actual SQL array
		$sql = $schema->ParseSchema($tmp_xml_file);
		unlink ($tmp_xml_file);

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
		$acl = CreateObject('_ACL.gacl_api',
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
				'caching' => true,
				'force_cache_expire' => true,
				'cache_expire_time' => 600
			)
		);

		include_once (ADODB_DIR.'/adodb-xmlschema.inc.php');
		$schema = new adoSchema ( &$acl->db, true );
		$sql = $schema->ParseSchema('lib/acl/freemed-acl-defaults.xml');
		$result = $schema->ExecuteSchema($sql, true);
		if ($result != 2) {
			print "ACL: data import error<br/>\n";
		}
		$schema->Destroy();
	} // end method _set_defaults

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

} // end class ACL

register_module('ACL');

?>
