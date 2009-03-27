/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class UserManagementScreen extends ScreenInterface implements
		ClickListener {

	protected CustomSortableTable wUsers = new CustomSortableTable();

	protected TextBox tbUsername, tbPassword, tbPasswordverify, tbDescription;

	protected CustomListBox lbUserType;

	protected SupportModuleWidget lbActualPhysician;

	protected Button addUserButton, clearButton;

	protected String className = "UserManagementScreen";

	public UserManagementScreen() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		// Panel #1

		final FlexTable userAddTable = new FlexTable();
		tabPanel.add(userAddTable, "Add User");
		tabPanel.selectTab(0);
		final Label usernameLabel = new Label("Username");
		userAddTable.setWidget(0, 0, usernameLabel);

		tbUsername = new TextBox();
		userAddTable.setWidget(0, 1, tbUsername);
		userAddTable.getFlexCellFormatter().setColSpan(0, 1, 2);
		tbUsername.setWidth("10em");

		final Label passwordLabel = new Label("Password");
		userAddTable.setWidget(1, 0, passwordLabel);

		tbPassword = new TextBox();
		userAddTable.setWidget(1, 1, tbPassword);
		userAddTable.getFlexCellFormatter().setColSpan(1, 1, 2);
		tbPassword.setWidth("10em");

		final Label passwordverifyLabel = new Label("Password (Verify)");
		userAddTable.setWidget(2, 0, passwordverifyLabel);

		tbPasswordverify = new TextBox();
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

		lbUserType.addChangeListener(new ChangeListener() {
			public void onChange(Widget sender) {
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

		addUserButton = new Button();
		userAddTable.setWidget(6, 1, addUserButton);
		addUserButton.setText("Add User");
		addUserButton.addClickListener(this);

		clearButton = new Button();
		userAddTable.setWidget(6, 2, clearButton);
		clearButton.setText("Clear");
		clearButton.addClickListener(this);

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

		wUsers.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents ste, int row, int col) {
				final Integer id = new Integer(wUsers.getValueByRow(row));
			}
		});

		// TODO:Backend needs to be fixed first
		// retrieveAllUsers();

	}

	public void onClick(Widget w) {
		if (w == addUserButton) {

			if (checkInput() == true) {

				HashMap<String, String> hm = new HashMap<String, String>();
				hm.put("username", tbUsername.getText());
				hm.put("userpassword", tbPassword.getText());
				hm.put("userdescrip", tbDescription.getText());
				String usertype = lbUserType.getValue(lbUserType
						.getSelectedIndex());
				hm.put("usertype", usertype);
				if (usertype == "phy") {
					hm.put("userrealphy", lbActualPhysician.getText());
				}

				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// Do nothing.
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { JsonUtil.jsonify(hm) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.api.UserInterface.add",
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								state.getToaster().addItem(className,
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
										state.getToaster().addItem(className,
												"Successfully Added User.",
												Toaster.TOASTER_INFO);
									}
								} else {
									state.getToaster().addItem(className,
											"Failed to add user.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						state.getToaster().addItem(className,
								"Failed to send message.",
								Toaster.TOASTER_ERROR);
					}
				} else {
					// TODO: Create GWT-RPC stuff here
				}

			} else if (w == clearButton) {
				clearForm();
			}
		}
	}

	public Boolean checkInput() {
		String base = "Please check the following fields:" + " ";
		String[] s = {};
		String pw = null;
		if (tbUsername.getText() == "") {
			s[s.length] = "Username";
		}
		if (tbPassword.getText() != "") {
			if (tbPassword.getText() != tbPasswordverify.getText()) {
				pw = "Passwords do not match!";
			}
		} else {
			s[s.length] = "Password";
		}

		if (tbDescription.getText() == "") {
			s[s.length] = "User Description";
		}

		if (lbUserType.getValue(lbUserType.getSelectedIndex()) == "null") {
			s[s.length] = "User Type";
		} else if (lbUserType.getValue(lbUserType.getSelectedIndex()) == "phy") {
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
		tbPassword.setText("");
		tbPasswordverify.setText("");
		tbDescription.setText("");
		lbUserType.setWidgetValue("misc");
		lbActualPhysician.clear();
	}

	public void retrieveAllUsers() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.UserInterface.GetAll",
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
								wUsers.clear();
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

}
