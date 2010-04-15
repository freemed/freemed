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

LoadObjectDependency( 'org.freemedsoftware.core.Generator' );

class Generator_HL7v2_A04 implements Generator {

	public $EVENT_TYPE = "A04";

	public $EOS = "\r\n";

	public function generate( $data ) {
		$p = $GLOBALS['sql']->queryRow(
			  "SELECT pt.* "
			. "FROM patient pt "
			. "WHERE pt.ptarchive = 0 "
			. " AND pt.id=".$GLOBALS['sql']->quote( $data )
		);
		$stamp = date('YmdHi');
		$gender = strtoupper( $pt['ptsex'] );
		$dob = str_replace( "-", "", $p['ptdob'] );
		$fullstamp = mktime();

		// TODO
		$addresstype = "";
		$country = "";
		$maritalstatus = "";
		$religion = "";

		$m = "";
		// MSH     Message Header Segment
		$m .= "MSH|^~\&|FreeMED|FreeMED|FreeMED||${stamp}||ADT^" . $this->EVENT_TYPE . "|${fullstamp}.1|P|2.5|||AL|NE" . $this->EOS;

		// EVN     Event type segment
		$m .= "EVN|" . $this->EVENT_TYPE . "|${stamp}|${stamp}|U||${stamp}|" . $this->EOS;

		// PID       Patient Identification segment
		$m .= "PID|1|${p['ptid']}|${p['ptid']}||"
			. "${p['ptlname']}^${p['ptfname']}^${p['ptmname']}^${p['ptsuffix']}^${p['ptprefix']}^^U||"
			. "${dob}|${gender}|${race}|"
			. "${p['addressline1']}^${p['addressline2']}^${p['city']}^${p['state']}^${p['postalcode']}^${addresstype}|${country}|${p['pthphone']}|${p['ptwphone']}||${maritalstatus}|${religion}"
			// TODO: finish PID
			. $this->EOS;

		// TODO
		// [PD1]    Patient Additional Demographic segment
		// TODO
		// [PV1]    Patient Visit segment

		return $m;
	} // end method generate

} // end class Generator_HL7v2_A04

?>
