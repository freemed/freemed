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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.screen.entry.SupportModuleEntry;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
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
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.PushButton;
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
	public final static String moduleNameACL = "admin";
	
	protected CustomListBox wField = null;

	protected TextBox searchText = null;

	protected CustomTable sortableTable = null;

	protected String moduleName = null;

	protected String rawXml = null;

	public static Integer KEYWORD_LENGTH_LIMIT = null;
	
	public static Integer MINIMUM_RECORDS_LIMIT = null;
	
	public SupportDataManagementScreen() {
		super(moduleNameACL);
		if(KEYWORD_LENGTH_LIMIT == null)
			KEYWORD_LENGTH_LIMIT = Integer.parseInt(CurrentState.getSystemConfig("module_search_keyword_limit")!=null?CurrentState.getSystemConfig("module_search_keyword_limit"):"2");
		if(MINIMUM_RECORDS_LIMIT == null)
			MINIMUM_RECORDS_LIMIT = Integer.parseInt(CurrentState.getSystemConfig("module_record_limit")!=null?CurrentState.getSystemConfig("module_record_limit"):"100");
		final SupportDataManagementScreen thisRef = this;

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		if(canWrite){
			final PushButton addButton = new PushButton(_("Add"), _("Add"));
			horizontalPanel.add(addButton);
			addButton.setStylePrimaryName("freemed-PushButton");
			addButton.setText(_("Add"));
			addButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					SupportModuleEntry entry = new SupportModuleEntry(moduleName);
					entry.setDoneCommand(thisRef);
					Util.spawnTab(moduleName + ": " + _("Add"), entry);
				}
			});
		}

		wField = new CustomListBox();
		horizontalPanel.add(wField);
		wField.setVisibleItemCount(1);
		wField.addChangeHandler(new ChangeHandler() {
			public void onChange(ChangeEvent evt) {
				// If we see text, repopulate
				if (searchText.getText().length() >= KEYWORD_LENGTH_LIMIT) {
					populateData();
				}
			}
		});

		searchText = new TextBox();
		searchText.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent evt) {
				// If we see text, repopulate
				if (((TextBox) evt.getSource()).getText().length() >= KEYWORD_LENGTH_LIMIT) {
					populateData();
				}
			}
		});
		horizontalPanel.add(searchText);

		final PushButton searchButton = new PushButton(_("Search"), _("Search"));
		horizontalPanel.add(searchButton);
		searchButton.setStylePrimaryName("freemed-PushButton");
		searchButton.setText(_("Search"));

		final PushButton clearButton = new PushButton(_("Clear"), _("Clear"));
		horizontalPanel.add(clearButton);
		clearButton.setStylePrimaryName("freemed-PushButton");
		clearButton.setText(_("Clear"));
		
		clearButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				populateData();
			}
		});
		
		sortableTable = new CustomTable();
		verticalPanel.add(sortableTable);
		sortableTable.setSize("100%", "100%");
		sortableTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				actionBar.applyPermissions(false, false, canDelete, canModify, false);
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						//Evaluating id
						Integer recordId = null;
						if(data.get("oid")!=null)
							recordId = Integer.parseInt(data.get("oid"));
						else if(data.get("id")!=null)
							recordId = Integer.parseInt(data.get("id"));
						//Checing action type
						if(action == HandleCustomAction.MODIFY){
							//Preparing record to be able modified
							SupportModuleEntry entry = new SupportModuleEntry(
									moduleName, recordId);
							entry.setDoneCommand(thisRef);
							Util.spawnTab(moduleName + ": " + _("Edit"), entry);
						}else if(action == HandleCustomAction.DELETE){
							Util.callModuleMethod(moduleName, "del", recordId, new CustomRequestCallback() {
							
								@Override
								public void onError() {
									// TODO Auto-generated method stub
							
								}
							
								@Override
								public void jsonifiedData(Object data) {
									if((Boolean)data){
										populateData();
										Util.showInfoMsg(moduleName, _("Record deleted succcessfully."));
									}else
										Util.showInfoMsg(moduleName, _("Failed to delete record."));							
								}
							
							}, "Boolean");
						}
					}
				});
				// Push value back to table
				return actionBar;
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
			final String interfaceUrl = Util.getUIBaseUrl()
					+ "resources/interface/" + moduleName + ".module.xml";
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
				sortableTable.clearData();
				for (int iter = 0; iter < elements.getLength(); iter++) {
					Element e = (Element) elements.item(iter);
					if (e.getAttribute("display")!=null && e.getAttribute("display").compareTo("1") == 0) {
						sortableTable.addColumn(e.getAttribute("title"), e
								.getAttribute("field"));
						wField.addItem(e.getAttribute("title"), e
								.getAttribute("field"));
					}
				}
				sortableTable.addColumn(_("Action"), "action");
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
		sortableTable.showloading(true);
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: populate in stubbed mode
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {
					moduleName,
					MINIMUM_RECORDS_LIMIT.toString(),
					(searchText.getText().length() >= KEYWORD_LENGTH_LIMIT) ? wField
							.getWidgetValue() : "",
					(searchText.getText().length() >= KEYWORD_LENGTH_LIMIT) ? searchText.getText()
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
						Util.showErrorMsg("SupportDataScreen", _("Could not load list of support data modules."));
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>[]");
								sortableTable.loadData(r);
							} else {
								Util.showErrorMsg("SupportDataScreen", _("Could not load list of support data modules."));
							}
							sortableTable.showloading(false);
						}
					}
				});
			} catch (RequestException e) {
				sortableTable.showloading(false);
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
			proxy.ModuleGetRecordsMethod(moduleName, MINIMUM_RECORDS_LIMIT, (searchText.getText()
					.length() > 2) ? wField.getWidgetValue() : "", (searchText
					.getText().length() >= KEYWORD_LENGTH_LIMIT) ? searchText.getText() : "",
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] res) {
							sortableTable.loadData(res);
							sortableTable.showloading(false);
						}

						public void onFailure(Throwable t) {
							Util.showErrorMsg("SupportDataScreen", _("Could not load list of support data modules."));
							sortableTable.showloading(false);
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
