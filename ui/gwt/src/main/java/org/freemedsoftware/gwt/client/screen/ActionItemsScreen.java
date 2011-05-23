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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.List;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.ActionItemsBox;

import com.google.gwt.user.client.ui.HorizontalPanel;

public class ActionItemsScreen extends ScreenInterface {

	public final static String moduleName = "emr";


	private static List<ActionItemsScreen> actionItemsScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static ActionItemsScreen getInstance(){
		ActionItemsScreen actionItemsScreen=null; 
		
		if(actionItemsScreenList==null)
			actionItemsScreenList=new ArrayList<ActionItemsScreen>();
		if(actionItemsScreenList.size()<AppConstants.MAX_ACTION_ITEMS_TABS)//creates & returns new next instance of ActionItemsScreen
			actionItemsScreenList.add(actionItemsScreen=new ActionItemsScreen());
		else //returns last instance of ActionItemsScreen from list 
			actionItemsScreen = actionItemsScreenList.get(AppConstants.MAX_ACTION_ITEMS_TABS-1);
		return actionItemsScreen;
	}
	
	public static boolean removeInstance(ActionItemsScreen reportingScreen){
		return actionItemsScreenList.remove(reportingScreen);
	}
	
	public ActionItemsScreen() {
		super(moduleName);
		
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		ActionItemsBox actionItemsBox = new ActionItemsBox(true);
		actionItemsBox.setEnableCollapse(false);
		horizontalPanel.add(actionItemsBox);
		actionItemsBox.retrieveData();
	}

	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
