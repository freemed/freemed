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
import org.freemedsoftware.gwt.client.widget.PatientInfoBar;

import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientScreen extends ScreenInterface {

	protected TabPanel tabPanel;
	
	public PatientScreen() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final PatientInfoBar patientInfoBar = new PatientInfoBar();
		verticalPanel.add(patientInfoBar);

		{
			final MenuBar menuBar = new MenuBar();
			verticalPanel.add(menuBar);

			final MenuBar menuBar_1 = new MenuBar(true);

			menuBar.addItem("New", menuBar_1);

			final MenuBar menuBar_2 = new MenuBar(true);

			menuBar_2.addItem("Billing", (Command)null);

			menuBar_2.addItem("Trending", (Command)null);

			menuBar.addItem("Reporting", menuBar_2);
		}

		final VerticalPanel verticalPanel_1 = new VerticalPanel();
		verticalPanel.add(verticalPanel_1);
		verticalPanel_1.setSize("100%", "100%");

		tabPanel = new TabPanel();
		verticalPanel_1.add(tabPanel);
		SimplePanel summary = new SimplePanel();
		tabPanel.add(summary, "Summary");
		tabPanel.selectTab(0);
	}

}
