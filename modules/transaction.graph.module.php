<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

if (!defined("__TRANSACTION_GRAPH_MODULE_PHP__")) {

include ("lib/phplot.php");

class TransactionGraph extends freemedGraphModule {

	var $MODULE_NAME = "Transaction Graph";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";


	function TransactionGraph () {
		$this->freemedGraphModule();
	} // end constructor TransactionGraph

	function view()
	{
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};
	
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
		
		$tl = _("Select Transaction Graph Dates");
		$display_buffer .= $this->GetGraphOptions($tl);

	}

	function display()
	{
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT SUM(payrecamt) as payrectot,payreccat,payrecpatient FROM payrec ".
				 "WHERE payrecdt>='$start_dt' AND payrecdt<='$end_dt' ".
				 "GROUP BY payreccat ORDER BY payreccat";
	
		
		$result = $sql->query($query) or DIE("Query failed");
		$title = _("Transaction Graph From")." $start_dt "._("To")." $end_dt";
		if ($sql->num_rows($result) > 0)
		{
			while ($row = $sql->fetch_array($result))
			{
				$graph_data[] = array($TRANS_TYPES[$row[payreccat]],bcadd($row[payrectot],0,2));
			}

			// bar graph
			$graph = CreateObject('FreeMED.PHPlot', 800, 600);
			$graph->SetDataValues($graph_data);
			$graph->SetDataColors(array("yellow"));
			$graph->SetBackgroundColor("white");
			$graph->SetTitle($title); //Lets have a legend
			$graph->SetDrawYGrid(0);
			$graph->SetPlotType('bars');
			$graph->SetVertTickIncrement(10000);
			$graph->SetDrawDataLabels('1');
			$graph->DrawGraph();
/*
			// pie chart
			$graph->SetPlotType('pie');
			$graph->SetLegend($TRANS_TYPES);
			$graph->SetLabelScalePosition(1.3);
			$graph->DrawGraph();
			$graph->PrintImage();
*/
		}
		else
		{
			$display_buffe .= _("No Records found");
		}

	} // end display


} // end class TransactionGraph

register_module ("TransactionGraph");

} // end if not defined

?>
