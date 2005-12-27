<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MFO_Element');

// Class: MFO_Element_Label
//
//	Text label
//
class MFO_Element_Label extends MFO_Element {

	function MFO_Element_Label ( $text, $options = NULL ) {
		$this->_contents = $text;
		$this->MFO_Element( $options );
	} // end constructor

	function toXml ( ) {
		return "<label>".htmlentities($this->_contents)."</label>\n";
	} // end method toXml

} // end class MFO_Element_Label

?>
