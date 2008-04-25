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

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;

import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;

public class PatientWidget extends Composite {

	protected int value = 0;

	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	protected HashMap map;

	// private final FlexTable listPanel;

	private SuggestBox searchBox;

	private final VerticalPanel layout;

	public PatientWidget() {
		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		map = new HashMap();

		layout = new VerticalPanel();

		searchBox = new SuggestBox(new SuggestOracle() {
			public void requestSuggestions(Request r, Callback cb) {
				loadSuggestions(r.getQuery(), r, cb);
			}
		});
		searchBox.addChangeListener(new ChangeListener() {
			public void onChange(Widget w) {
				String cur = (String) ((SuggestBox) w).getText();
				((SuggestBox) w).setTitle(cur);
				value = getValueFromText(cur);
			}
		});

		layout.add(searchBox);

		// listPanel = new FlexTable();
		// layout.add(listPanel);

		initWidget(layout);
	}

	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		PatientInterfaceAsync service = null;
		try {
			service = ((PatientInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface"));
		} catch (Exception e) {
		}

		service.Picklist(req, 20, 10, new AsyncCallback() {
			public void onSuccess(Object data) {
				/**
				 * @gwt.typeArgs <java.lang.String,java.lang.String>
				 */
				final HashMap result = (HashMap) data;
				Set keys = result.keySet();
				Iterator iter = keys.iterator();

				List items = new ArrayList();
				map.clear();
				while (iter.hasNext()) {
					final String key = (String) iter.next();
					final String val = (String) result.get(key);
					map.put(key, val);
					items.add(new SuggestOracle.Suggestion() {
						public String getDisplayString() {
							return val;
						}

						public String getReplacementString() {
							return val;
						}
					});

				}
				cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
			}

			public void onFailure(Throwable t) {

			}

		});
	}

	/**
	 * Resolve value of widget from full text.
	 * 
	 * @param text
	 * @return
	 */
	public Integer getValueFromText(String text) {
		String found = new String("");
		Iterator keys = map.keySet().iterator();
		while (found.length() == 0 && keys.hasNext()) {
			String cur = (String) keys.next();
			if (cur.contentEquals(text)) {
				found = cur;
			}
		}
		return new Integer((String) found);
	}

	/**
	 * Get integer value of currently selected patient.
	 * 
	 * @return Current selected patient value
	 */
	public Integer getValue() {
		return new Integer(value);
	}

	/**
	 * Pass change listener through to internal widget.
	 * 
	 * @param changeListener
	 */
	public void addChangeListener( ChangeListener changeListener ) {
		searchBox.addChangeListener(changeListener);
	}
	
}
