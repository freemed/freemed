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
import org.freemedsoftware.gwt.client.widget.CustomSortableTable.TableWidgetColumnSetInterface;

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
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.Widget;

public class PatientProblemList extends Composite {

	public class ActionBar extends Composite implements ClickListener {

		protected Integer internalId = 0;

		protected HashMap<String, String> data = null;

		protected Image annotateImage = null, deleteImage = null,
				modifyImage = null, unlockedImage = null, lockedImage = null,
				printImage = null;

		protected CheckBox cb = null;

		public ActionBar(HashMap<String, String> item) {
			// Pull ID for future
			internalId = Integer.parseInt(item.get("id"));
			data = item;

			HorizontalPanel hPanel = new HorizontalPanel();
			initWidget(hPanel);

			// Multiple select box
			cb = new CheckBox();
			cb.addClickListener(this);
			hPanel.add(cb);

			// Build icons
			annotateImage = new Image("resources/images/add1.16x16.png");
			annotateImage.setTitle("Add Annotation");
			annotateImage.addClickListener(this);
			hPanel.add(annotateImage);

			printImage = new Image("rsources/images/ico.printer.16x16.png");
			printImage.setTitle("Print");
			printImage.addClickListener(this);
			hPanel.add(printImage);

			// Display all unlocked things
			if (Integer.parseInt(data.get("locked")) != 0) {
				deleteImage = new Image(
						"resources/images/summary_delete.16x16.png");
				deleteImage.setTitle("Remove");
				deleteImage.addClickListener(this);
				hPanel.add(deleteImage);
				modifyImage = new Image(
						"resources/images/summary_modify.16x16.png");
				modifyImage.setTitle("Edit");
				modifyImage.addClickListener(this);
				hPanel.add(modifyImage);
			} else {
				// Display all actions for locked items
			}
		}

		public void onClick(Widget sender) {
			if (sender == cb) {
				Window.alert("toggle item " + internalId.toString());
			} else if (sender == annotateImage) {
				Window.alert("annotate item " + internalId.toString());
			} else if (sender == printImage) {
				EmrPrintDialog d = new EmrPrintDialog();
				d.setItems(new Integer[] { Integer.parseInt(data.get("id")) });
				d.center();
			} else if (sender == deleteImage) {
				Window.alert("delete item " + internalId.toString());
			} else if (sender == modifyImage) {
				Window.alert("modify item " + internalId.toString());
			} else {
				// Do nothing
			}
		}

	}

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
		Image allImage = new Image("resources/images/chart_full.16x16.png");
		allImage.setTitle("All");
		createSummaryTable(allImage, "all");
		// Progress Notes
		Image notesImage = new Image("resources/images/chart.16x16.png");
		notesImage.setTitle("Progress Notes");
		createSummaryTable(notesImage, "pnotes");
		// Letters
		Image lettersImage = new Image(
				"resources/images/summary_envelope.16x16.png");
		lettersImage.setTitle("Letters");
		createSummaryTable(lettersImage, "letters,patletter");

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

	private void createSummaryTable(Widget tab, String criteria) {
		CustomSortableTable t = new CustomSortableTable();
		t.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				JsonUtil.debug("Rendering action bar");
				return new ActionBar(data);
			}
		});
		t.setMaximumRows(maximumRows);
		t.addColumn("Date", "date_mdy");
		t.addColumn("Module", "type");
		t.addColumn("Summary", "summary");
		t.addColumn("Action", "action");
		tabPanel.add(t, tab);
		tables.put(criteria, t);
	}

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
		// Handle incidence of needle == haystack
		if (needle.equalsIgnoreCase(haystack)) {
			return true;
		}
		String[] stack = haystack.split(",");
		for (int iter = 0; iter < stack.length; iter++) {
			if (needle.trim().equalsIgnoreCase(stack[iter].trim())) {
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
					if (!inSet(rec.get("module"), crit)) {
						JsonUtil.debug(rec.get("module") + " not include "
								+ crit);
					} else {
						JsonUtil.debug(rec.get("module") + " INCLUDE " + crit);
					}
					if (!inSet(rec.get("module"), crit)) {
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
