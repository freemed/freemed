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
	
	public PatientInfoBar() {
		final RoundedPanel container = new RoundedPanel();
		initWidget(container);
		container.setCornerColor("#ccccff");
		container.setStylePrimaryName("freemed-PatientInfoBar");
		container.setWidth("100%");
		
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		container.add(horizontalPanel);

		wPatientName = new Label("Patient");
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

}
