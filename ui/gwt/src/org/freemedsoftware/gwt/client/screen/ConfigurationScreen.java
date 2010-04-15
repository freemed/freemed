/*
 * $Id$
 *
 * Authors:
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
import org.freemedsoftware.gwt.client.Api.SystemConfigAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;

import com.google.gwt.core.client.GWT;
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
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ConfigurationScreen extends ScreenInterface {
	
	public final static String moduleName = "admin";

	protected TabPanel tabPanel;

	protected HashMap<String, FlexTable> containers;

	protected HashMap<String, Widget> widgets;

	protected HashMap<String, Integer> containerWidgetCount;

	private static List<ConfigurationScreen> configurationScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static ConfigurationScreen getInstance(){
		ConfigurationScreen configurationScreen=null; 
		
		if(configurationScreenList==null)
			configurationScreenList=new ArrayList<ConfigurationScreen>();
		if(configurationScreenList.size()<AppConstants.MAX_CONFIGURATION_TABS)//creates & returns new next instance of ConfigurationScreen
			configurationScreenList.add(configurationScreen=new ConfigurationScreen());
		else //returns last instance of ConfigurationScreen from list 
			configurationScreen = configurationScreenList.get(AppConstants.MAX_CONFIGURATION_TABS-1);
		return configurationScreen;
	}  
	
	public static boolean removeInstance(ConfigurationScreen configurationScreen){
		return configurationScreenList.remove(configurationScreen);
	}
	
	public ConfigurationScreen() {
		super(moduleName);
		
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setWidth("100%");

		tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);
		// tabPanel.selectTab(0);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		if(canModify){
			final CustomButton commitChangesButton = new CustomButton("Commit Changes",AppConstants.ICON_ADD);
			horizontalPanel.add(commitChangesButton);
			commitChangesButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent event) {
					commitValues();
				}
			});
		}

		populate();
	}

	/**
	 * Retrieve <HashMap> containing key/value pairs representing all current
	 * form configuration values.
	 * 
	 * @return
	 */
	public HashMap<String, String> getAllValues() {
		HashMap<String, String> v = new HashMap<String, String>();
		Iterator<String> iter = widgets.keySet().iterator();
		while (iter.hasNext()) {
			String cur = iter.next();
			if (widgets.get(cur) instanceof CustomListBox) {
				v.put(cur, ((CustomListBox) widgets.get(cur)).getWidgetValue());
			}
			if (widgets.get(cur) instanceof TextBox) {
				v.put(cur, ((TextBox) widgets.get(cur)).getText());
			}
		}
		return v;
	}

	protected void commitValues() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			Util.showInfoMsg("ConfigurationScreen", "Updated configuration.");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(getAllValues()) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.SystemConfig.SetValues",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("ConfigurationScreen", "Failed to update configuration.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Boolean r = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Boolean");
							if (r.booleanValue()) {
								Util.showInfoMsg("ConfigurationScreen", "Updated configuration.");
								CurrentState.retrieveSystemConfiguration(true);//re-evaluate system configuration
							} else {
								Util.showErrorMsg("ConfigurationScreen", "Failed to update configuration.");
							}
						} else {
							Util.showErrorMsg("ConfigurationScreen", "Failed to update configuration.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("ConfigurationScreen", "Failed to update configuration.");
			}
		} else {
			getProxy().SetValues(getAllValues(), new AsyncCallback<Boolean>() {
				public void onSuccess(Boolean result) {
					if (result.booleanValue()) {
						Util.showInfoMsg("ConfigurationScreen", "Updated configuration.");
					} else {
						Util.showErrorMsg("ConfigurationScreen", "Failed to update configuration.");
					}
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
	}

	public void populate() {
		containers = new HashMap<String, FlexTable>();
		widgets = new HashMap<String, Widget>();
		containerWidgetCount = new HashMap<String, Integer>();

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: Simulate
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.SystemConfig.GetConfigSections",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[] r = (String[]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[]");
							// Create the actual tabs
							createTabs(r);
							tabPanel.selectTab(0);

							// Fire off population routine
							populateConfig();
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			getProxy().GetConfigSections(new AsyncCallback<String[]>() {
				public void onSuccess(String[] r) {
					// Create the actual tabs
					createTabs(r);

					// Fire off population routine
					populateConfig();
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
	}

	protected void populateConfig() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: populate config values
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.SystemConfig.GetAll",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							for (int iter = 0; iter < r.length; iter++) {
								try {
									addToStack(r[iter]);
								} catch (Exception ex) {
									JsonUtil.debug(ex.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			getProxy().GetAll(new AsyncCallback<HashMap<String, String>[]>() {
				public void onSuccess(HashMap<String, String>[] r) {
					for (int iter = 0; iter < r.length; iter++) {
						addToStack(r[iter]);
					}
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
	}

	/**
	 * Internal method to create widget instance.
	 * 
	 * @param r
	 * @return
	 */
	protected Widget addWidget(HashMap<String, String> r) {
		String widgetType = r.get("c_type");
		if (widgetType.compareToIgnoreCase("Select") == 0) {
			CustomListBox w = new CustomListBox();
			String[] options = r.get("c_options").split(",");
			for (int iter = 0; iter < options.length; iter++) {
				w.addItem(options[iter]);
			}
			w.setWidgetValue(r.get("c_value"));
			// Add to index
			widgets.put(r.get("c_option"), w);
			return w;
		} else if (widgetType.compareToIgnoreCase("YesNo") == 0) {
			CustomListBox w = new CustomListBox();
			w.addItem("Yes", "1");
			w.addItem("No", "0");
			w.setWidgetValue(r.get("c_value"));
			widgets.put(r.get("c_option"), w);
			return w;
		} else if (widgetType.compareToIgnoreCase("Number") == 0) {
			// TODO: implement number and bounds checking
			TextBox w = new TextBox();
			w.setText(r.get("c_value"));
			widgets.put(r.get("c_option"), w);
			return w;
		} else {
			// Text
			TextBox w = new TextBox();
			w.setText(r.get("c_value"));
			widgets.put(r.get("c_option"), w);
			return w;
		}
	}

	protected void addToStack(HashMap<String, String> r) {
 
		// Add initial widget, get appropriate count and container
		JsonUtil.debug(r.get("c_option") + " (" + r.get("c_type") + ")");
		Widget w = addWidget(r);
		if(!canModify){
			if(w instanceof TextBox)
				((TextBox)w).setEnabled(false);
			else if(w instanceof CustomListBox)
				((CustomListBox)w).setEnabled(false);
		}
		FlexTable f = containers.get(r.get("c_section"));
		JsonUtil.debug(" --- got flextable");
		Integer c = new Integer(0);
		try {
			c = containerWidgetCount.get(r.get("c_section"));
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}

		// Populate proper row of FlexTable
		f.setText(c, 0, r.get("c_title"));
		f.setWidget(c, 1, w);

		// Update count for this particular container
		containerWidgetCount.put(r.get("c_section"), c + 1);
	}

	/**
	 * Create tabbed configuration containers from array of strings with titles
	 * and initialize all counters.
	 * 
	 * @param t
	 */
	protected void createTabs(String[] t) {
		for (int iter = 0; iter < t.length; iter++) {
			// Create container
			FlexTable f = new FlexTable();

			// Add to list of containers and add to present tab panel
			containers.put(t[iter], f);
			tabPanel.add(f, t[iter]);
			containerWidgetCount.put(t[iter], new Integer(0));
		}
	}

	/**
	 * Internal method to get <SystemConfigAsync> proxy.
	 * 
	 * @return
	 */
	protected SystemConfigAsync getProxy() {
		SystemConfigAsync proxy = null;
		try {
			proxy = (SystemConfigAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.SystemConfig");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		return proxy;
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}