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

package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.entry.SupportModuleEntry;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
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
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.xml.client.DOMException;
import com.google.gwt.xml.client.Document;
import com.google.gwt.xml.client.Element;
import com.google.gwt.xml.client.Node;
import com.google.gwt.xml.client.NodeList;
import com.google.gwt.xml.client.XMLParser;

public class SupportDataManagementScreen extends ScreenInterface implements
		Command {

	protected CustomListBox wField = null;

	protected TextBox searchText = null;

	protected CustomSortableTable sortableTable = null;

	protected String moduleName = null;

	protected String rawXml = null;

	public SupportDataManagementScreen() {
		final SupportDataManagementScreen thisRef = this;

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final PushButton addButton = new PushButton("Add", "Add");
		horizontalPanel.add(addButton);
		addButton.setStylePrimaryName("freemed-PushButton");
		addButton.setText("Add");
		addButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				SupportModuleEntry entry = new SupportModuleEntry(moduleName);
				entry.setDoneCommand(thisRef);
				Util.spawnTab(moduleName + ": " + "Add", entry);
			}
		});

		wField = new CustomListBox();
		horizontalPanel.add(wField);
		wField.setVisibleItemCount(1);
		wField.addChangeListener(new ChangeListener() {
			public void onChange(Widget sender) {
				// If we see text, repopulate
				if (searchText.getText().length() > 2) {
					populateData();
				}
			}
		});

		searchText = new TextBox();
		searchText.addChangeListener(new ChangeListener() {
			public void onChange(Widget sender) {
				// If we see text, repopulate
				if (((TextBox) sender).getText().length() > 2) {
					populateData();
				}
			}
		});
		horizontalPanel.add(searchText);

		final PushButton searchButton = new PushButton("Search", "Search");
		horizontalPanel.add(searchButton);
		searchButton.setStylePrimaryName("freemed-PushButton");
		searchButton.setText("Search");

		sortableTable = new CustomSortableTable();
		verticalPanel.add(sortableTable);
		sortableTable.setSize("100%", "100%");
		sortableTable.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents ste, int row, int col) {
				// Get information on row...
				try {
					final Integer recordId = new Integer(sortableTable
							.getValueByRow(row));
					SupportModuleEntry entry = new SupportModuleEntry(
							moduleName, recordId);
					entry.setDoneCommand(thisRef);
					Util.spawnTab(moduleName + ": " + "Edit", entry);
				} catch (Exception e) {
					GWT.log("Caught exception: ", e);
				}
			}
		});
	}

	public void setModuleName(String module) {
		moduleName = module;
		populateSchema();
	}

	public void populateSchema() {
		if (moduleName != null) {
			// Get XML file name from module
			final String interfaceUrl = "resources/interface/" + moduleName
					+ ".module.xml";
			RequestBuilder builder = new RequestBuilder(RequestBuilder.GET, URL
					.encode(interfaceUrl));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							rawXml = response.getText();
							populateColumns(rawXml);
							populateData();
						} else {
							GWT.log("Error requesting " + interfaceUrl + ": "
									+ response.getStatusText(), null);
						}
					}

					public void onError(Request request, Throwable exception) {
						GWT.log("Exception", exception);
					}
				});
			} catch (RequestException e) {
				GWT.log("RequestException", e);
			}
		}
	}

	/**
	 * Create column headers for sortable table from interface xml definition.
	 * 
	 * @param xml
	 *            Raw xml string
	 */
	public void populateColumns(String xml) {
		try {
			// parse the XML document into a DOM
			Document dom = XMLParser.parse(xml);

			// find the sender's display name in an attribute of the <from> tag
			Node simpleUIBuilderNode = dom.getElementsByTagName(
					"SimpleUIBuilder").item(0);
			if (simpleUIBuilderNode != null) {
				NodeList elements = dom.getElementsByTagName("Element");
				sortableTable.clear();
				for (int iter = 0; iter < elements.getLength(); iter++) {
					Element e = (Element) elements.item(iter);
					if (e.getAttribute("display").compareTo("1") == 0) {
						sortableTable.addColumn(e.getAttribute("title"), e
								.getAttribute("field"));
						wField.addItem(e.getAttribute("title"), e
								.getAttribute("field"));
					}
				}
			} else {
				// Deal with other possibilities
			}
		} catch (DOMException e) {
			GWT.log("Could not parse XML document.", e);
		}
	}

	@SuppressWarnings("unchecked")
	public void populateData() {
		try {
			sortableTable.clearData();
		} catch (Exception ex) {
			JsonUtil.debug(ex.toString());
		}
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: populate in stubbed mode
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {
					moduleName,
					"40",
					(searchText.getText().length() > 2) ? wField
							.getWidgetValue() : "",
					(searchText.getText().length() > 2) ? searchText.getText()
							: "" };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleGetRecordsMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem(
								"SupportDataManagementScreen",
								"Could not load support data records.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								sortableTable.loadData(r);
							} else {
								CurrentState.getToaster().addItem(
										"SupportDataManagementScreen",
										"Could not load support data records.",
										Toaster.TOASTER_ERROR);
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			ModuleInterfaceAsync proxy = null;
			try {
				proxy = (ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
			} catch (Exception e) {
				GWT.log("Exception", e);
			}
			proxy.ModuleGetRecordsMethod(moduleName, 40, (searchText.getText()
					.length() > 2) ? wField.getWidgetValue() : "", (searchText
					.getText().length() > 2) ? searchText.getText() : "",
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] res) {
							sortableTable.loadData(res);
						}

						public void onFailure(Throwable t) {
							CurrentState.getToaster().addItem(
									"SupportDataManagementScreen",
									"Could not load support data records.",
									Toaster.TOASTER_ERROR);
						}
					});
		}
	}

	/**
	 * Executed when something is done to the data in this screen.
	 */
	public void execute() {
		populateData();
	}

}
