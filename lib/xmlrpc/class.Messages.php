<?php
 // $Id$
 // $Author$
 // Defines FreeMED.Messages.* namespace

class Messages {

	function get ($message) {
		global $sql;

		// Perform search
		$query = "SELECT * FROM messages WHERE id='".addslashes($message)."'";
		$result = $sql->query($query);

		if ($sql->results($result)) {
			$r = $sql->fetch_array($result);

			// Check for appropriate access (correct doc)
			if ($r['msgfor'] != $GLOBALS['__freemed']['basic_auth_id']) {
				// Deny access
				return CreateObject('PHP.xmlrpcval', false, 'boolean');
			}
			
			$element = CreateObject('PHP.xmlrpcval');
			$element->addStruct(array(
				"physician"  => rpc_prepare($r['msgfor']),
				"patient"    => rpc_prepare($r['msgpatient']),
				"person"     => rpc_prepare($r['msgperson']),
				"subject"    => rpc_prepare($r['msgsubject']),
				"text"       => rpc_prepare($r['msgtext']),
				"urgency"    => rpc_prepare($r['msgurgency']),
				"read"       => rpc_prepare($r['msgread']),
				"time"       => rpc_prepare($r['msgtime'])
			));

			// Create the wrapper and return the element
			return CreateObject('PHP.xmlrpcresp', 
				CreateObject('PHP.xmlrpcval', $element, 'struct')
			);
		} else {
			return CreateObject('PHP.xmlrpcval', false, 'boolean');
		}
	} // end method get

	function remove ($message_id) {
		global $sql;

		// Perform actual deletion
		$res = $sql->query("DELETE FROM messages WHERE ".
			"id='".addslashes($message_id)."' AND ".
			"msgfor='".addslashes($GLOBALS['__freemed']['basic_auth_id'])."'");
		return CreateObject('PHP.xmlrpcval', $res, "boolean");
	} // end method remove

	function send ($message) {
		global $sql;

		// Check for error conditions
		if (($message['patient'] < 1) and (empty($message['person']))) { 
			return CreateObject('PHP.xmlrpcval', false, 'boolean');
		}

		// Insert the appropriate record
		$res = $sql->query($sql->insert_query(
				"messages",
				array(
					"msgfor"     => $message['physician'],
					"msgpatient" => $message['patient'],
					"msgperson"  => $message['person'],
					"msgtext"    => $message['text'],
					"msgsubject" => $message['subject'],
					"msgurgency" => $message['urgency'],
					"msgtag"     => '',
					"msgread"    => '0',
					"msgtime"    => SQL__NOW
				)
			));
		return CreateObject('PHP.xmlrpcval', $res, 'boolean');
	} // end method send

	function unread ( ) {
		global $sql;

		// Perform search
		$query = "SELECT COUNT(*) AS my_count FROM messages WHERE ".
			"msgfor='".addslashes($GLOBALS['__freemed']['basic_auth_id'])."'".
			" AND msgread='0'";
		$result = $sql->query($query);

		if ($sql->results($result)) {
			$r = $sql->fetch_array($result);
			//syslog(LOG_INFO, "FreeMED.Messages.unread: should return ".$r['my_count']);
			return CreateObject('PHP.xmlrpcval', $r['my_count']+0, 'int');
		}

		return CreateObject('PHP.xmlrpcval', 0, 'int');
	} // end method unread
		
	function view ($unread_only=false) {
		global $sql;

		// Perform search
		$query = "SELECT * FROM messages WHERE ".
			"msgfor='".addslashes($GLOBALS['__freemed']['basic_auth_id'])."'".
			($unread_only ? " AND msgread='0'" : "" );
		$result = $sql->query($query);

		if ($sql->results($result)) {
			while ($r = $sql->fetch_array($result)) {

				$element = CreateObject('PHP.xmlrpcval');
				$element->addStruct(array(
					"physician"  => rpc_prepare($r['msgfor']),
					"patient"    => rpc_prepare($r['msgpatient']),
					"person"     => rpc_prepare($r['msgperson']),
					"subject"    => rpc_prepare($r['msgsubject']),
					"text"       => rpc_prepare($r['msgtext']),
					"urgency"    => rpc_prepare($r['msgurgency']),
					"read"       => rpc_prepare($r['msgread']),
					"time"       => rpc_prepare($r['msgtime'])
				));

				// Add element to the stack
				$a[] = $element;
			} // end while

			// Create the wrapper and return the elements
			return CreateObject('PHP.xmlrpcresp', 
				CreateObject('PHP.xmlrpcval', $a, 'array')
			);
		} else {
			return CreateObject('PHP.xmlrpcval', false, 'boolean');
		}
	} // end method view

} // end class Messages

?>
