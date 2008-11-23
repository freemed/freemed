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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.TableMaintenanceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.entry.SupportModuleEntry;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.Toaster;

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
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SupportDataScreen extends ScreenInterface {

	protected CustomSortableTable sortableTable;

	public SupportDataScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		sortableTable = new CustomSortableTable();
		sortableTable.setIndexName("module_class");
		sortableTable.addColumn("Name", "module_name");
		sortableTable.addColumn("Version", "module_version");
		sortableTable.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents e, int row, int col) {
				// String moduleName = sortableTable.getValueByRow(row);
				handleClick(row);
			}
		});
		verticalPanel.add(sortableTable);

		// When everything else is done, populate
		populate();
	}

	@SuppressWarnings("unchecked")
	public void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> r = new ArrayList<HashMap<String, String>>();
			String[][] stockModules = {
					{ "AppointmentTemplates", "Appointment Templates" },
					{ "ClaimTypes", "Claim Types" },
					{ "CoverageTypes", "Coverage Types" } };
			for (int iter = 0; iter < stockModules.length; iter++) {
				HashMap<String, String> a = new HashMap<String, String>();
				a.put("module_class", stockModules[iter][0]);
				a.put("module_name", stockModules[iter][1]);
				a.put("module_version", "0.0");
				r.add(a);
			}
			sortableTable.loadData((HashMap<String, String>[]) r
					.toArray(new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "SupportModule", "" };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.TableMaintenance.GetModules",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						state.getToaster().addItem("SupportDataScreen",
								"Could not load list of support data modules.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							sortableTable.loadData(r);
						} else {
							state
									.getToaster()
									.addItem(
											"SupportDataScreen",
											"Could not load list of support data modules.",
											Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			TableMaintenanceAsync proxy = null;
			try {
				proxy = (TableMaintenanceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.TableMaintenance");
			} catch (Exception e) {
				GWT.log("Exception", e);
			}
			proxy.GetModules("SupportModule", "", false,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] res) {
							sortableTable.loadData(res);
						}

						public void onFailure(Throwable t) {
							state
									.getToaster()
									.addItem(
											"SupportDataScreen",
											"Could not load list of support data modules.",
											Toaster.TOASTER_ERROR);
						}
					});
		}
	}

	protected void handleClick(int idx) {
		// TODO: For right now, bring up add screen. Fix it later.
		String moduleClass = sortableTable.getValueFromIndex(idx,
				"module_class");
		String moduleName = sortableTable.getValueFromIndex(idx, "module_name");
		SupportDataManagementScreen screen = new SupportDataManagementScreen();
		screen.setModuleName(moduleClass);
		Util.spawnTab(moduleName, screen, state);
	}
}
