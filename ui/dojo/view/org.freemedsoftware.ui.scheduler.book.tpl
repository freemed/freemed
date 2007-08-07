<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

<script type="text/javascript">
	dojo.require( 'dojo.date' );

	var s = {
		handleResponse: function ( data ) {
			if (data) {
				<!--{if $id}-->
				freemedMessage( "<!--{t}-->Moved appointment.<!--{/t}-->", "INFO" );
				<!--{else}-->
				freemedMessage( "<!--{t}-->Booked appointment.<!--{/t}-->", "INFO" );
				<!--{/if}-->
				<!--{if $patient}-->
				freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );
				<!--{else}-->
				// ?
				<!--{/if}-->
			} else {
				dojo.widget.byId('BookingFormCommitChangesButton').enable();
			}
		},
		validate: function ( content ) {
			var r = true;
			var m = "";
			// TODO: validation goes here
			if ( m.length > 1 ) { alert( m ); }
			return r;
		},
		initialLoad: function ( ) {
			<!--{if $id}-->
			<!--{method namespace="org.freemedsoftware.api.Scheduler.GetAppointment" param0=$id var='record'}-->
			var x = <!--{json value=$record}-->;
			dojo.widget.byId('caldateof').setValue( x.caldateof );
			//calhour: dojo.date.strftime( dojo.widget.byId('caltime').timePicker.time, '%H', dojo.widget.byId('caltime').lang ),
			var dt = new Date( );
			dt.setHours( x.calhour );
			dt.setMinutes( x.calminute );
			dojo.widget.byId('caltime').timePicker.setTime( dt );
			calphysician.onAssign( x.calphysician );
			document.getElementById( 'calduration' ).value = x.calduration;
			document.getElementById( 'calprenote' ).value = x.calprenote;
			<!--{/if}-->
			<!--{if $patient}-->
			calpatient.onAssign( <!--{$patient}--> );
			<!--{/if}-->
			s.updatePreview();
		},
		prevDay: function ( ) {
			var d = dojo.widget.byId( 'caldateof' );
			var prevDate = dojo.date.add( d.value, dojo.date.dateParts.DAY, -1 );
			d.setValue( prevDate );
			d.value = prevDate;
		},
		nextDay: function ( ) {
			var d = dojo.widget.byId( 'caldateof' );
			var nextDate = dojo.date.add( d.value, dojo.date.dateParts.DAY, 1 );
			d.setValue( nextDate );
			d.value = nextDate;
		},
		updatePreview: function ( ) {
			var d = dojo.widget.byId( 'caldateof' ).getValue();
			var p = document.getElementById( 'calphysician' ).value;
			document.getElementById( 'datePreviewLabel' ).innerHTML = d;
			// Load date
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: d,
					param1: p ? p : ''
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
					dojo.widget.byId( 'datePreviewFilteringTable' ).store.setData( data );
					}
				},
				mimetype: 'text/json'

			});
		},
		submit: function ( ) {
			try {
				dojo.widget.byId('BookingFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				<!--{if $id}-->id: "<!--{$id|escape}-->",<!--{/if}-->
				caldateof: dojo.widget.byId('caldateof').getValue(),
				calhour: dojo.date.strftime( dojo.widget.byId('caltime').timePicker.time, '%H', dojo.widget.byId('caltime').lang ),
				calminute: dojo.date.strftime( dojo.widget.byId('caltime').timePicker.time, '%M', dojo.widget.byId('caltime').lang ),
				calpatient: parseInt( document.getElementById( 'calpatient' ).value ),
				calphysician: parseInt( document.getElementById( 'calphysician' ).value ),
				calduration: parseInt( document.getElementById( 'calduration' ).value ),
				calprenote: document.getElementById( 'calprenote' ).value
			};
			if (s.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						<!--{if $id}-->
						param0: <!--{$id}-->,
						param1: myContent
						<!--{else}-->
						param0: myContent
						<!--{/if}-->
					},
					url: "<!--{$relay}-->/org.freemedsoftware.api.Scheduler.<!--{if $id}-->Move<!--{else}-->Set<!--{/if}-->Appointment",
					load: function ( type, data, evt ) {
						s.handleResponse( data );
					},
					mimetype: "text/json"
				});
			}
		}
	};

	_container_.addOnLoad(function() {
		s.initialLoad();
		dojo.event.connect( dojo.widget.byId('BookingFormCommitChangesButton'), 'onClick', s, 'submit' );
		dojo.event.connect( dojo.widget.byId( 'caldateof' ), 'onValueChanged', s, 'updatePreview' );
                dojo.event.connect(dojo.widget.byId( 'caldateofNext' ), "onClick", s, "nextDay");
                dojo.event.connect(dojo.widget.byId( 'caldateofPrev' ), "onClick", s, "prevDay");
		dojo.event.topic.subscribe( 'calphysician-setValue', s, 'updatePreview' );
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('BookingFormCommitChangesButton'), 'onClick', s, 'submit' );
		dojo.event.disconnect( dojo.widget.byId('caldateof'), 'onValueChanged', s, 'updatePreview' );
                dojo.event.disconnect(dojo.widget.byId( 'caldateofNext' ), "onClick", s, "nextDay");
                dojo.event.disconnect(dojo.widget.byId( 'caldateofPrev' ), "onClick", s, "prevDay");
		dojo.event.topic.unsubscribe( 'calphysician-setValue', s, 'updatePreview' );
	});

