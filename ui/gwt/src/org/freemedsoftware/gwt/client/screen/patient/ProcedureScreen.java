package org.freemedsoftware.gwt.client.screen.patient;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.Popup;
import org.freemedsoftware.gwt.client.widget.PopupView;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Node;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ProcedureScreen extends PatientEntryScreenInterface {

	public final static String moduleName = "ProcedureModule";

	protected TabPanel topTabPanel, entryTabPanel;
	protected VerticalPanel newProcedureVerticalPanelMain, verticalPanel,
			viewPlanVerticalPanel, basicInfoVPanel, coverageVPanel, miscVPanel,
			eocVPanel;

	protected FlexTable basicInfoflexTable, coverageFlexTable, miscFlexTable,
			eocFlexTable, finalStepFlexTable;


	protected Label lbBasicInfo, lbCoverage, lbMisc;
	protected SupportModuleWidget providerWidget;
	private CustomDatePicker procDate;
	protected SupportModuleWidget procCodeWidget;
	protected SupportModuleWidget procModifier3Widget;
	protected SupportModuleWidget procModifier2Widget;
	protected TextBox tbUnits;
	protected SupportModuleWidget diagCode1Widget;
	private SupportModuleWidget diagCode3Widget;
	protected SupportModuleWidget diagCode4Widget;
	protected SupportModuleWidget diagCode2Widget;
	protected SupportModuleWidget procModifier1Widget;
	protected TextBox tbVoucherNo;
	protected TextArea tbComments;
	protected CustomDatePicker lastVisitDate;
	protected SupportModuleWidget refProviderWidget;
	protected SupportModuleWidget posWidget;
	protected CustomListBox listAuthorizations;
	protected CustomListBox listCertifications;
	protected CustomListBox listClaimTypes;
	protected boolean isTabView = true;
	protected TextBox tbOutsideLabCharges;
	protected TextBox tbMedOrigRef;
	protected TextBox tbMedResubCode;
	protected CheckBox cbTabView;
	protected HashMap<String, String> eocMap;
	protected CustomListBox eocList;
	protected CheckBox cbInsuranceBilable;
	protected TextBox tbCalculatedCharge;
	protected Popup calculatedCostPopup;
	protected HorizontalPanel buttonsHPanel;
	protected CustomTable procedureViewTable;
	protected Integer modRecId;
	protected CustomButton actionBtn;
	protected CustomListBox collectedPayList;
	protected CustomListBox collectedDeductList;
	protected CustomListBox collectedCopayList;

	protected Label lbPrimCoverage;

	protected CheckBox cbPrimary;

	protected Label lbSecCoverage;

	protected CheckBox cbSec;

	protected Label lbTertCoverage;

	protected CheckBox cbTert;

	protected Label lbWorkCoverage;

	protected CheckBox cbWork;

	protected Integer primaryCovId = 0;
	protected Integer secCovId = 0;
	protected Integer tertCovId = 0;
	protected Integer workCovId = 0;

	protected Integer ptid;

	public ProcedureScreen() {
		super(moduleName);
		verticalPanel = new VerticalPanel();
		viewPlanVerticalPanel = new VerticalPanel();
		newProcedureVerticalPanelMain = new VerticalPanel();

		initWidget(verticalPanel);
		topTabPanel = new TabPanel();
		topTabPanel.add(newProcedureVerticalPanelMain, "Add");
		topTabPanel.add(viewPlanVerticalPanel, "List");
		topTabPanel.selectTab(1);
		verticalPanel.add(topTabPanel);

		entryTabPanel = new TabPanel();
		basicInfoVPanel = new VerticalPanel();
		coverageVPanel = new VerticalPanel();
		miscVPanel = new VerticalPanel();
		lbBasicInfo = new Label("Service Information");
		lbBasicInfo.setStyleName("medium-header-label");
		lbCoverage = new Label("Coverage");
		lbCoverage.setStyleName("medium-header-label");
		lbMisc = new Label("Lab/Payment/Other");
		lbMisc.setStyleName("medium-header-label");
		entryTabPanel.add(basicInfoVPanel, lbBasicInfo.getText());
		entryTabPanel.add(coverageVPanel, lbCoverage.getText());
		entryTabPanel.add(miscVPanel, lbMisc.getText());
		entryTabPanel.selectTab(0);
		createProedureStep1Vpanel();
		createProedureStep2Vpanel();
		createProedureStep3Vpanel();
		cbTabView = new CheckBox("Tab View");
		cbTabView.setValue(true);
		cbTabView.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				switchView(cbTabView.getValue());
			}
		});
		newProcedureVerticalPanelMain.add(cbTabView);
		newProcedureVerticalPanelMain.add(entryTabPanel);

		actionBtn = new CustomButton("Add",AppConstants.ICON_ADD);
		actionBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (validateProcedure()) {
					if(verifyAdvancePayment()){
						createCostFlexTable();
						VerticalPanel vp = new VerticalPanel();
						Label lbHead = new Label("Calculated Cost");
						lbHead.setStyleName("label");
						vp.add(lbHead);
						vp.add(finalStepFlexTable);
						calculatedCostPopup = new Popup();
						calculatedCostPopup.setPixelSize(500, 20);
						PopupView viewInfo = new PopupView(vp);
						calculatedCostPopup.setNewWidget(viewInfo);
						calculatedCostPopup.initialize();
						showProcedureCostPopup();
					}
				}
			}
		});
		CustomButton resetBtn = new CustomButton("Reset",AppConstants.ICON_CLEAR);
		resetBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				reset();
			}
		});
		buttonsHPanel = new HorizontalPanel();
		buttonsHPanel.add(actionBtn);
		buttonsHPanel.add(resetBtn);

		newProcedureVerticalPanelMain.add(buttonsHPanel);
		createProcedureViewTable();
		viewPlanVerticalPanel.add(procedureViewTable);
		Util.setFocus(providerWidget);
	}

	public void createProedureStep1Vpanel() {
		basicInfoVPanel.setSize("100%", "100%");
		basicInfoflexTable = new FlexTable();
		int fieldCounter = 0;
		basicInfoVPanel.add(basicInfoflexTable);
		Label lbProvs = new Label("Provider");
		lbProvs.setStyleName("label");

		basicInfoflexTable.setWidget(fieldCounter, 0, lbProvs);
		fieldCounter++;

		Label lbProvider = new Label("Provider");
		providerWidget = new SupportModuleWidget("ProviderModule");
		providerWidget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbProvider);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, providerWidget);
		fieldCounter++;
		// Adding Referring Provider
		Label lbRefProvider = new Label("Referring Provider");
		refProviderWidget = new SupportModuleWidget("ProviderModule");
		refProviderWidget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbRefProvider);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, refProviderWidget);
		fieldCounter++;

		Label lbProcedure = new Label("Procedure");
		lbProcedure.setStyleName("label");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbProcedure);
		fieldCounter++;

		// Adding procedure date
		Label lbProcDate = new Label("Date of Service");
		procDate = new CustomDatePicker();
		procDate.setValue(new Date());
		procDate.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbProcDate);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, procDate);
		fieldCounter++;

		// Adding Facility
		Label lbFacility = new Label("Place of Service");
		posWidget = new SupportModuleWidget("FacilityModule");
		posWidget.setValue(CurrentState.getDefaultFacility());
		posWidget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbFacility);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, posWidget);
		fieldCounter++;

		// Adding Claim Types
		Label lbClaimType = new Label("Claim Type");
		listClaimTypes = new CustomListBox();
		listClaimTypes.addItem("NONE SELECTED");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbClaimType);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, listClaimTypes);
		fieldCounter++;

		// Adding Units
		Label lbUnits = new Label("Units");
		tbUnits = new TextBox();
		tbUnits.setText("1");
		tbUnits.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbUnits);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, tbUnits);
		fieldCounter++;

		// Adding Episode of Care
		CustomButton removeTest = new CustomButton("X");
		Label lbEoc = new Label("Episode of Care");
		eocVPanel = new VerticalPanel();
		eocFlexTable = new FlexTable();
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

		basicInfoflexTable.setWidget(fieldCounter, 0, lbEoc);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, eocVPanel);
		fieldCounter++;

		// Adding CPT Code
		Label lbCPTCodes = new Label("Procedural Code");
		procCodeWidget = new SupportModuleWidget("CptCodes");
		procCodeWidget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbCPTCodes);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, procCodeWidget);
		fieldCounter++;

		Label lbModifier1 = new Label("Modifier 1");
		procModifier1Widget = new SupportModuleWidget("CptModifiers");
		procModifier1Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbModifier1);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, procModifier1Widget);
		fieldCounter++;

		Label lbModifier2 = new Label("Modifier 2");
		procModifier2Widget = new SupportModuleWidget("CptModifiers");
		procModifier2Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbModifier2);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, procModifier2Widget);
		fieldCounter++;

		Label lbModifier3 = new Label("Modifier 3");
		procModifier3Widget = new SupportModuleWidget("CptModifiers");
		procModifier3Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbModifier3);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, procModifier3Widget);
		fieldCounter++;

		// Adding Diagnosis Codes

		Label lbDiagCode1 = new Label("Diagnosis Code 1");
		diagCode1Widget = new SupportModuleWidget("IcdCodes");
		diagCode1Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbDiagCode1);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, diagCode1Widget);
		fieldCounter++;

		Label lbDiagCode2 = new Label("Diagnosis Code 2");
		diagCode2Widget = new SupportModuleWidget("IcdCodes");
		diagCode2Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbDiagCode2);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, diagCode2Widget);
		fieldCounter++;

		Label lbDiagCode3 = new Label("Diagnosis Code 3");
		diagCode3Widget = new SupportModuleWidget("IcdCodes");
		diagCode3Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbDiagCode3);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, diagCode3Widget);
		fieldCounter++;

		Label lbDiagCode4 = new Label("Diagnosis Code 4");
		diagCode4Widget = new SupportModuleWidget("IcdCodes");
		diagCode4Widget.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbDiagCode4);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, diagCode4Widget);
		fieldCounter++;

		// Adding Voucher Number
		Label lbVoucher = new Label("Voucher Number");
		tbVoucherNo = new TextBox();
		tbVoucherNo.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbVoucher);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, tbVoucherNo);
		fieldCounter++;

		// Adding Authorization
		Label lbAuthorization = new Label("Authorization");
		listAuthorizations = new CustomListBox();
		listAuthorizations.addItem("NONE SELECTED");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbAuthorization);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, listAuthorizations);
		fieldCounter++;

		// Adding Certifications
		Label lbCertifications = new Label("Certifications");
		listCertifications = new CustomListBox();
		listCertifications.addItem("NONE SELECTED");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbCertifications);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, listCertifications);
		fieldCounter++;

		// Adding last date of visit
		Label lbLastDate = new Label("Date of Last Visit");
		lastVisitDate = new CustomDatePicker();
		lastVisitDate.setWidth("200px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbLastDate);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, lastVisitDate);
		fieldCounter++;

		// Adding Comment
		Label lbComment = new Label("Comment");
		tbComments = new TextArea();
		tbComments.setWidth("400px");
		basicInfoflexTable.setWidget(fieldCounter, 0, lbComment);
		basicInfoflexTable.getFlexCellFormatter().getElement(fieldCounter, 0)
				.getStyle().setProperty("textIndent", "10px");
		basicInfoflexTable.setWidget(fieldCounter, 1, tbComments);
		fieldCounter++;
	}

	public void createProedureStep2Vpanel() {
		coverageVPanel.setSize("100%", "100%");
		coverageFlexTable = new FlexTable();
		coverageVPanel.add(coverageFlexTable);

		int fieldCounter = 0;

		lbPrimCoverage = new Label("Primary Coverage");
		lbPrimCoverage.setVisible(false);
		cbPrimary = new CheckBox();
		cbPrimary.setVisible(false);
		coverageFlexTable.setWidget(fieldCounter, 0, lbPrimCoverage);
		coverageFlexTable.setWidget(fieldCounter, 1, cbPrimary);
		fieldCounter++;

		lbSecCoverage = new Label("Secondary Coverage");
		lbSecCoverage.setVisible(false);
		cbSec = new CheckBox();
		cbSec.setVisible(false);
		coverageFlexTable.setWidget(fieldCounter, 0, lbSecCoverage);
		coverageFlexTable.setWidget(fieldCounter, 1, cbSec);
		fieldCounter++;

		lbTertCoverage = new Label("Tertiary Coverage");
		lbTertCoverage.setVisible(false);
		cbTert = new CheckBox();
		cbTert.setVisible(false);
		coverageFlexTable.setWidget(fieldCounter, 0, lbTertCoverage);
		coverageFlexTable.setWidget(fieldCounter, 1, cbTert);
		fieldCounter++;

		lbWorkCoverage = new Label("Work Comp Coverage");
		lbWorkCoverage.setVisible(false);
		cbWork = new CheckBox();
		cbWork.setVisible(false);
		coverageFlexTable.setWidget(fieldCounter, 0, lbWorkCoverage);
		coverageFlexTable.setWidget(fieldCounter, 1, cbWork);
		fieldCounter++;
	}

	public void createProedureStep3Vpanel() {
		miscVPanel.setSize("100%", "100%");
		miscFlexTable = new FlexTable();
		miscVPanel.add(miscFlexTable);

		int fieldCounter = 0;

		// Adding Outside Lab Charges
		Label lbOutsideLabCharges = new Label("Outside Lab Charges");
		tbOutsideLabCharges = new TextBox();
		miscFlexTable.setWidget(fieldCounter, 0, lbOutsideLabCharges);
		miscFlexTable.setWidget(fieldCounter, 1, tbOutsideLabCharges);
		fieldCounter++;

		// Adding Medicaid Original Reference
		Label lbMedOrigRef = new Label("Medicaid Original Reference");
		tbMedOrigRef = new TextBox();
		miscFlexTable.setWidget(fieldCounter, 0, lbMedOrigRef);
		miscFlexTable.setWidget(fieldCounter, 1, tbMedOrigRef);
		fieldCounter++;

		// Adding Medicaid Resubmission Code
		Label lbMedResubCode = new Label("Medicaid Resubmission Code");
		tbMedResubCode = new TextBox();
		miscFlexTable.setWidget(fieldCounter, 0, lbMedResubCode);
		miscFlexTable.setWidget(fieldCounter, 1, tbMedResubCode);
		fieldCounter++;

		Label lbAttColPay = new Label("Attach Collected Payment");
		collectedPayList = new CustomListBox();
		collectedPayList.addItem("NONE SELECTED");
		miscFlexTable.setWidget(fieldCounter, 0, lbAttColPay);
		miscFlexTable.setWidget(fieldCounter, 1, collectedPayList);
		fieldCounter++;

		Label lbAttColCopay = new Label("Attach Collected Copay");
		collectedCopayList = new CustomListBox();
		collectedCopayList.addItem("NONE SELECTED");
		miscFlexTable.setWidget(fieldCounter, 0, lbAttColCopay);
		miscFlexTable.setWidget(fieldCounter, 1, collectedCopayList);
		fieldCounter++;

		Label lbAttColDeduct = new Label("Attach Collected Deductible");
		collectedDeductList = new CustomListBox();
		collectedDeductList.addItem("NONE SELECTED");
		miscFlexTable.setWidget(fieldCounter, 0, lbAttColDeduct);
		miscFlexTable.setWidget(fieldCounter, 1, collectedDeductList);
		fieldCounter++;
	}

	public void switchView(boolean isTabView) {
		if (isTabView) {
			entryTabPanel.setVisible(true);
			entryTabPanel.add(basicInfoVPanel, "Basic Information");
			entryTabPanel.add(coverageVPanel, "Coverage");
			entryTabPanel.add(miscVPanel, "Miscellaneous");

			lbBasicInfo.setVisible(false);
			lbCoverage.setVisible(false);
			lbMisc.setVisible(false);

			entryTabPanel.selectTab(0);

		} else {
			entryTabPanel.setVisible(false);
			newProcedureVerticalPanelMain.setVisible(true);
			lbBasicInfo.setVisible(true);
			lbCoverage.setVisible(true);
			lbMisc.setVisible(true);
			newProcedureVerticalPanelMain.add(lbBasicInfo);
			newProcedureVerticalPanelMain.add(basicInfoVPanel);
			newProcedureVerticalPanelMain.add(lbCoverage);
			newProcedureVerticalPanelMain.add(coverageVPanel);
			newProcedureVerticalPanelMain.add(lbMisc);
			newProcedureVerticalPanelMain.add(miscVPanel);
			newProcedureVerticalPanelMain.add(buttonsHPanel);
		}
	}

	public void loadAuthorizations() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString(),
					procDate.getTextBox().getText() };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Authorizations.getValidAuthorizations",
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
								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (result != null) {
									if (result.length != 0) {
										for (int i = 0; i < result.length; i++) {
											HashMap<String, String> m = (HashMap<String, String>) result[i];
											listAuthorizations.addItem(m
													.get("auth_info"), m
													.get("Id"));
										}
									} else {

									}
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

	public void loadCertifications() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString() };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Certifications.getCertifications",
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
								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (result != null) {
									if (result.length != 0) {
										for (int i = 0; i < result.length; i++) {
											HashMap<String, String> m = (HashMap<String, String>) result[i];
											listCertifications.addItem(m
													.get("cert_desc"), m
													.get("Id"));
										}
									} else {
									}
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

	public void loadClaimTypes() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = {};

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ClaimTypes.getClaimTypes",
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
								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (result != null) {
									if (result.length != 0) {
										for (int i = 0; i < result.length; i++) {
											HashMap<String, String> m = (HashMap<String, String>) result[i];
											listClaimTypes.addItem(m
													.get("claim_info"), m
													.get("Id"));
											if (m.get("claim_info")
													.equalsIgnoreCase("11(O)")) {
												listClaimTypes
														.setSelectedIndex(i + 1);
											}
										}
									} else {
									}
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

	public void loadCoverage(final int type) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString(),
					type + "" };

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
									// Window.alert("Response is:"+type+"
									// :"+response.getText());
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											for (int i = 0; i < 1; i++) {
												HashMap<String, String> m = (HashMap<String, String>) result[i];
												if (type == 1) {
													lbPrimCoverage
															.setVisible(true);
													cbPrimary.setVisible(true);
													cbPrimary.setText(m
															.get("comp_name"));
													primaryCovId = Integer
															.parseInt(m
																	.get("Id"));
												} else if (type == 2) {
													lbSecCoverage
															.setVisible(true);
													cbSec.setVisible(true);
													cbSec.setText(m
															.get("comp_name"));
													secCovId = Integer
															.parseInt(m
																	.get("Id"));
												} else if (type == 3) {
													lbTertCoverage
															.setVisible(true);
													cbTert.setVisible(true);
													cbTert.setText(m
															.get("comp_name"));
													tertCovId = Integer
															.parseInt(m
																	.get("Id"));
												} else if (type == 4) {
													lbWorkCoverage
															.setVisible(true);
													cbWork.setVisible(true);
													cbWork.setText(m
															.get("comp_name"));
													workCovId = Integer
															.parseInt(m
																	.get("Id"));
												}
											}
										} else {

										}
									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
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

	public void loadEOC() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString() };

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
									// Window.alert("Response is:"+type+"
									// :"+response.getText());
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
									// Window.alert(e.getMessage());
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

	public void createCostFlexTable() {
		finalStepFlexTable = new FlexTable();
		finalStepFlexTable.setSize("100%", "100%");
		Label lbProceduralCode = new Label("Procedural Code");
		Label proceduralCodeVal = new Label(procCodeWidget.getText());
		finalStepFlexTable.setWidget(0, 0, lbProceduralCode);
		finalStepFlexTable.setWidget(0, 1, proceduralCodeVal);

		Label lbUnits = new Label("Units");
		Label unitsVal = new Label(tbUnits.getText());
		finalStepFlexTable.setWidget(1, 0, lbUnits);
		finalStepFlexTable.setWidget(1, 1, unitsVal);

		Label lbCalCharge = new Label("Calculated Charge");
		tbCalculatedCharge = new TextBox();
		finalStepFlexTable.setWidget(2, 0, lbCalCharge);
		finalStepFlexTable.setWidget(2, 1, tbCalculatedCharge);

		Label lbInsuranceBillable = new Label("Insurance Billable?");
		cbInsuranceBilable = new CheckBox();
		cbInsuranceBilable.setValue(true);
		finalStepFlexTable.setWidget(3, 0, lbInsuranceBillable);
		finalStepFlexTable.setWidget(3, 1, cbInsuranceBilable);

		CustomButton addProcedure = new CustomButton("Finish",AppConstants.ICON_ADD);
		addProcedure.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				calculatedCostPopup.hide();
				saveProcedure();
			}

		});
		finalStepFlexTable.setWidget(5, 0, addProcedure);
	}

	private void showProcedureCostPopup() {
		// return new FlexTable();
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String selCov = "0";
			if (cbWork.getValue()) {
				selCov = "" + workCovId;
			}
			if (cbTert.getValue()) {
				selCov = "" + tertCovId;
			}
			if (cbSec.getValue()) {
				selCov = "" + secCovId;
			}
			if (cbPrimary.getValue()) {
				selCov = "" + primaryCovId;
			}
			String selUnits = JsonUtil.jsonify(tbUnits.getText());
			String selCode = JsonUtil.jsonify(procCodeWidget.getStoredValue());
			String selPro = JsonUtil.jsonify(providerWidget.getStoredValue());
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String ptid = JsonUtil.jsonify(patient
					.toString());
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
								tbCalculatedCharge.setText(result.toString());
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

	public boolean verifyAdvancePayment() {
		if (collectedPayList.getItemCount() > 1
				|| collectedCopayList.getItemCount() > 1
				|| collectedDeductList.getItemCount() > 1) {
			if(Window
					.confirm("There is payment in the system, do you want to apply this payment now?")){
				entryTabPanel.selectTab(2);
				return false;
			}
			else{
				return true;
			}

		}
		return true;
	}

	private boolean validateProcedure() {
		String msg = "";
		if (providerWidget.getStoredValue() == null
				|| providerWidget.getStoredValue().equals("0")
				|| providerWidget.getText().trim().equals(""))
			msg += "Please Specify the Provider.\n";
		if (procDate.getTextBox().getText() == null
				|| procDate.getTextBox().getText().equals(""))
			msg += "Please Specify the Procedure Date.\n";
		if (diagCode1Widget.getStoredValue() == null
				|| diagCode1Widget.getStoredValue().equals("0")
				|| diagCode1Widget.getText().trim().equals(""))
			msg += "Please Specify diagnosis code 1.\n";
		if (posWidget.getStoredValue() == null
				|| posWidget.getStoredValue().equals("0")
				|| posWidget.getText().trim().equals(""))
			msg += "Please Specify the Place of Service.\n";
		if (listClaimTypes.getSelectedIndex() == 0)
			msg += "Please Specify the Type of Claim.\n";
		if (procCodeWidget.getStoredValue() == null
				|| procCodeWidget.getStoredValue().equals("0")
				|| procCodeWidget.getText().trim().equals(""))
			msg += "Please Specify the Procedural code.\n";
		if (tbUnits.getText() == null || procCodeWidget.getText().equals(""))
			msg += "Please Specify the Units.\n";
		if (tbUnits.getText() == null || procCodeWidget.getText().equals(""))
			msg += "Please Specify the Units.\n";
		else if (!Util.isNumber(tbUnits.getText())) {
			msg += "The specified valued for Units is not correct Number \n";
		}
		if (!msg.equals("")) {
			Window.alert(msg);
			return false;
		}
		return true;
	}

	private void saveProcedure() {
		JsonUtil.debug("before saving");
		String[] params = { JsonUtil.jsonify(prepareDataForProcedure()) };
		RequestBuilder builder = null;
		if (modRecId == 0)
			builder = new RequestBuilder(RequestBuilder.POST, URL.encode(Util
					.getJsonRequest(
							"org.freemedsoftware.module.ProcedureModule.add",
							params)));
		else
			builder = new RequestBuilder(RequestBuilder.POST, URL.encode(Util
					.getJsonRequest(
							"org.freemedsoftware.module.ProcedureModule.mod",
							params)));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
				}

				public void onResponseReceived(Request request,
						Response response) {

					if (200 == response.getStatusCode()) {
						Boolean check = false;
						Integer recid = 0;
						if (modRecId == 0) {
							Integer result = (Integer) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Integer");
							if (result != null && result > 0) {
								check = true;
								recid = result;
							}
						} else {
							Boolean result = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Boolean");
							if (result != null && result) {
								check = true;
							}
						}
						if (check) {
							if (collectedPayList.getSelectedIndex() != 0) {
								if (modRecId == 0)
									attachProcId(collectedPayList
											.getStoredValue(), recid.toString());
								else
									attachProcId(collectedPayList
											.getStoredValue(), modRecId
											.toString());
							}
							if (collectedCopayList.getSelectedIndex() != 0) {
								if (modRecId == 0)
									attachProcId(collectedCopayList
											.getStoredValue(), recid.toString());
								else
									attachProcId(collectedCopayList
											.getStoredValue(), modRecId
											.toString());
							}
							if (collectedDeductList.getSelectedIndex() != 0) {
								if (modRecId == 0)
									attachProcId(collectedDeductList
											.getStoredValue(), recid.toString());
								else
									attachProcId(collectedDeductList
											.getStoredValue(), modRecId
											.toString());
							}
							if (modRecId == 0)
								Util.showInfoMsg("ProcedureModule",
										"New Procedure Created.");
							else
								Util.showInfoMsg("ProcedureModule",
										"Procedure Modified.");
							modRecId = 0;
							topTabPanel.selectTab(1);
							reset();
							loadProcedureTableData();

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

	private HashMap<String, String> prepareDataForProcedure() {
		HashMap<String, String> map = new HashMap<String, String>();
		if (modRecId != 0)
			map.put((String) "id", modRecId.toString());
		map.put((String) "procpatient", patientScreen.getPatient().toString());
		map.put((String) "procphysician", providerWidget.getStoredValue());
		if (refProviderWidget.getStoredValue() != null
				|| !refProviderWidget.getStoredValue().equals(""))
			map.put((String) "procrefdoc", refProviderWidget.getStoredValue());
		if (procModifier1Widget.getStoredValue() != null
				|| !procModifier1Widget.getStoredValue().equals(""))
			map
					.put((String) "proccptmod", procModifier1Widget
							.getStoredValue());
		if (procModifier2Widget.getStoredValue() != null
				|| !procModifier2Widget.getStoredValue().equals(""))
			map.put((String) "proccptmod2", procModifier2Widget
					.getStoredValue());
		if (procModifier3Widget.getStoredValue() != null
				|| !procModifier3Widget.getStoredValue().equals(""))
			map.put((String) "proccptmod3", procModifier3Widget
					.getStoredValue());
		if (diagCode1Widget.getStoredValue() != null
				|| !diagCode1Widget.getStoredValue().equals(""))
			map.put((String) "procdiag1", diagCode1Widget.getStoredValue());
		if (diagCode2Widget.getStoredValue() != null
				|| !diagCode2Widget.getStoredValue().equals(""))
			map.put((String) "procdiag2", diagCode2Widget.getStoredValue());
		if (diagCode3Widget.getStoredValue() != null
				|| !diagCode3Widget.getStoredValue().equals(""))
			map.put((String) "procdiag3", diagCode3Widget.getStoredValue());
		if (diagCode4Widget.getStoredValue() != null
				|| !diagCode4Widget.getStoredValue().equals(""))
			map.put((String) "procdiag4", diagCode4Widget.getStoredValue());
		if (procDate.getTextBox().getText() != null
				|| !procDate.getTextBox().getText().equals(""))
			map.put((String) "procdt", procDate.getTextBox().getText());
		if (procCodeWidget.getStoredValue() != null
				|| !procCodeWidget.getStoredValue().equals(""))
			map.put((String) "proccpt", procCodeWidget.getStoredValue());
		if (tbUnits.getText() != null || !tbUnits.getText().equals(""))
			map.put((String) "procunits", tbUnits.getText());
		if (posWidget.getStoredValue() != null
				|| !posWidget.getStoredValue().equals(""))
			map.put((String) "procpos", posWidget.getStoredValue());
		if (tbVoucherNo.getText() != null || !tbVoucherNo.getText().equals(""))
			map.put((String) "procvoucher", tbVoucherNo.getText());
		if (listAuthorizations.getSelectedIndex() != 0)
			map.put((String) "procauth", listAuthorizations
					.getValue(listAuthorizations.getSelectedIndex()));
		if (listCertifications.getSelectedIndex() != 0)
			map.put((String) "proccert", listCertifications
					.getValue(listCertifications.getSelectedIndex()));
		if (listClaimTypes.getSelectedIndex() != 0)
			map.put((String) "procclmtp", listClaimTypes
					.getValue(listClaimTypes.getSelectedIndex()));
		if (lastVisitDate.getTextBox().getText() != null
				|| !lastVisitDate.getTextBox().getText().equals(""))
			map.put((String) "procrefdt", lastVisitDate.getTextBox().getText());
		if (tbComments.getText() != null || !tbComments.getText().equals(""))
			map.put((String) "proccomment", tbComments.getText());
		String coverageId = "";
		String coverageType = "";
		if (cbWork.getValue()) {
			coverageType = "4";
			map.put((String) "proccov4", "" + workCovId);
			coverageId = "" + workCovId;
		}
		if (cbTert.getValue()) {
			coverageType = "3";
			map.put((String) "proccov3", "" + tertCovId);
			coverageId = "" + tertCovId;
		}
		if (cbSec.getValue()) {
			coverageType = "2";
			map.put((String) "proccov2", "" + secCovId);
			coverageId = "" + secCovId;
		}
		if (cbPrimary.getValue()) {
			coverageType = "1";
			map.put((String) "proccov1", "" + primaryCovId);
			coverageId = "" + primaryCovId;
		}
		if (!coverageId.equals(""))
			map.put((String) "proccurcovid", coverageId);
		if (!coverageType.equals(""))
			map.put((String) "proccurcovtp", coverageType);
		if (cbInsuranceBilable.getValue()) {
			map.put((String) "procbillable", "1");
		} else {
			map.put((String) "procbillable", "0");
		}
		if (tbCalculatedCharge.getText() != null
				|| !tbCalculatedCharge.getText().equals("")) {
			map.put((String) "procbalorig", tbCalculatedCharge.getText());
			map.put((String) "procbalcurrent", tbCalculatedCharge.getText());
		}
		if (tbOutsideLabCharges.getText() != null
				|| !tbOutsideLabCharges.getText().equals(""))
			map.put((String) "proclabcharges", tbOutsideLabCharges.getText());
		if (tbMedOrigRef.getText() != null
				|| !tbMedOrigRef.getText().equals(""))
			map.put((String) "procmedicaidref", tbMedOrigRef.getText());
		if (tbMedResubCode.getText() != null
				|| !tbMedResubCode.getText().equals(""))
			map.put((String) "procmedicaidresub", tbMedResubCode.getText());
		String eocVal = "";
		for (int i = 0; i < eocFlexTable.getRowCount(); i++) {
			try {
				CustomListBox clb = (CustomListBox) eocFlexTable
						.getWidget(i, 0);
				if (i == eocFlexTable.getRowCount() - 1) {
					if (!clb.getStoredValue().equals("")
							&& !clb.getStoredValue().equals("0")
							&& clb.getSelectedIndex() != 0)
						eocVal = eocVal + clb.getStoredValue();
				} else {
					if (!clb.getStoredValue().equals("")
							&& !clb.getStoredValue().equals("0")
							&& clb.getSelectedIndex() != 0)
						eocVal = eocVal + clb.getStoredValue() + ",";
				}
			} catch (Exception e) {

			}
		}
		if (!eocVal.equals("")) {
			map.put((String) "proceoc", eocVal);
		}
		return map;
	}

	public void createProcedureViewTable() {
		procedureViewTable = new CustomTable();
		procedureViewTable.setIndexName("Id");
		procedureViewTable.setSize("100%", "100%");
		procedureViewTable.addColumn("Procedure Date", "proc_date");
		procedureViewTable.addColumn("Procedure Code", "proc_code");
		procedureViewTable.addColumn("Modifier", "proc_mod");
		procedureViewTable.addColumn("Comments", "comment");
		procedureViewTable.addColumn("Action", "action");
		modRecId = 0;
		procedureViewTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				actionBar.applyPermissions(canRead, false, false, canModify, false);
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						if(action == HandleCustomAction.MODIFY){
							try {
								reset();
								actionBtn.setText("Modify");
								modRecId = id;
								getProcDetails();
							} catch (Exception e) {
								GWT.log("Caught exception: ", e);
							}
						}else if(action == HandleCustomAction.PRINT){
							List<String> params = new ArrayList<String>();
							params.add(id+"");
							String reportName = "Patient Receipt";
							Util.generateReportToBrowser(reportName, "pdf", params);
						}else if(action == HandleCustomAction.VIEW){
							List<String> params = new ArrayList<String>();
							params.add(id+"");
							String reportName = "Patient Receipt";
							Util.generateReportToBrowser(reportName, "html", params);
						}
					}
				});
				
				// Push value back to table
				return actionBar;
			}
		});
		
	}

	public void getProcDetails() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {

			String[] params = { modRecId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.getProcByID",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								try {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>");
									topTabPanel.selectTab(0);
									fillFields(result);
								} catch (Exception e) {

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
				procedureViewTable.setVisible(true);
			}
		} else {
		}
	}

	public void loadProcedureTableData() {
		procedureViewTable.clearData();
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.getProcedureInfo",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {

								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								procedureViewTable.loadData(result);
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
				procedureViewTable.setVisible(true);
			}
		} else {
		}

	}

	public void getPreviousProcData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.getLastProc",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								try {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>");
									fillFields(result);
								} catch (Exception e) {

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
				procedureViewTable.setVisible(true);
			}
		} else {
		}
	}

	public void attachProcId(String payid, String procid) {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { payid, procid };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PaymentModule.attachProcedure",
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
									Boolean result = (Boolean) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Boolean");
									if (result) {
										Util
												.showInfoMsg("PaymentModule",
														"Advance Payment Successfully Attached.");
									} else {
										Util
												.showErrorMsg("PaymentModule",
														"Advance Payment Failed to Attach.");
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

	public void loadUnAttachedPay(final String funcName, final CustomListBox lb) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			Integer patient=0;
			if(ptid>1){
				patient=ptid;
			}
			else{
				patient=patientScreen.getPatient();
			}
			String[] params = { patient.toString() };

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.PaymentModule."
									+ funcName, params)));
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
									// Window.alert("Response is:"+type+"
									// :"+response.getText());
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											for (int i = 0; i < result.length; i++)
												lb.addItem(result[i]
														.get("pay_info"),
														result[i].get("Id"));
										} else {

										}
									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
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

	public void fillFields(HashMap<String, String> map) {
		try {
			if (map.get("procphysician") != null)
				providerWidget.setValue(new Integer(map.get("procphysician")));
			if (map.get("procphysician") != null)
				refProviderWidget.setValue(new Integer(map.get("procrefdoc")));
			if (map.get("proccptmod") != null)
				procModifier1Widget
						.setValue(new Integer(map.get("proccptmod")));
			if (map.get("proccptmod2") != null)
				procModifier2Widget
						.setValue(new Integer(map.get("proccptmod2")));
			if (map.get("proccptmod3") != null)
				procModifier3Widget
						.setValue(new Integer(map.get("proccptmod3")));

			if (map.get("procdiag1") != null)
				diagCode1Widget.setValue(new Integer(map.get("procdiag1")));
			if (map.get("procdiag2") != null)
				diagCode2Widget.setValue(new Integer(map.get("procdiag2")));
			if (map.get("procdiag3") != null)
				diagCode3Widget.setValue(new Integer(map.get("procdiag3")));
			if (map.get("procdiag4") != null)
				diagCode4Widget.setValue(new Integer(map.get("procdiag4")));

			procDate.setValue(Util.getSQLDate(new Date()));
			if (map.get("proccpt") != null)
				procCodeWidget.setValue(new Integer(map.get("proccpt")));
			if (map.get("procunits") != null)
				tbUnits.setText(map.get("procunits"));
			if (map.get("procpos") != null)
				posWidget.setValue(new Integer(map.get("procpos")));
			if (map.get("procvoucher") != null)
				tbVoucherNo.setText(map.get("procvoucher"));
			if (map.get("procauth") != null)
				listAuthorizations.setWidgetValue(map.get("procauth"));
			if (map.get("proccert") != null)
				listCertifications.setWidgetValue(map.get("proccert"));
			if (map.get("procclmtp") != null)
				listClaimTypes.setWidgetValue(map.get("procclmtp"));
			if (map.get("procrefdt") != null)
				lastVisitDate.setValue(map.get("procrefdt"));
			if (map.get("proccomment") != null)
				tbComments.setText(map.get("proccomment"));
			if (map.get("proccov4") != null && !map.get("proccov4").equals("0"))
				cbWork.setValue(true);
			if (map.get("proccov3") != null && !map.get("proccov3").equals("0"))
				cbTert.setValue(true);
			if (map.get("proccov2") != null && !map.get("proccov2").equals("0"))
				cbSec.setValue(true);
			if (map.get("proccov1") != null && !map.get("proccov1").equals("0"))
				cbPrimary.setValue(true);
			if (map.get("proclabcharges") != null)
				tbOutsideLabCharges.setText(map.get("proclabcharges"));
			if (map.get("procmedicaidref") != null)
				tbMedOrigRef.setText(map.get("procmedicaidref"));
			if (map.get("procmedicaidresub") != null)
				tbMedResubCode.setText(map.get("procmedicaidresub"));
			if (map.get("procdt") != null) {
				lastVisitDate.setValue(map.get("procdt"));
			}
			if (map.get("proceoc") != null
					&& !map.get("proceoc").trim().equals("")) {
				String eocVal = map.get("proceoc");
				String[] eocVals = eocVal.split(",");
				for (int i = 0; i < eocVals.length; i++) {
					eocFlexTable.clear();
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
					eoc.setWidgetValue(eocVals[i]);
					eocFlexTable.setWidget(rc, 0, eoc);
					eocFlexTable.setWidget(rc, 1, remove);
				}
			}
		} catch (Exception e) {
		}

	}

	public void loadData() {
		loadProcedureTableData();
		if(modRecId >0){
			//reset();
			actionBtn.setText("Modify");
			getProcDetails();
		}
		else
			getPreviousProcData();
		loadAuthorizations();
		loadCertifications();
		loadClaimTypes();
		loadCoverage(1);
		loadCoverage(2);
		loadCoverage(3);
		loadCoverage(4);
		loadUnAttachedPay("getUnAttachedPayments", collectedPayList);
		loadUnAttachedPay("getUnAttachedCopays", collectedCopayList);
		loadUnAttachedPay("getUnAttachedDeductables", collectedDeductList);
	}

	public void reset() {
		actionBtn.setText("Add");
		modRecId = 0;
		providerWidget.clear();
		refProviderWidget.clear();
		procModifier1Widget.clear();
		procModifier2Widget.clear();
		procModifier3Widget.clear();
		diagCode1Widget.clear();
		diagCode2Widget.clear();
		diagCode3Widget.clear();
		diagCode4Widget.clear();
		procDate.setValue(Util.getSQLDate(new Date()));
		procCodeWidget.clear();
		eocFlexTable.clear();
		//eocList.setSelectedIndex(0);
		//eocFlexTable.setWidget(0, 0, eocList);
		tbUnits.setText("1.0");
		posWidget.setValue(CurrentState.getDefaultFacility());
		tbVoucherNo.setText("");
		listAuthorizations.setSelectedIndex(0);
		listCertifications.setSelectedIndex(0);
		//listClaimTypes.setSelectedIndex(0);
		lastVisitDate.getTextBox().setText("");
		tbComments.setText("");
		cbPrimary.setValue(false);
		cbSec.setValue(false);
		cbTert.setValue(false);
		cbWork.setValue(false);
		tbOutsideLabCharges.setText("");
		tbMedOrigRef.setText("");
		tbMedResubCode.setText("");
	}
	
	public void setModificationRecordId(Integer modid){
		modRecId=modid;
	}
	
	public void setPatientId(Integer pid){
		ptid = pid;
	}
}
