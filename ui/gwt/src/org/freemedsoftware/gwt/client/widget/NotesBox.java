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

import org.freemedsoftware.gwt.client.WidgetInterface;

import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.Widget;

public class NotesBox extends WidgetInterface {

	protected TextArea textArea;

	public NotesBox() {

		final SimplePanel simplePanel = new SimplePanel();
		initWidget(simplePanel);
		simplePanel
				.setStyleName("freemed-PatientSummaryContainer, .freemed-MessageBoxContainer, .freemed-NotesBoxContainer");
		simplePanel.setSize("100%", "100%");

		simplePanel.addStyleName("freemed-NotesBoxContainer");

		textArea = new TextArea();
		simplePanel.setWidget(textArea);
		textArea.setSize("100%", "100%");
		textArea.setStyleName("freemed-NotesBox-textarea");

		textArea.addChangeListener(new ChangeListener() {
			public void onChange(final Widget sender) {
				state.setUserConfig("notepad", textArea.getText());
			}
		
		});

	}

	protected void onLoad() {
		// Load initial Data
		super.onLoad();

		String text = state.getUserConfig("notepad");

		if (text == "") {
			text = "Enter your here your notes...";
		}
		textArea.setText(text);
	}

}
