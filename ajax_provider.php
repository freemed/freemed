<?php
	// $Id$
	// $Author$

include_once ( 'lib/freemed.php' );

$ajax = CreateObject( 'PHP.Sajax', 'ajax_provider.php' );
$ajax->export ( 'lookup' );
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
