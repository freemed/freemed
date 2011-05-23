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
import java.util.HashSet;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

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
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class RemittBillingWidget extends Composite {
	public enum BillingType {
		BILL, REBILL
	};

	protected CustomRequestCallback callback;
	protected HashSet<String> procs;
	protected HashSet<String> billKeysSet;
	protected VerticalPanel vPanel;
	protected CustomTable claimsTable;
	protected HashMap<String, HashMap<String, String>> procsInfoMap;
	protected CustomListBox clearingHouseList;
	protected CustomListBox billingServiceList;
	protected CustomListBox billingContactList;
	String[] remittUniqueIds;
	String[] billKeys;
	String[] formats;
	String[] target;
	String[] billBatches;
	HashMap<String, String> ss[];
	protected CustomTable statusTable;
	protected HorizontalPanel actionPanel;
	protected CustomButton postBtn;
	protected CustomButton cancelBtn;
	protected HashMap<String, String> fileNamesMap;
	protected Timer t;

	public RemittBillingWidget(HashSet<String> p, CustomRequestCallback cb,
			BillingType btype) {
		callback = cb;

		vPanel = new VerticalPanel();
		initWidget(vPanel);
		fileNamesMap = new HashMap<String, String>();
		if (btype == BillingType.BILL)
			createBillingUI(p);
		else
			createReBillingUI(p);
	}

	private void createBillingUI(HashSet<String> p) {
		procs = p;

		procsInfoMap = new HashMap<String, HashMap<String, String>>();
		// Iterator<String> it = procs.iterator();

		claimsTable = new CustomTable();
		claimsTable.setAllowSelection(false);
		claimsTable.setSize("100%", "100%");
		claimsTable.setIndexName("id");
		claimsTable.addColumn("Patient", "pt_name");
		claimsTable.addColumn("Service Date", "ser_date");
		claimsTable.addColumn("Media Format", "format");
		claimsTable.addColumn("Coverage", "coverage");

		claimsTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {

					@Override
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {

						if (columnName.compareTo("format") == 0) {

							final CustomListBox formatListBox = new CustomListBox();
							formatListBox.addItem(
									"Electric - hcfa 1500/FreeClaims", "0");
							formatListBox.addItem("Paper - hcfa 1500/PDF", "1");
							formatListBox.addChangeHandler(new ChangeHandler() {

								@Override
								public void onChange(ChangeEvent arg0) {
									HashMap<String, String> hm = procsInfoMap
											.get(data.get("id"));
									hm.put("format", formatListBox
											.getStoredValue());
								}
							});
							HashMap<String, String> hm = procsInfoMap.get(data
									.get("id"));
							hm.put("ptid", data.get("pt_id"));
							hm.put("format", formatListBox.getStoredValue());
							return formatListBox;
						} else if (columnName.compareTo("coverage") == 0) {
							final CustomListBox coverageListBox = new CustomListBox();
							coverageListBox
									.addChangeHandler(new ChangeHandler() {

										@Override
										public void onChange(ChangeEvent arg0) {
											HashMap<String, String> hm = procsInfoMap
													.get(data.get("id"));
											hm.put("cov", coverageListBox
													.getStoredValue());
										}
									});
							loadCoverageList(coverageListBox, data.get("id"));
							return coverageListBox;
						} else {
							return (Widget) null;
						}

					}
				});
		vPanel.add(claimsTable);
		loadSeletedProcedureInfo();
		actionPanel = new HorizontalPanel();
		actionPanel.setSpacing(5);
		postBtn = new CustomButton("Post Claim/s", AppConstants.ICON_ADD);
		postBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				prepareClaimsDataForSubmitting();
			}

		});
		cancelBtn = new CustomButton("Cancel", AppConstants.ICON_CANCEL);
		final RemittBillingWidget rbw = this;
		cancelBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (t != null) {
					t.cancel();
				}
				rbw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		actionPanel.add(postBtn);
		actionPanel.add(cancelBtn);
		vPanel.add(actionPanel);

	}

	private void createReBillingUI(HashSet<String> p) {
		billKeysSet = p;
		actionPanel = new HorizontalPanel();
		actionPanel.setSpacing(5);

		cancelBtn = new CustomButton("Close", AppConstants.ICON_CANCEL);
		final RemittBillingWidget rbw = this;
		cancelBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (t != null) {
					t.cancel();
				}
				rbw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		actionPanel.add(cancelBtn);
		createStatusTable();
		rebill();
	}

	public void loadSeletedProcedureInfo() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { procs.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ClaimLog.getProceduresInfo",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length > 0) {
											for (int i = 0; i < result.length; i++) {
												HashMap<String, String> hm = new HashMap<String, String>();
												procsInfoMap.put(result[i]
														.get("id"), hm);
											}
											claimsTable.loadData(result);
										}
									}
								} catch (Exception e) {

								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}

		} else {
		}
	}

	public void loadCoverageList(final CustomListBox covList, final String proc) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { proc };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.getCoverages",
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
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											for (int i = 0; i < result.length; i++) {
												covList
														.addItem(
																result[i]
																		.get("payer")
																		+ " - "
																		+ result[i]
																				.get("type"),
																result[i]
																		.get("id"));
											}
											HashMap<String, String> hm = procsInfoMap
													.get(proc);
											hm.put("cov", covList
													.getStoredValue());
										} else {

										}
									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	public void prepareClaimsDataForSubmitting() {
		Iterator<String> iterator = procsInfoMap.keySet().iterator();
		ArrayList<String> procIds = new ArrayList<String>();
		ArrayList<String> patientIds = new ArrayList<String>();
		while (iterator.hasNext()) {
			String id = iterator.next();
			procIds.add(id);
			HashMap<String, String> item = procsInfoMap.get(id);
			patientIds.add(item.get("ptid"));
		}
		createStatusTable();
		processClaims(procIds, patientIds);
	}

	public void createStatusTable() {
		statusTable = new CustomTable();
		statusTable.setAllowSelection(false);
		// statusTable.setSize("100%", "100%");
		statusTable.setIndexName("billkey");
		statusTable.addColumn("Identifier", "result");
		statusTable.addColumn("File", "file");
		statusTable.addColumn("Status", "status");
		statusTable.addColumn("Action", "action");

		statusTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {

					@Override
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {

						if (columnName.compareTo("result") == 0) {
							Label lb = new Label(data.get("result") + "("
									+ data.get("format") + ")");
							return lb;
						} else if (columnName.compareTo("action") == 0) {
							HTML html = new HTML(
									"<a href=\"javascript:undefined;\" style='color:blue'>Mark As Billed</a>");
							html.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {
									billBatches = null;
									billBatches = new String[1];
									billBatches[0] = data.get("billkey");
									markAsBilled();
								}

							});
							return html;
						} else if (columnName.compareTo("file") == 0) {

							if (fileNamesMap.get(data.get("result")) != null) {
								HTML html = new HTML(
										"<a href=\"javascript:undefined;\" style='color:blue'>"
												+ fileNamesMap.get(data
														.get("result"))
												+ "</a>");
								html.addClickHandler(new ClickHandler() {
									@Override
									public void onClick(ClickEvent arg0) {
										getFile(fileNamesMap.get(data
												.get("result")));
									}

								});
								return html;
							} else {
								getFileName(data.get("result"));
								Label lb = new Label();
								return lb;
							}

						} else {
							return (Widget) null;
						}

					}
				});
	}

	public void processClaims(ArrayList<String> procIds,
			ArrayList<String> patientIds) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { patientIds.toString(), procIds.toString(), "",
					"1", "1", "1" };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.RemittBillingTransport.ProcessClaims",
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
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {

								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result.length > 0) {
										showStatus(result);
									}

								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	public void rebill() {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { billKeysSet.toString() };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.RemittBillingTransport.rebillkeys",
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
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {

								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result.length > 0) {
										showStatus(result);
									}

								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	@SuppressWarnings("unchecked")
	public void showStatus(HashMap<String, String>[] data) {
		vPanel.clear();
		vPanel.add(statusTable);
		actionPanel.clear();
		cancelBtn.setText("Close");

		CustomButton markAllBilled = new CustomButton(
				"Mark All Bacthes as Billed", AppConstants.ICON_SELECT_ALL);
		actionPanel.add(markAllBilled);
		actionPanel.add(cancelBtn);
		vPanel.add(actionPanel);
		ss = new HashMap[data.length];
		formats = new String[data.length];
		billKeys = new String[data.length];
		remittUniqueIds = new String[data.length];
		target = new String[data.length];
		for (int i = 0; i < data.length; i++) {
			HashMap<String, String> map = data[i];
			formats[i] = map.get("format");
			billKeys[i] = map.get("billkey");
			remittUniqueIds[i] = map.get("result");
			target[i] = map.get("target");
		}
		markAllBilled.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				billBatches = billKeys;
				markAsBilled();
			}

		});
		loadStatus();
		t = new Timer() {
			public void run() {
				loadStatus();
			}
		};
		t.scheduleRepeating(15000);
	}

	public void loadStatus() {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { JsonUtil.jsonify(remittUniqueIds) };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.RemittBillingTransport.GetStatus",
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
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>");
									if (result != null) {
										Iterator<String> iterator = result
												.keySet().iterator();
										boolean isAllCompleted = true;
										while (iterator.hasNext()
												&& isAllCompleted) {
											String id = iterator.next();
											String item = result.get(id);
											if (!item.equals("0")) {
												isAllCompleted = false;
											}
											if (fileNamesMap.get(id) == null) {
												isAllCompleted = false;
											}
										}
										if (isAllCompleted)
											t.cancel();
										for (int i = 0; i < ss.length; i++) {
											ss[i] = new HashMap<String, String>();
											ss[i].put("billkey", billKeys[i]);
											ss[i].put("result",
													remittUniqueIds[i]);
											ss[i].put("target", target[i]);
											ss[i].put("format", formats[i]);

											if (result.get(remittUniqueIds[i]) != null) {
												if (result.get(
														remittUniqueIds[i])
														.equals("0")) {
													ss[i].put("status",
															"completed");
												} else if (result.get(
														remittUniqueIds[i])
														.equals("1")) {
													ss[i].put("status",
															"validation");
												} else if (result.get(
														remittUniqueIds[i])
														.equals("2")) {
													ss[i].put("status",
															"render");
												} else if (result.get(
														remittUniqueIds[i])
														.equals("3")) {
													ss[i].put("status",
															"translation");
												} else if (result.get(
														remittUniqueIds[i])
														.equals("4")) {
													ss[i].put("status",
															"transmission");
												} else if (result.get(
														remittUniqueIds[i])
														.equals("5")) {
													ss[i].put("status",
															"unknown");
												}

											} else
												ss[i].put("status", "");
										}
										statusTable.loadData(ss);

									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	public void markAsBilled() {
		final RemittBillingWidget rbw = this;
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { JsonUtil.jsonify(billBatches) };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.RemittBillingTransport.MarkAsBilled",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									Boolean result = (Boolean) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Boolean");
									if (result != null) {
										if (result) {

											rbw.removeFromParent();
											callback.jsonifiedData("update");
										}
									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	public void getFileName(final String id) {
		// final RemittBillingWidget rbw = this;
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { "output", "payload", JsonUtil.jsonify(id) };

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.Remitt.GetFileList",
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
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										for (HashMap<String, String> r : result) {
											fileNamesMap.put(id, r
													.get("filename"));
										}
									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	public void getFile(final String name) {
		String[] params = { "output", name, "html" };
		Window.open(URL.encode(Util.getJsonRequest(
				"org.freemedsoftware.api.Remitt.GetFile", params)), name, "");

	}
}
