<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.FixedFormRenderer');

class HCFA1500Renderer extends FixedFormRenderer {

	var $rendered_forms;

	function DefineForm ( ) {
		$this->name = 'HCFA Ins Form';
		$this->description = 'US HCFA 1500';
		$this->loop_number = 4;
		$this->page_length = 68;
		$this->line_length = 80;
		$this->form = array (
			array ( '1', '58', '25', '$insco[name]', '', 'Insco Name'),
			array ( '2', '58', '25', '$insco[line1]', '', 'Insco Addr1'),
			array ( '3', '58', '25', '$insco[line2]', '', 'Insco Addr2'),
			array ( '4', '58', '25', '$insco[city] $insco[state] $insco[zip]', '', 'Insco City/ST/Zip'),
			array ( '8', '60', '10', '$insco[number]', '', 'Insurance coverage ID'),
			array ( '10', '1', '33', '$ptname[last] $ptname[first]  $ptname[middle]', '', 'Patient middle name'),
			array ( '10', '36', '2', '$ptdob[month]', '', 'Patient date of birth (month)'),
			array ( '10', '40', '2', '$ptdob[day]', '', 'Patient date of birth (day)'),
			array ( '10', '43', '4', '$ptdob[year]', '', 'Patient date of birth (year)'),
			array ( '10', '50', '1', '$ptsex[male]', '', 'Is FECA?'),
			array ( '10', '56', '1', '$ptsex[female]', '', 'Gender female?', '1', '1' ),
			array ( '10', '60', '33', '$guarname[last] $guarname[first]  $guarname[middle]', '', 'Full name of guarantor', '33', '33' ),
			array ( '12', '1', '25', '$ptaddr[line1]', '', 'Pat address', '25', '25' ),
			array ( '12', '39', '1', '$ptreldep[self]', '', 'Pat DOB (mm)', '1', '1' ),
			array ( '12', '45', '1', '$ptreldep[spouse]', '', 'Pat DOB (dd)', '1', '1' ),
			array ( '12', '50', '1', '$ptreldep[child]', '', 'Pat DOB (yyyy)', '1', '1' ),
			array ( '12', '56', '1', '$ptreldep[other]', '', 'Is male?', '1', '1' ),
			array ( '12', '60', '25', '$guaraddr[line1]', '', 'Is female?', '25', '25' ),
			array ( '15', '1', '15', '$ptaddr[city]', '', 'Insured name', '15', '15' ),
			array ( '15', '31', '3', '$ptaddr[state]', '', 'Insured fname', '3', '3' ),
			array ( '15', '42', '1', '$ptmarital[single]', '', 'Insured mi', '1', '1' ),
			array ( '15', '49', '1', '$ptmarital[married]', '', 'Pat addr1', '1', '1' ),
			array ( '15', '57', '1', '$ptmarital[other]', '', 'Pat is Self?', '1', '1' ),
			array ( '15', '60', '15', '$guaraddr[city]', '', 'Pat is Spouse?', '15', '15' ),
			array ( '15', '88', '3', '$guaraddr[state]', '', 'Pat is Child?', '3', '3' ),
			array ( '17', '1', '10', '$ptaddr[zip]', '', 'Pat is Other Dependent?', '10', '10' ),
			array ( '17', '18', '16', '$ptphone[full]', '', 'Insured addr1', '16', '16' ),
			array ( '17', '41', '1', '$ptemployed[yes]', '', 'Pat city', '1', '1' ),
			array ( '17', '60', '10', '$guaraddr[zip]', '', 'Pat state', '10', '10' ),
			array ( '17', '78', '14', '$guarphone[full]', '', 'Pat Single?', '14', '14' ),
			array ( '19', '1', '17', '$other[last] $other[first] $other[middle]', '', 'Pat Married?', '17', '17' ),
			array ( '19', '60', '30', '$insco[group]', '', 'Pat Marital-Status Other?', '30', '30' ),
			array ( '22', '1', '17', '$other[group]', '', 'Insured city', '17', '17' ),
			array ( '22', '41', '1', '$related_employment[yes]', '', 'Insured state', '1', '1' ),
			array ( '22', '49', '1', '$related_employment[no]', '', 'Pat zip', '1', '1' ),
			array ( '22', '63', '2', '$guardob[month]', '', 'Pat phone-num', '2', '2' ),
			array ( '22', '67', '2', '$guardob[day]', '', 'Employed?', '2', '2' ),
			array ( '22', '70', '4', '$guardob[year]', '', 'Full-Time Student?', '4', '4' ),
			array ( '22', '81', '1', '$guarsex[male]', '', 'Part-Time Student?', '1', '1' ),
			array ( '22', '89', '1', '$guarsex[female]', '', 'Insured zip', '1', '1' ),
			array ( '24', '2', '2', '$other[month]', '', 'Insured phone-num', '2', '2' ),
			array ( '24', '5', '2', '$other[day]', '', 'Insured2 lname', '2', '2' ),
			array ( '24', '8', '4', '$other[year]', '', 'Insured2 fname', '4', '4' ),
			array ( '24', '18', '1', '$other[male]', '', 'Insured2 mi', '1', '1' ),
			array ( '24', '24', '1', '$other[female]', '', 'Insured groupnum', '1', '1' ),
			array ( '24', '41', '1', '$related_auto[yes]', '', 'Insured2 ins id', '1', '1' ),
			array ( '24', '49', '1', '$related_auto[no]', '', 'Insured2 groupnum', '1', '1' ),
			array ( '24', '45', '2', '$related_auto[state]', '', 'Related to employment? (Y-box)', '2', '2' ),
			array ( '24', '50', '30', '$emp[name]', '', 'Related to employment? (N-box)', '30', '30' ),
			array ( '26', '1', '28', '$box9c[value]', '', 'Insured dob (mm)', '28', '28' ),
			array ( '26', '41', '1', '$related_other[yes]', '', 'Insured dob (dd)', '1', '1' ),
			array ( '26', '49', '1', '$related_other[no]', '', 'Insured dob (yyyy)', '1', '1' ),
			array ( '26', '60', '25', '$insco[name]', '', 'Insured sex (M-box)', '25', '25' ),
			array ( '27', '1', '25', '$other[insconame]', '', 'Insured sex (F-box)', '25', '25' ),
			array ( '27', '52', '1', '$otherins[yes]', '', 'Insured2 dob (mm)', '1', '1' ),
			array ( '27', '57', '1', '$otherins[no]', '', 'Insured2 dob (dd)', '1', '1' ),
			array ( '33', '6', '17', 'SIGNATURE ON FILE', '', 'Insured2 dob (yyyy)', '17', '17' ),
			array ( '33', '42', '10', '$curdate[mmddyyyy]', '', 'Insured2 sex (M-box)', '10', '10' ),
			array ( '33', '66', '17', 'SIGNATURE ON FILE', '', 'Insured2 sex (F-box)', '17', '17' ),
			array ( '34', '2', '2', '$dtfirstsym[mm]', '', 'Auto accident? (Y-box)', '2', '2' ),
			array ( '34', '5', '2', '$dtfirstsym[dd]', '', 'Auto accident (N-box)', '2', '2' ),
			array ( '34', '8', '4', '$dtfirstsym[yy]', '', 'Auto accident state', '4', '4' ),
			array ( '34', '37', '2', '$dtsame[mm]', '', 'Insured eployer or school', '2', '2' ),
			array ( '34', '40', '2', '$dtsame[dd]', '', 'Insured2 employer or school', '2', '2' ),
			array ( '34', '43', '4', '$dtsame[yy]', '', 'Other accident? (Y-box)', '4', '4' ),
			array ( '34', '54', '2', '$dtdisstart[mm]', '', 'Other accident? (N-box)', '2', '2' ),
			array ( '34', '57', '2', '$dtdisstart[dd]', '', 'Insco\'s name', '2', '2' ),
			array ( '34', '61', '4', '$dtdisstart[yy]', '', 'Insco2\'s name', '4', '4' ),
			array ( '34', '68', '2', '$dtdisendt[mm]', '', 'Is there an insco2? (Y-box)', '2', '2' ),
			array ( '34', '71', '2', '$dtdisendt[dd]', '', 'Is there an insco2? (N-box)', '2', '2' ),
			array ( '34', '74', '4', '$dtdisendt[yy]', '', '\'Signature on File\' note', '4', '4' ),
			array ( '35', '1', '25', '$refphy[name]', '', 'curdate', '25', '25' ),
			array ( '35', '28', '8', '$refphy[upin]', '', 'SIGNATURE', '8', '8' ),
			array ( '35', '54', '2', '$dthospst[mm]', '', 'DateFirstSymptom (mm)', '2', '2' ),
			array ( '35', '57', '2', '$dthospst[dd]', '', 'DateFirstSymptom (dd)', '2', '2' ),
			array ( '35', '61', '4', '$dthospst[yy]', '', 'DateFirstSymptom (yyyy)', '4', '4' ),
			array ( '35', '68', '2', '$dthospend[mm]', '', 'DateSimilarIllness (mm)', '2', '2' ),
			array ( '35', '71', '2', '$dthospend[dd]', '', 'DateSimilarIllness (dd)', '2', '2' ),
			array ( '35', '74', '4', '$dthospend[yy]', '', 'DateSimilarIllness (yyyy)', '4', '4' ),
			array ( '36', '1', '2', '$dtlastseen[mm]', '', 'BeganTotalDisability (mm)', '2', '2' ),
			array ( '36', '3', '2', '$dtlastseen[dd]', '', 'BeganTotalDisability (dd)', '2', '2' ),
			array ( '36', '5', '4', '$dtlastseen[yy]', '', 'BeganTotalDisability (yyyy)', '4', '4' ),
			array ( '36', '52', '1', '$outlab[yes]', '', 'EndTotalDisability (mm)', '1', '1' ),
			array ( '36', '57', '1', '$outlab[no]', '', 'EndTotalDisability (dd)', '1', '1' ),
			array ( '36', '65', '8', '$outlab[charge]', 'D', 'EndTotalDisability (yyyy)', '8', '8' ),
			array ( '42', '3', '10', '$ptdiag[1]', 'D', 'ref doc', '10', '10' ),
			array ( '42', '36', '10', '$ptdiag[3]', 'D', 'ref doc id (UPIN)', '10', '10' ),
			array ( '43', '3', '10', '$ptdiag[2]', 'D', 'BeganHospitalization (mm)', '10', '10' ),
			array ( '43', '36', '10', '$ptdiag[4]', 'D', 'BeganHospitalization (dd)', '10', '10' ),
			array ( '44', '60', '20', '$authorized[authnum]', '', 'BeganHospitalization (yyyy)', '20', '20' ),
			array ( '49', '1', '2', '$itemdate_m[1]', '', 'EndHospitalization (mm)', '2', '2' ),
			array ( '49', '5', '2', '$itemdate_d[1]', '', 'BeganHospitalization (dd)', '2', '2' ),
			array ( '49', '8', '2', '$itemdate_sy[1]', '', 'BeganHospitalization (yyyy)', '2', '2' ),
			array ( '49', '12', '2', '$itemdate_m[1]', '', 'date lastseen (mm)', '2', '2' ),
			array ( '49', '15', '2', '$itemdate_d[1]', '', 'date lastseen (dd)', '2', '2' ),
			array ( '49', '19', '2', '$itemdate_sy[1]', '', 'date lastseen (yyyy)', '2', '2' ),
			array ( '49', '22', '2', '$itempos[1]', '', 'outside lab? (Y-box)', '2', '2' ),
			array ( '49', '26', '2', '$itemtos[1]', '', 'outside lab? (N-box)', '2', '2' ),
			array ( '49', '30', '7', '$itemcpt[1]', '', 'lab charges', '7', '7' ),
			array ( '49', '38', '2', '$itemcptmod[1]', '', 'diag code 1', '2', '2' ),
			array ( '49', '50', '7', '$itemdiagref[1]', '', 'diag code 3', '7', '7' ),
			array ( '49', '60', '7', '$itemcharges[1]', '', 'diag code 2', '7', '7' ),
			array ( '49', '71', '1', '$itemunits[1]', '', 'diag code 4', '1', '1' ),
			array ( '50', '1', '2', '$itemdate_m[2]', '', 'authorization num', '2', '2' ),
			array ( '50', '5', '2', '$itemdate_d[2]', '', 'svc date (mm)', '2', '2' ),
			array ( '50', '8', '2', '$itemdate_sy[2]', '', 'svc date (dd)', '2', '2' ),
			array ( '50', '12', '2', '$itemdate_m[2]', '', 'svc date (yy)', '2', '2' ),
			array ( '50', '15', '2', '$itemdate_d[2]', '', 'svc end date (mm)', '2', '2' ),
			array ( '50', '19', '2', '$itemdate_sy[2]', '', 'svc end date (dd)', '2', '2' ),
			array ( '50', '22', '2', '$itempos[2]', '', 'svc end date (yy)', '2', '2' ),
			array ( '50', '26', '2', '$itemtos[2]', '', 'place of service code', '2', '2' ),
			array ( '50', '30', '7', '$itemcpt[2]', '', 'type of service code', '7', '7' ),
			array ( '50', '38', '2', '$itemcptmod[2]', '', 'CPT/HCPCS code', '2', '2' ),
			array ( '50', '50', '7', '$itemdiagref[2]', '', 'CPT modifier', '7', '7' ),
			array ( '50', '60', '7', '$itemcharges[2]', '', 'diag ref', '7', '7' ),
			array ( '50', '71', '1', '$itemunits[2]', '', 'charge for the item', '1', '1' ),
			array ( '52', '1', '2', '$itemdate_m[3]', '', 'units of service', '2', '2' ),
			array ( '52', '5', '2', '$itemdate_d[3]', '', 'EPSDT (huh?) flag (Y-box)', '2', '2' ),
			array ( '52', '8', '2', '$itemdate_sy[3]', '', 'family plan? (Y-box)', '2', '2' ),
			array ( '52', '12', '2', '$itemdate_m[3]', '', 'emergency? (Y-box)', '2', '2' ),
			array ( '52', '15', '2', '$itemdate_d[3]', '', 'reserved for local', '2', '2' ),
			array ( '52', '19', '2', '$itemdate_sy[3]', '', '', '2', '2' ),
			array ( '52', '22', '2', '$itempos[3]', '', '', '2', '2' ),
			array ( '52', '26', '2', '$itemtos[3]', '', '', '2', '2' ),
			array ( '52', '30', '7', '$itemcpt[3]', '', '', '7', '7' ),
			array ( '52', '38', '2', '$itemcptmod[3]', '', '', '2', '2' ),
			array ( '52', '50', '7', '$itemdiagref[3]', '', '', '7', '7' ),
			array ( '52', '60', '7', '$itemcharges[3]', '', '', '7', '7' ),
			array ( '52', '71', '1', '$itemunits[3]', '', '', '1', '1' ),
			array ( '54', '1', '2', '$itemdate_m[4]', '', '', '2', '2' ),
			array ( '54', '5', '2', '$itemdate_d[4]', '', '', '2', '2' ),
			array ( '54', '8', '2', '$itemdate_sy[4]', '', '', '2', '2' ),
			array ( '54', '12', '2', '$itemdate_m[4]', '', '', '2', '2' ),
			array ( '54', '15', '2', '$itemdate_d[4]', '', '', '2', '2' ),
			array ( '54', '19', '2', '$itemdate_sy[4]', '', '', '2', '2' ),
			array ( '54', '22', '2', '$itempos[4]', '', '', '2', '2' ),
			array ( '54', '26', '2', '$itemtos[4]', '', '', '2', '2' ),
			array ( '54', '30', '7', '$itemcpt[4]', '', '', '7', '7' ),
			array ( '54', '38', '2', '$itemcptmod[4]', '', '', '2', '2' ),
			array ( '54', '50', '7', '$itemdiagref[4]', '', '', '7', '7' ),
			array ( '54', '60', '7', '$itemcharges[4]', '', '', '7', '7' ),
			array ( '54', '71', '1', '$itemunits[4]', '', '', '1', '1' ),
			array ( '56', '1', '2', '$itemdate_m[5]', '', '', '2', '2' ),
			array ( '56', '5', '2', '$itemdate_d[5]', '', '', '2', '2' ),
			array ( '56', '8', '2', '$itemdate_sy[5]', '', '', '2', '2' ),
			array ( '56', '12', '2', '$itemdate_m[5]', '', '', '2', '2' ),
			array ( '56', '15', '2', '$itemdate_d[5]', '', '', '2', '2' ),
			array ( '56', '19', '2', '$itemdate_sy[5]', '', '', '2', '2' ),
			array ( '56', '22', '2', '$itempos[5]', '', '', '2', '2' ),
			array ( '56', '26', '2', '$itemtos[5]', '', '', '2', '2' ),
			array ( '56', '30', '7', '$itemcpt[5]', '', '', '7', '7' ),
			array ( '56', '38', '2', '$itemcptmod[5]', '', '', '2', '2' ),
			array ( '56', '50', '7', '$itemdiagref[5]', '', '', '7', '7' ),
			array ( '56', '60', '7', '$itemcharges[5]', '', '', '7', '7' ),
			array ( '56', '71', '1', '$itemunits[5]', '', '', '1', '1' ),
			array ( '58', '1', '2', '$itemdate_m[6]', '', '', '2', '2' ),
			array ( '58', '5', '2', '$itemdate_d[6]', '', '', '2', '2' ),
			array ( '58', '8', '2', '$itemdate_sy[6]', '', '', '2', '2' ),
			array ( '58', '12', '2', '$itemdate_m[6]', '', '', '2', '2' ),
			array ( '58', '15', '2', '$itemdate_d[6]', '', '', '2', '2' ),
			array ( '58', '19', '2', '$itemdate_sy[6]', '', '', '2', '2' ),
			array ( '58', '22', '2', '$itempos[6]', '', '', '2', '2' ),
			array ( '58', '26', '2', '$itemtos[6]', '', '', '2', '2' ),
			array ( '58', '30', '7', '$itemcpt[6]', '', '', '7', '7' ),
			array ( '58', '38', '2', '$itemcptmod[6]', '', '', '2', '2' ),
			array ( '58', '50', '7', '$itemdiagref[6]', '', '', '7', '7' ),
			array ( '58', '60', '7', '$itemcharges[6]', '', '', '7', '7' ),
			array ( '58', '71', '1', '$itemunits[6]', '', '', '1', '1' ),
			array ( '62', '1', '10', '$taxid', '', '', '10', '10' ),
			array ( '62', '22', '1', '$boxein', '', '', '1', '1' ),
			array ( '62', '23', '1', '$boxssn', '', '', '1', '1' ),
			array ( '62', '26', '10', '$ptid', '', '', '10', '10' ),
			array ( '62', '44', '1', 'X', '', '', '1', '1' ),
			array ( '62', '60', '8', '$total_charges', '', '', '8', '8' ),
			array ( '62', '74', '7', '$total_paid', '', '', '7', '7' ),
			array ( '62', '85', '8', '$current_balance', '', '', '8', '8' ),
			array ( '64', '28', '14', '$rendfac[name]', '', '', '14', '14' ),
			array ( '64', '60', '24', '$phy[practice]', '', '', '24', '24' ),
			array ( '65', '28', '25', '$rendfac[addr1]', '', '', '25', '25' ),
			array ( '65', '60', '25', '$phy[addr1]', '', '', '25', '25' ),
			array ( '66', '1', '20', '$phy[name]', '', '', '20', '20' ),
			array ( '66', '28', '30', '$rendfac[city] $rendfac[state] $rendfac[zip]', '', '', '30', '30' ),
			array ( '66', '60', '30', '$phy[city] $phy[state] $phy[zip]', '', '', '30', '30' ),
			array ( '67', '17', '10', '$curdate[mmddyyyy]', '', '', '10', '10' ),
			array ( '67', '28', '15', '$rendfac[ein]', '', '', '15', '15' ),
		);
		$this->variables = array_merge (
			array (
				"ptname",
				"ptdob",
				"ptsex",
				"ptid",
				"ptssn",
				"ptreldep",
				"ptmarital",
				"ptemployed",
				"ptemplpart",
				"ptemplfull",
				"ptaddr",
				"ptphone",
				"ptdiag",
				"phy",
				"refphy",
				"insco",
				"fac",
				"rendfac",
				"curdate",
				"guarname",
				"guaraddr",
				"guardob",
				"guarsex",
				"guarphone",
				"itemdate",
				"itemdate_m",
				"itemdate_d",
				"itemdate_y",
				"itemdate_sy",
				"itemcharges",
				"itemunits",
				"itempos",
				"itemvoucher",
				"itemcpt",
				"itemcptmod",
				"itemtos",
				"itemdiagref",
				"itemauthnum",
				"current_balance",
				"total_charges",
				"total_paid",
				"employment", 
				"related_employment",
				"related_auto",
				"related_other",
				"authorized",
				"taxid",
				"boxein",
				"boxssn"
			),
			$this->variables
		);
	} // end method DefineForm

