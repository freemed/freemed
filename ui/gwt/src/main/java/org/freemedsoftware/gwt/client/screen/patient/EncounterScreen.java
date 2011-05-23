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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.EncounterTemplateWidget;
import org.freemedsoftware.gwt.client.widget.EncounterWidget;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.EncounterTemplateWidget.CallbackType;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.EncounterCommandType;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.EncounterFormMode;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.EncounterFormType;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class EncounterScreen extends PatientScreenInterface {
	protected VerticalPanel verticalPanel;
	protected TabPanel tabPanel;
	protected VerticalPanel entryVerticalPanel;
	protected EncounterWidget encounterWidget;
	protected CustomTable enotesCustomTable;
	protected Boolean isAdding = true;
	protected HashMap<String, String> moddata;
	protected HashMap<String, List<String>> sections;

	public EncounterScreen() {
		moddata = new HashMap<String, String>();
		sections = new HashMap<String, List<String>>();
		verticalPanel = new VerticalPanel();
		verticalPanel.setSize("100%", "100%");
		entryVerticalPanel = new VerticalPanel();
		tabPanel = new TabPanel();
		tabPanel.add(entryVerticalPanel, "Add");
		verticalPanel.add(tabPanel);
		initWidget(verticalPanel);
		tabPanel.selectTab(0);
	}

	public void laodData() {
		createEncounterNotesListTab();
		createEncounterNotesAdditionTab();
	}

	public void createEncounterNotesAdditionTab() {
		EncounterFormMode mod;
		String templateName = "";
		if (isAdding) {
			mod = EncounterFormMode.ADD;
		} else {
			if (moddata.get("pnotestemplate") != null)
				templateName = moddata.get("pnotestemplate");
			mod = EncounterFormMode.EDIT;
		}
		encounterWidget = new EncounterWidget(
				EncounterFormType.ENCOUNTER_NOTE_VALUES, mod, sections,
				templateName, moddata, patientId.toString(),
				new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@Override
					public void jsonifiedData(Object data) {
						if (data instanceof EncounterCommandType) {
							if (((EncounterCommandType) data) == EncounterCommandType.CREATE_TEMPLATE) {
								final EncounterTemplateWidget encounterTemplateWidget = new EncounterTemplateWidget(
										new CustomRequestCallback() {
											@Override
											public void onError() {

											}

											@Override
											public void jsonifiedData(
													Object data) {
												if (data instanceof CallbackType) {
													if (((CallbackType) data) == CallbackType.CANCEL) {
														tabPanel
																.remove(tabPanel
																		.getWidgetCount() - 1);
														tabPanel.selectTab(0);
													} else if (((CallbackType) data) == CallbackType.UPDATED) {
														tabPanel
																.remove(tabPanel
																		.getWidgetCount() - 1);
														tabPanel.selectTab(0);
													}
												} else if (data instanceof Integer[]) {
													// splitBatch((Integer[])data);
												}

											}
										});
								tabPanel.add(encounterTemplateWidget,
										"Encounter Template");
								tabPanel
										.selectTab(tabPanel.getWidgetCount() - 1);
							} else if (((EncounterCommandType) data) == EncounterCommandType.EDIT_TEMPLATE) {
								final EncounterTemplateWidget encounterTemplateWidget = new EncounterTemplateWidget(
										new CustomRequestCallback() {
											@Override
											public void onError() {

											}

											@Override
											public void jsonifiedData(
													Object data) {
												if (data instanceof CallbackType) {
													if (((CallbackType) data) == CallbackType.CANCEL) {
														tabPanel
																.remove(tabPanel
																		.getWidgetCount() - 1);
														tabPanel.selectTab(0);
													} else if (((CallbackType) data) == CallbackType.UPDATED) {
														tabPanel
																.remove(tabPanel
																		.getWidgetCount() - 1);
														tabPanel.selectTab(0);
													}
												} else if (data instanceof Integer[]) {
													// splitBatch((Integer[])data);
												}

											}
										});
								encounterTemplateWidget
										.getTemplateValues(encounterWidget
												.getSelectedTemplate());
								tabPanel.add(encounterTemplateWidget,
										"Encounter Template");
								tabPanel
										.selectTab(tabPanel.getWidgetCount() - 1);
							} else if (((EncounterCommandType) data) == EncounterCommandType.UPDATE) {
								reset();
								tabPanel.selectTab(1);
								loadEncountersList();
								entryVerticalPanel.clear();
								createEncounterNotesAdditionTab();
							} else if (((EncounterCommandType) data) == EncounterCommandType.RESET) {
								reset();
								tabPanel.selectTab(0);
								loadEncountersList();
								entryVerticalPanel.clear();
								createEncounterNotesAdditionTab();
							}
						}

					}
				});
		entryVerticalPanel.add(encounterWidget);
	}

	public void createEncounterNotesListTab() {
		VerticalPanel listPanel = new VerticalPanel();
		tabPanel.add(listPanel, "List");
		enotesCustomTable = new CustomTable();
		enotesCustomTable.setIndexName("id");
		// patientCustomTable.setSize("100%", "100%");
		enotesCustomTable.setWidth("100%");
		enotesCustomTable.addColumn("Date", "note_date");
		enotesCustomTable.addColumn("Type", "note_type");
		enotesCustomTable.addColumn("Description", "note_desc");
		enotesCustomTable.addColumn("Submitter", "user");
		enotesCustomTable.addColumn("Action", "action");

		enotesCustomTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							HashMap<String, String> data) {
						// Render only action column, otherwise skip renderer
						if (columnName.compareToIgnoreCase("action") != 0) {
							return null;
						}
						final CustomActionBar actionBar = new CustomActionBar(
								data);
						actionBar.applyPermissions(false, false, CurrentState
								.isActionAllowed("EncounterNotes",
										AppConstants.DELETE), CurrentState
								.isActionAllowed("EncounterNotes",
										AppConstants.MODIFY), false);

						actionBar
								.setHandleCustomAction(new HandleCustomAction() {
									@Override
									public void handleAction(int id,
											HashMap<String, String> data,
											int action) {
										if (action == HandleCustomAction.MODIFY) {
											try {
												isAdding = false;
												laodEncounterNoteInfo(data
														.get("id"));
												tabPanel.selectTab(0);
											} catch (Exception e) {
												GWT
														.log(
																"Caught exception: ",
																e);
											}

										} else if (action == HandleCustomAction.DELETE) {
											deleteNote(data.get("id"));
										}
									}
								});
						// Push value back to table
						return actionBar;
					}
				});

		listPanel.add(enotesCustomTable);
		loadEncountersList();
	}

	public void loadEncountersList() {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {

			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotes.getEncountersList",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {

									enotesCustomTable.loadData(r);
								} else {

								}
							} catch (Exception e) {

							}

						} else {
						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}

	public void laodEncounterNoteInfo(String id) {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { id };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotes.getEncounterNoteInfo",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									moddata = r;
									if (r.get("pnotestemplate") == null
											|| r.get("pnotestemplate").equals(
													"")
											|| r.get("pnotestemplate").equals(
													"0")) {

										entryVerticalPanel.clear();
										createEncounterNotesAdditionTab();

									} else {
										getTemplateValues(moddata
												.get("pnotestemplate"));
									}
								} else {
								}
							} catch (Exception e) {
							}

						} else {
						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}

	public void getTemplateValues(String templateId) {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { templateId };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotesTemplate.getTemplateInfo",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									String secStr = r.get("pnotestsections");
									sections = (HashMap<String, List<String>>) JsonUtil
											.shoehornJson(JSONParser
													.parse(secStr),
													"HashMap<String,List>");
									entryVerticalPanel.clear();
									createEncounterNotesAdditionTab();
								} else {

								}
							} catch (Exception e) {

							}

						} else {

						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}

	public void deleteNote(String id) {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { id };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.EncounterNotes.del",
							params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								Boolean r = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Boolean");
								if (r) {
									Util
											.showInfoMsg("EncounterNotes",
													"Encounter Note Successfully Deleted.");
									loadEncountersList();
								}

							} catch (Exception e) {

							}

						} else {

						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}

	public void reset() {
		isAdding = true;
		sections.clear();
		moddata.clear();
	}
}
