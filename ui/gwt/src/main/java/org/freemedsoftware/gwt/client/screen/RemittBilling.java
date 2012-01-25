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

package org.freemedsoftware.gwt.client.screen;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.RemittReportsWidget;
import org.freemedsoftware.gwt.client.widget.ReportingWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Grid;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class RemittBilling extends ScreenInterface {

	public final static String moduleName = "RemittBillingTransport";

	protected Grid gridLinks = new Grid(10, 10);
	// protected CustomTable remittBillingTable;
	protected TabPanel tabPanel;
	private static List<RemittBilling> remittBillingScreenList = null;
	protected CustomTable statusTable;

	// Creates only desired amount of instances if we follow this pattern
	// otherwise we have public constructor as well
	public static RemittBilling getInstance() {
		RemittBilling remittBillingScreen = null;

		if (remittBillingScreenList == null)
			remittBillingScreenList = new ArrayList<RemittBilling>();
		if (remittBillingScreenList.size() < AppConstants.MAX_REPORTING_TABS)// creates
																				// &
																				// returns
																				// new
																				// next
																				// instance
																				// of
																				// SuperBillScreen
			remittBillingScreenList
					.add(remittBillingScreen = new RemittBilling());
		else
			remittBillingScreen = remittBillingScreenList
					.get(AppConstants.MAX_REPORTING_TABS - 1);
		return remittBillingScreen;
	}

	public static boolean removeInstance(RemittBilling remittBillingScreen) {
		return remittBillingScreenList.remove(remittBillingScreen);
	}

	public RemittBilling() {
		super(moduleName);
		tabPanel = new TabPanel();
		initWidget(tabPanel);
		// /////////////////////////////////////////////////////
		tabPanel.add(getPerformBillingUI(), _("Perform Billing"));
		// tabPanel.add(getBillingStatusUI(), "Billing Status");
		// tabPanel.add(getReBillingUI(), "Rebill");
		tabPanel.add(getShowReportsUI(), _("Show Reports"));

		tabPanel.selectTab(0);

		// ////////////////////////////////////
	}

	public VerticalPanel getPerformBillingUI() {
		final VerticalPanel performBillingVPanel = new VerticalPanel();
		performBillingVPanel.setWidth("100%");

		final HorizontalPanel buttonsHPanel = new HorizontalPanel();
		performBillingVPanel.add(buttonsHPanel);

		final CustomButton selectAllBtn = new CustomButton(_("Select All"),
				AppConstants.ICON_SELECT_ALL);
		buttonsHPanel.add(selectAllBtn);
		selectAllBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
			}
		});

		final CustomButton selectNoneBtn = new CustomButton(_("Select None"),
				AppConstants.ICON_SELECT_NONE);
		buttonsHPanel.add(selectNoneBtn);
		selectNoneBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
			}
		});

		final CustomButton submitClaimsBtn = new CustomButton(
				_("Submit Claims"), AppConstants.ICON_ADD);
		buttonsHPanel.add(submitClaimsBtn);
		submitClaimsBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
			}
		});

		final CustomTable topSelectionTable = new CustomTable();
		topSelectionTable.setWidth("100%");
		performBillingVPanel.add(topSelectionTable);
		topSelectionTable.getFlexTable().setWidget(0, 0,
				new Label(_("Clearinghouse")));
		topSelectionTable.getFlexTable().setWidget(0, 1,
				new Label(_("Billing Service")));
		topSelectionTable.getFlexTable().setWidget(0, 2,
				new Label(_("Billing Contact")));

		final CustomListBox clearingHouseList = new CustomListBox();
		Util.callModuleMethod("BillingClearinghouse", "picklist",
				(Integer) null, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							Iterator<String> iterator = result.keySet()
									.iterator();
							while (iterator.hasNext()) {
								String id = iterator.next();
								String item = result.get(id);
								clearingHouseList.addItem(item, id);
							}
						}
					}
				}, "HashMap<String,String>");
		topSelectionTable.getFlexTable().setWidget(1, 0, clearingHouseList);

		final CustomListBox billingServiceList = new CustomListBox();
		Util.callModuleMethod("BillingService", "picklist", (Integer) null,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							Iterator<String> iterator = result.keySet()
									.iterator();
							while (iterator.hasNext()) {
								String id = iterator.next();
								String item = result.get(id);
								billingServiceList.addItem(item, id);
							}
						}
					}
				}, "HashMap<String,String>");
		topSelectionTable.getFlexTable().setWidget(1, 1, billingServiceList);

		final CustomListBox billingContactList = new CustomListBox();
		Util.callModuleMethod("BillingContact", "picklist", (Integer) null,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							Iterator<String> iterator = result.keySet()
									.iterator();
							while (iterator.hasNext()) {
								String id = iterator.next();
								String item = result.get(id);
								billingContactList.addItem(item, id);
							}
						}
					}
				}, "HashMap<String,String>");
		topSelectionTable.getFlexTable().setWidget(1, 2, billingContactList);

		final CustomTable claimsSubmissionTable = new CustomTable();
		claimsSubmissionTable.setWidth("100%");
		performBillingVPanel.add(claimsSubmissionTable);
		claimsSubmissionTable.addColumn(_("Selected"), "selected");
		claimsSubmissionTable.addColumn(_("Patient"), "patient");
		claimsSubmissionTable.addColumn(_("Total Claims"), "claim_count");
		claimsSubmissionTable.setIndexName("patient_id");
		claimsSubmissionTable.setMultipleSelection(true);
		claimsSubmissionTable.showloading(true);
		Util.callModuleMethod("RemittBillingTransport", "PatientsToBill",
				(Integer) null, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							claimsSubmissionTable
									.loadData((HashMap<String, String>[]) data);
						} else {
							claimsSubmissionTable.showloading(false);
						}
					}
				}, "HashMap<String,String>[]");

		final List<CheckBox> checkBoxesList = new ArrayList<CheckBox>();

		final HashMap<String, String> selectedPatientsWithClaims = new HashMap<String, String>();

		claimsSubmissionTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					public Widget setColumn(String columnName,
							final HashMap<String, String> data) {
						if (columnName.compareTo("selected") == 0) {
							CheckBox c = new CheckBox();
							checkBoxesList.add(c);
							c.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
								@Override
								public void onValueChange(
										ValueChangeEvent<Boolean> arg0) {
									if (arg0.getValue())
										selectedPatientsWithClaims.put(
												data.get("patient_id"),
												data.get("claims"));
									else
										selectedPatientsWithClaims.remove(data
												.get("patient_id"));
								}
							});
							return c;
						} else {
							return (Widget) null;
						}
					}
				});

		selectAllBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				Iterator<CheckBox> itr = checkBoxesList.iterator();
				while (itr.hasNext()) {
					CheckBox checkBox = (CheckBox) itr.next();
					checkBox.setValue(true, true);
				}
			}
		});
		selectNoneBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				Iterator<CheckBox> itr = checkBoxesList.iterator();
				while (itr.hasNext()) {
					CheckBox checkBox = (CheckBox) itr.next();
					checkBox.setValue(false, true);
				}
			}
		});
		submitClaimsBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				if (selectedPatientsWithClaims.size() > 0) {
					List<String> patientsList = new ArrayList<String>();
					List<String> claimsList = new ArrayList<String>();

					Iterator<String> iterator = selectedPatientsWithClaims
							.keySet().iterator();
					while (iterator.hasNext()) {
						String key = iterator.next();
						String value = selectedPatientsWithClaims.get(key);
						patientsList.add(key);
						claimsList.add(key);
					}

					List paramsList = new ArrayList();
					paramsList.add(patientsList.toArray(new String[0]));
					paramsList.add(claimsList.toArray(new String[0]));
					Util.callModuleMethod("RemittBillingTransport",
							"ProcessClaims", paramsList,
							new CustomRequestCallback() {
								@Override
								public void onError() {
								}

								@Override
								public void jsonifiedData(Object data) {
								}
							}, "");

				} else
					Window.alert("Please select at least one claim!");
			}
		});

		return performBillingVPanel;
	}

	public Widget getBillingStatusUI() {
		RemittReportsWidget rrw = new RemittReportsWidget();
		return rrw;
	}

	public VerticalPanel getReBillingUI() {
		final VerticalPanel reBillingPanel = new VerticalPanel();
		return reBillingPanel;
	}

	public Widget getShowReportsUI() {
		//VerticalPanel billStatusPanel = new VerticalPanel();
		ReportingWidget reportingWidget = new ReportingWidget(
				AppConstants.REPORTING_BILLING);
		return reportingWidget;
	}

	@Override
	public void closeScreen() {
		super.closeScreen();
		removeInstance(this);
	}

}
