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
import org.freemedsoftware.gwt.client.widget.SchedulerWidget;
import org.freemedsoftware.gwt.client.widget.WorkList;

import com.google.gwt.user.client.ui.VerticalPanel;

public class SchedulerScreen extends ScreenInterface {

	protected WorkList workList = new WorkList();

	protected SchedulerWidget scheduler = null;

	protected VerticalPanel verticalPanel = null;

	private static List<SchedulerScreen> schedulerScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static SchedulerScreen getInstance(){
		SchedulerScreen schedulerScreen=null; 
		if(schedulerScreenList==null)
			schedulerScreenList=new ArrayList<SchedulerScreen>();
		if(schedulerScreenList.size()<AppConstants.MAX_SCHEDULER_TABS)//creates & returns new next instance of SchedulerScreen
			schedulerScreenList.add(schedulerScreen=new SchedulerScreen());
		else{ //returns last instance of SchedulerScreen from list 
			schedulerScreen = schedulerScreenList.get(AppConstants.MAX_SCHEDULER_TABS-1);
			schedulerScreen.getSchedulerWidget().refreshData();
		}	
		return schedulerScreen;
	}
	
	public static boolean removeInstance(SchedulerScreen schedulerScreen){
		return schedulerScreenList.remove(schedulerScreen);
	}
	
	public SchedulerScreen() {
		verticalPanel = new VerticalPanel();
		scheduler = new SchedulerWidget();
		verticalPanel.add(scheduler);
		initWidget(verticalPanel);
	}

	
	public SchedulerWidget getSchedulerWidget() {
		return scheduler;
	}
	
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
