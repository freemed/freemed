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

import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.Iterator;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.FocusWidget;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.ui.FlexTable.FlexCellFormatter;

public class MultipleKeyValueEntry extends Composite {

	public enum WidgetType {
		DEFAULT, TEXTBOX, SELECT, MODULEPICKLIST, DATEPICKER, SELECT_YN
	};

	public class KeyValuePair {
		private String key;

		private String displayName;

		private String value;

		private WidgetType type;

		private String constraints;

		public KeyValuePair() {
		}

		public KeyValuePair(HashMap<String, String> map) {
			setKey(map.get("key"));
			setValue(map.get("value"));
			setDisplayName(map.get("displayName"));
			String t = map.get("type");
			if (t.equalsIgnoreCase("select")) {
				setType(WidgetType.SELECT);
			} else if (t.equalsIgnoreCase("yn")
					|| t.equalsIgnoreCase("select_yn")) {
				setType(WidgetType.SELECT_YN);
			} else if (t.equalsIgnoreCase("text")) {
				setType(WidgetType.TEXTBOX);
			} else if (t.equalsIgnoreCase("module")
					|| t.equalsIgnoreCase("modulepicklist")) {
				setType(WidgetType.MODULEPICKLIST);
			} else if (t.equalsIgnoreCase("date")
					|| t.equalsIgnoreCase("datepicker")) {
				setType(WidgetType.DATEPICKER);
			} else {
				// Default is free text
				setType(WidgetType.TEXTBOX);
			}
			setConstraints(map.get("constraints"));
		}

		public String getKey() {
			return key;
		}

		public String getDisplayName() {
			return displayName;
		}

		public String getValue() {
			return value;
		}

		public WidgetType getType() {
			return type;
		}

		public String getConstraints() {
			return constraints;
		}

		public void setKey(String n) {
			key = n;
		}

		public void setDisplayName(String n) {
			displayName = n;
		}

		public void setValue(String n) {
			value = n;
		}

		public void setType(WidgetType n) {
			type = n;
		}

		public void setConstraints(String n) {
			constraints = n;
		}
	}

	protected class KVChangeHandler implements ValueChangeHandler<Integer>,
			ChangeHandler {
		@Override
		public void onValueChange(ValueChangeEvent<Integer> event) {
			myAction();
		}

		@Override
		public void onChange(ChangeEvent event) {
			myAction();
		}

		public void myAction() {
			// Acquire value from current widget
			String v = getValueWidgetValue();

			// Grab value pair and set current
			KeyValuePair kvp = keys.get(currentKey);
			kvp.setValue(v);

			// Push back in
			keys.put(currentKey, kvp);

			// Clear the form
			valueWidget.setVisible(false);
			keyWidget.setWidgetValue("");
			currentKey = "";
			currentType = null;
		}
	};

	protected FlexTable layoutTable;

	protected CustomListBox keyWidget;

	protected Widget valueWidget;

	protected CustomTable displayTable;

	protected HashMap<String, KeyValuePair> keys;

	protected String currentKey;

	protected WidgetType currentType;

	protected HashMap<String, HashMap<String, String>> displayData = new HashMap<String, HashMap<String, String>>();

	protected KVChangeHandler onSelect = new KVChangeHandler();

