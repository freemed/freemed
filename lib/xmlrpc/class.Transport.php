<?php
	// $Id$
	// $Author$

class Transport {

	function parse ( $transport, $message ) {
		$parser = CreateObject('_FreeMED.Parser_'.$transport, $message);
		if (!is_object($parser)) return false;
		return $parser->Handle();
	} // end method FreeMED.Transport.parse

} // end class Transport

?>
