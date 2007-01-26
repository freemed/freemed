<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //	Fred Forester <fforest@netcarrier.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.GraphModule');

class ChargesGraph extends GraphModule {

	var $MODULE_NAME = "Charges Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "999e20ff-2206-4bf3-8207-b12bc0e94d7a";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	public function __construct ( ) {
		$this->graph_text = __("Select Charges Graph Dates");
		$this->parameters = array (
			'start_dt' => array (
				'text' => __("Starting Date"),
				'type' => 'date',
				'required' => true
			),
			'end_dt' => array (
				'text' => __("Ending Date"),
				'type' => 'date',
				'required' => true
			),
			'type' => array (
				'text' => __("Chart Type"),
				'type' => 'select',
				'options' => 'bar,pie',
				'required' => true
			)
		);
		parent::__construct( );
	} // end constructor ChargesGraph

	function display()
	{
		// Assemble ...
		$sqlcharges = implode(",",$CHARGE_TYPES);

		$start_dt = $params["start_dt"];
		$end_dt = $params["end_dt"];

		$type = $params['type'] ? $params['type'] : 'bar';

		$query = "SELECT payrecamt,payreccat FROM payrec ".
				 "WHERE payrecdt >= ".$GLOBALS['sql']->quote( $start_dt )." ".
				 "AND payrecdt <= ".$GLOBALS['sql']->quote( $end_dt )." ".
				 ( !empty($sqlcharges) ?
				 " AND payreccat IN ($sqlcharges)" : "" );

		$result = $GLOBALS['sql']->queryAll( $query );
		$titleb = __("Charges Graph From")." $start_dt ".__("To")." $end_dt";
		$titlep = __("Charges Pie Chart From")." $start_dt ".__("To")." $end_dt";
		if (count($result) > 0) {
			$tot_denial = 0;
			$tot_withhold = 0;
			$tot_deduct = 0;
			$tot_feeadj = 0;
			$tot_writeoff = 0;
			foreach ( $result AS $row ) {
				$denial = 0;
				$withhold = 0;
				$deduct = 0;
				$feeadj = 0;
				$writeoff = 0;
				switch ( $row['payreccat'] ) {
					case DENIAL:
					$denial = bcadd($row['payrecamt'],0,2);
					break;

					case WITHHOLD:
					$withhold = bcadd($row['payrecamt'],0,2);
					break;

					case DEDUCTABLE:
					$deduct = bcadd($row['payrecamt'],0,2);
					break;

					case FEEADJUST:
					$feeadj = bcadd($row['payrecamt'],0,2);
					break;

					case WRITEOFF:
					$writeoff = bcadd($row['payrecamt'],0,2);
					break;

					default: break;
				}

				$pie_data[] = array("",$deduct,$denial,$withhold,$feeadj,$writeoff);
				$tot_denial = bcadd($tot_denial,$denial,2);
				$tot_deduct = bcadd($tot_deduct,$deduct,2);
				$tot_withhold = bcadd($tot_withhold,$withhold,2);
				$tot_feeadj = bcadd($tot_feeadj,$feeadj,2);
				$tot_writeoff = bcadd($tot_writeoff,$writeoff,2);
			}
			$grand = 0;
			$grand = bcadd($tot_deduct,$grand,2);
			$grand = bcadd($tot_denial,$grand,2);
			$grand = bcadd($tot_withhold,$grand,2);
			$grand = bcadd($tot_feeadj,$grand,2);
			$grand = bcadd($tot_writeoff,$grand,2);
			$bar_data[] = array("",$tot_withhold,$tot_denial,$tot_deduct,$tot_feeadj,$tot_writeoff,$grand);

			$graph = CreateObject('org.freemedsoftware.core.PHPlot', 500, 500); // (x,y) or (w,h)
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
				$graph->SetDrawDataLabels('1');
				$graph->SetDataColors(array("pink","cyan","yellow","orange","gray"));
				$graph->SetLegend(array("Deductible","Denial","Withhold","Allowed","Writeoff"));
				$graph->SetLabelScalePosition(1.3);
				$graph->DrawGraph();
				$graph->PrintImage();
				break;

				default:
				$this->GraphError(__("Invalid graph type."));
				break;
			}
		}
	} // end GenerateReport

} // end class ChargesGraph

register_module ("ChargesGraph");

?>
