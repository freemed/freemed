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
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.SystemNotifications;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.InfoDialog;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.MenuItem;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.UIObject;

public class MainScreen extends Composite {

	private static final AppConstants CONSTANTS = (AppConstants) GWT
			.create(AppConstants.class);

	private MenuBar menuBar_1;

	protected FreemedInterface freemedInterface;

	protected final TabPanel tabPanel;

	protected final HorizontalSplitPanel statusBarContainer;

	protected final Label statusBar1, statusBar2;

	protected final CurrentState state = new CurrentState();

	protected final SystemNotifications notifications = new SystemNotifications();

	protected DashboardScreen dashboard = null;

	public MainScreen() {
		final DockPanel mainPanel = new DockPanel();
		initWidget(mainPanel);
		mainPanel.setSize("98%", "98%");

		populateDefaultProvider();

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

			final MenuItem menuItem_7 = menuBar_1.addItem("config",
					new Command() {
						public void execute() {
							Util.spawnTab("Configuration",
									new ConfigurationScreen(), state);
						}
					});
			menuItem_7.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_3 = menuBar_1.addItem("logout",
					new Command() {
						public void execute() {
							if (Util.getProgramMode() == ProgramMode.STUBBED) {

							} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
								String[] params = {};
								RequestBuilder builder = new RequestBuilder(
										RequestBuilder.POST,
										URL
												.encode(Util
														.getJsonRequest(
																"org.freemedsoftware.public.Login.Logout",
																params)));
								try {
									builder.sendRequest(null,
											new RequestCallback() {
												public void onError(
														com.google.gwt.http.client.Request request,
														Throwable ex) {
													GWT.log("Exception", ex);
													Window
															.alert("Failed to log out.");
												}

												public void onResponseReceived(
														com.google.gwt.http.client.Request request,
														com.google.gwt.http.client.Response response) {
													if (200 == response
															.getStatusCode()) {
														hide();
														UIObject
																.setVisible(
																		RootPanel
																				.get(
																						"loginScreenOuter")
																				.getElement(),
																		true);
														freemedInterface
																.getLoginDialog()
																.center();
													} else {
														Window
																.alert("Failed to log out.");
													}
												}
											});
								} catch (RequestException e) {
									GWT.log("Exception", e);
									Window.alert("Failed to log out.");
								}

							} else {
								try {
									LoginAsync service = (LoginAsync) Util
											.getProxy("org.freemedsoftware.gwt.client.Public.Login");
									service.Logout(new AsyncCallback<Void>() {
										public void onSuccess(Void r) {
											hide();
											UIObject.setVisible(RootPanel.get(
													"loginScreenOuter")
													.getElement(), true);
											freemedInterface.getLoginDialog()
													.center();
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
					new Command() {
						public void execute() {
							Util.spawnTab("New Patient", new PatientForm(),
									state);
						}
					});
			menuItem_5.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_6 = menuBar_3.addItem("tags",
					new Command() {
						public void execute() {
							Util.spawnTab("Tag Search",
									new PatientTagSearchScreen(), state);
						}
					});
			menuItem_6.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_1 = menuBar
					.addItem(
							"<span id=\"freemed-PrimaryMenuItem-title\">patient</span>",
							true, menuBar_3);
			menuItem_1.setSize("105px", "30px");
			menuItem_1.setStyleName("freemed-PrimaryMenuItem");

			// Document management bar
			final MenuBar menuBar_document = new MenuBar();
			menuBar_document.setAutoOpen(true);
			menuBar_document.setStyleName("freemed-SecondaryMenuBar");
			final MenuItem menuItem_document = menuBar
					.addItem(
							"<span id=\"freemed-PrimaryMenuItem-title\">documents</span>",
							true, menuBar_document);
			menuItem_document.setSize("105px", "30px");
			menuItem_document.setStyleName("freemed-PrimaryMenuItem");
			final MenuItem menuItem_unfiled = menuBar_document.addItem(
					"unfiled", new Command() {
						public void execute() {
							Util.spawnTab("Unfiled Documents",
									new UnfiledDocuments(), state);
						}
					});
			menuItem_unfiled.setStyleName("freemed-SecondaryMenuItem");

			// Support functionality
			final MenuBar menuBar_report = new MenuBar();
			menuBar_report.setAutoOpen(true);
			menuBar_report.setStyleName("freemed-SecondaryMenuBar");
			final MenuItem menuItemBar_report = menuBar
					.addItem(
							"<span id=\"freemed-PrimaryMenuItem-title\">reporting</span>",
							true, menuBar_report);
			menuItemBar_report.setSize("105px", "30px");
			menuItemBar_report.setStyleName("freemed-PrimaryMenuItem");
			final MenuItem menuItem_report = menuBar_report.addItem(
					"reporting", new Command() {
						public void execute() {
							Util.spawnTab("Reporting", new ReportingScreen(),
									state);
						}
					});
			menuItem_report.setStyleName("freemed-SecondaryMenuItem");

			// Data functionality
			final MenuBar menuBar_data = new MenuBar();
			menuBar_data.setAutoOpen(true);
			menuBar_data.setStyleName("freemed-SecondaryMenuBar");
			final MenuItem menuItemBar_data = menuBar
					.addItem(
							"<span id=\"freemed-PrimaryMenuItem-title\">data entry</span>",
							true, menuBar_data);
			menuItemBar_data.setSize("105px", "30px");
			menuItemBar_data.setStyleName("freemed-PrimaryMenuItem");
			final MenuItem menuItem_data = menuBar_data.addItem("support data",
					new Command() {
						public void execute() {
							Util.spawnTab("Support Data",
									new SupportDataScreen(), state);
						}
					});
			menuItem_data.setStyleName("freemed-SecondaryMenuItem");

			// Support functionality
			final MenuBar menuBar_support = new MenuBar();
			menuBar_support.setAutoOpen(true);
			menuBar_support.setStyleName("freemed-SecondaryMenuBar");
			final MenuItem menuItemBar_support = menuBar
					.addItem(
							"<span id=\"freemed-PrimaryMenuItem-title\">support</span>",
							true, menuBar_support);
			menuItemBar_support.setSize("105px", "30px");
			menuItemBar_support.setStyleName("freemed-PrimaryMenuItem");

			final MenuItem menuItem_communitySupport = menuBar_support.addItem(
					"community support", new Command() {
						public void execute() {
							InfoDialog d = new InfoDialog();
							d.setCaption("Community Support");
							d
									.setContent(new HTML(
											"TODO: describe community support blurb here."));
							d.center();
						}
					});
			menuItem_communitySupport.setStyleName("freemed-SecondaryMenuItem");

			final MenuItem menuItem_commercialSupport = menuBar_support
					.addItem("commercial support", new Command() {
						public void execute() {
							InfoDialog d = new InfoDialog();
							d.setCaption("Commercial Support");
							d
									.setContent(new HTML(
											"Commercial support is available for <b>FreeMED</b> through the Foundation's commercial support partners."
													+ "<br/><br/>"
													+ "More information is available at <a href=\"http://freemedsoftware.org/commercial_support\" target=\"_new\">http://freemedsoftware.org/commercial_support</a>."));
							d.center();
						}
					});
			menuItem_commercialSupport
					.setStyleName("freemed-SecondaryMenuItem");
		}

		/*
		 * SimplePanel to hold (hopefully) a horizontal sub menu, going to try
		 * to use the Menu Bar items to call each sub-menu -JA
		 */

		tabPanel = new TabPanel();
		mainPanel.add(tabPanel, DockPanel.CENTER);
		tabPanel.setSize("100%", "100%");

		dashboard = new DashboardScreen();
		dashboard.assignState(state);
		tabPanel.add(dashboard, "Dashboard");
		tabPanel.selectTab(0);
		state.assignTabPanel(tabPanel);

		// Expand out main tabpanel to take up all extra room
		mainPanel.setCellWidth(tabPanel, "100%");
		mainPanel.setCellHeight(tabPanel, "100%");

		statusBarContainer = new HorizontalSplitPanel();
		mainPanel.add(statusBarContainer, DockPanel.SOUTH);
		statusBarContainer.setSize("100%", "30px");
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

		// Handle system notifications
		notifications.setState(getCurrentState());
		notifications.start();
	}

	public CurrentState getCurrentState() {
		return state;
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

	public void populateDefaultProvider() {
		if (state.getDefaultProvider().intValue() < 1) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// Do gornicht.
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = {};
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.UserInterface.GetCurrentProvider",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							state
									.getToaster()
									.addItem(
											"MainScreen",
											"Could not determine provider information.",
											Toaster.TOASTER_ERROR);
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != null) {
									JsonUtil
											.debug("MainScreen.populateDefaultProvider: found "
													+ r.toString());
									state.assignDefaultProvider(r);
									dashboard.assignState(state);
								} else {
									JsonUtil
											.debug("MainScreen.populateDefaultProvider: found error");
								}
							} else {
								state
										.getToaster()
										.addItem(
												"MainScreen",
												"Could not determine provider information.",
												Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					state.getToaster().addItem("MainScreen",
							"Could not determine provider information.",
							Toaster.TOASTER_ERROR);
				}
			} else {
				// TODO: GWT-RPC support for this function
			}
		} else {
			JsonUtil
					.debug("MainScreen.populateDefaultProvider: already assigned");
		}
	}

}
