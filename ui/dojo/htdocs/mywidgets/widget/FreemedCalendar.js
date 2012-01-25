/*
 * $Id$
 *
 * Authors:
 * 	Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2012 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

dojo.provide("mywidgets.widget.FreemedCalendar");
dojo.require("mywidgets.widget.Calendar");
dojo.widget.defineWidget(
	"mywidgets.widget.FreemedCalendar",
	mywidgets.widget.Calendar,
	{
		dataRelayUrl: null,
		createNewEntry: function (evt) {
			evt.stopPropagation();
			freemedLoad( 'org.freemedsoftware.ui.scheduler.book' );
			/*
			if(dojo.widget.byId('newentrydialog')){
				dojo.widget.byId('newentrydialog').show();
			}else{
				var width = "460px";
				var height = "350px";
				var div = document.createElement("div");
				div.style.position="absolute";
				div.style.width = width;
				div.style.height = height;
				dojo.body().appendChild(div);
				var pars = {
					contentClass: "mywidgets:FreemedCalendarDialogNewEntry",
					openerId: this.widgetId,
					title: "Create Appointment",
					iconSrc: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/calendar_add.gif"),
					id: "newentrydialog",
					width: width,
					height: height,
					resizable: false
				};
				var widget = dojo.widget.createWidget("mywidgets:CalendarDialog", pars, div);
			}
			*/
		}
	}
);

dojo.widget.defineWidget(
	"mywidgets.widget.FreemedCalendarDialogNewEntry",
	mywidgets.widget.CalendarDialogNewEntry,
	{
		templatePath: dojo.uri.dojoUri("../mywidgets/widget/templates/newfreemedcalendarentry.html")
	}
);

