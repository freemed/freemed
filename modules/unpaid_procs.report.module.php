<?php
 // $Id$
 // desc: patient demographic report module
 // lic : LGPL

LoadObjectDependency('FreeMED.ReportsModule');

class UnpaidProceduresReport extends ReportsModule {

	var $MODULE_NAME = "Unpaid Procedures Report";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function UnpaidProceduresReport () {
		$this->ReportsModule();
	} // end constructor UnpaidProceduresReport

	// function "view" is used to show a form that would be submitted to
	// generate the report shown in "display".
	
	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

   		// produce a list only. Don't actually process any bills
        $query = "SELECT procrec.procbilled,procrec.procdtbilled,
                  procrec.procbalcurrent,patient.ptlname,patient.ptfname,
                  patient.id
                  FROM procrec,patient 
                  WHERE procrec.procbalcurrent > '0' 
                    AND procrec.procpatient = patient.id
                  ORDER BY patient.ptlname
                 ";
        $result = $sql->query($query);
 		if (!$result) {
			$display_buffer .= __("ERROR");
			template_display();
		}
		if ($result) {
    		$display_buffer .= "
      		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 WIDTH=\"100%\">
      		<TR>
       		<TD><B>".__("Name")."</B></TD>
       		<TD><B>".__("Payments")."</B></TD>
       		<TD><B>".__("Ledger")."</B></TD>
       		<TD><B>".__("Billed")."</B></TD>
       		<TD><B>".__("Date Billed")."</B></TD>
	        <TD><B>".__("Balance")."</B></TD>
      		</TR>
    		"; // header of box
 			$total_unpaid = 0.00;
                // setup control break
                $prev_patient="$$";
                $patient_balance = 0.00;
                $billed = 1;
			while ($r = $sql->fetch_array($result)) 
			{
      				$id = $r["id"        ] ;
      				$ptlname  = $r["ptlname"  ] ;
      				$ptfname  = $r["ptfname"  ] ;
				$procbalcurrent = $r["procbalcurrent"];
				$procbilled = $r["procbilled"];
				$procdtbilled = $r["procdtbilled"];
				if ($prev_patient=="$$") // first time
				{
					$prev_patient = $r['id'];
					$prev_lname = $ptlname;
					$prev_fname = $ptfname;
					$oldest_bill = "0000-00-00";
				}

        		// alternate the bar color

				if ($r['id'] != $prev_patient)
				{
      				$display_buffer .= "
        				<tr CLASS=\"".freemed_alternate()."\">
        				<td><a HREF=
         				\"manage.php?id=$prev_patient\"
         				>$prev_lname, $prev_fname</a></td>
                  		<td><a HREF=
                  		\"$this->page_name?id=$prev_patient&patient=$prev_patient&module=PaymentModule&action=addform\"
                  		><small>".__("View/Manage")."</small></a></td>
                  		<td><a HREF=
                  		\"$this->page_name?patient=$prev_patient&module=PaymentModule&action=addform&viewaction=unpaidledger\"
                  		><small>".__("Patient Ledger")."</small></a></td>
      					";
					if (!$billed) {
						$display_buffer .= "<td CLASS=\"cell_hilite\">&nbsp;NO&nbsp;</td>\n";
					} else {
						$display_buffer .= "<td>YES</td>\n";
					}
					$display_buffer .= "<td>$oldest_bill</td>\n";
					$total_unpaid += $patient_balance;  // add to grand total
                                $display_buffer .= "<td ALIGN=\"RIGHT\">".bcadd($patient_balance,0,2)."</td>\n";
	
					// reset control break

					$patient_balance = 0.00;				
					//$oldest_bill = $procdtbilled;
					$oldest_bill = "0000-00-00";
					$billed = 1;
					$prev_patient = $id;
					$prev_lname = $ptlname;
					$prev_fname = $ptfname;
					$display_buffer .= "</TR>";
				}	
				$patient_balance += $procbalcurrent;
				if ($procdtbilled != "0000-00-00")
				{
					if ($oldest_bill == "0000-00-00") {
						$oldest_bill = $procdtbilled;
					} elseif ($procdtbilled < $oldest_bill) {
						$oldest_bill = $procdtbilled;
					}
				}

				// it only takes 1 unbilled to show NO
				if (!$procbilled) {
					$billed = 0;
				}

    		} // while there are no more
			// process last record from control break;
            $display_buffer .= "
               <tr CLASS=\"".freemed_alternate()."\">
                  <td><a HREF=
                  \"manage.php?id=$id\"
                  >$prev_lname, $prev_fname</a></td>
                  <td><a HREF=
                  \"$this->page_name?id=$prev_patient&patient=$prev_patient&module=PaymentModule&action=addform\"
                  ><small>".__("View/Manage")."</small></a></td>
                 <td><a HREF=
                 \"$this->page_name?patient=$prev_patient&module=PaymentModule&action=addform&viewaction=unpaidledger\"
                 ><small>".__("Patient Ledger")."</small></a></td>
                  ";
                  if (!$billed) {
                      $display_buffer .= "<td CLASS=\"cell_hilite\">&nbsp;NO&nbsp;</td>\n";
                  } else {
                      $display_buffer .= "<td>YES</td>\n";
		  }
                  $display_buffer .= "<td>$oldest_bill</td>\n";
                  $total_unpaid += $patient_balance;  // add to grand total
                  $display_buffer .= "<td ALIGN=\"RIGHT\">".bcadd($patient_balance,0,2)."</td>\n";
			// end control break

		// process totals.
             $display_buffer .= "<tr>
			<td><b>".__("Total")."</b></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td ALIGN=\"RIGHT\" CLASS=\"cell_hilite\">".bcadd($total_unpaid,0,2)."</td>
			</TR>
			";

	 		$display_buffer .= "</TABLE>\n"; 

		} // end of result set

	} // end view function

} // end class ReportsModule

register_module ("UnpaidProceduresReport");

?>
