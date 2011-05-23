/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.BlockScreenWidget;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomTable;
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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ACLScreen extends ScreenInterface implements
		ClickHandler {

	public final static String moduleName = "acl";

	protected CustomTable groupsTable = new CustomTable();
	protected  CustomTable groupAddTable; 
	protected Integer groupId = null;
	
	protected TextBox groupName;

	protected CustomButton addGroupButton, clearButton, deleteGroupButton,copyButton;

	protected String className = "ACLScreen";

	protected HashMap<String,CheckBox> aclPermissionsMap=new HashMap<String,CheckBox>();
	
	protected BlockScreenWidget blockScreenWidget = null;
	
	protected VerticalPanel aclContainerVPanel = null;
	
	protected TabPanel tabPanel = null; 
	
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
		super(moduleName);
		aclContainerVPanel = new VerticalPanel();
		initWidget(aclContainerVPanel);

		blockScreenWidget = new BlockScreenWidget("Please wait while modules are being populated....");
		aclContainerVPanel.add(blockScreenWidget);
		tabPanel = new TabPanel();
		aclContainerVPanel.add(tabPanel);

		// Panel #1
		if(canWrite){
			VerticalPanel groupAddPanel = new VerticalPanel();
			tabPanel.add(groupAddPanel, "Add Group");
			tabPanel.selectTab(0);
			
			HorizontalPanel groupNameHPanel = new HorizontalPanel();
			groupAddPanel.add(groupNameHPanel);
			groupNameHPanel.setSpacing(5);
			groupNameHPanel.add(new Label("Group Name"));
			
			groupName = new TextBox();
			groupName.setWidth("10em");
			groupNameHPanel.add(groupName);
			

			groupAddTable = new CustomTable();
			groupAddTable.removeTableStyle();
			groupAddPanel.add(groupAddTable);
			
			Label moduleHeading = new Label("Modules");
			moduleHeading.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
			groupAddTable.getFlexTable().setWidget(0, 0, moduleHeading);
			HorizontalPanel headerButtonPanels = new HorizontalPanel();
			headerButtonPanels.setWidth("100%");
			groupAddTable.getFlexTable().setWidget(0, 1, headerButtonPanels);
			CustomButton selectAllBtn = new CustomButton("Select All",AppConstants.ICON_SELECT_ALL);
			selectAllBtn.setWidth("100%");
			selectAllBtn.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					Iterator<String> iterator = aclPermissionsMap.keySet().iterator();
					while(iterator.hasNext()){
						aclPermissionsMap.get(iterator.next()).setValue(true);
					}
				}
			});
			headerButtonPanels.add(selectAllBtn);
			CustomButton selectNoneBtn = new CustomButton("Select None",AppConstants.ICON_SELECT_NONE);
			selectNoneBtn.setWidth("100%");
			selectNoneBtn.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					Iterator<String> iterator = aclPermissionsMap.keySet().iterator();
					while(iterator.hasNext()){
						aclPermissionsMap.get(iterator.next()).setValue(false);
					}
				}
			});
			headerButtonPanels.add(selectNoneBtn);
			
			addGroupButton = new CustomButton("Add Group",AppConstants.ICON_ADD);
			addGroupButton.setWidth("100%");
			addGroupButton.addClickHandler(this);
			headerButtonPanels.add(addGroupButton);

			copyButton = new CustomButton("Copy",AppConstants.ICON_ADD);
			copyButton .setWidth("100%");
			copyButton.addClickHandler(this);
			copyButton.setVisible(false);
			headerButtonPanels.add(copyButton);
			
			deleteGroupButton = new CustomButton("Delete Group",AppConstants.ICON_DELETE);
			deleteGroupButton .setWidth("100%");
			deleteGroupButton.addClickHandler(this);
			deleteGroupButton.setVisible(false);
			headerButtonPanels.add(deleteGroupButton);
			
			clearButton = new CustomButton("Reset",AppConstants.ICON_CLEAR);
			clearButton.setWidth("100%");
			clearButton.addClickHandler(this);
			headerButtonPanels.add(clearButton);
			
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
				if(canWrite){
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
	
	public HashMap<String, List> populateAllPermissions(){
		HashMap<String, List> allPermiHashMap = new HashMap<String, List>();
		
		final Iterator<String> aclPermissionsMapItr = aclPermissionsMap.keySet().iterator();
		
		int permissionAddedCounter=0;
		while(aclPermissionsMapItr.hasNext()){
			String sectionWithValue = aclPermissionsMapItr.next();
			CheckBox checkBox = aclPermissionsMap.get(sectionWithValue);
			if(checkBox.getValue()){
				String section = sectionWithValue.substring(0, sectionWithValue.indexOf(":"));
				List sectionValues=allPermiHashMap.get(section);
				if(sectionValues==null)
					sectionValues = new ArrayList();
				sectionValues.add(checkBox.getText());
				allPermiHashMap.put(section, sectionValues);
				permissionAddedCounter++;
			}
		}
		
		return allPermiHashMap;
	}
	
	public void onClick(ClickEvent evt) {
		Widget w = (Widget) evt.getSource();
		if (w == addGroupButton) {

			if (checkInput() == true) {
				blockScreenWidget.setText("Please wait while permissions are being applied......");
				aclContainerVPanel.add(blockScreenWidget);
				HashMap<String, String> m=new HashMap<String, String>();
				m.put("groupName",groupName.getText() );
				String requestURL="org.freemedsoftware.module.ACL.AddGroupWithPermissions";
				
				if(groupId!=null){
					requestURL="org.freemedsoftware.module.ACL.ModGroupWithPermissions";
					m.put("groupId", groupId.toString());
				}
				
				final HashMap<String, List> permissions = new HashMap<String, List>();
				final HashMap<String, List> allPermiHashMap = populateAllPermissions();
				
				final Iterator<String> aclPermissionsMapItr = allPermiHashMap.keySet().iterator();
				
				int permissionAddedCounter=0;
				while(aclPermissionsMapItr.hasNext()){
					String section = aclPermissionsMapItr.next();
					permissions.put(section, allPermiHashMap.get(section));
					permissionAddedCounter++;
					if(permissionAddedCounter==8){
						if(allPermiHashMap.get("admin")!=null)
							permissions.put("admin", allPermiHashMap.get("admin"));
						if(allPermiHashMap.get("acl")!=null)
							permissions.put("acl", allPermiHashMap.get("acl"));
						break;
					}
				}
				
				if(permissions.size()==0){
					Window.alert("Please Select at least one module!");
					aclContainerVPanel.remove(blockScreenWidget);
					return;
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
								Util.showErrorMsg("Bottle Transfer", "Failed to add Group.");
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Integer");
									if (r != null) {
										if(aclPermissionsMapItr.hasNext())
											sendDataInChucks(aclPermissionsMapItr,allPermiHashMap,r);//Sending data to server in chunks
										else{
											aclContainerVPanel.remove(blockScreenWidget);
											retrieveAllGroups();
											clearForm();
											Util.showInfoMsg(className, "Permissions successfully applied.");
										}
									}else{
										Boolean b = (Boolean) JsonUtil
										.shoehornJson(JSONParser
												.parse(response.getText()),
												"Boolean");
										if(b){
											if(aclPermissionsMapItr.hasNext())
												sendDataInChucks(aclPermissionsMapItr,allPermiHashMap,groupId);//Sending data to server in chunks
											else{
												aclContainerVPanel.remove(blockScreenWidget);
												retrieveAllGroups();
												clearForm();
												Util.showInfoMsg(className, "Permissions successfully applied.");
											}
										}
									}
								} else {
									Util.showErrorMsg("Bottle Transfer", "Failed to Apply Permissions.");
								}
							}
						});
					} catch (RequestException e) {
						Util.showErrorMsg("Bottle Transfer", "Failed to apply Permissions.");
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
								Util.showErrorMsg("Bottle Transfer", "Failed to delete Group.");
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
										Boolean flag = (Boolean) JsonUtil
										.shoehornJson(JSONParser
												.parse(response.getText()),
												"Boolean");
										if(flag){
											Util.showInfoMsg(className, "Successfully deleted Group.");
											retrieveAllGroups();
											clearForm();
										}
								} else {
									Util.showErrorMsg("Bottle Transfer", "Failed to add Group.");
								}
							}
						});
					} catch (RequestException e) {
						Util.showErrorMsg("Bottle Transfer", "Failed to add Group.");
					}
				} else {
					// TODO: Create GWT-RPC stuff here
				}

			}
		} else if (w == clearButton) {
			clearForm();
		} else if (w == copyButton) {
			copyGroup();
		}
	}

	private void sendDataInChucks(final Iterator<String> aclPermissionsMapItr,final HashMap<String, List> allPermissionHashMap,final int groupId){
		final HashMap<String, List> permissions = new HashMap<String, List>();
		if(aclPermissionsMapItr.hasNext()){
			int permissionAddedCounter=0;
			while(aclPermissionsMapItr.hasNext()){
					String section = aclPermissionsMapItr.next();
					permissions.put(section, allPermissionHashMap.get(section));
					permissionAddedCounter++;
					if(permissionAddedCounter==10 || !aclPermissionsMapItr.hasNext()){
						permissionAddedCounter=0;
						List params = new ArrayList();
						params.add(groupId);
						params.add(permissions);
						Util.callModuleMethod("ACL", "AddMorePermissions", params, new CustomRequestCallback() {
							@Override
							public void onError() {
							}
							@Override
							public void jsonifiedData(Object data) {
								sendDataInChucks(aclPermissionsMapItr, allPermissionHashMap, groupId);
								if(!aclPermissionsMapItr.hasNext()){
									retrieveAllGroups();
									clearForm();
									Util.showInfoMsg(className, "Permissions successfully applied.");
									aclContainerVPanel.remove(blockScreenWidget);
								}
							}
						}, "Boolean");
						permissions.clear();
						break;
					}
			}
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
		copyButton.setVisible(false);
		Iterator<String> itr = aclPermissionsMap.keySet().iterator();
		while(itr.hasNext()){
			String sectionWithValue = itr.next();
			aclPermissionsMap.get(sectionWithValue).setValue(false);
		}
		groupName.setFocus(true);
	}

	public void copyGroup(){
		groupName.setText("");
		addGroupButton.setText("Add Group");
		groupId=null;
		deleteGroupButton.setVisible(false);
		copyButton.setVisible(false);
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
							HashMap<String,String[]> data = (HashMap<String,String[]>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String[]>");
							if (data != null) {
								addACLGroupPermissions(data);
								blockScreenWidget.removeFromParent();
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
							HashMap<String,String[]> data = (HashMap<String,String[]>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String[]>");
							if (data != null) {
								Iterator<String> iterator = data.keySet().iterator();
								while(iterator.hasNext()){
									final String section = iterator.next();
									String[] values = data.get(section);
									for(int i=0;i<values.length;i++){
											final String value=values[i];
											if(aclPermissionsMap.get(section+":"+value)!=null)
												aclPermissionsMap.get(section+":"+value).setValue(true);
									}
								}
								addGroupButton.setText("Modify Group");
								deleteGroupButton.setVisible(true);
								copyButton.setVisible(true);
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
	
	public void addACLGroupPermissions(final HashMap<String,String[]> data){

		int row=groupAddTable.getFlexTable().getRowCount();
		Iterator<String> iterator = data.keySet().iterator();
		while(iterator.hasNext()){
			final String section = iterator.next();
			final String[] values = data.get(section);
			HorizontalPanel temPanel=new HorizontalPanel();
			temPanel.setWidth("100%");
			for(int i=0;i<values.length;i++){
					final String value=values[i];
					final CheckBox checkBox = new CheckBox(value);
					temPanel.add(checkBox );
					aclPermissionsMap.put(section+":"+value,checkBox);
			}
			
			CustomButton clearSubLink = new CustomButton("None",AppConstants.ICON_SELECT_NONE);
			clearSubLink.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					for(int i=0;i<values.length;i++){
						final String value=values[i];
						aclPermissionsMap.get(section+":"+value).setValue(false);
				}
				}
			});
			temPanel.add(clearSubLink);
			
			CustomButton selectAllSubLink = new CustomButton("All",AppConstants.ICON_SELECT_ALL);
			selectAllSubLink.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					for(int i=0;i<values.length;i++){
						final String value=values[i];
						aclPermissionsMap.get(section+":"+value).setValue(true);
				}
				}
			});
			temPanel.add(selectAllSubLink);
			
			Label label = new Label(section);
			label.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
			groupAddTable.getFlexTable().setWidget(row, 0, label);
			
			groupAddTable.getFlexTable().setWidget(row, 1, temPanel);
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
