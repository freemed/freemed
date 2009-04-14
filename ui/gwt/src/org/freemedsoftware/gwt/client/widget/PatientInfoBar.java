/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng 	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.screen.SchedulerScreen;

import com.bouwkamp.gwt.user.client.ui.RoundedPanel;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.DisclosurePanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

//To add Items, do the following:
// 1.) define them in iconParser()
// 2.) Add them to the panel in PatientInfobarSettingsPopup.listItems


public class PatientInfoBar extends WidgetInterface implements Command{

	protected Label wPatientName;

	protected HTML wPatientHiddenInfo;

	protected Integer patientId = new Integer(0);

	protected HorizontalPanel iconBar = new HorizontalPanel();
	
	protected ArrayList<String> activeIcons = new ArrayList<String>();
	
	protected Command[] commandList= {null};
	
	protected ListBox lB = new ListBox();

	public PatientInfoBar() {
		final RoundedPanel container = new RoundedPanel();
		initWidget(container);
		container.setCornerColor("#ccccff");
		container.setStylePrimaryName("freemed-PatientInfoBar");
		container.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		horizontalPanel.setWidth("100%");
		container.add(horizontalPanel);

		wPatientName = new Label("");
		horizontalPanel.add(wPatientName);

		final DisclosurePanel wDropdown = new DisclosurePanel("");
		final VerticalPanel wDropdownContainer = new VerticalPanel();
		wDropdown.add(wDropdownContainer);
		wPatientHiddenInfo = new HTML();
		wDropdownContainer.add(wPatientHiddenInfo);
		horizontalPanel.add(wDropdown);
		horizontalPanel.setCellHorizontalAlignment(wDropdown,
				HasHorizontalAlignment.ALIGN_CENTER);

		horizontalPanel.add(iconBar);
		horizontalPanel.setCellHorizontalAlignment(iconBar,
				HasHorizontalAlignment.ALIGN_RIGHT);

		clear();
		onSetState(this);
	}

	/**
	 * Set patient information with HashMap returned from PatientInformation()
	 * method.
	 * 
	 * @param map
	 */
	public void setPatientFromMap(HashMap<String, String> map) {
		try {
			wPatientName.setText((String) map.get("patient_name"));
		} catch (Exception e) {
		}
		try {
			wPatientHiddenInfo.setHTML("<small>"
					+ (String) map.get("address_line_1") + "<br/>"
					+ (String) map.get("address_line_2") + "<br/>"
					+ (String) map.get("csz") + "<br/>" + "H:"
					+ (String) map.get("pthphone") + "<br/>" + "W:"
					+ (String) map.get("ptwphone") + "</small>");
		} catch (Exception e) {
		}
		try {
			patientId = new Integer((String) map.get("id"));
		} catch (Exception e) {
		}
	}

	protected void clear() {
		iconBar.clear();
		// STATIC Icon definitions
		newIcon("resources/images/settings.32x32.png", new Command() {
			public void execute() {
				settingsPopup();
			}
		});
	}
	
	public void execute() {
		loadConfig();
	}
	
	protected void iconParser(String s) {
		JsonUtil.debug("s =" + s);
		activeIcons.add(s);
		if (s == "Scheduler") {
			newIcon("resources/images/book_appt.32x32.png", new Command() {
				public void execute() {
					Util.spawnTab("Scheduler", new SchedulerScreen(), state);
				}

			});
		} else {
			//remove last Icon as it is something crambled
			activeIcons.remove(s);
		}
		
		
	}

	protected void loadConfig() {
		try {
		String config = state.getUserConfig("PatientInfoBar");
		String[] array = {};

		if (config != "") {
			array = (String[]) JsonUtil.shoehornJson(JSONParser.parse(config),
					"String[]");
			if (array.length != 0) {
			for (int i = 0; i < array.length; i++) {
				if (array[i] != "") {
					iconParser(array[i]);
				}
			}
			}
		}
		} catch (Exception e) {
			JsonUtil.debug("PatientInfoBar.java: Caught exception: "+ e.toString());
		}
	}
	
	public void populateWidget() {
		for (int i = 0; i < commandList.length; i++) {
			commandList[i].execute();
		}
	}
	
	protected void saveConfig() {	
		if (state != null) {
			try {
			state.setUserConfig("PatientInfoBar", (String) JsonUtil.jsonify(activeIcons
				.toArray(new String[0])));
			} catch (Exception e) {
				JsonUtil.debug("PatientInfoBar: Caught exception" + e.toString());
			}
		} else {
			commandList[commandList.length] = new Command() {
				public void execute() {
					try {
					state.setUserConfig("PatientInfoBar", (String) JsonUtil.jsonify(activeIcons.toArray(new String[0])));
					state.getToaster().addItem("PatientInfoBar", "InfoBar Data saved");
					} catch (Exception e) {
						JsonUtil.debug("PatientInfoBar: Caught exception" + e.toString());
					}
				}
			};
		}
	}
	
	protected void newIcon(String imagePath, final Command c) {
		final Image i = new Image(imagePath);
		i.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				c.execute();
			}
		});
		iconBar.add(i);
		
	}
	
	protected void settingsPopup() {
		String[] listItems = {"Scheduler"};
		final PopupPanel popup = new PopupPanel();
		final VerticalPanel vPanel = new VerticalPanel();
		popup.add(vPanel);
		
		
		lB.setMultipleSelect(true);
		lB.setVisibleItemCount(5);
		vPanel.add(lB);
		
		for (int i = 0; i <listItems.length; i++) {
			lB.addItem(listItems[i]);
		}
		
		
		
		Button buttonOK = new Button("Save");
		buttonOK.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				clear();
				for (int i = 0; i < lB.getItemCount(); i++) {
					if (lB.isItemSelected(i) == true) {
						iconParser(lB.getItemText(i));
					}
					
				}
				saveConfig();
				popup.hide();
			}
		});
		
		Button buttonCancel = new Button("Cancel");
		buttonCancel.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				popup.hide();
			}
		});
		
		vPanel.add(buttonOK);
		vPanel.add(buttonCancel);
		

		
		popup.setStyleName("freemed-HelpPopup");
		popup.center();
		
	}

	

}
