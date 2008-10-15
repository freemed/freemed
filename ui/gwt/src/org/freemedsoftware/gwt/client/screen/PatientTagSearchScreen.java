/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.PatientTagAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.TextBox;

public class PatientTagSearchScreen extends ScreenInterface {

	private TextBox tagWidget = null;

	public PatientTagSearchScreen() {
		tagWidget = new TextBox();
	}

	/**
	 * Populate the screen with data.
	 * 
	 * @param data
	 */
	protected void populate(HashMap<String, String>[] data) {
		// TODO: finish this
	}

	/**
	 * Set value of the tag widget which is being used for the search.
	 * 
	 * @param tagValue
	 *            Textual tag name.
	 */
	public void setTagValue(String tagValue) {
		tagWidget.setText(tagValue);
	}

	/**
	 * Perform tag search and pass population data on.
	 * 
	 * @param t
	 *            Textual value of tag being searched.
	 */
	@SuppressWarnings("unchecked")
	public void searchForTag(String t) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> results = new ArrayList<HashMap<String, String>>();
			populate((HashMap<String, String>[]) results
					.toArray(new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { t, JsonUtil.jsonify(Boolean.FALSE) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientTag.SimpleTagSearch",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log("Exception", ex);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								populate(r);
							}
						} else {
							GWT.log("Exception", null);
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			PatientTagAsync proxy = null;
			try {
				proxy = (PatientTagAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.PatientTag");
			} catch (Exception ex) {
				GWT.log("Exception", ex);
			}
			proxy.SimpleTagSearch(t, Boolean.FALSE,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] data) {
							populate(data);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

}
