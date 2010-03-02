/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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
import org.freemedsoftware.gwt.client.Api.MessagesAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.UserMultipleChoiceWidget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class MessagingComposeScreen extends ScreenInterface {

	protected final TextArea wText;

	protected UserMultipleChoiceWidget wTo;

	protected final TextBox wSubject;

	protected final PatientWidget wPatient;

	protected final CustomListBox wUrgency;

	protected MessagingScreen parentScreen = null;

	protected final String className = "org.freemedsoftware.gwt.client.MessagingComposeScreen";

	protected FlexTable flexTable;

	public MessagingComposeScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		final Label toLabel = new Label("To : ");
		flexTable.setWidget(0, 0, toLabel);

		wTo = new UserMultipleChoiceWidget();
		flexTable.setWidget(0, 1, wTo);

		final Label subjectLabel = new Label("Subject : ");
		flexTable.setWidget(1, 0, subjectLabel);

		wSubject = new TextBox();
		wSubject.setWidth("100%");
		flexTable.setWidget(1, 1, wSubject);

		final Label urgencyLabel = new Label("Urgency : ");
		flexTable.setWidget(3, 0, urgencyLabel);

		wUrgency = new CustomListBox();
		flexTable.setWidget(3, 1, wUrgency);
		wUrgency.addItem("1 (Urgent)");
		wUrgency.addItem("2 (Expedited)");
		wUrgency.addItem("3 (Standard)");
		wUrgency.addItem("4 (Notification)");
		wUrgency.addItem("5 (Bulk)");
		wUrgency.setSelectedIndex(2);

		final Label patientLabel = new Label("Patient : ");
		flexTable.setWidget(2, 0, patientLabel);

		wPatient = new PatientWidget();
		wPatient.setWidth("100%");
		flexTable.setWidget(2, 1, wPatient);

		wText = new TextArea();
		flexTable.setWidget(4, 1, wText);
		wText.setVisibleLines(10);
		wText.setCharacterWidth(60);
		wText.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		final Button sendButton = new Button();
		horizontalPanel.add(sendButton);
		sendButton.setText("Send");
		sendButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				sendMessage(false);
			}
		});

		final Button sendAnotherButton = new Button();
		horizontalPanel.add(sendAnotherButton);
		sendAnotherButton.setText("Send and Compose Another");
		sendAnotherButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				sendMessage(true);
			}
		});

		final Button clearButton = new Button();
		horizontalPanel.add(clearButton);
		clearButton.setText("Clear");
		clearButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				clearForm();
			}
		});
		Util.setFocus(wTo);
	}
	
	public void setParentScreen(MessagingScreen p) {
		parentScreen = p;
	}

	protected MessagingComposeScreen getThisObject() {
		return this;
	}

	public void clearForm() {
		wPatient.clear();
		wTo.setValue(new Integer[] {});
		wSubject.setText("");
		wText.setText("");
		wTo.setFocus();
	}

	public void sendMessage(final boolean sendAnother) {
		CurrentState.statusBarAdd(className, "Sending Message");

		// Form data
		HashMap<String, String> data = new HashMap<String, String>();
		data.put("patient", wPatient.getValue().toString());
		data.put("for", wTo.getCommaSeparatedValues());
		data.put("text", wText.getText());
		data.put("subject", wSubject.getText());
		data.put("urgency", wUrgency.getWidgetValue());

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			CurrentState.statusBarRemove(className);
			CurrentState.getToaster().addItem(className, "Sent message.",
					Toaster.TOASTER_INFO);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(data) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.Messages.Send", params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.statusBarRemove(className);
						CurrentState.getToaster().addItem(className,
								"Failed to send message.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[] r = (String[]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[]");
							if (r != null) {
								CurrentState.statusBarRemove(className);
								CurrentState.getToaster().addItem(className,
										"Sent message.", Toaster.TOASTER_INFO);
								if (!sendAnother) {
									if(parentScreen != null){
										parentScreen.populate("");
										parentScreen.populateTagWidget();
									}
									getThisObject().closeScreen();
								}
								else{
									wSubject.setText("");
									wPatient.clear();
									wText.setText("");									
									wTo.setValue(new Integer[] {});
								}
							}
						} else {
							CurrentState.statusBarRemove(className);
							CurrentState.getToaster().addItem(className,
									"Failed to send message.",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				CurrentState.getToaster().addItem(className,
						"Failed to send message.", Toaster.TOASTER_ERROR);
			}
		} else {
			getProxy().Send(data, new AsyncCallback<Boolean>() {
				public void onSuccess(Boolean result) {
					CurrentState.statusBarRemove(className);
					CurrentState.getToaster().addItem(className,
							"Sent message.", Toaster.TOASTER_INFO);
					if (!sendAnother && parentScreen != null) {
						parentScreen.populate("");
						getThisObject().closeScreen();
					}
				}

				public void onFailure(Throwable t) {
					CurrentState.statusBarRemove(className);
					CurrentState.getToaster().addItem(className,
							"Failed to send message.", Toaster.TOASTER_ERROR);
				}
			});
		}
	}

	/**
	 * Internal method to retrieve proxy object from Util.getProxy()
	 * 
	 * @return
	 */
	protected MessagesAsync getProxy() {
		MessagesAsync p = null;
		try {
			p = (MessagesAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.Messages");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		return p;
	}
	
	public String getSubject(){
		return this.wSubject.getText();
	}
	public void setSubject(String subject){
		this.wSubject.setText(subject);
	}
	public String getBodyText(){
		return this.wText.getText();
	}
	public void setBodyText(String bodyText){
		this.wText.setText(bodyText);
	}
	public void setTo(Integer userId){
		wTo.setValue(new Integer[] {userId});
	}
	public void setPatient(Integer patientId){
		wPatient.setValue(patientId);
	}
}
