/*
 * $Ids$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;

import com.google.gwt.json.client.JSONArray;
import com.google.gwt.json.client.JSONNumber;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.FocusListener;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.RadioButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class EmrPrintDialog extends DialogBox {

	protected Button printfaxButton;

	protected Label messageLabel;

	protected RadioButton printerMethod, faxMethod, browserMethod;

	protected SupportModuleWidget printerWidget;

	protected TextBox faxNumber;

	protected CurrentState state;

	protected Integer[] items;

	public EmrPrintDialog() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		setWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);
		flexTable.setWidth("100%");

		browserMethod = new RadioButton("printingMethod");
		flexTable.setWidget(0, 0, browserMethod);
		browserMethod.setChecked(true);
		browserMethod.setName("printingMethod");
		browserMethod.setText("Browser");

		printerMethod = new RadioButton("printingMethod");
		flexTable.setWidget(1, 0, printerMethod);
		printerMethod.setName("printingMethod");
		printerMethod.setText("Printer");

		printerWidget = new SupportModuleWidget();
		printerWidget.setModuleName("Printers");
		flexTable.setWidget(1, 1, printerWidget);

		faxMethod = new RadioButton("printingMethod");
		flexTable.setWidget(2, 0, faxMethod);
		faxMethod.setName("printingMethod");
		faxMethod.setText("Fax");

		faxNumber = new TextBox();
		faxNumber.addFocusListener(new FocusListener() {
			public void onFocus(Widget w) {
				faxMethod.setChecked(true);
			}

			public void onLostFocus(Widget w) {
			}
		});
		flexTable.setWidget(2, 1, faxNumber);
		faxNumber.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel.setWidth("100%");
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		printfaxButton = new Button();
		horizontalPanel.add(printfaxButton);
		printfaxButton.setText("Print/Fax");
		printfaxButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				printfaxButton.setEnabled(false);
				if (faxMethod.isChecked()) {
					// Fax
					printToFax();
				} else if (browserMethod.isChecked()) {
					// Browser
					printToBrowser();
				} else {
					// Printer
					printToPrinter();
				}
			}
		});

		final Button cancelButton = new Button();
		horizontalPanel.add(cancelButton);
		cancelButton.setText("Cancel");
		cancelButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				closeDialog();
			}
		});

		messageLabel = new Label("");
		verticalPanel.add(messageLabel);
		messageLabel.setWidth("100%");
	}

	/**
	 * Set items ids to be printed as referenced in the patient_emr aggregation
	 * table.
	 * 
	 * @param i
	 */
	public void setItems(Integer[] i) {
		items = i;
	}

	/**
	 * Set the current state structure.
	 * 
	 * @param s
	 */
	public void setState(CurrentState s) {
		state = s;
	}

	/**
	 * Hide and remove dialog.
	 */
	public void closeDialog() {
		hide();
		removeFromParent();
	}

	public int printToFax() {
		String f = faxNumber.getText();
		if (f.length() < 7) {
			messageLabel.setText("Invalid fax number.");
			printfaxButton.setEnabled(true);
			return 1;
		}
		state.getToaster().addItem("FaxOSubsystem", "Sending fax to " + f);
		getProxy().PrintToFax(f, items, new AsyncCallback() {
			public void onSuccess(Object o) {
				closeDialog();
			}

			public void onFailure(Throwable t) {
				state.getToaster().addItem("FaxSubsystemError",
						"Error faxing document.", Toaster.TOASTER_ERROR);
			}
		});
		return 0;
	}

	public int printToPrinter() {
		Integer p = printerWidget.getValue();
		if (p < 1) {
			messageLabel.setText("Please select a printer.");
			printfaxButton.setEnabled(true);
			return 1;
		}
		state.getToaster().addItem("PrintSubsystem",
				"Sending document to printer.");
		getProxy().PrintToPrinter("", items, new AsyncCallback() {
			public void onSuccess(Object o) {
				closeDialog();
			}

			public void onFailure(Throwable t) {
				state.getToaster().addItem("PrintSubsystemError",
						"Error sending document.", Toaster.TOASTER_ERROR);
			}
		});
		return 0;
	}

	public int printToBrowser() {
		String[] args = null;
		JSONArray a = new JSONArray();
		for (int iter = 0; iter < items.length; iter++) {
			a.set(iter, new JSONNumber(items[iter]));
		}
		args[0] = a.toString();
		String url = Util.getJsonRequest(
				"org.freemedsoftware.api.ModuleInterface.PrintToBrowser", args);
		Window.open(url, "", "");
		closeDialog();
		return 0;
	}

	private ModuleInterfaceAsync getProxy() {
		ModuleInterfaceAsync p = null;
		try {
			p = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.api.ModuleInterface");
		} catch (Exception ex) {
		}
		return p;
	}

}
