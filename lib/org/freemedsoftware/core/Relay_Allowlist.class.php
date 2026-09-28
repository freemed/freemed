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

// Class: org.freemedsoftware.core.Relay_Allowlist
//
//	Deny-by-default call-set check for the relay (Task 2.8).
//
//	lib/loader.php's CallMethod() instantiates ANY registered class and calls ANY
//	public method, with arguments taken from the request. A quoting omission
//	anywhere therefore used to be remotely reachable through relay.php the moment
//	the caller had a session. This class is the control that stops the *next*
//	omission from being reachable: the relay consults it before dispatch and a
//	method that is not named here is refused.
//
//	The pattern list is NOT in this file. It lives in the data file
//	data/config/relay-allowlist.php, so a deployment can extend its call set
//	without patching code (see that file, and doc/RELAY_ALLOWLIST).
//
//	Matching rules:
//	*	A pattern is either an exact relay method string
//		`org.freemedsoftware.<namespace>.<Class>.<Method>` or the same with a
//		trailing `*`, which matches any method name with that prefix.
//	*	Matching is case-insensitive. That is deliberate and it does not widen
//		what is reachable: PHP dispatches method names case-insensitively, so
//		`org.freemedsoftware.core.User.GetName` and `.getName` are the same call
//		(CallMethod resolves the method name with ResolveMethodName() and hands it
//		to call_user_func_array()). The namespace/class segments, by contrast, are
//		resolved case-sensitively against the filesystem by
//		ResolveObjectPath(), so a differently-cased namespace still fails there and
//		a case-insensitive match can only avoid refusing a call the server would
//		have served.
//	*	Matching is literal, never a regex: the dots in a pattern are dots.
//	*	A pattern that is broader than `org.freemedsoftware.<ns>.<Class>` (a bare
//		`*`, or `org.freemedsoftware.*`) expresses no call set — it defeats the
//		control — so it is REJECTED at load, with a LOG_WARNING, and is not
//		honoured. Rejected patterns are visible to callers via rejected_patterns()
//		and are pinned by tests/security/relay_allowlist.test.php.
//
//	What it is not: an authorization check. It answers "is this method part of
//	the relay's call set", not "may this user call it" — per-method ACLs remain
//	each module's job.

class Relay_Allowlist {

	// Constant: CONFIG_FILE
	//
	//	The data file, relative to the installation root.
	const CONFIG_FILE = 'data/config/relay-allowlist.php';

	// Member: $config
	//	The loaded data file, or NULL until first use. Never written to by a
	//	request: the only sources are the data file and an explicit $config
	//	argument, so nothing that arrives over the wire can change the pattern
	//	list.
	private static $config = NULL;

	// Member: $rejected
	//	Patterns refused at load as too broad (see the class comment).
	private static $rejected = array();

	// Method: config_file
	//
	//	Absolute path of the data file. The installation root is
	//	PHYSICAL_LOCATION when the framework has been loaded (lib/freemed.php
	//	defines it) and is derived from this file's own location otherwise, so the
	//	class also works in a hermetic test process with no framework.
	//
	// Returns:
	//
	//	String.
	public static function config_file ( ) {
		if ( defined ( 'PHYSICAL_LOCATION' ) ) {
			return PHYSICAL_LOCATION . '/' . self::CONFIG_FILE;
		}
		// lib/org/freemedsoftware/core/Relay_Allowlist.class.php -> installation root
		$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		return $root . '/' . self::CONFIG_FILE;
	} // end method config_file

	// Method: config
	//
	//	The effective configuration: array ( 'enforce' => bool, 'patterns' =>
	//	array ).
	//
	// Parameters:
	//
	//	$config - (optional) A configuration array to use instead of the data
	//		file. This is how the test suite exercises both enforcement states in
	//		one process; the relay itself never passes it.
	//
	// Returns:
	//
	//	Array.
	public static function config ( $config = NULL ) {
		if ( $config !== NULL ) { return self::normalize( $config ); }
		if ( self::$config === NULL ) { self::$config = self::load( ); }
		return self::$config;
	} // end method config

	// Method: enforce
	//
	//	Whether a pattern miss is REFUSED. FALSE (the shipped default) means
	//	log-only: the miss is logged at LOG_WARNING and the call still executes.
	//	See data/config/relay-allowlist.php for the enable procedure.
	//
	// Parameters:
	//
	//	$config - (optional) As config().
	//
	// Returns:
	//
	//	Boolean.
	public static function enforce ( $config = NULL ) {
		$c = self::config( $config );
		return (bool) $c['enforce'];
	} // end method enforce

	// Method: allowed
	//
	//	Is $method part of the relay call set?
	//
	// Parameters:
	//
	//	$method - The relay method string, as it arrived.
	//
	//	$config - (optional) As config().
	//
	// Returns:
	//
	//	Boolean. FALSE for NULL, for an empty string and for a non-string (an
	//	attacker may send an array as `method`; that must never match).
	public static function allowed ( $method, $config = NULL ) {
		if ( ! is_string ( $method ) or $method === '' ) { return false; }
		$c = self::config( $config );
		foreach ( $c['patterns'] as $pattern ) {
			if ( self::pattern_matches( $pattern, $method ) ) { return true; }
		}
		return false;
	} // end method allowed

