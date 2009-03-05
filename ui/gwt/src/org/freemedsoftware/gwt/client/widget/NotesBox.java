/*
 * $Id$
 *
 * Authors:
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
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.WidgetInterface;

import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class NotesBox extends WidgetInterface {

	public class Note extends Composite implements ClosableTabInterface,
			ChangeListener {

		protected Integer myKey = 0;

		protected TextArea textArea;
		protected Widget widget;
		protected String name;
		protected Label label = new Label("");

		public Note() {
			SimplePanel simplePanel = new SimplePanel();
			initWidget(simplePanel);

			textArea = new TextArea();

			VerticalPanel vPanel = new VerticalPanel();
			simplePanel.setWidget(vPanel);

			HorizontalPanel hPanelInner = new HorizontalPanel();

			PushButton button = new PushButton(new Image(
					"resources/images/close_x.16x16.png"));
			vPanel.add(hPanelInner);
			hPanelInner.add(label);
			hPanelInner.add(button);
			vPanel.add(textArea);
			textArea.setSize("100%", "100%");
			textArea.setStyleName("freemed-NotesBox-textarea");

			simplePanel.setSize("100%", "100%");

			simplePanel.addStyleName("freemed-NotesBoxContainer");

			textArea.addChangeListener(this);

			button.addClickListener(new ClickListener() {
				public void onClick(Widget w) {
					onClose();
				}
			});

		}

		public void setKey(Integer key) {
			myKey = key;
		}

		public TextArea getTextArea() {
			return textArea;
		}

		public void onClose() {
			// Kill it as we close it ...
			notes.remove(myKey);
			// ... save/update ...
			saveContent();
			widget.removeFromParent();
		}

		public void setName(String s) {
			name = s;
			label.setText(name);
		}

		public void setWidget(Widget w) {
			widget = w;
		}

		public void onChange(Widget sender) {
			saveContent();
		}

		public boolean isReadyToClose() {
			return true;
		}

	}

	protected TextArea textArea;

	protected Command command;

	protected HorizontalPanel hPanel = new HorizontalPanel();

	protected HashMap<Integer, Note> notes = new HashMap<Integer, Note>();

	public NotesBox() {
		final SimplePanel simplePanel = new SimplePanel();
		final VerticalPanel verticalPanel = new VerticalPanel();
		simplePanel.setStyleName("freemed-NotesBoxContainer");
		initWidget(simplePanel);
		simplePanel.setWidget(verticalPanel);
		PushButton addButton = new PushButton();
		HorizontalPanel addButtonLayout = new HorizontalPanel();
		addButtonLayout.add(new Image("resources/images/add_plus.16x16.png"));
		addButtonLayout.add(new Label("Add Note"));
		addButton.getUpFace().setHTML(addButtonLayout.toString());
		addButton.addClickListener(new ClickListener() {
			public void onClick(Widget sender) {
				addNote("");
			}
		});
		verticalPanel.add(addButton);
		// verticalPanel.add(tabPanel);
		verticalPanel.add(hPanel);
	}

	public void setState(CurrentState currentState) {
		super.setState(currentState);
		JsonUtil.debug("NotesBox.setState() called");

		// NotesBox
		String oNote = null;
		String[] aNote = {};
		oNote = state.getUserConfig("notepad");

		if (notes.size() == 0) {
			if (oNote != "") {
				aNote = (String[]) JsonUtil.shoehornJson(JSONParser
						.parse(oNote), "String[]");
				for (int i = 0; i < aNote.length; i++) {
					if (aNote[i] != "") {
						addNote(aNote[i]);
					}
				}
			} else {
				addNote("Edit this note!");
			}
		}
	}

	public void saveContent() {
		Iterator<Note> iter = notes.values().iterator();
		List<String> a = new ArrayList<String>();
		while (iter.hasNext()) {
			Note t = iter.next();
			a.add(t.getTextArea().getText());
		}
		state.setUserConfig("notepad", (String) JsonUtil.jsonify(a
				.toArray(new String[0])));
	}

	public void addNote(String text) {
		Integer idx = notes.size();
		// If it's used, skip ahead
		while (notes.get(idx) != null) {
			idx++;
		}

		Note n = new Note();
		n.setKey(idx);
		n.getTextArea().setText(text);
		notes.put(idx, n);

		n.setWidget(n);
		n.setName("Note #" + (idx + 1) + " ");
		hPanel.add(n);

	}

}
