<?php
	// $Id$
	// $Author$

class FBGlobal {
	
	function CurrentDate ( ) {
		return CreateObject('PHP.xmlrpcval', date("Ymd")."T00:00:00", xmlrpcDateTime);
	} // end method CurrentDate

	function getBillKey ( $key ) {
		// TODO: Stubbed functionality
		return false;
	} // end method getBillKey

} // end class FBGlobal

?>
