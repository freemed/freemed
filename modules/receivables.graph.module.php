<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.GraphModule');

class ReceivablesGraph extends GraphModule {

	var $MODULE_NAME = "Receivables Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function ReceivablesGraph () {
		$this->graph_text = __("Select Receivables Graph Dates");
		$this->GraphModule();
	} // end constructor ReceivablesGraph

	function display() {
		global $sql, $display_buffer;
		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT payrecamt,payrecsource,payreccat FROM payrec ".
				 "WHERE payrecdt>='".addslashes($start_dt)."' ".
				 "AND payrecdt<='".addslashes($end_dt)."' ".
				 "AND (payreccat='".PAYMENT."' OR payreccat='".COPAY."') ";
		$result = $sql->query($query) or DIE("Query failed");
		if ($sql->num_rows($result) > 0) {
			$this->view();
			$display_buffer .= "<p/>\n";
			$display_buffer .= "<center><img src=\"".$this->AssembleURL(
				array (
					'graphmode' => 1,
					'action' => 'image'
				)
			)."\" border=\"0\" alt=\"\"/></center>\n";
		} else {
			$display_buffer .= "<div align=\"center\">\n";
			$display_buffer .= __("No receivables found.")."\n";
			$display_buffer .= "</div>\n";
			$display_buffer .= "<div align=\"center\">\n";
			$display_buffer .= "<a href=\"reports.php\">".
				__("Reports")."</a>\n";
			$display_buffer .= "</div>\n";
		}

	}

	function image()
	{
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT payrecamt,payrecsource,payreccat FROM payrec ".
				 "WHERE payrecdt>='".addslashes($start_dt)."' ".
				 "AND payrecdt<='".addslashes($end_dt)."' ".
				 "AND (payreccat='".PAYMENT."' OR payreccat='".COPAY."') ";
	
		
		$result = $sql->query($query);// or DIE("Query failed");
		$titleb = __("Receivables Graph From")." $start_dt ".__("To")." $end_dt";
		$titlep = __("Receivables Pie Chart From")." $start_dt ".__("To")." $end_dt";
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
				if ($row[payrecsource] == 0) {
					if ($row[payreccat] == COPAY) {
						$copay = bcadd($row[payrecamt],0,2);
					}
					if ($row[payreccat] == PAYMENT) {
						$patpay = bcadd($row[payrecamt],0,2);
					}
				} else {
					$inspay = bcadd($row[payrecamt],0,2);
				}

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

			$graph = CreateObject('FreeMED.PHPlot',500,500); // (x,y) or (w,h)

			// bar
			$graph->SetPrintImage(0);
			$graph->SetTitle($titleb);
			$graph->SetNewPlotAreaPixels(100,100,350,500);
			$graph->SetPlotType('bars');
			$graph->SetDataValues($bar_data);
			$graph->SetDrawDataLabels('1');
			$graph->SetBackgroundColor("white");
			$graph->SetDataColors(array("yellow","cyan","pink","orange"));
			$graph->SetVertTickIncrement(10000);
			$graph->SetLegend(array("Patient","Copay","Insurance","Total"));
			$graph->DrawGraph();
			$graph->PrintImage();

			return;

			// FOR NOW, DISABLE THE FOLLOWING ...
			
			// pie chart
			//$graph->SetNewPlotAreaPixels(100,500,350,500); works below bar
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
	} // end image

} // end class ReceivablesGraph

register_module ("ReceivablesGraph");

?>
