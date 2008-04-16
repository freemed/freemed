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

package org.freemedsoftware.gwt.client;

import java.util.*;
import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Command;
import org.freemedsoftware.gwt.client.*;
import org.freemedsoftware.gwt.client.Module.*;
import org.freemedsoftware.gwt.client.widget.*;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.*;
import com.google.gwt.user.client.rpc.*;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.MenuItem;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.StackPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.VerticalSplitPanel;
import com.thapar.gwt.user.ui.client.widget.SortableTable;

public class MainScreen extends Composite {

	protected final TabPanel tabPanel;
	protected final HorizontalSplitPanel statusBarContainer;
	protected final Label statusBar1, statusBar2;
	
	public MainScreen() {
		final DockPanel mainPanel = new DockPanel();
		initWidget(mainPanel);
		mainPanel.setSize("100%", "100%");
		
		{
			final MenuBar menuBar = new MenuBar();
			mainPanel.add(menuBar, DockPanel.NORTH);
			menuBar.setHeight("1.5em");

			final MenuBar menuBar_1 = new MenuBar(true);

			menuBar.addItem("System", menuBar_1);

			menuBar_1.addItem("Messages", new Command() {
				public void execute() {
					final Messaging p = new Messaging();
					tabPanel.add(p, new ClosableTab("Messages", p));					
				}
			});

			final MenuItem menuItem = menuBar_1.addItem("Logout", new Command() {
				public void execute() {
					
				}
			});

			final MenuBar menuBar_2 = new MenuBar(true);

			menuBar_2.addItem("Search", new Command() {
				public void execute() {
					
				}
			});

			menuBar_2.addItem("Entry", new Command() {
				public void execute() {
					
				}
			});

			menuBar.addItem("Patient", menuBar_2);
		}
		
		statusBarContainer = new HorizontalSplitPanel();
		mainPanel.add(statusBarContainer, DockPanel.SOUTH);
		statusBarContainer.setSize("100%", "25px");
		statusBarContainer.setSplitPosition("50%");

		statusBar1 = new Label("Ready");
		statusBar1.setStyleName("statusBar");
		statusBarContainer.add(statusBar1);
		statusBar2 = new Label("-");
		statusBar2.setStyleName("statusBar");
		statusBarContainer.add(statusBar2);
		
		tabPanel = new TabPanel();
		mainPanel.add(tabPanel, DockPanel.CENTER);
		tabPanel.setSize("100%", "100%");
		final HTML dashboard = new HTML("Dashboard");
		dashboard.setSize("100%", "100%");
		tabPanel.add(dashboard, "Dashboard");
		tabPanel.selectTab(0);

		// Expand out main tabpanel to take up all extra room
		mainPanel.setCellWidth(tabPanel, "100%");
		mainPanel.setCellHeight(tabPanel, "100%");
	}
	
}

