<?php
	// $Id$
	// $Author$

include_once ( 'lib/freemed.php' );

$ajax = CreateObject( 'PHP.Sajax', 'ajax_provider.php' );
$ajax->export ( 'lookup', 'patient_lookup' );
$ajax->handle_client_request();

//----- Function library

function lookup ( $module, $parameter, $patient = NULL ) {
	$_cache = freemed::module_cache();
	include_once(resolve_module($module));
	if (!resolve_module($module)) { return false; }
	$m = new $module ();
	$table = $m->table_name;
	$hash = $m->widget_hash;
	$limit = 10;		// logical limit to how many we can display
	
	// Extract keys
	$fields = _extract_keys ( $hash );
	foreach ($fields AS $field) {
		$q[] = $field.' LIKE \'%'.addslashes($parameter).'%\'';
	}
	
	$query = "SELECT * FROM ".$table." WHERE ( ".join(' OR ', $q)." ) ".
		($patient ? "AND ".$pfield."='".addslashes($patient)."'" : '' );
	$res = $GLOBALS['sql']->query($query);
	if (!$GLOBALS['sql']->results($res)) { return false; }
	$count = 0;
	while ($r = $GLOBALS['sql']->fetch_array( $res ) ) {
		$count++;
		if ($count < $limit) {
			$return[] = trim(_result_to_hash($r, $hash)).'@'.$r['id'];
		}
	}
	if ($count >= $limit) { $return[] = " ... "; }
	return join('|', $return);
} // end function lookup

function patient_lookup ( $criteria ) {
	$limit = 10;

	// Form query with "smart search"
	if (!(strpos($criteria, ',') === false)) {
		// last, first
		list ($last, $first) = explode (',', $criteria);
	} else {
		// Determine if there's a space
		if (!(strpos($criteria, ' ') === false)) {
			// Break into first last
			list ($first, $last) = explode (',', $criteria);
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
		$q[] = "( ptlname LIKE '%".addslashes($last)."%' AND ".
			" ptfname LIKE '%".addslashes($first)."%' )";
	} else {
		// Either
		$q[] = "ptfname LIKE '%".addslashes($either)."%'";
		$q[] = "ptlname LIKE '%".addslashes($either)."%'";
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
			$return[] = trim($_obj->to_text()).'@'.$r['id'];
		}
	}
	if ($count >= $limit) { $return[] = " ... "; }
	return join('|', $return);
} // end function patient_lookup

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
