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
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.PatientTagsWidget;
import org.freemedsoftware.gwt.client.widget.RecentAllergiesList;
import org.freemedsoftware.gwt.client.widget.RecentMedicationsList;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
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

		final CustomSortableTable customSortableTable = new CustomSortableTable();
		verticalPanel.add(customSortableTable);

		final VerticalPanel verticalPanel_1 = new VerticalPanel();
		flexTable.setWidget(1, 0, verticalPanel_1);

		final Label clinicalInformationLabel = new Label("Clinical Information");
		verticalPanel_1.add(clinicalInformationLabel);

		final TabPanel clinicalInformationTabPanel = new TabPanel();
		verticalPanel_1.add(clinicalInformationTabPanel);

		final SimplePanel clinicalTagsPanel = new SimplePanel();
		final PatientTagsWidget patientTags = new PatientTagsWidget();
		patientTags.setPatient(patientId);
		clinicalTagsPanel.add(patientTags);
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
		if (Util.isStubbedMode()) {
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
		recentMedicationsList.setPatientId(patientId);
		clinicalMedicationsPanel.add(recentMedicationsList);
		final Image medicationsLabel = new Image();
		medicationsLabel.setUrl("resources/images/rx_prescriptions.32x32.png");
		medicationsLabel.setTitle("Medications");
		clinicalInformationTabPanel.add(clinicalMedicationsPanel,
				medicationsLabel);

		final SimplePanel clinicalAllergiesPanel = new SimplePanel();
		final RecentAllergiesList recentAllergiesList = new RecentAllergiesList();
		recentAllergiesList.setPatientId(patientId);
		clinicalAllergiesPanel.add(recentAllergiesList);
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
		if (Util.isStubbedMode()) {

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
								summaryTable.setText(iter + 1, 0,
										(String) r[iter].get("stamp"));
								summaryTable.setText(iter + 1, 1,
										(String) r[iter].get("type"));
								summaryTable.setText(iter + 1, 2,
										(String) r[iter].get("summary"));
							}
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}
}
