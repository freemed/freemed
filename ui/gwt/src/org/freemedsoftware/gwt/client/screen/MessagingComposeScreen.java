/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.ScreenInterface;

import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MessagingComposeScreen extends ScreenInterface {

	protected final TextArea wText;

	protected final ListBox wTo;

	protected final String className = "org.freemedsoftware.gwt.client.MessagingComposeScreen";

	public MessagingComposeScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		final Label toLabel = new Label("To : ");
		flexTable.setWidget(0, 0, toLabel);

		wTo = new ListBox();
		flexTable.setWidget(0, 1, wTo);
		wTo.setMultipleSelect(true);
		wTo.setVisibleItemCount(5);

		final Label subjectLabel = new Label("Subject : ");
		flexTable.setWidget(1, 0, subjectLabel);

		final Label urgencyLabel = new Label("Urgency : ");
		flexTable.setWidget(3, 0, urgencyLabel);

		final ListBox wUrgency = new ListBox();
		flexTable.setWidget(3, 1, wUrgency);
		wUrgency.addItem("1 (Urgent)");
		wUrgency.addItem("2 (Expedited)");
		wUrgency.addItem("3 (Standard)");
		wUrgency.addItem("4 (Notification)");
		wUrgency.addItem("5 (Bulk)");
		wUrgency.setSelectedIndex(2);

		final TextBox wSubject = new TextBox();
		flexTable.setWidget(1, 1, wSubject);
		wSubject.setWidth("100%");

		final Label patientLabel = new Label("Patient : ");
		flexTable.setWidget(2, 0, patientLabel);

		final SuggestBox suggestBox = new SuggestBox();
		flexTable.setWidget(2, 1, suggestBox);

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
		sendButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				sendMessage(false);
			}
		});

		final Button sendAnotherButton = new Button();
		horizontalPanel.add(sendAnotherButton);
		sendAnotherButton.setText("Send and Compose Another");
		sendAnotherButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				sendMessage(true);
			}
		});

		final Button clearButton = new Button();
		horizontalPanel.add(clearButton);
		clearButton.setText("Clear");
		clearButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				clearForm();
			}
		});
	}

	public void clearForm() {
		wText.setText(new String(""));
	}

	public void sendMessage(boolean sendAnother) {
		state.statusBarAdd(className, "Sending Message");

	}

}
