package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.EntryScreenInterface;
import org.freemedsoftware.gwt.client.widget.SimpleUIBuilder.WidgetType;

public class RoomEntry extends EntryScreenInterface {

	public RoomEntry() {
		initWidget(ui);
	}

	@Override
	public String validateData(HashMap<String, String> data) {
		return null;
	}

	@Override
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

	@Override
	protected String getModuleName() {
		return "RoomModule";
	}

}
