<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MFO');

// Class: MFO_Stack
//
//	Class to allow "stacks" of elements to be created.
//
class MFO_Stack extends MFO {

	var $_name;
	var $_contents;
	var $_size = 0;

	function MFO_Stack ( $name, $options = NULL ) {
		$this->_name = $name;
		$this->_options = $options;
	} // end constructor

	// Method: add
	//
	//	Add an element (from type MFO_Element) to the stack object.
	//
	// Parameters:
	//
	//	$element - MFO_Element object
	//
	function add ( $element ) {
		if (is_object($element)) {
			// Add element
			$this->_contents[] = $element;
			$this->_size++;
		} elseif (is_array($element)) {
			// Recurse over array elements
			foreach ($element AS $e) { $this->add($e); }
		} else {
			// TODO: Handle exceptions
		}
	} // end method add

	// Method: toXml
	//
	//	Render the stack to XML.
	//
	function toXml ( ) {
		$buffer = '<stack id="'.htmlentities($this->_name).'">'."\n";
		foreach ($this->_contents AS $element) {
			$buffer .= $element->toXml();
		}
		$buffer .= "</stack>\n";
		return $buffer;
	} // end method toXml

} // end class MFO_Stack

?>
