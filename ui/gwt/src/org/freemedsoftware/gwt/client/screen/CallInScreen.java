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
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomCommand;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.ReportingAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomDialogBox;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.EventsWidget;
import org.freemedsoftware.gwt.client.widget.PatientCoverages;
import org.freemedsoftware.gwt.client.widget.Popup;
import org.freemedsoftware.gwt.client.widget.PopupView;
import org.freemedsoftware.gwt.client.widget.ProviderWidget;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.EventData;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
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
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;


public class CallInScreen extends ScreenInterface implements ClickHandler {

	VerticalPanel verticalPanelMenu = new VerticalPanel();

	protected VerticalPanel entryVPanel = null;
	
	VerticalPanel basicForm = null;
	
	protected Integer selectedEntryId;
	protected CustomTable callInTable;
	protected static String locale = "en_US";
	protected TabPanel tabPanel;

	protected HashMap<CheckBox, Integer> checkboxStack = new HashMap<CheckBox, Integer>();

	protected HashMap<String, Widget> basicFormFields = new HashMap<String, Widget>(); 

	// Declreaing Button
	protected CustomButton btnAdd;	
	protected CustomButton btnClear;

	// /////////////////
	
	protected CustomDialogBox searchDialogBox = null;
	
	protected CustomListBox formSelection = new CustomListBox();
	
	private static List<CallInScreen> CallInScreenList = null;

	public final static String ModuleName =  "Callin";
	
	protected PatientCoverages callinPatientCoverages = null;
	
	protected TabPanel basicFormEntryTabPanel;

