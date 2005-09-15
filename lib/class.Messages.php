<?php
	// $Id$
	// $Author$

// Class: FreeMED.Messages
class Messages {

	function Messages ( ) { }

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
		if (!is_object($this_user)) $this_user = CreateObject('_FreeMED.User');

		// Perform search
		$query = "SELECT * FROM messages WHERE id='".addslashes($message)."'";
		$result = $GLOBALS['sql']->query($query);

		if ($GLOBALS['sql']->results($result)) {
			$r = $GLOBALS['sql']->fetch_array($result);

			// Check for appropriate access (correct user)
			if ($r['msgfor'] != $this_user->user_number) {
				return false;
			}
			
			return array (
				'physician'  => $r['msgfor'],
				'patient'    => $r['msgpatient'],
				'person'     => $r['msgperson'],
				'subject'    => $r['msgsubject'],
				'text'       => $r['msgtext'],
				'urgency'    => $r['msgurgency'],
				'read'       => $r['msgread'],
				'time'       => $r['msgtime']
			);
		} else {
			return false;
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
		$res = $GLOBALS['sql']->query($query);
		$a = array ();
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
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
		if (!is_object($this_user)) $this_user = CreateObject('_FreeMED.User');
		
		// Perform actual deletion
		$result = $GLOBALS['sql']->query(
			"DELETE FROM messages WHERE ".
			"id='".addslashes($message_id)."' AND ".
			"msgfor='".addslashes($this_user->user_number)."'"
		);
		return result;
	} // end method remove

	// Method: send
	//
	//	Send a message using the FreeMED messaging system
	//
	// Parameters:
	//
	//	$message - Associative array containing information
	//	describing the message.
	//
	// Returns:
	//
	//	Boolean, successful
	//
	function send ($message) {
		global $this_user;
		if (!is_object($this_user)) $this_user = CreateObject('_FreeMED.User');

		// Check for error conditions
		if (($message['patient'] < 1) and (empty($message['person']))) { 
			return false;
		}

		// Insert the appropriate record
		$result = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			"messages",
			array(
				"msgby"      => ( $message['system'] ? 0 : $this_user->user_number ),
				"msgfor"     => $message['user'],
				"msgrecip"   => $message['user'],
				"msgpatient" => $message['patient'],
				"msgperson"  => $message['person'],
				"msgtext"    => $message['text'],
				"msgsubject" => $message['subject'],
				"msgurgency" => $message['urgency'],
				"msgread"    => '0',
				"msgtag"     => '',
				"msgunique"  => mktime(),
				"msgtime"    => SQL__NOW
			)
		));
		return $result;
	} // end method send

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
	function view ($unread_only=false, $patient=NULL) {
		global $this_user;
		if (!is_object($this_user)) $this_user = CreateObject('_FreeMED.User');

		// Perform search
		if ($patient != NULL) {
			$query = "SELECT * FROM messages WHERE ".
			"msgpatient='".addslashes($patient)."'".
			($unread_only ? " AND msgread='0'" : "" );
		} else {
			$query = "SELECT * FROM messages WHERE ".
			"msgfor='".addslashes($this_user->user_number)."'".
			($unread_only ? " AND msgread='0'" : "" );
		}
		$result = $GLOBALS['sql']->query($query);

		if ($GLOBALS['sql']->results($result)) {
			while ($r = $GLOBALS['sql']->fetch_array($result)) {
				$return[] = array(
					"user"       => $r['msgfor'],
					"patient"    => $r['msgpatient'],
					"person"     => $r['msgperson'],
					"subject"    => $r['msgsubject'],
					"text"       => $r['msgtext'],
					"urgency"    => $r['msgurgency'],
					"read"       => $r['msgread'],
					"time"       => $r['msgtime']
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
	function view_per_patient ( $patient, $unread_only = false ) {
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
	function view_per_user ( $unread_only = false ) {
		return $this->view ( $unread_only );
	} // end method view_per_user

} // end class Messages

?>
