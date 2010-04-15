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

import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTextArea;
import org.freemedsoftware.gwt.client.widget.PatientWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientLinkEntry extends PatientEntryScreenInterface {

	protected String moduleName = "PatientLink";

	protected String patientIdName = "srcpatient";

	protected CustomTextArea wNotes = null;

	protected CustomListBox wType = null;

	protected PatientWidget wLinkPatient = null;	
	
	public PatientLinkEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		int pos = 0;

		final Label linkPatientLabel = new Label("Link Patient");
		flexTable.setWidget(pos, 0, linkPatientLabel);
		wLinkPatient = new PatientWidget();
		wLinkPatient.setHashMapping("dstpatient");
		addEntryWidget("dstpatient", wLinkPatient);
		flexTable.setWidget(pos, 1, wLinkPatient);
		pos++;

		final Label typeLabel = new Label("Relationship");
		flexTable.setWidget(pos, 0, typeLabel);
		wType = new CustomListBox();		
		wType.addItem("Spouse (01)", "01");
		wType.addItem("Natural Child, Insured has financial responsibility (19)", "19");
		wType.addItem("Natural Child, insured does not have financial responsibility (43)", "43");
		wType.addItem("Step Child (17)", "17");
		wType.addItem("Foster Child (10)", "10");
		wType.addItem("Ward of the Court (15)", "15");
		wType.addItem("Employee (20)", "20");
		wType.addItem("Unknown (21)", "21");
		wType.addItem("Handicapped Dependent (22)", "22");
		wType.addItem("Organ donor (39)", "39");
		wType.addItem("Cadaver donor (40)", "40");
		wType.addItem("Grandchild (05)", "05");
		wType.addItem("Niece/Nephew (07)", "07");
		wType.addItem("Injured Plaintiff (41)", "41");
		wType.addItem("Sponsored Dependent (23)", "23");
		wType.addItem("Minor Dependent of a Minor Dependent (24)", "24");
		wType.addItem("Father (32)", "32");
		wType.addItem("Mother (33)", "33");
		wType.addItem("Grandparent (04)", "04");
		wType.addItem("Life Partner (53)", "53");
		wType.addItem("Significant Other (29)", "29");
		wType.setHashMapping("linktype");
		addEntryWidget("linktype", wType);
		flexTable.setWidget(pos, 1, wType);
		pos++;

		final Label notesLabel = new Label("Details");
		flexTable.setWidget(pos, 0, notesLabel);
		wNotes = new CustomTextArea();
		wNotes.setHashMapping("linkdetails");
		addEntryWidget("linkdetails", wNotes);
		flexTable.setWidget(pos, 1, wNotes);
		pos++;

		// Submit stuff at the bottom of the form

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
		Util.setFocus(wLinkPatient);
	}

	public String getModuleName() {
		return "PatientLink";
	}

	public void resetForm() {
	}
	
}
