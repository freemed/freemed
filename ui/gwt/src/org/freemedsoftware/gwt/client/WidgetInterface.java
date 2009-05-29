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
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.Composite;

public abstract class WidgetInterface extends Composite {

	protected CurrentState state = null;

	protected Command setStateCommand = null;

	protected List<WidgetInterface> children = new ArrayList<WidgetInterface>();

	public void assignState(CurrentState s) {
		setState(s);
	}

	public void setState(CurrentState s) {
		state = s;
		if (setStateCommand != null) {
			setStateCommand.execute();
		}

		if (children.size() > 0) {
			Iterator<WidgetInterface> iter = children.iterator();
			while (iter.hasNext()) {
				WidgetInterface c = iter.next();
				if (c != null) {
					JsonUtil.debug(this.getClass().getName() + " child:"
							+ c.getClass().getName());
					c.setState(state);
				}
			}
		}
	}

	public void onSetState(Command cmd) {
		setStateCommand = cmd;
	}

	public CurrentState getState() {
		return state;
	}

	/**
	 * Method used to initialize widget, called after state is set.
	 */
	public void populateWidget() {
	}

	/**
	 * Method to set current widget value from HashMap of data. Defaults to no
	 * action.
	 * 
	 * @param data
	 */
	public void setFromData(HashMap<String, String> data) {
	}

}
