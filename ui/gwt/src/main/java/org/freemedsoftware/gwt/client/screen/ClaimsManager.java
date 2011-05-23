/*
 * $Id: ClaimsManager.java 4643 2009-10-21 11:50:05Z Fawad $
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

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.AgingSummaryWidget;
import org.freemedsoftware.gwt.client.widget.BlockScreenWidget;
import org.freemedsoftware.gwt.client.widget.ClaimDetailsWidget;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.LedgerWidget;
import org.freemedsoftware.gwt.client.widget.PatientTagWidget;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.PopupView;
import org.freemedsoftware.gwt.client.widget.PostCheckWidget;
import org.freemedsoftware.gwt.client.widget.RemittBillingWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.LedgerWidget.PayCategory;
import org.freemedsoftware.gwt.client.widget.RemittBillingWidget.BillingType;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.dom.client.Node;
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
import com.google.gwt.i18n.client.DateTimeFormat;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.FlowPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.RadioButton;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.ui.FlexTable.FlexCellFormatter;
import com.google.gwt.user.datepicker.client.DateBox;
import com.google.gwt.user.datepicker.client.DateBox.DefaultFormat;

public class ClaimsManager extends ScreenInterface {

	public final static String moduleName = "ClaimLogTable";

	protected TabPanel tabPanel;
	protected CustomTable claimsManagerTable;
	protected DialogBox ledgerPopup;
	protected ListBox actionsList;
	// Declering Labels for calaimManager
	Label lblAging;
	Label lblProvider;
	Label lblPayerCriteria;
	Label lblPayer;
	Label lblPlanName;
	Label lblPatientCriteria;
	Label lblName;
	Label lblClaimCriteria;
	Label lblBillingStatus;
	Label lblDateOfService;

	// Declreaing TexBoxes for last and firt Name in the Claim Manager.
	TextBox txtLastName;
	TextBox txtFirstName;
	// DatePicker for date of service
	DateBox dateBox;// = new DateBox();
	FlexTable parentSearchTable, searchCriteriaTable, procDetailFlexTable;
	// Declreaing CustomButton
	CustomButton btnSearchClaim;
	CustomButton btnClear;
	CustomButton btnAgingSummary;
	// Declreaing all list box which will be popolate in JSONRPC Mode
	SupportModuleWidget provWidget;
	CustomModuleWidget payerWidget;
	CustomModuleWidget planWidget;
	// Declaring Ration CustomButton for age;
	RadioButton rb120Plus, rb91To120, rb61To90, rb31To60, rb0To30, rbNoSearch;
	RadioButton rbQueued, rbBilled;
	String currentProcId;
	String currentPatientName;
	String currentPatientId;
	String procCovSrc;

	private static List<ClaimsManager> claimsManagerScreenList = null;
	protected VerticalPanel popupVPanel, searchCriteriaVPanel,
			currentCriteriaPanel;
	protected FlexTable ledgerStep1FlexTable;
	protected HorizontalPanel ledgerStep1HPanel;
	protected HorizontalPanel procDetailsHPanel;
	protected PatientWidget patientWidget;
	protected Label lbPatientWidget;
	protected FlexTable existingCriteriaTable;
	protected HorizontalPanel statusHp;
	protected HorizontalPanel panelAging;

	protected HashSet<String> selectedProcs, selectedBillKeys;

	protected CheckBox cbShowZeroBalance;
	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well

	protected Label lblFacility;

	protected SupportModuleWidget facilityWidget;

	protected List<CheckBox> checkBoxesList;

	protected CheckBox cbWholeWeek;

	protected HTML viewLedgerDetails;

	protected VerticalPanel verticalPanel;

	protected PatientTagWidget tagWidget;

	protected Label lbTagSearch;

	public static ClaimsManager getInstance() {
		ClaimsManager claimsManagerScreen = null;

		if (claimsManagerScreenList == null)
			claimsManagerScreenList = new ArrayList<ClaimsManager>();
		if (claimsManagerScreenList.size() < AppConstants.MAX_CLAIMSMANAGER_TABS)
			claimsManagerScreenList
					.add(claimsManagerScreen = new ClaimsManager());
		else
			claimsManagerScreen = claimsManagerScreenList
					.get(AppConstants.MAX_CLAIMSMANAGER_TABS - 1);
		return claimsManagerScreen;
	}

	public static boolean removeInstance(ClaimsManager claimsManagerScreen) {
		return claimsManagerScreenList.remove(claimsManagerScreen);
	}

	public ClaimsManager() {
		super(moduleName);
		// Intializing all labels.
		lblAging = new Label("Aging");
		lblFacility = new Label("Facility");
		lblProvider = new Label("Provider");
		lblPayer = new Label("Payer");
		lblPlanName = new Label("Plan Name");
		lblName = new Label("Name (Last, First)");
		lblBillingStatus = new Label("Billing Status");
		lblDateOfService = new Label("Date of Service");
		lbPatientWidget = new Label("Patient Full Name");
		lbTagSearch = new Label("Tag Search: ");
		// TextBoxs for FirsName and LastName
		txtFirstName = new TextBox();
		txtFirstName.setWidth("200px");
		txtLastName = new TextBox();
		txtLastName.setWidth("200px");
		facilityWidget = new SupportModuleWidget("FacilityModule");

		patientWidget = new PatientWidget();
		// date for service's date and its simple format i;e without time.
		dateBox = new DateBox();
		dateBox
				.setFormat(new DefaultFormat(DateTimeFormat
						.getShortDateFormat()));
		cbShowZeroBalance = new CheckBox("Include Zero Balances");
		cbWholeWeek = new CheckBox("Select Week");
		// Buttons for
		btnSearchClaim = new CustomButton("Search Claim",AppConstants.ICON_SEARCH);
		popupVPanel = new VerticalPanel();
		popupVPanel.setSize("100%", "100%");
		popupVPanel.setSpacing(5);
		ledgerStep1HPanel = new HorizontalPanel();
		ledgerStep1HPanel.setSpacing(10);
		Label actionType = new Label("Action");
		// actionType.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		ledgerStep1HPanel.add(actionType);
		// ledgerStep1HPanel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		actionsList = new ListBox();
		actionsList.addItem("NONE SELECTED");
		actionsList.addItem("Rebill");
		actionsList.addItem("Payment");
		// actionsList.addItem("Copay");
		actionsList.addItem("Adjustment");
		// actionsList.addItem("Deductable");
		actionsList.addItem("Withhold");
		actionsList.addItem("Transfer");
		actionsList.addItem("Allowed Amount");
		actionsList.addItem("Denial");
		actionsList.addItem("Writeoff");
		actionsList.addItem("Refund");
		// actionsList.addItem("Mistake");
		actionsList.addItem("Ledger");
		ledgerStep1HPanel.add(actionsList);

		CustomButton selectLineItemBtn = new CustomButton("Proceed",AppConstants.ICON_NEXT);
		ledgerStep1HPanel.add(selectLineItemBtn);
		selectLineItemBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (actionsList.getSelectedIndex() != 0) {
					LedgerWidget pw = null;
					CustomRequestCallback cb = new CustomRequestCallback() {
						@Override
						public void onError() {

						}

						@Override
						public void jsonifiedData(Object data) {
							tabPanel.selectTab(0);
							if (data.toString().equals("update")) {
								ledgerPopup.clear();
								ledgerPopup.hide();
								refreshSearch();
								openPopup();
							} else if (data.toString().equals("close")) {
								refreshSearch();
							}
						}
					};
					boolean hasUI = true;
					if (actionsList.getSelectedIndex() == 1) {
						hasUI = false;
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.REBILLED, cb);
					} else if (actionsList.getSelectedIndex() == 2) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.PAYMENT, cb);
					} else if (actionsList.getSelectedIndex() == 3) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.ADJUSTMENT, cb);
					} else if (actionsList.getSelectedIndex() == 4) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.WITHHOLD, cb);
					} else if (actionsList.getSelectedIndex() == 5) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.TRANSFER, cb);
					} else if (actionsList.getSelectedIndex() == 6) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.ALLOWEDAMOUNT, cb);
					} else if (actionsList.getSelectedIndex() == 7) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.DENIAL, cb);
					} else if (actionsList.getSelectedIndex() == 8) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.WRITEOFF, cb);
					} else if (actionsList.getSelectedIndex() == 9) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.REFUND, cb);
					} 
//					else if (actionsList.getSelectedIndex() == 12) {
//						hasUI = false;
//						pw = new LedgerWidget(currentProcId, currentPatientId,
//								procCovSrc, PayCategory.MISTAKE, cb);
//					} 
					else if (actionsList.getSelectedIndex() == 10) {
						pw = new LedgerWidget(currentProcId, currentPatientId,
								procCovSrc, PayCategory.LEDGER, cb);
					}

					if (pw != null) {
						if (hasUI) {
							ledgerPopup.clear();
							ledgerPopup.hide();
							tabPanel.add(pw, currentPatientName);
							tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
						}
					}
				} else {
					Window.alert("Please select the action type");
				}
			}

		});

		actionsList.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {

			}

		});

		btnSearchClaim.addClickHandler(new ClickHandler() {

			public void onClick(ClickEvent event) {
				if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					refreshSearch();
				} else {
					Window.alert("You are on STUB Mod !");
				}
			}

		});
		btnClear = new CustomButton("Clear",AppConstants.ICON_CLEAR);
		btnClear.addClickHandler(new ClickHandler() {

			public void onClick(ClickEvent event) {
				clearSearch();
			}

		});

		btnAgingSummary = new CustomButton("Aging Summary",AppConstants.ICON_VIEW);
		btnAgingSummary.addClickHandler(new ClickHandler() {

			public void onClick(ClickEvent event) {
				CustomRequestCallback cb = new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@Override
					public void jsonifiedData(Object data) {
						tabPanel.selectTab(0);
						if (data instanceof HashMap) {
							HashMap<String, String> map = (HashMap<String, String>) data;
							if (map.get("payer") != null) {
								payerWidget.setValue(Integer.parseInt(map
										.get("payer")));
								payerWidget.setText(map.get("payer_name"));
							}
							if (map.get("aging") != null) {
								if (map.get("aging").equals("0-30")) {
									rb0To30.setValue(true);
								} else if (map.get("aging").equals("31-60")) {
									rb31To60.setValue(true);
								} else if (map.get("aging").equals("61-90")) {
									rb61To90.setValue(true);
								} else if (map.get("aging").equals("91-120")) {
									rb91To120.setValue(true);
								} else if (map.get("aging").equals("120+")) {
									rb120Plus.setValue(true);
								}
							}
							refreshSearch();
						}
						if (data instanceof String) {
							if (data.toString().equals("cancel")) {
								tabPanel.selectTab(0);
							}
						}

					}
				};
				AgingSummaryWidget asw = new AgingSummaryWidget(cb);
				tabPanel.add(asw, "Aging Summary");
				tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
			}

		});
		parentSearchTable = new FlexTable();
		parentSearchTable.setSize("100%", "100%");
		parentSearchTable.setBorderWidth(1);
		parentSearchTable.getElement().getStyle().setProperty("borderCollapse",
				"collapse");
		searchCriteriaVPanel = new VerticalPanel();
		searchCriteriaVPanel.setWidth("100%");
		searchCriteriaVPanel.setSpacing(5);
		Label lbSearch = new Label("Claims Criteria");
		lbSearch.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
		lbSearch.getElement().getStyle().setProperty("fontSize", "15px");
		lbSearch.getElement().getStyle().setProperty("textDecoration",
				"underline");
		lbSearch.getElement().getStyle().setProperty("fontWeight", "bold");
		searchCriteriaTable = new FlexTable();
		searchCriteriaVPanel.add(lbSearch);
		searchCriteriaVPanel.add(searchCriteriaTable);

		currentCriteriaPanel = new VerticalPanel();
		currentCriteriaPanel.setWidth("100%");
		currentCriteriaPanel.setSpacing(5);
		Label lbExistingCri = new Label("Current Criteria");
		lbExistingCri.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
		lbExistingCri.getElement().getStyle().setProperty("fontSize", "15px");
		lbExistingCri.getElement().getStyle().setProperty("textDecoration",
				"underline");
		lbExistingCri.getElement().getStyle().setProperty("fontWeight", "bold");
		existingCriteriaTable = new FlexTable();
		currentCriteriaPanel.add(lbExistingCri);
		currentCriteriaPanel.add(existingCriteriaTable);

		parentSearchTable.setWidget(0, 0, searchCriteriaVPanel);
		parentSearchTable.setWidget(0, 1, currentCriteriaPanel);
		parentSearchTable.getFlexCellFormatter().getElement(0, 0).setAttribute(
				"width", "50%");
		parentSearchTable.getFlexCellFormatter().setVerticalAlignment(0, 1,
				HasVerticalAlignment.ALIGN_TOP);
			
		tagWidget = new PatientTagWidget();

		tabPanel = new TabPanel();
		initWidget(tabPanel);

		final HorizontalPanel searchHorizontalPanel = new HorizontalPanel();
		tabPanel.add(searchHorizontalPanel, "Search");
		tabPanel.selectTab(0);
		searchHorizontalPanel.setSize("100%", "100%");

		verticalPanel = new VerticalPanel();
		searchHorizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		// Adding all labels to the fexTable
		searchCriteriaTable.setWidget(0, 0, lblAging);
		searchCriteriaTable.setWidget(1, 0, lblFacility);
		searchCriteriaTable.setWidget(2, 0, lblProvider);
		searchCriteriaTable.setWidget(3, 0, lblPayer);
		searchCriteriaTable.setWidget(4, 0, lblPlanName);
		searchCriteriaTable.setWidget(5, 0, lblName);
		searchCriteriaTable.setWidget(6, 0, lbPatientWidget);
		searchCriteriaTable.setWidget(7, 0, lbTagSearch);
		searchCriteriaTable.setWidget(8, 0, lblBillingStatus);
		searchCriteriaTable.setWidget(9, 0, lblDateOfService);
		panelAging = new HorizontalPanel();
		panelAging.setSpacing(9);
		// panelAging.setSize("10","2"); //FIXME
		rb120Plus = new RadioButton("aging", "120+");
		rb91To120 = new RadioButton("aging", "91-120");
		rb61To90 = new RadioButton("aging", "61-90");
		rb31To60 = new RadioButton("aging", "31-60");
		rb0To30 = new RadioButton("aging", "0-30");
		rbNoSearch = new RadioButton("aging", "No Search");
		panelAging.add(rb120Plus);
		panelAging.add(rb91To120);
		panelAging.add(rb61To90);
		panelAging.add(rb31To60);
		panelAging.add(rb0To30);
		panelAging.add(rbNoSearch);
		searchCriteriaTable.setWidget(0, 1, panelAging);
		searchCriteriaTable.getFlexCellFormatter().setColSpan(0, 1, 2);
		// //////////////////////
		searchCriteriaTable.setWidget(1, 1, facilityWidget);
		provWidget = new SupportModuleWidget("ProviderModule");
		searchCriteriaTable.setWidget(2, 1, provWidget);
		provWidget.addValueChangeHandler(new ValueChangeHandler<Integer>() {

			@Override
			public void onValueChange(ValueChangeEvent<Integer> arg0) {
				refreshSearch();

			}

		});
		facilityWidget.addValueChangeHandler(new ValueChangeHandler<Integer>() {

			@Override
			public void onValueChange(ValueChangeEvent<Integer> arg0) {
				refreshSearch();

			}

		});
		// ////////////////////
		/* set column span so that it takes up the whole row. */

		searchCriteriaTable.getFlexCellFormatter().setColSpan(10, 1, 20); /*
																			 * col
																			 * span
																			 * for
																			 * Buttons
																			 */
		payerWidget = new CustomModuleWidget(
				"api.ClaimLog.RebillDistinctPayers");
		searchCriteriaTable.setWidget(3, 1, payerWidget);
		payerWidget.addValueChangeHandler(new ValueChangeHandler<Integer>() {

			@Override
			public void onValueChange(ValueChangeEvent<Integer> arg0) {
				refreshSearch();

			}

		});
		// /////////////////////////////
		planWidget = new CustomModuleWidget();
		searchCriteriaTable.setWidget(4, 1, planWidget);
		planWidget.addValueChangeHandler(new ValueChangeHandler<Integer>() {

			@Override
			public void onValueChange(ValueChangeEvent<Integer> arg0) {
				refreshSearch();

			}

		});
		// ////////////////////////////
		FlowPanel panelName = new FlowPanel();
		panelName.add(txtLastName);
		panelName.add(txtFirstName);
		searchCriteriaTable.setWidget(5, 1, panelName);
		searchCriteriaTable.getFlexCellFormatter().setColSpan(5, 1, 2);
		searchCriteriaTable.setWidget(6, 0, lbPatientWidget);
		searchCriteriaTable.setWidget(6, 1, patientWidget);
		searchCriteriaTable.setWidget(6, 2, cbShowZeroBalance);
		patientWidget.addValueChangeHandler(new ValueChangeHandler<Integer>() {

			@Override
			public void onValueChange(ValueChangeEvent<Integer> arg0) {
				refreshSearch();

			}

		});
		tagWidget.addValueChangeHandler(new ValueChangeHandler<String>() {
		
			@Override
			public void onValueChange(ValueChangeEvent<String> arg0) {
				refreshSearch();
			}
		
		});
		searchCriteriaTable.setWidget(7, 1, tagWidget);
		// ///////////////////////////
		rbQueued = new RadioButton("status", "Queued");
		rbQueued.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				refreshSearch();
			}

		});
		rbBilled = new RadioButton("status", "Billed");
		rbBilled.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				refreshSearch();
			}

		});
		statusHp = new HorizontalPanel();
		statusHp.add(rbQueued);
		statusHp.add(rbBilled);

		searchCriteriaTable.setWidget(8, 1, statusHp);
		// ////////////////////////////
		searchCriteriaTable.setWidget(9, 1, dateBox);
		searchCriteriaTable.setWidget(9, 2, cbWholeWeek);

		dateBox.addValueChangeHandler(new ValueChangeHandler<Date>() {

			@Override
			public void onValueChange(ValueChangeEvent<Date> arg0) {
				refreshSearch();
			}

		});
		cbShowZeroBalance
				.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

					@Override
					public void onValueChange(ValueChangeEvent<Boolean> arg0) {
						refreshSearch();

					}

				});
		cbWholeWeek.addValueChangeHandler(new ValueChangeHandler<Boolean>() {

			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				refreshSearch();

			}

		});
		// ////////////////////
		HorizontalPanel panelButtons = new HorizontalPanel();

		panelButtons.setSpacing(5);
		panelButtons.add(btnSearchClaim);
		panelButtons.add(btnClear);
		panelButtons.add(btnAgingSummary);
		searchCriteriaTable.setWidget(10, 1, panelButtons);
		searchCriteriaTable.getFlexCellFormatter().setColSpan(10, 1, 2);
		procDetailsHPanel = new HorizontalPanel();
		// procDetailsHPanel.setSize("100%", "100%");
		procDetailFlexTable = new FlexTable();
		procDetailFlexTable.setStyleName(AppConstants.STYLE_TABLE);
		procDetailFlexTable.setWidth("100%");
		viewLedgerDetails = new HTML(
				"<a href=\"javascript:undefined;\" style='color:blue'>View Details</a>");
		viewLedgerDetails.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {

				CustomRequestCallback cb = new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@Override
					public void jsonifiedData(Object data) {
						tabPanel.selectTab(0);
						if (data.toString().equals("update"))
							refreshSearch();

					}
				};
				LedgerWidget pw = new LedgerWidget(currentProcId,
						currentPatientId, procCovSrc, PayCategory.LEDGER, cb);
				ledgerPopup.clear();
				ledgerPopup.hide();
				tabPanel.add(pw, currentPatientName);
				tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
			}

		});
		procDetailsHPanel.add(procDetailFlexTable);
		// procDetailFlexTable.setSize("100%", "100%");
		// / Preparing Columns for ClaimManager Table
		claimsManagerTable = new CustomTable();
		claimsManagerTable.setAllowSelection(false);
		claimsManagerTable.setSize("100%", "100%");
		claimsManagerTable.setIndexName("Id");
		claimsManagerTable.addColumn("S", "selected");
		claimsManagerTable.addColumn("DOS", "date_of");
		claimsManagerTable.addColumn("Facility", "posname");
		claimsManagerTable.addColumn("Patient", "patient");
		claimsManagerTable.addColumn("Provider", "provider_name");
		claimsManagerTable.addColumn("Payer", "payer");
		claimsManagerTable.addColumn("Paid", "paid");
		claimsManagerTable.addColumn("Balance", "balance");
		claimsManagerTable.addColumn("Status", "status");
		claimsManagerTable.addColumn("Claim", "claim");
		claimsManagerTable.addColumn("Action", "action");
		claimsManagerTable.getFlexTable().getFlexCellFormatter().setWidth(0, 0,
				"5px");
		checkBoxesList = new ArrayList<CheckBox>();
		// final HashMap<String, String> selectedPatientsWithClaims= new
		// HashMap<String, String>();
		claimsManagerTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					@Override
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {
						if (columnName.compareTo("action") == 0) {

							HorizontalPanel actionPanel = new HorizontalPanel();
							actionPanel.setSpacing(5);
							HTML htmlLedger = new HTML(
									"<a href=\"javascript:undefined;\" style='color:blue'>Ledger</a>");

							htmlLedger.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {
									currentProcId = data.get("Id");
									currentPatientName = data.get("patient");
									currentPatientId = data.get("patient_id");
									procCovSrc = data.get("proc_cov_type");
									openPopup();
								}

							});

							HTML htmlEMR = new HTML(
									"<a href=\"javascript:undefined;\" style='color:blue'>EMR</a>");

							htmlEMR.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {

									Integer ptID = Integer.parseInt(data
											.get("patient_id"));
									PatientScreen p = new PatientScreen();
									p.setPatient(ptID);
									Util.spawnTab(data.get("patient"), p);

								}

							});

							HTML htmlBill = null;
							if (data.get("billed") == null
									|| data.get("billed").equals("")
									|| data.get("billed").equals("0")) {
								htmlBill = new HTML(
										"<a href=\"javascript:undefined;\" style='color:blue'>Bill</a>");

								htmlBill.addClickHandler(new ClickHandler() {
									@Override
									public void onClick(ClickEvent arg0) {
										selectedProcs.clear();
										selectedProcs.add(data.get("Id"));
										CustomRequestCallback cb = new CustomRequestCallback() {
											@Override
											public void onError() {

											}

											@Override
											public void jsonifiedData(
													Object data) {
												tabPanel.selectTab(0);
												if (data.toString().equals(
														"update"))
													refreshSearch();

											}
										};
										RemittBillingWidget billClaimsWidget = new RemittBillingWidget(
												selectedProcs, cb,
												BillingType.BILL);
										tabPanel.add(billClaimsWidget,
												"Billing Queue");
										tabPanel.selectTab(tabPanel
												.getWidgetCount() - 1);
									}

								});
							} else {
								htmlBill = new HTML(
										"<a href=\"javascript:undefined;\" style='color:blue'>ReBill</a>");

								htmlBill.addClickHandler(new ClickHandler() {
									@Override
									public void onClick(ClickEvent arg0) {
										if (data.get("billkey") != null
												&& !data.get("billkey").equals(
														"")) {
											selectedBillKeys.clear();
											selectedBillKeys.add(data
													.get("billkey"));
											CustomRequestCallback cb = new CustomRequestCallback() {
												@Override
												public void onError() {

												}

												@Override
												public void jsonifiedData(
														Object data) {
													tabPanel.selectTab(0);
													if (data.toString().equals(
															"update"))
														refreshSearch();

												}
											};
											RemittBillingWidget billClaimsWidget = new RemittBillingWidget(
													selectedBillKeys, cb,
													BillingType.REBILL);
											tabPanel.add(billClaimsWidget,
													"Re-Bill Claims");
											tabPanel.selectTab(tabPanel
													.getWidgetCount() - 1);
										} else {
											Window
													.alert("The selected claim is not submitted before");
										}
									}

								});
							}
							actionPanel.add(htmlEMR);
							actionPanel.add(htmlBill);
							actionPanel.add(htmlLedger);
							return actionPanel;
						} else if (columnName.compareTo("selected") == 0) {
							int actionRow = claimsManagerTable.getActionRow();
							claimsManagerTable.getFlexTable()
									.getFlexCellFormatter().setWidth(actionRow,
											0, "5px");
							CheckBox c = new CheckBox();
							checkBoxesList.add(c);
							c
									.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
										@Override
										public void onValueChange(
												ValueChangeEvent<Boolean> arg0) {
											if (arg0.getValue()) {
												selectedProcs.add(data
														.get("Id"));
												if (data.get("billkey") != null
														&& !data.get("billkey")
																.equals(""))
													selectedBillKeys.add(data
															.get("billkey"));
											} else {
												selectedProcs.remove(data
														.get("Id"));
												selectedBillKeys.remove(data
														.get("billkey"));
											}
											// selectedPatientsWithClaims.put(data.get("patient_id"),
											// data.get("claims"));
											// else
											// selectedPatientsWithClaims.remove(data.get("patient_id"));
										}
									});
							return c;
						} else if (columnName.compareTo("status") == 0) {
							Float balance = Float.parseFloat(data.get("balance"));
							Label label = new Label(); 
							if (data.get("billed").equals("0")) {
								label.setText("Queued");
								if (balance==0) 
									label.getElement().getStyle().setColor("#5B5B3B");
								 else if (balance<0)
									label.getElement().getStyle().setColor("#FF0000");
							}
							 else{
								label.setText("Billed");
								label.getElement().getStyle().setColor("#6000A0");
							 }
							
							return label;
						} else if (data.get("balance") != null) {
							Float balance = Float.parseFloat(data.get("balance"));
							Label label = new Label(data.get(columnName));
							if (data.get("billed").equals("1")) 
								label.getElement().getStyle().setColor("#6000A0");
							else if (balance==0) 
								label.getElement().getStyle().setColor("#5B5B3B");
							 else if (balance<0)
								label.getElement().getStyle().setColor("#FF0000");
							return label;

						} else {
							return (Widget) null;
						}
					}
					
				});
		

		
		claimsManagerTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col == 1) {
						dateBox.setValue(Util.getSQLDate(data.get("date_of")));
						refreshSearch();
					}
					if (col == 2) {
						CustomRequestCallback cb = new CustomRequestCallback() {
							@Override
							public void onError() {

							}

							@Override
							public void jsonifiedData(Object data) {
								if (data.toString().equals("done"))
									refreshSearch();
							}
						};
						facilityWidget.setAfterSetValueCallBack(cb);
						facilityWidget.setValue(Integer.parseInt(data
								.get("pos")));
					}
					if (col == 3) {
						CustomRequestCallback cb = new CustomRequestCallback() {
							@Override
							public void onError() {

							}

							@Override
							public void jsonifiedData(Object data) {
								if (data.toString().equals("done"))
									refreshSearch();
							}
						};
						patientWidget.setAfterSetValueCallBack(cb);
						patientWidget.setValue(Integer.parseInt(data
								.get("patient_id")));

					}
					if (col == 4) {
						CustomRequestCallback cb = new CustomRequestCallback() {
							@Override
							public void onError() {

							}

							@Override
							public void jsonifiedData(Object data) {
								if (data.toString().equals("done"))
									refreshSearch();
							}
						};
						provWidget.setAfterSetValueCallBack(cb);
						provWidget.setValue(Integer.parseInt(data
								.get("provider_id")));

					}
					if (col == 5) {
						payerWidget.setValue(Integer.parseInt(data
								.get("payer_id")));
						payerWidget.setText(data.get("payer"));
						refreshSearch();
					}
					if (col == 8) {
						if (data.get("billed").equals("0"))
							rbQueued.setValue(true);
						else
							rbBilled.setValue(false);
						refreshSearch();
					}

					if (col == 9) {
						CustomRequestCallback cb = new CustomRequestCallback() {
							@Override
							public void onError() {

							}

							@Override
							public void jsonifiedData(Object data) {
								tabPanel.selectTab(0);
								if (data.toString().equals("update"))
									refreshSearch();
								if (data.toString().equals("new"))
									clearSearch();

							}
						};
						ClaimDetailsWidget claimDetail = new ClaimDetailsWidget(
								Integer.parseInt(data.get("claim")), Integer
										.parseInt(data.get("patient_id")),data.get("patient"), cb);
						tabPanel.add(claimDetail, "Claim Details: "
								+ data.get("claim"));
						tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
					}

				} catch (Exception e) {
					JsonUtil.debug("ClaimManager.java: Caught exception: "
							+ e.toString());
				}
			}
		});
		final HorizontalPanel buttonsHPanel = new HorizontalPanel();
		buttonsHPanel.setWidth("100%");
		HorizontalPanel buttonsHPanelLeft = new HorizontalPanel();
		buttonsHPanel.add(buttonsHPanelLeft);
		HorizontalPanel buttonsHPanelRight = new HorizontalPanel();
		buttonsHPanel.add(buttonsHPanelRight);
		buttonsHPanel.setCellHorizontalAlignment(buttonsHPanelRight,
				HorizontalPanel.ALIGN_RIGHT);
		final CustomButton selectAllBtn = new CustomButton("Select All",AppConstants.ICON_SELECT_ALL);
		buttonsHPanelLeft.add(selectAllBtn);
		selectAllBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				Iterator<CheckBox> itr = checkBoxesList.iterator();
				while (itr.hasNext()) {
					CheckBox checkBox = (CheckBox) itr.next();
					checkBox.setValue(true, true);
				}
			}
		});

		final CustomButton selectNoneBtn = new CustomButton("Select None",AppConstants.ICON_SELECT_NONE);
		buttonsHPanelLeft.add(selectNoneBtn);
		selectNoneBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				Iterator<CheckBox> itr = checkBoxesList.iterator();
				while (itr.hasNext()) {
					CheckBox checkBox = (CheckBox) itr.next();
					checkBox.setValue(false, true);
				}
			}
		});

		final CustomButton postCheckBtn = new CustomButton("Post Check",AppConstants.ICON_SEND);
		buttonsHPanelLeft.add(postCheckBtn);
		postCheckBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				CustomRequestCallback cb = new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@Override
					public void jsonifiedData(Object data) {
						tabPanel.selectTab(0);
						if (data.toString().equals("update"))
							refreshSearch();

					}
				};
				PostCheckWidget postCheckWidget = new PostCheckWidget(
						selectedProcs, cb);
				tabPanel.add(postCheckWidget, "Post Check");
				tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
			}
		});

		final CustomButton billClaimsBtn = new CustomButton("Bill Claims");
		buttonsHPanelRight.add(billClaimsBtn);
		billClaimsBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				CustomRequestCallback cb = new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@Override
					public void jsonifiedData(Object data) {
						tabPanel.selectTab(0);
						if (data.toString().equals("update"))
							refreshSearch();

					}
				};
				RemittBillingWidget billClaimsWidget = new RemittBillingWidget(
						selectedProcs, cb, BillingType.BILL);
				tabPanel.add(billClaimsWidget, "Billing Queue");
				tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
			}
		});

		final CustomButton markBilledBtn = new CustomButton("Mark Billed",AppConstants.ICON_SELECT_ALL);
		buttonsHPanelRight.add(markBilledBtn);
		markBilledBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				markAsBilled();
			}
		});

		final CustomButton rebillClaimsBtn = new CustomButton("Re-Bill Claims");
		buttonsHPanelRight.add(rebillClaimsBtn);
		rebillClaimsBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				CustomRequestCallback cb = new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@Override
					public void jsonifiedData(Object data) {
						tabPanel.selectTab(0);
						if (data.toString().equals("update"))
							refreshSearch();

					}
				};
				RemittBillingWidget billClaimsWidget = new RemittBillingWidget(
						selectedBillKeys, cb, BillingType.REBILL);
				tabPanel.add(billClaimsWidget, "Re-Bill Claims");
				tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
			}
		});

		selectedProcs = new HashSet<String>();
		selectedBillKeys = new HashSet<String>();
		// ////////////
		verticalPanel.add(parentSearchTable);
		verticalPanel.add(buttonsHPanel);
		verticalPanel.add(claimsManagerTable);
		currentProcId = "";

		populate();
		Util.setFocus(rbNoSearch);
	}

	public void openPopup() {

		getProcDetails();
		popupVPanel.clear();
		HorizontalPanel popupClosePanel = new HorizontalPanel();
		popupClosePanel.setWidth("100%");
		popupClosePanel.setHorizontalAlignment(HorizontalPanel.ALIGN_RIGHT);
		Image closeImage = new Image("resources/images/close_x.16x16.png");
		closeImage.setTitle("Close Popup");
		closeImage.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				ledgerPopup.clear();
				ledgerPopup.hide();
			}
		});
		closeImage.getElement().getStyle().setProperty("cursor", "pointer");

		popupClosePanel.add(closeImage);
		popupVPanel.add(popupClosePanel);
		actionsList.setSelectedIndex(0);

		Label lblHeading2 = new Label("Procedure");
		lblHeading2.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		HorizontalPanel topHp = new HorizontalPanel();
		topHp.setSpacing(5);
		topHp.add(lblHeading2);
		topHp.add(viewLedgerDetails);
		popupVPanel.add(topHp);
		popupVPanel.add(procDetailsHPanel);
		popupVPanel.add(ledgerStep1HPanel);
		popupVPanel.setCellHorizontalAlignment(ledgerStep1HPanel,
				HasHorizontalAlignment.ALIGN_CENTER);
		ledgerPopup = new DialogBox();
		ledgerPopup.setPixelSize(700, 20);
		PopupView viewInfo = new PopupView(popupVPanel);
		// ledgerPopup.setNewWidget(viewInfo);
		// ledgerPopup.initialize();
		ledgerPopup.setWidget(viewInfo);
		ledgerPopup.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		ledgerPopup.center();
		ledgerPopup.show();
		// showProcedureCostPopup();
	}

	private void populate() {
		// if (Util.getProgramMode() == ProgramMode.STUBBED){
		/*
		 * }else if (Util.getProgramMode() == ProgramMode.JSONRPC){ //TODO for
		 * JSONRPC
		 */// }
	}

	public void getProcDetails() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {

			String[] params = { currentProcId.toString() };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.ClaimLog.getProcInfo",
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

									procDetailFlexTable.clear();
									int col = 0;
									Label procDateLb = new Label(
											"Procedure Date");
									procDateLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label procDateVal = new Label(result
											.get("proc_date"));
									procDetailFlexTable.setWidget(0, col,
											procDateLb);
									FlexCellFormatter cellFormatter = procDetailFlexTable
											.getFlexCellFormatter();
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											procDateVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									Label procCode = new Label("Procedure Code");
									procCode.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label procCodeVal = new Label(result
											.get("proc_code"));
									procDetailFlexTable.setWidget(0, col,
											procCode);
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											procCodeVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									Label provLb = new Label("Provider");
									provLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label provVal = new Label(result
											.get("prov_name"));
									procDetailFlexTable.setWidget(0, col,
											provLb);
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											provVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									Label chargedLb = new Label("Charged");
									chargedLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label chargedVal = new Label(result
											.get("proc_obal"));
									procDetailFlexTable.setWidget(0, col,
											chargedLb);
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											chargedVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									if (result.get("proc_allowed") != null
											&& !result.get("proc_allowed")
													.equals("")) {
										Label allowedLb = new Label("Allowed");
										allowedLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
										Label allowedVal = new Label(result
												.get("proc_allowed"));
										procDetailFlexTable.setWidget(0, col,
												allowedLb);
										cellFormatter.setStyleName(0, col,
												AppConstants.STYLE_TABLE_HEADER);
										procDetailFlexTable.setWidget(1, col,
												allowedVal);
										cellFormatter.setStyleName(1, col,
												AppConstants.STYLE_TABLE_ROW_ALTERNATE);
										col++;
									}

									Label chargesLb = new Label("Charges");
									chargesLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label chargesVal = new Label(result
											.get("proc_charges"));
									procDetailFlexTable.setWidget(0, col,
											chargesLb);
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											chargesVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									Label procPaidLb = new Label("Paid");
									procPaidLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label procPaidVal = new Label(result
											.get("proc_paid"));
									procDetailFlexTable.setWidget(0, col,
											procPaidLb);
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											procPaidVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									Label balanceLb = new Label("Balance");
									balanceLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
									Label balanceVal = new Label(result
											.get("proc_currbal"));
									procDetailFlexTable.setWidget(0, col,
											balanceLb);
									cellFormatter.setStyleName(0, col,
											AppConstants.STYLE_TABLE_HEADER);
									procDetailFlexTable.setWidget(1, col,
											balanceVal);
									cellFormatter.setStyleName(1, col,
											AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									col++;

									if (result.get("proc_billed").equals("1")) {
										Label billedLb = new Label("Billed");
										billedLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
										Label billedVal = new Label("Yes");
										procDetailFlexTable.setWidget(0, col,
												billedLb);
										cellFormatter.setStyleName(0, col,
												AppConstants.STYLE_TABLE_HEADER);
										procDetailFlexTable.setWidget(1, col,
												billedVal);
										cellFormatter.setStyleName(1, 8,
												AppConstants.STYLE_TABLE_ROW_ALTERNATE);
										col++;

										Label dateBilledLb = new Label(
												"Date Billed");
										dateBilledLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
										Label dateBilledVal = new Label(result
												.get("proc_billdate"));
										procDetailFlexTable.setWidget(0, col,
												dateBilledLb);
										cellFormatter.setStyleName(0, col,
												AppConstants.STYLE_TABLE_HEADER);
										procDetailFlexTable.setWidget(1, col,
												dateBilledVal);
										cellFormatter.setStyleName(1, col,
												AppConstants.STYLE_TABLE_ROW_ALTERNATE);
										col++;
									} else {
										Label billedLb = new Label("Billed");
										billedLb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
										Label billedVal = new Label("No");
										procDetailFlexTable.setWidget(0, col,
												billedLb);
										cellFormatter.setStyleName(0, col,
												AppConstants.STYLE_TABLE_HEADER);
										procDetailFlexTable.setWidget(1, col,
												billedVal);
										cellFormatter.setStyleName(1, col,
												AppConstants.STYLE_TABLE_ROW_ALTERNATE);
										col++;
									}

								} catch (Exception e) {

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
		}
	}

	@SuppressWarnings("unchecked")
	protected void refreshSearch() {
		final BlockScreenWidget blockScreenWidget = new BlockScreenWidget("Loading claims, please wait...");
		verticalPanel.add(blockScreenWidget);
		claimsManagerTable.clearData();
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			HashMap<String, String> a = new HashMap<String, String>();
			a.put("item_type", "item type1");
			a.put("item", "item 1");
			a.put("patient", "def");
			a.put("provider", "JEFF");
			a.put("date_of", "1979-08-10");
			a.put("total_balance", "28");
			a.put("payment_date", "1979-09-10");
			a.put("procedure_id", "22");
			a.put("money_out", "200");
			a.put("money_in", "500");
			a.put("id", "1");

			List<HashMap<String, String>> l = new ArrayList<HashMap<String, String>>();
			l.add(a);
			claimsManagerTable.loadData((HashMap<String, String>[]) l
					.toArray(new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			existingCriteriaTable.clear();
			existingCriteriaTable.removeAllRows();
			selectedProcs.clear();
			selectedBillKeys.clear();
			checkBoxesList.clear();
			HashMap<String, String> criteria = new HashMap<String, String>();
			boolean isAgingSelected = false;
			if (rb120Plus.getValue()) {
				criteria.put("aging", "120+");
				addExistingSearchCriteria("aging", "Aging", "120+");
				isAgingSelected = true;
			}
			if (rb91To120.getValue()) {
				criteria.put("aging", "91-120");
				addExistingSearchCriteria("aging", "Aging", "91-120");
				isAgingSelected = true;
			}
			if (rb61To90.getValue()) {
				criteria.put("aging", "61-90");
				addExistingSearchCriteria("aging", "Aging", "61-90");
				isAgingSelected = true;
			}
			if (rb31To60.getValue()) {
				criteria.put("aging", "31-60");
				addExistingSearchCriteria("aging", "Aging", "31-60");
				isAgingSelected = true;
			}
			if (rb0To30.getValue()) {
				criteria.put("aging", "0-30");
				addExistingSearchCriteria("aging", "Aging", "0-30");
				isAgingSelected = true;
			}
			if (isAgingSelected) {
				panelAging.setVisible(false);
				lblAging.setVisible(false);
				rb120Plus.setVisible(false);
				rb91To120.setVisible(false);
				rb61To90.setVisible(false);
				rb31To60.setVisible(false);
				rb0To30.setVisible(false);
				rbNoSearch.setVisible(false);
			}

			if (facilityWidget.getStoredValue() != null
					&& !facilityWidget.getStoredValue().equals("0")) {
				criteria.put("facility", facilityWidget.getStoredValue());
				facilityWidget.setVisible(false);
				lblFacility.setVisible(false);
				addExistingSearchCriteria("facility", "Facility",
						facilityWidget.getText());
			}
			if (provWidget.getStoredValue() != null
					&& !provWidget.getStoredValue().equals("0")) {
				criteria.put("provider", provWidget.getStoredValue());
				provWidget.setVisible(false);
				lblProvider.setVisible(false);
				addExistingSearchCriteria("provider", "Provider", provWidget
						.getText());
			}
			if (payerWidget.getStoredValue() != null
					&& !payerWidget.getStoredValue().equals("0")) {
				criteria.put("payer", payerWidget.getStoredValue());
				lblPayer.setVisible(false);
				payerWidget.setVisible(false);
				addExistingSearchCriteria("payer", "Payer", payerWidget
						.getText());
			}
			if (planWidget.getStoredValue() != null
					&& !planWidget.getStoredValue().equals("0")) {
				criteria.put("plan", planWidget.getStoredValue());
				lblPlanName.setVisible(false);
				planWidget.setVisible(false);
				addExistingSearchCriteria("plan", "Plan Name", planWidget
						.getText());
			}
			if (patientWidget.getStoredValue() != null
					&& !patientWidget.getStoredValue().equals("0")) {
				criteria.put("patient", patientWidget.getStoredValue());
				lbPatientWidget.setVisible(false);
				patientWidget.setVisible(false);
				addExistingSearchCriteria("patient", "Patient Full Name",
						patientWidget.getText());
			}
			criteria.put("first_name", txtFirstName.getValue());
			if (!txtFirstName.getText().equals("")) {
				addExistingSearchCriteria("first_name", "First Name",
						txtFirstName.getText());
			}
			criteria.put("last_name", txtLastName.getValue());
			if (!txtLastName.getText().equals("")) {
				addExistingSearchCriteria("last_name", "Last Name", txtLastName
						.getText());
			}
			if (tagWidget.getValue()!=null && !tagWidget.getValue().equals("")) {
				addExistingSearchCriteria("tag", "Tag", tagWidget.getValue());
				criteria.put("tag", tagWidget.getValue());
				tagWidget.setVisible(false);
				lbTagSearch.setVisible(false);
			}
			if (rbQueued.getValue()) {
				criteria.put("billed", "0");
				statusHp.setVisible(false);
				lblBillingStatus.setVisible(false);
				rbQueued.setVisible(false);
				rbBilled.setVisible(false);
				addExistingSearchCriteria("billed", "Billing Status", "Queued");
			}
			if (rbBilled.getValue()) {
				criteria.put("billed", "1");
				statusHp.setVisible(false);
				lblBillingStatus.setVisible(false);
				rbQueued.setVisible(false);
				rbBilled.setVisible(false);
				addExistingSearchCriteria("billed", "Billing Status", "Billed");
			}
			// Check for date of Service.
			if (dateBox.getValue() == null || dateBox.getValue().equals("")) {

			} else {
				criteria.put("date", dateBox.getTextBox().getValue());
				lblDateOfService.setVisible(false);
				dateBox.setVisible(false);
				addExistingSearchCriteria("date", "Procedures On", dateBox
						.getTextBox().getText());
			}
			if (cbShowZeroBalance.getValue()) {
				criteria.put("zerobalance", "1");
				cbShowZeroBalance.setVisible(false);
				addExistingSearchCriteria("zerobalance", "Include Zero Balance", "");
			}
			if (cbWholeWeek.getValue()) {
				criteria.put("week", "1");
				cbWholeWeek.setVisible(false);
				addExistingSearchCriteria("week", "Select Week", "");
			}
			String[] params = { JsonUtil.jsonify(criteria) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ClaimLog.AgingReportQualified",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						verticalPanel.remove(blockScreenWidget);
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								try{
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser.parse(response
													.getText()),
													"HashMap<String,String>[]");
	
									if (result.length > 0) {
										claimsManagerTable.setVisible(true); // FIXME
																				// old
																				// value:
																				// claimsManagerTable.setVisible(false);
									} else {
										claimsManagerTable.setVisible(true);
									}
									verticalPanel.remove(blockScreenWidget);
									claimsManagerTable.loadData(result);
								}
								catch(Exception e){
									verticalPanel.remove(blockScreenWidget);
								}
							} else {
								verticalPanel.remove(blockScreenWidget);
								claimsManagerTable.setVisible(false); // FIXME
																		// old
																		// value:
																		// claimsManagerTable.setVisible(true);
							}
						}
					}
				});
			} catch (RequestException e) {
				verticalPanel.remove(blockScreenWidget);
				Window.alert(e.toString());
				claimsManagerTable.setVisible(true);
			}
		} else {
			verticalPanel.remove(blockScreenWidget);
		}
	}

	public void addExistingSearchCriteria(String k, String name, String value) {
		Label lbName = new Label(name);
		lbName.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		Label lbVal = new Label(value);
		final String key = k;
		final CustomButton remove = new CustomButton("X");
		remove.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				Node tempNode = remove.getElement();
				while (!tempNode.getNodeName().equals("TR")) {
					tempNode = tempNode.getParentNode();
				}
				tempNode.removeFromParent();

				if (key.equals("facility")) {
					lblFacility.setVisible(true);
					facilityWidget.clear();
					facilityWidget.setVisible(true);
				}
				if (key.equals("provider")) {
					lblProvider.setVisible(true);
					provWidget.clear();
					provWidget.setVisible(true);
				}
				if (key.equals("plan")) {
					lblPlanName.setVisible(true);
					planWidget.clear();
					planWidget.setVisible(true);
				}
				if (key.equals("payer")) {
					lblPayer.setVisible(true);
					payerWidget.clear();
					payerWidget.setVisible(true);
				}
				if (key.equals("first_name")) {
					lblName.setVisible(true);
					txtFirstName.setText("");
					txtFirstName.setVisible(true);
				}
				if (key.equals("last_name")) {
					lblName.setVisible(true);
					txtLastName.setText("");
					txtLastName.setVisible(true);
				}
				if (key.equals("patient")) {
					lbPatientWidget.setVisible(true);
					patientWidget.clear();
					patientWidget.setVisible(true);
				}
				if (key.equals("aging")) {
					panelAging.setVisible(true);
					lblAging.setVisible(true);
					rb120Plus.setVisible(true);
					rb120Plus.setValue(false);
					rb91To120.setVisible(true);
					rb91To120.setValue(false);
					rb61To90.setVisible(true);
					rb61To90.setValue(false);
					rb31To60.setVisible(true);
					rb31To60.setValue(false);
					rb0To30.setVisible(true);
					rb0To30.setValue(false);
					rbNoSearch.setVisible(true);
					rbNoSearch.setValue(false);
				}
				if (key.equals("billed")) {
					statusHp.setVisible(true);
					lblBillingStatus.setVisible(true);
					rbQueued.setVisible(true);
					rbQueued.setValue(false);
					rbBilled.setVisible(true);
					rbBilled.setValue(false);
				}
				if (key.equals("date")) {
					dateBox.getTextBox().setText("");
					lblDateOfService.setVisible(true);
					dateBox.setVisible(true);
				}
				if (key.equals("zerobalance")) {
					cbShowZeroBalance.setValue(false);
					cbShowZeroBalance.setVisible(true);
				}
				if (key.equals("week")) {
					cbWholeWeek.setValue(false);
					cbWholeWeek.setVisible(true);
				}
				if (key.equals("tag")) {
					tagWidget.setVisible(true);
					lbTagSearch.setVisible(true);
					tagWidget.clear();
				}
				// parentTR = tempNode;

				// parentTableBody.removeChild(parentTR);
				refreshSearch();
			}
		});
		int rc = existingCriteriaTable.getRowCount();
		existingCriteriaTable.setWidget(rc, 0, lbName);
		existingCriteriaTable.setWidget(rc, 1, lbVal);
		existingCriteriaTable.setWidget(rc, 2, remove);

	}

	public void clearSearch() {
		existingCriteriaTable.clear();
		existingCriteriaTable.removeAllRows();

		panelAging.setVisible(true);
		lblAging.setVisible(true);
		rb120Plus.setVisible(true);
		rb120Plus.setValue(false);
		rb91To120.setVisible(true);
		rb91To120.setValue(false);
		rb61To90.setVisible(true);
		rb61To90.setValue(false);
		rb31To60.setVisible(true);
		rb31To60.setValue(false);
		rb0To30.setVisible(true);
		rb0To30.setValue(false);
		rbNoSearch.setVisible(true);
		rbNoSearch.setValue(false);

		lblFacility.setVisible(true);
		facilityWidget.clear();
		facilityWidget.setVisible(true);

		lblProvider.setVisible(true);
		provWidget.clear();
		provWidget.setVisible(true);

		lblPlanName.setVisible(true);
		planWidget.clear();
		planWidget.setVisible(true);

		lblPayer.setVisible(true);
		payerWidget.clear();
		payerWidget.setVisible(true);

		lblName.setVisible(true);
		txtFirstName.setText("");
		txtFirstName.setVisible(true);

		lblName.setVisible(true);
		txtLastName.setText("");
		txtLastName.setVisible(true);

		lbPatientWidget.setVisible(true);
		patientWidget.clear();
		patientWidget.setVisible(true);
		
		tagWidget.setVisible(true);
		lbTagSearch.setVisible(true);
		tagWidget.clear();

		statusHp.setVisible(true);
		lblBillingStatus.setVisible(true);
		rbQueued.setVisible(true);
		rbQueued.setValue(false);
		rbBilled.setVisible(true);
		rbBilled.setValue(false);

		dateBox.getTextBox().setText("");
		lblDateOfService.setVisible(true);
		dateBox.setVisible(true);

		cbShowZeroBalance.setValue(false);
		cbShowZeroBalance.setVisible(true);

		cbWholeWeek.setValue(false);
		cbWholeWeek.setVisible(true);

		refreshSearch();
	}

	public void markAsBilled() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { selectedProcs.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ClaimLog.MarkClaimsAsBilled",
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
									refreshSearch();

								} catch (Exception e) {

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}

		} else {
		}
	}

	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
