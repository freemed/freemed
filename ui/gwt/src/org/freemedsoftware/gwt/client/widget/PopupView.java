/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.s
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

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.screen.MessagingComposeScreen;
import org.freemedsoftware.gwt.client.screen.MessagingScreen;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.FocusPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class PopupView extends WidgetInterface {

	protected Command onClose = null;

	protected HTML text = new HTML("");
	
	public PopupView() {
		final SimplePanel sPanel = new SimplePanel();
		initWidget(sPanel);
		VerticalPanel verticalPanel = new VerticalPanel();

		HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		// Button definitions:
		//
		// TODO: Replace Images by new ones
		// We don't use a Button, but an image+label inside a focusPanel,
		// because it
		// allows us, that the user can click on both to provoke the event
		//
		// Reply Button



		// Forward Button





		verticalPanel.add(text);
		sPanel.add(verticalPanel);

	}

	
	public PopupView(Widget w) {
		final SimplePanel sPanel = new SimplePanel();
		initWidget(sPanel);
		VerticalPanel verticalPanel = new VerticalPanel();

		HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		// Button definitions:
		//
		// TODO: Replace Images by new ones
		// We don't use a Button, but an image+label inside a focusPanel,
		// because it
		// allows us, that the user can click on both to provoke the event
		//
		// Reply Button



		// Forward Button



		verticalPanel.add(w);
		
		verticalPanel.setWidth("100%");

//		verticalPanel.add(text);
		sPanel.add(verticalPanel);

	}
	
	
	
	
	public void setText(String t) {
		text.setHTML(t);
	}

	public void setOnClose(Command c) {
		onClose = c;
	}

}
