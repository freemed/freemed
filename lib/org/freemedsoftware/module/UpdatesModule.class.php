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

LoadObjectDependency('org.freemedsoftware.core.BaseModule');

class UpdatesModule extends BaseModule {

	var $MODULE_NAME = "Updates";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "FreeMED Software Foundation RSS feeds for software and security updates.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "7835c6ac-115e-44d9-bc1c-7a02d3ca21c1";
	var $PACKAGE_MINIMUM_VERSION = "0.8.1";

	public function __construct ( ) {
		// __("Updates")

		// Add main menu notification handlers
		$this->_SetHandler('MenuNotifyItems', 'menu_notify');
		$this->_SetHandler('MainMenu', 'notify');

		$this->_SetHandler('Utilities', 'menu');
		$this->_SetMetaInformation('UtilityName', __("Updates"));
		$this->_SetMetaInformation('UtilityDescription', __("Update and security feeds menu."));
		
		// Form proper configuration information
		$this->_SetMetaInformation('global_config_vars', array(
			'update_user',
			'update_pass'
		));
		$this->_SetMetaInformation('global_config', array(
			__("Updates Username") =>
			'html_form::text_widget ( "update_user" ) ',
			__("Updates Password") =>
			'html_form::password_widget ( "update_pass" ) ',
			)
		);
		
		// Call parent constructor
		parent::__construct ( );
	} // end constructor UpdatesModule

	function notify ( ) {
		$u = freemed::config_value('update_user');
		$p = freemed::config_value('update_pass');

		// If we don't have a username or password, then die out
		if (!$u or !$p) { return false; }

		// Check to see if the file is older than a certain time
		// and attempt to fetch a newer copy if it is

		$n = $this->_cache_feed('Security');

		// Get date information from the security feed
		$feed = 'Security';
		$rss = CreateObject('org.freemedsoftware.core.MagpieRSS', join("\n", file("data/cache/rss.feed.${feed}")));
		$newest_timestamp = $rss->items[0]['dc']['date'];

		return array (
			__("Updates"),
			sprintf(__("The security feed was last updated on %s."), $newest_timestamp)." ".
			"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=feed\">".
			"[".__("View")."]</a>",
			"img/security_icon.png"
		);
	} // end method notify

	function menu_notify ( ) {
		$u = freemed::config_value('update_user');
		$p = freemed::config_value('update_pass');

		// If we don't have a username or password, then die out
		if (!$u or !$p) { return false; }

		if ($this->_cache_feed('Security')) {
			return array (
				__("There is new information in the security feed"),
				"module_loader.php?module=".urlencode(get_class($this))."&action=feed"
			);
		}
		return false;
	} // end method menu_notify

	// Method: GetFeed
	//
	//	Retrieve update feed
	//
	// Parameters:
	//
	//	$feed - (optional) Feed name. Defaults to 'Security'.
	//
	// Returns:
	//
	//	Hash containing:
	//	* title - Title of feed
	//	* feed - Hash containing feed
	//	  * link
	//	  * title
	//	  * date
	//	  * description
	//
	public function GetFeed ( $feed = 'Security' ) {
		$myfeed = freemed::secure_filename ( $feed );
		
		$rss = CreateObject('org.freemedsoftware.core.MagpieRSS', join("\n", file("data/cache/rss.feed.${myfeed}")));
		$display_buffer .= "<div class=\"DataHead\">".
		$return['title'] = $rss->channel['title'];
		foreach ($rss->items AS $item) {
			$return['feed'][] = array (
				'link' => $item['link'],
				'title' => $item['title'],
				'date' => $item['dc']['date'],
				'description' => $item['description']
			);
		}
		return $return;
	} // end method GetFeed

	//------ Actual functions for news feeds

	// Method: _cache_feed
	//
	// Parameters:
	//
	//	$feed - Feed name
	//
	// Returns:
	//
	//	Boolean, whether or not the user needs to be notified of
	//	a new feed.
	//
	private function _cache_feed ( $feed ) {
		$u = freemed::config_value('update_user');
		$p = freemed::config_value('update_pass');
		$notify = false;
		if (!$this->_check_cached_copy($feed)) {
			// Download feed
			$new_feed = $this->_get('update.freemedsoftware.net', '/feed/?mode=rss&category='.$feed, $u, $p);
			$old_feed = @file_get_contents('data/cache/rss.feed.'.$feed);
			if ($new_feed != $old_feed) { $notify = true; }
			// Write to feed file
			$fp = fopen('data/cache/rss.feed.'.$feed, 'w');
			if (!$fp) { die ("Unable to write to feed!"); }
			fwrite($fp, $new_feed);
			fclose($fp);
		}
		return $notify;
	} // end method _cache_feed

	// Method: _check_cached_copy
	//
	//	Check to see whether or not the cached copy of the provided
	//	feed is okay.
	//
	// Parameters:
	//
	//	$feed - Name of the feed
	//
	// Returns:
	//
	//	Boolean, true if there is a cached copy, false if there is
	//	no cached copy or it is stale.
	//
	private function _check_cached_copy ( $feed ) {
		$hours = 24;
		$cache_file = "data/cache/rss.feed.${feed}";

		if (!file_exists($cache_file)) { return false; }
		$s = stat($cache_file);

		if ( ($s[9]+(3600 * $hours)) >= mktime() ) {
			return true;
		}

		// Fall back to always retrieving
		return false;
	} // end method _check_cached_copy

	// Method: _get
	//
	//	Retrieve URL via raw sockets
	//
	// Parameters:
	//
	//	$host - Hostname
	//
	//	$url - Relative URL to fetch (/something/something)
	//
	//	$username - Username to fetch with
	//
	//	$password - Password to fetch with
	//
	// Returns:
	//
	//	Text of the resultant page, or false if it fails.
	//
	private function _get ( $host, $url, $username, $password ) {
		if (!$fp = fsockopen($host, 80, $errno, $errstr, 15)) {
			return false;
		}

		$auth = 'Basic '.base64_encode($username.':'.$password);
		if (!fputs( $fp, "GET ${url} HTTP/1.0\r\nAuthorization: ${auth}\r\nHost: ${host}\r\n\r\n" )) {
			return false;
		}

		$push = false;
		while (!feof($fp)) {
			$line = fgets($fp, 512); 
			if (strpos($line, '<?xml') !== false) { $push = true; }
			if ($push) { $buffer .= $line; }
		}
		fclose($fp);

		return $buffer;
	} // end method _get

} // end class UpdatesModule

register_module('UpdatesModule');

?>
