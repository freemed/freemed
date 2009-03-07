/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;

public class PrescriptionRefillBox extends WidgetInterface {
	protected Integer patid = 0;

	public PrescriptionRefillBox() {

		final SimplePanel simplePanel = new SimplePanel();
		simplePanel
				.setStyleName("freemed-WidgetContainer");
		//simplePanel.addStyleName("freemed-PrescriptionRefillBoxContainer");
		initWidget(simplePanel);

		final FlexTable flexTable = new FlexTable();
		simplePanel.setWidget(flexTable);
		flexTable.setSize("100%", "100%");

		final PatientWidget patientWidget = new PatientWidget();
		flexTable.setWidget(0, 1, patientWidget);

		final Label selectionLabel = new Label("Select a Patient:");
		flexTable.setWidget(0, 0, selectionLabel);

		final Label textLabel = new Label("Add an optional note:");
		flexTable.setWidget(1, 0, textLabel);

		final TextBox textBox = new TextBox();
		flexTable.setWidget(1, 1, textBox);
		textBox.setWidth("100%");

		final Button sendButton = new Button();
		flexTable.setWidget(2, 1, sendButton);
		sendButton.setText("Send Request");
		sendButton.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				patid = patientWidget.value;

				HashMap<String, String> data = new HashMap<String, String>();
				// data.put("id", "some stuff"); not needed??
				data.put("provider", Integer.toString(state
						.getDefaultProvider()));
				data.put("note", textBox.getText());
				data.put("patient", Integer.toString(patid));
				// send stuff
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// do nothing - we just save the stuff
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { JsonUtil.jsonify(data) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.RxRefillRequest.add",
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								state.getToaster().addItem(
										"PrescriptionRefillBox",
										"Error adding refill request."
												+ ex.toString(),
										Toaster.TOASTER_ERROR);
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Integer");
									if (r != 0) {
										state
												.getToaster()
												.addItem(
														"PrescriptionRefillBox",
														"Prescription refill successfully saved.",
														Toaster.TOASTER_INFO);
									}
								} else {
									state.getToaster().addItem(
											"PrescriptionRefillBox",
											"Error adding prescription refill",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
					}

				} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
					// TODO: GWT-RPC still missing
				}

			}

		});

	}

}
