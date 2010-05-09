/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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
import java.util.Iterator;
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
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.PreferencesScreen;
import org.freemedsoftware.gwt.client.screen.ReportingScreen;
import org.freemedsoftware.gwt.client.widget.AsyncPicklistWidgetBase;
import org.freemedsoftware.gwt.client.widget.ClosableTab;
import org.freemedsoftware.gwt.client.widget.ClosableTabInterface;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonGroup;
import org.freemedsoftware.gwt.client.widget.PatientTagWidget;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.ProviderWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.UserMultipleChoiceWidget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.core.client.RunAsyncCallback;
import com.google.gwt.dom.client.Element;
import com.google.gwt.dom.client.HeadElement;
import com.google.gwt.dom.client.Node;
import com.google.gwt.dom.client.NodeList;
import com.google.gwt.event.dom.client.MouseDownEvent;
import com.google.gwt.event.dom.client.MouseDownHandler;
import com.google.gwt.event.dom.client.MouseMoveEvent;
import com.google.gwt.event.dom.client.MouseMoveHandler;
import com.google.gwt.event.dom.client.MouseOutEvent;
import com.google.gwt.event.dom.client.MouseOutHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.i18n.client.DateTimeFormat;
import com.google.gwt.i18n.client.LocaleInfo;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FocusWidget;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.UIObject;
import com.google.gwt.user.client.ui.Widget;

import eu.future.earth.gwt.client.TimeBox;

public final class Util {

