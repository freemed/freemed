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

package org.freemedsoftware.gwt.client;

import java.io.Serializable;
import java.util.Date;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.MainScreen;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.event.shared.HandlerManager;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.DecoratedTabPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;

public class CurrentState {

	protected static HashMap<String, String> statusItems = new HashMap<String, String>();

	protected static Label statusBar = null;

	protected static Toaster toaster = null;

	protected static DecoratedTabPanel tabPanel = null;

	protected static String locale = "en_US";

	protected static String currentPageHelp = "main";

	protected static Integer defaultProvider = new Integer(0);

	protected static Integer defaultFacility = new Integer(0);

	protected static String defaultUser = "";

	protected static String userType = "";

	protected static HashMap<Integer, PatientScreen> patientScreenMap = new HashMap<Integer, PatientScreen>();

	protected static HashMap<Integer, HashMap<String, PatientScreenInterface>> patientSubScreenMap = new HashMap<Integer, HashMap<String, PatientScreenInterface>>();

	protected static HashMap<String, Object> userConfiguration = new HashMap<String, Object>();

	protected static HashMap<String, String> userModules = new HashMap<String, String>();

	protected static HashMap<String, String> systemConfiguration = new HashMap<String, String>();

	protected static FreemedInterface freemedInterface = null;

	protected static MainScreen mainScreen = null;

	protected static HashMap<String, HashMap<String, Integer>> leftNavigationOptions = new HashMap<String, HashMap<String, Integer>>();

	protected static HandlerManager eventBus = new HandlerManager(null);

	public static String CUR_THEME = "chrome";

	public static String LAST_THEME = "chrome";

	public static Integer defaultProviderGroup = null;

	private static String SYSTEM_NOTIFY_TYPE = AppConstants.SYSTEM_NOTIFY_ERROR;

	public static boolean FormAutosaveEnable = true;

	protected static Integer FormAutosaveInterval = 60 * 1000;

	protected static Integer MinCharCountForSmartSearch = 1;

	public CurrentState() {
		retrieveUserConfiguration(true);
		retrieveSystemConfiguration(true, null);
	}

	/**
	 * Bulk assign mainscreen object
	 * 
	 * @param m
	 */
	public static void assignMainScreen(MainScreen m) {
		mainScreen = m;
		assignStatusBar(m.getStatusBar());
		assignTabPanel(m.getTabPanel());
	}

	public static void assignFreemedInterface(FreemedInterface i) {
		freemedInterface = i;
	}

	/**
	 * Assign status bar object.
	 * 
	 * @param w
	 */
	public static void assignStatusBar(Label l) {
		statusBar = l;
	}

	/**
	 * Assign default provider.
	 * 
	 * @param p
	 */
	public static void assignDefaultProvider(Integer p) {
		defaultProvider = p;
	}

	/**
	 * Assign default facility.
	 * 
	 * @param f
	 */
	public static void assignDefaultFacility(Integer f) {
		defaultFacility = f;
	}

	/**
	 * Assign default User.
	 * 
	 * @param u
	 */
	public static void assignDefaultUser(String u) {
		defaultUser = u;
	}

	/**
	 * Assign User Type.
	 * 
	 * @param u
	 */
	public static void assignUserType(String u) {
		userType = u;
	}

	/**
	 * Assign tab panel object.
	 * 
	 * @param t
	 */
	public static void assignTabPanel(DecoratedTabPanel t) {
		tabPanel = t;
	}

	/**
	 * Assign toaster object.
	 */
	public static void assignToaster(Toaster t) {
		toaster = t;
	}

	/**
	 * Assign locale value.
	 * 
	 * @param l
	 *            Locale string, default is "en_US"
	 */
	public static void assignLocale(String l) {
		locale = l;
	}

	/**
	 * Assign current page.
	 * 
	 * @param l
	 *            current page string, default is "main"
	 */
	public static void assignCurrentPageHelp(String currentPageHelp) {
		CurrentState.currentPageHelp = currentPageHelp;
	}

