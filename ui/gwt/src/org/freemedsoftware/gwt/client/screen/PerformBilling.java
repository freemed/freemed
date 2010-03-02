/*
 * $Id: CallInScreen.java 4643 2009-10-21 11:50:05Z Fawad $
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.List;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PerformBilling extends ScreenInterface {

		

	private static List<PerformBilling> performBillingScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static PerformBilling getInstance(){
		PerformBilling performBillingScreen=null; 
		
		if(performBillingScreenList==null)
			performBillingScreenList=new ArrayList<PerformBilling>();
		if(performBillingScreenList.size()<AppConstants.MAX_REPORTING_TABS)//creates & returns new next instance of SuperBillScreen
			performBillingScreenList.add(performBillingScreen=new PerformBilling());
		else  
			performBillingScreen = performBillingScreenList.get(AppConstants.MAX_REPORTING_TABS-1);
		return performBillingScreen;
	}
	
	public PerformBilling() {
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		VerticalPanel performBillPanel = new VerticalPanel();
		horizontalPanel.add(performBillPanel);
		performBillPanel.setSize("100%", "100%");

		final Label performBillingLabel = new Label("Performing Bills");
		performBillingLabel.setStyleName("large-header-label");
		performBillPanel.add(performBillingLabel);
		performBillingLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		HorizontalPanel horizontalPanel1 = new HorizontalPanel();
		horizontalPanel1.setSpacing(5);
		horizontalPanel1.add(new Button("Process"));
		horizontalPanel1.add(new Button("Select All"));
		horizontalPanel1.add(new Button("Select None"));
		horizontalPanel1.add(new Button("Return to MainMenu"));
		performBillPanel.add(horizontalPanel1);		
	
	}

	







}