	public MultipleKeyValueEntry() {
		layoutTable = new FlexTable();
		FlexCellFormatter formatter = layoutTable.getFlexCellFormatter();
		initWidget(layoutTable);

		keyWidget = new CustomListBox();
		keyWidget.setTabIndex(1);
		keyWidget.setVisibleItemCount(1);
		keyWidget.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent evt) {
				Widget sender = (Widget) evt.getSource();
				if (((CustomListBox) sender).getWidgetValue().length() > 0) {
					// Only fire change if something is actually selected
					onChangeKey(((CustomListBox) sender).getWidgetValue());
				}
			}
		});
		layoutTable.setWidget(0, 0, keyWidget);

		// Default constraints ...
		setValueWidgetType(WidgetType.DEFAULT, "");
		layoutTable.setWidget(0, 1, valueWidget);

		displayTable = new CustomTable();
		displayTable.addColumn("Name", "displayName");
		displayTable.addColumn("Value", "value");
		layoutTable.setWidget(1, 0, displayTable);
		formatter.setColSpan(0, 1, 2);
	}

	/**
	 * To be called when the active key (the one being edited) is changed.
	 * 
	 * @param newKey
	 *            String key name
	 */
	protected void onChangeKey(String newKey) {
		Iterator<String> iter = keys.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			if (k.compareTo(newKey) == 0) {
				KeyValuePair kvp = keys.get(k);

				// Set current key
				currentKey = k;

				// Set the widget to the appropriate type
				setValueWidgetType(kvp.getType(), kvp.getConstraints());

				// Assign value if there is one
				assignValue(kvp.getType(), kvp.getValue());
			}
		}
	}

	@SuppressWarnings("unchecked")
	protected void addKey(KeyValuePair k) {
		// Add to master HashMap
		keys.put(k.getKey(), k);

		// Add to keyWidget
		keyWidget.addItem(k.getDisplayName(), k.getKey());

		// Add to display table, if appropriate
		if (k.getValue().length() > 0) {
			HashMap<String, String> displayItem = new HashMap<String, String>();
			displayItem.put("displayName", k.getDisplayName());
			displayItem.put("value", k.getValue());
			// Update in display data
			displayData.put(k.getKey(), displayItem);
			displayTable.loadData((HashMap<String, String>[]) displayData
					.values().toArray(new HashMap<?, ?>[0]));
		}
	}

	public Date importSqlDate(String date) {
		Calendar calendar = new GregorianCalendar();
		calendar.set(Calendar.YEAR, Integer.parseInt(date.substring(0, 4)));
		calendar
				.set(Calendar.MONTH, Integer.parseInt(date.substring(5, 7)) - 1);
		calendar.set(Calendar.DATE, Integer.parseInt(date.substring(8, 10)));

		calendar.set(Calendar.HOUR, 1);
		calendar.set(Calendar.MINUTE, 0);
		calendar.set(Calendar.SECOND, 0);
		calendar.set(Calendar.MILLISECOND, 0);

		return new Date(calendar.getTime().getTime());
	}

	/**
	 * Assign a preexisting value to the currently displayed entry widget.
	 * 
	 * @param newType
	 *            <WidgetType> enumerated value
	 * @param newValue
	 *            String value
	 */
	protected void assignValue(WidgetType newType, String newValue) {
		switch (newType) {
		case SELECT:
		case SELECT_YN:
			((CustomListBox) valueWidget).setWidgetValue(newValue);
			break;
		case DATEPICKER:
			((CustomDatePicker) valueWidget).setValue(importSqlDate(newValue));
			break;
		case MODULEPICKLIST:
			((SupportModuleWidget) valueWidget).setValue(new Integer(newValue));
			break;
		case TEXTBOX:
		default:
			((TextBox) valueWidget).setText(newValue);
			break;
		}
	}

	/**
	 * Import all data into widget.
	 * 
	 * @param data
	 */
	public void loadData(HashMap<String, String>[] data) {
		for (int iter = 0; iter < data.length; iter++) {
			addKey(new KeyValuePair(data[iter]));
		}
	}

	/**
	 * Get all values assigned by this widget.
	 * 
	 * @return
	 */
	public HashMap<String, String> getAllValues() {
		HashMap<String, String> v = new HashMap<String, String>();
		Iterator<KeyValuePair> i = keys.values().iterator();
		while (i.hasNext()) {
			KeyValuePair kvp = i.next();
			// Avoid null values
			if (kvp.value.length() > 0) {
				v.put(kvp.getKey(), kvp.getValue());
			}
		}
		return v;
	}

	/**
	 * Get current "value" for displayed form.
	 * 
	 * @return
	 */
	protected String getValueWidgetValue() {
		switch (currentType) {
		case SELECT:
		case SELECT_YN:
			return ((CustomListBox) valueWidget).getWidgetValue();
		case DATEPICKER:
			return ((CustomDatePicker) valueWidget).getStoredValue();
		case MODULEPICKLIST:
			return ((SupportModuleWidget) valueWidget).getValue().toString();
		case TEXTBOX:
		default:
			return ((TextBox) valueWidget).getText();
		}
	}

	protected void setValueWidgetType(WidgetType newType, String constraints) {
		currentType = newType;
		switch (newType) {
		case SELECT:
			valueWidget = new CustomListBox();
			((CustomListBox) valueWidget).setVisibleItemCount(1);
			// CSV of values for constraints ....
			String[] options = constraints.split(",");
			for (int iter = 0; iter < options.length; iter++) {
				((ListBox) valueWidget).addItem(options[iter]);
			}
			((CustomListBox) valueWidget).addChangeHandler(onSelect);
			break;
		case SELECT_YN:
			valueWidget = new CustomListBox();
			((CustomListBox) valueWidget).setVisibleItemCount(1);
			((CustomListBox) valueWidget).addItem("Yes", "1");
			((CustomListBox) valueWidget).addItem("No", "0");
			((CustomListBox) valueWidget).addChangeHandler(onSelect);
			break;
		case DATEPICKER:
			valueWidget = new CustomDatePicker();
			((CustomDatePicker) valueWidget)
					.addValueChangeHandler(new ValueChangeHandler<Date>() {
						@Override
						public void onValueChange(ValueChangeEvent<Date> event) {
							// Acquire value from current widget
							String v = getValueWidgetValue();

							// Grab value pair and set current
							KeyValuePair kvp = keys.get(currentKey);
							kvp.setValue(v);

							// Push back in
							keys.put(currentKey, kvp);

							// Clear the form
							valueWidget.setVisible(false);
							keyWidget.setWidgetValue("");
							currentKey = "";
							currentType = null;
						}
					});
			break;
		case MODULEPICKLIST:
			valueWidget = new SupportModuleWidget();
			((SupportModuleWidget) valueWidget).addChangeHandler(onSelect);
			break;
		case TEXTBOX:
		default:
			valueWidget = new TextBox();
			((TextBox) valueWidget).addChangeHandler(onSelect);
			break;
		}
		try {
			((FocusWidget) valueWidget).setTabIndex(2);
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}

		// Make sure everything is visible...
		valueWidget.setVisible(true);
	}

}
