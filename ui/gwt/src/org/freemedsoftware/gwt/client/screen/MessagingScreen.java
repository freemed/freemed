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

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.MessagesAsync;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Module.MessagesModule;
import org.freemedsoftware.gwt.client.Module.MessagesModuleAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.ClosableTab;
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
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MessagingScreen extends ScreenInterface {

	private CustomSortableTable wMessages;

	private HashMap<String, String>[] mStore;

	protected HTML messageView;

	protected final static String LOADING_IMAGE = "resources/images/loading.gif";

	public MessagingScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final Button composeButton = new Button();
		horizontalPanel.add(composeButton);
		composeButton.setText("Compose");
		composeButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				final MessagingComposeScreen p = new MessagingComposeScreen();
				CurrentState.getTabPanel().add(p,
						new ClosableTab("Compose Message", p));
				CurrentState.getTabPanel().selectTab(
						CurrentState.getTabPanel().getWidgetCount() - 1);
			}
		});

		final Button selectAllButton = new Button();
		horizontalPanel.add(selectAllButton);
		selectAllButton.setText("Select All");

		final Button selectNoneButton = new Button();
		horizontalPanel.add(selectNoneButton);
		selectNoneButton.setText("Select None");

		final VerticalPanel verticalSplitPanel = new VerticalPanel();
		verticalPanel.add(verticalSplitPanel);
		verticalSplitPanel.setSize("100%", "100%");
		// verticalSplitPanel.setSplitPosition("50%");

		wMessages = new CustomSortableTable();
		verticalSplitPanel.add(wMessages);
		wMessages.setSize("100%", "100%");
		wMessages.addColumn("Received", "stamp"); // col 0
		wMessages.addColumn("From", "from_user"); // col 1
		wMessages.addColumn("Subject", "subject"); // col 2
		wMessages.addColumn("Delete", "delete"); // col 3
		wMessages.setIndexName("id");
		wMessages.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents ste, int row, int col) {
				// Get information on row...
				try {
					final Integer messageId = new Integer(wMessages
							.getValueByRow(row));
					if (col == 3) {
						deleteMessage(messageId);
					} else {
						showMessage(messageId);
					}
				} catch (Exception e) {
					GWT.log("Caught exception: ", e);
				}
			}
		});

		messageView = new HTML("<img src=\"" + LOADING_IMAGE
				+ "\" border=\"0\" />");
		verticalSplitPanel.add(messageView);
		messageView.setSize("100%", "100%");
		// verticalSplitPanel.setSize("100%", "100%");

		// Start population routine
		populate("");
	}

	public void populate(String tag) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			HashMap<String, String>[] dummyData = getStubData();
			populateByData(dummyData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { tag, JsonUtil.jsonify(Boolean.FALSE) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.MessagesModule.GetAllByTag",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								populateByData(r);
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// Populate the whole thing.
			MessagesModuleAsync service = (MessagesModuleAsync) GWT
					.create(MessagesModule.class);
			ServiceDefTarget endpoint = (ServiceDefTarget) service;
			String moduleRelativeURL = Util.getRelativeURL();
			endpoint.setServiceEntryPoint(moduleRelativeURL);
			service.GetAllByTag(tag, Boolean.FALSE,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] result) {
							populateByData(result);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Actual internal data population method, wrapped for testing.
	 * 
	 * @param data
	 */
	public void populateByData(HashMap<String, String>[] data) {
		// Keep a copy of the data in the local store
		mStore = data;
		// Clear any current contents
		wMessages.clear();
		wMessages.loadData(data);
		// Quickly add something blank to the message view so loading image goes
		// away
		messageView.setHTML("<br/>&nbsp;<br/>&nbsp;<br/>");
	}

	@SuppressWarnings("unchecked")
	public HashMap<String, String>[] getStubData() {
		List<HashMap<String, String>> m = new ArrayList<HashMap<String, String>>();

		final HashMap<String, String> a = new HashMap<String, String>();
		a.put("id", "1");
		a.put("stamp", "2007-08-01");
		a.put("from_user", "A");
		a.put("subject", "Subject A");
		m.add(a);

		final HashMap<String, String> b = new HashMap<String, String>();
		b.put("id", "2");
		b.put("stamp", "2007-08-01");
		b.put("from_user", "B");
		b.put("subject", "Subject B");
		m.add(b);

		final HashMap<String, String> c = new HashMap<String, String>();
		c.put("id", "3");
		c.put("stamp", "2007-08-03");
		c.put("from_user", "C");
		c.put("subject", "Subject C");
		m.add(c);

		return (HashMap<String, String>[]) m.toArray(new HashMap<?, ?>[0]);
	}

	protected void deleteMessage(Integer messageId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			CurrentState.getToaster().addItem("MessagingScreen",
					"Deleted message.", Toaster.TOASTER_INFO);
			populate("");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(messageId) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.Messages.Remove", params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem("MessagingScreen",
								"Failed to delete message.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Boolean r = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Boolean");
							if (r != null) {
								CurrentState.getToaster().addItem(
										"MessagingScreen", "Deleted message.",
										Toaster.TOASTER_INFO);
								populate("");
							}
						} else {
							CurrentState.getToaster().addItem(
									"MessagingScreen",
									"Failed to delete message.",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			MessagesAsync service = null;

			try {
				service = (MessagesAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.Messages");
			} catch (Exception e) {
				CurrentState.getToaster().addItem("MessagingScreen",
						"Failed to delete message.", Toaster.TOASTER_ERROR);
			}
			service.Remove(messageId, new AsyncCallback<Boolean>() {
				public void onSuccess(Boolean data) {
					if (data) {
						CurrentState.getToaster().addItem("MessagingScreen",
								"Deleted message.", Toaster.TOASTER_INFO);
						populate("");
					} else {
						CurrentState.getToaster().addItem("MessagingScreen",
								"Failed to delete message.",
								Toaster.TOASTER_ERROR);
					}
				}

				public void onFailure(Throwable t) {
					CurrentState.getToaster().addItem("MessagingScreen",
							"Failed to delete message.", Toaster.TOASTER_ERROR);
				}
			});
		}
	}

	protected void showMessage(Integer messageId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			String txt = new String();
			switch (messageId.intValue()) {
			case 1:
				txt = "Text from message A";
				break;
			case 2:
				txt = "Some more text from message B.";
				break;
			case 3:
				txt = "Why are you still clicking on me? I'm from message C.";
				break;
			default:
				txt = "";
				break;
			}
			messageView.setHTML(txt);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "MessagesModule", JsonUtil.jsonify(messageId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleGetRecordMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String> r = (HashMap<String, String>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>");
							if (r != null) {
								messageView.setHTML(r.get("msgtext").replace(
										"\\", "").replace("\n", "<br/>"));
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			ModuleInterfaceAsync service = null;

			try {
				service = (ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
			} catch (Exception e) {
				GWT.log("Caught exception: ", e);
			}
			service.ModuleGetRecordMethod("MessagesModule", messageId,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> data) {
							messageView.setHTML(data.get("msgtext").replace(
									"\\", "").replace("\n", "<br/>"));
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

}
