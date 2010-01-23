/*
 * $Id: PatientSearchScreen.java 4643 2009-10-21 11:50:05Z Jeff $
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
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomTable;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class AccountsReceivableScreen extends ScreenInterface {

	protected CustomTable sortableTable = null;

	protected Label sortableTableEmptyLabel = new Label();

//	protected PatientWidget wSmartSearch = null;
	
	protected TextBox patientfnTextBox=null;
	
	protected TextBox patientlnTextBox=null;
	
	protected Button searchButton=null;
	
	protected Button clearButton=null;

	protected ListBox wProviderList = null;

	protected CustomDatePicker wDos;

	private static List<AccountsReceivableScreen> accountsReceivableScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static AccountsReceivableScreen getInstance(){
		AccountsReceivableScreen accountsReceivableScreen=null; 
		
		if(accountsReceivableScreenList==null)
			accountsReceivableScreenList=new ArrayList<AccountsReceivableScreen>();
		if(accountsReceivableScreenList.size()<AppConstants.MAX_ACCOUNTRECIEVABLE_TABS)//creates & returns new next instance of PatientSearchScreen
			accountsReceivableScreenList.add(accountsReceivableScreen=new AccountsReceivableScreen());
		else //returns last instance of PatientSearchScreen from list 
			accountsReceivableScreen = accountsReceivableScreenList.get(AppConstants.MAX_SEARCH_TABS-1);
		return accountsReceivableScreen;
	}  

	public static boolean removeInstance(AccountsReceivableScreen accountsReceivableScreen){
		return accountsReceivableScreenList.remove(accountsReceivableScreen);
	}
	
	public AccountsReceivableScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		//First FlexTable for holding first name & last name
		final FlexTable patientCriteriaflexTable = new FlexTable();
		patientCriteriaflexTable.setWidth("40%");
		
		final Label criteriaLabel = new Label("Patient Criteria ");
		criteriaLabel.setStyleName("small-header-label");
		criteriaLabel.setWidth("40%");
		verticalPanel.add(criteriaLabel);
		
		final Label smartSearchLabel = new Label("Name(Last,First):  ");
		patientCriteriaflexTable.setWidget(1, 0, smartSearchLabel);

		patientlnTextBox = new TextBox();
		patientCriteriaflexTable.setWidget(1, 1, patientlnTextBox);
		patientfnTextBox = new TextBox();
		patientCriteriaflexTable.setWidget(1, 2, patientfnTextBox);
		
		verticalPanel.add(patientCriteriaflexTable);
		
		final Label claimCriteriaLabel = new Label("Claim Criteria ");
		claimCriteriaLabel.setStyleName("small-header-label");
		claimCriteriaLabel.setWidth("40%");
		verticalPanel.add(claimCriteriaLabel);
		
		//Second FlexTable for holding  claim Criteria fields provider,date
		final FlexTable claimCriteriaflexTable = new FlexTable();
		claimCriteriaflexTable.setWidth("27%");
		
		final Label fieldSearchLabel = new Label("Provider: ");
		claimCriteriaflexTable.setWidget(1, 0, fieldSearchLabel);

		wProviderList = new ListBox();
		claimCriteriaflexTable.setWidget(1, 1, wProviderList);
		wProviderList.setVisibleItemCount(1);
		
		//search & update providers list
		updateProviderList();

		final Label dateOfServiceLabel = new Label("Date of Service: ");
		claimCriteriaflexTable.setWidget(2, 0, dateOfServiceLabel);

		wDos = new CustomDatePicker();
		claimCriteriaflexTable.setWidget(2, 1, wDos);

		verticalPanel.add(claimCriteriaflexTable);
		
		final HorizontalPanel buttonPanel = new HorizontalPanel();
		buttonPanel.setSpacing(5);
		searchButton = new Button("Search");
		searchButton.addClickHandler(new ClickHandler(){
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
					refreshSearch();
			}
		}
		);
		buttonPanel.add(searchButton);
		clearButton = new Button("Clear");
		clearButton.addClickHandler(new ClickHandler(){
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				clearForm();
			}
		}
		);
		
		buttonPanel.add(clearButton);
		buttonPanel.setSpacing(5);

		
		claimCriteriaflexTable.setWidget(3, 1, buttonPanel);
		
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

	
		//creating search result table
		sortableTable = new CustomTable();
		sortableTable.setWidth("100%");
		sortableTable.addColumn("Svc Date", "date_of");
		sortableTable.addColumn("Acct Bal", "total_balance");
		sortableTable.addColumn("Provider", "provider");
		sortableTable.addColumn("Patient", "patient");
		sortableTable.addColumn("Item", "item");
		sortableTable.addColumn("Type", "item_type");
		sortableTable.addColumn("Svc", "procedure_id");
		sortableTable.addColumn("Date", "payment_date");
		sortableTable.addColumn("Adjs", "money_in");
		sortableTable.addColumn("Charges", "money_out");


		sortableTableEmptyLabel.setStylePrimaryName("freemed-MessageText");
		sortableTableEmptyLabel
				.setText("No patients found with the specified criteria.");
		sortableTableEmptyLabel.setVisible(true);

		verticalPanel.add(sortableTable);

		// Set visible focus *after* this is shown, otherwise it won't focus.
		try {
			patientfnTextBox.setFocus(true);
		} catch (Exception e) {
			GWT.log("Caught exception: ", e);
		}
		Util.setFocus(patientlnTextBox);
	}

	public void updateProviderList(){
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			wProviderList.addItem("---", "---");
			wProviderList.addItem("Provider 1", "ptid");
			wProviderList.addItem("Provider 2", "ssn");
			wProviderList.addItem("Provider 3", "dmv");
			wProviderList.addItem("Provider 4", "email");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProviderModule.InternalPicklist",
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
								HashMap<Integer, String> result = (HashMap<Integer, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<Integer,String>");
								if (result != null) {
									Set<Integer> keys = result.keySet();
									Iterator<Integer> iter = keys.iterator();

									List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
//									map.clear();
									wProviderList.addItem("---", "---");
									while (iter.hasNext()) {
										Integer keyInt = (Integer) iter.next();
										String key = keyInt.toString();
										String val = (String) result
												.get(keyInt);
										wProviderList.addItem(val, key);
//										addKeyValuePair(items, val, key);
									}
//									cb.onSuggestionsReady(r,
//											new SuggestOracle.Response(items));
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
	
	@SuppressWarnings("unchecked")
	protected void refreshSearch() {
	if(validateForm()){//validate form first
		sortableTable.clearData();
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			HashMap<String, String> a = new HashMap<String, String>();
			a.put("item_type", "item type1");
			a.put("item", "item 1");
			a.put("patient", "abc");
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
			sortableTable.loadData((HashMap<String, String>[]) l
					.toArray(new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			HashMap<String, String> criteria = new HashMap<String, String>();
			criteria.put("date", wDos.getTextBox().getValue());
			criteria.put("provider", wProviderList.getValue(wProviderList.getSelectedIndex()));
			criteria.put("first_name", patientfnTextBox.getValue());
			criteria.put("last_name", patientlnTextBox.getValue());

			String[] params = { JsonUtil.jsonify(criteria) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.Ledger.AgingReportQualified",
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
									sortableTableEmptyLabel.setVisible(false);
								} else {
									sortableTableEmptyLabel.setVisible(true);
									Window.alert("No record found!!!");
								}
								sortableTable.loadData(result);
							} else {
								Window.alert(response.toString());
								sortableTableEmptyLabel.setVisible(true);
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
				sortableTableEmptyLabel.setVisible(true);
			}
		} else {
			
			
			// TODO normal mode code goes here 
		}
		}
	}

	protected boolean validateForm() {
		String msg = new String("");
		if (wProviderList.getSelectedIndex()<1) {
			msg += "Please specify provider." + "\n";
		}
		
		if (msg != "") {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	public void clearForm() {
		patientfnTextBox.setText("");
		patientlnTextBox.setText("");
		wProviderList.setItemSelected(0, true);
		wDos.getTextBox().setValue("");
		patientlnTextBox.setFocus(true);
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
