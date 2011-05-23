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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class ToolsScreen extends ScreenInterface {

	public final static String moduleName = "Tools";

	protected CustomTable toolTable;

	private static List<ToolsScreen> toolsScreenList = null;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well
	public static ToolsScreen getInstance() {
		ToolsScreen toolsScreen = null;

		if (toolsScreenList == null) {
			toolsScreenList = new ArrayList<ToolsScreen>();
		}
		if (toolsScreenList.size() < AppConstants.MAX_TOOLS_TABS) {
			// creates & returns new next instance of SupportDataScreen
			toolsScreenList.add(toolsScreen = new ToolsScreen());
		} else { // returns last instance of ToolsScreen from list
			toolsScreen = toolsScreenList.get(AppConstants.MAX_TOOLS_TABS - 1);
		}
		return toolsScreen;
	}

	public static boolean removeInstance(ToolsScreen toolsScreen) {
		return toolsScreenList.remove(toolsScreen);
	}

	public ToolsScreen() {
		super(moduleName);
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final Label pleaseChooseALabel = new Label("Please choose a tool.");
		verticalPanel.add(pleaseChooseALabel);
		pleaseChooseALabel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);

		toolTable = new CustomTable();
		verticalPanel.add(toolTable);
		toolTable.setAllowSelection(false);
		toolTable.setSize("100%", "100%");
		toolTable.setIndexName("tool_uuid");
		toolTable.addColumn("Name", "tool_name");
		toolTable.addColumn("Description", "tool_desc");
		toolTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				ToolsExecutionScreen screen = new ToolsExecutionScreen(data
						.get("tool_name"), data.get("tool_uuid"));
				Util.spawnTab(data.get("tool_name"), screen);
			}
		});

		// After everything is initialized, start population routine.
		populate();
	}

	public void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			toolTable.showloading(true);
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Tools.GetTools",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						GWT.log("Exception", ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (result != null) {
								toolTable.loadData(result);
							} else {
								toolTable.showloading(false);
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			// TODO: Make this work with GWT-RPC
		}
	}

	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
