/*
 * $Id: CallInScreen.java 4643 2009-10-21 11:50:05Z Fawad $
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.List;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable;

import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SuperBills extends ScreenInterface {

	protected CustomTable superBillsTable;	

	private static List<SuperBills> superBillsScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static SuperBills getInstance(){
		SuperBills superBillsScreen=null; 
		
		if(superBillsScreenList==null)
			superBillsScreenList=new ArrayList<SuperBills>();
		if(superBillsScreenList.size()<AppConstants.MAX_REPORTING_TABS)//creates & returns new next instance of SuperBillScreen
			superBillsScreenList.add(superBillsScreen=new SuperBills());
		else  
			superBillsScreen = superBillsScreenList.get(AppConstants.MAX_REPORTING_TABS-1);
		return superBillsScreen;
	}
	
	public static boolean removeInstance(SuperBills superBillsScreen){
		return superBillsScreenList.remove(superBillsScreen);
	}
	
	public SuperBills() {
		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		superBillsTable = new CustomTable();
		verticalPanel.add(superBillsTable);
		superBillsTable.setAllowSelection(false);
		superBillsTable.setSize("100%", "100%");		
		superBillsTable.addColumn("Date", "superBill_date");
		superBillsTable.addColumn("Patient", "superBill_patient");
		superBillsTable.addColumn("Provider", "superBill_provider");
		superBillsTable.addColumn("Procdural Codes", "superBill_proceduaralCodes");
	
	}

	



	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}



}
