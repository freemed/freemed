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

import org.freemedsoftware.gwt.client.Api.PatientInterface;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.Module.Annotations;
import org.freemedsoftware.gwt.client.Module.AnnotationsAsync;
import org.freemedsoftware.gwt.client.Module.MessagesModule;
import org.freemedsoftware.gwt.client.Module.MessagesModuleAsync;
import org.freemedsoftware.gwt.client.Public.Login;
import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.widget.ClosableTab;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.Widget;

public final class Util {

	/**
	 * Get base url of FreeMED installation.
	 * 
	 * @return Base URL string
	 */
	public static synchronized String getBaseUrl() {
		return new String("../../../..");
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
						+ args[iter];
			}
			return url + "/" + method + "/" + params;
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

		if (className.compareTo("org.freemedsoftware.gwt.client.Public.Login") == 0) {
			service = (LoginAsync) GWT.create(Login.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Api.PatientInterface") == 0) {
			service = (PatientInterfaceAsync) GWT
					.create(PatientInterface.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.Annotations") == 0) {
			service = (AnnotationsAsync) GWT.create(Annotations.class);
		}

		if (className
				.compareTo("org.freemedsoftware.gwt.client.Module.MessagesModule") == 0) {
			service = (MessagesModuleAsync) GWT.create(MessagesModule.class);
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

}
