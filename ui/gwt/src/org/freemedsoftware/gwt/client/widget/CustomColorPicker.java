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

import java.util.HashMap;

import net.auroris.ColorPicker.client.ColorPicker;

import org.freemedsoftware.gwt.client.HashSetter;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;

public class CustomColorPicker extends ColorPicker implements HashSetter {

	protected String value = "";

	protected String hashMapping = null;

	@Override
	public void onChange(ChangeEvent e) {
		// Call superclass methods first
		super.onChange(e);

		// Assign value
		value = "#" + this.getHexColor();
	}

	/**
	 * Find color tuplet (like #ffffff)
	 * 
	 * @return
	 */
	public String getValue() {
		return value;
	}

	/**
	 * Set widget value from textual tuplet.
	 * 
	 * @param tuplet
	 */
	public void setValue(String tuplet) {
		try {
			setHex(tuplet.replaceAll("#", ""));
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
	}

	/**
	 * Prepare hex pair formatting for use in color triplets.
	 * 
	 * @param raw
	 * @return
	 */
	protected String formatHexPair(String raw) {
		if (raw.length() == 1) {
			return "0" + raw;
		}
		if (raw.length() > 2) {
			return raw.substring(raw.length() - 2);
		}
		return raw;
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getStoredValue() {
		return getValue();
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		setValue(data.get(hashMapping));
	}

}