	function GenerateForms ( $method = 'RenderToBuffer' ) {
		$this->rendered_forms = array ();
		if (!$patients = $this->_GetPatientsToInsuranceBill()) {
			return false;
		}
		foreach ($patients as $__garbage => $p) {
			// Determine distinct coverages
			$coverages = $this->_GetCoverages($p);
			if (!is_array($coverages)) {
				trigger_error(__("No coverages detected!"));
			}
			foreach ($coverages as $__garbage2 => $c) {
				$this->GenerateHCFA($p, $c, $method);
			}
		}

		switch ($method) {
			case 'RenderToBuffer': default:
			return join ('', $this->rendered_forms);
			break;
		}
	} // end method GenerateForms

	function GenerateHCFA ( $parmpatient, $parmcovid, $method = 'RenderToBuffer' ) {
		// Import variables into current scope
		reset ($GLOBALS);
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		foreach ($this->variables AS $k => $v) { global ${$v}; }

		// Get patient information
		$this_patient = CreateObject('FreeMED.Patient', $parmpatient);
		if (!$this_patient) {
			trigger_error('Failed retrieving patient', E_USER_ERROR);
		}

		// Get coverage information
		$this_coverage = CreateObject('FreeMED.Coverage', $parmcovid);
		if (!$this_coverage) {
			trigger_error('No coverage', E_USER_ERROR);
		}

		// Perform date hashing
		$curdate['mmddyy'] = date ('m d y');
		$curdate['mmddyyyy'] = date ('m d Y');
		$curdate['m'] = date('m');
		$curdate['d'] = date('d');
		$curdate['y'] = date('Y');
		$curdate['sy'] = substr($curdate['y'], 2, 2);

		// Get all procedures to bill
		$result = $this_coverage->GetProceduresToBill( $parmpatient, $parmcovid, $this_coverage->local_record['covtype'] );

		// Add to stack
		$this->MakeStack($result, $this->loop_number);
		$this->pat_processed++;
		$this->patient_forms[$this->pat_processed] = $parmpatient;
	} // end method GenerateHCFA

