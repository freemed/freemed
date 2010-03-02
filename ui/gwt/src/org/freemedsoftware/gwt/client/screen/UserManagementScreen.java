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
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
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
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class UserManagementScreen extends ScreenInterface implements
		ClickHandler {

	protected CustomTable wUsers = new CustomTable();

	protected Integer userId = null;
	
	protected TextBox tbUsername,tbDescription;

	protected PasswordTextBox tbPassword,tbPasswordverify;
	
	protected CustomListBox lbUserType;

	protected SupportModuleWidget lbActualPhysician;

	protected Button addUserButton, clearButton, deleteUserButton;

	protected String className = "UserManagementScreen";

	protected List<Integer> aclGroupsIds=new ArrayList<Integer>();
	
	protected HorizontalPanel aclGroupsPanel=new HorizontalPanel();
	
	protected HashMap<Integer,CheckBox> aclGroupsMap=new HashMap<Integer,CheckBox>();
	
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
		
		final boolean canAddUser =  CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.UTILITIES_CATEGORY, AppConstants.USER_MANAGEMENT);
		
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		// Panel #1
		if(canAddUser){
			final FlexTable userAddTable = new FlexTable();
			tabPanel.add(userAddTable, "Add User");
			tabPanel.selectTab(0);
			final Label usernameLabel = new Label("User Name");
			userAddTable.setWidget(0, 0, usernameLabel);
	
			tbUsername = new TextBox();
			userAddTable.setWidget(0, 1, tbUsername);
			userAddTable.getFlexCellFormatter().setColSpan(0, 1, 2);
			tbUsername.setWidth("10em");
	
			final Label passwordLabel = new Label("Password");
			userAddTable.setWidget(1, 0, passwordLabel);
	
			tbPassword = new PasswordTextBox();
			userAddTable.setWidget(1, 1, tbPassword);
			userAddTable.getFlexCellFormatter().setColSpan(1, 1, 2);
			tbPassword.setWidth("10em");
	
			final Label passwordverifyLabel = new Label("Password (Verify)");
			userAddTable.setWidget(2, 0, passwordverifyLabel);
	
			tbPasswordverify = new PasswordTextBox();
			userAddTable.setWidget(2, 1, tbPasswordverify);
			userAddTable.getFlexCellFormatter().setColSpan(2, 1, 2);
			tbPasswordverify.setWidth("10em");
	
			final Label descriptionLabel = new Label("Description");
			userAddTable.setWidget(3, 0, descriptionLabel);
	
			tbDescription = new TextBox();
			userAddTable.setWidget(3, 1, tbDescription);
			userAddTable.getFlexCellFormatter().setColSpan(3, 1, 2);
			tbDescription.setWidth("100%");
	
			final Label userTypeLabel = new Label("User Type");
			userAddTable.setWidget(4, 0, userTypeLabel);
	
			lbUserType = new CustomListBox();
			userAddTable.setWidget(4, 1, lbUserType);
			userAddTable.getFlexCellFormatter().setColSpan(4, 1, 2);
			lbUserType.addItem("Select User Type", "null");
			lbUserType.addItem("Miscellaneous", "misc");
			lbUserType.addItem("Provider", "phy");
	
			final Label actualPhysicianLabel = new Label("Actual Physician");
			userAddTable.setWidget(5, 0, actualPhysicianLabel);
			actualPhysicianLabel.setVisible(false);
	
			lbActualPhysician = new SupportModuleWidget("ProviderModule");
			userAddTable.setWidget(5, 1, lbActualPhysician);
			userAddTable.getFlexCellFormatter().setColSpan(5, 1, 2);
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
	
			final Label aclLabel = new Label("User Groups");
			userAddTable.setWidget(6, 0, aclLabel);
			userAddTable.setWidget(6, 1, aclGroupsPanel);
			
			HorizontalPanel buttonsPanel = new HorizontalPanel();
			addUserButton = new Button();
			addUserButton.setText("Add User");
			addUserButton.addClickHandler(this);
			buttonsPanel.add(addUserButton);
	
			deleteUserButton = new Button();
			deleteUserButton.setText("Delete User");
			deleteUserButton.addClickHandler(this);
			deleteUserButton.setVisible(false);
			buttonsPanel.add(deleteUserButton);
			
			clearButton = new Button();
			clearButton.setText("Clear");
			clearButton.addClickHandler(this);
			buttonsPanel.add(clearButton);
			
			userAddTable.setWidget(7, 1, buttonsPanel);
			getACLGroups();
		}
		// Panel #2

		final FlexTable userListTable = new FlexTable();
		tabPanel.add(userListTable, "List Users");

		userListTable.setWidget(0, 0, wUsers);

		wUsers.setSize("100%", "100%");
		wUsers.addColumn("Username", "username"); // col 0
		wUsers.addColumn("Description", "userdescrip"); // col 1
		wUsers.addColumn("Level", "userlevel"); // col 2
		wUsers.addColumn("Type", "usertype"); // col 3
		wUsers.setIndexName("id");

		wUsers.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				if(canAddUser){
					clearForm();
					userId = Integer.parseInt(data.get("id"));
					getUserDetails(userId);
					getUserGroup(userId);
					tabPanel.selectTab(0);
				}
			}
		});

		// TODO:Backend needs to be fixed first
		retrieveAllUsers();
		Util.setFocus(tbUsername);
	}

	public void onClick(ClickEvent evt) {
		Widget w = (Widget) evt.getSource();
		if (w == addUserButton) {

			if (checkInput() == true) {
				String requestURL="org.freemedsoftware.api.UserInterface.add";
				HashMap<String, String> hm = new HashMap<String, String>();
				if(userId!=null){
					hm.put("id", userId.toString());
					requestURL="org.freemedsoftware.api.UserInterface.mod";
				}
					
				hm.put("username", tbUsername.getText());
				if(userId==null)
					hm.put("userpassword", tbPassword.getText());

				hm.put("userdescrip", tbDescription.getText());
				String usertype = lbUserType.getValue(lbUserType
						.getSelectedIndex());
				hm.put("usertype", usertype);
				if (usertype == "phy") {
					hm.put("userrealphy", lbActualPhysician.getValue().toString());
				}
				String useracl="";
				Iterator<Integer> itr = aclGroupsIds.iterator();
				while(itr.hasNext()){
					useracl=useracl+itr.next().toString();
					if(itr.hasNext())
						useracl=useracl+",";
				}
				hm.put("useracl", useracl);

				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// Do nothing.
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { JsonUtil.jsonify(hm) };
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
										"Failed to add user.",
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
												"Successfully Added User.",
												Toaster.TOASTER_INFO);
										retrieveAllUsers();
										clearForm();
									}else{
										Boolean b = (Boolean) JsonUtil
										.shoehornJson(JSONParser
												.parse(response.getText()),
												"Boolean");
										if(b){
											CurrentState.getToaster().addItem(
													className,
													"Successfully Modified User.",
													Toaster.TOASTER_INFO);
											retrieveAllUsers();
											clearForm();
										}
									}
								} else {
									CurrentState.getToaster().addItem(
											className, "Failed to add user.",
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
		}else if (w == deleteUserButton) {

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
								CurrentState.getToaster().addItem(className,
										"Failed to delete user.",
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
													"Successfully deleted User.",
													Toaster.TOASTER_INFO);
											retrieveAllUsers();
											clearForm();
										}
								} else {
									CurrentState.getToaster().addItem(
											className, "Failed to delete user.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						CurrentState.getToaster().addItem(className,
								"Failed to delete user.",
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
		String pw = null;
		if (tbUsername.getText() == "") {
			s[s.length] = "Username";
		}
		if(userId==null){//if modifying user then no need to get new password
			if (tbPassword.getText() != "") {
				if (tbPassword.getText() != tbPasswordverify.getText()) {
					pw = "Passwords do not match!";
				}
			} else {
				s[s.length] = "Password";
			}
		}

		if (tbDescription.getText() == "") {
			s[s.length] = "User Description";
		}

		if (lbUserType.getWidgetValue() == "null") {
			s[s.length] = "User Type";
		} else if (lbUserType.getWidgetValue() == "phy") {
			if (lbActualPhysician.getText() == "") {
				s[s.length] = "Actual Physician";
			}
		}

		if (s.length == 0 && pw == null) {
			return true;
		}

		for (int i = 0; i < s.length; i++) {
			base = base + s[i];
			if (i != s.length - 1) {
				base = base + ", ";
			}
		}

		Window.alert(base + "\n" + pw);

		return false;
	}

	public void clearForm() {
		tbUsername.setText("");
		tbPassword.setEnabled(true);
		tbPassword.setText("");
		tbPasswordverify.setEnabled(true);
		tbPasswordverify.setText("");
		tbDescription.setText("");
		lbUserType.setWidgetValue("null");
		lbActualPhysician.clear();
		addUserButton.setText("Add User");
		userId=null;
		deleteUserButton.setVisible(false);
		aclGroupsIds.clear();
		lbActualPhysician.setVisible(false);
		Iterator<Integer> itr = aclGroupsMap.keySet().iterator();
		while(itr.hasNext()){
			Integer key = itr.next();
			CheckBox checkBox = aclGroupsMap.get(key);
			checkBox.setValue(false);
		}
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
								lbUserType.setWidgetValue(data.get("usertype"));
								try{
								if(data.get("usertype").equalsIgnoreCase("phy")){
									lbActualPhysician.setValue(Integer.parseInt(data.get("userrealphy")));
									lbActualPhysician.setVisible(true);
								}
								}catch(Exception e){JsonUtil.debug("user real physician not found"+userId);}
								addUserButton.setText("Modify User");
								tbPassword.setEnabled(false);
								tbPasswordverify.setEnabled(false);
								deleteUserButton.setVisible(true);
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
	
	public void getUserGroup(Integer userId) {
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
										CheckBox checkBox = aclGroupsMap.get(groupId);
										checkBox.setValue(true);
										aclGroupsIds.add(groupId);
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
	
	public void addACLGroup(final String[][] data){
		for(int i=0;i<data.length;i++){
				final String groupName=data[i][0];
				final Integer groupId=Integer.parseInt(data[i][1]);
				final CheckBox checkBox = new CheckBox(groupName);
				checkBox.addClickHandler(new ClickHandler() {
				
					@Override
					public void onClick(ClickEvent arg0) {
						if(checkBox.getValue())
							aclGroupsIds.add(groupId);
						else 
							aclGroupsIds.remove(groupId);
					}
				
				});
				aclGroupsPanel.add(checkBox );
				aclGroupsMap.put(groupId, checkBox);
		}
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
