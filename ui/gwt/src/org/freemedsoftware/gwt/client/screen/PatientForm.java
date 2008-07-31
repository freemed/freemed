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

package org.freemedsoftware.gwt.client.screen;

import java.util.Date;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.simpledatepicker.SimpleDatePicker;

public class PatientForm extends ScreenInterface {

	protected SimpleDatePicker wDob;

	protected TextBox emailAddress;

	protected TextBox phoneMobile;

	protected TextBox phoneFax;

	protected TextBox phoneWork;

	protected TextBox phoneHome;

	protected CustomListBox preferredContact;

	protected SuggestBox suggestBox;

	protected TextBox addressLine2;

	protected TextBox addressLine1;

	protected CustomListBox addressRelationship, addressActive, addressType;

	protected CustomListBox wTitle;

	protected TextBox wLastName;

	protected TextBox wFirstName;

	protected TextBox wMiddleName;

	protected CustomListBox wSuffix;

	protected CustomListBox wGender;

	protected Button submitButton, addressAddButton, addressModifyButton;

	protected Integer patientId = new Integer(0);

	protected CustomSortableTable addressContainer;

	public final static String moduleName = "PatientModule";

	public PatientForm() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		final FlexTable demographicsTable = new FlexTable();
		tabPanel.add(demographicsTable, "Demographics");

		final Label titleLabel = new Label("Title");
		demographicsTable.setWidget(0, 0, titleLabel);

		wTitle = new CustomListBox();
		demographicsTable.setWidget(0, 1, wTitle);
		wTitle.addItem("[Choose title]", "");
		wTitle.addItem("Mr");
		wTitle.addItem("Mrs");
		wTitle.addItem("Ms");
		wTitle.addItem("Dr");
		wTitle.addItem("Fr");
		wTitle.setVisibleItemCount(1);

		final Label lastNameLabel = new Label("Last Name");
		demographicsTable.setWidget(1, 0, lastNameLabel);

		wLastName = new TextBox();
		demographicsTable.setWidget(1, 1, wLastName);
		wLastName.setTabIndex(1);
		wLastName.setWidth("100%");

		final Label firstNameLabel = new Label("First Name");
		demographicsTable.setWidget(2, 0, firstNameLabel);

		wFirstName = new TextBox();
		demographicsTable.setWidget(2, 1, wFirstName);
		wFirstName.setTabIndex(2);
		wFirstName.setWidth("100%");

		final Label middleNameLabel = new Label("Middle Name");
		demographicsTable.setWidget(3, 0, middleNameLabel);

		wMiddleName = new TextBox();
		demographicsTable.setWidget(3, 1, wMiddleName);
		wMiddleName.setTabIndex(3);
		wMiddleName.setWidth("100%");

		final Label suffixLabel = new Label("Suffix");
		demographicsTable.setWidget(4, 0, suffixLabel);

		wSuffix = new CustomListBox();
		demographicsTable.setWidget(4, 1, wSuffix);
		wSuffix.setTabIndex(4);
		wSuffix.addItem("[No Suffix]", "");
		wSuffix.addItem("Sr");
		wSuffix.addItem("Jr");
		wSuffix.addItem("II");
		wSuffix.addItem("III");
		wSuffix.addItem("IV");
		wSuffix.setVisibleItemCount(1);

		final Label genderLabel = new Label("Gender");
		demographicsTable.setWidget(5, 0, genderLabel);

		wGender = new CustomListBox();
		demographicsTable.setWidget(5, 1, wGender);
		wGender.setTabIndex(5);
		wGender.addItem("[Choose Value]", "");
		wGender.addItem("Male", "m");
		wGender.addItem("Female", "f");
		wGender.addItem("Transgendered", "t");
		wGender.setVisibleItemCount(1);

		final Label dateOfBirthLabel = new Label("Date of Birth");
		demographicsTable.setWidget(6, 0, dateOfBirthLabel);

		wDob = new SimpleDatePicker();
		demographicsTable.setWidget(6, 1, wDob);
		wDob.setWeekendSelectable(true);
		wDob.setTabIndex(6);

		final Label patientPracticeIdLabel = new Label("Patient Practice ID");
		demographicsTable.setWidget(7, 0, patientPracticeIdLabel);

		final TextBox wPatientId = new TextBox();
		demographicsTable.setWidget(7, 1, wPatientId);
		wPatientId.setTabIndex(7);
		wPatientId.setWidth("100%");

		final FlexTable addressTable = new FlexTable();
		tabPanel.add(addressTable, "Address");

		final Label typeOfAddressLabel = new Label("Type of Address");
		addressTable.setWidget(0, 0, typeOfAddressLabel);

		addressType = new CustomListBox();
		addressType.addItem("Home", "H");
		addressType.addItem("Work", "W");
		addressTable.setWidget(0, 1, addressType);
		addressType.setVisibleItemCount(1);

