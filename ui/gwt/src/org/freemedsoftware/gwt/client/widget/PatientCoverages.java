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
import java.util.Iterator;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
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
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientCoverages extends Composite {

	public class Coverage {

		protected Integer coverageId;

		protected Integer insuranceCompany;

		protected Integer coverageInsuranceType;

		protected String providerAcceptsAssigment = "";

		protected String assigmentOfBenefits = "";

		protected String releaseOfInformation = "";

		protected String releaseDateSigned = null;

		protected String groupPlanName = "";

		protected String startDate = null;

		protected String insuranceIDNumber = "";

		protected String insuranceGroupNumber = "";

		protected String insuranceType = "";

		protected String relationshipToInsured = "";

		protected String insuredFirstName = "";

		protected String insuredLastName = "";

		protected String insuredMiddleName = "";

		protected String insuredDOB = "";

		protected String insuredSex = "";

		protected String insuredSSN = "";

		protected String insuredAddress1 = "";

		protected String insuredAddress2 = "";

		protected String insuredCity = "";

		protected String insuredState = "";

		protected String insuredZip = "";

		protected String copay = "";

		protected String deductable = "";

		protected String replaceLikeCoverage = "";

		protected String isAssigning = "";

		protected String schoolNameForInsured = "";

		protected String employerOfInsured = "";

		public Coverage() {
		}

		/**
		 * Retrieve HashMap for object used from RPC.
		 * 
		 * @return
		 */
		public HashMap<String, String> getMap() {
			HashMap<String, String> map = new HashMap<String, String>();
			if (coverageId != null)
				map.put("id", coverageId.toString());
			if (getInsuranceCompany() != null)
				map.put("covinsco", getInsuranceCompany().toString());
			if (getCoverageInsuranceType() != null)
				map.put("covinstp", getCoverageInsuranceType().toString());
			map.put("covprovasgn", getProviderAcceptsAssigment());
			map.put("covbenasgn", getAssigmentOfBenefits());
			map.put("covrelinfo", getReleaseOfInformation());
			if (getReleaseDateSigned() != null)
				map.put("covrelinfodt", getReleaseDateSigned().toString());
			map.put("covplanname", getGroupPlanName());
			if (getStartDate() != null)
				map.put("coveffdt", getStartDate().toString());
			map.put("covpatinsno", getInsuranceIDNumber());
			map.put("covpatgrpno", getInsuranceGroupNumber());
			map.put("covtype", getInsuranceType());
			map.put("covrel", getRelationshipToInsured());

			if (!getRelationshipToInsured().equalsIgnoreCase("S")) {
				map.put("covfname", getInsuredFirstName());
				map.put("covlname", getInsuredLastName());
				map.put("covmname", getInsuredMiddleName());
				map.put("covdob", getInsuredDOB());
				map.put("covsex", getInsuredSex());
				map.put("covssn", getInsuredSSN());
				map.put("covaddr1", getInsuredAddress1());
				map.put("covaddr2", getInsuredAddress2());
				map.put("covcity", getInsuredCity());
				map.put("covstate", getInsuredState());
				map.put("covzip", getInsuredZip());
			}

			map.put("covcopay", getCopay());
			map.put("covdeduct", getDeductable());
			map.put("", getReplaceLikeCoverage());
			map.put("covisassigning", getIsAssigning());
			map.put("covschool", getSchoolNameForInsured());
			map.put("covemployer", getEmployerOfInsured());
			HashMap<String, String> newMap = new HashMap<String, String>();
			Iterator<String> iterator = map.keySet().iterator();
			while(iterator.hasNext()){
				String key = iterator.next();
				String value = map.get(key);
				if(value!=null){
					newMap.put(key, value);
				}
			}
			return newMap;
		}

		/**
		 * Retrieve HashMap for object used from RPC.
		 * 
		 * @return
		 */
		public void loadData(HashMap<String, String> data) {
			if (data.get("id") != null)
				setCoverageId(Integer.parseInt(data.get("id")));
			if (data.get("covinsco") != null)
				setInsuranceCompany(Integer.parseInt(data.get("covinsco")));
			if (data.get("covinstp") != null)
				setCoverageInsuranceType(Integer.parseInt(data.get("covinstp")));
			setProviderAcceptsAssigment(data.get("covprovasgn"));
			setAssigmentOfBenefits(data.get("covbenasgn"));
			setReleaseOfInformation(data.get("covrelinfo"));
			setReleaseDateSigned(data.get("covrelinfodt"));
			setGroupPlanName(data.get("covplanname"));
			setStartDate(data.get("coveffdt"));// temporary
			setInsuranceIDNumber(data.get("covpatinsno"));
			setInsuranceGroupNumber(data.get("covpatgrpno"));
			setInsuranceType(data.get("covtype"));
			setRelationshipToInsured(data.get("covrel"));
			if (!getRelationshipToInsured().equalsIgnoreCase("S")) {
				setInsuredFirstName(data.get("covfname"));
				setInsuredLastName(data.get("covlname"));
				setInsuredMiddleName(data.get("covmname"));
				setInsuredDOB(data.get("covdob"));
				setInsuredSex(data.get("covsex"));
				setInsuredSSN(data.get("covssn"));
				setInsuredAddress1(data.get("covaddr1"));
				setInsuredAddress2(data.get("covaddr2"));
				setInsuredCity(data.get("covcity"));
				setInsuredState(data.get("covstate"));
				setInsuredZip(data.get("covzip"));
			}
			setCopay(data.get("covcopay"));
			setDeductable(data.get("covdeduct"));
			setReplaceLikeCoverage(data.get(""));
			setIsAssigning(data.get("covisassigning"));
			setSchoolNameForInsured(data.get("covschool"));
			setEmployerOfInsured(data.get("covemployer"));
		}
		
		public String getProviderAcceptsAssigment() {
			return providerAcceptsAssigment;
		}

		public void setProviderAcceptsAssigment(String providerAcceptsAssigment) {
			this.providerAcceptsAssigment = providerAcceptsAssigment;
		}

		public String getAssigmentOfBenefits() {
			return assigmentOfBenefits;
		}

		public void setAssigmentOfBenefits(String assigmentOfBenefits) {
			this.assigmentOfBenefits = assigmentOfBenefits;
		}

		public String getReleaseOfInformation() {
			return releaseOfInformation;
		}

		public void setReleaseOfInformation(String releaseOfInformation) {
			this.releaseOfInformation = releaseOfInformation;
		}

		public String getReleaseDateSigned() {
			return releaseDateSigned;
		}

		public void setReleaseDateSigned(String releaseDateSigned) {
			this.releaseDateSigned = releaseDateSigned;
		}

		public String getGroupPlanName() {
			return groupPlanName;
		}

		public void setGroupPlanName(String groupPlanName) {
			this.groupPlanName = groupPlanName;
		}

		public String getStartDate() {
			return startDate;
		}

		public void setStartDate(String startDate) {
			this.startDate = startDate;
		}

		public String getInsuranceIDNumber() {
			return insuranceIDNumber;
		}

		public void setInsuranceIDNumber(String insuranceIDNumber) {
			this.insuranceIDNumber = insuranceIDNumber;
		}

		public String getInsuranceGroupNumber() {
			return insuranceGroupNumber;
		}

		public void setInsuranceGroupNumber(String insuranceGroupNumber) {
			this.insuranceGroupNumber = insuranceGroupNumber;
		}

		public String getInsuranceType() {
			return insuranceType;
		}

		public void setInsuranceType(String insuranceType) {
			this.insuranceType = insuranceType;
		}

		public String getRelationshipToInsured() {
			return relationshipToInsured;
		}

		public void setRelationshipToInsured(String relationshipToInsured) {
			this.relationshipToInsured = relationshipToInsured;
		}

		public String getCopay() {
			return copay;
		}

		public void setCopay(String copay) {
			this.copay = copay;
		}

		public String getDeductable() {
			return deductable;
		}

		public void setDeductable(String deductable) {
			this.deductable = deductable;
		}

		public String getReplaceLikeCoverage() {
			return replaceLikeCoverage;
		}

		public void setReplaceLikeCoverage(String replaceLikeCoverage) {
			this.replaceLikeCoverage = replaceLikeCoverage;
		}

		public String getIsAssigning() {
			return isAssigning;
		}

		public void setIsAssigning(String isAssigning) {
			this.isAssigning = isAssigning;
		}

		public String getSchoolNameForInsured() {
			return schoolNameForInsured;
		}

		public void setSchoolNameForInsured(String schoolNameForInsured) {
			this.schoolNameForInsured = schoolNameForInsured;
		}

		public String getEmployerOfInsured() {
			return employerOfInsured;
		}

		public void setEmployerOfInsured(String employerOfInsured) {
			this.employerOfInsured = employerOfInsured;
		}

		public void setInsuranceCompany(Integer insuranceCompany) {
			this.insuranceCompany = insuranceCompany;
		}

		public Integer getInsuranceCompany() {
			return insuranceCompany;
		}

		public void setCoverageInsuranceType(Integer coverageInsuranceType) {
			this.coverageInsuranceType = coverageInsuranceType;
		}

		public Integer getCoverageInsuranceType() {
			return coverageInsuranceType;
		}

		public Integer getCoverageId() {
			return coverageId;
		}

		public void setCoverageId(Integer coverageId) {
			this.coverageId = coverageId;
		}

		public String getInsuredFirstName() {
			return insuredFirstName;
		}

		public void setInsuredFirstName(String insuredFirstName) {
			this.insuredFirstName = insuredFirstName;
		}

		public String getInsuredLastName() {
			return insuredLastName;
		}

		public void setInsuredLastName(String insuredLastName) {
			this.insuredLastName = insuredLastName;
		}

		public String getInsuredMiddleName() {
			return insuredMiddleName;
		}

		public void setInsuredMiddleName(String insuredMiddleName) {
			this.insuredMiddleName = insuredMiddleName;
		}

		public String getInsuredDOB() {
			return insuredDOB;
		}

		public void setInsuredDOB(String insuredDOB) {
			this.insuredDOB = insuredDOB;
		}

		public String getInsuredSex() {
			return insuredSex;
		}

		public void setInsuredSex(String insuredSex) {
			this.insuredSex = insuredSex;
		}

		public String getInsuredSSN() {
			return insuredSSN;
		}

		public void setInsuredSSN(String insuredSSN) {
			this.insuredSSN = insuredSSN;
		}

		public String getInsuredAddress1() {
			return insuredAddress1;
		}

		public void setInsuredAddress1(String insuredAddress1) {
			this.insuredAddress1 = insuredAddress1;
		}

		public String getInsuredAddress2() {
			return insuredAddress2;
		}

		public void setInsuredAddress2(String insuredAddress2) {
			this.insuredAddress2 = insuredAddress2;
		}

		public String getInsuredCity() {
			return insuredCity;
		}

		public void setInsuredCity(String insuredCity) {
			this.insuredCity = insuredCity;
		}

		public String getInsuredState() {
			return insuredState;
		}

		public void setInsuredState(String insuredState) {
			this.insuredState = insuredState;
		}

		public String getInsuredZip() {
			return insuredZip;
		}

		public void setInsuredZip(String insuredZip) {
			this.insuredZip = insuredZip;
		}

	}

	protected Integer patientId = new Integer(0);

	VerticalPanel coveragesPanel = new VerticalPanel();

	protected HashMap<Integer, Coverage> coverages;

	protected CurrentState state = null;

	protected Command onCompletion = null;

	protected String ModuleName = "PatientCoverages";

	protected CustomButton addCoveragesButton = null; 
	
	protected Integer maxCoveragesCount = null; 
	
	public PatientCoverages() {
		coverages = new HashMap<Integer, Coverage>();

		VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setWidth("100%");
		initWidget(verticalPanel);

		coveragesPanel = new VerticalPanel();
		coveragesPanel.setWidth("100%");
		verticalPanel.add(coveragesPanel);

		HorizontalPanel hP = new HorizontalPanel();
		verticalPanel.add(hP);

		if (CurrentState.isActionAllowed(ModuleName, AppConstants.WRITE)) {
			addCoveragesButton = new CustomButton("Add Coverage",
					AppConstants.ICON_ADD);
			addCoveragesButton.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent evt) {
					Coverage a = new Coverage();
					addCoverage(coverages.size() + 1, a);
					if(maxCoveragesCount!=null && coverages.size()==maxCoveragesCount)
						addCoveragesButton.setVisible(false);
				}
			});
			hP.add(addCoveragesButton);
		}
	}

	/**
	 * Set <Command> which is run on completion of data submission.
	 * 
	 * @param oc
	 */
	public void setOnCompletion(Command oc) {
		onCompletion = oc;
	}

	public void addCoverage(HashMap<String, String> coverageData) {
		Coverage coverage = new Coverage();
		coverage.loadData(coverageData);
		addCoverage(coverages.size() + 1, coverage);
	}

	public void addEmptyCoverage() {
		Coverage coverage = new Coverage();
		addCoverage(coverages.size() + 1, coverage);
	}
	
	public HashMap<String, String> getCoverageData(int index){
		Coverage coverage = coverages.get(index);
		return coverage!=null?coverage.getMap():null;
	}

	public void loadCoverageData(int index, HashMap<String, String> data){
		Coverage coverage = coverages.get(index);
		if(coverage==null){
			addCoverage(data);
			if(maxCoveragesCount==coverages.size())
				addCoveragesButton.setVisible(false);
		}else
			coverage.loadData(data);
	}
	public void removeCoverage(int index){
		if(coverages.get(index)!=null){
			coveragesPanel.remove(index-1);
			coverages.remove(index);
			if(coverages.size()<maxCoveragesCount)
			addCoveragesButton.setVisible(true);
		}
	}
	
	/**
	 * Add additional Coverage object to a particular position on the flexTable.
	 * 
	 * @param pos
	 *            Integer row number
	 * @param coverage
	 *            Coverage object containing population data.
	 */
	@SuppressWarnings("unchecked")
	public void addCoverage(final Integer pos, final Coverage coverage) {
		// Keep a record of this
		coverages.put(pos, coverage);
		int row = 0;

		final CustomTable flexTable = new CustomTable();
		flexTable.setWidth("100%");
		flexTable.removeTableStyle();
		coveragesPanel.add(flexTable);

		final Label insuranceCompanyLabel = new Label("Insurance Company:");
		flexTable.getFlexTable().setWidget(row, 0, insuranceCompanyLabel);
		final SupportModuleWidget insuranceCompany = new SupportModuleWidget(
				"InsuranceCompanyModule");
		flexTable.getFlexTable().setWidget(row, 1, insuranceCompany);

		final Label coverageInsuranceTypeLabel = new Label(
				"Coverage Insurance Type:");
		flexTable.getFlexTable().setWidget(row, 2, coverageInsuranceTypeLabel);
		final SupportModuleWidget coverageInsuranceType = new SupportModuleWidget(
				"CoverageTypes");
		flexTable.getFlexTable().setWidget(row, 3, coverageInsuranceType);

		if (CurrentState.isActionAllowed(ModuleName, AppConstants.DELETE)) {
			final Label deleCoverageLabel = new Label("Delete This Coverage:");
			flexTable.getFlexTable().setWidget(row, 4, deleCoverageLabel);
			CustomButton deleCoverageButton = new CustomButton("Delete",
					AppConstants.ICON_DELETE);
			deleCoverageButton.setWidth("100%");
			deleCoverageButton.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent evt) {
					coveragesPanel.remove(flexTable);
					coverages.remove(pos);
					if(maxCoveragesCount!=null && coverages.size()<maxCoveragesCount)
						addCoveragesButton.setVisible(true);
					if (coverage.getCoverageId() != null)
						deleteCoverage(coverage.getCoverageId());
				}
			});
			flexTable.getFlexTable().setWidget(row, 5, deleCoverageButton);
		}
		row++;

		final Label providerAcceptsAssigmentLabel = new Label(
				"Provider Accepts Assigment:");
		flexTable.getFlexTable().setWidget(row, 0,
				providerAcceptsAssigmentLabel);
		final CustomRadioButtonGroup providerAcceptsAssigment = new CustomRadioButtonGroup(
				"providerAcceptsAssigment" + pos);
		providerAcceptsAssigment.addItem("Yes", "1");
		providerAcceptsAssigment.addItem("No", "0");
		flexTable.getFlexTable().setWidget(row, 1, providerAcceptsAssigment);

		final Label assigmentOfBenefitsLabel = new Label(
				"Assigment Of Benefits:");
		flexTable.getFlexTable().setWidget(row, 2, assigmentOfBenefitsLabel);
		final CustomRadioButtonGroup assigmentOfBenefits = new CustomRadioButtonGroup(
				"assigmentOfBenefits" + pos);
		assigmentOfBenefits.addItem("Yes", "1");
		assigmentOfBenefits.addItem("No", "0");
		flexTable.getFlexTable().setWidget(row, 3, assigmentOfBenefits);

		final Label releaseOfInformationLabel = new Label(
				"Release Of Information:");
		flexTable.getFlexTable().setWidget(row, 4, releaseOfInformationLabel);
		final CustomRadioButtonGroup releaseOfInformation = new CustomRadioButtonGroup(
				"releaseOfInformation" + pos);
		releaseOfInformation.addItem("Yes", "1");
		releaseOfInformation.addItem("No", "0");
		flexTable.getFlexTable().setWidget(row, 5, releaseOfInformation);

		row++;

		final Label releaseDateSignedLabel = new Label("Release Date Signed:");
		flexTable.getFlexTable().setWidget(row, 0, releaseDateSignedLabel);
		final CustomDatePicker releaseDateSigned = new CustomDatePicker();
		flexTable.getFlexTable().setWidget(row, 1, releaseDateSigned);

		final Label groupPlanNameLabel = new Label("Group - Plan Name:");
		flexTable.getFlexTable().setWidget(row, 2, groupPlanNameLabel);
		final TextBox groupPlanName = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, groupPlanName);

		final Label startDateLabel = new Label("Start Date:");
		flexTable.getFlexTable().setWidget(row, 4, startDateLabel);
		final CustomDatePicker startDate = new CustomDatePicker();
		flexTable.getFlexTable().setWidget(row, 5, startDate);

		row++;

		final Label insuranceIDNumberLabel = new Label("Insurance ID Number:");
		flexTable.getFlexTable().setWidget(row, 0, insuranceIDNumberLabel);
		final TextBox insuranceIDNumber = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, insuranceIDNumber);

		final Label insuranceGroupNumberLabel = new Label(
				"Insurance Group Number:");
		flexTable.getFlexTable().setWidget(row, 2, insuranceGroupNumberLabel);
		final TextBox insuranceGroupNumber = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, insuranceGroupNumber);

		final Label insuranceTypeLabel = new Label("Insurance Type:");
		flexTable.getFlexTable().setWidget(row, 4, insuranceTypeLabel);
		final CustomRadioButtonGroup insuranceType = new CustomRadioButtonGroup(
				"insuranceType" + pos);
		insuranceType.addItem("Primary", "1");
		insuranceType.addItem("Secondary", "2");
		insuranceType.addItem("Tertiary", "3");
		insuranceType.addItem("Work Comp", "4");
		flexTable.getFlexTable().setWidget(row, 5, insuranceType);

		row++;

		final Label relationshipToInsuredLabel = new Label(
				"Relationship to Insured:");
		flexTable.getFlexTable().setWidget(row, 0, relationshipToInsuredLabel);
		final CustomListBox relationshipToInsured = new CustomListBox();
		relationshipToInsured.addItem("Self", "S");
		relationshipToInsured.addItem("Child", "C");
		relationshipToInsured.addItem("Husband", "H");
		relationshipToInsured.addItem("Wife", "W");
		relationshipToInsured.addItem("Child Not Fin", "D");
		relationshipToInsured.addItem("Step Child", "SC");
		relationshipToInsured.addItem("Foster Child", "FC");
		relationshipToInsured.addItem("Ward of Court", "WC");
		relationshipToInsured.addItem("HC Dependent", "HD");
		relationshipToInsured.addItem("Sponsored Dependent", "SD");
		relationshipToInsured.addItem("Medicare Legal Rep", "LR");
		relationshipToInsured.addItem("Other", "O");
		flexTable.getFlexTable().setWidget(row, 1, relationshipToInsured);

		final int insStartRow = row, insStartCol = 2;

		final Label insuredFirstNameLabel = new Label("Insured First Name:");
		flexTable.getFlexTable().setWidget(row, 2, insuredFirstNameLabel);
		final TextBox insuredFirstName = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, insuredFirstName);

		final Label insuredLastNameLabel = new Label("Insured Last Name:");
		flexTable.getFlexTable().setWidget(row, 4, insuredLastNameLabel);
		final TextBox insuredLastName = new TextBox();
		flexTable.getFlexTable().setWidget(row, 5, insuredLastName);

		row++;

		final Label insuredMiddleNameLabel = new Label("Insured Middle Name:");
		flexTable.getFlexTable().setWidget(row, 0, insuredMiddleNameLabel);
		final TextBox insuredMiddleName = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, insuredMiddleName);

		final Label insuredDOBLabel = new Label("Insured DOB:");
		flexTable.getFlexTable().setWidget(row, 2, insuredDOBLabel);
		final CustomDatePicker insuredDOB = new CustomDatePicker();
		flexTable.getFlexTable().setWidget(row, 3, insuredDOB);

		final Label insuredSexLabel = new Label("Insured Sex:");
		flexTable.getFlexTable().setWidget(row, 4, insuredSexLabel);
		final CustomRadioButtonGroup insuredSex = new CustomRadioButtonGroup(
				"insuredSex" + pos);
		insuredSex.addItem("Male", "m");
		insuredSex.addItem("Female", "f");
		insuredSex.addItem("Transgendered", "t");
		flexTable.getFlexTable().setWidget(row, 5, insuredSex);

		row++;

		final Label insuredSSNLabel = new Label("Insured SSN:");
		flexTable.getFlexTable().setWidget(row, 0, insuredSSNLabel);
		final TextBox insuredSSN = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, insuredSSN);

		final Label insuredAddress1Label = new Label("Insured Address1:");
		flexTable.getFlexTable().setWidget(row, 2, insuredAddress1Label);
		final TextBox insuredAddress1 = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, insuredAddress1);

		final Label insuredAddress2Label = new Label("Insured Address2:");
		flexTable.getFlexTable().setWidget(row, 4, insuredAddress2Label);
		final TextBox insuredAddress2 = new TextBox();
		flexTable.getFlexTable().setWidget(row, 5, insuredAddress2);

		row++;

		final Label insuredCityLabel = new Label("Insured City:");
		flexTable.getFlexTable().setWidget(row, 0, insuredCityLabel);
		final TextBox insuredCity = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, insuredCity);

		final Label insuredStateLabel = new Label("Insured State:");
		flexTable.getFlexTable().setWidget(row, 2, insuredStateLabel);
		final TextBox insuredState = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, insuredState);

		final Label insuredZipLabel = new Label("Insured Zip:");
		flexTable.getFlexTable().setWidget(row, 4, insuredZipLabel);
		final TextBox insuredZip = new TextBox();
		flexTable.getFlexTable().setWidget(row, 5, insuredZip);

		final int insEndRow = row, insEndCol = 5;

		row++;

		final Label copayLabel = new Label("Copay:");
		flexTable.getFlexTable().setWidget(row, 0, copayLabel);
		final TextBox copay = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, copay);

		final Label deductableLabel = new Label("Deductable:");
		flexTable.getFlexTable().setWidget(row, 2, deductableLabel);
		final TextBox deductable = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, deductable);

		final Label replaceLikeCoverageLabel = new Label(
				"Replace Like Coverage:");
		flexTable.getFlexTable().setWidget(row, 4, replaceLikeCoverageLabel);
		final CustomRadioButtonGroup replaceLikeCoverage = new CustomRadioButtonGroup(
				"replaceLikeCoverage" + pos);
		replaceLikeCoverage.addItem("Yes", "1");
		replaceLikeCoverage.addItem("No", "0");
		flexTable.getFlexTable().setWidget(row, 5, replaceLikeCoverage);

		row++;

		final Label isAssigningLabel = new Label("Is Assigning?");
		flexTable.getFlexTable().setWidget(row, 0, isAssigningLabel);
		final CustomRadioButtonGroup isAssigning = new CustomRadioButtonGroup(
				"isAssigning" + pos);
		isAssigning.addItem("Yes", "1");
		isAssigning.addItem("No", "0");
		flexTable.getFlexTable().setWidget(row, 1, isAssigning);

		final Label schoolNameForInsuredLabel = new Label(
				"School Name for Insured:");
		flexTable.getFlexTable().setWidget(row, 2, schoolNameForInsuredLabel);
		final TextBox schoolNameForInsured = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, schoolNameForInsured);

		final Label employerOfInsuredLabel = new Label("Employer of Insured:");
		flexTable.getFlexTable().setWidget(row, 4, employerOfInsuredLabel);
		final TextBox employerOfInsured = new TextBox();
		flexTable.getFlexTable().setWidget(row, 5, employerOfInsured);

		final ChangeHandler cl = new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				Coverage x = coverages.get(pos);
				x.setInsuranceCompany(insuranceCompany.getValue());
				x.setCoverageInsuranceType(coverageInsuranceType.getValue());
				x.setProviderAcceptsAssigment(providerAcceptsAssigment
						.getWidgetValue());
				x.setAssigmentOfBenefits(assigmentOfBenefits.getWidgetValue());
				x
						.setReleaseOfInformation(releaseOfInformation
								.getWidgetValue());
				x.setReleaseDateSigned(releaseDateSigned.getStoredValue());
				x.setGroupPlanName(groupPlanName.getText());
				x.setStartDate(startDate.getStoredValue());
				x.setInsuranceIDNumber(insuranceIDNumber.getText());
				x.setInsuranceGroupNumber(insuranceGroupNumber.getText());
				x.setInsuranceType(insuranceType.getWidgetValue());
				x.setRelationshipToInsured(relationshipToInsured
						.getWidgetValue());

				x.setCopay(copay.getText());
				x.setDeductable(deductable.getText());
				x.setReplaceLikeCoverage(replaceLikeCoverage.getWidgetValue());
				x.setIsAssigning(isAssigning.getWidgetValue());
				x.setSchoolNameForInsured(schoolNameForInsured.getText());
				x.setEmployerOfInsured(employerOfInsured.getText());

				if (!relationshipToInsured.getWidgetValue().equalsIgnoreCase(
						"S")) {
					showHideInsuredField(flexTable.getFlexTable(), insStartRow,
							insStartCol, insEndRow, insEndCol, true);
					x.setInsuredFirstName(insuredFirstName.getValue());
					x.setInsuredLastName(insuredLastName.getValue());
					x.setInsuredMiddleName(insuredMiddleName.getValue());
					x.setInsuredDOB(insuredDOB.getStoredValue());
					x.setInsuredSex(insuredSex.getWidgetValue());
					x.setInsuredSSN(insuredSSN.getValue());
					x.setInsuredAddress1(insuredAddress1.getValue());
					x.setInsuredAddress2(insuredAddress2.getValue());
					x.setInsuredCity(insuredCity.getValue());
					x.setInsuredState(insuredState.getValue());
					x.setInsuredZip(insuredZip.getValue());

				} else {
					showHideInsuredField(flexTable.getFlexTable(), insStartRow,
							insStartCol, insEndRow, insEndCol, false);
				}

				coverages.put(pos, x);

			}
		};
		ValueChangeHandler valueChangeHandler = new ValueChangeHandler() {
			public void onValueChange(
					com.google.gwt.event.logical.shared.ValueChangeEvent arg0) {
				cl.onChange(null);
			}
		};

		if (coverage.getInsuranceCompany() != null)
			insuranceCompany.setValue(coverage.getInsuranceCompany());
		if (coverage.getCoverageInsuranceType() != null)
			coverageInsuranceType.setValue(coverage.getCoverageInsuranceType());
		providerAcceptsAssigment.setWidgetValue(coverage
				.getProviderAcceptsAssigment());
		assigmentOfBenefits.setWidgetValue(coverage.getAssigmentOfBenefits());
		releaseOfInformation.setWidgetValue(coverage.getReleaseOfInformation());
		releaseDateSigned.setValue(coverage.getReleaseDateSigned());
		groupPlanName.setValue(coverage.getGroupPlanName());
		startDate.setValue(coverage.getStartDate());
		insuranceIDNumber.setValue(coverage.getInsuranceIDNumber());
		insuranceGroupNumber.setValue(coverage.getInsuranceGroupNumber());
		insuranceType.setWidgetValue(coverage.getInsuranceType());
		relationshipToInsured.setWidgetValue(coverage
				.getRelationshipToInsured());

		if (coverage.getCoverageId() != null
				&& relationshipToInsured.getWidgetValue() != null
				&& !relationshipToInsured.getWidgetValue()
						.equalsIgnoreCase("S")) {
			showHideInsuredField(flexTable.getFlexTable(), insStartRow,
					insStartCol, insEndRow, insEndCol, true);

			insuredLastName.setValue(coverage.getInsuredLastName());
			insuredMiddleName.setValue(coverage.getInsuredMiddleName());
			insuredFirstName.setValue(coverage.getInsuredFirstName());
			insuredZip.setValue(coverage.getInsuredZip());
			insuredState.setValue(coverage.getInsuredState());
			insuredCity.setValue(coverage.getInsuredCity());
			insuredAddress2.setValue(coverage.getInsuredAddress2());
			insuredAddress1.setValue(coverage.getInsuredAddress1());
			insuredSSN.setValue(coverage.getInsuredSSN());
			insuredSex.setWidgetValue(coverage.getInsuredSex());
			insuredDOB.setValue(coverage.getInsuredDOB());
		} else {
			showHideInsuredField(flexTable.getFlexTable(), insStartRow,
					insStartCol, insEndRow, insEndCol, false);
		}
		// insuredName.addValueChangeHandler(valueChangeHandler);
		copay.setValue(coverage.getCopay());
		deductable.setValue(coverage.getDeductable());
		replaceLikeCoverage.setWidgetValue(coverage.getReplaceLikeCoverage());
		isAssigning.setWidgetValue(coverage.getIsAssigning());
		schoolNameForInsured.setValue(coverage.getSchoolNameForInsured());
		employerOfInsured.setValue(coverage.getEmployerOfInsured());

		// Implement changelisteners
		insuranceCompany.addValueChangeHandler(valueChangeHandler);
		coverageInsuranceType.addValueChangeHandler(valueChangeHandler);
		providerAcceptsAssigment.addValueChangeHandler(valueChangeHandler);
		assigmentOfBenefits.addValueChangeHandler(valueChangeHandler);
		releaseOfInformation.addValueChangeHandler(valueChangeHandler);
		releaseDateSigned.addValueChangeHandler(valueChangeHandler);
		groupPlanName.addValueChangeHandler(valueChangeHandler);
		startDate.addValueChangeHandler(valueChangeHandler);
		insuranceIDNumber.addValueChangeHandler(valueChangeHandler);
		insuranceGroupNumber.addValueChangeHandler(valueChangeHandler);
		insuranceType.addValueChangeHandler(valueChangeHandler);
		relationshipToInsured.addChangeHandler(cl);

		insuredFirstName.addChangeHandler(cl);
		insuredLastName.addChangeHandler(cl);
		insuredMiddleName.addChangeHandler(cl);
		insuredDOB.addValueChangeHandler(valueChangeHandler);
		insuredSex.addValueChangeHandler(valueChangeHandler);
		insuredSSN.addChangeHandler(cl);
		insuredAddress1.addChangeHandler(cl);
		insuredAddress2.addChangeHandler(cl);
		insuredCity.addChangeHandler(cl);
		insuredState.addChangeHandler(cl);
		insuredZip.addChangeHandler(cl);

		copay.addValueChangeHandler(valueChangeHandler);
		deductable.addValueChangeHandler(valueChangeHandler);
		replaceLikeCoverage.addValueChangeHandler(valueChangeHandler);
		isAssigning.addValueChangeHandler(valueChangeHandler);
		schoolNameForInsured.addValueChangeHandler(valueChangeHandler);
		employerOfInsured.addValueChangeHandler(valueChangeHandler);
		// End Implement changelisteners

	}

	public void showHideInsuredField(FlexTable flexTable, int startRow,
			int startCol, int endRow, int endCol, boolean action) {
		int row = startRow, col = startCol;
		while (row <= endRow) {
			flexTable.getWidget(row, col++).setVisible(action);
			if (row == endRow && col > endCol)
				break;
			if (col > 5) {
				row++;
				col = 0;
			}
		}
	}

	public void commitChanges() {
		// Form map
		// HashMap<String, String>[] map;
		Iterator<Integer> iter = coverages.keySet().iterator();
		while (iter.hasNext()) {
			HashMap<String, String> mmp = coverages.get(iter.next()).getMap();
			Iterator<String> innerItr = mmp.keySet().iterator();
			while (innerItr.hasNext()) {
				String key = innerItr.next();
				if (mmp.get(key) == null)
					mmp.remove(key);
			}

			mmp.put("covpatient", patientId.toString());
			String url = "org.freemedsoftware.module.PatientCoverages.Add";
			if (mmp.get("id") != null)
				url = "org.freemedsoftware.module.PatientCoverages.Mod";
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				Util.showInfoMsg("PatientCoverages",
						"Updated patient Coverages.");
				if (onCompletion != null) {
					onCompletion.execute();
				}
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { JsonUtil.jsonify(mmp) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST, URL.encode(Util.getJsonRequest(
								url, params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(
								com.google.gwt.http.client.Request request,
								Throwable ex) {
							GWT.log("Exception", ex);
							Util.showErrorMsg("PatientCoverages",
									"Failed to update patient Coverages.");
						}

						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							if (200 == response.getStatusCode()) {
								Boolean result = (Boolean) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()), "Boolean");
								if (result != null) {
									Util.showInfoMsg("PatientCoverages",
											"Updated patient Coverages.");
									if (onCompletion != null) {
										onCompletion.execute();
									}
								}
							} else {
								Window.alert(response.toString());
							}
						}
					});
				} catch (RequestException e) {
					GWT.log("Exception", e);
					Util.showErrorMsg("PatientCoverages",
							"Failed to update patient Coverages.");
				}
			}
		}
	}

	/**
	 * Set and populate widget with patient information.
	 * 
	 * @param myPatientId
	 */
	public void setPatient(Integer myPatientId) {
		patientId = myPatientId;
		populate();
	}

	private void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: Stubbed stuff
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientCoverages.GetAllCoverages",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						GWT.log("Exception", ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (result != null) {
								for (int iter = 0; iter < result.length; iter++) {
									// Create new Coverage object

									Coverage x = new Coverage();
					
									x.loadData(result[iter]);
									// builder
									addCoverage(new Integer(iter + 1), x);
								}
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		}
	}

	public void deleteCoverage(Integer cid) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { cid.toString() };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.PatientCoverages.del",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {

						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.getMessage());
			}
		} else {
			// TODO normal mode code goes here
		}
	}

	public HashMap<Integer, Coverage> getCoverages() {
		return coverages;
	}

	public static String returnRelationshipToInsured(String id) {
		if (id.equalsIgnoreCase("C")) {
			return "Child";
		}

		else if (id.equalsIgnoreCase("H")) {
			return "Husband";
		}

		else if (id.equalsIgnoreCase("s")) {
			return "Self";
		} else if (id.equalsIgnoreCase("W")) {
			return "Wife";
		}

		else if (id.equalsIgnoreCase("D")) {
			return "Child Not Fin";
		}

		else if (id.equalsIgnoreCase("SC")) {
			return "Step Child";
		} else if (id.equalsIgnoreCase("FC")) {
			return "Foster Child";
		} else if (id.equalsIgnoreCase("WC")) {
			return "Ward of Court";
		} else if (id.equalsIgnoreCase("HD")) {
			return "HC Dependent";
		}

		else if (id.equalsIgnoreCase("SD")) {
			return "Sponsored Dependent";
		}

		else if (id.equalsIgnoreCase("LR")) {
			return "Medicare Legal Rep";
		}

		else
			return "other";

	}

	public Integer getMaxCoveragesCount() {
		return maxCoveragesCount;
	}

	public void setMaxCoveragesCount(Integer maxCoveragesCount) {
		this.maxCoveragesCount = maxCoveragesCount;
	}
}
