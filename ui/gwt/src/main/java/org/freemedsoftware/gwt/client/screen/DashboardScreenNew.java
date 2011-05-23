/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng <pmeng@freemedsoftware.org>
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
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.ActionItemsBox;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.DocumentBox;
import org.freemedsoftware.gwt.client.widget.MessageBox;
import org.freemedsoftware.gwt.client.widget.NotesBox;
import org.freemedsoftware.gwt.client.widget.PrescriptionRefillBox;
import org.freemedsoftware.gwt.client.widget.WorkList;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class DashboardScreenNew extends ScreenInterface {

	protected FlexTable quickSummaryTable = null;
	
	//Messages vars
	protected MessageBox messageBox = null;
	protected Label msgsCountLabel  = null;
	protected Label msgsDetails		= null;
	protected String msgsDetailsStr = "new messages in your inbox.";
	
	//WirkList vars
	protected WorkList workList = null;

	//Prescriptions Vars
	protected PrescriptionRefillBox prescriptionRefillBox = null;

	//NoteBox Vars
	protected NotesBox notesBox = null;

	//Documents vars
	protected DocumentBox documentBox    = null;
	protected Label documentsCountLabel  = null;
	protected Label documentsDetails	 = null;
	protected String documentsDetailsStr = "unfiled documents available.";
	
	//Action Items vars
	protected ActionItemsBox actionItemsBox = null;
	protected Label actionItemsCountLabel  = null;
	protected Label actionItemsDetails		= null;
	protected String actionItemsDetailsStr = "new messages in your inbox.";

	protected VerticalPanel dashBoardContainer;
	protected VerticalPanel widgetsPanel=new VerticalPanel();

	protected List<String> widgets;
	private static List<DashboardScreenNew> dashboardScreenList = null;
	public static DashboardScreenNew getInstance() {
		DashboardScreenNew dashboardScreen = null;

		if (dashboardScreenList == null)
			dashboardScreenList = new ArrayList<DashboardScreenNew>();
		if (dashboardScreenList.size() < AppConstants.MAX_DASHBOARD_TABS) {
			// creates & returns new next instance of preferencesScreen
			dashboardScreenList
					.add(dashboardScreen = new DashboardScreenNew());
		} else {
			// returns last instance of preferencesScreen from list
			dashboardScreen = dashboardScreenList
					.get(AppConstants.MAX_DASHBOARD_TABS - 1);
		}
		return dashboardScreen;
	}
	
	private DashboardScreenNew() {
		dashBoardContainer = new VerticalPanel();
		dashBoardContainer.setWidth("100%");
		
		//Adding Header Panel
		dashBoardContainer.add(createHeaderPanel());
		
		//Adding Quick Links Panel
		dashBoardContainer.add(createQuickLinksPanel());		
		
		initWidget(dashBoardContainer);
		
	}

	protected HorizontalPanel createHeaderPanel(){
		final HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setWidth("100%");
		//Adding quick text on top left corner
		
		final HorizontalPanel refreshDashBoardHPanel = new HorizontalPanel();
		refreshDashBoardHPanel.setWidth("100%");
		headerHPanel.add(refreshDashBoardHPanel);
		
		final HorizontalPanel refreshDashBoardSubHPanel = new HorizontalPanel();
		refreshDashBoardHPanel.add(refreshDashBoardSubHPanel);
		
		final Label lastUpdateLabel = new Label("Last updated on "+Util.getTodayDate());
		refreshDashBoardSubHPanel.add(lastUpdateLabel);
		refreshDashBoardSubHPanel.setCellVerticalAlignment(lastUpdateLabel, HasVerticalAlignment.ALIGN_MIDDLE);
		
		final CustomButton refreshDashBoardBtn = new  CustomButton("Refresh",AppConstants.ICON_REFRESH);
		refreshDashBoardBtn.getElement().setAttribute("style", "float:right");
		refreshDashBoardSubHPanel.add(refreshDashBoardBtn);
		refreshDashBoardHPanel.setCellHorizontalAlignment(refreshDashBoardSubHPanel, HasHorizontalAlignment.ALIGN_RIGHT);
		
		refreshDashBoardBtn.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				lastUpdateLabel.setText("Last updated on "+Util.getTodayDate());
				clearView();
				loadWidgets();
				reloadDashboard();
			}
		
		});
		
		
		return headerHPanel;
	}
	
	public void loadWidgets(){
		//Adding Widgets Panel
		dashBoardContainer.remove(widgetsPanel);
		widgetsPanel=createWidgetsPanel();
		dashBoardContainer.add(widgetsPanel);
	}
	
	protected HorizontalPanel createQuickLinksPanel(){
		final HorizontalPanel linksHPanel = new HorizontalPanel();
		quickSummaryTable = new FlexTable();
		quickSummaryTable.setCellSpacing(10);
		linksHPanel.add(quickSummaryTable);
		
		int row = 0;
		
		
		//Messages Quick link & detail area
		msgsCountLabel = new Label();
		msgsCountLabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		quickSummaryTable.setWidget(row, 0, msgsCountLabel);
		quickSummaryTable.getCellFormatter().setVerticalAlignment(row, 0, HasVerticalAlignment.ALIGN_TOP);
		
		final VerticalPanel msgsDetailsVPanel = new VerticalPanel();
		final HTML msgsDetailsHeader = new HTML("<a href='javascript:undefined' >New Messages</a>");
		msgsDetailsHeader.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		msgsDetailsVPanel.add(msgsDetailsHeader);
		msgsDetails = new Label();
		msgsDetailsVPanel.add(msgsDetails);
		quickSummaryTable.setWidget(row, 1, msgsDetailsVPanel);
		
		msgsDetailsHeader.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				Util.spawnTab(AppConstants.MESSAGES, MessagingScreen.getInstance());
			}
		
		});
		
		row++;
		//Documents Quick link & detail area
		documentsCountLabel = new Label();
		documentsCountLabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		quickSummaryTable.setWidget(row, 0, documentsCountLabel);
		quickSummaryTable.getCellFormatter().setVerticalAlignment(row, 0, HasVerticalAlignment.ALIGN_TOP);
		
		final VerticalPanel documentsDetailsVPanel = new VerticalPanel();
		final HTML documentsDetailsHeader = new HTML("<a href='javascript:undefined' >Unfiled Documents</a>");
		documentsDetailsHeader.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		documentsDetailsVPanel.add(documentsDetailsHeader);
		
		documentsDetails = new Label();
		documentsDetailsVPanel.add(documentsDetails);
		quickSummaryTable.setWidget(row, 1, documentsDetailsVPanel);
		
		documentsDetailsHeader.addClickHandler(new ClickHandler() {
			
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				Util.spawnTab(AppConstants.UNFILED+" Documents", UnfiledDocuments.getInstance());
			}
		
		});

		row++;
		//Documents Quick link & detail area
		actionItemsCountLabel = new Label();
		actionItemsCountLabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		quickSummaryTable.setWidget(row, 0, actionItemsCountLabel);
		quickSummaryTable.getCellFormatter().setVerticalAlignment(row, 0, HasVerticalAlignment.ALIGN_TOP);
		
		final VerticalPanel actionItemsDetailsVPanel = new VerticalPanel();
		final HTML actionItemsDetailsHeader = new HTML("<a href='javascript:undefined' >Action Items</a>");
		actionItemsDetailsHeader.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		actionItemsDetailsVPanel.add(actionItemsDetailsHeader);
		
		actionItemsDetails = new Label();
		actionItemsDetailsVPanel.add(actionItemsDetails);
		quickSummaryTable.setWidget(row, 1, actionItemsDetailsVPanel);
		
		actionItemsDetailsHeader.addClickHandler(new ClickHandler() {
			
			@Override
			public void onClick(ClickEvent arg0) {
				// TODO Auto-generated method stub
				Util.spawnTab(AppConstants.ACTION_ITEMS, ActionItemsScreen.getInstance());
			}
		
		});
		
		for(int i = row+1;i<5;i++){
			quickSummaryTable.setText(i,0, "");
		}
		
		return linksHPanel;
	}
	protected VerticalPanel createWidgetsPanel(){
		 widgets = null;
		try{
			HashMap<String,List<String>> selectedSections=(HashMap<String,List<String>>)CurrentState.getUserConfig("defaultWidgets","HashMap<String,List>");
			widgets=selectedSections.get("Sections");
		}
		catch(Exception e){
		}
		int loopMax=0;
		if(widgets==null)
			loopMax=1;
		else
			loopMax=widgets.size();
		final VerticalPanel widgtsVPanel = new VerticalPanel();
		for(int i=0;i<loopMax;i++){
			widgtsVPanel.setWidth("100%");
			
			//Adding work list
			if((widgets!=null && widgets.get(i).equals("WORK LIST")) || widgets==null){
				try{
					workList = new WorkList();
					workList.setWidth("70%");
					widgtsVPanel.add(workList);
					
				}catch(Exception e){}
			}
			//Adding messages panel
			if((widgets!=null && widgets.get(i).equals("MESSAGES")) || widgets==null){
				messageBox = new MessageBox();
				messageBox.setWidth("70%");
				widgtsVPanel.add(messageBox);
			}
			
			if((widgets!=null && widgets.get(i).equals("UNFILED DOCUMENTS")) || widgets==null){
				documentBox = new DocumentBox();
				documentBox.setWidth("70%");
				widgtsVPanel.add(documentBox);
			}
			
			//Adding prescription refills
			if((widgets!=null && widgets.get(i).equals("RX REFILLS")) || widgets==null){
				prescriptionRefillBox = new  PrescriptionRefillBox();
				prescriptionRefillBox.setWidth("70%");
				widgtsVPanel.add(prescriptionRefillBox);
			}
			//Adding Action Items panel
			if((widgets!=null && widgets.get(i).equals("ACTION ITEMS")) || widgets==null){
				actionItemsBox = new ActionItemsBox(true);
				actionItemsBox.setWidth("70%");
				widgtsVPanel.add(actionItemsBox);	
			}
		}
		
		return widgtsVPanel;
	}	
	public void reloadDashboard(){
		
		//Handling Counters
		Util.callApiMethod("UserInterface", "getDashBoardDetails", (Integer)null, new CustomRequestCallback() {
		
			@Override
			public void onError() {
				// TODO Auto-generated method stub
		
			}
		
			@Override
			public void jsonifiedData(Object data) {
				// TODO Auto-generated method stub
				HashMap<String, String> result = (HashMap<String,String>)data;
				//updating messages info
				msgsCountLabel.setText(result.get("unreadMsgs"));
				msgsDetails.setText(result.get("unreadMsgs") + " " + msgsDetailsStr);
				
				//updating documents info
				documentsCountLabel.setText(result.get("unfiledDocuments"));
				documentsDetails.setText(result.get("unfiledDocuments") + " " + documentsDetailsStr);
				
				//updating Action Items info
				actionItemsCountLabel.setText(result.get("actionItems"));
				actionItemsDetails.setText(result.get("actionItems") + " " + actionItemsDetailsStr);
			}
		
		}, "HashMap<String,String>");
		
		//Handling Worklist		
		int loopMax=0;
		if(widgets==null)
			loopMax=1;
		else
			loopMax=widgets.size();
		for(int i=0;i<loopMax;i++){
			if((widgets!=null && widgets.get(i).equals("WORK LIST")) || widgets==null)
				workList.setProviderGroup(CurrentState.defaultProviderGroup);
			
			//Handling Messages
			if((widgets!=null && widgets.get(i).equals("MESSAGES")) || widgets==null)
				messageBox.retrieveData("");
			
			//Handling Documents Refills
			if((widgets!=null && widgets.get(i).equals("UNFILED DOCUMENTS")) || widgets==null)
				documentBox.retrieveData();
			
			//Handling Prescriptions Refills
			if((widgets!=null && widgets.get(i).equals("RX REFILLS")) || widgets==null){
				prescriptionRefillBox.cleanView();
				Util.callApiMethod("UserInterface", "GetUserType", (Integer)null, new CustomRequestCallback() {
				
					@Override
					public void onError() {
					}
				
					@Override
					public void jsonifiedData(Object data) {
						String userType = (String)data;
						CurrentState.assignUserType(userType);
						if(userType.equalsIgnoreCase(AppConstants.USER_TYPE_PROVIDER))
							prescriptionRefillBox.showDoctor();
						else if(userType.equalsIgnoreCase(AppConstants.USER_TYPE_MISCELLANEOUS))
							prescriptionRefillBox.showStaff();
					}
				
				}, "String");
			}
			//Handling Messages
			if((widgets!=null && widgets.get(i).equals("ACTION ITEMS")) || widgets==null)
				actionItemsBox.retrieveData();
		}
		
	}
	
	public void clearView(){
		//setting to default messages info
		msgsCountLabel.setText("");
		msgsDetails.setText("");
		
		//setting to default documents info
		documentsCountLabel.setText("");
		documentsDetails.setText("");

		//setting to default Action Items info
		actionItemsCountLabel.setText("");
		actionItemsDetails.setText("");		
		int loopMax=0;
		if(widgets==null)
			loopMax=1;
		else
			loopMax=widgets.size();
		//Handling Worklist
		for(int i=0;i<loopMax;i++){
			if((widgets!=null && widgets.get(i).equals("WORK LIST")) || widgets==null)
				workList.clearView();
			
			//Handling Messages
			if((widgets!=null && widgets.get(i).equals("MESSAGES")) || widgets==null)
				messageBox.clearView();
			
			//Handling Documents Refills
			if((widgets!=null && widgets.get(i).equals("UNFILED DOCUMENTS")) || widgets==null)
				documentBox.clearView();
			
			//Handling Prescriptions Refills
			if((widgets!=null && widgets.get(i).equals("RX REFILLS")) || widgets==null)
				prescriptionRefillBox.cleanView();
		}
		
	}
}
