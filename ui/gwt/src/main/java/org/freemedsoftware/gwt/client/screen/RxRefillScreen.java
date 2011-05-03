/*
 * $Id: CallInScreen.java 4643 2009-10-21 11:50:05Z Fawad $
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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDialogBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.Popup;
import org.freemedsoftware.gwt.client.widget.ProviderWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.MultiWordSuggestOracle;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class RxRefillScreen extends ScreenInterface implements ClickHandler{
	
	public final static String moduleName = "RxRefillRequest";
	
	private PatientWidget patient = null;
	protected ProviderWidget provider;
	protected Integer patientId = new Integer(0);
	protected CustomTable rxRefillTable;
	protected HashMap<CheckBox, Integer> checkboxStack = new HashMap<CheckBox, Integer>();
	TextArea noteBox = null; 
	protected Integer selectedEntryId;
	protected Popup refillDetailPopup;
	protected final String className = "org.freemedsoftware.gwt.client.screen.RxRefillScreen";
	
	private static List<RxRefillScreen> RxRefillScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static RxRefillScreen getInstance(){
		RxRefillScreen rxRefillScreen=null; 
		
		if(RxRefillScreenList==null)
			RxRefillScreenList=new ArrayList<RxRefillScreen>();
		if(RxRefillScreenList.size()<AppConstants.MAX_RXREFILL_TABS){//creates & returns new next instance of RxRefillScreen
			RxRefillScreenList.add(rxRefillScreen=new RxRefillScreen());
		}else{ //returns last instance of RxRefillScreen from list 
			rxRefillScreen = RxRefillScreenList.get(AppConstants.MAX_RXREFILL_TABS-1);
			
		}
		return rxRefillScreen;
	}

	public static boolean removeInstance(RxRefillScreen rxRefillScreen){
		return RxRefillScreenList.remove(rxRefillScreen);
	}
	
	public RxRefillScreen() {
		super(moduleName);
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");
		verticalPanel.setSpacing(10);
		//top_Label_Horizontal_Panel starts
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final Label reRefillLabel = new Label("Prescription Refill Request.");
		horizontalPanel.add(reRefillLabel);
		reRefillLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		//top_Label_Horizontal_Panel ends
		
		//patient_TextField_Horizontal_Panel starts
		final HorizontalPanel horizontalPanel1 = new HorizontalPanel();
		verticalPanel.add(horizontalPanel1);
		horizontalPanel1.setSize("100%", "100%");
		
		final Label patientLabel = new Label("Patient:");
		horizontalPanel1.add(patientLabel);
		patientLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
		horizontalPanel1.setCellWidth(patientLabel, "5%");
		
		
		patient = new PatientWidget();
		patient.setWidth("250");
		patient.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				Integer val = ((PatientWidget) event.getSource()).getValue();
				patientId = val;
				// Log.debug("Patient value = " + val.toString());
				try {
					if (val.compareTo(new Integer(0)) != 0) {
						//spawnPatientScreen(val, wSmartSearch.getText());
						//clearForm();
					}
				} catch (Exception e) {
					// Don't do anything if no patient is declared
					GWT.log("Caught exception", e);
				}
			}
		});
		
		SuggestBox suggestbox = new SuggestBox(null);//new SuggestBox(createPatientsOracle());
		horizontalPanel1.add(patient);
		horizontalPanel1.setCellHorizontalAlignment(patient, HasHorizontalAlignment.ALIGN_LEFT);
		
		
		//patient_TextField_Horizontal_Panel ends
		
		//note_TextField_Horizontal_Panel starts
		final HorizontalPanel horizontalPanel2 = new HorizontalPanel();
		verticalPanel.add(horizontalPanel2);
		horizontalPanel2.setSize("100%", "100%");
		
		final Label noteLabel = new Label("Note:");
		horizontalPanel2.add(noteLabel);
		noteLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
		horizontalPanel2.setCellWidth(noteLabel, "5%");
		
		noteBox = new TextArea();
		noteBox.setPixelSize(250, 200);
		horizontalPanel2.add(noteBox);
		horizontalPanel2.setCellHorizontalAlignment(noteBox, HasHorizontalAlignment.ALIGN_LEFT);
		
		//note_TextField_Horizontal_Panel ends
		
		//buttons_Horizontal_Panel_Container starts
		final HorizontalPanel buttonPanelContainer = new HorizontalPanel();
		buttonPanelContainer.setWidth("30%");
		verticalPanel.add(buttonPanelContainer);
		
		//buttons_Horizontal_Panel starts
		final HorizontalPanel buttonPanel = new HorizontalPanel();
		buttonPanelContainer.add(buttonPanel);
		buttonPanelContainer.setCellHorizontalAlignment(buttonPanel, HasHorizontalAlignment.ALIGN_CENTER);
//		verticalPanel.setCellHorizontalAlignment(buttonPanel, HasHorizontalAlignment.ALIGN_RIGHT);
		
		
		final CustomButton submitButton = new CustomButton("Submit Request",AppConstants.ICON_ADD);
		
		submitButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				submitButton.setEnabled(false);

			
				
				// Add
				String[] params = { JsonUtil
						.jsonify(populateHashMap()) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.RxRefillRequest.add",
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
									
									
								
									if (200 == response.getStatusCode()) {
										Integer r = (Integer) JsonUtil
												.shoehornJson(
														JSONParser
																.parse(response
																		.getText()),
														"Integer");
										if (r != 0) {
											
											submitButton.setEnabled(true);
											noteBox.setText("");// clearing form
											
											horizontalPanel1.add(patient);//clearing form
											CurrentState
												.getToaster()
												.addItem(
														"RxRefillScreen",
														"RxRefill succefully added.");
										}
									} else {
										CurrentState
												.getToaster()
												.addItem(
														"RxRefillScreen",
														"Adding RxRefill failed.");
									}
								}


							});
				} catch (RequestException e) {
				}
				retrieveData();
			}
			
		});	
		buttonPanel.add(submitButton);
		
		final CustomButton cancelButton = new CustomButton("Cancel",AppConstants.ICON_CANCEL);
		buttonPanel.add(cancelButton);
		
		//buttons_Horizontal_Panel ends
		
		final HorizontalPanel buttonPanel2 = new HorizontalPanel();
		verticalPanel.add(buttonPanel2);
		final HorizontalPanel buttonPanelContainer2 = new HorizontalPanel();
		buttonPanel2.add(buttonPanelContainer2);
		
		if(CurrentState.getUserType().equalsIgnoreCase(AppConstants.USER_TYPE_PROVIDER)){
			final CustomButton refillButton= new CustomButton("RxRefill");
			buttonPanelContainer2.add(refillButton);
			
			refillButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (rxRefillTable.getSelectedCount() < 1)
						Window.alert("Please select an entry!");
					else {
						List<String> slectedItems = rxRefillTable.getSelected();
						Iterator<String> itr = slectedItems.iterator();// Get all
																		// selected
																		// items
																		// from
																		// custom
																		// table
						
	//					btnAdd.setText("Modify");
						selectedEntryId=Integer.parseInt(itr.next());
						showRefillPopup();
					}
				}
			});
		}
		
		rxRefillTable = new CustomTable();
		rxRefillTable.setSize("100%", "100%");
		verticalPanel.add(rxRefillTable);
		if(CurrentState.getUserType().equalsIgnoreCase(AppConstants.USER_TYPE_PROVIDER))
			rxRefillTable.addColumn("Selected", "selected");
		rxRefillTable.addColumn("Date", "stamp"); // col 0
		rxRefillTable.addColumn("User", "user"); // col 1
		rxRefillTable.addColumn("Patient", "patient"); // col 2
//		rxRefillTable.addColumn("RX Orig", "rxorig"); // col 3
		rxRefillTable.addColumn("Note", "note"); // col 4
		rxRefillTable.addColumn("Approved", "approved");// col 5
		rxRefillTable.addColumn("locked", "locked"); // col 6
		retrieveData();
		rxRefillTable
		.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				Integer id = Integer.parseInt(data.get("id"));
				if(CurrentState.getUserType().equalsIgnoreCase(AppConstants.USER_TYPE_PROVIDER)){
					if (columnName.compareTo("selected") == 0) {
						CheckBox c = new CheckBox();
						c.addClickHandler(getRxRefillScreen());
						checkboxStack.put(c, id);
						return c;
					} 
				}
				
				return (Widget) null;
				
			}
		});
		Util.setFocus(patient);		
	}

	
	MultiWordSuggestOracle createPatientsOracle()
	{
	    MultiWordSuggestOracle oracle = new MultiWordSuggestOracle();

	    oracle.add("Afghanistan");
	    oracle.add("Bermuda");
	    oracle.add("Croatia");
	    oracle.add("Djibouti");
	    oracle.add("Eritrea");
	    oracle.add("Zimbabwe");

	    return oracle;
	}

	/**
	 * Populate hash from form to be fed into the RPC routines.
	 */
	/*protected HashMap<String, String> populateHashMap() {
		HashMap<String, String> m = new HashMap<String, String>();

		m.put((String) "patient", patientId.toString());
		m.put((String) "provider", "");
		m.put((String) "rxorig", "");
		m.put((String) "note", noteBox.getText());
		m.put((String) "approved", "");
		m.put((String) "user", CurrentState.getUserConfig("user"));
		//Window.alert(CurrentState.getUserConfig("user"));
		
		return m;
	}*/

	protected HashMap<String, String> populateHashMap() {
		HashMap<String, String> m = new HashMap<String, String>();

		if(CurrentState.getUserConfig("user")!=null)
			m.put((String) "user", CurrentState.getUserConfig("user").toString());
		m.put((String) "patient", patientId.toString());
		m.put((String) "provider", "");
		m.put((String) "rxorig", noteBox.getText());
		m.put((String) "note", "");
		m.put((String) "approved", "");
		
		//Window.alert(CurrentState.getUserConfig("user"));
		
		return m;
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
	
	public void retrieveData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Runs in STUBBED MODE => Feed with Sample Data
			// HashMap<String, String>[] sampleData = getSampleData();
			// loadData(sampleData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			rxRefillTable.showloading(true);
			// Use JSON-RPC to retrieve the data
			
			String[] requestparams = {};

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.RxRefillRequest.GetAll",
											requestparams)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						JsonUtil.debug(request.toString());
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String, String>[] data = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null) {
								loadData(data);
							}else{
								rxRefillTable.showloading(true);
							}
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}

	}
	
	protected void loadData(HashMap<String, String>[] data) {
		rxRefillTable.loadData(data);
	}
	
	public RxRefillScreen getRxRefillScreen() {
		return this;
	}
	
	public void onClick(ClickEvent evt) {
		Widget w = (Widget) evt.getSource();
		if (w instanceof CheckBox) {
			Integer id = checkboxStack.get(w);
			handleClickForItemCheckbox(id, (CheckBox) w);
		}
	}
	
	protected void handleClickForItemCheckbox(Integer item, CheckBox c) {
		// Add or remove from itemlist
		if (c.getValue()) {
			// selectedItems.add((Integer) item);
			rxRefillTable.selectionAdd(item.toString());
		} else {
			// selectedItems.remove((Object) item);
			rxRefillTable.selectionRemove(item.toString());
		}
	}
	
	protected void showRefillPopup(){
		refillDetailPopup=new Popup();
		refillDetailPopup.setPixelSize(500, 20);
		final CustomDialogBox dialogBox=new CustomDialogBox();
		final VerticalPanel vpanel=new VerticalPanel();
		
		final HorizontalPanel horizontalPanel1 = new HorizontalPanel();
		horizontalPanel1.setSize("100%", "100%");
		vpanel.add(horizontalPanel1);
		
		final Label patientLabel = new Label("Provider: ");
		horizontalPanel1.add(patientLabel);
		patientLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
		horizontalPanel1.setCellWidth(patientLabel, "60px");
		
		provider = new ProviderWidget();
		provider.setWidth("250");
		provider.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				Integer val = ((PatientWidget) event.getSource()).getValue();
				patientId = val;
				// Log.debug("Patient value = " + val.toString());
				try {
					if (val.compareTo(new Integer(0)) != 0) {
						//spawnPatientScreen(val, wSmartSearch.getText());
						//clearForm();
					}
				} catch (Exception e) {
					// Don't do anything if no patient is declared
					GWT.log("Caught exception", e);
				}
			}
		});
		horizontalPanel1.add(provider);
		
		final HorizontalPanel horizontalPanel2 = new HorizontalPanel();
		horizontalPanel2.setSize("100%", "100%");
		
		vpanel.add(horizontalPanel2);
		final Label noteLabel = new Label("Note:");
		horizontalPanel2.add(noteLabel);
		noteLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);
		horizontalPanel2.setCellWidth(noteLabel, "60px");
		
		noteBox = new TextArea();
		noteBox.setPixelSize(250, 200);
		horizontalPanel2.add(noteBox);
		horizontalPanel2.setCellHorizontalAlignment(noteBox, HasHorizontalAlignment.ALIGN_LEFT);
		
		final HorizontalPanel buttonContainer = new HorizontalPanel();
		final CustomButton sendButton= new CustomButton("Send");
		buttonContainer.add(sendButton);
		buttonContainer.setSize("50", "50");
		sendButton.getElement().setAttribute("style", "float:left");
		
		sendButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				updateRefillTable(selectedEntryId, provider.getValue());
				dialogBox.hide();
			}
		});
		vpanel.add(sendButton);
		
		//PopupView viewInfo=new PopupView(vpanel);
		//refillDetailPopup.setNewWidget(viewInfo);
		//refillDetailPopup.initialize();
		
		
		dialogBox.setContent(vpanel);
		dialogBox.center();
		dialogBox.show();		
	}
	
	
	public void updateRefillTable(Integer id, Integer provider) {
		ArrayList<String> params=new ArrayList<String>();
		params.add(id.toString());
		params.add(provider.toString());
		params.add(noteBox.getText());
		
		Util.callModuleMethod("RxRefillRequest", "updateRecord", params, new CustomRequestCallback() {
			@Override
			public void onError() {
			}

			@Override
			public void jsonifiedData(Object data) {
				if (data != null) {
					HashMap<String, String> [] result = (HashMap<String, String>[]) data;
					if(result!=null){
						
					}
				}
			}
		}, "HashMap<String,String>[]");
		
		refillDetailPopup.hide();
		
		Util.showInfoMsg(className, "Request sent to provider.");
	}
	
}
