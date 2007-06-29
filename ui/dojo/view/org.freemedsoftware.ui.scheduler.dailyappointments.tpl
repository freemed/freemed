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

<style type="text/css">

	.dateSelectionHeader {
		size: 10pt;
		border: 1px solid #5555ff;
		padding: 5px;
		background-color: #aaaaff;
		}

</style>

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require('dojo.widget.DropdownDatePicker');

	var o = {
		dailyCalendarSetDate: function ( ) {
			var date = dojo.widget.byId('dailyAppointmentsDate').inputNode.value;
			// Initial data load
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: date,
					param1: '<!--{$SESSION.authdata.user_record.userrealphy}-->'
	
				},
				url: '<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.api.Scheduler.GetDailyAppointments',
				error: function() { },
				load: function( type, data, evt ) {
					if (data) {
						for ( var i=0; i<data.length; i++) {
							if ( data[i].status_color ) {
								var s = data[i].status;
								data[i].status='<div style="width: 100%; background-color: ' + data[i].status_color + '; color: #999999; text-align: center;" >' + s + '</div>';	
							}
						}
						dojo.widget.byId('dailyPatientAppointments').store.setData( data );
					}
				},
				mimetype: "text/json"
			});
		},
		setAtomicStatus: function ( evt ) {
			if ( parseInt(evt) > 0 ) {
				// Check to see if anything is selected
				try {
					var s = dojo.widget.byId('dailyPatientAppointments').getSelectedData();
					var d = {
						csstatus: parseInt( evt ),
						csappt: parseInt( s.scheduler_id ),
						cspatient: parseInt( s.patient_id )
					};
					dojo.io.bind({
						method: "GET",
						content: {
							param0: d
						},
						url: '<!--{$relay}-->/org.freemedsoftware.module.schedulerpatientstatus.add',
						load: function( type, data, evt ) {
							if (data) {
								freemedMessage( "<!--{t}-->Status updated.<!--{/t}-->", 'INFO' );
								o.resetAtomicStatus();
								// Reload calendar by force
								o.dailyCalendarSetDate();
							}
						}
					});
				} catch (err) {
					// Nothing selected, reset
					o.resetAtomicStatus();
					alert("<!--{t}-->Please select an appointment.<!--{/t}-->");
				}
			}
		},
		viewPatient: function ( ) {
			try {
				var s = dojo.widget.byId('dailyPatientAppointments').getSelectedData();
				freemedLoad( 'org.freemedsoftware.controller.patient.overview?patient=' + s.patient_id );
			} catch (err) {
				alert("<!--{t}-->Please select an appointment.<!--{/t}-->");
			}
		},
		resetAtomicStatus: function () {
			dojo.widget.byId('atomicStatusWidget').setLabel('');
			dojo.widget.byId('atomicStatusWidget').setValue('');
		}
	};

	_container_.addOnLoad(function() {
		o.resetAtomicStatus();
		dojo.event.connect(dojo.widget.byId('atomicStatusWidget'), "onValueChanged", o, "setAtomicStatus");
		dojo.event.connect(dojo.widget.byId('dailyAppointmentsDate'), "onValueChanged", o, "dailyCalendarSetDate");
		dojo.event.connect(dojo.widget.byId('viewPatientButton'), "onClick", o, "viewPatient");
		o.dailyCalendarSetDate();
	});

	_container_.addOnUnload(function() {
		dojo.event.disconnect(dojo.widget.byId('atomicStatusWidget'), "onValueChanged", o, "setAtomicStatus");
		dojo.event.disconnect(dojo.widget.byId('dailyAppointmentsDate'), "onValueChanged", o, "dailyCalendarSetDate");
		dojo.event.disconnect(dojo.widget.byId('viewPatientButton'), "onClick", o, "viewPatient");
	});
</script>

<div align="center" class="dateSelectionHeader">
<table border="0">
	<tr>
		<td><b>Today's Patients</b></td>
		<td><input dojoType="DropdownDatePicker" value="today" id="dailyAppointmentsDate" widgetId="dailyAppointmentsDate" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table style="width: auto;" border="0">
				<tr>
					<td><button dojoType="Button" id="noshowButton" widgetId="nosnowButton"><!--{t}-->No Show<!--{/t}--></button></td>
					<td>
						<input dojoType="Select" value=""
						autocomplete="false"
						id="atomicStatusWidget" widgetId="atomicStatusWidget"
						style="width: 300px;"
						dataUrl="<!--{$relay}-->/org.freemedsoftware.module.schedulerstatustype.picklist?param0=%{searchString}"
						mode="remote" />
					</td>
					<td><button dojoType="Button" id="viewPatientButton" widgetId="viewPatientButton"><!--{t}-->View Patient<!--{/t}--></button></td>
				<!--
					<td><button dojoType="Button" id="noshowButton" widgetId="nosnowButton"><!--{t}--><!--{/t}--></button></td>
					<td><button dojoType="Button" id="noshowButton" widgetId="nosnowButton"><!--{t}--><!--{/t}--></button></td>
				-->
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>

<div class="tableContainer">
	<table dojoType="FilteringTable" id="dailyPatientAppointments" widgetId="dailyPatientAppointments" headClass="fixedHeader"
	 tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow"
	 valueField="scheduler_id" border="0" multiple="false">
	<thead>
		<tr>
			<th field="date_of_mdy" dataType="Date">Date</th>
			<th field="appointment_time" dataType="String" sort="asc">Time</th>
			<th field="patient" dataType="String">Patient</th>
			<th field="provider" dataType="String">Provider</th>
			<th field="status" dataType="Html">Status</th>
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

