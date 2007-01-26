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

class ReceivablesGraph extends GraphModule {

	var $MODULE_NAME = "Receivables Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "c1f1926e-c604-4b59-954b-07edf6eef025";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	public function __construct ( ) {
		$this->graph_text = __("Select Receivables Graph Dates");
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
			)
		);

		parent::__construct( );
	} // end constructor ReceivablesGraph

	protected function GenerateReport ( $params ) {
		$start_dt = $params['start_dt'];
		$end_dt = $params['end_dt'];

		$query = "SELECT payrecamt,payrecsource,payreccat FROM payrec ".
				 "WHERE payrecdt >= ".$GLOBALS['sql']->quote( $start_dt )." ".
				 "AND payrecdt <= ".$GLOBALS['sql']->quote( $end_dt )." ".
				 "AND (payreccat='".PAYMENT."' OR payreccat='".COPAY."') ";
		$result = $GLOBALS['sql']->queryAll( $query );

		$titleb = __("Receivables Graph From")." $start_dt ".__("To")." $end_dt";
		$titlep = __("Receivables Pie Chart From")." $start_dt ".__("To")." $end_dt";
		if (count( $result ) > 0) {
			$tot_copay = 0;
			$tot_patpay = 0;
			$tot_inspay = 0;
			foreach ( $result AS $row ) {
				$copay = 0;
				$patpay = 0;
				$inspay = 0;
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
			$grand = 0;
			$grand = bcadd($tot_inspay,$grand,2);
			$grand = bcadd($tot_copay,$grand,2);
			$grand = bcadd($tot_patpay,$grand,2);
			$bar_data[] = array("",$tot_patpay,$tot_copay,$tot_inspay,$grand);

			$graph = CreateObject('org.freemedsoftware.core.PHPlot',500,500); // (x,y) or (w,h)

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
	} // end GenerateReport

} // end class ReceivablesGraph

register_module ("ReceivablesGraph");

?>
