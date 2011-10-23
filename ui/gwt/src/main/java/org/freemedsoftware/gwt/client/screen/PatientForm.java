/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2011 FreeMED Software Foundation
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonGroup;
import org.freemedsoftware.gwt.client.widget.PatientAddresses;
import org.freemedsoftware.gwt.client.widget.PatientAddresses.Address;
import org.freemedsoftware.gwt.client.widget.PatientAuthorizations;
import org.freemedsoftware.gwt.client.widget.PatientCoverages;
import org.freemedsoftware.gwt.client.widget.ProviderWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.dom.client.KeyUpEvent;
import com.google.gwt.event.dom.client.KeyUpHandler;
import com.google.gwt.event.logical.shared.SelectionEvent;
import com.google.gwt.event.logical.shared.SelectionHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientForm extends ScreenInterface {
	
	protected CustomDatePicker wDob;

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

//	protected CustomListBox wTitle;

    protected CustomRadioButtonGroup title;

	protected TextBox wLastName;

	protected TextBox wFirstName;

	protected TextBox wMiddleName;

//	protected CustomListBox wSuffix;
	
	protected CustomRadioButtonGroup suffix;;

//	protected CustomListBox wGender;
	
	protected CustomRadioButtonGroup gender;

	protected CustomButton submitButton, addressAddButton, addressModifyButton;

	protected Integer patientId = new Integer(0);

	protected PatientAddresses addressContainer;
	
	protected PatientCoverages patientCoverages;
	
	protected String CoveragesModuleName = "PatientCoverages";
	
	protected PatientAuthorizations patientAuthorizations;
	
	protected String AuthorizationsModuleName = "Authorizations";

	protected CustomRadioButtonGroup martialStatus;
	
	protected CustomListBox employmentStatus;
	
	protected TextBox socialSecurityNumber;
	
	protected CustomListBox race;
	
	protected CustomListBox religion;
	
	protected CustomListBox languages;
	
	protected TextBox driverLicence; 

	protected CustomListBox typeofBilling;
	
	protected TextBox monthlyBudgetAmount;
	
	protected TextBox patientPracticeID;
	
//	protected CustomListBox bloodType;
	
//	protected TextBox preferredPharmacy;
	
	protected SupportModuleWidget preferredPharmacy; 
	
	protected ProviderWidget inHouseDoctor;
	
	protected ProviderWidget referringDoctor;
	
	protected ProviderWidget primaryCarePhysician;
	
	protected CustomListBox numberofOtherPhysicians;
	
	protected ProviderWidget otherPhysician1;
	protected ProviderWidget otherPhysician2;
	protected ProviderWidget otherPhysician3;
	protected ProviderWidget otherPhysician4;
	
	protected Label otherPhysician1Label;
	protected Label otherPhysician2Label;
	protected Label otherPhysician3Label;
	protected Label otherPhysician4Label;
	
	public final static String moduleName = "PatientModule";

	private static List<PatientForm> patientFormList=null;
	protected static HashMap<Integer,String> religions=null;

	protected SupportModuleWidget primaryFacility;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static PatientForm getInstance(){
		PatientForm patientForm=null; 
		
		if(patientFormList==null)
			patientFormList=new ArrayList<PatientForm>();
		if(patientFormList.size()<AppConstants.MAX_NEWPATIENT_TABS)//creates & returns new next instance of PatientForm
			patientFormList.add(patientForm=new PatientForm());
		else //returns last instance of PatientForm from list 
			patientForm = patientFormList.get(AppConstants.MAX_NEWPATIENT_TABS-1);
		return patientForm;
	}
	
	public static boolean removeInstance(PatientForm patientForm){
		return patientFormList!=null?patientFormList.remove(patientForm):false;
	}
	
	public PatientForm() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setWidth("100%");
		initWidget(verticalPanel);

		final CheckBox tabView = new CheckBox(_("Tab View"));
		tabView.setValue(true);
		
		verticalPanel.add(tabView);
		
		final TabPanel tabPanel = new TabPanel();
		tabPanel.addSelectionHandler(new SelectionHandler<Integer>() {		
			@Override
			public void onSelection(SelectionEvent<Integer> event) {
				// TODO Auto-generated method stub
				 if (event.getSelectedItem() == 0)
					 title.setFocus(true);	
				 if (event.getSelectedItem() == 2)
					 preferredContact.setFocus(true);
				 if (event.getSelectedItem() == 3)
					 martialStatus.setFocus(true);
				 if (event.getSelectedItem() == 4)
					 primaryFacility.setFocus(true);
			}		
		});
		verticalPanel.add(tabPanel);

		final VerticalPanel nonTabViewContainer = new VerticalPanel();
		nonTabViewContainer.setWidth("100%");
		nonTabViewContainer.setVisible(false);
		verticalPanel.add(nonTabViewContainer);
		
		final FlexTable demographicsTable = new FlexTable();
		demographicsTable.setWidth("100%");
		tabPanel.add(demographicsTable, _("Demographics"));

		final Label titleLabel = new Label(_("Title"));
		demographicsTable.setWidget(0, 0, titleLabel);
		
		/*
		wTitle = new CustomListBox();
		demographicsTable.setWidget(0, 1, wTitle);
		wTitle.addItem("[Choose title]", "");
		wTitle.addItem("Mr");
		wTitle.addItem("Mrs");
		wTitle.addItem("Ms");
		wTitle.addItem("Dr");
		wTitle.addItem("Fr");
		wTitle.setVisibleItemCount(1);
		*/
		title = new CustomRadioButtonGroup("title");
		title.setWidth("30%");
		demographicsTable.setWidget(0, 1, title);
		title.addItem("Mr");
		title.addItem("Mrs");
		title.addItem("Ms");
		title.addItem("Dr");
		title.addItem("Fr");

		final Label lastNameLabel = new Label(_("Last Name"));
		demographicsTable.setWidget(1, 0, lastNameLabel);

		wLastName = new TextBox();
		demographicsTable.setWidget(1, 1, wLastName);
		wLastName.setTabIndex(1);
		wLastName.setFocus(true);
		wLastName.setWidth("100%");
		wLastName.addKeyUpHandler(new KeyUpHandler() {
			@Override
			public void onKeyUp(KeyUpEvent arg0) {
				generatePracticeId();
			}
		
		});

		final Label firstNameLabel = new Label(_("First Name"));
		demographicsTable.setWidget(2, 0, firstNameLabel);

		wFirstName = new TextBox();
		demographicsTable.setWidget(2, 1, wFirstName);
		wFirstName.setTabIndex(2);
		wFirstName.setWidth("100%");
		wFirstName.addKeyUpHandler(new KeyUpHandler() {
			@Override
			public void onKeyUp(KeyUpEvent arg0) {
				generatePracticeId();
			}
		
		});
		
		final Label middleNameLabel = new Label(_("Middle Name"));
		demographicsTable.setWidget(3, 0, middleNameLabel);

		wMiddleName = new TextBox();
		demographicsTable.setWidget(3, 1, wMiddleName);
		wMiddleName.setTabIndex(3);
		wMiddleName.setWidth("100%");
		wMiddleName.addKeyUpHandler(new KeyUpHandler() {
			@Override
			public void onKeyUp(KeyUpEvent arg0) {
				generatePracticeId();
			}
		
		});
		
		final Label suffixLabel = new Label(_("Suffix"));
		demographicsTable.setWidget(4, 0, suffixLabel);

		/*
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
		*/

		suffix = new CustomRadioButtonGroup("suffix");
		suffix.setWidth("30%");
		demographicsTable.setWidget(4, 1, suffix);
		suffix.addItem("Sr");
		suffix.addItem("Jr");
		suffix.addItem("II");
		suffix.addItem("III");
		suffix.addItem("IV");

		final Label genderLabel = new Label(_("Gender"));
		demographicsTable.setWidget(5, 0, genderLabel);

		/*
		wGender = new CustomListBox();
		demographicsTable.setWidget(5, 1, wGender);
		wGender.setTabIndex(5);
		wGender.addItem("[Choose Value]", "");
		wGender.addItem("Male", "m");
		wGender.addItem("Female", "f");
		wGender.addItem("Transgendered", "t");
		wGender.setVisibleItemCount(1);
		*/
		
		gender = new CustomRadioButtonGroup("gender");
		gender.setWidth("30%");
		demographicsTable.setWidget(5, 1, gender);
		gender.addItem(_("Male"), "m");
		gender.addItem(_("Female"), "f");
		gender.addItem(_("Transgendered"), "t");
		
		final Label dateOfBirthLabel = new Label(_("Date of Birth"));
		demographicsTable.setWidget(6, 0, dateOfBirthLabel);

		wDob = new CustomDatePicker();
		demographicsTable.setWidget(6, 1, wDob);
		// wDob.setTabIndex(6);

		addressContainer = new PatientAddresses();
		addressContainer.setWidth("100%");
		tabPanel.add(addressContainer, _("Address"));

		final FlexTable contactTable = new FlexTable();
		contactTable.setWidth("100%");
		tabPanel.add(contactTable, _("Contact"));

		final Label preferredContactLabel = new Label(_("Preferred Contact"));
		contactTable.setWidget(0, 0, preferredContactLabel);

		preferredContact = new CustomListBox();
		preferredContact.addItem(_("Home"), "home");
		preferredContact.addItem(_("Work"), "work");
		preferredContact.addItem(_("Mobile"), "mobile");
		preferredContact.addItem(_("Email"), "email");
		preferredContact.setVisibleItemCount(1);
		contactTable.setWidget(0, 1, preferredContact);

		final Label homePhoneLabel = new Label(_("Home Phone"));
		contactTable.setWidget(1, 0, homePhoneLabel);

		final Label workPhoneLabel = new Label(_("Work Phone"));
		contactTable.setWidget(2, 0, workPhoneLabel);

		final Label faxPhoneLabel = new Label(_("Fax Phone"));
		contactTable.setWidget(3, 0, faxPhoneLabel);

		final Label mobilePhoneLabel = new Label(_("Mobile Phone"));
		contactTable.setWidget(4, 0, mobilePhoneLabel);

		final Label emailAddressLabel = new Label(_("Email Address"));
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

		
		//creating personal tab
		final FlexTable personalTable = new FlexTable();
		personalTable.setWidth("100%");
		tabPanel.add(personalTable, _("Personal"));

		final Label martialStatusLabel = new Label(_("Marital Status"));
		personalTable.setWidget(0, 0, martialStatusLabel);

		martialStatus = new CustomRadioButtonGroup("martialStatus");
		martialStatus.addItem(_("Single"), "single");
		martialStatus.addItem(_("Married"), "married");
		martialStatus.addItem(_("Divorced"), "divorced");
		martialStatus.addItem(_("Separated"), "separated");
		martialStatus.addItem(_("Widowed"), "widowed");
//		martialStatus.setVisibleItemCount(1);
		personalTable.setWidget(0, 1, martialStatus);

		final Label employmentStatusLabel = new Label(_("Employment Status"));
		personalTable.setWidget(1, 0, employmentStatusLabel);

		employmentStatus = new CustomListBox();
		employmentStatus.addItem(_("Yes"), "y");
		employmentStatus.addItem(_("No"), "n");
		employmentStatus.addItem(_("Part Time"), "p");
		employmentStatus.addItem(_("Self"), "s");
		employmentStatus.addItem(_("Retired"), "r");
		employmentStatus.addItem(_("Military"), "m");
		employmentStatus.addItem(_("Unknown"), "u");
		employmentStatus.setVisibleItemCount(1);
		personalTable.setWidget(1, 1, employmentStatus);

		final Label patientStatusLabel = new Label(_("Patient Status"));
		personalTable.setWidget(2, 0, patientStatusLabel);
		
		final Label patientStatusValueLabel = new Label(_("None"));
		personalTable.setWidget(2, 1, patientStatusValueLabel);

		final Label socialSecurityNumberLabel = new Label(_("Social Security Number"));
		personalTable.setWidget(3, 0, socialSecurityNumberLabel);

		socialSecurityNumber = new TextBox();
		personalTable.setWidget(3, 1, socialSecurityNumber);
		socialSecurityNumber.setWidth("100%");
		socialSecurityNumber.addKeyUpHandler(new KeyUpHandler() {
			@Override
			public void onKeyUp(KeyUpEvent arg0) {
				generatePracticeId();
			}
		
		});

		
		final Label raceLabel = new Label(_("Race"));
		personalTable.setWidget(4, 0, raceLabel);

		race = new CustomListBox();
		race.addItem(_("Unknown race"), "7");
		race.addItem(_("Hispanic, white"), "1");
		race.addItem(_("Hispanic, black"), "2");
		race.addItem(_("American Indian or Alaska Native"), "3");
		race.addItem(_("Black, not of Hispanic origin"), "4");
		race.addItem(_("Asian or Pacific Islander"), "5");
		race.addItem(_("White, not of Hispanic origin"), "6");
		race.setVisibleItemCount(1);
		personalTable.setWidget(4, 1, race);
		
		
		final Label religionLabel = new Label(_("Religion"));
		personalTable.setWidget(5, 0, religionLabel);

		religion = new CustomListBox();
		religion.addItem(_("Unknown/No preference"),"29");
		religion.addItem(_("Catholic"),"0");
		religion.addItem(_("Jewish"),"1");
		religion.addItem(_("Eastern Orthodox"),"2");
		religion.addItem(_("Baptist"),"3");
		religion.addItem(_("Methodist"),"4");
		religion.addItem(_("Lutheran"),"5");
		religion.addItem(_("Presbyterian"),"6");
		religion.addItem(_("United Church of God"),"7");
		religion.addItem(_("Episcopalian"),"8");
		religion.addItem(_("Adventist"),"9");
		religion.addItem(_("Assembly of God"),"10");
		religion.addItem(_("Brethren"),"11");
		religion.addItem(_("Christian Scientist"),"12");
		religion.addItem(_("Church of Christ"),"13");
		religion.addItem(_("Church of God"),"14");
		religion.addItem(_("Disciples of Christ"),"15");
		religion.addItem(_("Evangelical Covenant"),"16");
		religion.addItem(_("Friends"),"17");
		religion.addItem(_("Jehovah's Witness"),"18");
		religion.addItem(_("Latter-Day Saints"),"19");
		religion.addItem(_("Islam"),"20");
		religion.addItem(_("Nazarene"),"21");
		religion.addItem(_("Other"),"22");
		religion.addItem(_("Pentecostal"),"23");
		religion.addItem(_("Protestant, Other"),"24");
		religion.addItem(_("Protestant, No Denomination"),"25");
		religion.addItem(_("Reformed"),"26");
		religion.addItem(_("Salvation Army"),"27");
		religion.addItem(_("Unitarian; Universalist"),"28");
		religion.addItem(_("Native American"),"30");
		religion.addItem(_("Buddhist"),"31");
		religion.setVisibleItemCount(1);
		personalTable.setWidget(5, 1, religion);
		
		final Label languageLabel = new Label(_("Language"));
		personalTable.setWidget(6, 0, languageLabel);
		languages = new CustomListBox();
		personalTable.setWidget(6, 1, languages);
		loadLanguages();
		
		final Label driverLicenceLabel = new Label(_("Driver's License (No State)"));
		personalTable.setWidget(7, 0, driverLicenceLabel);

		driverLicence = new TextBox();
		personalTable.setWidget(7, 1, driverLicence);
		driverLicence.setWidth("100%");
		
		
		final Label typeofBillingLabel = new Label(_("Type of Billing"));
		personalTable.setWidget(8, 0, typeofBillingLabel);

		typeofBilling = new CustomListBox();
		typeofBilling.addItem(_("NONE SELECTED"),"");
		typeofBilling.addItem(_("Monthly Billing On Account"),"mon");
		typeofBilling.addItem(_("Statement Billing"),"sta");
		typeofBilling.addItem(_("Charge Card Billing"),"chg");
		typeofBilling.setVisibleItemCount(1);
		personalTable.setWidget(8, 1, typeofBilling);
		
		
		final Label monthlyBudgetAmountLabel = new Label(_("Monthly Budget Amount"));
		personalTable.setWidget(9, 0, monthlyBudgetAmountLabel);

		monthlyBudgetAmount = new TextBox();
		personalTable.setWidget(9, 1, monthlyBudgetAmount);
		monthlyBudgetAmount.setWidth("100%");
		
		final Label patientPracticeIdLabel = new Label(_("Patient Practice ID"));
		personalTable.setWidget(10, 0, patientPracticeIdLabel);

		patientPracticeID = new TextBox();
		personalTable.setWidget(10, 1, patientPracticeID);
		patientPracticeID.setTabIndex(7);
		patientPracticeID.setWidth("100%");
		
		//creating Medical tab
		final FlexTable medicalTable = new FlexTable();
		medicalTable.setWidth("100%");
		tabPanel.add(medicalTable, _("Medical"));

//		final Label bloodTypeLabel = new Label("Blood Type");
//		medicalTable.setWidget(0, 0, bloodTypeLabel);
//
//		bloodType = new CustomListBox();
//		bloodType.addItem("-","");
//		bloodType.addItem("O","O");
//		bloodType.addItem("O+","O+");
//		bloodType.addItem("O-","O-");
//		bloodType.addItem("A","A");
//		bloodType.addItem("A+","A+");
//		bloodType.addItem("A-","A-");
//		bloodType.addItem("B","B");
//		bloodType.addItem("B+","B+");
//		bloodType.addItem("B-","B-");
//		bloodType.addItem("AB","AB");
//		bloodType.addItem("AB+","AB+");
//		bloodType.addItem("AB-","AB-");
//		bloodType.setVisibleItemCount(1);
//		medicalTable.setWidget(0, 1, bloodType);
		
		final Label primaryFacilityLabel = new Label(_("Primary Facility"));
		medicalTable.setWidget(0, 0, primaryFacilityLabel);
		
		primaryFacility = new SupportModuleWidget("FacilityModule");
		medicalTable.setWidget(0, 1, primaryFacility);
		primaryFacility.setWidth("100%");
		
		
		final Label preferredPharmacyLabel = new Label(_("Preferred Pharmacy"));
		medicalTable.setWidget(1, 0, preferredPharmacyLabel);
		
		preferredPharmacy = new SupportModuleWidget("Pharmacy");
		medicalTable.setWidget(1, 1, preferredPharmacy);
		preferredPharmacy.setWidth("100%");
		
		final Label inHouseDoctorLabel = new Label(_("In House Doctor"));
		medicalTable.setWidget(2, 0, inHouseDoctorLabel);
		
		inHouseDoctor = new ProviderWidget();

		medicalTable.setWidget(2, 1, inHouseDoctor);
		inHouseDoctor.setWidth("100%");

		final Label referringDoctorLabel = new Label(_("Referring Doctor"));
		medicalTable.setWidget(3, 0, referringDoctorLabel);
		
		referringDoctor = new ProviderWidget();
		medicalTable.setWidget(3, 1, referringDoctor);
		referringDoctor.setWidth("100%");

		final Label primaryCarePhysicianLabel = new Label(_("Primary Care Physician"));
		medicalTable.setWidget(4, 0, primaryCarePhysicianLabel);
		
		primaryCarePhysician = new ProviderWidget();
		medicalTable.setWidget(4, 1, primaryCarePhysician);
		primaryCarePhysician.setWidth("100%");


		final Label numberofOtherPhysiciansLabel = new Label(_("Number of Other Physicians"));
		medicalTable.setWidget(5, 0, numberofOtherPhysiciansLabel);

		numberofOtherPhysicians = new CustomListBox();
		numberofOtherPhysicians.addItem("0","0");
		numberofOtherPhysicians.addItem("1","1");
		numberofOtherPhysicians.addItem("2","2");
		numberofOtherPhysicians.addItem("3","3");
		numberofOtherPhysicians.addItem("4","4");
		numberofOtherPhysicians.setVisibleItemCount(1);
		medicalTable.setWidget(5, 1, numberofOtherPhysicians);

		numberofOtherPhysicians.addChangeHandler(new ChangeHandler() {
		
			@Override
			public void onChange(ChangeEvent arg0) {
				// TODO Auto-generated method stub
				int selectedCount=Integer.parseInt(numberofOtherPhysicians.getValue(numberofOtherPhysicians.getSelectedIndex()));
				updateOtherPhysiciansBoxes(selectedCount);	
			}
		
		});

		otherPhysician1Label = new Label(_("Physician 1"));
		medicalTable.setWidget(6, 0, otherPhysician1Label);
		otherPhysician1=new ProviderWidget();
		medicalTable.setWidget(6, 1, otherPhysician1);

		otherPhysician2Label = new Label(_("Physician 2"));
		medicalTable.setWidget(7, 0, otherPhysician2Label);
		otherPhysician2=new ProviderWidget();
		medicalTable.setWidget(7, 1, otherPhysician2);
		
		otherPhysician3Label = new Label(_("Physician 3"));
		medicalTable.setWidget(8, 0, otherPhysician3Label);
		otherPhysician3=new ProviderWidget();
		medicalTable.setWidget(8, 1, otherPhysician3);
		
		otherPhysician4Label = new Label(_("Physician 4"));
		medicalTable.setWidget(9, 0, otherPhysician4Label);
		otherPhysician4=new ProviderWidget();
		medicalTable.setWidget(9, 1, otherPhysician4);		
		
		updateOtherPhysiciansBoxes(0);
		
		// Select first tab "demographics" as active tag
		tabPanel.selectTab(0);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		verticalPanel.setCellHorizontalAlignment(horizontalPanel,HasHorizontalAlignment.ALIGN_CENTER);
//		horizontalPanel.setWidth("100%");

		
		submitButton = new CustomButton(_("Commit"),AppConstants.ICON_ADD);
		horizontalPanel.add(submitButton);
		submitButton.addClickHandler(new ClickHandler() {
			@SuppressWarnings({ "rawtypes", "unchecked" })
			@Override
			public void onClick(ClickEvent evt) {
//				submitButton.setEnabled(false);
				if(validateForm()){
					if(patientId==null || patientId==0){
						List params = new ArrayList();
						HashMap<String, String> criteria = new HashMap<String, String>();
						criteria.put("ptlname", (String) wLastName.getText());
						criteria.put("ptfname", (String) wFirstName.getText());
						criteria.put("ptdob", (String) wDob.getStoredValue());
						params.add(criteria);
	
						Util.callApiMethod("PatientInterface", "GetDuplicatePatients", params, new CustomRequestCallback() {
						
							@Override
							public void onError() {
	//							submitButton.setEnabled(true);
							}
						
							@Override
							public void jsonifiedData(Object data) {
								HashMap<String, String> result = (HashMap<String, String>)data;
								if(result==null || result.size()==0)
									commitChanges();
								else{
									String msg = _("This patient is already in the system. Do you want to continue adding?");
									Util.confirm(msg,new Command() {
									
										@Override
										public void execute() {
											commitChanges();
										}
									},null);
								}
							}
						}, "HashMap<String,String>");
						
					}else
					commitChanges();
				}
			}
		});
	
		CustomButton cancelButton = new CustomButton(_("Cancel"), AppConstants.ICON_CANCEL);
		cancelButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				try{
					closeScreen();
				}catch(Exception e){}

				if(patientId!=0)
					spawnPatientScreen(patientId);
			}
		});
		horizontalPanel.add(cancelButton);
		
		if(CurrentState.isActionAllowed(CoveragesModuleName,AppConstants.SHOW)){
			patientCoverages = new PatientCoverages();
			patientCoverages.setWidth("100%");
			tabPanel.add(patientCoverages, _("Coverages"));
		}
		if(CurrentState.isActionAllowed(AuthorizationsModuleName,AppConstants.SHOW)){
			patientAuthorizations = new PatientAuthorizations();
			patientAuthorizations.setWidth("100%");
			tabPanel.add(patientAuthorizations, _("Authorizations"));
		}
		
		tabView.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
		
			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				if(arg0.getValue()){
					tabPanel.clear();
					//Adding Demographics
					tabPanel.add(demographicsTable, _("Demographics"));
					//Adding Address
					tabPanel.add(addressContainer, _("Address"));
					//Adding Contact
					tabPanel.add(contactTable, _("Contact"));
					//Adding Personal
					tabPanel.add(personalTable, _("Personal"));
					//Adding Medical
					tabPanel.add(medicalTable, _("Medical"));
					//Adding Coverages
					tabPanel.add(patientCoverages, _("Coverages"));
					//Adding Authorizations
					tabPanel.add(patientAuthorizations, _("Authorizations"));
					
					nonTabViewContainer.setVisible(false);
					tabPanel.setVisible(true);
					tabPanel.selectTab(0);
				}else{
					nonTabViewContainer.clear();
					//Adding Demographics
					Label label = new Label(_("Demographics"));
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(demographicsTable);
					demographicsTable.setWidth("100%");
					
					//Adding Address
					label = new Label(_("Address"));
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(addressContainer);
					addressContainer.setWidth("100%");
					
					//Adding Contact
					label = new Label(_("Contact"));
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(contactTable);
					contactTable.setWidth("100%");

					//Adding Personal
					label = new Label(_("Personal"));
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(personalTable);
					personalTable.setWidth("100%");
					
					//Adding Medical
					label = new Label(_("Medical"));
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(medicalTable);
					medicalTable.setWidth("100%");
					
					//Adding Coverages
					label = new Label(_("Coverages"));
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(patientCoverages);
					patientCoverages.setWidth("100%");
					
					//Adding Authorizations
					label = new Label("Authorizations");
					label.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
					nonTabViewContainer.add(label);
					nonTabViewContainer.add(patientAuthorizations);
					patientAuthorizations.setWidth("100%");
					
					tabPanel.setVisible(false);
					nonTabViewContainer.setVisible(true);
				}
		
			}
		
		});
		
		Util.setFocus(title);	
	}
	
	public void commitChanges(){
		if (validateForm()) {

			if (Util.getProgramMode() == ProgramMode.STUBBED) {

				submitButton.setEnabled(true);
				Util.showInfoMsg("PatientForm", _("Updated patient information."));
				addressContainer.setOnCompletion(new Command() {
					public void execute() {
						closeScreen();
					}
				});
				addressContainer.commitChanges();
			} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
				if (patientId == 0) {
					// Add
					getModuleProxy().ModuleAddMethod(moduleName,
							populateHashMap(),
							new AsyncCallback<Integer>() {
								public void onSuccess(Integer o) {
									CurrentState
											.getToaster()
											.addItem(
													"Patient",
													_("Updated patient information."),
													Toaster.TOASTER_INFO);
									addressContainer.setPatient(o);
									addressContainer
											.setOnCompletion(new Command() {
												public void execute() {
													closeScreen();
												}
											});
									addressContainer.commitChanges();
									
									if(CurrentState.isActionAllowed(CoveragesModuleName,AppConstants.MODIFY)){
										patientCoverages.setPatient(o);
										patientCoverages
												.setOnCompletion(new Command() {
													public void execute() {
														closeScreen();
													}
												});
										patientCoverages.commitChanges();
									}
									
									
									patientAuthorizations.setPatient(o);
									patientAuthorizations
											.setOnCompletion(new Command() {
												public void execute() {
													closeScreen();
												}
											});
									patientAuthorizations.commitChanges();
								}

								public void onFailure(Throwable t) {
									JsonUtil.debug("Exception");
									submitButton.setEnabled(true);
								}
							});
				} else {
					// Modify
					getModuleProxy().ModuleModifyMethod(moduleName,
							populateHashMap(),
							new AsyncCallback<Integer>() {
								public void onSuccess(Integer o) {
									CurrentState
											.getToaster()
											.addItem(
													"Patient",
													_("Updated patient information."),
													Toaster.TOASTER_INFO);
									addressContainer
											.setOnCompletion(new Command() {
												public void execute() {
													closeScreen();
												}
											});
									addressContainer.commitChanges();
									
									patientCoverages
									.setOnCompletion(new Command() {
										public void execute() {
											closeScreen();
										}
									});
									patientCoverages.commitChanges();
									
									patientAuthorizations
									.setOnCompletion(new Command() {
										public void execute() {
											closeScreen();
										}
									});
									patientAuthorizations.commitChanges();
								}

								public void onFailure(Throwable t) {
									JsonUtil.debug("Exception");
									submitButton.setEnabled(true);
								}
							});
				}
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {

				if (patientId == 0) {
					// Add
					String[] params = { JsonUtil
							.jsonify(populateHashMap()) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.PatientModule.add",
													params)));
					try {
						builder.sendRequest(null,
								new RequestCallback() {
									public void onError(
											Request request,
											Throwable ex) {
									}

									public void onResponseReceived(
											Request request,
											Response response) {
										if (200 == response
												.getStatusCode()) {
											Integer r = (Integer) JsonUtil
													.shoehornJson(
															JSONParser
																	.parseStrict(response
																			.getText()),
															"Integer");
											if (r != 0) {
												addressContainer.setPatient(r);
												addressContainer.commitChanges();
												
												if(CurrentState.isActionAllowed(CoveragesModuleName,AppConstants.WRITE)){
													patientCoverages.setPatient(r);
													patientCoverages.commitChanges();
												}
												if(CurrentState.isActionAllowed(AuthorizationsModuleName,AppConstants.WRITE)){
													patientAuthorizations.setPatient(r);
													patientAuthorizations.commitChanges();
												}
												
												spawnPatientScreen(r);
												CurrentState
														.getToaster()
														.addItem(
																"PatientForm",
																_("Patient successfully added."));
											}
										} else {
											CurrentState
													.getToaster()
													.addItem(
															"PatientForm",
															_("Adding patient failed."));
										}
									}
								});
					} catch (RequestException e) {
					}

				} else {

					// Modify
					String[] params = { JsonUtil
							.jsonify(populateHashMap()) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.PatientModule.mod",
													params)));
					try {
						builder.sendRequest(null,
								new RequestCallback() {
									public void onError(
											Request request,
											Throwable ex) {
									}

									public void onResponseReceived(
											Request request,
											Response response) {
										if (200 == response
												.getStatusCode()) {
											Boolean r = (Boolean) JsonUtil
													.shoehornJson(
															JSONParser
																	.parseStrict(response
																			.getText()),
															"boolean");
											if (r) {
//												addressContainer.setPatient(patientId);
												
												//addressContainer.deleteAddress();
												addressContainer.commitChanges();
												
												if(CurrentState.isActionAllowed(CoveragesModuleName,AppConstants.MODIFY))
													patientCoverages.commitChanges();
												if(CurrentState.isActionAllowed(AuthorizationsModuleName,AppConstants.MODIFY))
													patientAuthorizations.commitChanges();
												
												spawnPatientScreen(patientId);
												CurrentState
														.getToaster()
														.addItem(
																"PatientForm",
																_("Patient information successfully modified."));
											}
										} else {
											CurrentState
													.getToaster()
													.addItem(
															"PatientForm",
															_("Adding patient failed."));
										}
									}
								});
					} catch (RequestException e) {
					}

				}

			}

			closeScreen();
		} else {
			// Form validation failed, allow user to continue
			submitButton.setEnabled(true);
			Util.showErrorMsg("PatientForm", "Form validation failed.");
		}
	}
	
	/**
	 * Create new tab for patient.
	 * 
	 * @param patient
	 */
	public void spawnPatientScreen(Integer patient) {
		PatientScreen s = new PatientScreen();
		s.setPatient(patient);
		String patientName = wLastName.getText()+", "+wFirstName.getText()+" "+wMiddleName.getText();
		Util.spawnTab(patientName, s);
	}
	public void setPrimaryFacility(Integer facilityId){
		primaryFacility.setValue(facilityId);
	}
	public void setPatientId(Integer newPatientId) {
		patientId = newPatientId;
		if (newPatientId > 0) {
			if (!Util.isStubbedMode()) {
				populateForm();
			}
		}
	}