	function CreateFormFromStack ( $stack, $method = 'RenderToBuffer' ) {
		global $debug;
		reset ($GLOBALS);
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		foreach ($this->variables AS $k => $v) { global ${$v}; }
		
		$count = count($stack);
		if ($count < 1) return 0;
		$row = $stack[0];

		$this->_FetchPatient($stack);
		$this->_FetchInsurance($stack);
		$this->_FetchServiceLines($stack);

		$this->rendered_forms[] = $form = $this->$method();
		if ($debug) { print "# of forms generated = ".count($this->rendered_forms)."<br/>\n"; }
	} // end method CreateFormFromStack

	function MakeStack ( $result, $maxloop, $method = 'RenderToBuffer' ) {
		global $display_buffer, $sql;

		if (!$result) return 0;
		if (!$maxloop) return 0;

		$first_procedure = 0;
		$proccount = 0;
		$totprocs = 0;
    		$diagset = CreateObject('FreeMED.diagnosis_set');
		while ($r = $sql->fetch_array ($result)) {
			if ($first_procedure == 0) {
				$prev_key = $this->NewKey($r);
				$diagset = CreateObject('FreeMED.diagnosis_set');
				$first_procedure = 1;
			}

			// keep tally on all bills billed for this patient
			$pat = $r['procpatient'];
			$this->patient_procs[$pat][$totprocs] = $r['id'];
			$totprocs++;

			$cur_key = $this->NewKey($r);

			if (!($diagset->testAddSet ($r['procdiag1'], $r['procdiag2'],
										$r['procdiag3'], $r['procdiag4'])) OR
					($proccount == $maxloop)  OR
					($prev_key != $cur_key) ) {
				if ($prev_key != $cur_key) {
					$prev_key = $cur_key;
					//$display_buffer .= "keychange $r[procphysician]<BR>";
				}

				// reset the diag_set array
				unset ($diagset);
				unset ($procstack);
				$proccount = 0;
				$diagset = CreateObject('FreeMED.diagnosis_set');
				$test_AddSet = $diagset->testAddSet (
					$r['procdiag1'], 
					$r['procdiag2'], 
					$r['procdiag3'], 
					$r['procdiag4']
				);
				if (!$test_AddSet) {
					$display_buffer .= "AddSet failed!!";
					template_display();
				}

			} 

			$procstack[$proccount] = $r;
			$proccount++;
		} // end of looping for all charges

		// check for last record
		if ($proccount > 0) {
			//call_user_method($callbackfunc,$callbackobj,$procstack);
			$this->CreateFormFromStack($procstack, $method);
		}

		// Render a page, and place it on the form stack
		//$this->rendered_forms[] = 
		//	$this->CreateFormFromStack($procstack, $method);
	} // end method MakeStack

