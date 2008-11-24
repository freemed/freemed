/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Jeremy Allen <ieziar.jeremy <--at--> gmail.com>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client;

import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomListBox;

import com.google.gwt.core.client.GWT;
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
import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.KeyboardListener;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;

public class LoginDialog extends DialogBox {

	protected boolean loggedIn = false;

	protected final CustomListBox facilityList, languageList;

	protected final TextBox userLogin;

	protected final PasswordTextBox loginPassword;

	protected final PushButton loginButton;

	protected FreemedInterface freemedInterface = null;

	protected DialogBox dialog;

	protected LoginAsync service = null;

	public LoginDialog() {
		try {
			service = (LoginAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Public.Login");
		} catch (Exception e) {
			GWT.log("Caught exception: ", e);
		}

		final AbsolutePanel absolutePanel = new AbsolutePanel();
		absolutePanel.setTitle("Login");
		absolutePanel.setStylePrimaryName("loginPanel");
		absolutePanel.setStyleName("loginPanel");
		absolutePanel.setSize("300px", "300px");

		final Label userLabel = new Label("user name");
		absolutePanel.add(userLabel, 25, 71);
		userLabel.setStylePrimaryName("gwt-Label-RAlign");

		userLogin = new TextBox();
		absolutePanel.add(userLogin, 92, 71);
		userLogin.setSize("139px", "22px");
		userLogin.setStylePrimaryName("freemed-LoginFields");
		userLogin.setText("");
		userLogin.setAccessKey('u');
		userLogin.setTabIndex(1);

		final Label passwordLabel = new Label("password");
		absolutePanel.add(passwordLabel, 25, 100);
		passwordLabel.setStylePrimaryName("gwt-Label-RAlign");

		loginPassword = new PasswordTextBox();
		absolutePanel.add(loginPassword, 92, 102);
		loginPassword.setSize("139px", "22px");
		loginPassword.setStylePrimaryName("freemed-LoginFields");
		loginPassword.setText("");
		loginPassword.setTabIndex(2);

		final Label facilityLabel = new Label("facility");
		absolutePanel.add(facilityLabel, 28, 152);
		facilityLabel.setStylePrimaryName("gwt-Label-RAlign");
		facilityLabel.setSize("59px", "19px");

		facilityList = new CustomListBox();
		absolutePanel.add(facilityList, 94, 149);
		facilityList.setSize("191px", "22px");
		facilityList.setStylePrimaryName("freemed-LoginFields");
		facilityList.setTabIndex(3);
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			facilityList.addItem(
					"Mt. Ascutney Hospital Medical Clinic Examination Room",
					"1");
			facilityList.addItem(
					"Associates in Surgery & Gastroenterology, LLC", "2");
			facilityList.addItem("Valley Regional Hospital", "3");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.public.Login.GetLocations",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[][] r = (String[][]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[][]");
							if (r != null) {
								for (int iter = 0; iter < r.length; iter++) {
									facilityList
											.addItem(r[iter][0], r[iter][1]);
								}
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			service.GetLocations(new AsyncCallback<String[][]>() {
				public void onSuccess(String[][] r) {
					for (int iter = 0; iter < r.length; iter++) {
						facilityList.addItem(r[iter][0], r[iter][1]);
					}
				}

				public void onFailure(Throwable t) {
					Window.alert(t.getMessage());
				}
			});
		}

		final Label languageLabel = new Label("language");
		absolutePanel.add(languageLabel, 28, 183);
		languageLabel.setStylePrimaryName("gwt-Label-RAlign");
		languageLabel.setSize("59px", "19px");

		languageList = new CustomListBox();
		absolutePanel.add(languageList, 94, 180);
		languageList.setSize("190px", "22px");
		languageList.setStylePrimaryName("freemed-LoginFields");
		languageList.setTabIndex(4);
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			languageList.addItem("English", "en_US");
			languageList.addItem("Deutsch", "de_DE");
			languageList.addItem("Espanol (Mexico)", "es_MX");
			languageList.addItem("Polski", "pl_PL");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.public.Login.GetLanguages",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[][] r = (String[][]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[][]");
							if (r != null) {
								for (int iter = 0; iter < r.length; iter++) {
									languageList
											.addItem(r[iter][0], r[iter][1]);
								}
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			service.GetLanguages(new AsyncCallback<String[][]>() {
				public void onSuccess(String[][] r) {
					for (int iter = 0; iter < r.length; iter++) {
						languageList.addItem(r[iter][0], r[iter][1]);
					}
				}

				public void onFailure(Throwable t) {
					Window.alert(t.toString());
				}
			});
		}

		final Image image = new Image("resources/images/button_on.png");
		image.setSize("100%", "100%");

		loginButton = new PushButton(image);
		loginButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				attemptLogin();
			}
		});
		loginButton.setTabIndex(5);
		absolutePanel.add(loginButton, 83, 233);
		loginButton.setStylePrimaryName("gwt-LoginButton");

		final Label loginLabel = new Label("Login");
		absolutePanel.add(loginLabel, 140, 242);
		loginLabel.setStylePrimaryName("gwt-Label-RAlign");

		final SimplePanel simplePanel = new SimplePanel();
		simplePanel.setWidget(absolutePanel);

		// Add custom keyboard listener to allow submit from password field.
		loginPassword.addKeyboardListener(new KeyboardListener() {

			public void onKeyUp(Widget sender, char keyCode, int modifiers) {
				switch (keyCode) {
				case KeyboardListener.KEY_ENTER:
					attemptLogin();
					try {
						((TextBox) sender).cancelKey();
					} catch (Exception ex) {
					}
					break;
				default:
					break;
				}
			}

			public void onKeyDown(Widget sender, char keyCode, int modifiers) {
			}

			public void onKeyPress(Widget sender, char keyCode, int modifiers) {
			}

		});

		this.setWidget(simplePanel);
	}

	public void attemptLogin() {
		// Disable submit button
		if (Util.isStubbedMode()) {
			hide();
			freemedInterface.resume();
		} else {
			loginButton.setEnabled(false);

			try {
				Util.login(userLogin.getText(), loginPassword.getText(),
						new Command() {
							public void execute() {
								hide();
								freemedInterface.resume();
								loginButton.setEnabled(true);
							}
						}, new Command() {
							public void execute() {
								show();
								loginPassword.setText("");
								loginButton.setEnabled(true);
							}

						});
			} catch (Exception e) {
			}
		}
	}

	public void show() {
		super.show();
		try {
			userLogin.setFocus(true);
		} catch (Exception e) {
			GWT.log("Caught exception: ", e);
		}
	}

	public void setFreemedInterface(FreemedInterface i) {
		freemedInterface = i;
	}

	public boolean isLoggedIn() {
		return loggedIn;
	}

	public String getLanguageSelected() {
		return languageList.getWidgetValue();
	}
}
