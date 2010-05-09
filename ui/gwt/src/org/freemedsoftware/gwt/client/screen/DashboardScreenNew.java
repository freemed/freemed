/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng <pmeng@freemedsoftware.org>
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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
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
	
	public DashboardScreenNew() {
		// Initialize everything
		//initialzing main vertical panel
		final VerticalPanel dashBoardContainer = new VerticalPanel();
		dashBoardContainer.setWidth("100%");
		
		//Adding Header Panel
		dashBoardContainer.add(createHeaderPanel());
		
		//Adding Quick Links Panel
		dashBoardContainer.add(createQuickLinksPanel());		

		//Adding Widgets Panel
		dashBoardContainer.add(createWidgetsPanel());
		
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
				reloadDashboard();
			}
		
		});
		
		
		return headerHPanel;
	}
	
	protected HorizontalPanel createQuickLinksPanel(){
		final HorizontalPanel linksHPanel = new HorizontalPanel();
		quickSummaryTable = new FlexTable();
		quickSummaryTable.setCellSpacing(10);
		linksHPanel.add(quickSummaryTable);
		
		int row = 0;
		
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
		
		for(int i = row+1;i<5;i++){
			quickSummaryTable.setText(i,0, "");
		}
		
		return linksHPanel;
	}
	protected VerticalPanel createWidgetsPanel(){
		final VerticalPanel widgtsVPanel = new VerticalPanel();
		widgtsVPanel.setWidth("100%");
		
		//Adding work list
		try{
			workList = new WorkList();
			workList.setWidth("70%");
			widgtsVPanel.add(workList);
			
		}catch(Exception e){}
		
		//Adding messages panel
		messageBox = new MessageBox();
		messageBox.setWidth("70%");
		widgtsVPanel.add(messageBox);
		
		//Adding Documents panel
		documentBox = new DocumentBox();
		documentBox.setWidth("70%");
		widgtsVPanel.add(documentBox);
		
		//Adding notes box
//		widgtsVPanel.add(notesBox);
		
		//Adding prescription refills
		prescriptionRefillBox = new  PrescriptionRefillBox();
		prescriptionRefillBox.setWidth("70%");
		widgtsVPanel.add(prescriptionRefillBox);
		
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
			}
		
		}, "HashMap<String,String>");
		
		//Handling Worklist		
		workList.setProviderGroup(CurrentState.defaultProviderGroup);
		
		//Handling Messages
		messageBox.retrieveData("");
		
		//Handling Documents Refills
		documentBox.retrieveData();
		
		//Handling Prescriptions Refills
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
	
	public void clearView(){
		//setting to default messages info
		msgsCountLabel.setText("");
		msgsDetails.setText("");
		
		//setting to default documents info
		documentsCountLabel.setText("");
		documentsDetails.setText("");
		
		//Handling Worklist		
		workList.clearView();
		
		//Handling Messages
		messageBox.clearView();
		
		//Handling Documents Refills
		documentBox.clearView();
		
		//Handling Prescriptions Refills
		prescriptionRefillBox.cleanView();
		
	}
}
