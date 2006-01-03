<?php
	// $Id$
	// $Author$

class MFO {

	var $_contents;

	function MFO ( $options ) {

	} // end constructor

	// Method: addStack
	function addStack ( $stack ) {
		if (is_object($stack)) {
			$this->_contents[] = $stack;
		} elseif (is_array($stack)) {
			foreach ($stack AS $s) { $this->addStack($s); }
		} else {
			// TODO: Handle type exceptions
		}
	} // end method addStack

	// Method: toXml
	function toXml ( ) {
		$buffer .= '<'.'?xml version="1.0"?'.">\n";
		$buffer .= "<mfo>\n";
		foreach ($this->_contents AS $item) {
			$buffer .= $item->toXml();
		}
		$buffer .= "</mfo>\n";
		return $buffer;
	} // end method toXml

	// Method: toOutput
	//
	// Parameters:
	//
	//	$xsl - XSLT stylesheet to use (skip the .xsl extension). If none is specified,
	//	MFO XML is directly output.
	//
	// Returns:
	//
	//	Output document.
	//
	function toOutput ( $xsl = NULL ) {
		if ($xsl == NULL) { return $this->toXml(); }
		if (!function_exists('xslt_create')) {
			die('toOutput(): no XSLT support exists in your version of PHP');
		}

		// Pass globals as parameters
		$variables = array_merge(
			$_REQUEST,
			$GLOBALS,
			array (
				'title' => 'title'
			)
		);

		$path = 'lib/xsl';
		$arguments = array('/_xml' => $this->toXml());
		$xsltproc = xslt_create();
		xslt_set_encoding($xsltproc, 'UTF-8');
		$output = xslt_process($xsltproc, 'arg:/_xml', "${path}/${xsl}.xsl", NULL, $arguments, $variables);
		if (empty($output)) {
			die('XSLT processing error: '.xslt_error($xsltproc));
		}
		xslt_free($xsltproc);
		return $output;
	} // end method toOutput

} // end class MFO

?>