	// Create billing control break key
	function NewKey($row) {
		global $display_buffer;

		// If any of these fields change while processing a
        	// bill we need to cut a new bill

		$pos = $row["procpos"];
		$doc = $row["procphysician"];
		$ref = $row["procrefdoc"];
		$auth = $row["procauth"];
		$eoc = $row["proceoc"];
		$clmtp = $row["procclmtp"];
		$cov1 = $row["proccov1"];
		$cov2 = $row["proccov2"];

		$date = $row["procdt"];
		$date = str_replace("-","",$date);
		$datey = substr($date,0,4);
		$datem = substr($date,4,2);
		$newkey = $pos.$doc.$ref.$eoc.$clmtp.
			$auth.$cov1.$cov2.$datey.$datem;
		return $newkey;
	} // end method NewKey

	// Fetch information classes
	// TODO: These should be moved to a higher class level than this...
	// 	they are only here temporarily!

	function _FetchInsurance ( $stack ) {
		foreach ($this->variables AS $k => $v) { global ${$v}; }
		global $sql;

		$row = $stack[0];
		if (!$row) return false;

		if ($row['proccurcovtp'] == PRIMARY) {
			$this_coverage = CreateObject('FreeMED.Coverage', $row[proccurcovid]);
			if (!$this_coverage) {
				trigger_error(__("No coverage"), E_USER_ERROR);
			}

			$this_insco = $this_coverage->covinsco;
			if (!$this_insco) {
				trigger_error(__("Insurance data fetch failed"), E_USER_ERROR);
			}
			if (!is_object($this_insco)) {
				trigger_error(__("Insurance company not object"), E_USER_ERROR);
			}

			$insco[number]     = $this_coverage->covpatinsno;
			$insco[group]      = $this_coverage->covpatgrpno;
			$insco[name]       = ( (empty($this_insco->inscoalias)) ? $this_insco->insconame : $this_insco->inscoalias);
			$insco[line1]      = $this_insco->local_record[inscoaddr1];
			$insco[line2]      = $this_insco->local_record[inscoaddr2];
			$insco[city]       = $this_insco->local_record[inscocity];
			$insco[state]      = $this_insco->local_record[inscostate];
			$insco[zip]        = $this_insco->local_record[inscozip];
		} else {
			$this->BillSecondary($stack);

			$this_coveragep = CreateObject('FreeMED.Coverage', $row[proccov1]);
		        if (!$this_coveragep) {
				trigger_error(__("No primary coverage"), E_USER_ERROR);
			}

			$this_inscop = $this_coveragep->covinsco;
			if (!$this_inscop) {
				trigger_error(__("Insurance data fetch failed"), E_USER_ERROR);
			}
			if (!is_object($this_inscop)) {
				trigger_error(__("Insurance company not object"), E_USER_ERROR);
			}

			$this_coverage = CreateObject('FreeMED.Coverage', $row[proccurcovid]);
			if (!$this_coverage) {
				trigger_error(__("No coverage"), E_USER_ERROR);
			}

			$this_insco = $this_coverage->covinsco;
			if (!$this_insco) {
				trigger_error(__("Insurance data fetch failed"), E_USER_ERROR);
			}
			if (!is_object($this_insco)) {
				trigger_error(__("Insurance company not object"), E_USER_ERROR);
			}

			$insco[number]     = $this_coverage->covpatinsno;
			$insco[group]      = $this_coverage->covpatgrpno;
			$insco[name]       = ( (empty($this_insco->inscoalias)) ? $this_insco->insconame : $this_insco->inscoalias);
			$insco[line1]      = $this_insco->local_record[inscoaddr1];
			$insco[line2]      = $this_insco->local_record[inscoaddr2];
			$insco[city]       = $this_insco->local_record[inscocity];
			$insco[state]      = $this_insco->local_record[inscostate];
			$insco[zip]        = $this_insco->local_record[inscozip];

			// show primary as other
		}
	} // end method _FetchInsurance

