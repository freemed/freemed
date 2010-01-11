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
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.Toaster;

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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PreferencesScreen extends ScreenInterface {

	protected TabPanel tabPanel;

	protected PasswordTextBox currentPassword;

	protected PasswordTextBox newPassword;

	protected PasswordTextBox confirmNewPassword;

	protected HashMap<String, CheckBox> navigationsCheckboxes = new HashMap<String, CheckBox>();

	protected FlexTable navigationsFlexTable;

	protected VerticalPanel navigationsVerticalPanel;

	protected boolean isThemeChanged = false;

	protected boolean isNavigatioMenuChanged = false;

	private static List<PreferencesScreen> preferencesScreenList = null;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well
	public static PreferencesScreen getInstance() {
		PreferencesScreen preferencesScreen = null;

		if (preferencesScreenList == null)
			preferencesScreenList = new ArrayList<PreferencesScreen>();
		if (preferencesScreenList.size() < AppConstants.MAX_CONFIGURATION_TABS) {
			// creates & returns new next instance of preferencesScreen
			preferencesScreenList
					.add(preferencesScreen = new PreferencesScreen());
		} else {
			// returns last instance of preferencesScreen from list
			preferencesScreen = preferencesScreenList
					.get(AppConstants.MAX_PREFERENCES_TABS - 1);
			preferencesScreen.refreshPreferences();
		}
		return preferencesScreen;
	}

	public static boolean removeInstance(PreferencesScreen preferencesScreen) {
		return preferencesScreenList.remove(preferencesScreen);
	}

	public PreferencesScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setWidth("100%");

		tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);
		createTabs();
		tabPanel.selectTab(0);
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final Button commitChangesButton = new Button();
		horizontalPanel.add(commitChangesButton);
		commitChangesButton.setText("Commit Changes");
		commitChangesButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (isThemeChanged)
					CurrentState.setUserConfig("Theme", CurrentState.CUR_THEME);
				if (isNavigatioMenuChanged)
					CurrentState.setUserConfig("LeftNavigationMenu",
							CurrentState.getLeftNavigationOptions());
				CurrentState.getMainScreen().initNavigations();
				CurrentState.getToaster().addItem("Preferences Screen.",
						"Preferences saved!!!", Toaster.TOASTER_INFO);
				if (currentPassword.getText().length() > 0
						|| newPassword.getText().length() > 0
						|| newPassword.getText().length() > 0)
					chagePasswordProcess();
				else
					Util.closeTab(getPreferencesScreen());
			}
		});

		// populate();
	}

	public void refreshPreferences() {
		JsonUtil.debug("PreferencesScreen:refreshPreferences - start");
		// refreshing Navigations
		/*
		 * Get available options from current state and re-evaluate the check
		 * boxes values
		 */
		createNavigatonOptions();
	}

	public void chagePasswordProcess() {
		if (currentPassword.getText().length() > 0
				&& newPassword.getText().length() > 0
				&& newPassword.getText().equals(confirmNewPassword.getText())) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO stubbed mode goes here
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = {};
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.core.User.GetName",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							Window.alert(ex.toString());
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								validateUser(response.getText(),
										currentPassword.getText());
							} else
								CurrentState.getToaster().addItem(
										"change password failed.",
										"password change failed!!!",
										Toaster.TOASTER_ERROR);
						}
					});
				} catch (RequestException e) {
					Window.alert(e.getMessage());
				}
			} else {

				// TODO normal mode code goes here
			}
		} else {
			Window.alert("enter correct information to change password.");
		}
	}

	public void validateUser(String username, String password) {
		username = username.replaceAll("\"", "");
		String[] params = { username, password };
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(
						"org.freemedsoftware.public.Login.Validate", params)));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
					Window.alert(ex.toString());
				}

				public void onResponseReceived(Request request,
						Response response) {
					if (200 == response.getStatusCode()) {
						if (response.getText().compareToIgnoreCase("true") == 0) {
							changePassword();
						} else
							CurrentState.getToaster().addItem(
									"invalid password changed.",
									"invalid password!!!",
									Toaster.TOASTER_ERROR);
					}
				}
			});
		} catch (RequestException e) {

		}

	}

	public void changePassword() {
		String[] params = { newPassword.getText() };
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(
						"org.freemedsoftware.core.User.setPassword", params)));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
					Window.alert(ex.toString());
				}

				public void onResponseReceived(Request request,
						Response response) {
					if (Util.checkValidSessionResponse(response.getText())) {
						if (200 == response.getStatusCode()) {
							CurrentState.getToaster().addItem(
									"password changed.",
									"password changed successfully!!!",
									Toaster.TOASTER_INFO);
							currentPassword.setText("");
							newPassword.setText("");
							confirmNewPassword.setText("");
							Util.closeTab(getPreferencesScreen());
						} else
							CurrentState.getToaster()
									.addItem("password changed failed.",
											"password failed!!!",
											Toaster.TOASTER_ERROR);
					}
				}
			});
		} catch (RequestException e) {
			Window.alert(e.toString());
		}
	}

	/**
	 * Create tabbed configuration containers from array of strings with titles
	 * and initialize all counters.
	 * 
	 * @param t
	 */
	protected void createTabs() {

		// Preparing themes tab elements
		final FlexTable themeFlexTable = new FlexTable();
		themeFlexTable.addStyleName("cw-FlexTable");

		themeFlexTable.setHTML(0, 0, "Select theme to apply");

		final ListBox themesList = new ListBox();
		themesList.addItem("chrome");
		themesList.addItem("standard");
		themesList.addItem("dark");

		themeFlexTable.setWidget(0, 1, themesList);

		themesList.addChangeHandler(new ChangeHandler() {
			public void onChange(ChangeEvent evt) {
				CurrentState.CUR_THEME = themesList.getItemText(themesList
						.getSelectedIndex());
				isThemeChanged = true;
				Util.updateStyleSheets(CurrentState.CUR_THEME,
						CurrentState.LAST_THEME);
			}
		});
		VerticalPanel themesVerticalPanel = new VerticalPanel();
		themesVerticalPanel.add(themeFlexTable);
		// Adding theme tab
		tabPanel.add(themesVerticalPanel, "Theme");

		// preparing password tab
		final FlexTable passwordsFlexTable = new FlexTable();
		passwordsFlexTable.addStyleName("cw-FlexTable");

		passwordsFlexTable.setHTML(0, 0, "Enter current password :");
		currentPassword = new PasswordTextBox();
		passwordsFlexTable.setWidget(0, 1, currentPassword);

		passwordsFlexTable.setHTML(1, 0, "Enter new password :");
		newPassword = new PasswordTextBox();
		passwordsFlexTable.setWidget(1, 1, newPassword);

		passwordsFlexTable.setHTML(2, 0, "Confirm new password :");
		confirmNewPassword = new PasswordTextBox();
		passwordsFlexTable.setWidget(2, 1, confirmNewPassword);

		VerticalPanel passwordsVerticalPanel = new VerticalPanel();
		passwordsVerticalPanel.add(passwordsFlexTable);
		// Adding password tab
		tabPanel.add(passwordsVerticalPanel, "Password");

		navigationsVerticalPanel = new VerticalPanel();
		Label navLabel = new Label("Show following items for navigation.");
		navLabel.setStyleName("label");
		navigationsVerticalPanel.add(navLabel);
		createNavigatonOptions();
		navigationsVerticalPanel.add(navigationsFlexTable);
		// Adding password tab
		tabPanel.add(navigationsVerticalPanel, "Navigations");

	}

	protected void createNavigatonOptions() {
		// preparing Navigation tab
		navigationsVerticalPanel.clear();
		navigationsFlexTable = new FlexTable();
		navigationsCheckboxes.clear();
		navigationsFlexTable.addStyleName("cw-FlexTable");

		final HashMap<String, HashMap<String, String>> leftNavCategories = CurrentState
				.getLeftNavigationOptions();
		Iterator<String> itrCats = leftNavCategories.keySet().iterator();
		int i = 0;
		while (itrCats.hasNext()) {
			final String categoryName = itrCats.next();
			Label navLabel = new Label(categoryName);
			navLabel.setStyleName("label");
			navigationsFlexTable.setWidget(i++, 0, navLabel);
			final HashMap<String, String> leftNavOpts = leftNavCategories
					.get(categoryName);
			Iterator<String> itr = leftNavOpts.keySet().iterator();
			while (itr.hasNext()) {
				final String Option = itr.next();
				final String OptionVal = leftNavOpts.get(Option);
				navigationsFlexTable.setHTML(i, 0, Option);
				final CheckBox checkBox = new CheckBox();
				if (OptionVal.charAt(4) == '1')
					checkBox.setValue(true);
				else
					checkBox.setValue(false);
				checkBox.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						isNavigatioMenuChanged = true;
						String newOptVal = OptionVal.substring(0, 4);
						if (checkBox.getValue()) {
							leftNavOpts.put(Option, newOptVal+"1");
						} else {
							leftNavOpts.put(Option, newOptVal+"0");
						}
					}
				});
				navigationsFlexTable.setWidget(i, 1, checkBox);
				navigationsCheckboxes.put(Option, checkBox);
				i++;
			}
		}
		navigationsVerticalPanel.add(navigationsFlexTable);

	}

	public PreferencesScreen getPreferencesScreen() {
		return this;
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}