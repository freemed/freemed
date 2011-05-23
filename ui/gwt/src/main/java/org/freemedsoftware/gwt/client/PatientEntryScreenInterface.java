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

package org.freemedsoftware.gwt.client;

import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

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

public class PatientEntryScreenInterface extends PatientScreenInterface {

	/**
	 * Internal id representing this record. If this is 0, we create a new one,
	 * otherwise we modify.
	 */
	protected Integer internalId = new Integer(0);

	protected HashMap<String, HashSetter> setters = new HashMap<String, HashSetter>();

	protected String patientIdName = "patient";

	protected String moduleName;

	public PatientEntryScreenInterface() {
		super();
	}

	public PatientEntryScreenInterface(String moduleName) {
		super(moduleName);
	}

	/**
	 * Add widget to list of HashMap'd data points represented by this form.
	 * 
	 * @param mapping
	 * @param widget
	 */
	public void addEntryWidget(String mapping, HashSetter widget) {
		setters.put(mapping, widget);
	}

	/**
	 * Public function to set all values properly from a hash.
	 */
	public void populateData(HashMap<String, String> r) {
		Iterator<HashSetter> iter = setters.values().iterator();
		while (iter.hasNext()) {
			iter.next().setFromHash(r);
		}
	}

	public void submitForm() {
		ModuleInterfaceAsync service = getProxy();
		// Form hashmap ...
		final HashMap<String, String> rec = new HashMap<String, String>();
		Iterator<String> iter = setters.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			JsonUtil.debug("grabbing key " + k + " from setters");
			rec.put(k, setters.get(k).getStoredValue());
		}

		// Set patient ID
		rec.put(patientIdName, patientId.toString());

		// Debug
		JsonUtil
				.debug("PatientEntryScreenInterface.submitForm() called with : "
						+ JsonUtil.jsonify(rec));

		if (!internalId.equals(new Integer(0))) {
			// Modify
			JsonUtil
					.debug("PatientEntryScreenInterface.submitForm() attempting modify");
			rec.put("id", (String) internalId.toString());
			if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { getModuleName(), JsonUtil.jsonify(rec) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.ModuleInterface.ModuleModifyMethod",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							Util.showErrorMsg(getModuleName(), "Failed to update.");
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != null) {
									Util.showInfoMsg(getModuleName(), "Updated.");
								}
							} else {
								Util.showErrorMsg(getModuleName(),
										"Failed to update.");
							}
						}
					});
				} catch (RequestException e) {
					Util.showErrorMsg(getModuleName(), "Failed to update.");
				}
			} else { // if programmode == JSONRPC (modify)
				// Modify
				service.ModuleModifyMethod(getModuleName(), rec,
						new AsyncCallback<Integer>() {
							public void onSuccess(Integer result) {
								Util.showErrorMsg(getModuleName(),
										"Failed to update.");
							}

							public void onFailure(Throwable th) {
								Util.showErrorMsg(getModuleName(),
										"Failed to update.");
							}
						});
			}
		} else { // if this is an "add" request ...
			JsonUtil
					.debug("PatientEntryScreenInterface.submitForm() attempting add");
			if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				JsonUtil.debug("Try to build parameters");
				String[] params = { getModuleName(), JsonUtil.jsonify(rec) };
				JsonUtil.debug("Create requestbuilder for " + getModuleName() + ", " + JsonUtil.jsonify(rec) );
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.ModuleInterface.ModuleAddMethod",
												params)));
				JsonUtil.debug("Entering try statement");
				try {
					JsonUtil.debug("Sending request");
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							Util.showErrorMsg(getModuleName(), "Failed to add.");
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != null) {
									Util.showInfoMsg(getModuleName(), "Added.");
								}
							} else {
								Util.showErrorMsg(getModuleName(), "Failed to add.");
							}
						}
					});
				} catch (RequestException e) {
					Util.showErrorMsg(getModuleName(), "Failed to update.");
				}
			} else { // add clause GWT-RPC
				// Add
				service.ModuleAddMethod(getModuleName(), rec,
						new AsyncCallback<Integer>() {
							public void onSuccess(Integer result) {
								Util.showInfoMsg(getModuleName(), "Added.");
							}

							public void onFailure(Throwable th) {
								Util.showErrorMsg(getModuleName(), "Failed to Add.");
							}
						});
			} // end add cause
		} // if add/mod selector
	} // end submitForm

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
						Util.showErrorMsg(getModuleName(), "Failed to load data.");
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
							Util.showErrorMsg(getModuleName(),
									"Failed to load data.");
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			ModuleInterfaceAsync service = getProxy();
			service.ModuleGetRecordMethod(getModuleName(), internalId,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> r) {
							populateData(r);
						}

						public void onFailure(Throwable t) {
							Util.showErrorMsg(getModuleName(),
									"Failed to load data.");
						}
					});
		}
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