	function _FetchPatient ( $stack ) {
		foreach ($this->variables AS $k => $v) { global ${$v}; }
		$row = $stack[0];
		$this_patient = CreateObject('FreeMED.Patient', $row['procpatient']);
		$this_coverage = CreateObject('FreeMED.Coverage', $row['proccurcovid']);

		// Render out form variables
		$ptname[last] = $this_patient->ptlname;
		$ptname[first] = $this_patient->ptfname;
		$ptname[middle] = $this_patient->ptmname;
		$ptdob[full] = $this_patient->ptdob;
		$ptdob[month] = substr($ptdob[full], 5, 2);
		$ptdob[day] = substr($ptdob[full], 8, 2);
		$ptdob[year] = substr($ptdob[full], 0, 4);
		$ptdob[syear] = substr($ptdob[full], 2, 2);
		$ptdob[mmddyy] = $ptdob[month].$ptdob[day].$ptdob[syear];
		$ptdob[mmddyyyy] = $ptdob[month].$ptdob[day].$ptdob[year];
		$ptsex[male] = ( ($this_patient->ptsex=='m') ? 'X' : ' ');
		$ptsex[female] = ( ($this_patient->ptsex=='f') ? 'X' : ' ');
		$ptsex[trans] = ( ($this_patient->ptsex=='t') ? 'X' : ' ');
		$ptssn = $this_patient->local_record['ptssn'];
		$ptid = $this_patient->local_record['ptid'];

		// Relationship to guarantor
		$ptreldep[self] = ( (($this_coverage->covreldep=='S') or
			($this_coverage->covdep==0)) ? 'X' : ' ');
		$ptreldep[child] = ( ($this_coverage->covreldep=='C') ?
			'X' : ' ' );
		$ptreldep[spouse] = ( (($this_coverage->covreldep=='H') or
			($this_coverage->covreldep=='W')) ? 'X' : ' ' );
		$ptreldep[husband] = ( ($this_coverage->covreldep=='H') ?
			'X' : ' ' );
		$ptreldep[wife] = ( ($this_coverage->covreldep=='W') ?
			'X' : ' ' );
		$ptreldep[other] = ( ($this_coverage->covreldep=='O') ?
			'X' : ' ' );
		
		// Marital Status
		$ptmarital[single] = ( ($this_patient->ptmarital=='single') ?
			'X' : ' ' );
		$ptmarital[married] = ( ($this_patient->ptmarital=='married') ?
			'X' : ' ' );
		$ptmarital[divorced] = ( ($this_patient->ptmarital=='divorced') ?
			'X' : ' ' );
		$ptmarital[separated] = ( ($this_patient->ptmarital=='separated') ?
			'X' : ' ' );
		$ptmarital[other] = ( (($this_patient->ptmarital=='separated')
			or ($this_patient->ptmarital=='divorced')) ?  'X' : ' ' );

		// Employment status
		$ptemployed[yes] = ( ($this_patient->ptempl == 'y') ?
			'X' : ' ' );
		$ptemplpart[yes] = ( ($this_patient->ptempl == 'p') ?
			'X' : ' ' );
		$ptemplfull[yes] = ( ($this_patient->ptempl == 'f') ?
			'X' : ' ' );

		// Address information
		$ptaddr[line1] = $this_patient->local_record['ptaddr1'];
		$ptaddr[line2] = $this_patient->local_record['ptaddr2'];
		$ptaddr[city] = $this_patient->local_record['ptcity'];
		$ptaddr[state] = $this_patient->local_record['ptstate'];
		$ptaddr[zip] = $this_patient->local_record['ptzip'];
		$ptaddr[country] = $this_patient->local_record['ptcountry'];
		$ptphone[full] = $this_patient->local_record['pthphone'];

		// Check for self insured (if so clear guarantor fields)
		if ($this_coverage->covdep == 0) {
			$guarname[last] =
			$guarname[first] =
			$guarname[middle] =
			$guarname[full] =
			$guarname[month] =
			$guarname[dob] =
			$guarname[year] =
			$guarname[male] =
			$guarname[female] =
			$guarname[trans] =
			$guarname[line1] =
			$guarname[line2] =
			$guarname[city] =
			$guarname[state] =
			$guarname[zip] =
			$guarphone[full] = '';
		}

		if ($this_coverage->covdep > 0) {
			$guarantor = CreateObject('FreeMED.Guarantor', $this_coverage->id);
			$guarname[last] = $guarantor->guarlname;
			$guarname[first] = $guarantor->guarfname;
			$guarname[middle] = $guarantor->guarmname;
			$guardob[full] = $guarantor->guardob;
			$guardob[month] = substr($guarantor->guardob, 5, 2);
			$guardob[day] = substr($guarantor->guardob, 8, 2);
			$guardob[year] = substr($guarantor->guardob, 0, 4);
			$guarsex[male] = ( ($guarantor->guarsex=='m') ?
				'X' : ' ' );
			$guarsex[female] = ( ($guarantor->guarsex=='f') ?
				'X' : ' ' );
			$guarsex[trans] = ( ($guarantor->guarsex=='t') ?
				'X' : ' ' );

			// Address information
			if ($guarantor->guarsame) {
				$guaraddr = $pataddr;
			} else {
				$guaraddr[line1] = $guarantor->guaraddr1;
				$guaraddr[line2] = $guarantor->guaraddr2;
				$guaraddr[city] = $guarantor->guarcity;
				$guaraddr[state] = $guarantor->guarstate;
				$guaraddr[zip] = $guarantor->guarzip;
			}
		}

		// Process episode of care
		$eocs = explode (":", $row[proceoc]);
		if (!$eocs[0]) {
			// TODO: error
		}

		if ($eocs[0]) {
			$eoc = freemed::get_link_rec($eocs[0], 'eoc');
			$employment = $eoc[eocrelemp];
			$related_employment[yes] = ( ($employment=='yes') ?
				'X' : ' ' );
			$related_employment[no] = ( ($employment=='no') ?
				'X' : ' ' );
			$auto = $eoc[eocrelauto];
			$related_auto[yes] = ( ($auto=='yes') ?
				'X' : ' ' );
			$related_auto[no] = ( ($auto=='no') ?
				'X' : ' ' );
			$related_auto[state] = ( ($auto=='yes') ?
				$eoc[eocrelautostpr] : '  ' );
			$other = $eoc[eocrelother];
			$related_other[yes] = ( ($other=='yes') ?
				'X' : ' ' );
			$related_other[no] = ( ($other=='no') ?
				'X' : ' ' );
		} else {
			// Use defaults if no Episode of Care present
			$related_employment[yes] = ' ';
			$related_employment[no] = 'X';
			$related_auto[yes] = ' ';
			$related_auto[no] = 'X';
			$related_auto[state] = '  ';
			$related_other[yes] = ' ';
			$related_other[no] = 'X';
		}
	} // end method _FetchPatient

