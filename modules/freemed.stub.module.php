<?php
	// $Id$
	// $Author$
	// note: Module for FreeMED installation. This primarily allows "core"
	//       tables, like "module", "config" and "user" to be updated with
	//       versioning.

LoadObjectDependency('FreeMED.MaintenanceModule');

class FreeMED_Package extends MaintenanceModule {

	var $MODULE_NAME = 'FreeMED';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.7.2';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = VERSION;

	function FreeMED_Package () {
		// Call parent constructor
		$this->BaseModule();
	} // end constructor FreeMED_Package

	// Use _update to perform upgrade-specific activities.
	function _update () {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.6.1
		//
		//	Database changes to core tables
		//
		if (!version_check($version, '0.6.1')) {
			// In version 0.6.1, we upgrade the configuration table
			// to have 128 character keys
			$sql->query('ALTER TABLE config CHANGE c_option c_option CHAR(64)');
		}

		// Version 0.6.3
		//
		// 	Insurance module (0.3.3)
		// 	(Actual update from old module name - HACK)
		//	Add inscodef{format,target}e for electronic mappings
		//
		if (!version_check($version, '0.6.3.2')) {
		//if ($GLOBALS['sql']->results($GLOBALS['sql']->query("SELECT * FROM module WHERE module_name='Insurance Company Maintenance'"))) {
			// Remove stale entry
			$GLOBALS['sql']->query(
				'DELETE FROM module WHERE '.
				'module_name=\'Insurance Company Maintenance\''
			);
			// Make changes
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscoidmap TEXT AFTER inscomod'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodefformate VARCHAR(50) AFTER inscodeftarget'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodeftargete VARCHAR(50) AFTER inscodefformate'
			);
		}

		// Version 0.7.1
		//
		//	User configuration change ... no tables, just formats
		//
		if (!version_check($version, '0.7.1')) {
			$all = $GLOBALS['sql']->query('SELECT * FROM user');
			while ($r = $GLOBALS['sql']->fetch_array($all)) {
				if (!empty($r['usermanageopt'])) {
					$a = explode('/', $r['usermanageopt']);
					unset ($c);
					foreach ($a AS $opt) {
						if (!empty($opt)) {
							list ($k, $v) = explode('=', $opt);
							if ( !(strpos($v, ':') === false) ) {
								$c["$k"] = explode(':', $v);
							} else {
								$c["$k"] = $v;
							}
							switch ($k) {
								case 'modular_components':
								foreach ($c["$k"] AS $comp) {
									$_c[$comp] = array (
										'module' => $comp,
										'order' => 5
									);
								}
								$c["$k"] = $_c;
								break;
									
								case 'static_components':
								foreach ($c["$k"] AS $comp) {
									$_c[$comp] = array (
										'static' => $comp,
										'order' => 5
									);
								}
								$c["$k"] = $_c;
								break;
									
								default: break;
							}
						}
					}

					// Map this to serialized data and replace
					$GLOBALS['sql']->query(
						$GLOBALS['sql']->update_query(
							'user',
							array(
								'usermanageopt' => serialize($c)
							),
							array('id'=>$r['id'])
						)
					);
				}
			}
		}

		// Version 0.7.2
		//
		//	ACL changes to user table, with automagic conversion
		//
		if (!version_check($version, '0.7.2')) {
			// Alter user table format
			$sql->query('ALTER TABLE user CHANGE COLUMN userlevel userlevel BLOB');

			// Loop through all users
			$q = $sql->query('SELECT * FROM user');
			while ($r = $sql->fetch_array($q)) {
				// Convert flags to "something,something" format
				unset ($a);
				// Guess which ACL groups
				if ($r['userlevel'] & USER_ROOT) { $a['admin'] = 'admin'; }
				if ($r['userlevel'] & USER_ADMIN) { $a['admin'] = 'admin'; }
				if ($r['userlevel'] & USER_DATABASE) { $a['entry'] = 'entry'; }
				if (($r['userrealphy'] > 0) and ($r['usertype'] == 'phy')) { $a['provider'] = 'provider'; }

				// Form and execute query
				if (!is_array($a)) { $a = array($a); }
				$new_query = $sql->update_query(
					'user',
					array ( 'userlevel' => join(',', $a) ),
					array ( 'id' => $r['id'] )
				);
				$sql->query($new_query);
			} // end while
		} // end 0.7.2
	} // end method _update
}

register_module('FreeMED_Package');

?>
