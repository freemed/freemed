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
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Module.MedicationsAsync;
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

public class RecentAllergiesList extends WidgetInterface {

	protected Integer patientId = new Integer(0);

	protected CustomTable allergiesTable;

	public RecentAllergiesList() {
		allergiesTable = new CustomTable();
		allergiesTable.setWidth("100%");
		initWidget(allergiesTable);
		allergiesTable.addColumn("Allergy", "allergy");
		allergiesTable.addColumn("Reaction", "reaction");
		allergiesTable.addColumn("Severity", "severity");
	}

	public void setPatientId(Integer id) {
		patientId = id;
		populate();
	}

	@SuppressWarnings("unchecked")
	protected void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			allergiesTable.clearData();
			List<HashMap<String, String>> results = new ArrayList<HashMap<String, String>>();
			{
				HashMap<String, String> item1 = new HashMap<String, String>();
				item1.put("allergy", "Penicillin");
				item1.put("reaction", "Swelling");
				item1.put("severity", "Moderate");
				results.add(item1);
			}
			{
				HashMap<String, String> item2 = new HashMap<String, String>();
				item2.put("allergy", "Bee Stings");
				item2.put("reaction", "Swelling");
				item2.put("severity", "Severe");
				results.add(item2);
			}
			{
				HashMap<String, String> item3 = new HashMap<String, String>();
				item3.put("allergy", "Avocado");
				item3.put("reaction", "Hives");
				item3.put("severity", "Moderate");
				results.add(item3);
			}
			allergiesTable.loadData(results
					.toArray((HashMap<String, String>[]) new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(patientId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Allergies.GetMostRecent",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								allergiesTable.clearData();
								try {
									allergiesTable.loadData(r);
								} catch (Exception e) {
									GWT.log("Exception", e);
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			getProxy().GetMostRecent(patientId,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] m) {
							allergiesTable.clearData();
							try {
								allergiesTable.loadData(m);
							} catch (Exception e) {
								GWT.log("Exception", e);
							}
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Internal method to retrieve proxy object from Util.getProxy()
	 * 
	 * @return
	 */
	protected MedicationsAsync getProxy() {
		MedicationsAsync p = null;
		try {
			p = (MedicationsAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Module.Allergies");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		return p;
	}

}
