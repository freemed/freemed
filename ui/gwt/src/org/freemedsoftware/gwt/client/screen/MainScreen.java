/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Jeremy Allen <ieziar.jeremy <--at--> gmail.com>
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

import java.util.*;
import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Command;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.*;
import org.freemedsoftware.gwt.client.Module.*;
import org.freemedsoftware.gwt.client.widget.*;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.Frame;
import com.google.gwt.user.client.ui.*;
import com.google.gwt.user.client.rpc.*;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.DeckPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HTMLPanel;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.Hyperlink;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.MenuItem;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.VerticalSplitPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.SortableTable;

public class MainScreen extends Composite {

	private static final AppConstants CONSTANTS = (AppConstants) GWT.create(AppConstants.class);
	protected final TabPanel tabPanel;
	protected final HorizontalSplitPanel statusBarContainer;
	protected final Label statusBar1, statusBar2;
	protected final CurrentState state;
	
	public MainScreen() {
		final DockPanel mainPanel = new DockPanel();
		initWidget(mainPanel);
		mainPanel.setSize("100%", "100%");

		// Assign state
		state = new CurrentState();
		
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		mainPanel.add(horizontalPanel, DockPanel.NORTH);
		horizontalPanel.setWidth("100%");
/*
 *	Currently using the PushButton widgit for a "go back to the beginning" Button, mainly because 
 *	I couldn't set css background image to function correctly. -JA
 */

		final PushButton pushButton_1 = new PushButton("freeMED", "linkHome");
		horizontalPanel.add(pushButton_1);
		pushButton_1.setSize("67px", "40px");
		horizontalPanel.setCellWidth(pushButton_1, "40px");
		horizontalPanel.setCellHeight(pushButton_1, "100%");
		pushButton_1.setStyleName("freemed-LogoMainMenuBar");

		{
			final MenuBar menuBar = new MenuBar();
			horizontalPanel.add(menuBar);
			menuBar.setSize("100%", "40px");
			menuBar.setStylePrimaryName("freemed-MainMenuBar");
			menuBar.setStyleName("freemed-MainMenuBar");

			final MenuBar menuBar_2 = new MenuBar();
			menuBar_2.setStylePrimaryName("freemed-SecondaryMenuBar");
			menuBar_2.setStyleName("freemed-SecondaryMenuBar");

			menuBar_2.addItem("messaging", new Command() {
				public void execute() {
					final MessagingScreen p = new MessagingScreen();
					p.assignState(state);
					tabPanel.add(p, new ClosableTab("Messages", p));
					tabPanel.selectTab(tabPanel.getWidgetCount() - 1);
				}
			});

			menuBar_2.addItem("logout", (Command)null);

			final MenuItem menuItem = menuBar.addItem("<span id=\"freemed-PrimaryMenuItem-title\">system</span>", true, menuBar_2);
			menuItem.setSize("105px", "30px");
			menuItem.setStylePrimaryName("freemed-PrimaryMenuItem");
			menuItem.setStyleName("freemed-PrimaryMenuItem");

			final MenuBar menuBar_3 = new MenuBar();

			menuBar_3.addItem("search", (Command)null);

			menuBar_3.addItem("entry", (Command)null);

			final MenuItem menuItem_1 = menuBar.addItem("patient", menuBar_3);
			menuItem_1.setSize("105px", "30px");
			menuItem_1.setStyleName("freemed-PrimaryMenuItem");
		}

/*
 * 	SimplePanel to hold (hopefully) a horizontal sub menu, going to try to 
 * 	use the Menu Bar items to call each sub-menu -JA
 */
		
		tabPanel = new TabPanel();
		mainPanel.add(tabPanel, DockPanel.CENTER);
		tabPanel.setSize("100%", "100%");

		final DashboardScreen dashboard = new DashboardScreen();
		dashboard.assignState(state);
		tabPanel.add(dashboard, "Dashboard");
		tabPanel.selectTab(0);
		state.assignTabPanel(tabPanel);

		// Expand out main tabpanel to take up all extra room
		mainPanel.setCellWidth(tabPanel, "100%");
		mainPanel.setCellHeight(tabPanel, "100%");
		
		statusBarContainer = new HorizontalSplitPanel();
		mainPanel.add(statusBarContainer, DockPanel.SOUTH);
		statusBarContainer.setSize("100%", "25px");
		statusBarContainer.setSplitPosition("50%");

		statusBar1 = new Label("Ready");
		statusBar1.setStyleName("statusBar");
		statusBarContainer.add(statusBar1);
		state.assignStatusBar(statusBar1);
		statusBar2 = new Label("-");
		statusBar2.setStyleName("statusBar");
		statusBarContainer.add(statusBar2);
	}
	
	public Label getStatusBar() {
		return statusBar1;
	}
	
	public TabPanel getTabPanel() {
		return tabPanel;
	}
	
}

