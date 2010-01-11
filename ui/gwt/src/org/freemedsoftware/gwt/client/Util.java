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

package org.freemedsoftware.gwt.client;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.Api.Authorizations;
import org.freemedsoftware.gwt.client.Api.AuthorizationsAsync;
import org.freemedsoftware.gwt.client.Api.Messages;
import org.freemedsoftware.gwt.client.Api.MessagesAsync;
import org.freemedsoftware.gwt.client.Api.ModuleInterface;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Api.PatientInterface;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.Api.Scheduler;
import org.freemedsoftware.gwt.client.Api.SchedulerAsync;
import org.freemedsoftware.gwt.client.Api.SystemConfig;
import org.freemedsoftware.gwt.client.Api.SystemConfigAsync;
import org.freemedsoftware.gwt.client.Api.Tickler;
import org.freemedsoftware.gwt.client.Api.TicklerAsync;
import org.freemedsoftware.gwt.client.Module.Allergies;
import org.freemedsoftware.gwt.client.Module.AllergiesAsync;
import org.freemedsoftware.gwt.client.Module.Annotations;
import org.freemedsoftware.gwt.client.Module.AnnotationsAsync;
import org.freemedsoftware.gwt.client.Module.Medications;
import org.freemedsoftware.gwt.client.Module.MedicationsAsync;
import org.freemedsoftware.gwt.client.Module.MessagesModule;
import org.freemedsoftware.gwt.client.Module.MessagesModuleAsync;
import org.freemedsoftware.gwt.client.Module.MultumDrugLexicon;
import org.freemedsoftware.gwt.client.Module.MultumDrugLexiconAsync;
import org.freemedsoftware.gwt.client.Module.PatientModule;
import org.freemedsoftware.gwt.client.Module.PatientModuleAsync;
import org.freemedsoftware.gwt.client.Module.PatientTag;
import org.freemedsoftware.gwt.client.Module.PatientTagAsync;
import org.freemedsoftware.gwt.client.Module.RemittBillingTransport;
import org.freemedsoftware.gwt.client.Module.RemittBillingTransportAsync;
import org.freemedsoftware.gwt.client.Module.Reporting;
import org.freemedsoftware.gwt.client.Module.ReportingAsync;
import org.freemedsoftware.gwt.client.Module.SuperbillTemplate;
import org.freemedsoftware.gwt.client.Module.SuperbillTemplateAsync;
import org.freemedsoftware.gwt.client.Module.UnfiledDocuments;
import org.freemedsoftware.gwt.client.Module.UnfiledDocumentsAsync;
import org.freemedsoftware.gwt.client.Module.UnreadDocuments;
import org.freemedsoftware.gwt.client.Module.UnreadDocumentsAsync;
import org.freemedsoftware.gwt.client.Public.Login;
import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.Public.Protocol;
import org.freemedsoftware.gwt.client.Public.ProtocolAsync;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.widget.ClosableTab;
import org.freemedsoftware.gwt.client.widget.ClosableTabInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.core.client.RunAsyncCallback;
import com.google.gwt.dom.client.Element;
import com.google.gwt.dom.client.HeadElement;
import com.google.gwt.dom.client.Node;
import com.google.gwt.dom.client.NodeList;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.i18n.client.DateTimeFormat;
import com.google.gwt.i18n.client.LocaleInfo;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.UIObject;
import com.google.gwt.user.client.ui.Widget;

public final class Util {

	public static enum ProgramMode {
		NORMAL, STUBBED, JSONRPC
	};

	/**
	 * Set currently running program mode.
	 */
	public static ProgramMode thisProgramMode = ProgramMode.JSONRPC;

	/**
	 * Get base url of FreeMED installation.
	 * 
	 * @return Base URL string
	 */
	public static synchronized String getBaseUrl() {
		if (isStubbedMode()) {
			return GWT.getModuleBaseURL();
		} else {
			return GWT.getModuleBaseURL() + "../../../../";
		}
	}

	/**
	 * Get base url of FreeMED UI.
	 * 
	 * @return Base URL string
	 */
	public static synchronized String getUIBaseUrl() {
		return GWT.getModuleBaseURL() + "../";
	}

	/**
	 * Get full url of FreeMED JSON relay.
	 * 
	 * @param method
	 *            Fully qualified method name
	 * @param args
	 *            Array of parameters, as strings
	 * @return URL to pass with JSON request
	 */
	public static synchronized String getJsonRequest(String method,
			String[] args) {
		String url = getBaseUrl() + "/relay.php/json";
		try {
			String params = new String();
			for (int iter = 0; iter < args.length; iter++) {
				if (iter > 0) {
					params += "&";
				}
				params += "param" + new Integer(iter).toString() + "="
						+ URL.encodeComponent(args[iter]);
			}
			return url + "/" + method + "?" + params;
		} catch (Exception e) {
			return url + "/" + method;
		}
	}

