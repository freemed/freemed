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

package org.freemedsoftware.gwt.client;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.TabPanel;

public abstract class PatientScreenInterface extends ScreenInterface {

	protected Integer patientId = new Integer(0);

	protected Integer internalId = new Integer(0);

	protected String moduleName;

	protected PatientScreen patientScreen = null;

	protected CurrentState state = null;

	/**
	 * Pass current state object.
	 */
	public void assignState(CurrentState s) {
		setState(s);
	}

	/**
	 * Pass current patient screen.
	 * 
	 * @param p
	 */
	public void assignPatientScreen(PatientScreen p) {
		patientScreen = p;
	}

	/**
	 * Close this screen by removing it from the tab panel.
	 */
	public void closeScreen() {
		TabPanel t = patientScreen.getTabPanel();
		t.selectTab(t.getWidgetIndex(this) - 1);
		t.remove(t.getWidgetIndex(this));
	}

	/**
	 * Function to return static module name.
	 * 
	 * @return
	 */
	public String getModuleName() {
		return "";
	}

	/**
	 * Set patient id stored in this object.
	 * 
	 * @param id
	 */
	public void setPatientId(Integer id) {
		patientId = id;
	}

	/**
	 * Set internal record id, if applicable, and fire off data load.
	 * 
	 * @param id
	 */
	public void setInternalId(Integer id) {
		internalId = id;
		loadData();
	}

	/**
	 * Internal method to load stock data into form.
	 */
	protected void loadData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// STUB
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { getModuleName(), internalId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleGetRecordMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						state.getToaster().addItem(moduleName,
								"Failed to load data.", Toaster.TOASTER_ERROR);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						JsonUtil.debug("onResponseReceived");
						if (200 == response.getStatusCode()) {
							JsonUtil.debug(response.getText());
							HashMap<String, String> r = (HashMap<String, String>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>");
							if (r != null) {
								populateData(r);
							}
						} else {
							state.getToaster().addItem(moduleName,
									"Failed to load data.",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			ModuleInterfaceAsync service = getProxy();
			service.ModuleGetRecordMethod(moduleName, internalId,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> r) {
							populateData(r);
						}

						public void onFailure(Throwable t) {
							state.getToaster().addItem(moduleName,
									"Failed to load data.",
									Toaster.TOASTER_ERROR);
						}
					});
		}
	}

	protected void populateData(HashMap<String, String> r) {
	}

	/**
	 * Load the module interface RPC proxy.
	 * 
	 * @return
	 */
	public ModuleInterfaceAsync getProxy() {
		try {
			ModuleInterfaceAsync service = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
			return service;
		} catch (Exception e) {
			GWT.log("Exception: ", e);
			return (ModuleInterfaceAsync) null;
		}
	}

}
