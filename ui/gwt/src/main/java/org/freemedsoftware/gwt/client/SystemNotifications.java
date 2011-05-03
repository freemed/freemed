/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Timer;

public class SystemNotifications {

	protected boolean mutexStatus = false;

	protected long mutexTimestamp = 0;

	protected Timer timer = null;

	/**
	 * Interval between polls to backend, specified in seconds.
	 */
	protected final int POLL_INTERVAL = 30;

	public SystemNotifications() {

	}

	/**
	 * Start system notification polling.
	 */
	public void start() {
		JsonUtil.debug("SystemNotifications.start() called");
		timer = new Timer() {
			public void run() {
				poll();
			}
		};
		// Run initial polling ...
		timer.run();

		// ... and force it to go in the future.
		timer.scheduleRepeating(POLL_INTERVAL * 1000);
	}

	/**
	 * Stop system notification polling.
	 */
	public void stop() {
		JsonUtil.debug("SystemNotifications.stop() called");
		if (timer != null) {
			timer.cancel();
			timer = null;
		}
	}

	/**
	 * Asynchronously poll for new system notifications
	 */
	public boolean poll() {
		JsonUtil.debug("SystemNotifications.poll() called");

		// if (mutexStatus) {
		// JsonUtil.debug("mutexStatus indicates run in progress.");
		// return false;
		// }

		mutexStatus = true;
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Nothing. Do nothing.
			mutexStatus = false;
			return true;
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String[] params = { Long.toString(mutexTimestamp) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.SystemNotifications.GetFromTimestamp",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("SystemNotifications",
								"Failed to get system notifications.");
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							if (response.getText().compareToIgnoreCase("false") != 0) {
								if (response.getText().trim().equalsIgnoreCase(
										AppConstants.INVALID_SESSION)) {
									Util.logout(new Command() {

										@Override
										public void execute() {
											stop();
										}

									});
									mutexStatus = false;
									return;
								}
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {
									if (r.length > 0) {
										// Update our working timestamp
										try {
											mutexTimestamp = Long
													.parseLong(r[0]
															.get("timestamp"));
											JsonUtil
													.debug("SystemNotifications(): Received timestamp "
															+ r[0]
																	.get("timestamp"));
											JsonUtil
													.debug("SystemNotifications(): Parsed timestamp as "
															+ mutexTimestamp);
										} catch (Exception ex) {
											JsonUtil
													.debug("SystemNotifications(): Exception parsing mutex timestamp"
															+ r[0]
																	.get("timestamp"));
										}
										for (int iter = 0; iter < r.length; iter++) {
											handleNotification(r[iter]);
										}
									}
								}
							} else {
								JsonUtil
										.debug("SystemNotifications(): Received dummy response from JSON backend");
							}
						} else {
							Util.showErrorMsg("SystemNotifications",
									"Failed to get system notifications.");
						}

						// Release mutex
						mutexStatus = false;
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("SystemNotifications",
						"Failed to get system notifications.");
			}
		} else {
			// GWT-RPC
		}

		return true;
	}

	/**
	 * Perform action for system notification record.
	 * 
	 * @param event
	 *            HashMap of event information.
	 */
	protected void handleNotification(HashMap<String, String> event) {
		try {
			JsonUtil.debug("SystemNotifications(): Firing event for "
					+ JsonUtil.jsonify(event));
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
		try {
			SystemEvent e = new SystemEvent();
			e.setSourceModule(event.get("nmodule"));
			e.setAction(event.get("naction"));
			e.setText(event.get("ntext"));
			try {
				e.setPatient(Integer.parseInt(event.get("npatient")));
			} catch (NumberFormatException nfe) {
			}
			CurrentState.getEventBus().fireEvent(e);
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
	}

}
