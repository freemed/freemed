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
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.MouseListener;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.Widget;

public class ClosableTab extends Composite {

	protected Widget widget;
	
	public ClosableTab(String labelText, Widget w) {
		// Store in namespace where we can see it later
		widget = w;
		
		final HorizontalPanel panel = new HorizontalPanel();
		initWidget(panel);

		final Label label = new Label(labelText);
		panel.add(label);
		panel.setCellHorizontalAlignment(label, HasHorizontalAlignment.ALIGN_LEFT);
		panel.setCellVerticalAlignment(label, HasVerticalAlignment.ALIGN_TOP);

		final Image image = new Image("resources/images/x_stop.16x16.png");

		// Create spacer
		panel.add(new HTML("&nbsp;"));
		
		panel.add(image);
		image.setUrl("resources/images/close_x.16x16.png");
		panel.setCellVerticalAlignment(image, HasVerticalAlignment.ALIGN_TOP);
		panel.setCellHorizontalAlignment(image, HasHorizontalAlignment.ALIGN_RIGHT);

		image.addClickListener(new ClickListener() {
			public void onClick( Widget thisWidget ) {
				TabPanel t = ((TabPanel) widget.getParent().getParent().getParent());
				t.selectTab(t.getWidgetIndex(widget) - 1);
				widget.removeFromParent();
			}
		});
	}
	
}

