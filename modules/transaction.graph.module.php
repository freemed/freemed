<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.GraphModule');

class TransactionGraph extends GraphModule {

	var $MODULE_NAME = "Transaction Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function TransactionGraph () {
		$this->graph_text = __("Select Transaction Graph Dates");
		$this->GraphModule();
	} // end constructor TransactionGraph

	function display () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT SUM(payrecamt) as payrectot,payreccat,payrecpatient ".
				"FROM payrec ".
				"WHERE payrecdt>='".addslashes($start_dt)."' ".
				"AND payrecdt<='".addslashes($end_dt)."' ".
				"GROUP BY payreccat ORDER BY payreccat";
		
		$result = $sql->query($query) or DIE("Query failed");
		if ($sql->num_rows($result) > 0) {
			$display_buffer .= $this->view();
			$display_buffer .= "<p/>\n";
			$display_buffer .= "<div align=\"center\">\n";
			$display_buffer .= "<a href=\"".$this->AssembleURL(array(
				'graphmode' => 1,
				'action' => 'image'
			))."\" target=\"print\">".__("Printable")."</a>\n";
			$display_buffer .= "</div>\n";
			$display_buffer .= "<p/>\n";
			$display_buffer .= "<div align=\"center\">\n";
			$display_buffer .= "<img src=\"".$this->AssembleURL(array(
				'graphmode' => 1,
				'action' => 'image'
			))."\" border=\"0\" alt=\"\"/>\n";
			$display_buffer .= "</div>\n";
		} else {
			$display_buffer .= $this->view();
			$display_buffer .= "<div align=\"center\">\n";
			$display_buffer .= __("No Records found")."\n";
			$display_buffer .= "</div>\n";
			$display_buffer .= "<div align=\"center\">\n";
			$display_buffer .= "<a href=\"reports.php\">".
				__("Reports")."</a>\n";
			$display_buffer .= "</div>\n";
		}
	} // end function TransactionGraph->display()

	function image () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT SUM(payrecamt) as payrectot,payreccat,payrecpatient ".
				"FROM payrec ".
				"WHERE payrecdt>='".addslashes($start_dt)."' ".
				"AND payrecdt<='".addslashes($end_dt)."' ".
				"GROUP BY payreccat ORDER BY payreccat";
		
		$result = $sql->query($query) or DIE("Query failed");
		$title = __("Transaction Graph From")." $start_dt ".__("To")." $end_dt";
		if ($sql->num_rows($result) > 0)
		{
			while ($row = $sql->fetch_array($result))
			{
				$max = $row['payrectot'] > $max ? $row['payrectot'] : $max;
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
			$graph->SetVertTickIncrement(ceil($max / 10));
			$graph->SetDrawXDataLabels('1');
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
	} // end method TransactionGraph->image

} // end class TransactionGraph

register_module ("TransactionGraph");

?>