		final Label relationshipToPatientLabel = new Label(
				"Relationship to Patient");
		addressTable.setWidget(1, 0, relationshipToPatientLabel);

		addressRelationship = new CustomListBox();
		addressRelationship.addItem("Self", "S");
		addressRelationship.addItem("Parents", "P");
		addressRelationship.addItem("Cousin", "C");
		addressRelationship.addItem("Shelter", "SH");
		addressRelationship.addItem("Unrelated", "U");
		addressTable.setWidget(1, 1, addressRelationship);
		addressRelationship.setVisibleItemCount(1);

		final Label addressLine1Label = new Label("Address Line 1");
		addressTable.setWidget(2, 0, addressLine1Label);

		addressLine1 = new TextBox();
		addressTable.setWidget(2, 1, addressLine1);
		addressLine1.setWidth("100%");

		final Label addressLine2Label = new Label("Address Line 2");
		addressTable.setWidget(3, 0, addressLine2Label);

		addressLine2 = new TextBox();
		addressTable.setWidget(3, 1, addressLine2);
		addressLine2.setWidth("100%");

		final Label cityStateLabel = new Label("City / State / Postal");
		addressTable.setWidget(4, 0, cityStateLabel);

		suggestBox = new SuggestBox();
		addressTable.setWidget(4, 1, suggestBox);

		final Label activeAddressLabel = new Label("Active Address");
		addressTable.setWidget(5, 0, activeAddressLabel);

		addressActive = new CustomListBox();
		addressActive.addItem("Yes", "1");
		addressActive.addItem("No", "0");
		addressTable.setWidget(5, 1, addressActive);
		addressActive.setVisibleItemCount(1);

		final HorizontalPanel horizontalPanel_1 = new HorizontalPanel();
		addressTable.setWidget(6, 0, horizontalPanel_1);
		horizontalPanel_1
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		addressTable.getFlexCellFormatter().setColSpan(6, 0, 2);

