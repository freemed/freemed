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

class TransactionGraph extends GraphModule {

	var $MODULE_NAME = "Transaction Graph";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "620a04e9-6e9e-4734-a6d0-d59a0d55523d";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	public function __construct ( ) {
		$this->graph_text = __("Select Transaction Graph Dates");
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
	} // end constructor TransactionGraph

	protected function GenerateReport ( $params ) {
		$start_dt = $params['start_dt'];
		$end_dt = $params['end_dt'];

		$query = "SELECT SUM(payrecamt) as payrectot,payreccat,payrecpatient ".
				"FROM payrec ".
				"WHERE payrecdt >= ".$GLOBALS['sql']->quote( $start_dt )." ".
				"AND payrecdt <= ".$GLOBALS['sql']->quote( $end_dt )." ".
				"GROUP BY payreccat ORDER BY payreccat";
		
		$result = $sql->query($query);

		$title = __("Transaction Graph From")." $start_dt ".__("To")." $end_dt";
		if (count($result) > 0) {
			foreach ( $result AS $row ) {
				$max = $row['payrectot'] > $max ? $row['payrectot'] : $max;
				$graph_data[] = array($TRANS_TYPES[$row[payreccat]],bcadd($row[payrectot],0,2));
			}

			// bar graph
			$graph = CreateObject('org.freemedsoftware.core.PHPlot', 800, 600);
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
	} // end method GenerateReport

} // end class TransactionGraph

register_module ("TransactionGraph");

?>
