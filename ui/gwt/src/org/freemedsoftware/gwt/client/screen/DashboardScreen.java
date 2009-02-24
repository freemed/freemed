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
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.widget.MessageBox;
import org.freemedsoftware.gwt.client.widget.NotesBox;
import org.freemedsoftware.gwt.client.widget.WorkList;

import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class DashboardScreen extends ScreenInterface {

	protected WorkList workList = new WorkList();

	protected NotesBox notesBox = new NotesBox();

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
		addChildWidget(messageBox);

		// for NotesBox
		final VerticalPanel notesBoxContainer = new VerticalPanel();
		widgetContainer.add(notesBoxContainer);

		final Label notesBoxLabel = new Label("Notepad");
		notesBoxLabel.setStylePrimaryName("freemed-DashboardLabel");
		notesBoxContainer.add(notesBoxLabel);
		notesBoxContainer.add(notesBox);

		// Add widgets which need state to the stack
		addChildWidget(workList);
		addChildWidget(messageBox);
		addChildWidget(notesBox);

	}

	public void assignState(CurrentState s) {
		super.assignState(s);

		// Custom junk here
		if (state.getDefaultProvider() > 0) {
			workList.setProvider(state.getDefaultProvider());
		}
		notesBox.populateWidget();
	}

}