</script>

<!--{if $id}-->
<h3><!--{t}-->Move Appointment<!--{/t}--></h3>
<!--{else}-->
<h3><!--{t}-->Book Appointment<!--{/t}--></h3>
<!--{/if}-->

<table border="0" cellpadding="5" style="width: auto;">
<tr><td valign="top">
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Patient<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="calpatient"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Date<!--{/t}--></td>
		<td><table border="0" style="width: auto;"><tr>
		<td><button dojoType="Button" id="caldateofPrev" widgetId="caldateofPrev">&lt;</button></td>
		<td><input dojoType="DropdownDatePicker" value="today" id="caldateof" name="caldateof" widgetId="caldateof" /></td>
		<td><button dojoType="Button" id="caldateofNext" widgetId="caldateofNext">&gt;</button></td>
		</table></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Time<!--{/t}--></td>
		<td><input dojoType="DropdownTimePicker" id="caltime" value=""></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Duration<!--{/t}--></td>
		<td><select name="calduration" id="calduration">
			<option value="5">0:05</option>
			<option value="10">0:10</option>
			<option value="15">0:15</option>
			<option value="20">0:20</option>
			<option value="25">0:25</option>
			<option value="30">0:30</option>
			<option value="45">0:45</option>
			<option value="60">1:00</option>
			<option value="75">1:15</option>
			<option value="90">1:30</option>
			<option value="105">1:45</option>
			<option value="120">2:00</option>
			<option value="150">2:30</option>
			<option value="180">3:00</option>
			<option value="240">4:00</option>
			<option value="300">5:00</option>
			<option value="360">6:00</option>
			<option value="420">7:00</option>
			<option value="480">8:00</option>
		</select></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Provider<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="calphysician" methodName="internalPicklist"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Note<!--{/t}--></td>
		<td><input type="text" id="calprenote" name="calprenote" size="50" /></td>
	</tr>

</table>

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
	        <button dojoType="Button" id="BookingFormCommitChangesButton" widgetId="BookingFormCommitChangesButton">
	                <div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{if $id}--><!--{t}-->Move Appointment<!--{/t}--><!--{else}--><!--{t}-->Book Appointment<!--{/t}--><!--{/if}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="BookingFormCancelButton" widgetId="BookingFormCancelButton" onClick="freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );">
        	        <div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

</td><td valign="top">
<b><!--{t}-->Preview for <!--{/t}--> <span id="datePreviewLabel">-</span></b>
<div class="tableContainer">
	<table dojoType="FilteringTable" id="datePreviewFilteringTable" widgetId="datePreviewFilteringTable" headClass="fixedHeader" tbodyClass="scrollContent" enableAlternateRows="true" rowAlternateClass="alternateRow" valueField="scheduler_id" border="0" multiple="false">
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
	<tbody></tbody>
	</table>
</div>

</td></tr></table>

