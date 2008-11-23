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

package org.freemedsoftware.gwt.client;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.SimpleUIBuilder;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.rpc.AsyncCallback;

public abstract class EntryScreenInterface extends ScreenInterface implements
		SimpleUIBuilder.Receiver {

	protected String moduleName;

	protected Command doneCommand = null;

	protected Integer internalId = 0;

	protected SimpleUIBuilder ui = new SimpleUIBuilder();

	public EntryScreenInterface() {
		ui.setReceiver(this);
		buildForm();
	}

	protected abstract void buildForm();

	/**
	 * Override to set the module name used.
	 * 
	 * @return The FreeMED module class name implemented by this class.
	 */
	protected abstract String getModuleName();

	/**
	 * Set command run when action is completed for this screen.
	 * 
	 * @param done
	 */
	public void setDoneCommand(Command done) {
		doneCommand = done;
	}

	/**
	 * Set the internal id associated with this form.
	 * 
	 * @param id
	 */
	public void setInternalId(Integer id) {
		ModuleInterfaceAsync service = null;
		try {
			service = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		} finally {
			internalId = id;
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO: Emulate stubbed mode
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { moduleName, JsonUtil.jsonify(id) };
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
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									ui.setValues(r);
								}
							} else {
							}
						}
					});
				} catch (RequestException e) {
				}
			} else {
				service.ModuleGetRecordMethod(moduleName, id,
						new AsyncCallback<HashMap<String, String>>() {
							public void onSuccess(HashMap<String, String> r) {
								ui.setValues(r);
							}

							public void onFailure(Throwable t) {
								GWT.log("Exception", t);
							}
						});
			}
		}
	}

	public void processData(HashMap<String, String> data) {
		if (internalId.intValue() > 0) {
			JsonUtil.debug("Found internalId, using modify");
			data.put("id", internalId.toString());
		}
		ModuleInterfaceAsync service = null;
		try {
			service = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		} finally {
			if (internalId.intValue() == 0) {
				// Add record
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					state.getToaster().addItem(moduleName,
							"Added successfully.", Toaster.TOASTER_INFO);
					if (doneCommand != null) {
						doneCommand.execute();
					}
					closeScreen();
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { moduleName, JsonUtil.jsonify(data) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.api.ModuleInterface.ModuleAddMethod",
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								state.getToaster().addItem(moduleName,
										"Failed to add record.",
										Toaster.TOASTER_ERROR);
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Integer");
									if (r != null) {
										state.getToaster().addItem(moduleName,
												"Added successfully.",
												Toaster.TOASTER_INFO);
										if (doneCommand != null) {
											doneCommand.execute();
										}
										closeScreen();
									}
								} else {
									state.getToaster().addItem(moduleName,
											"Failed to add record.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						state.getToaster().addItem(moduleName,
								"Failed to add record.", Toaster.TOASTER_ERROR);
					}
				} else {
					service.ModuleAddMethod(moduleName, data,
							new AsyncCallback<Integer>() {
								public void onSuccess(Integer r) {
									state.getToaster().addItem(moduleName,
											"Added successfully.",
											Toaster.TOASTER_INFO);
									if (doneCommand != null) {
										doneCommand.execute();
									}
									closeScreen();
								}

								public void onFailure(Throwable t) {
									GWT.log("Exception", t);
								}
							});
				}
			} else {
				// Modify record
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					state.getToaster().addItem(moduleName,
							"Modified successfully.", Toaster.TOASTER_INFO);
					closeScreen();
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { moduleName, JsonUtil.jsonify(data) };
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
								state.getToaster().addItem(moduleName,
										"Failed to modify record.",
										Toaster.TOASTER_ERROR);
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Boolean r = (Boolean) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Boolean");
									if (r != false && r != null) {
										state.getToaster().addItem(moduleName,
												"Modified successfully.",
												Toaster.TOASTER_INFO);
										if (doneCommand != null) {
											doneCommand.execute();
										}
										closeScreen();
									} else {
										state.getToaster().addItem(moduleName,
												"Failed to modify record.",
												Toaster.TOASTER_ERROR);
									}
								} else {
									state.getToaster().addItem(moduleName,
											"Failed to modify record.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
					}
				} else {
					service.ModuleModifyMethod(moduleName, data,
							new AsyncCallback<Integer>() {
								public void onSuccess(Integer r) {
									state.getToaster().addItem(moduleName,
											"Modified successfully.",
											Toaster.TOASTER_INFO);
									if (doneCommand != null) {
										doneCommand.execute();
									}
									closeScreen();
								}

								public void onFailure(Throwable t) {
									GWT.log("Exception", t);
								}
							});
				}
			}
		}

	}

}
