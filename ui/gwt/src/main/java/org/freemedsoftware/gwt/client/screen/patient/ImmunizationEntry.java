/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.Date;

import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTextArea;
import org.freemedsoftware.gwt.client.widget.CustomTextBox;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class ImmunizationEntry extends PatientEntryScreenInterface {

	protected String moduleName = "Immunizations";

	protected CustomDatePicker dateOfWidget = null;

	protected SupportModuleWidget wProvider = null, wImmunization = null,
			wBodySite = null, wRoute = null, wAdminProvider = null;

	protected CustomTextBox wManufacturer = null, wLotNumber = null;

	protected CustomListBox wRecovered = null;

	protected CustomTextArea wNotes = null;

	public ImmunizationEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		int pos = 0;

		final Label dateOfLabel = new Label("Date of");
		flexTable.setWidget(pos, 0, dateOfLabel);
		dateOfWidget = new CustomDatePicker();
		dateOfWidget.setHashMapping("dateof");
		addEntryWidget("dateof", dateOfWidget);
		flexTable.setWidget(pos, 1, dateOfWidget);
		pos++;

		final Label providerLabel = new Label("Provider");
		flexTable.setWidget(pos, 0, providerLabel);
		wProvider = new SupportModuleWidget("ProviderModule");
		wProvider.setHashMapping("provider");
		addEntryWidget("provider", wProvider);
		flexTable.setWidget(pos, 1, wProvider);
		pos++;

		final Label adminProviderLabel = new Label("Administering Provider");
		flexTable.setWidget(pos, 0, adminProviderLabel);
		wAdminProvider = new SupportModuleWidget("ProviderModule");
		wAdminProvider.setHashMapping("admin_provider");
		addEntryWidget("admin_provider", wAdminProvider);
		flexTable.setWidget(pos, 1, wAdminProvider);
		pos++;

		final Label immunizationLabel = new Label("Immunization");
		flexTable.setWidget(pos, 0, immunizationLabel);
		wImmunization = new SupportModuleWidget("Bccdc");
		wImmunization.setHashMapping("immunization");
		addEntryWidget("immunization", wImmunization);
		flexTable.setWidget(pos, 1, wImmunization);
		pos++;

		final Label routeLabel = new Label("Route");
		flexTable.setWidget(pos, 0, routeLabel);
		wRoute = new SupportModuleWidget("RouteOfAdministration");
		wRoute.setHashMapping("route");
		addEntryWidget("route", wRoute);
		flexTable.setWidget(pos, 1, wRoute);
		pos++;

		final Label bodySiteLabel = new Label("Body Site");
		flexTable.setWidget(pos, 0, bodySiteLabel);
		wBodySite = new SupportModuleWidget("BodySite");
		wBodySite.setHashMapping("body_site");
		addEntryWidget("body_site", wBodySite);
		flexTable.setWidget(pos, 1, wBodySite);
		pos++;

		final Label manufacturerLabel = new Label("Manufacturer");
		flexTable.setWidget(pos, 0, manufacturerLabel);
		wManufacturer = new CustomTextBox();
		wManufacturer.setHashMapping("manufacturer");
		addEntryWidget("manufacturer", wManufacturer);
		flexTable.setWidget(pos, 1, wManufacturer);
		pos++;

		final Label lotNumberLabel = new Label("Lot Number");
		flexTable.setWidget(pos, 0, lotNumberLabel);
		wLotNumber = new CustomTextBox();
		wLotNumber.setHashMapping("lot_number");
		addEntryWidget("lot_number", wLotNumber);
		flexTable.setWidget(pos, 1, wLotNumber);
		pos++;

		final Label recoveredLabel = new Label("Recovered");
		flexTable.setWidget(pos, 0, recoveredLabel);
		wRecovered = new CustomListBox();
		wRecovered.addItem("Yes", "1");
		wRecovered.addItem("No", "0");
		wRecovered.setHashMapping("recovered");
		addEntryWidget("recovered", wRecovered);
		flexTable.setWidget(pos, 1, wRecovered);
		pos++;

		final Label notesLabel = new Label("Notes");
		flexTable.setWidget(pos, 0, notesLabel);
		wNotes = new CustomTextArea();
		wNotes.setHashMapping("notes");
		addEntryWidget("notes", wNotes);
		flexTable.setWidget(pos, 1, wNotes);
		pos++;

		// Submit stuff at the bottom of the form

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final CustomButton wSubmit = new CustomButton("Submit",
				AppConstants.ICON_ADD);
		buttonBar.add(wSubmit);
		wSubmit.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				submitForm();
			}
		});
		final CustomButton wReset = new CustomButton("Reset",
				AppConstants.ICON_CLEAR);
		buttonBar.add(wReset);
		wReset.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
		Util.setFocus(wProvider);
	}

	public String getModuleName() {
		return "Immunizations";
	}

	public void resetForm() {
		dateOfWidget.setValue(new Date(System.currentTimeMillis()));
		wProvider.clear();
		wImmunization.clear();
		wBodySite.clear();
		wRoute.clear();
		wManufacturer.setValue("");
		wLotNumber.setValue("");
		wRecovered.setWidgetValue("1");
		wNotes.setValue("");
		wProvider.setFocus(true);
	}

}
