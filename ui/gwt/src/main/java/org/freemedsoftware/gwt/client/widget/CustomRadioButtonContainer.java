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
import java.util.Iterator;

import org.freemedsoftware.gwt.client.HashSetter;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Command;

public class CustomRadioButtonContainer implements HashSetter, ClickHandler {

	protected HashMap<String, CustomRadioButtonSingle> widgets = new HashMap<String, CustomRadioButtonSingle>();

	protected String hashMapping = null;

	protected Command onChange = null;

	public CustomRadioButtonContainer() {
	}

	/**
	 * Add <CustomRadioButtonSingle> to stack of widgets.
	 * 
	 * @param value
	 * @param widget
	 */
	public void addItem(String value, CustomRadioButtonSingle widget) {
		widgets.put(value, widget);
	}

	public void setHashMapping(String hashMapping) {
		this.hashMapping = hashMapping;
	}

	@Override
	public String getHashMapping() {
		return hashMapping;
	}

	@Override
	public String getStoredValue() {
		Iterator<String> iter = widgets.keySet().iterator();
		while (iter.hasNext()) {
			String key = iter.next();
			if (widgets.get(key).getValue()) {
				return key;
			}
		}
		return null;
	}

	@Override
	public void setFromHash(HashMap<String, String> data) {
		Iterator<String> iter = widgets.keySet().iterator();
		while (iter.hasNext()) {
			String key = iter.next();
			if (data.get(hashMapping).equalsIgnoreCase(key)) {
				widgets.get(key).setValue(Boolean.TRUE);
			}
		}
	}

	@Override
	public void onClick(ClickEvent event) {
		if (event.getSource() instanceof CustomRadioButtonSingle) {
			if (onChange != null)
				this.onChange.execute();
		}
	}

	public void setOnChange(Command change) {
		this.onChange = change;
	}

	public void setValue(String value) {
		try {
			widgets.get(value).setValue(Boolean.TRUE);
		} catch (Exception ex) {
		}
	}

}
