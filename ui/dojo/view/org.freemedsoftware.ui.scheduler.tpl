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
	dojo.require("mywidgets.widget.Calendar");
	dojo.require("mywidgets.widget.Timezones");

	var oCalendar;

	var sched = {
		init: function () {
			oCalendar = dojo.widget.byId("dojoCalendar");
			oCalendar.setTimeZones(mywidgets.widget.timezones);
			oCalendar.selectedtimezone = dojo.io.cookie.getObjectCookie("DCTZ");
			oCalendar.onSetTimeZone = sched.widgetTimeZoneChanged;
			oCalendar.changeEventTimes = true;
			oCalendar.onEventChanged = sched.widgetEventChanged;
			oCalendar.setAbleToCreateNew(true);
			oCalendar.onNewEntry = sched.widgetNewEntry;
			oCalendar.onValueChanged = sched.widgetValueChanged;
			sched.widgetValueChanged(new Date());
		},

		widgetValueChanged: function (dateObj){
			dojo.require("dojo.date.serialize");
			var d1s = new Date(dateObj);
			d1s.setDate(1);
			d1s.setHours(14,0,0,0);
			var d1e = new Date(dateObj);
			d1e.setDate(1);
			d1e.setHours(14,30,0,0);
			var d15s = new Date(dateObj);
			d15s.setDate(15);
			var d15e = new Date(dateObj);
			d15e.setDate(15);
			var d28s = new Date(dateObj);
			d28s.setDate(28);
			d28s.setHours(16,40,0,0);
			var d28e = new Date(dateObj);
			d28e.setDate(28);
			d28e.setHours(18,30,0,0);
			var entries = {
				"id1": {
					starttime: dojo.date.toRfc3339(d1s),
					endtime: dojo.date.toRfc3339(d1e),
					allday: false,
					repeated: false,
					title: "Title 1",
					url: "",
					body: "This is the body of entry with id: id1 and title: Title 1",
					attributes: {
						Location: "My Galactic Headquarters",
						Chair: "John Doe"
					},
					type: ["meeting","appointment"]
				},
				"id2": {
					starttime: dojo.date.toRfc3339(d15s),
					endtime: dojo.date.toRfc3339(d15e),
					allday: true,
					repeated: false,
					title: "Title 2",
					url: "",
					body: "This is the body of entry with id: id2 and title: Title 2",
					attributes: {
						Location: "Somewhere"
					},
					type: ["appointment","super"]
				},
				"id3": {
					starttime: dojo.date.toRfc3339(d28s),
					endtime: dojo.date.toRfc3339(d28e),
					allday: false,
					repeated: false,
					title: "Title 3",
					url: "",
					body: "This is the body of entry with id: id3 and title: Title 3",
					attributes: "",
					type: ["reminder"]
				}
			}
			oCalendar.setCalendarEntries(entries);
		},
		widgetEventChanged: function (eventId,eventObject){
			var sReturn = "id " + eventId + "=\n";
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
			alert(sReturn);
			//Call script to update back-end db
			oCalendar.refreshScreen();
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
			alert(sReturn);
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

	_container_.addOnLoad(sched.init);

</script>

<h3><!--{t}-->Scheduler<!--{/t}--></h3>

<div align="center">
	<div style="width:800px; height:400px; background-color:#cccccc; overflow:auto;">
		<div id="dojoCalendar" dojoType="mywidgets:calendar"></div>
	</div>
</div>

