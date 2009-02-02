<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.Authorizations
//
//	Handle all aspects of authorizations for patient visits. This
//	allows calculation and manipulation of visits by coverages and
//	office visits.
//
class Authorizations {

	// constructor STUB
	public function __construct ( ) { }

	// Method: find_by_coverage
	//
	//	Find authorizations based on a coverage id
	//
	// Parameters:
	//
	//	$coverage - Coverage id key
	//
	// Returns:
	//
	//	Array of authorization keys, or false if it cannot
	//	find any.
	//
	function find_by_coverage ( $coverage ) {
		// Get insurance company and patient from coverage
		$this_coverage = $GLOBALS['sql']->get_link( 'coverage', $coverage );
		$patient = $this_coverage['covpatient'];
		$insco = $this_coverage['covinsco'];

		// If there is no insurance company link, fail
		if (!$insco) { return false; }

		// Select authorizations that match criteria
		$query = "SELECT id FROM authorizations WHERE ".
			"authinsco = '".addslashes($insco)."' AND ".
			"authpatient = '".addslashes($patient)."'";
		$return = $GLOBALS['sql']->queryCol( $query );

		// Return array of identifiers
		return $return;
	} // end method find_by_coverage

	// Method: get_authorization
	//
	//	Gets the SQL record associated with an authorization.
	//
	// Parameters:
	//
	//	$auth - Authorization id key
	//
	// Returns:
	//
	//	Associative array of values.
	//
	function get_authorization ( $auth ) {
		return $GLOBALS['sql']->get_link( 'authorizations', $auth );
	} // end method get_authorization

	// Method: replace
	//
	//	Puts an authorization back to pre-appointment status.
	//	This has the exact opposite effect as <use_authorization>,
	//	as it increases the number of visits remaining on an
	//	authorization.
	//
	// Parameters:
	//
	//	$auth - Authorization key id
	//
	// Returns:
	//
	//	Boolean, successful
	//
	// See Also:
	//	<use_authorization>
	//
	function replace_authorization ( $auth ) {
		$query = "UPDATE authorizations ".
			"SET authvisitsused = authvisitsused - 1, ".
			"authvisitsremain = authvisitsremain + 1 ".
			"WHERE id = '".addslashes($auth)."'";
		$result = $GLOBALS['sql']->query ( $query );
		return $result;
	} // end method replace_authorization

	// Method: use_authorization
	//
	//	"Use" an authorization. This computes remaining visits
	//	and other information for the authorization. This has
	//	the exact opposite effect as <replace_authorization>.
	//
	// Parameters:
	//
	//	$auth - Authorization key id
	//
	// Returns:
	//
	//	Boolean, successful
	//
	// See Also:
	//	<replace_authorization>
	//
	function use_authorization ( $auth ) {
		$query = "UPDATE authorizations ".
			"SET authvisitsused = authvisitsused + 1, ".
			"authvisitsremain = authvisitsremain - 1 ".
			"WHERE id = '".addslashes($auth)."'";
		$result = $GLOBALS['sql']->query ( $query );
		return $result;
	} // end method use_authorization

	// Method: valid
	//
	//	Determine if an authorization is valid, based on the
	//	date given.
	//
	// Parameters:
	//
	//	$auth - Authorization id key
	//
	//	$date - (optional) Date for the comparison. Defaults to
	//	the current date if none is provided.
	//
	// Returns:
	//
	//	Boolean, if authorization is currently valid.
	//
	function valid ( $auth, $date = NULL ) {
		// Check for date passed... use current by default
		if ($date = NULL) {
			$search_date = date('Y-m-d');
		} else {
			$search_date = $date;
		} // end date passed or not

		// Get authorization record
		$a = $this->get_authorization($auth);

		// First check dates
		$startdt = str_replace('-', '', $a['authdtbegin']);
		$enddt   = str_replace('-', '', $a['authdtend']);
		$curdt   = str_replace('-', '', $search_date);
		if ( ($curdt < $start) or ($curdt > $end) ) {
			//print "denied by date range ($startd < $curdt < $end)<br/>\n";
			return false;
		} // end date check

		// Check by visits remaining
		if ($a['authvisitsremain'] < 1) {
			//print "denied by no visits remaining<br/>\n";
			return false;
		}

		// If all else fails, this is valid, return pass
		return true;
	} // end method valid

	// Method: valid_set
	//
	//	Find set of valid authorizations from a set of
	//	unvalidated authorization keys.
	//
	// Parameters:
	//
	//	$set - Array of unvalidated authorization keys
	//
	//	$date - (optional) Date to use for range comparison.
	//	Defaults to the current date.
	//
	// Returns:
	//
	//	Array of valid authorization keys, or NULL array if
	//	none exist.
	//
	// See Also:
	//	<valid>
	//
	function valid_set ( $set, $date = NULL ) {
		// Check input
		if (!is_array($set)) { return array ( ); }

		// Start with an empty array in case there are no valids
		$result = array ( );
		foreach ($set AS $authorization) {
			if ($this->valid($authorization, $date)) {
				$result[] = $authorization;
			} // end checking for valid
		} // end foreach auth

		// Return resulting validated set
		return $result;
	} // end method valid_set

} // end class Authorizations

?>
