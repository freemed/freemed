<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.Handler_HL7v2');

class Handler_HL7v2_A08 extends Handler_HL7v2 {

	function Handle () {
		syslog(LOG_INFO, 'HL7 parser| Entered A08 parser');
		if (!is_object($this->parser)) {
			die('Handler_HL7v2_A08: parser object not present');
		}

		// Loop for each PID
		foreach ($this->parser->message['PID'] AS $k => $v) {
			// Understanding that this breaks true HL7 compliance,
			// this will check to see if the PID exists in the
			// system and generate the equivalent of an A04 call
			// if it does not.
			$exist_query = $GLOBALS['sql']->query(
				"SELECT * FROM patient WHERE ptid='".
				addslashes($v[HL7v2_PID_ID])."'"
			);
			// Create array of variables
			$variables = array (
				'ptlname' => $v[HL7v2_PID_NAME][HL7v2_PID_NAME_LAST],
				'ptfname' => $v[HL7v2_PID_NAME][HL7v2_PID_NAME_FIRST],
				'ptmname' => $v[HL7v2_PID_NAME][HL7v2_PID_NAME_MIDDLE],
				'ptdob' => $this->_ConvertDate($v[HL7v2_PID_DATEOFBIRTH]),
				'ptsex' => strtolower($v[HL7v2_PID_GENDER]),
				'ptaddr1' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_LINE1],
				'ptaddr2' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_LINE2],
				'ptcity' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_CITY],
				'ptstate' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_STATE],
				'ptzip' => $v[HL7v2_PID_ADDRESS][HL7v2_PID_ADDRESS_ZIPCODE],
				//'ptcountry' => $this->parser->PID['patient']['country'],
				'pthphone' => $this->_StripToNumeric($v[HL7v2_PID_PHONE_HOME]),
				'ptwphone' => $this->_StripToNumeric($v[HL7v2_PID_PHONE_WORK]),
				'ptssn' => $this->_StripToNumeric($v[HL7v2_PID_SOCIALSECURITY]),
				'ptarchive' => '0',
				'ptid' => $v[HL7v2_PID_ID]
			);
			if ($GLOBALS['sql']->results($exist_query)) {
				// Exists, proceed with A08
				$query = $GLOBALS['sql']->update_query(
					'patient',
					$variables,
					array ('ptid' => $v[HL7v2_PID_ID])
				);
			} else {
				// Otherwise use A04 type query to add patient
				$query = $GLOBALS['sql']->insert_query(
					'patient',
					$variables
				);
			}
			//print "query = $query<br>\n";
			syslog(LOG_INFO, 'HL7 parser| query = '.$query);
			$result = $GLOBALS['sql']->query($query);
		}
	} // end method Handle

	function Type () { return 'ADT'; }

} // end class Handler_HL7v2_A08

?>
