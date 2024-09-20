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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class UnfiledDocuments extends SupportModule {

	var $MODULE_NAME = "Unfiled Documents";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "edcf764c-1c99-4abd-924a-39d795541b44";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = "0.8.0";

	var $table_name = 'unfileddocuments';

	var $variables = array (
		'uffdate',
		'ufffilename'
	);

	public function __construct ( ) {
		// __("Unfiled Documents")

		// Add main menu notification handlers
		$this->_SetHandler('MenuNotifyItems', 'menu_notify');
		$this->_SetHandler('MainMenu', 'notify');
		
		// Form proper configuration information
		$this->_SetMetaInformation('global_config_vars', array(
			'uffax_user'
		));
		$this->_SetMetaInformation('global_config', array(
			__("Recipient(s)") =>
			'freemed::multiple_choice ( '.
				'"SELECT CONCAT(username, \' (\', userdescrip, \')\') '.
				'AS descrip, id FROM user ORDER BY descrip", "descrip", '.
				'"uffax_user", fm_join_from_array($uffax_user))'
			)
		);
	
		$this->list_view = array (
			__("Date")        => "uffdate",
			__("File name")   => "ufffilename"
		);
		
		// Call parent constructor
		parent::__construct();
	} // end constructor UnfiledDocuments

	protected function add_pre ( &$data ) {
		syslog( LOG_DEBUG, get_class($this)."::add_pre ( ... )" );
		// Temporarily set filename to something absurd
		$data['uffilename'] = '-';
	} // end method add_pre

	protected function add_post ( $id, $data ) {
		// Handle uploads, if they exist
		syslog( LOG_DEBUG, get_class($this)."::add_post ( $id, ... )" );
		if ( $_FILES['file']['name'] != '' ) {
			if ( $_FILES['file']['error'] == UPLOAD_ERR_OK ) {
				$orig = $_FILES['file']['name'];
				if ( file_exists( $_FILES['file']['tmp_name'] ) ) {
					syslog( LOG_INFO, get_class($this)."::add_post received $orig for id $id" );
					$query = $GLOBALS['sql']->update_query(
						$this->table_name,
						array (
							  'ufffilename' => $orig
							, 'ufffile' => file_get_contents( $_FILES['file']['tmp_name'] )
						),
						array ( 'id' => $id )
					);
					$GLOBALS['sql']->query( $query );
				} else {
					syslog( LOG_ERR, get_class($this)."::add_post failed to receive $orig for id $id" );
				}
			}
		}
	} // end method add_post

	protected function mod_pre ( &$data ) {
		$id = $data['id'];
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$filename = $this->GetLocalCachedFile( $id );

		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$data['date'] = $s->ImportDate( $data['date'] );

		// Catch multiple people using the same document
		if ( $rec['ufffile'] == '' ) {
			trigger_error(__("Document file does not exist!"));
		}

		if ($data['flip'] == 1) {
			syslog(LOG_INFO, "flip");
			$command = "./scripts/flip_djvu.sh \"{$filename}\"";
			system("$command");
			$this->UpdateFileFromCachedFile( $id );
		}

		if (!empty($data['faxback'])) {
			syslog(LOG_INFO, "faxback");
			$this->faxback( $data['id'], $data['faxback'] );
		}

		if ($data['notify']+0 > 0) {
			syslog(LOG_INFO, "notify");
			$msg = CreateObject('org.freemedsoftware.api.Messages');
			$msg->send(array(
				'patient' => $data['patient'],
				'user' => $data['notify'],
				'urgency' => 4,
				'text' => __("Document received for patient").
					" (".$data['note'].")"
			));
		}

		// If we're removing the first page, do that now
		if ($data['withoutfirstpage']) {
			syslog(LOG_INFO, "remove 1st page");
			$command = "/usr/bin/djvm -d ".escapeshellarg($filename)." 1";
			system("$command");
			$this->UpdateFileFromCachedFile( $id );
		}

		// Figure category / type
		$cat = $GLOBALS['sql']->get_link( 'documents_tc', $data['category'] );

		if ($data['filedirectly']) {
			syslog(LOG_INFO, "directly");
			// Insert new table query in unread
			$query = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
				'images',
				array (
					"imagedt" => $data['date'],
					"imagepat" => $data['patient'],
					"imagetype" => $data['category'],
					//"imagecat" => $cat['category'],
					"imagedesc" => $data['note'],
					"imagephy" => $data['physician'],
					"imagetext" => $data['text'],
					"imagereviewed" => 0,
					"user" => freemed::user_cache()->user_number
				)
			));
			$new_id = $GLOBALS['sql']->lastInsertID( 'images', 'id' );

			$new_filename = freemed::image_filename(
				freemed::secure_filename($data['patient']),
				$new_id,
				'djvu',
				true
			);

			$query = $GLOBALS['sql']->update_query(
				'images',
				array ( 'imagefile' => $new_filename ),
				array ( 'id' => $new_id )
			);

			// Move actual file to new location
			$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
			$pds->StoreFile( $data['urfpatient'], "scanneddocuments", $new_id, file_get_contents( $this->GetLocalCachedFile( $id ) ) );
		} else {
			// Insert new table query in unread
			$result = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
				'unreaddocuments',
				array (
					"urfdate" => $data['date'],
					"urffilename" => $filename,
					"urffile" => file_get_contents( $filename  ),
					"urfpatient" => $data['patient'],
					"urfphysician" => $data['physician'],
					"urftype" => $data['category'],
					"urfnote" => $data['note'],
					"user" => freemed::user_cache()->user_number
				)
			));
		}

		// Remove old entry	
		$GLOBALS['sql']->query("DELETE FROM `".$this->table_name."` WHERE id='".addslashes($data['id'])."'");

		//$new_id = $GLOBALS['sql']->lastInsertID( $this->table_name, 'id' );

		$this->save_variables = $this->variables;
		unset ( $this->variables );
	} // end method mod_pre

	function mod_post ( $data ) {
		// Restore variables
		$this->variables = $this->save_variables;
	} // end method mod_post

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
		return (int)$djvu->NumberOfPages();
	} // end method NumberOfPages

	// Method: batchSplit
	//
	//	Split multiple faxed documents.
	//
	// Parameters:
	//
	//	$id - Record id
	//
	//	$splitafter - Array of "splits"
	//
	public function batchSplit ( $id, $splitafter ) {
		// Get the "splits"

		// Get page information
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			$this->GetLocalCachedFile( $id ) );
		$pages = $djvu->NumberOfPages();
		$chunks = $djvu->StoredChunks();

		// Create temporary extraction location
		$dir_prefix = tempnam('/tmp', 'fmdir');
		$dir = $dir_prefix.'.d';

		// Extract
		$filename = $djvu->GetFileName();
		system('mkdir '.escapeshellarg($dir));
		//print "dir = $dir<br/>\n";
		system('djvmcvt -i '.escapeshellarg($filename).' '.escapeshellarg($dir).' '.escapeshellarg("$dir/index.djvu"));;

		// Figure out where the splits are ...
		$cur = 1;
		for ($i = 1; $i <= $pages; $i++) {
			$d[$cur][] = $i;
			if ($splitafter[$i] == 1) {
				$cur++;
			}
		}

		// Reassemble
		foreach ($d AS $k => $v) {
			$hash = "";

			// Put together lists of files
			foreach ($v AS $this_file) {
				$hash .= escapeshellarg($dir."/".$chunks[$this_file-1]).' ';
			}

			// New Filename
			$new_filename = $filename.'.'.$k.'.djvu';

			// Create new file
			$output = system('djvm -c '.escapeshellarg($new_filename).' '.$hash);

			// Erase temporary files
			unlink($dir."/index.djvu");
			foreach ($pages AS $_page) {
				unlink($dir."/".$_page);
			}
			unlink($dir_prefix);
			unlink($dir);

			// Add new entry for fax file
			$result = $GLOBALS['sql']->query(
				$GLOBALS['sql']->insert_query(
					$this->table_name,
					array (
						'uffdate' => $r['uffdate'],
						'ufffilename' => basename(trim($new_filename)),
						'ufffile' => file_get_contents( $new_filename )
					)
				)
			);

			// TODO TODO: Make sure to erase old fax
		}
		
		// Cleanup
		unlink($dir);
		unlink($dir_prefix);
		return true;
	} // end method batchSplit

	// Method: GetDocumentPage
	//
	//	Get fax/document page image as JPEG.
	//
	// Parameters:
	//
	//	$id - Record id of unfiled document
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
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			$this->GetLocalCachedFile( $id ) );

		Header( "Content-type: image/jpeg" );
		return readfile( $thumbnail ? $djvu->GetPageThumbnail( $page ) : $djvu->GetPage( $page, false, false, false ) );
	} // end method GetDocumentPage

	// Method: GetAll
	//
	//	Get all records.
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function GetAll ( ) {
		$query = "SELECT *,DATE_FORMAT(uffdate, '%m/%d/%Y') AS uffdate_mdy FROM ".$this->table_name." ORDER BY id DESC";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetAll

	// Method: faxback
	//
	// Parameters:
	//
	//	$id - Record id
	//
	//	$faxback - Fax number to send faxback to
	//
	public function faxback ( $id, $faxback ) {
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );

		// Analyze File
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			$this->GetLocalCachedFile( $id ) );
		$pages = $djvu->NumberOfPages( );

		// Fax the first page back
		$tempfile = $djvu->GetPage( 1, false, true );
		$fax = CreateObject('org.freemedsoftware.core.Fax',
			$tempfile,
			array (
				'sender' => INSTALLATION." (".PACKAGENAME." v".DISPLAY_VERSION.")",
				'subject' => '['.$pages.' '.__("page(s) received").']',
				'comments' => __("All pages received.").' '.
					__("Thank you.")
			)
		);
		$output = $fax->Send( $faxback );
		unlink($tempfile);
	} // end method faxback

	function notify ( ) {
		// Check to see if we're the person who is supposed to be
		// notified. If not, die out right now.
		$supposed = freemed::config_value('uffax_user');
		$authdata = HTTP_Session2::get( 'authdata' );
		if (!(strpos($supposed, ',') === false)) {
			// Handle array
			$found = false;
			foreach (explode(',', $supposed) AS $s) {
				if ($s == $authdata['user']) { $found = true; }
			}
			if (!$found) { return false; }
		} else {
			if (($supposed > 0) and ($supposed != $authdata['user'])) {
				return false;
			}
		}
	
		// Decide if we have any "unfiled documents" in the system
		$query = "SELECT COUNT(*) AS unfiled FROM ".$this->table_name;
		$unfiled = $GLOBALS['sql']->queryOne( $query );
		if ($unfiled > 0) {
			return array (
				__("Unfiled Documents"),
				( $unfiled==1 ?
				__("There is currently 1 unfiled document in the system.") :
				sprintf(__("There are currently %d unfiled document(s) in the system."), $unfiled) )." ".
				"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\">".
				"[".__("File")."]</a>",
				"img/facsimile_icon.png"
			);
		} else {
			// For now, we're just going to return nothing so that
			// the box doesn't show up
			return false;
			return array (
				__("Unfiled Documents"),
				__("There are no unfiled documents at this time."),
				"img/facsimile_icon.png"
			);
		}
	} // end method notify

	function menu_notify ( ) {
		// Check to see if we're the person who is supposed to be
		// notified. If not, die out right now.
		$supposed = freemed::config_value('uffax_user');
		$authdata = HTTP_Session2::get( 'authdata' );
		if (!(strpos($supposed, ',') === false)) {
			// Handle array
			$found = false;
			foreach (explode(',', $supposed) AS $s) {
				if ($s == $authdata['user']) { $found = true; }
			}
			if (!$found) { return false; }
		} else {
			if (($supposed > 0) and ($supposed != $authdata['user'])) {
				return false;
			}
		}
	
		// Decide if we have any "unfiled documents" in the system
		$query = "SELECT COUNT(*) AS unfiled FROM ".$this->table_name;
		$unfiled = $GLOBALS['sql']->queryOne( $query );
		if ($unfiled > 0) {
			return array (
				sprintf(__("You have %d unfiled documents"), $unfiled),
				"module_loader.php?module=".urlencode(get_class($this))."&action=display"
			);
		} else {
			// For now, we're just going to return nothing so that
			// the box doesn't show up
			return false;
		}
	} // end method menu_notify

	// Method: GetCount
	//
	//	Retrieve number of unfiled documents in the system.
	//
	// Returns:
	//
	//	Current number of unfiled documents in the system.
	//
	public function GetCount ( ) {
		$q = "SELECT COUNT(*) AS unfiled FROM ".$this->table_name;
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
	protected function GetLocalCachedFile( $id ) {
		// Create hash for filename
		$hash = PHYSICAL_LOCATION . "/data/cache/" . $this->table_name . "-" . md5( $id );

		// If it exists, return file name
		if (file_exists( $hash )) {
			return $hash;
		} else {
			// ... otherwise cache it first ...
	                $r = $GLOBALS['sql']->get_link( $this->table_name, $id );
			file_put_contents( $hash, $r['ufffile'] );
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
					'ufffile' => file_get_contents( $hash )	
				), array ( 'id' => $id )
			);
			$GLOBALS['sql']->query( $query );
			return true;
		}
	} // end method UpdateFileFromCachedFile

} // end class UnfiledDocuments

register_module('UnfiledDocuments');

?>
