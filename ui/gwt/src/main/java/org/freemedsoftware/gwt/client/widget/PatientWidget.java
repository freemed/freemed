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
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;

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

public class PatientWidget extends AsyncPicklistWidgetBase implements
		HashSetter {

	protected String hashMapping = null;
	protected CustomRequestCallback callback=null;
	
	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		if(req.length()<CurrentState.getMinCharCountForSmartSearch())
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
			String[] params = { req, new Integer(20).toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.PatientInterface.Picklist",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Window.alert(ex.toString());
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								HashMap<Integer, String> result = (HashMap<Integer, String>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<Integer,String>");
								if (result != null) {
									Set<Integer> keys = result.keySet();
									Iterator<Integer> iter = keys.iterator();

									List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
									map.clear();
									while (iter.hasNext()) {
										Integer keyInt = (Integer) iter.next();
										String key = keyInt.toString();
										String val = (String) result
												.get(keyInt);
										addKeyValuePair(items, val, key);
									}
									cb.onSuggestionsReady(r,
											new SuggestOracle.Response(items));
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
			PatientInterfaceAsync service = null;
			try {
				service = ((PatientInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface"));
			} catch (Exception e) {
			}

			service.Picklist(req, new Integer(20),
					new AsyncCallback<HashMap<Integer, String>>() {
						public void onSuccess(HashMap<Integer, String> result) {
							Set<Integer> keys = result.keySet();
							Iterator<Integer> iter = keys.iterator();

							List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
							map.clear();
							while (iter.hasNext()) {
								Integer keyInt = (Integer) iter.next();
								String key = keyInt.toString();
								// Log.debug("keyInt = " + keyInt.toString());
								String val = (String) result.get(keyInt);
								addKeyValuePair(items, val, key);
							}
							cb.onSuggestionsReady(r,
									new SuggestOracle.Response(items));
						}

						public void onFailure(Throwable t) {
							Window.alert(t.getMessage());
						}

					});
		}
	}

	public void setValue(Integer v) {
		value = v;
		getTextForValue(value);
	}

	@Override
	public void getTextForValue(Integer val) {
		if (val > 0) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				searchBox.setText("Hackenbush, Hugo Z (STUB)");
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				textBox.setEnabled(false);
				String[] params = { val.toString() };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.PatientInterface.ToText",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(
								com.google.gwt.http.client.Request request,
								Throwable ex) {
							textBox.setEnabled(true);
							Window.alert(ex.toString());
						}

						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							textBox.setEnabled(true);
							if (Util.checkValidSessionResponse(response
									.getText())) {
								if (200 == response.getStatusCode()) {
									String result = (String) JsonUtil
											.shoehornJson(JSONParser
													.parseStrict(response.getText()),
													"String");
									if (result != null) {
										searchBox.setText(result);
										if(callback!=null){
											callback.jsonifiedData("done");
										}
									}
								} else {
									Window.alert(response.toString());
								}
							}
						}
					});
				} catch (RequestException e) {
					textBox.setEnabled(true);
					Window.alert(e.toString());
				}
			} else {
				PatientInterfaceAsync service = null;
				try {
					service = ((PatientInterfaceAsync) Util
							.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface"));
				} catch (Exception e) {
				}
				textBox.setEnabled(false);
				service.ToText(val, true, new AsyncCallback<String>() {
					public void onSuccess(String r) {
						textBox.setEnabled(true);
						searchBox.setText(r);
					}

					public void onFailure(Throwable t) {
						textBox.setEnabled(true);
						GWT.log("Exception", t);
					}
				});
			}
		}
	}
	
	public void setHashMapping(String hm) {
		hashMapping = hm;
	}
	
	public void setAfterSetValueCallBack(CustomRequestCallback cb){
		callback=cb;
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
	

}
