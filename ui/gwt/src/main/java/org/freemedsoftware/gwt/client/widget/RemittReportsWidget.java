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

package org.freemedsoftware.gwt.client.widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.RemittBillingWidget.BillingType;

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

public class RemittReportsWidget extends Composite {

	protected VerticalPanel panel;
	protected FlexTable allReportTable;
	protected VerticalPanel reportsPanel;
	protected VerticalPanel rebillPanel;

	public RemittReportsWidget() {
		panel = new VerticalPanel();
		panel.setSpacing(10);
		initWidget(panel);
		reportsPanel = new VerticalPanel();
		reportsPanel.setWidth("100%");
		rebillPanel = new VerticalPanel();
		rebillPanel.setWidth("100%");
		rebillPanel.setVisible(false);

		panel.add(reportsPanel);
		panel.add(rebillPanel);
		loadMonthsInfo();
	}

	public void loadMonthsInfo() {
		allReportTable = new FlexTable();
		allReportTable.setWidth("80%");
		reportsPanel.clear();
		reportsPanel.add(allReportTable);
		Util.callModuleMethod("RemittBillingTransport", "getMonthsInfo",
				(Integer) null, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@SuppressWarnings("unchecked")
					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String>[] result = (HashMap[]) data;
							for (int i = 0; i < result.length; i++) {
								int row = i / 2;
								int col = i % 2;
								VerticalPanel reportPanel = new VerticalPanel();
								reportPanel.setSpacing(10);
								reportPanel.setWidth("70%");
								HorizontalPanel hpanel = new HorizontalPanel();
								hpanel.setSpacing(5);
								final Label expandLb = new Label("+");
								final CustomTable reportsInfoTable = new CustomTable();
								reportsInfoTable.setAllowSelection(false);
								reportsInfoTable.setWidth("100%");
								reportsInfoTable
										.addColumn(_("Report"), "filename");
								reportsInfoTable.addColumn(_("Size"), "filesize");
								reportsInfoTable.addColumn(_("Date Sent"),
										"inserted");
								reportsInfoTable.addColumn(_("Action"), "action");
								reportsInfoTable
										.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
											@Override
											public Widget setColumn(
													String columnName,
													final HashMap<String, String> data) {
												if (columnName
														.compareTo("action") == 0) {

													HorizontalPanel actionPanel = new HorizontalPanel();
													actionPanel.setSpacing(5);
													HTML htmlLedger = new HTML(
															"<a href=\"javascript:undefined;\">" + _("View") + "</a>");

													htmlLedger
															.addClickHandler(new ClickHandler() {
																@Override
																public void onClick(
																		ClickEvent arg0) {

																	String[] params = {
																			"output",
																			data
																					.get("filename"),
																			"html" };
																	Window
																			.open(
																					URL
																							.encode(Util
																									.getJsonRequest(
																											"org.freemedsoftware.api.Remitt.GetFile",
																											params)),
																					data
																							.get("filename"),
																					"");

																}

															});
													HTML htmlReSend = null;
													if (data.get("originalId") != null) {
														htmlReSend = new HTML(
																"<a href=\"javascript:undefined;\">" + _("Re-Send") + "</a>");

														htmlReSend
																.addClickHandler(new ClickHandler() {
																	@Override
																	public void onClick(
																			ClickEvent arg0) {

																		CustomRequestCallback cb = new CustomRequestCallback() {
																			@Override
																			public void onError() {

																			}

																			@Override
																			public void jsonifiedData(
																					Object data) {
																				rebillPanel
																						.setVisible(false);
																				reportsPanel
																						.setVisible(true);
																				loadMonthsInfo();

																			}
																		};
																		reportsPanel
																				.setVisible(false);
																		rebillPanel
																				.clear();

																		HashSet<String> hs = new HashSet<String>();
																		hs
																				.add(data
																						.get("originalId"));
																		RemittBillingWidget billClaimsWidget = new RemittBillingWidget(
																				hs,
																				cb,
																				BillingType.REBILL);
																		rebillPanel
																				.add(billClaimsWidget);
																		rebillPanel
																				.setVisible(true);

																	}

																});
													} else {
														htmlReSend = new HTML(
																"<a href=\"javascript:undefined;\"  style=\"cursor:default;color: blue;\">" + _("Re-Send") + "</a>");
													}
													actionPanel.add(htmlLedger);
													actionPanel.add(htmlReSend);
													return actionPanel;
												} else if (columnName
														.compareTo("inserted") == 0) {
													Label lb = new Label(data
															.get("inserted")
															.substring(0, 10));
													return lb;
												} else {
													return (Widget) null;
												}

											}
										});
								reportsInfoTable.setVisible(false);
								expandLb.getElement().getStyle().setCursor(
										Cursor.POINTER);
								final int index = i;
								expandLb.addClickHandler(new ClickHandler() {

									@Override
									public void onClick(ClickEvent arg0) {
										if (expandLb.getText().trim().equals(
												"+")) {
											expandLb.setText("-");
											reportsInfoTable.setVisible(true);
											loadReportsDetails(result[index]
													.get("month"),
													reportsInfoTable);
										} else {
											expandLb.setText("+");
											reportsInfoTable.setVisible(false);
										}
									}

								});
								hpanel.setWidth("100%");
								hpanel
										.setStyleName(AppConstants.STYLE_TABLE_HEADER);
								Label infoLb = new Label(result[i].get("month"));
								hpanel.add(expandLb);
								hpanel.add(infoLb);
								hpanel.setCellWidth(expandLb, "5px");

								reportPanel.add(hpanel);
								reportPanel.add(reportsInfoTable);
								allReportTable.setWidget(row, col, reportPanel);
								allReportTable.getFlexCellFormatter()
										.setVerticalAlignment(row, col,
												HasVerticalAlignment.ALIGN_TOP);
								// panel.add();
								// panel.add(reportsInfoTable);

							}
						}
					}
				}, "HashMap<String,String>[]");
	}

	@SuppressWarnings("unchecked")
	public void loadReportsDetails(String month, final CustomTable reportsTable) {
		ArrayList params = new ArrayList();
		params.add(month);
		Util.callModuleMethod("RemittBillingTransport",
				"getMonthlyReportsDetails", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String>[] result = (HashMap[]) data;
							if (result.length != 0) {
								reportsTable.loadData(result);
							}

						}
					}
				}, "HashMap<String,String>[]");
	}

}
