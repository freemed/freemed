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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;

public class SummaryScreen extends PatientScreenInterface {

	protected CustomSortableTable summaryTable;

	/**
	 * @gwt.typeArgs <java.lang.String,java.lang.String>
	 */
	protected HashMap[] dataStore;

	public SummaryScreen() {

		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);
		flexTable.setSize("100%", "100%");

		summaryTable = new CustomSortableTable();
		summaryTable.addColumnHeader("Date", 0);
		summaryTable.addColumnHeader("Type", 1);
		summaryTable.addColumnHeader("Summary", 2);
		summaryTable.formatTable(20, 3);
		flexTable.setWidget(0, 0, summaryTable);

	}

	public void loadData() {
		if (Util.isStubbedMode()) {

		} else {
			PatientInterfaceAsync service = null;
			try {
				Util
						.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface");
			} catch (Exception e) {
				GWT.log("Failed to get proxy for PatientInterface", null);
			}
			service.EmrAttachmentsByPatient(patientId, new AsyncCallback() {
				public void onSuccess(Object result) {
					/**
					 * @gwt.typeArgs <java.lang.String, java.lang.String>
					 */
					HashMap[] r = (HashMap[]) result;
					dataStore = r;
					for (int iter=0; iter<r.length; iter++) {
						summaryTable.setText(iter+1, 0, (String) r[iter].get("stamp"));
						summaryTable.setText(iter+1, 1, (String) r[iter].get("type"));
						summaryTable.setText(iter+1, 2, (String) r[iter].get("summary"));
					}
				}

				public void onFailure(Throwable t) {

				}
			});
		}
	}
}
