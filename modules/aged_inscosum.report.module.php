<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

LoadObjectDependency('FreeMED.ReportsModule');

class AgedInscoReport extends ReportsModule {

	var $MODULE_NAME = "Insurance Aged Summary Report";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function AgedInscoReport () {
		$this->ReportsModule();
	} // end constructor AgedInscoReport

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
				"d.inscophone AS inscophone, ".
				"a.procbalcurrent AS procbalcurrent, ".
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
				"ORDER BY d.insconame,b.ptlname";

		$aged_result = $sql->query($query);

		if ($sql->num_rows($aged_result) <= 0) {
			$display_buffer .= "No unpaid procedures found\n<p/>\n";
			return false;
		}

		$prevpat = "0";
		$previns = "0";
		$numbuckets = 5;

		$display_buffer .= "
		<table BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\" WIDTH=\"100%\">
		<tr>
		<td><B>"._("Insurance")."</b></td>
		<td><B>"._("Phone")."</b></td>
		<td><B>"._("Patient")."</b></td>
		<td ALIGN=\"CENTER\"><b>&lt;30</b></td>
		<td ALIGN=\"CENTER\"><b>30</b></td>
		<td ALIGN=\"CENTER\"><b>60</b></td>
		<td ALIGN=\"CENTER\"><b>90</b></td>
		<td ALIGN=\"CENTER\"><b>120&gt;</b></td>
		<td ALIGN=\"CENTER\"><b>"._("Total")."</b></td>
		</tr>
		";

		for ($i=0;$i<$numbuckets;$i++)
		{
			$ins_bucket[$i] = 0;
			$tot_bucket[$i] = 0;
			$pat_bucket[$i] = 0;
		}

		while($row = $sql->fetch_array($aged_result))
		{
			$pat = $row['ptlname'].", ".$row['ptfname'];
			$insconame = $row['insconame'];	
			$inscoph = $row['inscophone'];
		
			//$display_buffer .= "pat $pat ins $insconame<BR>";

			if ($prevpat != $pat)
			{
				if ($prevpat != "0") // not first time thru
				{
					// calc pat totals.
					$display_buffer .= $this->pattotals($patins,$patinsph,$pat_bucket,$prevpat);
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
					$display_buffer .= $this->instotals($ins_bucket);

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


			$age = $row['procage'];
			$bal = bcadd($row['procbalcurrent'], 0, 2);

			if ($age < 30) {
				$pat_bucket[0] += $bal;
			} elseif ($age < 60) {
				$pat_bucket[1] += $bal;
			} elseif ($age < 90) {
				$pat_bucket[2] += $bal;
			} elseif ($age < 120) {
				$pat_bucket[3] += $bal;
			} else {
				$pat_bucket[4] += $bal;
			}
				
		}

		// calc pat totals.
		$display_buffer .= $this->pattotals($patins,$patinsph,$pat_bucket,$prevpat);

		for ($i=0;$i<$numbuckets;$i++)
		{
			$ins_bucket[$i] += $pat_bucket[$i];
			$pat_bucket[$i] = 0;
		}

		// calc ins totals.
		$display_buffer .= $this->instotals($ins_bucket);

		for ($i=0;$i<$numbuckets;$i++)
		{
			$tot_bucket[$i] += $ins_bucket[$i];
			$ins_bucket[$i] = 0;
		}
		// calc ins totals.
		$display_buffer .= $this->instotals($tot_bucket);

		$display_buffer .= "</table>\n";
	} // end view function

	function pattotals($insname,$insphone,$total,$patname,$color) {
		global $display_buffer;
		$num = count($total);

		// calc pat totals.
		$buffer =  "<tr CLASS=\"".(
			isset($color) ? $color : freemed_alternate() )."\">\n";
		$buffer .=  "<td>$insname</td>\n";
		$buffer .=  "<td>$insphone</td>\n";
		$buffer .=  "<td>$patname</td>\n";

		$pattot = 0;
		for ($i=0;$i<$num;$i++)
		{
			$bal = bcadd($total[$i], 0, 2);
			$buffer .=  "<td ALIGN=\"RIGHT\">$bal</td>\n";
			$pattot += $bal;
		}
		$pattot = bcadd($pattot, 0, 2);
		$buffer .=  "<td ALIGN=\"RIGHT\">$pattot</td>\n";
		$buffer .=  "</tr>\n";
		return $buffer;
	} // end function AgedInscoReport->pattotals

	function instotals($total,$color) {
		$num = count($total);

		// calc ins totals.
		$buffer =  "<tr CLASS=\"".(
			isset($color) ? $color : freemed_alternate() )."\">\n";
		$buffer .=  "<td><b>"._("Total")."</b></td>\n";
		$buffer .=  "<td>&nbsp;</td>\n";
		$buffer .=  "<td>&nbsp;</td>\n";

		$pattot = 0;
		for ($i=0;$i<$num;$i++)
		{
			$bal = bcadd($total[$i],0,2);
			$buffer .=  "<td ALIGN=\"RIGHT\"><b>$bal</b></td>\n";
			$pattot += $bal;
		}
		$pattot = bcadd($pattot,0,2);
		$buffer .=  "<td ALIGN=\"RIGHT\"><b>$pattot</b></td>\n";
		$buffer .=  "</tr>\n";
		return $buffer;
	} // end function AgedInscoReport->instotals

} // end class AgedInscoReport

register_module ("AgedInscoReport");

?>
