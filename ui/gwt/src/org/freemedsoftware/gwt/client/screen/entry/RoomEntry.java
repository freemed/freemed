/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.screen.entry;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.EntryScreenInterface;
import org.freemedsoftware.gwt.client.widget.SimpleUIBuilder.WidgetType;

public class RoomEntry extends EntryScreenInterface {

	public RoomEntry() {
		initWidget(ui);
	}

	public String validateData(HashMap<String, String> data) {
		return null;
	}

	protected void buildForm() {
		ui.addWidget("roomname", "Name", WidgetType.TEXT, "20", null);
		ui.addWidget("roompos", "Place of Service", WidgetType.MODULE,
				"FacilityModule", null);
		ui.addWidget("roomdescrip", "Description", WidgetType.TEXT, "40", null);
		ui.addWidget("roomequipment", "Equipment", WidgetType.MODULE_MULTIPLE,
				"RoomEquipment", null);
		ui.addWidget("roomsurgery", "Surgery Enabled", WidgetType.SELECT,
				"No|n,Yes|y", null);
		ui.addWidget("roombooking", "Booking Enabled", WidgetType.SELECT,
				"Yes|y,No|n", null);
		ui.addWidget("roomipaddr", "Room IP Address", WidgetType.TEXT, "20",
				null);
	}

	protected String getModuleName() {
		return "RoomModule";
	}

}
