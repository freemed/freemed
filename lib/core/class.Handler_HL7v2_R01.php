<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

class Handler_HL7v2_R01 extends Handler_HL7v2 {

	public function Handle () {
		syslog(LOG_INFO, 'HL7 parser| Entered R01 parser');
		if (!is_object($this->parser)) {
			die('Handler_HL7v2_R01: parser object not present');
		}

		// Assume only one pid per message. Borked assumption?
		$pid = $this->parser->message['PID'][0];
		$patient = $this->parser->pid_to_patient($pid[4]); // use practice ID instead of HL7v2_PID_ID, which is sent ID;

		// If we can't find the patient, dump this
/*
		if (!$patient) {
			syslog(LOG_INFO, "HL7 parser| ERROR; could not resolve patient '".$pid[4]."' from PID");
			return false;
		}
*/

		// Determine status (only one orc per)
		$orc = $this->parser->message['ORC'][0];

		// Reject non-results (RE) segments, as we can't handle filling
		// them for now.
		if ($orc[1] != 'RE' and is_array($orc)) {
			syslog(LOG_INFO, 'HL7 parser| ORC segment: can\'t handle non-requests type \''.$orc[1].'\'');
			return false;
		}

		// Map to local[index] = (obr, obr_nte[], obx[], obx_nte[])

		$obr_count = -1; $orc_reached = false;
		foreach ($this->parser->map AS $pos => $element) {
			if ($element['type'] == 'ORC') { $orc_reached = true; }
			// Increment at OBR (record break)
			if ($element['type'] == 'OBR') { $obr_count++; }
			// Keep track of last non-note segment
			if ($element['type'] != 'NTE') { $last_segment = $element['type']; }
			switch ($element['type']) {
				case 'OBR':
				$local[$obr_count]['OBR'] = $this->parser->message[$element['type']][$element['position']];
				break; // OBR

				case 'OBX':
				$local[$obr_count]['OBX'][] = $this->parser->message[$element['type']][$element['position']];
				break; // OBX

				case 'NTE':
				if (!$orc_reached) { break; }
				switch ($last_segment) {
					case 'OBR':
					$tmp = $this->parser->message[$element['type']][$element['position']];
					$local[$obr_count]['NTE_OBR'][] = $tmp[3];
					break;

					case 'OBX':
					$tmp = $this->parser->message[$element['type']][$element['position']];
					$local[$obr_count]['NTE_OBX'][] = $tmp[3];
					break;
				}
				break; // NTE

				case 'ORC': break;

				default:
				if (!$orc_reached) { break; }
				syslog(LOG_INFO, 'HL7 parser| R01 unexpected segment "'.$element['type'].'" at position '.$pos);
				break;
			}
		}

		// Make sure there are no null OBR records
		foreach ($local AS $k => $v) {
			if (!is_array($v['OBR'])) { unset($local[$k]); }
		}

		// Determine which type of anything this is
		if (!is_array($orc)) {
			// Check for OBR[3] to be L^(something) which indicates progress notes
			foreach ($local AS $k => $v) {
				syslog(LOG_INFO, "OBR[3] = ".$v['OBR'][3]);
				if (substr($v['OBR'][3], 0, 2) == 'L^') {
					syslog(LOG_INFO, 'HL7 parser| found progress note');
				}
			}
			die("No orc");
		} else {
			die("orc, assume lab data");
		}

		// Now loop through locals and add them as labs/labresults
		foreach ($local AS $k => $v) {
			// Check for lab existing
			$lab_record = $this->obr_to_lab($v['OBR'], $patient);

			// Populate record
			$lab = array(
				'labpatient' => $patient, // converted from PID segment
				'labprovider' => $this->parser->composite_to_provider($orc[12]),
				'labfiller' => $v['OBR'][21][0],
				'labstatus' => $orc[5],
				'labordercode' => $v['OBR'][4][3],
				'laborderdescrip'=> $v['OBR'][4][4],
				'labcomponentcode' => $v['OBR'][20][3],
				'labcomponentdescrip' => $v['OBR'][20][4],
				'labfillernum' => $v['OBR'][2],
				'labplacernum' => $v['OBR'][3],
				'labtimestamp' => $v['OBR'][7],
				'labresultstatus' => $v['OBR'][25],
				'labnotes' => @join("\n", $v['NTE_OBR'])
			);

			//print "<b>$k</b><br/>\n";
			//print "<pre>"; print_r($lab); print "</pre>\n";
			//print "<hr/>\n";

			// insert or update depending on existence
			if (!$lab_record) {
				$result = $GLOBALS['sql']->query(
					$GLOBALS['sql']->insert_query(
						'labs',
						$lab
					)
				);
				$last_record = $GLOBALS['sql']->lastInsertID( 'labs', 'id' );
				syslog(LOG_INFO, 'HL7 parser| R01 assigned value '.$last_record.' to new OBR record');
			} else {
				$GLOBALS['sql']->query(
					$GLOBALS['sql']->update_query(
						'labs',
						$lab,
						array('id' => $lab_record)
					)
				);
			} // end if !lab_record

			// Loop through all OBX records, put into labresults
			foreach ($v['OBX'] AS $ko => $vo) {
				$obx_query = array (
					'labid' => $last_record,
					'labpatient' => $patient,
					'labobsnote' => @join("\n", $v['NTE_OBX']),
					'labobscode' => $vo[3][4],
					'labobsdescrip' => $vo[3][5],
					'labobsvalue' => ( $vo[5]=='SEE NOTE' ? @join("\n", $v['NTE_OBX']) : $vo[5] ), // OBX 05 / NTE
					'labobsunit' => $vo[6],
					'labobsranges' => $vo[7],
					'labobsabnormal' => $vo[8],
					'labobsstatus' => $vo[11],
					'labobsreported' => $vo[14],
					'labobsfiller' => $v[21][0] // OBX 15 / OBR 21-01
				);
				$result = $GLOBALS['sql']->query(
					$GLOBALS['sql']->insert_query(
						'labresults',
						$obx_query
					)
				);
				$last_obx = $GLOBALS['sql']->lastInsertID( 'labresults', 'id' );
				syslog(LOG_INFO, 'HL7 parser| R01 assigned value '.$last_obx.' to new OBX record for lab '.$last_record);
			} // end foreach obx
		} // end foreach

		//print "<pre>\n";
		//print_r($local);
		//print "</pre>\n";
	} // end method Handle

	public function Type () { return 'ORU'; }

	// Method: obr_to_lab
	//
	//	Convert parsed OBR array into labs record ID
	//
	// Parameters:
	//
	//	$obr - OBR record array
	//
	//	$patient - Patient identifier
	//
	// Returns:
	//
	//	Record id, or false if none is found.
	//
	protected function obr_to_lab ( $obr, $patient ) {
		$query = "SELECT * FROM labs WHERE ".
			"labpatient = '".addslashes($patient)."' AND ".
			"labtimestamp = '".addslashes($obr[7])."' AND ".
			"labordercode = '".addslashes($obr[4][3])."' AND ".
			"labcomponentcode = '".addslashes($obr[20][3])."'";
		$result = $GLOBALS['sql']->queryAll($query);
		if (count($result) >= 1) {
			$r = $result[0];
			return $r['id'];
		}
		return false;
	} // end method obr_to_lab

} // end class Handler_HL7v2_R01

?>
