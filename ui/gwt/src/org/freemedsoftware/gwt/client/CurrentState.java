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

package org.freemedsoftware.gwt.client;

import java.io.Serializable;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.MainScreen;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;

public class CurrentState {

	protected HashMap<String, String> statusItems;

	protected Label statusBar = null;

	protected Toaster toaster = null;

	protected TabPanel tabPanel = null;

	protected String locale = "en_US";

	protected Integer defaultProvider = new Integer(0);

	protected HashMap<Integer, PatientScreen> patientScreenMap = new HashMap<Integer, PatientScreen>();

	protected HashMap<String, String> userConfiguration = new HashMap<String, String>();

	protected FreemedInterface freemedInterface = null;

	protected MainScreen mainScreen = null;

	public CurrentState() {
		statusItems = new HashMap<String, String>();
		retrieveUserConfiguration(true);
	}

	/**
	 * Bulk assign mainscreen object
	 * 
	 * @param m
	 */
	public void assignMainScreen(MainScreen m) {
		mainScreen = m;
		assignStatusBar(m.getStatusBar());
		assignTabPanel(m.getTabPanel());
	}

	public void assignFreemedInterface(FreemedInterface i) {
		freemedInterface = i;
	}

	/**
	 * Assign status bar object.
	 * 
	 * @param w
	 */
	public void assignStatusBar(Label l) {
		statusBar = l;
	}

	/**
	 * Assign default provider.
	 * 
	 * @param p
	 */
	public void assignDefaultProvider(Integer p) {
		defaultProvider = p;
	}

	/**
	 * Assign tab panel object.
	 * 
	 * @param t
	 */
	public void assignTabPanel(TabPanel t) {
		tabPanel = t;
		/*
		 * t.addTabListener(new TabListener() {
		 * 
		 * public boolean onBeforeTabSelected(SourcesTabEvents sender, int
		 * tabIndex) { return false; }
		 * 
		 * public void onTabSelected(SourcesTabEvents sender, int tabIndex) { //
		 * Fire focus event try { ((ScreenInterface)
		 * getTabPanel().getWidget(tabIndex)) .onFocus(); } catch (Exception ex)
		 * { JsonUtil.debug(ex.toString()); } }
		 * 
		 * });
		 */
	}

	/**
	 * Assign toaster object.
	 * 
	 * @param t
	 */
	public void assignToaster(Toaster t) {
		toaster = t;
	}

	/**
	 * Assign locale value.
	 * 
	 * @param l
	 *            Locale string, default is "en_US"
	 */
	public void assignLocale(String l) {
		locale = l;
	}

	/**
	 * Add an item to the status bar stack.
	 * 
	 * @param module
	 * @param text
	 */
	public void statusBarAdd(String module, String text) {
		statusItems.put(module, text);
		((Label) statusBar).setText("Processing (" + text + ")");
	}

	/**
	 * Remove an item from the status bar stack.
	 * 
	 * @param module
	 */
	public void statusBarRemove(String module) {
		statusItems.remove(module);
		if (statusItems.size() > 0) {
			((Label) statusBar).setText("Processing");
		} else {
			((Label) statusBar).setText("Ready");
		}
	}

	public String getLocale() {
		return locale;
	}

	public Integer getDefaultProvider() {
		return defaultProvider;
	}

	public FreemedInterface getFreemedInterface() {
		return freemedInterface;
	}

	public MainScreen getMainScreen() {
		return mainScreen;
	}

	public TabPanel getTabPanel() {
		return tabPanel;
	}

	public Toaster getToaster() {
		return toaster;
	}

	public HashMap<Integer, PatientScreen> getPatientScreenMap() {
		return patientScreenMap;
	}

	/**
	 * Get user specific configuration value, or "" if there is no value.
	 * 
	 * @param key
	 * @return
	 */
	public String getUserConfig(String key) {

		JsonUtil.debug("getUserConfig() called");
		if (userConfiguration.size() != 0) {
			return userConfiguration.get(key);
		}
		JsonUtil.debug("getUserConfig(): was unable to find userConfiguration "
				+ "| key = " + key);
		return "";

	}

	/**
	 * Set user specific configuration value.
	 * 
	 * @param key
	 * @param value
	 */
	public void setUserConfig(String key, Object value) {
		// Set key locally
		if (value == null) {
			value = new String("");
			JsonUtil.debug("For key = " + key + ", value was null");
		}
		if (value instanceof String) {
			userConfiguration.put(key, (String) value);
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
						getToaster().addItem("CurrentState",
								"Failed to update configuration value.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							getToaster().addItem("CurrentState",
									"Updated configuration value.",
									Toaster.TOASTER_INFO);
						} else {
							getToaster().addItem("CurrentState",
									"Failed to update configuration value.",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				getToaster().addItem("CurrentState",
						"Failed to update configuration value.",
						Toaster.TOASTER_ERROR);
			}

		} else {
			// GWT-RPC
		}
	}

	public void retrieveUserConfiguration(boolean forceReload) {
		retrieveUserConfiguration(forceReload, null);
	}

	/**
	 * Pull user configuration settings into CurrentState object.
	 * 
	 * @param forceReload
	 */
	public void retrieveUserConfiguration(boolean forceReload,
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

								JsonUtil
										.debug("Retrieved good looking content");

								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									JsonUtil
											.debug("successfully retrieved User Configuration");
									userConfiguration = r;

									if (onLoad != null) {
										onLoad.execute();
									}
								}
							} else {
								userConfiguration = new HashMap<String, String>();
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

}
