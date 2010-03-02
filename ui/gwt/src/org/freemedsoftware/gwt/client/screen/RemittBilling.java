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
import com.google.gwt.user.client.ui.Grid;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;


public class RemittBilling extends ScreenInterface {
	
	protected  Grid gridLinks = new Grid(10,10);
	//protected CustomTable remittBillingTable;	
	protected TabPanel tabPanel;
	private static List<RemittBilling> remittBillingScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static RemittBilling getInstance(){
		RemittBilling remittBillingScreen=null; 
		
		if(remittBillingScreenList==null)
			remittBillingScreenList=new ArrayList<RemittBilling>();
		if(remittBillingScreenList.size()<AppConstants.MAX_REPORTING_TABS)//creates & returns new next instance of SuperBillScreen
			remittBillingScreenList.add(remittBillingScreen=new RemittBilling());
		else  
			remittBillingScreen = remittBillingScreenList.get(AppConstants.MAX_REPORTING_TABS-1);
		return remittBillingScreen;
	}
	
	public static boolean removeInstance(RemittBilling remittBillingScreen){
		return remittBillingScreenList.remove(remittBillingScreen);
	}
	
	public RemittBilling() {
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");
		tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);
	//////////////////////////////////////////  FIXME the following code between comment 
			VerticalPanel performBillPanel = new VerticalPanel();
			horizontalPanel.add(performBillPanel);
			performBillPanel.setSize("100%", "100%");
	
			HorizontalPanel horizontalPanel1 = new HorizontalPanel();
			horizontalPanel1.setSpacing(5);
			horizontalPanel1.add(new Button("Process"));
			horizontalPanel1.add(new Button("Select All"));
			horizontalPanel1.add(new Button("Select None"));
			performBillPanel.add(horizontalPanel1);		
		///////////////////////////////////////////////////////
		tabPanel.add(performBillPanel, "Perform Billing");
		tabPanel.add(new VerticalPanel(), "Rebill");
		tabPanel.add(new VerticalPanel(), "Show Reports");
		tabPanel.selectTab(0);

/*		final Label remittBillingLabel = new Label("Remitt Billing");
		remittBillingLabel.setStyleName("large-header-label");  
		verticalPanel.add(remittBillingLabel);
		remittBillingLabel.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		
		HTML htmlPerformBilling = new HTML("<a href=\"#\">Perform Billing</a> ");
		HTML htmlRebill = new HTML("<a href=\"#\">Rebill</a> ");		
		HTML htmlShowReports= new HTML("<a href=\"#\">Show Reports</a> ");
		gridLinks.setWidget(1, 0, htmlPerformBilling);
		gridLinks.setWidget(3, 0, htmlRebill);
		gridLinks.setWidget(5, 0, htmlShowReports);
		//verticalPanel.add(gridLinks);
		//verticalPanel.setHorizontalAlignment(VerticalPanel.ALIGN_LEFT);
		
		Label lblPerformBilling = new Label ("Perform Remitt billing runs.");
		Label lblRebill = new Label ("Select a previous billing to rebill.");		
		Label  lblsShowReports= new Label ("View output files and logs from Remitt.");
		gridLinks.setWidget(1, 9, lblPerformBilling);
		gridLinks.setWidget(3, 9, lblRebill);
		gridLinks.setWidget(5, 9, lblsShowReports);
		verticalPanel.add(gridLinks);
		htmlPerformBilling.addClickHandler(new ClickHandler(){
  
			public void onClick(ClickEvent event) {						
								 Util.spawnTab("PerformBilling Bills",PerformBilling.getInstance());												
			
			}
			
		});*/
		//////////////////////////////////////
	}

	



	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}



}
