<?php
 // $Id$
 // desc: aged bills report
 // lic : LGPL

LoadObjectDependency('FreeMED.GraphModule');

class ChargesGraph extends GraphModule {

	var $MODULE_NAME = "Charges Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function ChargesGraph () {
		$this->graph_text = _("Select Charges Graph Dates");
		$this->graph_opts = array(
			_("Chart Type") =>
			html_form::select_widget(
				'type',
				array('bar', 'pie'),
				array('refresh' => true)
			)
		);
		$this->GraphModule();
	} // end constructor ChargesGraph

	function display()
	{
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Assemble ...
		$sqlcharges = implode(",",$CHARGE_TYPES);

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		global $type; if (!$type) { $type = 'bar'; }

		$display_buffer .= $this->GetGraphOptions(
			_("Select Charges Graph Dates"),
			// Our modifications to the form
			array(
				_("Chart Type") =>
				html_form::select_widget(
					'type',
					array('bar', 'pie'),
					array('refresh' => true)
				)
			)
		);
		

		$display_buffer .= "<p/>\n";
		$display_buffer .= "<div align=\"center\"><a href=\"".$this->AssembleURL(
			array(
				'graphmode' => 1,
				'action' => 'image',
				'type' => $type
			))."\" target=\"print\" class=\"button\" ".
			">"._("Printable")."</a></div>\n";
		$display_buffer .= "<p/>\n";
		$display_buffer .= "<img src=\"".$this->AssembleURL(
			array(
				'action' => 'image',
				'type' => $type
			))."\" border=\"0\" alt=\"\"/>\n";
	} // end display

	function image() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$sqlcharges = implode(",",$CHARGE_TYPES);

		$start_dt = fm_date_assemble("start_dt");
		$end_dt = fm_date_assemble("end_dt");

		$query = "SELECT payrecamt,payreccat FROM payrec ".
				 "WHERE payrecdt>='".addslashes($start_dt)."' ".
				 "AND payrecdt<='".addslashes($end_dt)."'".
				 ( !empty($sqlcharges) ?
				 " AND payreccat in($sqlcharges)" : "" );

		$result = $sql->query($query);
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

			$graph = CreateObject('FreeMED.PHPlot', 500, 500); // (x,y) or (w,h)
			global $type;
			switch ($type) {
				case "bar":
				// bar
				$graph->SetPrintImage(0);
				$graph->SetTitle($titleb);
				$graph->SetNewPlotAreaPixels(50,50,400,400);
				$graph->SetPlotType('bars');
				$graph->SetDataValues($bar_data);
				$graph->SetDrawDataLabels('1');
				$graph->SetBackgroundColor("white");
				$graph->SetDataColors(array("yellow","cyan","pink","orange","gray","green"));
				$graph->SetVertTickIncrement(10000);
				$graph->SetLegend(array("Withhold","Denial","Deductible","Allowed","Writeoff","Total"));
				$graph->DrawGraph();
				$graph->PrintImage();
				break;

				case "pie":
				// pie chart
				$graph->SetTitle($titlep);
				$graph->SetNewPlotAreaPixels(400,100,400,400); 
				$graph->SetPlotType('pie');
				$graph->SetBackgroundColor("white");
				$graph->SetDataValues($pie_data);
				$graph->SetDataColors(array("pink","cyan","yellow","orange","gray"));
				$graph->SetLegend(array("Deductible","Denial","Withhold","Allowed","Writeoff"));
				$graph->SetLabelScalePosition(1.3);
				$graph->DrawGraph();
				$graph->PrintImage();
				break;

				default:
				die();
				break;
			}
		}
	} // end image

} // end class ChargesGraph

register_module ("ChargesGraph");

?>
