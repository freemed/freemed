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
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;

public class SupportModuleWidget extends AsyncPicklistWidgetBase {

	protected String moduleName = null;

	public SupportModuleWidget() {
		super();
	}

	public SupportModuleWidget(String module) {
		// Load superclass constructor first...
		super();
		setModuleName(module);
	}

	/**
	 * Set value of current widget based on integer value, asynchronously.
	 * 
	 * @param widgetValue
	 */
	public void setValue(Integer widgetValue) {
		value = widgetValue;
		if (Util.isStubbedMode()) {
			searchBox.setText("Stub Value");
			searchBox.setTitle("Stub Value");
		} else {
			ModuleInterfaceAsync service = null;
			try {
				service = ((ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
			} catch (Exception e) {
			}
			service.ModuleToTextMethod(moduleName, widgetValue,
					new AsyncCallback() {
						public void onSuccess(Object res) {
							String textual = (String) res;
							searchBox.setText(textual);
							searchBox.setTitle(textual);
						}

						public void onFailure(Throwable t) {

						}
					});

		}
	}

	/**
	 * Set module class name.
	 * 
	 * @param module
	 */
	public void setModuleName(String module) {
		moduleName = module;
	}

	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		if (Util.isStubbedMode()) {
			// Handle in a stubbed sort of way
			List items = new ArrayList();
			map.clear();
			addKeyValuePair(items, new String("Hackenbush, Hugo Z"),
					new String("1"));
			addKeyValuePair(items, new String("Firefly, Rufus T"), new String(
					"2"));
			cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
		} else {
			ModuleInterfaceAsync service = null;
			try {
				service = ((ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
			} catch (Exception e) {
			}

			service.ModuleSupportPicklistMethod(moduleName, req,
					new AsyncCallback() {
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
								addKeyValuePair(items, key, val);
							}
							cb.onSuggestionsReady(r,
									new SuggestOracle.Response(items));
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception thrown: ", t);
						}

					});
		}
	}

}
