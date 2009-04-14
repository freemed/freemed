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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;

public class DrugWidget extends WidgetInterface implements HashSetter {

	public class DrugNameWidget extends AsyncPicklistWidgetBase implements
			HashSetter {

		protected String hashMapping = null;

		public DrugNameWidget() {
			super();
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
					textBox.setEnabled(false);
					String[] params = { widgetValue.toString() };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.NDCLexicon.NameLookupToText",
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
										.getText(), state)) {
									if (200 == response.getStatusCode()) {
										String result = (String) JsonUtil
												.shoehornJson(JSONParser
														.parse(response
																.getText()),
														"String");
										textBox.setEnabled(true);
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
				// FIXME! GWT-RPC needs to be implemented here!
			}
		}

		protected void loadSuggestions(String req, final Request r,
				final Callback cb) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// Handle in a stubbed sort of way
				List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
				map.clear();
				addKeyValuePair(items, new String("KEFLEX"), new String("1"));
				addKeyValuePair(items, new String("CITALOPRAM"),
						new String("2"));
				cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { req };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.NDCLexicon.TradenamePicklist",
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
									.getText(), state)) {
								if (200 == response.getStatusCode()) {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>");
									if (result != null) {
										Set<String> keys = result.keySet();
										Iterator<String> iter = keys.iterator();

										List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
										map.clear();
										while (iter.hasNext()) {
											final String key = (String) iter
													.next();
											final String val = (String) result
													.get(key);
											addKeyValuePair(items, val, key);
										}
										cb.onSuggestionsReady(r,
												new SuggestOracle.Response(
														items));
									}
								} else {
									GWT.log("Result "
											+ response.getStatusText(), null);
								}
							}
						}
					});
				} catch (RequestException e) {
					GWT.log("Exception thrown: ", e);
				}
			} else {
				// FIXME: GWT-RPC NOT IMPLEMENTED YET!
			}
		}

		@Override
		public void getTextForValue(Integer val) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				searchBox.setText("KEFLEX CAPSULES");
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { val.toString() };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.NDCLexicon.NameLookupToText",
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
							if (Util.checkValidSessionResponse(response
									.getText(), state)) {
								if (200 == response.getStatusCode()) {
									String result = (String) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"String");
									if (result != null) {
									}
								} else {
									GWT.log("Result "
											+ response.getStatusText(), null);
								}
							}
						}
					});
				} catch (RequestException e) {
					GWT.log("Exception thrown: ", e);
				}
			} else {
				// FIXME: GWT-RPC NOT IMPLEMENTED YET!
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
	}

	protected DrugNameWidget drugNameLookup;

	protected CustomListBox drugStrength;

	protected String hashMapping;

	public DrugWidget() {
		final HorizontalPanel container = new HorizontalPanel();
		initWidget(container);

		drugNameLookup = new DrugNameWidget();
		container.add(drugNameLookup);
		drugNameLookup.addChangeListener(new ChangeListener() {

			public void onChange(Widget sender) {
				JsonUtil.debug("Change detected in drugNameLookup widget");
				Integer value = ((DrugNameWidget) sender).getValue();
				if (Util.isStubbedMode()) {
					// TODO: make this do something in stubbed mode
				} else {
					JsonUtil.debug("Calling populateStrength()");
					populateStrength(value);
				}
			}

		});
		drugStrength = new CustomListBox();
		drugStrength.setVisibleItemCount(1);
		container.add(drugStrength);
	}

	protected void populateStrength(Integer drugValue) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			drugStrength.addItem("125MG", "1");
			drugStrength.addItem("500MG", "2");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { drugValue.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.NDCLexicon.DosagesForDrug",
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
						if (Util.checkValidSessionResponse(response.getText(),
								state)) {
							if (200 == response.getStatusCode()) {
								String[][] r = (String[][]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()), "String[][]");
								if (r != null) {
									drugStrength.clear();
									for (int iter = 0; iter < r.length; iter++) {
										drugStrength.addItem(r[iter][0],
												r[iter][1]);
									}
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
			// FIXME: GWT-RPC NOT IMPLEMENTED YET!
		}
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getStoredValue() {
		return drugNameLookup.getStoredValue();
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		// setValue(Integer.parseInt(data.get(hashMapping)));
	}

}
