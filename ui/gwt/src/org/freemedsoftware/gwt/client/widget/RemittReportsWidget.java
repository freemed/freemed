package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;


import com.google.gwt.dom.client.Style.Cursor;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.URL;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class RemittReportsWidget extends Composite{

	protected VerticalPanel panel;
	protected FlexTable allReportTable;
	
	public RemittReportsWidget(){
		panel = new VerticalPanel();
		panel.setSpacing(10);
		initWidget(panel);
		allReportTable = new FlexTable();
		allReportTable.setWidth("80%");
		panel.add(allReportTable);
		loadMonthsInfo();
	}
	
	public void loadMonthsInfo(){
		Util.callModuleMethod("RemittBillingTransport", "getMonthsInfo",
				(Integer) null, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String> [] result = (HashMap[]) data;
							for(int i=0;i<result.length;i++){
								int row=i/2;
								int col=i%2;
								VerticalPanel reportPanel=new VerticalPanel();
								reportPanel.setSpacing(10);
								reportPanel.setWidth("70%");
								HorizontalPanel hpanel=new HorizontalPanel();
								hpanel.setSpacing(5);
								final Label expandLb=new Label("+");
								final CustomTable reportsInfoTable = new CustomTable();
								reportsInfoTable.setAllowSelection(false);
								reportsInfoTable.setWidth("100%");
								reportsInfoTable.addColumn("Report", "filename");
								reportsInfoTable.addColumn("Size", "filesize");
								reportsInfoTable.addColumn("Action", "action");
								reportsInfoTable
								.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
									@Override
									public Widget setColumn(String columnName,
											final HashMap<String, String> data) {
										if (columnName.compareTo("action") == 0) {
											
											HorizontalPanel actionPanel = new HorizontalPanel();
											actionPanel.setSpacing(5);
											HTML  htmlLedger= new HTML(
											"<a href=\"javascript:undefined;\" style='color:blue'>View</a>");
									
											
											htmlLedger.addClickHandler(new ClickHandler() {
														@Override
														public void onClick(
																ClickEvent arg0) {
															
																String[] params = { "output", data.get("filename"),"html" };
																Window.open(URL.encode(Util.getJsonRequest(
																		"org.freemedsoftware.api.Remitt.GetFile", params)), data.get("filename"), "");

							
														}

													});
	
											actionPanel.add(htmlLedger);
											return actionPanel;
										}
										
										else {
											return (Widget) null;
										}

									}
								});
								reportsInfoTable.setVisible(false);
								expandLb.getElement().getStyle().setCursor(Cursor.POINTER);
								final int index=i;
								expandLb.addClickHandler(new ClickHandler() {
								
									@Override
									public void onClick(ClickEvent arg0) {
										if(expandLb.getText().trim().equals("+")){
											expandLb.setText("-");
											reportsInfoTable.setVisible(true);
											loadReportsDetails(result[index].get("month"),reportsInfoTable);
										}
										else{
											expandLb.setText("+");
											reportsInfoTable.setVisible(false);
										}
									}
								
								});
								hpanel.setWidth("100%");
								hpanel.setStyleName("tableHeader");
								Label infoLb=new Label(result[i].get("month"));
								hpanel.add(expandLb);
								hpanel.add(infoLb);
								hpanel.setCellWidth(expandLb, "5px");
								
								reportPanel.add(hpanel);
								reportPanel.add(reportsInfoTable);
								allReportTable.setWidget(row, col, reportPanel);
								allReportTable.getFlexCellFormatter().setVerticalAlignment(row, col, HasVerticalAlignment.ALIGN_TOP);
								//panel.add();
								//panel.add(reportsInfoTable);

							}
						}
					}
				}, "HashMap<String,String>[]");
	}
	
	public void loadReportsDetails(String month,final CustomTable reportsTable){
		ArrayList params=new ArrayList();
		params.add(month);
		Util.callModuleMethod("RemittBillingTransport", "getMonthlyReportsDetails",
				params, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String> [] result = (HashMap[]) data;	
							if(result.length!=0){
								reportsTable.loadData(result);
							}

							
						}
					}
				}, "HashMap<String,String>[]");
	}
	
	
}
