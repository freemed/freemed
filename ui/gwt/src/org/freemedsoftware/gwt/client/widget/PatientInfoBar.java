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

package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientForm;
import org.freemedsoftware.gwt.client.screen.PatientScreen;

import com.bouwkamp.gwt.user.client.ui.RoundedPanel;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DisclosurePanel;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientInfoBar extends Composite {

	protected Label wPatientName;

	protected HTML editLink;
	
	protected HTML viewLink;

	protected HTML wPatientHiddenInfo;

	protected HTML wPatientVisibleInfo;

	protected Integer patientId = new Integer(0);
	protected String patientPracticeId = null;
	protected String patientDob = null;

	protected Image photoId = null;
	
	protected Image allergyImg = null;
	
	protected ScreenInterface parentScreen;
	
	protected String provideName;

	protected DisclosurePanel wDropdown = null;

	public void setExpandPatientDetails() {
		if (!wDropdown.isOpen())
			wDropdown.setOpen(true);
	}
	
	public String getProviderName(){
		return provideName;
	}

	public String getPatientDob() {
		return patientDob;
	}

	public PatientInfoBar() {
		final RoundedPanel container = new RoundedPanel();
		initWidget(container);
		container.setCornerColor("#ccccff");
		container.setStylePrimaryName("freemed-PatientInfoBar");
		container.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		horizontalPanel.setWidth("100%");
		container.add(horizontalPanel);

		wPatientName = new Label("");
		wPatientVisibleInfo = new HTML();
		HorizontalPanel horizontalsubPanel = new HorizontalPanel();
		horizontalsubPanel.setSpacing(2);
		horizontalPanel.add(horizontalsubPanel);
		horizontalPanel.setCellWidth(horizontalsubPanel, "70%");
		// horizontalsubPanel .add(wPatientName);
		horizontalsubPanel.add(wPatientVisibleInfo);

		allergyImg = new Image("resources/images/allergy.16x16.png");
		allergyImg.setVisible(false);
		horizontalsubPanel.add(allergyImg);
		
		if (CurrentState.isActionAllowed(PatientScreen.moduleName, AppConstants.MODIFY)) {
			editLink = new HTML(
					"(<a href=\"javascript:undefined;\" style='color:blue'>edit</a>)");
			editLink.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent event) {
					Util.closeTab(parentScreen);
					PatientForm patientForm = new PatientForm();
					Util.spawnTab(wPatientName.getText(), patientForm);
					patientForm.setPatientId(getPatientId());
				}
			});
			horizontalsubPanel.add(editLink);
		}
		if (CurrentState.isActionAllowed(PatientScreen.moduleName, AppConstants.READ)) {
			viewLink = new HTML(
					"(<a href=\"javascript:undefined;\" style='color:blue'>view</a>)");
			viewLink.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent event) {
					showPatientDetails();
				}
			});
			horizontalsubPanel.add(viewLink);
		}
		wDropdown = new DisclosurePanel("");

		final HorizontalPanel wDropdownContainer = new HorizontalPanel();
		final VerticalPanel patientInfoContainer = new VerticalPanel();
		// wDropdown.add(wDropdownContainer);
		wPatientHiddenInfo = new HTML();
		patientInfoContainer.add(wPatientHiddenInfo);

		wDropdownContainer.add(patientInfoContainer);

		final VerticalPanel clinicalPhotoIdPanel = new VerticalPanel();
		photoId = new Image();
		photoId.setWidth("70px");
		photoId.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				final Popup p = new Popup();
				MugshotWebcamWidget m = new MugshotWebcamWidget(getPatientId());
				p.setWidget(m);
				p.center();
				m.setOnFinishedCommand(new Command() {
					@Override
					public void execute() {
						p.hide();
					}
				});
			}
		});
		clinicalPhotoIdPanel.add(photoId);

		wDropdownContainer.add(clinicalPhotoIdPanel);

		wDropdown.add(wDropdownContainer);

		// adding DisclosurePanel panel into a horizontal panel
		horizontalPanel.add(wDropdown);
		horizontalPanel.setCellHorizontalAlignment(wDropdown,
				HasHorizontalAlignment.ALIGN_CENTER);

		final HorizontalPanel iconBar = new HorizontalPanel();

		final Image wBookAppointment = new Image(
				"resources/images/book_appt.32x32.png");
		wBookAppointment.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {

			}
		});
		iconBar.add(wBookAppointment);

		horizontalPanel.add(iconBar);
		horizontalPanel.setCellHorizontalAlignment(iconBar,
				HasHorizontalAlignment.ALIGN_RIGHT);

		if (patientId > 0) {
			loadData();
		}
	}

	public Integer getPatientId() {
		return patientId;
	}
	
	public String getPatientPracticeId() {
		return patientPracticeId;
	}

	/**
	 * Set patient information with HashMap returned from PatientInformation()
	 * method.
	 * 
	 * @param map
	 */
	public void setPatientFromMap(HashMap<String, String> map) {
		try {
			provideName=map.get("pcp");
			wPatientName.setText((String) map.get("patient_name"));
			String ptInfoHTML = (String) map.get("patient_name") + " "
			+ "[" + (String) map.get("date_of_birth") + "] "
			+ (String)map.get("age")+" old, "
			+ (String) map.get("ptid");
			if(map.get("pcp")!=null)
				ptInfoHTML = ptInfoHTML + "<br> <b>PCP</b>: "+map.get("pcp");
			if(map.get("facility")!=null)
				ptInfoHTML = ptInfoHTML + "<br> <b>Facility</b>: "+map.get("facility");
			if(map.get("pharmacy")!=null)
				ptInfoHTML = ptInfoHTML + "<br> <b>Pharmacy</b>: "+map.get("pharmacy");

			wPatientVisibleInfo.setHTML(ptInfoHTML);
			

		} catch (Exception e) {
			e.printStackTrace();
		}
		if(map.get("hasallergy").equalsIgnoreCase("true")){
			allergyImg.setVisible(true);
		}
		try {
			wPatientHiddenInfo.setHTML("<small>"
					+ (String) map.get("address_line_1") + "<br/>"
					+ (String) map.get("address_line_2") + "<br/>"
					+ (String) map.get("csz") + "<br/>" + "H:"
					+ (String) map.get("pthphone") + "<br/>" + "W:"
					+ (String) map.get("ptwphone") + "</small>");
		} catch (Exception e) {
			e.printStackTrace();
		}
		try {
			patientId = new Integer((String) map.get("id"));
			patientPracticeId = (String) map.get("ptid");
			patientDob = (String) map.get("date_of_birth");
		} catch (Exception e) {
			JsonUtil.debug(e.toString());
		} finally {
			loadData();
		}
	}

	public void loadData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Don't populate
		} else {
			photoId
					.setUrl(Util
							.getJsonRequest(
									"org.freemedsoftware.module.PhotographicIdentification.GetPhotoID",
									new String[] { patientId.toString() }));
		}
	}

	public ScreenInterface getParentScreen() {
		return parentScreen;
	}

	public void setParentScreen(ScreenInterface parentScreen) {
		this.parentScreen = parentScreen;
	}

	public void showPatientDetails(){
		Util.callApiMethod("PatientInterface", "PatientDetailedInformation", patientId, new CustomRequestCallback() {
		
			@Override
			public void onError() {
				// TODO Auto-generated method stub
		
			}
		
			@Override
			public void jsonifiedData(Object data) {
				// TODO Auto-generated method stub
				if(data!=null){
					
					HorizontalPanel infoPane=new HorizontalPanel();
					infoPane.add(loadPersonalInfo(data));
					infoPane.add(loadCoverageInfo(data));
					infoPane.add(loadAuthInfo(data));
					infoPane.setSpacing(3);
					Popup popup = new Popup();
					PopupView popupView = new PopupView(infoPane);
					popup.setNewWidget(popupView);
					popup.initialize();
					popup.show();
				}
			}
		
		}, "HashMap<String,String>");
	}
	
	
	
	
	public FlexTable loadPersonalInfo(Object data)
	{
		
		HashMap< String, String> retrieveData=(HashMap<String, String>)data;
		FlexTable personalInfoTable = new FlexTable();
		personalInfoTable.setBorderWidth(1);
		Label personaInfolbl=new Label("Personal Info");
		personaInfolbl.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		personalInfoTable.setWidget(0, 0, personaInfolbl);
		personalInfoTable.getFlexCellFormatter().setColSpan(0, 0, 2);
		personalInfoTable.getFlexCellFormatter().setHorizontalAlignment(0, 0, HorizontalPanel.ALIGN_CENTER);
		int row=1;
		personalInfoTable.setWidget(row, 0, new Label("Marital Status"));
		personalInfoTable.setWidget(row, 1, new Label(retrieveData.get("ptmarital")));
		row++;
		personalInfoTable.setWidget(row, 0, new Label("Religion"));
		personalInfoTable.setWidget(row, 1, new Label(PatientForm.returnReligion(Integer.parseInt(retrieveData.get("ptreligion")))));
		row++;
		personalInfoTable.setWidget(row, 0, new Label("Race"));
		personalInfoTable.setWidget(row, 1, new Label(PatientForm.returnRace(Integer.parseInt(retrieveData.get("ptrace")))));
		row++;
		personalInfoTable.setWidget(row, 0, new Label("Type OF Billing"));
		personalInfoTable.setWidget(row, 1, new Label(PatientForm.returnTypeOfBilling(retrieveData.get("ptbilltype"))));
		row++;
		personalInfoTable.setWidget(row, 0, new Label("Employee Status"));
		personalInfoTable.setWidget(row, 1, new Label(PatientForm.returnEmploymentStatus(retrieveData.get("ptempl"))));
		row++;
		personalInfoTable.setWidget(row, 0, new Label("Amount"));
		personalInfoTable.setWidget(row, 1, new Label(PatientForm.returnEmploymentStatus(retrieveData.get("ptbudg"))));
		
		return personalInfoTable;
	
	}
	
	
	
	
	
	public FlexTable loadCoverageInfo(Object data)
	{
		
		
		
		HashMap< String, String> retrieveData=(HashMap<String, String>)data;
		FlexTable coverageInfoTable = new FlexTable();
		coverageInfoTable.setBorderWidth(1);
		Label coverageInfolbl=new Label("Coverage Info");
		coverageInfolbl.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		coverageInfoTable.setWidget(0, 0, coverageInfolbl);
		coverageInfoTable.getFlexCellFormatter().setColSpan(0, 0, 2);
		coverageInfoTable.getFlexCellFormatter().setHorizontalAlignment(0, 0, HorizontalPanel.ALIGN_CENTER);
		
		
	int row=1;
		coverageInfoTable.getFlexCellFormatter().setHorizontalAlignment(0, 0, HorizontalPanel.ALIGN_CENTER);
		coverageInfoTable.setWidget(row, 0, new Label("Insurance Company"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("covinsco")));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Group - Plan Name"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("covplanname")));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Insurance ID Number"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("covpatinsno")));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Insurance Group Number"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("covpatgrpno")));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Start Date"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("coveffdt")));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Relationship to Insured"));
		coverageInfoTable.setWidget(row, 1, new Label(PatientCoverages.returnRelationshipToInsured(retrieveData.get("covrel"))));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Copay"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("covcopay")));
		row++;
		coverageInfoTable.setWidget(row, 0, new Label("Deductable"));
		coverageInfoTable.setWidget(row, 1, new Label(retrieveData.get("covdeduct")));
		
		return coverageInfoTable;
	
	}
	
	
	
	
	
	
	public FlexTable loadAuthInfo(Object data)
	{
		
		
		
		HashMap< String, String> retrieveData=(HashMap<String, String>)data;
		FlexTable AuthInfoTable = new FlexTable();
		AuthInfoTable.setBorderWidth(1);
		
		Label authInfolbl=new Label("Authorization Info");
		authInfolbl.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		
		
		AuthInfoTable.setWidget(0, 0, authInfolbl);
		AuthInfoTable.getFlexCellFormatter().setColSpan(0, 0, 2);
		AuthInfoTable.getFlexCellFormatter().setHorizontalAlignment(0, 0, HorizontalPanel.ALIGN_CENTER);
		
		
		int row=1;
		AuthInfoTable.getFlexCellFormatter().setHorizontalAlignment(0, 0, HorizontalPanel.ALIGN_CENTER);
		AuthInfoTable.setWidget(row, 0, new Label("Starting Date:"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authdtbegin")));
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Ending Date:"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authdtend")));
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Authorization Number"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authnum")));
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Authorization Type"));
		AuthInfoTable.setWidget(row, 1, new Label(PatientAuthorizations.returnAuthorizationType(Integer.parseInt(retrieveData.get("authtype")))));
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Authorization Provider"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authdtend")));
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Provider Identifier"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authprovid")));
		
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Authorizing Insurance Company"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authinsco")));
		
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Number of Visits"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authvisits")));
		
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Used Visits"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authvisitsused")));
		
		row++;
		AuthInfoTable.setWidget(row, 0, new Label("Comment"));
		AuthInfoTable.setWidget(row, 1, new Label(retrieveData.get("authcomment")));
		
		
		return AuthInfoTable;
	
	}
	
	
	
}
