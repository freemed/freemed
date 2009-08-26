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
import java.util.Iterator;
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
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MessagingScreen extends ScreenInterface implements ClickHandler {

	private CustomTable wMessages = null;

	private HashMap<String, String>[] mStore = null;

	protected HTML messageView;

	protected final static String LOADING_IMAGE = "resources/images/loading.gif";

	protected HashMap<CheckBox, Integer> checkboxStack = new HashMap<CheckBox, Integer>();

	protected List<Integer> selectedItems = new ArrayList<Integer>();

	protected CustomListBox messageTagSelect = new CustomListBox();

	public MessagingScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final Button composeButton = new Button();
		horizontalPanel.add(composeButton);
		composeButton.setText("Compose");
		composeButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				final MessagingComposeScreen p = new MessagingComposeScreen();
				p.setParentScreen(getMessagingScreen());
				CurrentState.getTabPanel().add(p,
						new ClosableTab("Compose Message", p));
				CurrentState.getTabPanel().selectTab(
						CurrentState.getTabPanel().getWidgetCount() - 1);
			}
		});

		final Button selectAllButton = new Button();
		horizontalPanel.add(selectAllButton);
		selectAllButton.setText("Select All");
		selectAllButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent wvt) {
				Iterator<CheckBox> iter = checkboxStack.keySet().iterator();
				while (iter.hasNext()) {
					CheckBox t = iter.next();
					t.setValue(true);
					if (!selectedItems.contains(checkboxStack.get(t))) {
						selectedItems.add(checkboxStack.get(t));
					}
				}
			}
		});

		final Button selectNoneButton = new Button();
		horizontalPanel.add(selectNoneButton);
		selectNoneButton.setText("Select None");
		selectNoneButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				Iterator<CheckBox> iter = checkboxStack.keySet().iterator();
				while (iter.hasNext()) {
					CheckBox t = iter.next();
					t.setValue(false);
					if (selectedItems.contains(checkboxStack.get(t))) {
						selectedItems.remove(checkboxStack.get(t));
					}
				}
			}
		});

		final Button deleteButton = new Button();
		horizontalPanel.add(deleteButton);
		deleteButton.setText("Delete");
		deleteButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				if (Window
						.confirm("Are you sure you want to delete these item(s)?")) {
					Window.alert("STUB: delete items");
				}
			}
		});

		horizontalPanel.add(messageTagSelect);
		messageTagSelect.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				try {
					String effective = messageTagSelect.getWidgetValue();
					Window
							.alert("effective value of tag widget = "
									+ effective);
				} catch (Exception ex) {
					Window.alert(ex.toString());
				}
			}
		});

		final Button selectButton = new Button();
		horizontalPanel.add(selectButton);
		selectButton.setText("Change");
		selectButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				populate(((CustomListBox) evt.getSource()).getWidgetValue());
			}
		});

		final Button moveButton = new Button();
		horizontalPanel.add(moveButton);
		moveButton.setText("Move");
		moveButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				Window.alert("STUB: move message(s)");
			}
		});

		final VerticalPanel verticalSplitPanel = new VerticalPanel();
		verticalPanel.add(verticalSplitPanel);
		verticalSplitPanel.setSize("100%", "100%");
		// verticalSplitPanel.setSplitPosition("50%");

		wMessages = new CustomTable();
		wMessages.setAllowSelection(true);
		wMessages.setMultipleSelection(true);
		verticalSplitPanel.add(wMessages);
		wMessages.setSize("100%", "100%");
		wMessages.addColumn("Selected", "selected");
		wMessages.addColumn("Received", "stamp"); // col 1
		wMessages.addColumn("From", "from_user"); // col 2
		wMessages.addColumn("Subject", "subject"); // col 3
		wMessages.addColumn("Delete", "delete"); // col 4
		wMessages.setIndexName("id");
		wMessages.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					final Integer messageId = Integer.parseInt(data.get("id"));
					if (col == 4) {
						deleteMessage(messageId);
					} else {
						showMessage(messageId);
					}
				} catch (Exception e) {
					GWT.log("Caught exception: ", e);
				}
			}
		});
		wMessages
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							HashMap<String, String> data) {
						Integer id = Integer.parseInt(data.get("id"));
						if (columnName.compareTo("selected") == 0) {
							CheckBox c = new CheckBox();
							c.addClickHandler(getMessagingScreen());
							checkboxStack.put(c, id);
							return c;
						} else {
							return (Widget) null;
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
		populateTagWidget();
	}

	public MessagingScreen getMessagingScreen() {
		return this;
	}

	public void onClick(ClickEvent evt) {
		Widget w = (Widget) evt.getSource();
		if (w instanceof CheckBox) {
			if (checkboxStack.keySet().size() > 0) {
				Iterator<CheckBox> iter = checkboxStack.keySet().iterator();
				while (iter.hasNext()) {
					CheckBox c = iter.next();
					if (c == w) {
						// Handle click event for item
						Integer id = checkboxStack.get(c);
						handleClickForItemCheckbox(id, c);
					}
				}
			}
		}
	}

	protected void handleClickForItemCheckbox(Integer item, CheckBox c) {
		// Add or remove from itemlist
		if (c.getValue()) {
			selectedItems.add((Integer) item);
		} else {
			selectedItems.remove((Object) item);
		}
	}

	/**
	 * Populate tag/folder selection widget.
	 */
	protected void populateTagWidget() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			HashMap<String, String>[] dummyData = getStubData();
			populateByData(dummyData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Populate message tags
			String[] mTparams = {};
			RequestBuilder mTbuilder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.MessagesModule.MessageTags",
											mTparams)));
			try {
				mTbuilder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								String[][] r = (String[][]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()), "String[][]");
								if (r != null) {
									messageTagSelect.clear();
									for (int i = 0; i < r.length; i++) {
										messageTagSelect.addItem(r[i][0],
												r[i][1]);
									}
								}
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
			}

		} else {
			// FIXME: GWT-RPC
		}
	}

	public void populate(String tag) {
		selectedItems.clear();
		checkboxStack.clear();
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
						if (Util.checkValidSessionResponse(response.getText())) {
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

			// FIXME: needs to populate tags widget
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
		wMessages.clearData();
		JsonUtil.debug("loaddata");
		wMessages.loadData(data);
		JsonUtil.debug("after loaddata");
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
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Boolean r = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Boolean");
								if (r != null) {
									CurrentState.getToaster().addItem(
											"MessagingScreen",
											"Deleted message.",
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
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									messageView.setHTML(r.get("msgtext")
											.replace("\\", "").replace("\n",
													"<br/>"));
								}
							} else {
							}
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
