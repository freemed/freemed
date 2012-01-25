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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.UserInterfaceAsync;

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

public class UserWidget extends AsyncPicklistWidgetBase {

	protected String userType;

	public UserWidget() {
		super();
		userType = "";
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
				String[] params = { widgetValue.toString() };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL.encode(Util
								.getJsonRequest(
										"org.freemedsoftware.api.UserInterface.GetRecord",
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
							if (Util.checkValidSessionResponse(response
									.getText())) {
								if (200 == response.getStatusCode()) {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parseStrict(response
															.getText()),
													"HashMap<String,String>");
									if (result != null) {
										searchBox.setText(result
												.get("userdescrip"));
										searchBox.setTitle(result
												.get("userdescrip"));
									}
								} else {
									Window.alert(response.toString());
								}
							}
						}
					});
				} catch (RequestException e) {

				}
			}
		} else {
			UserInterfaceAsync service = null;
			try {
				service = ((UserInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.UserInterface"));
			} catch (Exception e) {
			}

			service.GetRecord(widgetValue,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> r) {
							searchBox.setText(r.get("userdescrip"));
							searchBox.setTitle(r.get("userdescrip"));
						}

						public void onFailure(Throwable t) {

						}
					});

		}
	}

	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		if (req.length() < CurrentState.getMinCharCountForSmartSearch())
			return;
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

			RequestBuilder builder = null;
			if (userType.equals("")) {
				String[] params = { req };
				builder = new RequestBuilder(
						RequestBuilder.POST,
						URL.encode(Util
								.getJsonRequest(
										"org.freemedsoftware.api.UserInterface.GetUsers",
										params)));
			} else {
				String[] params = { req, userType };
				builder = new RequestBuilder(
						RequestBuilder.POST,
						URL.encode(Util
								.getJsonRequest(
										"org.freemedsoftware.api.UserInterface.GetUsers",
										params)));
			}
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
								String[][] result = (String[][]) JsonUtil
										.shoehornJson(
												JSONParser.parseStrict(response
														.getText()),
												"String[][]");
								if (result != null) {
									List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
									map.clear();
									for (int iter = 0; iter < result.length; iter++) {
										String[] x = result[iter];
										final String key = x[1];
										final String val = x[0];
										addKeyValuePair(items, val, key);
									}
									cb.onSuggestionsReady(r,
											new SuggestOracle.Response(items));
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
			UserInterfaceAsync service = null;
			try {
				service = ((UserInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.UserInterface"));
			} catch (Exception e) {
			}

			service.GetUsers(req, new AsyncCallback<String[][]>() {
				public void onSuccess(String[][] result) {
					List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
					map.clear();
					for (int iter = 0; iter < result.length; iter++) {
						String[] x = result[iter];
						final String key = x[1];
						final String val = x[0];
						addKeyValuePair(items, val, key);
					}
					cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
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
			String[] params = { val.toString() };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.UserInterface.GetRecord",
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
										.shoehornJson(
												JSONParser.parseStrict(response
														.getText()),
												"HashMap<String,String>");
								if (result != null) {
									searchBox.setText(result.get("userdescrip"));
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
			UserInterfaceAsync service = null;
			try {
				service = ((UserInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.UserInterface"));
			} catch (Exception e) {
			}
			service.GetRecord(val,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> r) {
							searchBox.setText(r.get("userdescrip"));
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	public void setUserType(String type) {
		userType = type;
	}
}
