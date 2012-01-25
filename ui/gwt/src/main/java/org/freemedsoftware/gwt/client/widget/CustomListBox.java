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

import com.google.gwt.dom.client.Document;
import com.google.gwt.event.dom.client.DomEvent;
import com.google.gwt.user.client.ui.ListBox;

public class CustomListBox extends ListBox implements HashSetter {

	protected String hashMapping = null;

	public String getStoredValue() {
		return getWidgetValue();
	}

	/**
	 * Determine the string value.
	 * 
	 * @return
	 */
	public String getWidgetValue() {
		try {
			return getValue(getSelectedIndex());
		} catch (Exception e) {
			return new String("");
		}
	}
	
	public String getWidgetText() {
		try {
			return getItemText(getSelectedIndex());
		} catch (Exception e) {
			return new String("");
		}
	}
	
	public CustomListBox(){
	}
	public CustomListBox(boolean isMultiSelecionList){
		super(isMultiSelecionList);
	}

	/**
	 * Set the active value of the ListBox widget to be val.
	 * 
	 * @param val
	 */
	public void setWidgetValue(String val) {
		if (getItemCount() > 0) {
			for (int iter = 0; iter < getItemCount(); iter++) {
				if (getValue(iter).compareTo(val) == 0) {
					this.setItemSelected(iter, true);
					
				}
			}
		}
	}
	
	/**
	 * Set the active value of the ListBox widget to be val.
	 * 
	 * @param val
	 * @param fireevent fires associated events
	 */
	public void setWidgetValue(String val,boolean fireEvent) {
		if (getItemCount() > 0) {
			for (int iter = 0; iter < getItemCount(); iter++) {
				if (getValue(iter).compareTo(val) == 0) {
					this.setItemSelected(iter, true);
					DomEvent.fireNativeEvent(Document.get().createChangeEvent(),
							this); 
				}
			}
		}
	}
	
	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		setWidgetValue(data.get(hashMapping));
	}

}
