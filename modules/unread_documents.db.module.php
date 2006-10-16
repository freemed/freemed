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

class UnreadDocuments extends SupportModule {

	var $MODULE_NAME = "Unread Documents";
	var $MODULE_HIDDEN = true;

	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "0905d8f4-b07c-43de-bcac-510812987e10";

	var $table_name = 'unreaddocument';

	public function __construct ( ) {
		// Set menu notify on the sidebar (or wherever the current
		// template decides to hide the notify items)
		$this->_SetHandler('MenuNotifyItems', 'notify');

		// Add this as a main menu handler as well
		$this->_SetHandler('MainMenu', 'MainMenuNotify');

		$this->table_definition = array (
			'urfdate'      => SQL__DATE, // date received
			'urffilename'  => SQL__VARCHAR(150), // temp file name
			'urftype'      => SQL__VARCHAR(50), // document type
			'urfpatient'   => SQL__INT_UNSIGNED(0),
			'urfphysician' => SQL__INT_UNSIGNED(0),
			'urfnote'      => SQL__TEXT, // note from filer
			'id' => SQL__SERIAL
		);

	 	$this->list_view = array (
			__("Date")        => "urfdate",
			__("Patient")     => "urfpatient:patient",
			__("Description") => "urfnote"
		);

		// Call parent constructor
		parent::__construct( );
	} // end constructor UnreadDocuments

	function notify ( ) {
		// Get current user object
		$user = CreateObject('org.freemedsoftware.core.User');

		// If user isn't a physician, no handler required
		if (!$user->isPhysician()) return false;

		// Get number of unread documentes from table
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($user->getPhysician())."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (sprintf(__("You have %d unread documentes"), $r['count']), 
			"module_loader.php?module=".urlencode(get_class($this)).
			"&action=display");
	} // end method notify

	function MainMenuNotify ( ) {
		// Try to import the user object
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('org.freemedsoftware.core.User');
		}

		// Only show something if they are a physician
		if (!$GLOBALS['this_user']->isPhysician()) {
			return false;
		}

		// Get number of unread documentes from table
		$r = $GLOBALS['sql']->queryOne("SELECT COUNT(*) AS count ".
			"FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($GLOBALS['this_user']->getPhysician())."'");
		if ($r < 1) { return false; }

		return array (
			__("Unread Documents"),
			( $r==1 ?
			__("There is currently 1 unread document in the system.") :
			sprintf(__("There are currently %d unread documentes in the system."), $unfiled) )." ".
			"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\">".
			"[".__("Read")."]</a>",
			"img/facsimile_icon.png"
		); 
	} // end method MainMenuNotify

	// Method: MoveToAnotherProvider
	//
	//	Moves an unread document to be associated with another provider in
	//	the system.
	//
	// Parameters:
	//
	//	$id - Record id for document.
	//
	//	$to - Destination provider.
	//
	public function MoveToAnotherProvider ( $id, $to ) {
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );

		$filename = freemed::secure_filename( $rec['urffilename'] );

		// Document sanity check
		if ( ! file_exists('data/document/unread/'.$filename) or empty($filename) ) {
			syslog(LOG_INFO, "UnreadDocument| attempted to file document that doesn't exist ($filename)");
			return false;
		}

		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array (
				'urfphysician' => $to
			), array ( 'id' => $id )
		);
		$r = $GLOBALS['sql']->query( $q );
		return true;
	} // end method MoveToAnotherProvider

	protected function mod_pre ( &$data ) {
		$filename = freemed::secure_filename( $data['urffilename'] );

		// Document sanity check
		if (!file_exists('data/document/unread/'.$filename) or empty($filename)) {
			syslog(LOG_INFO, "UnreadDocument| attempted to file document that doesn't exist ($filename)");
			return false;
		}

		// Extract type and category
		list ($type, $cat) = explode('/', $data['urftype']);

		// Create user object
		$this_user = CreateObject('org.freemedsoftware.core.User');
		
		// Insert new table query in unread
		$query = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'images',
			array (
				"imagedt" => $data['urfdate'],
				"imagepat" => $data['urfpatient'],
				"imagetype" => $type,
				"imagecat" => $cat,
				"imagedesc" => $data['urfnote'],
				"imagephy" => $data['urfphysician'],
				"imagereviewed" => $this_user->user_number
			)
		));

		$new_id = $GLOBALS['sql']->lastInsertID( 'images', 'id' );
		$new_filename = freemed::image_filename (
			freemed::secure_filename( $data['urfpatient'] ),
			$new_id,
			'djvu',
			true
		);

		$query = $GLOBALS['sql']->update_query (
			'images',
			array ( 'imagefile' => $new_filename ),
			array ( 'id' => $new_id )
		);
		$result = $GLOBALS['sql']->query( $query );
		syslog(LOG_INFO, "UnreadDocument| query = $query, result = $result");

		// Move actual file to new location
		//echo "mv data/document/unread/$filename $new_filename -f<br/>\n";
		$dirname = dirname($new_filename);
		`mkdir -p "$dirname"`;
		//echo "mkdir -p $dirname";
		`mv "data/document/unread/$filename" "$new_filename" -f`;

		$GLOBALS['sql']->query("DELETE FROM ".$this->table_name." WHERE id='".addslashes($data['id'])."'");
	} // end method mod_pre

} // end class UnreadDocuments

register_module('UnreadDocuments');

?>
