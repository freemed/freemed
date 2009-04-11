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
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class SupportModuleMultipleChoiceWidget extends WidgetInterface
		implements HashSetter {

	protected SupportModuleWidget supportModuleWidget = null;

	protected String module = null;

	protected VerticalPanel container = null;

	protected Integer[] widgetValues;

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
	}

	protected void pushWidgetValue(Integer val) {
		List<Integer> a = new ArrayList<Integer>();
		if (widgetValues != null) {
			for (int iter = 0; iter < widgetValues.length; iter++) {
				a.add(widgetValues[iter]);
			}
		}
		a.add(val);
		widgetValues = a.toArray(new Integer[0]);
	}

	protected void addValue(String text, final Integer value) {
		// Push into internal store of values
		pushWidgetValue(value);

		// Create new container, push in
		final HorizontalPanel hp = new HorizontalPanel();
		hp.add(new Label(text));
		Button removeButton = new Button();
		removeButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				// Remove the hp object from its parent container, "container"
				w.getParent().removeFromParent();

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
		widgetValues = null;
	}

	/**
	 * Get integer values associated with this widget.
	 * 
	 * @return
	 */
	public Integer[] getValues() {
		return widgetValues;
	}

	/**
	 * Get all integer values squished into a comma separated string.
	 * 
	 * @return
	 */
	public String getCommaSeparatedValues() {
		String buffer = new String("");
		Iterator<Integer> iter = Arrays.asList(widgetValues).iterator();
		while (iter.hasNext()) {
			buffer += iter.next();
			if (iter.hasNext()) {
				buffer += ",";
			}
		}
		return buffer.toString();
	}

	/**
	 * Assign all integer values from a squished comma separated string.
	 * 
	 * @param v
	 * @return
	 */
	public void setCommaSeparatedValues(String v) {
		if (v != null) {
			String[] s = v.split(",");
			List<Integer> i = new ArrayList<Integer>();
			for (int iter = 0; iter < s.length; iter++) {
				i.add(new Integer(s[iter]));
			}
			setValue(i.toArray(new Integer[0]));
		}
	}

	/**
	 * Remove value from the internal widget data store
	 * 
	 * @param value
	 */
	protected void removeValueFromStore(Integer value) {
		Integer[] temp = {};
		for (int iter = 0; iter < widgetValues.length; iter++) {
			if (widgetValues[iter].compareTo(value) != 0) {
				temp[temp.length] = widgetValues[iter];
			}
		}
		widgetValues = temp;
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
				String[] params = {};
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
			ArrayList<Integer> a = new ArrayList<Integer>();
			String[] sValues = data.get(hashMapping).split(",");
			Iterator<String> iter = Arrays.asList(sValues).iterator();
			while (iter.hasNext()) {
				a.add(Integer.parseInt(iter.next()));
			}
			setValue(a.toArray(new Integer[0]));
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
	}

}
