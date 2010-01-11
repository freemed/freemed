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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTextArea;
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

public class DrugSampleEntry extends PatientEntryScreenInterface {

	protected SupportModuleWidget wDrugSample = null, wPrescriber = null;
	protected CustomTextBox wAmount = null;
	protected CustomTextArea wInstructions = null;

	private static List<DrugSampleEntry> drugSampleEntryList=null;
	
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static DrugSampleEntry getInstance(){
		DrugSampleEntry drugSampleEntry=null; 
		if(drugSampleEntryList==null)
			drugSampleEntryList=new ArrayList<DrugSampleEntry>();
		if(drugSampleEntryList.size()<AppConstants.MAX_PATIENT_DRUG_SAMPLE_TABS)//creates & returns new next instance of DrugSampleEntry
			drugSampleEntryList.add(drugSampleEntry=new DrugSampleEntry());
		else{ //returns last instance of DrugSampleEntry from list 
			drugSampleEntry = drugSampleEntryList.get(AppConstants.MAX_PATIENT_DRUG_SAMPLE_TABS-1);
		}	
		return drugSampleEntry;
	}
	
	public static boolean removeInstance(DrugSampleEntry drugSampleEntry){
		return drugSampleEntryList.remove(drugSampleEntry);
	}
	
	public DrugSampleEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		int i = 0;

		flexTable.setWidget(i, 0, new Label("Drug Sample"));
		wDrugSample = new SupportModuleWidget("DrugSampleInventory");
		wDrugSample.setHashMapping("drugsampleid");
		addEntryWidget("drugsampleid", wDrugSample);
		flexTable.setWidget(i, 1, wDrugSample);
		i++;

		flexTable.setWidget(i, 0, new Label("Provider"));
		wPrescriber = new SupportModuleWidget("ProviderModule");
		wPrescriber.setHashMapping("prescriber");
		addEntryWidget("prescriber", wPrescriber);
		flexTable.setWidget(i, 1, wPrescriber);

		flexTable.setWidget(i, 0, new Label("Amount"));
		wAmount = new CustomTextBox();
		wAmount.setHashMapping("amount");
		addEntryWidget("amount", wAmount);
		flexTable.setWidget(i, 1, wAmount);

		flexTable.setWidget(i, 0, new Label("Instructions"));
		wInstructions = new CustomTextArea();
		wInstructions.setHashMapping("instructions");
		addEntryWidget("instructions", wInstructions);
		flexTable.setWidget(i, 1, wInstructions);

		// Buttons

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
		return "DrugSamples";
	}

	public void resetForm() {
		Integer defaultProvider = CurrentState.getDefaultProvider();
		if (defaultProvider != null && defaultProvider > 0) {
			wPrescriber.setValue(defaultProvider);
		} else {
			wPrescriber.clear();
		}
		wDrugSample.clear();
		wAmount.setText("0");
		wInstructions.setText("");
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
