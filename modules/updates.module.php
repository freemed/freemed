<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.BaseModule');

class UpdatesModule extends BaseModule {

	var $MODULE_NAME = "Updates";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_DESCRIPTION = "FreeMED Software Foundation RSS feeds for software and security updates.";
	var $MODULE_FILE = __FILE__;
	var $PACKAGE_MINIMUM_VERSION = "0.8.1";

	function UpdatesModule ( ) {
		// __("Updates")

		// Add main menu notification handlers
		$this->_SetHandler('MenuNotifyItems', 'menu_notify');
		$this->_SetHandler('MainMenu', 'notify');
		
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
		$this->BaseModule();
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
		$rss = CreateObject('_FreeMED.MagpieRSS', join("\n", file("data/cache/rss.feed.${feed}")));
		$newest_timestamp = $rss->items[0]['dc']['date'];

		return array (
			__("Updates"),
			sprintf(__("The security feed was last updated on %s."), $newest_timestamp)." ".
			"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\">".
			"[".__("View")."]</a>",
			"img/facsimile_icon.png"
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
				"module_loader.php?module=".urlencode(get_class($this))."&action=display"
			);
		}
		return false;
	} // end method menu_notify

	function main () {
		switch ($_REQUEST['action']) {
			default:
				$this->view();
				break;
		}
	} // end method main

	function view () {
		global $display_buffer;

		$feed = 'Security';
		$rss = CreateObject('_FreeMED.MagpieRSS', join("\n", file("data/cache/rss.feed.${feed}")));
		$display_buffer .= "<div class=\"DataHead\">".$rss->channel['title']."</div>\n";
		foreach ($rss->items AS $item) {
			$display_buffer .= "<h2><a href=\"".$item['link']."\">".$item['title']."</a></h2>\n";
			$display_buffer .= $item['description']."<br/>\n";
		}
		$display_buffer .= "<br/><br/><div align=\"center\"><a href=\"javascript:history.go(-1);\" class=\"button\">".__("Go Back")."</a></div>\n";
	} // end method view

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
	function _cache_feed ( $feed ) {
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
	function _check_cached_copy ( $feed ) {
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
	function _get ( $host, $url, $username, $password ) {
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
