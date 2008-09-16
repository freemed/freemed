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

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.URL;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.HTTPRequest;
import com.google.gwt.user.client.ResponseTextHandler;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.Widget;

public final class Util {

	/**
	 * Get base url of FreeMED installation.
	 * 
	 * @return Base URL string
	 */
	public static synchronized String getBaseUrl() {
		if (isStubbedMode()) {
			return GWT.getModuleBaseURL();
		} else {
			return new String("../../../..");
		}
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
		return true;
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
		HTTPRequest.asyncGet(Util.getJsonRequest(
				"org.freemedsoftware.public.Login.Validate", params),
				new ResponseTextHandler() {
					public void onCompletion(String result) {
						if (result.compareTo("true") == 0) {
							whenDone.execute();
						} else {
							whenFail.execute();
						}
					}
				});
	}

	/**
	 * Create new tab in main window with specified title and ScreenInterface
	 * 
	 * @param title
	 *            String title of the new tab
	 * @param screen
	 *            Object containing extended composite with content
	 * @param state
	 *            Pass internal program state.
	 */
	public static synchronized void spawnTab(String title,
			ScreenInterface screen, CurrentState state) {
		screen.assignState(state);
		state.getTabPanel().add((Widget) screen,
				new ClosableTab(title, (Widget) screen));
		state.getTabPanel().selectTab(state.getTabPanel().getWidgetCount() - 1);
	}

	/**
	 * Create new tab in patient screen with specified title and
	 * PatientScreenInterface
	 * 
	 * @param title
	 *            String title of the new tab
	 * @param screen
	 *            Object containing extended composite with content
	 * @param state
	 *            Pass internal program state.
	 * @param pScreen
	 *            Pass reference to PatientScreen parent
	 */
	public static synchronized void spawnTabPatient(String title,
			PatientScreenInterface screen, CurrentState state,
			PatientScreen pScreen) {
		screen.assignState(state);
		screen.assignPatientScreen(pScreen);
		pScreen.getTabPanel().add((Widget) screen,
				new ClosableTab(title, (Widget) screen));
		pScreen.getTabPanel().selectTab(
				pScreen.getTabPanel().getWidgetCount() - 1);
	}

}
