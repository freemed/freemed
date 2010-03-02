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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.ReportingAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTimeBox;
import org.freemedsoftware.gwt.client.widget.Popup;
import org.freemedsoftware.gwt.client.widget.PopupView;
import org.freemedsoftware.gwt.client.widget.ProviderWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleListBox;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.SelectionEvent;
import com.google.gwt.event.logical.shared.SelectionHandler;
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
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;


public class CallInScreen extends ScreenInterface implements ClickHandler {

	VerticalPanel verticalPanelMenu = new VerticalPanel();
	VerticalPanel verticalPanelEntry = new VerticalPanel();

	protected Integer selectedEntryId;
	protected CustomTable callInTable;
	protected static String locale = "en_US";
	protected TabPanel tabPanel;

	protected HashMap<CheckBox, Integer> checkboxStack = new HashMap<CheckBox, Integer>();

	protected FlexTable flexTable = new FlexTable();
	// /////////////
	protected Label lblLastName;
	protected Label lblFirstName;
	protected Label lblMiddleName;
	protected Label lblDob;
	protected Label lblComplaint;
	protected Label lblFacility;
	protected Label lblPhysician;
	protected Label lblClaimCriteria;
	protected Label lblBillingStatus;
	protected Label lblDateOfService;
	// Declreaing TexBoxes for last and firt Name in the Claim Manager.
	protected TextBox txtLastName;
	protected TextBox txtFirstName;
	protected TextBox txtMiddleName;

	protected TextArea taComplaints;
	// DatePicker for date of service
	protected CustomDatePicker dateBox;// = new DateBox();

	protected TextBox txtHomePhone;

	protected TextBox txtWorkPhone;

	protected TextBox txtTookCall;

	protected FlexTable callinPatientDetail;
	protected Popup callinDetailPopup;

	// Declreaing Button
	protected Button btnAdd;	
	protected Button btnClear;
	protected ProviderWidget provider;
	protected SupportModuleWidget facility;

	// /////////////////
	private static List<CallInScreen> CallInScreenList = null;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well
	public static CallInScreen getInstance() {
		CallInScreen callInScreen = null;

		if (CallInScreenList == null)
			CallInScreenList = new ArrayList<CallInScreen>();
		if (CallInScreenList.size() < AppConstants.MAX_CALLIN_TABS) {// creates &
																	// returns
																	// new next
																	// instance
																	// of
																	// CallInScreen
			CallInScreenList.add(callInScreen = new CallInScreen());
		} else { // returns last instance of CallInScreen from list
			callInScreen = CallInScreenList.get(AppConstants.MAX_CALLIN_TABS - 1);
			callInScreen.populate();
		}
		return callInScreen;
	}

	public static boolean removeInstance(CallInScreen callInScreen){
		return CallInScreenList.remove(callInScreen);
	}
	
