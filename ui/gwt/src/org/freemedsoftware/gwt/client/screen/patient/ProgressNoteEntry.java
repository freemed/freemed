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

import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomRichTextArea;
import org.freemedsoftware.gwt.client.widget.CustomTextArea;
import org.freemedsoftware.gwt.client.widget.RecentMedicationsList;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.simpledatepicker.SimpleDatePicker;

public class ProgressNoteEntry extends PatientEntryScreenInterface {

	protected CustomDatePicker wDate;

	protected CustomTextArea wDescription;

	protected SupportModuleWidget wProvider;

	protected SuggestBox wTemplate;

	protected CustomRichTextArea S, O, A, P, I, E, R;

	final protected String moduleName = "ProgressNotes";

	public ProgressNoteEntry() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final Button wSubmit = new Button("Submit");
		buttonBar.add(wSubmit);
		wSubmit.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				submitForm();
			}
		});
		final Button wReset = new Button("Reset");
		buttonBar.add(wReset);
		wReset.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);

		final SimplePanel simplePanel = new SimplePanel();
		tabPanel.add(simplePanel, "Summary");

		final FlexTable flexTable = new FlexTable();
		simplePanel.setWidget(flexTable);
		flexTable.setSize("100%", "100%");

		final Label label = new Label("Import Previous Notes for ");
		flexTable.setWidget(0, 0, label);

		final HorizontalPanel dateContainer = new HorizontalPanel();
		final SimpleDatePicker wImportDate = new SimpleDatePicker();
		wImportDate.setWeekendSelectable(true);
		dateContainer.add(wImportDate);
		final Button wImportPrevious = new Button("Import");
		dateContainer.add(wImportPrevious);
		flexTable.setWidget(0, 1, dateContainer);

		final Label dateLabel = new Label("Date : ");
		flexTable.setWidget(1, 0, dateLabel);

		wDate = new CustomDatePicker();
		wDate.setHashMapping("pnotesdt");
		addEntryWidget("pnotesdt", wDate);
		flexTable.setWidget(1, 1, wDate);

		final Label providerLabel = new Label("Provider : ");
		flexTable.setWidget(2, 0, providerLabel);

		wProvider = new SupportModuleWidget("ProviderModule");
		wProvider.setHashMapping("pnotesphy");
		addEntryWidget("pnotesphy", wProvider);
		flexTable.setWidget(2, 1, wProvider);

		final Label descriptionLabel = new Label("Description : ");
		flexTable.setWidget(3, 0, descriptionLabel);

		wDescription = new CustomTextArea();
		wDescription.setHashMapping("pnotesdescrip");
		addEntryWidget("pnotesdescrip", wDescription);
		flexTable.setWidget(3, 1, wDescription);
		wDescription.setWidth("100%");

		final Label templateLabel = new Label("Template : ");
		flexTable.setWidget(4, 0, templateLabel);

		final SuggestBox wTemplate = new SuggestBox();
		flexTable.setWidget(4, 1, wTemplate);

		final SimplePanel containerS = new SimplePanel();
		tabPanel.add(containerS, "S");
		S = new CustomRichTextArea();
		S.setHashMapping("pnotes_S");
		addEntryWidget("pnotes_S", S);
		containerS.setWidget(S);
		S.setSize("100%", "100%");

		final SimplePanel containerO = new SimplePanel();
		tabPanel.add(containerO, "O");
		O = new CustomRichTextArea();
		O.setHashMapping("pnotes_O");
		addEntryWidget("pnotes_O", O);
		containerO.setWidget(O);
		O.setSize("100%", "100%");

		final SimplePanel containerA = new SimplePanel();
		tabPanel.add(containerA, "A");
		A = new CustomRichTextArea();
		A.setHashMapping("pnotes_A");
		addEntryWidget("pnotes_A", A);
		containerA.setWidget(A);
		A.setSize("100%", "100%");

		final SimplePanel containerP = new SimplePanel();
		tabPanel.add(containerP, "P");
		P = new CustomRichTextArea();
		P.setHashMapping("pnotes_P");
		addEntryWidget("pnotes_P", P);
		containerP.setWidget(P);
		P.setSize("100%", "100%");

		final SimplePanel containerI = new SimplePanel();
		tabPanel.add(containerI, "I");
		I = new CustomRichTextArea();
		I.setHashMapping("pnotes_I");
		addEntryWidget("pnotes_I", I);
		containerI.setWidget(I);
		I.setSize("100%", "100%");

		final SimplePanel containerE = new SimplePanel();
		tabPanel.add(containerE, "E");
		E = new CustomRichTextArea();
		E.setHashMapping("pnotes_E");
		addEntryWidget("pnotes_E", E);
		containerE.setWidget(E);
		E.setSize("100%", "100%");

		final VerticalPanel containerR = new VerticalPanel();
		tabPanel.add(containerR, "R");
		R = new CustomRichTextArea();
		R.setHashMapping("pnotes_R");
		addEntryWidget("pnotes_R", R);
		containerR.add(R);
		R.setSize("100%", "100%");
		final RecentMedicationsList recentMedicationsList = new RecentMedicationsList();
		recentMedicationsList.setPatientId(patientId);
		containerR.add(recentMedicationsList);

		tabPanel.selectTab(0);
	}

	public String getModuleName() {
		return "ProgressNotes";
	}

	public void resetForm() {
		S.setHTML(new String(""));
		O.setHTML(new String(""));
		A.setHTML(new String(""));
		P.setHTML(new String(""));
		I.setHTML(new String(""));
		E.setHTML(new String(""));
		R.setHTML(new String(""));
	}
}
