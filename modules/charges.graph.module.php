<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

LoadObjectDependency('FreeMED.GraphModule');

class ChargesGraph extends GraphModule {

	var $MODULE_NAME = "Charges Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function ChargesGraph () {
		$this->GraphModule();
	} // end constructor ChargesGraph

	function view()
	{
		global $display_buffer;
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
	
		$tl = _("Select Charges Graph Dates");
		$display_buffer .= $this->GetGraphOptions($tl);
	}

	function display()
	{
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$sqlcharges = implode(",",$CHARGE_TYPES);

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT payrecamt,payreccat FROM payrec ".
				 "WHERE payrecdt>='$start_dt' AND payrecdt<='$end_dt' ".
				 "AND payreccat in($sqlcharges)";
	
		
		$result = $sql->query($query) or DIE("Query failed");
		$titleb = _("Charges Graph From")." $start_dt "._("To")." $end_dt";
		$titlep = _("Charges Pie Chart From")." $start_dt "._("To")." $end_dt";
		if ($sql->num_rows($result) > 0)
		{
			$tot_denial=0;
			$tot_withhold=0;
			$tot_deduct=0;
			$tot_feeadj=0;
			$tot_writeoff=0;
			while ($row = $sql->fetch_array($result))
			{
				$denial=0;
				$withhold=0;
				$deduct=0;
				$feeadj=0;
				$writeoff=0;
				if ($row[payreccat] == DENIAL)
					$denial = bcadd($row[payrecamt],0,2);
				if ($row[payreccat] == WITHHOLD)
					$withhold = bcadd($row[payrecamt],0,2);
				if ($row[payreccat] == DEDUCTABLE)
					$deduct = bcadd($row[payrecamt],0,2);
				if ($row[payreccat] == FEEADJUST)
					$feeadj = bcadd($row[payrecamt],0,2);
				if ($row[payreccat] == WRITEOFF)
					$writeoff = bcadd($row[payrecamt],0,2);

				$pie_data[] = array("",$deduct,$denial,$withhold,$feeadj,$writeoff);
				$tot_denial = bcadd($tot_denial,$denial,2);
				$tot_deduct = bcadd($tot_deduct,$deduct,2);
				$tot_withhold = bcadd($tot_withhold,$withhold,2);
				$tot_feeadj = bcadd($tot_feeadj,$feeadj,2);
				$tot_writeoff = bcadd($tot_writeoff,$writeoff,2);
			}
			$grand=0;
			$grand = bcadd($tot_deduct,$grand,2);
			$grand = bcadd($tot_denial,$grand,2);
			$grand = bcadd($tot_withhold,$grand,2);
			$grand = bcadd($tot_feeadj,$grand,2);
			$grand = bcadd($tot_writeoff,$grand,2);
			$bar_data[] = array("",$tot_withhold,$tot_denial,$tot_deduct,$tot_feeadj,$tot_writeoff,$grand);

			$graph = CreateObject('FreeMED.PHPlot', 900, 900); // (x,y) or (w,h)

			// bar
			$graph->SetPrintImage(0);
			$graph->SetTitle($titleb);
			$graph->SetNewPlotAreaPixels(100,100,350,800);
			$graph->SetPlotType('bars');
			$graph->SetDataValues($bar_data);
			$graph->SetDrawDataLabels('1');
			$graph->SetBackgroundColor("white");
			$graph->SetDataColors(array("yellow","cyan","pink","orange","gray","green"));
			$graph->SetVertTickIncrement(10000);
			$graph->SetLegend(array("Withhold","Denial","Deductible","Allowed","Writeoff","Total"));
			$graph->DrawGraph();
			//$graph->PrintImage();

			// pie chart
			$graph->SetTitle($titlep);
			$graph->SetNewPlotAreaPixels(500,100,800,400); 
			$graph->SetPlotType('pie');
			$graph->SetBackgroundColor("white");
			$graph->SetDataValues($pie_data);
			$graph->SetDataColors(array("pink","cyan","yellow","orange","gray"));
			$graph->SetLegend(array("Deductible","Denial","Withhold","Allowed","Writeoff"));
			$graph->SetLabelScalePosition(1.3);
			$graph->DrawGraph();
			$graph->PrintImage();
		}
		else
		{
			$display_buffer .= _("No Records found");
		}

	} // end display


} // end class ChargesGraph

register_module ("ChargesGraph");

?>