	public CallInScreen() {
		final boolean canDelete = CurrentState.isActionAllowed(AppConstants.DELETE, AppConstants.PATIENT_CATEGORY, AppConstants.CALL_IN);
		final boolean canWrite  = CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.PATIENT_CATEGORY, AppConstants.CALL_IN);
		final boolean canBook   = CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.SYSTEM_CATEGORY, AppConstants.SCHEDULER);
		final boolean canModify   = CurrentState.isActionAllowed(AppConstants.MODIFY, AppConstants.PATIENT_CATEGORY, AppConstants.CALL_IN);
    
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		tabPanel = new TabPanel();
		tabPanel.addSelectionHandler(new SelectionHandler<Integer>() {		
			@Override
			public void onSelection(SelectionEvent<Integer> event) {
				// TODO Auto-generated method stub
				 if (event.getSelectedItem() == 1)
					 txtLastName.setFocus(true);				
			}		
		});
		verticalPanel.add(tabPanel);

		/*
		 * final Label callInLabel = new Label("Call-in Patient Management.");
		 * callInLabel.setStyleName("large-header-label");
		 * verticalPanelMenu.add(callInLabel);
		 * callInLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		 */

		final HorizontalPanel menuButtonsPanel = new HorizontalPanel();
		menuButtonsPanel.setSpacing(1);
		if(canDelete || canWrite || canBook){
			final Button selectAllButton = new Button();
			menuButtonsPanel.add(selectAllButton);
			selectAllButton.setText("Select All");
			selectAllButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent wvt) {
					Iterator<CheckBox> iter = checkboxStack.keySet().iterator();
					while (iter.hasNext()) {
						CheckBox t = iter.next();
						t.setValue(true);
						callInTable.selectionAdd(checkboxStack.get(t).toString());
						// }
					}
				}
			});
		}
		if(canDelete || canWrite || canBook){
			final Button selectNoneButton = new Button();
			menuButtonsPanel.add(selectNoneButton);
			selectNoneButton.setText("Select None");
			selectNoneButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					Iterator<CheckBox> iter = checkboxStack.keySet().iterator();
					while (iter.hasNext()) {
						CheckBox t = iter.next();
						t.setValue(false);
						callInTable
								.selectionRemove(checkboxStack.get(t).toString());
					}
				}
			});
		}
		
		if(canDelete){
			final Button deleteButton = new Button();
			menuButtonsPanel.add(deleteButton);
			deleteButton.setText("Delete");
			deleteButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else if (Window
							.confirm("Are you sure you want to delete these item(s)?")) {
						List<String> slectedItems = callInTable.getSelected();
						Iterator<String> itr = slectedItems.iterator();// Get all
																		// selected
																		// items
																		// from
																		// custom
																		// table
						while (itr.hasNext())
							deleteEntry(Integer.parseInt(itr.next()));// delete
																		// messages
																		// one by
																		// one
						populate();
					}
				}
			});
		}
		if(canWrite){
			final Button enterButton = new Button();
			menuButtonsPanel.add(enterButton);
			enterButton.setText("Enter");
			enterButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else {
						List<String> slectedItems = callInTable.getSelected();
						Iterator<String> itr = slectedItems.iterator();// Get all
																		// selected
																		// items
																		// from
																		// custom
																		// table
						while (itr.hasNext()) {
							openPatientForm(Integer.parseInt(itr.next()));
						}
					}
				}
			});
		}

		if(canBook){
			final Button bookButton = new Button();
			menuButtonsPanel.add(bookButton);
			bookButton.setText("Book");
			bookButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else {
						List<String> slectedItems = callInTable.getSelected();
						Iterator<String> itr = slectedItems.iterator();// Get all
																		// selected
																		// items
																		// from
																		// custom
																		// table
						while (itr.hasNext()) {
							final StringEventDataDialog dialog = new StringEventDataDialog();
							dialog.setPatient(Integer.parseInt(itr.next()));
							dialog.show();
							dialog.center();
						}
					}
				}
			});
			
		if(canModify){
			final Button modifyButton = new Button();
			menuButtonsPanel.add(modifyButton);
			modifyButton.setText("Modify");
			modifyButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select an entry!");
					else if(callInTable.getSelectedCount() > 1)
						Window.alert("You can modify only a single entry at a time!");
					else {
						List<String> slectedItems = callInTable.getSelected();
						Iterator<String> itr = slectedItems.iterator();// Get all
																		// selected
																		// items
																		// from
																		// custom
																		// table
						tabPanel.selectTab(1);
						btnAdd.setText("Modify");
						selectedEntryId=Integer.parseInt(itr.next());
						modifyEntry(selectedEntryId);
					}
				}
			});
		}
			
		}
		
		verticalPanelMenu.add(menuButtonsPanel);
		callInTable = new CustomTable();
		verticalPanelMenu.add(callInTable);
		callInTable.setAllowSelection(false);
		callInTable.setSize("100%", "100%");
		// //what for is this used???To work on this
		callInTable.setIndexName("id");
		// ///
		if(canDelete || canWrite || canBook)
			callInTable.addColumn("Selected", "selected");
		callInTable.addColumn("Date", "call_date_mdy");
		callInTable.addColumn("Name", "name");
		callInTable.addColumn("Home Phone", "phone_home");
		callInTable.addColumn("Work Phone", "phone_work");
		callInTable.addColumn("Complaint", "complaint");

		callInTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col != 0 || !(canBook || canWrite || canDelete )) {
						final Integer callinId = Integer.parseInt(data
								.get("id"));
						callinDetailPopup=new Popup();
						callinDetailPopup.setPixelSize(500, 20);
						callinPatientDetail=new FlexTable();
						showCallinInfo(callinId);
						PopupView viewInfo=new PopupView(callinPatientDetail);
						callinDetailPopup.setNewWidget(viewInfo);
						callinDetailPopup.initialize();
					}
				} catch (Exception e) {
					GWT.log("Caught exception: ", e);
				}
			}
		});

		callInTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							HashMap<String, String> data) {
						Integer id = Integer.parseInt(data.get("id"));
						if (columnName.compareTo("selected") == 0) {
							CheckBox c = new CheckBox();
							c.addClickHandler(getCallInScreen());
							checkboxStack.put(c, id);
							return c;
						} else {
							return (Widget) null;
						}
					}
				});

		callinPatientDetail = new FlexTable();
		callinPatientDetail.setWidth("35%");
		tabPanel.add(verticalPanelMenu, "Menu");
		if(canWrite)
			tabPanel.add(createEntryTabBar(), "Entry");
		// tabPanel.add(new VerticalPanel(),"Entry");
		tabPanel.selectTab(0);
		// createEntryTabBar();

		// callInTable.formatTable(5);
		// callInTable.getFlexTable().setWidth("100%");

		// //////
		populate();
	}

	public CallInScreen getCallInScreen() {
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
			callInTable.selectionAdd(item.toString());
		} else {
			// selectedItems.remove((Object) item);
			callInTable.selectionRemove(item.toString());
		}
	}

	private VerticalPanel createContactInformation() {
		/* HorizontalPanel horContInformation= new HorizontalPanel(); */
		VerticalPanel verContInformation = new VerticalPanel();
		verContInformation.setStyleName("small-header-label");

		// contInfoFlexTable.setSize(width, height); FIXME
		Label lblContactInformation = new Label("Contact Information");
		verContInformation.add(lblContactInformation);
		verContInformation.setCellHorizontalAlignment(lblContactInformation,
				HasHorizontalAlignment.ALIGN_CENTER);

		FlexTable contInfoFlexTable = new FlexTable();
		Label lblHomePhone = new Label("Home Phone");
		Label lblWorkPhone = new Label("Work Phone");
		Label lblTookCall = new Label("Took Call");

		txtHomePhone = new TextBox();
		txtHomePhone.setWidth("150px");
		txtWorkPhone = new TextBox();
		txtWorkPhone.setWidth("150px");
		txtTookCall = new TextBox();
		txtTookCall.setWidth("150px");
		txtTookCall.setEnabled(false);
		txtTookCall.setText(CurrentState.getDefaultUser());

		contInfoFlexTable.setWidget(0, 0, lblHomePhone);
		contInfoFlexTable.setWidget(1, 0, lblWorkPhone);
		contInfoFlexTable.setWidget(2, 0, lblTookCall);
		contInfoFlexTable.setWidget(0, 1, txtHomePhone);
		contInfoFlexTable.setWidget(1, 1, txtWorkPhone);
		contInfoFlexTable.setWidget(2, 1, txtTookCall);

		verContInformation.add(contInfoFlexTable);

		return verContInformation;
	}

	private VerticalPanel createEntryTabBar() {

		HorizontalPanel horPanel = new HorizontalPanel();

		lblLastName = new Label("Last Name");
		lblFirstName = new Label("First Name");
		lblMiddleName = new Label("Middle Name");
		lblDob = new Label("Date of Birth");
		lblComplaint = new Label("Complaint");
		lblFacility = new Label("Facility");
		lblPhysician = new Label("Provider");

		// TextBoxs for FirsName and LastName
		txtFirstName = new TextBox();
		txtFirstName.setWidth("200px");
		txtLastName = new TextBox();
		txtLastName.setWidth("200px");
		txtMiddleName = new TextBox();
		txtMiddleName.setWidth("200px");

		facility = new SupportModuleWidget("FacilityModule");
		facility.setWidth("200px");
		provider = new ProviderWidget();
		provider.setWidth("200px");
		taComplaints = new TextArea();
		taComplaints.setCharacterWidth(22);
		taComplaints.setVisibleLines(5); // FIXME
		btnAdd = new Button("Add");
		btnAdd.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				saveForm();
			}
		});
		btnClear = new Button("Clear");
		btnClear.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				clearForm();
			}
		});

		// date for service's date and its simple format i;e without time.
		dateBox = new CustomDatePicker();

		// Adding all labels to the fexTable
		flexTable.setWidget(0, 0, lblLastName);
		flexTable.setWidget(1, 0, lblFirstName);
		flexTable.setWidget(2, 0, lblMiddleName);
		flexTable.setWidget(3, 0, lblDob);
		flexTable.setWidget(4, 0, lblComplaint);
		flexTable.setWidget(5, 0, lblFacility);
		flexTable.setWidget(6, 0, lblPhysician);
		// HorizontalPanel for Add , Clear , and Cancel Buttons

		// flexTable.setWidget(7, 1, panelButtons);
		flexTable.setWidget(0, 1, txtLastName);
		flexTable.setWidget(1, 1, txtFirstName);
		flexTable.setWidget(2, 1, txtMiddleName);
		flexTable.setWidget(3, 1, dateBox);
		flexTable.setWidget(4, 1, taComplaints);
		// flexTable.setWidget(0, 2, createContactInformation());
		// flexTable.getFlexCellFormatter().setRowSpan(0, 2, 4); /* Row span for
		// Payer Criteria */
		flexTable.setWidget(5, 1, facility);
		flexTable.setWidget(6, 1, provider);

		horPanel.add(flexTable);
		horPanel.add(createContactInformation());

		// verticalPanelEntry.add(flexTable);
		verticalPanelEntry.add(horPanel);

		FlexTable panelButtons = new FlexTable();
		panelButtons.setWidth("20%");
		panelButtons.setWidget(0, 25, btnAdd);
		panelButtons.setWidget(0, 26, btnClear);

		verticalPanelEntry.add(panelButtons);

		return verticalPanelEntry;
	}

	protected void showCallinInfo(Integer callinId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(callinId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Callin.GetDetailedRecord",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> data = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (data != null) {
									while (callinPatientDetail.getRowCount() > 0)
										callinPatientDetail.removeRow(0);
									callinPatientDetail.setWidget(0, 0,
											new Label("Name:"));
									callinPatientDetail.setWidget(0, 1,
											new Label(data.get("name")));

									callinPatientDetail.setWidget(1, 0,
											new Label("Date of Birth:"));
									callinPatientDetail.setWidget(1, 1,
											new Label(data.get("dob")));

									callinPatientDetail.setWidget(2, 0,
											new Label("complaint:"));
									callinPatientDetail.setWidget(2, 1,
											new Label(data.get("complaint")));

									callinPatientDetail.setWidget(3, 0,
											new Label("Home Phone:"));
									callinPatientDetail.setWidget(3, 1,
											new Label(data.get("phone_home")));

									callinPatientDetail.setWidget(4, 0,
											new Label("Work Phone:"));
									callinPatientDetail.setWidget(4, 1,
											new Label(data.get("phone_work")));

									callinPatientDetail.setWidget(5, 0,
											new Label("Facility:"));
									callinPatientDetail.setWidget(5, 1,
											new Label(data.get("facility")));

									callinPatientDetail.setWidget(6, 0,
											new Label("Provider:"));
									callinPatientDetail.setWidget(6, 1,
											new Label(data.get("physician")));

									callinPatientDetail.setWidget(7, 0,
											new Label("Call Date:"));
									callinPatientDetail.setWidget(7, 1,
											new Label(data.get("call_date")));

									callinPatientDetail.setWidget(8, 0,
											new Label("Took Call:"));
									callinPatientDetail.setWidget(8, 1,
											new Label(data.get("took_call")));

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// TODO NORMAL MODE STUFF
		}
	}
	
	protected void modifyEntry(Integer callinId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(callinId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Callin.GetDetailedRecord",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> data = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (data != null) {
									txtFirstName.setText(data.get("firstname"));
									txtMiddleName.setText(data.get("middlename"));
									txtLastName.setText(data.get("lastname"));
									
									
									taComplaints.setText(data.get("complaint"));									
									dateBox.setValue(data.get("dob"));									
									facility.setValue(Integer.parseInt(data.get("facilityid")));
									provider.setValue(Integer.parseInt(data.get("physicianid")));
									
									txtHomePhone.setText(data.get("phone_home"));
									txtWorkPhone.setText(data.get("phone_work"));
									txtTookCall.setText(data.get("took_call"));
								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// TODO NORMAL MODE STUFF
		}
	}

	protected void openPatientForm(final Integer callinId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(callinId) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.Callin.GetRecord",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> data = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (data != null) {
									PatientForm patientForm = new PatientForm();
									String fname = data.get("cifname");
									String lname = data.get("cilname");
									String mname = data.get("cimname");
									String dob = data.get("cidob");
									String provider = data.get("ciphysician");
									String homePhone = data.get("cihphone");
									String workPhone = data.get("ciwphone");
									patientForm.setInfoFromCallin(
											getCallInScreen(), callinId, lname,
											fname, mname, dob, Integer
													.parseInt(provider),
											homePhone, workPhone);
									Util.spawnTab(lname + "," + fname + " "
											+ mname, patientForm);
								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// TODO NORMAL MODE STUFF
		}
	}

	public void populate() {
		while (callinPatientDetail.getRowCount() > 0)
			// clearing detail of callin patient (given below the table)
			callinPatientDetail.removeRow(0);

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { locale };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL
							.encode(Util.getJsonRequest(
									"org.freemedsoftware.module.Callin.GetAll",
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
								callInTable.clearAllSelections();
								callInTable.loadData(result);
							} else {
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			getProxy().GetReports(locale,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] r) {
							callInTable.loadData(r);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	protected ReportingAsync getProxy() {
		ReportingAsync p = null;
		try {
			p = (ReportingAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Module.Reporting");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}

	public void saveForm() {
		if (validateForm()) {
			// Add callin info
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO STUBBED MODE STUFF
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				RequestBuilder builder=null;
				if(btnAdd.getText().equals("Add"))
				{
				String[] params = { JsonUtil.jsonify(populateHashMap(null)) };
				builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.Callin.add",
												params)));
				}
				else{
					String[] params = { JsonUtil.jsonify(populateHashMap(selectedEntryId)) };
					builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.Callin.mod",
													params)));
				}
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {	
								Integer r=null;
								r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");								
									if (r != null) {
									clearForm();
									populate();									
									CurrentState.getToaster().addItem(
											"Callin Form",
											"Entry successfully added.");									
								}else {
									r=(Boolean) JsonUtil.shoehornJson(
														JSONParser.parse(response.getText()),
												"Boolean")?1:0;
									if(r==1){
										clearForm();
											populate();	
											CurrentState.getToaster().addItem(
													"Callin Form",
													"Entry successfully modified.");
											btnAdd.setText("Add");
									}else{
										
									}
								}									
								
							} else {
								CurrentState.getToaster().addItem(
										"Callin Form", "Callin Form failed.");
							}
						}
					});
				} catch (RequestException e) {
				}
			} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
				// TODO GWT WORK
			}
		}
	}

	protected void clearForm() {

		txtLastName.setText("");
		txtFirstName.setText("");
		txtMiddleName.setText("");
		dateBox.getTextBox().setText("");
		taComplaints.setText("");
		facility.clear();
		provider.clear();

		txtHomePhone.setText("");
		txtWorkPhone.setText("");
		if(btnAdd.getText().equals("Modify"))
			btnAdd.setText("Add");
		txtLastName.setFocus(true);

	}

	protected boolean validateForm() {
		String msg = new String("");
		if (txtLastName.getText().length() < 2) {
			msg += "Please specify a last name." + "\n";
		}
		if (txtFirstName.getText().length() < 2) {
			msg += "Please specify a first name." + "\n";
		}
		if (dateBox.getTextBox().getText().length() < 10) {
			msg += "Please specify date of birth." + "\n";
		}

		if (msg != "") {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	protected HashMap<String, String> populateHashMap(Integer id ) {
		HashMap<String, String> m = new HashMap<String, String>();
		
		m.put((String) "id", String.valueOf(id));
		m.put((String) "cilname", (String) txtLastName.getText());
		m.put((String) "cifname", (String) txtFirstName.getText());
		m.put((String) "cimname", (String) txtMiddleName.getText());
		m.put((String) "cidob", (String) dateBox.getTextBox().getText());
		m.put((String) "cicomplaint", (String) taComplaints.getText());
		if (facility.getText().length() > 0)
			m.put((String) "cifacility", (String) facility.getStoredValue());
		if (provider.getText().length() > 0)
			m.put((String) "ciphysician", (String) provider.getStoredValue());

		m.put((String) "cihphone", (String) txtHomePhone.getText());
		m.put((String) "ciwphone", (String) txtWorkPhone.getText());
		m.put((String) "citookcall", (String) txtTookCall.getText());

		return m;
	}

	protected void deleteEntry(Integer callId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(callId) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.Callin.del", params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem("CallinScreen",
								"Failed to delete entry.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Boolean r = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Boolean");
								if (r != null) {
									CurrentState.getToaster().addItem(
											"CallinScreen", "Entry deleted.",
											Toaster.TOASTER_INFO);
									// populate(tag);
								}
							} else {
								CurrentState.getToaster().addItem(
										"CallinScreen",
										"Failed to delete entry.",
										Toaster.TOASTER_ERROR);
							}
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// TODO NORMAL MODE STUFF
		}
	}

	public class StringEventDataDialog extends DialogBox {

		private SupportModuleWidget patient = null;

		private SupportModuleWidget provider = null;

		private TextArea text = new TextArea();

		// private DateEditFieldWithPicker date;
		private CustomDatePicker date;

		private CheckBox wholeDay = new CheckBox();

		private HorizontalPanel time = new HorizontalPanel();

		private HorizontalPanel timePanel = new HorizontalPanel();

		private CustomTimeBox start;

		private CustomTimeBox end;

		private Button cancel = null;

		private Button ok = null;

		private SupportModuleListBox selectTemplate = null;

		/**
		 * 
		 * 
		 * @param newData
		 * @param newCommand
		 */

		public StringEventDataDialog() {
			super();

			this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);

			setText("New Appointment");
			
			date = new CustomDatePicker(new Date());
			start = new CustomTimeBox();
			end = new CustomTimeBox();

			start.setDate(new Date());
			
			Calendar cend = new GregorianCalendar();
			cend.setTime(new Date());
			cend.add(Calendar.MINUTE, 30);
			end.setDate(cend.getTime());
			

			final FlexTable table = new FlexTable();

			table.setWidget(0, 0, new Label("Date"));
			table.setWidget(0, 1, date);

			timePanel.add(start);
			timePanel.add(new Label("-"));
			timePanel.add(end);

			time.add(wholeDay);
			wholeDay.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					if(wholeDay.getValue())
						time.remove(timePanel);
					else
						time.add(timePanel);
				}
			});
			time.add(timePanel);

			table.setWidget(0, 2, time);
			table.getFlexCellFormatter().setHorizontalAlignment(0, 2,
					HorizontalPanel.ALIGN_LEFT);

			patient = new SupportModuleWidget("Callin");
			table.setWidget(1, 0, new Label("Patient"));
			table.setWidget(1, 1, patient);

			provider = new SupportModuleWidget();
			provider.setModuleName("ProviderModule");

			table.setWidget(2, 0, new Label("Provider"));
			table.setWidget(2, 1, provider);

			table.setWidget(3, 0, new Label("Description"));
			table.setWidget(3, 1, text);
			table.getFlexCellFormatter().setColSpan(1, 1, 2);

			cancel = new Button("Cancel");
			cancel.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					hide();
				}

			});
			cancel.setFocus(true);
			cancel.setAccessKey('c');

			ok = new Button("Ok");
			ok.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					if(!CurrentState.canBookAppoinment(start.getValue(date.getValue()), end.getValue(date.getValue()))){
						CurrentState.getToaster().addItem("Scheduler",
								"Can not book appointment in between("+CurrentState.BREAK_HOUR+":00 -"+(CurrentState.BREAK_HOUR+1)+":00) !",
								Toaster.TOASTER_ERROR);
						return;
					}


						setAppointment();
				}

			});
			ok.setFocus(true);
			ok.setAccessKey('o');

			final HorizontalPanel button = new HorizontalPanel();
			button.add(ok);

			button.add(new HTML(" "));
			button.add(cancel);
			table.setWidget(5, 1, button);
			setWidget(table);

			final Label templateLabel = new Label("Template");
			table.setWidget(4, 0, templateLabel);
			selectTemplate = new SupportModuleListBox("AppointmentTemplates",
					"Select a Template");
			table.setWidget(4, 1, selectTemplate);

			selectTemplate.initChangeListener(new Command() {
				public void execute() {
					updateFromTemplate(Integer.parseInt(selectTemplate
							.getWidgetValue()));
				}
			});

		}

		/**
		 * 
		 * @param i
		 *            The Index value of the Appointment-Template
		 */
		public void updateFromTemplate(Integer i) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO: STUBBED
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				// JSON-RPC
				String[] params = { JsonUtil.jsonify(i) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.AppointmentTemplates.GetRecord",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							JsonUtil
									.debug("Error on retrieving AppointmentTemplate");
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								if (response.getText().compareToIgnoreCase(
										"false") != 0) {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>");
									if (result != null) {

											Integer duration = Integer
													.parseInt(result
															.get("atduration"));
											Date date_start = start
													.getValue(new Date());
											Calendar c = new GregorianCalendar();
											c.setTime(date_start);
											c
													.add(
															Calendar.HOUR_OF_DAY,
															(int) Math
																	.ceil(duration / 60));
											c.add(Calendar.MINUTE,
													(duration % 60));
											end.setDate(c.getTime());

									}
								} else {
									JsonUtil
											.debug("Received dummy response from JSON backend");
								}
							} else {
								CurrentState.getToaster().addItem("Scheduler",
										"Failed to get scheduler items.",
										Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					CurrentState.getToaster().addItem("Scheduler",
							"Failed to get scheduler items.",
							Toaster.TOASTER_ERROR);
				}
			} else {
				// GWT-RPC
			}
		}
		public void setAppointment() {
			if(validateAppointmentForm()){
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// TODO: STUBBED
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					// JSON-RPC
					String[] params = { JsonUtil.jsonify(populateHashMap()) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.api.Scheduler.SetAppointment",
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								JsonUtil.debug("Error on saving Appointment");
							}
	
							@SuppressWarnings("unchecked")
							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									hide();
									CurrentState.getToaster().addItem(
											"CallinScheduler",
											"Appointment saved successfully.",
											Toaster.TOASTER_INFO);
								} else {
									CurrentState.getToaster().addItem("Scheduler",
											"Failed to save appointment.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						CurrentState.getToaster().addItem("Scheduler",
								"Failed to save appointment.",
								Toaster.TOASTER_ERROR);
					}
				} else {
					// GWT-RPC
				}
			}

		}
		public boolean validateAppointmentForm(){
			String msg = new String("");
			if (date.getTextBox().getText().length()<7) {
				msg += "Please specify date." + "\n";
			}
			if (patient.getValue() < 1 || patient.getText().length()<1) {
				msg += "Please specify patient." + "\n";
			}
			if (provider.getValue() < 1 || provider.getText().length()<1) {
				msg += "Please specify provider." + "\n";
			}

			if (!msg.equalsIgnoreCase("")) {
				Window.alert(msg);
				return false;
			}

			return true;
		}
		public HashMap<String, String> populateHashMap() {

			HashMap<String, String> d = new HashMap<String, String>();

			String calhour = "";
			String calminute = "";
			String calduration = "";

			if (wholeDay.getValue() == false) {
				Calendar cstart = new GregorianCalendar();
				cstart.setTime(start.getValue(date.getValue()));
				Calendar cend = new GregorianCalendar();
				cend.setTime(end.getValue(date.getValue()));

				Integer dur = (cend.get(Calendar.HOUR) - cstart
						.get(Calendar.HOUR));
				if (dur < 0) {
					dur = dur + 24;
				}
				dur = (dur * 60)
						+ (cend.get(Calendar.MINUTE) - cstart
								.get(Calendar.MINUTE));
				calhour = Integer.toString(cstart.get(Calendar.HOUR_OF_DAY));
				calminute = Integer.toString(cstart.get(Calendar.MINUTE));
				calduration = Integer.toString(dur);

			} else {
				calhour = "1";
				calminute = "0";
				calduration = "420";
			}

			d.put("caldateof", date.getTextBox().getValue());
			d.put("calhour", calhour);
			d.put("calminute", calminute);
			d.put("calduration", calduration);

			d.put("caltype", AppConstants.APPOINTMENT_TYPE_CALLIN_PATIENT);
			d.put("calpatient", patient.getValue().toString());
			d.put("calphysician", provider.getValue().toString());
			d.put("calprenote", text.getText());
			if(selectTemplate.getStoredValue()!=null)
				d.put("calappttemplate", selectTemplate.getStoredValue());

			return d;
		}

		public void setPatient(Integer patientId) {
			this.patient.setValue(patientId);
		}

		public void setProvider(Integer providerId) {
			this.provider.setValue(providerId);
		}

	}

	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}

}
