<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.Handler_HL7v2');

class Handler_HL7v2_A04 extends Handler_HL7v2 {

	public function Handle () {
		syslog(LOG_INFO, 'HL7 parser| Entered A04 parser');
		if (!is_object($this->parser)) {
			die('Handler_HL7v2_A04: parser object not present');
		}

		// Loop for each PID
		foreach ($this->parser->message['PID'] AS $k => $v) {
			// Understanding that this breaks true HL7 compliance,
			// this will check to see if the PID exists in the
			// system and generate the equivalent of an A04 call
			// if it does not.
			$exist_query = $GLOBALS['sql']->queryRow(
				"SELECT * FROM patient WHERE ptid='".
				addslashes($v[HL7v2_PID_ID])."' AND ptarchive=0"
			);

			// Select matching PV1 segment (hack?)
			$pv1 = $this->parser->message['PV1'][$k];
			if ($pv1[HL7v2_PV1_REFERRING][HL7v2_PV1_REFERRING_ID]) {
				syslog(LOG_INFO, 'HL7 parser| PV1 - for ID #'.$pv1[HL7v2_PV1_REFERRING][HL7v2_PV1_REFERRING_ID].' found '.$this->parser->composite_to_provider($pv1[HL7v2_PV1_REFERRING]));
			}
			
			// Create array of variables
			$variables = array (
				'ptlname' => $v[HL7v2_PID_NAME][HL7v2_PID_NAME_LAST],
				'ptfname' => $v[HL7v2_PID_NAME][HL7v2_PID_NAME_FIRST],
				'ptmname' => $v[HL7v2_PID_NAME][HL7v2_PID_NAME_MIDDLE],
				'ptdob' => $this->ConvertDate($v[HL7v2_PID_DATEOFBIRTH]),
				'ptsex' => strtolower($v[HL7v2_PID_GENDER]),
				'ptaddr1' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_LINE1],
				'ptaddr2' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_LINE2],
				'ptcity' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_CITY],
				'ptstate' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_STATE],
				'ptzip' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_ZIPCODE],
				//'ptcountry' => $this->parser->PID['patient']['country'],
				'pthphone' => $this->FixPhoneNumber($this->StripToNumeric($v[HL7v2_PID_PHONE_HOME])),
				'ptwphone' => $this->FixPhoneNumber($this->StripToNumeric($v[HL7v2_PID_PHONE_WORK])),
				'ptssn' => $this->StripToNumeric($v[HL7v2_PID_SOCIALSECURITY]),
				'ptreligion' => '99',
				'ptrace' => '7',
				'ptmarital' => 'unknown',
				'ptarchive' => '0',
				'ptid' => $v[HL7v2_PID_ID]
			);
			// Don't put referrers in who aren't specified
			if ($pv1[HL7v2_PV1_REFERRING]) {
				$variables['ptrefdoc'] = $this->parser->composite_to_provider($pv1[HL7v2_PV1_REFERRING]);
			}
			if ($exist_query['id']) {
				// Exists, proceed with A08
				$query = $GLOBALS['sql']->update_query(
					'patient',
					$variables,
					array ('ptid' => $v[HL7v2_PID_ID])
				);
				syslog(LOG_INFO, 'HL7 parser| query = '.$query);
				$result = $GLOBALS['sql']->query( $query );
				$r = $GLOBALS['sql']->queryRow("SELECT * FROM patient WHERE ptid='".addslashes($v[HL7v2_PID_ID])."' AND ptarchive=0"));
				freemed::handler_breakpoint('PatientModify', array($r['pid']));
			} else {
				// Otherwise use A04 type query to add patient
				$query = $GLOBALS['sql']->insert_query(
					'patient',
					$variables
				);
				syslog(LOG_INFO, 'HL7 parser| query = '.$query);
				$result = $GLOBALS['sql']->query( $query );
				$pid = $GLOBALS['sql']->lastInsertID( 'patient', 'id' );
				freemed::handler_breakpoint('PatientAdd', array($pid));
			}
		}
	} // end method Handle

	public function Type () { return 'ADT'; }

} // end class Handler_HL7v2_A04

?>