/*
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
						wDob.setValue((String) m.get((String) "ptdob"));

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
						JsonUtil.debug("Exception");
					}
				});
		// Populate address container
		addressContainer.setPatient(patientId);
	}
*/
	
	public void updateOtherPhysiciansBoxes(int selectedCount){
		otherPhysician1Label.setVisible(false);
		otherPhysician1.setVisible(false);
		otherPhysician2Label.setVisible(false);
		otherPhysician2.setVisible(false);
		otherPhysician3Label.setVisible(false);
		otherPhysician3.setVisible(false);
		otherPhysician4Label.setVisible(false);
		otherPhysician4.setVisible(false);

		if(selectedCount>0){
			otherPhysician1Label.setVisible(true);
			otherPhysician1.setVisible(true);
		}
		if(selectedCount>1){
			otherPhysician2Label.setVisible(true);
			otherPhysician2.setVisible(true);
		}
		if(selectedCount>2){
			otherPhysician3Label.setVisible(true);
			otherPhysician3.setVisible(true);
		}
		if(selectedCount>3){
			otherPhysician4Label.setVisible(true);
			otherPhysician4.setVisible(true);
		}
		
	}
	
	protected void populateForm() {

		if(Util.getProgramMode() == Util.ProgramMode.JSONRPC){
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.PatientInterface.PatientInformation",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Window.alert(ex.toString());
					}
	
					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>");
								if (result != null) {
									
									addressContainer.setPatient(patientId);
									
									if(CurrentState.isActionAllowed(CoveragesModuleName,AppConstants.READ))
										patientCoverages.setPatient(patientId);
									if(CurrentState.isActionAllowed(AuthorizationsModuleName,AppConstants.READ))
										patientAuthorizations.setPatient(patientId);
									
									// Demographics screen
//									wTitle.setWidgetValue((String) result
//											.get((String) "ptsalut"));
									title.setWidgetValue((String) result
											.get((String) "ptsalut"));
									wLastName.setText((String) result.get((String) "ptlname"));
									wFirstName.setText((String) result.get((String) "ptfname"));
									wMiddleName.setText((String) result.get((String) "ptmname"));


									suffix.setWidgetValue((String) result
											.get((String) "ptsuffix"));
									gender
											.setWidgetValue((String) result
													.get((String) "ptsex"));

									wDob.setValue((String) result.get((String) "ptdob"));
	
									// Contact screen
									preferredContact.setWidgetValue((String) result
											.get((String) "ptprefcontact"));
									phoneHome.setText((String) result.get((String) "pthphone"));
									phoneWork.setText((String) result.get((String) "ptwphone"));
									phoneMobile
											.setText((String) result.get((String) "ptmphone"));
									phoneFax.setText((String) result.get((String) "ptfax"));
									emailAddress.setText((String) result.get((String) "ptemail"));
									
									
									// Personal screen
									martialStatus.setWidgetValue((String) result.get((String) "ptmarital"));
									employmentStatus.setWidgetValue((String) result.get((String) "ptempl"));
									socialSecurityNumber.setText((String) result.get((String) "ptssn"));

									race.setWidgetValue((String) result.get((String) "ptrace"));
									religion.setWidgetValue((String) result.get((String) "ptreligion"));
									languages.setWidgetValue((String) result.get((String) "ptprimarylanguage"));
									typeofBilling.setWidgetValue((String) result.get((String) "ptbilltype"));
									monthlyBudgetAmount.setText((String) result.get((String) "ptbudg"));
									patientPracticeID.setText((String) result.get((String) "ptid"));
									
									// Medical screen
//									bloodType.setWidgetValue((String) result.get((String) "ptblood"));
									if(result.get((String) "ptprimaryfacility")!=null & result.get((String) "ptprimaryfacility").length()>0)
										primaryFacility.setValue(new Integer(result.get((String) "ptprimaryfacility")));
									if(result.get((String) "ptpharmacy")!=null & result.get((String) "ptpharmacy").length()>0)
										preferredPharmacy.setValue(new Integer(result.get((String) "ptpharmacy")));
									if(result.get((String) "ptdoc")!=null & result.get((String) "ptdoc").length()>0)
										inHouseDoctor.setValue(new Integer(result.get((String) "ptdoc")));
									if(result.get((String) "ptrefdoc")!=null & result.get((String) "ptrefdoc").length()>0)
										referringDoctor.setValue(new Integer(result.get((String) "ptrefdoc")));
									if(result.get((String) "ptpcp")!=null & result.get((String) "ptpcp").length()>0)
										primaryCarePhysician.setValue(new Integer(result.get((String) "ptpcp")));

									int tempCount=0;
									if(result.get((String) "ptphy1")!=null & result.get((String) "ptphy1").length()>0){
										otherPhysician1.setValue(new Integer(result.get((String) "ptphy1")));
										tempCount++;
									}
									if(result.get((String) "ptphy2")!=null & result.get((String) "ptphy2").length()>0){
										otherPhysician2.setValue(new Integer(result.get((String) "ptphy2")));
										tempCount++;
									}
									if(result.get((String) "ptphy3")!=null & result.get((String) "ptphy3").length()>0){
										otherPhysician3.setValue(new Integer(result.get((String) "ptphy3")));
										tempCount++;
									}
									if(result.get((String) "ptphy4")!=null & result.get((String) "ptphy4").length()>0){
										otherPhysician4.setValue(new Integer(result.get((String) "ptphy4")));
										tempCount++;
									}

									updateOtherPhysiciansBoxes(tempCount);
									numberofOtherPhysicians.setWidgetValue(tempCount+"");
									// Populate address container
									
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
		}

	}
	
	public void generatePracticeId(){
		if(!wFirstName.getText().trim().equals("") && !wLastName.getText().trim().equals("") && !socialSecurityNumber.getText().trim().equals("")){
			String wMiddleNameTXT = wMiddleName.getText().trim().equals("")?"X":wMiddleName.getText().charAt(0)+"";
			String ptid = wFirstName.getText().charAt(0)+wMiddleNameTXT+wLastName.getText().charAt(0)+"-";
			String ssn = socialSecurityNumber.getText();
			if(ssn.length()<4)
				ptid+=ssn;
			else
				ptid+=ssn.substring(ssn.length()-4);
			patientPracticeID.setText(ptid);
		}
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
		m.put((String) "ptdob", (String) wDob.getStoredValue());
//		m.put((String) "ptsalut", (String) wTitle.getWidgetValue());
		if(title.getWidgetValue()!=null)
			m.put((String) "ptsalut", (String) title.getWidgetValue());
		m.put((String) "ptlname", (String) wLastName.getText());
		m.put((String) "ptfname", (String) wFirstName.getText());
		m.put((String) "ptmname", (String) wMiddleName.getText());
		if(suffix.getWidgetValue()!=null)
			m.put((String) "ptsuffix", (String) suffix.getWidgetValue());
		m.put((String) "ptsex", (String) gender.getWidgetValue());

		// Contact screen
		m.put((String) "ptprefcontact", (String) preferredContact
				.getWidgetValue());
		m.put((String) "pthphone", (String) phoneHome.getText());
		m.put((String) "ptwphone", (String) phoneWork.getText());
		m.put((String) "ptmphone", (String) phoneMobile.getText());
		m.put((String) "ptfax", (String) phoneFax.getText());
		m.put((String) "ptemail", (String) emailAddress.getText());

//		preferredContact.setWidgetValue((String) m
//				.get((String) "ptprefcontact"));
//		phoneHome.setText((String) m.get((String) "pthphone"));
//		phoneWork.setText((String) m.get((String) "ptwphone"));
//		phoneMobile.setText((String) m.get((String) "ptmphone"));
//		phoneFax.setText((String) m.get((String) "ptfax"));

		// Personal screen
		if(martialStatus.getWidgetValue()!=null)
			m.put((String) "ptmarital", (String) martialStatus.getWidgetValue());
		m.put((String) "ptempl", (String) employmentStatus.getWidgetValue());
		m.put((String) "ptssn", (String) socialSecurityNumber.getText());
		m.put((String) "ptrace", (String) race.getWidgetValue());
		m.put((String) "ptreligion", (String) religion.getWidgetValue());
		m.put((String) "ptprimarylanguage", (String) languages.getWidgetValue());
		m.put((String) "ptbilltype", (String) typeofBilling.getWidgetValue());
		m.put((String) "ptbudg", (String) monthlyBudgetAmount.getText());
		m.put((String) "ptid", (String) patientPracticeID.getText());
		
		// Medical screen
//		m.put((String) "ptblood", (String) bloodType.getWidgetValue());
		if(primaryFacility.getText().length()>0)
			m.put((String) "ptprimaryfacility", (String) primaryFacility.getStoredValue());
		if(preferredPharmacy.getText().length()>0)
			m.put((String) "ptpharmacy", (String) preferredPharmacy.getStoredValue());
		if(inHouseDoctor.getText().length()>0)
			m.put((String) "ptdoc", (String) inHouseDoctor.getStoredValue());
		if(referringDoctor.getText().length()>0)
			m.put((String) "ptrefdoc", (String) referringDoctor.getStoredValue());
		if(primaryCarePhysician.getText().length()>0)
			m.put((String) "ptpcp", (String) primaryCarePhysician.getStoredValue());
		if(otherPhysician1.isVisible() && otherPhysician1.getText().length()>0)
			m.put((String) "ptphy1", (String) otherPhysician1.getStoredValue());
		if(otherPhysician2.isVisible() && otherPhysician2.getText().length()>0)
			m.put((String) "ptphy2", (String) otherPhysician2.getStoredValue());
		if(otherPhysician3.isVisible() && otherPhysician3.getText().length()>0)
			m.put((String) "ptphy3", (String) otherPhysician3.getStoredValue());
		if(otherPhysician4.isVisible() && otherPhysician4.getText().length()>0)
			m.put((String) "ptphy4", (String) otherPhysician4.getStoredValue());
		return m;
	}

	protected ModuleInterfaceAsync getModuleProxy() {
		ModuleInterfaceAsync p = null;
		try {
			p = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception e) {
			JsonUtil.debug("Exception");
		}
		return p;
	}

	@SuppressWarnings({ "rawtypes", "unchecked" })
	protected boolean validateForm() {
		String msg = new String("");
		if (wLastName.getText().length() < 2) {
			msg += _("Please specify a last name.") + "\n";
		}
		if (wFirstName.getText().length() < 2) {
			msg += _("Please specify a first name.") + "\n";
		}
		if (wDob.getTextBox().getText().length()<10) {
			msg += "Please specify date of birth." + "\n";
		}
//		if (wGender.getSelectedIndex()<1) {
//			msg += "Please specify gender." + "\n";
//		}
		if (gender.getWidgetValue()==null) {
			msg += _("Please specify gender.") + "\n";
		}
		
		HashMap addressMap = addressContainer.getAddresses();
		if(addressMap!=null && addressMap.size()>0){
			Iterator<Integer> iter = addressMap.keySet().iterator();
			while (iter.hasNext()) {
				Integer key = iter.next();
				Address address=(Address)addressMap.get(key);
				if(address.getLine1()==null || address.getLine1().equals("")){
					msg += _("Please specify at least one address.") + "\n";
					break;
				}
			}
		} else {
			msg += _("Please specify at least one address.") + "\n";
		}						
		
		if (!msg.equals("")) {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	public void loadLanguages(){
		Util.callModuleMethod("org.freemedsoftware.module.i18nLanguages", "GetAll", (Integer)null, new CustomRequestCallback() {
			@Override
			public void onError() {
			}
			@Override
			public void jsonifiedData(Object data) {
				if(data!=null ){
					try{
						@SuppressWarnings("unchecked")
						HashMap<String,String>[] langs=(HashMap<String,String>[])data;
						for(int i=0;i<langs.length;i++){
							languages.addItem(langs[i].get("language"), langs[i].get("abbrev"));
						}
					}
					catch(Exception e){
						
					}
				}
			}
		}, "HashMap<String,String>[]");
	}
	
	@Override
	public void closeScreen() {
		super.closeScreen();
		removeInstance(this);
	}	
	
	public static String returnEmploymentStatus(String id){		
		if(id.equalsIgnoreCase("y"))
		{
			return _("Yes");
		}		
		else if(id.equalsIgnoreCase("n"))
		{
			return _("No");
		}		
		else if(id.equalsIgnoreCase("p"))
		{
			return _("Part time");
		}				
		else if(id.equalsIgnoreCase("s"))
		{
			return _("Self");
		}
		else if(id.equalsIgnoreCase("r"))
		{
			return _("Retired");
		}		
		else if(id.equalsIgnoreCase("M"))
		{
			return _("Military");
		}		
		else
		{
	       return _("Unknown");
		}	
	}
	
	public static String returnRace(int id){		
		if(id==1)
		{
			return _("Hispanic, white");
		}
		
		else if(id==2)
		{
			return _("Hispanic, black");
		}
		
		else if(id==3)
		{
			return _("American Indian or Alaska Native");
		}
		else if(id==4)
		{
			return _("Black, not of Hispanic origin");
		}
		else if(id==5)
		{
			return _("Asian or Pacific Islander");
		}
		else if(id==6)
		{
			return _("White, not of Hispanic origin");
		}		
		else
	       return _("Unknown race");	
	}
	
	public static String returnReligion(int id){		
		HashMap<Integer, String> religionList=religions();	  
		return religionList.get(id); 	
	}
	
	public static HashMap<Integer, String> religions()
	{
		religions=new HashMap<Integer, String>();		
		religions.put(0, _("Catholic"));
		religions.put(1, _("Jewish"));
		religions.put(2, _("Eastern Orthodox"));		
		religions.put(3, _("Baptist"));
		religions.put(4, _("Methodist"));
		religions.put(5, _("Lutheran"));
		religions.put(6, _("Presbyterian"));
		religions.put(7, _("United Church of God"));
		religions.put(8, _("Episcopalian"));
		religions.put(9, _("Adventist"));
		religions.put(10, _("Assembly of God"));
		religions.put(11, _("Brethren"));
		religions.put(12, _("Christian Scientist"));
		religions.put(13, _("Church of Christ"));
		religions.put(14, _("Church of God"));
		religions.put(15, _("Disciples of Christ"));
		religions.put(16, _("Evangelical Covenant"));
		religions.put(17, _("Friends"));
		religions.put(18, _("Jehovah's Witness"));
		religions.put(19, _("Latter-Day Saints"));
		religions.put(20, _("Islam"));
		religions.put(21, _("Nazarene"));
		religions.put(22, _("Other"));
		religions.put(23, _("Pentecostal"));
		religions.put(24, _("Protestant, Other"));
		religions.put(25, _("Protestant, No Denomination"));
		religions.put(26, _("Reformed"));
		religions.put(27, _("Salvation Army"));
		religions.put(28, _("Unitarian; Universalist"));
		religions.put(29, _("Unknown/No preference"));
		religions.put(30, _("Native American"));
		religions.put(31, _("Buddhist"));
		return religions;
	}
		
	public static String returnTypeOfBilling(String id){
		if(id.equalsIgnoreCase("mon"))
		{
			return _("Monthly Billing On Account");
		}		
		else if(id.equalsIgnoreCase("sta"))
		{
			return _("Statement Billing");
		}		
		else if(id.equalsIgnoreCase("chg"))
		{
			return _("Charge Card Billing");
		}					
		else
	       return _("NONE SELECTED");	
	}
	
}
