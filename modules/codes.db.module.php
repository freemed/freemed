<?php
  // $Id$
  //
  // Authors:
  //      Jeff Buchbinder <jeff@freemedsoftware.org>
  //
  // Copyright (C) 1999-2006 FreeMED Software Foundation
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

LoadObjectDependency('_FreeMED.MaintenanceModule');

class CodesModule extends MaintenanceModule {

	var $MODULE_NAME = 'Codes';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name = "codes";
	var $order_field = "codevalue, codedescripinternal";
	var $widget_hash = "##codevalue## ##codedescripinternal## [##codedictionary##]";

	function CodesModule () {
		// __("Codes")

		$this->table_definition = array (
			'codedictionary' => SQL__VARCHAR(50),
			'codevalue' => SQL__VARCHAR(50),
			'codedescripinternal' => SQL__VARCHAR(100),
			'codedescripexternal' => SQL__VARCHAR(100),
			'codelimitgender' => SQL__ENUM(array('n', 'm', 'f')),
			'id' => SQL__SERIAL
		);
		$this->table_keys = array ( 'codedictionary', 'codedescripinternal', 'codevalue', 'codelimitgender' );
		$this->variables = array ( 'codedictionary', 'codevalue', 'codedescripinternal', 'codedescripexternal', 'codelimitgender' );

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor CodesModule

	function generate_form ( ) {
		return array (
			'Dictionary' => html_form::select_widget(
					'codedictionary',
					array (
						'DSM' => 'dsm',
					)
				),
			'Code' => html_form::text_widget('codevalue', 50, 50),
			'Internal Description' => html_form::text_widget('codedescripinternal', 50, 250),
			'External Description' => html_form::text_widget('codedescripexternal', 50, 250),
			'Gender Limitation' => html_form::select_widget(
				'codegenderlimit',
				array (
					'None' => 'n',
					'Male' => 'm',
					'Female' => 'f'
				)
			)
		);
	} // end method form_table

	function view ( ) {
		$GLOBALS['display_buffer'] .= freemed_display_itemlist (
			$GLOBALS['sql']->query (
				"SELECT * FROM ".$this->table_name." ".
				freemed::itemlist_conditions ( )." ".
				"ORDER BY ".$this->order_field
			),
			$this->page_name,
			array (
				'Dictionary' => 'codedictionary',
				'Code' => 'codevalue',
				'Description' => 'codedescripinternal'
			),
			array (
				'',
				'',
				''
			)
		);
	} // end method view

	function remote_picklist ( $dsm , $type, $search ) {
		$query = "SELECT tpoption FROM $this->table_name WHERE tpdsm='".addslashes($dsm)."' AND tptype='".addslashes($type)."' AND ( tpoption LIKE '%".addslashes($search)."%' )";
		$result = $GLOBALS['sql']->query( $query );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[$r['tpoption']] = $r['tpoption'];
		}
		return $return;
	} // end method remote_picklist

} // end class CodesModule 

register_module("CodesModule");

?>
