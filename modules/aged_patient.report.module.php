<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

LoadObjectDependency('FreeMED.ReportsModule');

class AgedPatientReport extends ReportsModule {

	var $MODULE_NAME = "Patient Aged Detail Report";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function AgedPatientReport () {
		$this->ReportsModule();
	} // end constructor AgedPatientReport

	function view() {
		global $display_buffer;
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
				b.ptlname,
				a.procbalcurrent,
				a.procdt,
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
				ORDER BY b.ptlname,d.insconame,procage
		";


		$aged_result = $sql->query($query);

		if ($sql->num_rows($aged_result) <= 0)
			$display_buffer .= "No unpaid procedures found<BR>";

		$prevpat = "0";
		$previnsco = "0";

		$display_buffer .= "
		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 WIDTH=100%>
		<TR>
		<TD><B>"._("Patient")."</B></TD>
		<TD><B>"._("Insurance")."</B></TD>
		<TD><B>"._("DOS")."</B></TD>
		<TD><B>"._("Balance")."</B></TD>
		<TD><B>"._("Days Old")."</B></TD>
		</TR>
		";


		while($row = $sql->fetch_array($aged_result))
		{
			$pat = $row[ptlname].$row[ptfname];

			//$display_buffer .= "doc $doc pat $pat<BR>";

			if ($prevpat != $pat)
			{
				if ($prevpat != "0") // not first time thru
				{
					// calc pat totals.
					$display_buffer .= $this->pattotals($pattot,$_alternate);
				}

				$prevpat = $pat;
				//$patrow = freemed::get_link_rec($pat,"patient");
				$patname = $row[ptlname].", ".$row[ptfname];
				//$display_buffer .= "patname $patname<BR>";
				$grandtot += $pattot;
				$pattot = 0;
				$previnsco="0";


			}

			if ($previnsco != $row[insconame])
			{
				//$covrec = freemed::get_link_rec($row[proccurcovid],"coverage");
				//$insco = freemed::get_link_rec($covrec[covinsco],"insco");
				$insconame = $row[insconame];	
				$previnsco = $row[insconame];
			}

			$age = $row[procage];
			$display_buffer .= "<TR CLASS=\"".freemed_alternate()."\">";
			$display_buffer .= "<TD>$patname</TD>";
			$display_buffer .= "<TD>$insconame</TD>";
			$display_buffer .= "<TD>$row[procdt]</TD>";
			$bal = bcadd($row[procbalcurrent],0,2);
			$display_buffer .= "<TD ALIGN=RIGHT>$bal</TD>";
			$display_buffer .= "<TD ALIGN=RIGHT>$age</TD>";
			$display_buffer .= "</TR>";
			$pattot += $bal;
			$phyname="&nbsp;";
			$patname="&nbsp;";
			$insconame="&nbsp;";

		}

		// calc pat totals.
		$display_buffer .= $this->pattotals($pattot,$_alternate);

		$grandtot += $pattot;

		// calc grand totals
		$display_buffer .= $this->grtotals($grandtot,$_alternate);
		$display_buffer .= "</TABLE>";
				 

	} // end view function

	function pattotals($total,$color)
	{
		// calc pat totals.
		$buffer =  "<TR CLASS=\"".( isset($color) ?
			$color : freemed_alternate() )."\">";
		$buffer .=  "<TD><B>"._("Patient Total")."</B></TD>";
		$buffer .=  "<TD>&nbsp;</TD>";
		$buffer .=  "<TD>&nbsp;</TD>";
		$pattot = bcadd($total,0,2);
		$buffer .=  "<TD ALIGN=RIGHT><B>$pattot</B></TD>";
		$buffer .=  "<TD ALIGN=RIGHT>&nbsp;</TD>";
		$buffer .=  "</TR>";
		return $buffer;
	}

	function grtotals($total,$color)
	{
		// calc pat totals.
		$buffer =  "<TR CLASS=\"".(
			isset($color) ? $color : freemed_alternate() )."\">\n";
		$buffer .=  "<TD><B>"._("Grand Total")."</B></TD>";
		$buffer .=  "<TD>&nbsp;</TD>";
		$buffer .=  "<TD>&nbsp;</TD>";
		$phytot = bcadd($total,0,2);
		$buffer .=  "<TD ALIGN=RIGHT><B>$phytot</B></TD>";
		$buffer .=  "<TD ALIGN=RIGHT>&nbsp;</TD>";
		$buffer .=  "</TR>";
		return $buffer;
	}

} // end class AgedPatientReport

register_module ("AgedPatientReport");

?>
