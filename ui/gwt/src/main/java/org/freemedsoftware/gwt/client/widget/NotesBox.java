/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
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
			ChangeHandler {

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

			simplePanel.setStyleName(AppConstants.STYLE_BUTTON_WIDGETS_CONTAINER );
			simplePanel.addStyleName("freemed-NotesBoxContainer");

			textArea.addChangeHandler(this);

			button.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent wvt) {
					onClose();
				}
			});

		}

		public void setKey(Integer key) {
			myKey = key;
		}

		public void setText(String s) {
			textArea.setText(s);
			setName();
		}

		public void onClose() {
			// Kill it as we close it ...
			notes.remove(myKey);
			// ... save/update ...
			saveContent();
			widget.removeFromParent();
		}

		public void setName() {
			// String s) {
			// name = s;
			// label.setText(name);

			name = textArea.getText();
			if (name.length() != 0) {
				int pos = name.indexOf(" ", name.indexOf(" ",
						name.indexOf(" ") + 1) + 1);
				if (pos != -1) {
					name = name.substring(0, pos);
				}
				label.setText(name);

			} else {
				name = _("Note") + " #" + (myKey + 1) + " ";
				label.setText(name);
			}

		}

		public void setWidget(Widget w) {
			widget = w;
		}

		public boolean isReadyToClose() {
			return true;
		}

		public TextArea getTextArea() {
			return textArea;
		}

		@Override
		public void onChange(ChangeEvent event) {
			saveContent();
			setName();
		}

	}

	protected TextArea textArea;

	protected Command command;

	protected HorizontalPanel hPanel = new HorizontalPanel();

	protected String emptyNoteText = "Edit this note!";

	protected HashMap<Integer, Note> notes = new HashMap<Integer, Note>();

	public NotesBox() {
		final SimplePanel simplePanel = new SimplePanel();
		final VerticalPanel verticalPanel = new VerticalPanel();
		simplePanel.setStyleName("freemed-NotesBoxContainer");
		initWidget(simplePanel);
		simplePanel.setWidget(verticalPanel);
		PushButton addButton = new PushButton();
		HorizontalPanel addButtonLayout = new HorizontalPanel();
		addButtonLayout.setStylePrimaryName("freemed-NotesBoxContainer");
		addButtonLayout.add(new Image("resources/images/add_plus.16x16.png"));
		addButtonLayout.add(new Label(_("Add Note")));
		addButton.getUpFace().setHTML(addButtonLayout.toString());
		addButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				addNote("");
			}
		});
		verticalPanel.add(addButton);
		// verticalPanel.add(tabPanel);
		verticalPanel.add(hPanel);

		String oNote = null;
		String[] aNote = {};
		if(CurrentState.getUserConfig("notepad")!=null)
			oNote = CurrentState.getUserConfig("notepad").toString();

		if (oNote != "") {
			aNote = (String[]) JsonUtil.shoehornJson(JSONParser.parseStrict(oNote),
					"String[]");
			if (aNote.length != 0) {
				for (int i = 0; i < aNote.length; i++) {
					if (aNote[i] != "") {
						addNote(aNote[i]);
					}
				}
			} else {
				addNote(emptyNoteText);
			}
		} else {
			addNote(emptyNoteText);
		}
		JsonUtil.debug("NotesBox.setState() finished");
	}

	public void saveContent() {
		Iterator<Note> iter = notes.values().iterator();
		List<String> a = new ArrayList<String>();
		while (iter.hasNext()) {
			Note t = iter.next();
			a.add(t.getTextArea().getText());
		}
		CurrentState.setUserConfig("notepad", (String) JsonUtil.jsonify(a
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
		n.setText(text);
		notes.put(idx, n);

		n.setWidget(n);
		// n.setName("Note #" + (idx + 1) + " ");
		hPanel.add(n);
	}

}
