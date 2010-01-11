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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.PatientProblemList;
import org.freemedsoftware.gwt.client.widget.PatientTagsWidget;
import org.freemedsoftware.gwt.client.widget.RecentAllergiesList;
import org.freemedsoftware.gwt.client.widget.RecentMedicationsList;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SummaryScreen extends PatientScreenInterface {

	protected HashMap<String, String>[] dataStore;

	protected PatientProblemList problemList = null;

	protected RecentMedicationsList recentMedicationsList = null;

	protected RecentAllergiesList recentAllergiesList = null;

	protected PatientTagsWidget patientTags = null;

//	protected Image photoId = null;

	public SummaryScreen() {
		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);
		flexTable.setSize("100%", "100%");

		final VerticalPanel problemContainer = new VerticalPanel();
		final Label problemLabel = new Label("Problems");
		problemLabel.setStylePrimaryName("freemed-PatientSummaryHeading");
		problemContainer.add(problemLabel);

		final SimplePanel cProblemList = new SimplePanel();
		cProblemList.setStylePrimaryName("freemed-PatientSummaryContainer");
		problemList = new PatientProblemList();
		problemList.setPatientScreen(patientScreen);
		cProblemList.setWidget(problemList);
		problemContainer.add(cProblemList);

		flexTable.setWidget(0, 0, problemContainer);

		final VerticalPanel verticalPanel = new VerticalPanel();
		flexTable.setWidget(0, 1, verticalPanel);

		final Label actionItemsLabel = new Label("Action Items");
		actionItemsLabel.setStylePrimaryName("freemed-PatientSummaryHeading");
		verticalPanel.add(actionItemsLabel);
		final SimplePanel cActionItems = new SimplePanel();
		cActionItems.setStylePrimaryName("freemed-PatientSummaryContainer");
		verticalPanel.add(cActionItems);
		verticalPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);

		final CustomTable customSortableTable = new CustomTable();
		verticalPanel.add(customSortableTable);

		final VerticalPanel verticalPanel_1 = new VerticalPanel();
		flexTable.setWidget(1, 0, verticalPanel_1);

		final Label clinicalInformationLabel = new Label("Clinical Information");
		clinicalInformationLabel
				.setStylePrimaryName("freemed-PatientSummaryHeading");
		clinicalInformationLabel.setWidth("78%");
		verticalPanel_1.add(clinicalInformationLabel);
		final SimplePanel cClinicalInformation = new SimplePanel();
		cClinicalInformation
				.setStylePrimaryName("freemed-PatientSummaryContainer");
		cClinicalInformation.setWidth("90%");
		verticalPanel_1.add(cClinicalInformation);
		verticalPanel_1.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);

		final TabPanel clinicalInformationTabPanel = new TabPanel();
		cClinicalInformation.setWidget(clinicalInformationTabPanel);

		final SimplePanel clinicalTagsPanel = new SimplePanel();
		patientTags = new PatientTagsWidget();
		clinicalTagsPanel.add(patientTags);
		addChildWidget(patientTags);

		final Image tagsLabel = new Image();
		tagsLabel.setUrl("resources/images/dashboard.16x16.png");
		tagsLabel.setTitle("Patient Tags");
		clinicalInformationTabPanel.add(clinicalTagsPanel, tagsLabel);

		/*
		final SimplePanel clinicalPhotoIdPanel = new SimplePanel();
		final Image photoIdLabel = new Image();
		photoIdLabel.setUrl("resources/images/patient.16x16.png");
		photoIdLabel.setTitle("Photo Identification");
		photoId = new Image();
		photoId.setWidth("230px");
		clinicalPhotoIdPanel.add(photoId);
		clinicalInformationTabPanel.add(clinicalPhotoIdPanel, photoIdLabel);
		*/
		
		final SimplePanel clinicalMedicationsPanel = new SimplePanel();
		recentMedicationsList = new RecentMedicationsList();
		clinicalMedicationsPanel.add(recentMedicationsList);
		addChildWidget(recentMedicationsList);

		final Image medicationsLabel = new Image();
		medicationsLabel.setUrl("resources/images/rx_prescriptions.16x16.png");
		medicationsLabel.setTitle("Medications");
		clinicalInformationTabPanel.add(clinicalMedicationsPanel,
				medicationsLabel);

		final SimplePanel clinicalAllergiesPanel = new SimplePanel();
		recentAllergiesList = new RecentAllergiesList();
		clinicalAllergiesPanel.add(recentAllergiesList);
		addChildWidget(recentAllergiesList);

		final Image allergiesLabel = new Image();
		allergiesLabel.setUrl("resources/images/allergy.16x16.png");
		allergiesLabel.setTitle("Allergies");
		clinicalInformationTabPanel.add(clinicalAllergiesPanel, allergiesLabel);

		final VerticalPanel verticalPanel_2 = new VerticalPanel();
		flexTable.setWidget(1, 1, verticalPanel_2);

		final Label financialLabel = new Label("Financial");
		financialLabel.setStylePrimaryName("freemed-PatientSummaryHeading");
		verticalPanel_2.add(financialLabel);

		JsonUtil.debug("build financial flex table");
		final FlexTable financialFlexTable = new FlexTable();
		final SimplePanel cFinancial = new SimplePanel();
		cFinancial.setStylePrimaryName("freemed-PatientSummaryContainer");
		cFinancial.setWidget(financialFlexTable);
		verticalPanel_2.add(cFinancial);

		JsonUtil.debug("selectTab(0)");
		clinicalInformationTabPanel.selectTab(0);
	}

	public void loadData() {
		try {
			recentMedicationsList.setPatientId(patientId);
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}

		try {
			recentAllergiesList.setPatientId(patientId);
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Don't populate
		} else {
			/*
			photoId
					.setUrl(Util
							.getJsonRequest(
									"org.freemedsoftware.module.PhotographicIdentification.GetPhotoID",
									new String[] { patientId.toString() }));
			*/
		}

		try {
			patientTags.setPatient(patientId);
		} catch (Exception ex) {
			JsonUtil.debug("Exception in patientTags: " + ex.toString());
		}

		try {
			problemList.setPatientId(patientId);
			problemList.setPatientScreen(patientScreen);
		} catch (Exception ex) {
			JsonUtil.debug("Exception in problemList: " + ex.toString());
		}
	}
	
	public void setPatientId(int patientId){
		problemList.setPatientId(patientId);
	}

}
