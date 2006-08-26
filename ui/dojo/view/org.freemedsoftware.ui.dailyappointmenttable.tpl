<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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
*}-->
<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.SortableTable");

	function handleDailyCalendar ( ) {
		alert('got a click');
		return true;
	}
</script>

<style type="text/css">
		table {
			width: 100%;
		}

		* html div.tableContainer {	/* IE only hack */
			width:95%;
			/* border:1px solid #ccc; */
			height: 285px;
			overflow-x:hidden;
			overflow-y: auto;
		}

		* html div.tableContainer table {
			width:100%; border:1px solid #ccc; cursor:default;
		}

		div.tableContainer table td,
		div.tableContainer table th{
			border-right:1px solid #999;
			padding:2px;
			font-weight:normal;
		}
		table thead td, table thead th {
			background:#94BEFF;
		}
		
		* html div.tableContainer table thead tr td,
		* html div.tableContainer table thead tr th{
			/* IE Only hacks */
			position:relative;
			top:expression(dojo.html.getFirstAncestorByTag(this,'table').parentNode.scrollTop-2);
		}
		
		html>body tbody.scrollContent {
			height: 262px;
			overflow-x:hidden;
			overflow-y: auto;
		}

		tbody.scrollContent td, tbody.scrollContent tr td {
			background: #FFF;
			padding: 2px;
		}

		tbody.scrollContent tr.alternateRow td {
			background: #e3edfa;
			padding: 2px;
		}

		tbody.scrollContent tr.selected td {
			background: yellow;
			padding: 2px;
		}
		tbody.scrollContent tr:hover td {
			background: #a6c2e7;
			padding: 2px;
		}
		tbody.scrollContent tr.selected:hover td {
			background: #ffff33;
			padding: 2px;
		}
</style>

<!--{if not $dailyCalendar.0.patient_id}-->
<div align="center"><i>No appointments scheduled for today.</i></div>
<!--{else}-->
<div class="tableContainer">
	<table dojoType="SortableTable" widgetId="dailyPatientAppointments" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 border="0" onUISelect="freemedPatientLoad(dojo.widget.byId('dailyPatientAppointments').getValue());">
	<thead>
		<tr>
			<th field="Id" dataType="Number"></th>
			<th field="Date" dataType="String">Date</th>
			<th field="Time" dataType="String">Time</th>
			<th field="Patient" dataType="String">Patient</th>
			<th field="Provider" dataType="String">Provider</th>
			<th field="Note" dataType="String">Note</th>
		</tr>
	</thead>
	<tbody>
<!--{* Loop through all $dailyCalendar[] items as set in the controller *}-->
<!--{foreach from=$dailyCalendar item=calItem}-->
		<tr>
			<td><!--{$calItem.patient_id}--></td>
			<td><!--{$calItem.date_of}--></td>
			<td><!--{$calItem.hour|string_format:"%02d"}-->:<!--{$calItem.minute|string_format:"%02d"}--></td>
			<td><!--{$calItem.patient}--></td>
			<td><!--{$calItem.provider}--></td>
			<td><!--{$calItem.note}--></td>
		</tr>
<!--{/foreach}-->
	</tbody>
	</table>
</div>
<!--{/if}-->

