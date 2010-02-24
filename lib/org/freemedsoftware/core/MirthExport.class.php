<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.core.MirthExport

class MirthExport {

	public function __construct ( ) { }

	public function SendMessage ( $msg ) {
		if ( freemed::config_value( 'mirth_enabled' ) ) {
			$params = array( 'in0' => $msg );
			return $this->GetProxy()->acceptMessage( (object) $params );
		}
	} // end method SendMessage

	protected function GetProxy ( ) {
		$sc = new SoapClient( PHYSICAL_LOCATION . '/data/wsdl/Mirth.wsdl', array (
			  'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
			, 'location' => freemed::config_value( 'mirth_endpoint' )
		));
		return $sc;
	} // end method GetProxy


} // end class MirthExport

?>
