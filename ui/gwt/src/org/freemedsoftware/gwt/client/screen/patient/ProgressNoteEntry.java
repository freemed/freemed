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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.PatientScreenInterface;

import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.RichTextArea;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.simpledatepicker.SimpleDatePicker;

public class ProgressNoteEntry extends PatientScreenInterface {

	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	protected HashMap patientMap = null;

	protected SimpleDatePicker wDate;

	protected TextArea wDescription;

	protected SuggestBox wProvider, wTemplate;

	protected RichTextArea S, O, A, P, I, E, R;

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

			}
		});
		final Button wReset = new Button("Reset");
		buttonBar.add(wReset);
		wReset.addClickListener(new ClickListener() {
			public void onClick(Widget w) {

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

		final SimpleDatePicker wDate = new SimpleDatePicker();
		flexTable.setWidget(1, 1, wDate);

		final Label providerLabel = new Label("Provider : ");
		flexTable.setWidget(2, 0, providerLabel);

		final SuggestBox wProvider = new SuggestBox();
		flexTable.setWidget(2, 1, wProvider);

		final Label descriptionLabel = new Label("Description : ");
		flexTable.setWidget(3, 0, descriptionLabel);

		wDescription = new TextArea();
		flexTable.setWidget(3, 1, wDescription);
		wDescription.setWidth("100%");

		final Label templateLabel = new Label("Template : ");
		flexTable.setWidget(4, 0, templateLabel);

		final SuggestBox wTemplate = new SuggestBox();
		flexTable.setWidget(4, 1, wTemplate);

		final SimplePanel containerS = new SimplePanel();
		tabPanel.add(containerS, "S");
		S = new RichTextArea();
		containerS.setWidget(S);
		S.setSize("100%", "100%");

		final SimplePanel containerO = new SimplePanel();
		tabPanel.add(containerO, "O");
		O = new RichTextArea();
		containerO.setWidget(O);
		O.setSize("100%", "100%");

		final SimplePanel containerA = new SimplePanel();
		tabPanel.add(containerA, "A");
		A = new RichTextArea();
		containerA.setWidget(A);
		A.setSize("100%", "100%");

		final SimplePanel containerP = new SimplePanel();
		tabPanel.add(containerP, "P");
		P = new RichTextArea();
		containerP.setWidget(P);
		P.setSize("100%", "100%");

		final SimplePanel containerI = new SimplePanel();
		tabPanel.add(containerI, "I");
		I = new RichTextArea();
		containerI.setWidget(I);
		I.setSize("100%", "100%");

		final SimplePanel containerE = new SimplePanel();
		tabPanel.add(containerE, "E");
		E = new RichTextArea();
		containerE.setWidget(E);
		E.setSize("100%", "100%");

		final SimplePanel containerR = new SimplePanel();
		tabPanel.add(containerR, "R");
		R = new RichTextArea();
		containerR.setWidget(R);
		R.setSize("100%", "100%");

		tabPanel.selectTab(0);
	}

}
