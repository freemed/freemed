/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
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

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.i18n.client.HasDirection.Direction;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;

public class AllergyEntryScreen extends PatientScreenInterface {

	protected HashMap<String, String> data = new HashMap<String, String>();
	protected TextBox allergyTextBox = new TextBox();
	protected TextBox reactionTextBox = new TextBox();
	protected TextBox severityTextBox = new TextBox();
	protected final String className = "org.freemedsoftware.gwt.client.screen.patient.AllergyEntryScreen";

	public AllergyEntryScreen() {

		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);

		final Label allergyLabel = new Label("Allergy");
		flexTable.setWidget(0, 0, allergyLabel);
		allergyLabel.setDirection(Direction.RTL);

		final Label reactionLabel = new Label("Reaction");
		flexTable.setWidget(1, 0, reactionLabel);
		reactionLabel.setDirection(Direction.RTL);

		final Label severityLabel = new Label("Severity");
		flexTable.setWidget(2, 0, severityLabel);
		severityLabel.setDirection(Direction.RTL);

		flexTable.setWidget(0, 1, allergyTextBox);
		flexTable.getFlexCellFormatter().setColSpan(0, 1, 2);
		allergyTextBox.setWidth("100%");

		flexTable.setWidget(1, 1, reactionTextBox);
		flexTable.getFlexCellFormatter().setColSpan(1, 1, 2);
		reactionTextBox.setWidth("100%");

		flexTable.setWidget(2, 1, severityTextBox);
		flexTable.getFlexCellFormatter().setColSpan(2, 1, 2);
		severityTextBox.setWidth("100%");

		final Button saveButton = new Button();
		flexTable.setWidget(3, 1, saveButton);
		saveButton.setText("Save");
		saveButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				// TODO add function to check input
				if (checkInput()) {
					saveButton.setEnabled(false);
					saveForm();
					// at the very end: close screen
					closeScreen();
				} else {
					Window.alert("Please fill in all fields!");
				}
			}
		});

		final Button resetButton = new Button();
		flexTable.setWidget(3, 2, resetButton);
		resetButton.setText("Reset");
		resetButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				resetForm();
			}
		});
	}

	public boolean checkInput() {
		Boolean complete = true;
		if (allergyTextBox.getText() == "") {
			complete = false;
		} else if (reactionTextBox.getText() == "") {
			complete = false;
		} else if (severityTextBox.getText() == "") {
			complete = false;
		}
		return complete;

	}

	public void resetForm() {
		allergyTextBox.setText("");
		reactionTextBox.setText("");
		severityTextBox.setText("");
	}

	public void saveForm() {
		data.put("patient", Integer.toString(patientId));
		data.put("allergy", allergyTextBox.getText());
		data.put("reaction", reactionTextBox.getText());
		data.put("severity", severityTextBox.getText());

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			state.getToaster().addItem(className, "Added Allergy.",
					Toaster.TOASTER_INFO);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(data) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL
							.encode(Util.getJsonRequest(
									"org.freemedsoftware.module.Allergies.add",
									params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						state.getToaster()
								.addItem(className, "Failed to add Allergy.",
										Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[] r = (String[]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[]");
							if (r != null) {
								state.getToaster().addItem(className,
										"Added Allergy.", Toaster.TOASTER_INFO);
							}
						} else {
							state.getToaster().addItem(className,
									"Failed to add Allergy",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				state.getToaster().addItem(className, "Failed to add Allergy.",
						Toaster.TOASTER_ERROR);
			}
		} else {
			// TODO: GWT-RPC Stuff
		}

	}

}
