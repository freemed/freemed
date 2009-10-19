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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.List;

import org.cobogw.gwt.user.client.CSS;

import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Widget;

public class ImageCompositedForm extends Composite {

	protected Image background = new Image();

	protected List<Widget> widgets = new ArrayList<Widget>();

	public ImageCompositedForm() {
		super();
	}

	public void setImage(String imageUrl) {
		background.setUrl(imageUrl);
		CSS.setProperty(background, CSS.A.Z_INDEX, "0");
	}

	public void setImage(Image image) {
		background = image;
		CSS.setProperty(background, CSS.A.Z_INDEX, "0");
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
	 */
	public void addWidget(Widget w, int x, int y) {
		// Add widget to list of widgets
		widgets.add(w);

		// Absolutely position widget at a higher z index
		CSS.setProperty(w, CSS.A.Z_INDEX, "1");
		CSS.setProperty(w, CSS.A.POSITION, CSS.V.POSITION.ABSOLUTE);
		CSS.setPropertyPx(w, CSS.A.LEFT, background.getAbsoluteLeft() + x);
		CSS.setPropertyPx(w, CSS.A.TOP, background.getAbsoluteTop() + y);
	}

	/**
	 * Remove widget from composited form.
	 * 
	 * @param w
	 */
	public void removeWidget(Widget w) {
		widgets.remove(w);
	}

}
