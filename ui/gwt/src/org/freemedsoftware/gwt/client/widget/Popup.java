/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.widget;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.WidgetInterface;

import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.ScrollPanel;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class Popup extends PopupPanel {

	protected CurrentState state = null;

	protected WidgetInterface mWidget = null;

	protected final SimplePanel sPanelOuter = new SimplePanel();

	protected final SimplePanel sPanelInner = new SimplePanel();

	protected final ScrollPanel scrollPanel = new ScrollPanel();

	protected final VerticalPanel verticalPanel = new VerticalPanel();

	protected Integer widthmodifier = 4;

	protected Integer heightmodifier = 3;

	protected Integer widthoffset = 0;

	protected Integer heightoffset = 0;

	public Popup() {
		// Auto-hide ON
		super(true);
	}

	public void setState(CurrentState st) {
		state = st;
	}

	public CurrentState getState() {
		return state;
	}

	public void setNewWidget(WidgetInterface w) {
		mWidget = w;

		// Pass state on to child widget
		if (mWidget != null) {
			mWidget.setState(getState());
		}
	}

	public void initialize() {
		sPanelInner.setStylePrimaryName("freemed-Popup-sPanelInner");
		sPanelOuter.add(sPanelInner);
		sPanelOuter.setStylePrimaryName("freemed-Popup-sPanelOuter");
		sPanelOuter.setWidth(Integer.toString(Window.getClientWidth() / 2)
				.concat("px"));
		scrollPanel.setHeight(Integer.toString(Window.getClientHeight() / 2)
				.concat("px"));
		scrollPanel.add(mWidget);
		verticalPanel.add(scrollPanel);
		verticalPanel
				.add(new HTML(
						"<br/><br/><small>(Click outside this popup to close it)</small>"));
		sPanelInner.add(verticalPanel);
		setWidget(sPanelOuter);
		setPosition();
	}

	public void setPosition() {
		setPopupPositionAndShow(new PopupPanel.PositionCallback() {
			public void setPosition(int offsetWidth, int offsetHeight) {
				int left = ((Window.getClientWidth() - offsetWidth) / widthmodifier)
						+ widthoffset;
				int top = ((Window.getClientHeight() - offsetHeight) / heightmodifier)
						+ heightoffset;
				setPopupPosition(left, top);
				// setStylePrimaryName("freemed-MessageBox-Popup");
			}
		});
	}

	public void setWidthOffset(Integer i) {
		widthoffset = i;
	}

	public void setHeightOffset(Integer i) {
		heightoffset = i;
	}

	public void setWidthModifier(Integer modifier) {
		widthmodifier = modifier;
	}

	public void setHeightModifier(Integer modifier) {
		heightmodifier = modifier;
	}
}