	// Method: rejected_patterns
	//
	//	Patterns that the last normalize() refused as too broad.
	//
	// Returns:
	//
	//	Array of strings.
	public static function rejected_patterns ( ) {
		return self::$rejected;
	} // end method rejected_patterns

	// Method: pattern_matches
	//
	//	Literal (non-regex), case-insensitive match of one pattern against a
	//	method string.
	//
	// Parameters:
	//
	//	$pattern - Exact method string, or an exact prefix ending in `*`.
	//
	//	$method - The relay method string.
	//
	// Returns:
	//
	//	Boolean.
	public static function pattern_matches ( $pattern, $method ) {
		if ( ! is_string ( $pattern ) or $pattern === '' ) { return false; }
		if ( ! is_string ( $method ) or $method === '' ) { return false; }
		if ( substr ( $pattern, -1 ) === '*' ) {
			$prefix = substr ( $pattern, 0, -1 );
			if ( $prefix === '' ) { return false; }
			return strncasecmp ( $method, $prefix, strlen ( $prefix ) ) === 0;
		}
		return strcasecmp ( $pattern, $method ) === 0;
	} // end method pattern_matches

	// Method: is_too_broad
	//
	//	A pattern expresses a call set only if it names at least
	//	`org.freemedsoftware.<namespace>.<Class>` (optionally with a trailing `*`).
	//	Anything broader — `*`, `org.freemedsoftware.*`, `org.freemedsoftware`,
	//	`org.freemedsoftware.api` — is not a call set, it is the control switched
	//	off in a way a reader would not notice. A `*` anywhere but the end is
	//	rejected too: it is a wildcard the matcher does not implement, so
	//	honouring the pattern literally would leave an operator believing a rule
	//	was in force when it matches nothing.
	//
	// Parameters:
	//
	//	$pattern - Candidate pattern.
	//
	// Returns:
	//
	//	Boolean.
	public static function is_too_broad ( $pattern ) {
		if ( ! is_string ( $pattern ) ) { return true; }
		$p = $pattern;
		if ( substr ( $p, -1 ) === '*' ) { $p = substr ( $p, 0, -1 ); }
		if ( $p === '' ) { return true; }
		if ( ! preg_match ( '/^[A-Za-z0-9_.]+$/', $p ) ) { return true; }
		// org.freemedsoftware.<ns>.<Class> or deeper: at least three dots in the
		// prefix once a trailing `*` is removed.
		if ( substr_count ( $p, '.' ) < 3 ) { return true; }
		return false;
	} // end method is_too_broad

	// Method: normalize
	//
	//	Validate a raw configuration array. Too-broad patterns are dropped with a
	//	LOG_WARNING and recorded in rejected_patterns(); everything else is kept
	//	verbatim (the list is data, not a place to silently repair typos).
	//
	// Parameters:
	//
	//	$raw - Configuration array as returned by the data file.
	//
	// Returns:
	//
	//	Array ( 'enforce' => bool, 'patterns' => array ).
	private static function normalize ( $raw ) {
		self::$rejected = array();
		$out = array( 'enforce' => false, 'patterns' => array() );
		if ( ! is_array ( $raw ) ) {
			syslog( LOG_WARNING, 'Relay allowlist: configuration is not an array; treating it as EMPTY (log-only, nothing allowed)' );
			return $out;
		}
		$out['enforce'] = isset ( $raw['enforce'] ) ? (bool) $raw['enforce'] : false;
		$patterns = isset ( $raw['patterns'] ) && is_array ( $raw['patterns'] ) ? $raw['patterns'] : array();
		foreach ( $patterns as $pattern ) {
			if ( ! is_string ( $pattern ) or trim ( $pattern ) === '' ) {
				syslog( LOG_WARNING, 'Relay allowlist: ignoring a non-string/empty pattern entry' );
				continue;
			}
			$pattern = trim ( $pattern );
			if ( self::is_too_broad ( $pattern ) ) {
				self::$rejected[] = $pattern;
				syslog( LOG_WARNING, "Relay allowlist: pattern '{$pattern}' is too broad to express a call set; it was REJECTED and is not honoured" );
				continue;
			}
			$out['patterns'][] = $pattern;
		}
		return $out;
	} // end method normalize

	// Method: load
	//
	//	Read the data file. A missing or unreadable file FAILS OPEN and LOUDLY:
	//	enforcement is off, the relay behaves exactly as it did before this
	//	control existed, and the operator gets a LOG_WARNING. That is the
	//	outage-safe direction for a control whose shipped default is log-only —
	//	but it is also why the file must stay in version control once a site sets
	//	enforce = true.
	//
	// Returns:
	//
	//	Array ( 'enforce' => bool, 'patterns' => array ).
	private static function load ( ) {
		$file = self::config_file( );
		if ( ! is_file ( $file ) ) {
			syslog( LOG_WARNING, 'Relay allowlist: data file ' . $file . ' is missing; the relay allowlist is NOT enforcing (log-only)' );
			return array( 'enforce' => false, 'patterns' => array() );
		}
		$c = @include $file;
		if ( ! is_array ( $c ) ) {
			syslog( LOG_WARNING, 'Relay allowlist: data file ' . $file . ' did not return an array; the relay allowlist is NOT enforcing (log-only)' );
			return array( 'enforce' => false, 'patterns' => array() );
		}
		return self::normalize( $c );
	} // end method load

} // end class Relay_Allowlist

?>
