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

import java.util.*;
import com.google.gwt.core.client.GWT;
import org.freemedsoftware.gwt.client.*;
import org.freemedsoftware.gwt.client.Module.*;
import org.freemedsoftware.gwt.client.widget.*;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.*;
import com.google.gwt.user.client.rpc.*;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.StackPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.VerticalSplitPanel;
import com.thapar.gwt.user.ui.client.widget.SortableTable;

public class MainScreen extends Composite {

	protected final TabPanel tabPanel;
	protected final HorizontalSplitPanel statusBar;
	
	public MainScreen() {
		final DockPanel dockPanel = new DockPanel();
		initWidget(dockPanel);
		dockPanel.setSize("100%", "100%");

		final StackPanel taskPanel = new StackPanel();
		dockPanel.add(taskPanel, DockPanel.WEST);
		taskPanel.setWidth("200px");

		final VerticalPanel tasksSystem = new VerticalPanel();
		taskPanel.add(tasksSystem, "<div class=\"tasksTitle\">System</div>", true);
		tasksSystem.add(new TaskbarIcon("Messages",
				new Image("resources/images/messaging.32x32.png"),
				new ClickListener() {
			public void onClick( Widget w ) {
				final Messaging p = new Messaging();
				tabPanel.add(p, new ClosableTab("Messages", p));
			}
		}));
		
		statusBar = new HorizontalSplitPanel();
		dockPanel.add(statusBar, DockPanel.SOUTH);
		statusBar.setSize("100%", "25px");
		statusBar.setSplitPosition("50%");

		tabPanel = new TabPanel();
		dockPanel.add(tabPanel, DockPanel.CENTER);
		tabPanel.setWidth("100%");
		tabPanel.add(new HTML("Dashboard goes here"), "Dashboard");
		tabPanel.selectTab(0);
		
	}
	
}

