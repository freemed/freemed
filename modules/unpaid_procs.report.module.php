<?php
 // $Id$
 // desc: patient demographic report module
 // lic : LGPL

if (!defined("__UNPAID_PROCEDURES_REPORT_MODULE_PHP__")) {

class unpaidProceduresReport extends freemedReportsModule {

	var $MODULE_NAME = "Unpaid Procedures Report";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	function unpaidProceduresReport () {
		$this->freemedReportsModule();
	} // end constructor unpaidProceduresReport

	// function "view" is used to show a form that would be submitted to
	// generate the report shown in "display".
	
	function view()
	{
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
 		if (!$result)
			DIE("Error<BR>");
		if ($result)
		{
     		$_alternate = freemed_bar_alternate_color ();

    		echo "
      		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 WIDTH=100%>
      		<TR>
       		<TD><B>"._("Name")."</B></TD>
       		<TD><B>"._("Payments")."</B></TD>
       		<TD><B>"._("Ledger")."</B></TD>
       		<TD><B>"._("Billed")."</B></TD>
       		<TD><B>"._("Date Billed")."</B></TD>
            <TD><B>"._("Balance")."</B></TD>
      		</TR>
    		"; // header of box
 			$total_unpaid = 0.00;
                // setup control break
                $prev_patient="$$";
                $patient_balance = 0.00;
                $billed = 1;
			while ($r = $sql->fetch_array($result)) 
			{

      			$id        = $r["id"        ] ;
      			$ptlname  = $r["ptlname"  ] ;
      			$ptfname  = $r["ptfname"  ] ;
				$procbalcurrent = $r["procbalcurrent"];
				$procbilled = $r["procbilled"];
				$procdtbilled = $r["procdtbilled"];
				if ($prev_patient=="$$") // first time
				{
					$prev_patient = $id;
					$prev_lname = $ptlname;
					$prev_fname = $ptfname;
					$oldest_bill = "0000-00-00";
				}

        		// alternate the bar color

				if ($id != $prev_patient)
				{
      				echo "
        				<TR BGCOLOR=\"".
     					($_alternate = freemed_bar_alternate_color ($_alternate))."\">
        				<TD><A HREF=
         				\"manage.php?$_auth&id=$prev_patient\"
         				>$prev_lname, $prev_fname</A></TD>
                  		<TD><A HREF=
                  		\"$this->page_name?$_auth&id=$prev_patient&patient=$prev_patient&module=PaymentModule&action=addform\"
                  		><FONT SIZE=-1>"._("View/Manage")."</FONT></A></TD>
                  		<TD><A HREF=
                  		\"$this->page_name?_auth=$auth&patient=$prev_patient&module=PaymentModule&action=addform&viewaction=unpaidledger\"
                  		><FONT SIZE=-1>"._("Patient Ledger")."</FONT></A></TD>
      					";
					if (!$billed)
						echo "<TD> <FONT COLOR=#ff0000>&nbspNO&nbsp</FONT></TD>";
					else
						echo "<TD>YES</TD>";
					echo "<TD>$oldest_bill</TD>";
					$total_unpaid += $patient_balance;  // add to grand total
                                echo "<TD ALIGN=RIGHT>".bcadd($patient_balance,0,2)."</TD>";
	
					// reset control break

					$patient_balance = 0.00;				
					//$oldest_bill = $procdtbilled;
					$oldest_bill = "0000-00-00";
					$billed = 1;
					$prev_patient = $id;
					$prev_lname = $ptlname;
					$prev_fname = $ptfname;
					echo "</TR>";
				}	
				$patient_balance += $procbalcurrent;
				if ($procdtbilled > "0000-00-00")
				{
					if ($oldest_bill == "0000-00-00")
						$oldest_bill = $procdtbilled;
					else
					if ($procdtbilled < $oldest_bill)
						$oldest_bill = $procdtbilled;
				}

				// it only takes 1 unbilled to show NO
				if (!$procbilled)
					$billed = 0;

    		} // while there are no more
			// process last record from control break;
            echo "
               <TR BGCOLOR=\"".($_alternate=freemed_bar_alternate_color ($_alternate))."\">
                  <TD><A HREF=
                  \"manage.php?$_auth&id=$id\"
                  >$prev_lname, $prev_fname</A></TD>
                  <TD><A HREF=
                  \"$this->page_name?$_auth&id=$prev_patient&patient=$prev_patient&module=PaymentModule&action=addform\"
                  ><FONT SIZE=-1>"._("View/Manage")."</FONT></A></TD>
                 <TD><A HREF=
                 \"$this->page_name?_auth=$auth&patient=$prev_patient&module=PaymentModule&action=addform&viewaction=unpaidledger\"
                 ><FONT SIZE=-1>"._("Patient Ledger")."</FONT></A></TD>
                  ";
                  if (!$billed)
                      echo "<TD> <FONT COLOR=#ff0000>&nbspNO&nbsp</FONT></TD>";
                  else
                      echo "<TD>YES</TD>";
                  echo "<TD>$oldest_bill</TD>";
                  $total_unpaid += $patient_balance;  // add to grand total
                  echo "<TD ALIGN=RIGHT>".bcadd($patient_balance,0,2)."</TD>";
			// end control break

			// process totals.
             echo "<TR>
			<TD><B>"._("Total")."</B></TD>
			<TD>&nbsp;</TD>
			<TD>&nbsp;</TD>
			<TD>&nbsp;</TD>
			<TD>&nbsp;</TD>
			<TD ALIGN=RIGHT><FONT COLOR=#ff0000>".bcadd($total_unpaid,0,2)."</TD>
			</TR>
			";

	 		echo "
      			</TABLE>
    			"; 

		} // end of result set

	} // end view function

} // end class freemedReportsModule

register_module ("unpaidProceduresReport");

} // end if not defined

?>
