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

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.PatientTagsWidget;
import org.freemedsoftware.gwt.client.widget.RecentAllergiesList;
import org.freemedsoftware.gwt.client.widget.RecentMedicationsList;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SummaryScreen extends PatientScreenInterface {

	protected CustomSortableTable summaryTable;

	protected HashMap<String, String>[] dataStore;

	public SummaryScreen() {
		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);
		flexTable.setSize("100%", "100%");

		final VerticalPanel problemContainer = new VerticalPanel();
		final Label problemLabel = new Label("Problems");
		problemContainer.add(problemLabel);
		summaryTable = new CustomSortableTable();
		summaryTable.addColumn("Date", "date_mdy");
		summaryTable.addColumn("Type", "type");
		summaryTable.addColumn("Summary", "summary");
		problemContainer.add(summaryTable);
		flexTable.setWidget(0, 0, problemContainer);

		final VerticalPanel verticalPanel = new VerticalPanel();
		flexTable.setWidget(0, 1, verticalPanel);

		final Label actionItemsLabel = new Label("Action Items");
		verticalPanel.add(actionItemsLabel);
		verticalPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);

		final CustomSortableTable customSortableTable = new CustomSortableTable();
		verticalPanel.add(customSortableTable);

		final VerticalPanel verticalPanel_1 = new VerticalPanel();
		flexTable.setWidget(1, 0, verticalPanel_1);

		final Label clinicalInformationLabel = new Label("Clinical Information");
		verticalPanel_1.add(clinicalInformationLabel);
		verticalPanel_1.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);

		final TabPanel clinicalInformationTabPanel = new TabPanel();
		verticalPanel_1.add(clinicalInformationTabPanel);

		final SimplePanel clinicalTagsPanel = new SimplePanel();
		final PatientTagsWidget patientTags = new PatientTagsWidget();
		try {
			patientTags.setPatient(patientId);
			clinicalTagsPanel.add(patientTags);
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		final Image tagsLabel = new Image();
		tagsLabel.setUrl("resources/images/dashboard.32x32.png");
		tagsLabel.setTitle("Patient Tags");
		clinicalInformationTabPanel.add(clinicalTagsPanel, tagsLabel);

		final SimplePanel clinicalPhotoIdPanel = new SimplePanel();
		final Image photoIdLabel = new Image();
		photoIdLabel.setUrl("resources/images/patient.32x32.png");
		photoIdLabel.setTitle("Photo Identification");
		final Image photoId = new Image();
		photoId.setWidth("230px");
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Don't populate
		} else {
			photoId
					.setUrl(Util
							.getJsonRequest(
									"org.freemedsoftware.module.PhotographicIdentification.GetPhotoID",
									new String[] { photoId.toString() }));
		}
		clinicalPhotoIdPanel.add(photoId);
		clinicalInformationTabPanel.add(clinicalPhotoIdPanel, photoIdLabel);

		final SimplePanel clinicalMedicationsPanel = new SimplePanel();
		final RecentMedicationsList recentMedicationsList = new RecentMedicationsList();
		try {
			recentMedicationsList.setPatientId(patientId);
			clinicalMedicationsPanel.add(recentMedicationsList);
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		final Image medicationsLabel = new Image();
		medicationsLabel.setUrl("resources/images/rx_prescriptions.32x32.png");
		medicationsLabel.setTitle("Medications");
		clinicalInformationTabPanel.add(clinicalMedicationsPanel,
				medicationsLabel);

		final SimplePanel clinicalAllergiesPanel = new SimplePanel();
		final RecentAllergiesList recentAllergiesList = new RecentAllergiesList();
		try {
			recentAllergiesList.setPatientId(patientId);
			clinicalAllergiesPanel.add(recentAllergiesList);
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		final Image allergiesLabel = new Image();
		allergiesLabel.setUrl("resources/images/allergy.32x32.png");
		allergiesLabel.setTitle("Allergies");
		clinicalInformationTabPanel.add(clinicalAllergiesPanel, allergiesLabel);

		final VerticalPanel verticalPanel_2 = new VerticalPanel();
		flexTable.setWidget(1, 1, verticalPanel_2);

		final Label financialLabel = new Label("Financial");
		verticalPanel_2.add(financialLabel);

		final FlexTable financialFlexTable = new FlexTable();
		verticalPanel_2.add(financialFlexTable);
		clinicalInformationTabPanel.selectTab(0);

	}

	public void loadData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			int i = 0;
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-01");
				item.put("type", "test");
				item.put("summary", "Test item 1");
				populateTableEntry(i, item);
				i++;
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-02");
				item.put("type", "test");
				item.put("summary", "Test item 2");
				populateTableEntry(i, item);
				i++;
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-02");
				item.put("type", "test");
				item.put("summary", "Test item 3");
				populateTableEntry(i, item);
				i++;
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-03");
				item.put("type", "test");
				item.put("summary", "Test item 4");
				populateTableEntry(i, item);
				i++;
			}
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.PatientInterface.EmrAttachmentsByPatient",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								dataStore = r;
								for (int iter = 0; iter < r.length; iter++) {
									populateTableEntry(iter, r[iter]);
								}
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			PatientInterfaceAsync service = null;
			try {
				service = (PatientInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface");
			} catch (Exception e) {
				GWT.log("Failed to get proxy for PatientInterface", e);
			}
			service.EmrAttachmentsByPatient(patientId,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] r) {
							dataStore = r;
							for (int iter = 0; iter < r.length; iter++) {
								populateTableEntry(iter, r[iter]);
							}
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Populate summary table entry
	 * 
	 * @param pos
	 *            Item position, starting at 0.
	 * @param entry
	 *            Data for population
	 */
	protected void populateTableEntry(int pos, HashMap<String, String> entry) {
		summaryTable.setText(pos + 1, 0, entry.get("stamp"));
		summaryTable.setText(pos + 1, 1, entry.get("type"));
		summaryTable.setText(pos + 1, 2, entry.get("summary"));
	}
}
