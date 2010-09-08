/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class SimpleUIBuilder extends WidgetInterface {

	protected static String helpprefix = "Help for";

	public enum WidgetType {
		MODULE, MODULE_MULTIPLE, USER_MULTIPLE, TEXT, SELECT, PATIENT, COLOR, DELIMITER, DRUG, MULTILIST, SINGLELIST, DATE, CHECKBOX
	};

	/**
	 * Interface for any <SimpleUIBuilder> subclasses to receive information
	 * back from this piece.
	 * 
	 * @author jeff@freemedsoftware.org
	 * 
	 */
	public interface Receiver {

		/**
		 * Check to make sure data is valid.
		 * 
		 * @param data
		 * @return null if there are no errors, or else a list of errors.
		 */
		public String validateData(HashMap<String, String> data);

		/**
		 * Handle data.
		 * 
		 * @param data
		 */
		public void processData(HashMap<String, String> data);
	};

	protected FlexTable table;

	protected Receiver receiver = null;

	protected HashMap<String, Widget> widgets;

	public SimpleUIBuilder() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setWidth("100%");

		table = new FlexTable();
		verticalPanel.add(table);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		CustomButton commitChangesButton = new CustomButton("Commit Changes",
				AppConstants.ICON_ADD);
		horizontalPanel.add(commitChangesButton);
		commitChangesButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				// Collect data
				HashMap<String, String> data = new HashMap<String, String>();
				data = getValues();

				// If a receiver has been set, push there
				if (receiver != null) {
					String v = receiver.validateData(data);
					if (v == null) {
						receiver.processData(data);
					}
				} else {
					JsonUtil.debug("No receiver was defined.");
				}
			}
		});

		// Initialize widget container
		widgets = new HashMap<String, Widget>();
	}

	/**
	 * Allow assigning of event <Receiver> to this widget.
	 * 
	 * @param r
	 */
	public void setReceiver(Receiver r) {
		receiver = r;
	}

	/**
	 * Add widget to display
	 * 
	 * @param name
	 *            Variable name to be associated with this widget.
	 * @param title
	 *            Caption for display to the user.
	 * @param type
	 *            Widget type, textual.
	 * @param options
	 *            Optional, to describe additional options.
	 * @param value
	 *            Default value.
	 */
	public void addWidget(String name, String title, WidgetType type,
			String options, String value, String help) {
		Widget w;

		if (type.equals(WidgetType.TEXT)) {
			w = new CustomTextBox();
			try {
				Integer len = new Integer(options);
				if (len > 0) {
					JsonUtil.debug("addWidget " + name + " has length of "
							+ len);
					((CustomTextBox) w).setVisibleLength(len.intValue() + 1);
					((CustomTextBox) w).setMaxLength(len.intValue());
				}
			} catch (Exception ex) {
			}
		} else if (type.equals(WidgetType.MODULE)) {
			w = new SupportModuleWidget(options);
		} else if (type.equals(WidgetType.MODULE_MULTIPLE)) {
			w = new SupportModuleMultipleChoiceWidget(options);
		} else if (type.equals(WidgetType.USER_MULTIPLE)) {
			w = new UserMultipleChoiceWidget();
		} else if (type.equals(WidgetType.SELECT)) {
			w = new CustomListBox();

			// Push in all "options" values
			String[] o = options.split(",");
			for (int iter = 0; iter < o.length; iter++) {
				// Check for "description" pairs
				if (o[iter].contains("|")) {
					String[] i = o[iter].split("\\|");
					((CustomListBox) w).addItem(i[0], i[1]);
				} else {
					if (o[iter].length() > 0) {
						((CustomListBox) w).addItem(o[iter]);
					}
				}
			}
		} else if (type.equals(WidgetType.PATIENT)) {
			w = new PatientWidget();
		} else if (type.equals(WidgetType.DATE)) {
			w = new CustomDatePicker();
		} else if (type.equals(WidgetType.COLOR)) {
			w = new CustomColorPicker();
		} else if (type.equals(WidgetType.DRUG)) {
			w = new DrugWidget();
		} else if (type.equals(WidgetType.DELIMITER)) {
			w = new Label(title);
			w.setStyleName("freemed-SimpleUIBuilder-Delimiter");
		} else if (type.equals(WidgetType.MULTILIST)) {
			w = new CustomMulltiSelectListBox(options, true);
		} else if (type.equals(WidgetType.SINGLELIST)) {
			w = new CustomMulltiSelectListBox(options, false);
		} else if (type.equals(WidgetType.CHECKBOX)) {
			w = new CheckBox();
		} else {
			// Unimplemented, use text box as fallback
			w = new CustomTextBox();
			JsonUtil.debug("SimpleUIBuilder: Unimplemented type '" + type
					+ "' found. Fallback to TextBox.");
		}

		// Add to indices and display
		widgets.put(name, w);

		if (type.equals(WidgetType.DELIMITER)) {
			table.setWidget(widgets.size() - 1, 0, w);
			table.getFlexCellFormatter().setColSpan(widgets.size() - 1, 0, 2);
		} else {
			table.setText(widgets.size() - 1, 0, title);
			table.setWidget(widgets.size() - 1, 1, w);
			if (help != null) {
				final Image image = new Image();
				image.setUrl("resources/images/q_help.16x16.png");
				Util.attachHelp(image, helpprefix + " " + title, help, false);

				table.setWidget(widgets.size() - 1, 2, image);
			}
		}

		// Set widget value after it is added.
		this.setWidgetValue(name, value);
	}

	/**
	 * Convert string into <WidgetType> enumerated value
	 * 
	 * @param widget
	 * @return
	 */
	public WidgetType stringToWidgetType(String widget) {
		if (widget.compareToIgnoreCase("TEXT") == 0) {
			return WidgetType.TEXT;
		}
		if (widget.compareToIgnoreCase("MODULE_MULTIPLE") == 0) {
			return WidgetType.MODULE_MULTIPLE;
		}
		if (widget.compareToIgnoreCase("USER_MULTIPLE") == 0) {
			return WidgetType.USER_MULTIPLE;
		}
		if (widget.compareToIgnoreCase("MODULE") == 0) {
			return WidgetType.MODULE;
		}
		if (widget.compareToIgnoreCase("SELECT") == 0) {
			return WidgetType.SELECT;
		}
		if (widget.compareToIgnoreCase("PATIENT") == 0) {
			return WidgetType.PATIENT;
		}
		if (widget.compareToIgnoreCase("COLOR") == 0) {
			return WidgetType.COLOR;
		}
		if (widget.compareToIgnoreCase("DELIMITER") == 0) {
			return WidgetType.DELIMITER;
		}
		if (widget.compareToIgnoreCase("DRUG") == 0) {
			return WidgetType.DRUG;
		}
		if (widget.compareToIgnoreCase("MULTILIST") == 0) {
			return WidgetType.MULTILIST;
		}
		if (widget.compareToIgnoreCase("SINGLELIST") == 0) {
			return WidgetType.SINGLELIST;
		}
		if (widget.compareToIgnoreCase("DATE") == 0) {
			return WidgetType.DATE;
		}
		if (widget.compareToIgnoreCase("CHECKBOX") == 0) {
			return WidgetType.CHECKBOX;
		}
		// By default, return text

		JsonUtil.debug("SimpleUIBuilder: Unimplemented type '" + widget
				+ "' found. Fallback to type TEXT.");
		return WidgetType.TEXT;
	}

	/**
	 * Form HashMap containing all values contained in this widget.
	 * 
	 * @return
	 */
	public HashMap<String, String> getValues() {
		HashMap<String, String> c = new HashMap<String, String>();
		Iterator<String> iter = widgets.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			c.put(k, getWidgetValue(k));
		}
		return c;
	}

	/**
	 * Set all widget values.
	 * 
	 * @param c
	 */
	public void setValues(HashMap<String, String> c) {
		Iterator<String> iter = widgets.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			String v = c.get(k);
			if (v != null) {
				setWidgetValue(k, v);
			}
		}
	}

	/**
	 * Convenience method for extracting value from a contained widget given the
	 * widget's name in the widgets hashmap.
	 * 
	 * @param name
	 *            "name" key in the widgets hashmap
	 * @return Value of the specified widget, or null if none is found.
	 */
	public String getWidgetValue(String name) {
		Widget w = widgets.get(name);
		if (w instanceof TextBox) {
			return ((TextBox) w).getText();
		}
		if (w instanceof CustomListBox) {
			return ((CustomListBox) w).getWidgetValue();
		}
		if (w instanceof SupportModuleWidget) {
			return ((SupportModuleWidget) w).getValue().toString();
		}
		if (w instanceof SupportModuleMultipleChoiceWidget) {
			return ((SupportModuleMultipleChoiceWidget) w)
					.getCommaSeparatedValues();
		}
		if (w instanceof UserMultipleChoiceWidget) {
			return ((UserMultipleChoiceWidget) w).getCommaSeparatedValues();
		}
		if (w instanceof CustomColorPicker) {
			return ((CustomColorPicker) w).getValue();
		}
		if (w instanceof PatientWidget) {
			return ((PatientWidget) w).getValue().toString();
		}
		if (w instanceof DrugWidget) {
			return ((DrugWidget) w).getStoredValue();
		}
		if (w instanceof CustomMulltiSelectListBox) {
			return ((CustomMulltiSelectListBox) w).getWidgetValue();
		}
		if (w instanceof CustomDatePicker) {
			return ((CustomDatePicker) w).getTextBox().getText();
		}
		if (w instanceof CheckBox) {
			if (((CheckBox) w).getValue())
				return "1";
			else
				return "0";
		}
		return null;
	}

	/**
	 * Convenience method for setting value of a contained widget given the
	 * widget's name in the widgets hashmap.
	 * 
	 * @param name
	 *            "name" key in the widgets hashmap
	 * @param value
	 *            Value to assign
	 */
	public void setWidgetValue(String name, String value) {
		Widget w = widgets.get(name);
		if (value != null) {
			if (w instanceof TextBox) {
				((TextBox) w).setText(value);
			}
			if (w instanceof CustomListBox) {
				((CustomListBox) w).setWidgetValue(value);
			}
			if (w instanceof SupportModuleWidget) {
				((SupportModuleWidget) w).setValue(Integer.parseInt(value));
			}
			if (w instanceof SupportModuleMultipleChoiceWidget) {
				((SupportModuleMultipleChoiceWidget) w)
						.setCommaSeparatedValues(value);
			}
			if (w instanceof UserMultipleChoiceWidget) {
				((UserMultipleChoiceWidget) w).setCommaSeparatedValues(value);
			}
			if (w instanceof CustomColorPicker) {
				((CustomColorPicker) w).setValue(value);
			}
			if (w instanceof PatientWidget) {
				((PatientWidget) w).setValue(Integer.parseInt(value));
			}
			if (w instanceof DrugWidget) {
				((DrugWidget) w).setValue(value);
			}
			if (w instanceof CustomMulltiSelectListBox) {
				((CustomMulltiSelectListBox) w).populateMultiList(value);
			}
			if (w instanceof CheckBox) {
				if (value.equals("0"))
					((CheckBox) w).setValue(false);
				else
					((CheckBox) w).setValue(true);
			}
		}
	}

}
