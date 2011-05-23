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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.MessagesAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
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
	protected SupportModuleWidget wGroupTo;

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

		final HorizontalPanel toPanel = new HorizontalPanel();
		toPanel.setWidth("100%");
		flexTable.setWidget(0, 1, toPanel);
		
		final Label userLabel = new Label("user:");
		toPanel.add(userLabel);
		wTo = new UserMultipleChoiceWidget();
		toPanel.add(wTo);
		
		final Label groupLabel = new Label("group:");
		toPanel.add(groupLabel);
		wGroupTo = new SupportModuleWidget("UserGroups");
		toPanel.add(wGroupTo);
		
		

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

		final CustomButton sendButton = new CustomButton("Send",AppConstants.ICON_SEND);
		horizontalPanel.add(sendButton);
		sendButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if(validateForm())
					sendMessage(false);
			}
		});

		final CustomButton sendAnotherButton = new CustomButton("Send and Compose Another",AppConstants.ICON_COMPOSE_MAIL);
		horizontalPanel.add(sendAnotherButton);
		sendAnotherButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if(validateForm())
					sendMessage(true);
			}
		});

		final CustomButton clearButton = new CustomButton("Clear",AppConstants.ICON_CLEAR);
		horizontalPanel.add(clearButton);
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

	protected boolean validateForm() {
		String msg = new String("");
		if (wTo.getCommaSeparatedValues().equals("") &&
				wGroupTo.getStoredValue().equals("0")) {
			msg += "Please specify at least one recipient or a group." + "\n";
		}
		if (wSubject.getText().trim().length() == 0) {
			msg += "Please specify subject." + "\n";
		}
		if (msg.length()>0) {
			Window.alert(msg);
			return false;
		}

		return true;
	}
	
	public void sendMessage(final boolean sendAnother) {
		CurrentState.statusBarAdd(className, "Sending Message");

		// Form data
		HashMap<String, String> data = new HashMap<String, String>();
		if(wPatient.getValue()!=null)
			data.put("patient", wPatient.getValue().toString());
		if(wTo.getCommaSeparatedValues()!=null)
			data.put("for", wTo.getCommaSeparatedValues());
		if(wGroupTo.getStoredValue()!=null)
			data.put("group", wGroupTo.getStoredValue());
		data.put("text", wText.getText());
		data.put("subject", wSubject.getText());
		data.put("urgency", wUrgency.getWidgetValue());

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			CurrentState.statusBarRemove(className);
			Util.showInfoMsg(className, "Message Sent.");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(data) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.Messages.Send", params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.statusBarRemove(className);
						Util.showErrorMsg(className, "Failed to send message.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[] r = (String[]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[]");
							if (r != null) {
								CurrentState.statusBarRemove(className);
								Util.showInfoMsg(className, "Message Sent.");
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
							Util.showErrorMsg(className, "Failed to send message.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg(className, "Failed to send message.");
			}
		} else {
			getProxy().Send(data, new AsyncCallback<Boolean>() {
				public void onSuccess(Boolean result) {
					CurrentState.statusBarRemove(className);
					Util.showErrorMsg(className, "Failed to send message.");
					if (!sendAnother && parentScreen != null) {
						parentScreen.populate("");
						getThisObject().closeScreen();
					}
				}

				public void onFailure(Throwable t) {
					CurrentState.statusBarRemove(className);
					Util.showErrorMsg(className, "Failed to send message.");
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
