/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2012 FreeMED Software Foundation
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.TemplateWidget;

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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
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
	
	protected CustomListBox themesList = null;
	
	protected CustomListBox printersList = null; 

	protected CustomListBox providerGroupList =null;
	
	protected CustomListBox systemNotificationSettingsList =null;
	
	private static List<PreferencesScreen> preferencesScreenList = null;
	protected HashMap<String, List<String>> sectionFieldsMap;

	protected TemplateWidget templateWidget;

	protected HashMap<String, List<String>> selectedSections;
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
		sectionFieldsMap=new HashMap<String, List<String>>();
		List<String> widgetsList=new ArrayList<String>();
		widgetsList.add(_("WORK LIST"));
		widgetsList.add(_("MESSAGES"));
		widgetsList.add(_("UNFILED DOCUMENTS"));
		widgetsList.add(_("RX REFILLS"));
		widgetsList.add(_("ACTION ITEMS"));
		sectionFieldsMap.put(_("Sections"), widgetsList);
		createTabs();
		tabPanel.selectTab(0);
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final CustomButton commitChangesButton = new CustomButton("Commit Changes",AppConstants.ICON_ADD);
		horizontalPanel.add(commitChangesButton);
		commitChangesButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				////////////////Handling UI////////////////////////
				if (isThemeChanged)
					CurrentState.setUserConfig(AppConstants.SYSTEM_THEME, CurrentState.CUR_THEME);
				CurrentState.setUserConfig("defaultPrinter", printersList.getStoredValue());
				CurrentState.setUserConfig("providerGroup", providerGroupList.getStoredValue());
				CurrentState.setUserConfig(AppConstants.SYSTEM_NOTIFICATION, systemNotificationSettingsList.getStoredValue());
				CurrentState.setUserConfig("defaultWidgets", templateWidget.getSelectedSectionFeildsMap());
				CurrentState.assignSYSTEM_NOTIFY_TYPE(systemNotificationSettingsList.getStoredValue());
				try{
					CurrentState.defaultProviderGroup=new Integer(providerGroupList.getStoredValue());
				}
				catch(Exception e){
					
				}
				////////////////End Handling UI////////////////////////
				
				////////////////Handling Navigation////////////////////////
				if (isNavigatioMenuChanged)
					CurrentState.setUserConfig("LeftNavigationMenu",
							CurrentState.getLeftNavigationOptions());
				CurrentState.getMainScreen().initNavigations();
				////////////////End Handling Navigation////////////////////////
				Util.showInfoMsg("PreferencesScreen", _("Preferences saved."));
				if (currentPassword.getText().length() > 0
						|| newPassword.getText().length() > 0
						|| newPassword.getText().length() > 0)
					chagePasswordProcess();
				else
					Util.closeTab(getPreferencesScreen());
				DashboardScreenNew dashboard=DashboardScreenNew.getInstance();
				dashboard.loadWidgets();
				dashboard.reloadDashboard();
			}
		});
		
		setDefaultPreferences();
		loadPrefererences();
	}

	public void setDefaultPreferences(){
		if(CurrentState.getUserConfig(AppConstants.SYSTEM_THEME)==null)
			CurrentState.setUserConfig(AppConstants.SYSTEM_THEME, CurrentState.CUR_THEME);
		if(CurrentState.getUserConfig(AppConstants.SYSTEM_NOTIFICATION)==null)
			CurrentState.setUserConfig(AppConstants.SYSTEM_NOTIFICATION, systemNotificationSettingsList.getStoredValue());
	}
	
	@SuppressWarnings("unchecked")
	public void loadPrefererences(){
		themesList.setWidgetValue(CurrentState.getUserConfig(AppConstants.SYSTEM_THEME,"String").toString());
		String notification = CurrentState.getUserConfig(AppConstants.SYSTEM_NOTIFICATION,"String").toString();
		if(notification.trim().length()!=0)
			systemNotificationSettingsList.setWidgetValue(CurrentState.getUserConfig(AppConstants.SYSTEM_NOTIFICATION,"String").toString());
		else // by default error's notifications should me appear
			systemNotificationSettingsList.setWidgetValue(AppConstants.SYSTEM_NOTIFY_ERROR);
		selectedSections = null;
		try{
			selectedSections=(HashMap<String,List<String>>)CurrentState.getUserConfig("defaultWidgets","HashMap<String,List>");
		}
		catch(Exception e){
			//Window.alert("exception:"+e.getMessage());
		}
		if(selectedSections==null)
			selectedSections=sectionFieldsMap;
		templateWidget.loadValues(selectedSections);
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
								validateUser((String)JsonUtil.shoehornJson(response.getText(),"String"),
										currentPassword.getText());
							} else
							Util.showErrorMsg("PreferencesScreen", _("Password change failed."));
						}
					});
				} catch (RequestException e) {
					Window.alert(e.getMessage());
				}
			} else {

				// TODO normal mode code goes here
			}
		} else {
			Window.alert(_("Enter correct information to change password."));
		}
	}

	public void validateUser(String username, String password) {
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
						Util.showErrorMsg("PreferencesScreen", _("Invalid password."));
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
							Util.showInfoMsg("PreferencesScreen", _("Password changed successfully."));
							currentPassword.setText("");
							newPassword.setText("");
							confirmNewPassword.setText("");
							Util.closeTab(getPreferencesScreen());
						} else
							Util.showErrorMsg("FaxSubsystem", _("Password change failed."));
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

		//////////////////////////////////// Preparing UI Tab Elements //////////////////////////////////
		final FlexTable uiFlexTable = new FlexTable();
		uiFlexTable.addStyleName("cw-FlexTable");

		int row = 0;

		uiFlexTable.setHTML(row, 0, _("Select theme to apply"));

		themesList = new CustomListBox();
		themesList.addItem("chrome");
		themesList.addItem("standard");
		themesList.addItem("dark");

		uiFlexTable.setWidget(row, 1, themesList);

		themesList.addChangeHandler(new ChangeHandler() {
			public void onChange(ChangeEvent evt) {
				CurrentState.CUR_THEME = themesList.getItemText(themesList
						.getSelectedIndex());
				isThemeChanged = true;
				Util.updateStyleSheets(CurrentState.CUR_THEME,
						CurrentState.LAST_THEME);
			}
		});
		
		row++;
		
		providerGroupList = new CustomListBox();
		populateProviderGroupList();
		Label provGroupLabel=new Label(_("Default Provider Group") + " :");
		uiFlexTable.setWidget(row, 0, provGroupLabel);
		uiFlexTable.setWidget(row, 1, providerGroupList);
		
		row++;
		
		Label notificationLabel=new Label(_("Show System Notifications") + " :");
		uiFlexTable.setWidget(row, 0, notificationLabel);
		systemNotificationSettingsList = new CustomListBox();
		systemNotificationSettingsList.addItem(AppConstants.SYSTEM_NOTIFY_NONE);
		systemNotificationSettingsList.addItem(AppConstants.SYSTEM_NOTIFY_ALL);
		systemNotificationSettingsList.addItem(AppConstants.SYSTEM_NOTIFY_INFO);
		systemNotificationSettingsList.addItem(AppConstants.SYSTEM_NOTIFY_ERROR);
		uiFlexTable.setWidget(row, 1, systemNotificationSettingsList);
		
		VerticalPanel uiVerticalPanel = new VerticalPanel();
		uiVerticalPanel.add(uiFlexTable);
		// Adding theme tab
		tabPanel.add(uiVerticalPanel, "UI");
		////////////////////////////////////End Preparing UI Tab Elements //////////////////////////////////
		
		
		//////////////////////////////////// Preparing Password Tab //////////////////////////////////
		final FlexTable passwordsFlexTable = new FlexTable();
		passwordsFlexTable.addStyleName("cw-FlexTable");

		passwordsFlexTable.setHTML(0, 0, _("Enter current password") + " :");
		currentPassword = new PasswordTextBox();
		passwordsFlexTable.setWidget(0, 1, currentPassword);

		passwordsFlexTable.setHTML(1, 0, _("Enter new password") + " :");
		newPassword = new PasswordTextBox();
		passwordsFlexTable.setWidget(1, 1, newPassword);

		passwordsFlexTable.setHTML(2, 0, _("Confirm new password") + " :");
		confirmNewPassword = new PasswordTextBox();
		passwordsFlexTable.setWidget(2, 1, confirmNewPassword);

		VerticalPanel passwordsVerticalPanel = new VerticalPanel();
		passwordsVerticalPanel.add(passwordsFlexTable);
		// Adding password tab
		tabPanel.add(passwordsVerticalPanel, _("Password"));
		//////////////////////////////////// End Preparing Password Tab //////////////////////////////////

		//////////////////////////////////// Preparing Navigations Tab //////////////////////////////////
		navigationsVerticalPanel = new VerticalPanel();
		Label navLabel = new Label(_("Show following items for navigation."));
		navLabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		navigationsVerticalPanel.add(navLabel);
		createNavigatonOptions();
		navigationsVerticalPanel.add(navigationsFlexTable);
		// Adding password tab
		tabPanel.add(navigationsVerticalPanel, _("Navigations"));
		//////////////////////////////////// End Preparing Navigations Tab //////////////////////////////////

		//////////////////////////////////// Preparing Printer Tab //////////////////////////////////
		final FlexTable printersFlexTable = new FlexTable();
		printersFlexTable.addStyleName("cw-FlexTable");

		printersFlexTable.setHTML(0, 0, _("Select default printer"));

		printersList = new CustomListBox();
		Util.callApiMethod("Printing", "GetPrinters", (List)null, new CustomRequestCallback(){
			@Override
			public void onError() {
			}
			@Override
			public void jsonifiedData(Object data) {
				if(data==null)
					return;
				@SuppressWarnings("unchecked")
				HashMap<String,String> result = (HashMap<String,String>)data;
				Iterator<String> iterator = result.keySet().iterator();
				while(iterator.hasNext()){
					String key = iterator.next();
					printersList.addItem(key, result.get(key));
				}
				printersList.setWidgetValue(""+CurrentState.getUserConfig("defaultPrinter"));
			}
		}, "HashMap<String,String>");
		
	

		printersFlexTable.setWidget(0, 1, printersList);

		printersList.addChangeHandler(new ChangeHandler() {
			public void onChange(ChangeEvent evt) {
			
			}
		});
		VerticalPanel printersVerticalPanel = new VerticalPanel();
		printersVerticalPanel.add(printersFlexTable);
		// Adding theme tab
		tabPanel.add(printersVerticalPanel, _("Printers"));
		////////////////////////////////////End Preparing Printer Tab //////////////////////////////////
		
		////////////////////////////////////Preparing Widgets Tab //////////////////////////////////

		VerticalPanel widgetsVerticalPanel = new VerticalPanel();
		templateWidget = new TemplateWidget(sectionFieldsMap);
		widgetsVerticalPanel.add(templateWidget);
		tabPanel.add(widgetsVerticalPanel, _("Dashboard Widgets"));
		//////////////////////////////////// End Widgets Tab //////////////////////////////////
	}

	protected void createNavigatonOptions() {
		// preparing Navigation tab
		navigationsVerticalPanel.clear();
		navigationsFlexTable = new FlexTable();
		navigationsCheckboxes.clear();
		navigationsFlexTable.addStyleName("cw-FlexTable");

		final HashMap<String, HashMap<String, Integer>> leftNavCategories = CurrentState
				.getLeftNavigationOptions();
		Iterator<String> itrCats = leftNavCategories.keySet().iterator();
		int i = 0;
		while (itrCats.hasNext()) {
			final String categoryName = itrCats.next();
			Label navLabel = new Label(categoryName);
			navLabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
			navigationsFlexTable.setWidget(i++, 0, navLabel);
			final HashMap<String, Integer> leftNavOpts = leftNavCategories
					.get(categoryName);
			Iterator<String> itr = leftNavOpts.keySet().iterator();
			while (itr.hasNext()) {
				final String Option = itr.next();
				final Integer OptionVal = leftNavOpts.get(Option);
				navigationsFlexTable.setHTML(i, 0, Option);
				final CheckBox checkBox = new CheckBox();
				if (OptionVal == 1)
					checkBox.setValue(true);
				else
					checkBox.setValue(false);
				checkBox.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						isNavigatioMenuChanged = true;
						if (checkBox.getValue()) {
							leftNavOpts.put(Option, 1);
						} else {
							leftNavOpts.put(Option, 0);
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
	
	public void populateProviderGroupList()
	{

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "ProviderGroups" };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleSupportPicklistMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>");
								if (result != null) {
									providerGroupList.clear();
									Set<String> keys = result.keySet();
									Iterator<String> iter = keys.iterator();

									providerGroupList.addItem("","" );
									while (iter.hasNext()) {
										
										final String key = (String) iter.next();
										final String val = (String) result
												.get(key);
										JsonUtil.debug(val);
										providerGroupList.addItem(val,key );
										
									}
									providerGroupList.setWidgetValue(CurrentState.getUserConfig("providerGroup","String")+"");
									
								}else{} // if no result then set value to 0
									//setValue(0);
							} else {
								GWT.log("Result " + response.getStatusText(),
										null);
							}
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception thrown: ", e);
			}
		} else {
			
		}
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