	/**
	 * Assign Form Autosave flag
	 * 
	 * @param boolean
	 */
	public static void assignFormAutoSave(Boolean enable) {
		FormAutosaveEnable = enable;
	}

	/**
	 * Assign Form FormAutosave Interval
	 * 
	 * @param boolean
	 */
	public static void assignFormAutoSaveInterval(Integer interval) {
		FormAutosaveInterval = interval;
	}

	/**
	 * Assign minimum characters count for smart search fields
	 * 
	 * @param boolean
	 */
	public static void assignMinCharCountForSmartSearch(Integer charCount) {
		MinCharCountForSmartSearch = charCount;
	}

	/**
	 * Assign SYSTEM_NOTIFY_TYPE
	 * 
	 * @param boolean
	 */
	public static void assignSYSTEM_NOTIFY_TYPE(String notify_type) {
		if (notify_type != null && notify_type.length() > 0) {
			SYSTEM_NOTIFY_TYPE = notify_type;
		} else {
			SYSTEM_NOTIFY_TYPE = AppConstants.SYSTEM_NOTIFY_ALL;
		}
	}

	/**
	 * Add an item to the status bar stack.
	 * 
	 * @param module
	 * @param text
	 */
	public static void statusBarAdd(String module, String text) {
		statusItems.put(module, text);
		((Label) statusBar).setText("Processing (" + text + ")");
	}

	/**
	 * Remove an item from the status bar stack.
	 * 
	 * @param module
	 */
	public static void statusBarRemove(String module) {
		statusItems.remove(module);
		if (statusItems.size() > 0) {
			((Label) statusBar).setText("Processing");
		} else {
			((Label) statusBar).setText("Ready");
		}
	}

	public static String getLocale() {
		return locale;
	}

	public static String getCurrentPageHelp() {
		return currentPageHelp;
	}

	public static Integer getDefaultProvider() {
		return defaultProvider;
	}

	public static Integer getDefaultFacility() {
		return defaultFacility;
	}

	public static String getDefaultUser() {
		return defaultUser;
	}

	public static String getUserType() {
		return userType;
	}

	public static HandlerManager getEventBus() {
		return eventBus;
	}

	public static FreemedInterface getFreemedInterface() {
		return freemedInterface;
	}

	public static MainScreen getMainScreen() {
		return mainScreen;
	}

	public static TabPanel getTabPanel() {
		return tabPanel;
	}

	public static Toaster getToaster() {
		return toaster;
	}

	public static boolean getFormAutoSave() {
		return FormAutosaveEnable;
	}

	public static Integer getFormAutoSaveInterval() {
		return FormAutosaveInterval;
	}

	public static Integer getMinCharCountForSmartSearch() {
		return MinCharCountForSmartSearch;
	}

	public static HashMap<Integer, PatientScreen> getPatientScreenMap() {
		return patientScreenMap;
	}

	public static String getSYSTEM_NOTIFY_TYPE() {
		return SYSTEM_NOTIFY_TYPE;
	}

	/**
	 * Get user specific configuration value, or "" if there is no value.
	 * 
	 * @param key
	 * @return
	 */
	public static Object getUserConfig(String key) {
		JsonUtil.debug("getUserConfig() called");

		if (userConfiguration.size() != 0 && userConfiguration.containsKey(key)) {
			return userConfiguration.get(key);
		}
		JsonUtil.debug("getUserConfig(): was unable to find userConfiguration "
				+ "| key = " + key);
		return "";
	}

	/**
	 * Get user specific configuration JSONified value, or "" if there is no
	 * value.
	 * 
	 * @param key
	 * @return
	 */
	public static Object getUserConfig(String key, String objectType) {
		JsonUtil.debug("getUserConfig() called");
		JsonUtil.debug("key:" + key);
		JsonUtil.debug("objectType:" + objectType);
		if (userConfiguration.size() != 0 && userConfiguration.containsKey(key)) {
			try {
				return JsonUtil.shoehornJson(JSONParser.parseStrict(userConfiguration
						.get(key).toString()), objectType);
			} catch (Exception e) {
				return userConfiguration.get(key);// if already Jsonified
			}
		}
		JsonUtil.debug("getUserConfig(): was unable to find userConfiguration "
				+ "| key = " + key);
		return "";
	}

