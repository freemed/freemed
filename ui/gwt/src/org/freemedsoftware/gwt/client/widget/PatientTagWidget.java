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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Module.PatientTagAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.KeyCodes;
import com.google.gwt.event.dom.client.KeyUpEvent;
import com.google.gwt.event.dom.client.KeyUpHandler;
import com.google.gwt.event.logical.shared.HasValueChangeHandlers;
import com.google.gwt.event.logical.shared.SelectionEvent;
import com.google.gwt.event.logical.shared.SelectionHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.event.shared.HandlerRegistration;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;
import com.google.gwt.user.client.ui.SuggestOracle.Suggestion;

public class PatientTagWidget extends WidgetInterface implements
		HasValueChangeHandlers<String> {

	protected String value = new String("");

	protected HashMap<String, String> map;

	// private final FlexTable listPanel;

	protected SuggestBox searchBox;

	protected TextBox textBox;

	private final VerticalPanel layout;

	private HashMap<ValueChangeHandler<String>, HandlerRegistration> changeHandlers = new HashMap<ValueChangeHandler<String>, HandlerRegistration>();

	public PatientTagWidget() {
		// Log.setUncaughtExceptionHandler();

		map = new HashMap<String, String>();

		layout = new VerticalPanel();

		textBox = new TextBox();

		searchBox = new SuggestBox(new SuggestOracle() {
			public void requestSuggestions(Request r, Callback cb) {
				loadSuggestions(r.getQuery(), r, cb);
			}
		}, textBox);
		searchBox.addValueChangeHandler(new ValueChangeHandler<String>() {
			@Override
			public void onValueChange(ValueChangeEvent<String> event) {
				// Window.alert("Here be the new value:" + event.getValue());
			}
		});
		searchBox.addKeyUpHandler(new KeyUpHandler() {
			@Override
			public void onKeyUp(KeyUpEvent event) {
				int keyCode = event.getNativeKeyCode();
				switch (keyCode) {
				case KeyCodes.KEY_ESCAPE:
				case KeyCodes.KEY_BACKSPACE:
					// Clear any current values
					searchBox.setText("");
					searchBox.setTitle("");
					setValue("");
					textBox.cancelKey();
					break;

				default:
					// Ignore any other keystroke
					break;
				}
			}
		});
		searchBox.addSelectionHandler(new SelectionHandler<Suggestion>() {
			@Override
			public void onSelection(SelectionEvent<Suggestion> event) {
				Suggestion s = (Suggestion) event.getSelectedItem();
				value = s.getDisplayString();
				setTitle(s.getDisplayString());
				onSelected();
			}
		});
		searchBox.setLimit(10);
		searchBox.setAnimationEnabled(true);
		layout.add(searchBox);

		initWidget(layout);
	}

	public TextBox getTextEntryWidget() {
		return textBox;
	}

	public PatientTagWidget getWidgetBase() {
		return this;
	}

	/**
	 * Resolve value of widget from full text.
	 * 
	 * @param text
	 * @return
	 */
	public Integer getValueFromText(String text) {
		String found = new String("");
		Iterator<String> keys = map.keySet().iterator();
		while (found.length() == 0 && keys.hasNext()) {
			String cur = (String) keys.next();
			if (cur.compareTo(text) == 0) {
				found = (String) map.get(cur);
			}
		}
		return new Integer((String) found);
	}

	/**
	 * Get string value of currently selected patient.
	 * 
	 * @return
	 */
	public String getText() {
		return searchBox.getText();
	}

	/**
	 * Get integer value of currently selected patient.
	 * 
	 * @return Current selected patient value
	 */
	public String getValue() {
		return value;
	}

	/**
	 * Passthrough for focus.
	 * 
	 * @param focused
	 */
	public void setFocus(boolean focused) {
		searchBox.setFocus(focused);
	}

	public void addChangeHandler(ValueChangeHandler<String> handler) {
		this.addValueChangeHandler(handler);
	}

	public void removeChangeHandler(ValueChangeHandler<String> handler) {
		try {
			changeHandlers.get(handler).removeHandler();
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
	}

	@Override
	public HandlerRegistration addValueChangeHandler(
			ValueChangeHandler<String> handler) {
		HandlerRegistration r = addHandler(handler, ValueChangeEvent.getType());
		changeHandlers.put(handler, r);
		return r;
	}

	/**
	 * Fire ValueChangeHandler classes attached to this object if there are any.
	 * 
	 */
	protected void onSelected() {
		// Fire change
		ValueChangeEvent.fire(this, value);
	}

	/**
	 * Clear contents of composite widget.
	 * 
	 */
	public void clear() {
		searchBox.setText("");
		map.clear();
	}

	/**
	 * Map key and value pair into the widget. Only used by subclasses.
	 * 
	 * @param key
	 * @param value
	 */
	protected void addKeyValuePair(List<SuggestOracle.Suggestion> items,
			final String key, final String value) {
		// Log.debug("Adding key = " + key + ", value = " + value);
		map.put(key, value);
		items.add(new SuggestOracle.Suggestion() {
			public String getDisplayString() {
				return key;
			}

			public String getReplacementString() {
				return key;
			}

			@SuppressWarnings("unused")
			public String getValue() {
				return value;
			}
		});
	}

	/**
	 * Set value of current widget based on integer value, asynchronously.
	 * 
	 * @param widgetValue
	 */
	public void setValue(String widgetValue) {
		this.value = widgetValue;
		if (widgetValue == null || widgetValue == "") {
			searchBox.setText("");
			searchBox.setTitle("");
		} else {
			searchBox.setText(widgetValue);
			searchBox.setTitle(widgetValue);
		}
	}

	public String getWidgetValue() {
		return value;
	}

	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Handle in a stubbed sort of way
			List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
			map.clear();
			addKeyValuePair(items, new String("Diabetes"), new String(
					"Diabetes"));
			addKeyValuePair(items, new String("Neuropathy"), new String(
					"Neuropathy"));
			cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { req };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.PatientTag.ListTags",
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
									List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
									map.clear();
									Iterator<String> iter = result.values()
											.iterator();
									while (iter.hasNext()) {
										String x = iter.next();
										addKeyValuePair(items, x, x);
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
			PatientTagAsync service = null;
			try {
				service = ((PatientTagAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.PatientTag"));
			} catch (Exception e) {
			}

			service.ListTags(req, new AsyncCallback<HashMap<String, String>>() {
				@Override
				public void onSuccess(HashMap<String, String> result) {
					List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
					map.clear();
					Iterator<String> iter = result.values().iterator();
					while (iter.hasNext()) {
						String x = iter.next();
						addKeyValuePair(items, x, x);
					}
					cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception thrown: ", t);
					Util.showErrorMsg("PatientTagWidget", "Exception: " + t.toString());
				}

			});
		}
	}

}
