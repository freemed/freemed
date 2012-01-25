/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;

import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TextBox;

public class DocumentScreen extends ScreenInterface {

	protected Integer myId = null;
	protected HashMap<String, String> data = null;
	protected SimplePanel sP = new SimplePanel();

	public DocumentScreen() {

		SimplePanel spanel = new SimplePanel();
		initWidget(spanel);

		final FlexTable flexTable = new FlexTable();
		spanel.setWidget(flexTable);
		flexTable.setSize("100%", "100%");

		final Label label = new Label("New Label");
		flexTable.setWidget(0, 0, label);

		final Label label_1 = new Label("New Label");
		flexTable.setWidget(1, 0, label_1);

		final Label label_2 = new Label("New Label");
		flexTable.setWidget(2, 0, label_2);

		final TextBox textBox = new TextBox();
		flexTable.setWidget(2, 1, textBox);
		textBox.setWidth("100%");

		flexTable.setWidget(3, 0, sP);
		flexTable.getFlexCellFormatter().setColSpan(3, 0, 2);

	}

	public void setData(Integer i) {
		myId = i;
		DjvuViewer djvu = new DjvuViewer();
		djvu.setInternalId(myId);
		sP.add(djvu);

	}

	public void saveData() {

	}

}
