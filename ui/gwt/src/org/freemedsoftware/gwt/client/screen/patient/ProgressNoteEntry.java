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
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.user.client.rpc.AsyncCallback;
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

	/**
	 * Internal id representing this record. If this is 0, we create a new one,
	 * otherwise we modify.
	 */
	protected Integer internalId = new Integer(0);

	protected SimpleDatePicker wDate;

	protected TextArea wDescription;

	protected SupportModuleWidget wProvider;

	protected SuggestBox wTemplate;

	protected RichTextArea S, O, A, P, I, E, R;

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

		final SimpleDatePicker wDate = new SimpleDatePicker();
		flexTable.setWidget(1, 1, wDate);

		final Label providerLabel = new Label("Provider : ");
		flexTable.setWidget(2, 0, providerLabel);

		wProvider = new SupportModuleWidget("ProviderModule");
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

	public void loadInternalId(Integer id) {
		ModuleInterfaceAsync service = getProxy();
		service.ModuleGetRecordMethod(moduleName, id, new AsyncCallback() {
			public void onSuccess(Object result) {
				/**
				 * @gwt.typeArgs <java.lang.String, java.lang.String>
				 */
				HashMap r = (HashMap) result;
				// TODO: finish this mapping
				wProvider.setValue(new Integer((String)r.get("pnotesphy")));
				S.setHTML((String) r.get("pnotes_S"));
				O.setHTML((String) r.get("pnotes_O"));
				A.setHTML((String) r.get("pnotes_A"));
				P.setHTML((String) r.get("pnotes_P"));
				I.setHTML((String) r.get("pnotes_I"));
				E.setHTML((String) r.get("pnotes_E"));
				R.setHTML((String) r.get("pnotes_R"));
			}

			public void onFailure(Throwable t) {

			}
		});
	}

	public void submitForm() {
		ModuleInterfaceAsync service = getProxy();
		// Form hashmap ...
		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		final HashMap rec = new HashMap();
		rec.put("pnotesdt", (String) wDate.getSelectedDate().toString());
		rec.put("pnotesdescrip", (String) wDescription.toString());
		rec.put("pnotesdoc", (String) wProvider.getValue().toString());
		rec.put("pnotes_S", (String) S.getHTML());
		rec.put("pnotes_O", (String) O.getHTML());
		rec.put("pnotes_A", (String) A.getHTML());
		rec.put("pnotes_P", (String) P.getHTML());
		rec.put("pnotes_I", (String) I.getHTML());
		rec.put("pnotes_E", (String) E.getHTML());
		rec.put("pnotes_R", (String) R.getHTML());

		if (!internalId.equals(new Integer(0))) {
			// Modify
			rec.put("id", (String) internalId.toString());
			service.ModuleModifyMethod(moduleName, rec, new AsyncCallback() {
				public void onSuccess(Object result) {
					Toaster t = state.getToaster();
					t.addItem("progressNotes", "Updated progress note.",
							Toaster.TOASTER_INFO);
				}

				public void onFailure(Throwable th) {
					Toaster t = state.getToaster();
					t.addItem("progressNotes",
							"Failed to update progress note.",
							Toaster.TOASTER_ERROR);
				}
			});
		} else {
			// Add
			service.ModuleAddMethod(moduleName, rec, new AsyncCallback() {
				public void onSuccess(Object result) {
					Toaster t = state.getToaster();
					t.addItem("progressNotes", "Added progress note.",
							Toaster.TOASTER_INFO);
				}

				public void onFailure(Throwable th) {
					Toaster t = state.getToaster();
					t.addItem("progressNotes", "Failed to add progress note.",
							Toaster.TOASTER_ERROR);
				}
			});
		}
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
