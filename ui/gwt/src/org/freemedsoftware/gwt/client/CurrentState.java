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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.screen.MainScreen;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;

public class CurrentState {

	protected HashMap<String, String> statusItems;

	protected Label statusBar = null;

	protected Toaster toaster = null;

	protected TabPanel tabPanel = null;

	protected String locale = "en_US";

	protected Integer defaultProvider = new Integer(0);

	protected HashMap<Integer, PatientScreen> patientScreenMap = new HashMap<Integer, PatientScreen>();

	public CurrentState() {
		statusItems = new HashMap<String, String>();
	}

	/**
	 * Bulk assign mainscreen object
	 * 
	 * @param m
	 */
	public void assignMainScreen(MainScreen m) {
		assignStatusBar(m.getStatusBar());
		assignTabPanel(m.getTabPanel());
	}

	/**
	 * Assign status bar object.
	 * 
	 * @param w
	 */
	public void assignStatusBar(Label l) {
		statusBar = l;
	}

	/**
	 * Assign default provider.
	 * 
	 * @param p
	 */
	public void assignDefaultProvider(Integer p) {
		defaultProvider = p;
	}

	/**
	 * Assign tab panel object.
	 * 
	 * @param t
	 */
	public void assignTabPanel(TabPanel t) {
		tabPanel = t;
	}

	/**
	 * Assign toaster object.
	 * 
	 * @param t
	 */
	public void assignToaster(Toaster t) {
		toaster = t;
	}

	/**
	 * Assign locale value.
	 * 
	 * @param l
	 *            Locale string, default is "en_US"
	 */
	public void assignLocale(String l) {
		locale = l;
	}

	/**
	 * Add an item to the status bar stack.
	 * 
	 * @param module
	 * @param text
	 */
	public void statusBarAdd(String module, String text) {
		statusItems.put(module, text);
		((Label) statusBar).setText("Processing (" + text + ")");
	}

	/**
	 * Remove an item from the status bar stack.
	 * 
	 * @param module
	 */
	public void statusBarRemove(String module) {
		statusItems.remove(module);
		if (statusItems.size() > 0) {
			((Label) statusBar).setText("Processing");
		} else {
			((Label) statusBar).setText("Ready");
		}
	}

	public String getLocale() {
		return locale;
	}

	public Integer getDefaultProvider() {
		return defaultProvider;
	}

	public TabPanel getTabPanel() {
		return tabPanel;
	}

	public Toaster getToaster() {
		return toaster;
	}

	public HashMap<Integer, PatientScreen> getPatientScreenMap() {
		return patientScreenMap;
	}

}
