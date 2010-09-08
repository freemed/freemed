/*
 * $Id: PatientSearchScreen.java 4643 2009-10-21 11:50:05Z Jeff $
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.LedgerPopup;
import org.freemedsoftware.gwt.client.widget.PatientTagWidget;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class AccountsReceivableScreen extends ScreenInterface {

	protected CustomTable sortableTable = null;

	protected Label sortableTableEmptyLabel = new Label();

//	protected PatientWidget wSmartSearch = null;
	
	protected TextBox patientfnTextBox=null;
	
	protected TextBox patientlnTextBox=null;
	
	protected CustomButton searchButton=null;
	
	protected CustomButton clearButton=null;

	protected CustomDatePicker wDos;
	
	protected CustomDatePicker wDos2;
	
	protected CustomDatePicker wDos3;
	
	protected PatientTagWidget tagWidget;

	private static List<AccountsReceivableScreen> accountsReceivableScreenList=null;

	protected FlexTable parentSearchTable;

	protected VerticalPanel searchCriteriaVPanel;

	protected FlexTable searchCriteriaTable;

	protected VerticalPanel currentCriteriaPanel;

	protected FlexTable existingCriteriaTable;
	
	protected HashMap<String, String> widgetTracker = new HashMap<String, String>();//It will contains widget and their respective row number so that we can use them for cretieria
	protected HashMap<String, Widget> widgetContainer = new HashMap<String, Widget>();

	protected HashMap<String,HorizontalPanel> widgetAddedToCreteria = new HashMap<String,HorizontalPanel>();//It will contains widget and their respective row number so that we can use them for cretieria
	
	protected HashMap<String, String> otherCreteriaMap = new HashMap<String, String>();

	protected PatientWidget patientWidget;

	protected SupportModuleWidget providerWidget,facilityModule;
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

		parentSearchTable = new FlexTable();
		parentSearchTable.setSize("100%", "100%");
		parentSearchTable.setBorderWidth(1	);
		parentSearchTable.getElement().getStyle().setProperty("borderCollapse", "collapse");
		verticalPanel.add(parentSearchTable);
		
		searchCriteriaVPanel = new VerticalPanel();
		searchCriteriaVPanel.setWidth("100%");
		searchCriteriaVPanel.setSpacing(5);
		parentSearchTable.setWidget(0, 0, searchCriteriaVPanel);
		parentSearchTable.getFlexCellFormatter().getElement(0, 0).setAttribute("width", "50%");
		parentSearchTable.getFlexCellFormatter().setVerticalAlignment(0, 0, HasVerticalAlignment.ALIGN_TOP);
		Label lbSearch=new Label("Search Criteria");
		lbSearch.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
		lbSearch.getElement().getStyle().setProperty("fontSize", "15px");
		lbSearch.getElement().getStyle().setProperty("textDecoration", "underline");
		lbSearch.getElement().getStyle().setProperty("fontWeight", "bold");
		searchCriteriaTable = new FlexTable();
		searchCriteriaVPanel.add(lbSearch);
		searchCriteriaVPanel.add(searchCriteriaTable);
		
		int row = 0;
		
		final Label smartLNameSearchLabel = new Label("Patient Last Name:  ");
		searchCriteriaTable.setWidget(row, 0, smartLNameSearchLabel);
		
		patientlnTextBox = new TextBox();
		searchCriteriaTable.setWidget(row, 1, patientlnTextBox);
		widgetTracker.put("Patient Last Name", row+":1:"+"last_name");
		widgetContainer.put("Patient Last Name", patientlnTextBox);
		
		final Label smartFNameSearchLabel = new Label("Patient First Name:  ");
		searchCriteriaTable.setWidget(row, 2, smartFNameSearchLabel);
		
		patientfnTextBox = new TextBox();
		searchCriteriaTable.setWidget(row, 3, patientfnTextBox);
		widgetTracker.put("Patient First Name", row+":3:"+"last_name");
		widgetContainer.put("Patient First Name", patientfnTextBox);
		row++;
		
		final Label ptFullName = new Label("Patient Full Name:  ");
		searchCriteriaTable.setWidget(row, 0, ptFullName);
		patientWidget = new PatientWidget();
		searchCriteriaTable.setWidget(row, 1, patientWidget);
		widgetTracker.put("Patient", row+":1:"+"patient");
		widgetContainer.put("Patient", patientWidget);
		row++;
		
		final Label fieldSearchLabel = new Label("Provider: ");
		searchCriteriaTable.setWidget(row, 0, fieldSearchLabel);

		providerWidget = new SupportModuleWidget("ProviderModule");
		searchCriteriaTable.setWidget(row, 1, providerWidget);
		widgetTracker.put("Provider", row+":1:"+"provider");
		widgetContainer.put("Provider", providerWidget);
		row++;

		final Label facilityLabel = new Label("Facility: ");
		searchCriteriaTable.setWidget(row, 0, facilityLabel);

		facilityModule = new SupportModuleWidget("FacilityModule");
		searchCriteriaTable.setWidget(row, 1, facilityModule);
		widgetTracker.put("Facility", row+":1:"+"facility");
		widgetContainer.put("Facility", facilityModule);
		row++;
		
		final Label dateOfServiceLabel = new Label("Date of Service: ");
		searchCriteriaTable.setWidget(row, 0, dateOfServiceLabel);

		wDos = new CustomDatePicker();
		searchCriteriaTable.setWidget(row, 1, wDos);
		widgetTracker.put("Date of Service", row+":1:"+"date_of");
		widgetContainer.put("Date of Service", wDos);
		row++;
		
		final Label transactionDateFrom = new Label("Transaction Date From: ");
		searchCriteriaTable.setWidget(row, 0, transactionDateFrom);
		
		wDos2 = new CustomDatePicker();
		searchCriteriaTable.setWidget(row, 1, wDos2);
		widgetTracker.put("Transaction Date From", row+":1:"+"date_from");
		widgetContainer.put("Transaction Date From", wDos2);
		
		final Label transactionDateTo = new Label("Transaction Date To: ");
		searchCriteriaTable.setWidget(row, 2, transactionDateTo);
		
		wDos3 = new CustomDatePicker();
		searchCriteriaTable.setWidget(row, 3, wDos3);
		widgetTracker.put("Transaction Date To", row+":3:"+"date_to");
		widgetContainer.put("Transaction Date To", wDos3);
		
		row++;
		
		final Label tagSearch = new Label("Tag Search: ");
		searchCriteriaTable.setWidget(row, 0, tagSearch);
		
		tagWidget = new PatientTagWidget();
		searchCriteriaTable.setWidget(row, 1, tagWidget);
		widgetTracker.put("Tag Search", row+":1:"+"tag");
		widgetContainer.put("Tag Search", tagWidget);
		
		row++;
		
		final HorizontalPanel buttonPanel = new HorizontalPanel();
		buttonPanel.setSpacing(5);
		searchButton = new CustomButton("Search",AppConstants.ICON_SEARCH);
		searchButton.addClickHandler(new ClickHandler(){
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
					refreshSearch();
			}
		}
		);
		buttonPanel.add(searchButton);
		clearButton = new CustomButton("Clear",AppConstants.ICON_CLEAR);
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

		
		searchCriteriaTable.setWidget(row, 1, buttonPanel);		
		
		currentCriteriaPanel = new VerticalPanel();
		currentCriteriaPanel.setWidth("100%");
		currentCriteriaPanel.setSpacing(5);
		Label lbExistingCri=new Label("Current Criteria");
		lbExistingCri.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
		lbExistingCri.getElement().getStyle().setProperty("fontSize", "15px");
		lbExistingCri.getElement().getStyle().setProperty("textDecoration", "underline");
		lbExistingCri.getElement().getStyle().setProperty("fontWeight", "bold");
		existingCriteriaTable = new FlexTable();
		currentCriteriaPanel.add(lbExistingCri);
//		currentCriteriaPanel.add(existingCriteriaTable);
		parentSearchTable.setWidget(0, 1, currentCriteriaPanel);
		parentSearchTable.getFlexCellFormatter().setVerticalAlignment(0, 1, HasVerticalAlignment.ALIGN_TOP);
		
		
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

	
		//creating search result table
		sortableTable = new CustomTable();
		sortableTable.setWidth("100%");
//		sortableTable.addColumn("", "selected");
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
		sortableTable.addColumn("Action","action");
		sortableTable.setIndexName("item");
		
		sortableTable.setTableRowClickHandler(new TableRowClickHandler(){
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				if(col==1){
					otherCreteriaMap.put("Date of Service", data.get("date_of")+":date_of:"+data.get("date_of"));
					refreshSearch();
				}else if(col==3){
					otherCreteriaMap.put("Provider", data.get("provider")+":provider:"+data.get("provider_id"));
					refreshSearch();
				}else if(col==4){
					otherCreteriaMap.put("Patient", data.get("patient")+":patient:"+data.get("patient_id"));
					refreshSearch();
				}else if(col==6){
					otherCreteriaMap.put("Item Type", data.get("item_type")+":type:"+data.get("item_type_id"));
					refreshSearch();
				}else if(col==7){
					otherCreteriaMap.put("Procedure", data.get("procedure_id")+":procedure:"+data.get("procedure_id"));
					refreshSearch();
				}
				
			}
		});
		
		sortableTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					final HashMap<String, String> data) {
					Integer id = Integer.parseInt(data.get("procedure_id"));
					if (columnName.compareTo("action") == 0) {
					
					HorizontalPanel actionPanel = new HorizontalPanel();
					actionPanel.setSpacing(5);
					HTML  htmlLedger= new HTML(
					"<a href=\"javascript:undefined;\" style='color:blue'>Ledger</a>");
					actionPanel.add(htmlLedger);
					
					htmlLedger.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(
										ClickEvent arg0) {
									LedgerPopup ledgerPopup = new LedgerPopup(data.get("procedure_id"),data.get("patient_id"),data.get("proc_cov_type"));
									ledgerPopup.removeAction(LedgerPopup.DEDUCTABLE);
									ledgerPopup.removeAction(LedgerPopup.COPAY);
									ledgerPopup.show();
									ledgerPopup.center();
								}

							});
					return actionPanel;
					
					}if (columnName.compareTo("selected") == 0) {
					CheckBox c = new CheckBox();
					c.addClickHandler(new ClickHandler() {
						@Override
						public void onClick(ClickEvent arg0) {
						}
					});
//					checkboxStack.put(c, id);
					return c;
				
				} else if (data.get("total_balance") != null) {
					Float balance = Float.parseFloat(data.get("total_balance"));
					Label label = new Label(data.get(columnName));
					if (balance==0) 
						label.getElement().getStyle().setColor("#0B6126");
					 else if (balance<0)
						label.getElement().getStyle().setColor("#FF0000");
					return label;
	
				} else {
					return (Widget) null;
				}
			}
		});

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
	public AccountsReceivableScreen getAccountsReceivableScreen(){
		return this;
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
			HashMap<String, String> criteria = populateCreteria();

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
									Util.showErrorMsg(getClass().getName(), "No record found!!!");
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

//	public HashMap<String, String> populateCreteria(){
//		HashMap<String, String> criteria = new HashMap<String, String>();
//		criteria.put("date", wDos.getTextBox().getValue());
//		criteria.put("provider", providerWidget.getStoredValue());
//		criteria.put("first_name", patientfnTextBox.getValue());
//		criteria.put("last_name", patientlnTextBox.getValue());
//		criteria.put("patient", patientWidget.getStoredValue());
//		return criteria;
//	}
	
	public HashMap<String, String> populateCreteria(){
		HashMap<String, String> criteria = new HashMap<String, String>();
		Iterator<String> iterator = widgetTracker.keySet().iterator();
		while(iterator.hasNext()){
			final String widgetLabel  = iterator.next();
			final Widget widget = widgetContainer.get(widgetLabel);
			String widgetValue = Util.getWidgetValue(widget);
			if(widgetValue!=null && !widgetValue.equals("") && !widgetValue.equals("0")){
				String rowColKey   = widgetTracker.get(widgetLabel);
				String []reqData = rowColKey.split(":");
				final Integer row = Integer.parseInt(reqData[0]);
				final Integer col = Integer.parseInt(reqData[1]);
				showHideWidget(widgetLabel, false);
				criteria.put(reqData[2], widgetValue);
				if(widgetAddedToCreteria.get(widgetLabel)==null){//If alreday in creteria then dont add 
					final HorizontalPanel horizontalPanel = new HorizontalPanel();
					currentCriteriaPanel.add(horizontalPanel);
					Label label = new Label(((Label)searchCriteriaTable.getWidget(row, col-1)).getText()/*+":"*/);
					label.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
					horizontalPanel.add(label);
					horizontalPanel.add(new Label(Util.getWidgetText(widget)));
					CustomButton button = new CustomButton("X");
					button.addClickHandler(new ClickHandler() {
						@Override
						public void onClick(ClickEvent arg0) {
//							Window.alert(arg0)
							currentCriteriaPanel.remove(horizontalPanel);
							widgetAddedToCreteria.remove(widgetLabel);
							showHideWidget(widgetLabel, true);
							Util.resetWidget(widget);
							refreshSearch();
						}
					});
					horizontalPanel.add(button);
					widgetAddedToCreteria.put(widgetLabel,horizontalPanel);
				}
			}
			
		}
		Iterator<String> OtherCretitr = otherCreteriaMap.keySet().iterator();
		while(OtherCretitr.hasNext()){
			final String widgetLabel = OtherCretitr.next();
			showHideWidget(widgetLabel, false);	
			final String []values = otherCreteriaMap.get(widgetLabel).split(":");
			final String labelValue = values[0];
			criteria.put(values[1], values[2]);
			if(widgetAddedToCreteria.get(widgetLabel)==null){
				final HorizontalPanel horizontalPanel = new HorizontalPanel();
				currentCriteriaPanel.add(horizontalPanel);
				Label label = new Label(widgetLabel+":");
				label.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				horizontalPanel.add(label);
				horizontalPanel.add(new Label(labelValue));
				CustomButton button = new CustomButton("X");
				button.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						currentCriteriaPanel.remove(horizontalPanel);
						otherCreteriaMap.remove(widgetLabel);
						widgetAddedToCreteria.remove(widgetLabel);
						showHideWidget(widgetLabel, true);
						refreshSearch();
					}
				});
				horizontalPanel.add(button);
				widgetAddedToCreteria.put(widgetLabel,horizontalPanel);
			}
		}
		return criteria;
	}

	public void showHideWidget(String widgetLabel,boolean visible){
		if(widgetContainer.get(widgetLabel)!=null){
			String rowColKey   = widgetTracker.get(widgetLabel);
			String []reqData = rowColKey.split(":");
			final Integer row = Integer.parseInt(reqData[0]);
			final Integer col = Integer.parseInt(reqData[1]);
			searchCriteriaTable.getWidget(row, col).setVisible(visible);
			searchCriteriaTable.getWidget(row, col-1).setVisible(visible);
		}
	}
	
	protected boolean validateForm() {
		String msg = new String("");
		//if (wProviderList.getSelectedIndex()<1) {
		//	msg += "Please specify provider." + "\n";
		//}
		
		if (!msg.equals("")) {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	public void clearForm() {
		patientfnTextBox.setText("");
		patientlnTextBox.setText("");
		providerWidget.clear();
		wDos.getTextBox().setValue("");
		wDos2.getTextBox().setValue("");
		wDos3.getTextBox().setValue("");
		patientlnTextBox.setFocus(true);
		patientWidget.clear();
		facilityModule.clear();
		tagWidget.clear();
		
		Iterator<String> iterator = widgetAddedToCreteria.keySet().iterator();
		while(iterator.hasNext()){
			String widgetLabel  = iterator.next();
			showHideWidget(widgetLabel, true);
			widgetAddedToCreteria.get(widgetLabel).removeFromParent();
		}
		widgetAddedToCreteria.clear();
		otherCreteriaMap.clear();
		refreshSearch();
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
