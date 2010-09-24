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

package org.freemedsoftware.gwt.client.screen.entry;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.EntryScreenInterface;
import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTextArea;
import org.freemedsoftware.gwt.client.widget.CustomTextBox;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class ClinicRegistrationEntry extends EntryScreenInterface {

	protected String moduleName = "ClinicRegistration";

	protected HashMap<String, HashSetter> setters = new HashMap<String, HashSetter>();

	protected CustomDatePicker wDateOfBirth = null;

	protected CustomTextBox wLastName1 = null, wLastName2 = null,
			wFirstName = null, wAge = null;

	protected CustomListBox wGender = null;

	protected CustomTextArea wNotes = null;

	public ClinicRegistrationEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		int pos = 0;

		final Label lastName1Label = new Label("Last Name 1");
		flexTable.setWidget(pos, 0, lastName1Label);
		wLastName1 = new CustomTextBox();
		wLastName1.setHashMapping("lastname");
		addEntryWidget("lastname", wLastName1);
		flexTable.setWidget(pos, 1, wLastName1);
		pos++;

		final Label lastName2Label = new Label("Last Name 2");
		flexTable.setWidget(pos, 0, lastName2Label);
		wLastName2 = new CustomTextBox();
		wLastName2.setHashMapping("lastname2");
		addEntryWidget("lastname2", wLastName2);
		flexTable.setWidget(pos, 1, wLastName2);
		pos++;

		final Label firstNameLabel = new Label("First Name");
		flexTable.setWidget(pos, 0, firstNameLabel);
		wFirstName = new CustomTextBox();
		wFirstName.setHashMapping("firstname");
		addEntryWidget("firstname", wFirstName);
		flexTable.setWidget(pos, 1, wFirstName);
		pos++;

		final Label dateOfBirthLabel = new Label("Date of Birth");
		flexTable.setWidget(pos, 0, dateOfBirthLabel);
		wDateOfBirth = new CustomDatePicker();
		wDateOfBirth.setHashMapping("dob");
		addEntryWidget("dob", wDateOfBirth);
		flexTable.setWidget(pos, 1, wDateOfBirth);
		pos++;

		final Label ageLabel = new Label("Age (if no date of birth)");
		flexTable.setWidget(pos, 0, ageLabel);
		wAge = new CustomTextBox();
		wAge.setHashMapping("age");
		addEntryWidget("age", wAge);
		flexTable.setWidget(pos, 1, wAge);
		pos++;

		final Label genderLabel = new Label("Gender");
		flexTable.setWidget(pos, 0, genderLabel);
		wGender = new CustomListBox();
		wGender.addItem("Male", "m");
		wGender.addItem("Female", "f");
		wGender.setHashMapping("gender");
		addEntryWidget("gender", wGender);
		flexTable.setWidget(pos, 1, wGender);
		pos++;

		final Label notesLabel = new Label("Notes");
		flexTable.setWidget(pos, 0, notesLabel);
		wNotes = new CustomTextArea();
		wNotes.setHashMapping("notes");
		addEntryWidget("notes", wNotes);
		flexTable.setWidget(pos, 1, wNotes);
		pos++;

		// Submit stuff at the bottom of the form

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final CustomButton wSubmit = new CustomButton("Submit",
				AppConstants.ICON_ADD);
		buttonBar.add(wSubmit);
		wSubmit.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				submitForm();
			}
		});
		final CustomButton wReset = new CustomButton("Reset",
				AppConstants.ICON_CLEAR);
		buttonBar.add(wReset);
		wReset.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
		Util.setFocus(wLastName1);
	}

	/**
	 * Add widget to list of HashMap'd data points represented by this form.
	 * 
	 * @param mapping
	 * @param widget
	 */
	public void addEntryWidget(String mapping, HashSetter widget) {
		setters.put(mapping, widget);
	}

	public String getModuleName() {
		return "ClinicRegistration";
	}

	public void resetForm() {
		wDateOfBirth.setValue((String) null);
		wLastName1.setValue("");
		wLastName2.setValue("");
		wFirstName.setValue("");
		wNotes.setValue("");
		wAge.setValue("");
		Util.setFocus(wLastName1);
	}

	public void submitForm() {
		ModuleInterfaceAsync service = getProxy();
		// Form hashmap ...
		final HashMap<String, String> rec = new HashMap<String, String>();
		/*
		Iterator<String> iter = setters.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			JsonUtil.debug("grabbing key " + k + " from setters");
			try {
				rec.put(k, setters.get(k).getStoredValue());
			} catch (Exception ex) {
				JsonUtil.debug("key " + k + ": " + ex.toString());
			}
		}
		*/

		if (wDateOfBirth.getStoredValue() != null) {
			rec.put("dob", wDateOfBirth.getStoredValue());
		}
		rec.put("lastname", wLastName1.getValue());
		rec.put("lastname2", wLastName2.getValue());
		rec.put("firstname", wFirstName.getValue());
		rec.put("notes", wNotes.getValue());
		if (wGender.getWidgetValue() != null) {
			rec.put("gender", wGender.getWidgetValue());
		}
		if (wAge.getValue() != null && wAge.getValue() != "") {
			rec.put("age", wAge.getValue());
		}

		// Debug
		JsonUtil.debug("ClinicRegistration.submitForm() called with : "
				+ JsonUtil.jsonify(rec));

		JsonUtil.debug("ClinicRegistration.submitForm() attempting add");
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			JsonUtil.debug("Try to build parameters");
			String[] params = { getModuleName(), JsonUtil.jsonify(rec) };
			JsonUtil.debug("Create requestbuilder for " + getModuleName()
					+ ", " + JsonUtil.jsonify(rec));
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleAddMethod",
											params)));
			JsonUtil.debug("Entering try statement");
			try {
				JsonUtil.debug("Sending request");
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg(getModuleName(), "Failed to add.");
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Integer r = (Integer) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Integer");
							if (r != null) {
								Util.showInfoMsg(getModuleName(), "Added.");
								resetForm();
							}
						} else {
							Util
									.showErrorMsg(getModuleName(),
											"Failed to add.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg(getModuleName(), "Failed to update.");
			}
		} else { // add clause GWT-RPC
			// Add
			service.ModuleAddMethod(getModuleName(), rec,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer result) {
							Util.showInfoMsg(getModuleName(), "Added.");
							resetForm();
						}

						public void onFailure(Throwable th) {
							Util
									.showErrorMsg(getModuleName(),
											"Failed to Add.");
						}
					});
		} // end add cause
	} // end submitForm

	@Override
	protected void buildForm() {
	}

	@Override
	public String validateData(HashMap<String, String> data) {
		return null;
	}

	/**
	 * Load the module interface RPC proxy.
	 * 
	 * @return
	 */
	public ModuleInterfaceAsync getProxy() {
		try {
			ModuleInterfaceAsync service = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
			return service;
		} catch (Exception e) {
			GWT.log("Exception: ", e);
			return (ModuleInterfaceAsync) null;
		}
	}

}
