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

package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import com.bouwkamp.gwt.user.client.ui.RoundedPanel;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DisclosurePanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientInfoBar extends Composite {

	protected Label wPatientName;

	protected HTML wPatientHiddenInfo;

	protected Integer patientId = new Integer(0);

	public PatientInfoBar() {
		final RoundedPanel container = new RoundedPanel();
		initWidget(container);
		container.setCornerColor("#ccccff");
		container.setStylePrimaryName("freemed-PatientInfoBar");
		container.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		container.add(horizontalPanel);

		wPatientName = new Label("");
		horizontalPanel.add(wPatientName);

		final DisclosurePanel wDropdown = new DisclosurePanel("");
		final VerticalPanel wDropdownContainer = new VerticalPanel();
		wDropdown.add(wDropdownContainer);
		wPatientHiddenInfo = new HTML();
		wDropdownContainer.add(wPatientHiddenInfo);
		horizontalPanel.add(wDropdown);
	}

	public void setPatient(int patientId) {

	}

	/**
	 * Set patient information with HashMap returned from PatientInformation()
	 * method.
	 * 
	 * @param map
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public void setPatientFromMap(HashMap map) {
		try {
			wPatientName.setText((String) map.get("patient_name"));
		} catch (Exception e) {
		}
		try {
			wPatientHiddenInfo.setHTML("<small>"
					+ (String) map.get("address_line_1") + "<br/>"
					+ (String) map.get("address_line_2") + "<br/>"
					+ (String) map.get("csz") + "<br/>" + "H:"
					+ (String) map.get("pthphone") + "<br/>" + "W:"
					+ (String) map.get("ptwphone") + "</small>");
		} catch (Exception e) {
		}
		try {
			patientId = new Integer((String) map.get("id"));
		} catch (Exception e) {
		}
	}

}
