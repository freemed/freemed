/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.ArrayList;
import java.util.List;

import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTextBox;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientIdEntry extends PatientEntryScreenInterface {

	protected String moduleName = "PatientIds";

	protected String patientIdName = "patient";

	private static List<PatientIdEntry> patientIdEntryList=null;
	
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static PatientIdEntry getInstance(){
		PatientIdEntry patientIdEntry=null; 
		if(patientIdEntryList==null)
			patientIdEntryList=new ArrayList<PatientIdEntry>();
		if(patientIdEntryList.size()<AppConstants.MAX_PATIENT_FOREIGNID_TABS)//creates & returns new next instance of PatientIdEntry
			patientIdEntryList.add(patientIdEntry=new PatientIdEntry());
		else{ //returns last instance of PatientIdEntry from list 
			patientIdEntry = patientIdEntryList.get(AppConstants.MAX_PATIENT_FOREIGNID_TABS-1);
		}	
		return patientIdEntry;
	}
	
	public static boolean removeInstance(PatientIdEntry patientIdEntry){
		return patientIdEntryList.remove(patientIdEntry);
	}
	
	public PatientIdEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		final Label providerLabel = new Label("Provider");
		flexTable.setWidget(0, 0, providerLabel);

		final SupportModuleWidget wProvider = new SupportModuleWidget(
				"ProviderModule");
		wProvider.setHashMapping("provider");
		addEntryWidget("provider", wProvider);
		flexTable.setWidget(0, 1, wProvider);

		final Label facilityLabel = new Label("Facility");
		flexTable.setWidget(1, 0, facilityLabel);

		final SupportModuleWidget wFacility = new SupportModuleWidget(
				"FacilityModule");
		wFacility.setHashMapping("facility");
		addEntryWidget("facility", wFacility);
		flexTable.setWidget(1, 1, wFacility);

		final Label foreignIdLabel = new Label("Foreign ID #");
		flexTable.setWidget(2, 0, foreignIdLabel);

		final CustomTextBox wForeignId = new CustomTextBox();
		wForeignId.setHashMapping("foreign_id");
		addEntryWidget("foreign_id", wForeignId);
		flexTable.setWidget(2, 1, wForeignId);

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final Button wSubmit = new Button("Submit");
		buttonBar.add(wSubmit);
		wSubmit.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				submitForm();
			}
		});
		final Button wReset = new Button("Reset");
		buttonBar.add(wReset);
		wReset.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
	}

	public String getModuleName() {
		return "PatientIds";
	}

	public void resetForm() {

	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
