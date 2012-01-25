/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

public class ReportingLogScreen extends ScreenInterface {

	public final static String moduleName = "ReportingPrintLog";

	protected CustomTable printLogTable = new CustomTable();

	protected String module = "ReportinPrintLog";
	
	private static List<ReportingLogScreen> logScreenList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static ReportingLogScreen getInstance(){
		ReportingLogScreen aclScreen=null; 
		
		if(logScreenList==null)
			logScreenList=new ArrayList<ReportingLogScreen>();
		if(logScreenList.size()<AppConstants.MAX_ACL_TABS)//creates & returns new next instance of SupportDataScreen
			logScreenList.add(aclScreen=new ReportingLogScreen());
		else //returns last instance of SupportDataScreen from list 
			aclScreen = logScreenList.get(AppConstants.MAX_ACL_TABS-1);
		return aclScreen;
	}
	
	public static boolean removeInstance(ReportingLogScreen aclScreen){
		return logScreenList.remove(aclScreen);
	}
	
	public ReportingLogScreen() {
		super(moduleName);
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		// Panel #2

		final FlexTable groupListTable = new FlexTable();
		tabPanel.add(groupListTable, _("Failed Reports"));

		groupListTable.setWidget(0, 0, printLogTable);

		printLogTable.setSize("100%", "100%");
		printLogTable.addColumn(_("Report Name"), "report_name"); // col 0
		printLogTable.addColumn(_("Format"), "report_format"); // col 1
		printLogTable.addColumn(_("Date"), "stamp"); // col 2
		printLogTable.addColumn(_("Action"), "action"); // col 2
		printLogTable.setIndexName("id");

		printLogTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				actionBar.hideAction(HandleCustomAction.ADD);
				actionBar.hideAction(HandleCustomAction.LOCK);
				actionBar.hideAction(HandleCustomAction.MODIFY);
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						if(action == HandleCustomAction.DELETE)
							deleteFailedReportsLog(id);
						else if(action == HandleCustomAction.PRINT){
							Util.generateReportToPrinter(data.get("report_name"), data.get("report_format"), convertParamsToList(data.get("report_params")),false);
						}else if(action == HandleCustomAction.VIEW){
							Util.generateReportToBrowser(data.get("report_name"), data.get("report_format"), convertParamsToList(data.get("report_params")));
						}
					}
				});
				
				// Push value back to table
				return actionBar;
			}
		});
		
		printLogTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
			}
		});
		tabPanel.selectTab(0);
		// TODO:Backend needs to be fixed first
		retrieveFailedReportsLog();
	}
	public void retrieveFailedReportsLog() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			printLogTable.showloading(true);
			Util.callModuleMethod(module, "GetAllRecords", (List)null, new CustomRequestCallback() {
			
				@Override
				public void onError() {
					Util.showErrorMsg(module, _("Failed to get log items."));
				}
			
				@Override
				public void jsonifiedData(Object data) {
					printLogTable.loadData((HashMap<String, String>[])data);
			
				}
			
			}, "HashMap<String,String>[]");
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}

	}
	
	public void deleteFailedReportsLog(Integer id) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			Util.callModuleMethod(module, "GetAllRecords", id, new CustomRequestCallback() {
			
				@Override
				public void onError() {
					Util.showErrorMsg(module, _("Failed to delete log items."));
				}
			
				@Override
				public void jsonifiedData(Object data) {
					if(data!=null && ((Boolean)data).booleanValue()){
						Util.showInfoMsg(module, _("Deleted successfully."));	
					}
			
				}
			
			}, "HashMap<String,String>[]");
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}

	}
	/*
	 * Converts comma separated values into list
	 * */
	public List convertParamsToList(String params){
		List paramsList = new ArrayList();
		String[] paramsArray = params.split(",");
		for(int i=0;i<paramsArray.length;i++){
			paramsList.add(paramsArray[i]);
		}
		return paramsList;
	}
	
	@Override
	public void closeScreen() {
		super.closeScreen();
		removeInstance(this);
	}
}
