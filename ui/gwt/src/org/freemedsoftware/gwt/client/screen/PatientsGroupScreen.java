/*
 * $Id$
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
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.HashSet;
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
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleListBox;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.dom.client.KeyUpEvent;
import com.google.gwt.event.dom.client.KeyUpHandler;
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
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

import eu.future.earth.gwt.client.TimeBox;

public class PatientsGroupScreen extends ScreenInterface implements ClickHandler {

	VerticalPanel verticalPanelMenu = new VerticalPanel();
	VerticalPanel verticalPanelEntry = new VerticalPanel();

	protected CustomTable patientGroupTable;
	protected static String locale = "en_US";
	protected TabPanel tabPanel;

	protected HashMap<CheckBox, Integer> checkboxStack = new HashMap<CheckBox, Integer>();

	// /////////////
	protected TextBox groupName;
	protected SupportModuleWidget facilityModuleWidget;
	protected TextBox groupFrequency;
	protected TextBox groupLength;

	protected VerticalPanel membersPanel;
	protected List<PatientWidget> groupMembersList =new ArrayList<PatientWidget>();
	
	protected FlexTable groupDetailTable;

	// Declreaing Button
	protected Button btnAdd;
	protected Button btnClear;

	// /////////////////
	private static List<PatientsGroupScreen> patientsGroupScreenList = null;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well
	public static PatientsGroupScreen getInstance() {
		PatientsGroupScreen patientsGroupScreen = null;

		if (patientsGroupScreenList == null)
			patientsGroupScreenList = new ArrayList<PatientsGroupScreen>();
		if (patientsGroupScreenList.size() < AppConstants.MAX_PATIENTSGROUP_TABS) {// creates &
																	// returns
																	// new next
																	// instance
																	// of
																	// CallInScreen
			patientsGroupScreenList.add(patientsGroupScreen = new PatientsGroupScreen());
		} else { // returns last instance of CallInScreen from list
			patientsGroupScreen = patientsGroupScreenList.get(AppConstants.MAX_PATIENTSGROUP_TABS - 1);
			patientsGroupScreen.populate();
		}
		return patientsGroupScreen;
	}

	public static boolean removeInstance(PatientsGroupScreen patientsGroupScreen){
		return patientsGroupScreenList.remove(patientsGroupScreen);
	}
	
	public PatientsGroupScreen() {
		
		final boolean canDelete = CurrentState.isActionAllowed(AppConstants.DELETE, AppConstants.PATIENT_CATEGORY, AppConstants.CALL_IN);
		final boolean canWrite  = CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.PATIENT_CATEGORY, AppConstants.CALL_IN);
		final boolean canBook   = CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.SYSTEM_CATEGORY, AppConstants.SCHEDULER);
		
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		tabPanel = new TabPanel();
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
						patientGroupTable.selectionAdd(checkboxStack.get(t).toString());
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
						patientGroupTable
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
					if (patientGroupTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else if (Window
							.confirm("Are you sure you want to delete these item(s)?")) {
						List<String> slectedItems = patientGroupTable.getSelected();
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
		
		if(canBook){
			final Button bookButton = new Button();
			menuButtonsPanel.add(bookButton);
			bookButton.setText("Book");
			bookButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (patientGroupTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else {
						List<String> slectedItems = patientGroupTable.getSelected();
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
		}
			
		verticalPanelMenu.add(menuButtonsPanel);
		patientGroupTable = new CustomTable();
		verticalPanelMenu.add(patientGroupTable);
		patientGroupTable.setAllowSelection(false);
		patientGroupTable.setSize("100%", "100%");
		// //what for is this used???To work on this
		patientGroupTable.setIndexName("id");
		// ///
		if(canBook || canDelete)
			patientGroupTable.addColumn("Selected", "selected");
		
		patientGroupTable.addColumn("Group Name", "groupname");
		patientGroupTable.addColumn("Group Facility", "groupfacility");
		patientGroupTable.addColumn("Group Frequency (in days)", "groupfrequency");
		patientGroupTable.addColumn("Group Member", "grouplength");

		patientGroupTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col != 0 || !(canBook || canWrite || canDelete )) {
						final Integer groupId = Integer.parseInt(data
								.get("id"));
						showGroupInfo(groupId);
					}
				} catch (Exception e) {
					GWT.log("Caught exception: ", e);
				}
			}
		});

		patientGroupTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							HashMap<String, String> data) {
						Integer id = Integer.parseInt(data.get("id"));
						if (columnName.compareTo("selected") == 0) {
							CheckBox c = new CheckBox();
							c.addClickHandler(getPatientGroupScreen());
							checkboxStack.put(c, id);
							return c;
						} else {
							return (Widget) null;
						}
					}
				});

		groupDetailTable = new FlexTable();
		groupDetailTable.setWidth("50%");
		verticalPanelMenu.add(groupDetailTable);
		tabPanel.add(verticalPanelMenu, "Menu");
		if(canWrite)
			tabPanel.add(createEntryTabBar(), "Entry");
		// tabPanel.add(new VerticalPanel(),"Entry");
		tabPanel.selectTab(0);
		// createEntryTabBar();

		// patientGroupTable.formatTable(5);
		// patientGroupTable.getFlexTable().setWidth("100%");

		// //////
		populate();
	}

	public PatientsGroupScreen getPatientGroupScreen() {
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
			patientGroupTable.selectionAdd(item.toString());
		} else {
			// selectedItems.remove((Object) item);
			patientGroupTable.selectionRemove(item.toString());
		}
	}

	private VerticalPanel createEntryTabBar() {

		Label groupNamelabel = new Label("Group Name");
		Label groupFacilityLabel = new Label("Group Facility");
		Label groupFrequencyLabel = new Label("Group Frequency (in days)");
		Label groupLengthLabel = new Label("Group Length");

		// TextBoxs for FirsName and LastName
		groupName = new TextBox();
		facilityModuleWidget = new SupportModuleWidget("FacilityModule");
		facilityModuleWidget.setWidth("200px");
		
		groupName = new TextBox();
		groupName.setWidth("200px");
		groupFrequency = new TextBox();
		groupFrequency.setWidth("200px");
		groupLength = new TextBox();
		groupLength.setWidth("200px");
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

		FlexTable flexTable = new FlexTable();
		// Adding all labels to the fexTable
		flexTable.setWidget(0, 0, groupNamelabel);
		flexTable.setWidget(1, 0, groupFacilityLabel);
		flexTable.setWidget(2, 0, groupFrequencyLabel);
		flexTable.setWidget(3, 0, groupLengthLabel);
		// HorizontalPanel for Add , Clear , and Cancel Buttons

		// flexTable.setWidget(7, 1, panelButtons);
		flexTable.setWidget(0, 1, groupName);
		flexTable.setWidget(1, 1, facilityModuleWidget);
		flexTable.setWidget(2, 1, groupFrequency);
		flexTable.setWidget(3, 1, groupLength);

		VerticalPanel membersLabelPanel = new VerticalPanel();
		membersLabelPanel.add(new Label("Group Members "));
		Label requirelabel = new Label("(must have more than one member)");
		requirelabel.setStyleName("label");
		membersLabelPanel.add(requirelabel);
		membersLabelPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_MIDDLE);
		
		membersPanel = new VerticalPanel();
		
		flexTable.setWidget(7, 0, membersLabelPanel);
		
		for(int i=0;i<4;i++)
		{
			PatientWidget patientWidget = new PatientWidget();
			patientWidget.setWidth("200px");
			membersPanel.add(patientWidget);
			groupMembersList.add(patientWidget);
		}
		flexTable.setWidget(7, 1, membersPanel);
		verticalPanelEntry.add(flexTable);
		Button addMoreMember = new Button("add another member");
		addMoreMember.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
			PatientWidget patientWidget = new PatientWidget();
			patientWidget.setWidth("200px");
			membersPanel.add(patientWidget);
			groupMembersList.add(patientWidget);	
			}
		});
		flexTable.setWidget(8, 1, addMoreMember);
		FlexTable panelButtons = new FlexTable();
		panelButtons.setWidth("20%");
		panelButtons.setWidget(0, 25, btnAdd);
		panelButtons.setWidget(0, 26, btnClear);

		verticalPanelEntry.add(panelButtons);

		return verticalPanelEntry;
	}

	public void populate() {
		while (groupDetailTable.getRowCount() > 0)
			// clearing detail of group (given below the table)
			groupDetailTable.removeRow(0);

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { locale };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL
							.encode(Util.getJsonRequest(
									"org.freemedsoftware.module.CalendarGroup.GetAll",
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
								patientGroupTable.clearAllSelections();
								patientGroupTable.loadData(result);
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
							patientGroupTable.loadData(r);
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


	protected void showGroupInfo(Integer groupId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(groupId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.CalendarGroup.GetDetailedRecord",
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
									diplayGroupDetails(data);

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

	public void diplayGroupDetails(HashMap<String, String> data){
	while (groupDetailTable.getRowCount() > 0)
			groupDetailTable.removeRow(0);
		groupDetailTable.setWidget(0, 0, new Label("Group Name:"));
		groupDetailTable.setWidget(0, 1, new Label(data.get("groupname")));

		groupDetailTable.setWidget(1, 0, new Label("Group Facility:"));
		groupDetailTable.setWidget(1, 1, new Label(data.get("groupfacility")));

		groupDetailTable.setWidget(2, 0,
				new Label("Group Frequency (in days):"));
		groupDetailTable.setWidget(2, 1, new Label(data.get("groupfrequency")));

		groupDetailTable.setWidget(3, 0, new Label("Group Length:"));
		groupDetailTable.setWidget(3, 1, new Label(data.get("grouplength")));

		groupDetailTable.setWidget(4, 0, new Label("Group Members:"));
		if (data.get("groupmembersName") != null) {
			String[] groupMembers = data.get("groupmembersName").split("\n");
			for (int i = 0; i < groupMembers.length; i++) {
				groupDetailTable
						.setWidget(4 + i, 1, new Label(groupMembers[i]));
			}
		}
	}
	
	public void saveForm() {
		if (validateForm()) {
			// Add callin info
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO STUBBED MODE STUFF
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { JsonUtil.jsonify(populateHashMap()) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.CalendarGroup.add",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != 0) {
									clearForm();
									populate();
									CurrentState.getToaster().addItem(
											"Calgroup Form",
											"Entry successfully added.");
								}
							} else {
								CurrentState.getToaster().addItem(
										"Calgroup Form", "Group Form failed.");
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

		groupName.setText("");
		groupFrequency.setText("");
		groupLength.setText("");
		facilityModuleWidget.setValue(0);
		
		while(membersPanel.getWidgetCount()>4){
			membersPanel.remove(0);
			groupMembersList.remove(0);
		}
	}

	protected boolean validateForm() {
		String msg = new String("");
		if (groupName.getText().length() < 2) {
			msg += "Please specify a group name." + "\n";
		}
		
		Iterator<PatientWidget> itr = groupMembersList.iterator();
		int members=0;
		HashSet<Integer> tempPatientValues = new HashSet<Integer>();
		while(itr.hasNext()){
			PatientWidget patientWidget = itr.next();
			int patientId = Integer.parseInt(patientWidget.getStoredValue());
			if(patientId!=0 && patientWidget.getText().length()>0){
				members++;
				if(tempPatientValues.contains(patientId)){
					msg += "Please remove duplicate members." + "\n";
					members=0;
					break;
				}else tempPatientValues.add(patientId);
			}
		}
		if(members<2)
			msg += "Please specify more than one group members." + "\n";
		
		if (msg != "") {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	protected HashMap<String, String> populateHashMap() {
		HashMap<String, String> m = new HashMap<String, String>();

		m.put((String) "groupname", (String) groupName.getText());
		if(facilityModuleWidget.getValue()!=null)
			m.put((String) "groupfacility", facilityModuleWidget.getValue().toString());
		m.put((String) "groupfrequency", (String) groupFrequency.getText());
		m.put((String) "grouplength", (String) groupLength.getText());
		Iterator<PatientWidget> itr = groupMembersList.iterator();
		String members="";
		while(itr.hasNext()){
			PatientWidget patientWidget = itr.next();
			if(patientWidget.getStoredValue()!=null && patientWidget.getText().length()>0)
				members=members+patientWidget.getStoredValue()+",";
		}
		if(members.length()>0)
			members = members.substring(0, members.length()-1); // removing last comma(,)
		m.put((String) "groupmembers", members);

		return m;
	}

	protected void deleteEntry(Integer groupId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO STUBBED MODE STUFF
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(groupId) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.CalendarGroup.del", params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem("CalendarGroup Screen",
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
											"CalendarGroup Screen", "Entry deleted.",
											Toaster.TOASTER_INFO);
									// populate(tag);
								}
							} else {
								CurrentState.getToaster().addItem(
										"CalendarGroup Screen",
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

		private SupportModuleWidget group = null;

		private SupportModuleWidget provider = null;

		private TextArea text = new TextArea();

		// private DateEditFieldWithPicker date;
		private CustomDatePicker date;

		private CheckBox wholeDay = new CheckBox();

		private HorizontalPanel time = new HorizontalPanel();

		private HorizontalPanel timePanel = new HorizontalPanel();

		private TimeBox start;

		private TimeBox end;

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
			start = new TimeBox();
			end = new TimeBox();

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

			group = new SupportModuleWidget("CalendarGroup");
			table.setWidget(1, 0, new Label("Group"));
			table.setWidget(1, 1, group);

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
			if (group.getValue() < 1 || group.getText().length()<1) {
				msg += "Please specify patient." + "\n";
			}
			if (provider.getValue() < 1 || provider.getText().length()<1) {
				msg += "Please specify provider." + "\n";
			}

			if (msg != "") {
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

			d.put("caltype", "group");
			d.put("calpatient", group.getValue().toString());
			d.put("calphysician", provider.getValue().toString());
			d.put("calprenote", text.getText());

			if(selectTemplate.getStoredValue()!=null)
				d.put("calappttemplate", selectTemplate.getStoredValue());
			
			return d;
		}

		public void setPatient(Integer patientId) {
			this.group.setValue(patientId);
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
