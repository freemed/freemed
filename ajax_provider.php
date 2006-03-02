<?php
	// $Id$
	// $Author$

include_once ( 'lib/freemed.php' );

$ajax = CreateObject( 'PHP.Sajax', 'ajax_provider.php' );
$ajax->export ( 'lookup', 'module_html', 'module_recent', 'patient_lookup' );
$ajax->handle_client_request();

//----- Function library

function lookup ( $module, $parameter, $field = 'id', $patient = NULL ) {
	$_cache = freemed::module_cache();
	include_once(resolve_module($module));
	if (!resolve_module($module)) { return false; }
	$m = new $module ();
	if (method_exists($m, 'ajax_lookup')) {
		return $m->ajax_lookup($parameter, $field, $patient);
	}
	$table = $m->table_name;
	$hash = $m->widget_hash;
	$limit = 10;		// logical limit to how many we can display
	
	// Extract keys
	$fields = _extract_keys ( $hash );
	foreach ($fields AS $f) {
		$q[] = $f.' LIKE \'%'.addslashes($parameter).'%\'';
	}
	
	$query = "SELECT * FROM ".$table." WHERE ( ".join(' OR ', $q)." ) ".
		($patient ? "AND ".$pfield."='".addslashes($patient)."'" : '' );
	$res = $GLOBALS['sql']->query($query);
	if (!$GLOBALS['sql']->results($res)) { return false; }
	$count = 0;
	while ($r = $GLOBALS['sql']->fetch_array( $res ) ) {
		$count++;
		if ($count < $limit) {
			$_res = trim(_result_to_hash($r, $hash));
			$_res = addslashes($_res);
			$return[] = $_res.'@'.$r[$field];
		}
	}
	if ($count >= $limit) { $return[] = " ... "; }
	return join('|', $return);
} // end function lookup

function module_html ( $module, $method, $parameter = NULL ) {
	$c = freemed::module_cache();
	include_once(resolve_module($module));
	if (!resolve_module($module)) { return false; }
	$m = new $module ();
	return $m->$method($parameter);	
} // end function module_html

function module_recent ( $module, $patient, $recent_date = NULL ) {
	$c = freemed::module_cache();
	include_once(resolve_module($module));
	if (!resolve_module($module)) { return false; }
	$m = new $module ();
	return $m->recent_text($patient, $recent_date);
} // end function module_recent

function patient_lookup ( $criteria ) {
	$limit = 10;

	// Correction for patients with apostrophes in their names
	$criteria = addslashes($criteria);

	// Form query with "smart search"
	if (!(strpos($criteria, ',') === false)) {
		// last, first
		list ($last, $first) = explode (',', $criteria);
	} else {
		// Determine if there's a space
		if (!(strpos($criteria, ' ') === false)) {
			// Break into first last
			list ($first, $last) = explode (' ', $criteria);
		} else {
			// Glob search
			$either = $criteria;	
		}
	}
	$last = trim($last);
	$first = trim($first);
	$either = trim($either);

	// If first and last, then F AND L else, F OR L
	if ($first AND $last) {
		// And
		$q[] = "( ptlname LIKE '".addslashes($last)."%' AND ".
			" ptfname LIKE '".addslashes($first)."%' )";
	} elseif ($first) {
		$q[] = "ptfname LIKE '".addslashes($first)."%'";
	} elseif ($last) {
		$q[] = "ptfname LIKE '".addslashes($last)."%'";
	} else {
		// Either
		$q[] = "ptfname LIKE '".addslashes($either)."%'";
		$q[] = "ptlname LIKE '".addslashes($either)."%'";
		$q[] = "ptid LIKE '".addslashes($either)."%'";
	}

	$query = "SELECT * FROM patient WHERE ( ".join(' OR ', $q)." ) ".
		"AND ( ISNULL(ptarchive) OR ptarchive=0 )";
	$res = $GLOBALS['sql']->query($query);
	if (!$GLOBALS['sql']->results($res)) { return false; }
	$count = 0;
	while ($r = $GLOBALS['sql']->fetch_array( $res ) ) {
		$count++;
		if ($count < $limit) {
			$_obj = CreateObject('_FreeMED.Patient', $r);
			$return[] = trim(stripslashes($_obj->to_text())).'@'.$r['id'];
		}
	}
	if ($count >= $limit) { $return[] = " ... "; }
	return join('|', $return);
} // end function patient_lookup

function csz_lookup ( $parameter ) {
	$limit = 10;		// logical limit to how many we can display

	// Parameter extraction
	$params = explode(' ', $parameter);
	
	// Extract keys
	foreach ($params AS $p) {
		if ($p+0 > 0) {
			// Numeric only is zipcode
			$q[] = "zip LIKE '".addslashes($p)."%'";
		} elseif (strlen($p) > 2) {
			// City only
			$q[] = "city LIKE '".addslashes($p)."%'";
		} else {
			// City + state
			$q[] = "( city LIKE '".addslashes($p)."%' OR state LIKE '".addslashes($p)."%' ) ";
		}
	}
	
	$query = "SELECT * FROM zipcodes WHERE ( ".join(' AND ', $q)." ) ";
	$res = $GLOBALS['sql']->query($query);
	if (!$GLOBALS['sql']->results($res)) { return false; }
	$count = 0;
	while ($r = $GLOBALS['sql']->fetch_array( $res ) ) {
		$count++;
		if ($count < $limit) {
			$return[] = $r['city'].','.$r['state'].','.$r['zip'];
		}
	}
	if ($count >= $limit) { $return[] = " ... "; }
	return join('|', $return);
} // end function csz_lookup

//----- Support functions

function _extract_keys ( $hash ) {
	$h = explode('##', $hash);
	if (count($h) == 1) { return $h; }
	foreach ($h AS $k => $v) {
		if ($k & 1) {
			$keys[] = $v;
		}
	}
	return $keys;
} // end function _extract_keys

function _result_to_hash ( $r, $hash ) {
	$h = explode('##', $hash);
	if (count($h) == 1) { return $h; }
	foreach ($h AS $k => $v) {
		if (!($k & 1)) {
			$return .= prepare($v);
		} else {
			$return .= prepare($r[$v]);
		}
	}
	return $return;
} // end function _result_to_hash

?>
