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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.LedgerWidget.PayCategory;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class LedgerPopup extends DialogBox {

	protected CustomTable procedureTable = null;

	protected String procedureId = "";

	protected CustomListBox actionList = null;

	public static String REBILL = "Rebill";
	public static String PAYMENT = "Payment";
	public static String COPAY = "Copay";
	public static String ADJUSTMENT = "Adjustment";
	public static String DEDUCTABLE = "Deductable";
	public static String WITH_HOLD = "Withhold";
	public static String TRANSFER = "Transfer";
	public static String ALLOWED_AMOUNT = "Allowed Amount";
	public static String DENIAL = "Denial";
	public static String WRITE_OFF = "Writeoff";
	public static String REFUND = "Refund";
	public static String MISTAKE = "Mistake";
	public static String LEDGER = "Ledger";

	@SuppressWarnings("unused")
	private LedgerPopup() {
	}

	public LedgerPopup(final String procedureId, final String patientId,
			final String procCovSrc) {
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);

		this.procedureId = procedureId;

		VerticalPanel popupContainer = new VerticalPanel();
		setWidget(popupContainer);

		// ///Top header
		final HorizontalPanel closeButtonContainer = new HorizontalPanel();
		popupContainer.add(closeButtonContainer);
		closeButtonContainer.setWidth("100%");

		Image closeImage = new Image("resources/images/close_x.16x16.png");
		closeImage.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				getLedgerPopup().hide();
			}
		});
		closeButtonContainer.add(closeImage);
		closeButtonContainer.setCellHorizontalAlignment(closeImage,
				HasHorizontalAlignment.ALIGN_RIGHT);

		// content panel
		final VerticalPanel contentVPanel = new VerticalPanel();
		popupContainer.add(contentVPanel);

		final VerticalPanel defaultVPanel = new VerticalPanel();
		contentVPanel.add(defaultVPanel);
		// ///View details
		final HorizontalPanel viewDetailHPanel = new HorizontalPanel();
		defaultVPanel.add(viewDetailHPanel);
		final Label procedureLabel = new Label("Procedure");
		viewDetailHPanel.add(procedureLabel);
		CustomButton showDetails = new CustomButton("View Details",
				AppConstants.ICON_VIEW);
		viewDetailHPanel.add(showDetails);

		showDetails.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				contentVPanel.clear();
				final VerticalPanel viewDetailsVPanel = new VerticalPanel();
				contentVPanel.add(viewDetailsVPanel);
				final CustomTable viewDetailsTable = new CustomTable();
				viewDetailsTable.removeTableStyle();
				viewDetailsVPanel.add(viewDetailsTable);
				viewDetailsTable.setWidth("100%");
				viewDetailsTable.addColumn("Date", "date");
				viewDetailsTable.addColumn("Type", "type");
				viewDetailsTable.addColumn("Description", "desc");
				viewDetailsTable.addColumn("Charges", "charge");
				viewDetailsTable.addColumn("Payments", "payment");
				viewDetailsTable.addColumn("Balance", "");
				Util.callApiMethod("Ledger", "getLedgerInfo", new Integer(
						procedureId), new CustomRequestCallback() {
					@Override
					public void onError() {

					}

					@SuppressWarnings("unchecked")
					@Override
					public void jsonifiedData(Object data) {
						HashMap<String, String>[] result = (HashMap<String, String>[]) data;
						viewDetailsTable.setMaximumRows(result.length);
						viewDetailsTable.loadData(result);
						// HashMap<String, String> totals =
						// result[result.length-1];
						Integer totalCharges = new Integer(
								result[result.length - 1].get("total_charges"));
						Integer totalPayments = new Integer(
								result[result.length - 1].get("total_payments"));

						FlexTable totalDetailsTable = viewDetailsTable
								.getFlexTable();
						int row = totalDetailsTable.getRowCount();
						totalDetailsTable.setHTML(row, 0, "Total");
						totalDetailsTable.setHTML(row, 3, totalCharges + "");
						totalDetailsTable.setHTML(row, 4, totalPayments + "");
						totalDetailsTable.setHTML(row, 5,
								(totalCharges - totalPayments) + "");
						totalDetailsTable.getRowFormatter().setStyleName(row,
								AppConstants.STYLE_TABLE_HEADER);
					}
				}, "HashMap<String,String>[]");
				final CustomButton backBtn = new CustomButton("Back",
						AppConstants.ICON_PREV);
				contentVPanel.add(backBtn);
				backBtn.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						contentVPanel.clear();
						contentVPanel.add(defaultVPanel);
					}
				});
			}
		});

		// /custom table

		procedureTable = new CustomTable();
		procedureTable.removeTableStyle();
		defaultVPanel.add(procedureTable);
		procedureTable.setMaximumRows(4);
		procedureTable.addColumn("Procedure Date", "proc_date");
		procedureTable.addColumn("Procedure Code", "proc_code");
		procedureTable.addColumn("Provider", "prov_name");
		procedureTable.addColumn("Charged", "proc_obal");
		procedureTable.addColumn("Charges", "proc_charges");
		procedureTable.addColumn("Paid", "proc_paid");
		procedureTable.addColumn("Balance", "proc_currbal");
		procedureTable.addColumn("Billed", "proc_billed");
		procedureTable.setIndexName("Id");

		// //////////action area
		final HorizontalPanel actionHPanel = new HorizontalPanel();
		final Label actionLabel = new Label("Action");
		actionHPanel.add(actionLabel);
		actionList = new CustomListBox();
		actionList.addItem("NONE SELECTED");
		actionList.addItem(REBILL);
		actionList.addItem(PAYMENT);
		actionList.addItem(COPAY);
		actionList.addItem(ADJUSTMENT);
		actionList.addItem(DEDUCTABLE);
		actionList.addItem(WITH_HOLD);
		actionList.addItem(TRANSFER);
		actionList.addItem(ALLOWED_AMOUNT);
		actionList.addItem(DENIAL);
		actionList.addItem(WRITE_OFF);
		actionList.addItem(REFUND);
		actionList.addItem(MISTAKE);
		actionList.addItem(LEDGER);
		actionHPanel.add(actionList);
		final CustomButton proceedButton = new CustomButton("Proceed",
				AppConstants.ICON_NEXT);
		actionHPanel.add(proceedButton);
		proceedButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				if (actionList.getSelectedIndex() != 0) {
					LedgerWidget pw = null;
					CustomRequestCallback cb = new CustomRequestCallback() {
						@Override
						public void onError() {

						}

						@Override
						public void jsonifiedData(Object data) {
							if (data.toString().equals("update")) {
								contentVPanel.clear();
								contentVPanel.add(defaultVPanel);
								refreshData();
							} else if (data.toString().equals("close")) {
								contentVPanel.clear();
								contentVPanel.add(defaultVPanel);
								refreshData();
							} else if (data.toString().equals("cancel")) {
								contentVPanel.clear();
								contentVPanel.add(defaultVPanel);
								refreshData();
							}
						}
					};
					boolean hasUI = true;
					if (actionList.getSelectedIndex() == 1) {
						hasUI = false;
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.REBILLED, cb);
					} else if (actionList.getSelectedIndex() == 2) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.PAYMENT, cb);
					} else if (actionList.getSelectedIndex() == 3) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.COPAY, cb);
					} else if (actionList.getSelectedIndex() == 4) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.ADJUSTMENT, cb);
					} else if (actionList.getSelectedIndex() == 5) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.DEDUCTABLE, cb);
					} else if (actionList.getSelectedIndex() == 6) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.WITHHOLD, cb);
					} else if (actionList.getSelectedIndex() == 7) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.TRANSFER, cb);
					} else if (actionList.getSelectedIndex() == 8) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.ALLOWEDAMOUNT, cb);
					} else if (actionList.getSelectedIndex() == 9) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.DENIAL, cb);
					} else if (actionList.getSelectedIndex() == 10) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.WRITEOFF, cb);
					} else if (actionList.getSelectedIndex() == 11) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.REFUND, cb);
					} else if (actionList.getSelectedIndex() == 12) {
						hasUI = false;
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.MISTAKE, cb);
					} else if (actionList.getSelectedIndex() == 13) {
						pw = new LedgerWidget(procedureId, patientId,
								procCovSrc, PayCategory.LEDGER, cb);
					}

					if (pw != null) {
						if (hasUI) {
							contentVPanel.clear();
							contentVPanel.add(pw);
						}
					}
				} else {
					Window.alert("Please select the action type");
				}
			}
		});
		defaultVPanel.add(actionHPanel);
		defaultVPanel.setCellHorizontalAlignment(actionHPanel,
				HasHorizontalAlignment.ALIGN_CENTER);
		refreshData();
	}

	public void refreshData() {
		Util.callApiMethod("ClaimLog", "getProcInfo", new Integer(procedureId),
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@SuppressWarnings("unchecked")
					@Override
					public void jsonifiedData(Object data) {
						HashMap<String, String>[] result = new HashMap[1];
						result[0] = (HashMap<String, String>) data;
						if (result[0].get("proc_allowed") != null)
							procedureTable.addColumn("Allowed", result[0]
									.get("proc_allowed"));
						if (result[0].get("proc_billed").equalsIgnoreCase("1"))
							result[0].put("proc_billed", "Yes");
						else
							result[0].put("proc_billed", "No");
						procedureTable.loadData(result);
					}
				}, "HashMap<String,String>");
	}

	public void removeAction(String action) {
		for (int index = 0; index < actionList.getItemCount(); index++) {
			if (actionList.getItemText(index).equals(action)) {
				actionList.removeItem(index);
				break;
			}
		}
	}

	public LedgerPopup getLedgerPopup() {
		return this;
	}
}
