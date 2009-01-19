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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
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
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabPanel;

public class PatientProblemList extends Composite {

	protected Integer patientId = new Integer(0);

	protected TabPanel tabPanel = null;

	protected HashMap<String, CustomSortableTable> tables = new HashMap<String, CustomSortableTable>();

	protected HashMap<String, String>[] dataStore = null;

	protected int maximumRows = 10;

	public PatientProblemList() {
		SimplePanel panel = new SimplePanel();
		tabPanel = new TabPanel();
		tabPanel.setSize("100%", "100%");
		tabPanel.setVisible(true);
		panel.setWidget(tabPanel);
		initWidget(panel);

		// All
		// Label allImage = new Label("All");
		CustomSortableTable allTable = new CustomSortableTable();
		allTable.setMaximumRows(10);
		allTable.addColumn("Date", "date_mdy");
		allTable.addColumn("Summary", "summary");
		tabPanel.add(allTable, new Label("All"));
		tables.put("all", allTable);
		// tabPanel.add(allTable, allImage);

		// Progress Notes
		// Label progressNotesImage = new Label("Progress Notes");
		CustomSortableTable progressNotesTable = new CustomSortableTable();
		progressNotesTable.setMaximumRows(10);
		progressNotesTable.addColumn("Date", "date_mdy");
		progressNotesTable.addColumn("Summary", "summary");
		tabPanel.add(progressNotesTable, new Label("Progress Notes"));
		tables.put("pnotes", progressNotesTable);
		// tabPanel.add(progressNotesTable, progressNotesImage);

		// Letters
		CustomSortableTable lettersTable = new CustomSortableTable();
		lettersTable.setMaximumRows(10);
		lettersTable.addColumn("Date", "date_mdy");
		lettersTable.addColumn("Summary", "summary");
		tabPanel.add(lettersTable, new Label("Letters"));
		tables.put("letters|patletter", lettersTable);

		tabPanel.selectTab(0);
	}

	public void setPatientId(Integer id) {
		patientId = id;
		// Call initial data load, as patient id is set
		loadData();
	}

	public void setMaximumRows(int maxRows) {
		maximumRows = maxRows;
		Iterator<String> iter = tables.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			tables.get(k).setMaximumRows(maximumRows);
		}
	}

	/*
	 * private SummaryTable createSummaryTable(String criteria, boolean starred)
	 * { SummaryTable t = new SummaryTable(criteria, starred); try {
	 * t.addColumn("Date", "date_mdy"); t.addColumn("Summary", "summary"); }
	 * catch (Exception ex) { JsonUtil.debug(ex.toString()); } return t; }
	 */

	@SuppressWarnings("unchecked")
	protected void loadData() {
		if (patientId.intValue() == 0) {
			JsonUtil
					.debug("ERROR: patientId not defined when loadData called for PatientProblemList");
		}
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> a = new ArrayList<HashMap<String, String>>();
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-01");
				item.put("type", "test");
				item.put("summary", "Test item 1");
				item.put("module", "ProgressNotes");
				a.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-02");
				item.put("type", "test");
				item.put("summary", "Test item 2");
				item.put("module", "ProgressNotes");
				a.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-02");
				item.put("type", "test");
				item.put("summary", "Test item 3");
				item.put("module", "Letters");
				a.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-03");
				item.put("type", "test");
				item.put("summary", "Test item 4");
				item.put("module", "Letters");
				a.add(item);
			}
			dataStore = (HashMap<String, String>[]) a
					.toArray(new HashMap<?, ?>[0]);
			populateData(dataStore);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.PatientInterface.EmrAttachmentsByPatient",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						JsonUtil.debug("onResponseReceived");
						if (200 == response.getStatusCode()) {
							JsonUtil.debug(response.getText());
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								JsonUtil
										.debug("PatientProblemList... r.length = "
												+ new Integer(r.length)
														.toString());
								dataStore = r;
								populateData(dataStore);
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			PatientInterfaceAsync service = null;
			try {
				service = (PatientInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface");
			} catch (Exception e) {
				GWT.log("Failed to get proxy for PatientInterface", e);
			}
			service.EmrAttachmentsByPatient(patientId,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] r) {
							dataStore = r;
							populateData(dataStore);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Check to see if a string is in a stack of pipe separated values.
	 * 
	 * @param needle
	 * @param haystack
	 * @return
	 */
	protected boolean inSet(String needle, String haystack) {
		String[] stack = haystack.split("|");
		for (int iter = 0; iter < stack.length; iter++) {
			if (needle.equalsIgnoreCase(stack[iter])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Internal method to populate all sub tables.
	 * 
	 * @param data
	 */
	@SuppressWarnings("unchecked")
	protected void populateData(HashMap<String, String>[] data) {
		JsonUtil.debug("PatientProblemList.populateData");
		Iterator<String> tIter = tables.keySet().iterator();
		while (tIter.hasNext()) {
			String k = tIter.next();
			JsonUtil.debug("Populating table " + k);

			// Clear table contents
			try {
				tables.get(k).clearData();
			} catch (Exception ex) {
			}

			// Depending on criteria, etc, choose what do to.
			String crit = k;
			// JsonUtil.debug(" --> got criteria = " + crit);
			// boolean star = tables.get(k).getStarred();

			List<HashMap<String, String>> res = new ArrayList<HashMap<String, String>>();
			List<HashMap<String, String>> d = Arrays.asList(data);
			Iterator<HashMap<String, String>> iter = d.iterator();
			while (iter.hasNext()) {
				HashMap<String, String> rec = iter.next();

				// TODO: handle star

				if (crit == null || crit.length() == 0
						|| crit.contentEquals("all")) {
					// Don't handle criteria at all, effectively passthru
					// JsonUtil.debug("-- pass through, no criteria");
				} else {
					// Handle criteria
					if (crit.compareToIgnoreCase(rec.get("module")) != 0
							|| inSet(rec.get("module"), crit)) {
						continue;
					}
				}

				// If it passes all criteria, add to the stack for the result.
				res.add(rec);
			}

			if (res.size() > 0) {
				JsonUtil.debug("Populating table " + k + " with "
						+ new Integer(res.size()).toString() + " entries");
				CustomSortableTable thisTable = tables.get(k);
				HashMap<String, String>[] thisData = (HashMap<String, String>[]) res
						.toArray(new HashMap<?, ?>[0]);
				thisTable.loadData(thisData);
				JsonUtil.debug("Completed populating table " + k);
			} else {
				JsonUtil.debug("Could not populate null results into table");
			}
		}
	}
}
