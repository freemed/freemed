/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

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
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ACLScreen extends ScreenInterface implements
		ClickHandler {

	protected CustomTable groupsTable = new CustomTable();
	protected  FlexTable groupAddTable; 
	protected Integer groupId = null;
	
	protected TextBox groupName;

	protected Button addGroupButton, clearButton, deleteGroupButton;

	protected String className = "ACLScreen";

	protected HashMap<CheckBox,String> aclPermissionsMap=new HashMap<CheckBox,String>();
	
	private static List<ACLScreen> aclScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static ACLScreen getInstance(){
		ACLScreen aclScreen=null; 
		
		if(aclScreenList==null)
			aclScreenList=new ArrayList<ACLScreen>();
		if(aclScreenList.size()<AppConstants.MAX_ACL_TABS)//creates & returns new next instance of SupportDataScreen
			aclScreenList.add(aclScreen=new ACLScreen());
		else //returns last instance of SupportDataScreen from list 
			aclScreen = aclScreenList.get(AppConstants.MAX_ACL_TABS-1);
		return aclScreen;
	}
	
	public static boolean removeInstance(ACLScreen aclScreen){
		return aclScreenList.remove(aclScreen);
	}
	
	public ACLScreen() {
		
		final boolean canAddGroup =  CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.UTILITIES_CATEGORY, AppConstants.ACL);
		
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		// Panel #1
		if(canAddGroup){
			VerticalPanel groupAddPanel = new VerticalPanel();
			groupAddTable = new FlexTable();
			groupAddPanel.add(groupAddTable);
			tabPanel.add(groupAddPanel, "Add Group");
			tabPanel.selectTab(0);
			final Label groupNameLabel = new Label("Group Name");
			groupAddTable.setWidget(0, 0, groupNameLabel);
	
			groupName = new TextBox();
			groupAddTable.setWidget(0, 1, groupName);
			groupAddTable.getFlexCellFormatter().setColSpan(0, 1, 2);
			groupName.setWidth("10em");
	
			final Label selectPermissionsLabel = new Label("Select Permissions");
			selectPermissionsLabel.setStyleName("label");
			groupAddTable.setWidget(1, 0, selectPermissionsLabel);
	
			
			HorizontalPanel buttonsPanel = new HorizontalPanel();
			addGroupButton = new Button();
			addGroupButton.setText("Add Group");
			addGroupButton.addClickHandler(this);
			buttonsPanel.add(addGroupButton);
	
			deleteGroupButton = new Button();
			deleteGroupButton.setText("Delete Group");
			deleteGroupButton.addClickHandler(this);
			deleteGroupButton.setVisible(false);
			buttonsPanel.add(deleteGroupButton);
			
			clearButton = new Button();
			clearButton.setText("Clear");
			clearButton.addClickHandler(this);
			buttonsPanel.add(clearButton);
			
			groupAddPanel.add(buttonsPanel);
			getACLPermissions();
		}
		// Panel #2

		final FlexTable groupListTable = new FlexTable();
		tabPanel.add(groupListTable, "List Groups");

		groupListTable.setWidget(0, 0, groupsTable);

		groupsTable.setSize("100%", "100%");
		groupsTable.addColumn("Group Name", "groupname"); // col 0
//		groupsTable.addColumn("Group Value", "groupvalue"); // col 1
//		groupsTable.addColumn("Parent Group", "parentgroup"); // col 2
		groupsTable.setIndexName("id");

		groupsTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				if(canAddGroup){
					clearForm();
					groupId = Integer.parseInt(data.get("id"));
					groupName.setText(data.get("groupname"));
					getGroupPermissions(groupId);
					tabPanel.selectTab(0);
				}
			}
		});

		// TODO:Backend needs to be fixed first
		retrieveAllGroups();
		Util.setFocus(groupName);
	}

	public void onClick(ClickEvent evt) {
		Widget w = (Widget) evt.getSource();
		if (w == addGroupButton) {

			if (checkInput() == true) {
				
				HashMap<String, String> m=new HashMap<String, String>();
				m.put("groupName",groupName.getText() );
				String requestURL="org.freemedsoftware.module.ACL.AddGroupWithPermissions";
				
				if(groupId!=null){
					requestURL="org.freemedsoftware.module.ACL.ModGroupWithPermissions";
					m.put("groupId", groupId.toString());
				}
				
				
				final HashMap<String, List> permissions = new HashMap<String, List>();
				Iterator<CheckBox> itr = aclPermissionsMap.keySet().iterator();
				
				while(itr.hasNext()){
					CheckBox checkBox = itr.next();
					if(checkBox.getValue()){
						String section = aclPermissionsMap.get(checkBox);
						List sectionValues=permissions.get(section);
						if(sectionValues==null)
							sectionValues = new ArrayList();
						sectionValues.add(checkBox.getText());
						permissions.put(section, sectionValues);
					}
				}
				

				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// Do nothing.
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { JsonUtil.jsonify(m),JsonUtil.jsonify(permissions) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(requestURL
													,
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								CurrentState.getToaster().addItem(className,
										"Failed to add Group.",
										Toaster.TOASTER_ERROR);
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Integer");
									if (r != null) {
										CurrentState.getToaster().addItem(
												className,
												"Successfully Added Group.",
												Toaster.TOASTER_INFO);
										retrieveAllGroups();
										clearForm();
									}else{
										Boolean b = (Boolean) JsonUtil
										.shoehornJson(JSONParser
												.parse(response.getText()),
												"Boolean");
										if(b){
											CurrentState.getToaster().addItem(
													className,
													"Successfully Modified Group.",
													Toaster.TOASTER_INFO);
											retrieveAllGroups();
											clearForm();
										}
									}
								} else {
									CurrentState.getToaster().addItem(
											className, "Failed to add Group.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						CurrentState.getToaster().addItem(className,
								"Failed to send message.",
								Toaster.TOASTER_ERROR);
					}
				} else {
					// TODO: Create GWT-RPC stuff here
				}

			}
		}else if (w == deleteGroupButton) {

			if (true) {
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// Do nothing.
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { JsonUtil.jsonify(groupId) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest("org.freemedsoftware.module.ACL.DelGroupWithPermissions"
													,
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								CurrentState.getToaster().addItem(className,
										"Failed to delete Group.",
										Toaster.TOASTER_ERROR);
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
										Boolean flag = (Boolean) JsonUtil
										.shoehornJson(JSONParser
												.parse(response.getText()),
												"Boolean");
										if(flag){
											CurrentState.getToaster().addItem(
													className,
													"Successfully deleted Group.",
													Toaster.TOASTER_INFO);
											retrieveAllGroups();
											clearForm();
										}
								} else {
									CurrentState.getToaster().addItem(
											className, "Failed to delete Group.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						CurrentState.getToaster().addItem(className,
								"Failed to delete Group.",
								Toaster.TOASTER_ERROR);
					}
				} else {
					// TODO: Create GWT-RPC stuff here
				}

			}
		} else if (w == clearButton) {
			clearForm();
		}
	}

	public Boolean checkInput() {
		String base = "Please check the following fields:" + " ";
		String[] s = {};
		if (groupName.getText() == "") {
			s[s.length] = "Group Name";
		}

		if (s.length == 0) {
			return true;
		}

		for (int i = 0; i < s.length; i++) {
			base = base + s[i];
			if (i != s.length - 1) {
				base = base + ", ";
			}
		}

		Window.alert(base + "\n");

		return false;
	}

	public void clearForm() {
		groupName.setText("");
		addGroupButton.setText("Add Group");
		groupId=null;
		deleteGroupButton.setVisible(false);
		Iterator<CheckBox> itr = aclPermissionsMap.keySet().iterator();
		while(itr.hasNext()){
			CheckBox key = itr.next();
			key.setValue(false);
		}
		groupName.setFocus(true);
	}

	public void retrieveAllGroups() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {JsonUtil.jsonify(true)};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.ACL.userGroups",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
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
								groupsTable.clearData();
								groupsTable.loadData(data);
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
	
	public void getACLPermissions() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.ACL.GetAllPermissions",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String,Object> data = (HashMap<String,Object>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,Object>");
							if (data != null) {
								addACLGroupPermissions(data);
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

	public void getGroupPermissions(Integer groupId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {JsonUtil.jsonify(groupId)};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.ACL.GetGroupPermissions",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String,HashMap<String,String>> data = (HashMap<String,HashMap<String,String>>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,HashMap<String,String>>");
							if (data != null) {
								Iterator<CheckBox> iterator = aclPermissionsMap.keySet().iterator();
								while(iterator.hasNext()){
									CheckBox keyCheckBox = iterator.next();
									String section = aclPermissionsMap.get(keyCheckBox);
									if(data.get(section)!=null && data.get(section).get(keyCheckBox.getText())!=null){
										keyCheckBox.setValue(true);
									}
								}
								addGroupButton.setText("Modify Group");
								deleteGroupButton.setVisible(true);
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
	
	public void addACLGroupPermissions(final HashMap<String,Object> data){

		int row=groupAddTable.getRowCount();
		Iterator<String> iterator = data.keySet().iterator();
		while(iterator.hasNext()){
			final String section = iterator.next();
			String[] values = (String[]) JsonUtil
			.shoehornJson(JSONParser.parse(data.get(section).toString()),
					"String[]");
			HorizontalPanel temPanel=new HorizontalPanel();
			for(int i=0;i<values.length;i++){
					final String value=values[i];
					final CheckBox checkBox = new CheckBox(value);
					temPanel.add(checkBox );
					aclPermissionsMap.put(checkBox, section);
			}
			
			Label label = new Label(section);
			label.setStyleName("label");
			groupAddTable.setWidget(row, 0, label);
			
			groupAddTable.setWidget(row, 1, temPanel);
			row++;
		}
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
