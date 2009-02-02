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

import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.HTML;

public class InfoDialog extends DialogBox {

	protected HTML content = new HTML();

	public InfoDialog() {
		// Make sure to add auto-close, passed to the parent constructor
		super(true);
		setAnimationEnabled(true);
		setStyleName("freemed-InfoDialog");

		setWidget(content);
	}

	/**
	 * Set displayed caption for dialog.
	 * 
	 * @param title
	 */
	public void setCaption(String title) {
		setText(title);
	}

	/**
	 * Change displayed HTML content.
	 * 
	 * @param text
	 */
	public void setContent(HTML text) {
		content.setHTML(text.getHTML());
	}
}
