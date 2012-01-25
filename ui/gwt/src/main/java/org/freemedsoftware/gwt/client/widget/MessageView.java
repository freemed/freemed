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
 * (at your option) any later version.s
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
import java.util.List;

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.screen.MessagingComposeScreen;
import org.freemedsoftware.gwt.client.screen.MessagingScreen;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.FocusPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class MessageView extends WidgetInterface {
	
	public final static String moduleName = MessagingScreen.moduleName;
	
	protected Command onClose = null;

	protected HTML text = new HTML("");
	
	protected Integer msgId;
	
	protected String msgSubject;
	
	protected String msgBody;
	
	protected Integer msgFromId;
	
	protected Integer msgPatientId;
	
	protected String msgDate;
	
	protected String msgFrom;
	
	protected MessagingScreen messagingScreen;
	
	public MessagingScreen getMessagingScreen() {
		return messagingScreen;
	}

	public void setMessagingScreen(MessagingScreen messagingScreen) {
		this.messagingScreen = messagingScreen;
	}

	public String getMsgSubject() {
		return msgSubject;
	}

	public void setMsgSubject(String msgSubject) {
		this.msgSubject = msgSubject;
	}
	
	public void setMessageId(Integer mid){
		this.msgId=mid;
	}

	public MessageView() {
		super(moduleName);
		final SimplePanel sPanel = new SimplePanel();
		initWidget(sPanel);
		VerticalPanel verticalPanel = new VerticalPanel();

		HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		// Button definitions:
		//
		// TODO: Replace Images by new ones
		// We don't use a Button, but an image+label inside a focusPanel,
		// because it
		// allows us, that the user can click on both to provoke the event
		//
		
		if(canWrite){
			// Reply Button
			final Image replyButton = new Image(
					"resources/images/messaging.32x32.png");
			final Label replyLabel = new Label("Reply");
			VerticalPanel replyVerticalPanel = new VerticalPanel();
			replyVerticalPanel.add(replyButton);
			replyVerticalPanel.add(replyLabel);
			replyVerticalPanel
					.setStylePrimaryName("freemed-MessageView-verticalPanelButton");
			final FocusPanel replyWrapper = new FocusPanel();
			replyWrapper.add(replyVerticalPanel);
			replyWrapper.setStylePrimaryName("freemed-MessageView-buttonWrapper");
	
			replyWrapper.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					MessagingComposeScreen messagingComposeScreen = new MessagingComposeScreen();
					messagingComposeScreen.setSubject("RE: "+msgSubject);
					messagingComposeScreen.setTo(msgFromId);
					messagingComposeScreen.setPatient(msgPatientId);
					messagingComposeScreen.setBodyText(createMessageBody());
					messagingComposeScreen.setParentScreen(getMessagingScreen());
					Util.spawnTab(_("Messages") + ":RE", messagingComposeScreen);
					if (onClose != null) {
						onClose.execute();
					}
				}
			});
			horizontalPanel.add(replyWrapper);
	
			// Forward Button
	
			final Image forwardButton = new Image(
					"resources/images/messaging.32x32.png");
			final Label forwardLabel = new Label("Forward");
			VerticalPanel forwardVerticalPanel = new VerticalPanel();
			forwardVerticalPanel.add(forwardButton);
			forwardVerticalPanel.add(forwardLabel);
			forwardVerticalPanel
					.setStylePrimaryName("freemed-MessageView-verticalPanelButton");
			final FocusPanel forwardWrapper = new FocusPanel();
			forwardWrapper.add(forwardVerticalPanel);
			forwardWrapper.setStylePrimaryName("freemed-MessageView-buttonWrapper");
	
			forwardWrapper.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					// TODO: create that
	//				Window.alert("Forward");
					MessagingComposeScreen messagingComposeScreen = new MessagingComposeScreen();
					messagingComposeScreen.setSubject("FW: "+msgSubject);
					messagingComposeScreen.setBodyText(createMessageBody());
					messagingComposeScreen.setPatient(msgPatientId);
					messagingComposeScreen.setParentScreen(getMessagingScreen());
					Util.spawnTab("Messages:FW", messagingComposeScreen);
					if (onClose != null) {
						onClose.execute();
					}
				}
			});
			horizontalPanel.add(forwardWrapper);
	
			// New Button
	
			final Image newMsgButton = new Image(
					"resources/images/messaging.32x32.png");
			final Label newMsgLabel = new Label("New");
			VerticalPanel newMsgVerticalPanel = new VerticalPanel();
			newMsgVerticalPanel.add(newMsgButton);
			newMsgVerticalPanel.add(newMsgLabel);
			newMsgVerticalPanel
					.setStylePrimaryName("freemed-MessageView-verticalPanelButton");
			final FocusPanel newMsgWrapper = new FocusPanel();
			newMsgWrapper.add(newMsgVerticalPanel);
			newMsgWrapper.setStylePrimaryName("freemed-MessageView-buttonWrapper");
	
			newMsgWrapper.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					// TODO: create that
	//				Window.alert("Forward");
					MessagingComposeScreen messagingComposeScreen = new MessagingComposeScreen();
					messagingComposeScreen.setParentScreen(getMessagingScreen());
					Util.spawnTab(_("Compose Message"), messagingComposeScreen);
					if (onClose != null) {
						onClose.execute();
					}
				}
			});
			horizontalPanel.add(newMsgWrapper);
		
		}
		// Print Button

		final Image printButton = new Image(
				"resources/images/ico.printer.32x32.png");
		final Label printLabel = new Label(_("Print"));
		VerticalPanel printVerticalPanel = new VerticalPanel();
		printVerticalPanel.add(printButton);
		printVerticalPanel.add(printLabel);
		printVerticalPanel
				.setStylePrimaryName("freemed-MessageView-verticalPanelButton");
		final FocusPanel printWrapper = new FocusPanel();
		printWrapper.add(printVerticalPanel);
		printWrapper.setStylePrimaryName("freemed-MessageView-buttonWrapper");

		printWrapper.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				List<String> reportParams = new ArrayList<String>();
				reportParams.add(""+msgId);
				Util.generateReportToPrinter(_("Email Message"), "pdf", reportParams);
			}
		});
		horizontalPanel.add(printWrapper);
		verticalPanel.add(text);
		sPanel.add(verticalPanel);

	}

	public String createMessageBody(){
		String body="\n\n--------------------------\n"
		           +"From: "+msgFrom+"\n"
		           +"Date: "+msgDate+"\n"
		           +"Subject: "+msgSubject+"\n\n\n"
		           +msgBody;
		
		return body;
	}
	public String createMessageHtml(String from,String date,String subject,String body){		
		return "From: "+from+"<br>"
		           +"Date: "+date+"<br>"
		           +"Subject: "+subject+"<br><br><br>"
		           +body;
	}
	
	public void setText(String t) {
		text.setHTML(t);
	}
	
	public void setOnClose(Command c) {
		onClose = c;
	}

	public String getMsgBody() {
		return msgBody;
	}

	public void setMsgBody(String msgBody) {
		this.msgBody = msgBody;
	}

	public Integer getMsgPatientId() {
		return msgPatientId;
	}

	public void setMsgPatientId(Integer msgPatientId) {
		this.msgPatientId = msgPatientId;
	}

	public Integer getMsgFromId() {
		return msgFromId;
	}

	public void setMsgFromId(Integer msgFromId) {
		this.msgFromId = msgFromId;
	}

	public String getMsgDate() {
		return msgDate;
	}

	public void setMsgDate(String msgDate) {
		this.msgDate = msgDate;
	}

	public String getMsgFrom() {
		return msgFrom;
	}

	public void setMsgFrom(String msgFrom) {
		this.msgFrom = msgFrom;
	}

}
