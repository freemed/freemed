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

import org.freemedsoftware.gwt.client.screen.PatientScreen;

import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.TabPanel;

public abstract class PatientScreenInterface extends ScreenInterface {

	protected Integer patientId = new Integer(0);

	protected Integer internalId = new Integer(0);

	protected String moduleName;

	protected PatientScreen patientScreen = null;

	/**
	 * Pass current patient screen.
	 * 
	 * @param p
	 */
	
	public PatientScreenInterface() {
		super();
	}
	
	public PatientScreenInterface(String moduleName) {
		super(moduleName);
	}
	
	public void assignPatientScreen(PatientScreen p) {
		patientScreen = p;

		// If the parent screen exists, push patient id into patient screen
		if (patientScreen != null) {
			setPatientId(patientScreen.getPatient());
		}
	}

	/**
	 * Close this screen by removing it from the tab panel.
	 */
	public void closeScreen() {
		TabPanel t = patientScreen.getTabPanel();
		if(t.getWidgetIndex(this)!=-1){
			t.selectTab(t.getWidgetIndex(this) - 1);
			t.remove(t.getWidgetIndex(this));
		}
		Integer patientId = getPatientId();
		CurrentState.getPatientSubScreenMap().get(patientId).remove(this.getClass().getName());
	}

	/**
	 * Function to return static module name.
	 * 
	 * @return
	 */
	public String getModuleName() {
		return "";
	}

	/**
	 * Set patient id stored in this object.
	 * 
	 * @param id
	 */
	public void setPatientId(Integer id) {
		patientId = id;
	}

	public Integer getPatientId() {
		return patientId;
	}

}
