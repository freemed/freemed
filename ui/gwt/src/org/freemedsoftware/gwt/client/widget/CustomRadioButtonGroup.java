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

public class CustomRadioButtonGroup extends Composite implements ValueChangeHandler{

	protected ValueChangeHandler valueChangeHandler=null;
	
	protected String hashMapping = null;

	protected Panel radioButtonsPanel;
	
	protected String groupName;
	
	protected java.util.List<CustomRadioButton> customRadioButtonGroup; 
	
	public CustomRadioButtonGroup(String groupName){
		this(groupName, false);
	}
	
	public CustomRadioButtonGroup(String groupName,boolean showVertical){
		customRadioButtonGroup=new ArrayList<CustomRadioButton>(); 
		this.groupName=groupName;
		if(showVertical)
			radioButtonsPanel=new VerticalPanel();
		else
			radioButtonsPanel=new HorizontalPanel();
		initWidget(radioButtonsPanel);
	}
	
	private CustomRadioButtonGroup(){
	}
	
	public void addItem(String label,String widgetValue){
		addItem(label, widgetValue, null);
	}
	
	public void addItem(String label,String widgetValue, Command fireAction){
		CustomRadioButton customRadioButton=new CustomRadioButton(groupName,label,widgetValue,fireAction);
		customRadioButton.addValueChangeHandler(this);
		customRadioButtonGroup.add(customRadioButton);
		radioButtonsPanel.add(customRadioButton);
	}
	
	public void addItem(String label){
		addItem(label, label, null);
	}
	public void addItem(String label, Command fireAction){
		addItem(label, label, fireAction);
	}
	public String getWidgetText(){
		String selectedRadioLabel=null;
		Iterator<CustomRadioButton> itr=customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while(itr.hasNext()){
			customRadioButton=itr.next();
			if(customRadioButton.getValue()==true){
				selectedRadioLabel = customRadioButton.getText();
				break;
			}
		}
		
		return selectedRadioLabel;
	}
	
	public String getWidgetValue(){
		String selectedValue=null;
		Iterator<CustomRadioButton> itr=customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while(itr.hasNext()){
			customRadioButton=itr.next();
			if(customRadioButton.getValue()==true){
				selectedValue = customRadioButton.getWidgetValue();
				break;
			}
		}
		
		return selectedValue;
	}
	
	public void setWidgetValue(String value){
		Iterator<CustomRadioButton> itr=customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while(itr.hasNext()){
			customRadioButton=itr.next();
			if(customRadioButton.getWidgetValue().equals(value)){
				customRadioButton.setValue(true);
				break;
			}
		}
		
	}
	
	public void setEnable(boolean enabled){
		Iterator<CustomRadioButton> itr=customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while(itr.hasNext()){
			customRadioButton=itr.next();
			customRadioButton.setEnabled(enabled);
		}
	}
	
	public void clear(){
		Iterator<CustomRadioButton> itr=customRadioButtonGroup.iterator();
		CustomRadioButton customRadioButton;
		while(itr.hasNext()){
			customRadioButton=itr.next();
			customRadioButton.setValue(false);
		}
		
	}
	
	public void addValueChangeHandler(ValueChangeHandler valueChangeHandler){
		this.valueChangeHandler = valueChangeHandler;
	}
	
	@Override
	public void onValueChange(ValueChangeEvent arg0) {
		if(valueChangeHandler!=null)
			valueChangeHandler.onValueChange(arg0);
	}
	
	public class CustomRadioButton extends RadioButton implements ClickHandler{

		protected String hashMapping = null;

		protected String widgetValue;

		protected Command fireAction = null;
		
		public CustomRadioButton(String group,String label){
			super(group,label);
		}
		
		public CustomRadioButton(String group,String label,String widgetValue){
			super(group,label);
			this.widgetValue=widgetValue;
		}
		
		public CustomRadioButton(String group,String label,String widgetValue,Command fireAction){
			super(group,label);
			this.widgetValue = widgetValue;
			this.fireAction = fireAction;
			this.addClickHandler(this);
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
			if(fireAction!=null)
				fireAction.execute();
		}
	}

	@Override
	public void setWidth(String width) {
		// TODO Auto-generated method stub
		super.setWidth(width);
		radioButtonsPanel.setWidth(width);
	}
}
