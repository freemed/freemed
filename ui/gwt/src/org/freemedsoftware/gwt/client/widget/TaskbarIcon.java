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

package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class TaskbarIcon extends Composite {

	public TaskbarIcon(String labelText, Image image, ClickListener l) {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		verticalPanel.add(image);
		verticalPanel.setCellVerticalAlignment(image, HasVerticalAlignment.ALIGN_BOTTOM);
		verticalPanel.setCellHorizontalAlignment(image, HasHorizontalAlignment.ALIGN_CENTER);

		final Label label = new Label(labelText);
		verticalPanel.add(label);
		verticalPanel.setCellHorizontalAlignment(label, HasHorizontalAlignment.ALIGN_CENTER);
		verticalPanel.setCellVerticalAlignment(label, HasVerticalAlignment.ALIGN_TOP);

		// Style this from CSS
		verticalPanel.setStyleName("taskbarIcon");
		
		// Push click listeners for both internal objects
		image.addClickListener(l);
		label.addClickListener(l);
	}
	
}

