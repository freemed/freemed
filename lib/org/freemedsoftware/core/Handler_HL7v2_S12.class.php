<?php
 // $Id$
 // Handler for HL7 message S12 - Appointment Booking
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

class Handler_HL7v2_S12 extends Handler_HL7v2 {

	public function Handle () {
		syslog(LOG_INFO, 'HL7 parser| Entered S12 parser');
		if (!is_object($this->parser)) {
			die('Handler_HL7v2_S12: parser object not present');
		}

		// FIXME: SHOULD HANDLE AIL SEGMENT FOR LOCATION

		// For now, one PID per message - FIXME
		$p = $this->parser->message['PID'][0];
		foreach ($this->parser->message['SCH'] AS $k => $v) {
			$pr = $this->parser->message['AIP'][$k];

			// Use scheduler API
			$c = CreateObject('org.freemedsoftware.api.Scheduler');
			$c->set_appointment(array(
				'type' => 'pat', // hardcode as patient
				'date' => $this->parser->date_to_sql($pr[HL7v2_AIP_DATETIME]),
				'hour' => $this->parser->date_to_hour($pr[HL7v2_AIP_DATETIME]),
				'minute' => $this->parser->date_to_minute($pr[HL7v2_AIP_DATETIME]),
				'duration' => ($pr[HL7v2_AIP_DURATION]+0),
				'patient' => $this->parser->pid_to_patient($p[HL7v2_PID_ID]),
				'provider' => $this->parser->aip_to_provider($pr[HL7v2_AIP_PROVIDER][HL7v2_AIP_PROVIDER_ID]),
				'note' => $v[HL7v2_SCH_EXTNOTE][HL7v2_SCH_EXTNOTE_LONG]
			));

			// Quickly log what has happened
			syslog(LOG_INFO, 'HL7 parser| accepted S12 appointment creation for patient #'.$this->parser->pid_to_patient($p[HL7v2_PID_ID]).', provider #'.$this->parser->aip_to_provider($pr[HL7v2_AIP_PROVIDER][HL7v2_AIP_PROVIDER_ID]));
		}
	} // end method Handle

	public function Type () { return 'SIU'; }

} // end class Handler_HL7v2_S12

?>
