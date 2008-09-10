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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.ReportingAsync;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class ReportingScreen extends ScreenInterface {

	protected CustomSortableTable reportTable;

	protected FlexTable reportParametersTable;

	protected static String locale = "";

	public ReportingScreen() {

		final HorizontalSplitPanel horizontalSplitPanel = new HorizontalSplitPanel();
		initWidget(horizontalSplitPanel);
		horizontalSplitPanel.setSize("100%", "100%");
		horizontalSplitPanel.setSplitPosition("50%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalSplitPanel.setLeftWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final Label pleaseChooseALabel = new Label("Please choose a report.");
		verticalPanel.add(pleaseChooseALabel);
		pleaseChooseALabel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);

		reportTable = new CustomSortableTable();
		verticalPanel.add(reportTable);
		reportTable.setWidth("100%");
		reportTable.setIndexName("report_uuid");
		reportTable.addColumn("Name", "report_name");
		reportTable.addColumn("Description", "report_desc");

		reportParametersTable = new FlexTable();
		horizontalSplitPanel.setRightWidget(reportParametersTable);
		reportParametersTable.setVisible(false);
		reportParametersTable.setSize("100%", "100%");

		if (Util.isStubbedMode()) {
			// TODO: Stub this somehow
		} else {
			populate();
		}
	}

	public void populate() {
		getProxy().GetReports(locale,
				new AsyncCallback<HashMap<String, String>[]>() {
					public void onSuccess(HashMap<String, String>[] r) {
						reportTable.loadData(r);
					}

					public void onFailure(Throwable t) {
						GWT.log("Exception", t);
					}
				});
	}

	protected ReportingAsync getProxy() {
		ReportingAsync p = null;
		try {
			p = (ReportingAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Module.Reporting");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}

}
