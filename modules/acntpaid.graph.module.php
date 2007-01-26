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

class AcntPaidGraph extends GraphModule {

	var $MODULE_NAME = "Account Paid Graph";
	var $MODULE_VERSION = "0.3";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "2ed45680-60ca-46b8-a6df-5d26620ef6da";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	public function __construct () {
		$this->description = __("Account Paid Graph");
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
		);
		parent::__construct( );
	} // end constructor AcntPaidGraph

	protected function GenerateReport ( $params ) {
		$start_dt = $params['start_dt'];
		$end_dt = $params['end_dt'];
	
		$query = "SELECT a.id,a.procdt,a.procbalorig,a.procbalcurrent,a.procamtpaid,".
				 "a.proccharges,b.id as pid,b.ptfname,b.ptlname,b.ptid ".
				 "FROM procrec AS a, patient AS b ".
				 "WHERE a.procdt >= ".$GLOBALS['sql']->quote( $start_dt )." ".
				 "AND a.procdt <= ".$GLOBALS['sql']->quote( $end_dt )." ".
				 "AND a.procbalcurrent=0 ".
				 "AND a.procpatient=b.id ".
				 "ORDER BY a.procdt";
		
		$result = $GLOBALS['sql']->queryAll( $query );
		$title = __("Account Paid Graph From")." $start_dt ".__("To")." $end_dt";

		if (count($result) > 0) {
			$tot_balorig = 0.00;
			$tot_paid = 0.00;
			$tot_charges = 0.00;
			$tot_balance = 0.00;

			$firstrec=1;
			foreach ( $result AS $row ) {
				$balorig = bcadd( $row['procbalorig'], 0, 2 );
				$paid = bcadd( $row['procamtpaid'], 0, 2 );
				//$charges = bcadd( $row['proccharges'], 0, 2 );
				$charges = bcadd( $balorig, -$row['proccharges'], 2 );
				$balance = bcadd( $row['procbalcurrent'], 0, 2 );
			
				$tmpbal = bcadd( $charges, $paid, 2 );
				if ($tmpbal != $balorig) {
					$color="#ff0000";
				} else {
					$color="#000000";
				}
		
				$copayq = "SELECT payrecamt FROM payrec WHERE payrecproc=".$GLOBALS['sql']->quote( $row['id'] ).
						  " AND payrecpatient=".$GLOBALS['sql']->quote( $row['pid'] )." AND payreccat=".$GLOBALS['sql']->quote( COPAY );
				$copayr = $GLOBALS['sql']->queryAll( $copayq );
				$copay_amt = "0.00";
				if (count($copayr) > 0) {
					while ($copayrow = $sql->fetch_array($copayr))
					foreach ( $copayr AS $copayrow ) {
						$copay_amt += $copayrow['payrecamt'];
					}
					$copay_amt = bcadd( $copay_amt, 0, 2 );
				}
				
				$paid = bcadd( $paid, -$copay_amt, 2 );	
				// gather data for graph
				if ($firstrec) {
					unset($year_array);
					unset($tot);
					$this_yrmo = substr($row['procdt'],0,7);
					$firstrec=0;
				}
				if ($this_yrmo != substr($row['procdt'],0,7)) {
					//$display_buffer .= "yrmo $this_yrmo<BR>";
					$graph_data[] = array( $this_yrmo, $tot[1], $tot[2], $tot[3], $tot[4] );
					$this_yrmo = substr( $row['procdt'], 0, 7 );
					unset($tot);
				}

				$tot[1] = bcadd( $tot[1], $balorig, 2 );
				$tot[2] = bcadd( $tot[2], $paid, 2 );
				$tot[3] = bcadd( $tot[3], $copay_amt, 2 );
				$tot[4] = bcadd( $tot[4], $charges, 2 );
			}
			$graph_data[] = array( $this_yrmo, $tot[1], $tot[2], $tot[3], $tot[4] );
			for ($i = 1; $i <= 4; $i++) {
				$max_x = $tot[$i] > $max_x ? $tot[$i] : $max_x;
			}
			
			$graph = CreateObject('org.freemedsoftware.core.PHPlot', 1200, 600); // (w,h)
			$graph->SetDataValues( $graph_data );
			$graph->SetDrawDataLabels( 1 );
			$graph->SetDataColors( array("yellow","orange","pink","red") );
			$graph->SetLegend( array('Charges','Payments','Copays','Adjustments') );
			$graph->SetTitle($title);
			$graph->SetDrawYGrid(0);
			$graph->SetPlotType('bars');
			$graph->SetBackgroundColor("white");
			$graph->SetVertTickIncrement(ceil(ceil(($max_x / 10000) * 1000)));
			$graph->SetDrawXDataLabels('1'); // draw the value on top of the bar.
			$graph->DrawGraph();
		}
	} // end GenerateReport

} // end class AcntPaidGraph

register_module ("AcntPaidGraph");

?>