	/**
	 * If true then JSONRPC calls can be sent to third server in hosted mode Set
	 * server URL in relay.jsp
	 */
	public static boolean GWT_HOSTED_MODE = false;

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
		if (GWT_HOSTED_MODE)
			url = getBaseUrl() + "/relay.jsp";
		try {
			String params = new String();
			for (int iter = 0; iter < args.length; iter++) {
				if (iter > 0) {
					params += "&";
				}
				params += "param" + new Integer(iter).toString() + "="
						+ URL.encodeComponent(args[iter]);
			}
			if (GWT_HOSTED_MODE)
				return url + "?module=" + method
						+ (params.length() > 0 ? "&" + params : "");
			else
				return url + "/" + method + "?" + params;
		} catch (Exception e) {
			return url + "/" + method;
		}
	}

	/**
	 * Get full url of FreeMED help pages.
	 * 
	 * @return URL to pass with JSON request
	 */
	public static synchronized String getHelpRequest() {
		String url = getBaseUrl() + "/help.php/gwt/"+CurrentState.getLocale()+"/";
		url = url + CurrentState.getCurrentPageHelp()+"."+CurrentState.getLocale();
		return url;
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
			final Command whenDone, final Command whenFail){
		login(username, password, whenDone, whenFail);
	}
	
	public static void login(String username, String password,String location,
			final Command whenDone, final Command whenFail) {
		List paramList = new ArrayList();
		paramList.add(username);
		paramList.add(password);
		if(location!=null)
			paramList.add(location);	
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(
						"org.freemedsoftware.public.Login.Validate", (String[])paramList.toArray(new String[0]))));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
					Window.alert(ex.toString());
				}

				public void onResponseReceived(Request request,
						Response response) {
					if (200 == response.getStatusCode()) {
						if (response.getText().trim().compareToIgnoreCase(
								"true") == 0) {
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
		boolean recycle = false;
		HashMap<Integer, HashMap<String, PatientScreenInterface>> map = CurrentState
				.getPatientSubScreenMap();
		Integer oldId = pScreen.getPatient();
		HashMap<String, PatientScreenInterface> subHashMap = null;
		if (map.get(oldId) == null) {
			// We don't find it, we have to instantiate new
			recycle = false;
			subHashMap = new HashMap<String, PatientScreenInterface>();
			subHashMap.put(screen.getClass().getName(), screen);
			// Push into mapping
			map.put(oldId, subHashMap);
		} else if (map.get(oldId).get(screen.getClass().getName()) == null) {
			// We don't find the required screen
			recycle = false;
			subHashMap = map.get(oldId);
			subHashMap.put(screen.getClass().getName(), screen);
		} else {
			recycle = true; // skip actual instantiation

			// Get screen
			PatientScreenInterface existingScreen = map.get(oldId).get(
					screen.getClass().getName());

			// Activate that screen
			try {
				pScreen.getTabPanel().selectTab(
						pScreen.getTabPanel().getWidgetIndex(existingScreen));
			} catch (Exception ex) {
				GWT.log("Exception", ex);
				JsonUtil.debug("Exception selecting tb: " + ex.toString());
			}
		}
		// Activate the instantiated screen.
		if (!recycle) {
			screen.assignPatientScreen(pScreen);
			pScreen.getTabPanel().add((Widget) screen,
					new ClosableTab(title, (Widget) screen));
			pScreen.getTabPanel().selectTab(
					pScreen.getTabPanel().getWidgetCount() - 1);
		}
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
							CurrentState.getMainScreen().setVisible(false);
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
		if(!flag){
			try {
				Float val = new Float(value);
				flag = true;
			} catch (Exception e) {
				flag = false;
			}
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
		Date date = null;
		DateTimeFormat df = DateTimeFormat.getFormat("yyyy-MM-dd HH:mm:ss");
		try {
			date = df.parse(dateStr);
		} catch (Exception e) {
			JsonUtil.debug(e.getMessage());
		}
		try {
			if (date == null) {
				df = DateTimeFormat.getFormat("yyyy-MM-dd");
				date = df.parse(dateStr);
			}

		} catch (Exception e) {
			JsonUtil.debug(e.getMessage());
			e.printStackTrace();
		}
		return date;
	}
	/**
	 * getTodayDate
	 * @return date string
	 */
	public static String getTodayDate(){
		Calendar cal = new GregorianCalendar();
		return cal.getTime().toString();
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

	/**
	 * Set Focus on the Widget after a time delay of 500ms
	 * 
	 * @param Widget -
	 *            The widget to be focused.
	 * 
	 */
	public static void setFocus(final Widget widget) {
		Timer timer = new Timer() {
			public void run() {
				if (widget instanceof AsyncPicklistWidgetBase) {
					((AsyncPicklistWidgetBase) widget).setFocus(true);
				}
				if (widget instanceof FocusWidget) {
					((FocusWidget) widget).setFocus(true);
				}
				if (widget instanceof CustomRadioButtonGroup) {
					((CustomRadioButtonGroup) widget).setFocus(true);
				}
				if (widget instanceof UserMultipleChoiceWidget) {
					((UserMultipleChoiceWidget) widget).setFocus();
				}
				if (widget instanceof PatientTagWidget) {
					((PatientTagWidget) widget).setFocus(true);
				}
			}
		};
		// Run initial polling ...
		timer.schedule(500);
		timer.run();
	}

	/**
	 * Create Label Widget to act as a Vertical spacer
	 * 
	 * @param Height -
	 *            height in pixels or percentages
	 * 
	 * return Widget
	 */

	public static Widget getVSpacer(String height) {
		final Label vSpacer = new Label();
		vSpacer.setHeight(height);
		return vSpacer;
	}

	/**
	 * Create Label Widget to act as a Horizontal spacer
	 * 
	 * @param Width -
	 *            width in pixels or percentages
	 * 
	 * return Widget
	 */

	public static Widget getHSpacer(String width) {
		final Label vSpacer = new Label();
		vSpacer.setWidth(width);
		return vSpacer;
	}

	/**
	 * passed widget map component's value will be set to default
	 * 
	 * @param HashMap
	 *            <String, Widget> - list of any ui component that belongs
	 *            Widget
	 * 
	 */

	public static void resetWidgetMap(HashMap<String, Widget> map) {
		Iterator<String> iterator = map.keySet().iterator();
		while (iterator.hasNext()) {
			String key = iterator.next();
			Widget widget = map.get(key);
			resetWidget(widget);
		}
	}

	/**
	 * reads all widgets values and put them into new jsonifyable map
	 * 
	 * @param HashMap
	 *            <String, Widget> - key as column and value as UI component
	 * 
	 * return HashMap<String, String> jsonifyable
	 */

	public static HashMap<String, String> populateHashMap(
			HashMap<String, Widget> containerFormFields) {
		HashMap<String, String> formDataMap = new HashMap<String, String>();
		Iterator<String> iterator = containerFormFields.keySet().iterator();
		while (iterator.hasNext()) {
			try {
				String key = iterator.next();
				Widget widget = containerFormFields.get(key);
				String widgetValue = getWidgetValue(widget);
				if(widgetValue!=null)
					formDataMap.put(key, widgetValue);
			} catch (Exception e) {
				JsonUtil.debug(e.getMessage());
			}
		}
		return formDataMap;
	}

	public static String getWidgetValue(Widget widget){
		String widegtValue = null;
		if (widget instanceof CustomRadioButtonGroup) {
			if (((CustomRadioButtonGroup) widget).getWidgetValue() != null) {
				widegtValue = ((CustomRadioButtonGroup) widget).getWidgetValue();
			}
		} else if (widget instanceof TimeBox
				&& ((TimeBox) widget).isEnabled()) {
			if (((TimeBox) widget).getValue() != null)
				widegtValue = Util.getSQLDate(((TimeBox) widget).getValue(new Date()));
		} else if (widget instanceof TextArea
				&& ((TextArea) widget).isEnabled()) {
			if (((TextArea) widget).getText() != null)
				widegtValue = ((TextArea) widget).getText();
		} else if (widget instanceof CheckBox
				&& ((CheckBox) widget).isEnabled()) {
			widegtValue = ((CheckBox) widget).getValue() ? "1": "0";
		} else if (widget instanceof TextBox
				&& ((TextBox) widget).isEnabled()) {
			if (((TextBox) widget).getText() != null)
				widegtValue = ((TextBox) widget).getText();
		} else if (widget instanceof CustomDatePicker) {
			if (((CustomDatePicker) widget).getStoredValue() != null)
				widegtValue = ((CustomDatePicker) widget).getStoredValue();
		} else if (widget instanceof ProviderWidget) {
			if (((ProviderWidget) widget).getStoredValue() != null)
				widegtValue = ((ProviderWidget) widget).getStoredValue();
		} else if (widget instanceof CustomListBox) {
			if (((CustomListBox) widget).getStoredValue() != null)
				widegtValue = ((CustomListBox) widget).getStoredValue();
		} else if (widget instanceof SupportModuleWidget) {
			if (((SupportModuleWidget) widget).getStoredValue() != null) {
				widegtValue = ((SupportModuleWidget) widget).getStoredValue();
			}
		} else if (widget instanceof PatientWidget) {
			if (((PatientWidget) widget).getStoredValue() != null) {
				widegtValue = ((PatientWidget) widget).getStoredValue();
			}
		}
		return widegtValue;
	}
	
		public static String getWidgetText(Widget widget){
		String widegtText = null;
		if (widget instanceof CustomRadioButtonGroup) {
			if (((CustomRadioButtonGroup) widget).getWidgetValue() != null) {
				widegtText = ((CustomRadioButtonGroup) widget).getWidgetText();
			}
		}else if (widget instanceof TextArea
				&& ((TextArea) widget).isEnabled()) {
			if (((TextArea) widget).getText() != null)
				widegtText = ((TextArea) widget).getText();
		} else if (widget instanceof CheckBox
				&& ((CheckBox) widget).isEnabled()) {
			widegtText = ((CheckBox) widget).getText();
		} else if (widget instanceof TextBox
				&& ((TextBox) widget).isEnabled()) {
			if (((TextBox) widget).getText() != null)
				widegtText = ((TextBox) widget).getText();
		} else if (widget instanceof CustomDatePicker) {
			if (((CustomDatePicker) widget).getStoredValue() != null)
				widegtText = ((CustomDatePicker) widget).getTextBox().getText();
		} else if (widget instanceof ProviderWidget) {
			if (((ProviderWidget) widget).getStoredValue() != null)
				widegtText = ((ProviderWidget) widget).getText();
		} else if (widget instanceof CustomListBox) {
			if (((CustomListBox) widget).getStoredValue() != null)
				widegtText = ((CustomListBox) widget).getWidgetText();
		} else if (widget instanceof SupportModuleWidget) {
			if (((SupportModuleWidget) widget).getStoredValue() != null) {
				widegtText = ((SupportModuleWidget) widget).getText();
			}
		} else if (widget instanceof PatientWidget) {
			if (((PatientWidget) widget).getStoredValue() != null) {
				widegtText = ((PatientWidget) widget).getText();
			}
		}
		return widegtText;
	}
	
	public static void resetWidget(Widget widget){
			if (widget instanceof CustomRadioButtonGroup)
				((CustomRadioButtonGroup) widget).clear(true);
			else if (widget instanceof TextArea)
				((TextArea) widget).setText("");
			else if (widget instanceof TimeBox)
				((TimeBox) widget).setDate(new Date());
			else if (widget instanceof TextBox)
				((TextBox) widget).setText("");
			else if (widget instanceof CustomDatePicker)
				((CustomDatePicker) widget).getTextBox().setText("");
			else if (widget instanceof CheckBox)
				((CheckBox) widget).setValue(false, true);
			else if (widget instanceof ProviderWidget) {
				((ProviderWidget) widget).setValue(0);
				((ProviderWidget) widget).getTextEntryWidget().setText("");
			} else if (widget instanceof CustomListBox) {
				((CustomListBox) widget).setSelectedIndex(0);
			} else if (widget instanceof SupportModuleWidget) {
				((SupportModuleWidget) widget).clear();
			} else if (widget instanceof PatientWidget) {
				((PatientWidget) widget).clear();
			}
			
	}
	
	/**
	 * reads data map and puts values to widget map components
	 * 
	 * @param HashMap
	 *            <String, String> - Data map containing keys as column and
	 *            value as column value
	 * 
	 * @param HashMap
	 *            <String, Widget> - key as column and value as UI component
	 * 
	 */

	public static void populateForm(
			HashMap<String, Widget> containerFormFields,
			HashMap<String, String> data) {
		Iterator<String> iterator = containerFormFields.keySet().iterator();
		while (iterator.hasNext()) {
			String key = iterator.next();
			Widget widget = containerFormFields.get(key);
			if (data.get(key) != null && !data.get(key).equals("")) {
				if (widget instanceof CustomRadioButtonGroup) {
					((CustomRadioButtonGroup) widget).setWidgetValue(data
							.get(key), true);
				} else if (widget instanceof TimeBox) {
					((TimeBox) widget).setDate(Util.getSQLDate(data.get(key)));
				} else if (widget instanceof TextBox) {
					((TextBox) widget).setText(data.get(key));
				} else if (widget instanceof TextArea) {
					((TextArea) widget).setText(data.get(key));
				} else if (widget instanceof CheckBox) {
					((CheckBox) widget).setValue(data.get(key)
							.equalsIgnoreCase("1") ? true : false, true);
				} else if (widget instanceof CustomDatePicker) {
					((CustomDatePicker) widget).setValue(data.get(key));
				} else if (widget instanceof ProviderWidget) {
					((ProviderWidget) widget).setValue(Integer.parseInt(data
							.get(key)));
				} else if (widget instanceof CustomListBox) {
					((CustomListBox) widget).setWidgetValue(data.get(key),true);
				} else if (widget instanceof SupportModuleWidget) {
					((SupportModuleWidget) widget).setValue(Integer
							.parseInt(data.get(key)));
				}
			}
		}
	}

	/**
	 * Calls server method 
	 * 
	 * @param package   - package name
	 * 
	 * @param module     - module name
	 *
	 * @param method     - method name           
	 * 
	 * @param paramsList - list of parameters of any type or multi-type
	 * 
	 * @param requestCallback - calls its onError & jsonifiedData function on getting response from server
	 * 
	 * @param responseType - type of response e.g Integer,HashMap<String,String>,String[],String[][] etc
	 */
	private static void callServerMethod(final String packageName,final String className,
			final String method, final List paramsList,final CustomRequestCallback requestCallback,final String responseType) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			List<String> paramsStr = new ArrayList<String>();
			if(paramsList!=null){
				Iterator iterator = paramsList.iterator();
				while(iterator.hasNext()){
					Object object = iterator.next();
					paramsStr.add(JsonUtil.jsonify(object));
				}
			}
			
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							packageName+"." + className + "." + method,
							paramsStr.toArray(new String[0]))));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						if(requestCallback!=null)
							requestCallback.onError();
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if(requestCallback!=null){
							if (200 == response.getStatusCode()) {
									Object result = JsonUtil
									.shoehornJson(JSONParser
											.parse(response.getText()),
											responseType);
									requestCallback.jsonifiedData(result);
							}else requestCallback.onError();
						}
					}
				});
			} catch (RequestException e) {
		}
		} else {
			// GWT-RPC
		}
	}
	
	public static void callApiMethod(final String className,
			final String method, final List paramsList,final CustomRequestCallback requestCallback,final String responseType){
			callServerMethod("org.freemedsoftware.api",className, method, paramsList, requestCallback, responseType);
	}
	public static void callApiMethod(final String className,
			final String method, final Integer id,final CustomRequestCallback requestCallback,final String responseType){
		List paramlst = new ArrayList();
		paramlst.add(id);
		callServerMethod("org.freemedsoftware.api",className, method, paramlst, requestCallback, responseType);
	}

	public static void callModuleMethod(final String className,
			final String method, final List paramsList,final CustomRequestCallback requestCallback,final String responseType){
			callServerMethod("org.freemedsoftware.module",className, method, paramsList, requestCallback, responseType);
	}
	public static void callModuleMethod(final String className,
			final String method, final Integer id,final CustomRequestCallback requestCallback,final String responseType){
		List paramlst = new ArrayList();
		paramlst.add(id);
		callServerMethod("org.freemedsoftware.module",className, method, paramlst, requestCallback, responseType);
	}

	/**
	 * Shows error messages on screen 
	 * 
	 * @param module     - module name
	 *
	 * @param msg        - message to display           
	 * 
	 */
	
	public static void showErrorMsg(String module,String msg){
		JsonUtil.debug("Error SYSTEM_NOTIFY_TYPE" + CurrentState.SYSTEM_NOTIFY_TYPE);
		if(CurrentState.SYSTEM_NOTIFY_TYPE.equals(AppConstants.SYSTEM_NOTIFY_ERROR)
				||CurrentState.SYSTEM_NOTIFY_TYPE.equals(AppConstants.SYSTEM_NOTIFY_ALL))
		CurrentState.getToaster().addItem(module,
				msg,
				Toaster.TOASTER_ERROR);
	}

	/**
	 * Shows info messages on screen 
	 * 
	 * @param module     - module name
	 *
	 * @param msg        - message to display           
	 * 
	 */
	
	public static void showInfoMsg(String module,String msg){
		JsonUtil.debug("INFO SYSTEM_NOTIFY_TYPE" + CurrentState.SYSTEM_NOTIFY_TYPE);
		if(CurrentState.SYSTEM_NOTIFY_TYPE.equals(AppConstants.SYSTEM_NOTIFY_INFO)
				||CurrentState.SYSTEM_NOTIFY_TYPE.equals(AppConstants.SYSTEM_NOTIFY_ALL))
		CurrentState.getToaster().addItem(module,
				msg,
				Toaster.TOASTER_INFO);
	}
	
	/**
	 * Generates Report To Browser
	 * 
	 * @param reportName   - Report name (stored in reporting table)
	 * 
	 * @param format       - format (pdf,html etc)
	 * 
	 * @param reportParams - list of parameters of any type or multi-type
	 * 
	 */
	
	public static void generateReportToBrowser(final String reportName,final String format, final List<String> reportParams) {
		List paramsList = new ArrayList();
		paramsList.add(reportName);
		callModuleMethod("Reporting", "GetReport", paramsList, new CustomRequestCallback() {
		
			@Override
			public void onError() {
	
			}
			@Override
			public void jsonifiedData(Object data) {
				if(data!=null){
					
						Window.open(Util.getJsonRequest(
								"org.freemedsoftware.module.Reporting.GenerateReport",
								new String[] { data.toString(), format,
										JsonUtil.jsonify(reportParams.toArray(new String[0])) }), reportName, "");
				}else{
					showErrorMsg(ReportingScreen.moduleName, "Report Not Found");
				}
			}
		
		}, "String");
		
	
	}
	
	/**
	 * Generates Report To Printer
	 * 
	 * @param reportName   - Report name (stored in reporting table)
	 * 
	 * @param format       - format (pdf,html etc)
	 * 
	 * @param reportParams - list of parameters of any type or multi-type
	 * 
	 * @param saveFailed   - if true then saves failed reports into printing log
	 * 
	 */
	
	public static void generateReportToPrinter(final String reportName,final String format, final List<String> reportParams,final boolean saveFailed) {
		
		List paramsList = new ArrayList();
		paramsList.add(reportName);
		callModuleMethod("Reporting", "GetReport", paramsList, new CustomRequestCallback() {
		
			@Override
			public void onError() {
	
			}
			@Override
			public void jsonifiedData(Object data) {
				if(data!=null){
					List paramsList = new ArrayList();
					paramsList.add(data.toString());
					paramsList.add(format);
					paramsList.add(reportParams.toArray(new String[0]));
					paramsList.add("true");
					
					callModuleMethod("Reporting", "GenerateReport", paramsList, new CustomRequestCallback() {
						
						@Override
						public void onError() {
							
						}
						@Override
						public void jsonifiedData(Object data) {
							if(data!=null){
								if(!data.toString().equals("PRINTED") && saveFailed)
									saveFailedReports(reportName, format, reportParams);
								if(data.toString().equals("DPNS")){
									if(Window.confirm("Default Printer Not Found!\nPress Ok to set default printer."))
										Util.spawnTab("Preferences", PreferencesScreen.getInstance());
								}else if(data.toString().equals("PNA")){
									showErrorMsg("Reporting", "Printer Not Available!");
								}
							}
						}
					
					}, "String");
				}else{
					showErrorMsg("reporting", "Report Not Found");
				}
			}
		
		}, "String");
		
	}
	/**
	 * Generates Report To Printer
	 * 
	 * @param reportName   - Report name (stored in reporting table)
	 * 
	 * @param format       - format (pdf,html etc)
	 * 
	 * @param reportParams - list of parameters of any type or multi-type
	 * 
	 */
	public static void generateReportToPrinter(final String reportName,final String format, final List<String> reportParams){
		generateReportToPrinter(reportName, format, reportParams, true);
	}
	
	/**
	 * Saves Failed Reports
	 * 
	 * @param reportName   - Report name (stored in reporting table)
	 * 
	 * @param format       - format (pdf,html etc)
	 * 
	 * @param reportParams - list of parameters of any type or multi-type
	 * 
	 */
	
	public static void saveFailedReports(final String reportName,final String format, final List<String> reportParams){
		Iterator<String> iterator = reportParams.iterator();
		String reportParamsStr = "";
		while(iterator.hasNext()){
			reportParamsStr=reportParamsStr + iterator.next();
			if(iterator.hasNext())
				reportParamsStr = reportParamsStr + ",";	
		}
		HashMap<String, String> paramMap = new HashMap<String, String>();
		paramMap.put("report_name", reportName);
		paramMap.put("report_params", reportParamsStr);
		paramMap.put("report_format", format);

		List paramsList = new ArrayList();
		paramsList.add(paramMap);
		
		callModuleMethod("ReportinPrintLog", "Add", paramsList, new CustomRequestCallback() {
			@Override
			public void onError() {
			}
			@Override
			public void jsonifiedData(Object data) {
			}
		
		}, "Integer");
	}
	/**
	 * Attaches the mouseover help popup
	 * 
	 * @param widget   - Widget for which mouseover help popup is required
	 * 
	 * @param title    - title of the help dialog 
	 * 
	 * @param help     - detailed text explaining the topic
	 * 
	 */
	public static void attachHelp(final Widget widget,String title, String help,final boolean showOnLeft){
		final PopupPanel popup = new PopupPanel();
		final HTML html = new HTML();
		html.setHTML("<b>" + title
				+ "</b><br/><br/>" + help);

		popup.add(html);
		popup.setStyleName("freemed-HelpPopup");
		if(widget instanceof FocusWidget){
			((FocusWidget)widget).addMouseOutHandler(new MouseOutHandler() {
				@Override
				public void onMouseOut(MouseOutEvent event) {
					// Hide help PopUp
					popup.hide();
				}
	
			});
			((FocusWidget)widget).addMouseDownHandler(new MouseDownHandler() {
				@Override
				public void onMouseDown(MouseDownEvent event) {
					// Hide help PopUp
					popup.hide();
				}
	
			});
			((FocusWidget)widget).addMouseMoveHandler(new MouseMoveHandler() {
				@Override
				public void onMouseMove(MouseMoveEvent event) {
					// Do nothing
					popup.show();
					popup.setPopupPosition(widget.getAbsoluteLeft() + (showOnLeft?-1*popup.getOffsetWidth():20),
							widget.getAbsoluteTop() + 20);
				}
			});
		}else if(widget instanceof Image){
			((Image)widget).addMouseOutHandler(new MouseOutHandler() {
				@Override
				public void onMouseOut(MouseOutEvent event) {
					// Hide help PopUp
					popup.hide();
				}
	
			});
			((Image)widget).addMouseDownHandler(new MouseDownHandler() {
				@Override
				public void onMouseDown(MouseDownEvent event) {
					// Hide help PopUp
					popup.hide();
				}
	
			});
			((Image)widget).addMouseMoveHandler(new MouseMoveHandler() {
				@Override
				public void onMouseMove(MouseMoveEvent event) {
					// Do nothing
					popup.show();
					popup.setPopupPosition(widget.getAbsoluteLeft() + (showOnLeft?-1*popup.getOffsetWidth():20),
							widget.getAbsoluteTop() + 20);
				}
			});
		}
	}
	
}
