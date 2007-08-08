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

	.limitText {
		font-size: 8pt;
		}

</style>

<script type="text/javascript">
	dojo.require("dojo.date");
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.FilteringTable");
	dojo.require('dojo.widget.DropdownDatePicker');

	var o = {
		limitStatus: 1,
		dailyCalendarSetDate: function ( ) {
			var date = dojo.widget.byId('dailyAppointmentsDate').inputNode.value;
			try {
				freemedGlobal.state.dailyAppointmentsDate = date;
			} catch ( err ) { }
			// Initial data load
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: date,
					param1: o.limitStatus ? '<!--{$SESSION.authdata.user_record.userrealphy}-->' : ''
	
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
		prevDay: function ( ) {
			var d = dojo.widget.byId('dailyAppointmentsDate');
			var prevDate = dojo.date.add( d.value, dojo.date.dateParts.DAY, -1 );
			d.setValue( prevDate );
			d.value = prevDate;
		},
		nextDay: function ( ) {
			var d = dojo.widget.byId('dailyAppointmentsDate');
			var nextDate = dojo.date.add( d.value, dojo.date.dateParts.DAY, 1 );
			d.setValue( nextDate );
			d.value = nextDate;
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
		onLimitChange: function ( ) {
			var w = document.getElementById( 'dailyAppointmentOnlyMe' );
			o.limitStatus = w.checked ? 1 : 0;
			o.dailyCalendarSetDate(); 	// refresh view
		},
		resetAtomicStatus: function () {
			dojo.widget.byId('atomicStatusWidget').setLabel('');
			dojo.widget.byId('atomicStatusWidget').setValue('');
		},
		noShow: function () {
			o.updateStatus( 'noshow' );
		},
		cancelAppt: function () {
			o.updateStatus( 'cancelled' );
		},
		moveAppt: function ( ) {
			try {
				var s = dojo.widget.byId('dailyPatientAppointments').getSelectedData();
				freemedLoad( "org.freemedsoftware.ui.scheduler.book?patient=" + s.patient_id + "&id=" + s.scheduler_id );
			} catch ( err ) {
				// Nothing selected, reset
				o.resetAtomicStatus();
				alert("<!--{t}-->Please select an appointment.<!--{/t}-->");
			}
		},
		updateStatus: function ( status ) {
			try {
				var s = dojo.widget.byId('dailyPatientAppointments').getSelectedData();
				dojo.io.bind({
					method: "GET",
					content: {
						param0: parseInt( s.scheduler_id ),
						param1: { calstatus: status }
					},
					url: '<!--{$relay}-->/org.freemedsoftware.api.Scheduler.MoveAppointment',
					load: function( type, data, evt ) {
						if (data) {
							freemedMessage( "<!--{t}-->Appointment updated.<!--{/t}-->", 'INFO' );
							o.dailyCalendarSetDate();
						}
					},
					mimetype: 'text/json'
				});
			} catch (err) { }
		}
	};

	_container_.addOnLoad(function() {
		try {
			var date = freemedGlobal.state.dailyAppointmentsDate;
			if ( date.length > 4 ) {
				dojo.widget.byId('dailyAppointmentsDate').inputNode.value = date;
			}
		} catch ( err ) { }
		o.resetAtomicStatus();
		dojo.event.connect(dojo.widget.byId('atomicStatusWidget'), "onValueChanged", o, "setAtomicStatus");
		dojo.event.connect(dojo.widget.byId('dailyAppointmentsDate'), "onValueChanged", o, "dailyCalendarSetDate");
		dojo.event.connect(dojo.widget.byId('viewPatientButton'), "onClick", o, "viewPatient");
		dojo.event.connect(dojo.widget.byId('dailyAppointmentNextDay'), "onClick", o, "nextDay");
		dojo.event.connect(dojo.widget.byId('dailyAppointmentPrevDay'), "onClick", o, "prevDay");
		dojo.event.connect(dojo.widget.byId('noshowButton'), "onClick", o, "noShow");
		dojo.event.connect(dojo.widget.byId('moveButton'), "onClick", o, "moveAppt");
		dojo.event.connect(dojo.widget.byId('cancelApptButton'), "onClick", o, "cancelAppt");
		o.dailyCalendarSetDate();
		document.getElementById( 'dailyAppointmentOnlyMe' ).onchange = o.onLimitChange;
	});

	_container_.addOnUnload(function() {
		dojo.event.disconnect(dojo.widget.byId('atomicStatusWidget'), "onValueChanged", o, "setAtomicStatus");
		dojo.event.disconnect(dojo.widget.byId('dailyAppointmentsDate'), "onValueChanged", o, "dailyCalendarSetDate");
		dojo.event.disconnect(dojo.widget.byId('viewPatientButton'), "onClick", o, "viewPatient");
		dojo.event.disconnect(dojo.widget.byId('dailyAppointmentNextDay'), "onClick", o, "nextDay");
		dojo.event.disconnect(dojo.widget.byId('dailyAppointmentPrevDay'), "onClick", o, "prevDay");
		dojo.event.disconnect(dojo.widget.byId('noshowButton'), "onClick", o, "noShow");
		dojo.event.disconnect(dojo.widget.byId('moveButton'), "onClick", o, "moveAppt");
		dojo.event.disconnect(dojo.widget.byId('cancelApptButton'), "onClick", o, "cancelAppt");
	});
</script>

<div align="center" class="dateSelectionHeader">
<table border="0">
	<tr>
		<td><b><!--{t}-->Today's Patients<!--{/t}--></b></td>
		<td>
		<table border="0" style="width: auto;"><tr>
		<td><button dojoType="Button" id="dailyAppointmentPrevDay" widgetId="dailyAppointmentPrevDay">&lt;</button></td>
		<td><input dojoType="DropdownDatePicker" value="today" id="dailyAppointmentsDate" widgetId="dailyAppointmentsDate" />
		<td><button dojoType="Button" id="dailyAppointmentNextDay" widgetId="dailyAppointmentNextDay">&gt;</button></td>
		<td style="border: 1px dashed #000000; <!--{if not $SESSION.authdata.user_record.userrealphy}-->display: none;<!--{/if}-->"><input type="checkbox" id="dailyAppointmentOnlyMe" checked="checked" /><label for="dailyAppointmentOnlyMe"><small><!--{t}-->Limit to provider<!--{/t}--></small></label></td>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table style="width: auto;" border="0">
				<tr>
					<td><button dojoType="Button" id="cancelApptButton" widgetId="cancelApptButton"><!--{t}-->Cancellation<!--{/t}--></button></td>
					<td><button dojoType="Button" id="noshowButton" widgetId="noshowButton"><!--{t}-->No Show<!--{/t}--></button></td>
					<td><button dojoType="Button" id="moveButton" widgetId="moveButton"><!--{t}-->Move<!--{/t}--></button></td>
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
			<th field="appointment_time" dataType="String"><!--{t}-->Time<!--{/t}--></th>
			<th field="duration" dataType="String"><!--{t}-->Duration<!--{/t}--></th>
			<th field="patient" dataType="String"><!--{t}-->Patient<!--{/t}--></th>
			<th field="provider" dataType="String"><!--{t}-->Provider<!--{/t}--></th>
			<th field="status" dataType="Html"><!--{t}-->Status<!--{/t}--></th>
			<th field="note" dataType="String"><!--{t}-->Note<!--{/t}--></th>
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