	/**
	 * Get the "relative URL" used by async services
	 * 
	 * @return URL
	 */
	public static synchronized String getRelativeURL() {
		return new String(getBaseUrl() + "/relay-gwt.php");
	}

	/**
	 * Find out if we're running in stub mode or not.
	 * 
	 * @return Stubbed mode status
	 */
	public static synchronized boolean isStubbedMode() {
		return thisProgramMode == ProgramMode.STUBBED;
	}

	/**
	 * Return "program mode" to determine whether it is running in GWT-RPC,
	 * stubbed, or JSON-RPC.
	 * 
	 * @return
	 */
	public static synchronized ProgramMode getProgramMode() {
		return thisProgramMode;
	}

	/**
	 * Generate async proxy for GWT-RPC interactions based on proxy name.
	 * 
	 * @param className
	 *            String representation of proxy we're looking for
	 * @return Async service object as generic Object
	 * @throws Exception
	 *             Thrown when className isn't resolved.
	 */
	public static synchronized Object getProxy(String className)
			throws Exception {
		Object service = null;

		// This is a *horrendous* hack to get around lack of dynamic loading

		// org.freemedsoftware.gwt.client.Public.*

		if (className.compareTo("org.freemedsoftware.gwt.client.Public.Login") == 0) {
			service = (LoginAsync) GWT.create(Login.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Public.Protocol") == 0) {
			service = (ProtocolAsync) GWT.create(Protocol.class);
		}

		// org.freemedsoftware.gwt.client.Api.*

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Api.Authorizations") == 0) {
			service = (AuthorizationsAsync) GWT.create(Authorizations.class);
		}

		if (className.compareTo("org.freemedsoftware.gwt.client.Api.Messages") == 0) {
			service = (MessagesAsync) GWT.create(Messages.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Api.ModuleInterface") == 0) {
			service = (ModuleInterfaceAsync) GWT.create(ModuleInterface.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Api.PatientInterface") == 0) {
			service = (PatientInterfaceAsync) GWT
					.create(PatientInterface.class);
		}

		if (className.compareTo("org.freemedsoftware.gwt.client.Api.Scheduler") == 0) {
			service = (SchedulerAsync) GWT.create(Scheduler.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Api.SystemConfig") == 0) {
			service = (SystemConfigAsync) GWT.create(SystemConfig.class);
		}

		if (className.compareTo("org.freemedsoftware.gwt.client.Api.Tickler") == 0) {
			service = (TicklerAsync) GWT.create(Tickler.class);
		}

		// org.freemedsoftware.gwt.client.Module.*

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.Allergies") == 0) {
			service = (AllergiesAsync) GWT.create(Allergies.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.Annotations") == 0) {
			service = (AnnotationsAsync) GWT.create(Annotations.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.Medications") == 0) {
			service = (MedicationsAsync) GWT.create(Medications.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.MessagesModule") == 0) {
			service = (MessagesModuleAsync) GWT.create(MessagesModule.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.MultumDrugLexicon") == 0) {
			service = (MultumDrugLexiconAsync) GWT
					.create(MultumDrugLexicon.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.PatientModule") == 0) {
			service = (PatientModuleAsync) GWT.create(PatientModule.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.PatientTag") == 0) {
			service = (PatientTagAsync) GWT.create(PatientTag.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.RemittBillingTransport") == 0) {
			service = (RemittBillingTransportAsync) GWT
					.create(RemittBillingTransport.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.Reporting") == 0) {
			service = (ReportingAsync) GWT.create(Reporting.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.SuperbillTemplate") == 0) {
			service = (SuperbillTemplateAsync) GWT
					.create(SuperbillTemplate.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.UnfiledDocuments") == 0) {
			service = (UnfiledDocumentsAsync) GWT
					.create(UnfiledDocuments.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.UnreadDocuments") == 0) {
			service = (UnreadDocumentsAsync) GWT.create(UnreadDocuments.class);
		}

		try {
			ServiceDefTarget endpoint = (ServiceDefTarget) service;
			String moduleRelativeURL = Util.getRelativeURL();
			endpoint.setServiceEntryPoint(moduleRelativeURL);
			return (Object) service;
		} catch (Exception e) {
			// All else fails, throw exception
			throw new Exception("Unable to resolve appropriate class "
					+ className);
		}
	}

	public static void login(String username, String password,
			final Command whenDone, final Command whenFail) {
		String[] params = { username, password };
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(
						"org.freemedsoftware.public.Login.Validate", params)));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
					Window.alert(ex.toString());
				}

				public void onResponseReceived(Request request,
						Response response) {
					if (200 == response.getStatusCode()) {
						if (response.getText().compareToIgnoreCase("true") == 0) {
							whenDone.execute();
						} else {
							whenFail.execute();
						}
					} else {
						whenFail.execute();
					}
				}
			});
		} catch (RequestException e) {
			whenFail.execute();
		}
	}

	/**
	 * sets facility into session so that it can be retrieved & show on home
	 * page after refresh
	 * 
	 * @param facility
	 *            String facility selected at login
	 */
	public static synchronized void setFacilityInSession(String facilityName,
			String facilityId) {
		if (thisProgramMode == ProgramMode.STUBBED) {
			// TODO STUBBED Mode stuff
		} else if (thisProgramMode == ProgramMode.JSONRPC) {
			String[] params = { facilityName, facilityId };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.FacilityModule.SetDefaultFacility",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							JsonUtil.debug("Util: SetDefaultFacility:"
									+ response.getText());
						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}

	/**
	 * Create new tab in main window with specified title and ScreenInterface
	 * 
	 * @param title
	 *            String title of the new tab
	 * @param screen
	 *            Object containing extended composite with content
	 */
	public static synchronized void spawnTab(String title,
			ScreenInterface screen) {
		Util.spawnTab(title, screen, null);
	}

	/**
	 * Create new tab in main window with specified title and ScreenInterface
	 * 
	 * @param title
	 *            String title of the new tab
	 * @param screen
	 *            Object containing extended composite with content
	 * @param ClosableTabInterface
	 *            Object implementing onclose & isReadyToClose functions
	 */
	public static synchronized void spawnTab(String title,
			ScreenInterface screen, ClosableTabInterface closableTabInterface) {
		boolean recycle = false;

		// Special handling for PatientScreen
		if (screen instanceof PatientScreen) {
			HashMap<Integer, PatientScreen> map = CurrentState
					.getPatientScreenMap();
			Integer oldId = ((PatientScreen) screen).getPatient();
			if (map.get(oldId) == null) {
				// We don't find it, we have to instantiate new
				recycle = false;

				// Push into mapping
				map.put(oldId, (PatientScreen) screen);

				// Force population only if it hadn't existed before.
				((PatientScreen) screen).populate();
			} else {
				recycle = true; // skip actual instantiation

				// Get screen
				PatientScreen existingScreen = map.get(oldId);

				// Activate that screen
				try {
					CurrentState.getTabPanel().selectTab(
							CurrentState.getTabPanel().getWidgetIndex(
									existingScreen));
				} catch (Exception ex) {
					GWT.log("Exception", ex);
					JsonUtil.debug("Exception selecting tb: " + ex.toString());
				}
			}
		}

		// Only instantiate new screen if we aren't recycling an old one
		if (!recycle) {
			if (screen instanceof PatientScreen) {
				CurrentState.getTabPanel().add((Widget) screen,
						new ClosableTab(title, (Widget) screen, null));
			} else {
				if (CurrentState.getTabPanel().getWidgetIndex(screen) == -1)
					CurrentState.getTabPanel().add(
							(Widget) screen,
							new ClosableTab(title, (Widget) screen,
									closableTabInterface));
			}
			CurrentState.getTabPanel().selectTab(
					CurrentState.getTabPanel().getWidgetIndex(screen));
		}
	}

	/**
	 * Close tab from main window
	 * 
	 * @param screen
	 *            Object containing extended composite with content
	 */
	public static synchronized void closeTab(ScreenInterface screen) {

		TabPanel t = CurrentState.getTabPanel();
		t.selectTab(t.getWidgetIndex(screen) - 1);
		t.remove(t.getWidgetIndex(screen));

		// Special handling for PatientScreen
		if (screen instanceof PatientScreen) {
			HashMap<Integer, PatientScreen> map = CurrentState
					.getPatientScreenMap();
			Integer oldId = ((PatientScreen) screen).getPatient();
			if (map.get(oldId) != null) {
				map.remove(oldId);
			}
		}
	}

	/**
	 * Close all tabs from main window after logout
	 * 
	 */
	public static synchronized void closeAllTabs() {

		TabPanel t = CurrentState.getTabPanel();
		while (t.getWidgetCount() > 1) {

			ScreenInterface screen = (ScreenInterface) t.getWidget(1);
			// Special handling for PatientScreen
			if (screen instanceof PatientScreen) {
				HashMap<Integer, PatientScreen> map = CurrentState
						.getPatientScreenMap();
				Integer oldId = ((PatientScreen) screen).getPatient();
				if (map.get(oldId) != null) {
					map.remove(oldId);
				}
			}
			screen.closeScreen();
		}
		t.selectTab(0);
	}

	/**
	 * Create new tab in patient screen with specified title and
	 * PatientScreenInterface
	 * 
	 * @param title
	 *            String title of the new tab
	 * @param screen
	 *            Object containing extended composite with content
	 * @param pScreen
	 *            Pass reference to PatientScreen parent
	 */
	public static synchronized void spawnTabPatient(String title,
			PatientScreenInterface screen, PatientScreen pScreen) {
		screen.assignPatientScreen(pScreen);
		pScreen.getTabPanel().add((Widget) screen,
				new ClosableTab(title, (Widget) screen));
		pScreen.getTabPanel().selectTab(
				pScreen.getTabPanel().getWidgetCount() - 1);
	}

	/**
	 * Check JSON response to see if it's a "valid" authenticated response.
	 * 
	 * @param response
	 *            JSON response text
	 * @return boolean false if the session has expired (logs out automatically)
	 *         or true if we can continue with business as usual.
	 */
	public static boolean checkValidSessionResponse(String response) {
		final String fail = "denied due to user not being logged in";
		if (response.indexOf(fail) != -1) {
			Util.logout();
			return false;
		}
		return true;
	}

	/**
	 * Execute a piece of code asynchronously.
	 * 
	 * @param cb
	 */
	public static void runAsync(final Command cb) {
		GWT.runAsync(new RunAsyncCallback() {
			@Override
			public void onSuccess() {
				cb.execute();
			}

			@Override
			public void onFailure(Throwable reason) {
				Window
						.alert("Could not connect to the server! Please contact your system administrator.");
			}
		});
	}

	/**
	 * Logout of the system and pop up a login dialog.
	 * 
	 * @param state
	 */
	public static void logout() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.public.Login.Logout", params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						GWT.log("Exception", ex);
						Window.alert("Failed to log out.");
					}

					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							// closeAllTabs();
							CurrentState.getMainScreen().hide();
							UIObject.setVisible(RootPanel.get(
									"loginScreenOuter").getElement(), true);
							CurrentState.getFreemedInterface().getLoginDialog()
									.center();
							CurrentState.getFreemedInterface().getLoginDialog()
									.setFocusToPasswordField();
						} else {
							Window.alert("Failed to log out.");
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
						CurrentState.getMainScreen().hide();
						UIObject.setVisible(RootPanel.get("loginScreenOuter")
								.getElement(), true);
						CurrentState.getFreemedInterface().getLoginDialog()
								.center();
					}

					public void onFailure(Throwable t) {
						Window.alert("Failed to log out.");
					}
				});
			} catch (Exception e) {
				Window.alert("Could not create proxy for Login");
			}
		}
	}

