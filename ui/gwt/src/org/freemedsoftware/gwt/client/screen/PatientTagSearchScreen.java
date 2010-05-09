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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.PatientTagAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.PatientTagWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;

public class PatientTagSearchScreen extends ScreenInterface {

	private PatientTagWidget tagWidget = null;

	private CustomTable customSortableTable = null;

	private static List<PatientTagSearchScreen> patientTagSearchScreenFormList = null;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well
	public static PatientTagSearchScreen getInstance() {
		PatientTagSearchScreen patientTagSearchScreen = null;

		if (patientTagSearchScreenFormList == null) {
			patientTagSearchScreenFormList = new ArrayList<PatientTagSearchScreen>();
		}
		if (patientTagSearchScreenFormList.size() < AppConstants.MAX_TAGSEARCH_TABS) {
			// creates & returns new next instance of PatientTagSearchScreen
			patientTagSearchScreenFormList
					.add(patientTagSearchScreen = new PatientTagSearchScreen());
		} else {
			// returns last instance of PatientTagSearchScreen from list
			patientTagSearchScreen = patientTagSearchScreenFormList
					.get(AppConstants.MAX_TAGSEARCH_TABS - 1);
		}
		return patientTagSearchScreen;
	}

	public static boolean removeInstance(
			PatientTagSearchScreen patientTagSearchScreen) {
		return patientTagSearchScreenFormList.remove(patientTagSearchScreen);
	}

	public PatientTagSearchScreen() {
		FlexTable layout = new FlexTable();
		initWidget(layout);

		Label tagLabel = new Label("Search for Tag: ");
		layout.setWidget(0, 0, tagLabel);
		tagWidget = new PatientTagWidget();
		layout.setWidget(0, 2, tagWidget);
		tagWidget.addValueChangeHandler(new ValueChangeHandler<String>() {
			@Override
			public void onValueChange(ValueChangeEvent<String> evt) {
				PatientTagWidget w = (PatientTagWidget) evt.getSource();
				if (w.getValue().length() > 2) {
					searchForTag(w.getValue());
				}
			}
		});

		customSortableTable = new CustomTable();
		customSortableTable.setWidth("100%");
		layout.setWidget(1, 0, customSortableTable);
		layout.getFlexCellFormatter().setColSpan(1, 0, 4);

		customSortableTable.setIndexName("patient_record");
		customSortableTable.addColumn("Last Name", "last_name");
		customSortableTable.addColumn("First Name", "first_name");
		customSortableTable.addColumn("DOB", "date_of_birth");
		customSortableTable.addColumn("Patient ID", "patient_id");
		customSortableTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				Integer patientId = null;
				String patientName = null;
				try {
					patientId = Integer.parseInt(data.get("patient_record"));
					JsonUtil.debug("patientId = " + patientId.toString());
					patientName = data.get("last_name") + ", "
							+ data.get("first_name") + " ["
							+ data.get("date_of_birth") + "] "
							+ data.get("patient_id");
					JsonUtil.debug("patientName = " + patientName);
				} catch (Exception ex) {
					GWT.log("Exception", ex);
				} finally {
					PatientScreen s = new PatientScreen();
					s.setPatient(patientId);
					JsonUtil.debug("Spawn patient screen with patient = "
							+ patientId.toString());
					GWT.log("Spawn patient screen with patient = "
							+ patientId.toString(), null);
					Util.spawnTab(patientName, s);
				}

			}
		});
		Util.setFocus(tagWidget);
	}
	/**
	 * Populate the screen with data.
	 * 
	 * @param data
	 */
	protected void populate(HashMap<String, String>[] data) {
		customSortableTable.loadData(data);
	}

	/**
	 * Set value of the tag widget which is being used for the search.
	 * 
	 * @param tagValue
	 *            Textual tag name.
	 */
	public void setTagValue(String tagValue) {
		tagWidget.setValue(tagValue);
		searchForTag(tagValue);
	}

	/**
	 * Perform tag search and pass population data on.
	 * 
	 * @param t
	 *            Textual value of tag being searched.
	 */
	@SuppressWarnings("unchecked")
	public void searchForTag(String t) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> results = new ArrayList<HashMap<String, String>>();
			populate((HashMap<String, String>[]) results
					.toArray(new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			customSortableTable.showloading(true);
			String[] params = { t, JsonUtil.jsonify(Boolean.FALSE) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientTag.SimpleTagSearch",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log("Exception", ex);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								populate(r);
							}
						} else {
							customSortableTable.showloading(false);
							GWT.log("Exception", null);
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			PatientTagAsync proxy = null;
			try {
				proxy = (PatientTagAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.PatientTag");
			} catch (Exception ex) {
				GWT.log("Exception", ex);
			}
			proxy.SimpleTagSearch(t, Boolean.FALSE,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] data) {
							populate(data);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
