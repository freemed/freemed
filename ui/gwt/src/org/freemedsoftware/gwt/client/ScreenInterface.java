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

import java.util.ArrayList;
import java.util.List;

import com.google.gwt.user.client.ui.TabPanel;

public abstract class ScreenInterface extends WidgetInterface {

	protected List<WidgetInterface> children = new ArrayList<WidgetInterface>();

	public ScreenInterface() {
		super();
	}

	/**
	 * Call function on getting tab focus.
	 */
	public void onFocus() {
	}

	/**
	 * Append additional child WidgetInterface to stack.
	 * 
	 * @param child
	 */
	public void addChildWidget(WidgetInterface child) {
		children.add(child);
	}

	/**
	 * Take a child WidgetInterface out of the stack.
	 * 
	 * @param child
	 */
	public void removeChildWidget(WidgetInterface child) {
		children.remove(child);
	}

	/**
	 * Remove all children WidgetInterface objects.
	 */
	public void clearChildWidgets() {
		children.clear();
	}

	/**
	 * Remove the current ScreenInterface from the parent TabPanel.
	 */
	public void closeScreen() {
		TabPanel t = CurrentState.getTabPanel();
		t.selectTab(t.getWidgetIndex(this) - 1);
		t.remove(t.getWidgetIndex(this));
	}

}
