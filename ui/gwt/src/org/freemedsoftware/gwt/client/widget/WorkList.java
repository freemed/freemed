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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.SchedulerScreen;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Anchor;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class WorkList extends WidgetInterface {

	protected CustomTable workListTable = null;

	protected Label providerLabel = null;

	protected Integer providerId = null;

	protected Label message = null;
	private PushButton refreshButton; 
	public WorkList() {
		SimplePanel sPanel = new SimplePanel();
		VerticalPanel vPanel = new VerticalPanel();

		sPanel.setWidget(vPanel);
		sPanel.addStyleName("freemed-WorkListContainer");
		initWidget(sPanel);

		providerLabel = new Label("Refresh to get latest schedules!");
		workListTable = new CustomTable();

		HorizontalPanel hPaneltop = new HorizontalPanel();
		

//		PushButton refreshButton = new PushButton();
//		refreshButton.setStyleName("gwt-simple-button");
//		refreshButton.getUpFace().setImage(
//				new Image("resources/images/summary_modify.16x16.png"));
//		refreshButton.getDownFace().setImage(
//				new Image("resources/images/summary_modify.16x16.png"));
//		refreshButton.addClickHandler(new ClickHandler() {
//			@Override
//			public void onClick(ClickEvent evt) {
//				retrieveData();
//				}
//		});

//		hPaneltop.add(refreshButton);
		hPaneltop.add(providerLabel);
		vPanel.add(hPaneltop);
		
		message = new Label();
		message.setStylePrimaryName("freemed-MessageText");
		message.setText("There are no items scheduled for this day.");
		vPanel.add(message);
		message.setVisible(false);
		//retrieveData();
		vPanel.add(workListTable);

		workListTable.setSize("100%", "100%");
		workListTable.addColumn("Patient", "patient_name");
		workListTable.addColumn("DD/MM", "date");
		workListTable.addColumn("Time", "time");
		workListTable.addColumn("Description", "note");
		workListTable.setVisible(true);


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
						a.addClickHandler(new ClickHandler() {
							@Override
							public void onClick(ClickEvent evt) {
								PatientScreen p = new PatientScreen();
								p.setPatient(Integer.parseInt(data
										.get("patient")));
								Util.spawnTab(data.get("patient_name"), p);
							}
						});
						return a;
					}
				});
		workListTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col > 0) {
						// TODO: Open the day of this particular event and
						// select that screen.
						SchedulerScreen schedulerScreen = new SchedulerScreen();
						// schedulerScreen.getSchedulerWidget().
						Util.spawnTab("Scheduler", schedulerScreen);
					}
				} catch (Exception e) {
					JsonUtil.debug("WorkList.java: Caught exception: "
							+ e.toString());
				}
			}
		});
	}

	public Widget getDefaultIcon(){
		refreshButton = new PushButton();
		refreshButton.setStyleName("gwt-simple-button");
		refreshButton.getUpFace().setImage(
				new Image("resources/images/summary_modify.16x16.png"));
		refreshButton.getDownFace().setImage(
				new Image("resources/images/summary_modify.16x16.png"));
		refreshButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				retrieveData();
				}
		});
		return refreshButton;
	}
	
	public void setProvider(Integer pId) {
		providerId = pId;
		retrieveData();
	}

	public void retrieveData() {
		if (providerId != null && providerId != 0) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// Runs in STUBBED MODE => Feed with Sample Data
				HashMap<String, String>[] sampleData = getSampleData();
				populateWorkList(sampleData);
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
							CurrentState.getToaster().addItem("WorkList",
									"Failed to get work list.",
									Toaster.TOASTER_ERROR);
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								try {
									if (response.getText().compareToIgnoreCase(
											"null") != 0
											&& response.getText().compareTo(
													"[[]]") != 0
											&& response.getText()
													.compareToIgnoreCase(
															"false") != 0) {
										HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
												.shoehornJson(JSONParser
														.parse(response
																.getText()),
														"HashMap<String,String>[]");
										if (r != null) {
											if (r.length > 0) {
												providerLabel.setVisible(false);
												workListTable.setVisible(true);
												populateWorkList(r);
											}
										}
									} else {
										message.setVisible(true);
									}
								} catch (Exception ex) {
									JsonUtil.debug(ex.toString());
								}
							} else {
								CurrentState.getToaster().addItem("WorkLists",
										"Failed to get work list.",
										Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					CurrentState.getToaster().addItem("WorkLists",
							"Failed to get work list.", Toaster.TOASTER_ERROR);
				}
			} else {
				// GWT-RPC
			}
		}else{
			workListTable.setVisible(false);
			providerLabel.setVisible(true);
			providerLabel.setText("Provider not available!");
		}
	}

	@SuppressWarnings("unchecked")
	protected HashMap<String, String>[] getSampleData() {
		List<HashMap<String, String>> m = new ArrayList<HashMap<String, String>>();

		workListTable.addColumn("Patient", "patient_name");
		workListTable.addColumn("DD/MM", "date");
		workListTable.addColumn("Time", "time");
		workListTable.addColumn("Description", "note");
		
		HashMap<String, String> a = new HashMap<String, String>();
		a.put("id", "1");
		a.put("patient_name", "abc");
		a.put("date", "2009-11-01");
		a.put("time", "7:30pm");
		a.put("note", "Test description1.");
		m.add(a);

		HashMap<String, String> b = new HashMap<String, String>();
		b.put("id", "2");
		a.put("patient_name", "def");
		a.put("date", "2009-11-06");
		a.put("time", "11:30am");
		a.put("note", "Test description2.");
		m.add(b);

		return (HashMap<String, String>[]) m.toArray(new HashMap<?, ?>[0]);
	}
	
	protected void populateWorkList(HashMap<String, String>[] data) {
		boolean empty = false;
		if (data != null) {
			if (data.length == 0) {
				empty = true;
			}
			workListTable.loadData(data);
			CurrentState.getToaster().addItem("WorkList",
					"Successfully updated worklist items.",
					Toaster.TOASTER_INFO);
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
