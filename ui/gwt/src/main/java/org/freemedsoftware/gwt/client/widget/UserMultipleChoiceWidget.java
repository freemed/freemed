/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2011 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.UserInterfaceAsync;

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
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class UserMultipleChoiceWidget extends Composite {

	protected UserWidget userWidget = null;

	protected String module = null;

	protected VerticalPanel container = null;

	protected Integer[] widgetValues;

	public UserMultipleChoiceWidget() {
		final VerticalPanel v = new VerticalPanel();
		container = new VerticalPanel();
		v.add(container);
		initWidget(v);

		// Add picklist for this ...
		userWidget = new UserWidget();
		v.add(userWidget);
		userWidget.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				UserWidget sender = (UserWidget) event.getSource();
				Integer val = sender.getValue();
				String label = sender.getText();
				if (val == null) {
					return;
				}
				if (val == 0) {
					return;
				}
				JsonUtil.debug("value = " + val.toString() + ", label = "
						+ label);
				addValue(label, val);
				sender.clear();
			}
		});
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
		Button removeButton = new Button("<sup>X</sup>");
		removeButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				Widget w = (Widget) evt.getSource();
				// Remove the HorizontalPanel object from its parent container,
				// "container"
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
	public void clearValues() {
		try {
			container.clear();
		} catch (Exception ex) {

		}
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
	
	public void setFocus(){
		userWidget.setFocus(true);
	}

	/**
	 * Get all integer values squished into a comma separated string.
	 * 
	 * @return
	 */
	public String getCommaSeparatedValues() {
		String buffer = new String("");
		try{
			Iterator<Integer> iter = Arrays.asList(widgetValues).iterator();
			while (iter.hasNext()) {
				buffer += iter.next();
				if (iter.hasNext()) {
					buffer += ",";
				}
			}
		}catch(Exception e){JsonUtil.debug("UserMultipleChoiceWidget: list empty");}
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
				String[] params = {i.toString()};
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.UserInterface.GetRecord",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(
								com.google.gwt.http.client.Request request,
								Throwable ex) {
							GWT.log("Exception", ex);
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>");
								if (result != null) {
									addValue(result.get("userdescrip"), i);
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
				UserInterfaceAsync service = null;
				try {
					service = ((UserInterfaceAsync) Util
							.getProxy("org.freemedsoftware.gwt.client.Api.UserInterface"));
				} catch (Exception e) {
				}
				final Integer i = new Integer(values[iter]);
				service.GetRecord(i,
						new AsyncCallback<HashMap<String, String>>() {
							public void onSuccess(HashMap<String, String> rec) {
								addValue(rec.get("userdescrip"), i);
							}

							public void onFailure(Throwable t) {

							}
						});
			}
		}
	}

}
