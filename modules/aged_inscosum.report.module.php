<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

if (!defined("__AGED_INSCO_REPORT_MODULE_PHP__")) {

class agedInscoReport extends freemedReportsModule {

	var $MODULE_NAME = "Insurance Aged Summary Report";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	function agedInscoReport () {
		$this->freemedReportsModule();
	} // end constructor agedInscoReport

	function view()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$ages_greater = array(00,30,60,090,00120);
		$ages_lesseq  = array(30,60,90,120,99999);

	 	//"TO_DAYS(CURRENT_DATE)-TO_DAYS(procdt)>'60' AND ".
 	 	//"TO_DAYS(CURRENT_DATE)-TO_DAYS(procdt)<'120' ".
		//$query = "SELECT procbalcurrent,procdt,procpatient,procphysician,proccurcovid,".
		//		 "TO_DAYS(CURRENT_DATE)-TO_DAYS(procdt) as procage ".
		//		 "FROM procrec ".
		//		 "WHERE procbalcurrent>'0' AND procbillable='0' ".
		//		 "ORDER BY procphysician,procpatient,proccurcovid,procage";

	
		$query = "SELECT 
				d.insconame,
				d.inscophone
				b.ptlname,
				a.procbalcurrent,
				b.ptfname,
				e.id,
				TO_DAYS(CURRENT_DATE)-TO_DAYS(a.procdt) as procage
				FROM procrec as a, patient as b, insco as d, coverage as e
				WHERE 
				a.procbalcurrent>'0' AND 
				a.procbillable='0'  AND
				a.procpatient=b.id AND
				a.proccurcovid=e.id AND
				e.covinsco=d.id
				ORDER BY d.insconame,b.ptlname
		";


		$aged_result = $sql->query($query);

		if ($sql->num_rows($aged_result) <= 0)
			echo "No unpaid procedures found<BR>";

		$prevpat = "0";
		$previns = "0";
		$numbuckets = 5;
		$_alternate = freemed_bar_alternate_color ();

		echo "
		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 WIDTH=100%>
		<TR>
		<TD><B>"._("Insurance")."</B></TD>
		<TD><B>"._("Phone")."</B></TD>
		<TD><B>"._("Patient")."</B></TD>
		<TD ALIGN=CENTER><B>&lt;30</B></TD>
		<TD ALIGN=CENTER><B>30</B></TD>
		<TD ALIGN=CENTER><B>60</B></TD>
		<TD ALIGN=CENTER><B>90</B></TD>
		<TD ALIGN=CENTER><B>120&gt;</B></TD>
		<TD ALIGN=CENTER><B>Total</B></TD>
		</TR>
		";

		for ($i=0;$i<$numbuckets;$i++)
		{
			$ins_bucket[$i] = 0;
			$tot_bucket[$i] = 0;
			$pat_bucket[$i] = 0;
		}

		while($row = $sql->fetch_array($aged_result))
		{
			$pat = $row[ptlname].", ".$row[ptfname];
			$insconame = $row[insconame];	
			$inscoph = $row[inscophone];
		
			//echo "pat $pat ins $insconame<BR>";


			if ($prevpat != $pat)
			{
				if ($prevpat != "0") // not first time thru
				{
					// calc pat totals.
					$_alternate=freemed_bar_alternate_color ($_alternate);
					echo $this->pattotals($patins,$patinsph,$pat_bucket,$prevpat,$_alternate);
					$patinsph = "&nbsp;";
				}

				$prevpat = $pat;
				for ($i=0;$i<$numbuckets;$i++)
				{
					$ins_bucket[$i] += $pat_bucket[$i];
					$pat_bucket[$i] = 0;
				}


			}

			if ($previns != $insconame)
			{
				if ($previns != "0")
				{
					// calc ins totals.
					$_alternate=freemed_bar_alternate_color ($_alternate);
					echo $this->instotals($ins_bucket,$_alternate);

				}
				$previns = $insconame;
				$patins = $insconame;
				$patinsph = $inscoph;
				for ($i=0;$i<$numbuckets;$i++)
				{
					$tot_bucket[$i] += $ins_bucket[$i];
					$ins_bucket[$i] = 0;
				}


			}


			$age = $row[procage];
			$bal = bcadd($row[procbalcurrent],0,2);

			if ($age < 30)
				$pat_bucket[0] += $bal;
			elseif ($age < 60)
				$pat_bucket[1] += $bal;
			elseif ($age < 90)
				$pat_bucket[2] += $bal;
			elseif ($age < 120)
				$pat_bucket[3] += $bal;
			else
				$pat_bucket[4] += $bal;
				
		}

		// calc pat totals.
		$_alternate=freemed_bar_alternate_color ($_alternate);
		echo $this->pattotals($patins,$patinsph,$pat_bucket,$prevpat,$_alternate);

		for ($i=0;$i<$numbuckets;$i++)
		{
			$ins_bucket[$i] += $pat_bucket[$i];
			$pat_bucket[$i] = 0;
		}

		// calc ins totals.
		$_alternate=freemed_bar_alternate_color ($_alternate);
		echo $this->instotals($ins_bucket,$_alternate);

		for ($i=0;$i<$numbuckets;$i++)
		{
			$tot_bucket[$i] += $ins_bucket[$i];
			$ins_bucket[$i] = 0;
		}
		// calc ins totals.
		$_alternate=freemed_bar_alternate_color ($_alternate);
		echo $this->instotals($tot_bucket,$_alternate);

		echo "</TABLE>";
				 

	} // end view function

	function pattotals($insname,$insphone,$total,$patname,$color)
	{
		$num = count($total);

		// calc pat totals.
		$buffer =  "<TR BGCOLOR=\"".$color."\">\n";
		$buffer .=  "<TD>$insname</TD>\n";
		$buffer .=  "<TD>$insphone</TD>\n";
		$buffer .=  "<TD>$patname</TD>\n";

		$pattot = 0;
		for ($i=0;$i<$num;$i++)
		{
			$bal = bcadd($total[$i],0,2);
			$buffer .=  "<TD ALIGN=RIGHT>$bal</TD>\n";
			$pattot += $bal;
		}
		$pattot = bcadd($pattot,0,2);
		$buffer .=  "<TD ALIGN=RIGHT>$pattot</TD>\n";
		$buffer .=  "</TR>\n";
		return $buffer;
	}

	function instotals($total,$color)
	{
		$num = count($total);

		// calc ins totals.
		$buffer =  "<TR BGCOLOR=\"".$color."\">\n";
		$buffer .=  "<TD><B>"._("Total")."</B></TD>\n";
		$buffer .=  "<TD>&nbsp;</TD>\n";
		$buffer .=  "<TD>&nbsp;</TD>\n";

		$pattot = 0;
		for ($i=0;$i<$num;$i++)
		{
			$bal = bcadd($total[$i],0,2);
			$buffer .=  "<TD ALIGN=RIGHT><B>$bal</B></TD>\n";
			$pattot += $bal;
		}
		$pattot = bcadd($pattot,0,2);
		$buffer .=  "<TD ALIGN=RIGHT><B>$pattot</B></TD>\n";
		$buffer .=  "</TR>\n";
		return $buffer;
	}


} // end class agedInscoReport

register_module ("agedInscoReport");

} // end if not defined

?>
