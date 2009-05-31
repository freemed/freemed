/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MessageBox extends WidgetInterface {

	protected Label messageCountLabel = new Label("You have no new Messages!");

	protected HashMap<String, String>[] result;

	protected CustomSortableTable wMessages = new CustomSortableTable();

	protected HashMap<String, String>[] dataMemory;

	protected Popup popupMessageView;

	public MessageBox() {
		SimplePanel sPanel = new SimplePanel();
		initWidget(sPanel);
		sPanel.setStyleName("freemed-WidgetContainer");

		final VerticalPanel verticalPanel = new VerticalPanel();

		sPanel.setWidget(verticalPanel);
		// sPanel.addStyleName("freemed-MessageBoxContainer");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();

		final PushButton showMessagesButton = new PushButton("", "");
		showMessagesButton.getUpFace().setImage(
				new Image("resources/images/messaging.32x32.png"));
		showMessagesButton.getDownFace().setImage(
				new Image("resources/images/messaging.32x32.png"));

		verticalPanel.add(horizontalPanel);
		horizontalPanel.add(showMessagesButton);

		verticalPanel.add(wMessages);
		wMessages.setSize("100%", "100%");
		wMessages.addColumn("Received", "stamp"); // col 0
		wMessages.addColumn("From", "from_user"); // col 1
		wMessages.addColumn("Subject", "subject"); // col 2
		wMessages.setIndexName("id");

		wMessages.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents ste, int row, int col) {
				// Get information on row...
				try {
					final Integer messageId = new Integer(wMessages
							.getValueByRow(row));
					if ((col == 0) || (col == 2)) {
						MessageView messageView = new MessageView();
						showMessage(messageId, messageView);
						popupMessageView = new Popup();
						popupMessageView.setNewWidget(messageView);
						messageView.setOnClose(new Command() {
							public void execute() {
								popupMessageView.hide();
							}
						});
						popupMessageView.initialize();
					}
				} catch (Exception e) {
					GWT.log("Caught exception: ", e);
				}
			}

		});

		// Standard is collapsed view of the Messagebox
		wMessages.setVisible(false);
		// Click listener for both: the button and the label

		showMessagesButton.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				if (wMessages.isVisible() == false)
					wMessages.setVisible(true);
				else
					wMessages.setVisible(false);
			}
		});
		messageCountLabel.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				if (wMessages.isVisible() == false)
					wMessages.setVisible(true);
				else
					wMessages.setVisible(false);
			}
		});

		horizontalPanel.add(messageCountLabel);

		// Load the Data; we have no searchtag - we search for everything
		retrieveData("");
	}

	public void showMessage(Integer messageId, MessageView messageView) {
		final MessageView view = messageView;
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			if (messageId == 1) {
				view
						.setText("This is some sample message according to ID#1. Here you can see that the messages can be designed using <b>HTML</b> in a <i>very cool <b>way</b></i>. <br/>You can even <sub>subcase letters</sub>.");
			} else if (messageId == 2) {
				view.setText("Text to MessageId 2");
			}
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
								view.setText(r.get("msgtext").replace("\\", "")
										.replace("\n", "<br/>"));
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
			}

		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {

		}
	}

	protected void retrieveData(String searchtag) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Runs in STUBBED MODE => Feed with Sample Data
			HashMap<String, String>[] sampleData = getSampleData();
			loadData(sampleData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] messagesparams = { searchtag,
					JsonUtil.jsonify(Boolean.FALSE) };
			// TODO: get Config setting to retrieve all mail, or only new one
			// if (state.getUserConfig("messagebox") == "retrieveall") {
			// messagesparams[1] = JsonUtil.jsonify(Boolean.TRUE) ;
			//	
			// }

			String[] countparams = { JsonUtil.jsonify(Boolean.FALSE),
					JsonUtil.jsonify(Boolean.FALSE) };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.MessagesModule.UnreadMessages",
											countparams)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							Integer data = (Integer) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Integer");
							if (data != null) {
								loadCounter(data);
							}
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}

			// Get data
			RequestBuilder dataBuilder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.MessagesModule.GetAllByTag",
											messagesparams)));
			try {
				dataBuilder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String, String>[] data = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null) {
								setResult(data);
								loadData(data);
							}
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}
	}

	public void loadData(HashMap<String, String>[] data) {
		wMessages.clear();
		wMessages.loadData(data);
		// Save the data internally
		dataMemory = data;
		// for testing purpose only
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			messageCountLabel.setText("You have 2 new Messages!");
		}
	}

	/**
	 * Set current messages count as displayed.
	 * 
	 * @param count
	 */
	public void loadCounter(Integer count) {
		String text;
		if (count < 1) {
			text = "You have " + count.toString() + " new messages!";
		} else {
			text = "There are no new messages.";
		}
		messageCountLabel.setText(text);
	}

	public HashMap<String, String>[] getResult() {
		return result;
	}

	public void setResult(HashMap<String, String>[] data) {
		result = data;
	}

	@SuppressWarnings("unchecked")
	protected HashMap<String, String>[] getSampleData() {
		List<HashMap<String, String>> m = new ArrayList<HashMap<String, String>>();

		HashMap<String, String> a = new HashMap<String, String>();
		a.put("id", "1");
		a.put("stamp", "2009-02-06");
		a.put("from_user", "Philipp");
		a.put("subject", "Test of SampleData");
		m.add(a);

		HashMap<String, String> b = new HashMap<String, String>();
		b.put("id", "2");
		b.put("stamp", "2009-02-06");
		b.put("from_user", "Some random Guy");
		b.put("subject", "Whatever he says");
		m.add(b);

		return (HashMap<String, String>[]) m.toArray(new HashMap<?, ?>[0]);
	}

}
