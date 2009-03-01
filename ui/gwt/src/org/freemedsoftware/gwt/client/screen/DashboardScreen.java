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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.widget.MessageBox;
import org.freemedsoftware.gwt.client.widget.NotesBox;
import org.freemedsoftware.gwt.client.widget.PrescriptionRefillBox;
import org.freemedsoftware.gwt.client.widget.WorkList;

import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class DashboardScreen extends ScreenInterface {

	protected WorkList workList = new WorkList();

	protected String noteBoxConfig = "";

	protected PrescriptionRefillBox prescriptionRefillBox = new PrescriptionRefillBox();

	protected NotesBox[] objectholder = { new NotesBox() };
	protected VerticalPanel[] vpanelholder = { new VerticalPanel() };
	protected Label[] labelholder = { new Label("NotePad") };
	protected HorizontalPanel[] hpanelholder = { new HorizontalPanel() };
	protected PushButton[] addbuttonholder = { new PushButton() };
	protected PushButton[] delbuttonholder = { new PushButton() };

	protected VerticalPanel notesBoxContainer = new VerticalPanel();

	protected Integer overrideIndex = null;

	protected String emptyNoteBoxMessage = "Enter your notes here";

	protected Integer holderIndex;

	public DashboardScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final VerticalPanel widgetContainer = new VerticalPanel();
		horizontalPanel.add(widgetContainer);

		horizontalPanel.add(widgetContainer);

		final VerticalPanel workListContainer = new VerticalPanel();
		final Label workListLabel = new Label("Work List");
		workListLabel.setStylePrimaryName("freemed-DashboardLabel");

		workListContainer.add(workListLabel);
		workListContainer.add(workList);

		widgetContainer.add(workListContainer);

		final VerticalPanel messageBoxContainer = new VerticalPanel();
		final Label messageBoxLabel = new Label("Messages");
		messageBoxLabel.setStylePrimaryName("freemed-DashboardLabel");
		final MessageBox messageBox = new MessageBox();

		messageBoxContainer.add(messageBoxLabel);
		messageBoxContainer.add(messageBox);

		widgetContainer.add(messageBoxContainer);

		// NoteBox
		widgetContainer.add(notesBoxContainer);

		// PrescriptionRefillBox

		final VerticalPanel prescriptionRefillBoxContainer = new VerticalPanel();
		final Label prescriptionRefillBoxContainerLabel = new Label(
				"Prescription Refills");
		prescriptionRefillBoxContainerLabel
				.setStylePrimaryName("freemed-DashboardLabel");
		prescriptionRefillBoxContainer.add(prescriptionRefillBoxContainerLabel);
		prescriptionRefillBoxContainer.add(prescriptionRefillBox);
		widgetContainer.add(prescriptionRefillBoxContainer);

		// Add widgets which need state to the stack
		addChildWidget(workList);
		addChildWidget(messageBox);

		addChildWidget(prescriptionRefillBox);
	}

	public void assignState(CurrentState s) {
		super.assignState(s);

		// Custom junk here
		if (state.getDefaultProvider() > 0) {
			workList.setProvider(state.getDefaultProvider());
		}
		// if (objectholder.length != 0) {
		// for (int i = 0; i < objectholder.length; i++) {
		// objectholder[i].populateWidget();
		// }
		// }

		// NotesBox
		String oNote = null;
		String[] aNote = {};
		oNote = state.getUserConfig("notepad");

		if (oNote != "") {
			aNote = (String[]) JsonUtil.shoehornJson(JSONParser.parse(oNote),
					"String[]");
		} else {
			aNote[0] = emptyNoteBoxMessage;
		}

		// Add at least one Notebox

		// First notebox shall have index 0

		for (int i = 0; i < aNote.length; i++) {
			if (aNote[i] != "") {
				addNoteBox(aNote[i]);
			}
		}

	}

	public void delNotesBox(Integer key) {
		vpanelholder[key].removeFromParent();
		objectholder[key].removeFromParent();
		objectholder[key] = null;
		// delFromArray(objectholder, (key));
	}

	public void saveContent() {
		String[] array = {};
		Object o = null;
		Integer index = 0;
		for (int i = 0; i < objectholder.length; i++) {
			if (objectholder[i] != null) {
				array[index] = objectholder[i].getText();
			}
			index++;
		}

		o = JsonUtil.jsonify(array);

		state.setUserConfig("notepad", o);
	}

	public NotesBox[] delFromArray(NotesBox[] array, Integer key) {
		Integer index = 0;
		NotesBox[] a = {};
		for (int i = 0; i < array.length; i++) {
			if (i != key) {
				a[index] = array[i];
			}
			index++;
		}
		return a;
	}

	public void setIndex(Integer i) {
		overrideIndex = i;
	}

	public void addNoteBox(String text) {
		holderIndex = objectholder.length - 1;
		if (overrideIndex != null) {
			holderIndex = overrideIndex;
			setIndex(null);
		}

		objectholder[holderIndex] = new NotesBox();
		objectholder[holderIndex].setCommand(new Command() {
			public void execute() {
				saveContent();
			}
		});

		// VerticalPanel container = new VerticalPanel();
		// Label notesBoxLabel = new Label("Notepad");
		labelholder[holderIndex] = new Label("NotePad");
		labelholder[holderIndex].setStylePrimaryName("freemed-DashboardLabel");
		// notesBoxLabel.setStylePrimaryName("freemed-DashboardLabel");
		vpanelholder[holderIndex] = new VerticalPanel();
		hpanelholder[holderIndex] = new HorizontalPanel();

		vpanelholder[holderIndex].add(hpanelholder[holderIndex]);
		hpanelholder[holderIndex].add(labelholder[holderIndex]);

		if (holderIndex == 0) {

			addbuttonholder[holderIndex] = new PushButton("add", "add");
			delbuttonholder[holderIndex] = new PushButton("del", "del");

			hpanelholder[holderIndex].add(addbuttonholder[holderIndex]);
			vpanelholder[holderIndex].add(delbuttonholder[holderIndex]);

			addbuttonholder[holderIndex].getUpFace().setImage(
					new Image("resources/images/add_plus.16x16.png"));
			delbuttonholder[holderIndex].getUpFace().setImage(
					new Image("resources/images/close_x.16x16.png"));

			addbuttonholder[holderIndex].addClickListener(new ClickListener() {
				public void onClick(Widget sender) {
					addNoteBox(emptyNoteBoxMessage);
				}

			});

			final Command c = new Command() {
				public void execute() {
					delNotesBox(holderIndex);
				}
			};
			delbuttonholder[holderIndex].addClickListener(new ClickListener() {

				public void onClick(Widget sender) {
					c.execute();
				}
			});

		}

		vpanelholder[holderIndex].add((Widget) objectholder[holderIndex]);
		// container.add(notesBoxLabel);

		addChildWidget((WidgetInterface) objectholder[holderIndex]);

		notesBoxContainer.add(vpanelholder[holderIndex]);
		objectholder[holderIndex].populateWidget(text);
	}

}
