<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MFO');

// Class: MFO_Element
//
//	Base class upon which to build additional widgets.
//
class MFO_Element extends MFO {

	var $_contents;

	function MFO_Element ( $options ) {

	} // end constructor

	function addStack ( $stack ) {
		die("addStack(): cannot be called for an element");
	} // end method addStack

	function toXml ( ) {
		die("toXml(): Must be overridden!");
	} // end method toXml

} // end class MFO_Element

?>
