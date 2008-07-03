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

import java.util.Date;
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
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.simpledatepicker.SimpleDatePicker;

public class PatientCorrespondenceEntry extends PatientScreenInterface {

	/**
	 * Internal id representing this record. If this is 0, we create a new one,
	 * otherwise we modify.
	 */
	protected Integer internalId = new Integer(0);

	protected SimpleDatePicker wDate;

	protected SupportModuleWidget wFrom, wTo;

	protected TextBox wSubject;

	protected RichTextArea wText;

	final protected String moduleName = "PatientCorrespondence";

	public PatientCorrespondenceEntry() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		final Label dateLabel = new Label("Date : ");
		flexTable.setWidget(0, 0, dateLabel);

		wDate = new SimpleDatePicker();
		flexTable.setWidget(0, 1, wDate);

		final Label fromLabel = new Label("From : ");
		flexTable.setWidget(1, 0, fromLabel);

		wFrom = new SupportModuleWidget();
		wFrom.setModuleName("ProviderModule");
		flexTable.setWidget(1, 1, wFrom);

		final Label subjectLabel = new Label("Subject : ");
		flexTable.setWidget(2, 0, subjectLabel);

		wSubject = new TextBox();
		flexTable.setWidget(2, 1, wSubject);
		wSubject.setWidth("100%");

		final Label templateLabel = new Label("Template : ");
		flexTable.setWidget(3, 0, templateLabel);

		final Label messageLabel = new Label("Message : ");
		flexTable.setWidget(4, 0, messageLabel);

		wText = new RichTextArea();
		flexTable.setWidget(4, 1, wText);

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
	}

	public void loadInternalId(Integer id) {
		ModuleInterfaceAsync service = getProxy();
		service.ModuleGetRecordMethod(moduleName, id, new AsyncCallback() {
			public void onSuccess(Object result) {
				/**
				 * @gwt.typeArgs <java.lang.String, java.lang.String>
				 */
				HashMap r = (HashMap) result;
				wFrom.setValue(new Integer((String) r.get("letterfrom")));
				wSubject.setText((String) r.get("lettersubject"));
				wDate.setSelectedDate(new Date((String) r.get("letterdt")));
				wText.setHTML((String) r.get("lettertext"));
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
		rec.put("letterdt", (String) wDate.getSelectedDate().toString());
		rec.put("letterpatient", (String) patientId.toString());
		rec.put("letterfrom", (String) wFrom.getValue().toString());
		rec.put("lettersubject", (String) wSubject.getText());

		if (!internalId.equals(new Integer(0))) {
			// Modify
			rec.put("id", (String) internalId.toString());
			service.ModuleModifyMethod(moduleName, rec, new AsyncCallback() {
				public void onSuccess(Object result) {
					Toaster t = state.getToaster();
					t.addItem("patientCorrespondence",
							"Updated correspondence.", Toaster.TOASTER_INFO);
				}

				public void onFailure(Throwable th) {
					Toaster t = state.getToaster();
					t.addItem("patientCorrespondence",
							"Failed to update correspondence.",
							Toaster.TOASTER_ERROR);
				}
			});
		} else {
			// Add
			service.ModuleAddMethod(moduleName, rec, new AsyncCallback() {
				public void onSuccess(Object result) {
					Toaster t = state.getToaster();
					t.addItem("patientCorrespondence", "Added correspondence.",
							Toaster.TOASTER_INFO);
				}

				public void onFailure(Throwable th) {
					Toaster t = state.getToaster();
					t.addItem("patientCorrespondence",
							"Failed to add correspondence.",
							Toaster.TOASTER_ERROR);
				}
			});
		}
	}

	public void resetForm() {

	}

}
