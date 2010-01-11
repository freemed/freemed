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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.HashSetter;

import com.google.gwt.user.client.ui.RadioButton;

public class CustomRadioButtonSingle extends RadioButton implements HashSetter {

	protected String value = null;

	protected String hashMapping = null;

	public CustomRadioButtonSingle(String groupName, String value, String label) {
		super(groupName, label);
		this.value = value;
	}

	public void setHashMapping(String hashMapping) {
		this.hashMapping = hashMapping;
	}

	@Override
	public String getHashMapping() {
		return hashMapping;
	}

	/**
	 * Get value which would be used if this radio button was active.
	 * 
	 * @return
	 */
	public String getButtonValueString() {
		return value;
	}

	@Override
	public String getStoredValue() {
		return this.getValue() ? value : null;
	}

	@Override
	public void setFromHash(HashMap<String, String> data) {
		if (data.get(hashMapping).equalsIgnoreCase(value)) {
			this.setValue(Boolean.TRUE);
		}
	}

}
