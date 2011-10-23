/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Jeremy Allen <ieziar.jeremy <--at--> gmail.com>
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

package org.freemedsoftware.gwt.client;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.dom.client.KeyCodes;
import com.google.gwt.event.dom.client.KeyUpEvent;
import com.google.gwt.event.dom.client.KeyUpHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TextBox;


public class LoginDialog extends DialogBox {

	protected boolean loggedIn = false;

	protected final CustomListBox facilityList, languageList;

	protected final TextBox userLogin;

	protected final PasswordTextBox loginPassword;

	protected final PushButton loginButton;

	protected final Image loadingImage;
	
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

		final Label userLabel = new Label(_("User Name"));
		absolutePanel.add(userLabel, 25, 71);
		userLabel.setStylePrimaryName("gwt-Label-RAlign");

		userLogin = new TextBox();
		absolutePanel.add(userLogin, 92, 71);
		userLogin.setSize("139px", "22px");
		userLogin.setStylePrimaryName("freemed-LoginFields");
		userLogin.setText("");
		userLogin.setAccessKey('u');
		userLogin.setTabIndex(1);
		
		final Label passwordLabel = new Label(_("Password"));
		absolutePanel.add(passwordLabel, 25, 100);
		passwordLabel.setStylePrimaryName("gwt-Label-RAlign");

		loginPassword = new PasswordTextBox();
		absolutePanel.add(loginPassword, 92, 102);
		loginPassword.setSize("139px", "22px");
		loginPassword.setStylePrimaryName("freemed-LoginFields");
		loginPassword.setText("");
		loginPassword.setTabIndex(2);

		final Label facilityLabel = new Label(_("Facility"));
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
									JSONParser.parseStrict(response.getText()),
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

		final Label languageLabel = new Label(_("Language"));
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
									JSONParser.parseStrict(response.getText()),
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

		final Image imageUp = new Image("resources/images/button_on.png");
		imageUp.setSize("100%", "100%");

		final Image imageDown = new Image("resources/images/button_on_down.png");
		imageDown.setSize("100%", "100%");
		
		loginButton = new PushButton(imageUp,imageDown);
		loginButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				attemptLogin();
			}
		});		
		loginButton.setTabIndex(5);
		absolutePanel.add(loginButton, 83, 233);
		loginButton.setStylePrimaryName("gwt-LoginButton");

//		final Label loginLabel = new Label("Login");
//		absolutePanel.add(loginLabel, 140, 242);
//		loginLabel.setStylePrimaryName("gwt-Label-RAlign");
		
		loadingImage = new Image(GWT
				.getHostPageBaseURL()
				+ "resources/images/login_loading.32x27.gif");
		loadingImage.setVisible(false);
		absolutePanel.add(loadingImage, 185, 237);

		final SimplePanel simplePanel = new SimplePanel();
		simplePanel.setWidget(absolutePanel);

		// Add custom keyboard listener to allow submit from password field.
		loginPassword.addKeyUpHandler(new KeyUpHandler() {
			@Override
			public void onKeyUp(KeyUpEvent event) {
				int keyCode = event.getNativeKeyCode();
				switch (keyCode) {
				case KeyCodes.KEY_ENTER:
					attemptLogin();
					try {
						((TextBox) event.getSource()).cancelKey();
					} catch (Exception ex) {
					}
					break;
				default:
					break;
				}
			}
		});

		this.setWidget(simplePanel);
		
		userLogin.setFocus(true);//set focus to user name input box
	}

	public void attemptLogin() {
		// Disable submit button
		if (Util.isStubbedMode()) {
			hide();
			freemedInterface.resume();
		} else {
			loadingImage.setVisible(true);
			loginButton.setEnabled(false);

			try {
				Util.login(userLogin.getText(), loginPassword.getText(),facilityList.getStoredValue(),
						new CustomCommand() {
							public void execute(Object data) {
								loadingImage.setVisible(false);
								hide();
								loginPassword.setText("");
								freemedInterface.resume();
								loginButton.setEnabled(true);
							}
						}, new CustomCommand() {
							public void execute(Object data) {
								loadingImage.setVisible(false);
								show();
								loginPassword.setText("");
								loginButton.setEnabled(true);
								String msg = "";
								if(data.toString().equalsIgnoreCase(AppConstants.INVALID_USER))
									msg = _("The user name or password is incorrect. Please try again.");
								else if(data.toString().equalsIgnoreCase(AppConstants.NOT_IN_FACILITY))
									msg = _("You can't login using facility '%s'.").replace("%s", facilityList.getItemText(facilityList.getSelectedIndex()));
								else if(data.toString().equalsIgnoreCase(AppConstants.INVALID_RESPONSE))
									msg = _("Unable to connect to server.");
								if(CurrentState.getToaster()==null){
									Toaster toaster = new Toaster();
									CurrentState.assignToaster(toaster);
									toaster.setTimeout(7);
								}
								
								CurrentState.getToaster().addItem("Login",
										msg,
										Toaster.TOASTER_ERROR);
								
							}

						});
			} catch (Exception e) {
			}
		}
	}

	public void setFocusToUserField(){
		try {
			userLogin.setFocus(true);
		} catch (Exception e) {
			GWT.log("Caught exception: ", e);
		}		
	}
	
	public void setFocusToPasswordField(){
		try {
			loginPassword.setFocus(true);
		} catch (Exception e) {
			GWT.log("Caught exception: ", e);
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
	
	public String getSelectedFacilityName() {
		String facilityName = null;
		if(facilityList!=null && facilityList.getItemCount()>0)
			facilityName = facilityList.getItemText(facilityList.getSelectedIndex());
		return facilityName;
	}
	public String getSelectedFacilityValue() {
		return facilityList.getWidgetValue();
	}

	public String getLoggedInUser() {
		return userLogin.getText();
	}
}
