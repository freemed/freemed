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
//	ONE DECISION, ONE LOG LINE: refuse() is the single entry point for the
//	decision, and it is what Relay::handle_request() calls for the OUTER method
//	string. It is also what every relay-reachable re-dispatcher calls before an
//	INNER dispatch it performs itself -- api/UserInterface.class.php's
//	Multicall() is the one that exists in this tree. A check that ran only in
//	Relay::handle_request() ran once, on the outer method string, so an
//	allowlisted re-dispatcher could reach the whole tree around it.
//
//	Matching rules:
//	*	A pattern is either an exact relay method string
//		`org.freemedsoftware.<namespace>.<Class>.<Method>`, or a prefix ending
//		in `*` which matches any method string with that prefix.
//	*	A trailing `*` can cut a pattern at three shapes, and the widest of them
//		is the namespace-level wildcard:
//			org.freemedsoftware.module.SomeModule.*       a CLASS
//				- every method of that one class
//			org.freemedsoftware.module.SomeModule.Get*    a PREFIX
//				- every method name beginning `Get`
//			org.freemedsoftware.module.*                  a NAMESPACE
//				- EVERY method of EVERY class under that namespace
//		A BARE NAMESPACE-LEVEL WILDCARD IS ACCEPTED -- it is narrower than a
//		whole-tree rule and the brief names `org.freemedsoftware.module.*` as
//		its example of a `*` pattern -- but it is a real widening and it is
//		NEVER ACCEPTED IN SILENCE: normalize() logs it at LOG_WARNING, naming
//		the namespace and how many method declarations it covers, because an
//		accepted wildcard with no log line is indistinguishable from the
//		control being switched off. The SHIPPED seed uses no `*` at all.
//	*	A pattern broader still -- a bare `*`, `org.freemedsoftware.*`,
//		`org.freemedsoftware`, `org.freemedsoftware.api` -- expresses no call
//		set: it is the control switched off in a way a reader would not notice.
//		It is REJECTED at load, with a LOG_WARNING, and is not honoured.
//		Rejected patterns are visible to callers via rejected_patterns() and are
//		pinned by tests/security/relay_allowlist.test.php.
//	*	An EXACT pattern must have exactly four dots (the relay method string
//		above), and a trailing `*` may cut at four (a Class- or method-name
//		prefix) or at three (the namespace-level form). A deeper spelling names
//		no relay method and a shallower one is the control off; both are
//		rejected rather than left silently inert (see is_too_broad()).
//	*	A `*` pattern matches a NON-EMPTY tail: `X.*` matches `X.something`,
//		never the bare prefix `X.`, which names no method and cannot dispatch.
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
//
//	A MISSING OR CORRUPT DATA FILE FAILS OPEN AND LOUDLY: enforcement is off,
//	the relay behaves exactly as it did before this control existed, and the
//	operator gets a loud log line. That covers an unreadable file, a file that
//	does not return an array, AND a file that cannot be parsed at all (a typo
//	while hand-editing `patterns` -- the editing the operator notes instruct --
//	raises a ParseError which `@include` does not suppress and which would
//	otherwise take the relay down). See load().
//
//	What it is not: an authorization check. It answers "is this method part of
//	the relay's call set", not "may this user call it" -- per-method ACLs remain
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

	// Member: $warnings
	//	Messages the last load()/normalize() emitted to syslog. Kept so the
	//	hermetic test can assert on a warning's TEXT (syslog() is not capturable
	//	from a CLI process with no syslogd) instead of on an unobservable side
	//	effect.
	private static $warnings = array();

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
			if ( self::pattern_matches ( $pattern, $method ) ) { return true; }
		}
		return false;
	} // end method allowed

	// Method: refuse
	//
	//	The relay's deny-by-default decision for ONE method string, and the only
	//	place the miss log line is written.
	//
	//	Relay::handle_request() calls this for the OUTER method string, and every
	//	relay-reachable re-dispatcher calls it for each INNER method it is about
	//	to dispatch itself (api/UserInterface.class.php:Multicall()). Sharing the
	//	entry point is the point: an inner call then gets the same allowlist, the
	//	same STAGE behaviour and the same log line as an outer one, so the
	//	log-only stage measures the inner call set too and enforcement closes it.
	//
	// Parameters:
	//
	//	$method - The relay method string, as it arrived.
	//
	//	$config - (optional) As config().
	//
	// Returns:
	//
	//	Boolean. TRUE means the caller must REFUSE the call and answer
	//	INVALID_CALL. FALSE means the call may proceed: either it is listed, or
	//	it is a miss and the site is in the shipped log-only stage (in which case
	//	the miss has just been logged, which is how an operator discovers a
	//	pattern that is needed).
	public static function refuse ( $method, $config = NULL ) {
		if ( self::allowed ( $method, $config ) ) { return false; }
		self::log_miss ( $method, $config );
		return self::enforce ( $config );
	} // end method refuse

	// Method: log_miss
	//
	//	Record a miss. The format lives here rather than at each call site so the
	//	outer relay check and an inner re-dispatched call are indistinguishable to
	//	the operator's `grep "Relay: method"` (doc/RELAY_ALLOWLIST step 3): a
	//	miss that is not greppable is a miss the operator never adds.
	//
	//	The identity the line carries is the method, the remote address and the
	//	stage -- not a user or a site. On a reverse-proxied or multi-tenant host
	//	that is a limit worth knowing; see doc/RELAY_ALLOWLIST.
	//
	// Parameters:
	//
	//	$method - The relay method string, as it arrived.
	//
	//	$config - (optional) As config().
	private static function log_miss ( $method, $config = NULL ) {
		$enforce = self::enforce( $config );
		$m = is_string($method) ? $method : '(non-string)';
		$remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-';
		$stage  = $enforce ? 'refused (INVALID_CALL)' : 'LOG-ONLY, call still executed';
		syslog( LOG_WARNING, "Relay: method '{$m}' is not in the relay allowlist (remote={$remote}, {$stage})" );
	} // end method log_miss

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

	// Method: warnings
	//
	//	The messages the last load()/normalize() emitted. Exposed so a test can
	//	assert on a warning's text; the relay never reads it.
	//
	// Returns:
	//
	//	Array of strings.
	public static function warnings ( ) {
		return self::$warnings;
	} // end method warnings

	// Method: pattern_matches
	//
	//	Literal (non-regex), case-insensitive match of one pattern against a
	//	method string.
	//
	// Parameters:
	//
	//	$pattern - Exact method string, or a prefix ending in `*`.
	//
	//	$method - The relay method string.
	//
	// Returns:
	//
	//	Boolean. A `*` pattern requires a NON-EMPTY tail: `X.*` matches
	//	`X.something`, never the bare prefix `X.`.
	public static function pattern_matches ( $pattern, $method ) {
		if ( ! is_string ( $pattern ) or $pattern === '' ) { return false; }
		if ( ! is_string ( $method ) or $method === '' ) { return false; }
		if ( substr ( $pattern, -1 ) === '*' ) {
			$prefix = substr ( $pattern, 0, -1 );
			if ( $prefix === '' ) { return false; }
			// The tail must be non-empty. `X.*` means "any method under X", and
			// `X.` itself names no method, so matching it would hand an operator
			// an unpredictable matcher edge for no reachable call.
			if ( strlen ( $method ) <= strlen ( $prefix ) ) { return false; }
			return strncasecmp ( $method, $prefix, strlen ( $prefix ) ) === 0;
		}
		return strcasecmp ( $pattern, $method ) === 0;
	} // end method pattern_matches

	// Method: is_namespace_wildcard
	//
	//	Is $pattern a trailing-`*` pattern that ends at the NAMESPACE, i.e.
	//	`org.freemedsoftware.<ns>.*`? That is the widest shape this control
	//	accepts, and the one whose acceptance normalize() reports.
	//
	// Parameters:
	//
	//	$pattern - Candidate pattern.
	//
	// Returns:
	//
	//	Boolean.
	public static function is_namespace_wildcard ( $pattern ) {
		if ( ! is_string ( $pattern ) or substr ( $pattern, -1 ) !== '*' ) { return false; }
		return substr_count ( $pattern, '.' ) === 3;
	} // end method is_namespace_wildcard

	// Method: namespace_method_count
	//
	//	How many method declarations sit under a namespace, as a bounded source
	//	scan of lib/<namespace>/*.class.php. This is the number the load-time
	//	wildcard warning names so an operator can see the size of what they just
	//	switched on. It is a LOWER BOUND: it counts declarations in the files
	//	present, not inherited methods, and it deliberately does not include or
	//	reflect-load the classes (the relay must not load a namespace to count
	//	it).
	//
	// Parameters:
	//
	//	$namespace - e.g. 'org.freemedsoftware.api' (no trailing dot).
	//
	// Returns:
	//
	//	Integer.
	private static function namespace_method_count ( $namespace ) {
		if ( defined ( 'PHYSICAL_LOCATION' ) ) {
			$root = PHYSICAL_LOCATION;
		} else {
			$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		}
		$dir = $root . '/lib/' . str_replace ( '.', '/', $namespace );
		if ( ! is_dir ( $dir ) ) { return 0; }
		$files = glob ( $dir . '/*.class.php' );
		if ( ! is_array ( $files ) ) { return 0; }
		$count = 0;
		foreach ( $files as $f ) {
			$src = @file_get_contents ( $f );
			if ( $src === false ) { continue; }
			$count += (int) preg_match_all ( '/^\s*(?:public\s+)?function\s+[A-Za-z_]/m', $src );
		}
		return $count;
	} // end method namespace_method_count

	// Method: is_too_broad
	//
	//	Does $pattern fail to express a relay call set?
	//
	//	A relay method string is exactly
	//	`org.freemedsoftware.<namespace>.<Class>.<Method>` -- four dots -- so:
	//	*	a pattern with NO trailing `*` must have exactly four dots;
	//	*	a pattern WITH a trailing `*` may cut at four (a Class- or
	//		method-name prefix, e.g. `...UserInterface.*` or `...UserInterface.Get*`)
	//		or at three (the namespace-level form `org.freemedsoftware.<ns>.*`,
	//		which is ACCEPTED -- see the class comment -- and reported by
	//		normalize()).
	//	Fewer dots than that is the control switched off (`*`,
	//	`org.freemedsoftware.*`, `org.freemedsoftware`, `org.freemedsoftware.api`);
	//	more dots names no relay method at all, so the pattern would match
	//	nothing. Both are rejected here rather than left silently inert.
	//	A `*` anywhere but the end is rejected too: it is a wildcard the matcher
	//	does not implement, so honouring the pattern literally would leave an
	//	operator believing a rule was in force when it matches nothing.
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
		$wildcard = false;
		if ( substr ( $p, -1 ) === '*' ) { $wildcard = true; $p = substr ( $p, 0, -1 ); }
		if ( $p === '' ) { return true; }
		if ( ! preg_match ( '/^[A-Za-z0-9_.]+$/', $p ) ) { return true; }
		$dots = substr_count ( $p, '.' );
		if ( $dots < 3 ) { return true; }
		if ( $wildcard ) { return ( $dots > 4 ); }
		return ( $dots !== 4 );
	} // end method is_too_broad

	// Method: warn
	//
	//	Record a diagnostic message and put it in syslog. Recording as well as
	//	logging is what makes the load-time warnings testable without a syslog
	//	sink.
	//
	// Parameters:
	//
	//	$message - Free text.
	//
	//	$priority - (optional) A syslog priority. Defaults to LOG_WARNING.
	private static function warn ( $message, $priority = LOG_WARNING ) {
		self::$warnings[] = $message;
		syslog( $priority, $message );
	} // end method warn

	// Method: normalize
	//
	//	Validate a raw configuration array. Too-broad patterns are dropped with a
	//	LOG_WARNING and recorded in rejected_patterns(); an accepted
	//	namespace-level wildcard is reported PROMINENTLY (see the class comment);
	//	everything else is kept verbatim (the list is data, not a place to
	//	silently repair typos).
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
		self::$warnings = array();
		$out = array( 'enforce' => false, 'patterns' => array() );
		if ( ! is_array ( $raw ) ) {
			self::warn( 'Relay allowlist: configuration is not an array; treating it as EMPTY (log-only, nothing allowed)' );
			return $out;
		}
		$out['enforce'] = isset ( $raw['enforce'] ) ? (bool) $raw['enforce'] : false;
		$patterns = isset ( $raw['patterns'] ) && is_array ( $raw['patterns'] ) ? $raw['patterns'] : array();
		foreach ( $patterns as $pattern ) {
			if ( ! is_string ( $pattern ) or trim ( $pattern ) === '' ) {
				self::warn( 'Relay allowlist: ignoring a non-string/empty pattern entry' );
				continue;
			}
			$pattern = trim ( $pattern );
			if ( self::is_too_broad ( $pattern ) ) {
				self::$rejected[] = $pattern;
				self::warn( "Relay allowlist: pattern '{$pattern}' is too broad to express a call set; it was REJECTED and is not honoured" );
				continue;
			}
			if ( self::is_namespace_wildcard ( $pattern ) ) {
				$namespace = substr ( $pattern, 0, -2 );
				$covered = self::namespace_method_count ( $namespace );
				self::warn( "Relay allowlist: pattern '{$pattern}' is a NAMESPACE-LEVEL WILDCARD and grants EVERY method of every class under '{$namespace}' (at least {$covered} method declarations in the class files there); it is HONOURED" );
			}
			$out['patterns'][] = $pattern;
		}
		return $out;
	} // end method normalize

	// Method: load
	//
	//	Read the data file. A MISSING, UNREADABLE, NON-ARRAY or UNPARSEABLE file
	//	FAILS OPEN and LOUDLY: enforcement is off, the relay behaves exactly as
	//	it did before this control existed, and the operator gets a log line.
	//
	//	The unparseable case is not a theoretical one: the operator notes instruct
	//	hand-editing `patterns`, and a typo there (a trailing comma, an unclosed
	//	bracket) raises a ParseError. `@include` suppresses warnings, not Errors,
	//	so without the catch below such a typo takes the relay down -- the exact
	//	outcome this log-only-by-default design exists to prevent. It is caught
	//	and treated as "the list is unavailable", at LOG_ERR because it is a
	//	deployment mistake rather than an expected state.
	//
	//	That is the outage-safe direction for a control whose shipped default is
	//	log-only -- but it is also why the file must stay in version control once
	//	a site sets enforce = true.
	//
	// Parameters:
	//
	//	$file - (optional) Path to read instead of the installation's data file.
	//		The test suite uses this to exercise the fail-open branches; the relay
	//		never passes it.
	//
	// Returns:
	//
	//	Array ( 'enforce' => bool, 'patterns' => array ).
	public static function load ( $file = NULL ) {
		if ( $file === NULL ) { $file = self::config_file( ); }
		self::$warnings = array();
		if ( ! is_file ( $file ) ) {
			self::warn( 'Relay allowlist: data file ' . $file . ' is missing; the relay allowlist is NOT enforcing (log-only)' );
			return array( 'enforce' => false, 'patterns' => array() );
		}
		try {
			$c = @include $file;
		} catch ( \Throwable $e ) {
			self::warn( 'Relay allowlist: data file ' . $file . ' is CORRUPT (' . $e->getMessage() . '); the relay allowlist is NOT enforcing (log-only); repair the file' , LOG_ERR );
			return array( 'enforce' => false, 'patterns' => array() );
		}
		if ( ! is_array ( $c ) ) {
			self::warn( 'Relay allowlist: data file ' . $file . ' did not return an array; the relay allowlist is NOT enforcing (log-only)' );
			return array( 'enforce' => false, 'patterns' => array() );
		}
		return self::normalize( $c );
	} // end method load

} // end class Relay_Allowlist

?>
