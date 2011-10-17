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

import java.util.HashMap;
import java.util.Iterator;
import java.util.Set;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.WidgetInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.VerticalPanel;

public class CustomMulltiSelectListBox extends WidgetInterface implements
		HashSetter {
	protected String hashMapping;
	protected CustomListBox multiLBox;
	protected String module = "";
	protected VerticalPanel container = null;
	protected String selVal;

	public CustomMulltiSelectListBox(String mod, boolean isMulti) {
		module = mod;
		init(isMulti);
	}

	private void init(boolean isMulti) {
		final VerticalPanel v = new VerticalPanel();
		initWidget(v);

		container = new VerticalPanel();
		v.add(container);

		// Add picklist for this ...
		if (isMulti)
			multiLBox = new CustomListBox(true);
		else
			multiLBox = new CustomListBox(false);
		v.add(multiLBox);
		populateMultiList("");
	}

	public void populateMultiList(String sval) {
		selVal = sval;
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { module };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL.encode(Util
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
										.shoehornJson(
												JSONParser.parseStrict(response
														.getText()),
												"HashMap<String,String>");
								if (result != null) {
									multiLBox.clear();
									Set<String> keys = result.keySet();
									Iterator<String> iter = keys.iterator();

									while (iter.hasNext()) {

										final String key = (String) iter.next();
										final String val = (String) result
												.get(key);
										JsonUtil.debug(val);
										multiLBox.addItem(val, key);
										// addKeyValuePair(items, val, key);
									}
									if (!(selVal == null || selVal.equals("")))
										setValue();
									// cb.onSuggestionsReady(r,
									// new SuggestOracle.Response(items));
								} else {
								} // if no result then set value to 0
									// setValue(0);
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

		}
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public String getWidgetValue() {
		int cnt = multiLBox.getItemCount();
		String items = "";
		for (int i = 0; i < cnt; i++) {
			if (multiLBox.isItemSelected(i)) {
				if (items.equals(""))
					items = multiLBox.getValue(i);
				else
					items += "," + multiLBox.getValue(i);
			}
		}
		JsonUtil.debug("Selected items are:" + items);
		return items;
	}

	public void setFromHash(HashMap<String, String> data) {

	}

	public String getStoredValue() {
		return "";
	}

	public void setValue() {

		String array[] = selVal.split(",");
		// Window.alert("cnt:"+array.length);

		int selCnt = array.length;

		for (int j = 0; j < selCnt; j++) {
			int cnt = multiLBox.getItemCount();
			// Window.alert(""+cnt);

			for (int i = 0; i < cnt; i++) {
				if (array[j].equals(multiLBox.getValue(i))) {
					multiLBox.setItemSelected(i, true);
					// multiLBox.setSelectedIndex(i);
				}

			}
		}
	}

}
