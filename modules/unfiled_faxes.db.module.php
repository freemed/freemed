<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

class UnfiledFaxes extends SupportModule {

	var $MODULE_NAME = "Unfiled Faxes";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "edcf764c-1c99-4abd-924a-39d795541b44";
	var $PACKAGE_MINIMUM_VERSION = "0.7.0";

	var $table_name = 'unfiledfax';

	public function __construct ( ) {
		// __("Unfiled Faxes")

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
	} // end constructor UnfiledFaxes

	protected function del_pre ( $id ) {
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Remove file name
		unlink('data/fax/unfiled/'.$filename);
	} // end method del_pre

	protected function mod_pre ( $data ) {
		$id = $data['id'];
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$filename = freemed::secure_filename( $rec['ufffilename'] );

		// Catch multiple people using the same fax
		if (!file_exists('data/fax/unfiled/'.$filename)) {
			trigger_error(__("Fax file does not exist!"));
		}

		if ($data['flip'] == 1) {
			$command = "./scripts/flip_djvu.sh \"\$(pwd)/data/fax/unfiled/${filename}\"";
			system("$command");
		}

		if (!empty($data['faxback'])) {
			$this->faxback( $data['id'], $data['faxback'] );
		}

		if ($data['notify']+0 > 0) {
			$msg = CreateObject('org.freemedsoftware.api.Messages');
			$msg->send(array(
				'patient' => $data['patient'],
				'user' => $data['notify'],
				'urgency' => 4,
				'text' => __("Fax received for patient").
					" (".$data['note'].")"
			));
		}

		// If we're removing the first page, do that now
		if ($data['withoutfirstpage']) {
			$command = "/usr/bin/djvm -d ".escapeshellarg("data/fax/unfiled/${filename}")." 1";
			system("$command");
		}

		// Move actual file to new location
		//echo "mv data/fax/unfiled/$filename data/fax/unread/$filename -f";
		if ($filename) { system('mv '.escapeshellarg("data/fax/unfiled/${filename}").' '.escapeshellarg("data/fax/unread/${filename}").' -f'); }

		if ($data['filedirectly']) {
			// Extract type and category
			list ($type, $cat) = explode('/', $data['type']);
		
			// Insert new table query in unread
			$query = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
				'images',
				array (
					"imagedt" => $data['date'],
					"imagepat" => $data['patient'],
					"imagetype" => $type,
					"imagecat" => $cat,
					"imagedesc" => $data['note'],
					"imagephy" => $data['physician'],
					"imagereviewed" => 0
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
			//echo "mv data/fax/unfiled/$filename $new_filename -f<br/>\n";
			$dirname = dirname($new_filename);
			system('mkdir -p '.escapeshellarg($dirname));
			if ($filename) { system('mv '.escapeshellarg("data/fax/unfiled/${filename}").' '.escapeshellarg($new_filename).' -f'); syslog(LOG_INFO, "UnfiledFax| mv data/fax/unfiled/$filename $new_filename -f"); }
		} else {
			// Insert new table query in unread
			$result = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
				'unreadfax',
				array (
					"urfdate" => $data['date'],
					"urffilename" => $filename,
					"urfpatient" => $data['patient'],
					"urfphysician" => $data['physician'],
					"urftype" => $data['type'],
					"urfnote" => $data['note']
				)
			));
		}

		$new_id = $GLOBALS['sql']->lastInsertID( $this->table_name, 'id' );
	} // end method mod_pre

	function mod_post ( $data ) {
		// Don't use the delete method, because we don't want to remove files
		$GLOBALS['sql']->query("DELETE FROM `".$this->table_name."` WHERE id='".addslashes($data['id'])."'");
	} // end method mod_post

	public function numberOfPages ( $id ) {
		$r = $GLOBALS['sql']->get_link ( $this->table_name, $id );
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$r['ufffilename']);
		return $djvu->NumberOfPages();
	} // end method numberOfPages

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
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$r['ufffilename']);
		$pages = $djvu->NumberOfPages();
		$chunks = $djvu->StoredChunks();

		// Create temporary extraction location
		$dir_prefix = tempnam('/tmp', 'fmdir');
		$dir = $dir_prefix.'.d';

		// Extract
		$filename = $djvu->filename;
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
						'ufffilename' => basename(trim($new_filename))
					)
				)
			);

			// TODO TODO: Make sure to erase old fax
		}
		
		// Cleanup
		unlink($dir);
		unlink($dir_prefix);
	} // end method batchSplit

	// Method: getFaxPage
	//
	//	Get fax page image as JPEG.
	//
	// Parameters:
	//
	//	$id - Record id of unfiled fax
	//
	//	$page - Page number
	//
	// Returns:
	//
	//	BLOB data containing jpeg image.
	//
	public function getFaxPage( $id, $page ) {
		// Return image ...
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$r['ufffilename']);
		return $djvu->GetPageThumbnail($page);
	} // end method getFaxPage

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
		$filename = freemed::secure_filename( $rec['ufffilename'] );

		// Analyze File
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$filename);
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
		if (!(strpos($supposed, ',') === false)) {
			// Handle array
			$found = false;
			foreach (explode(',', $supposed) AS $s) {
				if ($s == $_SESSION['authdata']['user']) { $found = true; }
			}
			if (!$found) { return false; }
		} else {
			if (($supposed > 0) and ($supposed != $_SESSION['authdata']['user'])) {
				return false;
			}
		}
	
		// Decide if we have any "unfiled faxes" in the system
		$query = "SELECT COUNT(*) AS unfiled FROM ".$this->table_name;
		$result = $GLOBALS['sql']->query($query);
		extract($GLOBALS['sql']->fetch_array($result));
		if ($unfiled > 0) {
			return array (
				__("Unfiled Faxes"),
				( $unfiled==1 ?
				__("There is currently 1 unfiled fax in the system.") :
				sprintf(__("There are currently %d unfiled fax(es) in the system."), $unfiled) )." ".
				"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\">".
				"[".__("File")."]</a>",
				"img/facsimile_icon.png"
			);
		} else {
			// For now, we're just going to return nothing so that
			// the box doesn't show up
			return false;
			return array (
				__("Unfiled Faxes"),
				__("There are no unfiled faxes at this time."),
				"img/facsimile_icon.png"
			);
		}
	} // end method notify

	function menu_notify ( ) {
		// Check to see if we're the person who is supposed to be
		// notified. If not, die out right now.
		$supposed = freemed::config_value('uffax_user');
		if (!(strpos($supposed, ',') === false)) {
			// Handle array
			$found = false;
			foreach (explode(',', $supposed) AS $s) {
				if ($s == $_SESSION['authdata']['user']) { $found = true; }
			}
			if (!$found) { return false; }
		} else {
			if (($supposed > 0) and ($supposed != $_SESSION['authdata']['user'])) {
				return false;
			}
		}
	
		// Decide if we have any "unfiled faxes" in the system
		$query = "SELECT COUNT(*) AS unfiled FROM ".$this->table_name;
		$result = $GLOBALS['sql']->query($query);
		extract($GLOBALS['sql']->fetch_array($result));
		if ($unfiled > 0) {
			return array (
				sprintf(__("You have %d unfiled faxes"), $unfiled),
				"module_loader.php?module=".urlencode(get_class($this))."&action=display"
			);
		} else {
			// For now, we're just going to return nothing so that
			// the box doesn't show up
			return false;
		}
	} // end method menu_notify

} // end class UnfiledFaxes

register_module('UnfiledFaxes');

?>