	/**
	 * Get system specific configuration value, or "" if there is no value.
	 * 
	 * @param key
	 * @return
	 */
	public static String getSystemConfig(String key) {
		JsonUtil.debug("getSystemConfig() called");
		if (systemConfiguration.size() != 0) {
			return systemConfiguration.get(key);
		}
		JsonUtil
				.debug("getSystemConfig(): was unable to find systemConfiguration "
						+ "| key = " + key);
		return "";
	}

	/**
	 * Set user specific configuration value.
	 * 
	 * @param key
	 * @param value
	 */
	public static synchronized void setUserConfig(String key, Object value) {
		// Set key locally
		if (value == null) {
			value = new String("");
			JsonUtil.debug("For key = " + key + ", value was null");
		}
		if (value instanceof String) {
			userConfiguration.put(key, (String) value);
		} else if (value instanceof HashMap) {
			userConfiguration.put(key, JsonUtil.jsonify(value));
		} else if (value instanceof Serializable) {
			userConfiguration.put(key, ((Serializable) value).toString());
		} else {
			JsonUtil.debug("Unable to serialize value");
		}

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// STUBBED mode
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.UserInterface.SetConfigValue",
											new String[] { key,
													JsonUtil.jsonify(value) })));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("CurrentState",
								"Failed to update configuration value.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Util.showInfoMsg("CurrentState",
									"Updated configuration value.");
						} else {
							Util.showErrorMsg("CurrentState",
									"Failed to update configuration value.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("CurrentState",
						"Failed to update configuration value.");
			}

		} else {
			// GWT-RPC
		}
	}

	public static void retrieveUserConfiguration(boolean forceReload) {
		retrieveUserConfiguration(forceReload, null);
	}

	/**
	 * Pull user configuration settings into CurrentState object.
	 * 
	 * @param forceReload
	 */
	public static void retrieveUserConfiguration(boolean forceReload,
			final Command onLoad) {

		JsonUtil.debug("retrieveUserConfiguration called");

		if (userConfiguration == null || forceReload) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// STUBBED mode
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.UserInterface.GetEMRConfiguration",
												new String[] {})));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()
									&& !response.getText().contentEquals("[]")) {
								HashMap<String, Object> r = (HashMap<String, Object>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,Object>");
								if (r != null) {
									JsonUtil
											.debug("successfully retrieved User Configuration");
									userConfiguration = r;
									if (userConfiguration.get("usermodules") != null) {
										userModules = (HashMap<String, String>) getUserConfig(
												"usermodules",
												"HashMap<String,String>");
									}
									if (userConfiguration
											.get("LeftNavigationMenu") != null) {
										leftNavigationOptions = (HashMap<String, HashMap<String, Integer>>) JsonUtil
												.shoehornJson(
														JSONParser
																.parseStrict(CurrentState
																		.getUserConfig(
																				"LeftNavigationMenu")
																		.toString()),
														"HashMap<String,HashMap<String,Integer>>");
										mainScreen.initMainScreen();
									}
									if (onLoad != null) {
										onLoad.execute();
									}
								}
							} else {
								userConfiguration = new HashMap<String, Object>();
								if (onLoad != null) {
									onLoad.execute();
								}
							}
						}
					});
				} catch (RequestException e) {
				}

			} else {
				// GWT-RPC
			}
		}
	}

	public static HashMap<String, HashMap<String, Integer>> getLeftNavigationOptions() {
		return leftNavigationOptions;
	}

	public static void setLeftNavigationOptions(
			HashMap<String, HashMap<String, Integer>> options) {
		leftNavigationOptions = options;
	}

	/**
	 * Pull system configuration settings into CurrentState object.
	 * 
	 * @param forceReload
	 */
	public static void retrieveSystemConfiguration(boolean forceReload) {
		CurrentState.retrieveSystemConfiguration(forceReload, null);
	}

	/**
	 * Pull system configuration settings into CurrentState object.
	 * 
	 * @param forceReload
	 * @param onLoad
	 *            - executes command after loading
	 */
	public static void retrieveSystemConfiguration(boolean forceReload,
			final Command onLoad) {

		JsonUtil.debug("retrieveUserConfiguration called");

		if (systemConfiguration == null || forceReload
				|| systemConfiguration.size() == 0) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// STUBBED mode
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.SystemConfig.GetAllSysOptions",
												new String[] {})));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()
									&& !response.getText().contentEquals("[]")) {

								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									JsonUtil
											.debug("successfully retrieved System Configuration");
									systemConfiguration = r;
									reEvaluateSystemConfiguration();
									if (onLoad != null) {
										onLoad.execute();
									}
								}
							} else {
								systemConfiguration = new HashMap<String, String>();
								if (onLoad != null) {
									onLoad.execute();
								}
							}
						}
					});
				} catch (RequestException e) {
				}

			} else {
				// GWT-RPC
			}
		}
	}

	protected static void reEvaluateSystemConfiguration() {
		if (getSystemConfig("form_autosave") != null)
			assignFormAutoSave(getSystemConfig("form_autosave")
					.equalsIgnoreCase("1"));

		if (getSystemConfig("form_autosave_interval") != null)
			assignFormAutoSaveInterval(Integer
					.parseInt(getSystemConfig("form_autosave_interval")) * 1000);

		if (getSystemConfig("smart_search_char_len") != null)
			assignMinCharCountForSmartSearch(Integer
					.parseInt(getSystemConfig("smart_search_char_len")));
	}

	/**
	 * evaluate whether this menu option should be visible or not
	 * 
	 * @param menuOption
	 *            : name of the navigation option
	 */
	public static boolean isMenuAllowed(String menuCatagory, String menuOption) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			return true;
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			if (leftNavigationOptions.get(menuCatagory) == null)// If
				// menuCatagory
				// not available
				return false;
			Integer optionVal = leftNavigationOptions.get(menuCatagory).get(
					menuOption);
			if (optionVal != null && optionVal == 1) {
				return true;
			}
		}
		return false;
	}

	/*
	 * Checks the permission string and evaluates the current action
	 * 
	 * @param module - module to be check
	 * 
	 * @param action - int value against read/write/delete/modify/lock/show from
	 * constants Class
	 */
	public static boolean isActionAllowed(String module, int action) {
		// if(true) return true; // temporarily blocked permissions
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			return true;
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			if (userModules.get(module) == null)// If
				// module
				// not available
				return false;
			String permissionBits = userModules.get(module);
			if (permissionBits != null) {
				switch (action) {
				case AppConstants.READ:
				case AppConstants.WRITE:
				case AppConstants.MODIFY:
				case AppConstants.DELETE:
				case AppConstants.LOCK: {
					if (permissionBits.charAt(action - 1) == '1')
						return true;
				}
					break;
				case AppConstants.SHOW: {
					if (Integer.parseInt(userModules.get(module)) != 0)
						return true;

				}
					break;
				}

			}
		}
		return false;
	}

	public static boolean isAnyActionAllowed(String module, int action1,
			int action2) {
		return isActionAllowed(module, action1)
				| isActionAllowed(module, action2);
	}

	public static boolean isAnyActionAllowed(String module, int action1,
			int action2, int action3) {
		return isAnyActionAllowed(module, action1, action2)
				| isActionAllowed(module, action3);
	}

	/**
	 * Check the hours of dates whether these dates lie in between break hours
	 * 
	 * @param forceReload
	 * @param onLoad
	 *            - executes command after loading
	 */
	public static synchronized boolean canBookAppoinment(Date startTime,
			Date endTime) {
		boolean flag = true;
		flag = isActionAllowed(SchedulerWidget.moduleName, AppConstants.WRITE);
		return flag;
	}

	public static HashMap<Integer, HashMap<String, PatientScreenInterface>> getPatientSubScreenMap() {
		return patientSubScreenMap;
	}

}
