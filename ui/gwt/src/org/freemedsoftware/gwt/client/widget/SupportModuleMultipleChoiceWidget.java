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
import java.util.Arrays;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;

import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class SupportModuleMultipleChoiceWidget extends Composite {

	protected SupportModuleWidget supportModuleWidget = null;

	protected String module = null;

	protected VerticalPanel container = null;

	protected Integer[] widgetValues;

	public SupportModuleMultipleChoiceWidget() {
		init();
	}

	public SupportModuleMultipleChoiceWidget(String moduleName) {
		init();
		setModuleName(moduleName);
	}

	private void init() {
		final VerticalPanel v = new VerticalPanel();
		initWidget(v);

		container = new VerticalPanel();
		v.add(container);

		// Add picklist for this ...
		supportModuleWidget = new SupportModuleWidget();
		v.add(supportModuleWidget);
	}

	protected void addValue(String text, final Integer value) {
		// Push into internal store of values
		widgetValues[widgetValues.length] = value;

		// Create new container, push in
		final HorizontalPanel hp = new HorizontalPanel();
		hp.add(new Label(text));
		Button removeButton = new Button();
		removeButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				// Remove the hp object from its parent container, "container"
				w.getParent().removeFromParent();

				// Remove from data store
				removeValueFromStore(value);
			}
		});
		hp.add(removeButton);

		container.add(hp);
	}

	/**
	 * Remove all current values from the widget.
	 */
	protected void clearValues() {
		container.clear();
		widgetValues = null;
	}

	/**
	 * Get integer values associated with this widget.
	 * 
	 * @return
	 */
	public Integer[] getValues() {
		return widgetValues;
	}

	/**
	 * Get all integer values squished into a comma separated string.
	 * 
	 * @return
	 */
	public String getCommaSeparatedValues() {
		String buffer = new String("");
		Iterator<Integer> iter = Arrays.asList(widgetValues).iterator();
		while (iter.hasNext()) {
			buffer += iter.next();
			if (iter.hasNext()) {
				buffer += ",";
			}
		}
		return buffer.toString();
	}

	/**
	 * Assign all integer values from a squished comma separated string.
	 * 
	 * @param v
	 * @return
	 */
	public void setCommaSeparatedValues(String v) {
		if (v != null) {
			String[] s = v.split(",");
			List<Integer> i = new ArrayList<Integer>();
			for (int iter = 0; iter < s.length; iter++) {
				i.add(new Integer(s[iter]));
			}
			setValue(i.toArray(new Integer[0]));
		}
	}

	/**
	 * Remove value from the internal widget data store
	 * 
	 * @param value
	 */
	protected void removeValueFromStore(Integer value) {
		Integer[] temp = {};
		for (int iter = 0; iter < widgetValues.length; iter++) {
			if (widgetValues[iter].compareTo(value) != 0) {
				temp[temp.length] = widgetValues[iter];
			}
		}
		widgetValues = temp;
	}

	public void setModuleName(String moduleName) {
		module = moduleName;

		// Pass along to child widgets
		supportModuleWidget.setModuleName(moduleName);
	}

	public void setValue(Integer[] values) {
		// In future, need to implement multiple resolve function so we don't
		// have to make a thousand RPC calls for a thousand entries. - Jeff

		// Clear current values in the widget
		clearValues();

		// Loop through all values given
		for (int iter = 0; iter < values.length; iter++) {
			if (Util.isStubbedMode()) {
				addValue("Value " + new Integer(iter).toString(), new Integer(
						iter));
			} else {
				ModuleInterfaceAsync service = null;
				try {
					service = ((ModuleInterfaceAsync) Util
							.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
				} catch (Exception e) {
				}
				final Integer i = new Integer(iter);
				service.ModuleToTextMethod(module, i,
						new AsyncCallback<String>() {
							public void onSuccess(String textual) {
								addValue(textual, i);
							}

							public void onFailure(Throwable t) {

							}
						});
			}
		}
	}

}
