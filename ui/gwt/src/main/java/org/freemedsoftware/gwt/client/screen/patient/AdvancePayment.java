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

package org.freemedsoftware.gwt.client.screen.patient;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.LedgerWidget;
import org.freemedsoftware.gwt.client.widget.LedgerWidget.PayCategory;

import com.google.gwt.user.client.ui.VerticalPanel;

public class AdvancePayment extends PatientEntryScreenInterface {

	protected VerticalPanel entryVerticalPanel;
	protected CustomListBox actionTypeList;
	protected VerticalPanel ledgerWigetContainer;
	protected VerticalPanel dataVerticalPanel;
	protected CustomTable advPaymentsViewTable;

	public AdvancePayment() {
		entryVerticalPanel = new VerticalPanel();
		entryVerticalPanel.setSize("100%", "100%");
		entryVerticalPanel.setSpacing(5);

		dataVerticalPanel = new VerticalPanel();
		dataVerticalPanel.setSize("100%", "100%");

		VerticalPanel vpanel = new VerticalPanel();
		dataVerticalPanel.setSize("100%", "100%");
		dataVerticalPanel.setSpacing(5);
		vpanel.add(entryVerticalPanel);
		vpanel.add(dataVerticalPanel);

		advPaymentsViewTable = new CustomTable();
		advPaymentsViewTable.setIndexName("Id");
		advPaymentsViewTable.setSize("100%", "100%");
		advPaymentsViewTable.addColumn(_("Payment Amount"), "amount");
		advPaymentsViewTable.addColumn(_("Payment Date"), "pay_date");
		advPaymentsViewTable.addColumn(_("Description"), "descp");
		advPaymentsViewTable.addColumn(_("Payment Category"), "category");
		dataVerticalPanel.add(advPaymentsViewTable);
		initWidget(vpanel);
	}

	public void loadUI() {
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

		ledgerWigetContainer = new VerticalPanel();
		// actionTypeGroup.setWidth("40%");
		// radioButtonPanel.add(yesNoRadionButtonGroup);

		final CustomRequestCallback cb = new CustomRequestCallback() {
			@Override
			public void onError() {

			}

			@Override
			public void jsonifiedData(Object data) {
				closeScreen();
			}
		};
		LedgerWidget pw = new LedgerWidget("0", patientId.toString(), "",
				PayCategory.PAYMENT, cb);
		ledgerWigetContainer.clear();
		ledgerWigetContainer.add(pw);
		entryVerticalPanel.add(ledgerWigetContainer);
	}
}
