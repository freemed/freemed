/*
 * $Id: ClaimsManager.java 4643 2009-10-21 11:50:05Z Fawad $
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.i18n.client.DateTimeFormat;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.FlowPanel;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.RadioButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.datepicker.client.DateBox;
import com.google.gwt.user.datepicker.client.DateBox.DefaultFormat;

public class ClaimsManager extends ScreenInterface {
	protected CustomTable claimsManagerTable;
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
	FlexTable flextable;
	// Declreaing Button
	Button btnSearchClaim;
	Button btnCancel;
	Button btnAgingSummary;
	// Declreaing all list box which will be popolate in JSONRPC Mode
	ListBox lbProvider;
	ListBox lbPayer;
	ListBox lbPlan;
	ListBox lbBillingStatus;
    //Declaring Ration button for age;
	RadioButton rb120Plus,rb91To120,rb61To90,rb31To60,rb0To30,rbNoSearch; 

	private static List<ClaimsManager> claimsManagerScreenList = null;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well

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

	public static boolean removeInstance(ClaimsManager claimsManagerScreen){
		return claimsManagerScreenList.remove(claimsManagerScreen);
	}
	
	public ClaimsManager() {
		// Intializing all labels.
		lblAging = new Label("Aging");
		lblAging.setStyleName("label");
		lblProvider = new Label("Provider");
		lblProvider.setStyleName("label");
		lblPayerCriteria = new Label("Payer Criteria");
		lblPayerCriteria.setStyleName("medium-header-label");
		lblPayer = new Label("Payer");
		lblPayer.setStyleName("label");
		lblPlanName = new Label("Plan Name");
		lblPlanName.setStyleName("label");
		lblPatientCriteria = new Label("Patient Criteria");
		lblPatientCriteria.setStyleName("medium-header-label");
		lblName = new Label("Name (Last, First)");
		lblName.setStyleName("label");
		lblClaimCriteria = new Label("Claim Criteria");
		lblClaimCriteria.setStyleName("medium-header-label");
		lblBillingStatus = new Label("Billing Status");
		lblBillingStatus.setStyleName("label");
		lblDateOfService = new Label("Date of Service");
		lblDateOfService.setStyleName("label");

		// TextBoxs for FirsName and LastName
		txtFirstName = new TextBox();
		txtFirstName.setWidth("200px");
		txtLastName = new TextBox();
		txtLastName.setWidth("200px");
		// date for service's date and its simple format i;e without time.
		dateBox = new DateBox();
		dateBox.setFormat(new DefaultFormat(DateTimeFormat.getShortDateFormat()));
		// Buttons for
		btnSearchClaim = new Button("Search Claim");
		btnSearchClaim.addClickHandler(new ClickHandler() {

			public void onClick(ClickEvent event) {
				if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					// ////////////////////////
					refreshSearch();
				} else {
					Window.alert("You are on STUB Mod !");
				}
			}

		});
		btnCancel = new Button("Cancel");
		btnCancel.addClickHandler(new ClickHandler() {

			public void onClick(ClickEvent event) {
				// TODO Auto-generated method stub
				Window.alert("Cancel.........");
			}

		});

		btnAgingSummary = new Button("Aging Summary");
		btnAgingSummary.addClickHandler(new ClickHandler() {

			public void onClick(ClickEvent event) {
				// TODO Auto-generated method stub
				Window.alert("Aging Summary.........");
			}

		});

		flextable = new FlexTable();

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		// Adding all labels to the fexTable
		flextable.setWidget(0, 0, lblAging);
		flextable.setWidget(1, 0, lblProvider);
		flextable.setWidget(2, 0, lblPayerCriteria);
		flextable.setWidget(3, 0, lblPayer);
		flextable.setWidget(4, 0, lblPlanName);
		flextable.setWidget(5, 0, lblPatientCriteria);
		flextable.setWidget(6, 0, lblName);
		flextable.setWidget(7, 0, lblClaimCriteria);
		flextable.setWidget(8, 0, lblBillingStatus);
		flextable.setWidget(9, 0, lblDateOfService);
		// ///////////////
		HorizontalPanel panelAging = new HorizontalPanel();
		panelAging.setSpacing(9);
		// panelAging.setSize("10","2"); //FIXME
		rb120Plus = new RadioButton("aging", "120+");
		rb120Plus.setStyleName("radio-label");
		rb91To120 = new RadioButton("aging", "91-120");
		rb91To120.setStyleName("radio-label");
		rb61To90 = new RadioButton("aging", "61-90");
		rb61To90.setStyleName("radio-label");
		rb31To60 = new RadioButton("aging", "31-60");
		rb31To60.setStyleName("radio-label");
		rb0To30 = new RadioButton("aging", "0-30");
		rb0To30.setStyleName("radio-label");
		rbNoSearch = new RadioButton("aging", "No Search");
		rbNoSearch.setStyleName("radio-label");
		panelAging.add(rb120Plus);
		panelAging.add(rb91To120);
		panelAging.add(rb61To90);
		panelAging.add(rb31To60);
		panelAging.add(rb0To30);
		panelAging.add(rbNoSearch);
		flextable.setWidget(0, 1, panelAging);
		// //////////////////////
		lbProvider = new ListBox();
		lbProvider.addItem("___","___");
		flextable.setWidget(1, 1, lbProvider);
		// ////////////////////
		/* set column span so that it takes up the whole row. */
		flextable.getFlexCellFormatter().setColSpan(2, 0, 3); /* col span for  Payer Criteria */
		flextable.getFlexCellFormatter().setColSpan(5, 0, 3); /* col span for Patient Criteria */
		flextable.getFlexCellFormatter().setColSpan(7, 0, 3); /* col span for Claim Criteria  */
		flextable.getFlexCellFormatter().setColSpan(10, 1, 20); /* col span for Buttons  */
		lbPayer = new ListBox();
		lbPayer.addItem("___","___");
		flextable.setWidget(3, 1, lbPayer);
		// /////////////////////////////
		lbPlan = new ListBox();
		lbPlan.addItem("");
		flextable.setWidget(4, 1, lbPlan);
		// ////////////////////////////
		FlowPanel panelName = new FlowPanel();
		panelName.add(txtLastName);
		panelName.add(txtFirstName);
		flextable.setWidget(6, 1, panelName);
		flextable.getFlexCellFormatter().setColSpan(6, 1, 4);
		// ///////////////////////////
		lbBillingStatus = new ListBox();
		lbBillingStatus.addItem("___","___");
		lbBillingStatus.addItem("Queued","0");
		lbBillingStatus.addItem("Billed","1");
		flextable.setWidget(8, 1, lbBillingStatus);
		// ////////////////////////////
		flextable.setWidget(9, 1, dateBox);
		// ////////////////////
		HorizontalPanel panelButtons = new HorizontalPanel();
		panelButtons.setSpacing(5);
		panelButtons.add(btnSearchClaim);
		panelButtons.add(btnCancel);
		panelButtons.add(btnAgingSummary);
		flextable.setWidget(10, 1, panelButtons);
		// / Preparing Columns for ClaimManager Table
		claimsManagerTable = new CustomTable();
		claimsManagerTable.setAllowSelection(false);
		claimsManagerTable.setSize("100%", "100%");
		claimsManagerTable.addColumn("Payer", "payer");
		claimsManagerTable.addColumn("Ins ID", "insured_id");
		claimsManagerTable.addColumn("Prov ID", "provider_id");
		claimsManagerTable.addColumn("Patient", "patient");
		claimsManagerTable.addColumn("Claim", "claim");
		claimsManagerTable.addColumn("Status", "status");
		claimsManagerTable.addColumn("Paid", "paid");
		claimsManagerTable.addColumn("Balance", "balance");

		// ////////////
		verticalPanel.add(flextable);
		verticalPanel.add(claimsManagerTable);
		populate();
		Util.setFocus(rbNoSearch);
	}

	private void populate() {
		// if (Util.getProgramMode() == ProgramMode.STUBBED){
		populateDataInProvider();
		populateDataInPayer();
		populateDataInPlanName();
		/*
		 * }else if (Util.getProgramMode() == ProgramMode.JSONRPC){ //TODO for
		 * JSONRPC
		 */// }
	}

	private void populateDataInPlanName() {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// //////////////////
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ClaimLog.aging_insurance_plans",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Window.alert(ex.toString() + "error1");

					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {

							if (Util.checkValidSessionResponse(response
									.getText())) {

								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");

								if (result != null) {

									Set<String> keys = result.keySet();
									Iterator<String> iter = keys.iterator();
									while (iter.hasNext()) {
										String keyInt = (String) iter.next();
										String key = keyInt.toString();
										String val = (String) result
												.get(keyInt);
										lbPlan.addItem(val, key);
									}
									// Adding ListProvider to the flexTable

								}
							}
						} else {
							Window.alert(response.toString() + "error2");
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
			// /////////////////////
		} else {
			lbPlan.addItem("___");
			lbPlan.addItem("First Plan");
			lbPlan.addItem("Second Plan");
			lbPlan.addItem("Third Plan");
			lbPlan.addItem("Fourth Plan");
			lbPlan.addItem("Fifth Plan");
			flextable.setWidget(4, 1, lbPlan);

		}
	}

	private void populateDataInPayer() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {

			// ///////////////////////
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ClaimLog.RebillDistinctPayers",
											// org.freemedsoftware.api.ClaimLog.RebillDistinctPayers
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Window.alert(ex.toString() + "error1");

					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {

						if (200 == response.getStatusCode()) {

							if (Util.checkValidSessionResponse(response
									.getText())) {

								HashMap<Integer, String> result = (HashMap<Integer, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<Integer,String>");

								if (result != null) {

									try {
										Set<Integer> keys = result.keySet();
										Iterator<Integer> iter = keys
												.iterator();
										while (iter.hasNext()) {
											Integer keyInt = (Integer) iter
													.next();
											String key = keyInt.toString();
											String val = (String) result
													.get(keyInt);

											lbPayer.addItem(val, key);
										}

									} catch (Exception e) {
										Window.alert(e.getMessage());
									}
									// Adding ListProvider to the flexTable
								}
							}
						} else {
							Window.alert(response.toString() + "error2");
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
			// //////////////////////////////

		} else {
			lbPayer.addItem("___");
			lbPayer.addItem("First Payer");
			lbPayer.addItem("Second Payer");
			lbPayer.addItem("Third Payer");
			lbPayer.addItem("Fourth Payer");
			lbPayer.addItem("Fifth Payer");
			flextable.setWidget(3, 1, lbPayer);
		}

	}

	private void populateDataInProvider() {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,URL.encode(Util.getJsonRequest("org.freemedsoftware.module.ProviderModule.InternalPicklist",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Window.alert(ex.toString() + "error1");

					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								HashMap<Integer, String> result = (HashMap<Integer, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<Integer,String>");
								if (result != null) {
									Set<Integer> keys = result.keySet();
									Iterator<Integer> iter = keys.iterator();
									while (iter.hasNext()) {
										Integer keyInt = (Integer) iter.next();
										String key = keyInt.toString();
										String val = (String) result
												.get(keyInt);
										lbProvider.addItem(val, key);
									}
									// Adding ListProvider to the flexTable

								}
							}
						} else {
							Window.alert(response.toString() + "error2");
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
			// ///////////////////////////
		} else {
			lbProvider.addItem("___");
			lbProvider.addItem("First");
			lbProvider.addItem("Second");
			lbProvider.addItem("Third");
			lbProvider.addItem("Fourth");
			lbProvider.addItem("Fifth");
			flextable.setWidget(1, 1, lbProvider);
		}
	}

	@SuppressWarnings("unchecked")
	protected void refreshSearch() {
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
		
			HashMap<String, String> criteria = new HashMap<String, String>();
			
				if(rb120Plus.getValue()){
					criteria.put("aging","120+");
					
				}
				if(rb91To120.getValue()){
					criteria.put("aging","91-120");
					  
				}
				if(rb61To90.getValue()){
					criteria.put("aging","61-90");
					  
				}
				if(rb31To60.getValue()){
					criteria.put("aging","31-60");
					  
				}
				if(rb0To30.getValue()){
					criteria.put("aging","0-30");
					  
				}
				
				
			if(lbProvider.getSelectedIndex() > 0 ){
			criteria.put("provider", lbProvider.getValue(lbProvider.getSelectedIndex()));
			}
			if(lbPayer.getSelectedIndex() > 0 ){
				criteria.put("payer", lbPayer.getValue(lbPayer.getSelectedIndex()));
				
			}
			if(lbPlan.getSelectedIndex() > 0 ){
				criteria.put("plan", lbPlan.getItemText(lbPlan.getSelectedIndex()));
				
			}
			criteria.put("first_name", txtFirstName.getValue());
			criteria.put("last_name", txtLastName.getValue());
			
			if(lbBillingStatus.getSelectedIndex() > 0 ){
				criteria.put("status", lbBillingStatus.getValue(lbBillingStatus.getSelectedIndex()));
				
			}
			//Check for date of Service.
			if(dateBox.getValue()== null || dateBox.getValue().equals("") ){
				
			}
			else{
				criteria.put("date", dateBox.getTextBox().getValue()); 
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
								
								if (result.length > 0) {
									claimsManagerTable.setVisible(true);   //FIXME old value: claimsManagerTable.setVisible(false);
								} else {
									claimsManagerTable.setVisible(true);
								}
								claimsManagerTable.loadData(result);
							} else {
								
								claimsManagerTable.setVisible(false);     //FIXME old value: claimsManagerTable.setVisible(true);
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
				claimsManagerTable.setVisible(true);
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
