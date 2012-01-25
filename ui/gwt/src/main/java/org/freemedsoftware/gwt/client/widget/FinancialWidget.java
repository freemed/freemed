/*
 * $Id$
 *
 * Authors:
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

package org.freemedsoftware.gwt.client.widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.patient.ProcedureScreen;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Element;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabBar;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class FinancialWidget extends WidgetInterface {
	public final static String moduleName = PatientScreen.moduleName;
	protected CustomTable currentTable;
	protected int maximumRows = 10;
	protected Integer patientId = new Integer(0);
	protected Label lbArrears;
	private TabPanel tabPanel;
	protected CustomTable procedureViewTable;
	PatientScreen patientScreen;
	public FinancialWidget() {
		super(moduleName);
		VerticalPanel panel = new VerticalPanel();
		panel.setWidth("100%");
		panel.setSpacing(1);
		initWidget(panel);
		tabPanel = new TabPanel();
		tabPanel.setSize("100%", "100%");
		tabPanel.setVisible(true);
		panel.add(tabPanel);
		TabBar tbar = tabPanel.getTabBar();
		Element tabBarFirstChild = tbar.getElement().getFirstChildElement()
				.getFirstChildElement().getFirstChildElement();
		tabBarFirstChild.setAttribute("width", "100%");
		tabBarFirstChild.setInnerHTML(_("FINANCIAL INFORMATION"));
		tabBarFirstChild.setClassName("label_bold");
		createCurrentTab();
	}

	public void createCurrentTab(){
		currentTable = new CustomTable();
		currentTable.setWidth("100%");
		currentTable.setIndexName("id");
		currentTable.setMaximumRows(maximumRows);
		currentTable.addColumn(_("Charge"), "charge");
		currentTable.addColumn(_("Payment"), "payment");
		currentTable.addColumn(_("Arrear"), "arrear");
		currentTable.addColumn(_("DOS"), "dos");
		currentTable.getFlexTable().getFlexCellFormatter().setWidth(0, 0,
				"70px");
		currentTable.getFlexTable().getFlexCellFormatter().setWidth(0, 1,
				"70px");
		currentTable.getFlexTable().getFlexCellFormatter().setWidth(0, 2,
				"70px");
		HTML html = new HTML("<hr/>");
		html.setWidth("100%");

		HorizontalPanel arrearsPanel = new HorizontalPanel();
		Label lb = new Label(_("Total Arrears") + "  =  ");
		lb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		lbArrears = new Label("0");
		arrearsPanel.add(lb);
		arrearsPanel.add(lbArrears);
		VerticalPanel currentVerticalPanel = new VerticalPanel();
		currentVerticalPanel.setWidth("100%");
		currentVerticalPanel.add(currentTable);
		currentVerticalPanel.add(html);
		currentVerticalPanel.add(arrearsPanel);
		tabPanel.add(currentVerticalPanel, _("Current"));
	}
	
	public void createProceduresTab(){
		procedureViewTable = new CustomTable();
		tabPanel.add(procedureViewTable, _("Procedures"));
		procedureViewTable.setIndexName("Id");
		procedureViewTable.setSize("100%", "100%");
		procedureViewTable.addColumn(_("Procedure Date"), "proc_date");
		procedureViewTable.addColumn(_("Procedure Code"), "proc_code");
		procedureViewTable.addColumn(_("Modifier"), "proc_mod");
		procedureViewTable.addColumn(_("Comments"), "comment");
		procedureViewTable.addColumn(_("Action"), "action");
		procedureViewTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							HashMap<String, String> data) {
						// Render only action column, otherwise skip renderer
						if (columnName.compareToIgnoreCase("action") != 0) {
							return null;
						}
						final CustomActionBar actionBar = new CustomActionBar(
								data);
						actionBar.applyPermissions(canRead, false, false,
								canModify, false);
						actionBar.showAction(HandleCustomAction.CLONE);
						actionBar
								.setHandleCustomAction(new HandleCustomAction() {
									@Override
									public void handleAction(int id,
											HashMap<String, String> data,
											int action) {
										if (action == HandleCustomAction.MODIFY) {
											try {
												
												ProcedureScreen ps = new ProcedureScreen();
												ps.setModificationRecordId(id);
												ps.setPatientId(patientId);
												ps.loadData();
												Util.spawnTabPatient("Manage Procedures", ps, patientScreen);
											} catch (Exception e) {
												GWT
														.log(
																"Caught exception: ",
																e);
											}
										} else if (action == HandleCustomAction.PRINT) {
											List<String> params = new ArrayList<String>();
											params.add(id + "");
											String reportName = "Patient Receipt";
											Util.generateReportToBrowser(
													reportName, "pdf", params);
										} else if (action == HandleCustomAction.VIEW) {
											List<String> params = new ArrayList<String>();
											params.add(id + "");
											String reportName = "Patient Receipt";
											Util.generateReportToBrowser(
													reportName, "html", params);
										}
										else if (action == HandleCustomAction.CLONE) {
											try {											
												ProcedureScreen ps = new ProcedureScreen();
												ps.setCloneRecordID(id);
												ps.setPatientId(patientId);
												ps.loadData();
												Util.spawnTabPatient(_("Manage Procedures"), ps, patientScreen);
											} catch (Exception e) {
												GWT
														.log(
																"Caught exception: ",
																e);
											}
										} 
									}
								});
						return actionBar;
					}
				});
		loadProcedureTableData();
	}
	
	public void loadProcedureTableData() {
		procedureViewTable.clearData();
		if (Util.getProgramMode() == ProgramMode.STUBBED) {

		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {			
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.getProcedureInfo",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								@SuppressWarnings("unchecked")
								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>[]");
								procedureViewTable.loadData(result);
							} else {
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
				procedureViewTable.setVisible(true);
			}
		} else {
		}

	}
	
	@SuppressWarnings({ "rawtypes", "unchecked" })
	public void loadTransactionsData(){
		ArrayList params1 = new ArrayList();
		params1.add(patientId.toString());
		Util.callModuleMethod("ProcedureModule", "getNonZeroBalProcs", params1,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String>[] result = (HashMap[]) data;
							if (result.length != 0) {
								currentTable.loadData(result);
							}

						}
					}
				}, "HashMap<String,String>[]");
		ArrayList params2 = new ArrayList();
		params2.add(patientId.toString());
		Util.callModuleMethod("ProcedureModule", "getTotalArrears", params2,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String> result = (HashMap) data;
							if (result.get("tarrears") != null) {
								lbArrears.setText(result.get("tarrears"));
							}

						}
					}
				}, "HashMap<String,String>");
	}
	public void createPaymentsTab(){
		final CustomTable advPaymentsViewTable = new CustomTable();
		advPaymentsViewTable.setIndexName("Id");
		advPaymentsViewTable.setSize("100%", "100%");
		advPaymentsViewTable.addColumn(_("Payment Amount"), "amount");
		advPaymentsViewTable.addColumn(_("Payment Date"), "pay_date");
		advPaymentsViewTable.addColumn(_("Description"), "descp");
		advPaymentsViewTable.addColumn(_("Payment Category"), "category");
		ArrayList<String> params = new ArrayList<String>();
		params.add("" + patientId);
		Util.callModuleMethod("PaymentModule", "getAdvancePaymentInfo", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@SuppressWarnings("unchecked")
					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String>[] result = (HashMap<String, String>[]) data;
							if (result != null) {
								if (result.length > 0) {
									advPaymentsViewTable.loadData(result);
								}
							}
						}
					}
				}, "HashMap<String,String>[]");
		tabPanel.add(advPaymentsViewTable, "Payment");
	}
	public void setPatientId(Integer id) {
		patientId = id;
		// Call initial data load, as patient id is set
		loadData();
	}
	
	public void setPatientScreen(PatientScreen ps){
		patientScreen=ps;
	}

	public void loadData() {
		loadTransactionsData();
		createProceduresTab();
		createPaymentsTab();
		tabPanel.selectTab(0);
	}

	public void setMaximumRows(int maxRows) {
		maximumRows = maxRows;
		currentTable.setMaximumRows(maxRows);
	}
}
