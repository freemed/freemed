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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
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

public class DrugSampleEntry extends PatientEntryScreenInterface {

	protected SupportModuleWidget wDrugSample = null, wPrescriber = null;
	protected CustomTextBox wAmount = null;
	protected CustomTextArea wInstructions = null;
	
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
		final CustomButton wSubmit = new CustomButton("Submit",AppConstants.ICON_ADD);
		buttonBar.add(wSubmit);
		wSubmit.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				submitForm();
			}
		});
		final CustomButton wReset = new CustomButton("Reset",AppConstants.ICON_CLEAR);
		buttonBar.add(wReset);
		wReset.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
		Util.setFocus(wDrugSample);
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
		wDrugSample.setFocus(true);
	}
	
}
