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
import org.freemedsoftware.gwt.client.Api.TableMaintenanceAsync;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SupportDataScreen extends ScreenInterface {

	protected CustomSortableTable sortableTable;

	public SupportDataScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		sortableTable = new CustomSortableTable();
		sortableTable.setIndexName("module_class");
		sortableTable.addColumn("Name", "module_name");
		sortableTable.addColumn("Version", "module_version");
		sortableTable.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents e, int row, int col) {
				String moduleName = sortableTable.getValueByRow(row);
				handleClick(moduleName);
			}
		});
		verticalPanel.add(sortableTable);

		// When everything else is done, populate
		populate();
	}

	public void populate() {
		TableMaintenanceAsync proxy = null;
		try {
			proxy = (TableMaintenanceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.TableMaintenance");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		proxy.GetModules("SupportModule", "", false,
				new AsyncCallback<HashMap<String, String>[]>() {
					public void onSuccess(HashMap<String, String>[] res) {
						sortableTable.loadData(res);
					}

					public void onFailure(Throwable t) {
						state.getToaster().addItem("SupportDataScreen",
								"Could not load list of support data modules.",
								Toaster.TOASTER_ERROR);
					}
				});
	}

	protected void handleClick(String moduleName) {
		// Since we can't dynamically load code segments, we're back to the idea
		// of a nasty if..else statement to loop through possible places to
		// spawn this....
		if (moduleName.compareToIgnoreCase("ProviderModule") == 0) {

		}
	}
}
