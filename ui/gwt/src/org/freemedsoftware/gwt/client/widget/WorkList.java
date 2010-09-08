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

import java.util.HashMap;
import java.util.Iterator;
import java.util.Set;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.SystemEvent;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.PatientsGroupScreen;
import org.freemedsoftware.gwt.client.screen.SchedulerScreen;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Style.Cursor;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
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
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class WorkList extends WidgetInterface implements SystemEvent.Handler {

	public final static String moduleName = "WorkListsModule";

	protected Label providerLabel = null;
	protected Integer providerGroupId = null;

	protected boolean eventMutex = false;

	protected Label message = null;
	private PushButton refreshButton;
	protected HashMap<String, String> statusNamesMap;
	protected HashMap<String, String> statusColorsMap;
	protected CustomTable[] workListsTables;
	protected Label[] providersLb;
	protected Integer[] providers;
	protected VerticalPanel vPanel;
	protected VerticalPanel tablesVPanel;
	protected HorizontalPanel paneltop;

	public WorkList() {
		super(moduleName);
		VerticalPanel superVPanel = new VerticalPanel();
		superVPanel.setStyleName(AppConstants.STYLE_BUTTON_WIDGETS_CONTAINER );
		superVPanel.setWidth("100%");
		initWidget(superVPanel);

		HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setSpacing(5);
		superVPanel.add(headerHPanel);
		
		final Image colExpBtn = new Image(Util.getResourcesURL()+"collapse.15x15.png");
		colExpBtn.getElement().getStyle().setCursor(Cursor.POINTER);
		headerHPanel.add(colExpBtn);
		colExpBtn.addClickHandler(new ClickHandler() {
			boolean expaned = false;
			@Override
			public void onClick(ClickEvent arg0) {
				if(expaned){
					colExpBtn.setUrl(Util.getResourcesURL()+"collapse.15x15.png");
					vPanel.setVisible(true);
				}else{
					colExpBtn.setUrl(Util.getResourcesURL()+"expand.15x15.png");
					vPanel.setVisible(false);
				}
					expaned = !expaned;
			}
		});

		Label headerLabel = new Label("WORK LIST");
		headerHPanel.add(headerLabel);
		headerLabel.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		
		vPanel = new VerticalPanel();
		superVPanel.add(vPanel);
		vPanel.setWidth("100%");
		
		paneltop = new HorizontalPanel();

		// providerLabel = new Label("Refresh to get latest schedules!");
		providerLabel = new Label();

		// hPaneltop.add(refreshButton);
		paneltop.add(providerLabel);
		vPanel.add(paneltop);

		message = new Label();
		message.setStylePrimaryName("freemed-MessageText");
		message.setText("There are no items scheduled for this day.");
		vPanel.add(message);

		tablesVPanel = new VerticalPanel();
		vPanel.add(tablesVPanel);
		// message.setVisible(false);
		// retrieveData();

		// Register on the event bus
		CurrentState.getEventBus().addHandler(SystemEvent.TYPE, this);
	}

	private void changeStatus(Widget w1, Widget w2, String appid, String patid,
			String st, int i) {
		final int index = i;
		final Widget wDisplay = w1;
		final Widget wHide = w2;
		final String statusid = st;
		HashMap<String, String> map = new HashMap<String, String>();
		map.put((String) "cspatient", patid);
		map.put((String) "csappt", appid);
		map.put((String) "csstatus", st);
		JsonUtil.debug("before saving");
		String[] params = { JsonUtil.jsonify(map) };
		RequestBuilder builder = new RequestBuilder(
				RequestBuilder.POST,
				URL
						.encode(Util
								.getJsonRequest(
										"org.freemedsoftware.module.SchedulerPatientStatus.add",
										params)));

		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
				}

				public void onResponseReceived(Request request,
						Response response) {

					if (200 == response.getStatusCode()) {
						wHide.setVisible(false);
						wDisplay.setVisible(true);
						int currRow = workListsTables[index].getActionRow();
						workListsTables[index].getFlexTable().getRowFormatter()
								.getElement(currRow).getStyle().setProperty(
										"backgroundColor",
										statusColorsMap.get(statusid));

					} else {
						Util.showErrorMsg("Patient Status",
								"Patient Status Change Failed.");

						// printLabelForAllTakeHome();
					}
				}
			});
		} catch (RequestException e) {

		}

	}

	public Widget getDefaultIcon() {
		refreshButton = new PushButton();
		refreshButton.setStyleName(AppConstants.STYLE_BUTTON_SIMPLE);
		refreshButton.getUpFace().setImage(
				new Image("resources/images/summary_modify.16x16.png"));
		refreshButton.getDownFace().setImage(
				new Image("resources/images/summary_modify.16x16.png"));
		refreshButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				setProviderGroup(CurrentState.defaultProviderGroup);
			}
		});
		return refreshButton;
	}

	public void populateStatus() {

		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.SchedulerStatusType.getStatusType",
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
								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (result != null) {
									statusNamesMap = new HashMap<String, String>();
									statusColorsMap = new HashMap<String, String>();
									for (int i = 0; i < result.length; i++) {
										statusNamesMap.put(result[i].get("Id"),
												result[i].get("descp"));
										statusColorsMap.put(
												result[i].get("Id"), result[i]
														.get("status_color"));
									}

								} else {
								}
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

	public void setProviderGroup(Integer pId) {
		if (pId != null) {
			providerGroupId = pId;
			populateStatus();
			retrieveData();
		}
	}

	public void clearView() {
		tablesVPanel.clear();
	}

	public void retrieveData() {
		vPanel.add(paneltop);
		vPanel.add(message);
		if (CurrentState.getDefaultProvider() == 0) {
			providerGroupId = CurrentState.defaultProviderGroup;
			if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { providerGroupId.toString() };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.ProviderGroups.getProviderIds",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(
								com.google.gwt.http.client.Request request,
								Throwable ex) {
							Window.alert(ex.toString() + "error1");

						}

						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							if (200 == response.getStatusCode()) {
								if (Util.checkValidSessionResponse(response
										.getText())) {
									String provs[] = (String[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"String[]");
									if (provs != null) {
										if (provs.length != 0) {
											providers = new Integer[provs.length];
											workListsTables = new CustomTable[provs.length];
											providersLb = new Label[provs.length];
											for (int i = 0; i < provs.length; i++) {
												providers[i] = new Integer(
														provs[i]);
												getProviderInfo(i);
											}

										} else {

										}
									}

								}
							} else {
								Window.alert(response.toString() + "error2");
							}
						}
					});
				} catch (RequestException e) {
					Window.alert(e.toString());
				}

			} else {
			}
		} else {
			providers = new Integer[1];
			workListsTables = new CustomTable[1];
			providersLb = new Label[1];
			providers[0] = new Integer(CurrentState.getDefaultProvider());
			getProviderInfo(0);
		}
	}

	private void getProviderInfo(int i) {
		final int index = i;
		providersLb[i] = new Label();
		providersLb[i].setVisible(false);
		providersLb[i].getElement().getStyle().setProperty("cursor", "pointer");
		providersLb[i].getElement().getStyle()
				.setProperty("fontWeight", "bold");
		providersLb[i].getElement().getStyle().setProperty("textDecoration",
				"underline");
		providersLb[i].getElement().getStyle().setProperty("verticalAlign",
				"middle");
		providersLb[i].setHeight("20");
		providersLb[i].addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent e) {
				if (workListsTables[index].isVisible()) {
					workListsTables[index].setVisible(false);
				} else {
					workListsTables[index].setVisible(true);
				}
			}
		});
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "" + providers[i] };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProviderModule.fullName",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Window.alert(ex.toString() + "error1");

					}

					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								String provInfo = (String) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()), "String");
								if (provInfo != null) {
									providersLb[index].setText(provInfo);
									createWorkListTableForProvider(index);
								}

							}
						} else {
							Window.alert(response.toString() + "error2");
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}

		} else {
		}
	}

	private void createWorkListTableForProvider(int i) {
		final int index = i;
		workListsTables[i] = new CustomTable();
		workListsTables[i].getFlexTable().getElement().setAttribute(
				"cellspacing", "0");
		workListsTables[i].setRowStyle("");
		workListsTables[i].setAlternateRowStyle("");
		workListsTables[i].setTableStyle("");
		if (CurrentState.getDefaultProvider() > 0)
			workListsTables[i].setVisible(true);
		else
			workListsTables[i].setVisible(false);
		workListsTables[i].setSize("110%", "100%");
		workListsTables[i].setIndexName("id");
		workListsTables[i].addColumn("Patient", "patient_name");
		// workListsTables[i].addColumn("DD/MM", "date");
		workListsTables[i].addColumn("Time", "time");
		// workListsTables[i].addColumn("Description", "note");
		workListsTables[i].addColumn("Status", "status_fullname");
		// workListsTables[i].setVisible(true);

		workListsTables[i]
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {

						if (columnName.equalsIgnoreCase("status_fullname")) {
							final HorizontalPanel hp = new HorizontalPanel();
							final Label statusText = new Label();
							final CustomListBox statusList = new CustomListBox();
							statusList.setVisible(false);
							hp.add(statusText);
							hp.add(statusList);
							statusList.addChangeHandler(new ChangeHandler() {
								@Override
								public void onChange(ChangeEvent event) {
									if (statusList.getSelectedIndex() != 0) {
										statusText.setText(statusList
												.getItemText(statusList
														.getSelectedIndex()));
										changeStatus(statusText, statusList,
												data.get("id"), data
														.get("patient"),
												statusList.getValue(statusList
														.getSelectedIndex()),
												index);
									}
								}
							});
							statusText
									.setTitle("Click to change the status for "
											+ data.get("patient_name"));

							statusText.setText(data.get("status_fullname"));

							int currRow = workListsTables[index].getActionRow();
							// workListsTables[index].getFlexTable().getCellFormatter().getElement(currRow,
							// 2).getStyle().setProperty("marginLeft", "4");
							workListsTables[index].getFlexTable()
									.getRowFormatter().getElement(currRow)
									.getStyle().setProperty("backgroundColor",
											data.get("status_color"));
							// hp.getElement().getParentElement().getParentElement().getStyle().setProperty(
							// "backgroundColor",
							// data.get("status_color"));
							return hp;
						}
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
								Integer entityId = Integer.parseInt(data
										.get("patient"));
								if (data.get("appointment_type").equals("pat")) {
									PatientScreen p = new PatientScreen();
									p.setPatient(entityId);
									Util.spawnTab(data.get("patient_name"), p);
								} else if (data.get("appointment_type").equals(
										"group")) {
									spawnGroupScreen(entityId);
								}
							}
						});
						return a;
					}
				});
		workListsTables[i].setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col == 1) {
						// TODO: Open the day of this particular event and
						// select that screen.
						SchedulerScreen schedulerScreen = new SchedulerScreen();
						// schedulerScreen.getSchedulerWidget().
						Util.spawnTab("Scheduler", schedulerScreen);
					} else if (col == 2) {
						// Window.alert("col 2 clicked");
						HorizontalPanel hp = (HorizontalPanel) workListsTables[index]
								.getWidget(2);
						Label statusText = (Label) hp.getWidget(0);
						CustomListBox statusList = (CustomListBox) hp
								.getWidget(1);
						statusText.setVisible(false);
						statusList.setVisible(true);
						Set<String> keys = statusNamesMap.keySet();
						Iterator<String> iter = keys.iterator();
						statusList.clear();
						statusList.addItem("-");
						while (iter.hasNext()) {

							final String key = (String) iter.next();
							final String val = (String) statusNamesMap.get(key);
							statusList.addItem(val, key);
						}

					}
				} catch (Exception e) {
					JsonUtil.debug("WorkList.java: Caught exception: "
							+ e.toString());
				}
			}
		});
		retrieveData(i);

	}

	/**
	 * spawn tab for Group.
	 * 
	 * @param patient
	 */
	public void spawnGroupScreen(Integer groupId) {
		Util.spawnTab(AppConstants.GROUPS, PatientsGroupScreen.getInstance());
		PatientsGroupScreen.getInstance().showGroupInfo(groupId);
	}

	private void retrieveData(int i) {
		final int index = i;
		if ((providerGroupId != null && providerGroupId != 0)
				|| CurrentState.getDefaultProvider() > 0) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {

			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				// JSON-RPC
				String[] params = { "" + providers[i] };
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
							Util.showErrorMsg("WorkLists",
									"Failed to get work list.");
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
										// Window.alert(r[0].toString());
										if (r != null) {
											if (r.length > 0) {

												// workListsTables[index].setVisible(true);
												if (r.length >= 10)
													workListsTables[index]
															.setMaximumRows(10);
												else
													workListsTables[index]
															.setMaximumRows(r.length);
												VerticalPanel vp = new VerticalPanel();
												vp.add(providersLb[index]);
												vp.add(workListsTables[index]);
												tablesVPanel.add(vp);

												providersLb[index]
														.setVisible(true);
												// workListsTables[index].setVisible(true);
												workListsTables[index]
														.loadData(r);
											}
										}
									} else {

									}
								} catch (Exception ex) {

								}
							} else {
								Util.showErrorMsg("WorkLists",
										"Failed to get work list.");
							}
						}
					});
				} catch (RequestException e) {
					Util.showErrorMsg("WorkLists", "Failed to get work list.");
				}
			} else {
				// GWT-RPC
			}
		} else {
			// workListTable.setVisible(false);
			providerLabel.setVisible(true);
			providerLabel.setText("Provider not available!");
		}
	}

	@Override
	public void onUnload() {
		super.onUnload();
		try {
			CurrentState.getEventBus().removeHandler(SystemEvent.TYPE, this);
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
	}

	@Override
	public void onSystemEvent(SystemEvent e) {
		if (eventMutex == false) {
			eventMutex = true;
			if (e.getSourceModule().equals("scheduler_status")) {
				Util.showInfoMsg("WorkLists",
						"Updated patient status available");
				retrieveData();
			}
			eventMutex = false;
		} else {
			JsonUtil
					.debug("WorkList duplicate system event, dropping on floor");
		}
	}

}
