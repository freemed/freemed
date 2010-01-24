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

// Class: org.freemedsoftware.core.NcpdpXmlInterface
//
//	Class to allow NCPDP communication
//
class NcpdpXmlInterface {

	protected $EOL = "\n";

	protected $productionMode = false;

	protected $userId = '';

	protected $password = '';

	public function __construct() { }

	// Method: setPassword
	public function setPassword( $p ) {
		$this->password = $p;
	} // end method setPassword

	// Method: setProductionMode
	//
	//	Set whether or not this is running in "production" mode,
	//	otherwise being testing/staging mode.
	//
	// Parameters:
	//
	//	$mode - Boolean production status
	//
	public function setProductionMode( boolean $mode ) {
		$this->productionMode = $mode;
	} // end method setProductionMode

	// Method: setUserId
	public function setUserId( $i ) {
		$this->userId = $i;
	} // end method setUserId

	// Method: createHeaderSegment
	//
	// Returns:
	//
	//	Fully formed <Header/> segment for an NCPDP message
	//
	public function createHeaderSegment ( ) {
		$h = ' <Header>' . $this->EOL;
		$h .= '  <To>mailto:SSSDIR.dp@surescripts.com</To>' . $this->EOL;
		$h .= '  <From>mailto:XXX001.dp@surescripts.com</From>' . $this->EOL;
		$h .= '  <MessageID>' . md5( mktime() ) . '</MessageID>' . $this->EOL;
		$h .= '  <SentTime>2007-01-24T21:22:29.4Z</SentTime>' . $this->EOL;
		$h .= '  <Security>' . $this->EOL;
		$h .= '   <UsernameToken>' . $this->EOL;
		$h .= '    <Username>' . htmlentities( $this->userId ) . '</Username>' . $this->EOL;
		$h .= '    <Password Type="PasswordDigest">' . sha1( $this->password ) . '</Password>' . $this->EOL;
		$h .= '    <Nonce>3608</Nonce>' . $this->EOL;
		$h .= '    <Created>1900-01-01T12:00:00.4Z</Created>' . $this->EOL;
		$h .= '   </UsernameToken>' . $this->EOL;
		$h .= '  </Security>' . $this->EOL;
		$h .= ' </Header>' . $this->EOL;
		return $h;
	} // end method createHeaderSegment

	// Method: createDirectoryDownloadMessage
	//
	// Parameters:
	//
	//	$prescriber - Boolean, prescriber = true, pharmacy = false
	//
	//	$downloadDate - YYYYMMDD nightly download date, empty if full download
	//
	// Returns:
	//
	//	XML message text
	//
	public function createDirectoryDownloadMessage( $prescriber = true, $downloadDate = '' ) {
		$msg = '<?xml version="1.0" encoding="utf-8"?>' . $this->EOL;
		$msg .= '<Message version="1.5" xmlns="http://www.surescripts.com/messaging">' . $this->EOL;
		$msg .= $this->createHeaderSegment() . $this->EOL;
		$msg .= ' <Body>' . $this->EOL;
		$msg .= '  <DirectoryDownload>' . $this->EOL;
		$msg .= '   <AccountID>1</AccountID>' . $this->EOL;
		$msg .= '   <VersionID>4</VersionID>' . $this->EOL;
		$msg .= '   <Taxonomy>' . $this->EOL;
		$msg .= '    <TaxonomyCode>' . ( $prescriber ? '193200000X' : '183500000X' ) . '</TaxonomyCode>' . $this->EOL;
		$msg .= '   </Taxonomy>' . $this->EOL;
		$msg .= '   <DirectoryDate>' . $downloadDate . '</DirectoryDate>' . $this->EOL;
		$msg .= '  </DirectoryDownload>' . $this->EOL;
		$msg .= ' </Body>' . $this->EOL;
		$msg .= '</Message>' . $this->EOL;
		return $msg;
	} // end method createDirectoryDownloadMessage

} // end class NcpdpXmlInterface

?>
