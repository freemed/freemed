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

package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable.TableWidgetColumnSetInterface;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.Anchor;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class WorkList extends Composite {

	protected CurrentState state = null;

	protected CustomSortableTable workListTable = null;

	protected Label providerLabel = null;

	protected Integer providerId = null;

	protected Label message = null;

	public WorkList() {
		SimplePanel sPanel = new SimplePanel();
		VerticalPanel vPanel = new VerticalPanel();

		sPanel.setWidget(vPanel);
		sPanel.addStyleName("freemed-WorkListContainer");
		initWidget(sPanel);

		providerLabel = new Label("");
		workListTable = new CustomSortableTable();

		vPanel.add(providerLabel);
		vPanel.add(workListTable);

		workListTable.setSize("100%", "100%");
		workListTable.addColumn("Patient", "patient_name");
		workListTable.addColumn("Time", "time");
		workListTable.addColumn("Description", "note");

		message = new Label();
		message.setStylePrimaryName("freemed-MessageText");
		message.setText("There are no items scheduled for this day.");
		vPanel.add(message);
		message.setVisible(false);

		workListTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {
						if (!columnName.equalsIgnoreCase("patient_name")) {
							// Skip renderer
							return null;
						}
						Anchor a = new Anchor();
						a.setTitle("View EMR for " + data.get("patient_name"));
						a.setText(data.get("patient_name"));
						a.addClickListener(new ClickListener() {
							public void onClick(Widget sender) {
								PatientScreen p = new PatientScreen();
								p.setPatient(Integer.parseInt(data
										.get("patient")));
								Util.spawnTab(data.get("patient_name"), p,
										state);
							}
						});
						return a;
					}
				});
	}

	public void setProvider(Integer pId) {
		providerId = pId;
		retrieveData();
	}

	public void setCurrentState(CurrentState s) {
		state = s;
	}

	protected void retrieveData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Nothing. Do nothing.
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String[] params = { providerId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.WorkListsModule.GenerateWorkList",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						state.getToaster().addItem("WorkList",
								"Failed to get work list.",
								Toaster.TOASTER_ERROR);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							JsonUtil.debug(response.getText());
							if (response.getText().compareToIgnoreCase("null") != 0
									&& response.getText().compareToIgnoreCase(
											"false") != 0) {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {
									if (r.length > 0) {
										populateWorkList(r);
									}
								}
							} else {
								message.setVisible(true);
							}
						} else {
							state.getToaster().addItem("WorkLists",
									"Failed to get work list.",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				state.getToaster().addItem("WorkLists",
						"Failed to get work list.", Toaster.TOASTER_ERROR);
			}
		} else {
			// GWT-RPC
		}
	}

	protected void populateWorkList(HashMap<String, String>[] data) {
		boolean empty = false;
		if (data != null) {
			if (data.length == 0) {
				empty = true;
			}
			workListTable.loadData(data);
		} else {
			empty = true;
		}

		if (empty) {
			message.setVisible(true);
		} else {
			message.setVisible(false);
		}
	}

}
