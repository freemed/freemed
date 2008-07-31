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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.FreemedInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.MenuItem;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;

public class MainScreen extends Composite {

	private static final AppConstants CONSTANTS = (AppConstants) GWT
			.create(AppConstants.class);

	private MenuBar menuBar_1;

	protected FreemedInterface freemedInterface;

	protected final TabPanel tabPanel;

	protected final HorizontalSplitPanel statusBarContainer;

	protected final Label statusBar1, statusBar2;

	protected final CurrentState state;

	public MainScreen() {
		final DockPanel mainPanel = new DockPanel();
		initWidget(mainPanel);
		mainPanel.setSize("98%", "98%");
		// mainPanel.setHeight(new Integer(Window.getClientHeight() -
		// 5).toString());

		// Assign state
		state = new CurrentState();

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		mainPanel.add(horizontalPanel, DockPanel.NORTH);
		horizontalPanel.setWidth("100%");
		/*
		 * Currently using the PushButton widget for a "go back to the
		 * beginning" Button, mainly because I couldn't set css background image
		 * to function correctly. -JA
		 */

		final PushButton pushButton_1 = new PushButton();
		horizontalPanel.add(pushButton_1);
		pushButton_1.setSize("67px", "40px");
		horizontalPanel.setCellWidth(pushButton_1, "40px");
		horizontalPanel.setCellHeight(pushButton_1, "100%");
		pushButton_1.setStyleName("freemed-LogoMainMenuBar");
		/*
		 * Start of the Main menu/toolbar. CSS inherits are VERY important, see
		 * the stylesheet.css for clearer explanation
		 */
		{
			final MenuBar menuBar = new MenuBar();
			horizontalPanel.add(menuBar);
			menuBar.setSize("100%", "40px");
			menuBar.setStylePrimaryName("freemed-MainMenuBar");

			menuBar_1 = new MenuBar();
			menuBar_1.setAutoOpen(true);
			menuBar_1.setStylePrimaryName("freemed-SecondaryMenuBar");
			menuBar_1.setStyleName("freemed-SecondaryMenuBar");

			final MenuItem menuItem_2 = menuBar_1.addItem("messaging",
					new Command() {
						public void execute() {
							Util.spawnTab("Messages", new MessagingScreen(),
									state);
						}
					});
			menuItem_2.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_3 = menuBar_1.addItem("logout",
					new Command() {
						public void execute() {
							try {
								LoginAsync service = (LoginAsync) Util
										.getProxy("org.freemedsoftware.gwt.client.Public.Login");
								service.Logout(new AsyncCallback<Void>() {
									public void onSuccess(Void r) {
										hide();
										freemedInterface.getLoginDialog()
												.center();
										//freemedInterface.getLoginDialog().show
										// ();
									}

									public void onFailure(Throwable t) {
										Window.alert("Failed to log out.");
									}
								});
							} catch (Exception e) {
								Window
										.alert("Could not create proxy for Login");
							}
						}
					});
			menuItem_3.setStyleName("freemed-SecondaryMenuItem");
			/*
			 * all the primary menu items are currently housed in static
			 * width/height since css backgrounds are batched buttons. This
			 * definitely needs to be worked on. hopefully using a more fluid
			 * css technique. probably will require more in code html markup.
			 */
			final MenuItem menuItem = menuBar.addItem(
					"<span id=\"freemed-PrimaryMenuItem-title\">system</span>",
					true, menuBar_1);
			menuItem.setSubMenu(menuBar_1);
			menuItem.setSize("105px", "30px");
			menuItem.setStylePrimaryName("freemed-PrimaryMenuItem");

			final MenuBar menuBar_3 = new MenuBar();
			menuBar_3.setAutoOpen(true);
			menuBar_3.setStyleName("freemed-SecondaryMenuBar");

			final MenuItem menuItem_4 = menuBar_3.addItem("search",
					new Command() {
						public void execute() {
							Util.spawnTab("Search", new PatientSearchScreen(),
									state);
						}
					});
			menuItem_4.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_5 = menuBar_3.addItem("entry",
					(Command) null);
			menuItem_5.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_1 = menuBar
					.addItem(
							"<span id=\"freemed-PrimaryMenuItem-title\">patient</span>",
							true, menuBar_3);
			menuItem_1.setSize("105px", "30px");
			menuItem_1.setStyleName("freemed-PrimaryMenuItem");
		}

		/*
		 * SimplePanel to hold (hopefully) a horizontal sub menu, going to try
		 * to use the Menu Bar items to call each sub-menu -JA
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
		if (Util.isStubbedMode()) {
			statusBar2.setText("STUBBED / TEST MODE");
		}

		// Create notification toaster
		Toaster toaster = new Toaster();
		state.assignToaster(toaster);
		toaster.setTimeout(10);
	}

	public Label getStatusBar() {
		return statusBar1;
	}

	public TabPanel getTabPanel() {
		return tabPanel;
	}

	public void hide() {
		RootPanel.setVisible(RootPanel.get("rootPanel").getElement(), false);
	}

	public void setFreemedInterface(FreemedInterface i) {
		freemedInterface = i;
	}

	public void show() {
		RootPanel.setVisible(RootPanel.get("rootPanel").getElement(), true);
	}

}
