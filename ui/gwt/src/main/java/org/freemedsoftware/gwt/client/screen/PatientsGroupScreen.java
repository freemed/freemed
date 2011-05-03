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
import org.freemedsoftware.gwt.client.widget.ClosableTab;
import org.freemedsoftware.gwt.client.widget.ClosableTabInterface;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTimeBox;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.Popup;
import org.freemedsoftware.gwt.client.widget.PopupView;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleListBox;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.EventData;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.core.client.GWT;
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
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;


public class PatientsGroupScreen extends ScreenInterface implements ClickHandler {

	public class NoteEntryWidget extends Composite{
		private List<GroupMember> groupMembers;
		private Integer groupId;
		private Integer appointmentId;
		private Integer provider;

		public NoteEntryWidget(){
		}
		public NoteEntryWidget(List<GroupMember> groupMembers){
			this.groupMembers = groupMembers;
		}
		public void init(){
			VerticalPanel noteEntryWidgetVPanel = new VerticalPanel();
			initWidget(noteEntryWidgetVPanel);
			
			Label groupNoteLabel = new Label("Create Group Note" );
			groupNoteLabel.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
			noteEntryWidgetVPanel.add(groupNoteLabel);
			
			final HorizontalPanel horizontalPanel = new HorizontalPanel();
			noteEntryWidgetVPanel.add(horizontalPanel);
			final VerticalPanel membersPanel = new VerticalPanel();
			horizontalPanel.add(membersPanel);
			final VerticalPanel detailDiscussionPanel = new VerticalPanel();
			horizontalPanel.add(detailDiscussionPanel);

			final FlexTable flexTable = new FlexTable();
			int row = 0;
			membersPanel.add(flexTable);
			///start adding group memebers
			Iterator<GroupMember> iterator = groupMembers.iterator();
			while(iterator.hasNext()){
				final GroupMember groupMember = iterator.next();
				CheckBox checkBox = new CheckBox(groupMember.getName());
				flexTable.setWidget(row, 0, checkBox);
				final TextArea textArea = new TextArea();
				textArea.addValueChangeHandler(new ValueChangeHandler<String>() {
					@Override
					public void onValueChange(ValueChangeEvent<String> arg0) {
						if(groupMember.isSelected())
							groupMember.setNote(textArea.getText());
					}
				});
				flexTable.setWidget(row, 1, textArea);
				checkBox.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
					@Override
					public void onValueChange(ValueChangeEvent<Boolean> arg0) {
							groupMember.setSelected(arg0.getValue());
							groupMember.setNote(textArea.getText());
					}
				});
				row++;
			}
			
			HorizontalPanel detailDiscussionHPanel = new HorizontalPanel();
			detailDiscussionPanel.add(detailDiscussionHPanel);
			
			Label label = new Label("Description :");
			detailDiscussionHPanel.add(label);
			final TextBox description = new TextBox();
			detailDiscussionHPanel.add(description);
			
			detailDiscussionHPanel = new HorizontalPanel();
			detailDiscussionPanel.add(detailDiscussionHPanel);
			
			label = new Label("Discussion :");
			detailDiscussionHPanel.add(label);
			final TextArea discussion = new TextArea();
			discussion.setWidth("250%");
			discussion.getElement().setPropertyString("rows", "12");
			detailDiscussionHPanel.add(discussion);
			