	protected HashMap<String, String> searchCriteria; 

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
			callInScreen.populate(null);
		}
		return callInScreen;
	}

	public static boolean removeInstance(CallInScreen callInScreen){
		return CallInScreenList.remove(callInScreen);
	}
	
	public CallInScreen() {
		super(ModuleName);
		final boolean canBook   = CurrentState.isActionAllowed(SchedulerWidget.moduleName, AppConstants.WRITE);
    
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
				 if (event.getSelectedItem() == 1 && formSelection.getWidgetValue().equals("Basic"))
					 ((TextBox)basicFormFields.get("cifname")).setFocus(true);				
			}		
		});
		verticalPanel.add(tabPanel);

		/*
		 * final Label callInLabel = new Label("Call-in Patient Management.");
		 * verticalPanelMenu.add(callInLabel);
		 * callInLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		 */

		final HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setWidth("100%");
		verticalPanelMenu.add(headerHPanel);
		
		final HorizontalPanel menuButtonsPanel = new HorizontalPanel();
		menuButtonsPanel.setSpacing(1);
		headerHPanel.add(menuButtonsPanel);
		if(canDelete || canWrite || canBook){
			final CustomButton selectAllButton = new CustomButton("Select All",AppConstants.ICON_SELECT_ALL);
			menuButtonsPanel.add(selectAllButton);
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
			final CustomButton selectNoneButton = new CustomButton("Select None",AppConstants.ICON_SELECT_NONE);
			menuButtonsPanel.add(selectNoneButton);
			selectNoneButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					clearSelection();
				}
			});
		}
		
		if(canDelete){
			final CustomButton deleteButton = new CustomButton("Delete",AppConstants.ICON_DELETE);
			menuButtonsPanel.add(deleteButton);
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
						int totalItems = slectedItems.size();
						int curItem = 1;
						while (itr.hasNext())
							deleteEntry(Integer.parseInt(itr.next()),curItem++,totalItems);// delete
																		// messages
																		// one by
																		// one
					}
				}
			});
		}
		if(canWrite){
			final CustomButton enterButton = new CustomButton("Create Patient",AppConstants.ICON_ADD_PERSON);
			menuButtonsPanel.add(enterButton);
			enterButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else if(callInTable.getSelectedCount() > 1)
						Window.alert("You can create only a single patient at a time!");
					else {
						List<String> slectedItems = callInTable.getSelected();
						Integer id = Integer.parseInt(slectedItems.get(0));
						final HashMap<String, String> data = callInTable.getDataById(id);
						if(data.get("archive")==null || data.get("archive").compareTo("0")==0){
//							openPatientForm(id,getCallInScreen());
							callinConvertFromPatient(id,new CustomCommand() {
								@Override
								public void execute(Object id) {
									populate(null);
									Util.spawnPatientScreen((Integer)id, data.get("name"));
								}
							
							});
						}else{
							Window.alert("You can't create patient of archived enteries!");	
						}
					}
				}
			});
		}

		if(canBook){
			final CustomButton bookButton = new CustomButton("Book",AppConstants.ICON_BOOK_APP);
			menuButtonsPanel.add(bookButton);
			bookButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else if(callInTable.getSelectedCount() > 1)
						Window.alert("You can Book only a single appointment at a time!");
					else {
						List<String> slectedItems = callInTable.getSelected();
						Integer id = Integer.parseInt(slectedItems.get(0));
						HashMap<String, String> data = callInTable.getDataById(id);
						if(data.get("archive")==null || data.get("archive").compareTo("0")==0){
							SchedulerScreen schedulerScreen = SchedulerScreen.getInstance();
							EventData eventData = schedulerScreen.getSchedulerWidget().getNewExternalDataEvent();
							eventData.setPatientId(id);
							if(data.get("provider")!=null)
								eventData.setProviderId(Integer.parseInt(data.get("provider")));
							eventData.setResourceType(AppConstants.APPOINTMENT_TYPE_CALLIN_PATIENT);
							schedulerScreen.getSchedulerWidget().setExternalDataEvent(eventData);
							Util.spawnTab(AppConstants.SCHEDULER,schedulerScreen);
						}else{
							Window.alert("You can't Book archived enteries!");	
						}

						
					}
				}
			});
			
		if(canModify){
			final CustomButton modifyButton = new CustomButton("Modify",AppConstants.ICON_MODIFY);
			menuButtonsPanel.add(modifyButton);
			modifyButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select an entry!");
					else if(callInTable.getSelectedCount() > 1)
						Window.alert("You can modify only a single entry at a time!");
					else {
						List<String> slectedItems = callInTable.getSelected();
						Integer id = Integer.parseInt(slectedItems.get(0));
						selectedEntryId = id;
						HashMap<String, String> data = callInTable.getDataById(id);
						if(data.get("archive")==null || data.get("archive").compareTo("0")==0){
							tabPanel.selectTab(1);
							selectedEntryId = id;
							modifyEntry(selectedEntryId);
						}else{
							Util.confirm("You can not modify archived Record. Do you want to un-archive this Record ?", new Command() {
							
								@Override
								public void execute() {
									// TODO Auto-generated method stub
									unarchiveEntry(selectedEntryId);
								}
							}, null);	
						}
					}
				}
			});
		}
			
		}
		
		if(canWrite){
			final CustomButton addEventButton = new CustomButton("Add Event",AppConstants.ICON_ADD);
			menuButtonsPanel.add(addEventButton);
			addEventButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (callInTable.getSelectedCount() < 1)
						Window.alert("Please select an entry!");
					else if(callInTable.getSelectedCount() > 1)
						Window.alert("You can modify only a single entry at a time!");
					else {
						List<String> slectedItems = callInTable.getSelected();
							Integer id = Integer.parseInt(slectedItems.get(0));
							HashMap<String, String> data = callInTable.getDataById(id);
							String name = null;
							if(data!=null && data.get("name")!=null){
								name = data.get("name");
							}
							openAddEventForm(id,name);
					}
				}
			});
		}
		

		if(canRead){
			final CustomButton searchButton = new CustomButton("Search",AppConstants.ICON_SEARCH);
			menuButtonsPanel.add(searchButton);
			searchButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					initSearchPopup();	
				}
			});
		}
		
		callInTable = new CustomTable();
		verticalPanelMenu.add(callInTable);
		callInTable.setAllowSelection(false);
		callInTable.setSize("100%", "100%");
		// //what for is this used???To work on this
		callInTable.setIndexName("id");
		// ///
		if(canDelete || canWrite || canBook)
			callInTable.addColumn("", "selected");
		callInTable.addColumn("Date", "call_date_mdy");
		callInTable.addColumn("Name", "name");
		callInTable.addColumn("Contact Phone", "contact_phone");
		callInTable.addColumn("Coverage", "coverage");
		callInTable.addColumn("Complaint", "complaint");

		callInTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col != 0 || !(canBook || canWrite || canDelete )) {
						final Integer callinId = Integer.parseInt(data
								.get("id"));
						showCallinInfo(callinId);

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
						} else if(data.get("archive")!=null && data.get("archive").compareTo("1") == 0){
								Label label = new Label(data.get(columnName));
								label.setStyleName(AppConstants.STYLE_LABEL_ALERT);
								return label;
						}
						return (Widget) null;
					}
				});

		tabPanel.add(verticalPanelMenu, "Menu");
		if(canWrite){
//			tabPanel.add(createEntryTabBar(), "Entry");
			entryVPanel = new VerticalPanel();
			tabPanel.add(entryVPanel, "Entry");
			final HorizontalPanel selectionHPanel = new HorizontalPanel();
			selectionHPanel.setStyleName(AppConstants.STYLE_LABEL_HEADER_SMALL);
			entryVPanel.add(selectionHPanel);
			selectionHPanel.setSpacing(5);
			final Label selectionLabel = new Label("Select Form Type:");
			selectionHPanel.add(selectionLabel);
			formSelection = new CustomListBox();
			selectionHPanel.add(formSelection);
			formSelection.addItem("","");
			formSelection.addItem("Basic Entry Form","Basic");
			formSelection.addChangeHandler(new ChangeHandler(){
				public void onChange(ChangeEvent arg0) {
					handleFormSelection();
				}
			});
		}
		// tabPanel.add(new VerticalPanel(),"Entry");
		tabPanel.selectTab(0);
		// createEntryTabBar();

		// callInTable.formatTable(5);
		// callInTable.getFlexTable().setWidth("100%");

		// //////
		populate(null);
	}

	public void callinConvertFromPatient(Integer id,final CustomCommand onSuccess){
		Util.callModuleMethod(ModuleName, "callinConvertFromPatient", id, new CustomRequestCallback(){
		
			@Override
			public void onError() {
				Util.showErrorMsg(ModuleName, "Failed To Create Patient!!!");
			}
		
			@Override
			public void jsonifiedData(Object id) {
				if(id!=null && ((Integer)id)>0){
					if(onSuccess!=null)
						onSuccess.execute(id);
					Util.showInfoMsg(ModuleName, "Patient Created Successfully!!!");
				}else
					Util.showErrorMsg(ModuleName, "Failed To Create Patient!!!");
			}
		}, "Integer");
	}

	protected void clearSelection(){
		Iterator<CheckBox> iter = checkboxStack.keySet().iterator();
		while (iter.hasNext()) {
			CheckBox t = iter.next();
			t.setValue(false);
			callInTable
					.selectionRemove(checkboxStack.get(t).toString());
		}
	}
	
	protected void handleFormSelection(){
		if(formSelection.getStoredValue().equals("Basic")){
			if(basicForm==null)
				basicForm = createBasicEntryForm();
			basicFormEntryTabPanel.selectTab(0);
			entryVPanel.add(basicForm);
			if(basicForm!=null)	
				entryVPanel.remove(basicForm);
		}else if(formSelection.getStoredValue().equals("")){
			if(basicForm!=null)		
				entryVPanel.remove(basicForm);
			
		}
	}
	protected void showPopupAfterSaveCallin(final Integer id){
		final Popup popup = new Popup();

		VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setWidth("100%");
		CustomButton bookAppointment = new CustomButton("Book Appointment",
				AppConstants.ICON_BOOK_APP);
		bookAppointment.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				popup.hide();
				SchedulerScreen schedulerScreen = SchedulerScreen.getInstance();
				EventData eventData = schedulerScreen.getSchedulerWidget().getNewExternalDataEvent();
				eventData.setPatientId(id);
				eventData.setResourceType(AppConstants.APPOINTMENT_TYPE_CALLIN_PATIENT);
				schedulerScreen.getSchedulerWidget().setExternalDataEvent(eventData);
				Util.spawnTab(AppConstants.SCHEDULER,schedulerScreen);
			}
		});
		verticalPanel.add(bookAppointment);
		verticalPanel.setCellHorizontalAlignment(bookAppointment,
				HasHorizontalAlignment.ALIGN_CENTER);
		CustomButton showList = new CustomButton("Show List",
				AppConstants.ICON_VIEW);

		showList.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				popup.hide();
				populate(null);
				formSelection.setWidgetValue("");
				handleFormSelection();
				tabPanel.selectTab(0);
			}
		});
		verticalPanel.add(showList);
		verticalPanel.setCellHorizontalAlignment(showList,
				HasHorizontalAlignment.ALIGN_CENTER);
		PopupView popupView = new PopupView(verticalPanel);

		popup.setNewWidget(popupView);
		popup.initialize();
		
		formSelection.setWidgetValue("");
		handleFormSelection();
		clearForm();
		populate(null);
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
		verContInformation.setStyleName(AppConstants.STYLE_LABEL_HEADER_SMALL);

		// contInfoFlexTable.setSize(width, height); FIXME
		Label lblContactInformation = new Label("Contact Information");
		verContInformation.add(lblContactInformation);
		verContInformation.setCellHorizontalAlignment(lblContactInformation,
				HasHorizontalAlignment.ALIGN_CENTER);

		FlexTable contInfoFlexTable = new FlexTable();
		int row = 0;
		final Label lblHomePhone = new Label("Home Phone");
		contInfoFlexTable.setWidget(row, 0, lblHomePhone);
		final  TextBox txtHomePhone = new TextBox();
		txtHomePhone.setWidth("150px");
		contInfoFlexTable.setWidget(row, 1, txtHomePhone);
		basicFormFields.put("cihphone", txtHomePhone);
		
		row++;
		
		final Label lblWorkPhone = new Label("Work Phone");
		contInfoFlexTable.setWidget(row, 0, lblWorkPhone);
		final TextBox txtWorkPhone = new TextBox();
		txtWorkPhone.setWidth("150px");
		contInfoFlexTable.setWidget(row, 1, txtWorkPhone);
		basicFormFields.put("ciwphone", txtWorkPhone);
		
		row++;
		
		final Label lblTookCall = new Label("Took Call");
		contInfoFlexTable.setWidget(row, 0, lblTookCall);
		final TextBox txtTookCall = new TextBox();
		txtTookCall.setWidth("150px");
		txtTookCall.setEnabled(false);
		txtTookCall.setText(CurrentState.getDefaultUser());
		contInfoFlexTable.setWidget(row, 1, txtTookCall);
		
		

		verContInformation.add(contInfoFlexTable);

		return verContInformation;
	}

	private VerticalPanel createBasicEntryForm() {

		final VerticalPanel verticalPanelEntry = new VerticalPanel();
		verticalPanelEntry.setWidth("100%");
		
		basicFormEntryTabPanel = new TabPanel();
		basicFormEntryTabPanel.setWidth("100%");
		verticalPanelEntry.add(basicFormEntryTabPanel);
		
		HorizontalPanel horPanel = new HorizontalPanel();
		basicFormEntryTabPanel.add(horPanel,"Contact");
		horPanel.setWidth("");
		
		final FlexTable flexTable = new FlexTable();
		horPanel.add(flexTable);
		int row = 0;
		
		final Label lblLastName = new Label("Last Name");
		flexTable.setWidget(row, 0, lblLastName);
		final TextBox txtLastName = new TextBox();
		txtLastName.setWidth("200px");
		flexTable.setWidget(row, 1, txtLastName);
		basicFormFields.put("cilname", txtLastName);
		
		row++;
		
		final Label lblFirstName = new Label("First Name");
		flexTable.setWidget(row, 0, lblFirstName);
		final TextBox txtFirstName = new TextBox();
		txtFirstName.setWidth("200px");		
		flexTable.setWidget(row, 1, txtFirstName);
		basicFormFields.put("cifname", txtFirstName);
		
		row++;
		
		final Label lblMiddleName = new Label("Middle Name");
		flexTable.setWidget(row, 0, lblMiddleName);
		final TextBox txtMiddleName = new TextBox();
		txtMiddleName.setWidth("200px");
		flexTable.setWidget(row, 1, txtMiddleName);
		basicFormFields.put("cimname", txtMiddleName);
		
		row++;
		
		final Label lblDob = new Label("Date of Birth");
		flexTable.setWidget(row, 0, lblDob);
		final CustomDatePicker dobBox = new CustomDatePicker();
		flexTable.setWidget(row, 1, dobBox);
		basicFormFields.put("cidob", dobBox);
		
		row++;
		
		final Label lblComplaint = new Label("Complaint");
		flexTable.setWidget(row, 0, lblComplaint);
		final TextArea taComplaints = new TextArea();
		taComplaints.setCharacterWidth(22);
		taComplaints.setVisibleLines(5); // FIXME
		flexTable.setWidget(row, 1, taComplaints);
		basicFormFields.put("cicomplaint", taComplaints);
		
		row++;
		
		final Label lblFacility = new Label("Facility");
		flexTable.setWidget(row, 0, lblFacility);
		final SupportModuleWidget facility = new SupportModuleWidget("FacilityModule");
		facility.setValue(CurrentState.getDefaultFacility());
		facility.setWidth("200px");
		flexTable.setWidget(row, 1, facility);
		basicFormFields.put("cifacility", lblFacility);
		
		row++;
		
		final Label lblPhysician = new Label("Provider");
		flexTable.setWidget(row, 0, lblPhysician);
		final ProviderWidget provider = new ProviderWidget();
		provider.setWidth("200px");
		flexTable.setWidget(row, 1, provider);
		basicFormFields.put("ciphysician", provider);
		
		
		btnAdd = new CustomButton("Add",AppConstants.ICON_ADD);
		btnAdd.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				saveForm();
			}
		});
		btnClear = new CustomButton("Clear",AppConstants.ICON_CLEAR);
		btnClear.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				clearForm();
			}
		});

		horPanel.add(createContactInformation());

		//handling coverages
		callinPatientCoverages = new PatientCoverages();
		callinPatientCoverages.setMaxCoveragesCount(1);
		basicFormEntryTabPanel.add(callinPatientCoverages,"Insurance");
		
		basicFormEntryTabPanel.selectTab(0);
		
		HorizontalPanel panelButtons = new HorizontalPanel();
		verticalPanelEntry.add(panelButtons);
		verticalPanelEntry.setCellHorizontalAlignment(panelButtons, HasHorizontalAlignment.ALIGN_CENTER);
		
		panelButtons.add(btnAdd);
		panelButtons.add(btnClear);

		

		return verticalPanelEntry;
	}

	public void showCallinInfo(Integer callinId) {
		tabPanel.selectTab(0);
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
									Popup callinDetailPopup=new Popup();
									callinDetailPopup.setPixelSize(500, 20);
									FlexTable callinPatientDetail=new FlexTable();
									while (callinPatientDetail.getRowCount() > 0)
										callinPatientDetail.removeRow(0);
									int row=0;
									callinPatientDetail.setWidget(row, 0,
											new Label("Name:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("name")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Date of Birth:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("dob")));

									callinPatientDetail.setWidget(row, 0,
											new Label("complaint:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("complaint")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Home Phone:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("phone_home")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Work Phone:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("phone_work")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Facility:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("facility")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Provider:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("physician")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Call Date:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("call_date")));

									callinPatientDetail.setWidget(row, 0,
											new Label("Took Call:"));
									callinPatientDetail.setWidget(row++, 1,
											new Label(data.get("took_call")));
									
									PopupView viewInfo=new PopupView(callinPatientDetail);
									callinDetailPopup.setNewWidget(viewInfo);
									callinDetailPopup.initialize();
									

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
	
	protected void modifyEntry(final Integer callinId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(callinId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Callin.GetDetailedRecordWithIntake",
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
									
									if(data.get("intaketype")==null){
										formSelection.setWidgetValue("Basic");
										handleFormSelection();
									
										Util.populateForm(basicFormFields, data);
										if(data.get("ciisinsured")!=null)
											callinPatientCoverages.loadCoverageData(1, data);
										btnAdd.setText("Modify");
									}
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
	
	protected void unarchiveEntry(final Integer callinId) {
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
						populate(searchCriteria);
						tabPanel.selectTab(1);
						selectedEntryId = callinId;
						modifyEntry(callinId);
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// TODO NORMAL MODE STUFF
		}
	}

	public void openAddEventForm(final Integer callinId,String name){
		EventsWidget eventsWidget = new EventsWidget(ModuleName,callinId,name);
		eventsWidget.show();
		eventsWidget.center();
	}
	
	public void populate(HashMap<String, String> criteria) {
		
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			callInTable.showloading(true);
			String[] params = {};
			if(criteria!=null && criteria.size()>0){
				String[] tempParams = { JsonUtil.jsonify(criteria) };
				params = tempParams;
			}
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL
							.encode(Util.getJsonRequest(
									"org.freemedsoftware.module.Callin.GetAllWithInsurance",
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
								callInTable.showloading(false);
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
//									clearForm();
//									populate();
										showPopupAfterSaveCallin(r);
									Util.showInfoMsg("Callin Form", "Entry successfully added.");
								}else {
									r=(Boolean) JsonUtil.shoehornJson(
														JSONParser.parse(response.getText()),
												"Boolean")?1:0;
									if(r==1){
//										clearForm();
//											populate();	
											formSelection.setWidgetValue("");
											tabPanel.selectTab(0);
											Util.showInfoMsg("Callin Form", "Entry successfully modified.");
											btnAdd.setText("Add");
											showPopupAfterSaveCallin(selectedEntryId);
									}else{
										
									}
								}									
								
							} else {
								Util.showErrorMsg("Callin Form", "Callin Form failed.");
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
		try{
			Util.resetWidgetMap(basicFormFields);
			if(btnAdd.getText().equals("Modify"))
				btnAdd.setText("Add");
			callinPatientCoverages.removeCoverage(1);
			((TextBox)basicFormFields.get("cilname")).setFocus(true);
		}catch (Exception e) {
			// TODO: handle exception
		}
		clearSelection();
		selectedEntryId = null;
	}

	protected boolean validateForm() {
		String msg = new String("");
		if (((TextBox)basicFormFields.get("cilname")).getText().length() < 2) {
			msg += "Please specify a last name." + "\n";
		}
		if (((TextBox)basicFormFields.get("cifname")).getText().length() < 2) {
			msg += "Please specify a first name." + "\n";
		}
		if (((CustomDatePicker)basicFormFields.get("cidob")).getTextBox().getText().length() < 10) {
			msg += "Please specify date of birth." + "\n";
		}

		if (!msg.equals("")) {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	protected HashMap<String, String> populateHashMap(Integer id ) {
		HashMap<String, String> m = Util.populateHashMap(basicFormFields);
		HashMap<String, String> callinPatientCoverageData = callinPatientCoverages.getCoverageData(1);
		if(callinPatientCoverageData!=null){
			
			m.put("ciisinsured", "1");
			m.putAll(callinPatientCoverageData);
		}
			
		if(id!=null)
			m.put((String) "id", String.valueOf(id));

		return m;
	}

	protected void deleteEntry(Integer callId,final int curItem,final int totalItems) {
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
						Util.showErrorMsg("Callin Form", "Failed to delete entry.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Boolean r = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Boolean");
								if (r != null) {
									Util.showInfoMsg("Callin Form", "Entry deleted.");
									if(curItem==totalItems)
										populate(null);
								}
							} else {
								Util.showErrorMsg("Callin Form", "Failed to delete entry.");
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
	
	protected void initSearchPopup(){
		if(searchDialogBox==null){
		searchDialogBox = new CustomDialogBox();
		FlexTable flexTable = new FlexTable();
		
		int row = 0;
		
		final Label firstLastNameLabel = new Label("Name (Last, First):");
		flexTable.setWidget(row, 0, firstLastNameLabel);
		final TextBox lastName = new TextBox();
		flexTable.setWidget(row, 1, lastName);
		final TextBox firstName = new TextBox();
		flexTable.setWidget(row, 2, firstName);
		
		row++;
		
		final Label fullNameLabel = new Label("Call-In Patient:");
		flexTable.setWidget(row, 0, fullNameLabel);
		final SupportModuleWidget fullName = new SupportModuleWidget("Callin");
		flexTable.setWidget(row, 1, fullName);
		
		final CheckBox showArchived = new CheckBox("Include Archived");
		flexTable.setWidget(row, 2, showArchived);
		showArchived.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
			@Override
			public void onValueChange(ValueChangeEvent<Boolean> event) {
				if(event.getValue()){
					HashMap<String, String> args = new HashMap<String, String>();
					args.put("ciarchive", "1");
					fullName.setAdditionalParameters(args);
				}else 
					fullName.setAdditionalParameters(null);
			}
		});
		
		row++;
		
		final CustomButton searchBTN = new CustomButton("Search",AppConstants.ICON_SEARCH);
		flexTable.setWidget(row, 1, searchBTN);
		searchCriteria = null;
		searchBTN.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent event) {
				searchCriteria = new HashMap<String, String>();
				if(lastName.getText().trim().length()>0)
					searchCriteria.put("cilname", lastName.getText());
				if(firstName.getText().trim().length()>0)
					searchCriteria.put("cifname", firstName.getText());
				if(fullName.getValue()>0)
					searchCriteria.put("id", fullName.getValue().toString());
				if(showArchived.getValue())
					searchCriteria.put("ciarchive", showArchived.getValue()?"1":"0");
				populate(searchCriteria);
				
				searchDialogBox.hide();
			}
		
		});
		
		searchDialogBox.setContent(flexTable);
		}
		searchDialogBox.show();
		searchDialogBox.center();
		
	}
	
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}

}
