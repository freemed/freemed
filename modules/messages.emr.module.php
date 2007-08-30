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
	var $MODULE_UID = "52b87bd6-08f2-4f8a-a7c9-159c56927ade";
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
		'msgtag',
		'user'
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
		$this->_SetHandler('Dashboard', get_class($this));
		$this->_SetHandler('MainMenu', 'UnreadMessages');

		// Call parent constructor
		parent::__construct();
	} // end constructor MessagesModule

	// Method: GetAllByTag
	//
	//	Grab hash of messages based on tags.
	//
	// Parameters:
	//
	//	$tag - (optional) Tag to search for, defaults to none.
	//
	//	$all - (optional) Get all messages, not just unread, defaults to false
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function GetAllByTag ( $tag = '', $all = false ) {
		$this_user = freemed::user_cache();
		$q = "SELECT m.id AS id, m.msgread AS read_status, CASE m.msgby WHEN 0 THEN 'System' ELSE u.userdescrip END AS from_user, m.msgtime AS stamp, DATE_FORMAT(m.msgtime, '%m/%d/%Y') AS stamp_mdy, CASE m.msgpatient WHEN 0 THEN m.msgperson ELSE CONCAT( pt.ptlname, ', ', pt.ptfname, ' (', pt.ptid, ')' ) END AS regarding, m.msgpatient AS patient_id, m.msgsubject AS subject, m.msgurgency AS urgency, m.msgtext AS content FROM messages m LEFT OUTER JOIN patient pt ON pt.id=m.msgpatient LEFT OUTER JOIN user u ON m.msgby=u.id WHERE m.msgtag='".addslashes( $tag )."' AND m.msgfor=".( $this_user->user_number + 0 )." ".( ( $tag=='' and !$all ) ? " AND m.msgread=0" : "" );
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAllByTag

	// Method: MessageTags
	//
	//	List of all message tags associated with a user.
	//
	// Returns:
	//
	//	Array of tags
	//
	public function MessageTags ( ) {
		$this_user = freemed::user_cache();
		$q = "SELECT DISTINCT msgtag AS tag FROM messages WHERE msgfor=".( $this_user->user_number + 0 )." AND LENGTH(msgtag) > 1 ORDER BY msgtag";
		$r = $GLOBALS['sql']->queryCol( $q );
		$return[] = array ( "INBOX", '' );
		foreach ( $r AS $v ) {
			$return[] = array ( $v, $v );
		}
		return $return;
	} // end method MessageTags

	// Method: UnreadMessages
	//
	//	Number of unread messages.
	//
	// Parameters:
	//
	//	$ts - (optional) Timestamp to use as marker.
	//
	//	$all - (optional) Show *all* messages, not just unread. Defaults to false.
	//
	// Returns:
	//
	//	Number of unread messages for the current user
	//
	public function UnreadMessages ( $ts = false, $all = false ) {
		$this_user = freemed::user_cache();
		$q = "SELECT COUNT(*) AS count FROM messages WHERE msgfor=".( $this_user->user_number + 0 )." ".( $all ? "" : " AND msgread=0 " )." AND msgtag='' ".( $ts ? " AND msgtime >= ".( $ts + 0 ) : "" );
		return $GLOBALS['sql']->queryOne( $q ) + 0;
	} // end method UnreadMessages

	// Method: DeleteMultiple
	//
	//	Remove multiple messages by id.
	//
	// Parameters:
	//
	//	$m - Array of message ids
	//
	public function DeleteMultiple ( $m ) {
		$hash = join( ',', $m );
		$q = "DELETE FROM ".$this->table_name." WHERE FIND_IN_SET( id, ".$GLOBALS['sql']->quote( $hash )." ) AND msgfor = ".$GLOBALS['sql']->quote( freemed::user_cache()->user_number );
		$res = $GLOBALS['sql']->query( $q );
		if ( PEAR::isError( $res ) ) { return false; }
		return true;
	} // end method DeleteMultiple

	function add_pre ( &$data ) {
		$this_user = freemed::user_cache();
		$data['msgby'] = $this_user->user_number;
		$data['user'] = $this_user->user_number;
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

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class MessagesModule

register_module('MessagesModule');

?>
