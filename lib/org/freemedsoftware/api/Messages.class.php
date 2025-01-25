<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.Messages
class Messages {

	public function __construct ( ) { }

	// Method: get
	//
	//	Retrieve message by message id key
	//
	// Parameters:
	//
	//	$message - Message id key
	//
	// Returns:
	//
	//	Associative array containing message information.
	//
	function get ($message) {
		global $this_user;
		if (!is_object($this_user)) $this_user = CreateObject('org.freemedsoftware.core.User');

		// Perform search
		$query = "SELECT * FROM messages WHERE id='".addslashes($message)."'";
		$r = $GLOBALS['sql']->queryRow( $query );

		if ($r['id']) {
			// Check for appropriate access (correct user)
			if ($r['msgfor'] != $this_user->user_number) {
				return array ( );
			}
			
			return array (
				'physician'  => $r['msgfor'],
				'patient'    => $r['msgpatient'],
				'person'     => $r['msgperson'],
				'subject'    => $r['msgsubject'],
				'text'       => $r['msgtext'],
				'urgency'    => $r['msgurgency'],
				'read'       => $r['msgread'],
				'time'       => $r['msgtime'],
				'id'         => $r['id']
			);
		} else {
			return array ( );
		}
	} // end method get

	// Method: recipients_to_text
	//
	//	Convert a recipients field (comma delimited users) into
	//	a list of user names.
	//
	// Parameters:
	//
	//	$recip - 'msgrecip' field
	//
	// Returns:
	//
	//	Comma-delimited list of user names
	//
	function recipients_to_text ( $recip ) {
		$query = "SELECT * FROM user WHERE ".
			"FIND_IN_SET(id, '".addslashes($recip)."')";
		$res = $GLOBALS['sql']->queryAll($query);
		$a = array ();
		foreach ($res AS $r) {
			$a[] = prepare($r['userdescrip']);
		}
		return join(', ', $a);
	} // end method recipients_to_text

	// Method: remove
	//
	//	Remove a message from the system by its id key
	//
	// Parameters:
	//
	//	$message_id - Message id key
	//
	// Returns:
	//
	//	Boolean, successful
	//
	function remove ($message_id) {
		global $this_user;
		if (!is_object($this_user)) $this_user = CreateObject('org.freemedsoftware.core.User');
		
		// Perform actual deletion
		$result = $GLOBALS['sql']->query(
			"DELETE FROM messages WHERE ".
			"id='".addslashes($message_id)."' AND ".
			"msgfor='".addslashes($this_user->user_number)."'"
		);
		if ( $result instanceof PEAR_Error ) { return false; }
		return true;
	} // end method remove

	// Method: ListOfUsers
	//
	//	Create list of users in the system.
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* id - User id
	//	* username - Name of the user in question
	//
	function ListOfUsers ( ) {
		$query = "SELECT username, id FROM user WHERE id > 1 ORDER BY username";
		$res = $GLOBALS['sql']->queryAll( $query );
		return $res;
	} // end method

	// Method: send
	//
	//	Send a message using the FreeMED messaging system
	//
	// Parameters:
	//
	//	$message - Associative array containing information describing the message.
	//	* system - Boolean, if system message
	//	* user - Destination user id
	//	* group - User group id, optional
	//	* for - Array of destination users (instad of 'user')
	//	* text - Message body
	//	* patient - Patient id
	//	* person - Person name whom the message is regarding
	//	* subject - Textual subject line
	//	* urgency - Urgency ( 1 to 5 )
	//	* tag - Tag under which to file message. Defaults to ''.
	//
	// Returns:
	//
	//	Boolean, successful
	//
	function send ($m) {
		$message = (array) $m;
		$this_user = freemed::user_cache();
		
		// Check for error conditions
		if (($message['patient'] < 1) and empty($message['for']) and empty($message['group'])) { 
			syslog( LOG_INFO, "Messages| did not find patient or person" );
			return false;
		}

		// Determine message recipients
		if($message[ 'for' ]){
			if ( is_array( $message[ 'for' ] ) ) {
				$msgFor = $message['for'];
			} else if ( strpos( $message['for'], ',' ) !== false ) {
				$msgFor = explode( ',', $message['for'] );
			} else {
				$msgFor = array( $message['for'] );
			}
		}

		// Handle message group if one is specified
		if ( $message['group'] > 0 ) {
			$g = $GLOBALS['sql']->get_link( 'usergroup', $message['group'] );
			if($msgFor)
				$msgFor = array_merge( $msgFor, explode( ',', $g['usergroup'] ) );
			else
				$msgFor = explode( ',', $g['usergroup'] );
		}
		
		// Unique timestamp
		$unique = time();

		// Insert the appropriate record
		$result = true;
		foreach ( $msgFor AS $thisMsgFor ) {
			$query = $GLOBALS['sql']->insert_query(
				"messages",
				array(
					"msgby"      => ( $message['system'] ? 0 : $this_user->user_number ),
					"msgfor"     => $thisMsgFor,
					"msgrecip"   => join( ',', $msgFor ),
					"msgpatient" => $message['patient'],
					"msgperson"  => $message['person'],
					"msgtext"    => $message['text'],
					"msgsubject" => $message['subject'],
					"msgurgency" => (int) $message['urgency'],
					"msgread"    => '0',
					"msgtag"     => ( $message['tag'] ? $message['tag'] : '' ),
					"msgunique"  => $unique,
					"msgtime"    => SQL__NOW
				)
			);
			syslog( LOG_DEBUG, "Messages.send| query = $query" );
			$result &= ( $GLOBALS['sql']->query( $query ) ? true : false );
		}

		// Save "sent" message
		if ( ! $message['system'] ) {
			$query = $GLOBALS['sql']->insert_query(
				"messages",
				array(
					"msgby"      => $this_user->user_number,
					"msgfor"     => $this_user->user_number,
					"msgrecip"   => join( ',', $msgFor ),
					"msgpatient" => $message['patient'],
					"msgperson"  => $message['person'],
					"msgtext"    => $message['text'],
					"msgsubject" => $message['subject'],
					"msgurgency" => (int) $message['urgency'],
					"msgread"    => '0',
					"msgtag"     => 'Sent',
					"msgunique"  => $unique,
					"msgtime"    => SQL__NOW
				)
			);
			syslog( LOG_DEBUG, "Messages.send| (sent message) query = $query" );
			$result &= ( $GLOBALS['sql']->query( $query ) ? true : false );
		}

		return $result;
	} // end method send

