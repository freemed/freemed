<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

class LogModule extends SupportModule {

	var $MODULE_NAME = "Log";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "fed9935d-3d3a-4569-92f6-34550eb86a4e";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Log";
	var $table_name = 'log';

	public function __construct () {
		// __("Log")
		$this->variables = array (
			'logstamp',
			'loguser',
			'logpatient',
			'logsystem',
			'logsubsystem',
			'logseverity',
			'logmsg'
		);

		// call parent constructor
		parent::__construct();
	} // end constructor Log

	protected function prepare ( $data ) {
		$d = $data;
		$d['logstamp'] = SQL__NOW;
		return $d;
	}

} // end class LogModule

register_module ("LogModule");

?>
