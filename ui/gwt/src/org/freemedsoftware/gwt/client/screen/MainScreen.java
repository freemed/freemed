/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Jeremy Allen <ieziar.jeremy <--at--> gmail.com>
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
import org.freemedsoftware.gwt.client.FreemedInterface;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.SystemNotifications;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.AccordionPanel;
import org.freemedsoftware.gwt.client.widget.InfoDialog;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.dom.client.HasClickHandlers;
import com.google.gwt.event.dom.client.HasMouseOutHandlers;
import com.google.gwt.event.dom.client.HasMouseOverHandlers;
import com.google.gwt.event.dom.client.MouseOutEvent;
import com.google.gwt.event.dom.client.MouseOutHandler;
import com.google.gwt.event.dom.client.MouseOverEvent;
import com.google.gwt.event.dom.client.MouseOverHandler;
import com.google.gwt.event.shared.HandlerRegistration;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.MenuItem;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class MainScreen extends Composite {

	public class MenuIcon extends Composite implements ClickHandler,
			MouseOverHandler, MouseOutHandler, HasClickHandlers,
			HasMouseOverHandlers, HasMouseOutHandlers {

		protected Command fireAction = null;

		public MenuIcon(Image icon, String caption, Command action) {
			VerticalPanel container = new VerticalPanel();
			container
					.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
			container.add(icon);

			Label captionWidget = new Label(caption);
			container.add(captionWidget);
			initWidget(container);

			// icon.addClickHandler(this);
			// captionWidget.addClickHandler(this);
			this.addClickHandler(this);

			// Over and out handlers
			this.addMouseOverHandler(this);
			this.addMouseOutHandler(this);
			this.setStyleName("accordion-item-unselected");

			fireAction = action;
		}

		@Override
		public void onClick(ClickEvent event) {
			if (fireAction != null) {
				fireAction.execute();
			}
		}

		@Override
		public void onMouseOver(MouseOverEvent event) {
			this.setStyleName("accordion-item-selected");
		}

		@Override
		public void onMouseOut(MouseOutEvent event) {
			this.setStyleName("accordion-item-unselected");
		}

		@Override
		public HandlerRegistration addMouseOverHandler(MouseOverHandler handler) {
			return addDomHandler(handler, MouseOverEvent.getType());
		}

		@Override
		public HandlerRegistration addMouseOutHandler(MouseOutHandler handler) {
			return addDomHandler(handler, MouseOutEvent.getType());
		}

		@Override
		public HandlerRegistration addClickHandler(ClickHandler handler) {
			return addDomHandler(handler, ClickEvent.getType());
		}
	}

	private static final AppConstants CONSTANTS = (AppConstants) GWT
			.create(AppConstants.class);

	private MenuBar menuBar_1;

	protected FreemedInterface freemedInterface;

	protected final TabPanel tabPanel;

	protected final HorizontalSplitPanel statusBarContainer;

	protected final Label statusBar1, statusBar2;

	// protected final CurrentState state = new CurrentState();

	protected final SystemNotifications notifications = new SystemNotifications();

	protected final DashboardScreen dashboard = new DashboardScreen();

	public MainScreen() {
		final DockPanel mainPanel = new DockPanel();
		initWidget(mainPanel);
		mainPanel.setSize("98%", "98%");

		CurrentState.retrieveUserConfiguration(true);

		JsonUtil.debug("MainScreen: call populateDefaultProvider");
		populateDefaultProvider();

		JsonUtil
				.debug("MainScreen: assign object to CurrentState static object");
		CurrentState.assignMainScreen(this);

		if (false) {
			final HorizontalPanel horizontalPanel = new HorizontalPanel();
			mainPanel.add(horizontalPanel, DockPanel.NORTH);
			horizontalPanel.setWidth("100%");
			/*
			 * Currently using the PushButton widget for a "go back to the
			 * beginning" Button, mainly because I couldn't set css background
			 * image to function correctly. -JA
			 */

			final PushButton pushButton_1 = new PushButton();
			horizontalPanel.add(pushButton_1);
			pushButton_1.setSize("67px", "40px");
			horizontalPanel.setCellWidth(pushButton_1, "40px");
			horizontalPanel.setCellHeight(pushButton_1, "100%");
			pushButton_1.setStyleName("freemed-LogoMainMenuBar");
			/*
			 * Start of the Main menu/toolbar. CSS inherits are VERY important,
			 * see the stylesheet.css for clearer explanation
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
								Util
										.spawnTab("Messages",
												new MessagingScreen());
							}
						});
				menuItem_2.setStyleName("freemed-SecondaryMenuItem");

				final MenuItem menuItem_scheduler = menuBar_1.addItem(
						"scheduler", new Command() {
							public void execute() {
								Util.spawnTab("Scheduler",
										new SchedulerScreen());
							}
						});
				menuItem_scheduler.setStyleName("freemed-SecondaryMenuItem");

				final MenuItem menuItem_7 = menuBar_1.addItem("config",
						new Command() {
							public void execute() {
								Util.spawnTab("Configuration",
										new ConfigurationScreen());
							}
						});
				menuItem_7.setStyleName("freemed-SecondaryMenuItem");

				final MenuItem menuItem_users = menuBar_1.addItem(
						"User Management", new Command() {
							public void execute() {
								Util.spawnTab("User Management",
										new UserManagementScreen());
							}
						});
				menuItem_users.setStyleName("freemed-SecondaryMenuItem");

				final MenuItem menuItem_3 = menuBar_1.addItem("logout",
						new Command() {
							public void execute() {
								try {
									dashboard.saveArrangement();
								} catch (Exception ex) {
									JsonUtil
											.debug("dashboard.saveArrangement() threw an exception, continue");
								}
								Util.logout();
							}
						});
				menuItem_3.setStyleName("freemed-SecondaryMenuItem");
				/*
				 * all the primary menu items are currently housed in static
				 * width/height since css backgrounds are batched buttons. This
				 * definitely needs to be worked on. hopefully using a more
				 * fluid css technique. probably will require more in code html
				 * markup.
				 */
				final MenuItem menuItem = menuBar
						.addItem(
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
								Util.spawnTab("Search",
										new PatientSearchScreen());
							}
						});
				menuItem_4.setStyleName("freemed-SecondaryMenuItem");

				final MenuItem menuItem_5 = menuBar_3.addItem("entry",
						new Command() {
							public void execute() {
								Util.spawnTab("New Patient", new PatientForm());
							}
						});
				menuItem_5.setStyleName("freemed-SecondaryMenuItem");

				final MenuItem menuItem_6 = menuBar_3.addItem("tags",
						new Command() {
							public void execute() {
								Util.spawnTab("Tag Search",
										new PatientTagSearchScreen());
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
										new UnfiledDocuments());
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
								Util.spawnTab("Reporting",
										new ReportingScreen());
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
				final MenuItem menuItem_data = menuBar_data.addItem(
						"support data", new Command() {
							public void execute() {
								Util.spawnTab("Support Data",
										new SupportDataScreen());
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

				final MenuItem menuItem_communitySupport = menuBar_support
						.addItem("community support", new Command() {
							public void execute() {
								InfoDialog d = new InfoDialog();
								d.setCaption("Community Support");
								d
										.setContent(new HTML(
												"TODO: describe community support blurb here."));
								d.center();
							}
						});
				menuItem_communitySupport
						.setStyleName("freemed-SecondaryMenuItem");

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

		}

		/*
		 * SimplePanel to hold (hopefully) a horizontal sub menu, going to try
		 * to use the Menu Bar items to call each sub-menu -JA
		 */

		JsonUtil.debug("MainScreen: create accordion panel");
		AccordionPanel accordionPanel = new AccordionPanel();
		accordionPanel.setHeight("100%");
		accordionPanel.setWidth("250px");
		{
			JsonUtil.debug("MainScreen: add main pane");
			VerticalPanel mainAccPanel = new VerticalPanel();
			mainAccPanel.setStyleName("accordion-panel");
			mainAccPanel
					.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
			mainAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/messaging.32x32.png"), "Messaging",
					new Command() {
						public void execute() {
							Util.spawnTab("Messages", new MessagingScreen());
						}
					}));
			mainAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/scheduler.32x32.png"), "Scheduler",
					new Command() {
						public void execute() {
							Util.spawnTab("Scheduler", new SchedulerScreen());
						}
					}));
			mainAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/user_config.32x32.png"),
					"Configuration", new Command() {
						public void execute() {
							Util.spawnTab("Configuration",
									new ConfigurationScreen());
						}
					}));
			mainAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/q_help.32x32.png"), "Support",
					new Command() {
						public void execute() {
							InfoDialog d = new InfoDialog();
							d.setCaption("Support");
							d
									.setContent(new HTML(
											"Commercial support is available for <b>FreeMED</b> through the Foundation's commercial support partners."
													+ "<br/><br/>"
													+ "More information is available at <a href=\"http://freemedsoftware.org/commercial_support\" target=\"_new\">http://freemedsoftware.org/commercial_support</a>."
													+ "<br/><br/>"
													+ "<hr/>"
													+ "<br/></br>"
													+ "Community support is available on the FreeMED group at "
													+ "<a href=\"http://groups.google.com/group/freemed-support?hl=en\">http://groups.google.com/group/freemed-support?hl=en</a>."));
							d.center();
						}
					}));

			mainAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/stop_cx.32x32.png"), "Logout",
					new Command() {
						public void execute() {
							try {
								dashboard.saveArrangement();
							} catch (Exception ex) {
								JsonUtil.debug("dashboard.saveArrangement()"
										+ " threw an exception, continue");
							}
							Util.logout();
						}
					}));

			accordionPanel.add("Main", mainAccPanel);

			JsonUtil.debug("MainScreen: add patient pane");
			VerticalPanel patientAccPanel = new VerticalPanel();
			patientAccPanel.setStyleName("accordion-panel");
			patientAccPanel
					.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
			patientAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/chart_search.32x32.png"), "Search",
					new Command() {
						public void execute() {
							Util.spawnTab("Search", new PatientSearchScreen());
						}
					}));
			patientAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/patient_entry.32x32.png"),
					"New Patient", new Command() {
						public void execute() {
							Util.spawnTab("New Patient", new PatientForm());
						}
					}));
			patientAccPanel.add(new MenuIcon(new Image(GWT.getHostPageBaseURL()
					+ "/resources/images/patient.32x32.png"), "Tag Search",
					new Command() {
						public void execute() {
							Util.spawnTab("Tag Search",
									new PatientTagSearchScreen());
						}
					}));
			accordionPanel.add("Patient", patientAccPanel);

			JsonUtil.debug("MainScreen: add document pane");
			VerticalPanel documentAccPanel = new VerticalPanel();
			documentAccPanel.setStyleName("accordion-panel");
			documentAccPanel
					.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
			documentAccPanel.add(new MenuIcon(new Image(GWT
					.getHostPageBaseURL()
					+ "/resources/images/unfiled.32x32.png"), "Unfiled",
					new Command() {
						public void execute() {
							Util.spawnTab("Unfiled Documents",
									new UnfiledDocuments());
						}
					}));
			documentAccPanel.add(new MenuIcon(new Image(GWT
					.getHostPageBaseURL()
					+ "/resources/images/unread.32x32.png"), "Unfiled",
					new Command() {
						public void execute() {
							// Util.spawnTab("Unread Documents",new
							// UnreadDocuments());
						}
					}));
			accordionPanel.add("Documents", documentAccPanel);

			JsonUtil.debug("MainScreen: add utilities pane");
			VerticalPanel utilitiesAccPanel = new VerticalPanel();
			utilitiesAccPanel.setStyleName("accordion-panel");
			utilitiesAccPanel
					.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
			utilitiesAccPanel.add(new MenuIcon(new Image(GWT
					.getHostPageBaseURL()
					+ "/resources/images/reporting.32x32.png"), "Reporting",
					new Command() {
						public void execute() {
							Util.spawnTab("Reporting", new ReportingScreen());
						}
					}));
			utilitiesAccPanel.add(new MenuIcon(new Image(GWT
					.getHostPageBaseURL()
					+ "/resources/images/modules.32x32.png"), "Support Data",
					new Command() {
						public void execute() {
							Util.spawnTab("Support Data",
									new SupportDataScreen());
						}
					}));
			utilitiesAccPanel.add(new MenuIcon(new Image(GWT
					.getHostPageBaseURL()
					+ "/resources/images/user_config.32x32.png"),
					"User Management", new Command() {
						public void execute() {
							Util.spawnTab("User Management",
									new UserManagementScreen());
						}
					}));

			accordionPanel.add("Utilities", utilitiesAccPanel);

			// Disable for now
			// accordionPanel.selectPanel("Main");
		}
		JsonUtil
				.debug("MainScreen: create container hpanel for accordion and tabs");
		HorizontalPanel menuAndContent = new HorizontalPanel();
		menuAndContent.setSize("100%", "100%");

		// Jam them together, no space.
		menuAndContent.setSpacing(0);
		// menuAndContent.setCellWidth(accordionPanel, "250px");

		JsonUtil.debug("MainScreen: create tabPanel");
		tabPanel = new TabPanel();
		tabPanel.setSize("100%", "100%");
		tabPanel.setWidth(new Integer(menuAndContent.getOffsetWidth() - 250)
				.toString()
				+ "px");
		// menuAndContent.setCellWidth(tabPanel, "auto");

		JsonUtil.debug("MainScreen: add accordion and tab panel to container");
		menuAndContent.add(accordionPanel);
		menuAndContent.add(tabPanel);
		menuAndContent.setCellHorizontalAlignment(tabPanel,
				HasHorizontalAlignment.ALIGN_LEFT);
		JsonUtil.debug("MainScreen: add container to dock panel");
		mainPanel.add(menuAndContent, DockPanel.CENTER);

		JsonUtil.debug("MainScreen: add dashboard panel to tabs and select");
		tabPanel.add(dashboard, "Dashboard");
		tabPanel.selectTab(0);
		JsonUtil.debug("MainScreen: pass tabPanel to static CurrentState");
		CurrentState.assignTabPanel(tabPanel);

		// Get configuration
		CurrentState.retrieveUserConfiguration(true, new Command() {
			public void execute() {
				JsonUtil.debug("MainScreen: Set State of dashboard");
				dashboard.afterStateSet();
			}
		});

		// Expand out main tabpanel to take up all extra room
		JsonUtil.debug("MainScreen: expand tabpanel");
		// mainPanel.setCellWidth(tabPanel, "100%");
		// mainPanel.setCellHeight(tabPanel, "100%");

		JsonUtil.debug("MainScreen: split panel");
		statusBarContainer = new HorizontalSplitPanel();
		mainPanel.add(statusBarContainer, DockPanel.SOUTH);
		statusBarContainer.setSize("100%", "30px");
		statusBarContainer.setSplitPosition("50%");

		JsonUtil.debug("MainScreen: status bar");
		statusBar1 = new Label("Ready");
		statusBar1.setStyleName("statusBar");
		statusBarContainer.add(statusBar1);
		CurrentState.assignStatusBar(statusBar1);
		statusBar2 = new Label("-");
		statusBar2.setStyleName("statusBar");
		statusBarContainer.add(statusBar2);
		if (Util.isStubbedMode()) {
			statusBar2.setText("STUBBED / TEST MODE");
		}

		// Create notification toaster
		JsonUtil.debug("MainScreen: create toaster");
		Toaster toaster = new Toaster();
		CurrentState.assignToaster(toaster);
		toaster.setTimeout(10);

		// Handle system notifications
		// notifications.setState(getCurrentState());
		JsonUtil.debug("MainScreen: start notifications");
		notifications.start();

		// Force showing the screen
		// show();
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
		CurrentState.assignFreemedInterface(i);
	}

	public void show() {
		RootPanel.setVisible(RootPanel.get("rootPanel").getElement(), true);
	}

	public void populateDefaultProvider() {
		if (CurrentState.getDefaultProvider().intValue() < 1) {
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
							CurrentState
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
									CurrentState.assignDefaultProvider(r);
								} else {
									JsonUtil
											.debug("MainScreen.populateDefaultProvider: found error");
								}
							} else {
								CurrentState
										.getToaster()
										.addItem(
												"MainScreen",
												"Could not determine provider information.",
												Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					CurrentState.getToaster().addItem("MainScreen",
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
