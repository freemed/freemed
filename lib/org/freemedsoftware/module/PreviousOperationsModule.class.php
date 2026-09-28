<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class PreviousOperationsModule extends EMRModule {

	var $MODULE_NAME = "Previous Operations";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "b7fd9cec-60b4-4a12-8ad2-88b178a14856";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Previous Operation";
	var $patient_field = "opatient";

	var $date_field = 'id';
	var $table_name = 'previous_operations';
	var $order_fields = 'odate,operation';
	var $widget_hash = '##odate## ##operation##';

	var $variables = array (
		'odate',
		'operation',
		'opatient',
		'user'
	);

	public function __construct () {
		parent::__construct( );
	} // end constructor PreviousOperationsModule

	protected function add_pre ( &$data ) {
		$data['odate'] = CreateObject('org.freemedsoftware.api.Scheduler')->ImportDate( $data['odate'] );
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['odate'] = CreateObject('org.freemedsoftware.api.Scheduler')->ImportDate( $data['odate'] );
		$data['user'] = freemed::user_cache()->user_number;
	}

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		// Category B: table/patient/order identifiers; Category A: patient id.
		// (Refused by log, R12.)
		$table = SqlIdent::name( $this->table_name );
		$pfield = SqlIdent::name( $this->patient_field );
		$order = SqlIdent::columns( $this->order_fields );
		if ( $table === false or $pfield === false or $order === false ) {
			syslog( LOG_ERR, get_class($this).'::recent_text| refusing invalid identifier '.var_export(array($this->table_name, $this->patient_field, $this->order_fields), true) );
			return '';
		}
		$query = sprintf('SELECT * FROM %s WHERE %s = %s ORDER BY %s', $table, $pfield, $GLOBALS['sql']->quote($patient), $order);
		$res = $GLOBALS['sql']->query($query);

		// Get operations, and extract to an array
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['odate'].' '.$r['operation']);
		}
		return @join(', ', $m);
	} // end method recent_text

} // end class PreviousOperationsModule

register_module ("PreviousOperationsModule");

?>
