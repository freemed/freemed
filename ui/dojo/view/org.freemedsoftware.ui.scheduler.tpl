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

<script type="text/javascript">
	dojo.require("mywidgets.widget.FreemedCalendar");
	dojo.require("mywidgets.widget.Timezones");

	var oCalendar;

	var sched = {
		init: function () {
			oCalendar = dojo.widget.byId( "dojoCalendar" );
			oCalendar.setTimeZones(mywidgets.widget.timezones);
			oCalendar.selectedtimezone = dojo.io.cookie.getObjectCookie( "DCTZ" );
			oCalendar.onSetTimeZone = sched.widgetTimeZoneChanged;
			oCalendar.changeEventTimes = true;
			oCalendar.onEventChanged = sched.widgetEventChanged;
			oCalendar.setAbleToCreateNew( true );
			oCalendar.onNewEntry = sched.widgetNewEntry;
			oCalendar.onValueChanged = sched.widgetValueChanged;
			sched.widgetValueChanged(new Date());
		},

		dataStore: { },
		widgetValueChanged: function ( dateObj ){
			// dateObj is current date
			dojo.require("dojo.date.serialize");

			var cal = dojo.widget.byId( 'dojoCalendar' );

			var sP = new Date( cal.firstDay );
			var eP;
			if ( cal.calendarType == 'day' ) {
				eP = sP;
			}
			if ( cal.calendarType == 'week' ) {
				eP = dojo.date.add( sP, dojo.date.dateParts.DAY, 6 );
			}
			if ( cal.calendarType == 'month' ) {
				eP = dojo.date.add( sP, dojo.date.dateParts.DAY, 27 );
			}

			// use io bind, sync and get ...
			var prov = document.getElementById( 'schedProvider' ).value;
			dojo.io.bind({
				method: 'POST',
				url: '<!--{$relay}-->/org.freemedsoftware.api.Scheduler.GetDailyAppointmentsRange',
				content: {
					param0: sched.dateToMdy( sP ),
					param1: sched.dateToMdy( eP ),
					param2: prov ? prov : ''
				},
				load: function ( type, data, evt ) {
					var d = data;
					var entries = { };
					// Populate with entries from relay
					for( var i in d ) {
						var sDate = sched.mdyToDate( d[i].date_of_mdy );
						sDate.setHours( d[i].hour );
						sDate.setMinutes( d[i].minute );
						var eDate = new Date( sDate.getTime() + ( d[i].duration * 60 * 1000 ) );
						entries[ 'appt_' + d[i].scheduler_id ] = {
							starttime: dojo.date.toRfc3339( sDate ),
							endtime: dojo.date.toRfc3339( eDate ),
							allday: false,
							repeated: false,
							title: ( d[i].patient ? ( d[i].patient + (d[i].note ? ' - <i>' + d[i].note + '</i>' : '' ) ) : "<!--{t}-->NON PATIENT APPOINTMENT<!--{/t}-->" ) + ' [' + d[i].duration + 'm]',
							code: d[i].patient_id ? "freemedLoad('org.freemedsoftware.ui.patient.overview?patient=" + d[i].patient_id + "');" : '',
							url: '',
							body: d[i].note,
							//attributes: {Location: "My Galactic Headquarters"},
							type: [ 'appointment' ]
						};
					}
					var c = dojo.widget.byId( 'dojoCalendar' );
					c.setCalendarEntries( entries );
				},
				mimetype: 'text/json'
			});
		},
		widgetEventChanged: function ( eventId,eventObject ){
			var apptId = eventId.replace( 'appt_', '' );

			// Get start date
			var sDate = eventObject.starttime.substring( 0, 10 );

			// Start time
			var sHour = eventObject.starttime.substr( 11, 2 );
			var sMinute = eventObject.starttime.substr( 14, 2 );

			// Figure duration
			var duration = dojo.date.fromRfc3339(eventObject.endtime).getTime() - dojo.date.fromRfc3339(eventObject.starttime).getTime();
			if ( duration < 1000 ) {
				alert( "<!--{t}-->You must select a valid appointment duration.<!--{/t}-->" );
				return false;
			}
			duration = duration / 60000;

			dojo.io.bind({
				method: 'POST',
				url: "<!--{$relay}-->/org.freemedsoftware.api.Scheduler.MoveAppointment",
				content: {
					param0: apptId,
					param1: {
						id: apptId,
						caldateof: sDate,
						calhour: parseInt( sHour ),
						calminute: parseInt( sMinute ),
						calduration: duration
					}
				},
				load: function ( type, data, evt ) {
					//Call script to update back-end db
					oCalendar.refreshScreen();
				},
				mimetype: 'text/json'
			});
		},

		mdyToDate: function ( mdy ) {
			var chunks = mdy.split('/');
			return new Date( chunks[2], chunks[0] - 1, chunks[1] );
		},

		dateToMdy: function ( dt ) {
			var m = dt.getMonth() + 1;
			var d = dt.getDate();
			var y = dt.getYear() + 1900;
			var s =  m + '/' + d + '/' + y;
			if ( s == 'NaN/NaN/NaN' ) {
				var dt = new Date();
				m = dt.getMonth() + 1;
				d = dt.getDate();
				y = dt.getYear() + 1900;
				s =  m + '/' + d + '/' + y;
			}
			return s;
		},

		widgetNewEntry: function(eventObject) {
			var sReturn = "";
			for(var i in eventObject){
				if(typeof(eventObject[i]) != "object"){
					sReturn += i + " = " + eventObject[i] + "\n";
				}else{
					oChildObject = eventObject[i];
					var sChildReturn = "";
					var iNum = 0;
					for(var j in oChildObject){
						if(iNum > 0){
							sChildReturn += ", ";
						}
						sChildReturn += j + ": " + oChildObject[j];
						iNum++;
					}
					sReturn += i + " = " + sChildReturn + "\n";
				}
			}
			//alert(sReturn);
			//Call script to add to back-end db
			oCalendar.refreshScreen();
		},

		widgetTimeZoneChanged: function(){
			//Setting cookie
			if(oCalendar.selectedtimezone == ""){
				dojo.io.cookie.deleteCookie("DCTZ");
			}else{
				dojo.io.cookie.setObjectCookie("DCTZ",oCalendar.selectedtimezone,3650);
			}
		},
		setLocale: function (sLocale){
			oCalendar.lang = sLocale;
			oCalendar._preInitUI(new Date(oCalendar.value));
		}
	};

	_container_.addOnLoad(function(){
		sched.init();
		dojo.event.topic.subscribe( 'schedProvider-setValue', sched, 'widgetValueChanged' );
	});
	_container_.addOnUnload(function(){
		dojo.event.topic.unsubscribe( 'schedProvider-setValue', sched, 'widgetValueChanged' );
	});

</script>

<h3><!--{t}-->Scheduler<!--{/t}--></h3>

<div align="center" style="padding: 1em;">
	<table border="0" style="width: auto;">
	<tr>
		<td><b><!--{t}-->Provider<!--{/t}--></b> : </td>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module="ProviderModule" varname="schedProvider" methodName="internalPicklist" defaultValue="$SESSION.authdata.user_record.userrealphy"}--></td>
	</tr>
	</table>
</div>

<div align="center" style="padding: 1em;">
	<div style="width:800px; height:600px; background-color:#cccccc; overflow:auto;">
		<div id="dojoCalendar"
		 dojoType="mywidgets:freemedcalendar"
		 calendarType="day"
		 dataRelayUrl="<!--{$relay}-->">
		</div>
	</div>
</div>

