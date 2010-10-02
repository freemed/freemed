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

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.gc.gwt.wysiwyg.client.Editor;
import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Node;
import com.google.gwt.dom.client.Style.Unit;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
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
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ScrollPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class EncounterWidget extends Composite {

	EncounterFormType formtype;
	NoteType ntype;
	private TabPanel tabPanel;
	protected VerticalPanel billingInfoPanel;
	protected VerticalPanel soapNotePanel;
	protected VerticalPanel ierPanel;
	protected VerticalPanel vitalGenPanel;
	protected VerticalPanel ccHpiPanel;
	protected VerticalPanel revOfSysPanel;
	protected VerticalPanel pastHisPanel;
	protected VerticalPanel famHisPanel;
	protected VerticalPanel socHisPanel;
	protected VerticalPanel examPanel;
	protected VerticalPanel assessPlanPanel;
	protected VerticalPanel freeFormPanel;
	protected String currTemplate = "";
	protected VerticalPanel basicInfoPanel;
	protected String patientID;
	protected HashMap<String, String> eocMap;
	protected CustomListBox eocList;
	protected CustomRequestCallback callback;
	protected HorizontalPanel actionPanel;
	protected TextArea tbSub;
	protected CustomListBox listObj;
	protected TextArea tbAssess;
	protected TextArea tbPlan;
	protected TextArea tbInterval;
	protected TextArea tbEducation;
	protected TextArea tbRx;
	protected TextBox tbBp1;
	protected TextBox tbBp2;
	protected CustomListBox listTemp;
	protected TextBox tbHeartRate;
	protected TextBox tbRespRate;
	protected TextBox tbWeight;
	protected TextBox tbHeight;
	protected Label tbBMIVal;
	protected TextArea tbGeneral;
	protected TextArea tbCC;
	protected TextArea tbHPI;
	protected TextArea tbConstitutional;
	protected TextArea tbEyes;
	protected TextArea tbENT;
	protected TextArea tbCV;
	protected TextArea tbResp;
	protected TextArea tbGI;
	protected TextArea tbGU;
	protected TextArea tbMS;
	protected TextArea tbSkinBreast;
	protected TextArea tbNeuro;
	protected TextArea tbPsych;
	protected TextArea tbEndo;
	protected TextArea tbHemeLymph;
	protected TextArea tbAlergyImmune;
	protected TextArea tbPH;
	private TextArea tbFH;
	protected TextArea tbSH;
	protected TextArea tbEyesExam;
	protected TextArea tbENTExam;
	protected TextArea tbNeckExam;
	protected TextArea tbRespExam;
	protected TextArea tbCVExam;
	protected TextArea tbChestBreast;
	protected TextArea tbGiAbd;
	protected TextArea tbGUExam;
	protected TextArea tbLymphatics;
	protected TextArea tbMSExam;
	protected TextArea tbSkinExam;
	protected TextArea tbNeuroExam;
	protected TextArea tbPsychExam;
	protected TextArea tbAssessment;
	protected Editor rte;
	protected TextArea tbPlanAssess;
	protected String templateName;
	private HashMap<String, String> templateValuesMap;
	protected CustomButton addBtn;
	protected EncounterFormMode formmode;
	protected SupportModuleWidget templateWidget;
	protected SupportModuleWidget provWidget;
	protected TextBox tbDesc;
	protected CustomDatePicker date;
	protected VerticalPanel vPanel;
	protected CheckBox cbTabView;
	protected String freeFormContents = "";
	protected CheckBox cbEyesExBill;
	protected CheckBox cbEntExBill;
	protected CheckBox cbNeckExBill;
	protected CheckBox cbRespExBill;
	protected CheckBox cbCVExBill;
	protected CheckBox cbBreastExBill;
	protected CheckBox cbGIExBill;
	protected CheckBox cbGUExBill;
	protected CheckBox cbMSExBill;
	protected CheckBox cbSkinExBill;
	protected CheckBox cbNeuroExBill;
	protected CheckBox cbPsychExBill;
	protected CheckBox cbLympExBill;
	protected HashMap<String, List<String>> sectionsFieldMap;
	protected HashMap<String, BillInfoWidget> billingFieldsWidgetsMap;
	protected SupportModuleWidget procCodeWidget;
	protected SupportModuleWidget diag1Widget;
	protected SupportModuleWidget diag2Widget;
	protected SupportModuleWidget diag3Widget;
	protected SupportModuleWidget diag4Widget;
	protected SupportModuleWidget mod1Widget;
	protected SupportModuleWidget mod2Widget;
	protected SupportModuleWidget mod3Widget;
	protected SupportModuleWidget posWidget;
	protected CustomListBox listAuthorizations;
	protected CustomListBox listPrimCov;
	protected CustomListBox listSecCov;
	protected CustomListBox listTertCov;
	protected CustomListBox listWorkCov;
	protected TextBox tbProcUnits;
	protected CustomRadioButtonGroup radClpi;
	protected TextArea tbClpi;
	protected CustomRadioButtonGroup radDiscEdgeSharp;
	protected TextArea tbDiscEdgeSharp;
	protected CustomRadioButtonGroup radVenPul;
	protected TextArea tbVenPul;
	protected CustomRadioButtonGroup radAVNicking;
	protected TextArea tbAVNicking;
	protected CustomRadioButtonGroup radHemorrhages;
	protected TextArea tbHemorrhages;
	protected CustomRadioButtonGroup radExudates;
	protected TextArea tbExudates;
	protected TextBox tbCupDiscRatio;
	protected CustomRadioButtonGroup radExtCanTms;
	protected TextArea tbExtCanTms;
	protected CustomRadioButtonGroup radNMS;
	protected TextArea tbNMS;
	protected CustomRadioButtonGroup radLGT;
	protected TextArea tbLGT;
	protected CustomRadioButtonGroup radOMS;
	protected TextArea tbOMS;
	protected CustomRadioButtonGroup radHTTP;
	protected TextArea tbHTTP;
	protected CustomRadioButtonGroup radThyroid;
	protected TextArea tbThyroid;
	protected CustomRadioButtonGroup radNeck;
	protected CustomRadioButtonGroup radBreast;
	protected TextArea tbBreastExam;
	protected CustomRadioButtonGroup radRespEff;
	protected TextArea tbRespEff;
	protected CustomRadioButtonGroup radLPA;
	protected TextArea tbLPA;
	protected CustomRadioButtonGroup radRegRyth;
	protected TextArea tbRegRyth;
	protected CustomRadioButtonGroup radS1Cons;
	protected TextArea tbS1Cons;
	protected CustomRadioButtonGroup radPhysSplit;
	protected TextArea tbPhysSplit;
	protected CustomRadioButtonGroup radMurmur;
	protected TextArea tbMurmur;
	protected CustomRadioButtonGroup radPalHrt;
	protected TextArea tbPalHrt;
	protected CustomRadioButtonGroup radAbAorta;
	protected TextArea tbAbAorta;
	protected CustomRadioButtonGroup radFemArt;
	protected TextArea tbFemArt;
	protected CustomRadioButtonGroup radPedalPulses;
	protected TextArea tbPedalPulses;
	protected CustomRadioButtonGroup radScars;
	protected TextArea tbScars;
	protected CustomRadioButtonGroup radBruit;
	protected TextArea tbBruit;
	protected CustomRadioButtonGroup radMass;
	protected TextArea tbMass;
	protected CustomRadioButtonGroup radTenderness;
	protected TextArea tbTenderness;
	protected CustomRadioButtonGroup radHepatomegaly;
	protected TextArea tbHepatomegaly;
	protected CustomRadioButtonGroup radSplenomegaly;
	protected TextArea tbSplenomegaly;
	protected CustomRadioButtonGroup radAPRS;
	protected TextArea tbAPRS;
	protected CustomRadioButtonGroup radBowSnd;
	protected CustomRadioButtonGroup radStool;
	protected TextArea tbStool;
	protected TextArea tbHemNeg;
	protected TextArea tbBowSnd;
	protected CustomRadioButtonGroup radGender;
	protected CustomRadioButtonGroup radPenis;
	protected TextArea tbPenis;
	protected CustomRadioButtonGroup radTestes;
	protected TextArea tbTestes;
	protected CustomRadioButtonGroup radProstate;
	protected TextArea tbProstate;
	protected CustomRadioButtonGroup radExtGen;
	protected CustomRadioButtonGroup radCervix;
	protected TextArea tbExtGen;
	protected TextArea tbCervix;
	protected CustomRadioButtonGroup radUteAdn;
	protected TextArea tbUteAdn;
	protected CustomRadioButtonGroup radLympNode;
	protected TextArea tbLympNode;
	protected CustomRadioButtonGroup radSkinSQTissue;
	protected TextArea tbSkinSQTissue;
	protected CustomRadioButtonGroup radGaitStat;
	protected TextArea tbGaitStat;
	protected CustomRadioButtonGroup radDigitsNails;
	protected TextArea tbDigitsNails;
	protected CustomRadioButtonGroup radRomStability;
	protected TextArea tbRomStability;
	protected CustomRadioButtonGroup radJntBnsMusc;
	protected TextArea tbJntBnsMusc;
	protected CustomRadioButtonGroup radMuscStrg;
	protected TextArea tbMuscStrg;
	protected CustomRadioButtonGroup radCranNerves;
	protected TextArea tbCranNerves;
	protected CustomRadioButtonGroup radDTRs;
	protected TextArea tbDTRs;
	protected CustomRadioButtonGroup radMotor;
	protected TextArea tbMotor;
	protected CustomRadioButtonGroup radSensation;
	protected TextArea tbSensation;
	protected CustomRadioButtonGroup radJudIns;
	protected TextArea tbJudIns;
	protected CustomRadioButtonGroup radMoodEffect;
	protected TextArea tbMoodEffect;
	protected CustomRadioButtonGroup radOrTimePlcPers;
	protected TextArea tbOrTimePlcPers;
	protected CustomRadioButtonGroup radMemory;
	protected TextArea tbMemory;
	protected CheckBox cbAlcohol;
	protected TextBox tbAlcohol;
	protected CheckBox cbTobacco;
	protected TextBox tbTobacco;
	protected CheckBox cbCounseledCessation;
	protected CheckBox cbIllDrugs;
	protected TextBox tbIllDrugs;
	protected CheckBox cbLivesWith;
	protected TextBox tbLivesWith;
	protected TextBox tbOccupation;
	protected CheckBox cbHivRiskFactor;
	protected TextBox tbHivRiskFactor;
	protected CheckBox cbTravel;
	protected TextBox tbTravel;
	protected CheckBox cbPets;
	protected TextBox tbPets;
	protected CheckBox cbHobbies;
	protected TextBox tbHobbies;
	protected CustomRadioButtonGroup radHousing;
	protected CheckBox cbGeneral;
	protected TextArea tbGeneralRos;
	protected CheckBox cbEyesRos;
	protected CheckBox cbPoorVision;
	private CheckBox cbEyesPain;
	private CheckBox cbEntRos;
	protected CheckBox cbSoreThroat;
	protected CheckBox cbENTPain;
	protected CheckBox cbCoryza;
	protected CheckBox cbAcuity;
	protected CheckBox cbDysphagia;
	private CheckBox cbCVRos;
	private CheckBox cbCVPain;
	protected CheckBox cbPalpitations;
	protected CheckBox cbHypoHyperTension;
	private CheckBox cbRespRos;
	protected CheckBox cbDyspnea;
	protected CheckBox cbCough;
	protected CheckBox cbTachypnea;
	protected CheckBox cbGIRos;
	private CheckBox cbPainGI;
	protected CheckBox cbNausea;
	protected CheckBox cbVomiting;
	protected CheckBox cbDiarrhea;
	protected CheckBox cbConstipation;
	private CheckBox cbGUROS;
	protected CheckBox cbPainGU;
	protected CheckBox cbBleeding;
	protected CheckBox cbIncontinent;
	protected CheckBox cbNocturia;
	protected CheckBox cbFoulSmell;
	protected CheckBox cbMuscle;
	protected CheckBox cbPainMuscle;
	protected CheckBox cbWeakness;
	protected CheckBox cbRash;
	private CheckBox cbSkinRos;
	private CheckBox cbPainSkin;
	protected CheckBox cbAbscess;
	protected CheckBox cbMass;
	protected CheckBox cbPsychRos;
	protected CheckBox cbFatigue;
	protected CheckBox cbInsomnia;
	protected CheckBox cbMoodProblem;
	protected CheckBox cbCrying;
	protected CheckBox cbDepression;
	private CheckBox cbEndoRos;
	protected CheckBox cbHotFlashes;
	protected CheckBox cbHemLymRos;
	protected CheckBox cbFevers;
	protected CheckBox cbChills;
	protected CheckBox cbSwelling;
	protected CheckBox cbNightSweats;
	private CheckBox cbNeuroRos;
	private CheckBox cbNumbness;
	protected CheckBox cbTingling;
	private CheckBox cbWeaknessNeuro;
	protected CheckBox cbHeadache;
	protected CheckBox cbLossOfCons;
	private CheckBox cbImmAllrgRos;
	protected TextArea tbImmAllrg;
	protected CustomRadioButtonGroup radType;
	protected CheckBox cbCLPI;
	protected CheckBox cbDiscEdgeSharp;
	protected CheckBox cbVenPul;
	protected CheckBox cbAVNicking;
	protected CheckBox cbHemorrhages;
	protected CheckBox cbExudates;
	protected CheckBox cbCupDiscRatio;
	protected CheckBox cbExtCanTms;
	protected CheckBox cbNMS;
	protected CheckBox cbLGT;
	protected CheckBox cbOMS;
	protected CheckBox cbHTTP;
	protected CheckBox cbThyroid;
	protected CheckBox cbNeck;
	protected CheckBox cbBreast;
	protected CheckBox cbRespEff;
	protected CheckBox cbLPA;
	protected CheckBox cbRegRyth;
	protected CheckBox cbS1Cons;
	protected CheckBox cbS2PhysSplit;
	protected CheckBox cbMurmur;
	protected CheckBox cbPalHrt;
	protected CheckBox cbAbAorta;
	protected CheckBox cbFemArt;
	protected CheckBox cbPedalPulses;
	protected CheckBox cbScars;
	protected CheckBox cbBruit;
	protected CheckBox cbMassExam;
	protected CheckBox cbTenderness;
	protected CheckBox cbHepatomegaly;
	protected CheckBox cbSplenomegaly;
	protected CheckBox cbAPRS;
	protected CheckBox cbBowSnd;
	protected CheckBox cbStool;
	protected CheckBox cbLympNode;
	protected CheckBox cbSkinSQTissue;
	protected CheckBox cbGaitStat;
	protected CheckBox cbDigitsNails;
	protected CheckBox cbRomStability;
	protected CheckBox cbJntBnsMusc;
	protected CheckBox cbMuscStrg;
	protected CheckBox cbCranNerves;
	protected CheckBox cbDTRs;
	protected CheckBox cbMotor;
	protected CheckBox cbSensation;
	protected CheckBox cbJudIns;
	protected CheckBox cbMoodEffect;
	protected CheckBox cbOrTimePlcPers;
	protected CheckBox cbMemory;
	protected CheckBox cbExtGen;
	protected CheckBox cbCervix;
	protected CheckBox cbUteAdn;
	protected CheckBox cbPenis;
	private CheckBox cbTestes;
	protected CheckBox cbProstate;
	protected CustomTable templateTable;
	protected Popup templatesPopup;
	protected TextArea tbEyeFreeForm;
	protected TextArea tbEntFreeForm;
	protected TextArea tbNeckFreeForm;
	protected TextArea tbBreastFreeForm;
	protected TextArea tbRespFreeForm;
	protected TextArea tbCVFreeForm;
	protected TextArea tbGIFreeForm;
	protected TextArea tbGUFreeForm;
	protected TextArea tbLympFreeForm;
	protected TextArea tbSkinFreeForm;
	protected TextArea tbMSFreeForm;
	protected TextArea tbNeuroFreeForm;
	protected TextArea tbEyesRos;
	protected TextArea tbENTRos;
	protected TextArea tbCVRos;
	protected TextArea tbGIRos;
	protected TextArea tbGURos;
	protected TextArea tbRespRos;
	protected TextArea tbMuscleRos;
	protected TextArea tbSkinRos;
	protected TextArea tbPsychRos;
	protected TextArea tbEndoRos;
	protected TextArea tbHemLymRos;
	protected TextArea tbNeuroRos;
	protected TextArea tbPsychFreeForm;
	protected String editortxt;
	protected HorizontalPanel editorContainer;
	protected CheckBox cbHead;
	protected TextArea tbHeadRos;
	protected TextArea tbHeadFreeForm;

	public enum EncounterFormType {
		TEMPLATE_VALUES, ENCOUNTER_NOTE_VALUES
	};

	public enum EncounterFormMode {
		ADD, EDIT
	}

	public enum NoteType {
		EncounterNote, ProgressNote
	}

	public enum EncounterCommandType {
		CREATE_TEMPLATE, EDIT_TEMPLATE, CLOSE, UPDATE, PREVIOUS, RESET
	}

	public EncounterWidget(EncounterFormType fType, EncounterFormMode fMod,
			NoteType nt, HashMap<String, List<String>> secFldMap, String tname,
			HashMap<String, String> valuesMap, CustomRequestCallback c) {
		JsonUtil.debug("EncounterWidget constructor");
		ntype = nt;
		if (valuesMap == null)
			templateValuesMap = new HashMap<String, String>();
		else
			templateValuesMap = valuesMap;
		formmode = fMod;
		formtype = fType;
		sectionsFieldMap = secFldMap;
		callback = c;
		templateName = tname;
		initialize();
	}

	public EncounterWidget(EncounterFormType fType, EncounterFormMode fMod,
			HashMap<String, List<String>> secFldMap, String tid,
			HashMap<String, String> valuesMap, String ptid,
			CustomRequestCallback c) {
		JsonUtil.debug("EncounterWidget constructor");
		if (valuesMap == null)
			templateValuesMap = new HashMap<String, String>();
		else
			templateValuesMap = valuesMap;
		formmode = fMod;
		formtype = fType;
		sectionsFieldMap = new HashMap<String, List<String>>();
		sectionsFieldMap = secFldMap;
		callback = c;
		currTemplate = tid;
		patientID = ptid;
		initialize();
		
	}

	private void initialize() {
		JsonUtil.debug("EncounterWidget.initialize()");
		vPanel = new VerticalPanel();
		initWidget(vPanel);
		vPanel.setWidth("100%");
		vPanel.setSpacing(5);
		cbTabView = new CheckBox("Tab View");
		cbTabView.setValue(true);
		vPanel.add(cbTabView);
		cbTabView.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				switchTabView();
			}

		});
		tabPanel = new TabPanel();
		actionPanel = new HorizontalPanel();
		actionPanel.setSpacing(5);
		CustomButton prevBtn = new CustomButton("Previous",
				AppConstants.ICON_PREV);
		prevBtn = new CustomButton("Previous", AppConstants.ICON_PREV);
		prevBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				callback.jsonifiedData(EncounterCommandType.PREVIOUS);
			}
		});

		if (formtype == EncounterFormType.TEMPLATE_VALUES) {
			actionPanel.add(prevBtn);
		}
		addBtn = null;
		if (formmode == EncounterFormMode.ADD) {
			addBtn = new CustomButton("Add", AppConstants.ICON_ADD);
			if (!CurrentState.isActionAllowed("EncounterNotes",
					AppConstants.WRITE)) {
				addBtn.setVisible(false);

			}
		} else {
			addBtn = new CustomButton("Modify", AppConstants.ICON_CHANGE);
			if (!CurrentState.isActionAllowed("EncounterNotes",
					AppConstants.MODIFY)) {
				addBtn.setVisible(false);
			}
		}
		addBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					saveEncounterNote();
				else {
					saveEncounterTemplate();
				}
			}
		});
		CustomButton cancelButton = null;
		if (formtype == EncounterFormType.TEMPLATE_VALUES) {
			cancelButton = new CustomButton("Cancel", AppConstants.ICON_CANCEL);
			cancelButton.addClickHandler(new ClickHandler() {

				@Override
				public void onClick(ClickEvent arg0) {
					callback.jsonifiedData(EncounterCommandType.CLOSE);
				}

			});
		} else {
			cancelButton = new CustomButton("Reset", AppConstants.ICON_REFRESH);
			cancelButton.addClickHandler(new ClickHandler() {

				@Override
				public void onClick(ClickEvent arg0) {
					callback.jsonifiedData(EncounterCommandType.RESET);
				}

			});
		}

		actionPanel.add(addBtn);
		actionPanel.add(cancelButton);
		tabPanel = new TabPanel();
		vPanel.add(tabPanel);
		vPanel.add(actionPanel);
		billingFieldsWidgetsMap = new HashMap<String, BillInfoWidget>();
		createTabs();
	}

	private void createTabs() {
		if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
			basicInfoPanel = new VerticalPanel();
			createBasicInfoPanel();
		}
		loadOtherTabs();

	}

	private void loadOtherTabs() {
		if (currTemplate.equals("0")) {
			currTemplate = "";
		}
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections"))
			loopCountMax = sectionsFieldMap.get("Sections").size();
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i)
					.equals("Billing Information"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				billingInfoPanel = new VerticalPanel();
				createBillingInfoTab();
				// tabPanel.add(soapNotePanel, "SOAP Note");
			}
			if ((secList != null && secList.get(i).equals("SOAP Note"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				soapNotePanel = new VerticalPanel();
				createSoapNoteTab();
				// tabPanel.add(soapNotePanel, "SOAP Note");
			}
			if ((secList != null && secList.get(i).equals("IER"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				ierPanel = new VerticalPanel();
				createIERTab();
				// tabPanel.add(ierPanel, "IER");
			}
			if ((secList != null && secList.get(i).equals("Vitals/Generals"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				vitalGenPanel = new VerticalPanel();
				// tabPanel.add(vitalGenPanel, "Vitals/Generals");
				createVitalsTab();
			}
			if ((secList != null && secList.get(i).equals("CC & HPI"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				ccHpiPanel = new VerticalPanel();
				// tabPanel.add(ccHpiPanel, "CC & HPI");
				createCCPHTab();
			}
			if ((secList != null && secList.get(i).equals("Review Of Systems"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				revOfSysPanel = new VerticalPanel();
				// tabPanel.add(revOfSysPanel, "Review Of Systems");
				createRevSysTab();
			}
			if ((secList != null && secList.get(i).equals(
					"Past Medical History"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				pastHisPanel = new VerticalPanel();
				// tabPanel.add(pastHisPanel, "Past History");
				createPastHistoryTab();
			}
			if ((secList != null && secList.get(i).equals("Family History"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				famHisPanel = new VerticalPanel();
				// tabPanel.add(famHisPanel, "Family History");
				createFamiliyHistoryTab();
			}
			if ((secList != null && secList.get(i).equals("Social History"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				socHisPanel = new VerticalPanel();
				// tabPanel.add(socHisPanel, "Social History");
				createSocialHistoryTab();
			}
			if ((secList != null && secList.get(i).equals("Exam"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				examPanel = new VerticalPanel();
				// tabPanel.add(examPanel, "Exam");
				createExamTab();
			}
			if ((secList != null && secList.get(i).equals("Assessment/Plan"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				assessPlanPanel = new VerticalPanel();
				// tabPanel.add(assessPlanPanel, "Assessment/Plan");
				createAssessmentPlanTab();
			}
			if ((secList != null && secList.get(i).equals("Free Form Entry"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				freeFormPanel = new VerticalPanel();
				freeFormPanel.setSize("100%", "200px");
				// tabPanel.add(freeFormPanel, "Free Form Entry");
				createFreeFormEntryTab();
			}
		}
		switchTabView();
	}

	private void switchTabView() {
		editortxt = "";
		List<String> secList = sectionsFieldMap.get("Sections");
		if (((secList != null && secList.contains(
			"Free Form Entry")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
			.equals("")))) {
			try{
				
			}
			catch(Exception e){
				
			}
		}
		
		vPanel.clear();
		vPanel.add(cbTabView);
		tabPanel.clear();
		if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES
				&& cbTabView.getValue()) {
			tabPanel.add(basicInfoPanel, "Basic Info");
		} else if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES
				&& !cbTabView.getValue()) {
			Label lbBasicInfo = new Label("Basic Info");
			lbBasicInfo.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
			vPanel.add(lbBasicInfo);
			vPanel.add(basicInfoPanel);
		}
		/*
		 * if ((sectionsSet.contains("Billing Information") || (formtype ==
		 * EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate .equals(""))) &&
		 * cbTabView.getValue()) { tabPanel.add(billingInfoPanel, "Billing
		 * Information"); } else if ((sectionsSet.contains("Billing
		 * Information") || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES &&
		 * currTemplate .equals(""))) && !cbTabView.getValue()) { Label
		 * lbBillingInfo = new Label("Billing Information");
		 * lbBillingInfo.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
		 * vPanel.add(lbBillingInfo); vPanel.add(billingInfoPanel); }
		 */
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections"))
			loopCountMax = sectionsFieldMap.get("Sections").size();
		else
			loopCountMax = 1;
		
		for (int i = 0; i < loopCountMax; i++) {
			if (((secList != null && secList.get(i).equals(
					"Billing Information")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(billingInfoPanel, "Billing Information");
			} else if (((secList != null && secList.get(i).equals("Billing Information")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbBill = new Label("Billing Information");
				lbBill.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbBill);
				vPanel.add(billingInfoPanel);
			}
			if (((secList != null && secList.get(i).equals("SOAP Note")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(soapNotePanel, "SOAP Note");
			} else if (((secList != null && secList.get(i).equals("SOAP Note")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbSoapNote = new Label("SOAP Note");
				lbSoapNote.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbSoapNote);
				vPanel.add(soapNotePanel);
			}
			if (((secList != null && secList.get(i).equals("IER")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(ierPanel, "IER");
			} else if (((secList != null && secList.get(i).equals("IER")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbIER = new Label("IER");
				lbIER.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbIER);
				vPanel.add(ierPanel);
			}
			if (((secList != null && secList.get(i).equals("Vitals/Generals")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(vitalGenPanel, "Vitals/Generals");
			} else if (((secList != null && secList.get(i).equals(
					"Vitals/Generals")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbVitGen = new Label("Vitals/Generals");
				lbVitGen.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbVitGen);
				vPanel.add(vitalGenPanel);
			}
			if (((secList != null && secList.get(i).equals("CC & HPI")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(ccHpiPanel, "CC & HPI");
			} else if (((secList != null && secList.get(i).equals("CC & HPI")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbCCHPI = new Label("CC & HPI");
				lbCCHPI.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbCCHPI);
				vPanel.add(ccHpiPanel);
			}
			if (((secList != null && secList.get(i).equals(
					"Past Medical History")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(pastHisPanel, "Past Medical History");
			} else if (((secList != null && secList.get(i).equals(
					"Past Medical History")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbPH = new Label("Past Medical History");
				lbPH.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbPH);
				vPanel.add(pastHisPanel);
			}
			if (((secList != null && secList.get(i).equals("Review Of Systems")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(revOfSysPanel, "Review Of Systems");
			} else if (((secList != null && secList.get(i).equals(
					"Review Of Systems")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbRos = new Label("Review Of Systems");
				lbRos.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbRos);
				vPanel.add(revOfSysPanel);
			}
			if (((secList != null && secList.get(i).equals("Social History")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(socHisPanel, "Social History");
			} else if (((secList != null && secList.get(i).equals(
					"Social History")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbSH = new Label("Social History");
				lbSH.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbSH);
				vPanel.add(socHisPanel);
			}
			if (((secList != null && secList.get(i).equals("Family History")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(famHisPanel, "Family History");
			} else if (((secList != null && secList.get(i).equals(
					"Family History")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbFH = new Label("Family History");
				lbFH.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbFH);
				vPanel.add(famHisPanel);
			}

			if (((secList != null && secList.get(i).equals("Exam")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(examPanel, "Exam");
			} else if (((secList != null && secList.get(i).equals("Exam")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbExam = new Label("Exam");
				lbExam.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbExam);
				vPanel.add(examPanel);
			}
			if (((secList != null && secList.get(i).equals("Assessment/Plan")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(assessPlanPanel, "Assessment/Plan");
			} else if (((secList != null && secList.get(i).equals(
					"Assessment/Plan")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				Label lbAssessPlan = new Label("Assessment/Plan");
				lbAssessPlan
						.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				vPanel.add(lbAssessPlan);
				vPanel.add(assessPlanPanel);
			}
			if (((secList != null && secList.get(i).equals("Free Form Entry")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& cbTabView.getValue()) {
				tabPanel.add(freeFormPanel, "Free Form Entry");
				rte=new Editor();
				editorContainer.clear();
				editorContainer.add(rte);
				if(!editortxt.equals("")){
					rte.setHTML(editortxt);
				}
				cbTabView.setFocus(true);
			} else if (((secList != null && secList.get(i).equals(
					"Free Form Entry")) || (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
					.equals("")))
					&& !cbTabView.getValue()) {
				
				Label lbFreeFormEntry = new Label("Free Form Entry");
				lbFreeFormEntry
						.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				
				vPanel.add(lbFreeFormEntry);
				vPanel.add(freeFormPanel);
				rte=new Editor();
				editorContainer.clear();
				editorContainer.add(rte);
				if(!editortxt.equals("")){
					rte.setHTML(editortxt);
				}
				cbTabView.setFocus(true);
			}
		}
		if (cbTabView.getValue()) {
			tabPanel.selectTab(0);
			vPanel.add(tabPanel);
		}

		vPanel.add(actionPanel);
	}

	public void createBasicInfoPanel() {
		int row = 0;
		int col = 0;
		templateWidget = new SupportModuleWidget("EncounterNotesTemplate");
		eocList = new CustomListBox();
		eocMap = new HashMap<String, String>();
		FlexTable basicInfoTable = new FlexTable();
		// basicInfoTable.setWidth("20%");
		col = 0;
		Label lbType = new Label("Type");
		radType = new CustomRadioButtonGroup("type");
		radType.addItem("Encounter Note", "Encounter Note", new Command() {

			@Override
			public void execute() {
				HashMap<String, String> map = new HashMap<String, String>();
				map.put("pnotesttype", "Encounter Note");
				templateWidget.setAdditionalParameters(map);

			}

		});
		radType.addItem("Progress Note", "Progress Note", new Command() {

			@Override
			public void execute() {
				HashMap<String, String> map = new HashMap<String, String>();
				map.put("pnotesttype", "Progress Note");
				templateWidget.setAdditionalParameters(map);
			}

		});
		basicInfoTable.setWidget(row, col++, lbType);
		basicInfoTable.setWidget(row++, col++, radType);
		col = 0;

		Label lbEnTemplate = new Label("Notes Template");
		templateWidget.addValueChangeHandler(new ValueChangeHandler<Integer>() {

			@Override
			public void onValueChange(ValueChangeEvent<Integer> arg0) {
				applyTemplate(templateWidget.getStoredValue());
			}

		});
		
		HTML addTemplateBtn = new HTML(
				"<a href=\"javascript:undefined;\">Add</a>");

		addTemplateBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				callback.jsonifiedData(EncounterCommandType.CREATE_TEMPLATE);
				// EncounterTemplateWidget enc=new EncounterTemplateWidget();
				// mainTabPanel.add(enc, "Encounter Template");
				// mainTabPanel.selectTab(mainTabPanel.getWidgetCount()-1);
			}

		});
		HTML editTemplateBtn = new HTML(
				"<a href=\"javascript:undefined;\">Edit</a>");
		editTemplateBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				if(templateWidget.getStoredValue()==null || templateWidget.getStoredValue().equals("0"))
					Window.alert("No template selected");
				else
					callback.jsonifiedData(EncounterCommandType.EDIT_TEMPLATE);
				// EncounterTemplateWidget enc=new EncounterTemplateWidget();
				// mainTabPanel.add(enc, "Encounter Template");
				// mainTabPanel.selectTab(mainTabPanel.getWidgetCount()-1);
			}

		});
		HTML listTemplateBtn = new HTML(
				"<a href=\"javascript:undefined;\">List</a>");
		templateTable = new CustomTable();
		templateTable.setIndexName("id");
		// patientCustomTable.setSize("100%", "100%");
		templateTable.setWidth("100%");
		templateTable.addColumn("Template Name", "tempname");
		templateTable.addColumn("Template Type", "notetype");
		templateTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				templateWidget.setValue(new Integer(data.get("id")));
				templatesPopup.hide();
				applyTemplate(data.get("id"));
			}
		});
		listTemplateBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				templatesPopup = new Popup();
				templatesPopup.setPixelSize(500, 20);
				VerticalPanel vp = new VerticalPanel();
				ScrollPanel sp = new ScrollPanel();
				vp.add(sp);
				templateTable.clearData();
				loadTemplates();
				sp.add(templateTable);
				//sp.setHeight("300px");
				PopupView viewInfo = new PopupView(vp);
				templatesPopup.setNewWidget(viewInfo);
				templatesPopup.initialize();
			}

		});
		if (!CurrentState.isActionAllowed("EncounterNotesTemplate",
				AppConstants.WRITE)) {
			addTemplateBtn.setVisible(false);
		}
		if (!CurrentState.isActionAllowed("EncounterNotesTemplate",
				AppConstants.MODIFY)) {
			editTemplateBtn.setVisible(false);
		}
		basicInfoTable.setWidget(row, col++, lbEnTemplate);
		basicInfoTable.setWidget(row, col++, templateWidget);
		if (CurrentState.isActionAllowed("EncounterNotesTemplate",
				AppConstants.WRITE))
			basicInfoTable.setWidget(row, col++, addTemplateBtn);
		if (CurrentState.isActionAllowed("EncounterNotesTemplate",
				AppConstants.MODIFY))
			basicInfoTable.setWidget(row, col++, editTemplateBtn);
		basicInfoTable.setWidget(row, col++, listTemplateBtn);
		row++;

		col = 0;
		Label lbProvider = new Label("Provider");
		provWidget = new SupportModuleWidget("ProviderModule");
		basicInfoTable.setWidget(row, col++, lbProvider);
		basicInfoTable.setWidget(row++, col++, provWidget);

		col = 0;
		Label lbDescription = new Label("Description");
		tbDesc = new TextBox();
		basicInfoTable.setWidget(row, col++, lbDescription);
		basicInfoTable.setWidget(row++, col++, tbDesc);

		col = 0;
		Label lbEoc = new Label("Related Episode(s)");
		VerticalPanel eocVPanel = new VerticalPanel();
		loadEOC();
		final FlexTable eocFlexTable = new FlexTable();
		eocVPanel.add(eocFlexTable);
		HTML addAnother = new HTML(
				"<a href=\"javascript:undefined;\" style='color:blue'>Add Episode of Care</a>");

		addAnother.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				CustomListBox eoc = new CustomListBox();
				eoc.addItem("NONE SELECTED");
				if (eocMap != null && eocMap.size() > 0) {
					Set<String> keys = eocMap.keySet();
					Iterator<String> iter = keys.iterator();

					while (iter.hasNext()) {

						final String key = (String) iter.next();
						final String val = (String) eocMap.get(key);
						JsonUtil.debug(val);
						eoc.addItem(val, key);
					}
				}
				final CustomButton remove = new CustomButton("X");
				remove.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent event) {
						Node parentTableBody = null;
						Node parentTR = null;

						Node tempNode = remove.getElement();
						while (!tempNode.getNodeName().equals("TBODY")) {
							tempNode = tempNode.getParentNode();
						}
						parentTableBody = tempNode;

						tempNode = remove.getElement();
						while (!tempNode.getNodeName().equals("TR")) {
							tempNode = tempNode.getParentNode();
						}
						parentTR = tempNode;

						parentTableBody.removeChild(parentTR);
					}
				});
				int rc = eocFlexTable.getRowCount();
				eocFlexTable.setWidget(rc, 0, eoc);
				eocFlexTable.setWidget(rc, 1, remove);
			}

		});
		eocVPanel.add(addAnother);
		basicInfoTable.setWidget(row, col++, lbEoc);
		basicInfoTable.setWidget(row++, col++, eocVPanel);
		col = 0;
		Label lbDate = new Label("Date");
		date = new CustomDatePicker();
		basicInfoTable.setWidget(row, col++, lbDate);
		basicInfoTable.setWidget(row++, col++, date);
		basicInfoPanel.add(basicInfoTable);
		if (formmode == EncounterFormMode.EDIT) {
			if (templateValuesMap.containsKey("pnotestype")) {
				radType.setWidgetValue(templateValuesMap.get("pnotestype"));
			}
			if (templateValuesMap.containsKey("pnotesdt")) {
				date.setValue(templateValuesMap.get("pnotesdt"));
			}
			if (templateValuesMap.containsKey("pnotesdescrip")) {
				tbDesc.setText(templateValuesMap.get("pnotesdescrip"));
			}
			if (templateValuesMap.containsKey("pnotesdoc")) {
				provWidget.setValue(new Integer(templateValuesMap
						.get("pnotesdoc")));
			}
			if (templateValuesMap.containsKey("pnotestemplate")) {
				templateWidget.setValue(new Integer(templateValuesMap
						.get("pnotestemplate")));
			}
		}

	}

	private void createBillingInfoTab() {
		FlexTable billInfoTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Billing Information"))
			loopCountMax = sectionsFieldMap.get("Sections#Billing Information")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap
						.containsKey("Sections#Billing Information"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap
				.get("Sections#Billing Information");
		String widgetWidth = "325px";
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("Procedure Code"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbProcCode = new Label("Procedure Code");
				procCodeWidget = new SupportModuleWidget("CptCodes");
				procCodeWidget.setWidth(widgetWidth);
				// procCodeWidget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestproccode")) {
					procCodeWidget.setValue(new Integer(templateValuesMap
							.get("pnotestproccode")));
				}
				billInfoTable.setWidget(row, 0, lbProcCode);
				billInfoTable.setWidget(row++, 1, procCodeWidget);
			}
			if ((secList != null && secList.get(i).equals("Diagnosis 1"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbDiag1 = new Label("Diagnosis 1");
				diag1Widget = new SupportModuleWidget("IcdCodes");
				diag1Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestdiag1")) {
					diag1Widget.setValue(new Integer(templateValuesMap
							.get("pnotestdiag1")));
				}
				billInfoTable.setWidget(row, 0, lbDiag1);
				billInfoTable.setWidget(row++, 1, diag1Widget);
			}
			if ((secList != null && secList.get(i).equals("Diagnosis 2"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbDiag2 = new Label("Diagnosis 2");
				diag2Widget = new SupportModuleWidget("IcdCodes");
				diag2Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestdiag2")) {
					diag2Widget.setValue(new Integer(templateValuesMap
							.get("pnotestdiag2")));
				}
				billInfoTable.setWidget(row, 0, lbDiag2);
				billInfoTable.setWidget(row++, 1, diag2Widget);
			}
			if ((secList != null && secList.get(i).equals("Diagnosis 3"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbDiag3 = new Label("Diagnosis 3");
				diag3Widget = new SupportModuleWidget("IcdCodes");
				diag3Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestdiag3")) {
					diag3Widget.setValue(new Integer(templateValuesMap
							.get("pnotestdiag3")));
				}
				billInfoTable.setWidget(row, 0, lbDiag3);
				billInfoTable.setWidget(row++, 1, diag3Widget);
			}
			if ((secList != null && secList.get(i).equals("Diagnosis 4"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbDiag4 = new Label("Diagnosis 4");
				diag4Widget = new SupportModuleWidget("IcdCodes");
				diag4Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestdiag4")) {
					diag4Widget.setValue(new Integer(templateValuesMap
							.get("pnotestdiag4")));
				}
				billInfoTable.setWidget(row, 0, lbDiag4);
				billInfoTable.setWidget(row++, 1, diag4Widget);
			}
			if ((secList != null && secList.get(i).equals("Modifier 1"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbMod1 = new Label("Modifier 1");
				mod1Widget = new SupportModuleWidget("CptModifiers");
				mod1Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestmod1")) {
					mod1Widget.setValue(new Integer(templateValuesMap
							.get("pnotestmod1")));
				}
				billInfoTable.setWidget(row, 0, lbMod1);
				billInfoTable.setWidget(row++, 1, mod1Widget);
			}
			if ((secList != null && secList.get(i).equals("Modifier 2"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbMod2 = new Label("Modifier 2");
				mod2Widget = new SupportModuleWidget("CptModifiers");
				mod2Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestmod2")) {
					mod2Widget.setValue(new Integer(templateValuesMap
							.get("pnotestmod2")));
				}
				billInfoTable.setWidget(row, 0, lbMod2);
				billInfoTable.setWidget(row++, 1, mod2Widget);
			}
			if ((secList != null && secList.get(i).equals("Modifier 3"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbMod3 = new Label("Modifier 3");
				mod3Widget = new SupportModuleWidget("CptModifiers");
				mod3Widget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestmod3")) {
					mod3Widget.setValue(new Integer(templateValuesMap
							.get("pnotestmod3")));
				}
				billInfoTable.setWidget(row, 0, lbMod3);
				billInfoTable.setWidget(row++, 1, mod3Widget);
			}
			if ((secList != null && secList.get(i).equals("Place Of Service"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbPOS = new Label("Place Of Service");
				posWidget = new SupportModuleWidget("FacilityModule");
				posWidget.setWidth(widgetWidth);
				if (templateValuesMap.containsKey("pnotestpos")) {
					posWidget.setValue(new Integer(templateValuesMap
							.get("pnotestpos")));
				}
				billInfoTable.setWidget(row, 0, lbPOS);
				billInfoTable.setWidget(row++, 1, posWidget);
			}
			if (((secList != null && secList.get(i).equals("Authorization")) && formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbAuth = new Label("Authorization");
				listAuthorizations = new CustomListBox();
				listAuthorizations.addItem("NONE SELECTED", "0");
				listAuthorizations.setWidth(widgetWidth);
				billInfoTable.setWidget(row, 0, lbAuth);
				billInfoTable.setWidget(row++, 1, listAuthorizations);
			}
			if (((secList != null && secList.get(i).equals("Primary Coverage")) && formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbPrimCov = new Label("Primary Coverage");
				listPrimCov = new CustomListBox();
				listPrimCov.setWidth(widgetWidth);
				listPrimCov.addItem("NONE SELECTED", "0");
				loadCoverage(1, listPrimCov);
				billInfoTable.setWidget(row, 0, lbPrimCov);
				billInfoTable.setWidget(row++, 1, listPrimCov);
			}
			if (((secList != null && secList.get(i)
					.equals("Secondary Coverage")) && formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbSecCov = new Label("Secondary Coverage");
				listSecCov = new CustomListBox();
				listSecCov.setWidth(widgetWidth);
				listSecCov.addItem("NONE SELECTED", "0");
				loadCoverage(2, listSecCov);
				billInfoTable.setWidget(row, 0, lbSecCov);
				billInfoTable.setWidget(row++, 1, listSecCov);
			}
			if (((secList != null && secList.get(i).equals("Tertiary Coverage")) && formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbTertCov = new Label("Tertiary Coverage");
				listTertCov = new CustomListBox();
				listTertCov.setWidth(widgetWidth);
				listTertCov.addItem("NONE SELECTED", "0");
				loadCoverage(3, listTertCov);
				billInfoTable.setWidget(row, 0, lbTertCov);
				billInfoTable.setWidget(row++, 1, listTertCov);
			}
			if (((secList != null && secList.get(i)
					.equals("Work Comp Coverage")) && formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbWorkCov = new Label("Work Comp");
				listWorkCov = new CustomListBox();
				listWorkCov.setWidth(widgetWidth);
				listWorkCov.addItem("NONE SELECTED", "0");
				loadCoverage(4, listWorkCov);
				billInfoTable.setWidget(row, 0, lbWorkCov);
				billInfoTable.setWidget(row++, 1, listWorkCov);
			}
			if ((secList != null && secList.get(i).equals("Procedural Units"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbProcUnits = new Label("Procedural Units");
				tbProcUnits = new TextBox();
				if (templateValuesMap.containsKey("pnotestprocunits")) {
					tbProcUnits.setText(templateValuesMap
							.get("pnotestprocunits"));
				}
				billInfoTable.setWidget(row, 0, lbProcUnits);
				billInfoTable.setWidget(row++, 1, tbProcUnits);
			}
		}
		billInfoTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		billingInfoPanel.add(billInfoTable);
	}

	private void createSoapNoteTab() {
		FlexTable soapNoteTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#SOAP Note"))
			loopCountMax = sectionsFieldMap.get("Sections#SOAP Note").size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#SOAP Note"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#SOAP Note");
		for (int i = 0; i < loopCountMax; i++) {

			if ((secList != null && secList.get(i).equals("Subjective"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbSub = new Label("Subjective");
				tbSub = new TextArea();
				tbSub.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_S")) {
					tbSub.setText(templateValuesMap.get("pnotes_S"));
				} else if (templateValuesMap.containsKey("pnotest_S")) {
					tbSub.setText(templateValuesMap.get("pnotest_S"));
				}

				soapNoteTable.setWidget(row, 0, lbSub);
				soapNoteTable.setWidget(row++, 1, tbSub);
				soapNoteTable.getFlexCellFormatter().setWidth(0, 0, "155px");
				if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					final CustomListBox listInsInfo = new CustomListBox();
					listInsInfo.addItem("Select information to insert");
					listInsInfo.addItem("Treatments");
					listInsInfo.addItem("Previous Operations");
					listInsInfo.addItem("Chronic Problems");
					listInsInfo.addItem("Current Problems");
					listInsInfo.addItem("Medications");
					listInsInfo.addItem("Shots");
					listInsInfo.addItem("Vitals");
					listInsInfo.addItem("Allergies");
					listInsInfo.addItem("Lab Values");
					listInsInfo.addItem("Prescription");
					listInsInfo.addChangeHandler(new ChangeHandler() {
						@Override
						public void onChange(ChangeEvent arg0) {
							if (listInsInfo.getSelectedIndex() != 0) {
								String value = listInsInfo.getWidgetText();
								tbSub.setText(tbSub.getText() + "\n\n" + value);
								insertModuleText(listInsInfo.getWidgetText(),
										tbSub);
							}
						}
					});
					soapNoteTable.setWidget(row++, 1, listInsInfo);
				}

			}
			if (((secList != null && secList.get(i).equals("Objective")) && formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES)
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbObj = new Label("Objective");
				listObj = new CustomListBox();
				listObj.addItem("NONE SELECTED");
				soapNoteTable.setWidget(row, 0, lbObj);
				soapNoteTable.setWidget(row++, 1, listObj);
			}
			if ((secList != null && secList.get(i).equals("Assessment"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbAssess = new Label("Assessment");
				tbAssess = new TextArea();
				tbAssess.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_A")) {
					tbAssess.setText(templateValuesMap.get("pnotes_A"));
				} else if (templateValuesMap.containsKey("pnotest_A")) {
					tbAssess.setText(templateValuesMap.get("pnotest_A"));
				}
				soapNoteTable.setWidget(row, 0, lbAssess);
				soapNoteTable.setWidget(row++, 1, tbAssess);
			}
			if ((secList != null && secList.get(i).equals("Plan"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbPlan = new Label("Plan");
				tbPlan = new TextArea();
				tbPlan.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_P")) {
					tbPlan.setText(templateValuesMap.get("pnotes_P"));
				} else if (templateValuesMap.containsKey("pnotest_P")) {
					tbPlan.setText(templateValuesMap.get("pnotest_P"));
				}
				soapNoteTable.setWidget(row, 0, lbPlan);
				soapNoteTable.setWidget(row++, 1, tbPlan);
			}
		}
		soapNotePanel.add(soapNoteTable);
	}

	private void createIERTab() {
		FlexTable ierTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#IER"))
			loopCountMax = sectionsFieldMap.get("Sections#IER").size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#IER"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#IER");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("Interval"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbInt = new Label("Interval");
				tbInterval = new TextArea();
				tbInterval.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_I")) {
					tbInterval.setText(templateValuesMap.get("pnotes_I"));
				} else if (templateValuesMap.containsKey("pnotest_I")) {
					tbInterval.setText(templateValuesMap.get("pnotest_I"));
				}
				ierTable.setWidget(row, 0, lbInt);
				ierTable.setWidget(row++, 1, tbInterval);
			}
			if ((secList != null && secList.get(i).equals("Education"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbEducation = new Label("Education");
				tbEducation = new TextArea();
				tbEducation.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_E")) {
					tbEducation.setText(templateValuesMap.get("pnotes_E"));
				} else if (templateValuesMap.containsKey("pnotest_E")) {
					tbEducation.setText(templateValuesMap.get("pnotest_E"));
				}
				ierTable.setWidget(row, 0, lbEducation);
				ierTable.setWidget(row++, 1, tbEducation);
			}
			if ((secList != null && secList.get(i).equals("Rx"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbRx = new Label("Rx");
				tbRx = new TextArea();
				tbRx.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_R")) {
					tbRx.setText(templateValuesMap.get("pnotes_R"));
				} else if (templateValuesMap.containsKey("pnotest_E")) {
					tbRx.setText(templateValuesMap.get("pnotest_R"));
				}

				ierTable.setWidget(row, 0, lbRx);
				ierTable.setWidget(row++, 1, tbRx);
				if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					final CustomListBox listRx = new CustomListBox();
					listRx.addItem("Select information to insert");
					listRx.addItem("Treatments");
					listRx.addItem("Previous Operations");
					listRx.addItem("Chronic Problems");
					listRx.addItem("Current Problems");
					listRx.addItem("Medications");
					listRx.addItem("Shots");
					listRx.addItem("Vitals");
					listRx.addItem("Allergies");
					listRx.addItem("Lab Values");
					listRx.addItem("Prescription");
					listRx.addChangeHandler(new ChangeHandler() {
						@Override
						public void onChange(ChangeEvent arg0) {
							if (listRx.getSelectedIndex() != 0) {
								String value = listRx.getWidgetText();
								tbRx.setText(tbRx.getText() + "\n\n" + value);
								insertModuleText(listRx.getWidgetText(),
										tbRx);
							}
						}
					});
					ierTable.setWidget(row++, 1, listRx);
				}
			}
		}
		ierTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		ierPanel.add(ierTable);
	}

	private void createVitalsTab() {
		FlexTable vitalsTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Vitals/Generals"))
			loopCountMax = sectionsFieldMap.get("Sections#Vitals/Generals")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Vitals/Generals"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#Vitals/Generals");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("Blood Pressure"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				HorizontalPanel temperaturePanel = new HorizontalPanel();
				temperaturePanel.setSpacing(5);
				Label lbBp = new Label("Blood Pressure");
				tbBp1 = new TextBox();
				if (templateValuesMap.containsKey("pnotessbp")) {
					tbBp1.setText(templateValuesMap.get("pnotessbp"));
				} else if (templateValuesMap.containsKey("pnotestsbp")) {
					tbBp1.setText(templateValuesMap.get("pnotestsbp"));
				}
				Label sepLabel = new Label("/");
				tbBp2 = new TextBox();
				if (templateValuesMap.containsKey("pnotesdbp")) {
					tbBp2.setText(templateValuesMap.get("pnotesdbp"));
				} else if (templateValuesMap.containsKey("pnotestdbp")) {
					tbBp2.setText(templateValuesMap.get("pnotestdbp"));
				}
				temperaturePanel.add(tbBp1);
				temperaturePanel.add(sepLabel);
				temperaturePanel.add(tbBp2);
				vitalsTable.setWidget(row, 0, lbBp);
				vitalsTable.setWidget(row++, 1, temperaturePanel);
			}
			if ((secList != null && secList.get(i).equals("Temperature"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbTemp = new Label("Temperature");
				int temp1 = 90;

				listTemp = new CustomListBox();
				while (temp1 <= 107) {
					int temp2 = 0;
					while (temp2 <= 9) {
						String value = temp1 + "." + temp2;
						listTemp.addItem(value);
						if (templateValuesMap.containsKey("pnotestemp")) {
							if (value.equals(templateValuesMap
									.get("pnotestemp")))
								listTemp.setSelectedIndex(listTemp
										.getItemCount() - 1);
						} else if (templateValuesMap.containsKey("pnotesttemp")) {
							if (value.equals(templateValuesMap
									.get("pnotesttemp")))
								listTemp.setSelectedIndex(listTemp
										.getItemCount() - 1);
						}
						temp2++;
					}
					temp1++;
				}
				listTemp.addItem("108.0");
				vitalsTable.setWidget(row, 0, lbTemp);
				vitalsTable.setWidget(row++, 1, listTemp);
			}
			if ((secList != null && secList.get(i).equals("Heart Rate"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbHeartRate = new Label("Heart Rate");
				tbHeartRate = new TextBox();
				if (templateValuesMap.containsKey("pnotesheartrate")) {
					tbHeartRate.setText(templateValuesMap
							.get("pnotesheartrate"));
				} else if (templateValuesMap.containsKey("pnotestheartrate")) {
					tbHeartRate.setText(templateValuesMap
							.get("pnotestheartrate"));
				}
				vitalsTable.setWidget(row, 0, lbHeartRate);
				vitalsTable.setWidget(row++, 1, tbHeartRate);
			}
			if ((secList != null && secList.get(i).equals("Respiratory Rate"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbRespRate = new Label("Respiratory Rate");
				tbRespRate = new TextBox();
				if (templateValuesMap.containsKey("pnotesresprate")) {
					tbRespRate.setText(templateValuesMap.get("pnotesresprate"));
				} else if (templateValuesMap.containsKey("pnotestresprate")) {
					tbRespRate
							.setText(templateValuesMap.get("pnotestresprate"));
				}
				vitalsTable.setWidget(row, 0, lbRespRate);
				vitalsTable.setWidget(row++, 1, tbRespRate);
			}
			if ((secList != null && secList.get(i).equals("Weight"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbWeight = new Label("Weight");
				tbWeight = new TextBox();
				if (templateValuesMap.containsKey("pnotesweight")) {
					tbWeight.setText(templateValuesMap.get("pnotesweight"));
				} else if (templateValuesMap.containsKey("pnotestweight")) {
					tbWeight.setText(templateValuesMap.get("pnotestweight"));
				}
				vitalsTable.setWidget(row, 0, lbWeight);
				vitalsTable.setWidget(row++, 1, tbWeight);
			}
			if ((secList != null && secList.get(i).equals("Height"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbHeight = new Label("Height");
				tbHeight = new TextBox();
				if (templateValuesMap.containsKey("pnotesheight")) {
					tbHeight.setText(templateValuesMap.get("pnotesheight"));
				} else if (templateValuesMap.containsKey("pnotestheight")) {
					tbHeight.setText(templateValuesMap.get("pnotestheight"));
				}
				vitalsTable.setWidget(row, 0, lbHeight);
				vitalsTable.setWidget(row++, 1, tbHeight);
			}
			if ((secList != null && secList.get(i).equals("BMI"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbBMI = new Label("BMI");
				tbBMIVal = new Label();
				if (templateValuesMap.containsKey("pnotesbmi")) {
					tbBMIVal.setText(templateValuesMap.get("pnotesbmi"));
				} else if (templateValuesMap.containsKey("pnotestbmi")) {
					tbBMIVal.setText(templateValuesMap.get("pnotestbmi"));
				}
				CustomButton calBmiBtn = new CustomButton("Calculate");
				calBmiBtn.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (!tbHeight.getText().equals("")
								&& !tbWeight.getText().equals("")) {
							if (Util.isNumber(tbHeight.getText())
									&& Util.isNumber(tbHeight.getText())) {
								float height = Float.parseFloat(tbHeight
										.getText());
								float weight = Float.parseFloat(tbWeight
										.getText());
								float bmi = (weight / height) * 703f;
								tbBMIVal.setText("" + bmi);
							}
						}
					}
				});
				vitalsTable.setWidget(row, 0, lbBMI);
				vitalsTable.setWidget(row, 1, tbBMIVal);
				vitalsTable.setWidget(row++, 2, calBmiBtn);
			}

			if ((secList != null && secList.get(i).equals("General (PE)"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbGeneral = new Label("General (PE)");
				tbGeneral = new TextArea();
				tbGeneral.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotesgeneral")) {
					tbGeneral.setText(templateValuesMap.get("pnotesgeneral"));
				} else if (templateValuesMap.containsKey("pnotestgeneral")) {
					tbGeneral.setText(templateValuesMap.get("pnotestgeneral"));
				}
				vitalsTable.setWidget(row, 0, lbGeneral);
				vitalsTable.setWidget(row++, 1, tbGeneral);
			}
		}
		vitalsTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		vitalGenPanel.add(vitalsTable);
	}

	private void createCCPHTab() {
		FlexTable ccphTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#CC & HPI"))
			loopCountMax = sectionsFieldMap.get("Sections#CC & HPI").size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#CC & HPI"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#CC & HPI");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("CC"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbCC = new Label("CC");
				tbCC = new TextArea();
				tbCC.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotescc")) {
					tbCC.setText(templateValuesMap.get("pnotescc"));
				} else if (templateValuesMap.containsKey("pnotestcc")) {
					tbCC.setText(templateValuesMap.get("pnotestcc"));
				}
				ccphTable.setWidget(row, 0, lbCC);
				ccphTable.setWidget(row++, 1, tbCC);
			}
			if ((secList != null && secList.get(i).equals("HPI"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbHPI = new Label("HPI");
				tbHPI = new TextArea();
				tbHPI.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnoteshpi")) {
					tbHPI.setText(templateValuesMap.get("pnoteshpi"));
				} else if (templateValuesMap.containsKey("pnotesthpi")) {
					tbHPI.setText(templateValuesMap.get("pnotesthpi"));
				}
				ccphTable.setWidget(row, 0, lbHPI);
				ccphTable.setWidget(row++, 1, tbHPI);
			}
		}
		ccphTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		ccHpiPanel.add(ccphTable);
	}

	private void createRevSysTab() {
		revOfSysPanel.setSpacing(10);
		FlexTable revSysTable = new FlexTable();
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Review Of Systems"))
			loopCountMax = sectionsFieldMap.get("Sections#Review Of Systems")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Review Of Systems"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap
				.get("Sections#Review Of Systems");
		Label lbInfo = new Label("REVIEW OF SYSTEMS: (check if done)");
		lbInfo.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
		revOfSysPanel.add(lbInfo);
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("General"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel generalPanel = new VerticalPanel();
				cbGeneral = new CheckBox("General");
				cbGeneral.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				generalPanel.add(cbGeneral);
				revOfSysPanel.add(generalPanel);
				tbGeneralRos = new TextArea();
				tbGeneralRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbGeneralRos.setSize("700px", "200px");
				cbGeneral
						.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

							@Override
							public void onValueChange(
									ValueChangeEvent<Boolean> arg0) {
								if (cbGeneral.getValue()) {
									generalPanel.add(tbGeneralRos);
								} else {
									generalPanel.remove(tbGeneralRos);
								}
							}

						});
				if (templateValuesMap.containsKey("pnotesrosgenralstatus")) {
					if (templateValuesMap.get("pnotesrosgenralstatus").equals(
							"1")) {
						cbGeneral.setValue(true);
						if (templateValuesMap.containsKey("pnotesrosgenral")) {
							generalPanel.add(tbGeneralRos);
							tbGeneralRos.setText(templateValuesMap
									.get("pnotesrosgenral"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosgenralstatus")) {
					if (templateValuesMap.get("pnotestrosgenralstatus").equals(
							"1")) {
						cbGeneral.setValue(true);
						if (templateValuesMap.containsKey("pnotestrosgenral")) {
							generalPanel.add(tbGeneralRos);
							tbGeneralRos.setText(templateValuesMap
									.get("pnotestrosgenral"));
						}
					}
				}

			}
			if ((secList != null && secList.get(i).equals("Head"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel headPanel = new VerticalPanel();
				cbHead = new CheckBox("Head");
				cbHead.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				headPanel.add(cbHead);
				revOfSysPanel.add(headPanel);
				tbHeadRos = new TextArea();
				tbHeadRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbHeadRos.setSize("700px", "200px");
				cbHead
						.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

							@Override
							public void onValueChange(
									ValueChangeEvent<Boolean> arg0) {
								if (cbHead.getValue()) {
									headPanel.add(tbHeadRos);
								} else {
									headPanel.remove(tbHeadRos);
								}
							}

						});
				if (templateValuesMap.containsKey("pnotesrosheadstatus")) {
					if (templateValuesMap.get("pnotesrosheadstatus").equals(
							"1")) {
						cbHead.setValue(true);
						if (templateValuesMap.containsKey("pnotesroshead")) {
							headPanel.add(tbHeadRos);
							tbHeadRos.setText(templateValuesMap
									.get("pnotesroshead"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosheadstatus")) {
					if (templateValuesMap.get("pnotestrosheadstatus").equals(
							"1")) {
						cbHead.setValue(true);
						if (templateValuesMap.containsKey("pnotestroshead")) {
							headPanel.add(tbHeadRos);
							tbHeadRos.setText(templateValuesMap
									.get("pnotestroshead"));
						}
					}
				}

			}
			if ((secList != null && secList.get(i).equals("Eyes"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel eyesPanel = new VerticalPanel();
				eyesPanel.setSpacing(5);

				cbEyesRos = new CheckBox("Eyes");
				cbEyesRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				eyesPanel.add(cbEyesRos);
				revOfSysPanel.add(eyesPanel);
				final Label lbInfo2 = new Label("Select if abnormal");
				lbInfo2.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo2.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel eyeHp = new HorizontalPanel();
				eyeHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbPoorVision = new CheckBox("poor vision");
				cbEyesPain = new CheckBox("pain");
				eyeHp.add(cbPoorVision);
				eyeHp.add(cbEyesPain);
				tbEyesRos = new TextArea();
				tbEyesRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbEyesRos.setSize("700px", "200px");
				cbEyesRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbEyesRos.getValue()) {
							eyesPanel.add(lbInfo2);
							eyesPanel.add(eyeHp);
							eyesPanel.add(tbEyesRos);
						} else {
							eyesPanel.remove(lbInfo2);
							eyesPanel.remove(eyeHp);
							eyesPanel.remove(tbEyesRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesroseyesstatus")) {
					if (templateValuesMap.get("pnotesroseyesstatus")
							.equals("1")) {
						cbEyesRos.setValue(true);
						eyesPanel.add(tbEyesRos);
						selVal = templateValuesMap.get("pnotesroseyes");
						if (templateValuesMap.get("pnotesroseyescmnts")!=null) {
							tbEyesRos.setText(templateValuesMap
									.get("pnotesroseyescmnts"));
						}

					}
				} else if (templateValuesMap
						.containsKey("pnotestroseyesstatus")) {
					if (templateValuesMap.get("pnotestroseyesstatus").equals(
							"1")) {
						cbEyesRos.setValue(true);
						eyesPanel.add(tbEyesRos);
						selVal = templateValuesMap.get("pnotestroseyes");
						if (templateValuesMap.get("pnotestroseyescmnts")!=null) {
							tbEyesRos.setText(templateValuesMap
									.get("pnotestroseyescmnts"));
						}

					}
				}
				if (selVal != null && !selVal.equals("")) {
					eyesPanel.add(lbInfo2);
					eyesPanel.add(eyeHp);
					if (selVal.indexOf("poor vision") != -1) {
						cbPoorVision.setValue(true);
					}
					if (selVal.indexOf("pain") != -1) {
						cbEyesPain.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("ENT"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel entPanel = new VerticalPanel();
				entPanel.setSpacing(5);

				cbEntRos = new CheckBox("ENT");
				cbEntRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				entPanel.add(cbEntRos);
				revOfSysPanel.add(entPanel);
				final Label lbInfo3 = new Label("Select if abnormal");
				lbInfo3.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo3.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel entHp = new HorizontalPanel();
				entHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbSoreThroat = new CheckBox("sore throat");
				cbENTPain = new CheckBox("pain");
				cbCoryza = new CheckBox("coryza");
				cbAcuity = new CheckBox("acuity");
				cbDysphagia = new CheckBox("dysphagia");
				entHp.add(cbSoreThroat);
				entHp.add(cbENTPain);
				entHp.add(cbCoryza);
				entHp.add(cbAcuity);
				entHp.add(cbDysphagia);
				tbENTRos = new TextArea();
				tbENTRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbENTRos.setSize("700px", "200px");
				cbEntRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbEntRos.getValue()) {
							entPanel.add(lbInfo3);
							entPanel.add(entHp);
							entPanel.add(tbENTRos);
						} else {
							entPanel.remove(lbInfo3);
							entPanel.remove(entHp);
							entPanel.remove(tbENTRos);
						}
					}
				});

				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosentstatus")) {
					if (templateValuesMap.get("pnotesrosentstatus").equals("1")) {
						entPanel.add(tbENTRos);
						cbEntRos.setValue(true);
						selVal = templateValuesMap.get("pnotesrosent");
						if (templateValuesMap.get("pnotesrosentcmnts")!=null) {
							tbENTRos.setText(templateValuesMap
									.get("pnotesrosentcmnts"));
						}
					}
				} else if (templateValuesMap.containsKey("pnotestrosentstatus")) {
					if (templateValuesMap.get("pnotestrosentstatus")
							.equals("1")) {
						entPanel.add(tbENTRos);
						cbEntRos.setValue(true);
						selVal = templateValuesMap.get("pnotestrosent");
						if (templateValuesMap.get("pnotestrosentcmnts")!=null) {
							tbENTRos.setText(templateValuesMap
									.get("pnotestrosentcmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					entPanel.add(lbInfo3);
					entPanel.add(entHp);
					if (selVal.indexOf("sore throat") != -1) {
						cbSoreThroat.setValue(true);
					}
					if (selVal.indexOf("pain") != -1) {
						cbENTPain.setValue(true);
					}
					if (selVal.indexOf("coryza") != -1) {
						cbCoryza.setValue(true);
					}
					if (selVal.indexOf("acuity") != -1) {
						cbAcuity.setValue(true);
					}
					if (selVal.indexOf("dysphagia") != -1) {
						cbDysphagia.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("CV"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel cvPanel = new VerticalPanel();
				cvPanel.setSpacing(5);

				cbCVRos = new CheckBox("CV");
				cbCVRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				cvPanel.add(cbCVRos);
				revOfSysPanel.add(cvPanel);
				final Label lbInfo4 = new Label("Select if abnormal");
				lbInfo4.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo4.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel cvHp = new HorizontalPanel();
				cvHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbCVPain = new CheckBox("pain");
				cbPalpitations = new CheckBox("palpitations");
				cbHypoHyperTension = new CheckBox("hypo/hypertension");
				cvHp.add(cbCVPain);
				cvHp.add(cbPalpitations);
				cvHp.add(cbHypoHyperTension);
				tbCVRos = new TextArea();
				tbCVRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbCVRos.setSize("700px", "200px");
				cbCVRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbCVRos.getValue()) {
							cvPanel.add(lbInfo4);
							cvPanel.add(cvHp);
							cvPanel.add(tbCVRos);
						} else {
							cvPanel.remove(lbInfo4);
							cvPanel.remove(cvHp);
							cvPanel.remove(tbCVRos);
						}
					}
				});

				String selVal = "";
				if (templateValuesMap.containsKey("pnotesroscvstatus")) {
					if (templateValuesMap.get("pnotesroscvstatus").equals("1")) {
						cvPanel.add(tbCVRos);
						cbCVRos.setValue(true);
						selVal = templateValuesMap.get("pnotesroscv");
						if (templateValuesMap.get("pnotesroscvsmnts")!=null) {
							tbCVRos.setText(templateValuesMap
									.get("pnotesroscvsmnts"));
						}
					}
				} else if (templateValuesMap.containsKey("pnotestroscvstatus")) {
					if (templateValuesMap.get("pnotestroscvstatus").equals("1")) {
						cvPanel.add(tbCVRos);
						cbCVRos.setValue(true);
						selVal = templateValuesMap.get("pnotestroscv");
						if (templateValuesMap.get("pnotestroscvsmnts")!=null) {
							tbCVRos.setText(templateValuesMap
									.get("pnotestroscvsmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					cvPanel.add(lbInfo4);
					cvPanel.add(cvHp);
					if (selVal.indexOf("pain") != -1) {
						cbCVPain.setValue(true);
					}
					if (selVal.indexOf("palpitations") != -1) {
						cbPalpitations.setValue(true);
					}
					if (selVal.indexOf("hypo/hypertension") != -1) {
						cbHypoHyperTension.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Resp"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel respPanel = new VerticalPanel();
				respPanel.setSpacing(5);

				cbRespRos = new CheckBox("Resp");
				cbRespRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				respPanel.add(cbRespRos);
				revOfSysPanel.add(respPanel);
				final Label lbInfo5 = new Label("Select if abnormal");
				lbInfo5.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo5.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel respHp = new HorizontalPanel();
				respHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbDyspnea = new CheckBox("dyspnea");
				cbCough = new CheckBox("cough");
				cbTachypnea = new CheckBox("tachypnea");
				respHp.add(cbDyspnea);
				respHp.add(cbCough);
				respHp.add(cbTachypnea);
				tbRespRos = new TextArea();
				tbRespRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbRespRos.setSize("700px", "200px");
				cbRespRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbRespRos.getValue()) {
							respPanel.add(lbInfo5);
							respPanel.add(respHp);
							respPanel.add(tbRespRos);
						} else {
							respPanel.remove(lbInfo5);
							respPanel.remove(respHp);
							respPanel.remove(tbRespRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosrespstatus")) {
					if (templateValuesMap.get("pnotesrosrespstatus")
							.equals("1")) {
						respPanel.add(tbRespRos);
						cbRespRos.setValue(true);
						selVal = templateValuesMap.get("pnotesrosresp");
						if (templateValuesMap.get("pnotesrosrespcmnts")!=null) {
							tbRespRos.setText(templateValuesMap
									.get("pnotesrosrespcmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosrespstatus")) {
					if (templateValuesMap.get("pnotestrosrespstatus").equals(
							"1")) {
						respPanel.add(tbRespRos);
						cbRespRos.setValue(true);
						selVal = templateValuesMap.get("pnotestrosresp");
						if (templateValuesMap.get("pnotestrosrespcmnts")!=null) {
							tbRespRos.setText(templateValuesMap
									.get("pnotestrosrespcmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					respPanel.add(lbInfo5);
					respPanel.add(respHp);
					if (selVal.indexOf("dyspnea") != -1) {
						cbDyspnea.setValue(true);
					}
					if (selVal.indexOf("cough") != -1) {
						cbCough.setValue(true);
					}
					if (selVal.indexOf("tachypnea") != -1) {
						cbTachypnea.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("GI"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel giPanel = new VerticalPanel();
				giPanel.setSpacing(5);

				cbGIRos = new CheckBox("GI");
				cbGIRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				giPanel.add(cbGIRos);
				revOfSysPanel.add(giPanel);
				final Label lbInfo6 = new Label("Select if abnormal");
				lbInfo6.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo6.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel giHp = new HorizontalPanel();
				giHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbPainGI = new CheckBox("pain");
				cbNausea = new CheckBox("nausea");
				cbVomiting = new CheckBox("vomiting");
				cbDiarrhea = new CheckBox("diarrhea");
				cbConstipation = new CheckBox("constipation");
				giHp.add(cbPainGI);
				giHp.add(cbNausea);
				giHp.add(cbVomiting);
				giHp.add(cbDiarrhea);
				giHp.add(cbConstipation);
				tbGIRos = new TextArea();
				tbGIRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbGIRos.setSize("700px", "200px");
				cbGIRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbGIRos.getValue()) {
							giPanel.add(lbInfo6);
							giPanel.add(giHp);
							giPanel.add(tbGIRos);
						} else {
							giPanel.remove(lbInfo6);
							giPanel.remove(giHp);
							giPanel.remove(tbGIRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosgistatus")) {
					if (templateValuesMap.get("pnotesrosgistatus").equals("1")) {
						giPanel.add(tbGIRos);
						cbGIRos.setValue(true);
						selVal = templateValuesMap.get("pnotesroshgi");
						if (templateValuesMap.get("pnotesrosgicmnts")!=null) {
							tbGIRos.setText(templateValuesMap
									.get("pnotesrosgicmnts"));
						}
					}
				} else if (templateValuesMap.containsKey("pnotestrosgistatus")) {
					if (templateValuesMap.get("pnotestrosgistatus").equals("1")) {
						giPanel.add(tbGIRos);
						cbGIRos.setValue(true);
						selVal = templateValuesMap.get("pnotestroshgi");
						if (templateValuesMap.get("pnotestrosgicmnts")!=null) {
							tbGIRos.setText(templateValuesMap
									.get("pnotestrosgicmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					giPanel.add(lbInfo6);
					giPanel.add(giHp);
					if (selVal.indexOf("pain") != -1) {
						cbPainGI.setValue(true);
					}
					if (selVal.indexOf("nausea") != -1) {
						cbNausea.setValue(true);
					}
					if (selVal.indexOf("vomiting") != -1) {
						cbVomiting.setValue(true);
					}
					if (selVal.indexOf("diarrhea") != -1) {
						cbDiarrhea.setValue(true);
					}
					if (selVal.indexOf("constipation") != -1) {
						cbConstipation.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("GU"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel guPanel = new VerticalPanel();
				guPanel.setSpacing(5);

				cbGUROS = new CheckBox("GU");
				cbGUROS.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				guPanel.add(cbGUROS);
				revOfSysPanel.add(guPanel);
				final Label lbInfo7 = new Label("Select if abnormal");
				lbInfo7.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo7.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel guHp = new HorizontalPanel();
				guHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbPainGU = new CheckBox("pain");
				cbBleeding = new CheckBox("bleeding");
				cbIncontinent = new CheckBox("incontinent");
				cbNocturia = new CheckBox("nocturia");
				cbFoulSmell = new CheckBox("foul smell");
				guHp.add(cbPainGU);
				guHp.add(cbBleeding);
				guHp.add(cbIncontinent);
				guHp.add(cbNocturia);
				guHp.add(cbFoulSmell);
				tbGURos = new TextArea();
				tbGURos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbGURos.setSize("700px", "200px");
				cbGUROS.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbGUROS.getValue()) {
							guPanel.add(lbInfo7);
							guPanel.add(guHp);
							guPanel.add(tbGURos);
						} else {
							guPanel.remove(lbInfo7);
							guPanel.remove(guHp);
							guPanel.remove(tbGURos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosgustatus")) {
					if (templateValuesMap.get("pnotesrosgustatus").equals("1")) {
						guPanel.add(tbGURos);
						cbGUROS.setValue(true);
						selVal = templateValuesMap.get("pnotesrosgu");
						if (templateValuesMap.get("pnotesrosgucmnts")!=null) {
							tbGURos.setText(templateValuesMap
									.get("pnotesrosgucmnts"));
						}
					}
				} else if (templateValuesMap.containsKey("pnotestrosgustatus")) {
					if (templateValuesMap.get("pnotestrosgustatus").equals("1")) {
						guPanel.add(tbGURos);
						cbGUROS.setValue(true);
						selVal = templateValuesMap.get("pnotestrosgu");
						if (templateValuesMap.get("pnotestrosgucmnts")!=null) {
							tbGURos.setText(templateValuesMap
									.get("pnotestrosgucmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					guPanel.add(lbInfo7);
					guPanel.add(guHp);
					if (selVal.indexOf("pain") != -1) {
						cbPainGU.setValue(true);
					}
					if (selVal.indexOf("bleeding") != -1) {
						cbBleeding.setValue(true);
					}
					if (selVal.indexOf("incontinent") != -1) {
						cbIncontinent.setValue(true);
					}
					if (selVal.indexOf("nocturia") != -1) {
						cbNocturia.setValue(true);
					}
					if (selVal.indexOf("foul smell") != -1) {
						cbFoulSmell.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Muscle"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel musclePanel = new VerticalPanel();
				musclePanel.setSpacing(5);

				cbMuscle = new CheckBox("Muscle");
				cbMuscle.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				musclePanel.add(cbMuscle);
				revOfSysPanel.add(musclePanel);
				final Label lbInfo8 = new Label("Select if abnormal");
				lbInfo8.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo8.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel muscleHp = new HorizontalPanel();
				muscleHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbPainMuscle = new CheckBox("pain");
				cbWeakness = new CheckBox("weakness");
				muscleHp.add(cbPainMuscle);
				muscleHp.add(cbWeakness);
				tbMuscleRos = new TextArea();
				tbMuscleRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbMuscleRos.setSize("700px", "200px");
				cbMuscle.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbMuscle.getValue()) {
							musclePanel.add(lbInfo8);
							musclePanel.add(muscleHp);
							musclePanel.add(tbMuscleRos);
						} else {
							musclePanel.remove(lbInfo8);
							musclePanel.remove(muscleHp);
							musclePanel.remove(tbMuscleRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosmusclestatus")) {
					if (templateValuesMap.get("pnotesrosmusclestatus").equals(
							"1")) {
						musclePanel.add(tbMuscleRos);
						cbMuscle.setValue(true);
						selVal = templateValuesMap.get("pnotesrosmuscles");
						if (templateValuesMap.get("pnotesrosmusclescmnts")!=null) {
							tbMuscleRos.setText(templateValuesMap
									.get("pnotesrosmusclescmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosmusclestatus")) {
					if (templateValuesMap.get("pnotestrosmusclestatus").equals(
							"1")) {
						musclePanel.add(tbMuscleRos);
						cbMuscle.setValue(true);
						selVal = templateValuesMap.get("pnotestrosmuscles");
						if (templateValuesMap.get("pnotestrosmusclescmnts")!=null) {
							tbMuscleRos.setText(templateValuesMap
									.get("pnotestrosmusclescmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					musclePanel.add(lbInfo8);
					musclePanel.add(muscleHp);
					if (selVal.indexOf("pain") != -1) {
						cbPainMuscle.setValue(true);
					}
					if (selVal.indexOf("weakness") != -1) {
						cbWeakness.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Skin"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel skinPanel = new VerticalPanel();
				skinPanel.setSpacing(5);

				cbSkinRos = new CheckBox("Skin");
				cbSkinRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				skinPanel.add(cbSkinRos);
				revOfSysPanel.add(skinPanel);
				final Label lbInfo9 = new Label("Select if abnormal");
				lbInfo9.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo9.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel skinHp = new HorizontalPanel();
				skinHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbRash = new CheckBox("rash");
				cbPainSkin = new CheckBox("pain");
				cbAbscess = new CheckBox("abscess");
				cbMass = new CheckBox("mass");
				skinHp.add(cbRash);
				skinHp.add(cbPainSkin);
				skinHp.add(cbAbscess);
				skinHp.add(cbMass);
				tbSkinRos = new TextArea();
				tbSkinRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbSkinRos.setSize("700px", "200px");
				cbSkinRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbSkinRos.getValue()) {
							skinPanel.add(lbInfo9);
							skinPanel.add(skinHp);
							skinPanel.add(tbSkinRos);
						} else {
							skinPanel.remove(lbInfo9);
							skinPanel.remove(skinHp);
							skinPanel.remove(tbSkinRos);
						}
					}
				});

				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosskinstatus")) {
					if (templateValuesMap.get("pnotesrosskinstatus")
							.equals("1")) {
						skinPanel.add(tbSkinRos);
						cbSkinRos.setValue(true);
						selVal = templateValuesMap.get("pnotesrosskin");
						if (templateValuesMap.get("pnotesrosskincmnts")!=null) {
							tbSkinRos.setText(templateValuesMap
									.get("pnotesrosskincmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosskinstatus")) {
					if (templateValuesMap.get("pnotestrosskinstatus").equals(
							"1")) {
						skinPanel.add(tbSkinRos);
						cbSkinRos.setValue(true);
						selVal = templateValuesMap.get("pnotestrosskin");
						if (templateValuesMap.get("pnotestrosskincmnts")!=null) {
							tbSkinRos.setText(templateValuesMap
									.get("pnotestrosskincmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					skinPanel.add(lbInfo9);
					skinPanel.add(skinHp);
					if (selVal.indexOf("rash") != -1) {
						cbRash.setValue(true);
					}
					if (selVal.indexOf("pain") != -1) {
						cbPainSkin.setValue(true);
					}
					if (selVal.indexOf("abscess") != -1) {
						cbAbscess.setValue(true);
					}
					if (selVal.indexOf("mass") != -1) {
						cbMass.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Psych"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel psychPanel = new VerticalPanel();
				psychPanel.setSpacing(5);

				cbPsychRos = new CheckBox("Psych");
				cbPsychRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				psychPanel.add(cbPsychRos);
				revOfSysPanel.add(psychPanel);
				final Label lbInfo10 = new Label("Select if abnormal");
				lbInfo10.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo10.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel psychHp = new HorizontalPanel();
				psychHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbFatigue = new CheckBox("fatigue");
				cbInsomnia = new CheckBox("insomnia");
				cbMoodProblem = new CheckBox("mood problem");
				cbCrying = new CheckBox("crying");
				cbDepression = new CheckBox("depression");
				psychHp.add(cbFatigue);
				psychHp.add(cbInsomnia);
				psychHp.add(cbMoodProblem);
				psychHp.add(cbCrying);
				psychHp.add(cbDepression);
				tbPsychRos = new TextArea();
				tbPsychRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbPsychRos.setSize("700px", "200px");
				cbPsychRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbPsychRos.getValue()) {
							psychPanel.add(lbInfo10);
							psychPanel.add(psychHp);
							psychPanel.add(tbPsychRos);
						} else {
							psychPanel.remove(lbInfo10);
							psychPanel.remove(psychHp);
							psychPanel.remove(tbPsychRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrospsychstatus")) {
					if (templateValuesMap.get("pnotesrospsychstatus").equals(
							"1")) {
						psychPanel.add(tbPsychRos);
						cbPsychRos.setValue(true);
						selVal = templateValuesMap.get("pnotesrospsych");
						if (templateValuesMap.get("pnotesrospsychcmnts")!=null) {
							tbPsychRos.setText(templateValuesMap
									.get("pnotesrospsychcmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrospsychstatus")) {
					if (templateValuesMap.get("pnotestrospsychstatus").equals(
							"1")) {
						psychPanel.add(tbPsychRos);
						cbPsychRos.setValue(true);
						selVal = templateValuesMap.get("pnotestrospsych");
						if (templateValuesMap.get("pnotestrospsychcmnts")!=null) {
							tbPsychRos.setText(templateValuesMap
									.get("pnotestrospsychcmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					psychPanel.add(lbInfo10);
					psychPanel.add(psychHp);
					if (selVal.indexOf("fatigue") != -1) {
						cbFatigue.setValue(true);
					}
					if (selVal.indexOf("insomnia") != -1) {
						cbInsomnia.setValue(true);
					}
					if (selVal.indexOf("mood problem") != -1) {
						cbMoodProblem.setValue(true);
					}
					if (selVal.indexOf("crying") != -1) {
						cbCrying.setValue(true);
					}
					if (selVal.indexOf("depression") != -1) {
						cbDepression.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Endocrine"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel endoPanel = new VerticalPanel();
				endoPanel.setSpacing(5);

				cbEndoRos = new CheckBox("Endocrine");
				cbEndoRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				endoPanel.add(cbEndoRos);
				revOfSysPanel.add(endoPanel);
				final Label lbInfo11 = new Label("Select if abnormal");
				lbInfo11.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo11.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel endoHp = new HorizontalPanel();
				endoHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbHotFlashes = new CheckBox("hot flashes");
				endoHp.add(cbHotFlashes);
				tbEndoRos = new TextArea();
				tbEndoRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbEndoRos.setSize("700px", "200px");
				cbEndoRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbEndoRos.getValue()) {
							endoPanel.add(lbInfo11);
							endoPanel.add(endoHp);
							endoPanel.add(tbEndoRos);
						} else {
							endoPanel.remove(lbInfo11);
							endoPanel.remove(endoHp);
							endoPanel.remove(tbEndoRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosendostatus")) {
					if (templateValuesMap.get("pnotesrosendostatus")
							.equals("1")) {
						endoPanel.add(tbEndoRos);
						cbEndoRos.setValue(true);
						selVal = templateValuesMap.get("pnotesrosendo");
						if (templateValuesMap.get("pnotesrosendocmnts")!=null) {
							tbEndoRos.setText(templateValuesMap
									.get("pnotesrosendocmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosendostatus")) {
					if (templateValuesMap.get("pnotestrosendostatus").equals(
							"1")) {
						endoPanel.add(tbEndoRos);
						cbEndoRos.setValue(true);
						selVal = templateValuesMap.get("pnotestrosendo");
						if (templateValuesMap.get("pnotestrosendocmnts")!=null) {
							tbEndoRos.setText(templateValuesMap
									.get("pnotestrosendocmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					endoPanel.add(lbInfo11);
					endoPanel.add(endoHp);
					if (selVal.indexOf("hot flashes") != -1) {
						cbHotFlashes.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Hem/Lymph"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel hemLymPanel = new VerticalPanel();
				hemLymPanel.setSpacing(5);

				cbHemLymRos = new CheckBox("Hem/Lymph");
				cbHemLymRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				hemLymPanel.add(cbHemLymRos);
				revOfSysPanel.add(hemLymPanel);
				final Label lbInfo12 = new Label("Select if abnormal");
				lbInfo12.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo12.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel hemLymHp = new HorizontalPanel();
				hemLymHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbFevers = new CheckBox("fevers");
				cbChills = new CheckBox("chills");
				cbSwelling = new CheckBox("swelling");
				cbNightSweats = new CheckBox("night sweats");
				hemLymHp.add(cbFevers);
				hemLymHp.add(cbChills);
				hemLymHp.add(cbSwelling);
				hemLymHp.add(cbNightSweats);
				tbHemLymRos = new TextArea();
				tbHemLymRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbHemLymRos.setSize("700px", "200px");
				cbHemLymRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbHemLymRos.getValue()) {
							hemLymPanel.add(lbInfo12);
							hemLymPanel.add(hemLymHp);
							hemLymPanel.add(tbHemLymRos);
						} else {
							hemLymPanel.remove(lbInfo12);
							hemLymPanel.remove(hemLymHp);
							hemLymPanel.remove(tbHemLymRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesroshemlympstatus")) {
					if (templateValuesMap.get("pnotesroshemlympstatus").equals(
							"1")) {
						hemLymPanel.add(tbHemLymRos);
						cbHemLymRos.setValue(true);
						selVal = templateValuesMap.get("pnotesroshemlymp");
						if (templateValuesMap.get("pnotesroshemlympcmnts")!=null) {
							tbHemLymRos.setText(templateValuesMap
									.get("pnotesroshemlympcmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestroshemlympstatus")) {
					if (templateValuesMap.get("pnotestroshemlympstatus")
							.equals("1")) {
						hemLymPanel.add(tbHemLymRos);
						cbHemLymRos.setValue(true);
						selVal = templateValuesMap.get("pnotestroshemlymp");
						if (templateValuesMap.get("pnotestroshemlympcmnts")!=null) {
							tbHemLymRos.setText(templateValuesMap
									.get("pnotestroshemlympcmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					hemLymPanel.add(lbInfo12);
					hemLymPanel.add(hemLymHp);
					if (selVal.indexOf("fevers") != -1) {
						cbFevers.setValue(true);
					}
					if (selVal.indexOf("chills") != -1) {
						cbChills.setValue(true);
					}
					if (selVal.indexOf("swelling") != -1) {
						cbSwelling.setValue(true);
					}
					if (selVal.indexOf("night sweats") != -1) {
						cbNightSweats.setValue(true);
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Neuro"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel neuroPanel = new VerticalPanel();
				neuroPanel.setSpacing(5);

				cbNeuroRos = new CheckBox("Neuro");
				cbNeuroRos.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				neuroPanel.add(cbNeuroRos);
				revOfSysPanel.add(neuroPanel);
				final Label lbInfo13 = new Label("Select if abnormal");
				lbInfo13.getElement().getStyle().setMarginLeft(30, Unit.PX);
				lbInfo13.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
				final HorizontalPanel neuroHp = new HorizontalPanel();
				neuroHp.getElement().getStyle().setMarginLeft(30, Unit.PX);
				cbNumbness = new CheckBox("numbness");
				cbTingling = new CheckBox("tingling");
				cbWeaknessNeuro = new CheckBox("weakness");
				cbHeadache = new CheckBox("headache");
				cbLossOfCons = new CheckBox("loss of consciousness");
				neuroHp.add(cbNumbness);
				neuroHp.add(cbTingling);
				neuroHp.add(cbWeaknessNeuro);
				neuroHp.add(cbHeadache);
				neuroHp.add(cbLossOfCons);
				tbNeuroRos = new TextArea();
				tbNeuroRos.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbNeuroRos.setSize("700px", "200px");
				cbNeuroRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbNeuroRos.getValue()) {
							neuroPanel.add(lbInfo13);
							neuroPanel.add(neuroHp);
							neuroPanel.add(tbNeuroRos);
						} else {
							neuroPanel.remove(lbInfo13);
							neuroPanel.remove(neuroHp);
							neuroPanel.remove(tbNeuroRos);
						}
					}
				});
				String selVal = "";
				if (templateValuesMap.containsKey("pnotesrosneurostatus")) {
					if (templateValuesMap.get("pnotesrosneurostatus").equals(
							"1")) {
						neuroPanel.add(tbNeuroRos);
						cbNeuroRos.setValue(true);
						selVal = templateValuesMap.get("pnotesrosneuro");
						if (templateValuesMap.get("pnotesrosneurocmnts")!=null) {
							tbNeuroRos.setText(templateValuesMap
									.get("pnotesrosneurocmnts"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosneurostatus")) {
					if (templateValuesMap.get("pnotestrosneurostatus").equals(
							"1")) {
						neuroPanel.add(tbNeuroRos);
						cbNeuroRos.setValue(true);
						selVal = templateValuesMap.get("pnotestrosneuro");
						if (templateValuesMap.get("pnotestrosneurocmnts")!=null) {
							tbNeuroRos.setText(templateValuesMap
									.get("pnotestrosneurocmnts"));
						}
					}
				}
				if (selVal != null && !selVal.equals("")) {
					neuroPanel.add(lbInfo13);
					neuroPanel.add(neuroHp);
					if (selVal.indexOf("numbness") != -1) {
						cbNumbness.setValue(true);
					}
					if (selVal.indexOf("tingling") != -1) {
						cbTingling.setValue(true);
					}
					if (selVal.indexOf("weakness") != -1) {
						cbWeaknessNeuro.setValue(true);
					}
					if (selVal.indexOf("headache") != -1) {
						cbHeadache.setValue(true);
					}
					if (selVal.indexOf("loss of consciousness") != -1) {
						cbLossOfCons.setValue(true);
					}
				}
			}

			if ((secList != null && secList.get(i).equals(
					"Immunologic/Allergies"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final VerticalPanel immAllrgPanel = new VerticalPanel();
				cbImmAllrgRos = new CheckBox("Immunologic/Allergies");
				cbImmAllrgRos
						.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				immAllrgPanel.add(cbImmAllrgRos);
				revOfSysPanel.add(immAllrgPanel);
				tbImmAllrg = new TextArea();
				tbImmAllrg.getElement().getStyle().setMarginLeft(30, Unit.PX);
				tbImmAllrg.setSize("700px", "200px");
				cbImmAllrgRos.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbImmAllrgRos.getValue()) {
							immAllrgPanel.add(tbImmAllrg);

						} else {
							immAllrgPanel.remove(tbImmAllrg);
						}
					}
				});

				if (templateValuesMap.containsKey("pnotesrosimmallrgstatus")) {
					if (templateValuesMap.get("pnotesrosimmallrgstatus")
							.equals("1")) {
						cbImmAllrgRos.setValue(true);
						if (templateValuesMap.containsKey("pnotesrosimmallrg")) {
							immAllrgPanel.add(tbImmAllrg);
							tbImmAllrg.setText(templateValuesMap
									.get("pnotesrosimmallrg"));
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestrosimmallrgstatus")) {
					if (templateValuesMap.get("pnotestrosimmallrgstatus")
							.equals("1")) {
						cbImmAllrgRos.setValue(true);
						if (templateValuesMap.containsKey("pnotestrosimmallrg")) {
							immAllrgPanel.add(tbImmAllrg);
							tbImmAllrg.setText(templateValuesMap
									.get("pnotestrosimmallrg"));
						}
					}
				}

			}
		}
		revSysTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		revOfSysPanel.add(revSysTable);
	}

	private void createPastHistoryTab() {
		FlexTable pastHistTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap
						.containsKey("Sections#Past Medical History"))
			loopCountMax = sectionsFieldMap
					.get("Sections#Past Medical History").size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap
						.containsKey("Sections#Past Medical History"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap
				.get("Sections#Past Medical History");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("PH"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbPH = new Label("PH");
				tbPH = new TextArea();
				tbPH.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotesph")) {
					tbPH.setText(templateValuesMap.get("pnotesph"));
				} else if (templateValuesMap.containsKey("pnotestph")) {
					tbPH.setText(templateValuesMap.get("pnotestph"));
				}
				pastHistTable.setWidget(row, 0, lbPH);
				pastHistTable.setWidget(row++, 1, tbPH);
				if (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					final CustomListBox listInsInfo = new CustomListBox();
					listInsInfo.addItem("Select information to insert");
					listInsInfo.addItem("Hospitalization", "Hospitalization");
					listInsInfo.addItem("Procedures", "Procedures");
					listInsInfo.addItem("Allergies", "Allergies");
					listInsInfo.addItem("Medications", "Medications");
					listInsInfo.addItem("Current Problems", "Current Problems");
					listInsInfo.addItem("Chronic Problems", "Chronic Problems");
					listInsInfo.addChangeHandler(new ChangeHandler() {
						@Override
						public void onChange(ChangeEvent arg0) {
							if (listInsInfo.getSelectedIndex() != 0) {
								String value = listInsInfo.getWidgetText();
								tbPH.setText(tbPH.getText() + "\n\n" + value);
								insertModuleText(listInsInfo.getWidgetText(),
										tbPH);
							}
						}
					});
					pastHistTable.setWidget(row++, 1, listInsInfo);
				}
			}
		}
		pastHistTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		pastHisPanel.add(pastHistTable);
	}

	private void createFamiliyHistoryTab() {
		FlexTable famHistTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Family History"))
			loopCountMax = sectionsFieldMap.get("Sections#Family History")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Family History"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#Family History");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("FH"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbFH = new Label("FH");
				tbFH = new TextArea();
				tbFH.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotesfh")) {
					tbFH.setText(templateValuesMap.get("pnotesfh"));
				} else if (templateValuesMap.containsKey("pnotestfh")) {
					tbFH.setText(templateValuesMap.get("pnotestfh"));
				}
				famHistTable.setWidget(row, 0, lbFH);
				famHistTable.setWidget(row++, 1, tbFH);
			}
		}
		famHistTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		famHisPanel.add(famHistTable);
	}

	private void createSocialHistoryTab() {
		Label lbInfo = new Label("SOCIAL HISTORY(Select if Applies)");
		lbInfo.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
		socHisPanel.add(lbInfo);
		socHisPanel.setSpacing(10);
		final FlexTable socialHistTable = new FlexTable();
		int shRowCount = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Social History"))
			loopCountMax = sectionsFieldMap.get("Sections#Social History")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Social History"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> shSecList = sectionsFieldMap
				.get("Sections#Social History");
		for (int i = 0; i < loopCountMax; i++) {
			if ((shSecList != null && shSecList.get(i).equals("Alcohol"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbAlcohol = new CheckBox("Alcohol");
				cbAlcohol.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbAlcohol = new TextBox();
				tbAlcohol.setWidth("400px");
				tbAlcohol.setEnabled(false);
				cbAlcohol.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbAlcohol.getValue())
							tbAlcohol.setEnabled(true);
						else
							tbAlcohol.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbAlcohol);
				socialHistTable.setWidget(shRowCount, 1, tbAlcohol);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshalcoholstatus")) {
					if (templateValuesMap.get("pnotesshalcoholstatus").equals(
							"1")) {
						cbAlcohol.setValue(true);
						tbAlcohol.setEnabled(true);
						tbAlcohol.setText(templateValuesMap
								.get("pnotesshalcoholcmnt"));
					}
				} else if (templateValuesMap
						.containsKey("pnotestshalcoholstatus")) {
					if (templateValuesMap.get("pnotestshalcoholstatus").equals(
							"1")) {
						cbAlcohol.setValue(true);
						tbAlcohol.setEnabled(true);
						tbAlcohol.setText(templateValuesMap
								.get("pnotestshalcoholcmnt"));
					}
				}
			}

			if ((shSecList != null && shSecList.get(i).equals("Tobacco"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbTobacco = new CheckBox("Tobacco");
				cbTobacco.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbTobacco = new TextBox();
				tbTobacco.setWidth("400px");
				tbTobacco.setEnabled(false);
				cbCounseledCessation = new CheckBox("Counseled about cessation");
				cbCounseledCessation.setEnabled(false);
				cbTobacco.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbTobacco.getValue()) {
							tbTobacco.setEnabled(true);
							cbCounseledCessation.setEnabled(true);
						} else {
							tbTobacco.setEnabled(false);
							cbCounseledCessation.setEnabled(false);
						}

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbTobacco);
				socialHistTable.setWidget(shRowCount++, 1, tbTobacco);
				socialHistTable.setWidget(shRowCount, 1, cbCounseledCessation);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshtobaccostatus")) {
					if (templateValuesMap.get("pnotesshtobaccostatus").equals(
							"1")) {
						cbTobacco.setValue(true);
						tbTobacco.setEnabled(true);
						cbCounseledCessation.setEnabled(true);
						tbTobacco.setText(templateValuesMap
								.get("pnotesshtobaccocmnt"));
						if (templateValuesMap.containsKey("pnotesshtcounseled")) {
							if (templateValuesMap.get("pnotesshtcounseled")
									.equals("1")) {
								cbCounseledCessation.setValue(true);
							}
						}
					}
				} else if (templateValuesMap
						.containsKey("pnotestshtobaccostatus")) {
					if (templateValuesMap.get("pnotestshtobaccostatus").equals(
							"1")) {
						cbTobacco.setValue(true);
						tbTobacco.setEnabled(true);
						tbTobacco.setText(templateValuesMap
								.get("pnotestshtobaccocmnt"));
						cbCounseledCessation.setEnabled(true);
						if (templateValuesMap
								.containsKey("pnotestshtcounseled")) {
							if (templateValuesMap.get("pnotestshtcounseled")
									.equals("1")) {
								cbCounseledCessation.setValue(true);
							}
						}
					}
				}

			}

			if ((shSecList != null && shSecList.get(i).equals("Illicit drugs"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbIllDrugs = new CheckBox("Illicit drugs");
				cbIllDrugs.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbIllDrugs = new TextBox();
				tbIllDrugs.setWidth("400px");
				tbIllDrugs.setEnabled(false);
				cbIllDrugs.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbIllDrugs.getValue())
							tbIllDrugs.setEnabled(true);
						else
							tbIllDrugs.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbIllDrugs);
				socialHistTable.setWidget(shRowCount, 1, tbIllDrugs);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshilctdrugstatus")) {
					if (templateValuesMap.get("pnotesshilctdrugstatus").equals(
							"1")) {
						cbIllDrugs.setValue(true);
						tbIllDrugs.setEnabled(true);
						tbIllDrugs.setText(templateValuesMap
								.get("pnotesshilctdrugscmnt"));
					}
				} else if (templateValuesMap
						.containsKey("pnotestshilctdrugstatus")) {
					if (templateValuesMap.get("pnotestshilctdrugstatus")
							.equals("1")) {
						cbIllDrugs.setValue(true);
						tbIllDrugs.setEnabled(true);
						tbIllDrugs.setText(templateValuesMap
								.get("pnotestshilctdrugscmnt"));
					}
				}
			}

			if ((shSecList != null && shSecList.get(i).equals("Lives with"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbLivesWith = new CheckBox("Lives with");
				cbLivesWith.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbLivesWith = new TextBox();
				tbLivesWith.setWidth("400px");
				tbLivesWith.setEnabled(false);
				cbLivesWith.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbLivesWith.getValue())
							tbLivesWith.setEnabled(true);
						else
							tbLivesWith.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbLivesWith);
				socialHistTable.setWidget(shRowCount, 1, tbLivesWith);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshliveswithstatus")) {
					if (templateValuesMap.get("pnotesshliveswithstatus")
							.equals("1")) {
						cbLivesWith.setValue(true);
						tbLivesWith.setEnabled(true);
						tbLivesWith.setText(templateValuesMap
								.get("pnotesshliveswithcmnt"));
					}
				} else if (templateValuesMap
						.containsKey("pnotestshliveswithstatus")) {
					if (templateValuesMap.get("pnotestshliveswithstatus")
							.equals("1")) {
						cbLivesWith.setValue(true);
						tbLivesWith.setEnabled(true);
						tbLivesWith.setText(templateValuesMap
								.get("pnotestshliveswithcmnt"));
					}
				}
			}

			if ((shSecList != null && shSecList.get(i).equals("Occupation"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final Label cbOccupation = new Label("Occupation");
				cbOccupation.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbOccupation = new TextBox();
				tbOccupation.setWidth("400px");
				socialHistTable.setWidget(shRowCount, 0, cbOccupation);
				socialHistTable.setWidget(shRowCount, 1, tbOccupation);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshoccupation")) {
					tbOccupation.setText(templateValuesMap
							.get("pnotesshoccupation"));
				} else if (templateValuesMap.containsKey("pnotestshoccupation")) {
					tbOccupation.setText(templateValuesMap
							.get("pnotestshoccupation"));
				}
			}

			if ((shSecList != null && shSecList.get(i).equals(
					"HIV risk factors"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbHivRiskFactor = new CheckBox("HIV risk factors");
				cbHivRiskFactor
						.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbHivRiskFactor = new TextBox();
				tbHivRiskFactor.setWidth("400px");
				tbHivRiskFactor.setEnabled(false);
				cbHivRiskFactor.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbHivRiskFactor.getValue())
							tbHivRiskFactor.setEnabled(true);
						else
							tbHivRiskFactor.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbHivRiskFactor);
				socialHistTable.setWidget(shRowCount, 1, tbHivRiskFactor);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshivrskfacstatus")) {
					if (templateValuesMap.get("pnotesshivrskfacstatus").equals(
							"1")) {
						cbHivRiskFactor.setValue(true);
						tbHivRiskFactor.setEnabled(true);
						tbHivRiskFactor.setText(templateValuesMap
								.get("pnotesshivrskfaccmnt"));
					}
				} else if (templateValuesMap
						.containsKey("pnotestshivrskfacstatus")) {
					if (templateValuesMap.get("pnotestshivrskfacstatus")
							.equals("1")) {
						cbHivRiskFactor.setValue(true);
						tbHivRiskFactor.setEnabled(true);
						tbHivRiskFactor.setText(templateValuesMap
								.get("pnotestshivrskfaccmnt"));
					}
				}
			}
			if ((shSecList != null && shSecList.get(i).equals("Travel"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbTravel = new CheckBox("Travel");
				cbTravel.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbTravel = new TextBox();
				tbTravel.setWidth("400px");
				tbTravel.setEnabled(false);
				cbTravel.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbTravel.getValue())
							tbTravel.setEnabled(true);
						else
							tbTravel.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbTravel);
				socialHistTable.setWidget(shRowCount, 1, tbTravel);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshtravelstatus")) {
					if (templateValuesMap.get("pnotesshtravelstatus").equals(
							"1")) {
						cbTravel.setValue(true);
						tbTravel.setEnabled(true);
						tbTravel.setText(templateValuesMap
								.get("pnotesshtravelcmnt"));
					}
				} else if (templateValuesMap
						.containsKey("pnotestshtravelstatus")) {
					if (templateValuesMap.get("pnotestshtravelstatus").equals(
							"1")) {
						cbTravel.setValue(true);
						tbTravel.setEnabled(true);
						tbTravel.setText(templateValuesMap
								.get("pnotestshtravelcmnt"));
					}
				}
			}
			if ((shSecList != null && shSecList.get(i).equals("Pets"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbPets = new CheckBox("Pets");
				cbPets.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbPets = new TextBox();
				tbPets.setWidth("400px");
				tbPets.setEnabled(false);
				cbPets.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbPets.getValue())
							tbPets.setEnabled(true);
						else
							tbPets.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbPets);
				socialHistTable.setWidget(shRowCount, 1, tbPets);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshpetsstatus")) {
					if (templateValuesMap.get("pnotesshpetsstatus").equals("1")) {
						cbPets.setValue(true);
						tbPets.setEnabled(true);
						tbPets.setText(templateValuesMap
								.get("pnotesshpetscmnt"));
					}
				} else if (templateValuesMap.containsKey("pnotestshpetsstatus")) {
					if (templateValuesMap.get("pnotestshpetsstatus")
							.equals("1")) {
						cbPets.setValue(true);
						tbPets.setEnabled(true);
						tbPets.setText(templateValuesMap
								.get("pnotestshpetscmnt"));
					}
				}
			}
			if ((shSecList != null && shSecList.get(i).equals("Hobbies"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				cbHobbies = new CheckBox("Hobbies");
				cbHobbies.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				tbHobbies = new TextBox();
				tbHobbies.setWidth("400px");
				tbHobbies.setEnabled(false);
				cbHobbies.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbHobbies.getValue())
							tbHobbies.setEnabled(true);
						else
							tbHobbies.setEnabled(false);

					}

				});
				socialHistTable.setWidget(shRowCount, 0, cbHobbies);
				socialHistTable.setWidget(shRowCount, 1, tbHobbies);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshhobbiesstatus")) {
					if (templateValuesMap.get("pnotesshhobbiesstatus").equals(
							"1")) {
						cbHobbies.setValue(true);
						tbHobbies.setEnabled(true);
						tbHobbies.setText(templateValuesMap
								.get("pnotesshhobbiescmnt"));
					}
				} else if (templateValuesMap
						.containsKey("pnotestshhobbiesstatus")) {
					if (templateValuesMap.get("pnotestshhobbiesstatus").equals(
							"1")) {
						cbHobbies.setValue(true);
						tbHobbies.setEnabled(true);
						tbHobbies.setText(templateValuesMap
								.get("pnotestshhobbiescmnt"));
					}
				}
			}
			if ((shSecList != null && shSecList.get(i).equals("Housing"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				final Label lbHousing = new Label("Housing");
				lbHousing.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				radHousing = new CustomRadioButtonGroup("housing");
				radHousing.addItem("Urban house", "1");
				radHousing.addItem("Trailer", "2");
				radHousing.addItem("Farm", "3");
				radHousing.addItem("Homeless", "4");
				socialHistTable.setWidget(shRowCount, 0, lbHousing);
				socialHistTable.setWidget(shRowCount, 1, radHousing);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
				socialHistTable.getFlexCellFormatter().setVerticalAlignment(
						shRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
				shRowCount++;
				if (templateValuesMap.containsKey("pnotesshhousing")) {
					radHousing.setWidgetValue(templateValuesMap
							.get("pnotesshhousing"));
				} else if (templateValuesMap.containsKey("pnotestshhousing")) {
					radHousing.setWidgetValue(templateValuesMap
							.get("pnotestshhousing"));
				}
			}
		}
		socialHistTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		socHisPanel.add(socialHistTable);
	}

	private void createExamTab() {
		examPanel.setSpacing(10);
		FlexTable examTable = new FlexTable();
		boolean isBillables = false;
		final String textWidth = "" + (int) (Window.getClientWidth() / 3);
		final String radWidth = "" + (int) (Window.getClientWidth() / 6);
		final String labelWidth = "" + (int) (Window.getClientWidth() / 5);
		final int maxbillables = Integer.parseInt(CurrentState
				.getSystemConfig("max_billable"));
		HashMap<String, HashMap<String, String>> billMap = null;
		if (templateValuesMap.get("pnotestbillable") != null
				&& !templateValuesMap.get("pnotestbillable").equals("")) {
			isBillables = true;
			billMap = (HashMap<String, HashMap<String, String>>) JsonUtil
					.shoehornJson(JSONParser.parse(templateValuesMap
							.get("pnotestbillable")),
							"HashMap<String,HashMap<String,String>>");
		} else if (templateValuesMap.get("pnotesbillable") != null
				&& !templateValuesMap.get("pnotesbillable").equals("")) {
			isBillables = true;
			billMap = (HashMap<String, HashMap<String, String>>) JsonUtil
					.shoehornJson(JSONParser.parse(templateValuesMap
							.get("pnotesbillable")),
							"HashMap<String,HashMap<String,String>>");
		}
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Exam"))
			loopCountMax = sectionsFieldMap.get("Sections#Exam").size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Exam"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#Exam");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("Head"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbHeadExam = new Label("Head");
				lbHeadExam.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbHeadExam);
				//headfTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(rightPanel);
				examPanel.add(hp);
				hp.setCellWidth(rightPanel, "100%");
				Label lbfreeform = new Label("Free Form Entry");
				lbfreeform
						.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

				tbHeadFreeForm = new TextArea();
				tbHeadFreeForm.setWidth(textWidth);
				HorizontalPanel freeHp = new HorizontalPanel();
				freeHp.setWidth("80%");
				freeHp.setSpacing(2);
				freeHp.add(lbfreeform);
				freeHp.add(tbHeadFreeForm);
				freeHp.setCellWidth(tbHeadFreeForm, "80%");
				rightPanel.add(freeHp);
				if (templateValuesMap
						.containsKey("pnotespeheadfreecmnt")) {
					tbHeadFreeForm.setText(templateValuesMap
							.get("pnotespeheadfreecmnt"));
				} else if (templateValuesMap
						.containsKey("pnotestpeheadfreecmnt")) {
					tbHeadFreeForm.setText(templateValuesMap
							.get("pnotestpeheadfreecmnt"));
				}

				VerticalPanel billPanel = new VerticalPanel();
				billPanel.setWidth("100%");
				billPanel.setSpacing(2);
				final BillInfoWidget biw = new BillInfoWidget();
				final CheckBox cbHeadExBill = new CheckBox("Procedure");
				cbHeadExBill
						.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				billPanel.add(cbHeadExBill);
				billPanel.add(biw);
				cbHeadExBill.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						if (cbHeadExBill.getValue()) {
							if (maxbillables == billingFieldsWidgetsMap
									.size()) {
								Window.alert("Only " + maxbillables
										+ " procedures can be created...");
								cbHeadExBill.setValue(false);
							} else {
								billingFieldsWidgetsMap.put("pnotespehead",
										biw);
								biw.setVisible(true);
							}
						} else {
							billingFieldsWidgetsMap.remove("pnotespehead");
							biw.setVisible(false);
						}
					}
				});
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespehead")) {
						HashMap<String, String> m = billMap.get("pnotespehead");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespehead", biw);
						cbHeadExBill.setValue(true);
						
					}
				}
			}
			if ((secList != null && secList.get(i).equals("Eyes"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {

				int eyeRowCount = 0;
				Label lbEyes = new Label("Eyes");
				lbEyes.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbEyes);
				final FlexTable eyeTable = new FlexTable();
				eyeTable.getElement().getStyle().setMarginLeft(30, Unit.PX);

				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(eyeTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int eyesLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Eyes"))
					eyesLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Eyes").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#Eyes"))
					eyesLoopCountMax = 0;
				else
					eyesLoopCountMax = 1;
				List<String> eyesSecList = sectionsFieldMap
						.get("Sections#Exam#Eyes");
				for (int j = 0; j < eyesLoopCountMax; j++) {

					if ((eyesSecList != null && eyesSecList.get(j).equals(
							"Conjunctivae_lids_pupils & irises"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbCLPI = new CheckBox(
								"Conjunctivae, lids, pupils & irises");
						cbCLPI
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radClpi = new CustomRadioButtonGroup("clpi");

						tbClpi = new TextArea();
						tbClpi.setEnabled(false);
						tbClpi.setVisible(false);
						tbClpi.setWidth(textWidth);
						radClpi.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbClpi.setVisible(false);
								cbCLPI.setValue(true, true);
							}
						});
						radClpi.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbClpi.setVisible(true);
								cbCLPI.setValue(true, true);
							}
						});
						radClpi.setEnable(false);
						cbCLPI
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbCLPI.getValue()) {
											radClpi.setEnable(true);
											tbClpi.setEnabled(true);
										} else {
											radClpi.setEnable(false);
											tbClpi.setEnabled(false);
										}
									}

								});
						eyeTable.setWidget(eyeRowCount, 0, cbCLPI);
						eyeTable.setWidget(eyeRowCount, 1, radClpi);
						eyeTable.setWidget(eyeRowCount + 1, 0, tbClpi);
						eyeTable.getFlexCellFormatter().setColSpan(
								eyeRowCount + 1, 0, 2);
						eyeTable.getFlexCellFormatter().setVerticalAlignment(
								eyeRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						eyeTable.getFlexCellFormatter().setVerticalAlignment(
								eyeRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						eyeTable.getFlexCellFormatter().setWidth(eyeRowCount,
								0, labelWidth);
						eyeTable.getFlexCellFormatter().setWidth(eyeRowCount,
								1, radWidth);

						eyeRowCount = eyeRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeeyeclpistatus")) {
							radClpi.setWidgetValue(templateValuesMap
									.get("pnotespeeyeclpistatus"), true);
							tbClpi.setText(templateValuesMap
									.get("pnotespeeyeclpicmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeeyeclpistatus")) {
							radClpi.setWidgetValue(templateValuesMap
									.get("pnotestpeeyeclpistatus"), true);
							tbClpi.setText(templateValuesMap
									.get("pnotestpeeyeclpicmnt"));
						}
					}

					if ((eyesSecList != null && eyesSecList.get(j).equals(
							"Fundi"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						Label lbFundi = new Label("Fundi:");
						lbFundi
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						eyeTable.setWidget(eyeRowCount, 0, lbFundi);
						eyeRowCount++;

						int fundiLoopCountMax = 0;
						if (sectionsFieldMap.containsKey("Sections")
								&& sectionsFieldMap
										.containsKey("Sections#Exam#Eyes#Fundi"))
							fundiLoopCountMax = sectionsFieldMap.get(
									"Sections#Exam#Eyes#Fundi").size();
						else if (sectionsFieldMap.containsKey("Sections")
								&& !sectionsFieldMap
										.containsKey("Sections#Exam#Eyes#Fundi"))
							fundiLoopCountMax = 0;
						else
							fundiLoopCountMax = 1;
						List<String> fundiSecList = sectionsFieldMap
								.get("Sections#Exam#Eyes#Fundi");
						for (int k = 0; k < fundiLoopCountMax; k++) {

							if ((fundiSecList != null && fundiSecList.get(k)
									.equals("Disc edges sharp"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbDiscEdgeSharp = new CheckBox(
										"Disc edges sharp");
								cbDiscEdgeSharp
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbDiscEdgeSharp.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radDiscEdgeSharp = new CustomRadioButtonGroup(
										"des");
								tbDiscEdgeSharp = new TextArea();
								tbDiscEdgeSharp.setWidth(textWidth);
								tbDiscEdgeSharp.setVisible(false);
								radDiscEdgeSharp.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbDiscEdgeSharp
														.setVisible(false);
												cbDiscEdgeSharp.setValue(true,
														true);

											}
										});
								radDiscEdgeSharp.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbDiscEdgeSharp
														.setVisible(true);
												cbDiscEdgeSharp.setValue(true,
														true);
											}
										});
								radDiscEdgeSharp.setEnable(false);
								tbDiscEdgeSharp.setEnabled(false);
								cbDiscEdgeSharp
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbDiscEdgeSharp.getValue()) {
													radDiscEdgeSharp
															.setEnable(true);
													tbDiscEdgeSharp
															.setEnabled(true);
												} else {
													radDiscEdgeSharp
															.setEnable(false);
													tbDiscEdgeSharp
															.setEnabled(false);
												}
											}

										});
								eyeTable.setWidget(eyeRowCount, 0,
										cbDiscEdgeSharp);
								eyeTable.setWidget(eyeRowCount, 1,
										radDiscEdgeSharp);
								eyeTable.setWidget(eyeRowCount + 1, 0,
										tbDiscEdgeSharp);
								eyeTable.getFlexCellFormatter().setColSpan(
										eyeRowCount + 1, 0, 2);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 0, labelWidth);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 1, radWidth);
								eyeRowCount = eyeRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespeeyedesstatus")) {
									radDiscEdgeSharp
											.setWidgetValue(
													templateValuesMap
															.get("pnotespeeyedesstatus"),
													true);
									tbDiscEdgeSharp.setText(templateValuesMap
											.get("pnotespeeyedescmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpeeyedesstatus")) {
									radDiscEdgeSharp
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpeeyedesstatus"),
													true);
									tbDiscEdgeSharp.setText(templateValuesMap
											.get("pnotestpeeyedescmnt"));
								}
							}
							if ((fundiSecList != null && fundiSecList.get(k)
									.equals("Venous pulses seen"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbVenPul = new CheckBox("Venous pulses seen");
								cbVenPul
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbVenPul.getElement().getStyle().setMarginLeft(
										50, Unit.PX);
								radVenPul = new CustomRadioButtonGroup("vps");
								tbVenPul = new TextArea();
								tbVenPul.setWidth(textWidth);
								tbVenPul.setVisible(false);
								radVenPul.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbVenPul.setVisible(false);
										cbVenPul.setValue(true, true);
									}
								});
								radVenPul.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbVenPul.setVisible(true);
												cbVenPul.setValue(true, true);
											}
										});
								radVenPul.setEnable(false);
								tbVenPul.setEnabled(false);
								cbVenPul
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbVenPul.getValue()) {
													radVenPul.setEnable(true);
													tbVenPul.setEnabled(true);
												} else {
													radVenPul.setEnable(false);
													tbVenPul.setEnabled(false);
												}
											}

										});
								eyeTable.setWidget(eyeRowCount, 0, cbVenPul);
								eyeTable.setWidget(eyeRowCount, 1, radVenPul);
								eyeTable
										.setWidget(eyeRowCount + 1, 0, tbVenPul);
								eyeTable.getFlexCellFormatter().setColSpan(
										eyeRowCount + 1, 0, 2);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 0, labelWidth);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 1, radWidth);
								eyeRowCount = eyeRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespeeyevpsstatus")) {
									radVenPul.setWidgetValue(templateValuesMap
											.get("pnotespeeyevpsstatus"), true);
									tbVenPul.setText(templateValuesMap
											.get("pnotespeeyevpscmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpeeyevpsstatus")) {
									radVenPul
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpeeyevpsstatus"),
													true);
									tbVenPul.setText(templateValuesMap
											.get("pnotestpeeyevpscmnt"));
								}
							}

							if ((fundiSecList != null && fundiSecList.get(k)
									.equals("A-V nicking"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbAVNicking = new CheckBox("A-V nicking");
								cbAVNicking
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbAVNicking.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radAVNicking = new CustomRadioButtonGroup("avn");
								tbAVNicking = new TextArea();
								tbAVNicking.setVisible(false);
								tbAVNicking.setWidth(textWidth);
								radAVNicking.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbAVNicking.setVisible(false);
												cbAVNicking
														.setValue(true, true);
											}
										});
								radAVNicking.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbAVNicking.setVisible(true);
												cbAVNicking
														.setValue(true, true);
											}
										});
								radAVNicking.setEnable(false);
								tbAVNicking.setEnabled(false);
								cbAVNicking
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbAVNicking.getValue()) {
													radAVNicking
															.setEnable(true);
													tbAVNicking
															.setEnabled(true);
												} else {
													radAVNicking
															.setEnable(false);
													tbAVNicking
															.setEnabled(false);
												}
											}

										});
								eyeTable.setWidget(eyeRowCount, 0, cbAVNicking);
								eyeTable
										.setWidget(eyeRowCount, 1, radAVNicking);
								eyeTable.setWidget(eyeRowCount + 1, 0,
										tbAVNicking);
								eyeTable.getFlexCellFormatter().setColSpan(
										eyeRowCount + 1, 0, 2);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 0, labelWidth);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 1, radWidth);
								eyeRowCount = eyeRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespeeyeavnstatus")) {
									radAVNicking
											.setWidgetValue(
													templateValuesMap
															.get("pnotespeeyeavnstatus"),
													true);
									tbAVNicking.setText(templateValuesMap
											.get("pnotespeeyeavncmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpeeyeavnstatus")) {
									radAVNicking
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpeeyeavnstatus"),
													true);
									tbAVNicking.setText(templateValuesMap
											.get("pnotestpeeyeavncmnt"));
								}
							}

							if ((fundiSecList != null && fundiSecList.get(k)
									.equals("Hemorrhages"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbHemorrhages = new CheckBox("Hemorrhages");
								cbHemorrhages
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbHemorrhages.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radHemorrhages = new CustomRadioButtonGroup(
										"hom");
								tbHemorrhages = new TextArea();
								tbHemorrhages.setVisible(false);
								tbHemorrhages.setWidth(textWidth);
								radHemorrhages.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbHemorrhages.setVisible(false);
												cbHemorrhages.setValue(true,
														true);
											}
										});
								radHemorrhages.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbHemorrhages.setVisible(true);
												cbHemorrhages.setValue(true,
														true);
											}
										});
								radHemorrhages.setEnable(false);
								tbHemorrhages.setEnabled(false);
								cbHemorrhages
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbHemorrhages.getValue()) {
													radHemorrhages
															.setEnable(true);
													tbHemorrhages
															.setEnabled(true);
												} else {
													radHemorrhages
															.setEnable(false);
													tbHemorrhages
															.setEnabled(false);
												}
											}

										});
								eyeTable.setWidget(eyeRowCount, 0,
										cbHemorrhages);
								eyeTable.setWidget(eyeRowCount, 1,
										radHemorrhages);
								eyeTable.setWidget(eyeRowCount + 1, 0,
										tbHemorrhages);
								eyeTable.getFlexCellFormatter().setColSpan(
										eyeRowCount + 1, 0, 2);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 0, labelWidth);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 1, radWidth);
								eyeRowCount = eyeRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespeeyehemstatus")) {
									radHemorrhages
											.setWidgetValue(
													templateValuesMap
															.get("pnotespeeyehemstatus"),
													true);
									tbHemorrhages.setText(templateValuesMap
											.get("pnotespeeyehemcmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpeeyehemstatus")) {
									radHemorrhages
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpeeyehemstatus"),
													true);
									tbHemorrhages.setText(templateValuesMap
											.get("pnotestpeeyehemcmnt"));
								}
							}

							if ((fundiSecList != null && fundiSecList.get(k)
									.equals("Exudates"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbExudates = new CheckBox("Exudates");
								cbExudates
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbExudates.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radExudates = new CustomRadioButtonGroup("exu");
								tbExudates = new TextArea();
								tbExudates.setVisible(false);
								tbExudates.setWidth(textWidth);
								radExudates.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbExudates.setVisible(false);
												cbExudates.setValue(true, true);
											}
										});
								radExudates.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbExudates.setVisible(true);
												cbExudates.setValue(true, true);
											}
										});
								radExudates.setEnable(false);
								tbExudates.setEnabled(false);
								cbExudates
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbExudates.getValue()) {
													radExudates.setEnable(true);
													tbExudates.setEnabled(true);
												} else {
													radExudates
															.setEnable(false);
													tbExudates
															.setEnabled(false);
												}
											}

										});
								eyeTable.setWidget(eyeRowCount, 0, cbExudates);
								eyeTable.setWidget(eyeRowCount, 1, radExudates);
								eyeTable.setWidget(eyeRowCount + 1, 0,
										tbExudates);
								eyeTable.getFlexCellFormatter().setColSpan(
										eyeRowCount + 1, 0, 2);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter()
										.setVerticalAlignment(eyeRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 0, labelWidth);
								eyeTable.getFlexCellFormatter().setWidth(
										eyeRowCount, 1, radWidth);
								eyeRowCount = eyeRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespeeyeexustatus")) {
									radExudates
											.setWidgetValue(
													templateValuesMap
															.get("pnotespeeyeexustatus"),
													true);
									tbExudates.setText(templateValuesMap
											.get("pnotespeeyeexucmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpeeyeexustatus")) {
									radExudates
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpeeyeexustatus"),
													true);
									tbExudates.setText(templateValuesMap
											.get("pnotestpeeyeexucmnt"));
								}
							}
						}
					}

					if ((eyesSecList != null && eyesSecList.get(j).equals(
							"Cup:disc ratio"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbCupDiscRatio = new CheckBox("Cup:disc ratio");
						cbCupDiscRatio
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						tbCupDiscRatio = new TextBox();
						eyeTable.setWidget(eyeRowCount, 0, cbCupDiscRatio);
						eyeTable.setWidget(eyeRowCount, 1, tbCupDiscRatio);
						eyeTable.getFlexCellFormatter().setVerticalAlignment(
								eyeRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						eyeTable.getFlexCellFormatter().setVerticalAlignment(
								eyeRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						eyeRowCount++;
						tbCupDiscRatio.setEnabled(false);
						cbCupDiscRatio
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbCupDiscRatio.getValue()) {
											tbCupDiscRatio.setEnabled(true);
										} else {
											tbCupDiscRatio.setEnabled(false);
										}
									}

								});
						if (templateValuesMap
								.containsKey("pnotespeeyecupdiscratio")) {
							tbCupDiscRatio.setText(templateValuesMap
									.get("pnotespeeyecupdiscratio"));
							cbCupDiscRatio.setValue(true, true);
						} else if (templateValuesMap
								.containsKey("pnotestpeeyecupdiscratio")) {
							tbCupDiscRatio.setText(templateValuesMap
									.get("pnotestpeeyecupdiscratio"));
							cbCupDiscRatio.setValue(true, true);
						}
					}
					if ((eyesSecList != null && eyesSecList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(eyesSecList!=null && eyesSecList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						
						tbEyeFreeForm = new TextArea();
						tbEyeFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbEyeFreeForm);
						freeHp.setCellWidth(tbEyeFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespeeyefreecmnt")) {
							tbEyeFreeForm.setText(templateValuesMap
									.get("pnotespeeyefreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeeyefreecmnt")) {
							tbEyeFreeForm.setText(templateValuesMap
									.get("pnotestpeeyefreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setWidth("100%");
					billPanel.setSpacing(2);
					rightPanel.add(billPanel);
					cbEyesExBill = new CheckBox("Procedure");
					cbEyesExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					// examTable.setWidget(row, 2, cbEyesExBill);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbEyesExBill);
					billPanel.add(biw);
					// examPanel.add(billPanel);
					// examTable.setWidget(row, 6, biw);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespeeyes")) {
						HashMap<String, String> m = billMap.get("pnotespeeyes");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespeeyes", biw);
						cbEyesExBill.setValue(true);
					}

					cbEyesExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbEyesExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbEyesExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespeeyes",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespeeyes");
								biw.setVisible(false);
							}
						}
					});
				}
			}
			
			if ((secList != null && secList.get(i).equals("ENT"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int entRowCount = 0;
				Label lbEnt = new Label("ENT");
				lbEnt.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbEnt);
				final FlexTable entTable = new FlexTable();
				entTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(entTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);
				int entLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#ENT"))
					entLoopCountMax = sectionsFieldMap.get("Sections#Exam#ENT")
							.size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#ENT"))
					entLoopCountMax = 0;
				else
					entLoopCountMax = 1;
				List<String> entSecList = sectionsFieldMap
						.get("Sections#Exam#ENT");
				for (int j = 0; j < entLoopCountMax; j++) {
					if ((entSecList != null && entSecList.get(j).equals(
							"External canals_TMs"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbExtCanTms = new CheckBox("External canals, TMs");
						cbExtCanTms
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radExtCanTms = new CustomRadioButtonGroup("et");
						tbExtCanTms = new TextArea();
						tbExtCanTms.setVisible(false);
						tbExtCanTms.setWidth(textWidth);
						radExtCanTms.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbExtCanTms.setVisible(false);
								cbExtCanTms.setValue(true, true);
							}
						});
						radExtCanTms.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbExtCanTms.setVisible(true);
								cbExtCanTms.setValue(true, true);

							}
						});
						radExtCanTms.setEnable(false);
						tbExtCanTms.setEnabled(false);
						cbExtCanTms
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbExtCanTms.getValue()) {
											radExtCanTms.setEnable(true);
											tbExtCanTms.setEnabled(true);
										} else {
											radExtCanTms.setEnable(false);
											tbExtCanTms.setEnabled(false);
										}
									}

								});
						entTable.setWidget(entRowCount, 0, cbExtCanTms);
						entTable.setWidget(entRowCount, 1, radExtCanTms);
						entTable.setWidget(entRowCount + 1, 0, tbExtCanTms);
						entTable.getFlexCellFormatter().setColSpan(
								entRowCount + 1, 0, 2);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								0, labelWidth);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								1, radWidth);
						entRowCount = entRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeentectstatus")) {
							radExtCanTms.setWidgetValue(templateValuesMap
									.get("pnotespeentectstatus"), true);
							tbExtCanTms.setText(templateValuesMap
									.get("pnotespeentectcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeentectstatus")) {
							radExtCanTms.setWidgetValue(templateValuesMap
									.get("pnotestpeentectstatus"), true);
							tbExtCanTms.setText(templateValuesMap
									.get("pnotestpeentectcmnt"));
						}
					}

					if ((entSecList != null && entSecList.get(j).equals(
							"Nasal mucosa_septum"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbNMS = new CheckBox("Nasal mucosa, septum");
						cbNMS
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radNMS = new CustomRadioButtonGroup("nms");
						tbNMS = new TextArea();
						tbNMS.setVisible(false);
						tbNMS.setWidth(textWidth);
						radNMS.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbNMS.setVisible(false);
								cbNMS.setValue(true, true);
							}
						});
						radNMS.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbNMS.setVisible(true);
								cbNMS.setValue(true, true);
							}
						});
						radNMS.setEnable(false);
						tbNMS.setEnabled(false);
						cbNMS
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbNMS.getValue()) {
											radNMS.setEnable(true);
											tbNMS.setEnabled(true);
										} else {
											radNMS.setEnable(false);
											tbNMS.setEnabled(false);
										}
									}

								});
						entTable.setWidget(entRowCount, 0, cbNMS);
						entTable.setWidget(entRowCount, 1, radNMS);
						entTable.setWidget(entRowCount + 1, 0, tbNMS);
						entTable.getFlexCellFormatter().setColSpan(
								entRowCount + 1, 0, 2);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								0, labelWidth);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								1, radWidth);
						entRowCount = entRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeentnmsstatus")) {
							radNMS.setWidgetValue(templateValuesMap
									.get("pnotespeentnmsstatus"), true);
							tbNMS.setText(templateValuesMap
									.get("pnotespeentnmscmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeentnmsstatus")) {
							radNMS.setWidgetValue(templateValuesMap
									.get("pnotestpeentnmsstatus"), true);
							tbNMS.setText(templateValuesMap
									.get("pnotestpeentnmscmnt"));
						}
					}

					if ((entSecList != null && entSecList.get(j).equals(
							"Lips_gums_teeth"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbLGT = new CheckBox("Lips, gums, teeth");
						cbLGT
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radLGT = new CustomRadioButtonGroup("lgt");
						tbLGT = new TextArea();
						tbLGT.setVisible(false);
						tbLGT.setWidth(textWidth);
						radLGT.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbLGT.setVisible(false);
								cbLGT.setValue(true, true);
							}
						});
						radLGT.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbLGT.setVisible(true);
								cbLGT.setValue(true, true);
							}
						});
						radLGT.setEnable(false);
						tbLGT.setEnabled(false);
						cbLGT
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbLGT.getValue()) {
											radLGT.setEnable(true);
											tbLGT.setEnabled(true);
										} else {
											radLGT.setEnable(false);
											tbLGT.setEnabled(false);
										}
									}

								});
						entTable.setWidget(entRowCount, 0, cbLGT);
						entTable.setWidget(entRowCount, 1, radLGT);
						entTable.setWidget(entRowCount + 1, 0, tbLGT);
						entTable.getFlexCellFormatter().setColSpan(
								entRowCount + 1, 0, 2);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								0, labelWidth);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								1, radWidth);
						entRowCount = entRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeentlgtstatus")) {
							radLGT.setWidgetValue(templateValuesMap
									.get("pnotespeentlgtstatus"), true);
							tbLGT.setText(templateValuesMap
									.get("pnotespeentlgtcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeentlgtstatus")) {
							radLGT.setWidgetValue(templateValuesMap
									.get("pnotestpeentlgtstatus"), true);
							tbLGT.setText(templateValuesMap
									.get("pnotestpeentlgtcmnt"));
						}
					}

					if ((entSecList != null && entSecList.get(j).equals(
							"Oropharynx_mucosa_salivary glands"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbOMS = new CheckBox(
								"Oropharynx, mucosa, salivary glands");
						cbOMS
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radOMS = new CustomRadioButtonGroup("oms");
						tbOMS = new TextArea();
						tbOMS.setVisible(false);
						tbOMS.setWidth(textWidth);
						radOMS.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbOMS.setVisible(false);
								cbOMS.setValue(true, true);
							}
						});
						radOMS.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbOMS.setVisible(true);
								cbOMS.setValue(true, true);
							}
						});
						radOMS.setEnable(false);
						tbOMS.setEnabled(false);
						cbOMS
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbOMS.getValue()) {
											radOMS.setEnable(true);
											tbOMS.setEnabled(true);
										} else {
											radOMS.setEnable(false);
											tbOMS.setEnabled(false);
										}
									}

								});
						entTable.setWidget(entRowCount, 0, cbOMS);
						entTable.setWidget(entRowCount, 1, radOMS);
						entTable.setWidget(entRowCount + 1, 0, tbOMS);
						entTable.getFlexCellFormatter().setColSpan(
								entRowCount + 1, 0, 2);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								0, labelWidth);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								1, radWidth);
						entRowCount = entRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeentomsgstatus")) {
							radOMS.setWidgetValue(templateValuesMap
									.get("pnotespeentomsgstatus"), true);
							tbOMS.setText(templateValuesMap
									.get("pnotespeentomsgcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeentomsgstatus")) {
							radOMS.setWidgetValue(templateValuesMap
									.get("pnotestpeentomsgstatus"), true);
							tbOMS.setText(templateValuesMap
									.get("pnotestpeentomsgcmnt"));
						}
					}

					if ((entSecList != null && entSecList
							.get(j)
							.equals(
									"Hard/soft palate_tongue_tonsils_posterior pharynx"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbHTTP = new CheckBox(
								"Hard/soft palate, tongue, tonsils, posterior pharynx");
						cbHTTP
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radHTTP = new CustomRadioButtonGroup("http");
						tbHTTP = new TextArea();
						tbHTTP.setVisible(false);
						tbHTTP.setWidth(textWidth);
						radHTTP.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbHTTP.setVisible(false);
								cbHTTP.setValue(true, true);
							}
						});
						radHTTP.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbHTTP.setVisible(true);
								cbHTTP.setValue(true, true);
							}
						});
						radHTTP.setEnable(false);
						tbHTTP.setEnabled(false);
						cbHTTP
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbHTTP.getValue()) {
											radHTTP.setEnable(true);
											tbHTTP.setEnabled(true);
										} else {
											radHTTP.setEnable(false);
											tbHTTP.setEnabled(false);
										}
									}

								});
						entTable.setWidget(entRowCount, 0, cbHTTP);
						entTable.setWidget(entRowCount, 1, radHTTP);
						entTable.setWidget(entRowCount + 1, 0, tbHTTP);
						entTable.getFlexCellFormatter().setColSpan(
								entRowCount + 1, 0, 2);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								0, labelWidth);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								1, radWidth);
						entRowCount = entRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeenthttpstatus")) {
							radHTTP.setWidgetValue(templateValuesMap
									.get("pnotespeenthttpstatus"), true);
							tbHTTP.setText(templateValuesMap
									.get("pnotespeenthttpcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeenthttpstatus")) {
							radHTTP.setWidgetValue(templateValuesMap
									.get("pnotestpeenthttpstatus"), true);
							tbHTTP.setText(templateValuesMap
									.get("pnotestpeenthttpcmnt"));
						}
					}

					if ((entSecList != null && entSecList.get(j).equals(
							"Thyroid"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbThyroid = new CheckBox("Thyroid");
						cbThyroid
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radThyroid = new CustomRadioButtonGroup("thy");
						tbThyroid = new TextArea();
						tbThyroid.setVisible(false);
						tbThyroid.setWidth(textWidth);
						radThyroid.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbThyroid.setVisible(false);
								cbThyroid.setValue(true, true);
							}
						});
						radThyroid.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbThyroid.setVisible(true);
								cbThyroid.setValue(true, true);
							}
						});
						radThyroid.setEnable(false);
						tbThyroid.setEnabled(false);
						cbThyroid
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbThyroid.getValue()) {
											radThyroid.setEnable(true);
											tbThyroid.setEnabled(true);
										} else {
											radThyroid.setEnable(false);
											tbThyroid.setEnabled(false);
										}
									}

								});
						entTable.setWidget(entRowCount, 0, cbThyroid);
						entTable.setWidget(entRowCount, 1, radThyroid);
						entTable.setWidget(entRowCount + 1, 0, tbThyroid);
						entTable.getFlexCellFormatter().setColSpan(
								entRowCount + 1, 0, 2);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setVerticalAlignment(
								entRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								0, labelWidth);
						entTable.getFlexCellFormatter().setWidth(entRowCount,
								1, radWidth);
						entRowCount = entRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeentthyrostatus")) {
							radThyroid.setWidgetValue(templateValuesMap
									.get("pnotespeentthyrostatus"), true);
							tbThyroid.setText(templateValuesMap
									.get("pnotespeentthyrocmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeentthyrostatus")) {
							radThyroid.setWidgetValue(templateValuesMap
									.get("pnotestpeentthyrostatus"), true);
							tbThyroid.setText(templateValuesMap
									.get("pnotestpeentthyrocmnt"));
						}
					}

					if ((entSecList != null && entSecList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(entSecList!=null && entSecList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbEntFreeForm = new TextArea();
						tbEntFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbEntFreeForm);
						freeHp.setCellWidth(tbEntFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespeentfreecmnt")) {
							tbEntFreeForm.setText(templateValuesMap
									.get("pnotespeentfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeentfreecmnt")) {
							tbEntFreeForm.setText(templateValuesMap
									.get("pnotestpeentfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbEntExBill = new CheckBox("Procedure");
					cbEntExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					rightPanel.add(billPanel);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbEntExBill);
					billPanel.add(biw);
					// examPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespeent")) {
						HashMap<String, String> m = billMap.get("pnotespeent");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespeent", biw);
						cbEntExBill.setValue(true);

					}
					cbEntExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbEntExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbEntExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespeent",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespeent");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Neck"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int neckRowCount = 0;
				Label lbEntExam = new Label("Neck");
				lbEntExam.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbEntExam);
				final FlexTable neckTable = new FlexTable();
				neckTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(neckTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);
				int neckLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Neck"))
					neckLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Neck").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#Neck"))
					neckLoopCountMax = 0;
				else
					neckLoopCountMax = 1;
				List<String> neckSecList = sectionsFieldMap
						.get("Sections#Exam#Neck");
				for (int j = 0; j < neckLoopCountMax; j++) {

					if ((neckSecList != null && neckSecList.get(j).equals(
							"Neck (note bruit_JVD)"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbNeck = new CheckBox("Neck (note bruit, JVD)");
						cbNeck
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radNeck = new CustomRadioButtonGroup("neckexam");
						tbNeckExam = new TextArea();
						tbNeckExam.setVisible(false);
						tbNeckExam.setWidth(textWidth);
						radNeck.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbNeckExam.setVisible(false);
								cbNeck.setValue(true, true);
							}
						});
						radNeck.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbNeckExam.setVisible(true);
								cbNeck.setValue(true, true);
							}
						});
						radNeck.setEnable(false);
						tbNeckExam.setEnabled(false);
						cbNeck
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbNeck.getValue()) {
											radNeck.setEnable(true);
											tbNeckExam.setEnabled(true);
										} else {
											radNeck.setEnable(false);
											tbNeckExam.setEnabled(false);
										}
									}

								});
						neckTable.setWidget(neckRowCount, 0, cbNeck);
						neckTable.setWidget(neckRowCount, 1, radNeck);
						neckTable.setWidget(neckRowCount + 1, 0, tbNeckExam);
						neckTable.getFlexCellFormatter().setColSpan(
								neckRowCount + 1, 0, 2);
						neckTable.getFlexCellFormatter()
								.setVerticalAlignment(neckRowCount, 0,
										HasVerticalAlignment.ALIGN_TOP);
						neckTable.getFlexCellFormatter()
								.setVerticalAlignment(neckRowCount, 1,
										HasVerticalAlignment.ALIGN_TOP);
						neckTable.getFlexCellFormatter().setWidth(neckRowCount,
								0, labelWidth);
						neckTable.getFlexCellFormatter().setWidth(neckRowCount,
								1, radWidth);
						neckRowCount = neckRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeneckbrjvdstatus")) {
							radNeck.setWidgetValue(templateValuesMap
									.get("pnotespeneckbrjvdstatus"), true);
							tbNeckExam.setText(templateValuesMap
									.get("pnotespeneckbrjvdcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneckbrjvdstatus")) {
							radNeck.setWidgetValue(templateValuesMap
									.get("pnotestpeneckbrjvdstatus"), true);
							tbNeckExam.setText(templateValuesMap
									.get("pnotestpeneckbrjvdcmnt"));
						}
					}

					if ((neckSecList != null && neckSecList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(neckSecList!=null && neckSecList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbNeckFreeForm = new TextArea();
						tbNeckFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbNeckFreeForm);
						freeHp.setCellWidth(tbNeckFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespeneckfreecmnt")) {
							tbNeckFreeForm.setText(templateValuesMap
									.get("pnotespeneckfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneckfreecmnt")) {
							tbNeckFreeForm.setText(templateValuesMap
									.get("pnotestpeneckfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbNeckExBill = new CheckBox("Procedure");
					cbNeckExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbNeckExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespeneck")) {
						HashMap<String, String> m = billMap.get("pnotespeneck");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespeneck", biw);
						cbNeckExBill.setValue(true);

					}
					cbNeckExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbNeckExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbNeckExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespeneck",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespeneck");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Breast"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int breastRowCount = 0;
				Label lbBreastExam = new Label("Breast");
				lbBreastExam
						.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbBreastExam);
				final FlexTable breastTable = new FlexTable();
				breastTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(breastTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int breastLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Breast"))
					breastLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Breast").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap
								.containsKey("Sections#Exam#Breast"))
					breastLoopCountMax = 0;
				else
					breastLoopCountMax = 1;
				List<String> breastSecList = sectionsFieldMap
						.get("Sections#Exam#Breast");
				for (int j = 0; j < breastLoopCountMax; j++) {

					if ((breastSecList != null && breastSecList.get(j).equals(
							"Breasts (note dimpling_discharge_mass)"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbBreast = new CheckBox(
								"Breasts (note dimpling, discharge, mass)");
						cbBreast
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radBreast = new CustomRadioButtonGroup("breastexam");
						tbBreastExam = new TextArea();
						tbBreastExam.setVisible(false);
						tbBreastExam.setWidth(textWidth);
						radBreast.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbBreastExam.setVisible(false);
								cbBreast.setValue(true, true);
							}
						});
						radBreast.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbBreastExam.setVisible(true);
								cbBreast.setValue(true, true);
							}
						});
						radBreast.setEnable(false);
						tbBreastExam.setEnabled(false);
						cbBreast
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbBreast.getValue()) {
											radBreast.setEnable(true);
											tbBreastExam.setEnabled(true);
										} else {
											radBreast.setEnable(false);
											tbBreastExam.setEnabled(false);
										}
									}

								});
						breastTable.setWidget(breastRowCount, 0, cbBreast);
						breastTable.setWidget(breastRowCount, 1, radBreast);
						breastTable.setWidget(breastRowCount + 1, 0,
								tbBreastExam);
						breastTable.getFlexCellFormatter().setColSpan(
								breastRowCount + 1, 0, 2);
						breastTable.getFlexCellFormatter()
								.setVerticalAlignment(breastRowCount, 0,
										HasVerticalAlignment.ALIGN_TOP);
						breastTable.getFlexCellFormatter()
								.setVerticalAlignment(breastRowCount, 1,
										HasVerticalAlignment.ALIGN_TOP);
						breastTable.getFlexCellFormatter().setWidth(
								breastRowCount, 0, labelWidth);
						breastTable.getFlexCellFormatter().setWidth(
								breastRowCount, 1, radWidth);
						breastRowCount++;
						if (templateValuesMap
								.containsKey("pnotespebrstddmstatus")) {
							radBreast.setWidgetValue(templateValuesMap
									.get("pnotespebrstddmstatus"), true);
							tbBreastExam.setText(templateValuesMap
									.get("pnotespebrstddmcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpebrstddmstatus")) {
							radBreast.setWidgetValue(templateValuesMap
									.get("pnotestpebrstddmstatus"), true);
							tbBreastExam.setText(templateValuesMap
									.get("pnotestpebrstddmcmnt"));
						}
					}
					if ((breastSecList != null && breastSecList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(breastSecList!=null && breastSecList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbBreastFreeForm = new TextArea();
						tbBreastFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbBreastFreeForm);
						freeHp.setCellWidth(tbBreastFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespebrstfreecmnt")) {
							tbBreastFreeForm.setText(templateValuesMap
									.get("pnotespebrstfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpebrstfreecmnt")) {
							tbBreastFreeForm.setText(templateValuesMap
									.get("pnotestpebrstfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbBreastExBill = new CheckBox("Procedure");
					cbBreastExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbBreastExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables
							&& billMap.containsKey("pnotespechestbreast")) {
						HashMap<String, String> m = billMap
								.get("pnotespechestbreast");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespechestbreast", biw);
						cbBreastExBill.setValue(true);

					}
					cbBreastExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbBreastExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbBreastExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put(
											"pnotespechestbreast", biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap
										.remove("pnotespechestbreast");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Resp"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int respRowCount = 0;
				Label lbRespExam = new Label("Resp");
				lbRespExam.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbRespExam);
				final FlexTable respTable = new FlexTable();
				respTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(respTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);
				int respLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Resp"))
					respLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Resp").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#Resp"))
					respLoopCountMax = 0;
				else
					respLoopCountMax = 1;
				List<String> respSecList = sectionsFieldMap
						.get("Sections#Exam#Resp");
				for (int j = 0; j < respLoopCountMax; j++) {

					if ((respSecList != null && respSecList.get(j).equals(
							"Respiratory effort"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbRespEff = new CheckBox(
								"Respiratory effort (note use of accessory muscles)");
						cbRespEff
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radRespEff = new CustomRadioButtonGroup("respeff");
						tbRespEff = new TextArea();
						tbRespEff.setVisible(false);
						tbRespEff.setWidth(textWidth);
						radRespEff.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbRespEff.setVisible(false);
								cbRespEff.setValue(true, true);
							}
						});
						radRespEff.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbRespEff.setVisible(true);
								cbRespEff.setValue(true, true);
							}
						});
						radRespEff.setEnable(false);
						tbRespEff.setEnabled(false);
						cbRespEff
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbRespEff.getValue()) {
											radRespEff.setEnable(true);
											tbRespEff.setEnabled(true);
										} else {
											radRespEff.setEnable(false);
											tbRespEff.setEnabled(false);
										}
									}

								});
						respTable.setWidget(respRowCount, 0, cbRespEff);
						respTable.setWidget(respRowCount, 1, radRespEff);
						respTable.setWidget(respRowCount + 1, 0, tbRespEff);
						respTable.getFlexCellFormatter().setColSpan(
								respRowCount + 1, 0, 2);
						respTable.getFlexCellFormatter()
								.setVerticalAlignment(respRowCount, 0,
										HasVerticalAlignment.ALIGN_TOP);
						respTable.getFlexCellFormatter()
								.setVerticalAlignment(respRowCount, 1,
										HasVerticalAlignment.ALIGN_TOP);
						respTable.getFlexCellFormatter().setWidth(respRowCount,
								0, labelWidth);
						respTable.getFlexCellFormatter().setWidth(respRowCount,
								1, radWidth);
						respRowCount = respRowCount + 2;

						if (templateValuesMap
								.containsKey("pnotesperespeffstatus")) {
							radRespEff.setWidgetValue(templateValuesMap
									.get("pnotesperespeffstatus"), true);
							tbRespEff.setText(templateValuesMap
									.get("pnotesperespeffcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestperespeffstatus")) {
							radRespEff.setWidgetValue(templateValuesMap
									.get("pnotestperespeffstatus"), true);
							tbRespEff.setText(templateValuesMap
									.get("pnotestperespeffcmnt"));
						}
					}

					if ((respSecList != null && respSecList.get(j).equals(
							"Lung percussion & auscultation"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbLPA = new CheckBox("Lung percussion & auscultation");
						cbLPA
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radLPA = new CustomRadioButtonGroup("lunper");
						tbLPA = new TextArea();
						tbLPA.setVisible(false);
						tbLPA.setWidth(textWidth);
						radLPA.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbLPA.setVisible(false);
								cbLPA.setValue(true, true);
							}
						});
						radLPA.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbLPA.setVisible(true);
								cbLPA.setValue(true, true);
							}
						});
						radLPA.setEnable(false);
						tbLPA.setEnabled(false);
						cbLPA
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbLPA.getValue()) {
											radLPA.setEnable(true);
											tbLPA.setEnabled(true);
										} else {
											radLPA.setEnable(false);
											tbLPA.setEnabled(false);
										}
									}

								});
						respTable.setWidget(respRowCount, 0, cbLPA);
						respTable.setWidget(respRowCount, 1, radLPA);
						respTable.setWidget(respRowCount + 1, 0, tbLPA);
						respTable.getFlexCellFormatter().setColSpan(
								respRowCount + 1, 0, 2);
						respTable.getFlexCellFormatter()
								.setVerticalAlignment(respRowCount, 0,
										HasVerticalAlignment.ALIGN_TOP);
						respTable.getFlexCellFormatter()
								.setVerticalAlignment(respRowCount, 1,
										HasVerticalAlignment.ALIGN_TOP);
						respTable.getFlexCellFormatter().setWidth(respRowCount,
								0, labelWidth);
						respTable.getFlexCellFormatter().setWidth(respRowCount,
								1, radWidth);
						respRowCount = respRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotesperesplungstatus")) {
							radLPA.setWidgetValue(templateValuesMap
									.get("pnotesperesplungstatus"), true);
							tbLPA.setText(templateValuesMap
									.get("pnotesperesplungcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestperesplungstatus")) {
							radLPA.setWidgetValue(templateValuesMap
									.get("pnotestperesplungstatus"), true);
							tbLPA.setText(templateValuesMap
									.get("pnotestperesplungcmnt"));
						}
					}
					if ((respSecList != null && respSecList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(respSecList!=null && respSecList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbRespFreeForm = new TextArea();
						tbRespFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbRespFreeForm);
						freeHp.setCellWidth(tbRespFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotesperespfreecmnt")) {
							tbRespFreeForm.setText(templateValuesMap
									.get("pnotesperespfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestperespfreecmnt")) {
							tbRespFreeForm.setText(templateValuesMap
									.get("pnotestperespfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbRespExBill = new CheckBox("Procedure");
					cbRespExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbRespExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotesperesp")) {
						HashMap<String, String> m = billMap.get("pnotesperesp");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotesperesp", biw);
						cbRespExBill.setValue(true);

					}
					cbRespExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbRespExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbRespExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotesperesp",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotesperesp");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("CV"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int cvRowCount = 0;
				Label lbCV = new Label("CV");
				lbCV.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbCV);
				final FlexTable cvTable = new FlexTable();
				cvTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(cvTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int cvLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#CV"))
					cvLoopCountMax = sectionsFieldMap.get("Sections#Exam#CV")
							.size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#CV"))
					cvLoopCountMax = 0;
				else
					cvLoopCountMax = 1;
				List<String> cvList = sectionsFieldMap.get("Sections#Exam#CV");
				for (int j = 0; j < cvLoopCountMax; j++) {
					if ((cvList != null && cvList.get(j).equals("Auscultation"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						Label lbExtCanTms = new Label("Auscultation:");
						lbExtCanTms
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						cvTable.setWidget(cvRowCount, 0, lbExtCanTms);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						cvRowCount++;
						int auscultationLoopCountMax = 0;
						if (sectionsFieldMap.containsKey("Sections")
								&& sectionsFieldMap
										.containsKey("Sections#Exam#CV#Auscultation"))
							auscultationLoopCountMax = sectionsFieldMap.get(
									"Sections#Exam#CV#Auscultation").size();
						else if (sectionsFieldMap.containsKey("Sections")
								&& !sectionsFieldMap
										.containsKey("Sections#Exam#CV#Auscultation"))
							auscultationLoopCountMax = 0;
						else
							auscultationLoopCountMax = 1;
						List<String> auscultationSecList = sectionsFieldMap
								.get("Sections#Exam#CV#Auscultation");
						for (int k = 0; k < auscultationLoopCountMax; k++) {

							if ((auscultationSecList != null && auscultationSecList
									.get(k).equals("Regular rhythm"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbRegRyth = new CheckBox("Regular rhythm");
								cbRegRyth
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbRegRyth.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radRegRyth = new CustomRadioButtonGroup(
										"regrhy");
								tbRegRyth = new TextArea();
								tbRegRyth.setVisible(false);
								tbRegRyth.setWidth(textWidth);
								radRegRyth.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbRegRyth.setVisible(false);
												cbRegRyth.setValue(true, true);
											}
										});
								radRegRyth.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbRegRyth.setVisible(true);
												cbRegRyth.setValue(true, true);

											}
										});
								radRegRyth.setEnable(false);
								tbRegRyth.setEnabled(false);
								cbRegRyth
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbRegRyth.getValue()) {
													radRegRyth.setEnable(true);
													tbRegRyth.setEnabled(true);
												} else {
													radRegRyth.setEnable(false);
													tbRegRyth.setEnabled(false);
												}
											}

										});
								cvTable.setWidget(cvRowCount, 0, cbRegRyth);
								cvTable.setWidget(cvRowCount, 1, radRegRyth);
								cvTable.setWidget(cvRowCount + 1, 0, tbRegRyth);
								cvTable.getFlexCellFormatter().setColSpan(
										cvRowCount + 1, 0, 2);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 0, labelWidth);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 1, radWidth);
								cvRowCount = cvRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespecvregrhystatus")) {
									radRegRyth.setWidgetValue(templateValuesMap
											.get("pnotespecvregrhystatus"),
											true);
									tbRegRyth.setText(templateValuesMap
											.get("pnotespecvregrhycmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpecvregrhystatus")) {
									radRegRyth.setWidgetValue(templateValuesMap
											.get("pnotestpecvregrhystatus"),
											true);
									tbRegRyth.setText(templateValuesMap
											.get("pnotestpecvregrhycmnt"));
								}
							}
							if ((auscultationSecList != null && auscultationSecList
									.get(k).equals("S1 constant"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbS1Cons = new CheckBox("S1 constant");
								cbS1Cons
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbS1Cons.getElement().getStyle().setMarginLeft(
										50, Unit.PX);
								radS1Cons = new CustomRadioButtonGroup("s1cons");
								tbS1Cons = new TextArea();
								tbS1Cons.setVisible(false);
								tbS1Cons.setWidth(textWidth);
								radS1Cons.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbS1Cons.setVisible(false);
										cbS1Cons.setValue(true, true);
									}
								});
								radS1Cons.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbS1Cons.setVisible(true);
												cbS1Cons.setValue(true, true);
											}
										});
								radS1Cons.setEnable(false);
								tbS1Cons.setEnabled(false);
								cbS1Cons
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbS1Cons.getValue()) {
													radS1Cons.setEnable(true);
													tbS1Cons.setEnabled(true);
												} else {
													radS1Cons.setEnable(false);
													tbS1Cons.setEnabled(false);
												}
											}

										});
								cvTable.setWidget(cvRowCount, 0, cbS1Cons);
								cvTable.setWidget(cvRowCount, 1, radS1Cons);
								cvTable.setWidget(cvRowCount + 1, 0, tbS1Cons);
								cvTable.getFlexCellFormatter().setColSpan(
										cvRowCount + 1, 0, 2);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 0, labelWidth);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 1, radWidth);
								cvRowCount = cvRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespecvs1consstatus")) {
									radS1Cons.setWidgetValue(templateValuesMap
											.get("pnotespecvs1consstatus"),
											true);
									tbS1Cons.setText(templateValuesMap
											.get("pnotespecvs1conscmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpecvs1consstatus")) {
									radS1Cons.setWidgetValue(templateValuesMap
											.get("pnotestpecvs1consstatus"),
											true);
									tbS1Cons.setText(templateValuesMap
											.get("pnotestpecvs1conscmnt"));
								}
							}

							if ((auscultationSecList != null && auscultationSecList
									.get(k).equals("S2 physiologic split"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbS2PhysSplit = new CheckBox(
										"S2 physiologic split");
								cbS2PhysSplit
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbS2PhysSplit.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radPhysSplit = new CustomRadioButtonGroup(
										"s2phy");
								tbPhysSplit = new TextArea();
								tbPhysSplit.setVisible(false);
								tbPhysSplit.setWidth(textWidth);
								radPhysSplit.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbPhysSplit.setVisible(false);
												cbS2PhysSplit.setValue(true,
														true);
											}
										});
								radPhysSplit.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbPhysSplit.setVisible(true);
												cbS2PhysSplit.setValue(true,
														true);
											}
										});
								radPhysSplit.setEnable(false);
								tbPhysSplit.setEnabled(false);
								cbS2PhysSplit
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbS2PhysSplit.getValue()) {
													radPhysSplit
															.setEnable(true);
													tbPhysSplit
															.setEnabled(true);
												} else {
													radPhysSplit
															.setEnable(false);
													tbPhysSplit
															.setEnabled(false);
												}
											}

										});
								cvTable.setWidget(cvRowCount, 0, cbS2PhysSplit);
								cvTable.setWidget(cvRowCount, 1, radPhysSplit);
								cvTable.setWidget(cvRowCount + 1, 0,
										tbPhysSplit);
								cvTable.getFlexCellFormatter().setColSpan(
										cvRowCount + 1, 0, 2);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 0, labelWidth);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 1, radWidth);
								cvRowCount = cvRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespecvs2physplstatus")) {
									radPhysSplit
											.setWidgetValue(
													templateValuesMap
															.get("pnotespecvs2physplstatus"),
													true);
									tbPhysSplit.setText(templateValuesMap
											.get("pnotespecvs2physplcmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpecvs2physplstatus")) {
									radPhysSplit
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpecvs2physplstatus"),
													true);
									tbPhysSplit.setText(templateValuesMap
											.get("pnotestpecvs2physplcmnt"));
								}
							}

							if ((auscultationSecList != null && auscultationSecList
									.get(k).equals("Murmur (describe)"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbMurmur = new CheckBox("Murmur (describe)");
								cbMurmur
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbMurmur.getElement().getStyle().setMarginLeft(
										50, Unit.PX);
								radMurmur = new CustomRadioButtonGroup("murmur");
								tbMurmur = new TextArea();
								tbMurmur.setVisible(false);
								tbMurmur.setWidth(textWidth);
								radMurmur.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbMurmur.setVisible(false);
										cbMurmur.setValue(true, true);
									}
								});
								radMurmur.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbMurmur.setVisible(true);
												cbMurmur.setValue(true, true);
											}
										});
								radMurmur.setEnable(false);
								tbMurmur.setEnabled(false);
								cbMurmur
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbMurmur.getValue()) {
													radMurmur.setEnable(true);
													tbMurmur.setEnabled(true);
												} else {
													radMurmur.setEnable(false);
													tbMurmur.setEnabled(false);
												}
											}

										});
								cvTable.setWidget(cvRowCount, 0, cbMurmur);
								cvTable.setWidget(cvRowCount, 1, radMurmur);
								cvTable.setWidget(cvRowCount + 1, 0, tbMurmur);
								cvTable.getFlexCellFormatter().setColSpan(
										cvRowCount + 1, 0, 2);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter()
										.setVerticalAlignment(cvRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 0, labelWidth);
								cvTable.getFlexCellFormatter().setWidth(
										cvRowCount, 1, radWidth);
								cvRowCount = cvRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespecvmurstatus")) {
									radMurmur.setWidgetValue(templateValuesMap
											.get("pnotespecvmurstatus"), true);
									tbMurmur.setText(templateValuesMap
											.get("pnotespecvmurcmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpecvmurstatus")) {
									radMurmur.setWidgetValue(templateValuesMap
											.get("pnotestpecvmurstatus"), true);
									tbMurmur.setText(templateValuesMap
											.get("pnotestpecvmurcmnt"));
								}
							}
						}
					}

					if ((cvList != null && cvList.get(j).equals(
							"Palpation of heart"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbPalHrt = new CheckBox("Palpation of heart");
						cbPalHrt
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radPalHrt = new CustomRadioButtonGroup("palhrt");
						tbPalHrt = new TextArea();
						tbPalHrt.setVisible(false);
						tbPalHrt.setWidth(textWidth);
						radPalHrt.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbPalHrt.setVisible(false);
								cbPalHrt.setValue(true, true);
							}
						});
						radPalHrt.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbPalHrt.setVisible(true);
								cbPalHrt.setValue(true, true);
							}
						});
						radPalHrt.setEnable(false);
						tbPalHrt.setEnabled(false);
						cbPalHrt
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbPalHrt.getValue()) {
											radPalHrt.setEnable(true);
											tbPalHrt.setEnabled(true);
										} else {
											radPalHrt.setEnable(false);
											tbPalHrt.setEnabled(false);
										}
									}

								});
						cvTable.setWidget(cvRowCount, 0, cbPalHrt);
						cvTable.setWidget(cvRowCount, 1, radPalHrt);
						cvTable.setWidget(cvRowCount + 1, 0, tbPalHrt);
						cvTable.getFlexCellFormatter().setColSpan(
								cvRowCount + 1, 0, 2);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 0,
								labelWidth);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 1,
								radWidth);
						cvRowCount = cvRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespecvpalhrtstatus")) {
							radPalHrt.setWidgetValue(templateValuesMap
									.get("pnotespecvpalhrtstatus"), true);
							tbPalHrt.setText(templateValuesMap
									.get("pnotespecvpalhrtcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpecvpalhrtstatus")) {
							radPalHrt.setWidgetValue(templateValuesMap
									.get("pnotestpecvpalhrtstatus"), true);
							tbPalHrt.setText(templateValuesMap
									.get("pnotestpecvpalhrtcmnt"));
						}
					}

					if ((cvList != null && cvList.get(j).equals(
							"Abdominal aorta"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbAbAorta = new CheckBox("Abdominal aorta");
						cbAbAorta
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radAbAorta = new CustomRadioButtonGroup("abdaor");
						tbAbAorta = new TextArea();
						tbAbAorta.setVisible(false);
						tbAbAorta.setWidth(textWidth);
						radAbAorta.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbAbAorta.setVisible(false);
								cbAbAorta.setValue(true, true);
							}
						});
						radAbAorta.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbAbAorta.setVisible(true);
								cbAbAorta.setValue(true, true);
							}
						});
						radAbAorta.setEnable(false);
						tbAbAorta.setEnabled(false);
						cbAbAorta
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbAbAorta.getValue()) {
											radAbAorta.setEnable(true);
											tbAbAorta.setEnabled(true);
										} else {
											radAbAorta.setEnable(false);
											tbAbAorta.setEnabled(false);
										}
									}

								});
						cvTable.setWidget(cvRowCount, 0, cbAbAorta);
						cvTable.setWidget(cvRowCount, 1, radAbAorta);
						cvTable.setWidget(cvRowCount + 1, 0, tbAbAorta);
						cvTable.getFlexCellFormatter().setColSpan(
								cvRowCount + 1, 0, 2);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 0,
								labelWidth);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 1,
								radWidth);
						cvRowCount = cvRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespecvabdaorstatus")) {
							radAbAorta.setWidgetValue(templateValuesMap
									.get("pnotespecvabdaorstatus"), true);
							tbAbAorta.setText(templateValuesMap
									.get("pnotespecvabdaorcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpecvabdaorstatus")) {
							radAbAorta.setWidgetValue(templateValuesMap
									.get("pnotestpecvabdaorstatus"), true);
							tbAbAorta.setText(templateValuesMap
									.get("pnotestpecvabdaorcmnt"));
						}
					}

					if ((cvList != null && cvList.get(j).equals(
							"Femoral arteries"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbFemArt = new CheckBox("Femoral arteries");
						cbFemArt
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radFemArt = new CustomRadioButtonGroup("femart");
						tbFemArt = new TextArea();
						tbFemArt.setVisible(false);
						tbFemArt.setWidth(textWidth);
						radFemArt.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbFemArt.setVisible(false);
								cbFemArt.setValue(true, true);
							}
						});
						radFemArt.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbFemArt.setVisible(true);
								cbFemArt.setValue(true, true);
							}
						});
						radFemArt.setEnable(false);
						tbFemArt.setEnabled(false);
						cbFemArt
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbFemArt.getValue()) {
											radFemArt.setEnable(true);
											tbFemArt.setEnabled(true);
										} else {
											radFemArt.setEnable(false);
											tbFemArt.setEnabled(false);
										}
									}

								});
						cvTable.setWidget(cvRowCount, 0, cbFemArt);
						cvTable.setWidget(cvRowCount, 1, radFemArt);
						cvTable.setWidget(cvRowCount + 1, 0, tbFemArt);
						cvTable.getFlexCellFormatter().setColSpan(
								cvRowCount + 1, 0, 2);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 0,
								labelWidth);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 1,
								radWidth);
						cvRowCount = cvRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespecvfemartstatus")) {
							radFemArt.setWidgetValue(templateValuesMap
									.get("pnotespecvfemartstatus"), true);
							tbFemArt.setText(templateValuesMap
									.get("pnotespecvfemartcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpecvfemartstatus")) {
							radFemArt.setWidgetValue(templateValuesMap
									.get("pnotestpecvfemartstatus"), true);
							tbFemArt.setText(templateValuesMap
									.get("pnotestpecvfemartcmnt"));
						}
					}

					if ((cvList != null && cvList.get(j).equals("Pedal pulses"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbPedalPulses = new CheckBox("Pedal pulses");
						cbPedalPulses
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radPedalPulses = new CustomRadioButtonGroup("pedpul");
						tbPedalPulses = new TextArea();
						tbPedalPulses.setVisible(false);
						tbPedalPulses.setWidth(textWidth);
						radPedalPulses.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbPedalPulses.setVisible(false);
								cbPedalPulses.setValue(true, true);
							}
						});
						radPedalPulses.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbPedalPulses.setVisible(true);
								cbPedalPulses.setValue(true, true);
							}
						});
						radPedalPulses.setEnable(false);
						tbPedalPulses.setEnabled(false);
						cbPedalPulses
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbPedalPulses.getValue()) {
											radPedalPulses.setEnable(true);
											tbPedalPulses.setEnabled(true);
										} else {
											radPedalPulses.setEnable(false);
											tbPedalPulses.setEnabled(false);
										}
									}

								});
						cvTable.setWidget(cvRowCount, 0, cbPedalPulses);
						cvTable.setWidget(cvRowCount, 1, radPedalPulses);
						cvTable.setWidget(cvRowCount + 1, 0, tbPedalPulses);
						cvTable.getFlexCellFormatter().setColSpan(
								cvRowCount + 1, 0, 2);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setVerticalAlignment(
								cvRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 0,
								labelWidth);
						cvTable.getFlexCellFormatter().setWidth(cvRowCount, 1,
								radWidth);
						cvRowCount = cvRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespecvpedpulstatus")) {
							radPedalPulses.setWidgetValue(templateValuesMap
									.get("pnotespecvpedpulstatus"), true);
							tbPedalPulses.setText(templateValuesMap
									.get("pnotespecvpadpulcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpecvpedpulstatus")) {
							radPedalPulses.setWidgetValue(templateValuesMap
									.get("pnotestpecvpedpulstatus"), true);
							tbPedalPulses.setText(templateValuesMap
									.get("pnotestpecvpadpulcmnt"));
						}
					}
					if ((cvList != null && cvList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(cvList!=null && cvList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbCVFreeForm = new TextArea();
						tbCVFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbCVFreeForm);
						freeHp.setCellWidth(tbCVFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespecvfreecmnt")) {
							tbCVFreeForm.setText(templateValuesMap
									.get("pnotespecvfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpecvfreecmnt")) {
							tbCVFreeForm.setText(templateValuesMap
									.get("pnotestpecvfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbCVExBill = new CheckBox("Procedure");
					cbCVExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbCVExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespecv")) {
						HashMap<String, String> m = billMap.get("pnotespecv");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespecv", biw);
						cbCVExBill.setValue(true);

					}
					cbCVExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbCVExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbCVExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespecv",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespecv");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("GI"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int giRowCount = 0;
				Label lbGI = new Label("GI");
				lbGI.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbGI);
				final FlexTable giTable = new FlexTable();
				giTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(giTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int giLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#GI"))
					giLoopCountMax = sectionsFieldMap.get("Sections#Exam#GI")
							.size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#GI"))
					giLoopCountMax = 0;
				else
					giLoopCountMax = 1;
				List<String> giList = sectionsFieldMap.get("Sections#Exam#GI");
				for (int j = 0; j < giLoopCountMax; j++) {
					if ((giList != null && giList.get(j).equals("Abdomen"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						Label lbAbd = new Label("Abdomen:");
						lbAbd
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						giTable.setWidget(giRowCount, 0, lbAbd);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						giRowCount++;
						int abdLoopCountMax = 0;
						if (sectionsFieldMap.containsKey("Sections")
								&& sectionsFieldMap
										.containsKey("Sections#Exam#GI#Abdomen"))
							abdLoopCountMax = sectionsFieldMap.get(
									"Sections#Exam#GI#Abdomen").size();
						else if (sectionsFieldMap.containsKey("Sections")
								&& !sectionsFieldMap
										.containsKey("Sections#Exam#GI#Abdomen"))
							abdLoopCountMax = 0;
						else
							abdLoopCountMax = 1;
						List<String> abdSecList = sectionsFieldMap
								.get("Sections#Exam#GI#Abdomen");

						for (int k = 0; k < abdLoopCountMax; k++) {

							if ((abdSecList != null && abdSecList.get(k)
									.equals("Scars"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbScars = new CheckBox("Scars");
								cbScars
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbScars.getElement().getStyle().setMarginLeft(
										50, Unit.PX);
								radScars = new CustomRadioButtonGroup("scars");
								tbScars = new TextArea();
								tbScars.setVisible(false);
								tbScars.setWidth(textWidth);
								radScars.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbScars.setVisible(false);
										cbScars.setValue(true, true);
									}
								});
								radScars.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbScars.setVisible(true);
												cbScars.setValue(true, true);
											}
										});
								radScars.setEnable(false);
								tbScars.setEnabled(false);
								cbScars
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbScars.getValue()) {
													radScars.setEnable(true);
													tbScars.setEnabled(true);
												} else {
													radScars.setEnable(false);
													tbScars.setEnabled(false);
												}
											}

										});
								giTable.setWidget(giRowCount, 0, cbScars);
								giTable.setWidget(giRowCount, 1, radScars);
								giTable.setWidget(giRowCount + 1, 0, tbScars);
								giTable.getFlexCellFormatter().setColSpan(
										giRowCount + 1, 0, 2);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 0, labelWidth);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 1, radWidth);
								giRowCount = giRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespegiscarsstatus")) {
									radScars
											.setWidgetValue(
													templateValuesMap
															.get("pnotespegiscarsstatus"),
													true);
									tbScars.setText(templateValuesMap
											.get("pnotespegiscarscmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpegiscarsstatus")) {
									radScars.setWidgetValue(templateValuesMap
											.get("pnotestpegiscarsstatus"),
											true);
									tbScars.setText(templateValuesMap
											.get("pnotestpegiscarscmnt"));
								}
							}
							if ((abdSecList != null && abdSecList.get(k)
									.equals("Bruit"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbBruit = new CheckBox("Bruit");
								cbBruit
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbBruit.getElement().getStyle().setMarginLeft(
										50, Unit.PX);
								radBruit = new CustomRadioButtonGroup("bruit");
								tbBruit = new TextArea();
								tbBruit.setVisible(false);
								tbBruit.setWidth(textWidth);
								radBruit.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbBruit.setVisible(false);
										cbBruit.setValue(true, true);
									}
								});
								radBruit.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbBruit.setVisible(true);
												cbBruit.setValue(true, true);
											}
										});
								radBruit.setEnable(false);
								tbBruit.setEnabled(false);
								cbBruit
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbBruit.getValue()) {
													radBruit.setEnable(true);
													tbBruit.setEnabled(true);
												} else {
													radBruit.setEnable(false);
													tbBruit.setEnabled(false);
												}
											}

										});
								giTable.setWidget(giRowCount, 0, cbBruit);
								giTable.setWidget(giRowCount, 1, radBruit);
								giTable.setWidget(giRowCount + 1, 0, tbBruit);
								giTable.getFlexCellFormatter().setColSpan(
										giRowCount + 1, 0, 2);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 0, labelWidth);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 1, radWidth);
								giRowCount = giRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespegibruitstatus")) {
									radBruit
											.setWidgetValue(
													templateValuesMap
															.get("pnotespegibruitstatus"),
													true);
									tbBruit.setText(templateValuesMap
											.get("pnotespegibruitcmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpegibruitstatus")) {
									radBruit.setWidgetValue(templateValuesMap
											.get("pnotestpegibruitstatus"),
											true);
									tbBruit.setText(templateValuesMap
											.get("pnotestpegibruitcmnt"));
								}
							}

							if ((abdSecList != null && abdSecList.get(k)
									.equals("Mass"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbMassExam = new CheckBox("Mass");
								cbMassExam
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbMassExam.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radMass = new CustomRadioButtonGroup("mass");
								tbMass = new TextArea();
								tbMass.setVisible(false);
								tbMass.setWidth(textWidth);
								radMass.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbMass.setVisible(false);
										cbMassExam.setValue(true, true);
									}
								});
								radMass.addItem("Abnormal", "2", new Command() {
									@Override
									public void execute() {
										tbMass.setVisible(true);
										cbMassExam.setValue(true, true);
									}
								});
								radMass.setEnable(false);
								tbMass.setEnabled(false);
								cbMassExam
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbMassExam.getValue()) {
													radMass.setEnable(true);
													tbMass.setEnabled(true);
												} else {
													radMass.setEnable(false);
													tbMass.setEnabled(false);
												}
											}

										});
								giTable.setWidget(giRowCount, 0, cbMassExam);
								giTable.setWidget(giRowCount, 1, radMass);
								giTable.setWidget(giRowCount + 1, 0, tbMass);
								giTable.getFlexCellFormatter().setColSpan(
										giRowCount + 1, 0, 2);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 0, labelWidth);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 1, radWidth);
								giRowCount = giRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespegimassstatus")) {
									radMass.setWidgetValue(templateValuesMap
											.get("pnotespegimassstatus"), true);
									tbMass.setText(templateValuesMap
											.get("pnotespegimasscmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpegimassstatus")) {
									radMass
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpegimassstatus"),
													true);
									tbMass.setText(templateValuesMap
											.get("pnotestpegimasscmnt"));
								}
							}

							if ((abdSecList != null && abdSecList.get(k)
									.equals("Tenderness"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbTenderness = new CheckBox("Tenderness");
								cbTenderness
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbTenderness.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radTenderness = new CustomRadioButtonGroup(
										"tender");
								tbTenderness = new TextArea();
								tbTenderness.setVisible(false);
								tbTenderness.setWidth(textWidth);
								radTenderness.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbTenderness.setVisible(false);
												cbTenderness.setValue(true,
														true);
											}
										});
								radTenderness.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbTenderness.setVisible(true);
												cbTenderness.setValue(true,
														true);
											}
										});
								radTenderness.setEnable(false);
								tbTenderness.setEnabled(false);
								cbTenderness
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbTenderness.getValue()) {
													radTenderness
															.setEnable(true);
													tbTenderness
															.setEnabled(true);
												} else {
													radTenderness
															.setEnable(false);
													tbTenderness
															.setEnabled(false);
												}
											}

										});
								giTable.setWidget(giRowCount, 0, cbTenderness);
								giTable.setWidget(giRowCount, 1, radTenderness);
								giTable.setWidget(giRowCount + 1, 0,
										tbTenderness);
								giTable.getFlexCellFormatter().setColSpan(
										giRowCount + 1, 0, 2);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 0, labelWidth);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 1, radWidth);
								giRowCount = giRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespegitendstatus")) {
									radTenderness
											.setWidgetValue(
													templateValuesMap
															.get("pnotespegitendstatus"),
													true);
									tbTenderness.setText(templateValuesMap
											.get("pnotespegitendcmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpegitendstatus")) {
									radTenderness
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpegitendstatus"),
													true);
									tbTenderness.setText(templateValuesMap
											.get("pnotestpegitendcmnt"));
								}
							}
							if ((abdSecList != null && abdSecList.get(k)
									.equals("Hepatomegaly"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbHepatomegaly = new CheckBox("Hepatomegaly");
								cbHepatomegaly
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbHepatomegaly.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radHepatomegaly = new CustomRadioButtonGroup(
										"hepat");
								tbHepatomegaly = new TextArea();
								tbHepatomegaly.setVisible(false);
								tbHepatomegaly.setWidth(textWidth);
								radHepatomegaly.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbHepatomegaly
														.setVisible(false);
												cbHepatomegaly.setValue(true,
														true);
											}
										});
								radHepatomegaly.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbHepatomegaly.setVisible(true);
												cbHepatomegaly.setValue(true,
														true);
											}
										});
								radHepatomegaly.setEnable(false);
								tbHepatomegaly.setEnabled(false);
								cbHepatomegaly
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbHepatomegaly.getValue()) {
													radHepatomegaly
															.setEnable(true);
													tbHepatomegaly
															.setEnabled(true);
												} else {
													radHepatomegaly
															.setEnable(false);
													tbHepatomegaly
															.setEnabled(false);
												}
											}

										});
								giTable
										.setWidget(giRowCount, 0,
												cbHepatomegaly);
								giTable.setWidget(giRowCount, 1,
										radHepatomegaly);
								giTable.setWidget(giRowCount + 1, 0,
										tbHepatomegaly);
								giTable.getFlexCellFormatter().setColSpan(
										giRowCount + 1, 0, 2);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 0, labelWidth);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 1, radWidth);
								giRowCount = giRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespegiheptstatus")) {
									radHepatomegaly
											.setWidgetValue(
													templateValuesMap
															.get("pnotespegiheptstatus"),
													true);
									tbHepatomegaly.setText(templateValuesMap
											.get("pnotespegiheptcmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpegiheptstatus")) {
									radHepatomegaly
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpegiheptstatus"),
													true);
									tbHepatomegaly.setText(templateValuesMap
											.get("pnotestpegiheptcmnt"));
								}
							}
							if ((abdSecList != null && abdSecList.get(k)
									.equals("Splenomegaly"))
									|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
											.equals(""))) {
								cbSplenomegaly = new CheckBox("Splenomegaly");
								cbSplenomegaly
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbSplenomegaly.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);
								radSplenomegaly = new CustomRadioButtonGroup(
										"splen");
								tbSplenomegaly = new TextArea();
								tbSplenomegaly.setVisible(false);
								tbSplenomegaly.setWidth(textWidth);
								radSplenomegaly.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbSplenomegaly
														.setVisible(false);
												cbSplenomegaly.setValue(true,
														true);
											}
										});
								radSplenomegaly.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbSplenomegaly.setVisible(true);
												cbSplenomegaly.setValue(true,
														true);
											}
										});
								radSplenomegaly.setEnable(false);
								tbSplenomegaly.setEnabled(false);
								cbSplenomegaly
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbSplenomegaly.getValue()) {
													radSplenomegaly
															.setEnable(true);
													tbSplenomegaly
															.setEnabled(true);
												} else {
													radSplenomegaly
															.setEnable(false);
													tbSplenomegaly
															.setEnabled(false);
												}
											}
										});
								giTable
										.setWidget(giRowCount, 0,
												cbSplenomegaly);
								giTable.setWidget(giRowCount, 1,
										radSplenomegaly);
								giTable.setWidget(giRowCount + 1, 0,
										tbSplenomegaly);
								giTable.getFlexCellFormatter().setColSpan(
										giRowCount + 1, 0, 2);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 0,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter()
										.setVerticalAlignment(giRowCount, 1,
												HasVerticalAlignment.ALIGN_TOP);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 0, labelWidth);
								giTable.getFlexCellFormatter().setWidth(
										giRowCount, 1, radWidth);
								giRowCount = giRowCount + 2;
								if (templateValuesMap
										.containsKey("pnotespegisplenstatus")) {
									radSplenomegaly
											.setWidgetValue(
													templateValuesMap
															.get("pnotespegisplenstatus"),
													true);
									tbSplenomegaly.setText(templateValuesMap
											.get("pnotespegisplencmnt"));
								} else if (templateValuesMap
										.containsKey("pnotestpegisplenstatus")) {
									radSplenomegaly
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpegisplenstatus"),
													true);
									tbSplenomegaly.setText(templateValuesMap
											.get("pnotestpegisplencmnt"));
								}
							}
						}
					}

					if ((giList != null && giList.get(j).equals(
							"Anus_perineum_rectum_sphincter tone"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbAPRS = new CheckBox(
								"Anus, perineum, rectum, sphincter tone");
						cbAPRS
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radAPRS = new CustomRadioButtonGroup("aprst");
						tbAPRS = new TextArea();
						tbAPRS.setVisible(false);
						tbAPRS.setWidth(textWidth);
						radAPRS.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbAPRS.setVisible(false);
								cbAPRS.setValue(true, true);
							}
						});
						radAPRS.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbAPRS.setVisible(true);
								cbAPRS.setValue(true, true);
							}
						});
						radAPRS.setEnable(false);
						tbAPRS.setEnabled(false);
						cbAPRS
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbAPRS.getValue()) {
											radAPRS.setEnable(true);
											tbAPRS.setEnabled(true);
										} else {
											radAPRS.setEnable(false);
											tbAPRS.setEnabled(false);
										}
									}
								});
						giTable.setWidget(giRowCount, 0, cbAPRS);
						giTable.setWidget(giRowCount, 1, radAPRS);
						giTable.setWidget(giRowCount + 1, 0, tbAPRS);
						giTable.getFlexCellFormatter().setColSpan(
								giRowCount + 1, 0, 2);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						giTable.getFlexCellFormatter().setWidth(giRowCount, 0,
								labelWidth);
						giTable.getFlexCellFormatter().setWidth(giRowCount, 1,
								radWidth);
						giRowCount = giRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespegiaprsstatus")) {
							radAPRS.setWidgetValue(templateValuesMap
									.get("pnotespegiaprsstatus"), true);
							tbAPRS.setText(templateValuesMap
									.get("pnotespegiaprscmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpegiaprsstatus")) {
							radAPRS.setWidgetValue(templateValuesMap
									.get("pnotestpegiaprsstatus"), true);
							tbAPRS.setText(templateValuesMap
									.get("pnotestpegiaprscmnt"));
						}
					}

					if ((giList != null && giList.get(j).equals("Bowel sounds"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbBowSnd = new CheckBox("Bowel sounds:");
						cbBowSnd
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radBowSnd = new CustomRadioButtonGroup("bowsnd");
						tbBowSnd = new TextArea();
						tbBowSnd.setVisible(false);
						tbBowSnd.setWidth(textWidth);
						radBowSnd.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbBowSnd.setVisible(false);
								cbBowSnd.setValue(true, true);
							}
						});
						radBowSnd.addItem("High", "2", new Command() {
							@Override
							public void execute() {

								tbBowSnd.setVisible(true);
								cbBowSnd.setValue(true, true);
							}
						});
						radBowSnd.addItem("Low", "3", new Command() {
							@Override
							public void execute() {

								tbBowSnd.setVisible(true);
								cbBowSnd.setValue(true, true);
							}
						});
						radBowSnd.addItem("Absent", "4", new Command() {
							@Override
							public void execute() {

								tbBowSnd.setVisible(true);
								cbBowSnd.setValue(true, true);
							}
						});
						radBowSnd.setEnable(false);
						tbBowSnd.setEnabled(false);
						cbBowSnd
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbBowSnd.getValue()) {
											radBowSnd.setEnable(true);
											tbBowSnd.setEnabled(true);
										} else {
											radBowSnd.setEnable(false);
											tbBowSnd.setEnabled(false);
										}
									}
								});
						giTable.setWidget(giRowCount, 0, cbBowSnd);
						giTable.setWidget(giRowCount, 1, radBowSnd);
						giTable.setWidget(giRowCount + 1, 0, tbBowSnd);
						giTable.getFlexCellFormatter().setColSpan(
								giRowCount + 1, 0, 2);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						giTable.getFlexCellFormatter().setWidth(giRowCount, 0,
								labelWidth);
						giTable.getFlexCellFormatter().setWidth(giRowCount, 1,
								radWidth);
						giRowCount = giRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespegibowsndstatus")) {
							radBowSnd.setWidgetValue(templateValuesMap
									.get("pnotespegibowsndstatus"), true);
							tbBowSnd.setText(templateValuesMap
									.get("pnotespegibowsndcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpegibowsndstatus")) {
							radBowSnd.setWidgetValue(templateValuesMap
									.get("pnotestpegibowsndstatus"), true);
							tbBowSnd.setText(templateValuesMap
									.get("pnotestpegibowsndcmnt"));
						}
					}

					if ((giList != null && giList.get(j).equals("Stool"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbStool = new CheckBox("Stool:");
						cbStool
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radStool = new CustomRadioButtonGroup("stool");
						tbStool = new TextArea();
						tbStool.setVisible(false);
						tbStool.setWidth(textWidth);
						radStool.addItem("Heme positive", "1", new Command() {
							@Override
							public void execute() {

								tbStool.setVisible(false);
								cbStool.setValue(true, true);
							}
						});
						radStool.addItem("Heme negative", "2", new Command() {
							@Override
							public void execute() {

								tbStool.setVisible(true);
								cbStool.setValue(true, true);
							}
						});
						radStool.setEnable(false);
						tbStool.setEnabled(false);
						cbStool
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbStool.getValue()) {
											radStool.setEnable(true);
											tbStool.setEnabled(true);
										} else {
											radStool.setEnable(false);
											tbStool.setEnabled(false);
										}
									}
								});
						giTable.setWidget(giRowCount, 0, cbStool);
						giTable.setWidget(giRowCount, 1, radStool);
						giTable.setWidget(giRowCount + 1, 0, tbStool);
						giTable.getFlexCellFormatter().setColSpan(
								giRowCount + 1, 0, 2);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						giTable.getFlexCellFormatter().setVerticalAlignment(
								giRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						giTable.getFlexCellFormatter().setWidth(giRowCount, 0,
								labelWidth);
						giTable.getFlexCellFormatter().setWidth(giRowCount, 1,
								radWidth);
						giRowCount = giRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespegistoolstatus")) {
							radStool.setWidgetValue(templateValuesMap
									.get("pnotespegistoolstatus"), true);
							tbStool.setText(templateValuesMap
									.get("pnotespegistoolcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpegistoolstatus")) {
							radStool.setWidgetValue(templateValuesMap
									.get("pnotestpegistoolstatus"), true);
							tbStool.setText(templateValuesMap
									.get("pnotestpegistoolcmnt"));
						}
					}
					if ((giList != null && giList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(giList!=null && giList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbGIFreeForm = new TextArea();
						tbGIFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbGIFreeForm);
						freeHp.setCellWidth(tbGIFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespegifreecmnt")) {
							tbGIFreeForm.setText(templateValuesMap
									.get("pnotespegifreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpegifreecmnt")) {
							tbGIFreeForm.setText(templateValuesMap
									.get("pnotestpegifreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbGIExBill = new CheckBox("Procedure");
					cbGIExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbGIExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespegiabd")) {
						HashMap<String, String> m = billMap
								.get("pnotespegiabd");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespegiabd", biw);
						cbGIExBill.setValue(true);

					}
					cbGIExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbGIExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbGIExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put(
											"pnotespegiabd", biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespegiabd");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("GU"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int guRowCount = 0;
				Label lbGU = new Label("GU");
				lbGU.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbGU);
				final FlexTable guTable = new FlexTable();
				guTable.setWidth("100%");
				guTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(guTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int guLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#GU"))
					guLoopCountMax = sectionsFieldMap.get("Sections#Exam#GU")
							.size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#GU"))
					guLoopCountMax = 0;
				else
					guLoopCountMax = 1;
				List<String> guList = sectionsFieldMap.get("Sections#Exam#GU");
				for (int j = 0; j < guLoopCountMax; j++) {
					if ((guList != null && guList.get(j).equals("Gender"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						Label lbGender = new Label("Gender:");
						lbGender
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radGender = new CustomRadioButtonGroup("gender");
						radPenis = new CustomRadioButtonGroup("penis");
						tbPenis = new TextArea();
						tbPenis.setVisible(false);
						tbPenis.setWidth(textWidth);

						radTestes = new CustomRadioButtonGroup("testes");
						tbTestes = new TextArea();
						tbTestes.setVisible(false);
						tbTestes.setWidth(textWidth);

						radProstate = new CustomRadioButtonGroup("prostate");
						tbProstate = new TextArea();
						tbProstate.setVisible(false);
						tbProstate.setWidth(textWidth);

						radExtGen = new CustomRadioButtonGroup("extgen");
						tbExtGen = new TextArea();
						tbExtGen.setVisible(false);
						tbExtGen.setWidth(textWidth);

						radCervix = new CustomRadioButtonGroup("cervix");
						tbCervix = new TextArea();
						tbCervix.setVisible(false);
						tbCervix.setWidth(textWidth);

						radUteAdn = new CustomRadioButtonGroup("uteradn");
						tbUteAdn = new TextArea();
						tbUteAdn.setVisible(false);
						tbUteAdn.setWidth(textWidth);
						final int r1 = guRowCount;
						radGender.addItem("Male", "1", new Command() {
							@Override
							public void execute() {
								cbPenis = new CheckBox("Penis");
								cbPenis
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbPenis.getElement().getStyle().setMarginLeft(
										50, Unit.PX);

								final int r11 = r1 + 1;
								radPenis.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbPenis.setVisible(false);
										cbPenis.setValue(true, true);
									}
								});
								radPenis.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbPenis.setVisible(true);
												cbPenis.setValue(true, true);
											}
										});
								radPenis.setEnable(false);
								tbPenis.setEnabled(false);
								cbPenis
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbPenis.getValue()) {
													radPenis.setEnable(true);
													tbPenis.setEnabled(true);
												} else {
													radPenis.setEnable(false);
													tbPenis.setEnabled(false);
												}
											}
										});
								guTable.setWidget(r11, 0, cbPenis);
								guTable.setWidget(r11, 1, radPenis);
								guTable.setWidget(r11 + 1, 0, tbPenis);
								guTable.getFlexCellFormatter().setColSpan(
										r11 + 1, 0, 2);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r11, 0,
												HasVerticalAlignment.ALIGN_TOP);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r11, 1,
												HasVerticalAlignment.ALIGN_TOP);

								cbTestes = new CheckBox("Testes");
								cbTestes
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbTestes.getElement().getStyle().setMarginLeft(
										50, Unit.PX);

								final int r12 = r1 + 4;
								radTestes.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbTestes.setVisible(false);
										cbTestes.setValue(true, true);
									}
								});
								radTestes.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbTestes.setVisible(true);
												cbTestes.setValue(true, true);
											}
										});
								radTestes.setEnable(false);
								tbTestes.setEnabled(false);
								cbTestes
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbTestes.getValue()) {
													radTestes.setEnable(true);
													tbTestes.setEnabled(true);
												} else {
													radTestes.setEnable(false);
													tbTestes.setEnabled(false);
												}
											}
										});
								guTable.setWidget(r12, 0, cbTestes);
								guTable.setWidget(r12, 1, radTestes);
								guTable.setWidget(r12 + 1, 0, tbTestes);
								guTable.getFlexCellFormatter().setColSpan(
										r12 + 1, 0, 2);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r12, 0,
												HasVerticalAlignment.ALIGN_TOP);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r12, 1,
												HasVerticalAlignment.ALIGN_TOP);

								cbProstate = new CheckBox("Prostate");
								cbProstate
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbProstate.getElement().getStyle()
										.setMarginLeft(50, Unit.PX);

								final int r13 = r1 + 6;
								radProstate.addItem("Normal", "1",
										new Command() {
											@Override
											public void execute() {
												tbProstate.setVisible(false);
												cbProstate.setValue(true, true);
											}
										});
								radProstate.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbProstate.setVisible(true);
												cbProstate.setValue(true, true);
											}
										});
								radProstate.setEnable(false);
								tbProstate.setEnabled(false);
								cbProstate
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbProstate.getValue()) {
													radProstate.setEnable(true);
													tbProstate.setEnabled(true);
												} else {
													radProstate
															.setEnable(false);
													tbProstate
															.setEnabled(false);
												}
											}
										});
								guTable.setWidget(r13, 0, cbProstate);
								guTable.setWidget(r13, 1, radProstate);
								guTable.setWidget(r13 + 1, 0, tbProstate);
								guTable.getFlexCellFormatter().setColSpan(
										r13 + 1, 0, 2);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r13, 0,
												HasVerticalAlignment.ALIGN_TOP);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r13, 1,
												HasVerticalAlignment.ALIGN_TOP);

							}
						});
						radGender.addItem("Female", "2", new Command() {
							@Override
							public void execute() {
								cbExtGen = new CheckBox("External genitalia");
								cbExtGen
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbExtGen.getElement().getStyle().setMarginLeft(
										50, Unit.PX);

								final int r14 = r1 + 1;
								radExtGen.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbExtGen.setVisible(false);
										cbExtGen.setValue(true, true);
									}
								});
								radExtGen.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {
												tbExtGen.setVisible(true);
												cbExtGen.setValue(true, true);
											}
										});
								radExtGen.setEnable(false);
								tbExtGen.setEnabled(false);
								cbExtGen
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbExtGen.getValue()) {
													radExtGen.setEnable(true);
													tbExtGen.setEnabled(true);
												} else {
													radExtGen.setEnable(false);
													tbExtGen.setEnabled(false);
												}
											}
										});
								guTable.setWidget(r14, 0, cbExtGen);
								guTable.setWidget(r14, 1, radExtGen);
								guTable.setWidget(r14 + 1, 0, tbExtGen);
								guTable.getFlexCellFormatter().setColSpan(
										r14 + 1, 0, 2);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r14, 0,
												HasVerticalAlignment.ALIGN_TOP);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r14, 1,
												HasVerticalAlignment.ALIGN_TOP);

								cbCervix = new CheckBox("Cervix");
								cbCervix
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbCervix.getElement().getStyle().setMarginLeft(
										50, Unit.PX);

								final int r15 = r1 + 4;
								radCervix.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbCervix.setVisible(false);
										cbCervix.setValue(true, true);
									}
								});
								radCervix.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbCervix.setVisible(true);
												cbCervix.setValue(true, true);
											}
										});
								radCervix.setEnable(false);
								tbCervix.setEnabled(false);
								cbCervix
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbCervix.getValue()) {
													radCervix.setEnable(true);
													tbCervix.setEnabled(true);
												} else {
													radCervix.setEnable(false);
													tbCervix.setEnabled(false);
												}
											}
										});
								guTable.setWidget(r15, 0, cbCervix);
								guTable.setWidget(r15, 1, radCervix);
								guTable.setWidget(r15 + 1, 0, tbCervix);
								guTable.getFlexCellFormatter().setColSpan(
										r15 + 1, 0, 2);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r15, 0,
												HasVerticalAlignment.ALIGN_TOP);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r15, 1,
												HasVerticalAlignment.ALIGN_TOP);

								cbUteAdn = new CheckBox("Uterus/adnexa");
								cbUteAdn
										.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
								cbUteAdn.getElement().getStyle().setMarginLeft(
										50, Unit.PX);

								final int r16 = r1 + 6;
								radUteAdn.addItem("Normal", "1", new Command() {
									@Override
									public void execute() {
										tbUteAdn.setVisible(false);
										cbUteAdn.setValue(true, true);
									}
								});
								radUteAdn.addItem("Abnormal", "2",
										new Command() {
											@Override
											public void execute() {

												tbUteAdn.setVisible(true);
												cbUteAdn.setValue(true, true);
											}
										});
								radUteAdn.setEnable(false);
								tbUteAdn.setEnabled(false);
								cbUteAdn
										.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

											@Override
											public void onValueChange(
													ValueChangeEvent<Boolean> arg0) {
												if (cbUteAdn.getValue()) {
													radUteAdn.setEnable(true);
													tbUteAdn.setEnabled(true);
												} else {
													radUteAdn.setEnable(false);
													tbUteAdn.setEnabled(false);
												}
											}
										});
								guTable.setWidget(r16, 0, cbUteAdn);
								guTable.setWidget(r16, 1, radUteAdn);
								guTable.setWidget(r16 + 1, 0, tbUteAdn);
								guTable.getFlexCellFormatter().setColSpan(
										r16 + 1, 0, 2);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r16, 0,
												HasVerticalAlignment.ALIGN_TOP);
								guTable.getFlexCellFormatter()
										.setVerticalAlignment(r16, 1,
												HasVerticalAlignment.ALIGN_TOP);

							}
						});
						guTable.setWidget(guRowCount, 0, lbGender);
						guTable.setWidget(guRowCount, 1, radGender);
						guTable.getFlexCellFormatter().setVerticalAlignment(
								guRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						guTable.getFlexCellFormatter().setVerticalAlignment(
								guRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						guTable.getFlexCellFormatter().setWidth(guRowCount, 0,
								labelWidth);
						guTable.getFlexCellFormatter().setWidth(guRowCount, 1,
								radWidth);
						guTable.getFlexCellFormatter().setVerticalAlignment(
								guRowCount, 2, HasVerticalAlignment.ALIGN_TOP);
						guRowCount++;
						if (templateValuesMap.containsKey("pnotespegugender")) {
							if (templateValuesMap.get("pnotespegugender")
									.equals("Male")) {
								radGender.setWidgetValue("1", true);
								if (templateValuesMap
										.containsKey("pnotespegupenisstatus")) {
									radPenis
											.setWidgetValue(
													templateValuesMap
															.get("pnotespegupenisstatus"),
													true);
									tbPenis.setText(templateValuesMap
											.get("pnotespegupeniscmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotespegutestesstatus")) {
									radTestes.setWidgetValue(templateValuesMap
											.get("pnotespegutestesstatus"),
											true);
									tbTestes.setText(templateValuesMap
											.get("pnotespegutestescmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotespeguproststatus")) {
									radProstate
											.setWidgetValue(
													templateValuesMap
															.get("pnotespeguproststatus"),
													true);
									tbProstate.setText(templateValuesMap
											.get("pnotespeguprostcmnt"));
								}
							} else if (templateValuesMap
									.get("pnotespegugender").equals("Female")) {
								radGender.setWidgetValue("2", true);
								if (templateValuesMap
										.containsKey("pnotespeguextgenstatus")) {
									radExtGen.setWidgetValue(templateValuesMap
											.get("pnotespeguextgenstatus"),
											true);
									tbExtGen.setText(templateValuesMap
											.get("pnotespeguextgencmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotespegucervixstatus")) {
									radCervix.setWidgetValue(templateValuesMap
											.get("pnotespegucervixstatus"),
											true);
									tbCervix.setText(templateValuesMap
											.get("pnotespegucervixcmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotespeguutadnstatus")) {
									radUteAdn
											.setWidgetValue(
													templateValuesMap
															.get("pnotespeguutadnstatus"),
													true);
									tbUteAdn.setText(templateValuesMap
											.get("pnotespeguutadncmnt"));
								}
							}
						} else if (templateValuesMap
								.containsKey("pnotestpegugender")) {
							if (templateValuesMap.get("pnotestpegugender")
									.equals("Male")) {
								radGender.setWidgetValue("1", true);
								if (templateValuesMap
										.containsKey("pnotestpegupenisstatus")) {
									radPenis.setWidgetValue(templateValuesMap
											.get("pnotestpegupenisstatus"),
											true);
									tbPenis.setText(templateValuesMap
											.get("pnotestpegupeniscmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotestpegutestesstatus")) {
									radTestes.setWidgetValue(templateValuesMap
											.get("pnotestpegutestesstatus"),
											true);
									tbTestes.setText(templateValuesMap
											.get("pnotestpegutestescmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotestpeguproststatus")) {
									radProstate
											.setWidgetValue(
													templateValuesMap
															.get("pnotestpeguproststatus"),
													true);
									tbProstate.setText(templateValuesMap
											.get("pnotestpeguprostcmnt"));
								}
							} else if (templateValuesMap.get(
									"pnotestpegugender").equals("Female")) {
								radGender.setWidgetValue("2", true);
								if (templateValuesMap
										.containsKey("pnotestpeguextgenstatus")) {
									radExtGen.setWidgetValue(templateValuesMap
											.get("pnotestpeguextgenstatus"),
											true);
									tbExtGen.setText(templateValuesMap
											.get("pnotestpeguextgencmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotestpegucervixstatus")) {
									radCervix.setWidgetValue(templateValuesMap
											.get("pnotestpegucervixstatus"),
											true);
									tbCervix.setText(templateValuesMap
											.get("pnotestpegucervixcmnt"));
								}
								if (templateValuesMap
										.containsKey("pnotestpeguutadnstatus")) {
									radUteAdn.setWidgetValue(templateValuesMap
											.get("pnotestpeguutadnstatus"),
											true);
									tbUteAdn.setText(templateValuesMap
											.get("pnotestpeguutadncmnt"));
								}
							}
						}
					}
					if ((guList != null && guList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(guList!=null && guList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbGUFreeForm = new TextArea();
						tbGUFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbGUFreeForm);
						freeHp.setCellWidth(tbGUFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespegufreecmnt")) {
							tbGUFreeForm.setText(templateValuesMap
									.get("pnotespegufreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpegufreecmnt")) {
							tbGUFreeForm.setText(templateValuesMap
									.get("pnotestpegufreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbGUExBill = new CheckBox("Procedure");
					cbGUExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbGUExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespegu")) {
						HashMap<String, String> m = billMap.get("pnotespegu");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespegu", biw);
						cbGUExBill.setValue(true);

					}
					cbGUExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbGUExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbGUExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespegu",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespegu");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Lymphatics"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int lympRowCount = 0;
				Label lbLymphatics = new Label("Lymphatics");
				lbLymphatics
						.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbLymphatics);
				final FlexTable lymphaticsTable = new FlexTable();
				lymphaticsTable.getElement().getStyle().setMarginLeft(30,
						Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(lymphaticsTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int lymphaticsLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap
								.containsKey("Sections#Exam#Lymphatics"))
					lymphaticsLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Lymphatics").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap
								.containsKey("Sections#Exam#Lymphatics"))
					lymphaticsLoopCountMax = 0;
				else
					lymphaticsLoopCountMax = 1;
				List<String> lymphaticsList = sectionsFieldMap
						.get("Sections#Exam#Lymphatics");
				for (int j = 0; j < lymphaticsLoopCountMax; j++) {
					if ((lymphaticsList != null && lymphaticsList.get(j)
							.equals("Lymph nodes"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbLympNode = new CheckBox("Lymph nodes");
						cbLympNode
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radLympNode = new CustomRadioButtonGroup("lymnds");
						tbLympNode = new TextArea();
						tbLympNode.setVisible(false);
						tbLympNode.setWidth(textWidth);
						radLympNode.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbLympNode.setVisible(false);
								cbLympNode.setValue(true, true);
							}
						});
						radLympNode.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbLympNode.setVisible(true);
								cbLympNode.setValue(true, true);
							}
						});
						radLympNode.setEnable(false);
						tbLympNode.setEnabled(false);
						cbLympNode
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbLympNode.getValue()) {
											radLympNode.setEnable(true);
											tbLympNode.setEnabled(true);
										} else {
											radLympNode.setEnable(false);
											tbLympNode.setEnabled(false);
										}
									}
								});
						lymphaticsTable.setWidget(lympRowCount, 0, cbLympNode);
						lymphaticsTable.setWidget(lympRowCount, 1, radLympNode);
						lymphaticsTable.setWidget(lympRowCount + 1, 0,
								tbLympNode);
						lymphaticsTable.getFlexCellFormatter().setColSpan(
								lympRowCount + 1, 0, 2);
						lymphaticsTable.getFlexCellFormatter()
								.setVerticalAlignment(lympRowCount, 0,
										HasVerticalAlignment.ALIGN_TOP);
						lymphaticsTable.getFlexCellFormatter()
								.setVerticalAlignment(lympRowCount, 1,
										HasVerticalAlignment.ALIGN_TOP);
						lymphaticsTable.getFlexCellFormatter().setWidth(
								lympRowCount, 0, labelWidth);
						lymphaticsTable.getFlexCellFormatter().setWidth(
								lympRowCount, 1, radWidth);
						lympRowCount = lympRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespelympnodesstatus")) {
							radLympNode.setWidgetValue(templateValuesMap
									.get("pnotespelympnodesstatus"), true);
							tbLympNode.setText(templateValuesMap
									.get("pnotespelympnodescmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpelympnodesstatus")) {
							radLympNode.setWidgetValue(templateValuesMap
									.get("pnotestpelympnodesstatus"), true);
							tbLympNode.setText(templateValuesMap
									.get("pnotestpelympnodescmnt"));
						}
					}
					if ((lymphaticsList != null && lymphaticsList.get(j)
							.equals("Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(lymphaticsList!=null && lymphaticsList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbLympFreeForm = new TextArea();
						tbLympFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbLympFreeForm);
						freeHp.setCellWidth(tbLympFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespelympfreecmnt")) {
							tbLympFreeForm.setText(templateValuesMap
									.get("pnotespelympfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpelympfreecmnt")) {
							tbLympFreeForm.setText(templateValuesMap
									.get("pnotestpelympfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbLympExBill = new CheckBox("Procedure");
					cbLympExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbLympExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespelymph")) {
						HashMap<String, String> m = billMap
								.get("pnotespelymph");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespelymph", biw);
						cbLympExBill.setValue(true);

					}
					cbLympExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbLympExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbLympExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put(
											"pnotespelymph", biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespelymph");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Skin"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int skinRowCount = 0;
				Label lbSkin = new Label("Skin");
				lbSkin.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbSkin);
				final FlexTable skinTable = new FlexTable();
				skinTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(skinTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);
				int skinLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Skin"))
					skinLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Skin").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#Skin"))
					skinLoopCountMax = 0;
				else
					skinLoopCountMax = 1;
				List<String> skinList = sectionsFieldMap
						.get("Sections#Exam#Skin");
				for (int j = 0; j < skinLoopCountMax; j++) {
					if ((skinList != null && skinList.get(j).equals(
							"Skin & SQ tissue"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbSkinSQTissue = new CheckBox(
								"Skin & SQ tissue (describe any rash)");
						cbSkinSQTissue
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radSkinSQTissue = new CustomRadioButtonGroup("sksq");
						tbSkinSQTissue = new TextArea();
						tbSkinSQTissue.setVisible(false);
						tbSkinSQTissue.setWidth(textWidth);
						radSkinSQTissue.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbSkinSQTissue.setVisible(false);
								cbSkinSQTissue.setValue(true, true);
							}
						});
						radSkinSQTissue.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbSkinSQTissue.setVisible(true);
								cbSkinSQTissue.setValue(true, true);
							}
						});
						radSkinSQTissue.setEnable(false);
						tbSkinSQTissue.setEnabled(false);
						cbSkinSQTissue
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbSkinSQTissue.getValue()) {
											radSkinSQTissue.setEnable(true);
											tbSkinSQTissue.setEnabled(true);
										} else {
											radSkinSQTissue.setEnable(false);
											tbSkinSQTissue.setEnabled(false);
										}
									}
								});
						skinTable.setWidget(skinRowCount, 0, cbSkinSQTissue);
						skinTable.setWidget(skinRowCount, 1, radSkinSQTissue);
						skinTable
								.setWidget(skinRowCount + 1, 0, tbSkinSQTissue);
						skinTable.getFlexCellFormatter().setColSpan(
								skinRowCount + 1, 0, 2);
						skinTable.getFlexCellFormatter()
								.setVerticalAlignment(skinRowCount, 0,
										HasVerticalAlignment.ALIGN_TOP);
						skinTable.getFlexCellFormatter()
								.setVerticalAlignment(skinRowCount, 1,
										HasVerticalAlignment.ALIGN_TOP);
						skinTable.getFlexCellFormatter().setWidth(skinRowCount,
								0, labelWidth);
						skinTable.getFlexCellFormatter().setWidth(skinRowCount,
								1, radWidth);
						skinRowCount = skinRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeskintissuestatus")) {
							radSkinSQTissue.setWidgetValue(templateValuesMap
									.get("pnotespeskintissuestatus"), true);
							tbSkinSQTissue.setText(templateValuesMap
									.get("pnotespeskintissuecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeskintissuestatus")) {
							radSkinSQTissue.setWidgetValue(templateValuesMap
									.get("pnotestpeskintissuestatus"), true);
							tbSkinSQTissue.setText(templateValuesMap
									.get("pnotestpeskintissuecmnt"));
						}
					}

					if ((skinList != null && skinList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(skinList!=null && skinList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbSkinFreeForm = new TextArea();
						tbSkinFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbSkinFreeForm);
						freeHp.setCellWidth(tbSkinFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespeskinfreecmnt")) {
							tbSkinFreeForm.setText(templateValuesMap
									.get("pnotespeskinfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeskinfreecmnt")) {
							tbSkinFreeForm.setText(templateValuesMap
									.get("pnotestpeskinfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbSkinExBill = new CheckBox("Procedure");
					cbSkinExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbSkinExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespeskin")) {
						HashMap<String, String> m = billMap.get("pnotespeskin");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespeskin", biw);
						cbSkinExBill.setValue(true);

					}
					cbSkinExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbSkinExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbSkinExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespeskin",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespeskin");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("MS"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int msRowCount = 0;
				Label lbMS = new Label("MS");
				lbMS.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbMS);
				final FlexTable msTable = new FlexTable();
				msTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(msTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);
				int msLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#MS"))
					msLoopCountMax = sectionsFieldMap.get("Sections#Exam#MS")
							.size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#MS"))
					msLoopCountMax = 0;
				else
					msLoopCountMax = 1;
				List<String> msList = sectionsFieldMap.get("Sections#Exam#MS");
				for (int j = 0; j < msLoopCountMax; j++) {
					if ((msList != null && msList.get(j).equals(
							"Gait & station"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbGaitStat = new CheckBox("Gait & station");
						cbGaitStat
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radGaitStat = new CustomRadioButtonGroup("gaitsec");
						tbGaitStat = new TextArea();
						tbGaitStat.setVisible(false);
						tbGaitStat.setWidth(textWidth);
						radGaitStat.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbGaitStat.setVisible(false);
								cbGaitStat.setValue(true, true);
							}
						});
						radGaitStat.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbGaitStat.setVisible(true);
								cbGaitStat.setValue(true, true);
							}
						});
						radGaitStat.setEnable(false);
						tbGaitStat.setEnabled(false);
						cbGaitStat
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbGaitStat.getValue()) {
											radGaitStat.setEnable(true);
											tbGaitStat.setEnabled(true);
										} else {
											radGaitStat.setEnable(false);
											tbGaitStat.setEnabled(false);
										}
									}
								});
						msTable.setWidget(msRowCount, 0, cbGaitStat);
						msTable.setWidget(msRowCount, 1, radGaitStat);
						msTable.setWidget(msRowCount + 1, 0, tbGaitStat);
						msTable.getFlexCellFormatter().setColSpan(
								msRowCount + 1, 0, 2);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 0,
								labelWidth);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 1,
								radWidth);
						msRowCount = msRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespemsgaitststatus")) {
							radGaitStat.setWidgetValue(templateValuesMap
									.get("pnotespemsgaitststatus"), true);
							tbGaitStat.setText(templateValuesMap
									.get("pnotespemsgaitstcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpemsgaitststatus")) {
							radGaitStat.setWidgetValue(templateValuesMap
									.get("pnotestpemsgaitststatus"), true);
							tbGaitStat.setText(templateValuesMap
									.get("pnotestpemsgaitstcmnt"));
						}
					}

					if ((msList != null && msList.get(j).equals("Digits_nails"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbDigitsNails = new CheckBox("Digits, nails");
						cbDigitsNails
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radDigitsNails = new CustomRadioButtonGroup("dignails");
						tbDigitsNails = new TextArea();
						tbDigitsNails.setVisible(false);
						tbDigitsNails.setWidth(textWidth);
						radDigitsNails.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbDigitsNails.setVisible(false);
								cbDigitsNails.setValue(true, true);
							}
						});
						radDigitsNails.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbDigitsNails.setVisible(true);
								cbDigitsNails.setValue(true, true);
							}
						});
						radDigitsNails.setEnable(false);
						tbDigitsNails.setEnabled(false);
						cbDigitsNails
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbDigitsNails.getValue()) {
											radDigitsNails.setEnable(true);
											tbDigitsNails.setEnabled(true);
										} else {
											radDigitsNails.setEnable(false);
											tbDigitsNails.setEnabled(false);
										}
									}
								});
						msTable.setWidget(msRowCount, 0, cbDigitsNails);
						msTable.setWidget(msRowCount, 1, radDigitsNails);
						msTable.setWidget(msRowCount + 1, 0, tbDigitsNails);
						msTable.getFlexCellFormatter().setColSpan(
								msRowCount + 1, 0, 2);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 0,
								labelWidth);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 1,
								radWidth);
						msRowCount = msRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespemsdignlsstatus")) {
							radDigitsNails.setWidgetValue(templateValuesMap
									.get("pnotespemsdignlsstatus"), true);
							tbDigitsNails.setText(templateValuesMap
									.get("pnotespemsdignlscmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpemsdignlsstatus")) {
							radDigitsNails.setWidgetValue(templateValuesMap
									.get("pnotestpemsdignlsstatus"), true);
							tbDigitsNails.setText(templateValuesMap
									.get("pnotestpemsdignlscmnt"));
						}
					}

					if ((msList != null && msList.get(j)
							.equals("ROM_stability"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbRomStability = new CheckBox("ROM, stability");
						cbRomStability
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radRomStability = new CustomRadioButtonGroup("romstab");
						tbRomStability = new TextArea();
						tbRomStability.setVisible(false);
						tbRomStability.setWidth(textWidth);
						radRomStability.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbRomStability.setVisible(false);
								cbRomStability.setValue(true, true);
							}
						});
						radRomStability.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbRomStability.setVisible(true);
								cbRomStability.setValue(true, true);
							}
						});
						radRomStability.setEnable(false);
						tbRomStability.setEnabled(false);
						cbRomStability
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbRomStability.getValue()) {
											radRomStability.setEnable(true);
											tbRomStability.setEnabled(true);
										} else {
											radRomStability.setEnable(false);
											tbRomStability.setEnabled(false);
										}
									}
								});
						msTable.setWidget(msRowCount, 0, cbRomStability);
						msTable.setWidget(msRowCount, 1, radRomStability);
						msTable.setWidget(msRowCount + 1, 0, tbRomStability);
						msTable.getFlexCellFormatter().setColSpan(
								msRowCount + 1, 0, 2);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 0,
								labelWidth);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 1,
								radWidth);
						msRowCount = msRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespemsromstbstatus")) {
							radRomStability.setWidgetValue(templateValuesMap
									.get("pnotespemsromstbstatus"), true);
							tbRomStability.setText(templateValuesMap
									.get("pnotespemsromstbcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpemsromstbstatus")) {
							radRomStability.setWidgetValue(templateValuesMap
									.get("pnotestpemsromstbstatus"), true);
							tbRomStability.setText(templateValuesMap
									.get("pnotestpemsromstbcmnt"));
						}
					}

					if ((msList != null && msList.get(j).equals(
							"Joints_bones_muscles"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbJntBnsMusc = new CheckBox("Joints, bones, muscles");
						cbJntBnsMusc
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radJntBnsMusc = new CustomRadioButtonGroup("jbm");
						tbJntBnsMusc = new TextArea();
						tbJntBnsMusc.setVisible(false);
						tbJntBnsMusc.setWidth(textWidth);
						radJntBnsMusc.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbJntBnsMusc.setVisible(false);
								cbJntBnsMusc.setValue(true, true);
							}
						});
						radJntBnsMusc.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbJntBnsMusc.setVisible(true);
								cbJntBnsMusc.setValue(true, true);
							}
						});
						radJntBnsMusc.setEnable(false);
						tbJntBnsMusc.setEnabled(false);
						cbJntBnsMusc
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbJntBnsMusc.getValue()) {
											radJntBnsMusc.setEnable(true);
											tbJntBnsMusc.setEnabled(true);
										} else {
											radJntBnsMusc.setEnable(false);
											tbJntBnsMusc.setEnabled(false);
										}
									}
								});
						msTable.setWidget(msRowCount, 0, cbJntBnsMusc);
						msTable.setWidget(msRowCount, 1, radJntBnsMusc);
						msTable.setWidget(msRowCount + 1, 0, tbJntBnsMusc);
						msTable.getFlexCellFormatter().setColSpan(
								msRowCount + 1, 0, 2);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 0,
								labelWidth);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 1,
								radWidth);
						msRowCount = msRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespemsjntbnsmusstatus")) {
							radJntBnsMusc.setWidgetValue(templateValuesMap
									.get("pnotespemsjntbnsmusstatus"), true);
							tbJntBnsMusc.setText(templateValuesMap
									.get("pnotespemsjntbnsmuscmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpemsjntbnsmusstatus")) {
							radJntBnsMusc.setWidgetValue(templateValuesMap
									.get("pnotestpemsjntbnsmusstatus"), true);
							tbJntBnsMusc.setText(templateValuesMap
									.get("pnotestpemsjntbnsmuscmnt"));
						}
					}

					if ((msList != null && msList.get(j).equals(
							"Muscle strength & tone"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbMuscStrg = new CheckBox("Muscle strength & tone");
						cbMuscStrg
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radMuscStrg = new CustomRadioButtonGroup("musstrtone");
						tbMuscStrg = new TextArea();
						tbMuscStrg.setVisible(false);
						tbMuscStrg.setWidth(textWidth);
						radMuscStrg.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbMuscStrg.setVisible(false);
								cbMuscStrg.setValue(true, true);
							}
						});
						radMuscStrg.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbMuscStrg.setVisible(true);
								cbMuscStrg.setValue(true, true);
							}
						});
						radMuscStrg.setEnable(false);
						tbMuscStrg.setEnabled(false);
						cbMuscStrg
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbMuscStrg.getValue()) {
											radMuscStrg.setEnable(true);
											tbMuscStrg.setEnabled(true);
										} else {
											radMuscStrg.setEnable(false);
											tbMuscStrg.setEnabled(false);
										}
									}
								});
						msTable.setWidget(msRowCount, 0, cbMuscStrg);
						msTable.setWidget(msRowCount, 1, radMuscStrg);
						msTable.setWidget(msRowCount + 1, 0, tbMuscStrg);
						msTable.getFlexCellFormatter().setColSpan(
								msRowCount + 1, 0, 2);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 0, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setVerticalAlignment(
								msRowCount, 1, HasVerticalAlignment.ALIGN_TOP);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 0,
								labelWidth);
						msTable.getFlexCellFormatter().setWidth(msRowCount, 1,
								radWidth);
						msRowCount = msRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespemsmusstrtnstatus")) {
							radMuscStrg.setWidgetValue(templateValuesMap
									.get("pnotespemsmusstrtnstatus"), true);
							tbMuscStrg.setText(templateValuesMap
									.get("pnotespemsmusstrtncmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpemsmusstrtnstatus")) {
							radMuscStrg.setWidgetValue(templateValuesMap
									.get("pnotestpemsmusstrtnstatus"), true);
							tbMuscStrg.setText(templateValuesMap
									.get("pnotestpemsmusstrtncmnt"));
						}
					}

					if ((msList != null && msList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(msList!=null && msList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbMSFreeForm = new TextArea();
						tbMSFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbMSFreeForm);
						freeHp.setCellWidth(tbMSFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespemsfreecmnt")) {
							tbMSFreeForm.setText(templateValuesMap
									.get("pnotespemsfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpemsfreecmnt")) {
							tbMSFreeForm.setText(templateValuesMap
									.get("pnotestpemsfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbMSExBill = new CheckBox("Procedure");
					cbMSExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbMSExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespems")) {
						HashMap<String, String> m = billMap.get("pnotespems");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespems", biw);
						cbMSExBill.setValue(true);

					}
					cbMSExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbMSExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbMSExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put("pnotespems",
											biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespems");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Neuro"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int neuroRowCount = 0;
				Label lbNeuro = new Label("Neuro");
				lbNeuro.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbNeuro);
				final FlexTable neuroTable = new FlexTable();
				neuroTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(neuroTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);
				int neuroLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Neuro"))
					neuroLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Neuro").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#Neuro"))
					neuroLoopCountMax = 0;
				else
					neuroLoopCountMax = 1;
				List<String> neuroList = sectionsFieldMap
						.get("Sections#Exam#Neuro");
				for (int j = 0; j < neuroLoopCountMax; j++) {
					if ((neuroList != null && neuroList.get(j).equals(
							"Cranial nerves (note deficits)"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbCranNerves = new CheckBox(
								"Cranial nerves (note deficits)");
						cbCranNerves
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radCranNerves = new CustomRadioButtonGroup("cranner");
						tbCranNerves = new TextArea();
						tbCranNerves.setVisible(false);
						tbCranNerves.setWidth(textWidth);
						radCranNerves.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbCranNerves.setVisible(false);
								cbCranNerves.setValue(true, true);
							}
						});
						radCranNerves.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbCranNerves.setVisible(true);
								cbCranNerves.setValue(true, true);
							}
						});
						radCranNerves.setEnable(false);
						tbCranNerves.setEnabled(false);
						cbCranNerves
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbCranNerves.getValue()) {
											radCranNerves.setEnable(true);
											tbCranNerves.setEnabled(true);
										} else {
											radCranNerves.setEnable(false);
											tbCranNerves.setEnabled(false);
										}
									}
								});
						neuroTable.setWidget(neuroRowCount, 0, cbCranNerves);
						neuroTable.setWidget(neuroRowCount, 1, radCranNerves);
						neuroTable
								.setWidget(neuroRowCount + 1, 0, tbCranNerves);
						neuroTable.getFlexCellFormatter().setColSpan(
								neuroRowCount + 1, 0, 2);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 0, labelWidth);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 1, radWidth);
						neuroRowCount = neuroRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeneurocrnervstatus")) {
							radCranNerves.setWidgetValue(templateValuesMap
									.get("pnotespeneurocrnervstatus"), true);
							tbCranNerves.setText(templateValuesMap
									.get("pnotespeneurocrnervcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneurocrnervstatus")) {
							radCranNerves.setWidgetValue(templateValuesMap
									.get("pnotestpeneurocrnervstatus"), true);
							tbCranNerves.setText(templateValuesMap
									.get("pnotestpeneurocrnervcmnt"));
						}
					}

					if ((neuroList != null && neuroList.get(j).equals("DTRs"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbDTRs = new CheckBox("DTRs");
						cbDTRs
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radDTRs = new CustomRadioButtonGroup("dtrs");
						tbDTRs = new TextArea();
						tbDTRs.setVisible(false);
						tbDTRs.setWidth(textWidth);
						radDTRs.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbDTRs.setVisible(false);
								cbDTRs.setValue(true, true);
							}
						});
						radDTRs.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbDTRs.setVisible(true);
								cbDTRs.setValue(true, true);
							}
						});
						radDTRs.setEnable(false);
						tbDTRs.setEnabled(false);
						cbDTRs
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbDTRs.getValue()) {
											radDTRs.setEnable(true);
											tbDTRs.setEnabled(true);
										} else {
											radDTRs.setEnable(false);
											tbDTRs.setEnabled(false);
										}
									}
								});
						neuroTable.setWidget(neuroRowCount, 0, cbDTRs);
						neuroTable.setWidget(neuroRowCount, 1, radDTRs);
						neuroTable.setWidget(neuroRowCount + 1, 0, tbDTRs);
						neuroTable.getFlexCellFormatter().setColSpan(
								neuroRowCount + 1, 0, 2);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 0, labelWidth);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 1, radWidth);
						neuroRowCount = neuroRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeneurodtrsstatus")) {
							radDTRs.setWidgetValue(templateValuesMap
									.get("pnotespeneurodtrsstatus"), true);
							tbDTRs.setText(templateValuesMap
									.get("pnotespeneurodtrscmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneurodtrsstatus")) {
							radDTRs.setWidgetValue(templateValuesMap
									.get("pnotestpeneurodtrsstatus"), true);
							tbDTRs.setText(templateValuesMap
									.get("pnotestpeneurodtrscmnt"));
						}
					}

					if ((neuroList != null && neuroList.get(j).equals("Motor"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbMotor = new CheckBox("Motor");
						cbMotor
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radMotor = new CustomRadioButtonGroup("motors");
						tbMotor = new TextArea();
						tbMotor.setVisible(false);
						tbMotor.setWidth(textWidth);
						radMotor.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbMotor.setVisible(false);
								cbMotor.setValue(true, true);
							}
						});
						radMotor.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {
								tbMotor.setVisible(true);
								cbMotor.setValue(true, true);
							}
						});
						radMotor.setEnable(false);
						tbMotor.setEnabled(false);
						cbMotor
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbMotor.getValue()) {
											radMotor.setEnable(true);
											tbMotor.setEnabled(true);
										} else {
											radMotor.setEnable(false);
											tbMotor.setEnabled(false);
										}
									}
								});
						neuroTable.setWidget(neuroRowCount, 0, cbMotor);
						neuroTable.setWidget(neuroRowCount, 1, radMotor);
						neuroTable.setWidget(neuroRowCount + 1, 0, tbMotor);
						neuroTable.getFlexCellFormatter().setColSpan(
								neuroRowCount + 1, 0, 2);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 0, labelWidth);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 1, radWidth);
						neuroRowCount = neuroRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeneuromotorstatus")) {
							radMotor.setWidgetValue(templateValuesMap
									.get("pnotespeneuromotorstatus"), true);
							tbMotor.setText(templateValuesMap
									.get("pnotespeneuromotorcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneuromotorstatus")) {
							radMotor.setWidgetValue(templateValuesMap
									.get("pnotestpeneuromotorstatus"), true);
							tbMotor.setText(templateValuesMap
									.get("pnotestpeneuromotorcmnt"));
						}
					}

					if ((neuroList != null && neuroList.get(j).equals(
							"Sensation"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbSensation = new CheckBox("Sensation");
						cbSensation
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radSensation = new CustomRadioButtonGroup("sensation");
						tbSensation = new TextArea();
						tbSensation.setVisible(false);
						tbSensation.setWidth(textWidth);
						radSensation.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbSensation.setVisible(false);
								cbSensation.setValue(true, true);
							}
						});
						radSensation.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbSensation.setVisible(true);
								cbSensation.setValue(true, true);
							}
						});
						radSensation.setEnable(false);
						tbSensation.setEnabled(false);
						cbSensation
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbSensation.getValue()) {
											radSensation.setEnable(true);
											tbSensation.setEnabled(true);
										} else {
											radSensation.setEnable(false);
											tbSensation.setEnabled(false);
										}
									}
								});
						neuroTable.setWidget(neuroRowCount, 0, cbSensation);
						neuroTable.setWidget(neuroRowCount, 1, radSensation);
						neuroTable.setWidget(neuroRowCount + 1, 0, tbSensation);
						neuroTable.getFlexCellFormatter().setColSpan(
								neuroRowCount + 1, 0, 2);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setVerticalAlignment(
								neuroRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 0, labelWidth);
						neuroTable.getFlexCellFormatter().setWidth(
								neuroRowCount, 1, radWidth);
						neuroRowCount = neuroRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespeneurosnststatus")) {
							radSensation.setWidgetValue(templateValuesMap
									.get("pnotespeneurosnststatus"), true);
							tbSensation.setText(templateValuesMap
									.get("pnotespeneurosnstcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneurosnststatus")) {
							radSensation.setWidgetValue(templateValuesMap
									.get("pnotestpeneurosnststatus"), true);
							tbSensation.setText(templateValuesMap
									.get("pnotestpeneurosnstcmnt"));
						}
					}
					if ((neuroList != null && neuroList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(neuroList!=null && neuroList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbNeuroFreeForm = new TextArea();
						tbNeuroFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbNeuroFreeForm);
						freeHp.setCellWidth(tbNeuroFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespeneurofreecmnt")) {
							tbNeuroFreeForm.setText(templateValuesMap
									.get("pnotespeneurofreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpeneurofreecmnt")) {
							tbNeuroFreeForm.setText(templateValuesMap
									.get("pnotestpeneurofreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbNeuroExBill = new CheckBox("Procedure");
					cbNeuroExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbNeuroExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespeneuro")) {
						HashMap<String, String> m = billMap
								.get("pnotespeneuro");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespeneuro", biw);
						cbNeuroExBill.setValue(true);

					}
					cbNeuroExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbNeuroExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbNeuroExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put(
											"pnotespeneuro", biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespeneuro");
								biw.setVisible(false);
							}
						}
					});
				}
			}

			if ((secList != null && secList.get(i).equals("Psych"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				int psychRowCount = 0;
				Label lbPsych = new Label("Psych");
				lbPsych.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
				examPanel.add(lbPsych);
				final FlexTable psychTable = new FlexTable();
				psychTable.getElement().getStyle().setMarginLeft(30, Unit.PX);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setSpacing(10);
				VerticalPanel rightPanel = new VerticalPanel();
				rightPanel.setWidth("100%");
				rightPanel
						.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
				rightPanel.setSpacing(5);
				hp.setWidth("100%");
				hp.add(psychTable);
				hp.add(rightPanel);
				hp.setCellWidth(rightPanel, "60%");
				examPanel.add(hp);

				int psychLoopCountMax = 0;
				if (sectionsFieldMap.containsKey("Sections")
						&& sectionsFieldMap.containsKey("Sections#Exam#Psych"))
					psychLoopCountMax = sectionsFieldMap.get(
							"Sections#Exam#Psych").size();
				else if (sectionsFieldMap.containsKey("Sections")
						&& !sectionsFieldMap.containsKey("Sections#Exam#Psych"))
					psychLoopCountMax = 0;
				else
					psychLoopCountMax = 1;
				List<String> psychList = sectionsFieldMap
						.get("Sections#Exam#Psych");
				for (int j = 0; j < psychLoopCountMax; j++) {
					if ((psychList != null && psychList.get(j).equals(
							"Judgment & insight"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbJudIns = new CheckBox("Judgment & insight");
						cbJudIns
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radJudIns = new CustomRadioButtonGroup("judins");
						tbJudIns = new TextArea();
						tbJudIns.setVisible(false);
						tbJudIns.setWidth(textWidth);
						radJudIns.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbJudIns.setVisible(false);
								cbJudIns.setValue(true, true);
							}
						});
						radJudIns.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbJudIns.setVisible(true);
								cbJudIns.setValue(true, true);
							}
						});
						radJudIns.setEnable(false);
						tbJudIns.setEnabled(false);
						cbJudIns
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbJudIns.getValue()) {
											radJudIns.setEnable(true);
											tbJudIns.setEnabled(true);
										} else {
											radJudIns.setEnable(false);
											tbJudIns.setEnabled(false);
										}
									}
								});
						psychTable.setWidget(psychRowCount, 0, cbJudIns);
						psychTable.setWidget(psychRowCount, 1, radJudIns);
						psychTable.setWidget(psychRowCount + 1, 0, tbJudIns);
						psychTable.getFlexCellFormatter().setColSpan(
								psychRowCount + 1, 0, 2);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 0, labelWidth);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 1, radWidth);
						psychRowCount = psychRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespepsychjudinsstatus")) {
							radJudIns.setWidgetValue(templateValuesMap
									.get("pnotespepsychjudinsstatus"), true);
							tbJudIns.setText(templateValuesMap
									.get("pnotespepsychjudinscmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpepsychjudinsstatus")) {
							radJudIns.setWidgetValue(templateValuesMap
									.get("pnotestpepsychjudinsstatus"), true);
							tbJudIns.setText(templateValuesMap
									.get("pnotestpepsychjudinscmnt"));
						}
					}

					if ((psychList != null && psychList.get(j).equals(
							"Mood & affect"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbMoodEffect = new CheckBox("Mood & affect");
						cbMoodEffect
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radMoodEffect = new CustomRadioButtonGroup("moodeff");
						tbMoodEffect = new TextArea();
						tbMoodEffect.setVisible(false);
						tbMoodEffect.setWidth(textWidth);
						radMoodEffect.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbMoodEffect.setVisible(false);
								cbMoodEffect.setValue(true, true);
							}
						});
						radMoodEffect.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbMoodEffect.setVisible(true);
								cbMoodEffect.setValue(true, true);
							}
						});
						radMoodEffect.setEnable(false);
						tbMoodEffect.setEnabled(false);
						cbMoodEffect
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbMoodEffect.getValue()) {
											radMoodEffect.setEnable(true);
											tbMoodEffect.setEnabled(true);
										} else {
											radMoodEffect.setEnable(false);
											tbMoodEffect.setEnabled(false);
										}
									}
								});
						psychTable.setWidget(psychRowCount, 0, cbMoodEffect);
						psychTable.setWidget(psychRowCount, 1, radMoodEffect);
						psychTable
								.setWidget(psychRowCount + 1, 0, tbMoodEffect);
						psychTable.getFlexCellFormatter().setColSpan(
								psychRowCount + 1, 0, 2);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 0, labelWidth);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 1, radWidth);
						psychRowCount = psychRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespepsychmoodeffstatus")) {
							radMoodEffect.setWidgetValue(templateValuesMap
									.get("pnotespepsychmoodeffstatus"), true);
							tbMoodEffect.setText(templateValuesMap
									.get("pnotespepsychmoodeffcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpepsychmoodeffstatus")) {
							radMoodEffect.setWidgetValue(templateValuesMap
									.get("pnotestpepsychmoodeffstatus"), true);
							tbMoodEffect.setText(templateValuesMap
									.get("pnotestpepsychmoodeffcmnt"));
						}
					}

					if ((psychList != null && psychList.get(j).equals(
							"Oriented to time_place_person"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbOrTimePlcPers = new CheckBox(
								"Oriented to time, place, person");
						cbOrTimePlcPers
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radOrTimePlcPers = new CustomRadioButtonGroup("ortpp");
						tbOrTimePlcPers = new TextArea();
						tbOrTimePlcPers.setVisible(false);
						tbOrTimePlcPers.setWidth(textWidth);
						radOrTimePlcPers.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbOrTimePlcPers.setVisible(false);
								cbOrTimePlcPers.setValue(true, true);
							}
						});
						radOrTimePlcPers.addItem("Abnormal", "2",
								new Command() {
									@Override
									public void execute() {

										tbOrTimePlcPers.setVisible(true);
										cbOrTimePlcPers.setValue(true, true);

									}
								});
						radOrTimePlcPers.setEnable(false);
						tbOrTimePlcPers.setEnabled(false);
						cbOrTimePlcPers
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbOrTimePlcPers.getValue()) {
											radOrTimePlcPers.setEnable(true);
											tbOrTimePlcPers.setEnabled(true);
										} else {
											radOrTimePlcPers.setEnable(false);
											tbOrTimePlcPers.setEnabled(false);
										}
									}
								});
						psychTable.setWidget(psychRowCount, 0, cbOrTimePlcPers);
						psychTable
								.setWidget(psychRowCount, 1, radOrTimePlcPers);
						psychTable.setWidget(psychRowCount + 1, 0,
								tbOrTimePlcPers);
						psychTable.getFlexCellFormatter().setColSpan(
								psychRowCount + 1, 0, 2);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 0, labelWidth);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 1, radWidth);
						psychRowCount = psychRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespepsychorntppstatus")) {
							radOrTimePlcPers.setWidgetValue(templateValuesMap
									.get("pnotespepsychorntppstatus"), true);
							tbOrTimePlcPers.setText(templateValuesMap
									.get("pnotespepsychorntppcmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpepsychorntppstatus")) {
							radOrTimePlcPers.setWidgetValue(templateValuesMap
									.get("pnotestpepsychorntppstatus"), true);
							tbOrTimePlcPers.setText(templateValuesMap
									.get("pnotestpepsychorntppcmnt"));
						}
					}

					if ((psychList != null && psychList.get(j).equals("Memory"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						cbMemory = new CheckBox("Memory");
						cbMemory
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
						radMemory = new CustomRadioButtonGroup("memory");
						tbMemory = new TextArea();
						tbMemory.setVisible(false);
						tbMemory.setWidth(textWidth);
						radMemory.addItem("Normal", "1", new Command() {
							@Override
							public void execute() {
								tbMemory.setVisible(false);
								cbMemory.setValue(true, true);
							}
						});
						radMemory.addItem("Abnormal", "2", new Command() {
							@Override
							public void execute() {

								tbMemory.setVisible(true);
								cbMemory.setValue(true, true);
							}
						});
						radMemory.setEnable(false);
						tbMemory.setEnabled(false);
						cbMemory
								.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

									@Override
									public void onValueChange(
											ValueChangeEvent<Boolean> arg0) {
										if (cbMemory.getValue()) {
											radMemory.setEnable(true);
											tbMemory.setEnabled(true);
										} else {
											radMemory.setEnable(false);
											tbMemory.setEnabled(false);
										}
									}
								});
						psychTable.setWidget(psychRowCount, 0, cbMemory);
						psychTable.setWidget(psychRowCount, 1, radMemory);
						psychTable.setWidget(psychRowCount + 1, 0, tbMemory);
						psychTable.getFlexCellFormatter().setColSpan(
								psychRowCount + 1, 0, 2);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 0,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setVerticalAlignment(
								psychRowCount, 1,
								HasVerticalAlignment.ALIGN_TOP);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 0, labelWidth);
						psychTable.getFlexCellFormatter().setWidth(
								psychRowCount, 1, radWidth);
						psychRowCount = psychRowCount + 2;
						if (templateValuesMap
								.containsKey("pnotespepsychmemorystatus")) {
							radMemory.setWidgetValue(templateValuesMap
									.get("pnotespepsychmemorystatus"), true);
							tbMemory.setText(templateValuesMap
									.get("pnotespepsychmemorycmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpepsychmemorystatus")) {
							radMemory.setWidgetValue(templateValuesMap
									.get("pnotestpepsychmemorystatus"), true);
							tbMemory.setText(templateValuesMap
									.get("pnotestpepsychmemorycmnt"));
						}
					}
					if ((psychList != null && psychList.get(j).equals(
							"Free Form Entry"))
							|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
									.equals(""))) {
						if(psychList!=null && psychList.size()==1)
							hp.setCellWidth(rightPanel, "100%");
						Label lbfreeform = new Label("Free Form Entry");
						lbfreeform
								.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

						tbPsychFreeForm = new TextArea();
						tbPsychFreeForm.setWidth(textWidth);
						HorizontalPanel freeHp = new HorizontalPanel();
						freeHp.setWidth("80%");
						freeHp.setSpacing(5);
						freeHp.add(lbfreeform);
						freeHp.add(tbPsychFreeForm);
						freeHp.setCellWidth(tbPsychFreeForm, "80%");
						rightPanel.add(freeHp);

						if (templateValuesMap
								.containsKey("pnotespepsychfreecmnt")) {
							tbPsychFreeForm.setText(templateValuesMap
									.get("pnotespepsychfreecmnt"));
						} else if (templateValuesMap
								.containsKey("pnotestpepsychfreecmnt")) {
							tbPsychFreeForm.setText(templateValuesMap
									.get("pnotestpepsychfreecmnt"));
						}
					}
				}
				if (formtype == EncounterFormType.TEMPLATE_VALUES
						|| formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES) {
					VerticalPanel billPanel = new VerticalPanel();
					billPanel.setSpacing(2);
					cbPsychExBill = new CheckBox("Procedure");
					cbPsychExBill
							.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					final BillInfoWidget biw = new BillInfoWidget();
					billPanel.add(cbPsychExBill);
					billPanel.add(biw);
					rightPanel.add(billPanel);
					biw.setVisible(false);
					if (isBillables && billMap.containsKey("pnotespepsych")) {
						HashMap<String, String> m = billMap
								.get("pnotespepsych");
						biw.setVisible(true);
						biw.setProceduralCode(new Integer(m.get("proccode")));
						biw.setDiagnosisCode(new Integer(m.get("diagcode")));
						billingFieldsWidgetsMap.put("pnotespepsych", biw);
						cbPsychExBill.setValue(true);

					}
					cbPsychExBill.addClickHandler(new ClickHandler() {

						@Override
						public void onClick(ClickEvent arg0) {
							if (cbPsychExBill.getValue()) {
								if (maxbillables == billingFieldsWidgetsMap
										.size()) {
									Window.alert("Only " + maxbillables
											+ " procedures can be created...");
									cbPsychExBill.setValue(false);
								} else {
									billingFieldsWidgetsMap.put(
											"pnotespepsych", biw);
									biw.setVisible(true);
								}
							} else {
								billingFieldsWidgetsMap.remove("pnotespepsych");
								biw.setVisible(false);
							}
						}
					});
				}
			}
		}
		examTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		examPanel.add(examTable);
	}

	private void createAssessmentPlanTab() {
		FlexTable assessPlanTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Assessment/Plan"))
			loopCountMax = sectionsFieldMap.get("Sections#Assessment/Plan")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Assessment/Plan"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#Assessment/Plan");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("Assessment"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbAssessment = new Label("Assessment");
				tbAssessment = new TextArea();
				tbAssessment.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_A")) {
					tbAssessment.setText(templateValuesMap.get("pnotes_A"));
				} else if (templateValuesMap.containsKey("pnotest_A")) {
					tbAssessment.setText(templateValuesMap.get("pnotest_A"));
				}
				assessPlanTable.setWidget(row, 0, lbAssessment);
				assessPlanTable.setWidget(row++, 1, tbAssessment);
			}
			if ((secList != null && secList.get(i).equals("Plan"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbPlan = new Label("Plan");
				tbPlanAssess = new TextArea();
				tbPlanAssess.setSize("700px", "100px");
				if (templateValuesMap.containsKey("pnotes_P")) {
					tbPlanAssess.setText(templateValuesMap.get("pnotes_P"));
				} else if (templateValuesMap.containsKey("pnotest_P")) {
					tbPlanAssess.setText(templateValuesMap.get("pnotest_P"));
				}
				assessPlanTable.setWidget(row, 0, lbPlan);
				assessPlanTable.setWidget(row++, 1, tbPlanAssess);
			}
		}
		assessPlanTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		assessPlanPanel.add(assessPlanTable);
	}

	private void createFreeFormEntryTab() {
		FlexTable freeFormTable = new FlexTable();
		int row = 0;
		int loopCountMax = 0;
		if (sectionsFieldMap.containsKey("Sections")
				&& sectionsFieldMap.containsKey("Sections#Free Form Entry"))
			loopCountMax = sectionsFieldMap.get("Sections#Free Form Entry")
					.size();
		else if (sectionsFieldMap.containsKey("Sections")
				&& !sectionsFieldMap.containsKey("Sections#Free Form Entry"))
			loopCountMax = 0;
		else
			loopCountMax = 1;
		List<String> secList = sectionsFieldMap.get("Sections#Free Form Entry");
		for (int i = 0; i < loopCountMax; i++) {
			if ((secList != null && secList.get(i).equals("Free Form Entry"))
					|| (formtype == EncounterFormType.ENCOUNTER_NOTE_VALUES && currTemplate
							.equals(""))) {
				Label lbFreeFormEntry = new Label("Free Form Entry");
				editorContainer = new HorizontalPanel();
				rte = new Editor();
				editorContainer.add(rte);
				if (templateValuesMap.containsKey("pnoteshandp")) {
					rte.setHTML(templateValuesMap.get("pnoteshandp"));
				} else if (templateValuesMap.containsKey("pnotesthandp")) {
					rte.setHTML(templateValuesMap.get("pnotesthandp"));
				}
				freeFormTable.setWidget(row, 0, lbFreeFormEntry);
				freeFormTable.setWidget(row++, 1, editorContainer);
			}
		}
		freeFormTable.getFlexCellFormatter().setWidth(0, 0, "155px");
		freeFormPanel.add(freeFormTable);
	}

	public void loadEOC() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////

			String[] params = { patientID };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EpisodeOfCare.getEOCValues",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											for (int i = 0; i < result.length; i++) {
												HashMap<String, String> m = (HashMap<String, String>) result[i];
												eocMap.put(m.get("Id"), m
														.get("eoc_info"));
												eocList.addItem(m
														.get("eoc_info"), m
														.get("Id"));
											}
										} else {

										}
									}
								} catch (Exception e) {
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {

		}
	}

	public void saveEncounterTemplate() {
		HashMap<String, String> map = new HashMap<String, String>();
		String method = "";
		if (formmode == EncounterFormMode.EDIT) {
			method = "org.freemedsoftware.module.EncounterNotesTemplate.mod";
			map.put("id", templateValuesMap.get("id"));
		} else {
			method = "org.freemedsoftware.module.EncounterNotesTemplate.add";
		}
		List<String> sectionArrayList = sectionsFieldMap.get("Sections");
		if (sectionArrayList.contains("Billing Information")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Billing Information");
			if (fieldsArrayList.contains("Procedure Code")) {
				map.put("pnotestproccode", procCodeWidget.getStoredValue());
			}
			if (fieldsArrayList.contains("Diagnosis 1")) {
				map.put("pnotestdiag1", diag1Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Diagnosis 2")) {
				map.put("pnotestdiag2", diag2Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Diagnosis 3")) {
				map.put("pnotestdiag3", diag3Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Diagnosis 4")) {
				map.put("pnotestdiag4", diag4Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Modifier 1")) {
				map.put("pnotestmod1", mod1Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Modifier 2")) {
				map.put("pnotestmod2", mod2Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Modifier 3")) {
				map.put("pnotestmod3", mod3Widget.getStoredValue());
			}
			if (fieldsArrayList.contains("Place Of Service")) {
				map.put("pnotestpos", posWidget.getStoredValue());
			}
			if (fieldsArrayList.contains("Procedural Units")) {
				map.put("pnotestprocunits", posWidget.getStoredValue());
			}
		}
		if (sectionArrayList.contains("SOAP Note")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#SOAP Note");
			if (fieldsArrayList.contains("Subjective")) {
				map.put("pnotest_S", tbSub.getText());
			}
			// if (fieldsArrayList.contains("Objective")) {
			// map.put("pnotest_O", listObj.getItemText(listObj
			// .getSelectedIndex()));
			// }
			if (fieldsArrayList.contains("Assessment")) {
				map.put("pnotest_A", tbAssess.getText());
			}
			if (fieldsArrayList.contains("Plan")) {
				map.put("pnotest_P", tbPlan.getText());
			}
		}

		if (sectionArrayList.contains("IER")) {
			List<String> fieldsArrayList = sectionsFieldMap.get("Sections#IER");
			if (fieldsArrayList.contains("Interval")) {
				map.put("pnotest_I", tbInterval.getText());
			}
			if (fieldsArrayList.contains("Education")) {
				map.put("pnotest_E", tbEducation.getText());
			}
			if (fieldsArrayList.contains("Rx")) {
				map.put("pnotest_R", tbRx.getText());
			}
		}

		if (sectionArrayList.contains("Vitals/Generals")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Vitals/Generals");
			if (fieldsArrayList.contains("Blood Pressure")) {
				map.put("pnotestsbp", tbBp1.getText());
			}
			if (fieldsArrayList.contains("Blood Pressure")) {
				map.put("pnotestdbp", tbBp2.getText());
			}
			if (fieldsArrayList.contains("Temperature")) {
				map.put("pnotesttemp", listTemp.getItemText(listTemp
						.getSelectedIndex()));
			}
			if (fieldsArrayList.contains("Heart Rate")) {
				map.put("pnotestheartrate", tbHeartRate.getText());
			}
			if (fieldsArrayList.contains("Respiratory Rate")) {
				map.put("pnotestresprate", tbRespRate.getText());
			}
			if (fieldsArrayList.contains("Weight")) {
				map.put("pnotestweight", tbWeight.getText());
			}
			if (fieldsArrayList.contains("Height")) {
				map.put("pnotestheight", tbHeight.getText());
			}
			if (fieldsArrayList.contains("BMI")) {
				map.put("pnotestbmi", tbBMIVal.getText());
			}
			if (fieldsArrayList.contains("General (PE)")) {
				map.put("pnotestgeneral", tbGeneral.getText());
			}
		}

		if (sectionArrayList.contains("CC & HPI")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#CC & HPI");
			if (fieldsArrayList.contains("CC")) {
				map.put("pnotestcc", tbCC.getText());
			}
			if (fieldsArrayList.contains("HPI")) {
				map.put("pnotesthpi", tbHPI.getText());
			}
		}

		if (sectionArrayList.contains("Review Of Systems")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Review Of Systems");
			if ((fieldsArrayList != null && fieldsArrayList.contains("General"))
					|| sectionArrayList.isEmpty()) {
				if (cbGeneral.getValue()) {
					map.put("pnotestrosgenralstatus", "1");
					map.put("pnotestrosgenral", tbGeneralRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Head"))
					|| sectionArrayList.isEmpty()) {
				if (cbHead.getValue()) {
					map.put("pnotestrosheadstatus", "1");
					map.put("pnotestroshead", tbHeadRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Eyes"))
					|| sectionArrayList.isEmpty()) {
				if (cbEyesRos.getValue()) {
					map.put("pnotestroseyesstatus", "1");
					String value = "";
					if (cbPoorVision.getValue()) {
						value = value + "poor vision,";
					}
					if (cbEyesPain.getValue()) {
						value = value + "pain,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestroseyes", value);
					map.put("pnotestroseyescmnts", tbEyesRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("ENT"))
					|| sectionArrayList.isEmpty()) {
				if (cbEntRos.getValue()) {
					map.put("pnotestrosentstatus", "1");
					String value = "";
					if (cbSoreThroat.getValue()) {
						value = value + "sore throat,";
					}
					if (cbENTPain.getValue()) {
						value = value + "pain,";
					}
					if (cbCoryza.getValue()) {
						value = value + "coryza,";
					}
					if (cbAcuity.getValue()) {
						value = value + "acuity,";
					}
					if (cbDysphagia.getValue()) {
						value = value + "dysphagia,";
					}
					if(value.length()>0)
					value = value.substring(0, value.length() - 1);
					map.put("pnotestrosent", value);
					map.put("pnotestrosentcmnts", tbENTRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("CV"))
					|| sectionArrayList.isEmpty()) {
				if (cbCVRos.getValue()) {
					map.put("pnotestroscvstatus", "1");
					String value = "";
					if (cbCVPain.getValue()) {
						value = value + "pain,";
					}
					if (cbPalpitations.getValue()) {
						value = value + "palpitations,";
					}
					if (cbHypoHyperTension.getValue()) {
						value = value + "hypo/hypertension,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestroscv", value);
					map.put("pnotestroscvsmnts", tbCVRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Resp"))
					|| sectionArrayList.isEmpty()) {
				if (cbRespRos.getValue()) {
					map.put("pnotestrosrespstatus", "1");
					String value = "";
					if (cbDyspnea.getValue()) {
						value = value + "dyspnea,";
					}
					if (cbCough.getValue()) {
						value = value + "cough,";
					}
					if (cbTachypnea.getValue()) {
						value = value + "tachypnea,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrosresp", value);
					map.put("pnotestrosrespcmnts", tbRespRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("GI"))
					|| sectionArrayList.isEmpty()) {
				if (cbGIRos.getValue()) {
					map.put("pnotestrosgistatus", "1");
					String value = "";
					if (cbPainGI.getValue()) {
						value = value + "pain,";
					}
					if (cbNausea.getValue()) {
						value = value + "nausea,";
					}
					if (cbVomiting.getValue()) {
						value = value + "vomiting,";
					}
					if (cbDiarrhea.getValue()) {
						value = value + "diarrhea,";
					}
					if (cbConstipation.getValue()) {
						value = value + "constipation,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestroshgi", value);
					map.put("pnotestrosgicmnts", tbGIRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("GU"))
					|| sectionArrayList.isEmpty()) {
				if (cbGUROS.getValue()) {
					map.put("pnotestrosgustatus", "1");
					String value = "";
					if (cbPainGU.getValue()) {
						value = value + "pain,";
					}
					if (cbBleeding.getValue()) {
						value = value + "bleeding,";
					}
					if (cbIncontinent.getValue()) {
						value = value + "incontinent,";
					}
					if (cbNocturia.getValue()) {
						value = value + "nocturia,";
					}
					if (cbFoulSmell.getValue()) {
						value = value + "foul smell,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrosgu", value);
					map.put("pnotestrosgucmnts", tbGURos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Muscle"))
					|| sectionArrayList.isEmpty()) {
				if (cbMuscle.getValue()) {
					map.put("pnotestrosmusclestatus", "1");
					String value = "";
					if (cbPainMuscle.getValue()) {
						value = value + "pain,";
					}
					if (cbWeakness.getValue()) {
						value = value + "weakness,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrosmuscles", value);
					map.put("pnotestrosmusclescmnts", tbMuscleRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Skin"))
					|| sectionArrayList.isEmpty()) {
				if (cbSkinRos.getValue()) {
					map.put("pnotestrosskinstatus", "1");
					String value = "";
					if (cbRash.getValue()) {
						value = value + "rash,";
					}
					if (cbPainSkin.getValue()) {
						value = value + "pain,";
					}
					if (cbAbscess.getValue()) {
						value = value + "abscess,";
					}
					if (cbMass.getValue()) {
						value = value + "mass,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrosskin", value);
					map.put("pnotestrosskincmnts", tbSkinRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Psych"))
					|| sectionArrayList.isEmpty()) {
				if (cbPsychRos.getValue()) {
					map.put("pnotestrospsychstatus", "1");
					String value = "";
					if (cbFatigue.getValue()) {
						value = value + "fatigue,";
					}
					if (cbInsomnia.getValue()) {
						value = value + "insomnia,";
					}
					if (cbMoodProblem.getValue()) {
						value = value + "mood problem,";
					}
					if (cbCrying.getValue()) {
						value = value + "crying,";
					}
					if (cbDepression.getValue()) {
						value = value + "depression,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrospsych", value);
					map.put("pnotestrospsychcmnts", tbPsychRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Endocrine"))
					|| sectionArrayList.isEmpty()) {
				if (cbEndoRos.getValue()) {
					map.put("pnotestrosendostatus", "1");
					String value = "";
					if (cbHotFlashes.getValue()) {
						value = value + "hot flashes,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrosendo", value);
					map.put("pnotestrosendocmnts", tbEndoRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Hem/Lymph"))
					|| sectionArrayList.isEmpty()) {
				if (cbPsychRos.getValue()) {
					map.put("pnotestroshemlympstatus", "1");
					String value = "";
					if (cbFevers.getValue()) {
						value = value + "fevers,";
					}
					if (cbChills.getValue()) {
						value = value + "chills,";
					}
					if (cbSwelling.getValue()) {
						value = value + "swelling,";
					}
					if (cbNightSweats.getValue()) {
						value = value + "night sweats,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestroshemlymp", value);
					map.put("pnotestroshemlympcmnts", tbHemLymRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Neuro"))
					|| sectionArrayList.isEmpty()) {
				if (cbNeuroRos.getValue()) {
					map.put("pnotestrosneurostatus", "1");
					String value = "";
					if (cbNumbness.getValue()) {
						value = value + "numbness,";
					}
					if (cbTingling.getValue()) {
						value = value + "tingling,";
					}
					if (cbWeaknessNeuro.getValue()) {
						value = value + "weakness,";
					}
					if (cbHeadache.getValue()) {
						value = value + "headache,";
					}
					if (cbLossOfCons.getValue()) {
						value = value + "loss of consciousness,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotestrosneuro", value);
					map.put("pnotestrosneurocmnts", tbNeuroRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Immunologic/Allergies"))
					|| sectionArrayList.isEmpty()) {
				if (cbImmAllrgRos.getValue()) {
					map.put("pnotestrosimmallrgstatus", "1");
					map.put("pnotestrosimmallrg", tbImmAllrg.getText());
				}
			}
		}

		if (sectionArrayList.contains("Past Medical History")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Past Medical History");
			if (fieldsArrayList.contains("PH")) {
				map.put("pnotestph", tbPH.getText());
			}
		}

		if (sectionArrayList.contains("Family History")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Family History");
			if (fieldsArrayList.contains("FH")) {
				map.put("pnotestfh", tbFH.getText());
			}
		}

		if (sectionArrayList.contains("Social History")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Social History");
			if ((fieldsArrayList != null && fieldsArrayList.contains("Alcohol"))
					|| sectionArrayList.isEmpty()) {
				if (cbAlcohol.getValue()) {
					map.put("pnotestshalcoholstatus", "1");
					map.put("pnotestshalcoholcmnt", tbAlcohol.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Tobacco"))
					|| sectionArrayList.isEmpty()) {
				if (cbTobacco.getValue()) {
					map.put("pnotestshtobaccostatus", "1");
					map.put("pnotestshtobaccocmnt", tbTobacco.getText());
					if (cbCounseledCessation.getValue()) {
						map.put("pnotestshtcounseled", "1");
					}
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Illicit drugs"))
					|| sectionArrayList.isEmpty()) {
				if (cbIllDrugs.getValue()) {
					map.put("pnotestshilctdrugstatus", "1");
					map.put("pnotestshilctdrugscmnt", tbIllDrugs.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Lives with"))
					|| sectionArrayList.isEmpty()) {
				if (cbLivesWith.getValue()) {
					map.put("pnotestshliveswithstatus", "1");
					map.put("pnotestshliveswithcmnt", tbLivesWith.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Occupation"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotestshoccupation", tbOccupation.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("HIV risk factors"))
					|| sectionArrayList.isEmpty()) {
				if (cbHivRiskFactor.getValue()) {
					map.put("pnotestshivrskfacstatus", "1");
					map.put("pnotestshivrskfaccmnt", tbHivRiskFactor.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Travel"))
					|| sectionArrayList.isEmpty()) {
				if (cbTravel.getValue()) {
					map.put("pnotestshtravelstatus", "1");
					map.put("pnotestshtravelcmnt", tbTravel.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Pets"))
					|| sectionArrayList.isEmpty()) {
				if (cbPets.getValue()) {
					map.put("pnotestshpetsstatus", "1");
					map.put("pnotestshpetscmnt", tbPets.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Hobbies"))
					|| sectionArrayList.isEmpty()) {
				if (cbHobbies.getValue()) {
					map.put("pnotestshhobbiesstatus", "1");
					map.put("pnotestshhobbiescmnt", tbHobbies.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Housing"))
					|| sectionArrayList.isEmpty()) {
				if (radHousing.getWidgetValue() != null) {
					map.put("pnotestshhousing", radHousing.getWidgetValue());
				}
			}
		}

		if (sectionArrayList.contains("Exam") || sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Exam");
			if ((fieldsArrayList != null && fieldsArrayList.contains("Head"))
					|| sectionArrayList.isEmpty()) {
				List<String> headArrayList = sectionsFieldMap
						.get("Sections#Exam#Head");
				if ((headArrayList != null && headArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpeheadfreecmnt", tbHeadFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Eyes"))
					|| sectionArrayList.isEmpty()) {
				List<String> eyesArrayList = sectionsFieldMap
						.get("Sections#Exam#Eyes");
				if ((eyesArrayList != null && eyesArrayList
						.contains("Conjunctivae_lids_pupils & irises"))
						|| sectionArrayList.isEmpty()) {
					if (cbCLPI.getValue() && radClpi.getWidgetValue() != null) {
						if (radClpi.getWidgetValue().equals("1")) {
							map.put("pnotestpeeyeclpistatus", radClpi
									.getWidgetValue());
						} else if (radClpi.getWidgetValue().equals("2")) {
							map.put("pnotestpeeyeclpistatus", radClpi
									.getWidgetValue());
							map.put("pnotestpeeyeclpicmnt", tbClpi.getText());
						}
					}
				}
				if ((eyesArrayList != null && eyesArrayList.contains("Fundi"))
						|| sectionArrayList.isEmpty()) {
					List<String> fundiArrayList = sectionsFieldMap
							.get("Sections#Exam#Eyes#Fundi");
					if ((fundiArrayList != null && fundiArrayList
							.contains("Disc edges sharp"))
							|| sectionArrayList.isEmpty()) {
						if (cbDiscEdgeSharp.getValue()
								&& radDiscEdgeSharp.getWidgetValue() != null) {
							if (radDiscEdgeSharp.getWidgetValue().equals("1")) {
								map.put("pnotestpeeyedesstatus",
										radDiscEdgeSharp.getWidgetValue());
							} else if (radDiscEdgeSharp.getWidgetValue()
									.equals("2")) {
								map.put("pnotestpeeyedesstatus",
										radDiscEdgeSharp.getWidgetValue());
								map.put("pnotestpeeyedescmnt", tbDiscEdgeSharp
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("Venous pulses seen"))
							|| sectionArrayList.isEmpty()) {
						if (cbVenPul.getValue()
								&& radVenPul.getWidgetValue() != null) {
							if (radVenPul.getWidgetValue().equals("1")) {
								map.put("pnotestpeeyevpsstatus", radVenPul
										.getWidgetValue());
							} else if (radVenPul.getWidgetValue().equals("2")) {
								map.put("pnotestpeeyevpsstatus", radVenPul
										.getWidgetValue());
								map.put("pnotestpeeyevpscmnt", tbVenPul
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("A-V nicking"))
							|| sectionArrayList.isEmpty()) {
						if (cbAVNicking.getValue()
								&& radAVNicking.getWidgetValue() != null) {
							if (radAVNicking.getWidgetValue().equals("1")) {
								map.put("pnotestpeeyeavnstatus", radAVNicking
										.getWidgetValue());
							} else if (radAVNicking.getWidgetValue()
									.equals("2")) {
								map.put("pnotestpeeyeavnstatus", radAVNicking
										.getWidgetValue());
								map.put("pnotestpeeyeavncmnt", tbAVNicking
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("Hemorrhages"))
							|| sectionArrayList.isEmpty()) {
						if (cbHemorrhages.getValue()
								&& radHemorrhages.getWidgetValue() != null) {
							if (radHemorrhages.getWidgetValue().equals("1")) {
								map.put("pnotestpeeyehemstatus", radHemorrhages
										.getWidgetValue());
							} else if (radHemorrhages.getWidgetValue().equals(
									"2")) {
								map.put("pnotestpeeyehemstatus", radHemorrhages
										.getWidgetValue());
								map.put("pnotestpeeyehemcmnt", tbHemorrhages
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("Exudates"))
							|| sectionArrayList.isEmpty()) {
						if (cbExudates.getValue()
								&& radExudates.getWidgetValue() != null) {
							if (radExudates.getWidgetValue().equals("1")) {
								map.put("pnotestpeeyeexustatus", radExudates
										.getWidgetValue());
							} else if (radExudates.getWidgetValue().equals("2")) {
								map.put("pnotestpeeyeexustatus", radExudates
										.getWidgetValue());
								map.put("pnotestpeeyeexucmnt", tbExudates
										.getText());
							}
						}
					}
				}
				if ((eyesArrayList != null && eyesArrayList
						.contains("Cup:disc ratio"))
						|| sectionArrayList.isEmpty()) {
					map.put("pnotestpeeyecupdiscratio", tbCupDiscRatio
							.getText());
				}
				
				if ((eyesArrayList != null && eyesArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpeeyefreecmnt", tbEyeFreeForm
							.getText());
				}

			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("ENT"))
					|| sectionArrayList.isEmpty()) {
				List<String> entArrayList = sectionsFieldMap
						.get("Sections#Exam#ENT");
				if ((entArrayList != null && entArrayList
						.contains("External canals_TMs"))
						|| sectionArrayList.isEmpty()) {
					if (cbExtCanTms.getValue()
							&& radExtCanTms.getWidgetValue() != null) {
						if (radExtCanTms.getWidgetValue().equals("1")) {
							map.put("pnotestpeentectstatus", radExtCanTms
									.getWidgetValue());
						} else if (radExtCanTms.getWidgetValue().equals("2")) {
							map.put("pnotestpeentectstatus", radExtCanTms
									.getWidgetValue());
							map.put("pnotestpeentectcmnt", tbExtCanTms
									.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Nasal mucosa_septum"))
						|| sectionArrayList.isEmpty()) {
					if (cbNMS.getValue() && radNMS.getWidgetValue() != null) {
						if (radNMS.getWidgetValue().equals("1")) {
							map.put("pnotestpeentnmsstatus", radNMS
									.getWidgetValue());
						} else if (radNMS.getWidgetValue().equals("2")) {
							map.put("pnotestpeentnmsstatus", radNMS
									.getWidgetValue());
							map.put("pnotestpeentnmscmnt", tbNMS.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Lips_gums_teeth"))
						|| sectionArrayList.isEmpty()) {
					if (cbLGT.getValue() && radLGT.getWidgetValue() != null) {
						if (radLGT.getWidgetValue().equals("1")) {
							map.put("pnotestpeentlgtstatus", radLGT
									.getWidgetValue());
						} else if (radLGT.getWidgetValue().equals("2")) {
							map.put("pnotestpeentlgtstatus", radLGT
									.getWidgetValue());
							map.put("pnotestpeentlgtcmnt", tbLGT.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Oropharynx_mucosa_salivary glands"))
						|| sectionArrayList.isEmpty()) {
					if (cbOMS.getValue() && radOMS.getWidgetValue() != null) {
						if (radOMS.getWidgetValue().equals("1")) {
							map.put("pnotestpeentomsgstatus", radOMS
									.getWidgetValue());
						} else if (radOMS.getWidgetValue().equals("2")) {
							map.put("pnotestpeentomsgstatus", radOMS
									.getWidgetValue());
							map.put("pnotestpeentomsgcmnt", tbOMS.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Hard/soft palate_tongue_tonsils_posterior pharynx"))
						|| sectionArrayList.isEmpty()) {
					if (cbHTTP.getValue() && radHTTP.getWidgetValue() != null) {
						if (radHTTP.getWidgetValue().equals("1")) {
							map.put("pnotestpeenthttpstatus", radHTTP
									.getWidgetValue());
						} else if (radHTTP.getWidgetValue().equals("2")) {
							map.put("pnotestpeenthttpstatus", radHTTP
									.getWidgetValue());
							map.put("pnotestpeenthttpcmnt", tbHTTP.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList.contains("Thyroid"))
						|| sectionArrayList.isEmpty()) {
					if (cbThyroid.getValue()
							&& radThyroid.getWidgetValue() != null) {
						if (radThyroid.getWidgetValue().equals("1")) {
							map.put("pnotestpeentthyrostatus", radThyroid
									.getWidgetValue());
						} else if (radThyroid.getWidgetValue().equals("2")) {
							map.put("pnotestpeentthyrostatus", radThyroid
									.getWidgetValue());
							map.put("pnotestpeentthyrocmnt", tbThyroid
									.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpeentfreecmnt", tbEntFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Neck"))
					|| sectionArrayList.isEmpty()) {
				List<String> neckArrayList = sectionsFieldMap
						.get("Sections#Exam#Neck");
				if ((neckArrayList != null && neckArrayList
						.contains("Neck (note bruit_JVD)"))
						|| sectionArrayList.isEmpty()) {
					if (cbNeck.getValue() && radNeck.getWidgetValue() != null) {
						if (radNeck.getWidgetValue().equals("1")) {
							map.put("pnotestpeneckbrjvdstatus", radNeck
									.getWidgetValue());
						} else if (radNeck.getWidgetValue().equals("2")) {
							map.put("pnotestpeneckbrjvdstatus", radNeck
									.getWidgetValue());
							map.put("pnotestpeneckbrjvdcmnt", tbNeckExam
									.getText());
						}
					}
				}
				if ((neckArrayList != null && neckArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpeneckfreecmnt", tbNeckFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Breast"))
					|| sectionArrayList.isEmpty()) {
				List<String> breastArrayList = sectionsFieldMap
						.get("Sections#Exam#Breast");
				if ((breastArrayList != null && breastArrayList
						.contains("Breasts (note dimpling_discharge_mass)"))
						|| sectionArrayList.isEmpty()) {
					if (cbBreast.getValue()
							&& radBreast.getWidgetValue() != null) {
						if (radBreast.getWidgetValue().equals("1")) {
							map.put("pnotestpebrstddmstatus", radBreast
									.getWidgetValue());
						} else if (radBreast.getWidgetValue().equals("2")) {
							map.put("pnotestpebrstddmstatus", radBreast
									.getWidgetValue());
							map.put("pnotestpebrstddmcmnt", tbBreastExam
									.getText());
						}
					}
				}
				if ((breastArrayList != null && breastArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpebrstfreecmnt", tbBreastFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Resp"))
					|| sectionArrayList.isEmpty()) {
				List<String> respArrayList = sectionsFieldMap
						.get("Sections#Exam#Resp");
				if ((respArrayList != null && respArrayList
						.contains("Respiratory effort"))
						|| sectionArrayList.isEmpty()) {
					if (cbRespEff.getValue()
							&& radRespEff.getWidgetValue() != null) {
						if (radRespEff.getWidgetValue().equals("1")) {
							map.put("pnotestperespeffstatus", radRespEff
									.getWidgetValue());
						} else if (radRespEff.getWidgetValue().equals("2")) {
							map.put("pnotestperespeffstatus", radRespEff
									.getWidgetValue());
							map
									.put("pnotestperespeffcmnt", tbRespEff
											.getText());
						}
					}
				}
				if ((respArrayList != null && respArrayList
						.contains("Lung percussion & auscultation"))
						|| sectionArrayList.isEmpty()) {
					if (cbLPA.getValue() && radLPA.getWidgetValue() != null) {
						if (radLPA.getWidgetValue().equals("1")) {
							map.put("pnotestperesplungstatus", radLPA
									.getWidgetValue());
						} else if (radLPA.getWidgetValue().equals("2")) {
							map.put("pnotestperesplungstatus", radLPA
									.getWidgetValue());
							map.put("pnotestperesplungcmnt", tbLPA.getText());
						}
					}
				}
				if ((respArrayList != null && respArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestperespfreecmnt", tbRespFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("CV"))
					|| sectionArrayList.isEmpty()) {
				List<String> cvArrayList = sectionsFieldMap
						.get("Sections#Exam#CV");
				if ((cvArrayList != null && cvArrayList
						.contains("Auscultation"))
						|| sectionArrayList.isEmpty()) {
					List<String> auscultationArrayList = sectionsFieldMap
							.get("Sections#Exam#CV#Auscultation");
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("Regular rhythm"))
							|| sectionArrayList.isEmpty()) {
						if (cbRegRyth.getValue()
								&& radRegRyth.getWidgetValue() != null) {
							if (radRegRyth.getWidgetValue().equals("1")) {
								map.put("pnotestpecvregrhystatus", radRegRyth
										.getWidgetValue());
							} else if (radRegRyth.getWidgetValue().equals("2")) {
								map.put("pnotestpecvregrhystatus", radRegRyth
										.getWidgetValue());
								map.put("pnotestpecvregrhycmnt", tbRegRyth
										.getText());
							}
						}
					}
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("S1 constant"))
							|| sectionArrayList.isEmpty()) {
						if (cbS1Cons.getValue()
								&& radS1Cons.getWidgetValue() != null) {
							if (radS1Cons.getWidgetValue().equals("1")) {
								map.put("pnotestpecvs1consstatus", radS1Cons
										.getWidgetValue());
							} else if (radS1Cons.getWidgetValue().equals("2")) {
								map.put("pnotestpecvs1consstatus", radS1Cons
										.getWidgetValue());
								map.put("pnotestpecvs1conscmnt", tbS1Cons
										.getText());
							}
						}
					}
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("S2 physiologic split"))
							|| sectionArrayList.isEmpty()) {
						if (cbS2PhysSplit.getValue()
								&& radPhysSplit.getWidgetValue() != null) {
							if (radPhysSplit.getWidgetValue().equals("1")) {
								map.put("pnotestpecvs2physplstatus",
										radPhysSplit.getWidgetValue());
							} else if (radPhysSplit.getWidgetValue()
									.equals("2")) {
								map.put("pnotestpecvs2physplstatus",
										radPhysSplit.getWidgetValue());
								map.put("pnotestpecvs2physplcmnt", tbPhysSplit
										.getText());
							}
						}
					}
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("Murmur (describe)"))
							|| sectionArrayList.isEmpty()) {
						if (cbMurmur.getValue()
								&& radMurmur.getWidgetValue() != null) {
							if (radMurmur.getWidgetValue().equals("1")) {
								map.put("pnotestpecvmurstatus", radMurmur
										.getWidgetValue());
							} else if (radMurmur.getWidgetValue().equals("2")) {
								map.put("pnotestpecvmurstatus", radMurmur
										.getWidgetValue());
								map.put("pnotestpecvmurcmnt", tbMurmur
										.getText());
							}
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Palpation of heart"))
						|| sectionArrayList.isEmpty()) {
					if (cbPalHrt.getValue()
							&& radPalHrt.getWidgetValue() != null) {
						if (radPalHrt.getWidgetValue().equals("1")) {
							map.put("pnotestpecvpalhrtstatus", radPalHrt
									.getWidgetValue());
						} else if (radPalHrt.getWidgetValue().equals("2")) {
							map.put("pnotestpecvpalhrtstatus", radPalHrt
									.getWidgetValue());
							map
									.put("pnotestpecvpalhrtcmnt", tbPalHrt
											.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Abdominal aorta"))
						|| sectionArrayList.isEmpty()) {
					if (cbAbAorta.getValue()
							&& radAbAorta.getWidgetValue() != null) {
						if (radAbAorta.getWidgetValue().equals("1")) {
							map.put("pnotestpecvabdaorstatus", radAbAorta
									.getWidgetValue());
						} else if (radAbAorta.getWidgetValue().equals("2")) {
							map.put("pnotestpecvabdaorstatus", radAbAorta
									.getWidgetValue());
							map.put("pnotestpecvabdaorcmnt", tbAbAorta
									.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Femoral arteries"))
						|| sectionArrayList.isEmpty()) {
					if (cbFemArt.getValue()
							&& radFemArt.getWidgetValue() != null) {
						if (radFemArt.getWidgetValue().equals("1")) {
							map.put("pnotestpecvfemartstatus", radFemArt
									.getWidgetValue());
						} else if (radFemArt.getWidgetValue().equals("2")) {
							map.put("pnotestpecvfemartstatus", radFemArt
									.getWidgetValue());
							map
									.put("pnotestpecvfemartcmnt", tbFemArt
											.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Pedal pulses"))
						|| sectionArrayList.isEmpty()) {
					if (cbPedalPulses.getValue()
							&& radPedalPulses.getWidgetValue() != null) {
						if (radPedalPulses.getWidgetValue().equals("1")) {
							map.put("pnotestpecvpedpulstatus", radPedalPulses
									.getWidgetValue());
						} else if (radPedalPulses.getWidgetValue().equals("2")) {
							map.put("pnotestpecvpedpulstatus", radPedalPulses
									.getWidgetValue());
							map.put("pnotestpecvpadpulcmnt", tbPedalPulses
									.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpecvfreecmnt", tbCVFreeForm
							.getText());
				}
			}

			if ((fieldsArrayList != null && fieldsArrayList.contains("GI"))
					|| sectionArrayList.isEmpty()) {
				List<String> giArrayList = sectionsFieldMap
						.get("Sections#Exam#GI");
				if ((giArrayList != null && giArrayList.contains("Abdomen"))
						|| sectionArrayList.isEmpty()) {
					List<String> abdomenArrayList = sectionsFieldMap
							.get("Sections#Exam#GI#Abdomen");
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Scars"))
							|| sectionArrayList.isEmpty()) {
						if (cbScars.getValue()
								&& radScars.getWidgetValue() != null) {
							if (radScars.getWidgetValue().equals("1")) {
								map.put("pnotestpegiscarsstatus", radScars
										.getWidgetValue());
							} else if (radScars.getWidgetValue().equals("2")) {
								map.put("pnotestpegiscarsstatus", radScars
										.getWidgetValue());
								map.put("pnotestpegiscarscmnt", tbScars
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Bruit"))
							|| sectionArrayList.isEmpty()) {
						if (cbBruit.getValue()
								&& radBruit.getWidgetValue() != null) {
							if (radBruit.getWidgetValue().equals("1")) {
								map.put("pnotestpegibruitstatus", radBruit
										.getWidgetValue());
							} else if (radBruit.getWidgetValue().equals("2")) {
								map.put("pnotestpegibruitstatus", radBruit
										.getWidgetValue());
								map.put("pnotestpegibruitcmnt", tbBruit
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Mass"))
							|| sectionArrayList.isEmpty()) {
						if (cbMassExam.getValue()
								&& radMass.getWidgetValue() != null) {
							if (radMass.getWidgetValue().equals("1")) {
								map.put("pnotestpegimassstatus", radMass
										.getWidgetValue());
							} else if (radMass.getWidgetValue().equals("2")) {
								map.put("pnotestpegimassstatus", radMass
										.getWidgetValue());
								map
										.put("pnotestpegimasscmnt", tbMass
												.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Tenderness"))
							|| sectionArrayList.isEmpty()) {
						if (cbTenderness.getValue()
								&& radTenderness.getWidgetValue() != null) {
							if (radTenderness.getWidgetValue().equals("1")) {
								map.put("pnotestpegitendstatus", radTenderness
										.getWidgetValue());
							} else if (radTenderness.getWidgetValue().equals(
									"2")) {
								map.put("pnotestpegitendstatus", radTenderness
										.getWidgetValue());
								map.put("pnotestpegitendcmnt", tbTenderness
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Hepatomegaly"))
							|| sectionArrayList.isEmpty()) {
						if (cbHepatomegaly.getValue()
								&& radHepatomegaly.getWidgetValue() != null) {
							if (radHepatomegaly.getWidgetValue().equals("1")) {
								map.put("pnotestpegiheptstatus",
										radHepatomegaly.getWidgetValue());
							} else if (radHepatomegaly.getWidgetValue().equals(
									"2")) {
								map.put("pnotestpegiheptstatus",
										radHepatomegaly.getWidgetValue());
								map.put("pnotestpegiheptcmnt", tbHepatomegaly
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Splenomegaly"))
							|| sectionArrayList.isEmpty()) {
						if (cbSplenomegaly.getValue()
								&& radSplenomegaly.getWidgetValue() != null) {
							if (radSplenomegaly.getWidgetValue().equals("1")) {
								map.put("pnotestpegisplenstatus",
										radSplenomegaly.getWidgetValue());
							} else if (radSplenomegaly.getWidgetValue().equals(
									"2")) {
								map.put("pnotestpegisplenstatus",
										radSplenomegaly.getWidgetValue());
								map.put("pnotestpegisplencmnt", tbSplenomegaly
										.getText());
							}
						}
					}
				}
				if ((giArrayList != null && giArrayList
						.contains("Anus_perineum_rectum_sphincter tone"))
						|| sectionArrayList.isEmpty()) {
					if (cbAPRS.getValue() && radAPRS.getWidgetValue() != null) {
						if (radAPRS.getWidgetValue().equals("1")) {
							map.put("pnotestpegiaprsstatus", radAPRS
									.getWidgetValue());
						} else if (radAPRS.getWidgetValue().equals("2")) {
							map.put("pnotestpegiaprsstatus", radAPRS
									.getWidgetValue());
							map.put("pnotestpegiaprscmnt", tbAPRS.getText());
						}
					}
				}
				if ((giArrayList != null && giArrayList
						.contains("Bowel sounds"))
						|| sectionArrayList.isEmpty()) {
					if (cbBowSnd.getValue()
							&& radBowSnd.getWidgetValue() != null) {
						if (radBowSnd.getWidgetValue().equals("1")) {
							map.put("pnotestpegibowsndstatus", radBowSnd
									.getWidgetValue());
						} else {
							map.put("pnotestpegibowsndstatus", radBowSnd
									.getWidgetValue());
							map
									.put("pnotestpegibowsndcmnt", tbBowSnd
											.getText());
						}
					}
				}
				if ((giArrayList != null && giArrayList.contains("Stool"))
						|| sectionArrayList.isEmpty()) {
					if (cbStool.getValue() && radStool.getWidgetValue() != null) {
						if (radStool.getWidgetValue().equals("1")) {
							map.put("pnotestpegistoolstatus", radStool
									.getWidgetValue());
						} else {
							map.put("pnotestpegistoolstatus", radStool
									.getWidgetValue());
							map.put("pnotestpegistoolcmnt", tbStool.getText());
						}
					}

				}
				if ((giArrayList != null && giArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpegifreecmnt", tbGIFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("GU"))
					|| sectionArrayList.isEmpty()) {
				List<String> guArrayList = sectionsFieldMap
						.get("Sections#Exam#GU");
				if ((guArrayList != null && guArrayList.contains("Gender"))
						|| sectionArrayList.isEmpty()) {
					if (radGender.getWidgetValue() != null) {
						if (radGender.getWidgetValue().equals("1")) {
							map.put("pnotestpegugender", "Male");
							if (cbPenis.getValue()
									&& radPenis.getWidgetValue() != null) {
								if (radPenis.getWidgetValue().equals("1")) {
									map.put("pnotestpegupenisstatus", radPenis
											.getWidgetValue());
								} else if (radPenis.getWidgetValue()
										.equals("2")) {
									map.put("pnotestpegupenisstatus", radPenis
											.getWidgetValue());
									map.put("pnotestpegupeniscmnt", tbPenis
											.getText());
								}
							}
							if (cbTestes.getValue()
									&& radTestes.getWidgetValue() != null) {
								if (radTestes.getWidgetValue().equals("1")) {
									map.put("pnotestpegutestesstatus",
											radTestes.getWidgetValue());
								} else if (radTestes.getWidgetValue().equals(
										"2")) {
									map.put("pnotestpegutestesstatus",
											radTestes.getWidgetValue());
									map.put("pnotestpegutestescmnt", tbTestes
											.getText());
								}
							}
							if (cbProstate.getValue()
									&& radProstate.getWidgetValue() != null) {
								if (radProstate.getWidgetValue().equals("1")) {
									map.put("pnotestpeguproststatus",
											radProstate.getWidgetValue());
								} else if (radProstate.getWidgetValue().equals(
										"2")) {
									map.put("pnotestpeguproststatus",
											radProstate.getWidgetValue());
									map.put("pnotestpeguprostcmnt", tbProstate
											.getText());
								}
							}
						} else if (radGender.getWidgetValue().equals("2")) {
							map.put("pnotestpegugender", "Female");
							if (cbExtGen.getValue()
									&& radExtGen.getWidgetValue() != null) {
								if (radExtGen.getWidgetValue().equals("1")) {
									map.put("pnotestpeguextgenstatus",
											radExtGen.getWidgetValue());
								} else if (radExtGen.getWidgetValue().equals(
										"2")) {
									map.put("pnotestpeguextgenstatus",
											radExtGen.getWidgetValue());
									map.put("pnotestpeguextgencmnt", tbExtGen
											.getText());
								}
							}
							if (cbCervix.getValue()
									&& radCervix.getWidgetValue() != null) {
								if (radCervix.getWidgetValue().equals("1")) {
									map.put("pnotestpegucervixstatus",
											radCervix.getWidgetValue());
								} else if (radCervix.getWidgetValue().equals(
										"2")) {
									map.put("pnotestpegucervixstatus",
											radCervix.getWidgetValue());
									map.put("pnotestpegucervixcmnt", tbCervix
											.getText());
								}
							}
							if (cbUteAdn.getValue()
									&& radUteAdn.getWidgetValue() != null) {
								if (radUteAdn.getWidgetValue().equals("1")) {
									map.put("pnotestpeguutadnstatus", radUteAdn
											.getWidgetValue());
								} else if (radUteAdn.getWidgetValue().equals(
										"2")) {
									map.put("pnotestpeguutadnstatus", radUteAdn
											.getWidgetValue());
									map.put("pnotestpeguutadncmnt", tbUteAdn
											.getText());
								}
							}
						}
					}
				}
				if ((guArrayList != null && guArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpegufreecmnt", tbGUFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Lymphatics"))
					|| sectionArrayList.isEmpty()) {
				List<String> lympArrayList = sectionsFieldMap
						.get("Sections#Exam#Lymphatics");
				if ((lympArrayList != null && lympArrayList
						.contains("Lymph nodes"))
						|| sectionArrayList.isEmpty()) {
					if (cbLympNode.getValue()
							&& radLympNode.getWidgetValue() != null) {
						if (radLympNode.getWidgetValue().equals("1")) {
							map.put("pnotestpelympnodesstatus", radLympNode
									.getWidgetValue());
						} else if (radLympNode.getWidgetValue().equals("2")) {
							map.put("pnotestpelympnodesstatus", radLympNode
									.getWidgetValue());
							map.put("pnotestpelympnodescmnt", tbLympNode
									.getText());
						}
					}
				}
				if ((lympArrayList != null && lympArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpelympfreecmnt", tbLympFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Skin"))
					|| sectionArrayList.isEmpty()) {
				List<String> skinArrayList = sectionsFieldMap
						.get("Sections#Exam#Skin");
				if ((skinArrayList != null && skinArrayList
						.contains("Skin & SQ tissue"))
						|| sectionArrayList.isEmpty()) {
					if (cbSkinSQTissue.getValue()
							&& radSkinSQTissue.getWidgetValue() != null) {
						if (radSkinSQTissue.getWidgetValue().equals("1")) {
							map.put("pnotestpeskintissuestatus",
									radSkinSQTissue.getWidgetValue());
						} else if (radSkinSQTissue.getWidgetValue().equals("2")) {
							map.put("pnotestpeskintissuestatus",
									radSkinSQTissue.getWidgetValue());
							map.put("pnotestpeskintissuecmnt", tbSkinSQTissue
									.getText());
						}
					}
				}
				if ((skinArrayList != null && skinArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpeskinfreecmnt", tbSkinFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("MS"))
					|| sectionArrayList.isEmpty()) {
				List<String> msArrayList = sectionsFieldMap
						.get("Sections#Exam#MS");
				if ((msArrayList != null && msArrayList
						.contains("Gait & station"))
						|| sectionArrayList.isEmpty()) {
					if (cbGaitStat.getValue()
							&& radGaitStat.getWidgetValue() != null) {
						if (radGaitStat.getWidgetValue().equals("1")) {
							map.put("pnotestpemsgaitststatus", radGaitStat
									.getWidgetValue());
						} else if (radGaitStat.getWidgetValue().equals("2")) {
							map.put("pnotestpemsgaitststatus", radGaitStat
									.getWidgetValue());
							map.put("pnotestpemsgaitstcmnt", tbGaitStat
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Digits_nails"))
						|| sectionArrayList.isEmpty()) {
					if (cbDigitsNails.getValue()
							&& radDigitsNails.getWidgetValue() != null) {
						if (radDigitsNails.getWidgetValue().equals("1")) {
							map.put("pnotestpemsdignlsstatus", radDigitsNails
									.getWidgetValue());
						} else if (radDigitsNails.getWidgetValue().equals("2")) {
							map.put("pnotestpemsdignlsstatus", radDigitsNails
									.getWidgetValue());
							map.put("pnotestpemsdignlscmnt", tbDigitsNails
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("ROM_stability"))
						|| sectionArrayList.isEmpty()) {
					if (cbRomStability.getValue()
							&& radRomStability.getWidgetValue() != null) {
						if (radRomStability.getWidgetValue().equals("1")) {
							map.put("pnotestpemsromstbstatus", radRomStability
									.getWidgetValue());
						} else if (radRomStability.getWidgetValue().equals("2")) {
							map.put("pnotestpemsromstbstatus", radRomStability
									.getWidgetValue());
							map.put("pnotestpemsromstbcmnt", tbRomStability
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Joints_bones_muscles"))
						|| sectionArrayList.isEmpty()) {
					if (cbJntBnsMusc.getValue()
							&& radJntBnsMusc.getWidgetValue() != null) {
						if (radJntBnsMusc.getWidgetValue().equals("1")) {
							map.put("pnotestpemsjntbnsmusstatus", radJntBnsMusc
									.getWidgetValue());
						} else if (radJntBnsMusc.getWidgetValue().equals("2")) {
							map.put("pnotestpemsjntbnsmusstatus", radJntBnsMusc
									.getWidgetValue());
							map.put("pnotestpemsjntbnsmuscmnt", tbJntBnsMusc
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Muscle strength & tone"))
						|| sectionArrayList.isEmpty()) {
					if (cbMuscStrg.getValue()
							&& radMuscStrg.getWidgetValue() != null) {
						if (radMuscStrg.getWidgetValue().equals("1")) {
							map.put("pnotestpemsmusstrtnstatus", radMuscStrg
									.getWidgetValue());
						} else if (radMuscStrg.getWidgetValue().equals("2")) {
							map.put("pnotestpemsmusstrtnstatus", radMuscStrg
									.getWidgetValue());
							map.put("pnotestpemsmusstrtncmnt", tbMuscStrg
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpemsfreecmnt", tbMSFreeForm
							.getText());
				}
			}

			if ((fieldsArrayList != null && fieldsArrayList.contains("Neuro"))
					|| sectionArrayList.isEmpty()) {
				List<String> neuroArrayList = sectionsFieldMap
						.get("Sections#Exam#Neuro");
				if ((neuroArrayList != null && neuroArrayList
						.contains("Cranial nerves (note deficits)"))
						|| sectionArrayList.isEmpty()) {
					if (cbCranNerves.getValue()
							&& radCranNerves.getWidgetValue() != null) {
						if (radCranNerves.getWidgetValue().equals("1")) {
							map.put("pnotestpeneurocrnervstatus", radCranNerves
									.getWidgetValue());
						} else if (radCranNerves.getWidgetValue().equals("2")) {
							map.put("pnotestpeneurocrnervstatus", radCranNerves
									.getWidgetValue());
							map.put("pnotestpeneurocrnervcmnt", tbCranNerves
									.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList.contains("DTRs"))
						|| sectionArrayList.isEmpty()) {
					if (cbDTRs.getValue() && radDTRs.getWidgetValue() != null) {
						if (radDTRs.getWidgetValue().equals("1")) {
							map.put("pnotestpeneurodtrsstatus", radDTRs
									.getWidgetValue());
						} else if (radDTRs.getWidgetValue().equals("2")) {
							map.put("pnotestpeneurodtrsstatus", radDTRs
									.getWidgetValue());
							map.put("pnotestpeneurodtrscmnt", tbDTRs.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList.contains("Motor"))
						|| sectionArrayList.isEmpty()) {
					if (cbMotor.getValue() && radMotor.getWidgetValue() != null) {
						if (radMotor.getWidgetValue().equals("1")) {
							map.put("pnotestpeneuromotorstatus", radMotor
									.getWidgetValue());
						} else if (radMotor.getWidgetValue().equals("2")) {
							map.put("pnotestpeneuromotorstatus", radMotor
									.getWidgetValue());
							map.put("pnotestpeneuromotorcmnt", tbMotor
									.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList
						.contains("Sensation"))
						|| sectionArrayList.isEmpty()) {
					if (cbSensation.getValue()
							&& radSensation.getWidgetValue() != null) {
						if (radSensation.getWidgetValue().equals("1")) {
							map.put("pnotestpeneurosnststatus", radSensation
									.getWidgetValue());
						} else if (radSensation.getWidgetValue().equals("2")) {
							map.put("pnotestpeneurosnststatus", radSensation
									.getWidgetValue());
							map.put("pnotestpeneurosnstcmnt", tbSensation
									.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpeneurofreecmnt", tbNeuroFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Psych"))
					|| sectionArrayList.isEmpty()) {
				List<String> psychArrayList = sectionsFieldMap
						.get("Sections#Exam#Psych");
				if ((psychArrayList != null && psychArrayList
						.contains("Judgment & insight"))
						|| sectionArrayList.isEmpty()) {
					if (cbJudIns.getValue()
							&& radJudIns.getWidgetValue() != null) {
						if (radJudIns.getWidgetValue().equals("1")) {
							map.put("pnotestpepsychjudinsstatus", radJudIns
									.getWidgetValue());
						} else if (radJudIns.getWidgetValue().equals("2")) {
							map.put("pnotestpepsychjudinsstatus", radJudIns
									.getWidgetValue());
							map.put("pnotestpepsychjudinscmnt", tbJudIns
									.getText());
						}
					}
				}
				if ((psychArrayList != null && psychArrayList
						.contains("Mood & affect"))
						|| sectionArrayList.isEmpty()) {
					if (cbMoodEffect.getValue()
							&& radMoodEffect.getWidgetValue() != null) {
						if (radMoodEffect.getWidgetValue().equals("1")) {
							map.put("pnotestpepsychmoodeffstatus",
									radMoodEffect.getWidgetValue());
						} else if (radMoodEffect.getWidgetValue().equals("2")) {
							map.put("pnotestpepsychmoodeffstatus",
									radMoodEffect.getWidgetValue());
							map.put("pnotestpepsychmoodeffcmnt", tbMoodEffect
									.getText());
						}
					}

				}
				if ((psychArrayList != null && psychArrayList
						.contains("Oriented to time_place_person"))
						|| sectionArrayList.isEmpty()) {
					if (cbOrTimePlcPers.getValue()
							&& radOrTimePlcPers.getWidgetValue() != null) {
						if (radOrTimePlcPers.getWidgetValue().equals("1")) {
							map.put("pnotestpepsychorntppstatus",
									radOrTimePlcPers.getWidgetValue());
						} else if (radOrTimePlcPers.getWidgetValue()
								.equals("2")) {
							map.put("pnotestpepsychorntppstatus",
									radOrTimePlcPers.getWidgetValue());
							map.put("pnotestpepsychorntppcmnt", tbOrTimePlcPers
									.getText());
						}
					}
				}
				if ((psychArrayList != null && psychArrayList
						.contains("Memory"))
						|| sectionArrayList.isEmpty()) {
					if (cbMemory.getValue()
							&& radMemory.getWidgetValue() != null) {
						if (radMemory.getWidgetValue().equals("1")) {
							map.put("pnotestpepsychmemorystatus", radMemory
									.getWidgetValue());
						} else if (radMemory.getWidgetValue().equals("2")) {
							map.put("pnotestpepsychmemorystatus", radMemory
									.getWidgetValue());
							map.put("pnotestpepsychmemorycmnt", tbMemory
									.getText());
						}
					}
				}
				if ((psychArrayList != null && psychArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotestpepsychfreecmnt", tbPsychFreeForm
							.getText());
				}
			}
			Set<String> keys = billingFieldsWidgetsMap.keySet();
			Iterator<String> iter = keys.iterator();
			HashMap<String, HashMap<String, String>> m = new HashMap<String, HashMap<String, String>>();
			while (iter.hasNext()) {
				String key = iter.next();
				BillInfoWidget biw = billingFieldsWidgetsMap.get(key);
				HashMap<String, String> billVal = new HashMap<String, String>();
				billVal.put("proccode", biw.getProceduralCode().toString());
				billVal.put("diagcode", biw.getDiagnosisCode().toString());
				m.put(key, billVal);
			}
			if (!m.isEmpty()) {
				map.put("pnotestbillable", JsonUtil.jsonify(m));
			}
		}

		if (sectionArrayList.contains("Assessment/Plan")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Assessment/Plan");
			if (fieldsArrayList.contains("Assessment")) {
				map.put("pnotest_A", tbAssessment.getText());
			}
			if (fieldsArrayList.contains("Plan")) {
				map.put("pnotest_P", tbPlanAssess.getText());
			}
		}

		if (sectionArrayList.contains("Free Form Entry")) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Free Form Entry");
			if (fieldsArrayList.contains("Free Form Entry")) {
				map.put("pnotesthandp", rte.getHTML());
			}
		}
		map.put("pnotestsections", JsonUtil.jsonify(sectionsFieldMap));
		if (ntype != null) {
			if (ntype == NoteType.EncounterNote) {
				map.put("pnotesttype", "Encounter Note");
			} else {
				map.put("pnotesttype", "Progress Note");
			}
		}
		map.put("pnotestname", templateName);
		List params = new ArrayList();
		params.add(map);

		if (formmode == EncounterFormMode.ADD) {
			Util.callModuleMethod("EncounterNotesTemplate", "add", params,
					new CustomRequestCallback() {
						@Override
						public void onError() {
							Util
							.showErrorMsg("EncounterNotesTemplate",
									"Encounter Notes Template Creation Failed.");
						}
	
						@SuppressWarnings("unchecked")
						@Override
						public void jsonifiedData(Object data) {
							if (data != null) {
								Integer result = (Integer) data;
								if (result> 0) {
									Util
									.showInfoMsg(
											"EncounterNotesTemplate",
											"Encounter Notes Template Successfully Created.");
								}
								else{
									Util
									.showErrorMsg("EncounterNotesTemplate",
											"Encounter Notes Template Creation Failed.");
								}
							}
							else{
								Util
								.showErrorMsg("EncounterNotesTemplate",
										"Encounter Notes Template Creation Failed.");
							}
						}
					}, "Integer");
		}
		else if (formmode == EncounterFormMode.EDIT) {
			Util.callModuleMethod("EncounterNotesTemplate", "mod", params,
					new CustomRequestCallback() {
						@Override
						public void onError() {
							Util
							.showErrorMsg("EncounterNotesTemplate",
									"Encounter Notes Template Modification Failed.");
						}
	
						@SuppressWarnings("unchecked")
						@Override
						public void jsonifiedData(Object data) {
							if (data != null) {
								Boolean result = (Boolean) data;
								if (result) {
									Util
									.showInfoMsg(
											"EncounterNotesTemplate",
											"Encounter Notes Template Successfully Modified.");
								}
								else{
									Util
									.showErrorMsg("EncounterNotesTemplate",
											"Encounter Notes Template Modification Failed.");
								}
							}
							else{
								Util
								.showErrorMsg("EncounterNotesTemplate",
										"Encounter Notes Template Modification Failed.");
							}
						}
					}, "Boolean");
		}
		callback.jsonifiedData(EncounterCommandType.UPDATE);
	}

	public void saveEncounterNote() {

		HashMap<String, String> map = new HashMap<String, String>();
		String method = "";
		if (formmode == EncounterFormMode.EDIT) {
			method = "org.freemedsoftware.module.EncounterNotes.mod";
			map.put("id", templateValuesMap.get("id"));
		} else {
			method = "org.freemedsoftware.module.EncounterNotes.add";
		}
		map.put("pnotesdt", date.getTextBox().getText());
		map.put("pnotesdoc", provWidget.getStoredValue());
		map.put("pnotesdescrip", tbDesc.getText());
		map.put("pnotespat", patientID);
		map.put("pnotestemplate", currTemplate);
		if (radType.getWidgetValue() != null)
			map.put("pnotestype", radType.getWidgetValue());
		List<String> sectionArrayList = sectionsFieldMap.get("Sections");
		if (sectionArrayList == null)
			sectionArrayList = new ArrayList<String>();
		if (sectionArrayList.contains("SOAP Note")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#SOAP Note");
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Subjective"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_S", tbSub.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Objective"))
					|| sectionArrayList.isEmpty()) {
				if (listObj.getSelectedIndex() != 0) {
					map.put("pnotes_O", listObj.getItemText(listObj
							.getSelectedIndex()));
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Assessment"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_A", tbAssess.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Plan"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_P", tbPlan.getText());
			}
		}

		if (sectionArrayList.contains("IER") || sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap.get("Sections#IER");
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Interval"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_I", tbInterval.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Education"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_E", tbEducation.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Rx"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_R", tbRx.getText());
			}
		}

		if (sectionArrayList.contains("Vitals/Generals")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Vitals/Generals");
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Blood Pressure"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotessbp", tbBp1.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Blood Pressure"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesdbp", tbBp2.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Temperature"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotestemp", listTemp.getItemText(listTemp
						.getSelectedIndex()));
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Heart Rate"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesheartrate", tbHeartRate.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Respiratory Rate"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesresprate", tbRespRate.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Weight"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesweight", tbWeight.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Height"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesheight", tbHeight.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("BMI"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesbmi", tbBMIVal.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("General (PE)"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesgeneral", tbGeneral.getText());
			}
		}

		if (sectionArrayList.contains("CC & HPI") || sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#CC & HPI");
			if ((fieldsArrayList != null && fieldsArrayList.contains("CC"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotescc", tbCC.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("HPI"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnoteshpi", tbHPI.getText());
			}
		}

		if (sectionArrayList.contains("Review Of Systems")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Review Of Systems");
			if ((fieldsArrayList != null && fieldsArrayList.contains("General"))
					|| sectionArrayList.isEmpty()) {
				if (cbGeneral.getValue()) {
					map.put("pnotesrosgenralstatus", "1");
					map.put("pnotesrosgenral", tbGeneralRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Head"))
					|| sectionArrayList.isEmpty()) {
				if (cbHead.getValue()) {
					map.put("pnotesrosheadstatus", "1");
					map.put("pnotesroshead", tbHeadRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Eyes"))
					|| sectionArrayList.isEmpty()) {
				if (cbEyesRos.getValue()) {
					map.put("pnotesroseyesstatus", "1");
					String value = "";
					if (cbPoorVision.getValue()) {
						value = value + "poor vision,";
					}
					if (cbEyesPain.getValue()) {
						value = value + "pain,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesroseyes", value);
					map.put("pnotesroseyescmnts", tbEyesRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("ENT"))
					|| sectionArrayList.isEmpty()) {
				if (cbEntRos.getValue()) {
					map.put("pnotesrosentstatus", "1");
					String value = "";
					if (cbSoreThroat.getValue()) {
						value = value + "sore throat,";
					}
					if (cbENTPain.getValue()) {
						value = value + "pain,";
					}
					if (cbCoryza.getValue()) {
						value = value + "coryza,";
					}
					if (cbAcuity.getValue()) {
						value = value + "acuity,";
					}
					if (cbDysphagia.getValue()) {
						value = value + "dysphagia,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosent", value);
					map.put("pnotesrosentcmnts", tbENTRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("CV"))
					|| sectionArrayList.isEmpty()) {
				if (cbCVRos.getValue()) {
					map.put("pnotesroscvstatus", "1");
					String value = "";
					if (cbCVPain.getValue()) {
						value = value + "pain,";
					}
					if (cbPalpitations.getValue()) {
						value = value + "palpitations,";
					}
					if (cbHypoHyperTension.getValue()) {
						value = value + "hypo/hypertension,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesroscv", value);
					map.put("pnotesroscvsmnts", tbCVRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Resp"))
					|| sectionArrayList.isEmpty()) {
				if (cbRespRos.getValue()) {
					map.put("pnotesrosrespstatus", "1");
					String value = "";
					if (cbDyspnea.getValue()) {
						value = value + "dyspnea,";
					}
					if (cbCough.getValue()) {
						value = value + "cough,";
					}
					if (cbTachypnea.getValue()) {
						value = value + "tachypnea,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosresp", value);
					map.put("pnotesrosrespcmnts", tbRespRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("GI"))
					|| sectionArrayList.isEmpty()) {
				if (cbGIRos.getValue()) {
					map.put("pnotesrosgistatus", "1");
					String value = "";
					if (cbPainGI.getValue()) {
						value = value + "pain,";
					}
					if (cbNausea.getValue()) {
						value = value + "nausea,";
					}
					if (cbVomiting.getValue()) {
						value = value + "vomiting,";
					}
					if (cbDiarrhea.getValue()) {
						value = value + "diarrhea,";
					}
					if (cbConstipation.getValue()) {
						value = value + "constipation,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesroshgi", value);
					map.put("pnotesrosgicmnts", tbGIRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("GU"))
					|| sectionArrayList.isEmpty()) {
				if (cbGUROS.getValue()) {
					map.put("pnotesrosgustatus", "1");
					String value = "";
					if (cbPainGU.getValue()) {
						value = value + "pain,";
					}
					if (cbBleeding.getValue()) {
						value = value + "bleeding,";
					}
					if (cbIncontinent.getValue()) {
						value = value + "incontinent,";
					}
					if (cbNocturia.getValue()) {
						value = value + "nocturia,";
					}
					if (cbFoulSmell.getValue()) {
						value = value + "foul smell,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosgu", value);
					map.put("pnotesrosgucmnts", tbGURos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Muscle"))
					|| sectionArrayList.isEmpty()) {
				if (cbMuscle.getValue()) {
					map.put("pnotesrosmusclestatus", "1");
					String value = "";
					if (cbPainMuscle.getValue()) {
						value = value + "pain,";
					}
					if (cbWeakness.getValue()) {
						value = value + "weakness,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosmuscles", value);
					map.put("pnotesrosmusclescmnts", tbMuscleRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Skin"))
					|| sectionArrayList.isEmpty()) {
				if (cbSkinRos.getValue()) {
					map.put("pnotesrosskinstatus", "1");
					String value = "";
					if (cbRash.getValue()) {
						value = value + "rash,";
					}
					if (cbPainSkin.getValue()) {
						value = value + "pain,";
					}
					if (cbAbscess.getValue()) {
						value = value + "abscess,";
					}
					if (cbMass.getValue()) {
						value = value + "mass,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosskin", value);
					map.put("pnotesrosskincmnts", tbSkinRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Psych"))
					|| sectionArrayList.isEmpty()) {
				if (cbPsychRos.getValue()) {
					map.put("pnotesrospsychstatus", "1");
					String value = "";
					if (cbFatigue.getValue()) {
						value = value + "fatigue,";
					}
					if (cbInsomnia.getValue()) {
						value = value + "insomnia,";
					}
					if (cbMoodProblem.getValue()) {
						value = value + "mood problem,";
					}
					if (cbCrying.getValue()) {
						value = value + "crying,";
					}
					if (cbDepression.getValue()) {
						value = value + "depression,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrospsych", value);
					map.put("pnotesrospsychcmnts", tbPsychRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Endocrine"))
					|| sectionArrayList.isEmpty()) {
				if (cbEndoRos.getValue()) {
					map.put("pnotesrosendostatus", "1");
					String value = "";
					if (cbHotFlashes.getValue()) {
						value = value + "hot flashes,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosendo", value);
					map.put("pnotesrosendocmnts", tbEndoRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Hem/Lymph"))
					|| sectionArrayList.isEmpty()) {
				if (cbPsychRos.getValue()) {
					map.put("pnotesroshemlympstatus", "1");
					String value = "";
					if (cbFevers.getValue()) {
						value = value + "fevers,";
					}
					if (cbChills.getValue()) {
						value = value + "chills,";
					}
					if (cbSwelling.getValue()) {
						value = value + "swelling,";
					}
					if (cbNightSweats.getValue()) {
						value = value + "night sweats,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesroshemlymp", value);
					map.put("pnotesroshemlympcmnts", tbHemLymRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Neuro"))
					|| sectionArrayList.isEmpty()) {
				if (cbNeuroRos.getValue()) {
					map.put("pnotesrosneurostatus", "1");
					String value = "";
					if (cbNumbness.getValue()) {
						value = value + "numbness,";
					}
					if (cbTingling.getValue()) {
						value = value + "tingling,";
					}
					if (cbWeaknessNeuro.getValue()) {
						value = value + "weakness,";
					}
					if (cbHeadache.getValue()) {
						value = value + "headache,";
					}
					if (cbLossOfCons.getValue()) {
						value = value + "loss of consciousness,";
					}
					if(value.length()>0)
						value = value.substring(0, value.length() - 1);
					map.put("pnotesrosneuro", value);
					map.put("pnotesrosneurocmnts", tbNeuroRos.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Immunologic/Allergies"))
					|| sectionArrayList.isEmpty()) {
				if (cbImmAllrgRos.getValue()) {
					map.put("pnotesrosimmallrgstatus", "1");
					map.put("pnotesrosimmallrg", tbImmAllrg.getText());
				}
			}
		}

		if (sectionArrayList.contains("Past Medical History")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Past Medical History");
			if ((fieldsArrayList != null && fieldsArrayList.contains("PH"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesph", tbPH.getText());
			}
		}

		if (sectionArrayList.contains("Family History")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Family History");
			if ((fieldsArrayList != null && fieldsArrayList.contains("FH"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesfh", tbFH.getText());
			}
		}

		if (sectionArrayList.contains("Social History")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Social History");
			if ((fieldsArrayList != null && fieldsArrayList.contains("Alcohol"))
					|| sectionArrayList.isEmpty()) {
				if (cbAlcohol.getValue()) {
					map.put("pnotesshalcoholstatus", "1");
					map.put("pnotesshalcoholcmnt", tbAlcohol.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Tobacco"))
					|| sectionArrayList.isEmpty()) {
				if (cbTobacco.getValue()) {
					map.put("pnotesshtobaccostatus", "1");
					map.put("pnotesshtobaccocmnt", tbTobacco.getText());
					if (cbCounseledCessation.getValue()) {
						map.put("pnotesshtcounseled", "1");
					}
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Illicit drugs"))
					|| sectionArrayList.isEmpty()) {
				if (cbIllDrugs.getValue()) {
					map.put("pnotesshilctdrugstatus", "1");
					map.put("pnotesshilctdrugscmnt", tbIllDrugs.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Lives with"))
					|| sectionArrayList.isEmpty()) {
				if (cbLivesWith.getValue()) {
					map.put("pnotesshliveswithstatus", "1");
					map.put("pnotesshliveswithcmnt", tbLivesWith.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Occupation"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotesshoccupation", tbOccupation.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("HIV risk factors"))
					|| sectionArrayList.isEmpty()) {
				if (cbHivRiskFactor.getValue()) {
					map.put("pnotesshivrskfacstatus", "1");
					map.put("pnotesshivrskfaccmnt", tbHivRiskFactor.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Travel"))
					|| sectionArrayList.isEmpty()) {
				if (cbTravel.getValue()) {
					map.put("pnotesshtravelstatus", "1");
					map.put("pnotesshtravelcmnt", tbTravel.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Pets"))
					|| sectionArrayList.isEmpty()) {
				if (cbPets.getValue()) {
					map.put("pnotesshpetsstatus", "1");
					map.put("pnotesshpetscmnt", tbPets.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Hobbies"))
					|| sectionArrayList.isEmpty()) {
				if (cbHobbies.getValue()) {
					map.put("pnotesshhobbiesstatus", "1");
					map.put("pnotesshhobbiescmnt", tbHobbies.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Housing"))
					|| sectionArrayList.isEmpty()) {
				if (radHousing.getWidgetValue() != null) {
					map.put("pnotesshhousing", radHousing.getWidgetValue());
				}
			}
		}

		if (sectionArrayList.contains("Exam") || sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Exam");
			if ((fieldsArrayList != null && fieldsArrayList.contains("Head"))
					|| sectionArrayList.isEmpty()) {
				List<String> headArrayList = sectionsFieldMap
						.get("Sections#Exam#Head");
				if ((headArrayList != null && headArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespeheadfreecmnt", tbHeadFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Eyes"))
					|| sectionArrayList.isEmpty()) {
				List<String> eyesArrayList = sectionsFieldMap
						.get("Sections#Exam#Eyes");
				if ((eyesArrayList != null && eyesArrayList
						.contains("Conjunctivae_lids_pupils & irises"))
						|| sectionArrayList.isEmpty()) {
					if (cbCLPI.getValue() && radClpi.getWidgetValue() != null) {
						if (radClpi.getWidgetValue().equals("1")) {
							map.put("pnotespeeyeclpistatus", radClpi
									.getWidgetValue());
						} else if (radClpi.getWidgetValue().equals("2")) {
							map.put("pnotespeeyeclpistatus", radClpi
									.getWidgetValue());
							map.put("pnotespeeyeclpicmnt", tbClpi.getText());
						}
					}
				}
				if ((eyesArrayList != null && eyesArrayList.contains("Fundi"))
						|| sectionArrayList.isEmpty()) {
					List<String> fundiArrayList = sectionsFieldMap
							.get("Sections#Exam#Eyes#Fundi");
					if ((fundiArrayList != null && fundiArrayList
							.contains("Disc edges sharp"))
							|| sectionArrayList.isEmpty()) {
						if (cbDiscEdgeSharp.getValue()
								&& radDiscEdgeSharp.getWidgetValue() != null) {
							if (radDiscEdgeSharp.getWidgetValue().equals("1")) {
								map.put("pnotespeeyedesstatus",
										radDiscEdgeSharp.getWidgetValue());
							} else if (radDiscEdgeSharp.getWidgetValue()
									.equals("2")) {
								map.put("pnotespeeyedesstatus",
										radDiscEdgeSharp.getWidgetValue());
								map.put("pnotespeeyedescmnt", tbDiscEdgeSharp
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("Venous pulses seen"))
							|| sectionArrayList.isEmpty()) {
						if (cbVenPul.getValue()
								&& radVenPul.getWidgetValue() != null) {
							if (radVenPul.getWidgetValue().equals("1")) {
								map.put("pnotespeeyevpsstatus", radVenPul
										.getWidgetValue());
							} else if (radVenPul.getWidgetValue().equals("2")) {
								map.put("pnotespeeyevpsstatus", radVenPul
										.getWidgetValue());
								map.put("pnotespeeyevpscmnt", tbVenPul
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("A-V nicking"))
							|| sectionArrayList.isEmpty()) {
						if (cbAVNicking.getValue()
								&& radAVNicking.getWidgetValue() != null) {
							if (radAVNicking.getWidgetValue().equals("1")) {
								map.put("pnotespeeyeavnstatus", radAVNicking
										.getWidgetValue());
							} else if (radAVNicking.getWidgetValue()
									.equals("2")) {
								map.put("pnotespeeyeavnstatus", radAVNicking
										.getWidgetValue());
								map.put("pnotespeeyeavncmnt", tbAVNicking
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("Hemorrhages"))
							|| sectionArrayList.isEmpty()) {
						if (cbHemorrhages.getValue()
								&& radHemorrhages.getWidgetValue() != null) {
							if (radHemorrhages.getWidgetValue().equals("1")) {
								map.put("pnotespeeyehemstatus", radHemorrhages
										.getWidgetValue());
							} else if (radHemorrhages.getWidgetValue().equals(
									"2")) {
								map.put("pnotespeeyehemstatus", radHemorrhages
										.getWidgetValue());
								map.put("pnotespeeyehemcmnt", tbHemorrhages
										.getText());
							}
						}
					}
					if ((fundiArrayList != null && fundiArrayList
							.contains("Exudates"))
							|| sectionArrayList.isEmpty()) {
						if (cbExudates.getValue()
								&& radExudates.getWidgetValue() != null) {
							if (radExudates.getWidgetValue().equals("1")) {
								map.put("pnotespeeyeexustatus", radExudates
										.getWidgetValue());
							} else if (radExudates.getWidgetValue().equals("2")) {
								map.put("pnotespeeyeexustatus", radExudates
										.getWidgetValue());
								map.put("pnotespeeyeexucmnt", tbExudates
										.getText());
							}
						}
					}
				}
				if ((eyesArrayList != null && eyesArrayList
						.contains("Cup:disc ratio"))
						|| sectionArrayList.isEmpty()) {
					map
							.put("pnotespeeyecupdiscratio", tbCupDiscRatio
									.getText());
				}
				if ((eyesArrayList != null && eyesArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespeeyefreecmnt", tbEyeFreeForm
							.getText());
				}

			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("ENT"))
					|| sectionArrayList.isEmpty()) {
				List<String> entArrayList = sectionsFieldMap
						.get("Sections#Exam#ENT");
				if ((entArrayList != null && entArrayList
						.contains("External canals_TMs"))
						|| sectionArrayList.isEmpty()) {
					if (cbExtCanTms.getValue()
							&& radExtCanTms.getWidgetValue() != null) {
						if (radExtCanTms.getWidgetValue().equals("1")) {
							map.put("pnotespeentectstatus", radExtCanTms
									.getWidgetValue());
						} else if (radExtCanTms.getWidgetValue().equals("2")) {
							map.put("pnotespeentectstatus", radExtCanTms
									.getWidgetValue());
							map
									.put("pnotespeentectcmnt", tbExtCanTms
											.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Nasal mucosa_septum"))
						|| sectionArrayList.isEmpty()) {
					if (cbNMS.getValue() && radNMS.getWidgetValue() != null) {
						if (radNMS.getWidgetValue().equals("1")) {
							map.put("pnotespeentnmsstatus", radNMS
									.getWidgetValue());
						} else if (radNMS.getWidgetValue().equals("2")) {
							map.put("pnotespeentnmsstatus", radNMS
									.getWidgetValue());
							map.put("pnotespeentnmscmnt", tbNMS.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Lips_gums_teeth"))
						|| sectionArrayList.isEmpty()) {
					if (cbLGT.getValue() && radLGT.getWidgetValue() != null) {
						if (radLGT.getWidgetValue().equals("1")) {
							map.put("pnotespeentlgtstatus", radLGT
									.getWidgetValue());
						} else if (radLGT.getWidgetValue().equals("2")) {
							map.put("pnotespeentlgtstatus", radLGT
									.getWidgetValue());
							map.put("pnotespeentlgtcmnt", tbLGT.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Oropharynx_mucosa_salivary glands"))
						|| sectionArrayList.isEmpty()) {
					if (cbOMS.getValue() && radOMS.getWidgetValue() != null) {
						if (radOMS.getWidgetValue().equals("1")) {
							map.put("pnotespeentomsgstatus", radOMS
									.getWidgetValue());
						} else if (radOMS.getWidgetValue().equals("2")) {
							map.put("pnotespeentomsgstatus", radOMS
									.getWidgetValue());
							map.put("pnotespeentomsgcmnt", tbOMS.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Hard/soft palate_tongue_tonsils_posterior pharynx"))
						|| sectionArrayList.isEmpty()) {
					if (cbHTTP.getValue() && radHTTP.getWidgetValue() != null) {
						if (radHTTP.getWidgetValue().equals("1")) {
							map.put("pnotespeenthttpstatus", radHTTP
									.getWidgetValue());
						} else if (radHTTP.getWidgetValue().equals("2")) {
							map.put("pnotespeenthttpstatus", radHTTP
									.getWidgetValue());
							map.put("pnotespeenthttpcmnt", tbHTTP.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList.contains("Thyroid"))
						|| sectionArrayList.isEmpty()) {
					if (cbThyroid.getValue()
							&& radThyroid.getWidgetValue() != null) {
						if (radThyroid.getWidgetValue().equals("1")) {
							map.put("pnotespeentthyrostatus", radThyroid
									.getWidgetValue());
						} else if (radThyroid.getWidgetValue().equals("2")) {
							map.put("pnotespeentthyrostatus", radThyroid
									.getWidgetValue());
							map
									.put("pnotespeentthyrocmnt", tbThyroid
											.getText());
						}
					}
				}
				if ((entArrayList != null && entArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespeentfreecmnt", tbEntFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Neck"))
					|| sectionArrayList.isEmpty()) {
				List<String> neckArrayList = sectionsFieldMap
						.get("Sections#Exam#Neck");
				if ((neckArrayList != null && neckArrayList
						.contains("Neck (note bruit_JVD)"))
						|| sectionArrayList.isEmpty()) {
					if (cbNeck.getValue() && radNeck.getWidgetValue() != null) {
						if (radNeck.getWidgetValue().equals("1")) {
							map.put("pnotespeneckbrjvdstatus", radNeck
									.getWidgetValue());
						} else if (radNeck.getWidgetValue().equals("2")) {
							map.put("pnotespeneckbrjvdstatus", radNeck
									.getWidgetValue());
							map.put("pnotespeneckbrjvdcmnt", tbNeckExam
									.getText());
						}
					}
				}
				if ((neckArrayList != null && neckArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespeneckfreecmnt", tbNeckFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Breast"))
					|| sectionArrayList.isEmpty()) {
				List<String> breastArrayList = sectionsFieldMap
						.get("Sections#Exam#Breast");
				if ((breastArrayList != null && breastArrayList
						.contains("Breasts (note dimpling_discharge_mass)"))
						|| sectionArrayList.isEmpty()) {
					if (cbBreast.getValue()
							&& radBreast.getWidgetValue() != null) {
						if (radBreast.getWidgetValue().equals("1")) {
							map.put("pnotespebrstddmstatus", radBreast
									.getWidgetValue());
						} else if (radBreast.getWidgetValue().equals("2")) {
							map.put("pnotespebrstddmstatus", radBreast
									.getWidgetValue());
							map.put("pnotespebrstddmcmnt", tbBreastExam
									.getText());
						}
					}
				}
				if ((breastArrayList != null && breastArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespebrstfreecmnt", tbBreastFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Resp"))
					|| sectionArrayList.isEmpty()) {
				List<String> respArrayList = sectionsFieldMap
						.get("Sections#Exam#Resp");
				if ((respArrayList != null && respArrayList
						.contains("Respiratory effort"))
						|| sectionArrayList.isEmpty()) {
					if (cbRespEff.getValue()
							&& radRespEff.getWidgetValue() != null) {
						if (radRespEff.getWidgetValue().equals("1")) {
							map.put("pnotesperespeffstatus", radRespEff
									.getWidgetValue());
						} else if (radRespEff.getWidgetValue().equals("2")) {
							map.put("pnotesperespeffstatus", radRespEff
									.getWidgetValue());
							map.put("pnotesperespeffcmnt", tbRespEff.getText());
						}
					}
				}
				if ((respArrayList != null && respArrayList
						.contains("Lung percussion & auscultation"))
						|| sectionArrayList.isEmpty()) {
					if (cbLPA.getValue() && radLPA.getWidgetValue() != null) {
						if (radLPA.getWidgetValue().equals("1")) {
							map.put("pnotesperesplungstatus", radLPA
									.getWidgetValue());
						} else if (radLPA.getWidgetValue().equals("2")) {
							map.put("pnotesperesplungstatus", radLPA
									.getWidgetValue());
							map.put("pnotesperesplungcmnt", tbLPA.getText());
						}
					}
				}
				if ((respArrayList != null && respArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotesperespfreecmnt", tbRespFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("CV"))
					|| sectionArrayList.isEmpty()) {
				List<String> cvArrayList = sectionsFieldMap
						.get("Sections#Exam#CV");
				if ((cvArrayList != null && cvArrayList
						.contains("Auscultation"))
						|| sectionArrayList.isEmpty()) {
					List<String> auscultationArrayList = sectionsFieldMap
							.get("Sections#Exam#CV#Auscultation");
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("Regular rhythm"))
							|| sectionArrayList.isEmpty()) {
						if (cbRegRyth.getValue()
								&& radRegRyth.getWidgetValue() != null) {
							if (radRegRyth.getWidgetValue().equals("1")) {
								map.put("pnotespecvregrhystatus", radRegRyth
										.getWidgetValue());
							} else if (radRegRyth.getWidgetValue().equals("2")) {
								map.put("pnotespecvregrhystatus", radRegRyth
										.getWidgetValue());
								map.put("pnotespecvregrhycmnt", tbRegRyth
										.getText());
							}
						}
					}
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("S1 constant"))
							|| sectionArrayList.isEmpty()) {
						if (cbS1Cons.getValue()
								&& radS1Cons.getWidgetValue() != null) {
							if (radS1Cons.getWidgetValue().equals("1")) {
								map.put("pnotespecvs1consstatus", radS1Cons
										.getWidgetValue());
							} else if (radS1Cons.getWidgetValue().equals("2")) {
								map.put("pnotespecvs1consstatus", radS1Cons
										.getWidgetValue());
								map.put("pnotespecvs1conscmnt", tbS1Cons
										.getText());
							}
						}
					}
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("S2 physiologic split"))
							|| sectionArrayList.isEmpty()) {
						if (cbS2PhysSplit.getValue()
								&& radPhysSplit.getWidgetValue() != null) {
							if (radPhysSplit.getWidgetValue().equals("1")) {
								map.put("pnotespecvs2physplstatus",
										radPhysSplit.getWidgetValue());
							} else if (radPhysSplit.getWidgetValue()
									.equals("2")) {
								map.put("pnotespecvs2physplstatus",
										radPhysSplit.getWidgetValue());
								map.put("pnotespecvs2physplcmnt", tbPhysSplit
										.getText());
							}
						}
					}
					if ((auscultationArrayList != null && auscultationArrayList
							.contains("Murmur (describe)"))
							|| sectionArrayList.isEmpty()) {
						if (cbMurmur.getValue()
								&& radMurmur.getWidgetValue() != null) {
							if (radMurmur.getWidgetValue().equals("1")) {
								map.put("pnotespecvmurstatus", radMurmur
										.getWidgetValue());
							} else if (radMurmur.getWidgetValue().equals("2")) {
								map.put("pnotespecvmurstatus", radMurmur
										.getWidgetValue());
								map
										.put("pnotespecvmurcmnt", tbMurmur
												.getText());
							}
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Palpation of heart"))
						|| sectionArrayList.isEmpty()) {
					if (cbPalHrt.getValue()
							&& radPalHrt.getWidgetValue() != null) {
						if (radPalHrt.getWidgetValue().equals("1")) {
							map.put("pnotespecvpalhrtstatus", radPalHrt
									.getWidgetValue());
						} else if (radPalHrt.getWidgetValue().equals("2")) {
							map.put("pnotespecvpalhrtstatus", radPalHrt
									.getWidgetValue());
							map.put("pnotespecvpalhrtcmnt", tbPalHrt.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Abdominal aorta"))
						|| sectionArrayList.isEmpty()) {
					if (cbAbAorta.getValue()
							&& radAbAorta.getWidgetValue() != null) {
						if (radAbAorta.getWidgetValue().equals("1")) {
							map.put("pnotespecvabdaorstatus", radAbAorta
									.getWidgetValue());
						} else if (radAbAorta.getWidgetValue().equals("2")) {
							map.put("pnotespecvabdaorstatus", radAbAorta
									.getWidgetValue());
							map
									.put("pnotespecvabdaorcmnt", tbAbAorta
											.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Femoral arteries"))
						|| sectionArrayList.isEmpty()) {
					if (cbFemArt.getValue()
							&& radFemArt.getWidgetValue() != null) {
						if (radFemArt.getWidgetValue().equals("1")) {
							map.put("pnotespecvfemartstatus", radFemArt
									.getWidgetValue());
						} else if (radFemArt.getWidgetValue().equals("2")) {
							map.put("pnotespecvfemartstatus", radFemArt
									.getWidgetValue());
							map.put("pnotespecvfemartcmnt", tbFemArt.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Pedal pulses"))
						|| sectionArrayList.isEmpty()) {
					if (cbPedalPulses.getValue()
							&& radPedalPulses.getWidgetValue() != null) {
						if (radPedalPulses.getWidgetValue().equals("1")) {
							map.put("pnotespecvpedpulstatus", radPedalPulses
									.getWidgetValue());
						} else if (radPedalPulses.getWidgetValue().equals("2")) {
							map.put("pnotespecvpedpulstatus", radPedalPulses
									.getWidgetValue());
							map.put("pnotespecvpadpulcmnt", tbPedalPulses
									.getText());
						}
					}
				}
				if ((cvArrayList != null && cvArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespecvfreecmnt", tbCVFreeForm
							.getText());
				}
			}

			if ((fieldsArrayList != null && fieldsArrayList.contains("GI"))
					|| sectionArrayList.isEmpty()) {
				List<String> giArrayList = sectionsFieldMap
						.get("Sections#Exam#GI");
				if ((giArrayList != null && giArrayList.contains("Abdomen"))
						|| sectionArrayList.isEmpty()) {
					List<String> abdomenArrayList = sectionsFieldMap
							.get("Sections#Exam#GI#Abdomen");
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Scars"))
							|| sectionArrayList.isEmpty()) {
						if (cbScars.getValue()
								&& radScars.getWidgetValue() != null) {
							if (radScars.getWidgetValue().equals("1")) {
								map.put("pnotespegiscarsstatus", radScars
										.getWidgetValue());
							} else if (radScars.getWidgetValue().equals("2")) {
								map.put("pnotespegiscarsstatus", radScars
										.getWidgetValue());
								map.put("pnotespegiscarscmnt", tbScars
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Bruit"))
							|| sectionArrayList.isEmpty()) {
						if (cbBruit.getValue()
								&& radBruit.getWidgetValue() != null) {
							if (radBruit.getWidgetValue().equals("1")) {
								map.put("pnotespegibruitstatus", radBruit
										.getWidgetValue());
							} else if (radBruit.getWidgetValue().equals("2")) {
								map.put("pnotespegibruitstatus", radBruit
										.getWidgetValue());
								map.put("pnotespegibruitcmnt", tbBruit
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Mass"))
							|| sectionArrayList.isEmpty()) {
						if (cbMass.getValue()
								&& radMass.getWidgetValue() != null) {
							if (radMass.getWidgetValue().equals("1")) {
								map.put("pnotespegimassstatus", radMass
										.getWidgetValue());
							} else if (radMass.getWidgetValue().equals("2")) {
								map.put("pnotespegimassstatus", radMass
										.getWidgetValue());
								map.put("pnotespegimasscmnt", tbMass.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Tenderness"))
							|| sectionArrayList.isEmpty()) {
						if (cbTenderness.getValue()
								&& radTenderness.getWidgetValue() != null) {
							if (radTenderness.getWidgetValue().equals("1")) {
								map.put("pnotespegitendstatus", radTenderness
										.getWidgetValue());
							} else if (radTenderness.getWidgetValue().equals(
									"2")) {
								map.put("pnotespegitendstatus", radTenderness
										.getWidgetValue());
								map.put("pnotespegitendcmnt", tbTenderness
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Hepatomegaly"))
							|| sectionArrayList.isEmpty()) {
						if (cbHepatomegaly.getValue()
								&& radHepatomegaly.getWidgetValue() != null) {
							if (radHepatomegaly.getWidgetValue().equals("1")) {
								map.put("pnotespegiheptstatus", radHepatomegaly
										.getWidgetValue());
							} else if (radHepatomegaly.getWidgetValue().equals(
									"2")) {
								map.put("pnotespegiheptstatus", radHepatomegaly
										.getWidgetValue());
								map.put("pnotespegiheptcmnt", tbHepatomegaly
										.getText());
							}
						}
					}
					if ((abdomenArrayList != null && abdomenArrayList
							.contains("Splenomegaly"))
							|| sectionArrayList.isEmpty()) {
						if (cbSplenomegaly.getValue()
								&& radSplenomegaly.getWidgetValue() != null) {
							if (radSplenomegaly.getWidgetValue().equals("1")) {
								map.put("pnotespegisplenstatus",
										radSplenomegaly.getWidgetValue());
							} else if (radSplenomegaly.getWidgetValue().equals(
									"2")) {
								map.put("pnotespegisplenstatus",
										radSplenomegaly.getWidgetValue());
								map.put("pnotespegisplencmnt", tbSplenomegaly
										.getText());
							}
						}
					}
				}
				if ((giArrayList != null && giArrayList
						.contains("Anus_perineum_rectum_sphincter tone"))
						|| sectionArrayList.isEmpty()) {
					if (cbAPRS.getValue() && radAPRS.getWidgetValue() != null) {
						if (radAPRS.getWidgetValue().equals("1")) {
							map.put("pnotespegiaprsstatus", radAPRS
									.getWidgetValue());
						} else if (radAPRS.getWidgetValue().equals("2")) {
							map.put("pnotespegiaprsstatus", radAPRS
									.getWidgetValue());
							map.put("pnotespegiaprscmnt", tbAPRS.getText());
						}
					}
				}
				if ((giArrayList != null && giArrayList
						.contains("Bowel sounds"))
						|| sectionArrayList.isEmpty()) {
					if (cbBowSnd.getValue()
							&& radBowSnd.getWidgetValue() != null) {
						if (radBowSnd.getWidgetValue().equals("1")) {
							map.put("pnotespegibowsndstatus", radBowSnd
									.getWidgetValue());
						} else {
							map.put("pnotespegibowsndstatus", radBowSnd
									.getWidgetValue());
							map.put("pnotespegibowsndcmnt", tbBowSnd.getText());
						}
					}
				}
				if ((giArrayList != null && giArrayList.contains("Stool"))
						|| sectionArrayList.isEmpty()) {
					if (cbStool.getValue() && radStool.getWidgetValue() != null) {
						if (radStool.getWidgetValue().equals("1")) {
							map.put("pnotespegistoolstatus", radStool
									.getWidgetValue());
						} else {
							map.put("pnotespegistoolstatus", radStool
									.getWidgetValue());
							map.put("pnotespegistoolcmnt", tbStool.getText());
						}
					}

				}
				if ((giArrayList != null && giArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespegifreecmnt", tbGIFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("GU"))
					|| sectionArrayList.isEmpty()) {
				List<String> guArrayList = sectionsFieldMap
						.get("Sections#Exam#GU");
				if ((guArrayList != null && guArrayList.contains("Gender"))
						|| sectionArrayList.isEmpty()) {
					if (radGender.getWidgetValue() != null) {
						if (radGender.getWidgetValue().equals("1")) {
							map.put("pnotespegugender", "Male");
							if (cbPenis.getValue()
									&& radPenis.getWidgetValue() != null) {
								if (radPenis.getWidgetValue().equals("1")) {
									map.put("pnotespegupenisstatus", radPenis
											.getWidgetValue());
								} else if (radPenis.getWidgetValue()
										.equals("2")) {
									map.put("pnotespegupenisstatus", radPenis
											.getWidgetValue());
									map.put("pnotespegupeniscmnt", tbPenis
											.getText());
								}
							}
							if (cbTestes.getValue()
									&& radTestes.getWidgetValue() != null) {
								if (radTestes.getWidgetValue().equals("1")) {
									map.put("pnotespegutestesstatus", radTestes
											.getWidgetValue());
								} else if (radTestes.getWidgetValue().equals(
										"2")) {
									map.put("pnotespegutestesstatus", radTestes
											.getWidgetValue());
									map.put("pnotespegutestescmnt", tbTestes
											.getText());
								}
							}
							if (cbProstate.getValue()
									&& radProstate.getWidgetValue() != null) {
								if (radProstate.getWidgetValue().equals("1")) {
									map.put("pnotespeguproststatus",
											radProstate.getWidgetValue());
								} else if (radProstate.getWidgetValue().equals(
										"2")) {
									map.put("pnotespeguproststatus",
											radProstate.getWidgetValue());
									map.put("pnotespeguprostcmnt", tbProstate
											.getText());
								}
							}
						} else if (radGender.getWidgetValue().equals("2")) {
							map.put("pnotespegugender", "Female");
							if (cbExtGen.getValue()
									&& radExtGen.getWidgetValue() != null) {
								if (radExtGen.getWidgetValue().equals("1")) {
									map.put("pnotespeguextgenstatus", radExtGen
											.getWidgetValue());
								} else if (radExtGen.getWidgetValue().equals(
										"2")) {
									map.put("pnotespeguextgenstatus", radExtGen
											.getWidgetValue());
									map.put("pnotespeguextgencmnt", tbExtGen
											.getText());
								}
							}
							if (cbCervix.getValue()
									&& radCervix.getWidgetValue() != null) {
								if (radCervix.getWidgetValue().equals("1")) {
									map.put("pnotespegucervixstatus", radCervix
											.getWidgetValue());
								} else if (radCervix.getWidgetValue().equals(
										"2")) {
									map.put("pnotespegucervixstatus", radCervix
											.getWidgetValue());
									map.put("pnotespegucervixcmnt", tbCervix
											.getText());
								}
							}
							if (cbUteAdn.getValue()
									&& radUteAdn.getWidgetValue() != null) {
								if (radUteAdn.getWidgetValue().equals("1")) {
									map.put("pnotespeguutadnstatus", radUteAdn
											.getWidgetValue());
								} else if (radUteAdn.getWidgetValue().equals(
										"2")) {
									map.put("pnotespeguutadnstatus", radUteAdn
											.getWidgetValue());
									map.put("pnotespeguutadncmnt", tbUteAdn
											.getText());
								}
							}
						}
					}
				}
				if ((guArrayList != null && guArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespegufreecmnt", tbGUFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Lymphatics"))
					|| sectionArrayList.isEmpty()) {
				List<String> lympArrayList = sectionsFieldMap
						.get("Sections#Exam#Lymphatics");
				if ((lympArrayList != null && lympArrayList
						.contains("Lymph nodes"))
						|| sectionArrayList.isEmpty()) {
					if (cbLympNode.getValue()
							&& radLympNode.getWidgetValue() != null) {
						if (radLympNode.getWidgetValue().equals("1")) {
							map.put("pnotespelympnodesstatus", radLympNode
									.getWidgetValue());
						} else if (radLympNode.getWidgetValue().equals("2")) {
							map.put("pnotespelympnodesstatus", radLympNode
									.getWidgetValue());
							map.put("pnotespelympnodescmnt", tbLympNode
									.getText());
						}
					}
				}
				if ((lympArrayList != null && lympArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespelympfreecmnt", tbLympFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Skin"))
					|| sectionArrayList.isEmpty()) {
				List<String> skinArrayList = sectionsFieldMap
						.get("Sections#Exam#Skin");
				if ((skinArrayList != null && skinArrayList
						.contains("Skin & SQ tissue"))
						|| sectionArrayList.isEmpty()) {
					if (cbSkinSQTissue.getValue()
							&& radSkinSQTissue.getWidgetValue() != null) {
						if (radSkinSQTissue.getWidgetValue().equals("1")) {
							map.put("pnotespeskintissuestatus", radSkinSQTissue
									.getWidgetValue());
						} else if (radSkinSQTissue.getWidgetValue().equals("2")) {
							map.put("pnotespeskintissuestatus", radSkinSQTissue
									.getWidgetValue());
							map.put("pnotespeskintissuecmnt", tbSkinSQTissue
									.getText());
						}
					}
				}
				if ((skinArrayList != null && skinArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespeskinfreecmnt", tbSkinFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("MS"))
					|| sectionArrayList.isEmpty()) {
				List<String> msArrayList = sectionsFieldMap
						.get("Sections#Exam#MS");
				if ((msArrayList != null && msArrayList
						.contains("Gait & station"))
						|| sectionArrayList.isEmpty()) {
					if (cbGaitStat.getValue()
							&& radGaitStat.getWidgetValue() != null) {
						if (radGaitStat.getWidgetValue().equals("1")) {
							map.put("pnotespemsgaitststatus", radGaitStat
									.getWidgetValue());
						} else if (radGaitStat.getWidgetValue().equals("2")) {
							map.put("pnotespemsgaitststatus", radGaitStat
									.getWidgetValue());
							map.put("pnotespemsgaitstcmnt", tbGaitStat
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Digits_nails"))
						|| sectionArrayList.isEmpty()) {
					if (cbDigitsNails.getValue()
							&& radDigitsNails.getWidgetValue() != null) {
						if (radDigitsNails.getWidgetValue().equals("1")) {
							map.put("pnotespemsdignlsstatus", radDigitsNails
									.getWidgetValue());
						} else if (radDigitsNails.getWidgetValue().equals("2")) {
							map.put("pnotespemsdignlsstatus", radDigitsNails
									.getWidgetValue());
							map.put("pnotespemsdignlscmnt", tbDigitsNails
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("ROM_stability"))
						|| sectionArrayList.isEmpty()) {
					if (cbRomStability.getValue()
							&& radRomStability.getWidgetValue() != null) {
						if (radRomStability.getWidgetValue().equals("1")) {
							map.put("pnotespemsromstbstatus", radRomStability
									.getWidgetValue());
						} else if (radRomStability.getWidgetValue().equals("2")) {
							map.put("pnotespemsromstbstatus", radRomStability
									.getWidgetValue());
							map.put("pnotespemsromstbcmnt", tbRomStability
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Joints_bones_muscles"))
						|| sectionArrayList.isEmpty()) {
					if (cbJntBnsMusc.getValue()
							&& radJntBnsMusc.getWidgetValue() != null) {
						if (radJntBnsMusc.getWidgetValue().equals("1")) {
							map.put("pnotespemsjntbnsmusstatus", radJntBnsMusc
									.getWidgetValue());
						} else if (radJntBnsMusc.getWidgetValue().equals("2")) {
							map.put("pnotespemsjntbnsmusstatus", radJntBnsMusc
									.getWidgetValue());
							map.put("pnotespemsjntbnsmuscmnt", tbJntBnsMusc
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Muscle strength & tone"))
						|| sectionArrayList.isEmpty()) {
					if (cbMuscStrg.getValue()
							&& radMuscStrg.getWidgetValue() != null) {
						if (radMuscStrg.getWidgetValue().equals("1")) {
							map.put("pnotespemsmusstrtnstatus", radMuscStrg
									.getWidgetValue());
						} else if (radMuscStrg.getWidgetValue().equals("2")) {
							map.put("pnotespemsmusstrtnstatus", radMuscStrg
									.getWidgetValue());
							map.put("pnotespemsmusstrtncmnt", tbMuscStrg
									.getText());
						}
					}
				}
				if ((msArrayList != null && msArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespemsfreecmnt", tbMSFreeForm
							.getText());
				}
			}

			if ((fieldsArrayList != null && fieldsArrayList.contains("Neuro"))
					|| sectionArrayList.isEmpty()) {
				List<String> neuroArrayList = sectionsFieldMap
						.get("Sections#Exam#Neuro");
				if ((neuroArrayList != null && neuroArrayList
						.contains("Cranial nerves (note deficits)"))
						|| sectionArrayList.isEmpty()) {
					if (cbCranNerves.getValue()
							&& radCranNerves.getWidgetValue() != null) {
						if (radCranNerves.getWidgetValue().equals("1")) {
							map.put("pnotespeneurocrnervstatus", radCranNerves
									.getWidgetValue());
						} else if (radCranNerves.getWidgetValue().equals("2")) {
							map.put("pnotespeneurocrnervstatus", radCranNerves
									.getWidgetValue());
							map.put("pnotespeneurocrnervcmnt", tbCranNerves
									.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList.contains("DTRs"))
						|| sectionArrayList.isEmpty()) {
					if (cbDTRs.getValue() && radDTRs.getWidgetValue() != null) {
						if (radDTRs.getWidgetValue().equals("1")) {
							map.put("pnotespeneurodtrsstatus", radDTRs
									.getWidgetValue());
						} else if (radDTRs.getWidgetValue().equals("2")) {
							map.put("pnotespeneurodtrsstatus", radDTRs
									.getWidgetValue());
							map.put("pnotespeneurodtrscmnt", tbDTRs.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList.contains("Motor"))
						|| sectionArrayList.isEmpty()) {
					if (cbMotor.getValue() && radMotor.getWidgetValue() != null) {
						if (radMotor.getWidgetValue().equals("1")) {
							map.put("pnotespeneuromotorstatus", radMotor
									.getWidgetValue());
						} else if (radMotor.getWidgetValue().equals("2")) {
							map.put("pnotespeneuromotorstatus", radMotor
									.getWidgetValue());
							map
									.put("pnotespeneuromotorcmnt", tbMotor
											.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList
						.contains("Sensation"))
						|| sectionArrayList.isEmpty()) {
					if (cbSensation.getValue()
							&& radSensation.getWidgetValue() != null) {
						if (radSensation.getWidgetValue().equals("1")) {
							map.put("pnotespeneurosnststatus", radSensation
									.getWidgetValue());
						} else if (radSensation.getWidgetValue().equals("2")) {
							map.put("pnotespeneurosnststatus", radSensation
									.getWidgetValue());
							map.put("pnotespeneurosnstcmnt", tbSensation
									.getText());
						}
					}
				}
				if ((neuroArrayList != null && neuroArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespeneurofreecmnt", tbNeuroFreeForm
							.getText());
				}
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Psych"))
					|| sectionArrayList.isEmpty()) {
				List<String> psychArrayList = sectionsFieldMap
						.get("Sections#Exam#Psych");
				if ((psychArrayList != null && psychArrayList
						.contains("Judgment & insight"))
						|| sectionArrayList.isEmpty()) {
					if (cbJudIns.getValue()
							&& radJudIns.getWidgetValue() != null) {
						if (radJudIns.getWidgetValue().equals("1")) {
							map.put("pnotespepsychjudinsstatus", radJudIns
									.getWidgetValue());
						} else if (radJudIns.getWidgetValue().equals("2")) {
							map.put("pnotespepsychjudinsstatus", radJudIns
									.getWidgetValue());
							map.put("pnotespepsychjudinscmnt", tbJudIns
									.getText());
						}
					}
				}
				if ((psychArrayList != null && psychArrayList
						.contains("Mood & affect"))
						|| sectionArrayList.isEmpty()) {
					if (cbMoodEffect.getValue()
							&& radMoodEffect.getWidgetValue() != null) {
						if (radMoodEffect.getWidgetValue().equals("1")) {
							map.put("pnotespepsychmoodeffstatus", radMoodEffect
									.getWidgetValue());
						} else if (radMoodEffect.getWidgetValue().equals("2")) {
							map.put("pnotespepsychmoodeffstatus", radMoodEffect
									.getWidgetValue());
							map.put("pnotespepsychmoodeffcmnt", tbMoodEffect
									.getText());
						}
					}

				}
				if ((psychArrayList != null && psychArrayList
						.contains("Oriented to time_place_person"))
						|| sectionArrayList.isEmpty()) {
					if (cbOrTimePlcPers.getValue()
							&& radOrTimePlcPers.getWidgetValue() != null) {
						if (radOrTimePlcPers.getWidgetValue().equals("1")) {
							map.put("pnotespepsychorntppstatus",
									radOrTimePlcPers.getWidgetValue());
						} else if (radOrTimePlcPers.getWidgetValue()
								.equals("2")) {
							map.put("pnotespepsychorntppstatus",
									radOrTimePlcPers.getWidgetValue());
							map.put("pnotespepsychorntppcmnt", tbOrTimePlcPers
									.getText());
						}
					}
				}
				if ((psychArrayList != null && psychArrayList
						.contains("Memory"))
						|| sectionArrayList.isEmpty()) {
					if (cbMemory.getValue()
							&& radMemory.getWidgetValue() != null) {
						if (radMemory.getWidgetValue().equals("1")) {
							map.put("pnotespepsychmemorystatus", radMemory
									.getWidgetValue());
						} else if (radMemory.getWidgetValue().equals("2")) {
							map.put("pnotespepsychmemorystatus", radMemory
									.getWidgetValue());
							map.put("pnotespepsychmemorycmnt", tbMemory
									.getText());
						}
					}
				}
				if ((psychArrayList != null && psychArrayList
						.contains("Free Form Entry"))
						|| sectionArrayList.isEmpty()) {
					map
					.put("pnotespepsychfreecmnt", tbPsychFreeForm
							.getText());
				}
			}
			Set<String> keys = billingFieldsWidgetsMap.keySet();
			Iterator<String> iter = keys.iterator();
			HashMap<String, HashMap<String, String>> m = new HashMap<String, HashMap<String, String>>();
			while (iter.hasNext()) {
				String key = iter.next();
				BillInfoWidget biw = billingFieldsWidgetsMap.get(key);
				HashMap<String, String> billVal = new HashMap<String, String>();
				billVal.put("proccode", biw.getProceduralCode().toString());
				billVal.put("diagcode", biw.getDiagnosisCode().toString());
				m.put(key, billVal);
			}
			if (!m.isEmpty()) {
				map.put("pnotesbillable", JsonUtil.jsonify(m));
			}
		}

		if (sectionArrayList.contains("Assessment/Plan")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Assessment/Plan");
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Assessment"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_A", tbAssessment.getText());
			}
			if ((fieldsArrayList != null && fieldsArrayList.contains("Plan"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnotes_P", tbPlanAssess.getText());
			}
		}

		if (sectionArrayList.contains("Free Form Entry")
				|| sectionArrayList.isEmpty()) {
			List<String> fieldsArrayList = sectionsFieldMap
					.get("Sections#Free Form Entry");
			if ((fieldsArrayList != null && fieldsArrayList
					.contains("Free Form Entry"))
					|| sectionArrayList.isEmpty()) {
				map.put("pnoteshandp", rte.getHTML());
			}
		}
		List params = new ArrayList();
		params.add(map);
		if (formmode == EncounterFormMode.ADD) {
			Util.callModuleMethod("EncounterNotes", "add", params,
					new CustomRequestCallback() {
						@Override
						public void onError() {
							Util.showErrorMsg("EncounterNotes",
							"Encounter Note Creation Failed.");
						}
	
						@SuppressWarnings("unchecked")
						@Override
						public void jsonifiedData(Object data) {
							if (data != null) {
								Integer result = (Integer) data;
								if (result> 0) {
									Util
									.showInfoMsg("EncounterNotes",
											"Encounter Note Successfully Created.");
									List<String> sectionArrayList = sectionsFieldMap
											.get("Sections");
									if (sectionArrayList == null
											|| sectionArrayList
													.contains("Billing Information")) {
										getBillAmount();
									} else {
										callback
												.jsonifiedData(EncounterCommandType.UPDATE);
									}
								}
								else{
									Util.showErrorMsg("EncounterNotes",
									"Encounter Note Creation Failed.");
								}
							}
							else{
								Util.showErrorMsg("EncounterNotes",
								"Encounter Note Creation Failed.");
							}
						}
					}, "Integer");
		}
		else if (formmode == EncounterFormMode.EDIT) {
			Util.callModuleMethod("EncounterNotes", "mod", params,
					new CustomRequestCallback() {
						@Override
						public void onError() {
							Util.showErrorMsg("EncounterNotes",
							"Encounter Note Modification Failed.");
						}
	
						@SuppressWarnings("unchecked")
						@Override
						public void jsonifiedData(Object data) {
							if (data != null) {
								Boolean result = (Boolean) data;
								if (result) {
									Util
									.showInfoMsg("EncounterNotes",
											"Encounter Note  Successfully Modified.");
									callback
											.jsonifiedData(EncounterCommandType.UPDATE);
								}
								else{
									Util.showErrorMsg("EncounterNotes",
									"Encounter Note Modification Failed.");
								}
							}
							else{
								Util.showErrorMsg("EncounterNotes",
								"Encounter Note Modification Failed.");
							}
						}
					}, "Boolean");
		}
	}

	public void applyTemplate(String tid) {
		currTemplate = tid;
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { tid };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotesTemplate.getTemplateInfo",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									templateValuesMap = r;
									String sectionsStr = r
											.get("pnotestsections");
									sectionsFieldMap = (HashMap<String, List<String>>) JsonUtil
											.shoehornJson(JSONParser
													.parse(sectionsStr),
													"HashMap<String,List>");
									for (int i = tabPanel.getWidgetCount() - 1; i >= 1; i--) {
										tabPanel.remove(i);
									}
									if (templateValuesMap
											.containsKey("pnotesttype")
											&& templateValuesMap
													.get("pnotesttype") != null) {
										radType
												.setWidgetValue(templateValuesMap
														.get("pnotesttype"));
									}
									loadOtherTabs();

								} else {

								}
							} catch (Exception e) {

							}

						} else {

						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}

	public String getSelectedTemplate() {
		return templateWidget.getStoredValue();
	}

	public void loadCoverage(final int type, final CustomListBox clb) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { patientID, type + "" };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientCoverages.GetCoverageByType",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											for (int i = 0; i < 1; i++) {
												HashMap<String, String> m = (HashMap<String, String>) result[i];
												clb.addItem(m.get("comp_name"),
														m.get("Id"));
											}
										} else {

										}
									}
								} catch (Exception e) {
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	public void getBillAmount() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String selCov = "0";
			if (listPrimCov != null
					&& !listPrimCov.getStoredValue().equals("0")) {
				selCov = listPrimCov.getStoredValue();
			} else if (listSecCov != null
					&& !listSecCov.getStoredValue().equals("0")) {
				selCov = listSecCov.getStoredValue();
			} else if (listTertCov != null
					&& !listTertCov.getStoredValue().equals("0")) {
				selCov = listTertCov.getStoredValue();
			} else if (listWorkCov != null
					&& !listWorkCov.getStoredValue().equals("0")) {
				selCov = listWorkCov.getStoredValue();
			}
			String selUnits = JsonUtil.jsonify(tbProcUnits.getText());
			String selCode = JsonUtil.jsonify(procCodeWidget.getStoredValue());
			String selPro = JsonUtil.jsonify(provWidget.getStoredValue());

			String ptid = JsonUtil.jsonify(patientID);
			String[] params = { selCov, selUnits, selCode, selPro, ptid };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.CalculateCharge",
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
							Float result = (Float) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Float");

							if (result != null) {
								createProcedure(result);
							} else {
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {

				GWT.log("Exception", e);
			}
		}
	}

	public void createProcedure(final float cost) {
		HashMap<String, String> map = new HashMap<String, String>();
		map.put((String) "procpatient", patientID);
		map.put((String) "procphysician", provWidget.getStoredValue());

		if (mod1Widget != null && mod1Widget.getStoredValue() != null
				|| !mod1Widget.getStoredValue().equals(""))
			map.put((String) "proccptmod", mod1Widget.getStoredValue());
		if (mod2Widget != null && mod2Widget.getStoredValue() != null
				|| !mod2Widget.getStoredValue().equals(""))
			map.put((String) "proccptmod2", mod2Widget.getStoredValue());
		if (mod3Widget != null && mod3Widget.getStoredValue() != null
				|| !mod3Widget.getStoredValue().equals(""))
			map.put((String) "proccptmod3", mod3Widget.getStoredValue());
		if (diag1Widget != null && diag1Widget.getStoredValue() != null
				|| !diag1Widget.getStoredValue().equals(""))
			map.put((String) "procdiag1", diag1Widget.getStoredValue());
		if (diag2Widget != null && diag2Widget.getStoredValue() != null
				|| !diag2Widget.getStoredValue().equals(""))
			map.put((String) "procdiag2", diag2Widget.getStoredValue());
		if (diag3Widget != null && diag3Widget.getStoredValue() != null
				|| !diag3Widget.getStoredValue().equals(""))
			map.put((String) "procdiag3", diag3Widget.getStoredValue());
		if (diag4Widget != null && diag4Widget.getStoredValue() != null
				|| !diag4Widget.getStoredValue().equals(""))
			map.put((String) "procdiag4", diag4Widget.getStoredValue());
		if (date.getTextBox().getText() != null
				|| !date.getTextBox().getText().equals(""))
			map.put((String) "procdt", date.getTextBox().getText());
		if (procCodeWidget.getStoredValue() != null
				|| !procCodeWidget.getStoredValue().equals(""))
			map.put((String) "proccpt", procCodeWidget.getStoredValue());
		if (tbProcUnits != null && tbProcUnits.getText() != null
				|| !tbProcUnits.getText().equals(""))
			map.put((String) "procunits", tbProcUnits.getText());
		if (posWidget.getStoredValue() != null
				|| !posWidget.getStoredValue().equals(""))
			map.put((String) "procpos", posWidget.getStoredValue());
		if (listAuthorizations.getSelectedIndex() != 0)
			map.put((String) "procauth", listAuthorizations
					.getValue(listAuthorizations.getSelectedIndex()));
		String coverageId = "";
		String coverageType = "";
		if (listWorkCov != null && !listWorkCov.getStoredValue().equals("0")) {
			coverageType = "4";
			map.put((String) "proccov4", listWorkCov.getStoredValue());
			coverageId = listWorkCov.getStoredValue();
		}
		if (listTertCov != null && !listTertCov.getStoredValue().equals("0")) {
			coverageType = "3";
			map.put((String) "proccov3", listTertCov.getStoredValue());
			coverageId = listTertCov.getStoredValue();
		}
		if (listSecCov != null && !listSecCov.getStoredValue().equals("0")) {
			coverageType = "2";
			map.put((String) "proccov2", listSecCov.getStoredValue());
			coverageId = listSecCov.getStoredValue();
		}
		if (listPrimCov != null && !listPrimCov.getStoredValue().equals("0")) {
			coverageType = "1";
			map.put((String) "proccov1", listPrimCov.getStoredValue());
			coverageId = listPrimCov.getStoredValue();
		}

		if (!coverageId.equals(""))
			map.put((String) "proccurcovid", coverageId);
		if (!coverageType.equals(""))
			map.put((String) "proccurcovtp", coverageType);

		map.put((String) "procbillable", "1");

		map.put((String) "proccharges", "" + cost);
		map.put((String) "procbalorig", "" + cost);
		map.put((String) "procbalcurrent", "" + cost);

		String[] params = { JsonUtil.jsonify(map) };
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(
						"org.freemedsoftware.module.ProcedureModule.add",
						params)));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
				}

				public void onResponseReceived(Request request,
						Response response) {

					if (200 == response.getStatusCode()) {

						Integer result = (Integer) JsonUtil
								.shoehornJson(JSONParser.parse(response
										.getText()), "Integer");
						if (result != null && result > 0) {
							callback.jsonifiedData(EncounterCommandType.UPDATE);
						}

					} else {
						Util.showErrorMsg("ProcedureModule",
								"Procedure creation failed.");
					}
				}
			});
		} catch (RequestException e) {

		}
	}

	public void insertModuleText(final String modname, final TextArea tb) {
		if (modname.equals("Allergies") || modname.equals("Prescription")) {
			// Handling Counters
			String module = modname;
			List<String> param = new ArrayList<String>();
			param.add("");
			param.add(patientID);
			// param
			Util.callModuleMethod(module, "picklist", param,
					new CustomRequestCallback() {

						@Override
						public void onError() {
							// TODO Auto-generated method stub

						}

						@Override
						public void jsonifiedData(Object data) {
							// TODO Auto-generated method stub
							HashMap<String, String> result = (HashMap<String, String>) data;
							Set<String> s = result.keySet();
							String[] keys = s.toArray(new String[0]);
							String values = "\n\n";
							for (int i = 0; i < keys.length; i++) {
								values += keys[i] + "\n";
							}
							tb.setText(tb.getText() + values);
						}

					}, "HashMap<String,String>");
		}

		if (modname.equals("Hospitalization")) {
			// Handling Counters
			List<String> param = new ArrayList<String>();
			param.add(patientID);
			// param
			Util.callModuleMethod("EpisodeOfCare", "getHospitalizations",
					param, new CustomRequestCallback() {

						@Override
						public void onError() {
							// TODO Auto-generated method stub

						}

						@Override
						public void jsonifiedData(Object data) {
							// TODO Auto-generated method stub
							HashMap<String, String>[] result = (HashMap<String, String>[]) data;
							if (result != null) {
								String value = "\n\n";
								for (int i = 0; i < result.length; i++) {
									if (result[i].get("admit_date") != null)
										value += result[i].get("admit_date")
												+ "  ";
									value += "--  ";
									if (result[i].get("disch_date") != null)
										value += result[i].get("disch_date")
												+ "  ";
									value += "\n";
								}
								tb.setText(tb.getText() + value);
							}
						}

					}, "HashMap<String,String>[]");
		}

		if (modname.equals("Procedures")) {
			// Handling Counters
			List<String> param = new ArrayList<String>();
			param.add(patientID);
			// param
			Util.callModuleMethod("ProcedureModule", "getPatientProcHistory",
					param, new CustomRequestCallback() {

						@Override
						public void onError() {
							// TODO Auto-generated method stub

						}

						@Override
						public void jsonifiedData(Object data) {
							// TODO Auto-generated method stub
							HashMap<String, String>[] result = (HashMap<String, String>[]) data;
							if (result != null) {
								String value = "\n\n";
								for (int i = 0; i < result.length; i++) {
									if (result[i].get("icode") != null)
										value += result[i].get("icode") + "  ";
									value += " ";
									if (result[i].get("idesc") != null)
										value += result[i].get("idesc") + "  ";
									if (result[i].get("pdate") != null)
										value += "(" + result[i].get("pdate")
												+ ")";
									value += "\n";
								}
								tb.setText(tb.getText() + value);
							}
						}

					}, "HashMap<String,String>[]");
		}
	}

	class BillInfoWidget extends Composite {
		protected SupportModuleWidget procCodeWidget;
		protected SupportModuleWidget diagCodeWidget;

		public BillInfoWidget() {
			VerticalPanel vp = new VerticalPanel();
			// vp.setBorderWidth(1);
			initWidget(vp);
			FlexTable billTable = new FlexTable();
			Label procCode = new Label("Procedural Code:");
			procCodeWidget = new SupportModuleWidget("CptCodes");
			Label diagnosisCode = new Label("Diagnosis Code:");
			diagCodeWidget = new SupportModuleWidget("IcdCodes");
			billTable.setWidget(0, 0, procCode);
			billTable.setWidget(0, 1, procCodeWidget);
			billTable.setWidget(1, 0, diagnosisCode);
			billTable.setWidget(1, 1, diagCodeWidget);
			vp.add(billTable);
		}

		public Integer getProceduralCode() {
			return procCodeWidget.getValue();
		}

		public void setProceduralCode(Integer code) {
			procCodeWidget.setValue(code);
		}

		public Integer getDiagnosisCode() {
			return diagCodeWidget.getValue();
		}

		public void setDiagnosisCode(Integer code) {
			diagCodeWidget.setValue(code);
		}

	}

	public void loadTemplates() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			RequestBuilder builder = null;
			if (radType.getWidgetValue() == null) {
				String[] params = {};
				builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.EncounterNotesTemplate.getTemplates",
												params)));
			} else {
				String[] params = { radType.getWidgetValue() };
				builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.EncounterNotesTemplate.getTemplates",
												params)));
			}

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {
									if (r.length > 0) {
										templateTable.loadData(r);
									} else {
									}
								} else {

								}
							} catch (Exception e) {

							}

						} else {

						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}
}
