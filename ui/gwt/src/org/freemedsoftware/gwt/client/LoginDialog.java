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

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;

public class LoginDialog extends DialogBox {

	protected boolean loggedIn = false;

	protected final ListBox facilityList, languageList;

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
		userLogin.setText("your user name");
		userLogin.setAccessKey('u');

		final Label passwordLabel = new Label("password");
		absolutePanel.add(passwordLabel, 25, 100);
		passwordLabel.setStylePrimaryName("gwt-Label-RAlign");

		loginPassword = new PasswordTextBox();
		absolutePanel.add(loginPassword, 92, 102);
		loginPassword.setSize("139px", "22px");
		loginPassword.setStylePrimaryName("freemed-LoginFields");
		loginPassword.setText("password");

		final Label facilityLabel = new Label("facility");
		absolutePanel.add(facilityLabel, 28, 152);
		facilityLabel.setStylePrimaryName("gwt-Label-RAlign");
		facilityLabel.setSize("59px", "19px");

		facilityList = new ListBox();
		absolutePanel.add(facilityList, 94, 149);
		facilityList.setSize("191px", "22px");
		facilityList.setStylePrimaryName("freemed-LoginFields");
		if (Util.isStubbedMode()) {
			facilityList.addItem(
					"Mt. Ascutney Hospital Medical Clinic Examination Room",
					"1");
			facilityList.addItem(
					"Associates in Surgery & Gastroenterology, LLC", "2");
			facilityList.addItem("Valley Regional Hospital", "3");
		} else {
			service.GetLocations(new AsyncCallback() {
				public void onSuccess(Object result) {
					String[][] r = (String[][]) result;
					for (int iter = 0; iter < r.length; iter++) {
						facilityList.addItem(r[iter][0], r[iter][1]);
					}
				}

				public void onFailure(Throwable t) {
					Window
							.alert("Unable to contact RPC service, try again later.");
				}
			});
		}

		final Label languageLabel = new Label("language");
		absolutePanel.add(languageLabel, 28, 183);
		languageLabel.setStylePrimaryName("gwt-Label-RAlign");
		languageLabel.setSize("59px", "19px");

		languageList = new ListBox();
		absolutePanel.add(languageList, 94, 180);
		languageList.setSize("190px", "22px");
		languageList.setStylePrimaryName("freemed-LoginFields");
		if (Util.isStubbedMode()) {
			languageList.addItem("English", "en_US");
			languageList.addItem("Deutsch", "de_DE");
			languageList.addItem("Espanol (Mexico)", "es_MX");
			languageList.addItem("Polski", "pl_PL");
		} else {
			service.GetLanguages(new AsyncCallback() {
				public void onSuccess(Object result) {
					String[][] r = (String[][]) result;
					for (int iter = 0; iter < r.length; iter++) {
						languageList.addItem(r[iter][0], r[iter][1]);
					}
				}

				public void onFailure(Throwable t) {
					Window
							.alert("Unable to contact RPC service, try again later.");
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
		absolutePanel.add(loginButton, 83, 233);
		loginButton.setStylePrimaryName("gwt-LoginButton");

		final Label loginLabel = new Label("Login");
		absolutePanel.add(loginLabel, 140, 242);
		loginLabel.setStylePrimaryName("gwt-Label-RAlign");

		this.setWidget(absolutePanel);
	}

	public void attemptLogin() {
		// Disable submit button
		if (Util.isStubbedMode()) {
			hide();
			freemedInterface.resume();
		} else {
			loginButton.setEnabled(false);
			LoginAsync service = null;

			try {
				service = (LoginAsync) Util
						.getProxy("org.freemedsoftware.gwt.Public.Login");
				service.LoggedIn(new AsyncCallback() {
					public void onSuccess(Object result) {
						Boolean r = (Boolean) result;
						if (r.booleanValue()) {
							// If logged in, continue
							hide();
							freemedInterface.resume();
						} else {
							// Force login loop
							show();
							loginPassword.setText("");
							loginButton.setEnabled(true);
						}
					}

					public void onFailure(Throwable t) {
						Window
								.alert("Unable to contact RPC service, try again later.");
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

}
