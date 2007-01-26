<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.BaseModule');

// Class: org.freemedsoftware.core.GraphModule
//
//	Graphing report module superclass. This is descended
//	from <BaseModule>.
//
class GraphModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Graph";
	var $CATEGORY_VERSION = "0.3";

	// contructor method
	public function __construct () {
		$this->_SetHandler( 'GraphingModule', 'Generate' );
		$this->_SetMetaInformation( 'table_name', 'NONE' );

		// Call parent constructor
		parent::__construct();
	} // end function GraphModule

	// Method: Generate
	//
	// SeeAlso:
	//	<GenerateReport>
	//
	public function Generate ( $params ) {
		$phash = $this->Parameters();

		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );

		// Check for required parameters, etc
		foreach ($phash AS $k => $v) {
			if ($v['required'] and empty($params[$k])) {
				return $this->GraphError(__("You did not fill out all required parameters."));
			}

			// Preprocess data depending on the type
			switch ( $type ) {
				case 'date':
				$p[$k] = $s->ImportDate( $params[$k] );
				break;

				default:
				$p[$k] = $params[$k];
				break;
			}
		}

		// Wrap the protected core function
		return $this->GenerateReport( $p );
	} // end method Generate

	// Method: GraphError
	//
	//	Return an error message.
	//
	// Parameters:
	//
	//	$message - Text of error message.
	//
	protected function GraphError ( $message ) {
		// TODO: This should give a graphical message.
		die ( $message );
	} // end method GraphError

	// Method: Parameters
	//
	//	Get hash of parameters.
	//
	// Returns:
	//
	//	Hash referencing hash with elements:
	//	* text - Text, descriptional
	//	* type - Enumerated:
	//	  * date
	//	  * select
	//	  * text
	//	* options - Varies, depending on 'type'
	//	* required - Boolean, whether element is required
	//
	public function Parameters ( ) {
		return is_array($this->parameters) ? $this->parameters : array();
	} // end method Parameters

} // end class GraphModule

?>
