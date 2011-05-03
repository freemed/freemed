/*
 * $Id$
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
package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.HTMLTable.RowFormatter;

public class LedgerWidget extends Composite {
	public enum PayCategory {
		PAYMENT, COPAY, ADJUSTMENT, WITHHOLD, DEDUCTABLE, TRANSFER, ALLOWEDAMOUNT, DENIAL, WRITEOFF, REFUND, REBILLED, MISTAKE, LEDGER
	};

	protected String procId;
	protected String patientId;
	protected String procCovType;
	private VerticalPanel ledgerPanel;
	protected int commonFieldsCount;
	protected int fieldCounter;
	protected FlexTable ledgerFlexTable;
	protected Label payDateLb;
	protected CustomDatePicker payDate;
	protected Label amountLb;
	protected TextBox tbAmount;
	protected Label descLb;
	protected TextArea tbDesc;
	protected HorizontalPanel buttonsActionPanel;
	protected Label headLb;
	protected String[] insuranceList;
	protected String moduleName;
	protected String[] params;
	protected PayCategory paycat;
	protected CustomListBox paySrcList;
	protected CustomListBox payTypeList;
	protected TextBox tbCheckNo;
	protected TextBox tbCreditCardNo;
	protected CustomDatePicker expDate;
	protected ListBox yearsList;
	protected ListBox monthsList;
	protected String functionName;
	protected CustomRequestCallback callback;
	protected CustomListBox destinationList;
	protected CustomListBox insCompanyList;
	protected CustomListBox transferList;
	protected FlexTable ledgerInfoFlexTable;
	protected CustomListBox copaysList;
	protected CustomListBox deductList;
	protected CustomRadioButtonGroup paymentTypeGroup;
	protected String covid;
	protected String deductId;
	protected Label payTypelb;
	protected boolean hasInsurance=false;
	protected int covCount; 

	public LedgerWidget(String prid, String pId, String pct, PayCategory pcat,
			CustomRequestCallback cb) {
		callback = cb;
		procId = prid;
		patientId = pId;
		procCovType = pct;
		paycat = pcat;
		ledgerPanel = new VerticalPanel();
		ledgerPanel.setSpacing(10);
		initWidget(ledgerPanel);
		moduleName = "org.freemedsoftware.api.Ledger";
		covCount=0;
		createCommonElements();
		handlePayCategory(paycat);
	}

	private void handlePayCategory(PayCategory paycat) {
		switch (paycat) {
		case PAYMENT:
			createPaymentUI();
			break;
		case COPAY:
			createCopayUI();
			break;
		case ADJUSTMENT:
			createAdjustmentUI();
			break;
		case WITHHOLD:
			createWithholdUI();
			break;
		case DEDUCTABLE:
			createDeductableUI();
			break;
		case TRANSFER:
			createTransferUI();
			break;
		case ALLOWEDAMOUNT:
			createAllowedAmmountUI();
			break;
		case DENIAL:
			createDenialUI();
			break;
		case WRITEOFF:
			createWriteoffUI();
			break;
		case REFUND:
			createRefundUI();
			break;
		case REBILLED:
			prepareDate();
			break;
		case MISTAKE:
			prepareDate();
		case LEDGER:
			createLedgerUI();
			break;
		}

	}

	public void createCommonElements() {

		payDateLb = new Label("Date Received");
		payDate = new CustomDatePicker();
		payDate.setValue(Util.getSQLDate(new Date()));
		amountLb = new Label();
		tbAmount = new TextBox();
		descLb = new Label("Description");
		tbDesc = new TextArea();
		headLb = new Label();
		headLb.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		ledgerPanel.add(headLb);
		ledgerFlexTable = new FlexTable();
		// paymentFlexTable.setSize("100%", "100%");
		ledgerPanel.add(ledgerFlexTable);
		buttonsActionPanel = new HorizontalPanel();
		buttonsActionPanel.setSpacing(5);
		CustomButton submitBtn = new CustomButton("Submit",
				AppConstants.ICON_SEND);
		submitBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				prepareDate();
			}
		});
		CustomButton cancelBtn = new CustomButton("Cancel",
				AppConstants.ICON_CANCEL);
		final LedgerWidget lw = this;
		cancelBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				lw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		buttonsActionPanel.add(submitBtn);
		buttonsActionPanel.add(cancelBtn);

	}

	private void createPaymentUI() {
		if (!procId.equals("0")) {
			headLb.setText("Payment");
		} else {
			headLb.setText("Patient Payment Responsibility");
		}

		fieldCounter = 0;
		payTypelb = new Label("Payment Type");
		paymentTypeGroup = new CustomRadioButtonGroup("actiontype");

		paymentTypeGroup.addItem("Payment", "1", new Command() {
			@Override
			public void execute() {
				createPayUI();
			}
		});
		paymentTypeGroup.addItem("Copay", "2", new Command() {
			@Override
			public void execute() {
				createCopayUI();
			}
		});
		paymentTypeGroup.addItem("Deductable", "3", new Command() {
			@Override
			public void execute() {
				createDeductableUI();
			}

		});

		paymentTypeGroup.setWidgetValue("1", true);
	}

	public void createPayUI() {
		fieldCounter = 0;
		ledgerFlexTable.clear();
		ledgerFlexTable.setWidget(fieldCounter, 0, payTypelb);
		ledgerFlexTable.setWidget(fieldCounter, 1, paymentTypeGroup);
		fieldCounter++;
		if (procId.equals("0")) {
			Label paySrclb = new Label("Payment Source");
			paySrcList = new CustomListBox();
			paySrcList.addItem("Patient", "0");
			loadCoverageByType(1, paySrcList);
			loadCoverageByType(2, paySrcList);
			loadCoverageByType(3, paySrcList);
			loadCoverageByType(4, paySrcList);
			ledgerFlexTable.setWidget(fieldCounter, 0, paySrclb);
			ledgerFlexTable.setWidget(fieldCounter, 1, paySrcList);
			fieldCounter++;
		}
		else{
			if(procCovType.equals("0")){
				paymentTypeGroup.customRadioButtonGroup.get(1).setEnabled(false);
				paymentTypeGroup.customRadioButtonGroup.get(2).setEnabled(false);
			}
			else{
				hasInsurance=true;
			}
		}
		Label payMethodlb = new Label("Payment Method");
		payTypeList = new CustomListBox();
		payTypeList.addItem("NONE SELECTED");
		payTypeList.addItem("cash", "0");
		payTypeList.addItem("cheque", "1");
		payTypeList.addItem("money order", "2");
		payTypeList.addItem("credit card", "3");
		payTypeList.addItem("traveler's check", "4");
		payTypeList.addItem("EFT", "5");
		payTypeList.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				if (payTypeList.getSelectedIndex() != 0) {
					handlePaymentType(payTypeList.getValue(payTypeList
							.getSelectedIndex()));
				}
			}

		});
		ledgerFlexTable.setWidget(fieldCounter, 0, payMethodlb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payTypeList);
		fieldCounter++;

		payDateLb.setText("Date Received");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		amountLb.setText("Payment Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		tbAmount.setText("");
		tbAmount.setEnabled(true);
		tbAmount.setVisible(true);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);
		fieldCounter++;

		commonFieldsCount = fieldCounter;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void handlePaymentType(String payType) {
		fieldCounter = commonFieldsCount;
		if (payType.equals("1") || payType.equals("4")) {
			Label chequeNoLb = new Label("Cheque Number");
			tbCheckNo = new TextBox();
			ledgerFlexTable.setWidget(fieldCounter, 0, chequeNoLb);
			ledgerFlexTable.setWidget(fieldCounter, 1, tbCheckNo);
			fieldCounter++;
		} else if (payType.equals("3")) {
			Label creditCardNoLb = new Label("Credit Card Number");
			tbCreditCardNo = new TextBox();
			ledgerFlexTable.setWidget(fieldCounter, 0, creditCardNoLb);
			ledgerFlexTable.setWidget(fieldCounter, 1, tbCreditCardNo);
			fieldCounter++;

			Label expDateLb = new Label("Expiration Date");
			monthsList = new ListBox();
			for (int i = 1; i <= 12; i++) {
				monthsList.addItem("" + i);
			}
			Label lb = new Label("/");
			Calendar calendar = new GregorianCalendar();
			yearsList = new ListBox();
			int year = calendar.get(Calendar.YEAR);
			for (int i = 1; i < 13; i++) {
				yearsList.addItem(year + "");
				year++;
			}
			HorizontalPanel hp = new HorizontalPanel();
			hp.add(monthsList);
			hp.add(lb);
			hp.add(yearsList);
			ledgerFlexTable.setWidget(fieldCounter, 0, expDateLb);
			ledgerFlexTable.setWidget(fieldCounter, 1, hp);
			fieldCounter++;
		} else if (!payType.equals("0")) {
			Window.alert("Not Implemented Yet.");
		}
		if (commonFieldsCount != fieldCounter || payType.equals("0")) {
			descLb.setText("Description");
			ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
			ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
			fieldCounter++;
		}
	}

	@SuppressWarnings("unchecked")
	private void createCopayUI() {
		// headLb.setText("Copay");
		fieldCounter = 0;
		ledgerFlexTable.clear();
		ledgerFlexTable.setWidget(fieldCounter, 0, payTypelb);
		ledgerFlexTable.setWidget(fieldCounter, 1, paymentTypeGroup);
		fieldCounter++;
		Label payMethodlb = new Label("Payment Method");
		payTypeList = new CustomListBox();
		payTypeList.addItem("NONE SELECTED");
		payTypeList.addItem("cash", "0");
		payTypeList.addItem("cheque", "1");
		payTypeList.addItem("money order", "2");
		payTypeList.addItem("credit card", "3");
		payTypeList.addItem("traveler's check", "4");
		payTypeList.addItem("EFT", "5");
		payTypeList.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				if (payTypeList.getSelectedIndex() != 0) {
					handlePaymentType(payTypeList.getValue(payTypeList
							.getSelectedIndex()));
				}
			}

		});
		ledgerFlexTable.setWidget(fieldCounter, 0, payMethodlb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payTypeList);
		fieldCounter++;

		payDateLb.setText("Date of Copay");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		amountLb.setText("Copay Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		copaysList = new CustomListBox();
		copaysList.addItem("NONE SELECTED", "0");
		covid = "";
		ArrayList params = new ArrayList();
		params.add(patientId);
		params.add(procId);
		Util.callApiMethod("Ledger", "getCoveragesCopayInfo", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							tbAmount.setText("");
							// tbAmount.setEnabled(false);
							if (result != null) {
								tbAmount.setText(result.get("copay"));
								covid = result.get("Id");
								// tbAmount.setEnabled(false);
							}
						}
					}
				}, "HashMap<String,String>");

		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);

		fieldCounter++;

		commonFieldsCount = fieldCounter;
		ledgerPanel.add(buttonsActionPanel);
	}

	private void createAdjustmentUI() {

		headLb.setText("Adjustment");

		fieldCounter = 0;
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		amountLb.setText("Adjustment Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void createWithholdUI() {

		headLb.setText("Withhold");

		fieldCounter = 0;
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		amountLb.setText("Withhold Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	@SuppressWarnings("unchecked")
	private void createDeductableUI() {
		// headLb.setText("Deductable");
		fieldCounter = 0;
		ledgerFlexTable.clear();
		ledgerFlexTable.setWidget(fieldCounter, 0, payTypelb);
		ledgerFlexTable.setWidget(fieldCounter, 1, paymentTypeGroup);
		fieldCounter++;
		Label payMethodlb = new Label("Payment Method");
		payTypeList = new CustomListBox();
		payTypeList.addItem("NONE SELECTED");
		payTypeList.addItem("cash", "0");
		payTypeList.addItem("cheque", "1");
		payTypeList.addItem("money order", "2");
		payTypeList.addItem("credit card", "3");
		payTypeList.addItem("traveler's check", "4");
		payTypeList.addItem("EFT", "5");
		payTypeList.addChangeHandler(new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				if (payTypeList.getSelectedIndex() != 0) {
					handlePaymentType(payTypeList.getValue(payTypeList
							.getSelectedIndex()));
				}
			}

		});
		ledgerFlexTable.setWidget(fieldCounter, 0, payMethodlb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payTypeList);
		fieldCounter++;

		payDateLb.setText("Date of Deductible");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		amountLb.setText("Deductable Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		deductList = new CustomListBox();
		deductList.addItem("NONE SELECTED", "0");
		covid = "";
		ArrayList params = new ArrayList();
		params.add(patientId);
		params.add(procId);
		Util.callApiMethod("Ledger", "getCoveragesDeductableInfo", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							tbAmount.setText("");
							// tbAmount.setEnabled(false);
							if (result != null) {

								tbAmount.setText(result.get("deduct"));
								covid = result.get("Id");
							}
						}
					}
				}, "HashMap<String,String>");
		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);

		fieldCounter++;
		commonFieldsCount = fieldCounter;
		ledgerPanel.add(buttonsActionPanel);
	}

	private void createTransferUI() {

		headLb.setText("Transfer");

		fieldCounter = 0;
		payDateLb.setText("Date of Transfer");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		Label transferToLb = new Label("Transfer to");
		ledgerFlexTable.setWidget(fieldCounter, 0, transferToLb);
		transferList = new CustomListBox();
		transferList.addItem("Patient", "0");
		loadInsuranceList(transferList);
		ledgerFlexTable.setWidget(fieldCounter, 1, transferList);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void createAllowedAmmountUI() {

		headLb.setText("Allowed Ammount");

		fieldCounter = 0;

		Label insCompanyLb = new Label("Insurance Company");
		ledgerFlexTable.setWidget(fieldCounter, 0, insCompanyLb);
		insCompanyList = new CustomListBox();
		loadInsuranceList(insCompanyList);
		ledgerFlexTable.setWidget(fieldCounter, 1, insCompanyList);
		fieldCounter++;

		payDateLb.setText("Date Received");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		amountLb.setText("Allowed Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void createDenialUI() {

		headLb.setText("Denial");

		fieldCounter = 0;
		payDateLb.setText("Date of Denial");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		Label adjustZeroLb = new Label("Adjust to Zero?");
		ledgerFlexTable.setWidget(fieldCounter, 0, adjustZeroLb);
		CustomListBox adjustZeroList = new CustomListBox();
		adjustZeroList.addItem("No");
		adjustZeroList.addItem("Yes");
		ledgerFlexTable.setWidget(fieldCounter, 1, adjustZeroList);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void createWriteoffUI() {
		headLb.setText("Writeoff");

		fieldCounter = 0;
		payDateLb.setText("Date of Writeoff");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void createRefundUI() {
		headLb.setText("Refund");
		fieldCounter = 0;

		payDateLb.setText("Date of Refund");
		ledgerFlexTable.setWidget(fieldCounter, 0, payDateLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, payDate);
		fieldCounter++;

		Label destinationLb = new Label("Destination");
		ledgerFlexTable.setWidget(fieldCounter, 0, destinationLb);
		destinationList = new CustomListBox();
		destinationList.addItem("Apply to Credit", "0");
		destinationList.addItem("Refund to Patient", "1");
		ledgerFlexTable.setWidget(fieldCounter, 1, destinationList);
		fieldCounter++;

		ledgerFlexTable.setWidget(fieldCounter, 0, descLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbDesc);
		fieldCounter++;

		amountLb.setText("Refund Amount");
		ledgerFlexTable.setWidget(fieldCounter, 0, amountLb);
		ledgerFlexTable.setWidget(fieldCounter, 1, tbAmount);
		fieldCounter++;

		ledgerPanel.add(buttonsActionPanel);
	}

	private void createLedgerUI() {
		ledgerInfoFlexTable = new FlexTable();
		ledgerPanel.add(ledgerInfoFlexTable);
		ledgerInfoFlexTable.setSize("100%", "100%");
		ledgerInfoFlexTable.setText(0, 0, "Date");
		ledgerInfoFlexTable.setText(0, 1, "Type");
		ledgerInfoFlexTable.setText(0, 2, "Description");
		ledgerInfoFlexTable.setText(0, 3, "Charges");
		ledgerInfoFlexTable.setText(0, 4, "Payments");
		ledgerInfoFlexTable.setText(0, 5, "Balance");
		RowFormatter rowFormatter = ledgerInfoFlexTable.getRowFormatter();
		rowFormatter.setStyleName(0, AppConstants.STYLE_TABLE_HEADER);
		CustomButton closeBtn = new CustomButton("Cancel",
				AppConstants.ICON_CANCEL);
		final LedgerWidget lw = this;
		closeBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				lw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		ledgerPanel.add(closeBtn);
		prepareDate();
	}

	public void loadInsuranceList(final CustomListBox insList) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { procId };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProcedureModule.getCoverages",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											hasInsurance=true;
											for (int i = 0; i < result.length; i++) {
												insList.addItem(result[i]
														.get("payer"),
														result[i].get("id"));
											}
										} else {

										}
									}
									else{
										paymentTypeGroup.customRadioButtonGroup.get(1).setEnabled(false);
										paymentTypeGroup.customRadioButtonGroup.get(2).setEnabled(false);
									}
								} catch (Exception e) {
									//Window.alert(e.getMessage());
									//paymentTypeGroup.customRadioButtonGroup.get(2).setEnabled(false);
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	private void prepareDate() {
		functionName = "";
		boolean isHandled = false;
		ArrayList<String> parameters = new ArrayList<String>();
		String errMsg = "";

		if (paycat == PayCategory.PAYMENT) {
			parameters.add(procId);
			parameters.add(payDate.getTextBox().getText());

			if (payTypeList.getValue(payTypeList.getSelectedIndex())
					.equals("0")) {
				functionName = moduleName + ".post_payment_cash";
			} else if (payTypeList.getValue(payTypeList.getSelectedIndex())
					.equals("1")
					|| payTypeList.getValue(payTypeList.getSelectedIndex())
							.equals("4")) {
				functionName = moduleName + ".post_payment_check";
				parameters.add(tbCheckNo.getText());
				if (tbCheckNo.getText() == null
						|| tbCheckNo.getText().equals("")) {
					errMsg += "Please Enter the Check Number \n";
				}
				if (payTypeList.getValue(payTypeList.getSelectedIndex())
						.equals("1")) {
					parameters.add("1");
				} else {
					parameters.add("4");
				}
			}

			else if (payTypeList.getValue(payTypeList.getSelectedIndex())
					.equals("3")) {
				functionName = moduleName + ".post_payment_credit_card";
				parameters.add(tbCreditCardNo.getText());
				if (tbCreditCardNo.getText() == null
						|| tbCreditCardNo.getText().equals("")) {
					errMsg += "Please Enter the Credit Card Number \n";
				}
				parameters.add(monthsList.getValue(monthsList
						.getSelectedIndex()));
				parameters
						.add(yearsList.getValue(yearsList.getSelectedIndex()));
			}

			else {
				errMsg += "Please Select the Payment Type \n";
			}
			if (paymentTypeGroup.getWidgetValue().equals("1")) {
				String tempMsg = validateIntegerTextBox(tbAmount,
						"Payment Amount");
				if (!tempMsg.equals("")) {
					errMsg += tempMsg + "\n";
				}
				parameters.add(tbAmount.getText());
				parameters.add(tbDesc.getText());
				if (procId.equals("0")) {
					parameters.add(patientId);
					parameters.add(paySrcList.getStoredValue());
				}
			} else if (paymentTypeGroup.getWidgetValue().equals("2")) {
				String tempMsg = validateIntegerTextBox(tbAmount,
						"Copay Amount");
				if (!tempMsg.equals("")) {
					errMsg += tempMsg + "\n";
				}
				parameters.add(tbAmount.getText());
				parameters.add(tbDesc.getText());
				parameters.add(patientId);
				if (covid.trim().equals(""))
					parameters.add("0");
				else
					parameters.add(covid);
				parameters.add("11"); // copay
			} else if (paymentTypeGroup.getWidgetValue().equals("3")) {
				String tempMsg = validateIntegerTextBox(tbAmount,
						"Deductible Amount");
				if (!tempMsg.equals("")) {
					errMsg += tempMsg + "\n";
				}
				parameters.add(tbAmount.getText());
				parameters.add(tbDesc.getText());
				parameters.add(patientId);
				if (covid.trim().equals(""))
					parameters.add("0");
				else
					parameters.add(covid);
				parameters.add("8"); // deductible
			}
		}

		else if (paycat == PayCategory.ADJUSTMENT) {
			functionName = moduleName + ".post_adjustment";
			parameters.add(procId);
			String tempMsg = validateIntegerTextBox(tbAmount,
					"Adjustment Amount");
			if (!tempMsg.equals("")) {
				errMsg += tempMsg + "\n";
			}
			parameters.add(tbAmount.getText());
			parameters.add(tbDesc.getText());
		}

		else if (paycat == PayCategory.WITHHOLD) {
			functionName = moduleName + ".post_withhold";
			parameters.add(procId);
			String tempMsg = validateIntegerTextBox(tbAmount, "Withhold Amount");
			if (!tempMsg.equals("")) {
				errMsg += tempMsg + "\n";
			}
			parameters.add(tbAmount.getText());
			parameters.add(tbDesc.getText());
		}

		else if (paycat == PayCategory.WRITEOFF) {
			functionName = moduleName + ".PostWriteoff";
			parameters.add(procId);
			parameters.add(tbDesc.getText());
		}

		else if (paycat == PayCategory.ALLOWEDAMOUNT) {
			functionName = moduleName + ".post_fee_adjustment";
			parameters.add(procId);
			if (insCompanyList.getStoredValue() != null
					&& !insCompanyList.getStoredValue().equals("0"))
				parameters.add(insCompanyList.getStoredValue());
			else
				parameters.add("0");
			String tempMsg = validateIntegerTextBox(tbAmount, "Allowed Amount");
			if (!tempMsg.equals("")) {
				errMsg += tempMsg + "\n";
			}
			parameters.add(tbAmount.getText());
			parameters.add(tbDesc.getText());
		}

		else if (paycat == PayCategory.DENIAL) {
			functionName = moduleName + ".move_to_next_coverage";
			parameters.add(procId);
		}

		else if (paycat == PayCategory.REFUND) {
			functionName = moduleName + ".post_refund";
			parameters.add(procId);
			String tempMsg = validateIntegerTextBox(tbAmount, "Refund Amount");
			if (!tempMsg.equals("")) {
				errMsg += tempMsg + "\n";
			}
			parameters.add(tbAmount.getText());
			parameters.add(destinationList.getStoredValue());
			parameters.add(tbDesc.getText());
		}

		else if (paycat == PayCategory.REBILLED) {
			functionName = moduleName + ".queue_for_rebill";
			parameters.add(procId);
			parameters.add(procCovType);
		}

		else if (paycat == PayCategory.TRANSFER) {
			functionName = moduleName + ".post_transfer";
			parameters.add(procId);
			parameters.add(transferList.getStoredValue());
			parameters.add(tbDesc.getText());
		}

		else if (paycat == PayCategory.MISTAKE) {
			functionName = moduleName + ".mistake";
			parameters.add(procId);
		}

		else if (paycat == PayCategory.LEDGER) {
			isHandled = true;
			functionName = moduleName + ".getLedgerInfo";
			parameters.add(procId);
			params = (String[]) parameters.toArray(new String[0]);
			getLedgerInfo();
		}
		if (!isHandled) {
			if (!errMsg.equals("")) {
				Window.alert(errMsg);
				return;
			} else {
				params = (String[]) parameters.toArray(new String[0]);
				processPayment();
			}
		}
	}

	private void processPayment() {
		JsonUtil.debug("before saving");
		final LedgerWidget lw = this;
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(functionName, params)));

		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
				}

				public void onResponseReceived(Request request,
						Response response) {

					if (200 == response.getStatusCode()) {
						try {
							Boolean result = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Boolean");
							if (result) {
								Util.showInfoMsg("Ledger",
										"Payment operation succeeded.");
								lw.removeFromParent();
								if(paycat == PayCategory.PAYMENT || paycat==PayCategory.COPAY || paycat==PayCategory.DEDUCTABLE)
								{
									if(CurrentState.getSystemConfig("auto_print_ptrcpt").equals("1")){
										printShortPatientReceipt();
									}
								}
								if ((paycat == PayCategory.REBILLED || paycat == PayCategory.WRITEOFF)
										&& !procId.equals("0")) {
									callback.jsonifiedData("close");
								} else {
									callback.jsonifiedData("update");
								}
							} else {
								Util.showErrorMsg("Ledger",
										"Payment operation failed.");
							}
						} catch (Exception e) {
							Util.showErrorMsg("Ledger",
									"Payment operation failed.");
						}
					} else {
						Util
								.showErrorMsg("Ledger",
										"Payment operation failed.");
					}
				}
			});
		} catch (RequestException e) {

		}
	}

	private void getLedgerInfo() {
		final LedgerWidget lw = this;
		RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, URL
				.encode(Util.getJsonRequest(functionName, params)));

		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable ex) {
				}

				@SuppressWarnings("unchecked")
				public void onResponseReceived(Request request,
						Response response) {

					if (200 == response.getStatusCode()) {

						HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
								.shoehornJson(JSONParser.parse(response
										.getText()), "HashMap<String,String>[]");

						if (result.length > 0) {
							for (int i = 0; i < result.length; i++) {
								int row = ledgerInfoFlexTable.getRowCount();
								if (i != (result.length - 1)) {
									ledgerInfoFlexTable.setText(row, 0,
											result[i].get("date"));
									ledgerInfoFlexTable.setText(row, 1,
											result[i].get("type"));
									ledgerInfoFlexTable.setText(row, 2,
											result[i].get("desc"));
									ledgerInfoFlexTable.setText(row, 3,
											result[i].get("charge"));
									ledgerInfoFlexTable.setText(row, 4,
											result[i].get("payment"));
									ledgerInfoFlexTable.setText(row, 5, "");
									if ((i % 2) == 0) {
										RowFormatter rowFormatter = ledgerInfoFlexTable
												.getRowFormatter();
										rowFormatter.setStyleName(row,
												AppConstants.STYLE_TABLE_ROW);
									} else {
										RowFormatter rowFormatter = ledgerInfoFlexTable
												.getRowFormatter();
										rowFormatter
												.setStyleName(
														row,
														AppConstants.STYLE_TABLE_ROW_ALTERNATE);
									}
								} else {
									ledgerInfoFlexTable
											.setText(row, 0, "Total");
									ledgerInfoFlexTable.setText(row, 3,
											result[i].get("total_charges"));
									ledgerInfoFlexTable.setText(row, 4,
											result[i].get("total_payments"));
									int charges = new Integer(result[i]
											.get("total_charges"));
									int payments = new Integer(result[i]
											.get("total_payments"));
									ledgerInfoFlexTable.setText(row, 5, ""
											+ (charges - payments));
									RowFormatter rowFormatter = ledgerInfoFlexTable
											.getRowFormatter();
									rowFormatter.setStyleName(row,
											AppConstants.STYLE_TABLE_HEADER);
								}
							}
						} else {
						}
					} else {
					}
				}
			});
		} catch (RequestException e) {

		}
	}

	public void loadCoverageByType(final int type, final CustomListBox lb) {

		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// ////////////////////
			String[] params = { patientId, type + "" };

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientCoverages.GetCoverageByType",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							if (Util.checkValidSessionResponse(response
									.getText())) {
								try {
									// Window.alert("Response is:"+type+"
									// :"+response.getText());
									covCount++;
									HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (result != null) {
										if (result.length != 0) {
											hasInsurance=true;
											String typeName = "";
											if (type == 1)
												typeName = "Primary ";
											else if (type == 2)
												typeName = "Secondary ";
											else if (type == 3)
												typeName = "Tertiary ";
											else if (type == 4)
												typeName = "Work Comp ";
											for (int i = 0; i < result.length; i++) {
												HashMap<String, String> m = (HashMap<String, String>) result[i];

												lb.addItem(typeName + "- "
														+ m.get("comp_name"), m
														.get("Id"));
											}
										} else {

										}
										if(covCount==4 && !hasInsurance){
											paymentTypeGroup.customRadioButtonGroup.get(1).setEnabled(false);
											paymentTypeGroup.customRadioButtonGroup.get(2).setEnabled(false);
										}
									}
								} catch (Exception e) {
									// Window.alert(e.getMessage());
								}
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {

		}
	}

	private String validateIntegerTextBox(TextBox tb, String fieldName) {
		String msg = new String("");
		if (tb.getText() == "" || tb.getText() == null) {
			msg += "Please specify " + fieldName + "\n";
		} else if (!Util.isNumber(tb.getText())) {
			msg += "The specified valued for " + fieldName
					+ " is not correct Number" + "\n";
		}
		return msg;
	}
	
	public void printShortPatientReceipt(){
		ArrayList<String> params = new ArrayList<String>();
		params.add("" + patientId);
		Util.callModuleMethod("PaymentModule", "getLastRecord", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@SuppressWarnings("unchecked")
					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							if (result != null) {
								List<String> reportParams = new ArrayList<String>();
								reportParams.add(result.get("id"));
								reportParams.add("1");
								Util.generateReportToPrinter("Patient Receipt Short", "pdf",
										reportParams);
							}
						}
					}
				}, "HashMap<String,String>");
	}

}
