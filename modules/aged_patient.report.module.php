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
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$ages_greater = array(00,30,60,090,00120);
		$ages_lesseq  = array(30,60,90,120,99999);

	 	//"TO_DAYS(CURRENT_DATE)-TO_DAYS(procdt)>'60' AND ".
 	 	//"TO_DAYS(CURRENT_DATE)-TO_DAYS(procdt)<'120' ".
		//$query = "SELECT procbalcurrent,procdt,procpatient,procphysician,proccurcovid,".
		//		 "TO_DAYS(CURRENT_DATE)-TO_DAYS(procdt) as procage ".
		//		 "FROM procrec ".
		//		 "WHERE procbalcurrent>'0' AND procbillable='0' ".
		//		 "ORDER BY procphysician,procpatient,proccurcovid,procage";

	
		$query = "SELECT ".
				"d.insconame AS insconame, ".
				"a.procbalcurrent AS procbalcurrent, ".
				"a.procdt AS procdt, ".
				"b.ptlname AS ptlname, ".
				"b.ptfname AS ptfname, ".
				"e.id AS id, ".
				"TO_DAYS(CURRENT_DATE)-TO_DAYS(a.procdt) as procage ".
				"FROM procrec as a, patient as b, ".
					"insco as d, coverage as e ".
				"WHERE ".
				"a.procbalcurrent>'0' AND ".
				"a.procbillable='0' AND ".
				"a.procpatient=b.id AND ".
				"a.proccurcovid=e.id AND ".
				"e.covinsco=d.id ".
				"ORDER BY b.ptlname,d.insconame,procage";

		$aged_result = $sql->query($query);

		if ($sql->num_rows($aged_result) <= 0) {
			$display_buffer .= "No unpaid procedures found\n<p/>\n";
			return false;
		}

		$prevpat = "0";
		$previnsco = "0";

		$display_buffer .= "
		<table BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\" WIDTH=\"100%\">
		<tr>
		<td><b>"._("Patient")."</b></td>
		<td><b>"._("Insurance")."</b></td>
		<td><b>"._("DOS")."</b></td>
		<td><b>"._("Balance")."</b></td>
		<td><b>"._("Days Old")."</b></td>
		</tr>
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
					$display_buffer .= $this->pattotals($pattot);
				}

				$prevpat = $pat;
				//$patrow = freemed::get_link_rec($pat,"patient");
				$patname = $row['ptlname'].", ".$row['ptfname'];
				//$display_buffer .= "patname $patname<BR>";
				$grandtot += $pattot;
				$pattot = 0;
				$previnsco="0";
			}

			if ($previnsco != $row['insconame'])
			{
				//$covrec = freemed::get_link_rec($row[proccurcovid],"coverage");
				//$insco = freemed::get_link_rec($covrec[covinsco],"insco");
				$insconame = $row['insconame'];	
				$previnsco = $row['insconame'];
			}

			$age = $row[procage];
			$display_buffer .= "<tr CLASS=\"".freemed_alternate()."\">\n";
			$display_buffer .= "<td>$patname</td>\n";
			$display_buffer .= "<td>$insconame</td>\n";
			$display_buffer .= "<td>".$row['procdt']."</td>\n";
			$bal = bcadd($row['procbalcurrent'],0,2);
			$display_buffer .= "<td ALIGN=\"RIGHT\">$bal</td>\n";
			$display_buffer .= "<td ALIGN=\"RIGHT\">$age</td>\n";
			$display_buffer .= "</tr>";
			$pattot += $bal;
			$phyname="&nbsp;";
			$patname="&nbsp;";
			$insconame="&nbsp;";

		}

		// calc pat totals.
		$display_buffer .= $this->pattotals($pattot);

		$grandtot += $pattot;

		// calc grand totals
		$display_buffer .= $this->grtotals($grandtot);
		$display_buffer .= "</table>";
				 

	} // end view function

	function pattotals($total,$color)
	{
		// calc pat totals.
		$buffer =  "<tr CLASS=\"".( isset($color) ?
			$color : freemed_alternate() )."\">\n";
		$buffer .=  "<td><b>"._("Patient Total")."</b></td>\n";
		$buffer .=  "<td>&nbsp;</td>\n";
		$buffer .=  "<td>&nbsp;</td>\n";
		$pattot = bcadd($total,0,2);
		$buffer .=  "<td ALIGN=RIGHT><b>$pattot</b></td>\n";
		$buffer .=  "<td ALIGN=\"RIGHT\">&nbsp;</td>\n";
		$buffer .=  "</tr>\n";
		return $buffer;
	}

	function grtotals($total,$color)
	{
		// calc pat totals.
		$buffer =  "<tr CLASS=\"".(
			isset($color) ? $color : freemed_alternate() )."\">\n";
		$buffer .=  "<td><b>"._("Grand Total")."</b></td>\n";
		$buffer .=  "<td>&nbsp;</td>\n";
		$buffer .=  "<td>&nbsp;</td>\n";
		$phytot = bcadd($total,0,2);
		$buffer .=  "<td ALIGN=\"RIGHT\"><b>$phytot</b></td>\n";
		$buffer .=  "<td ALIGN=\"RIGHT\">&nbsp;</td>\n";
		$buffer .=  "</tr>\n";
		return $buffer;
	}

} // end class AgedPatientReport

register_module ("AgedPatientReport");

?>