	// Method: TagModify
	//
	//	Change the tagging associated with a message.
	//
	// Parameters:
	//
	//	$message - Message table ID
	//
	//	$tag - Tag to associate with this message
	//
	// Returns:
	//
	//	Boolean, successful
	//
	public function TagModify ( $message, $tag ) {
		$q = $GLOBALS['sql']->update_query(
			"messages",
			array (
				"msgtag" => $tag
			), array ( "id" => $message + 0 )
		);
		$result = $GLOBALS['sql']->query( $q );
		return ( $result ? true : false );
	} // end method TagModify

	// Method: view
	//
	//	Get all messages for this user or patient. Use
	//	<view_per_user> and <view_per_patient> instead of
	//	using this function directly.
	//
	// Parameters:
	//
	//	$unread_only - (optional) Whether to retrieve messages
	//	that are unread, or if false to return all messages whether
	//	they have been read or not. Defaults to false.
	//
	//	$patient - (optional) Which patient to view messages for.
	//	This causes the search criteria to be per patient, not
	//	per user. This is wrapped by <view_per_patient>.
	//
	// Returns:
	//
	//	Array of associative arrays containing message information,
	//	or boolean false if there are no messages.
	//
	// See Also:
	//	<view_per_patient>
	//	<view_per_user>
	//
	public function view ($unread_only=false, $patient=NULL) {
		$this_user = freemed::user_cache();

		// Perform search
		if ($patient != NULL) {
			$query = "SELECT * FROM messages WHERE ".
			"LENGTH(msgtag)<1 AND ".
			"msgpatient='".addslashes($patient)."'".
			($unread_only ? " AND msgread='0' AND msgtag=''" : "" );
		} else {
			$query = "SELECT * FROM messages WHERE ".
			"LENGTH(msgtag)<1 AND ".
			"msgfor='".addslashes($this_user->user_number)."'".
			($unread_only ? " AND msgread='0' AND msgtag=''" : "" );
		}
		$result = $GLOBALS['sql']->queryAll($query);

		if (count($result)) {
			foreach ($result AS $r) {
				$return[] = array(
					"user"       => $r['msgfor'],
					"patient"    => $r['msgpatient'],
					"person"     => $r['msgperson'],
					"subject"    => $r['msgsubject'],
					"text"       => $r['msgtext'],
					"urgency"    => $r['msgurgency'],
					"read"       => $r['msgread'],
					"time"       => $r['msgtime'],
					"id"         => $r['id']
				);
			} // end while

			return $return;
		} else {
			return false;
		}
	} // end method view

	// Method: view_per_patient
	//
	//	Get all messages associated with a patient
	//
	// Parameters:
	//
	//	$patient - Patient id key
	//
	//	$unread_only - (optional) Boolean, restrict to unread
	//	messages only. Defaults to false.
	//
	// Returns:
	//
	//	Array of associative arrays containing message information.
	//
	// See Also:
	//	<view>
	//	<view_per_user>
	//
	public function view_per_patient ( $patient, $unread_only = false ) {
		return $this->view ( $unread_only, $patient );
	} // end method view_per_patient

	// Method: view_per_user
	//
	//	Get all messages associated with the current user
	//
	// Parameters:
	//
	//	$unread_only - (optional) Boolean, restrict to unread
	//	messages only. Defaults to false.
	//
	// Returns:
	//
	//	Array of associative arrays containing message information.
	//
	// See Also:
	//	<view>
	//	<view_per_patient>
	//
	public function view_per_user ( $unread_only = false ) {
		return $this->view ( $unread_only );
	} // end method view_per_user

} // end class Messages

?>
