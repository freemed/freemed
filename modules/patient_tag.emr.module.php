<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class PatientTag extends SupportModule {

	var $MODULE_NAME = "Patient Tag";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "1c34f308-1503-4478-9179-896248067fb4";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Patient Tag";
	var $table_name  = "patienttag";
	var $order_field = "datecreate,dateexpire";

	//var $widget_hash = "##tag## (##datecreate## - ##dateexpire##)";
	var $widget_hash = "tag";

	public function __construct ( ) {
		// __("Patient Tag")
	
		$this->list_view = array (
			__("Tag") => 'tag',
			__("Date Created") => 'datecreate',
			__("Date Expires") => 'dateexpire'
		);

		$user = freemed::user_cache();
		$this->variables = array (
			"tag",
			"patient",
			"user",
			"datecreate",
			"dateexpire"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$date['datecreate'] = '';
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: ListTags
	//
	//	Get list of tags based on criteria.
	//
	// Parameters:
	//
	//	$criteria - Criteria
	//
	// Returns:
	//
	//	Array of key = value hashes.
	//
	public function ListTags ( $criteria ) {
		if (strlen($criteria) < 3) { return array(); }
		$query = "SELECT DISTINCT(tag) AS tag FROM ".$this->table_name." WHERE tag LIKE '%".$GLOBALS['sql']->escape( $criteria )."%' AND ( dateexpire = 0 OR dateexpire > NOW() ) ORDER BY tag LIMIT 20";
		$result = $GLOBALS['sql']->queryCol( $query );
		$found = false;
		foreach ( $result AS $entry ) {
			if (strtolower($entry) == strtolower($criteria)) { $found = true; }
			$return[$entry] = $entry;
		}
		if (!$found) { $return[$criteria] = $criteria; }
		return $return;
	} // end method ListTags

	// Method: CreateTag
	//
	//	Attach a new tag to a patient
	//
	// Parameters:
	//
	//	$patient - Patient record id.
	//
	//	$tag - Textual name of tag
	//
	public function CreateTag ( $patient, $tag ) {
		if ($patient and $tag) {
			$user = freemed::user_cache();
			$query = $GLOBALS['sql']->insert_query (
				$this->table_name,
				array (
					'user' => $user->user_number,
					'patient' => $patient,
					'tag' => $tag
				)
			);
			$GLOBALS['sql']->query( $query );
		}
		return true;
	} // end method CreateTag

	// Method: ExpireTag
	//
	//	Force tag to expire for specified patient and tag.
	//
	// Parameters:
	//
	//	$patient - Patient record id.
	//
	//	$tag - Textual name of tag in question.
	//
	public function ExpireTag ( $patient, $tag ) {
		$query = "UPDATE `".$this->table_name."` SET dateexpire=NOW() WHERE patient=".$GLOBALS['sql']->quote( $patient )." AND tag=".$GLOBALS['sql']->quote( $tag );
		$GLOBALS['sql']->query( $query );
		return true;
	} // end method ExpireTag

	// Method: TagsForPatient
	//
	//	Get list of all tags associated with a patient.
	//
	// Parameters:
	//
	//	$patient - Patient record id.
	//
	// Returns:
	//
	//	Array of tags.
	//
	public function TagsForPatient ( $patient ) {
		$query = "SELECT tags FROM patienttaglookup WHERE patient=".$GLOBALS['sql']->quote( $patient );
		$return = $GLOBALS['sql']->queryOne( $query );
		return explode( ',', $return );
	} // end method TagsForPatient

	// Method: SimpleTagSearch
	//
	//	Tag search function.
	//
	// Parameters:
	//
	//	$tag - Name of tag to search for.
	//
	//	$include_inactive - (optional) Boolean, include inactive tags.
	//	Defaults to false.
	//
	// Returns:
	//
	//	Array of hashes. (See <SearchEngine> output)
	//
	// SeeAlso:
	//	<AdvancedTagSearch>
	//	<SearchEngine>
	//
	public function SimpleTagSearch ( $tag, $include_inactive = false ) {
		// Handle anything "funny"
		if (!$tag) { return false; }

		return $this->SearchEngine( "t.tag=".$GLOBALS['sql']->quote($tag), $include_inactive );
	} // end method SimpleTagSearch

	// Method: AdvancedTagSearch
	//
	//	Advanced tag searching function, allowing simple boolean searching.
	//
	// Parameters:
	//
	//	$tag - Name of primary tag to search for.
	//
	//	$clauses - Array of hashes like this:
	//	* tag - Name of tag
	//	* operator - 'AND' or 'OR'
	//
	//	$include_inactive - (optional) Boolean, include inactive tags.
	//	Defaults to false.
	//
	// Returns:
	//
	//	Array of hashes. (See <SearchEngine> output)
	//
	// SeeAlso:
	//	<SimpleTagSearch>
	//	<SearchEngine>
	//
	public function AdvancedTagSearch ( $tag, $clauses, $include_inactive = false ) {
		// Handle anything "funny"
		if (!$tag) { return false; }

		$where = "t.patient IN ( SELECT t.patient FROM patienttag t WHERE t.tag=".$GLOBALS['sql']->quote($tag).( !$include_inactive ? " AND ( t.dateexpire=0 OR t.dateexpire>NOW() )" : "" )." ) ";

		foreach ($clauses AS $clause) {
			if ($clause['tag']) {
				switch ($clause['operator']) {
					case 'AND': case 'OR':
					$where .= $clause['operator']." t.patient IN ( SELECT t.patient FROM patienttag t WHERE t.tag=".$GLOBALS['sql']->quote($clause['tag']).( !$include_inactive ? " AND ( t.dateexpire=0 OR t.dateexpire>NOW() )" : "" )." ) ";
					break;

					default: break;
				}
			}
		}
		return $this->SearchEngine( $where, $include_inactive );
	} // end method SimpleTagSearch

	// Method: SearchEngine
	//
	//	Protected internal function with actual searching capabilities.
	//
	// Parameters:
	//
	//	$clase - WHERE clause
	//
	//	$include_inactive - (optional) Boolean, include inactive tags.
	//	Defaults to false.
	//
	// Returns:
	//
	//	Hash of found values.
	//	* patient_record - Patient record id
	//	* patient_id - Practice ID for patient
	//	* last_seen - Date last seen/next appointment
	//	* first_name - First name of patient
	//	* last_name - Last name of patient
	//	* middle_name - Middle name of patient
	//	* date_of_birth - Patient's date of birth
	//	* tags - CSV list of patient tags
	//
	protected function SearchEngine ( $clause, $include_inactive = false ) {
		$query = "SELECT p.id AS patient_record, p.ptid AS patient_id, MAX(c.caldateof) AS last_seen, p.ptlname AS last_name, p.ptfname AS first_name, p.ptmname AS middle_name, p.ptdob AS date_of_birth, tl.tags AS tags FROM patient p LEFT OUTER JOIN patienttag t ON p.id=t.patient LEFT OUTER JOIN scheduler c ON p.id=c.calpatient LEFT OUTER JOIN patienttaglookup tl ON tl.patient=p.id WHERE ".( !$include_inactive ? "( t.dateexpire=0 OR t.dateexpire>NOW() ) AND" : "" )." ( ${clause} ) GROUP BY p.id";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method SearchEngine

} // end class PatientTag

register_module ("PatientTag");

?>
