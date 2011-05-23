/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2011 FreeMED Software Foundation
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

import com.google.gwt.event.dom.client.HasNativeEvent;
import com.google.gwt.event.dom.client.MouseDownEvent;
import com.google.gwt.event.dom.client.MouseDownHandler;
import com.google.gwt.event.dom.client.MouseMoveEvent;
import com.google.gwt.event.dom.client.MouseMoveHandler;
import com.google.gwt.event.dom.client.MouseUpEvent;
import com.google.gwt.event.dom.client.MouseUpHandler;
import com.google.gwt.event.dom.client.MouseWheelEvent;
import com.google.gwt.event.dom.client.MouseWheelHandler;
import com.google.gwt.event.shared.HandlerRegistration;
import com.google.gwt.user.client.Window;
import com.google.gwt.widgetideas.graphics.client.GWTCanvas;

public class CustomCanvas extends GWTCanvas {

	public CustomCanvas() {
		super();
	}

	public CustomCanvas(int coordX, int coordY) {
		super(coordX, coordY);
	}

	public CustomCanvas(int coordX, int coordY, int pixelX, int pixelY) {
		super(coordX, coordY, pixelX, pixelY);
	}

	public HandlerRegistration addMouseHandler(MouseDownHandler handler) {
		return addDomHandler(handler, MouseDownEvent.getType());
	}

	public HandlerRegistration addMouseHandler(MouseUpHandler handler) {
		return addDomHandler(handler, MouseUpEvent.getType());
	}

	public HandlerRegistration addMouseHandler(MouseWheelHandler handler) {
		return addDomHandler(handler, MouseWheelEvent.getType());
	}

	public HandlerRegistration addMouseHandler(MouseMoveHandler handler) {
		return addDomHandler(handler, MouseMoveEvent.getType());
	}

	/**
	 * Calculate relative X coordinate on the Canvas element from a
	 * <HasNativeEvent> event.
	 * 
	 * @param event
	 * @return
	 */
	public int getXCoordFromEvent(HasNativeEvent event) {
		return event.getNativeEvent().getClientX() - getAbsoluteLeft()
				+ Window.getScrollLeft();
	}

	/**
	 * Calculate relative Y coordinate on the Canvas element from a
	 * <HasNativeEvent> event.
	 * 
	 * @param event
	 * @return
	 */
	public int getYCoordFromEvent(HasNativeEvent event) {
		return event.getNativeEvent().getClientY() - getAbsoluteTop()
				+ Window.getScrollTop();
	}

}