		addressAddButton = new Button();
		horizontalPanel_1.add(addressAddButton);
		addressAddButton.setText("Add Address");
		addressAddButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				((Button) w).setEnabled(false);
			}
		});
		addressModifyButton = new Button();
		horizontalPanel_1.add(addressModifyButton);
		addressModifyButton.setText("Modify Address");
		addressModifyButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				((Button) w).setEnabled(false);
			}
		});

		addressContainer = new CustomSortableTable();
		addressContainer.addColumn("Address Type", "type");
		addressContainer.addColumn("Relationship to Patient", "relate");
		addressContainer.addColumn("Address Line 1", "line1");
		addressContainer.addColumn("Address Line 2", "line2");
		addressContainer.addColumn("City / State / Postal", "csv");
		addressContainer.addColumn("Active", "active");
		addressTable.setWidget(7, 0, addressContainer);
		addressTable.getFlexCellFormatter().setColSpan(7, 0, 2);

		final FlexTable contactTable = new FlexTable();
		tabPanel.add(contactTable, "Contact");

		final Label preferredContactLabel = new Label("Preferred Contact");
		contactTable.setWidget(0, 0, preferredContactLabel);

		preferredContact = new CustomListBox();
		preferredContact.addItem("Home", "home");
		preferredContact.addItem("Work", "work");
		preferredContact.addItem("Mobile", "mobile");
		preferredContact.addItem("Email", "email");
		preferredContact.setVisibleItemCount(1);
		contactTable.setWidget(0, 1, preferredContact);

		final Label homePhoneLabel = new Label("Home Phone");
		contactTable.setWidget(1, 0, homePhoneLabel);

		final Label workPhoneLabel = new Label("Work Phone");
		contactTable.setWidget(2, 0, workPhoneLabel);

		final Label faxPhoneLabel = new Label("Fax Phone");
		contactTable.setWidget(3, 0, faxPhoneLabel);

		final Label mobilePhoneLabel = new Label("Mobile Phone");
		contactTable.setWidget(4, 0, mobilePhoneLabel);

		final Label emailAddressLabel = new Label("Email Address");
		contactTable.setWidget(5, 0, emailAddressLabel);

		phoneHome = new TextBox();
		contactTable.setWidget(1, 1, phoneHome);
		phoneHome.setWidth("100%");

		phoneWork = new TextBox();
		contactTable.setWidget(2, 1, phoneWork);
		phoneWork.setWidth("100%");

		phoneFax = new TextBox();
		contactTable.setWidget(3, 1, phoneFax);
		phoneFax.setWidth("100%");

		phoneMobile = new TextBox();
		contactTable.setWidget(4, 1, phoneMobile);
		phoneMobile.setWidth("100%");

		emailAddress = new TextBox();
		contactTable.setWidget(5, 1, emailAddress);
		emailAddress.setWidth("100%");

		// Select first tab "demographics" as active tag
		tabPanel.selectTab(0);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		horizontalPanel.setWidth("100%");

		submitButton = new Button();
		horizontalPanel.add(submitButton);
		submitButton.setText("Commit");
		submitButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				submitButton.setEnabled(false);
				if (validateForm()) {
					if (Util.isStubbedMode()) {
						submitButton.setEnabled(true);
					} else {
						if (patientId.equals(new Integer(0))) {
							// Add
							getModuleProxy().ModuleAddMethod(moduleName,
									populateHashMap(),
									new AsyncCallback<Integer>() {
										public void onSuccess(Integer o) {
											// TODO: handle success
											closeScreen();
										}

										public void onFailure(Throwable t) {
											GWT.log("Exception", t);
											submitButton.setEnabled(true);
										}
									});
						} else {
							// Modify
							getModuleProxy().ModuleModifyMethod(moduleName,
									populateHashMap(),
									new AsyncCallback<Integer>() {
										public void onSuccess(Integer o) {
											// TODO: handle success
											closeScreen();
										}

										public void onFailure(Throwable t) {
											GWT.log("Exception", t);
											submitButton.setEnabled(true);
										}
									});
						}
					}
				} else {
					// Form validation failed, allow user to continue
					submitButton.setEnabled(true);
				}
			}
		});
	}

	public void setPatientId(Integer newPatientId) {
		patientId = newPatientId;
		if (!Util.isStubbedMode()) {
			populateForm();
		}
	}

	protected void populateForm() {
		getModuleProxy().ModuleGetRecordMethod("PatientModule", patientId,
				new AsyncCallback<HashMap<String, String>>() {
					public void onSuccess(HashMap<String, String> m) {
						// Demographics screen
						wTitle.setWidgetValue((String) m
								.get((String) "ptsalut"));
						wLastName.setText((String) m.get((String) "ptlname"));
						wFirstName.setText((String) m.get((String) "ptfname"));
						wMiddleName.setText((String) m.get((String) "ptmname"));
						wSuffix.setWidgetValue((String) m
								.get((String) "ptsuffix"));
						wGender
								.setWidgetValue((String) m
										.get((String) "ptsex"));
						wDob.setSelectedDate(new Date((String) m
								.get((String) "ptdob")));

						// Contact screen
						preferredContact.setWidgetValue((String) m
								.get((String) "ptprefcontact"));
						phoneHome.setText((String) m.get((String) "pthphone"));
						phoneWork.setText((String) m.get((String) "ptwphone"));
						phoneMobile
								.setText((String) m.get((String) "ptmphone"));
						phoneFax.setText((String) m.get((String) "ptfax"));

					}

					public void onFailure(Throwable t) {
						GWT.log("Exception", t);
					}
				});
	}

	/**
	 * Populate hash from form to be fed into the RPC routines.
	 */
	protected HashMap<String, String> populateHashMap() {
		HashMap<String, String> m = new HashMap<String, String>();
		if (patientId.intValue() > 0) {
			m.put((String) "id", (String) patientId.toString());
		}

		// Demographic screen
		m.put((String) "ptdob", (String) wDob.getSelectedDate().toString());
		m.put((String) "ptsalut", (String) wTitle.getWidgetValue());
		m.put((String) "ptlname", (String) wLastName.getText());
		m.put((String) "ptfname", (String) wFirstName.getText());
		m.put((String) "ptmname", (String) wMiddleName.getText());
		m.put((String) "ptsuffix", (String) wSuffix.getWidgetValue());
		m.put((String) "ptsex", (String) wGender.getWidgetValue());

		// Contact screen
		m.put((String) "ptprefcontact", (String) preferredContact
				.getWidgetValue());
		m.put((String) "pthphone", (String) phoneHome.getText());
		m.put((String) "ptwphone", (String) phoneWork.getText());
		m.put((String) "ptmphone", (String) phoneMobile.getText());
		m.put((String) "ptfax", (String) phoneFax.getText());

		preferredContact.setWidgetValue((String) m
				.get((String) "ptprefcontact"));
		phoneHome.setText((String) m.get((String) "pthphone"));
		phoneWork.setText((String) m.get((String) "ptwphone"));
		phoneMobile.setText((String) m.get((String) "ptmphone"));
		phoneFax.setText((String) m.get((String) "ptfax"));

		return m;
	}

	protected ModuleInterfaceAsync getModuleProxy() {
		ModuleInterfaceAsync p = null;
		try {
			p = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}

	protected boolean validateForm() {
		String msg = new String("");
		if (wLastName.getText().length() < 2) {
			msg += "Please specify a last name." + "\n";
		}
		if (wFirstName.getText().length() < 2) {
			msg += "Please specify a first name." + "\n";
		}
		if (msg.length() == 0) {
			return false;
		}
		return true;
	}

}
