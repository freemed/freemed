<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

class XmrDefinition extends SupportModule {

	var $MODULE_NAME    = "XMR Definition";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Form templates.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "0caf55e8-b604-44e6-a55d-741336281771";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "XMR Definition";
	var $table_name     = "xmr_definition";
	var $order_by       = "form_name";

	var $widget_hash = "##form_name## (##form_locale##)";

	var $variables = array (
		  'form_name'
		, 'form_description'
		, 'form_locale'
		, 'form_template'
		, 'form_page_count'
	);

	var $element_keys = array (
		  'form_id'
		, 'text_name'
		, 'code_set'
		, 'parent_concept_id'
		, 'concept_id'
		, 'quant_code_set'
		, 'quant_id'
		, 'external_population'
		, 'widget_type'
		, 'widget_options'
		, 'form_source_page'
		, 'form_destination_page'
		, 'form_x'
		, 'form_y'
	);

	public function __construct () {
		// For i18n: __("XMR Definition")

		$this->list_view = array (
			__("Name")	=> "form_name",
			__("Language")	=> "form_locale"
		);

		// Run constructor
		parent::__construct();
	} // end constructor

	// Method: GetFormElementsWithDefaults
	//
	// Parameters:
	//
	//	$id - Form id
	//
	public function GetFormElementsWithDefaults( $id ) {
		$q = "SELECT * FROM xmr_definition_element WHERE form_id = " . $GLOBALS['sql']->escape( $id );
		$res = $GLOBALS['sql']->queryAll( $q );
		$elements = array();
		foreach ( $res AS $r ) {
			$elements[] = (array) $r;
		}

		// TODO: FIXME: get actual defaults instead of none
		$defaults = array(array());

		return array( $elements, $defaults );
	} // end method GetFormElementsWithDefaults

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
					'xmr_definition_element',
					$this->element_keys
				);
				$GLOBALS['sql']->query( $query );
			} else {
				if ( $a['altered'] ) {
					syslog( LOG_DEBUG, "SetElements: modifying address for form $id, id = ".$a['id'] );
					$GLOBALS['sql']->load_data( $a );
					$query = $GLOBALS['sql']->update_query(
						'xmr_definition_element',
						$this->element_keys,
						array( 'id' => $a['id'] )
					);
					$GLOBALS['sql']->query( $query );
				}
			}
		}
		return true;
	} // end method SetElements

} // end class XmrDefinition

register_module ("XmrDefinition");

?>
