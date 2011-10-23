/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng <pmeng@freemedsoftware.org>
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.HashMap;
import java.util.Iterator;
import java.util.Set;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.WidgetInterface;

import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SupportModuleListBox extends WidgetInterface implements HashSetter {

	protected String moduleName = null;

	protected String hashMapping = null;

	protected VerticalPanel layout;

	protected ListBox listBox;

	protected String selectText = _("Select an Item");

	protected Command command = null;
	
	protected String holdWidgetValue=null;

	public SupportModuleListBox(String s) {
		moduleName = s;
		buildForm();
	}

	public SupportModuleListBox(String s, String text) {
		selectText = text;
		moduleName = s;
		buildForm();
	}

	public void buildForm() {
		layout = new VerticalPanel();
		listBox = new ListBox();
		listBox.addItem(selectText);
		layout.add(listBox);
		initWidget(layout);
		loadValues();
	}

	public void loadValues() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			listBox.addItem("Item 1", "i1");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			listBox.setEnabled(false);
			String[] params = { moduleName, "" };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleSupportPicklistMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						JsonUtil
								.debug("SupportModuleListBox: Error retrieving Data");
						listBox.setEnabled(false);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>");

								if (result != null) {
									Set<String> keys = result.keySet();
									Iterator<String> iter = keys.iterator();
									while (iter.hasNext()) {
										final String key = (String) iter.next();
										final String val = (String) result
												.get(key);
										listBox.addItem(val, key);
									}

								}
								listBox.setEnabled(true);
								if(holdWidgetValue!=null)
									setWidgetValue(holdWidgetValue);
							} else {
								Window.alert(response.toString());
							}
						}
					}
				});
			} catch (RequestException e) {
				listBox.setEnabled(true);
				JsonUtil
						.debug("Exception in SupportModuleListBox.loadValues; Message:"
								+ e.getMessage());
			}
		}
	}

	public String getSelectedText() {
		Integer index = listBox.getSelectedIndex();
		if (index > 1) {
			String s = listBox.getItemText(index);
			return s;
		}
		return null;
	}

	/**
	 * This method gives the Value of the Selected Item in the Listbox.
	 * 
	 * @return The String Value, null if there was an error.
	 */
	public String getSelectedValue() {
		Integer index = listBox.getSelectedIndex();
		if (index > 1) {
			String s = listBox.getValue(index);
			return s;
		}
		return null;
	}

	public void initChangeListener(Command c) {
		command = c;
		listBox.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent evt) {
				command.execute();
			}
		});
	}

	public String getStoredValue() {
		return getWidgetValue();
	}

	/**
	 * Determine the string value.
	 * 
	 * @return
	 */
	public String getWidgetValue() {
		try {
			return listBox.getValue(listBox.getSelectedIndex());
		} catch (Exception e) {
			return new String("");
		}
	}

	/**
	 * Set the active value of the ListBox widget to be val.
	 * 
	 * @param val
	 */
	public void setWidgetValue(String val) {
		holdWidgetValue = val;
		if (listBox.getItemCount() > 0) {
			for (int iter = 0; iter < listBox.getItemCount(); iter++) {
				if (listBox.getValue(iter).compareTo(val) == 0) {
					listBox.setItemSelected(iter, true);
					holdWidgetValue = null;
				}
			}
		}
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		setWidgetValue(data.get(hashMapping));
	}

}
