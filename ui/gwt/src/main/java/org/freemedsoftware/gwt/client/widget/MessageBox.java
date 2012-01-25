/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2012 FreeMED Software Foundation
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Style.Cursor;
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
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MessageBox extends WidgetInterface {

	protected Label messageCountLabel = new Label(_("You have no new messages."));

	protected HashMap<String, String>[] result;

	protected CustomTable wMessages = new CustomTable();

	protected HashMap<String, String>[] dataMemory;

	protected Popup popupMessageView;

	private PushButton showMessagesButton;
	
	protected final VerticalPanel contentVPanel;
	
	public MessageBox() {
		VerticalPanel superVPanel = new VerticalPanel();
		initWidget(superVPanel);
		superVPanel.setStyleName(AppConstants.STYLE_BUTTON_WIDGETS_CONTAINER );
		superVPanel.setWidth("100%");

		
		HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setSpacing(5);
		superVPanel.add(headerHPanel);
		
		final Image colExpBtn = new Image(Util.getResourcesURL()+"collapse.15x15.png");
		colExpBtn.getElement().getStyle().setCursor(Cursor.POINTER);
		headerHPanel.add(colExpBtn);
		colExpBtn.addClickHandler(new ClickHandler() {
			boolean expaned = false;
			@Override
			public void onClick(ClickEvent arg0) {
				if(expaned){
					colExpBtn.setUrl(Util.getResourcesURL()+"collapse.15x15.png");
					contentVPanel.setVisible(true);
				}else{
					colExpBtn.setUrl(Util.getResourcesURL()+"expand.15x15.png");
					contentVPanel.setVisible(false);
				}
					expaned = !expaned;
			}
		});

		Label headerLabel = new Label(_("MESSAGES"));
		headerHPanel.add(headerLabel);
		headerLabel.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		
		
		
		contentVPanel = new VerticalPanel();
		contentVPanel.setWidth("100%");
		superVPanel.add(contentVPanel);
		// sPanel.addStyleName("freemed-MessageBoxContainer");

		contentVPanel.add(wMessages);
		wMessages.setSize("100%", "100%");
		wMessages.addColumn(_("Received"), "stamp"); // col 0
		wMessages.addColumn(_("From"), "from_user"); // col 1
		wMessages.addColumn(_("Subject"), "subject"); // col 2
		wMessages.setIndexName("id");
		wMessages.setMaximumRows(7);
		if(true){
			wMessages.setTableRowClickHandler(new TableRowClickHandler() {
				@Override
				public void handleRowClick(HashMap<String, String> data, int col) {
					// Get information on row...
					try {
						final Integer messageId = Integer.parseInt(data.get("id"));
						if ((col == 0) || (col == 2)) {
							MessageView messageView = new MessageView();
							showMessage(messageId, messageView);
							messageView.setMsgFrom(data.get("from_user"));
							messageView.setMsgDate(data.get("stamp"));
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
		}
		// Standard is collapsed view of the Messagebox
//		wMessages.setVisible(false);
		// Click listener for both: the button and the label

//		showMessagesButton.addClickHandler(new ClickHandler() {
//			@Override
//			public void onClick(ClickEvent evt) {
//				if (wMessages.isVisible() == false)
//					wMessages.setVisible(true);
//				else
//					wMessages.setVisible(false);
//			}
//		});
//		messageCountLabel.addClickHandler(new ClickHandler() {
//			@Override
//			public void onClick(ClickEvent evt) {
//				if (wMessages.isVisible() == false)
//					wMessages.setVisible(true);
//				else
//					wMessages.setVisible(false);
//			}
//		});

//		horizontalPanel.add(messageCountLabel);

		// Load the Data; we have no searchtag - we search for everything
//		retrieveData("");
	}

	public Widget getDefaultIcon(){
		if(showMessagesButton==null){
			showMessagesButton = new PushButton("", "");
			showMessagesButton.setStyleName(AppConstants.STYLE_BUTTON_SIMPLE);
			showMessagesButton.getUpFace().setImage(
					new Image("resources/images/messaging.16x16.png"));
			showMessagesButton.getDownFace().setImage(
					new Image("resources/images/messaging.16x16.png"));
			showMessagesButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (wMessages.isVisible() == false)
						wMessages.setVisible(true);
					else
						wMessages.setVisible(false);
				}
			});
		}
		return showMessagesButton;
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
									.shoehornJson(JSONParser.parseStrict(response
											.getText()),
											"HashMap<String,String>");
							if (r != null) {
								view.setText(r.get("msgtext").replace("\\", "")
										.replace("\n", "<br/>"));
								view.setMsgFromId(Integer.parseInt(r.get("msgby")));
								view.setMsgSubject(r.get("msgsubject"));
								view.setMsgPatientId(Integer.parseInt(r.get("msgpatient")));
								view.setMsgBody(r.get("msgtext"));
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

	public void clearView(){
		wMessages.clearData();
	}
	
	public void retrieveData(String searchtag) {
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
									JSONParser.parseStrict(response.getText()),
									"Integer");
							if (data != null) {
								JsonUtil.debug("Msg count from server is:"+data);
								loadCounter(data);
							}
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}

			wMessages.showloading(true);
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
									.shoehornJson(JSONParser.parseStrict(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null) {
								setResult(data);
								loadData(data);
							}else wMessages.showloading(false);
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
		wMessages.clearData();
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
		JsonUtil.debug("Msg count is:"+count);
		if (count < 1) {
			text = "There are no new messages.";			
		} else {
			text = "You have " + count.toString() + " new messages!";
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