	/**
	 * Validate if it is number or not
	 * 
	 * @param value
	 */

	public static synchronized boolean isNumber(String value) {
		boolean flag;
		try {
			Integer val = new Integer(value);
			flag = true;
		} catch (Exception e) {
			flag = false;
		}
		return flag;
	}

	/**
	 * Compare dates.
	 * 
	 * @param date1
	 * @param date2
	 * @return 0 (in case of same i.e. date1 is equals to date), 1 (in case of
	 *         date1 is After(Greater) than date2), -1 (in case of date is
	 *         Before(less) than date2)
	 */
	public static synchronized int compareDate(Date date1, Date date2) {
		Date d1, d2 = null;
		DateTimeFormat df = DateTimeFormat.getFormat("yyyy-MM-dd");
		int compare;
		d1 = df.parse(df.format(date1));
		d2 = df.parse(df.format(date2));
		compare = d1.compareTo(d2);
		return compare;
	}

	/**
	 * Format date in SQL format.
	 * 
	 * @param date
	 * @return String formated date for transmission into db table
	 */
	public static synchronized String getSQLDate(Date date) {
		// 2009-12-18 15:41:56
		DateTimeFormat df = DateTimeFormat.getFormat("yyyy-MM-dd HH:mm:ss");
		return df.format(date);
	}

