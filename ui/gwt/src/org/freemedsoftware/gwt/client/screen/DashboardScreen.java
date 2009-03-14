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
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.widget.DocumentBox;
import org.freemedsoftware.gwt.client.widget.MessageBox;
import org.freemedsoftware.gwt.client.widget.NotesBox;
import org.freemedsoftware.gwt.client.widget.PrescriptionRefillBox;
import org.freemedsoftware.gwt.client.widget.WorkList;

import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class DashboardScreen extends ScreenInterface {

	public class DashboardItemContainer extends Composite {

		public DashboardItemContainer(String title, WidgetInterface contents) {
			final VerticalPanel container = new VerticalPanel();
			initWidget(container);
			final Label label = new Label(title);
			label.setStylePrimaryName("freemed-DashboardLabel");
			container.add(label);
			container.add(contents);
			addChildWidget(contents);
		}

	}

	protected MessageBox messageBox = new MessageBox();

	protected WorkList workList = new WorkList();

	protected String noteBoxConfig = "";

	protected PrescriptionRefillBox prescriptionRefillBox = new PrescriptionRefillBox();

	protected NotesBox notesBox = new NotesBox();

	protected DocumentBox documentBox = new DocumentBox();

	public DashboardScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final VerticalPanel widgetContainer = new VerticalPanel();
		horizontalPanel.add(widgetContainer);

		widgetContainer.add(new DashboardItemContainer("Work List", workList));
		widgetContainer.add(new DashboardItemContainer("Messages", messageBox));

		// NotesBox
		widgetContainer.add(new DashboardItemContainer("Notepad", notesBox));

		// PrescriptionRefillBox
		widgetContainer.add(new DashboardItemContainer("Prescription Refills",
				prescriptionRefillBox));

		// Unfiled documents
		widgetContainer.add(new DashboardItemContainer("Unfiled Documents",
				documentBox));
	}

	public void assignState(CurrentState s) {
		super.assignState(s);

		// Custom junk here
		if (state.getDefaultProvider() > 0) {
			workList.setProvider(state.getDefaultProvider());
		}
	}

}
