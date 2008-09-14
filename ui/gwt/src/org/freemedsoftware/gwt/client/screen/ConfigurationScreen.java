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

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.SystemConfigAsync;
import org.freemedsoftware.gwt.client.widget.CustomListBox;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ConfigurationScreen extends Composite {

	protected TabPanel tabPanel;

	protected HashMap<String, FlexTable> containers;

	protected HashMap<String, Widget> widgets;

	protected HashMap<String, Integer> containerWidgetCount;

	public ConfigurationScreen() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setWidth("100%");

		tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);
		// tabPanel.selectTab(0);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final Button commitChangesButton = new Button();
		horizontalPanel.add(commitChangesButton);
		commitChangesButton.setText("Commit Changes");
		commitChangesButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {

			}
		});

	}

	public void populate() {
		containers = new HashMap<String, FlexTable>();
		widgets = new HashMap<String, Widget>();
		containerWidgetCount = new HashMap<String, Integer>();

		if (Util.isStubbedMode()) {
			// TODO: Simulate
		} else {
			getProxy().GetConfigSections(new AsyncCallback<String[]>() {
				public void onSuccess(String[] r) {
					// Create the actual tabs
					createTabs(r);

					// Fire off population routine
					populateConfig();
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
	}

	public void populateConfig() {
		if (Util.isStubbedMode()) {
			// TODO: populate config values
		} else {
			getProxy().GetAll(new AsyncCallback<HashMap<String, String>[]>() {
				public void onSuccess(HashMap<String, String>[] r) {
					for (int iter = 0; iter < r.length; iter++) {
						addToStack(r[iter]);
					}
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
	}

	protected Widget addWidget(HashMap<String, String> r) {
		CustomListBox w = new CustomListBox();
		String[] options = r.get("options").split(",");
		for (int iter = 0; iter < options.length; iter++) {
			w.addItem(options[iter]);
		}
		// Add to index
		widgets.put(r.get("c_name"), w);
		return w;
	}

	protected void addToStack(HashMap<String, String> r) {
		// Add initial widget, get appropriate count and container
		Widget w = addWidget(r);
		FlexTable f = containers.get(r.get("c_section"));
		Integer c = containerWidgetCount.get(r.get("c_section"));

		// Populate proper row of FlexTable
		f.setText(c, 0, r.get("c_title"));
		f.setWidget(c, 1, w);

		// Update count for this particular container
		containerWidgetCount.put(r.get("c_section"), c + 1);
	}

	/**
	 * Create tabbed configuration containers from array of strings with titles
	 * and initialize all counters.
	 * 
	 * @param t
	 */
	protected void createTabs(String[] t) {
		for (int iter = 0; iter < t.length; iter++) {
			// Create container
			FlexTable f = new FlexTable();

			// Add to list of containers and add to present tab panel
			containers.put(t[iter], f);
			tabPanel.add(f, t[iter]);
			containerWidgetCount.put(t[iter], new Integer(0));
		}
	}

	protected SystemConfigAsync getProxy() {
		SystemConfigAsync proxy = null;
		try {
			proxy = (SystemConfigAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.SystemConfig");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		return proxy;
	}

}
