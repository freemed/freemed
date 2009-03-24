/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
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

import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomRichTextArea;
import org.freemedsoftware.gwt.client.widget.CustomTextBox;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class LetterEntry extends PatientScreenInterface {

	/**
	 * Internal id representing this record. If this is 0, we create a new one,
	 * otherwise we modify.
	 */
	protected Integer internalId = new Integer(0);

	protected CustomDatePicker wDate;

	protected SupportModuleWidget wFrom, wTo;

	protected CustomTextBox wSubject;

	protected CustomRichTextArea wText;

	protected String moduleName = "Letters";

	protected HashMap<String, HashSetter> setters = new HashMap<String, HashSetter>();

	public LetterEntry() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		final Label dateLabel = new Label("Date : ");
		flexTable.setWidget(0, 0, dateLabel);

		wDate = new CustomDatePicker();
		wDate.setHashMapping("letterdt");
		setters.put("letterdt", wDate);
		flexTable.setWidget(0, 1, wDate);

		final Label fromLabel = new Label("From : ");
		flexTable.setWidget(1, 0, fromLabel);

		wFrom = new SupportModuleWidget();
		wFrom.setModuleName("ProviderModule");
		wFrom.setHashMapping("letterfrom");
		setters.put("letterfrom", wFrom);
		flexTable.setWidget(1, 1, wFrom);

		final Label toLabel = new Label("To : ");
		flexTable.setWidget(2, 0, toLabel);

		wTo = new SupportModuleWidget();
		wTo.setModuleName("ProviderModule");
		wTo.setHashMapping("letterto");
		setters.put("letterto", wTo);
		flexTable.setWidget(2, 1, wTo);

		final Label subjectLabel = new Label("Subject : ");
		flexTable.setWidget(3, 0, subjectLabel);

		wSubject = new CustomTextBox();
		wSubject.setHashMapping("lettersubject");
		setters.put("lettersubject", wSubject);
		flexTable.setWidget(3, 1, wSubject);
		wSubject.setWidth("100%");

		final Label templateLabel = new Label("Template : ");
		flexTable.setWidget(4, 0, templateLabel);

		final Label messageLabel = new Label("Message : ");
		flexTable.setWidget(5, 0, messageLabel);

		wText = new CustomRichTextArea();
		wText.setHashMapping("lettertext");
		setters.put("lettertext", wText);
		flexTable.setWidget(5, 1, wText);

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final Button wSubmit = new Button("Submit");
		buttonBar.add(wSubmit);
		wSubmit.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				submitForm();
			}
		});
		final Button wReset = new Button("Reset");
		buttonBar.add(wReset);
		wReset.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
	}

	public void populateData(HashMap<String, String> r) {
		Iterator<HashSetter> iter = setters.values().iterator();
		while (iter.hasNext()) {
			iter.next().setFromHash(r);
		}
	}

	public String getModuleName() {
		return "Letters";
	}

	public void submitForm() {
		ModuleInterfaceAsync service = getProxy();
		// Form hashmap ...
		final HashMap<String, String> rec = new HashMap<String, String>();
		Iterator<String> iter = setters.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			rec.put(k, setters.get(k).getStoredValue());
		}

		if (!internalId.equals(new Integer(0))) {
			// Modify
			rec.put("id", (String) internalId.toString());
			if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { moduleName, JsonUtil.jsonify(rec) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.ModuleInterface.ModuleModifyMethod",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							Toaster t = state.getToaster();
							t.addItem("letters", "Failed to update letter.",
									Toaster.TOASTER_ERROR);
						}

						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != null) {
									Toaster t = state.getToaster();
									t.addItem("letters", "Updated letter.",
											Toaster.TOASTER_INFO);
								}
							} else {
								Toaster t = state.getToaster();
								t.addItem("letters",
										"Failed to update letter.",
										Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					Toaster t = state.getToaster();
					t.addItem("letters", "Failed to update letter.",
							Toaster.TOASTER_ERROR);
				}
			} else {
				if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { moduleName, JsonUtil.jsonify(rec) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.api.ModuleInterface.ModuleAddMethod",
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								Toaster t = state.getToaster();
								t.addItem("letters", "Failed to add letter.",
										Toaster.TOASTER_ERROR);
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"Integer");
									if (r != null) {
										Toaster t = state.getToaster();
										t.addItem("letters", "Added letter.",
												Toaster.TOASTER_INFO);
									}
								} else {
									Toaster t = state.getToaster();
									t.addItem("letters",
											"Failed to add letter.",
											Toaster.TOASTER_ERROR);
								}
							}
						});
					} catch (RequestException e) {
						Toaster t = state.getToaster();
						t.addItem("letters", "Failed to add letter.",
								Toaster.TOASTER_ERROR);
					}
				} else {
					service.ModuleModifyMethod(moduleName, rec,
							new AsyncCallback<Integer>() {
								public void onSuccess(Integer result) {
									Toaster t = state.getToaster();
									t.addItem("letters", "Updated letter.",
											Toaster.TOASTER_INFO);
								}

								public void onFailure(Throwable th) {
									Toaster t = state.getToaster();
									t.addItem("letters",
											"Failed to update letter.",
											Toaster.TOASTER_ERROR);
								}
							});
				}
			}
		} else {
			// Add
			service.ModuleAddMethod(moduleName, rec,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer result) {
							Toaster t = state.getToaster();
							t.addItem("letters", "Added letter.",
									Toaster.TOASTER_INFO);
						}

						public void onFailure(Throwable th) {
							Toaster t = state.getToaster();
							t.addItem("letters", "Failed to add letter.",
									Toaster.TOASTER_ERROR);
						}
					});
		}
	}

	public void resetForm() {

	}

}
