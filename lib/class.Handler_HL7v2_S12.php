<?php
	// $Id$
	// $Author$

	// Handler for HL7 message S12 - Appointment Booking

LoadObjectDependency('_FreeMED.Handler_HL7v2');

class Handler_HL7v2_S12 extends Handler_HL7v2 {

	function Handle () {
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
			$c = CreateObject('_FreeMED.Scheduler');
			$c->set_appointment(array(
				'type' => 'pat', // hardcode as patient
				'date' => $this->parser->__date_to_sql($pr[HL7v2_AIP_DATETIME]),
				'hour' => $this->parser->__date_to_hour($pr[HL7v2_AIP_DATETIME]),
				'minute' => $this->parser->__date_to_minute($pr[HL7v2_AIP_DATETIME]),
				'duration' => ($pr[HL7v2_AIP_DURATION]+0),
				'patient' => $this->parser->__pid_to_patient($p[HL7v2_PID_ID]),
				'provider' => $this->parser->__aip_to_provider($pr[HL7v2_AIP_PROVIDER][HL7v2_AIP_PROVIDER_ID]),
				'note' => $v[HL7v2_SCH_NOTE][HL7v2_SCH_NOTE_LONG]
			));

			// Quickly log what has happened
			syslog(LOG_INFO, 'HL7 parser| accepted S12 appointment creation for patient #'.$this->parser->__pid_to_patient($p[HL7v2_PID_ID]).', provider #'.$this->parser->__aip_to_provider($pr[HL7v2_AIP_PROVIDER][HL7v2_AIP_PROVIDER_ID]));
		}
	} // end method Handle

	function Type () { return 'SIU'; }

} // end class Handler_HL7v2_S12

?>
