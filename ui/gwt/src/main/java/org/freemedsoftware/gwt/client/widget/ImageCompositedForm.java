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
import java.util.Iterator;
import java.util.List;

import org.cobogw.gwt.user.client.CSS;
import org.freemedsoftware.gwt.client.HashSetter;

import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Widget;

public class ImageCompositedForm extends Composite {

	protected AbsolutePanel layout = new AbsolutePanel();

	protected Image background = new Image();

	protected List<Widget> widgets = new ArrayList<Widget>();

	protected List<HashSetter> hashSetters = new ArrayList<HashSetter>();

	protected float opacity = (float) 0.7;

	public ImageCompositedForm() {
		super();
		initWidget(layout);

		layout.setVisible(true);
		background.setVisible(false);

		// Set background widget
		// CSS.setProperty(background, CSS.A.Z_INDEX, "0");
		layout.add(background, 0, 0);
	}

	public void setSize(int w, int h) {
		this.setSize(w + "px", h + "px");
		layout.setSize(w + "px", h + "px");
	}

	public void setOpacity(float o) {
		opacity = o;

		// Readjust existing widgets
		if (widgets.size() > 0) {
			Iterator<Widget> iter = widgets.iterator();
			while (iter.hasNext()) {
				CSS.setOpacity(iter.next().getElement(), opacity);
			}
		}
	}

	public void setImage(String imageUrl) {
		background.setUrl(imageUrl);
		background.setVisible(true);
		// CSS.setProperty(background, CSS.A.Z_INDEX, "0");
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
		if (w instanceof HashSetter) {
			hashSetters.add((HashSetter) w);
		}

		// Absolutely position widget at a higher z index
		CSS.setProperty(w, CSS.A.Z_INDEX, "1");
		CSS.setOpacity(w.getElement(), opacity);

		// Position widget on absolute panel
		layout.add(w, x, y);
	}

	/**
	 * Remove widget from composited form.
	 * 
	 * @param w
	 */
	public void removeWidget(Widget w) {
		widgets.remove(w);
		if (w instanceof HashSetter) {
			hashSetters.remove((HashSetter) w);
		}
		try {
			layout.remove(w);
		} catch (Exception ex) {
		}
	}

}
