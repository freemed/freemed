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

package org.freemedsoftware.gwt.client.screen;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.widget.PatientWidget;

import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.SortableTable;

public class PatientSearchScreen extends ScreenInterface {

	protected PatientWidget wSmartSearch = null;

	public PatientSearchScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);
		flexTable.setWidth("100%");

		final Label smartSearchLabel = new Label("Smart Search : ");
		flexTable.setWidget(0, 0, smartSearchLabel);

		wSmartSearch = new PatientWidget();
		wSmartSearch.addChangeListener(new ChangeListener() {
			public void onChange(Widget w) {
				Integer val = wSmartSearch.getValue();
				try {
					if (val.compareTo(new Integer(0)) != 0) {
						spawnPatientScreen(wSmartSearch.getValue());
					}
				} catch (Exception e) {
					// Don't do anything if no patient is declared
				}
			}
		});
		flexTable.setWidget(0, 1, wSmartSearch);

		final Label fieldSearchLabel = new Label("Field Search : ");
		flexTable.setWidget(1, 0, fieldSearchLabel);

		final ListBox wFieldName = new ListBox();
		flexTable.setWidget(1, 1, wFieldName);
		wFieldName.setVisibleItemCount(1);

		final TextBox wFieldValue = new TextBox();
		flexTable.setWidget(2, 1, wFieldValue);
		wFieldValue.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final SortableTable sortableTable = new SortableTable();
		verticalPanel.add(sortableTable);
	}

	/**
	 * Create new tab for patient.
	 * 
	 * @param patient
	 */
	public void spawnPatientScreen(Integer patient) {
		// TODO: Force spawning of patient screen
	}

}
