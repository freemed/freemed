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
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.BlockScreenWidget;
import org.freemedsoftware.gwt.client.widget.CustomAlert;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonGroup;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Style.BorderStyle;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
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
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class UserManagementScreen extends ScreenInterface implements
		ClickHandler {

	public final static String moduleName = "admin";

	protected CustomTable wUsers = new CustomTable();

	protected Integer userId = null;
	
	protected TextBox tbUsername,tbDescription;

	protected PasswordTextBox tbPassword,tbPasswordverify;

	protected  HTML changePasswordLink;
	
	protected CustomListBox lbUserType;

	protected SupportModuleWidget lbActualPhysician;

	protected CustomButton addUserButton, clearButton, deleteUserButton, copyButton;

	protected String className = "UserManagementScreen";

	protected Set<Integer> aclSelectedGroupsIdsList=new HashSet<Integer>();
	
	protected FlexTable aclGroupsTable=new FlexTable();
	
	protected HashMap<Integer,CheckBox> aclGroupsCheckBoxesMap=new HashMap<Integer,CheckBox>();//
	
	protected HashMap<Integer,CheckBox> facilitiesCheckBoxesMap=new HashMap<Integer,CheckBox>();//
	
	protected HashMap<String,CheckBox> allAclPermissionsMap=new HashMap<String,CheckBox>();//Contains all Permissions by using key as "Section:Value" and value as "CheckBox"
	
	protected HashMap<String, Integer> selectedPermissionsMap = new HashMap<String, Integer>();//Contains selected Permissions by using key as "Section:Value" and Integer as count of selected groups that contains this "Section and value" 
	
	protected HashMap<String, List> blockedPermissionsMap = new HashMap<String, List>();//The Permissions which exist in selected Group but unchecked under enhanced permissions
	
	protected HashMap<String, List> allowedPermissionsMap = new HashMap<String, List>();//The Permissions which does not exist in selected Group but checked under enhanced permissions
	
	protected CustomTable customizePermissionsTable = new CustomTable();
	
	final String showCustPermissionsString = "Enhance Permissions";
	
	final String hideCustPermissionsString = "Hide Permissions";

	protected CustomButton customizePermissionsLink = new CustomButton(showCustPermissionsString,AppConstants.ICON_ADD);
	
	protected VerticalPanel addUserVPanel = null; 
	protected TabPanel tabPanel = null;
	private static List<UserManagementScreen> userManagementScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static UserManagementScreen getInstance(){
		UserManagementScreen userManagementScreen=null; 
		
		if(userManagementScreenList==null)
			userManagementScreenList=new ArrayList<UserManagementScreen>();
		if(userManagementScreenList.size()<AppConstants.MAX_USERMANAGEMENT_TABS)//creates & returns new next instance of SupportDataScreen
			userManagementScreenList.add(userManagementScreen=new UserManagementScreen());
		else //returns last instance of SupportDataScreen from list 
			userManagementScreen = userManagementScreenList.get(AppConstants.MAX_USERMANAGEMENT_TABS-1);
		return userManagementScreen;
	}
	
	public static boolean removeInstance(UserManagementScreen userManagementScreen){
		return userManagementScreenList.remove(userManagementScreen);
	}
	
	public UserManagementScreen() {
		super(moduleName);
		
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);
		
		addUserVPanel = new VerticalPanel();
		// Panel #1
		if(canWrite || canModify){
			if(canWrite)
				tabPanel.add(addUserVPanel, "Add User");
			
			final FlexTable  userAddTable = new FlexTable();
			addUserVPanel.add(userAddTable);
			
			int row = 0;
			
			final Label usernameLabel = new Label("User Name");
			userAddTable.setWidget(row, 0, usernameLabel);
	
			tbUsername = new TextBox();
			userAddTable.setWidget(row, 1, tbUsername);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbUsername.setWidth("20em");
	
			row++;
			
			final Label passwordLabel = new Label("Password");
			userAddTable.setWidget(row, 0, passwordLabel);
	
			tbPassword = new PasswordTextBox();
			userAddTable.setWidget(row, 1, tbPassword);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbPassword.setWidth("20em");
	
			row++;
			
			final Label passwordverifyLabel = new Label("Password (Verify)");
			userAddTable.setWidget(row, 0, passwordverifyLabel);
	
			final HorizontalPanel horizontalPanel = new HorizontalPanel();
			horizontalPanel.setWidth("100%");
			userAddTable.setWidget(row, 1, horizontalPanel);
			
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			
			tbPasswordverify = new PasswordTextBox();
			tbPasswordverify.setWidth("20em");
			horizontalPanel.add(tbPasswordverify);
			
			final String changePassString = "<a href='javascript:undefined'> change password </a>";
			final String donotChangePassString = "<a href='javascript:undefined'>don't change password </a>";
			changePasswordLink = new HTML(changePassString);
			changePasswordLink.setVisible(false);
			changePasswordLink.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					if(tbPassword.isEnabled()){
						tbPassword.setEnabled(false);
						tbPasswordverify.setEnabled(false);
						changePasswordLink.setHTML(changePassString);
					}else{
						tbPassword.setEnabled(true);
						tbPasswordverify.setEnabled(true);
						changePasswordLink.setHTML(donotChangePassString);
					}
				}
			});
			horizontalPanel.add(changePasswordLink);

			row++;
	
			final Label descriptionLabel = new Label("Description");
			userAddTable.setWidget(row, 0, descriptionLabel);
	
			tbDescription = new TextBox();
			userAddTable.setWidget(row, 1, tbDescription);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbDescription.setWidth("100%");
	
			row++;
			
			final Label userfnameLabel = new Label("First Name");
			userAddTable.setWidget(row, 0, userfnameLabel);
	
			tbUserFirstName = new TextBox();
			userAddTable.setWidget(row, 1, tbUserFirstName);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbUserFirstName.setWidth("20em");
	
			row++;
	
			
			final Label userMiddlenameLabel = new Label("Middle Name");
			userAddTable.setWidget(row, 0, userMiddlenameLabel);
	
			tbUserMiddleName = new TextBox();
			userAddTable.setWidget(row, 1, tbUserMiddleName);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbUserMiddleName.setWidth("20em");
	
			row++;
	
			
			final Label userLastNameLabel = new Label("Last Name");
			userAddTable.setWidget(row, 0, userLastNameLabel);
	
			tbUserLastName = new TextBox();
			userAddTable.setWidget(row, 1, tbUserLastName);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbUserLastName.setWidth("20em");
	
			row++;
	
			
			final Label userTitleLabel = new Label("User Title");
			userAddTable.setWidget(row, 0, userTitleLabel);
	
			tbUserTitle= new CustomRadioButtonGroup("title");
			tbUserTitle.addItem("Mr");
			tbUserTitle.addItem("Mrs");
			tbUserTitle.addItem("Ms");
			tbUserTitle.addItem("Dr");
			tbUserTitle.addItem("Fr");
			userAddTable.setWidget(row, 1, tbUserTitle);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			tbUserTitle.setWidth("20em");
	
			row++;
	
			final Label userTypeLabel = new Label("User Type");
			userAddTable.setWidget(row, 0, userTypeLabel);
	
			lbUserType = new CustomListBox();
			userAddTable.setWidget(row, 1, lbUserType);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			lbUserType.addItem("Select User Type", "null");
			lbUserType.addItem("Miscellaneous", "misc");
			lbUserType.addItem("Provider", "phy");
	
			row++;
			
			final Label actualPhysicianLabel = new Label("Actual Physician");
			userAddTable.setWidget(row, 0, actualPhysicianLabel);
			actualPhysicianLabel.setVisible(false);
	
			lbActualPhysician = new SupportModuleWidget("ProviderModule");
			userAddTable.setWidget(row, 1, lbActualPhysician);
			userAddTable.getFlexCellFormatter().setColSpan(row, 1, 2);
			lbActualPhysician.setVisible(false);
	
			lbUserType.addChangeHandler(new ChangeHandler() {
				public void onChange(ChangeEvent evt) {
					Widget sender = (Widget) evt.getSource();
					String value = ((CustomListBox) sender).getWidgetValue();
					if (value.compareTo("phy") == 0) {
						// Is provider
						lbActualPhysician.setVisible(true);
						actualPhysicianLabel.setVisible(true);
					} else {
						// Is not provider
						lbActualPhysician.setVisible(false);
						actualPhysicianLabel.setVisible(false);
					}
				}
			});
	
			row++;
			
			final Label facilityLabel = new Label("Facility");
			userAddTable.setWidget(row, 0, facilityLabel);
			VerticalPanel facilityVPanel = new VerticalPanel();
			facilityVPanel.setStyleName("top-border-only");
			userAddTable.setWidget(row, 1, facilityVPanel);
			final FlexTable facilityTable = new FlexTable();
			facilityVPanel.add(facilityTable);
			Util.callModuleMethod("FacilityModule", "GetAll", (Integer)null, new CustomRequestCallback() {
			
				@Override
				public void onError() {
					// TODO Auto-generated method stub
			
				}
			
				@Override
				public void jsonifiedData(Object data) {
					// TODO Auto-generated method stub
					int tempRow = 0;
					int tempCol = 0;
					HashMap<String, String>[] result =(HashMap<String, String>[])data;
					for(int i=0;i<result.length;i++){
						HashMap<String, String> facilityMap = result[i];
						CheckBox checkBox = new CheckBox(facilityMap.get("psrname"));
						Integer id= Integer.parseInt(facilityMap.get("id"));
						facilitiesCheckBoxesMap.put(id, checkBox);
						facilityTable.setWidget(tempRow, tempCol, checkBox);
						tempCol++;
						if(tempCol==3){
							tempCol = 0;
							tempRow++; 
						}
							
					}
					
				}
			
			}, "HashMap<String,String>[]");
			
			row++;
			
			final Label aclLabel = new Label("User Groups");
			userAddTable.setWidget(row, 0, aclLabel);
			final VerticalPanel aclGroupsVpanel = new VerticalPanel();
			aclGroupsVpanel.setStyleName("top-border-only");
			aclGroupsVpanel.add(aclGroupsTable);
			userAddTable.setWidget(row, 1, aclGroupsVpanel);
			
			HorizontalPanel buttonsPanel = new HorizontalPanel();
			addUserButton = new CustomButton("Add User",AppConstants.ICON_ADD_PERSON);
			addUserButton.addClickHandler(this);
			buttonsPanel.add(addUserButton);
	
			copyButton = new CustomButton("Copy",AppConstants.ICON_ADD);
			copyButton.addClickHandler(this);
			copyButton.setVisible(false);
			buttonsPanel.add(copyButton);

			buttonsPanel.add(customizePermissionsLink);
			
			deleteUserButton = new CustomButton("Delete User",AppConstants.ICON_REMOVE_PERSON);
			deleteUserButton.addClickHandler(this);
			deleteUserButton.setVisible(false);
			buttonsPanel.add(deleteUserButton);
			
			clearButton = new CustomButton("Reset",AppConstants.ICON_CLEAR);
			clearButton.addClickHandler(this);
			buttonsPanel.add(clearButton);

			row++;
			
			userAddTable.setWidget(row, 1, buttonsPanel);
			getACLGroups();
			
			showEnhancedPermssions(false);
			customizePermissionsTable.removeTableStyle();
			addUserVPanel.add(customizePermissionsTable);
			
			
			customizePermissionsLink.addClickHandler(new ClickHandler() {
				boolean show = false;
				@Override
				public void onClick(ClickEvent arg0) {
					show = !show;
					showEnhancedPermssions(show);
				}//End onlick
			});//End customizePermissionsLink AddClick Handler
		}
		// Panel #2

		final FlexTable userListTable = new FlexTable();
		tabPanel.add(userListTable, "List Users");

		userListTable.setWidget(0, 0, wUsers);

		wUsers.setSize("100%", "100%");
		wUsers.addColumn("Username", "username"); // col 0
		wUsers.addColumn("Description", "userdescrip"); // col 1
		wUsers.addColumn("First Name", "userfname"); // col 2
		wUsers.addColumn("Last Name", "userlname"); // col 3
		wUsers.addColumn("Middle Name", "usermname"); // col 4
		wUsers.addColumn("Title", "usertitle"); // col 5
		wUsers.addColumn("Level", "userlevel"); // col 6
		wUsers.addColumn("Type", "usertype"); // col 7
		wUsers.setIndexName("id");

		wUsers.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				if(canWrite || canModify){
					if(!canWrite){
						tabPanel.add(addUserVPanel, "Modify User");
						tabPanel.selectTab(1);
					}else
						tabPanel.selectTab(0);
					clearForm();
					userId = Integer.parseInt(data.get("id"));
					getUserDetails(userId);
					getUserGroup(userId);
					
					Util.callModuleMethod("ACL", "GetBlockedACOs", userId, new CustomRequestCallback() {
						@Override
						public void onError() {
						}
						
						@Override
						public void jsonifiedData(Object data) {
							HashMap<String, List> result = (HashMap<String, List>)data; 
							if(result!=null && result.size()>0){
								alreadyShowingEnhancedPermissions = true;
								blockedPermissionsMap =(HashMap<String,List>)data;
								alreadyShowingEnhancedPermissions = false;
								//setCheckBoxesValue((HashMap<String, String[]>)data, false);
							}
							Util.callModuleMethod("ACL", "GetAllowedACOs", userId, new CustomRequestCallback() {
								@Override
								public void onError() {
								}
								@Override
								public void jsonifiedData(Object data) {
									HashMap<String, List> result = (HashMap<String, List>)data; 
									if(result!=null && result.size()>0){
										allowedPermissionsMap =(HashMap<String,List>)data; 
										alreadyShowingEnhancedPermissions = false;
										//setCheckBoxesValue((HashMap<String, String[]>)data, false);
									}
									if(blockedPermissionsMap!=null && blockedPermissionsMap.size()>0 || allowedPermissionsMap!=null && allowedPermissionsMap.size()>0)
										showEnhancedPermssions(true);
								}
							}, "HashMap<String,List>");
						}
					}, "HashMap<String,List>");

				}
			}
		});
		
		
		// TODO:Backend needs to be fixed first
		retrieveAllUsers();
		tabPanel.selectTab(0);
		Util.setFocus(tbUsername);
	}
	boolean alreadyShowingEnhancedPermissions = false;
	protected synchronized void showEnhancedPermssions(boolean show){
		if(!show){
			customizePermissionsTable.setVisible(false);
			customizePermissionsLink.setText(showCustPermissionsString);
		}else{
			customizePermissionsTable.setVisible(true);
			customizePermissionsLink.setText(hideCustPermissionsString);
			if(allAclPermissionsMap.size()==0){
				final BlockScreenWidget blockScreenWidget = new BlockScreenWidget("Please wait while modules are being populated....");
				addUserVPanel.add(blockScreenWidget);
				Label moduleHeading = new Label("Modules");
				moduleHeading.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
				customizePermissionsTable.getFlexTable().setWidget(0, 0, moduleHeading);
				HorizontalPanel headerButtonPanels = new HorizontalPanel();
				customizePermissionsTable.getFlexTable().setWidget(0, 1, headerButtonPanels);
				CustomButton selectAllBtn = new CustomButton("Select All",AppConstants.ICON_SELECT_ALL);
				selectAllBtn.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						Iterator<String> iterator = allAclPermissionsMap.keySet().iterator();
						while(iterator.hasNext()){
							allAclPermissionsMap.get(iterator.next()).setValue(true);
						}
					}
				});
				headerButtonPanels.add(selectAllBtn);
				CustomButton selectNoneBtn =new CustomButton(" Select None",AppConstants.ICON_SELECT_NONE);
				selectNoneBtn.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						Iterator<String> iterator = allAclPermissionsMap.keySet().iterator();
						while(iterator.hasNext()){
							allAclPermissionsMap.get(iterator.next()).setValue(false);
						}
						Iterator<Integer> itrGroupIds = aclGroupsCheckBoxesMap.keySet().iterator();
						while(itrGroupIds.hasNext()){
							aclGroupsCheckBoxesMap.get(itrGroupIds.next()).setValue(false);
						}
						blockedPermissionsMap.clear();
						allowedPermissionsMap.clear();
						selectedPermissionsMap.clear();
					}
				});
				headerButtonPanels.add(selectNoneBtn);
				
				//Getting list of all available permissions from acl_aco table
				Util.callModuleMethod("ACL", "GetAllPermissions", (Integer)null, new CustomRequestCallback() {
					@Override
					public void onError() {
						addUserVPanel.remove(blockScreenWidget);
					}
					@Override
					public void jsonifiedData(Object data) {
						if(data!=null){
							HashMap<String,String[]> result = (HashMap<String,String[]>)data;
							int row = 1;
							Iterator<String> iterator = result.keySet().iterator();
							while(iterator.hasNext()){
								final String section = iterator.next();
								final String[] values = result.get(section);
								HorizontalPanel temPanel=new HorizontalPanel();
								for(int i=0;i<values.length;i++){
										final String value=values[i];
										final CheckBox checkBox = new CheckBox(value);
										temPanel.add(checkBox );
										allAclPermissionsMap.put(section+":"+value,checkBox);
								}
								CustomButton selectNoneBtn = new CustomButton("None",AppConstants.ICON_SELECT_NONE);
								selectNoneBtn.addClickHandler(new ClickHandler() {
									@Override
									public void onClick(ClickEvent arg0) {
										for(int i=0;i<values.length;i++){
											final String value=values[i];
											allAclPermissionsMap.get(section+":"+value).setValue(false);
									}
									}
								});
								temPanel.add(selectNoneBtn);
								
								CustomButton selectAllBtn = new CustomButton("All",AppConstants.ICON_SELECT_ALL);
								selectAllBtn.addClickHandler(new ClickHandler() {
									@Override
									public void onClick(ClickEvent arg0) {
										for(int i=0;i<values.length;i++){
											final String value=values[i];
											allAclPermissionsMap.get(section+":"+value).setValue(true);
									}
									}
								});
								temPanel.add(selectAllBtn);
								Label label = new Label(section);
								label.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
								customizePermissionsTable.getFlexTable().setWidget(row, 0, label);
								
								customizePermissionsTable.getFlexTable().setWidget(row, 1, temPanel);
								row++;
							}// end while
							reselectGroups();
						}
						addUserVPanel.remove(blockScreenWidget);
					}//end jsonifiedData
				}, "HashMap<String,String[]>");
			}else// end if allAclPermissionsMap.size() == 0
				reselectGroups();
		}//End else
		
	}  
	
	public void reselectGroups(){
		//Reselecting the checkboxes so that onclick could be fired and required checkboxes can be checked by default.
		Iterator<Integer> itr = aclGroupsCheckBoxesMap.keySet().iterator();
		while(itr.hasNext()){
			Integer key = itr.next();
			CheckBox checkBox = aclGroupsCheckBoxesMap.get(key);
			if(checkBox.getValue()){
				JsonUtil.debug("reselectGroups:"+key);
				checkBox.setValue(false);
				checkBox.setValue(true,true);
			}
		}//End while
	}
	
	public void onClick(ClickEvent evt) {
		Widget w = (Widget) evt.getSource();
		if (w == addUserButton) {

			if (checkInput() == true) {
				if(userId!=null)
					addUser();
				else{
					List params = new ArrayList();
					params.add(tbUsername.getText());
					Util.callApiMethod("UserInterface", "CheckDupilcate", params, new CustomRequestCallback() {
					
						@Override
						public void onError() {
							Util.showErrorMsg(moduleName, "Failed!");
						}
					
						@Override
						public void jsonifiedData(Object data) {
							if(data==null)
								onError();
							Boolean flag = (Boolean)data;
							if(flag){
								Util.showErrorMsg(moduleName, "User Already Exists!!!");
								Util.alert("This user is already in the system!!");
							}else
								addUser();
						}
					
					}, "Boolean");
				}
			}
		}else if (w == deleteUserButton) {
			
			deleteUser();
	
		} else if (w == clearButton) {
			clearForm();
		} else if (w == copyButton) {
			clearForm();
		}
	}

	protected void addUser(){
		String requestURL="org.freemedsoftware.api.UserInterface.add";
		HashMap<String, String> hm = new HashMap<String, String>();
		if(userId!=null){
			hm.put("id", userId.toString());
			requestURL="org.freemedsoftware.api.UserInterface.mod";
		}
			
		hm.put("username", tbUsername.getText());
		if(tbPassword.isEnabled())
			hm.put("userpassword", tbPassword.getText());

		hm.put("userfname", tbUserFirstName.getText());
		hm.put("userlname", tbUserLastName.getText());
		hm.put("usermname", tbUserMiddleName.getText());
		hm.put("usertitle", tbUserTitle.getWidgetValue());
		hm.put("userdescrip", tbDescription.getText());
		String usertype = lbUserType.getValue(lbUserType
				.getSelectedIndex());
		hm.put("usertype", usertype);
		if (usertype == "phy") {
			hm.put("userrealphy", lbActualPhysician.getValue().toString());
		}
		
		String userfac="";
		Iterator<Integer> itr = facilitiesCheckBoxesMap.keySet().iterator();
		while(itr.hasNext()){
			Integer id = itr.next();
			CheckBox checkBox =  facilitiesCheckBoxesMap.get(id);
			if(checkBox.getValue())
				userfac=userfac+id.toString()+",";
		}
		hm.put("userfac", userfac);
		String useracl="";
		itr = aclSelectedGroupsIdsList.iterator();
		while(itr.hasNext()){
			useracl=useracl+itr.next().toString();
			if(itr.hasNext())
				useracl=useracl+",";
		}
		hm.put("useracl", useracl);
		
		List paramsList = new ArrayList();
		paramsList.add(JsonUtil.jsonify(hm));
		
		if(customizePermissionsTable.isVisible()){
			calculateBlockedAndAllowedACLSections();
			if(blockedPermissionsMap.size()>0)
				paramsList.add(JsonUtil.jsonify(blockedPermissionsMap));
			else
				paramsList.add("");
			if(allowedPermissionsMap.size()>0)
				paramsList.add(JsonUtil.jsonify(allowedPermissionsMap));
			else
				paramsList.add("");
		}
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing.
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = (String[])paramsList.toArray(new String[0]);
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
						Util.showErrorMsg("UserManagementScreen", "Failed to add user.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Integer r = (Integer) JsonUtil
									.shoehornJson(JSONParser
											.parse(response.getText()),
											"Integer");
							if (r != null) {
								Util.showInfoMsg("UserManagementScreen", "Successfully Added User.");
								retrieveAllUsers();
								clearForm();
							}else{
								Boolean b = (Boolean) JsonUtil
								.shoehornJson(JSONParser
										.parse(response.getText()),
										"Boolean");
								if(b){
									Util.showInfoMsg("UserManagementScreen", "Successfully Modified User.");
									retrieveAllUsers();
									clearForm();
								}
							}
						} else {
							Util.showErrorMsg("UserManagementScreen", "Failed to add user.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("UserManagementScreen", "Failed to add user.");
			}
		} else {
			// TODO: Create GWT-RPC stuff here
		}

	}
	
	protected void deleteUser(){
		if (true) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// Do nothing.
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { JsonUtil.jsonify(userId) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest("org.freemedsoftware.api.UserInterface.del"
												,
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							Util.showErrorMsg("UserManagementScreen", "Failed to delete user.");
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
									Boolean flag = (Boolean) JsonUtil
									.shoehornJson(JSONParser
											.parse(response.getText()),
											"Boolean");
									if(flag){
										Util.showInfoMsg("UserManagementScreen", "Successfully deleted User.");
										retrieveAllUsers();
										clearForm();
									}
							} else {
								Util.showErrorMsg("UserManagementScreen", "Failed to delete user.");
							}
						}
					});
				} catch (RequestException e) {
					Util.showErrorMsg("UserManagementScreen", "Failed to delete user.");
				}
			} else {
				// TODO: Create GWT-RPC stuff here
			}

		}
	}
	
	public Boolean checkInput() {
		String base = "Please check the following fields:" + " ";
		String msg = "";
		if (tbUsername.getText().equals("")) {
			msg += "\nUsername";
		}
		if(userId==null){//if modifying user then no need to get new password
			if (!tbPassword.getText().equals("")) {
				if (!tbPassword.getText().equals(tbPasswordverify.getText())) {
					msg += "\nPasswords do not match!";
				}
			} else {
				msg += "\nPassword";
			}
		}

		if (tbUserFirstName.getText().equals("")) {
			msg += "\nFirst Name!";
		}

		if (tbUserLastName.getText().equals("")) {
			msg += "\nLast Name!";
		}

		if (tbUserTitle.getWidgetValue()==null) {
			msg += "\nTitle!";
		}
		
		if (lbUserType.getWidgetValue().equals("null")) {
			msg += "\nUser Type";
		} else if (lbUserType.getWidgetValue().equals("phy")) {
			if (lbActualPhysician.getText() .equals("")) {
				msg += "\nActual Physician";
			}
		}

		if (msg.length() == 0) {
			return true;
		}

		Window.alert(base+msg + "\n" );

		return false;
	}

	public void clearForm() {
		tbUsername.setText("");
		tbPassword.setEnabled(true);
		tbPassword.setText("");
		tbPasswordverify.setEnabled(true);
		tbPasswordverify.setText("");
		tbUserFirstName.setText("");
		tbUserLastName.setText("");
		tbUserMiddleName.setText("");
		tbUserTitle.setWidgetValue("");
		changePasswordLink.setVisible(false);
		tbDescription.setText("");
		lbUserType.setWidgetValue("null");
		lbActualPhysician.clear();
		addUserButton.setText("Add User");
		userId=null;
		deleteUserButton.setVisible(false);
		copyButton.setVisible(false);
		aclSelectedGroupsIdsList.clear();
		lbActualPhysician.setVisible(false);
		Iterator<Integer> itr = aclGroupsCheckBoxesMap.keySet().iterator();
		while(itr.hasNext()){
			Integer key = itr.next();
			CheckBox checkBox = aclGroupsCheckBoxesMap.get(key);
			checkBox.setValue(false);
		}
		
		itr = facilitiesCheckBoxesMap.keySet().iterator();
		while(itr.hasNext()){
			Integer key = itr.next();
			CheckBox checkBox = facilitiesCheckBoxesMap.get(key);
			checkBox.setValue(false);
		}
		

		Iterator<String> itr2 = allAclPermissionsMap.keySet().iterator();
		while(itr2.hasNext()){
			String key = itr2.next();
			CheckBox checkBox = allAclPermissionsMap.get(key);
			checkBox.setValue(false);
		}
		
		
		showEnhancedPermssions(false);
		selectedPermissionsMap.clear();
		blockedPermissionsMap.clear(); 
		allowedPermissionsMap.clear();
		
		tbUsername.setFocus(true);
		
		if(!canWrite && canModify)
			tabPanel.remove(addUserVPanel);
	}

	public void copyUser(){
		tbUsername.setText("");
		tbPassword.setEnabled(true);
		tbPassword.setText("");
		tbPasswordverify.setEnabled(true);
		tbPasswordverify.setText("");
		changePasswordLink.setVisible(false);
		tbDescription.setText("");
		lbUserType.setWidgetValue("null");
		lbActualPhysician.clear();
		addUserButton.setText("Add User");
		userId=null;
		deleteUserButton.setVisible(false);
		copyButton.setVisible(false);
		lbActualPhysician.setVisible(false);
		tbUsername.setFocus(true);
	}
	
	public void retrieveAllUsers() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.UserInterface.GetRecords",
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
								wUsers.clearData();
								wUsers.loadData(data);
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
	
	public void getACLGroups() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {};

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
							String[][] data = (String[][]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"String[][]");
							if (data != null) {
								addACLGroup(data);
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
	
	public void getUserDetails(final Integer userId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {JsonUtil.jsonify(userId)};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.UserInterface.GetRecord",
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
							HashMap<String,String> data = (HashMap<String,String>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>");
							if (data != null) {
								tbUsername.setText(data.get("username"));
								tbDescription.setText(data.get("userdescrip"));
								tbUserFirstName.setText(data.get("userfname"));
								tbUserLastName.setText(data.get("userlname"));
								tbUserMiddleName.setText(data.get("usermname"));
								tbUserTitle.setWidgetValue(data.get("usertitle"));
								lbUserType.setWidgetValue(data.get("usertype"));
								if(data.get("usertype")!=null && data.get("usertype").equalsIgnoreCase("phy")){
										lbActualPhysician.setValue(Integer.parseInt(data.get("userrealphy")));
										lbActualPhysician.setVisible(true);
								}
								
								if(data.get("userfac")!=null && data.get("userfac").length()>0){
									String[] userFacilities = data.get("userfac").split(",");
									for(int i=0;i<userFacilities.length;i++){
										Integer id = Integer.parseInt(userFacilities[i]);
										CheckBox checkBox = facilitiesCheckBoxesMap.get(id);
										if(checkBox!=null)
											checkBox.setValue(true);
									}
								}
								
								addUserButton.setText("Modify User");
								changePasswordLink.setVisible(true);
								tbPassword.setEnabled(false);
								tbPasswordverify.setEnabled(false);
								deleteUserButton.setVisible(true);
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
	
	public void getUserGroup(final Integer userId) {
		aclSelectedGroupsIdsList.clear();
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {JsonUtil.jsonify(userId)};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.ACL.GetUserGroups",
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
							String[] data = (String[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"String[]");
							if (data != null) {
								Integer groupId=null;
									for(int i=0;i<data.length;i++){
										groupId = Integer.parseInt(data[i]);
										CheckBox checkBox = aclGroupsCheckBoxesMap.get(groupId);
										checkBox.setValue(true);
										aclSelectedGroupsIdsList.add(groupId);
									}
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
	
	protected int requestTracker = 0;

	protected TextBox tbUserFirstName;

	protected TextBox tbUserMiddleName;

	protected TextBox tbUserLastName;

	protected CustomRadioButtonGroup tbUserTitle;
	public void addACLGroup(final String[][] data){
		int row=0,col=0;
		for(int i=0;i<data.length;i++){
				final String groupName=data[i][0];
				final Integer groupId=Integer.parseInt(data[i][1]);
				final CheckBox checkBox = new CheckBox(groupName);
				checkBox.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
					HashMap<String,List> thisGroupMap = null;
					@Override
					public void onValueChange(ValueChangeEvent<Boolean> arg0) {
						if(checkBox.getValue()){
							if(!aclSelectedGroupsIdsList.contains(groupId))
								aclSelectedGroupsIdsList.add(groupId);
							if(thisGroupMap==null){
								Util.callModuleMethod("ACL", "GetGroupPermissions", groupId, new CustomRequestCallback() {
									@Override
									public void onError() {
									}
									@Override
									public void jsonifiedData(Object data) {
										thisGroupMap = (HashMap<String,List>) data;
										setCheckBoxesValue(thisGroupMap, true,false);
										setCheckBoxesValue(blockedPermissionsMap, false,true);
										setCheckBoxesValue(allowedPermissionsMap, true,true);
									}
								}, "HashMap<String,List>");
							}else{
								setCheckBoxesValue(thisGroupMap, true,false);
								setCheckBoxesValue(blockedPermissionsMap, false,true);
								setCheckBoxesValue(allowedPermissionsMap, true,true);
							}
						}
						else{ 
							aclSelectedGroupsIdsList.remove(groupId);
							setCheckBoxesValue(thisGroupMap, false,false);
						}
				
					}
				
				});
				
				
				aclGroupsTable.setWidget(row, col, checkBox );
				aclGroupsCheckBoxesMap.put(groupId, checkBox);
				if((i+1)%3==0){
					row++;
					col=0;
				}else col++;	
		}
	}
	public void setCheckBoxesValue(HashMap<String,List> container,boolean checked,boolean skipSelection){
		if(allAclPermissionsMap.size()>0){
			Iterator<String> iterator = container.keySet().iterator();
			while(iterator.hasNext()){
				final String section = iterator.next();
				List values = container.get(section);
				Iterator<String> innserItr = values.iterator(); 
				while(innserItr.hasNext()){
						final String value=innserItr.next();
						String key = section+":"+value;
						if(checked){
							if(!skipSelection){
								Integer thisSectionValueContainerGroupCount = selectedPermissionsMap.get(key);
								if(thisSectionValueContainerGroupCount==null)
									thisSectionValueContainerGroupCount=1;
								else
									thisSectionValueContainerGroupCount++;
								selectedPermissionsMap.put(key,thisSectionValueContainerGroupCount);
							}
							allAclPermissionsMap.get(key).setValue(checked);
						}else{
							if(!skipSelection){
								Integer thisSectionValueContainerGroupCount = selectedPermissionsMap.get(key);
								if(thisSectionValueContainerGroupCount==null || thisSectionValueContainerGroupCount==1){
									selectedPermissionsMap.remove(key);
								}else{
									thisSectionValueContainerGroupCount--;
									selectedPermissionsMap.put(key,thisSectionValueContainerGroupCount);
								}
							}
							allAclPermissionsMap.get(key).setValue(checked);
						}
				}//end for loop
			}// end while loop
		}//End outer most if
	}
	
	public void calculateBlockedAndAllowedACLSections(){
		allowedPermissionsMap.clear();
		blockedPermissionsMap.clear();
		JsonUtil.debug("Additional section   ");
		Iterator<String> iterator = allAclPermissionsMap.keySet().iterator();
		while(iterator.hasNext()){
			String key = iterator.next();
			CheckBox sectionValue = allAclPermissionsMap.get(key);
			if(sectionValue.getValue()){
				if(selectedPermissionsMap.get(key)==null){
					JsonUtil.debug("Additional section           :             "+key);
					String[] sectionWithValue = key.split(":");
					List sectionValuesList = allowedPermissionsMap.get(sectionWithValue[0]);
					if(sectionValuesList==null)
						sectionValuesList = new ArrayList();
					sectionValuesList.add(sectionWithValue[1]);
					allowedPermissionsMap.put(sectionWithValue[0], sectionValuesList);
				}
			}else{
				if(selectedPermissionsMap.get(key)!=null){
					JsonUtil.debug("Blocked section           :             "+key);
					
					String[] sectionWithValue = key.split(":");
					List sectionValuesList = blockedPermissionsMap.get(sectionWithValue[0]);
					if(sectionValuesList==null)
						sectionValuesList = new ArrayList();
					sectionValuesList.add(sectionWithValue[1]);
					blockedPermissionsMap.put(sectionWithValue[0], sectionValuesList);
				}
			}
		}
	}
	
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
