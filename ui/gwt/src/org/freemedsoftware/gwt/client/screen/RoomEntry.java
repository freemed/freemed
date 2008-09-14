package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.EntryScreenInterface;

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
		ui.addWidget("roomname", "Name", "text", "20", null);
		ui.addWidget("roompos", "Place of Service", "module", "FacilityModule",
				null);
		ui.addWidget("roomdescrip", "Description", "text", "40", null);
		ui.addWidget("roomequipment", "Equipment", "modulemultiple",
				"RoomEquipment", null);
		ui.addWidget("roomsurgery", "Surgery Enabled", "select", "No|n,Yes|y",
				null);
		ui.addWidget("roombooking", "Booking Enabled", "select", "Yes|y,No|n",
				null);
		ui.addWidget("roomipaddr", "Room IP Address", "text", "20", null);
	}

	@Override
	protected String getModuleName() {
		return "RoomModule";
	}

}
