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
import java.util.Arrays;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class SupportModuleMultipleChoiceWidget extends WidgetInterface
		implements HashSetter {

	protected SupportModuleWidget supportModuleWidget = null;

	protected String module = null;

	protected VerticalPanel container = null;

	protected List<Integer> widgetValues = new ArrayList<Integer>();

	protected String hashMapping = null;

	public SupportModuleMultipleChoiceWidget() {
		init();
	}

	public SupportModuleMultipleChoiceWidget(String moduleName) {
		init();
		setModuleName(moduleName);
	}

	private void init() {
		final VerticalPanel v = new VerticalPanel();
		initWidget(v);

		container = new VerticalPanel();
		v.add(container);

		// Add picklist for this ...
		supportModuleWidget = new SupportModuleWidget();
		v.add(supportModuleWidget);
		supportModuleWidget.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				SupportModuleWidget w = (SupportModuleWidget) event.getSource();
				addValue(w.getText(), w.getValue());
				w.clear();
			}
		});
	}

	protected void addValue(String text, final Integer value) {
		// Push into internal store of values
		widgetValues.add(value);

		// Create new container, push in
		final HorizontalPanel hp = new HorizontalPanel();
		hp.add(new Label(text));
		Button removeButton = new Button("X");
		removeButton.setTitle("Click to remove this item from this list.");
		removeButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				// Remove the hp object from its parent container, "container"
				((Widget) evt.getSource()).getParent().removeFromParent();

				// Remove from data store
				removeValueFromStore(value);
			}
		});
		hp.add(removeButton);

		container.add(hp);
	}

	/**
	 * Remove all current values from the widget.
	 */
	protected void clearValues() {
		container.clear();
		widgetValues.clear();
	}

	/**
	 * Get integer values associated with this widget.
	 * 
	 * @return
	 */
	public Integer[] getValues() {
		return widgetValues.toArray(new Integer[0]);
	}

	/**
	 * Get all integer values squished into a comma separated string.
	 * 
	 * @return
	 */
	public String getCommaSeparatedValues() {
		if (widgetValues.size() == 0) {
			JsonUtil
					.debug("getCommaSeparatedValues(): no items found for widget "
							+ hashMapping);
			return "";
		}
		try {
			StringBuffer buffer = new StringBuffer();
			Iterator<Integer> iter = widgetValues.iterator();
			if (iter.hasNext()) {
				buffer.append(iter.next());
				while (iter.hasNext()) {
					buffer.append(",");
					buffer.append(iter.next());
				}
			}
			return buffer.toString();
		} catch (Exception ex) {
			JsonUtil.debug("getCommaSeparatedValues(): " + ex.toString());
			return "";
		}
	}

	/**
	 * Assign all integer values from a squished comma separated string.
	 * 
	 * @param v
	 * @return
	 */
	public void setCommaSeparatedValues(String v) {
		if (v != null) {
			List<Integer> a = new ArrayList<Integer>();
			String[] s = v.split(",");
			Iterator<String> iter = Arrays.asList(s).iterator();
			while (iter.hasNext()) {
				String n = iter.next();
				JsonUtil.debug("setCSV (multiple choice) : found " + n);
				a.add(Integer.parseInt(n));
			}
			setValue(a.toArray(new Integer[0]));
		}
	}

	/**
	 * Remove value from the internal widget data store
	 * 
	 * @param value
	 */
	protected void removeValueFromStore(Integer value) {
		widgetValues.remove((Integer) value);
	}

	public void setModuleName(String moduleName) {
		module = moduleName;

		// Pass along to child widgets
		supportModuleWidget.setModuleName(moduleName);
	}

	public void setValue(Integer[] values) {
		// In future, need to implement multiple resolve function so we don't
		// have to make a thousand RPC calls for a thousand entries. - Jeff

		// Clear current values in the widget
		clearValues();

		// Loop through all values given
		for (int iter = 0; iter < values.length; iter++) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				addValue("Value " + new Integer(iter).toString(), new Integer(
						iter));
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				final Integer i = new Integer(values[iter]);
				String[] params = { module, JsonUtil.jsonify(i) };
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
							GWT.log("Exception", ex);
						}

						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							if (200 == response.getStatusCode()) {
								String result = (String) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"String");
								if (result != null) {
									addValue(result, i);
								}
							} else {
								GWT.log(response.toString(), null);
							}
						}
					});
				} catch (RequestException e) {
					GWT.log("Exception", e);
				}
			} else {
				ModuleInterfaceAsync service = null;
				try {
					service = ((ModuleInterfaceAsync) Util
							.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
				} catch (Exception e) {
				}
				final Integer i = new Integer(values[iter]);
				service.ModuleToTextMethod(module, i,
						new AsyncCallback<String>() {
							public void onSuccess(String textual) {
								addValue(textual, i);
							}

							public void onFailure(Throwable t) {

							}
						});
			}
		}
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public String getStoredValue() {
		return getCommaSeparatedValues();
	}

	public void setFromHash(HashMap<String, String> data) {
		try {
			List<Integer> a = new ArrayList<Integer>();
			String[] sValues = data.get(hashMapping).split(",");
			Iterator<String> iter = Arrays.asList(sValues).iterator();
			while (iter.hasNext()) {
				String n = iter.next();
				JsonUtil.debug("setFromHash (multiple choice) : found " + n);
				a.add(Integer.parseInt(n));
			}
			setValue(a.toArray(new Integer[0]));
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
	}

}
