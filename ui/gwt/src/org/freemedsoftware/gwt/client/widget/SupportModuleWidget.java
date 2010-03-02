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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;

public class SupportModuleWidget extends AsyncPicklistWidgetBase implements
		HashSetter {

	protected String moduleName = null;

	protected String hashMapping = null;

	public SupportModuleWidget() {
		super();
	}

	public SupportModuleWidget(String module) {
		// Load superclass constructor first...
		super();
		setModuleName(module);
	}

	/**
	 * Set value of current widget based on integer value, asynchronously.
	 * 
	 * @param widgetValue
	 */
	public void setValue(Integer widgetValue) {
		value = widgetValue;
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			searchBox.setText("Stub Value");
			searchBox.setTitle("Stub Value");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			if (widgetValue.compareTo(0) == 0) {
				searchBox.setText("");
				searchBox.setTitle("");
			} else {
//				textBox.setEnabled(false);
				String[] params = { moduleName, widgetValue.toString() };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.ModuleInterface.ModuleToTextMethod",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(
								com.google.gwt.http.client.Request request,
								Throwable ex) {
							textBox.setEnabled(true);
						}

						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								if (200 == response.getStatusCode()) {
									String result = (String) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"String");
//									textBox.setEnabled(true);
									if (result != null) {
										searchBox.setText(result);
										searchBox.setTitle(result);
									}
								} else {
									Window.alert(response.toString());
								}
							}
						}
					});
				} catch (RequestException e) {
					textBox.setEnabled(true);
				}
			}
		} else {
			ModuleInterfaceAsync service = null;
			try {
				service = ((ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
			} catch (Exception e) {
			}
			textBox.setEnabled(false);
			service.ModuleToTextMethod(moduleName, widgetValue,
					new AsyncCallback<String>() {
						public void onSuccess(String textual) {
							textBox.setEnabled(true);
							searchBox.setText(textual);
							searchBox.setTitle(textual);
						}

						public void onFailure(Throwable t) {

						}
					});

		}
	}

	/**
	 * Set module class name.
	 * 
	 * @param module
	 */
	public void setModuleName(String module) {
		moduleName = module;
	}

	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Handle in a stubbed sort of way
			List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
			map.clear();
			addKeyValuePair(items, new String("Hackenbush, Hugo Z"),
					new String("1"));
			addKeyValuePair(items, new String("Firefly, Rufus T"), new String(
					"2"));
			cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { moduleName, req };
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
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (result != null) {
									Set<String> keys = result.keySet();
									Iterator<String> iter = keys.iterator();

									List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
									map.clear();
									while (iter.hasNext()) {
										final String key = (String) iter.next();
										final String val = (String) result
												.get(key);
										addKeyValuePair(items, val, key);
									}
									cb.onSuggestionsReady(r,
											new SuggestOracle.Response(items));
								}else // if no result then set value to 0
									setValue(0);
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
			ModuleInterfaceAsync service = null;
			try {
				service = ((ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
			} catch (Exception e) {
			}

			service.ModuleSupportPicklistMethod(moduleName, req,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> result) {
							Set<String> keys = result.keySet();
							Iterator<String> iter = keys.iterator();

							List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
							map.clear();
							while (iter.hasNext()) {
								final String key = (String) iter.next();
								final String val = (String) result.get(key);
								addKeyValuePair(items, val, key);
							}
							cb.onSuggestionsReady(r,
									new SuggestOracle.Response(items));
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception thrown: ", t);
						}

					});
		}
	}

	@Override
	public void getTextForValue(Integer val) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			searchBox.setText("Hackenbush, Hugo Z (STUB)");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { moduleName, val.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleToTextMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								String result = (String) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"String");
								if (result != null) {
								}
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
			ModuleInterfaceAsync service = null;
			try {
				service = ((ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
			} catch (Exception e) {
			}
			service.ModuleToTextMethod(moduleName, val,
					new AsyncCallback<String>() {
						public void onSuccess(String r) {
							searchBox.setText(r);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getStoredValue() {
		return getValue().toString();
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		setValue(Integer.parseInt(data.get(hashMapping)));
	}
	
	public void setEnable(boolean val){
		textBox.setEnabled(val);
	}
}
