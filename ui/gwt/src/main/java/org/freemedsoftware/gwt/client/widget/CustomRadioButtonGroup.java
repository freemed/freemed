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

import java.util.ArrayList;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.JsonUtil;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Panel;
import com.google.gwt.user.client.ui.RadioButton;
import com.google.gwt.user.client.ui.VerticalPanel;

@SuppressWarnings("unchecked")
public class CustomRadioButtonGroup extends Composite implements
		ValueChangeHandler {

	protected ValueChangeHandler valueChangeHandler = null;

	protected String hashMapping = null;

	protected Panel radioButtonsPanel;

	protected String groupName;

	protected java.util.List<CustomRadioButton> customRadioButtonGroup;
	
	protected boolean labelsAsHTML = false;

	public CustomRadioButtonGroup(String groupName) {
		this(groupName, false);
	}

	public CustomRadioButtonGroup(String groupName, boolean showVertical) {
		customRadioButtonGroup = new ArrayList<CustomRadioButton>();
		this.groupName = groupName;
		if (showVertical)
			radioButtonsPanel = new VerticalPanel();
		else
			radioButtonsPanel = new HorizontalPanel();
		initWidget(radioButtonsPanel);
	}

	@SuppressWarnings("unused")
	private CustomRadioButtonGroup() {
	}

	public void addItem(String label, String widgetValue) {
		addItem(label, widgetValue, null);
	}

	public void addItem(String label, String widgetValue, Command fireAction) {
		CustomRadioButton customRadioButton = new CustomRadioButton(groupName,
				label, widgetValue, fireAction);
		customRadioButton.addValueChangeHandler(this);
		customRadioButtonGroup.add(customRadioButton);
		radioButtonsPanel.add(customRadioButton);
	}

	public void addItem(String label) {
		addItem(label, label, null);
	}

	public void addItem(String label, Command fireAction) {
		addItem(label, label, fireAction);
	}

	public String getWidgetText() {
		String selectedRadioLabel = null;
		Iterator<CustomRadioButton> itr = customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while (itr.hasNext()) {
			customRadioButton = itr.next();
			if (customRadioButton.getValue() == true) {
				selectedRadioLabel = customRadioButton.getText();
				break;
			}
		}

		return selectedRadioLabel;
	}

	public String getWidgetValue() {
		String selectedValue = null;
		Iterator<CustomRadioButton> itr = customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while (itr.hasNext()) {
			customRadioButton = itr.next();
			if (customRadioButton.getValue() == true) {
				selectedValue = customRadioButton.getWidgetValue();
				break;
			}
		}

		return selectedValue;
	}

	public void setWidgetValue(String value) {
		setWidgetValue(value, false);

	}

	public void setWidgetValue(String value, boolean fireEvent) {
		Iterator<CustomRadioButton> itr = customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while (itr.hasNext()) {
			customRadioButton = itr.next();
			if (customRadioButton.getWidgetValue().equals(value)) {
				customRadioButton.setValue(true);
				if (fireEvent && customRadioButton.fireAction != null) {
					customRadioButton.fireAction.execute();
				}
				if(fireEvent && valueChangeHandler!=null){
					ValueChangeEvent<String> changeEvent = new ValueChangeEvent<String>(value) {
						@Override
						public String getValue() {
							// TODO Auto-generated method stub
							return super.getValue();
						}
					};
					valueChangeHandler.onValueChange(changeEvent);
					}
				break;
			}else customRadioButton.setValue(false); 
		}

	}

	public void setEnable(boolean enabled) {
		Iterator<CustomRadioButton> itr = customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while (itr.hasNext()) {
			customRadioButton = itr.next();
			customRadioButton.setEnabled(enabled);
		}
	}

	public void clear() {
		this.clear(false);
	}

	public void clear(boolean fireEvent) {
		Iterator<CustomRadioButton> itr = customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while (itr.hasNext()) {
			customRadioButton = itr.next();
			customRadioButton.setValue(false);
			if (fireEvent && customRadioButton.fireAction != null) {
				customRadioButton.fireAction.execute();
			}
		}

	}

	public void addValueChangeHandler(ValueChangeHandler valueChangeHandler) {
		this.valueChangeHandler = valueChangeHandler;
	}

	@Override
	public void onValueChange(ValueChangeEvent arg0) {
		if (valueChangeHandler != null)
			valueChangeHandler.onValueChange(arg0);
	}

	public class CustomRadioButton extends RadioButton implements ClickHandler {

		protected String hashMapping = null;

		protected String widgetValue;

		protected Command fireAction = null;

		protected ValueChangeEvent<String> changeEvent = null;
		
		public CustomRadioButton(String group, String label,
				String widgetValue1, Command fireAction) {
			super(group, label);
			if(labelsAsHTML)
				setHTML(label);
			this.widgetValue = widgetValue1;
			this.fireAction = fireAction;
			this.addClickHandler(this);
			changeEvent = new ValueChangeEvent<String>(widgetValue1) {
				@Override
				public String getValue() {
					// TODO Auto-generated method stub
					return widgetValue;
				}
			};
		}
		public void setHashMapping(String hm) {
			hashMapping = hm;
		}

		public String getHashMapping() {
			return hashMapping;
		}

		public String getWidgetValue() {
			return widgetValue;
		}

		public void setWidgetValue(String widgetValue) {
			this.widgetValue = widgetValue;
		}

		@Override
		public void onClick(ClickEvent event) {
			JsonUtil.debug("CustomRadioButton:onClick Called");
			if (fireAction != null)
				fireAction.execute();
			if(valueChangeHandler!=null){
				valueChangeHandler.onValueChange(changeEvent);
			}
		}
	}

	@Override
	public void setWidth(String width) {
		// TODO Auto-generated method stub
		super.setWidth(width);
		radioButtonsPanel.setWidth(width);
	}

	public void setFocus(boolean focus) {
		customRadioButtonGroup.get(0).setFocus(focus);
	}

	public boolean isLabelsAsHTML() {
		return labelsAsHTML;
	}

	public void setLabelsAsHTML(boolean labelsAsHTML) {
		this.labelsAsHTML = labelsAsHTML;
	}

}
