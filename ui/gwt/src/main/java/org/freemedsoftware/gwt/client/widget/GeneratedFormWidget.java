/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2012 FreeMED Software Foundation
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

import java.util.ArrayList;
import java.util.List;

import org.freemedsoftware.gwt.client.HashSetter;

import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Widget;

public class GeneratedFormWidget extends Composite {

	protected FlexTable layout = new FlexTable();

	protected List<Widget> widgets = new ArrayList<Widget>();

	protected List<HashSetter> hashSetters = new ArrayList<HashSetter>();

	public GeneratedFormWidget() {
		super();
		initWidget(layout);

		layout.setVisible(true);
	}

	public List<HashSetter> getHashSetters() {
		return hashSetters;
	}

	/**
	 * Add widget to composited form.
	 * 
	 * @param w
	 *            Widget
	 * @param x
	 *            Horizontal offset, top left corner
	 * @param y
	 *            Vertical offset, top left corner
	 * @param text
	 *            Textual description, displayed
	 */
	public void addWidget(Widget w, int x, int y, String text) {
		// Add widget to list of widgets
		widgets.add(w);
		if (w instanceof HashSetter) {
			hashSetters.add((HashSetter) w);
		}

		int rowCount = hashSetters.indexOf(w);
		layout.setText(rowCount, 0, text);
		layout.setWidget(rowCount, 1, w);
	}

	/**
	 * Remove widget from form.
	 * 
	 * @param w
	 */
	public void removeWidget(Widget w) {
		int rowPos = hashSetters.indexOf(w);
		widgets.remove(w);
		if (w instanceof HashSetter) {
			hashSetters.remove((HashSetter) w);
		}
		try {
			layout.clearCell(rowPos, 0);
			layout.clearCell(rowPos, 1);
		} catch (Exception ex) {
		}
	}

}
