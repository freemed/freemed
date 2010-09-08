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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.ActionItemsBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.FinancialWidget;
import org.freemedsoftware.gwt.client.widget.PatientProblemList;
import org.freemedsoftware.gwt.client.widget.PatientTagsWidget;
import org.freemedsoftware.gwt.client.widget.RecentAllergiesList;
import org.freemedsoftware.gwt.client.widget.RecentMedicationsList;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Element;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabBar;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SummaryScreen extends PatientScreenInterface {

	protected HashMap<String, String>[] dataStore;

	protected ActionItemsBox actionItemsBox = null;
	
	protected PatientProblemList problemList = null;

	protected RecentMedicationsList recentMedicationsList = null;

	protected RecentAllergiesList recentAllergiesList = null;

	protected PatientTagsWidget patientTags = null;
	
	FinancialWidget financialWidget=null;

//	protected Image photoId = null;

	public SummaryScreen() {
		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);
		flexTable.setSize("100%", "100%");
		
		final VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setWidth("70%");
		flexTable.setWidget(0, 0, verticalPanel);

		/*final Label actionItemsLabel = new Label("ACTION ITEMS");
		actionItemsLabel.setStylePrimaryName("label_bold");
		verticalPanel.add(actionItemsLabel);
		final SimplePanel cActionItems = new SimplePanel();
		cActionItems.setStylePrimaryName("freemed-PatientSummaryContainer");
		verticalPanel.add(cActionItems);
		verticalPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);*/

		//Adding messages panel
		actionItemsBox = new ActionItemsBox(false);
		actionItemsBox.setWidth("100%");
		actionItemsBox.setEnableCollapse(false);
		verticalPanel.add(actionItemsBox);
		
		final CustomTable customSortableTable = new CustomTable();
		verticalPanel.add(customSortableTable);
		
		final VerticalPanel problemContainer = new VerticalPanel();
		problemContainer.setWidth("70%");
		//final Label problemLabel = new Label("Problems");
		//problemLabel.setStylePrimaryName("freemed-PatientSummaryHeading");
		//problemContainer.add(problemLabel);

		final SimplePanel cProblemList = new SimplePanel();
		cProblemList.setStylePrimaryName("freemed-PatientSummaryContainer");
		problemList = new PatientProblemList();
		problemList.setPatientScreen(patientScreen);
		cProblemList.setWidget(problemList);
		problemContainer.add(cProblemList);
		flexTable.setWidget(1, 0, problemContainer);

		

		final VerticalPanel verticalPanel_1 = new VerticalPanel();
		verticalPanel_1.setWidth("70%");
		flexTable.setWidget(2, 0, verticalPanel_1);

		//final Label clinicalInformationLabel = new Label("Clinical Information");
		//clinicalInformationLabel
		//		.setStylePrimaryName("freemed-PatientSummaryHeading");
		//clinicalInformationLabel.setWidth("78%");
		//verticalPanel_1.add(clinicalInformationLabel);
		final SimplePanel cClinicalInformation = new SimplePanel();
		//cClinicalInformation
		//		.setStylePrimaryName("freemed-PatientSummaryContainer");
		cClinicalInformation.setWidth("100%");
		verticalPanel_1.add(cClinicalInformation);
		verticalPanel_1.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);

		final TabPanel clinicalInformationTabPanel = new TabPanel();
		clinicalInformationTabPanel.setSize("100%", "100%");
		TabBar tbar=clinicalInformationTabPanel.getTabBar();
		Element tabBarFirstChild=tbar.getElement().getFirstChildElement().getFirstChildElement().getFirstChildElement();
		tabBarFirstChild.setAttribute("width", "100%");
		tabBarFirstChild.setInnerHTML("CLINICAL INFORMATION");
		tabBarFirstChild.setClassName("label_bold");
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
		verticalPanel_2.setWidth("70%");
		flexTable.setWidget(3, 0, verticalPanel_2);

		financialWidget=new FinancialWidget();
		final SimplePanel cFinancial = new SimplePanel();
		cFinancial.setStylePrimaryName("freemed-PatientSummaryContainer");
		cFinancial.setWidget(financialWidget);
		verticalPanel_2.add(cFinancial);

		JsonUtil.debug("selectTab(0)");
		clinicalInformationTabPanel.selectTab(0);
	}

	public void loadData() {
		try{
			actionItemsBox.setPatientId(patientId);
			actionItemsBox.retrieveData();
		}catch (Exception e) {
			GWT.log("Exception", e);
		}
		
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
			financialWidget.setPatientId(patientId);
			financialWidget.setPatientScreen(patientScreen);
		} catch (Exception ex) {
			JsonUtil.debug("Exception in problemList: " + ex.toString());
		}
	}
	
	public void setPatientId(int patientId){
		problemList.setPatientId(patientId);
	}

	protected void populateProblems(){
		problemList.setPatientId(patientId);
	}

	protected void populateClinicalInformation(){
		recentMedicationsList.setPatientId(patientId);
		recentAllergiesList.setPatientId(patientId);
		patientTags.setPatient(patientId);
		
	}

	protected void populateActionItems(){
		//@ TODO actions items population code goes here 
	}
	
	protected void populateFinancial(){
		//@ TODO Financial items population code goes here 
	}
	
}
