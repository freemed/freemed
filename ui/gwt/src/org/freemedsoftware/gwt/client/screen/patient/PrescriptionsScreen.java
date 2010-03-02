/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.DrugWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.i18n.client.HasDirection.Direction;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;

import eu.future.earth.gwt.client.DateEditFieldWithPicker;

public class PrescriptionsScreen extends PatientScreenInterface {

	final SupportModuleWidget wProvider = new SupportModuleWidget(
			"ProviderModule");
	final DrugWidget wDrug = new DrugWidget();
	final SupportModuleWidget wQuantity = new SupportModuleWidget(
			"DrugQuantityQualifiers");
	
	public PrescriptionsScreen() {
		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);

		final Label dateLabel = new Label("Starting Date");
		flexTable.setWidget(0, 0, dateLabel);
		dateLabel.setDirection(Direction.RTL);

		final Label providerLabel = new Label("Provider");
		flexTable.setWidget(1, 0, providerLabel);
		providerLabel.setDirection(Direction.RTL);

		final Label drugLabel = new Label("Drug");
		flexTable.setWidget(2, 0, drugLabel);
		drugLabel.setDirection(Direction.RTL);

		final Label quantityLabel = new Label("Quantity");
		flexTable.setWidget(3, 0, quantityLabel);
		quantityLabel.setDirection(Direction.RTL);

		final Label intervalLabel = new Label("Interval");
		flexTable.setWidget(4, 0, intervalLabel);
		intervalLabel.setDirection(Direction.RTL);

		final Label substitutionsLabel = new Label("Substitutions");
		flexTable.setWidget(5, 0, substitutionsLabel);
		substitutionsLabel.setDirection(Direction.RTL);

		final Label coverageStatusLabel = new Label("Coverage Status");
		flexTable.setWidget(6, 0, coverageStatusLabel);
		coverageStatusLabel.setDirection(Direction.RTL);

		final Label refillsLabel = new Label("Refills");
		flexTable.setWidget(7, 0, refillsLabel);
		refillsLabel.setDirection(Direction.RTL);

		final Label signatureLabel = new Label("Signature");
		flexTable.setWidget(8, 0, signatureLabel);
		signatureLabel.setDirection(Direction.RTL);

		final Label noteLabel = new Label("Note");
		flexTable.setWidget(9, 0, noteLabel);
		noteLabel.setDirection(Direction.RTL);

		final DateEditFieldWithPicker wDate = new DateEditFieldWithPicker();
		flexTable.setWidget(0, 1, wDate);
		flexTable.getFlexCellFormatter().setColSpan(0, 1, 2);

		flexTable.setWidget(1, 1, wProvider);
		flexTable.getFlexCellFormatter().setColSpan(1, 1, 2);

		flexTable.setWidget(2, 1, wDrug);
		flexTable.getFlexCellFormatter().setColSpan(2, 1, 2);

		flexTable.setWidget(3, 1, wQuantity);
		flexTable.getFlexCellFormatter().setColSpan(3, 1, 2);

		final TextBox wInterval = new TextBox();
		flexTable.setWidget(4, 1, wInterval);
		flexTable.getFlexCellFormatter().setColSpan(4, 1, 2);

		final TextBox wSubstitutions = new TextBox();
		flexTable.setWidget(5, 1, wSubstitutions);
		flexTable.getFlexCellFormatter().setColSpan(5, 1, 2);

		final TextBox wCoverageStatus = new TextBox();
		flexTable.setWidget(6, 1, wCoverageStatus);
		flexTable.getFlexCellFormatter().setColSpan(6, 1, 2);

		final TextBox wRefills = new TextBox();
		flexTable.setWidget(7, 1, wRefills);
		flexTable.getFlexCellFormatter().setColSpan(8, 1, 2);

		final TextBox tSignature = new TextBox();
		flexTable.setWidget(8, 1, tSignature);
		flexTable.getFlexCellFormatter().setColSpan(8, 1, 2);
		tSignature.setWidth("100%");
		flexTable.getFlexCellFormatter().setColSpan(9, 1, 2);

		final Button saveButton = new Button("Save");
		flexTable.setWidget(10, 1, saveButton);
		saveButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				savePrescription();
			}
		});

		final Button resetButton = new Button("Reset");
		flexTable.setWidget(10, 2, resetButton);
		resetButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		Util.setFocus(wProvider);
	}

	public void savePrescription() {
		HashMap<String, String> data = new HashMap<String, String>();
		data.put("rxphy", Integer.toString(wProvider.getValue()));
		data.put("rxpatient", Integer.toString(patientId));
		data.put("rxdrug", wDrug.getStoredValue());
		// rxform
		// rxdosage
		// rxquantity
		data.put("rxquantityqual", Integer.toString(wQuantity.getValue()));
		// rxsize
		// rxunit
		// rxinterval
		// rxsubstitute
		// rxrefills
		// rxrefillinterval
		// rxperrefill
		// rxorigrx
		// rxdx
		// rxcovstatus
		// rxsig
		// rxnote

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String[] params = { JsonUtil.jsonify(data) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.Prescription.add",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem("PrescriptionScreen",
								"Failed to add Prescription",
								Toaster.TOASTER_ERROR);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							if (response.getText().compareToIgnoreCase("false") != 0) {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									// Successful
									CurrentState.getToaster().addItem(
											"PrescriptionScreen",
											"Successfully added prescription",
											Toaster.TOASTER_INFO);
								}
							} else {
								JsonUtil
										.debug("Received dummy response from JSON backend");
							}
						} else {
							CurrentState.getToaster().addItem(
									"PrescriptionScreen",
									"Failed to add Prescription",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				CurrentState.getToaster().addItem("PrescriptionScreen",
						"Failed to add Prescription", Toaster.TOASTER_ERROR);
			}
		} else {
			// GWT-RPC
		}

	}

	public void resetForm() {
		// TODO
	}
	
}
