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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class Xmr extends EMRModule {

	var $MODULE_NAME    = "XMR";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Extensible forms module";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "69d40a18-f6b7-4f56-8a41-eb0d93a01078";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "XMR";
	var $table_name     = "xmr";
	var $patient_field  = "patient";
	var $widget_hash    = "##stamp## ##fr_formname##";

	var $variables = array (
		  'patient'
		, 'form_id'
		, 'provider'
		, 'user'
	);

	var $element_keys = array (
		  'patient'
		, 'patient_form_id'
		, 'atom_id'
		, 'obx_source'
		, 'user'
		, 'value'
	);

	public function __construct ( ) {
		// __("Forms")

		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => '_timestamp',
			__("Form") => 'form_id'
		);
		$this->summary_query = array (
			"DATE_FORMAT(fr_timestamp, '%b %d, %Y %H:%i') AS _timestamp"
		);
		$this->summary_options |= SUMMARY_PRINT | SUMMARY_DELETE;

		// Run parent constructor
		parent::__construct ( );
	} // end constructor Xmr

	// Method: SetElements
	//
	// Parameters:
	//
	//	$id - Form ID
	//
	//	$elements - Array of hashes
	//	* form_id - Link to parent form record
	//	* text_name - Textual name
	//	* parent_concept_id - UMLS parent concept ID
	//	* concept_id - UMLS concept ID
	//	* quant_id - UMLS quantifier concept ID
	//	* external_population - Boolean flag to determine if this is populated by other parts of the medical record
	//	* altered - Boolean flag to determine whether or not this entry has been altered.
	//	* id - 0 if new, otherwise the current id
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function SetElements ( $id, $elements ) {
		$es = (array) $elements;
		foreach ( $es AS $a ) {
			// Force as an array
			$a = (array) $a;

			// Preprocessing
			$a['form_id'] = $id;

			// If id = 0, process as new entry
			if ( ( (int) $a['id'] ) == 0 ) {
				syslog( LOG_DEBUG, "SetElements: adding new address for $id" );
				$GLOBALS['sql']->load_data( $a );
				$query = $GLOBALS['sql']->insert_query(
					'xmr_element',
					$this->element_keys
				);
				$GLOBALS['sql']->query( $query );
			} else {
				if ( $a['altered'] ) {
					syslog( LOG_DEBUG, "SetElements: modifying address for form $id, id = ".$a['id'] );
					$GLOBALS['sql']->load_data( $a );
					$query = $GLOBALS['sql']->update_query(
						'xmr_element',
						$this->element_keys,
						array( 'id' => $a['id'] )
					);
					$GLOBALS['sql']->query( $query );
				}
			}
		}
		return true;
	} // end method SetElements

} // end class Xmr

register_module ("Xmr");

?>
