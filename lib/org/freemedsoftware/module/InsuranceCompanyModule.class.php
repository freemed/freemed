<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //     Horea Teodoru <teodoruh@gmail.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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

class InsuranceCompanyModule extends SupportModule {

	var $MODULE_NAME = "Insurance Companies";
	var $MODULE_VERSION = "0.5";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "01692334-2893-452e-8e55-08ad65c4d17d";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Insurance Company";
	var $table_name = "insco";
	var $archive_field = "inscoarchive";
	var $variables		= array (
		  "inscodtmod"
		, "insconame"
		, "inscoalias"
		, "inscoaddr1"
		, "inscoaddr2"
		, "inscocity"
		, "inscostate"
		, "inscozip"
		, "inscophone"
		, "inscofax"
		, "inscocontact"
		, "inscoid"
		, "inscowebsite"
		, "inscoemail"
		, "inscogroup"
		, "inscotype"
		, "inscoassign"
		, "inscomod"
		, "inscoidmap"
		, "inscox12id"
		// Billing related information
		, "inscodefoutput"
		, "inscodefformat"
		, "inscodeftarget"
		, "inscodeftargetopt"
		, "inscodefformate"
		, "inscodeftargete"
		, "inscodeftargetopte"
		, "inscoarchive"
	);

	var $widget_hash = '##insconame## (##inscocity##, ##inscostate##)';
	var $order_field = 'insconame, inscostate, inscocity';

	public function __construct ( ) {
//		$this->table_join = array (
//			'inscogroup' => 'inscogroup'
//		);
		$this->list_view = array (
			__("Name")	=>	"insconame",
			__("City")	=>	"inscocity",
			__("State")	=>	"inscostate",
			__("Group")	=>	"inscogroup"
		);
	
		// Run parent constructor
		parent::__construct();
	} // end constructor InsuranceCompanyModule

	protected function add_pre ( &$data ) {
		$inscodtadd = date('Y-m-d');
		$inscodtmod = date('Y-m-d');
		$data['inscoidmap'] = serialize($data['inscoidmap']);
		if ( $data['inscocsz'] ) {
			list( $data['inscocity'], $data['inscostate'], $data['inscozip'] ) = $this->SplitCSZ( $data['inscocsz'] );
		}
	}

	protected function mod_pre ( &$data ) {
		unset ( $data['inscodtadd'] ); // no add date
		$inscodtmod = date('Y-m-d');
		$data['inscoidmap'] = serialize($data['inscoidmap']);
		if ( $data['inscocsz'] ) {
			list( $data['inscocity'], $data['inscostate'], $data['inscozip'] ) = $this->SplitCSZ( $data['inscocsz'] );
		}
	}
	
	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Category B: the table identifier is validated once and reused
		// (refused by log, R12 — the maintenance handler then does nothing).
		$table = SqlIdent::name( $this->table_name );
		if ( $table === false ) {
			syslog( LOG_ERR, get_class($this).'::_update| refusing invalid table_name '.var_export($this->table_name, true) );
			return false;
		}

		// Version 0.3
		//
		//	Move phyidmap to be mapped in insco table (inscoidmap)
		//
		if (!version_check ( $version, '0.3' )) {
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscoidmap TEXT AFTER inscomod', $table)
			);
		}

		// Version 0.3.1
		//
		//	Add inscodefformat and inscodeftarget mappings
		//
		if (!version_check ( $version, '0.3.4.1' )) {
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap', $table)
			);
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat', $table)
			);
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodefformate VARCHAR(50) AFTER inscodeftarget', $table)
			);
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodeftargete VARCHAR(50) AFTER inscodefformatE', $table)
			);
		}

		// Version 0.3.3 (Actual update from old module name - HACK)
		//
		//	Add inscodef{format,target}e for electronic mappings
		//
		if ($GLOBALS['sql']->results($GLOBALS['sql']->query("SELECT * FROM module WHERE module_name='Insurance Company Maintenance'"))) {
			// Remove stale entry
			$GLOBALS['sql']->query(
				'DELETE FROM module WHERE '.
				'module_name=\'Insurance Company Maintenance\''
			);
			// Make changes
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap', $table)
			);
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat', $table)
			);
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodefformate VARCHAR(50) AFTER inscodeftarget', $table)
			);
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscodeftargete VARCHAR(50) AFTER inscodefformate', $table)
			);
		}

		// Version 0.4
		//
		//	Add inscox12id for 837p/remitt
		//
		if (!version_check ( $version, '0.4' )) {
			$GLOBALS['sql']->query(
				sprintf('ALTER TABLE %s ADD COLUMN inscox12id VARCHAR(32) AFTER inscoidmap', $table)
			);
			$GLOBALS['sql']->query( sprintf('UPDATE %s SET inscox12id=%s WHERE id>0', $table, $GLOBALS['sql']->quote('')) );
		}

		// Version 0.4.1
		//
		//	Add inscodefoutput for remitt
		//
		if (!version_check ( $version, '0.4.1' )) {
			$GLOBALS['sql']->query(
				sprintf("ALTER TABLE %s ADD COLUMN inscodefoutput ENUM('electronic', 'paper') AFTER inscox12id", $table)
			);
			$GLOBALS['sql']->query( sprintf('UPDATE %s SET inscodefoutput=%s WHERE id>0', $table, $GLOBALS['sql']->quote('electronic')) );
		}

	} // end method _update

} // end class InsuranceCompanyModule

register_module ("InsuranceCompanyModule");

?>