	function _FetchServiceLines ( $stack ) {
		foreach ($this->variables AS $k => $v) { global ${$v}; }
		global $sql;

		$row = $stack[0];

		$this_coverage = CreateObject('FreeMED.Coverage', $row[proccurcovid]);
		if (!$this_coverage) {
			trigger_error(__("No coverage"), E_USER_ERROR);
		}

		$this_insco = $this_coverage->covinsco;
		if (!$this_insco) {
			trigger_error(__("Insurance data fetch failed"), E_USER_ERROR);
		}

		// not the object just the id;
		$cur_insco = $this_coverage->local_record['covinsco'];

		// here we should have date of first symptom, injury or last mestral
		// fix me

		// doctor link/information
		$this_physician = CreateObject('FreeMED.Physician', $row['procphysician']);
		$phy[name] = $this_physician->fullName();
		$phy[title] = $this_physician->local_record["phytitle"];
		$phy[practice] = $this_physician->practiceName();
		if (empty($phy[practice])) $phy[practice] = $phy[name];
		$phy[addr1] = $this_physician->local_record["phyaddr1a"];
		$phy[addr2] = $this_physician->local_record["phyaddr2a"];
		$phy[city] = $this_physician->local_record["phycitya" ];
		$phy[state] = $this_physician->local_record["phystatea"];
		$phy[zip] = $this_physician->local_record["phyzipa"  ];
		$phy[phone] = $this_physician->local_record["phyphonea"];

		// pull physician # for insco
		$insco[phyid] = ( ($this_insco->local_record[inscogroup] < 1) ?
			"" :
			($this_physician->getMapId($this_insco->local_record[inscogroup]))
		);

		if ($_SESSION['default_facility'] > 0) {
			$dfltfac = freemed::get_link_rec($_SESSION['default_facility'], 'facility');
			$taxid = $dfltfac[psrein];
			$boxein = 'X';
			$boxssn = '';
		} else {
			$taxid = $this_physician->local_record["physsn"];
			$boxssn = "X";
			$boxein = "";
		}


		$this_facility = freemed::get_link_rec ($row['procpos'], 'facility');
		$pos = $this_facility['psrpos'];
		$cur_pos = freemed::get_link_rec($pos, 'pos');
		$pos = $cur_pos['posname'];
		if ($pos==0) $pos=11;
		//$display_buffer .= "pos $pos<BR>";
		if ($pos > 12) { // if done out of office
			$rendfac[name] = $this_facility[psrname];
			$rendfac[addr1] = $this_facility[psraddr1];
			$rendfac[city] = $this_facility[psrcity];
			$rendfac[state] = $this_facility[psrstate];
			$rendfac[zip] = $this_facility[psrzip];
			$rendfac[ein] = $this_facility[psrein];
		} else {
			$rendfac[name] = "SAME";
			$rendfac[addr1] = "";
			$rendfac[city] = "";
			$rendfac[state] = "";
			$rendfac[zip] = "";
			$rendfac[ein] = "";
		}

		if ($row[procrefdoc]>0) {
			$refdoc  = CreateObject('FreeMED.Physician', $row[procrefdoc]);
			$refphy[upin] = $refdoc->local_record[phyupin];
			$refphy[name] = $refdoc->local_record[phyfname].", ".
				$refdoc->local_record[phylname];
		} else {
			$refphy[upin] = "";
			$refphy[name] = "";
		}

		$this_auth = freemed::get_link_rec ($row['procauth'], 'authorizations');
		$authorized[authnum] = $this_auth[authnum];
		if (!$this_auth[authnum]) {
			//$display_buffer .= "<B>Warning: Procedure not Authorized!!</B><BR>\n";
			//flush();
		} elseif (!date_in_range($cur_date, $this_auth['authdtbegin'], $this_auth['authdtend'])) {
			//$display_buffer .= "<B>Warning: Authorization $this_auth[authnum] has expired!!</B><BR>\n";
			//flush();

		}

		// zero current number of charges
		$number_of_charges = 0; $total_charges = 0; $total_paid = 0;
		// and zero the arrays
		for ($j=1;$j<=$this->formno[ffloopnum];$j++) {
			$itemdate[$j]   = $itemdate_m[$j]  = $itemdate_d[$j]  =
			$itemdate_y[$j] = $itemdate_sy[$j] = $itemcharges[$j] =
			$itemunits[$j]  = $itempos[$j]     = $itemvoucher[$j] =
			$itemcpt[$j]    = $itemcptmod[$j]  = $itemtos[$j]     =
			$itemdiagref[$j] = $itemauthnum[$j] = "";
		}
		$diag_set = CreateObject('FreeMED.diagnosis_set');

		$count = count($stack);
		for ($i=0;$i<$count;$i++) {	
			$row = $stack[$i];
			$diag_set->testAddSet($row[procdiag1], $row[procdiag2],
                                    $row[procdiag3], $row[procdiag4]);
		}
		
		for ($i=0;$i<$count;$i++) {
			$row = $stack[$i];
			$number_of_charges++;

			$cur_cpt = freemed::get_link_rec ($row[proccpt], "cpt");
        		$tos_stack = fm_split_into_array ($cur_cpt[cpttos]);
        		$this_tos = ( ($tos_stack[$cur_insco] < 1) ?
                		$cur_cpt[cptdeftos] :
                		$tos_stack[$cur_insco] );
			$itemdate    [$number_of_charges] = $row[procdt];
			$itemdate_m  [$number_of_charges] = substr($row[procdt], 5, 2);
			$itemdate_d  [$number_of_charges] = substr($row[procdt], 8, 2);
			$itemdate_y  [$number_of_charges] = substr($row[procdt], 0, 4);
			$itemdate_sy [$number_of_charges] = substr($row[procdt], 2, 2);
			$itemcharges [$number_of_charges] = ($row[procamtallowed]) ?
				bcadd($row[procamtallowed], 0, 2) : bcadd($row[procbalorig], 0, 2);
			$itemunits   [$number_of_charges] = bcadd($row[procunits],   0, 0);
			//$itempos     [$number_of_charges] = "11";  // KLUDGE!! KLUDGE!!
			$itempos     [$number_of_charges] = $pos;
			$itemvoucher [$number_of_charges] = $row[procvoucher];
			$itemcpt     [$number_of_charges] = $cur_cpt[cptcode];
			$itemtos     [$number_of_charges] =
			  freemed::get_link_field ($this_tos, "tos", "tosname");
			$itemcptmod  [$number_of_charges] =
			  freemed::get_link_field ($row[proccptmod], "cptmod", "cptmod");
			$itemdiagref [$number_of_charges] =
			  $diag_set->xrefList ($row[procdiag1], $row[procdiag2],
								   $row[procdiag3], $row[procdiag4]);
			$itemauthnum [$number_of_charges] = $this_auth [authnum];
			$total_paid    += $row[procamtpaid];
			$total_charges += $itemcharges[$number_of_charges];
		}

		$ptdiag = $diag_set->getStack(); // get pt diagnoses
		$current_balance = bcadd($total_charges - $total_paid, 0, 2);
		$total_charges   = bcadd($total_charges, 0, 2);
		$total_paid      = bcadd($total_paid,    0, 2);
	} // end method _FetchServiceLines

} // end class HCFA1500Renderer

?>