	/**
	 * Parse SQL formatted date into Java object.
	 * 
	 * @param date
	 *            SQL formatted date string.
	 * @return Parsed Date
	 */
	public static synchronized Date getSQLDate(String dateStr) {
		// 2009-12-18 15:41:56
		Date date=null;
		DateTimeFormat df = DateTimeFormat.getFormat("yyyy-MM-dd HH:mm:ss");
		try{
			date = df.parse(dateStr);
		}catch(Exception e){}
		try{
			df = DateTimeFormat.getFormat("yyyy-MM-dd");
			date = df.parse(dateStr);
		}catch(Exception e){}
		return date;
	}

	/**
	 * Calculates age w.r.t provided DOB
	 * 
	 * @param date
	 *            SQL formatted date string.
	 * @return age
	 */
	public static synchronized int calculateAge(Date DOB) {
		int age = -1;
		Calendar calDOB = new GregorianCalendar();
		calDOB.setTime(DOB);
		Calendar calNow = new GregorianCalendar();
		calNow.setTime(new Date());
		age = calNow.get(Calendar.YEAR) - calDOB.get(Calendar.YEAR); 
		return age;
	}
	
	/**
	 * Update the style sheets to reflect the current theme and direction.
	 * 
	 * @param currentTheme
	 * @param lastTheme
	 */
	public static void updateStyleSheets(String currentTheme, String lastTheme) {
		// Generate the names of the style sheets to include
		String gwtStyleSheet = "resources/themes/" + currentTheme + "/"
				+ currentTheme + ".css";
		String showcaseStyleSheet = "resources/" + currentTheme
				+ "-stylesheet.css";
		if (LocaleInfo.getCurrentLocale().isRTL()) {
			gwtStyleSheet = gwtStyleSheet.replace(".css", "_rtl.css");
			showcaseStyleSheet = showcaseStyleSheet.replace(".css", "_rtl.css");
		}
		// Find existing style sheets that need to be removed
		boolean styleSheetsFound = false;
		final HeadElement headElem = StyleSheetLoader.getHeadElement();
		final List<Element> toRemove = new ArrayList<Element>();
		NodeList<Node> children = headElem.getChildNodes();
		for (int i = 0; i < children.getLength(); i++) {
			Node node = children.getItem(i);
			if (node.getNodeType() == Node.ELEMENT_NODE) {
				Element elem = Element.as(node);
				if (elem.getTagName().equalsIgnoreCase("link")
						&& elem.getPropertyString("rel").equalsIgnoreCase(
								"stylesheet")) {
					styleSheetsFound = true;
					String href = elem.getPropertyString("href");
					// If the correct style sheets are already loaded, then we
					// should have
					// nothing to remove.
					if (href.contains(lastTheme)) {
						toRemove.add(elem);
					}
				}
			}
		}
		lastTheme = currentTheme;
		// Return if we already have the correct style sheets

		if (styleSheetsFound && toRemove.size() == 0) {
			return;
		}

		// Detach the app while we manipulate the styles to avoid rendering
		// issues
		RootPanel.get().remove(CurrentState.getMainScreen());

		// Remove the old style sheets
		for (Element elem : toRemove) {
			headElem.removeChild(elem);
		}

		// Load the GWT theme style sheet
		String modulePath = GWT.getHostPageBaseURL();
		Command callback = new Command() {
			/**
			 * The number of style sheets that have been loaded and executed
			 * this command.
			 */
			private int numStyleSheetsLoaded = 0;

			public void execute() {
				/*
				 * Wait until all style sheets have loaded before re-attaching
				 * the app.
				 */
				numStyleSheetsLoaded++;
				if (numStyleSheetsLoaded < 2) {
					return;
				}

				/*
				 * Different themes use different background colors for the body
				 * element, but IE only changes the background of the visible
				 * content on the page instead of changing the background color
				 * of the entire page. By changing the display style on the body
				 * element, we force IE to redraw the background correctly.
				 */
				RootPanel.getBodyElement().getStyle().setProperty("display",
						"none");
				RootPanel.getBodyElement().getStyle()
						.setProperty("display", "");
				RootPanel.get().add(CurrentState.getMainScreen());
			}
		};
		StyleSheetLoader.loadStyleSheet(modulePath + gwtStyleSheet,
				getCurrentReferenceStyleName("gwt", currentTheme), callback);

		/*
		 * Load the showcase specific style sheet after the GWT theme style
		 * sheet so that custom styles supercede the theme styles.
		 */
		StyleSheetLoader.loadStyleSheet(modulePath + showcaseStyleSheet,
				getCurrentReferenceStyleName("Application", currentTheme),
				callback);
		CurrentState.LAST_THEME = currentTheme;
	}

	public static String getCurrentReferenceStyleName(String prefix,
			String CUR_THEME) {
		String gwtRef = prefix + "-Reference-" + CUR_THEME;
		if (LocaleInfo.getCurrentLocale().isRTL()) {
			gwtRef += "-rtl";
		}
		return gwtRef;
	}

}
