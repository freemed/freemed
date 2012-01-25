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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.HashSetter;

import com.google.gwt.user.client.ui.RichTextArea;

public class CustomRichTextArea extends RichTextArea implements HashSetter {

	protected String hashMapping = null;

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getStoredValue() {
		return getText();
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		setHTML(data.get(hashMapping));
	}

	public native void insertHTML(String html) /*-{
		var el = this.@com.google.gwt.user.client.ui.UIObject::getElement()(); 
		if (el.contentWindow.document.selection) { 
		el.contentWindow.document.selection.createRange().pasteHTML(html); // For IE 
		} else { 
		el.contentDocument.execCommand('insertHTML',false,html); // For Mozilla 
		}
	}-*/;

}
