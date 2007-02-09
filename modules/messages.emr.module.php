<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class MessagesModule extends EMRModule {

	var $MODULE_NAME = "Message";
	var $MODULE_VERSION = "0.8.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "03633733-9ec0-4535-b233-83a1686318ff";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name    = "messages";
	var $patient_field = "msgpatient";
	var $order_by      = "msgunique DESC";
	var $widget_hash   = "##msgsubject## [##msgtime##]";

	var $variables = array (
		'msgby',
		'msgtime',
		'msgfor',
		'msgrecip',
		'msgpatient',
		'msgperson',
		'msgurgency',
		'msgsubject',
		'msgtext',
		'msgread',
		'msgunique',
		'msgtag'
	);

	public function __construct () {
		// Set configuration stuff
	/*
		$this->_SetMetaInformation('global_config_vars', array(
			'message_delete'
		));
		$this->_SetMetaInformation('global_config', array(
			__("Allow Direct Message Deletion") =>
			'html_form::select_widget("message_delete", '.
				'array( '.
					'"'.__("Disable").'" => "0", '.
					'"'.__("Enable").'" => "1" '.
				')'.
			')'
		));
	*/

		// Add main menu handler item
		$this->_SetHandler('MainMenu', 'UnreadMessages');

		// Call parent constructor
		parent::__construct();
	} // end constructor MessagesModule

	public function UnreadMessages ( ) {
		// Ask the API how many messages we have
		$m = CreateObject('org.freemedsoftware.api.Messages');
		if (!$m->view_per_user(true)) { return false; }
		return count($m->view_per_user(true));
	} // end method UnreadMessages

	function add_pre ( &$data ) {
		$this_user = freemed::user_cache();
		$data['msgby'] = $this_user->user_number;
	}

	protected function additional_move ( $id, $from, $to ) {
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array ( 'msgpatient' => $to ),
			array ( 'msgunique' => $r['msgunique'] )
		);
		if ($r['msgunique'] > 0) {
			syslog(LOG_INFO, "Messages| moved messages with msgunique of ".$r['msgunique']." to $to");
			$result = $GLOBALS['sql']->query($q);
			if (!$result) { return false; }			
		}
		return true;
	} // end method additional_move

} // end class MessagesModule

register_module('MessagesModule');

?>
