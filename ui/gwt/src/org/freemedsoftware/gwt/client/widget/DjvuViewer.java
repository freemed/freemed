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
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class DjvuViewer extends Composite {

	protected String viewerType = null;
	
	public DjvuViewer( ) {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel.setWidth("100%");

		final PushButton wBackTop = new PushButton("Up text", "Down text");
		horizontalPanel.add(wBackTop);
		wBackTop.setText("-");

		final Label wPageTop = new Label("1 of 1");
		horizontalPanel.add(wPageTop);
		wPageTop.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		final PushButton wForwardTop = new PushButton("Up text", "Down text");
		horizontalPanel.add(wForwardTop);
		wForwardTop.setText("+");

		final Image wImage = new Image();
		verticalPanel.add(wImage);
		wImage.setSize("100%", "100%");

		final HorizontalPanel horizontalPanel_1 = new HorizontalPanel();
		verticalPanel.add(horizontalPanel_1);
		horizontalPanel_1.setWidth("100%");
		horizontalPanel_1.setVerticalAlignment(HasVerticalAlignment.ALIGN_BOTTOM);

		final PushButton wBackBottom = new PushButton("Up text", "Down text");
		horizontalPanel_1.add(wBackBottom);
		wBackBottom.setText("-");

		final Label wPageBottom = new Label("1 of 1");
		horizontalPanel_1.add(wPageBottom);
		wPageBottom.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		final PushButton wForwardBottom = new PushButton("Up text", "Down text");
		horizontalPanel_1.add(wForwardBottom);
		wForwardBottom.setText("+");
	}
	
	/**
	 * Set string indicating URL used for image transfer from JSON relay.
	 * 
	 * @param type
	 */
	public void setType( String type ) {
		viewerType = type;
	}
	
}

