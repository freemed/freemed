<?php
 // $Id$
 // desc: manage patients bills
 // code: Fred Forester <fforest@netcarrier.com>
 // lic : GPL, v2

 $page_name = "manage_bills.php";
 $db_name = "procrec";
 $record_name = "Manage Patient Bills";
 include ("lib/freemed.php");
 include ("lib/API.php");
 

 freemed_open_db ($LoginCookie);

 freemed_display_html_top();
 freemed_display_banner();

 switch ($action) 
 { // master action switch
  case "list":
   // procduce a list only. Don't acutally process any bills

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
		freemed_display_box_top($record_name);
		freemed_display_actionbar($page_name, $_ref);

    		echo "
      		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 WIDTH=100%>
      		<TR>
       		<TD><B>Name</B></TD>
                <TD COLSPAN=2 ALIGN=CENTER><B>Billing Functions</B></TD>
       		<TD><B>Procedures</B></TD>
       		<TD><B>Billed</B></TD>
       		<TD><B>Date Billed</B></TD>
                <TD><B>Balance</B></TD>
      		</TR>
    		"; // header of box
 		$total_unpaid = 0.00;
                // setup control break
                $prev_patient="$$";
                $patient_balance = 0.00;
                $billed = 1;
     		$_alternate = freemed_bar_alternate_color ();
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
        			// alternate the bar color

      				echo "
        				<TR BGCOLOR=\"".
     				($_alternate = freemed_bar_alternate_color ($_alternate))
						."\">
        				<TD><A HREF=
         				\"manage.php?$_auth&id=$prev_patient\"
         				>$prev_lname, $prev_fname</A></TD>
        				<TD><A HREF=
         				\"manage_payment_records.php?$_auth&id=$prev_patient&patient=$prev_patient&bills=yes\"
         				><FONT SIZE=-1>View/Manage</FONT></A></TD>
        				<TD><A HREF=
         				\"payment_record.php?_ref=$page_name&patient=$prev_patient\"
         				><FONT SIZE=-1>Patient Ledger</FONT></A></TD>
        				<TD><A HREF=
         				\"procedure.php?$_auth&id=$id&patient=$prev_patient\"
         				><FONT SIZE=-1>View/Manage</FONT></A></TD>
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
        		  <TR BGCOLOR=\"".
     				($_alternate = freemed_bar_alternate_color ($_alternate))
						."\">
                  <TD><A HREF=
                  \"manage.php?$_auth&id=$id\"
                  >$prev_lname, $prev_fname</A></TD>
                  <TD><A HREF=
                  \"manage_payment_records.php?$_auth&id=$prev_patient&patient=$prev_patient&bills=yes\"
                  ><FONT SIZE=-1>View/Manage</FONT></A></TD>
                  <TD><A HREF=
                  \"payment_record.php?_ref=$page_name&patient=$prev_patient\"
                  ><FONT SIZE=-1>Patient Ledger</FONT></A></TD>
                  <TD><A HREF=
                  \"procedure.php?$_auth&id=$id&patient=$prev_patient\"
                  ><FONT SIZE=-1>View/Manage</FONT></A></TD>
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
		<TD><B>Total</B></TD>
		<TD>&nbsp;</TD>
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
		
		freemed_display_box_bottom();

	} // end of result set
        break;

 }  // end action

 freemed_display_html_bottom ();
 freemed_close_db ();
?>
