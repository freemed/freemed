<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

if (!defined("__RECEIVABLES_GRAPH_MODULE_PHP__")) {

include ("lib/phplot.php");

class ReceivablesGraph extends freemedGraphModule {

	var $MODULE_NAME = "Receivables Graph";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";


	function ReceivablesGraph () {
		$this->freemedGraphModule();
	} // end constructor ReceivablesGraph

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

		$tl = _("Select Receivables Graph Dates");
		$display_buffer .= $this->GetGraphOptions($tl);

	}

	function display()
	{
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT payrecamt,payrecsource,payreccat FROM payrec ".
				 "WHERE payrecdt>='$start_dt' AND payrecdt<='$end_dt' ".
				 "AND (payreccat='".PAYMENT."' OR payreccat='".COPAY."') ";
	
		
		$result = $sql->query($query) or DIE("Query failed");
		$titleb = _("Receivables Graph From")." $start_dt "._("To")." $end_dt";
		$titlep = _("Receivables Pie Chart From")." $start_dt "._("To")." $end_dt";
		if ($sql->num_rows($result) > 0)
		{
			$tot_copay=0;
			$tot_patpay=0;
			$tot_inspay=0;
			while ($row = $sql->fetch_array($result))
			{
				$copay=0;
				$patpay=0;
				$inspay=0;
				if ($row[payrecsource] == PATIENT)
				{
					if ($row[payreccat] == COPAY)
						$copay = bcadd($row[payrecamt],0,2);
					if ($row[payreccat] == PAYMENT)
						$patpay = bcadd($row[payrecamt],0,2);
				}
				else
					$inspay = bcadd($row[payrecamt],0,2);

				$pie_data[] = array("",$inspay,$copay,$patpay);
				$tot_copay = bcadd($tot_copay,$copay,2);
				$tot_inspay = bcadd($tot_inspay,$inspay,2);
				$tot_patpay = bcadd($tot_patpay,$patpay,2);
			}
			$grand=0;
			$grand = bcadd($tot_inspay,$grand,2);
			$grand = bcadd($tot_copay,$grand,2);
			$grand = bcadd($tot_patpay,$grand,2);
			$bar_data[] = array("",$tot_patpay,$tot_copay,$tot_inspay,$grand);

			$graph = CreateObject('FreeMED.PHPlot', 900,900); // (x,y) or (w,h)

			// bar
			$graph->SetPrintImage(0);
			$graph->SetTitle($titleb);
			$graph->SetNewPlotAreaPixels(100,100,350,800);
			$graph->SetPlotType('bars');
			$graph->SetDataValues($bar_data);
			$graph->SetDrawDataLabels('1');
			$graph->SetBackgroundColor("white");
			$graph->SetDataColors(array("yellow","cyan","pink","orange"));
			$graph->SetVertTickIncrement(10000);
			$graph->SetLegend(array("Patient","Copay","Insurance","Total"));
			$graph->DrawGraph();
			//$graph->PrintImage();

			// pie chart
			//$graph->SetNewPlotAreaPixels(100,600,350,800); works below bar
			$graph->SetTitle($titlep);
			$graph->SetNewPlotAreaPixels(500,100,800,400); 
			$graph->SetPlotType('pie');
			$graph->SetBackgroundColor("white");
			$graph->SetDataValues($pie_data);
			$graph->SetDataColors(array("pink","cyan","yellow"));
			$graph->SetLegend(array("Insurance","Copay","Patient"));
			$graph->SetLabelScalePosition(1.3);
			$graph->DrawGraph();
			$graph->PrintImage();
		}
		else
		{
			$display_buffer .= _("No Records found");
		}

	} // end display


} // end class ReceivablesGraph

register_module ("ReceivablesGraph");

} // end if not defined

?>
