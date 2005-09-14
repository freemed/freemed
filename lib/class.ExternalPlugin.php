<?php
	// $Id$
	// $Author$

// Class: FreeMED.ExternalPlugin
//
//	Allows for CLI plugins to extend FreeMED functionality
//
class ExternalPlugin {

	// Variable: $path
	//
	//	Holds the location of the plugins to be "scanned".
	//	Assigned by the <ExternalPlugin> constructor.
	//
	var $path;

	// Variable: $cache
	//
	//	Fully qualified path to plugin cache. Assigned by the
	//	<ExternalPlugin> constructor.
	//
	var $cache;

	// Variable: $plugin_cache
	//
	//	Internal storage of plugins' metainformation.
	//
	var $plugin_cache;

	// Constructor: ExternalPlugin
	function ExternalPlugin ( $path ) {
		$this->path = dirname(dirname(__FILE__)).'/'.$path;
		$this->cache = dirname(dirname(__FILE__)).'/data/cache/'.strtolower(get_class($this));
	} // end constructor ExternalPlugin

	// Method: GetCatalog
	//
	//	Get associative array of available plugins with metainformation
	//
	function GetCatalog ( ) {
		// Make sure we have the appropriate information
		$this->Cache();

		// Return plugin cached version
		return $this->plugin_cache;
	} // end method Catalog

	// Method: GetPicklist
	//
	//	Produce a picklist for a select widget of the available plugins,
	//	derived from the cache.
	//
	// Parameters:
	//
	//	$format - Format of the output picklist, using ##'s to surround the
	//	variables. Defaults to '##NAME##'.
	//
	// Returns:
	//
	//	Associative array
	//
	function GetPicklist ( $format = '##NAME##' ) {
		// Get information from cache first
		$this->Cache();

		foreach ($this->plugin_cache AS $k => $v) {
			$key = '';
			$h = explode('##', $format);
			foreach ($h AS $hk => $hv) {
				if ($hk & 1) {
					$key .= $v[$hv];
				} else {
					$key .= $hv;
				}
			}
			$p[$key] = $v['UUID'];
		}

		return $p;
	} // end method GetPicklist

	// Method: UUIDToPlugin
	//
	//	Resolve plugin from UUID
	//
	// Parameters:
	//
	//	$uuid - Unique ID number from plugin
	//
	// Returns:
	//
	//	Filename of plugin, or boolean false if the plugin is not resolved.
	//
	function UUIDToPlugin ( $uuid ) {
		// Get cache information
		$this->Cache();

		foreach ($this->plugin_cache AS $k => $v) {
			if ($v['UUID'] == $uuid) { return $k; }
		}

		// If this does not resolve, return false
		return false;
	} // end method UUIDToPlugin

	// Method: Cache
	//
	//	Perform plugin caching operation if necessary.
	//
	// SeeAlso:
	//	<IsCached>
	//
	function Cache ( ) {
		if (is_array($this->plugin_cache)) { return true; }
		if (!$this->IsCached()) {
			if ($this->debug) { print "Caching\n"; }
			if (!($d = dir($this->path))) {
				die("ExternalPlugin: failed to open ".$this->path);
			}
			// Recurse and read in entries
			while ($e = $d->read()) {
				if (is_file($this->path.'/'.$e) and (substr($e, 0, 1) != '.')) {
					if ($this->debug) { print "\tcaching $e\n"; }
					$o = $this->Command($e, 'INFO');
					if ($o) {
						$__cache[$e] = $this->ParseInfo($o);
					}
				}
			}
			// Write cache
			$h = fopen($this->cache, 'w');
			if (!$h) { die("ExternalPlugin: could not write to cache file ".$this->cache); }
			fwrite($h, serialize($__cache));
			fclose($h);

			// Retain cache data
			$this->plugin_cache = $__cache;
		} else {
			// Read the cache file back into memory
			ob_start();
			readfile($this->cache);
			$__cache = ob_get_contents();
			ob_end_clean();
			$this->plugin_cache = unserialize($__cache);
		}
	} // end method Cache

	// Method: IsCached
	//
	//	Determine if plugin cache is up-to-date
	//
	// SeeAlso:
	//	<Cache>
	//
	function IsCached ( ) {
		clearstatcache();
		$d_lstat = lstat($this->path);
		$d = $d_lstat[9];

		$f_lstat = lstat($this->cache);
		$f = $f_lstat[9];

		if ($f < $d) {
			return false;
		} else {
			return true;
		}
	} // end method IsCached

	// Method: Command
	//
	//	Pass an arbitrary command to a plugin and return its
	//	results.
	//
	// Parameters:
	//
	//	$plugin - Name of plugin file (non-qualified)
	//
	//	$command - Command string to be passed to the plugin
	//
	// Returns:
	//
	//	Full text output of the plugin
	//
	function Command ( $plugin, $command ) {
		$output = shell_exec($this->path.'/'.$plugin.' '.$command);
		return $output;
	} // end method Command

	// Method: ParseInfo
	//
	//	Parse the results of a module INFO query.
	//
	function ParseInfo ( $info ) {
		$lines = explode("\n", $info);
		foreach ($lines AS $line) {
			if (!(strpos($line, ':') === false)) {
				$k = trim(substr($line, 0, strpos($line, ':')));
				$v = trim(substr($line, -(strlen($line) - strlen($k) - 1)));
				$parsed[strtoupper($k)] = $v;
			}
		}
		return $parsed;
	} // end method ParseInfo

} // end class ExternalPlugin

?>
