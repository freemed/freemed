<?php
// $Id$
// desc: intake module. produce intake report for this patient
// lic : GPL, v2

if (!defined("__INTAKE_EMRREPORT_MODULE_PHP__")) {

    define (__INTAKE_EMRREPORT_MODULE_PHP__, true);

    // class testModule extends freemedModule
    class IntakeReportModule extends freemedEMRReportModule {

        // override variables
        var $MODULE_NAME = "Intake Report";
        var $MODULE_VERSION = "0.1";

		// contructor method
        function IntakeReportModule ($nullvar = "") {
            // call parent constructor
            $this->freemedEMRReportModule($nullvar);
        } // end function testModule


		function addform()
		{
			// this function is called when the ADD button is
			// clicked on the patient menu
			$this->view();
		}
		function view()
		{
			// this function is called when the View button is
			// clicked on the patient menu

			global $patient,$sql,$pt, $default_facility;
			$this_patient = new Patient($patient);

			echo "<HTML><BODY BGCOLOR=\"#FFFFFF\">";
			$sched_result = $sql->query("SELECT MAX(id) AS id FROM scheduler WHERE calpatient='$patient'");
			if ($sql->num_rows($sched_result) > 0)
			{
				$sched_row = $sql->fetch_array($sched_result);
				$schid = $sched_row[id];
				//echo "schid $schid<BR>";
				$sched_row = freemed_get_link_rec($schid, "scheduler");
				$pt[calldate] = $sched_row[caldateof];
				$calminute = $sched_row["calminute"];
				$calhour = $sched_row["calhour"];
				//if ($calminute==0) $calminute="00";

				// time checking/creation if/else clause
				//if ($sched_row["calhour"]<12)
				//	$_time = $sched_row["calhour"].":".$calminute." AM";
				//elseif ($sched_row["calhour"]==12)
				//	$_time = $sched_row["calhour"].":".$calminute." PM";
				//else
				//	$_time = ($r["calhour"]-12).":".$calminute." PM";
				$pt[calltime] = fc_get_time_string($calhour,$calminute);
			}
			else
			{
				$pt[calldate] = "N/A";
				$pt[calltime] = "N/A";
			
			}

			if ($default_facility > 0)
			{
				$fac_row = 0;
				$fac_row = freemed_get_link_rec($default_facility,"facility");
				if ($fac_row)
				{
					$pt[facility] = $fac_row[psrname]." "."Intake Report";
					$facaddr = $fac_row[psraddr1];
					$facaddr2 = $fac_row[psraddr2];
					if (!empty($facaddr2))
						$facaddr = $facaddr.", ".$facaddr2;

					$facaddr = $facaddr." ".$fac_row[psrcity].", ".$fac_row[psrstate]." ".$fac_row[psrzip];
					$pt[facaddr] = $facaddr;
					$facphone = $fac_row[psrphone];
					$facfax = $fac_row[psrfax];
					if (!empty($facfax))
						$facphone = $facphone." FAX ".$facfax;
					$pt[facphone] = $facphone;
				}
			
			}

			$pt[callid] = $this_patient->local_record[ptid];
			$pt[first] = $this_patient->local_record[ptfname];
			$pt[last] = $this_patient->local_record[ptlname];
			$sex = $this_patient->local_record[ptsex];
			$pt[sex] = strtoupper($sex);
			$marital = $this_patient->local_record[ptmarital];
			if ($marital == "married")
				$marital = "Y";
			else
				$marital = "N";
			$pt[marital] = $marital;
			$pt[addr1] = $this_patient->local_record[ptaddr1];
			$pt[addr2] = $this_patient->local_record[ptaddr2];
			$pt[city] = $this_patient->local_record[ptcity];
			$pt[state] = $this_patient->local_record[ptstate];
			$pt[zip] = $this_patient->local_record[ptzip];
			$pt[homephone] = $this_patient->local_record[pthphone];
			$pt[workphone] = $this_patient->local_record[pthwhone];
			$ptdoc = $this_patient->local_record[ptdoc];
			$ptrefdoc = $this_patient->local_record[ptrefdoc];
			$ptpcp = $this_patient->local_record[ptpcp];  // primary care physician

			if ($ptpcp > 0)    // primary 
			{
				$this_primdoc = new Physician($ptpcp);
				$pt[docname] = "Dr. ".$this_primdoc->fullname();
				$pt[docaddr1] = $this_primdoc->local_record[phyaddr1a];
				$pt[doccity] = $this_primdoc->local_record[phycitya];
				$pt[docstate] = $this_primdoc->local_record[phystatea];
				$pt[doczip] = $this_primdoc->local_record[phyzipa];
				$pt[docphone] = $this_primdoc->local_record[phyphonea];
				$pt[docfax] = $this_primdoc->local_record[phyfaxa];
			}

			if ($ptrefdoc > 0)  // referring doc
			{
				$this_refdoc = new Physician($ptrefdoc);
				$pt[refdocname] = "Dr. ".$this_refdoc->fullname();

				// below not needed at this time.
				//$pt[docaddr1] = $this_refdoc->local_record[phyaddr1a];
				//$pt[doccity] = $this_refdoc->local_record[phycitya];
				//$pt[docstate] = $this_refdoc->local_record[phystatea];
				//$pt[doczip] = $this_refdoc->local_record[phyzipa];
				//$pt[docphone] = $this_refdoc->local_record[phyphonea];
				//$pt[docfax] = $this_refdoc->local_record[phyfaxa];
			}

			if ($ptdoc > 0)  // in house doc
			{
				$this_indoc = new Physician($ptdoc);
				$pt[interdocname] = "Dr. ".$this_indoc->fullname();

				// below not needed at this time.
				//$pt[docaddr1] = $this_indoc->local_record[phyaddr1a];
				//$pt[doccity] = $this_indoc->local_record[phycitya];
				//$pt[docstate] = $this_indoc->local_record[phystatea];
				//$pt[doczip] = $this_indoc->local_record[phyzipa];
				//$pt[docphone] = $this_indoc->local_record[phyphonea];
				//$pt[docfax] = $this_indoc->local_record[phyfaxa];
			}

			$covid = fm_verify_patient_coverage($patient); // get primary;
			if ($covid > 0)
			{
				$coverage = new Coverage($covid);
				//$insco = $coverage->insco;
				$pt[insname] = $coverage->covinsco->local_record[insconame];
				$pt[insphone] = $coverage->covinsco->local_record[inscophone];
				$pt[insaddr1] = $coverage->covinsco->local_record[inscoaddr1];
				$pt[inscity] = $coverage->covinsco->local_record[inscocity];
				$pt[insstate] = $coverage->covinsco->local_record[inscostate];
				$pt[inszip] = $coverage->covinsco->local_record[inscozip];

				$pt[patdob] = $this_patient->local_record[ptdob]; 
				$pt[patssn] = $this_patient->local_record[ptssn]; 

				if ($coverage->covdep > 0)
				{
					$guarantor = new Guarantor($coverage->covdep);
					// there is a guarantor
					$pt[ispatient] = " ";
					$pt[isother] = "X";
					$pt[insuredname] = $guarantor->guarfname." ".$guarantor->guarlname; 
					$pt[insureddob] = $guarantor->guardob;
				}
				else
				{
					// patient is insured
					$pt[isother] = " ";
					$pt[ispatient] = "X";
				}

				$pt[insid] = $coverage->covpatinsno;
				$pt[insgrp] = $coverage->covpatgrpno;



			}		

			$buffer = render_fixedForm(5);
			echo "<PRE>".$buffer."</PRE>";
			echo "</BODY></HTML>";
			

		}

	} // end intake report module

	register_module("IntakeReportModule");
} // end module define

	

