<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

if (!defined("__ACNTPAID_GRAPH_MODULE_PHP__")) {

include ("lib/phplot.php");

class AcntPaidGraph extends freemedGraphModule {

	var $MODULE_NAME = "Account Paid Graph";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	function AcntPaidGraph () {
		$this->freemedGraphModule();
	} // end constructor AcntPaidGraph

	function view()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
	
		if (!isset($start_dt))
		{
			global $start_dt;
			$start_dt=$cur_date;
		}
		if (!isset($end_dt))
		{
			global $end_dt;
			$end_dt=$cur_date;
		}

		$tl = _("Select Account Paid Graph Dates");
		echo $this->GetGraphOptions($tl);

	}

	function display()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$alt_color = freemed_bar_alternate_color();

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");
	
		$query = "SELECT a.id,a.procdt,a.procbalorig,a.procbalcurrent,a.procamtpaid,".
				 "a.proccharges,b.id as pid,b.ptfname,b.ptlname,b.ptid ".
				 "FROM procrec AS a, patient AS b ".
				 "WHERE a.procdt>='$start_dt' AND a.procdt<='$end_dt' AND a.procbalcurrent='0' ".
				 "AND a.procpatient=b.id ".
				 "ORDER BY a.procdt";
		
		$result = $sql->query($query) or DIE("Query failed");
		//echo "<CENTER><B>"._("Account Paid Graph From")." $start_dt "._("To")." $end_dt</B><P>";
		$title = _("Account Paid Graph From")." $start_dt "._("To")." $end_dt";

		if ($sql->num_rows($result) > 0)
		{
			$tot_balorig = 0.00;
			$tot_paid = 0.00;
			$tot_charges = 0.00;
			$tot_balance = 0.00;

			$firstrec=1;
			while ($row = $sql->fetch_array($result))
			{
				$balorig = bcadd($row[procbalorig],0,2);
				$paid = bcadd($row[procamtpaid],0,2);
				//$charges = bcadd($row[proccharges],0,2);
				$charges = bcsub($balorig,$row[proccharges],2);
				$balance = bcadd($row[procbalcurrent],0,2);
			
				$tmpbal = bcadd($charges,$paid,2);
				if ($tmpbal != $balorig)
					$color="#ff0000";
				else
					$color="#000000";	
		
				$copayq = "SELECT payrecamt FROM payrec WHERE payrecproc='".$row[id].
						  "' AND payrecpatient='".$row[pid]."' AND payreccat='".COPAY."'";
				$copayr = $sql->query($copayq);
				$copay_amt = "0.00";
				if ($sql->num_rows($copayr) > 0)
				{
					while ($copayrow = $sql->fetch_array($copayr))
					{
						$copay_amt += $copayrow[payrecamt];
					}
					$copay_amt = bcadd($copay_amt,0,2);
				}
				
				$paid = bcsub($paid,$copay_amt,2);	
				// gather data for graph
				if ($firstrec)
				{
					unset($year_array);
					unset($tot);
					$this_yrmo = substr($row[procdt],0,7);
					$firstrec=0;
				}
				if ($this_yrmo != substr($row[procdt],0,7))
				{
					//echo "yrmo $this_yrmo<BR>";
					$graph_data[] = array($this_yrmo,$tot[1],$tot[2],$tot[3],$tot[4]);
					$this_yrmo = substr($row[procdt],0,7);
					unset($tot);
				}

				$tot[1] = bcadd($tot[1],$balorig,2);
				$tot[2] = bcadd($tot[2],$paid,2);
				$tot[3] = bcadd($tot[3],$copay_amt,2);
				$tot[4] = bcadd($tot[4],$charges,2);
			}
			$graph_data[] = array($this_yrmo,$tot[1],$tot[2],$tot[3],$tot[4]);
			
			$graph = new PHPlot(1200,600); // (w,h)
			$graph->SetDataValues($graph_data);
			$graph->SetDataColors(array("yellow","orange","pink","red"));
			$graph->SetLegend(array('Charges','Payments','Copays','Adjustments')); //Lets have a legend
			$graph->SetTitle($title);
			$graph->SetDrawYGrid(0);
			$graph->SetPlotType('bars');
			$graph->SetBackgroundColor("white");
			$graph->SetVertTickIncrement(1000);
			$graph->SetDrawDataLabels('1'); // draw the value on top of the bar.
			$graph->DrawGraph();
			
		}
		else
		{
			echo _("No Records found");
		}

	} // end display


} // end class AcntPaidGraph

register_module ("AcntPaidGraph");

} // end if not defined

?>
