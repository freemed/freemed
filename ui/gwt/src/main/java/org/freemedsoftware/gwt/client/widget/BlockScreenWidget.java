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

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.VerticalPanel;

/**
 * Display "blocking" screen which does not allow user interaction to occur
 * underneath it.
 */
public class BlockScreenWidget extends VerticalPanel {

	protected HTML blockOuterDiv = null;

	protected HTML blockInnerDiv = null;

	@SuppressWarnings("unused")
	private BlockScreenWidget() {
	}

	public BlockScreenWidget(String msg) {
		this.setWidth("100%");
		String blockDivOuterStr = "<div style=\"border: medium none ; margin: 0pt; padding: 0pt;";
		blockDivOuterStr += " z-index: 1000; width: " + Window.getClientWidth()
				+ "; height: " + Window.getClientHeight()
				+ "; top: 0pt; left: 0pt;";
		blockDivOuterStr += " background-color: rgb(0, 0, 0); filter: alpha(opacity = 60);opacity: 0.6; cursor: wait;";
		blockDivOuterStr += "  position: absolute;\" class=\"blockUI blockOverlay\" title=\"Please wait...\"";
		blockDivOuterStr += "/>";
		blockOuterDiv = new HTML(blockDivOuterStr);
		setText(msg);
		this.add(blockOuterDiv);
	}

	/**
	 * Set HTML text displayed on widget. This method does not escape or HTML
	 * format raw text.
	 * 
	 * @param msg
	 *            HTML-formatted and escaped text to be displayed.
	 */
	public void setText(String msg) {
		if (this.getWidgetIndex(blockInnerDiv) != -1) {
			this.remove(blockInnerDiv);
		}
		String blockDivInnerStr = "  <div style='border: medium none ; margin: 0px; padding: 15px; z-index: 1100;";
		blockDivInnerStr += " position: absolute; width: 30%; top: 40%; left: 35%; text-align: center; color: rgb(255, 255, 255);";
		blockDivInnerStr += " cursor: wait;";
		blockDivInnerStr += " filter: alpha(opacity = 50);opacity: 0.5;'>";
		blockDivInnerStr += "<img src=\"" + GWT.getHostPageBaseURL()
				+ "resources/images/loading.gif"
				+ "\" style=' margin-left: auto;margin-right: auto;'/><br/>";
		blockDivInnerStr += "<h1>" + msg + "</h1></div>";
		blockInnerDiv = new HTML(blockDivInnerStr);
		this.add(blockInnerDiv);
	}

}
