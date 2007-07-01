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
	dojo.require( 'dojo.event.*' );

	var s = {
		handleResponse: function ( data ) {
			if (data) {
				freemedMessage( "<!--{t}-->Booked appointment.<!--{/t}-->", "INFO" );
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
			<!--{if $patient}-->
			calpatient.onAssign( <!--{$patient}--> );
			<!--{/if}-->
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
				calduration: parseInt( document.getElementById( 'calduration' ).value ),
				calprenote: document.getElementById( 'calprenote' ).value
			};
			if (s.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.api.Scheduler.SetAppointment",
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
	});
	_container_.addOnUnload(function() {
		dojo.event.disconnect( dojo.widget.byId('BookingFormCommitChangesButton'), 'onClick', s, 'submit' );
	});

</script>

<h3><!--{t}-->Book Appointment<!--{/t}--></h3>

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Patient<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="calpatient"}--></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Date<!--{/t}--></td>
		<td><input dojoType="DropdownDatePicker" id="caldateof" name="caldateof" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Time<!--{/t}--></td>
		<td><input dojoType="DropdownTimePicker" id="caltime" value=""></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Duration<!--{/t}--></td>
		<td><select name="calduration" id="calduration">
			<option>15</option>
			<option>30</option>
			<option>45</option>
			<option>60</option>
		</select></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Provider<!--{/t}--></td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="calphysician"}--></td>
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
	                <div><!--{t}-->Book Appointment<!--{/t}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="BookingFormCancelButton" widgetId="BookingFormCancelButton" onClick="freemedPatientContentLoad( 'org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->' );">
        	        <div><!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

