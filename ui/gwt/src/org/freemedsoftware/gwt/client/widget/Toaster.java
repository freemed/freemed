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

import java.util.HashMap;
import java.util.Iterator;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class Toaster extends PopupPanel {

	public static final int TOASTER_INFO = 0;

	public static final int TOASTER_WARN = 1;

	public static final int TOASTER_ERROR = 2;

	/**
	 * @gwt.typeArgs <java.lang.String,java.lang.String>
	 */
	protected HashMap items = null;

	/**
	 * @gwt.typeArgs <java.lang.String,com.google.gwt.user.client.ui.HTML>
	 */
	protected HashMap itemWidgets = null;

	protected VerticalPanel container = null;

	protected int timeout = 5;

	public Toaster() {
		super(false);
		items = new HashMap();
		itemWidgets = new HashMap();
		container = new VerticalPanel();
		container.setStylePrimaryName("freemed-Toaster");
		add(container);
	}

	/**
	 * Compatibility function to add TOASTER_INFO item.
	 * 
	 * @param module
	 * @param value
	 */
	public void addItem(String module, String value) {
		addItem(module, value, TOASTER_INFO);
	}

	/**
	 * Add additional item to toaster.
	 * 
	 * @param module
	 * @param value
	 * @param toasterStatus
	 *            TOASTER_INFO, TOASTER_WARN, TOASTER_ERROR
	 */
	public void addItem(final String module, String value, int toasterStatus) {
		// Add items to hash
		items.put(module, value);

		// Create new HTML segment, add
		HTML x = new HTML(value);
		x.setStylePrimaryName("freemed-ToasterItem");
		if (toasterStatus == TOASTER_INFO) {
			x.addStyleDependentName("Info");
		} else if (toasterStatus == TOASTER_WARN) {
			x.addStyleDependentName("Warning");
		} else if (toasterStatus == TOASTER_ERROR) {
			x.addStyleDependentName("Error");
		} else {
			// Do nothing, invalid
		}
		x.setTitle(module);
		x.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				removeItem(module);
			}
		});
		itemWidgets.put(module, x);
		container.add(x);

		// Set timer for remove
		Timer t = new Timer() {
			public void run() {
				// After timer runs out, remove object
				removeItem(module);
			}
		};
		t.schedule(timeout * 1000);

		// Make sure we're displaying
		refreshDisplay();
	}

	protected void refreshDisplay() {
		// If there are items...
		if (items.keySet().size() > 0) {
			// Set positioning at bottom left of screen
			try {
				hide();
			} catch (Exception e) {
				GWT.log("Exception", e);
			}

			// Loop through and set CSS properly
			/**
			 * @gwt.typeArgs <java.lang.String>
			 */
			Iterator ks = items.keySet().iterator();
			while (ks.hasNext()) {
				final String k = (String) ks.next();
				if (!ks.hasNext()) {
					// If this is the last one, set to special
					((HTML) itemWidgets.get(k))
							.setStylePrimaryName("freemed-ToasterItem-Last");
				} else {
					// Otherwise regular style
					((HTML) itemWidgets.get(k))
							.setStylePrimaryName("freemed-ToasterItem");
				}
			}

			try {
				setPopupPositionAndShow(new PopupPanel.PositionCallback() {
					public void setPosition(int offsetWidth, int offsetHeight) {
						int left = (Window.getClientWidth() - offsetWidth) - 5;
						int top = (Window.getClientHeight() - offsetHeight) - 5;
						setPopupPosition(left, top);
					}
				});

			} catch (Exception e) {
				GWT.log("Exception attempting to popup toaster", e);
			}
		} else {
			hide();
		}
	}

	/**
	 * Remove item from toaster.
	 * 
	 * @param module
	 */
	public void removeItem(String module) {
		try {
			container.remove((Widget) itemWidgets.get(module));
		} catch (Exception e) {
		}
		try {
			itemWidgets.remove(module);
		} catch (Exception e) {
		}
		try {
			items.remove(module);
		} catch (Exception e) {
		}

		// Refresh
		refreshDisplay();
	}

	/**
	 * Set timeout for items, after which time they disappear.
	 * 
	 * @param seconds
	 */
	public void setTimeout(int seconds) {
		if (timeout > 0) {
			timeout = seconds;
		}
	}

}