			CustomButton addButton = new CustomButton("Add",AppConstants.ICON_ADD);
			addButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					String msg="";
					if(description.getText().trim().length()==0)
						msg += "Description";
					if(discussion.getText().trim().length()==0)
						msg += "\nDiscussion";
					if(msg.length()>0)
						Window.alert("Please fill the following fields\n"+msg);
					else{
						commitChanges(description.getText(),discussion.getText());
					}
				}
			});
			noteEntryWidgetVPanel.add(addButton);
		}
		public List<GroupMember> getGroupMembers() {
			return groupMembers;
		}
		public void setGroupMembers(List<GroupMember> groupMembers) {
			this.groupMembers = groupMembers;
		}
		public Integer getAppointmentId() {
			return appointmentId;
		}
		public void setAppointmentId(Integer appointmentId) {
			this.appointmentId = appointmentId;
		}
		public Integer getProvider() {
			return provider;
		}
		public void setProvider(Integer provider) {
			this.provider = provider;
		}
		public Integer getGroupId() {
			return groupId;
		}
		public void setGroupId(Integer groupId) {
			this.groupId = groupId;
		}
		public void commitChanges(String description,String discussion){
			Iterator<GroupMember> iterator = groupMembers.iterator();
			while(iterator.hasNext()){
				GroupMember groupMember = iterator.next();
				////////preparing and saving data into CallGroupAttend table
				HashMap<String, String> data = new HashMap<String, String>();
				data.put("calgroupid", groupId.toString());
				data.put("calid", appointmentId.toString());
				data.put("patientid", groupMember.getId().toString());
				if(groupMember.isSelected())
					data.put("calstatus", "attended");
				else
					data.put("calstatus", "noshow");
				saveCallGroupAttend(data);
			}
			groupNoteTabs.remove(groupId);
			tabPanel.remove(this);
			tabPanel.selectTab(0);
		}
		public void saveCallGroupAttend(HashMap<String, String> data){
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO STUBBED MODE STUFF
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { JsonUtil.jsonify(data) };
				RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
						URL.encode(Util.getJsonRequest(
								"org.freemedsoftware.module.CalendarGroupAttendance.add", params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							Util.showErrorMsg("CalendarGroupScreen", "Failed to add entry.");
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (Util.checkValidSessionResponse(response.getText())) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil.shoehornJson(
											JSONParser.parse(response.getText()),
											"Integer");
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
	}
	
	public class GroupMember{
		private Integer id = null;
		private String name = null;
		private String note = null;
		private boolean isSelected=false;
		
		public GroupMember(){
		}
		public GroupMember(Integer id,String name){
			this(id,name,null,false);
		}
		public GroupMember(Integer id,String name,String note){
			this(id,name,note,false);
		}
		public GroupMember(Integer id,String name,String note,boolean isSelected){
			this.id = id;
			this.name = name;
			this.note = note;
			this.isSelected = isSelected;
		}
		public Integer getId() {
			return id;
		}

		public void setId(Integer id) {
			this.id = id;
		}

		public String getName() {
			return name;
		}

		public void setName(String name) {
			this.name = name;
		}

		public String getNote() {
			return note;
		}

		public void setNote(String note) {
			this.note = note;
		}

		public boolean isSelected() {
			return isSelected;
		}

		public void setSelected(boolean isSelected) {
			this.isSelected = isSelected;
		}
	}
	
	VerticalPanel verticalPanelMenu = new VerticalPanel();
	VerticalPanel verticalPanelEntry = new VerticalPanel();
	
	protected Integer selectedEntryId;
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
	protected List<PatientWidget> groupMembersListInEntryForm =new ArrayList<PatientWidget>();
	
	protected VerticalPanel groupDetailPanel;
	protected FlexTable groupDetailTable;
	protected Popup groupDetailPopup;

	// Declreaing Button
	protected CustomButton btnAdd;
	protected CustomButton btnClear;

	protected CustomListBox groupAppointmentsList=new CustomListBox();
	
	protected List<GroupMember> groupMembersListInPopUp = new ArrayList<GroupMember>();
	
	// /////////////////
	private static List<PatientsGroupScreen> patientsGroupScreenList = null;
	
	private HashMap<Integer, NoteEntryWidget> groupNoteTabs = new HashMap<Integer, NoteEntryWidget>();;

	public final static String ModuleName = "CalendarGroup"; 	
	
	final boolean canBook   = CurrentState.isActionAllowed(SchedulerWidget.moduleName, AppConstants.WRITE);
	
	
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
		super(ModuleName);
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
					 groupName.setFocus(true);				
			}		
		});
		
		verticalPanel.add(tabPanel);

		/*
		 * final Label callInLabel = new Label("Call-in Patient Management.");
		 * verticalPanelMenu.add(callInLabel);
		 * callInLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		 */

		final HorizontalPanel menuButtonsPanel = new HorizontalPanel();
		menuButtonsPanel.setSpacing(1);
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
						patientGroupTable.selectionAdd(checkboxStack.get(t).toString());
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

		if(canBook){
			final CustomButton bookButton = new CustomButton("Book",AppConstants.ICON_BOOK_APP);
			menuButtonsPanel.add(bookButton);
			bookButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (patientGroupTable.getSelectedCount() < 1)
						Window.alert("Please select at least one entry!");
					else {
						List<String> slectedItems = patientGroupTable.getSelected();
						SchedulerScreen schedulerScreen = SchedulerScreen.getInstance();
						EventData eventData = schedulerScreen.getSchedulerWidget().getNewExternalDataEvent();
						eventData.setPatientId(Integer.parseInt(slectedItems.get(0)));
						eventData.setResourceType(AppConstants.APPOINTMENT_TYPE_GROUP);
						schedulerScreen.getSchedulerWidget().setExternalDataEvent(eventData);
						Util.spawnTab(AppConstants.SCHEDULER,schedulerScreen);
					}
				}
			});
		}
		
	if(canModify){
		final CustomButton modifyButton = new CustomButton("Modify",AppConstants.ICON_MODIFY);
		menuButtonsPanel.add(modifyButton);
		modifyButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				if (patientGroupTable.getSelectedCount() < 1)
					Window.alert("Please select an entry!");
				else if(patientGroupTable.getSelectedCount() > 1)
					Window.alert("You can modify only a single entry at a time!");
				else {
					List<String> slectedItems = patientGroupTable.getSelected();
					Iterator<String> itr = slectedItems.iterator();// Get all
																	// selected
																	// items
																	// from
																	// custom
																	// table
					tabPanel.selectTab(1);
					groupName.setFocus(true);
					btnAdd.setText("Modify");
					selectedEntryId=Integer.parseInt(itr.next());
					modifyEntry(selectedEntryId);
				}
			}
		});
	}
	
	if(canDelete){
		final CustomButton deleteButton = new CustomButton("Delete",AppConstants.ICON_DELETE);
		menuButtonsPanel.add(deleteButton);
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
		patientGroupTable.addColumn("Group Length (min)", "grouplength");

		patientGroupTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if(col!=0)
						showGroupInfo(Integer.parseInt(data.get("id")));
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
	
	public void getGroupAppointments(final Integer groupId,final String groupName){
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { groupId.toString() };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL
							.encode(Util.getJsonRequest(
									"org.freemedsoftware.api.Scheduler.FindGroupAppointmentsDates",
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
							if (result != null && result.length>0) {
								groupAppointmentsList = new CustomListBox();
								for(int i=0;i<result.length;i++){
									groupAppointmentsList.addItem(result[i].get("caldateof"),result[i].get("id")+":"+result[i].get("calphysician"));
								}
								HorizontalPanel horizontalPanel = new HorizontalPanel();
								horizontalPanel.add(new Label("Select Appointment Date:"));
								horizontalPanel.add(groupAppointmentsList);
								groupDetailPanel.add(horizontalPanel);
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
		groupLengthLabel.setVisible(false);

		// TextBoxs for FirsName and LastName
		groupName = new TextBox();
		facilityModuleWidget = new SupportModuleWidget("FacilityModule");
		facilityModuleWidget.setWidth("300px");
		
		groupName = new TextBox();
		groupName.setWidth("300px");
		groupFrequency = new TextBox();
		groupFrequency.setWidth("300px");
		groupLength = new TextBox();
		groupLength.setEnabled(false);
		groupLength.setVisible(false);
		groupLength.setWidth("300px");
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

		// date for service's date and its simple format i;e without time.

		FlexTable flexTable = new FlexTable();
		// Adding all labels to the fexTable
		flexTable.setWidget(0, 0, groupNamelabel);
		flexTable.setWidget(1, 0, groupFacilityLabel);
		flexTable.setWidget(0, 3, groupFrequencyLabel);
		flexTable.setWidget(1, 3, groupLengthLabel);
		// HorizontalPanel for Add , Clear , and Cancel Buttons

		// flexTable.setWidget(7, 1, panelButtons);
		flexTable.setWidget(0, 1, groupName);
		flexTable.setWidget(1, 1, facilityModuleWidget);
		flexTable.setWidget(0, 4, groupFrequency);
		flexTable.setWidget(1, 4, groupLength);

		VerticalPanel membersLabelPanel = new VerticalPanel();
		membersLabelPanel.add(new Label("Group Members "));
		Label requirelabel = new Label("(must have more than one member)");
		requirelabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		membersLabelPanel.add(requirelabel);
		membersLabelPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_TOP);
		
		membersPanel = new VerticalPanel();
		
		flexTable.setWidget(2, 0, membersLabelPanel);
		flexTable.getCellFormatter().setVerticalAlignment(2, 0, HasVerticalAlignment.ALIGN_TOP);
		for(int i=0;i<4;i++)
		{
			PatientWidget patientWidget = new PatientWidget();
			patientWidget.setWidth("300px");
			membersPanel.add(patientWidget);
			groupMembersListInEntryForm.add(patientWidget);
		}
		flexTable.setWidget(2, 1, membersPanel);
		verticalPanelEntry.add(flexTable);
		CustomButton addMoreMember = new CustomButton("add another member",AppConstants.ICON_ADD_PERSON);
		addMoreMember.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
			PatientWidget patientWidget = new PatientWidget();
			patientWidget.setWidth("300px");
			membersPanel.add(patientWidget);
			groupMembersListInEntryForm.add(patientWidget);	
			}
		});
		//flexTable.setWidget(3, 2, addMoreMember);
		CustomButton removeMember = new CustomButton("Remove last member",AppConstants.ICON_REMOVE_PERSON);
		removeMember.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				if(membersPanel.getWidgetCount()>4)
				membersPanel.remove(membersPanel.getWidgetCount()-1);	
			}
		});
		FlexTable panelButtons = new FlexTable();
		panelButtons.setWidget(0, 0, btnAdd);
		panelButtons.setWidget(0, 1, btnClear);
		panelButtons.setWidget(0, 2, addMoreMember);  
		panelButtons.setWidget(0, 3, removeMember);
		HorizontalPanel buttonsHPanel=new HorizontalPanel();
		buttonsHPanel.setWidth("80%");
		buttonsHPanel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		buttonsHPanel.add(panelButtons);		
		verticalPanelEntry.add(buttonsHPanel);
		
		return verticalPanelEntry;
	}

	public void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			patientGroupTable.showloading(true);
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
								patientGroupTable.showloading(false);
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


	public void showGroupInfo(Integer groupId) {
		if (canRead) {
			groupDetailPopup=new Popup();
			groupDetailPanel=new VerticalPanel();
			groupDetailTable=new FlexTable();
			groupDetailPanel.add(groupDetailTable);
			PopupView viewInfo=new PopupView(groupDetailPanel);
			groupDetailPopup.setNewWidget(viewInfo);
			groupDetailPopup.initialize();
		}else return;
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
	
	protected void modifyEntry(Integer groupId) {
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
									groupName.setText(data.get("groupname"));
									facilityModuleWidget.setValue(Integer.parseInt(data.get("facility")));
									groupFrequency.setText(data.get("groupfrequency"));
									groupLength.setText(data.get("grouplength"));
									String[] groupMembers = data.get("groupmembers").split(",");
									for(int i=0;i<groupMembers.length;i++){
										if(i>3){
											PatientWidget patientWidget = new PatientWidget();
											patientWidget.setWidth("300px");
											membersPanel.add(patientWidget);
											groupMembersListInEntryForm.add(patientWidget);
										}
									groupMembersListInEntryForm.get(i).setValue(Integer.parseInt(groupMembers[i]));
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
	
	

	public void diplayGroupDetails(HashMap<String, String> data){
	
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
	
		String[] groupMembersNames=null;
		
		if (data.get("groupmembersName") != null) {
			groupMembersNames = data.get("groupmembersName").split("\n");

			for (int i = 0; i < groupMembersNames.length; i++) {
				groupDetailTable
						.setWidget(4 + i, 1, new Label(groupMembersNames[i]));
			}
		}
		String[] groupMembersId=null;
		if(data.get("groupmembers")!=null)
			groupMembersId = data.get("groupmembers").split(",");
		groupMembersListInPopUp = new ArrayList<GroupMember>();
		if(groupMembersId!=null && groupMembersNames!=null)
			for (int i = 0; i < groupMembersId.length; i++) {
				groupMembersListInPopUp.add(new GroupMember(Integer.parseInt(groupMembersId[i]),groupMembersNames[i]));
			}
		getGroupAppointments(Integer.parseInt(data.get("id")),data.get("groupname"));
	}
	
	public void saveForm() {
		if (validateForm()) {
			// Add callin info
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO STUBBED MODE STUFF
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				RequestBuilder builder=null;
				if(btnAdd.getText().equals("Add")){
				String[] params = { JsonUtil.jsonify(populateHashMap(null)) };
				builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.CalendarGroup.add",
												params)));
				}else{
					String[] params = { JsonUtil.jsonify(populateHashMap(selectedEntryId)) };
					builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.CalendarGroup.mod",
													params)));
					
				}
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
								if (r != null) {
									clearForm();
									populate();
									Util.showInfoMsg("CalendarGroupScreen", "Entry successfully added.");
								}else{
									r=(Boolean) JsonUtil.shoehornJson(
											JSONParser.parse(response.getText()),
									"Boolean")?1:0;
									if(r==1){
										clearForm();
											populate();	
											Util.showInfoMsg("CalendarGroupScreen", "Entry successfully modified.");
											btnAdd.setText("Add");
									}else{
										
									}
								}
							} else {
								Util.showErrorMsg("CalendarGroupScreen", "Group Form failed.");
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
		if(btnAdd.getText().equals("Modify"))
			btnAdd.setText("Add");

		for(int i=0;i<groupMembersListInEntryForm.size();i++){
			groupMembersListInEntryForm.get(0).clear();
			groupMembersListInEntryForm.get(0).removeFromParent();
			groupMembersListInEntryForm.remove(0);
		}
		
		for(int i=0;i<membersPanel.getWidgetCount();i++){
			membersPanel.remove(0);
		}
		
		groupMembersListInEntryForm.clear();
		membersPanel.clear();
		groupMembersListInEntryForm =new ArrayList<PatientWidget>();
			for(int i=0;i<4;i++){
				PatientWidget patientWidget = new PatientWidget();
				patientWidget.setWidth("300px");
				membersPanel.add(patientWidget);
				groupMembersListInEntryForm.add(patientWidget);
			}
			groupName.setFocus(true);
		
	}

	protected boolean validateForm() {
		String msg = new String("");
		if (groupName.getText().length() < 2) {
			msg += "Please specify a group name." + "\n";
		}
		
		Iterator<PatientWidget> itr = groupMembersListInEntryForm.iterator();
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
		
		if (!msg.equals("")) {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	protected HashMap<String, String> populateHashMap(Integer id) {
		HashMap<String, String> m = new HashMap<String, String>();
		m.put((String) "id", String.valueOf(id));
		m.put((String) "groupname", (String) groupName.getText());
		if(facilityModuleWidget.getValue()!=null)
			m.put((String) "groupfacility", facilityModuleWidget.getValue().toString());
		m.put((String) "groupfrequency", (String) groupFrequency.getText());
		Iterator<PatientWidget> itr = groupMembersListInEntryForm.iterator();
		String members="";
		Integer membersLength = 0;
		while(itr.hasNext()){
			PatientWidget patientWidget = itr.next();
			if(patientWidget.getStoredValue()!=null && patientWidget.getText().length()>0){
				members=members+patientWidget.getStoredValue()+",";
				membersLength++;
			}
		}
		if(members.length()>0)
			members = members.substring(0, members.length()-1); // removing last comma(,)
		m.put((String) "grouplength", membersLength.toString());
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
						Util.showErrorMsg("CalendarGroupScreen", "Failed to delete entry.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Boolean r = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Boolean");
								if (r != null) {
									Util.showInfoMsg("CalendarGroupScreen", "Entry deleted.");
									// populate(tag);
								}
							} else {
								Util.showErrorMsg("CalendarGroupScreen", "Failed to delete entry.");
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

		private CustomTimeBox start;

		private CustomTimeBox end;

		private CustomButton cancel = null;

		private CustomButton ok = null;

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

			cancel = new CustomButton("Cancel",AppConstants.ICON_CANCEL);
			cancel.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					hide();
				}

			});
			cancel.setFocus(true);
			cancel.setAccessKey('c');

			ok = new CustomButton("Ok",AppConstants.ICON_ADD);
			ok.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					if (!CurrentState.canBookAppoinment(start.getValue(date
							.getValue()), end.getValue(date.getValue()))) {
						/*
						Util.showErrorMsg("CalendarGroupScreen",
								"Can not book appointment in between("
										+ CurrentState.BREAK_HOUR + ":00 -"
										+ (CurrentState.BREAK_HOUR + 1)
										+ ":00) !");
						*/
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
								Util.showErrorMsg("CalendarGroupScreen", "Failed to get scheduler items.");
							}
						}
					});
				} catch (RequestException e) {
					Util.showErrorMsg("CalendarGroupScreen", "Failed to get scheduler items.");
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
									Util.showInfoMsg("CalendarGroupScreen", "Appointment saved successfully.");
								} else {
									Util.showErrorMsg("CalendarGroupScreen", "Failed to save appointment.");
								}
							}
						});
					} catch (RequestException e) {
						Util.showErrorMsg("CalendarGroupScreen", "Failed to save appointment.");
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

			if (!msg.equals("")) {
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

			d.put("caltype", AppConstants.APPOINTMENT_TYPE_GROUP);
			d.put("calpatient", group.getValue().toString());
			d.put("calgroupid", group.getValue().toString());
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
