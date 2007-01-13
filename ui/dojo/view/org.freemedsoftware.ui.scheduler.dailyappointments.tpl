<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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
*}-->

<!--{include file="org.freemedsoftware.ui.framework.tpl"}-->

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require('dojo.widget.DropdownDatePicker');

	dojo.addOnLoad(function() {
		dojo.event.connect(dojo.widget.byId('dailyPatientAppointments'), "onSelect", function () {
			var w = dojo.widget.byId('dailyPatientAppointments');
			var val;
			if (w.getSelectedData().length > 0) {
				dojo.debug("found getSelectedData()");
				val = w.getSelectedData()[0].patient_id;
			}
			if (val) {
				freemedLoadPage('<!--{$base_uri}-->/controller.php/<!--{$ui}-->/org.freemedsoftware.controller.patient.overview?patient=' + val);
				return true;
			}
		});
		// Initial data load
		dojo.io.bind({
			method: 'POST',
			content: {
				param0: '2007-01-12'
			},
			url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.api.Scheduler.GetDailyAppointments',
			error: function() { },
			load: function( type, data, evt ) {
				if (data) {
					dojo.widget.byId('dailyPatientAppointments').store.setData( data );
				}
			},
			mimetype: "text/json"
		});
	});

	function handleDailyCalendar ( ) {
		alert('got a click');
		return true;
	}
</script>

<div align="center" style="size: 10pt; border: 1px solid #5555ff; padding: 5px; background-color: #aaaaff;">
<table border="0">
	<tr>
		<td><b>Today's Patients (<!--{$dailyAppointmentsDate}-->)</b></td>
		<td><input dojoType="DropdownDatePicker" date="<!--{$dailyAppointmentsDate}-->" id="dailyAppointmentsDate" onSetDate=""></td>
	</tr>
</table>
</div>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="dailyPatientAppointments" widgetId="dailyPatientAppointments" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="scheduler_id" border="0" multiple="no">
	<thead>
		<tr>
			<th field="date_of" dataType="String">Date</th>
			<th field="appointment_time" dataType="String">Time</th>
			<th field="patient" dataType="String">Patient</th>
			<th field="provider" dataType="String">Provider</th>
			<th field="note" dataType="String">Note</th>
		</tr>
	</thead>
	<tbody>
<!--{*
        //      * scheduler_id
        //      * patient
        //      * patient_id
        //      * provider
        //      * provider_id
        //      * note
        //      * hour
*}-->
	</tbody>
	</table>
</div>

