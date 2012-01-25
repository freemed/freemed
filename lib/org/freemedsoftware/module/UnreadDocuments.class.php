<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class UnreadDocuments extends SupportModule {

	var $MODULE_NAME = "Unread Document";
	var $MODULE_VERSION = "0.3";
	var $MODULE_HIDDEN = true;

	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "0905d8f4-b07c-43de-bcac-510812987e10";

	var $table_name = 'unreaddocuments';

	public function __construct ( ) {
		// Set menu notify on the sidebar (or wherever the current
		// template decides to hide the notify items)
		$this->_SetHandler('MenuNotifyItems', 'notify');

		// Add this as a main menu handler as well
		$this->_SetHandler('MainMenu', 'MainMenuNotify');

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

		// Get number of unread documents from table
		$count = $GLOBALS['sql']->queryOne("SELECT COUNT(*) AS count ".
			"FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($user->getPhysician())."'");
		if ($count < 1) { return false; }

		return array (sprintf(__("You have %d unread documents"), $count), 
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

		// Get number of unread documents from table
		$r = $GLOBALS['sql']->queryOne("SELECT COUNT(*) AS count ".
			"FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($GLOBALS['this_user']->getPhysician())."'");
		if ($r < 1) { return false; }

		return array (
			__("Unread Documents"),
			( $r==1 ?
			__("There is currently 1 unread document in the system.") :
			sprintf(__("There are currently %d unread documents in the system."), $unfiled) )." ".
			"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\">".
			"[".__("Read")."]</a>",
			"img/facsimile_icon.png"
		); 
	} // end method MainMenuNotify

	// Method: NumberOfPages
	//
	//	Expose the number of pages of a Djvu document
	//
	// Parameters:
	//
	//	$id - Table record id
	//
	// Returns:
	//
	//	Integer, number of pages in the specified document
	//
	public function NumberOfPages ( $id ) {
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			$this->GetLocalCachedFile( $id ) );
		return $djvu->NumberOfPages();
	} // end method NumberOfPages

	// Method: GetDocumentPage
	//
	//	Get fax/document page image as JPEG.
	//
	// Parameters:
	//
	//	$id - Record id of unread document
	//
	//	$page - Page number
	//
	//	$thumbnail - (optional) Boolean, if image is to be rendered
	//	as a thumbnail. Defaults to false.
	//
	// Returns:
	//
	//	BLOB data containing jpeg image.
	//
	public function GetDocumentPage( $id, $page, $thumbnail = false ) {
		// Return image ...
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			$this->GetLocalCachedFile( $id ) );

		return readfile( $thumbnail ? $djvu->GetPageThumbnail( $page ) : $djvu->GetPage( $page, false, false, false ) );
	} // end method GetDocumentPage

	// Method: GetAll
	//
	//	Get all records for the current user.
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function GetAll ( ) {
		$user = freemed::user_cache( );
		$provider = $user->getPhysician();
		if (! $provider ) { return array(); }
		$query = "SELECT u.*,DATE_FORMAT(u.urfdate, '%m/%d/%Y') AS urfdate_mdy, c.description AS category, CONCAT(p.ptfname, ' ', p.ptlname, ' (', p.ptid, ')') AS patient FROM ".$this->table_name." u LEFT OUTER JOIN documents_tc c ON c.id = u.urftype LEFT OUTER JOIN patient p ON p.id=u.urfpatient WHERE urfphysician=".($provider+0)." ORDER BY id DESC";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetAll

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
	// Returns:
	//
	//	Boolean, success.
	//
	public function MoveToAnotherProvider ( $id, $to ) {
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );

		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array (
				'urfphysician' => (int) $to
			), array ( 'id' => (int) $id )
		);
		$r = $GLOBALS['sql']->query( $q );
		return true;
	} // end method MoveToAnotherProvider

	// Method: ReviewIntoRecord
	//
	//	Review patient unread document into scanned document in
	//	patient record.
	//
	// Parameters:
	//
	//	$id - Record ID of unread document
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function ReviewIntoRecord ( $id ) {
		syslog( LOG_DEBUG, "ReviewIntoRecord $id" );
		$data = $GLOBALS['sql']->get_link( $this->table_name, $id );

		$this_user = freemed::user_cache()->user_number;
		
		$filename = $this->GetLocalCachedFile( $id );
		syslog( LOG_DEBUG, "user = this_user, filename = $filename" );

		// Document sanity check
		if ( $data['id'] == 0 ) {
			syslog(LOG_INFO, "UnreadDocument| attempted to file document that doesn't exist ($filename)");
			return false;
		}

		// Extract type and category
		list ($type, $cat) = explode('/', $data['urftype']);

		// Insert new table query in unread
		$query = $GLOBALS['sql']->insert_query(
			'images',
			array (
				"imagedt" => $data['urfdate'],
				"imagepat" => $data['urfpatient'],
				"imagetype" => $type,
				"imagecat" => $cat,
				"imagedesc" => $data['urfnote'],
				"imagephy" => $data['urfphysician'],
				"imagetext" => $data['urftext'],
				"imagereviewed" => $this_user,
				"user" => $this_user
			)
		);
		syslog( LOG_DEBUG, "query = $query" );
		$result = $GLOBALS['sql']->query( $query );

		$new_id = $GLOBALS['sql']->lastInsertID( 'images', 'id' );
		$new_filename = freemed::image_filename (
			freemed::secure_filename( $data['urfpatient'] ),
			$new_id,
			'djvu',
			true
		);
		syslog( LOG_DEBUG, "insert id = $new_id, filename = $new_filename" );

		$query2 = $GLOBALS['sql']->update_query (
			'images',
			array ( 'imagefile' => $new_filename ),
			array ( 'id' => $new_id )
		);
		$result2 = $GLOBALS['sql']->query( $query2 );

		// Move actual file to new location
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		$pds->StoreFile( $data['urfpatient'], "scanneddocuments", $new_id, file_get_contents( $this->GetLocalCachedFile( $id ) ) );

		$q = "DELETE FROM ".$this->table_name." WHERE id=".$GLOBALS['sql']->quote( $id );
		syslog( LOG_DEBUG, "q = $q" );
		$GLOBALS['sql']->query( $q );

		return true;
	} // end method ReviewIntoRecord

	// Method: GetCount
	//
	//	Retrieve number of unread documents in the system for the current user.
	//
	// Returns:
	//
	//	Current number of unfiled documents in the system.
	//
	public function GetCount ( ) {
		$user = freemed::user_cache();
		if (!$GLOBALS['this_user']->isPhysician()) { return 0; }
		$q = "SELECT COUNT(*) AS unread FROM ".$this->table_name." WHERE urfphysician=".$GLOBALS['sql']->quote( $user->getPhysician() );
		$r = $GLOBALS['sql']->queryOne( $q );
		return $r;
	} // end method GetCount

	// Method: GetLocalCachedFile
	//
	// Parameters:
	//
	//	$id - Table id
	//
	// Returns:
	//
	//	Path to locally cached filesystem copy of database object.
	//
	public function GetLocalCachedFile( $id ) {
		// Create hash for filename
		$hash = PHYSICAL_LOCATION . "/data/cache/" . $this->table_name . "-" . md5( $id );

		// If it exists, return file name
		if (file_exists( $hash )) {
			return $hash;
		} else {
			// ... otherwise cache it first ...
	                $r = $GLOBALS['sql']->get_link( $this->table_name, $id );
			file_put_contents( $hash, $r['urffile'] );
			// ... then return the hash.
			return $hash;
		}
	} // end method GetLocalCachedFile

	protected function UpdateFileFromCachedFile( $id ) {
		$hash = PHYSICAL_LOCATION . "/data/cache/" . $this->table_name . "-" . md5( $id );	

		if (!file_exists( $hash )) {
			return false;
		} else {
			$query = $GLOBALS['sql']->update_query(
				$this->table_name,
				array (
					'urffile' => file_get_contents( $hash )	
				), array ( 'id' => $id )
			);
			$GLOBALS['sql']->query( $query );
			return true;
		}
	} // end method UpdateFileFromCachedFile

} // end class UnreadDocuments

register_module('UnreadDocuments');

?>
