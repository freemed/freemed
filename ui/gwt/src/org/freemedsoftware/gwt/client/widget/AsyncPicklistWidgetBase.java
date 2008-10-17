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
import java.util.List;

import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ChangeListenerCollection;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.KeyboardListener;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.SuggestionEvent;
import com.google.gwt.user.client.ui.SuggestionHandler;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;
import com.google.gwt.user.client.ui.SuggestOracle.Suggestion;

abstract public class AsyncPicklistWidgetBase extends Composite {

	protected Integer value = new Integer(0);

	protected HashMap<String, String> map;

	// private final FlexTable listPanel;

	protected SuggestBox searchBox;

	protected TextBox textBox;

	private final VerticalPanel layout;

	private ChangeListenerCollection changeListeners;

	public AsyncPicklistWidgetBase() {
		// Log.setUncaughtExceptionHandler();

		map = new HashMap<String, String>();

		changeListeners = new ChangeListenerCollection();

		layout = new VerticalPanel();

		textBox = new TextBox();

		searchBox = new SuggestBox(new SuggestOracle() {
			public void requestSuggestions(Request r, Callback cb) {
				loadSuggestions(r.getQuery(), r, cb);
			}
		}, textBox);
		searchBox.addEventHandler(new SuggestionHandler() {
			public void onSuggestionSelected(SuggestionEvent e) {
				Suggestion s = e.getSelectedSuggestion();
				value = getValueFromText(s.getDisplayString());
				setTitle(s.getDisplayString());
				onSelected();
			}
		});
		searchBox.addKeyboardListener(new KeyboardListener() {

			public void onKeyDown(Widget sender, char keyCode, int modifiers) {
			}

			public void onKeyPress(Widget sender, char keyCode, int modifiers) {
			}

			public void onKeyUp(Widget sender, char keyCode, int modifiers) {
				switch (keyCode) {
				case KeyboardListener.KEY_ESCAPE:
				case KeyboardListener.KEY_BACKSPACE:
					// Clear any current values
					searchBox.setText("");
					searchBox.setTitle("");
					setValue(0);
					textBox.cancelKey();
					break;

				default:
					break;
				}
			}

		});
		searchBox.setLimit(10);
		layout.add(searchBox);

		initWidget(layout);
	}

	/**
	 * Defined in subclasses to actually return data.
	 * 
	 * @param req
	 * @param r
	 * @param cb
	 */
	abstract protected void loadSuggestions(String req, final Request r,
			final Callback cb);

	/**
	 * Resolve value of widget from full text.
	 * 
	 * @param text
	 * @return
	 */
	public Integer getValueFromText(String text) {
		String found = new String("");
		Iterator<String> keys = map.keySet().iterator();
		while (found.length() == 0 && keys.hasNext()) {
			String cur = (String) keys.next();
			if (cur.compareTo(text) == 0) {
				found = (String) map.get(cur);
			}
		}
		return new Integer((String) found);
	}

	/**
	 * Get string value of currently selected patient.
	 * 
	 * @return
	 */
	public String getText() {
		return searchBox.getText();
	}

	/**
	 * Get integer value of currently selected patient.
	 * 
	 * @return Current selected patient value
	 */
	public Integer getValue() {
		return value;
	}

	/**
	 * Set widget value.
	 * 
	 * @param v
	 */
	public void setValue(Integer v) {
		value = v;
	}

	public abstract void getTextForValue(Integer val);

	public void addChangeListener(ChangeListener listener) {
		if (changeListeners == null)
			changeListeners = new ChangeListenerCollection();
		changeListeners.add(listener);
	}

	public void removeChangeListener(ChangeListener listener) {
		if (changeListeners != null)
			changeListeners.remove(listener);
	}

	/**
	 * Fire change listeners attached to this object if there are any.
	 * 
	 */
	protected void onSelected() {
		if (changeListeners != null) {
			changeListeners.fireChange(this);
		}
	}

	/**
	 * Clear contents of composite widget.
	 * 
	 */
	public void clear() {
		searchBox.setText("");
		map.clear();
	}

	/**
	 * Map key and value pair into the widget. Only used by subclasses.
	 * 
	 * @param key
	 * @param value
	 */
	protected void addKeyValuePair(List<SuggestOracle.Suggestion> items,
			final String key, final String value) {
		// Log.debug("Adding key = " + key + ", value = " + value);
		map.put(key, value);
		items.add(new SuggestOracle.Suggestion() {
			public String getDisplayString() {
				return key;
			}

			public String getReplacementString() {
				return key;
			}

			@SuppressWarnings("unused")
			public String getValue() {
				return value;
			}
		});
	}
}
