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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.SuperbillTemplateAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.core.client.GWT.UncaughtExceptionHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.VerticalPanel;

public class EncounterScreen extends PatientScreenInterface {

	protected final static int COLUMNS = 3;

	protected HashMap<String, String>[] dx = null;

	protected FlexTable dxTable = null;

	protected SupportModuleWidget dxPicklist = new SupportModuleWidget(
			"IcdCodes");

	protected HashMap<String, Boolean> dxValues = new HashMap<String, Boolean>();

	protected Integer dxCount = 0;

	protected HashMap<String, String>[] px = null;

	protected FlexTable pxTable = null;

	protected SupportModuleWidget pxPicklist = new SupportModuleWidget(
			"CptCodes");

	protected HashMap<String, Boolean> pxValues = new HashMap<String, Boolean>();

	protected Integer pxCount = 0;

	protected VerticalPanel popoutPanel = null;	
	
	public EncounterScreen() {
		GWT.setUncaughtExceptionHandler(new UncaughtExceptionHandler() {

			public void onUncaughtException(Throwable e) {
				JsonUtil.debug(e.getMessage());
			}

		});
		VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setSpacing(5);
		initWidget(verticalPanel);

		final HorizontalPanel superbillHPanel = new HorizontalPanel();
		verticalPanel.add(superbillHPanel);

		final Label superbillLabel = new Label(
				"Encounter/Superbill Template : ");
		superbillHPanel.add(superbillLabel);
		final SupportModuleWidget superbillTemplate = new SupportModuleWidget(
				"SuperbillTemplate");
		superbillTemplate.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				Integer value = event.getValue();
				if (value > 0) {
					populateSuperbillFromId(value);
					popoutPanel.setVisible(true);
				}
			}
		});
		superbillTemplate
				.setTitle("Select a template to use for constructing this encounter.");
		superbillHPanel.add(superbillTemplate);

		popoutPanel = new VerticalPanel();
		popoutPanel.setVisible(false);
		verticalPanel.add(popoutPanel);

		popoutPanel.add(new HTML("<b>" + "Diagnoses" + "</b>"));
		dxTable = new FlexTable();
		dxTable.setWidth("100%");
		popoutPanel.add(dxTable);
		dxPicklist.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				SupportModuleWidget w = (SupportModuleWidget) event.getSource();
				if (w.getValue() > 0) {
					// Push additional copy in
					final String value = w.getValue().toString();
					String label = w.getText();
					dxCount++;
					CheckBox cb = new CheckBox(label);
					cb.setValue(true);
					dxValues.put(value, true);
					cb.addClickHandler(new ClickHandler() {
						public void onClick(ClickEvent evt) {
							Boolean cur = dxValues.get(value);
							dxValues.put(value, !cur.booleanValue());
						}
					});
					dxTable.setWidget((dxCount == 0 ? 0 : dxCount / COLUMNS),
							(dxCount == 0 ? 0 : dxCount % COLUMNS), cb);

					// Reset the value when we're done
					((SupportModuleWidget) w).setValue(0);
				}
			}
		});
		popoutPanel.add(dxPicklist);
		popoutPanel.add(new HTML("<b>" + "Procedures" + "</b>"));
		pxTable = new FlexTable();
		pxTable.setWidth("100%");
		popoutPanel.add(pxTable);
		pxPicklist.addChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				SupportModuleWidget w = (SupportModuleWidget) event.getSource();
				if (((SupportModuleWidget) w).getValue() > 0) {
					// Push additional copy in
					final String value = (((SupportModuleWidget) w).getValue())
							.toString();
					String label = ((SupportModuleWidget) w).getText();
					pxCount++;
					CheckBox cb = new CheckBox(label);
					cb.setValue(true);
					pxValues.put(value, true);
					cb.addClickHandler(new ClickHandler() {
						@Override
						public void onClick(ClickEvent evt) {
							Boolean cur = pxValues.get(value);
							pxValues.put(value, !cur.booleanValue());
						}
					});
					pxTable.setWidget((pxCount == 0 ? 0 : pxCount / COLUMNS),
							(pxCount == 0 ? 0 : pxCount % COLUMNS), cb);

					// Reset the value when we're done
					((SupportModuleWidget) w).setValue(0);
				}
			}
		});
		popoutPanel.add(pxPicklist);

		// Submit button
		PushButton submitButton = new PushButton("Add Encounter");
		submitButton.setStylePrimaryName("freemed-PushButton");
		submitButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
			}
		});
		popoutPanel.add(submitButton);
		Util.setFocus(superbillTemplate);
	}

	/**
	 * Get composite's diagnosis values.
	 * 
	 * @return
	 */
	public String[] getDxValues() {
		List<String> r = new ArrayList<String>();
		Iterator<String> iter = dxValues.keySet().iterator();
		while (iter.hasNext()) {
			String v = iter.next();
			if (dxValues.get(v).booleanValue()) {
				r.add(v);
			}
		}
		return (String[]) r.toArray(new String[0]);
	}

	/**
	 * Get composite's procedure values.
	 * 
	 * @return
	 */
	public String[] getPxValues() {
		List<String> r = new ArrayList<String>();
		Iterator<String> iter = pxValues.keySet().iterator();
		while (iter.hasNext()) {
			String v = iter.next();
			if (pxValues.get(v).booleanValue()) {
				r.add(v);
			}
		}
		return (String[]) r.toArray(new String[0]);
	}

	/**
	 * Internal method to populate "superbill" form with dx and px elements from
	 * RPC.
	 * 
	 * @param data
	 */
	protected void populateSuperbill(
			HashMap<String, HashMap<String, String>[]> data) {
		dx = data.get("dx");
		px = data.get("px");

		// Populate diagnoses
		dxTable.clear();
		dxValues.clear();
		if (dx.length > 0) {
			for (int iter = 0; iter < dx.length; iter++) {
				int row = (int) ((iter == 0) ? 0 : iter / COLUMNS);
				int col = (iter == 0) ? 0 : iter % COLUMNS;
				final String value = dx[iter].get("id");
				CheckBox w = new CheckBox();
				String label = dx[iter].get("code") + " ("
						+ dx[iter].get("descrip") + ")";
				// Bold the title if it's a previous entry
				if (Integer.parseInt(dx[iter].get("previous")) == 1) {
					w.setHTML("<b>" + label + "</b>");
					dxValues.put(value, Boolean.TRUE);
				} else {
					w.setText(label);
					dxValues.put(value, Boolean.FALSE);
				}
				w.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent evt) {
						Boolean cur = dxValues.get(value);
						dxValues.put(value, !cur.booleanValue());
					}
				});
				dxTable.setWidget(row, col, w);
			}
			dxCount = dx.length;
		}

		// Populate procedures
		pxTable.clear();
		pxValues.clear();
		if (px.length > 0) {
			for (int iter = 0; iter < px.length; iter++) {
				int row = (int) ((iter == 0) ? 0 : iter / COLUMNS);
				int col = (iter == 0) ? 0 : iter % COLUMNS;
				final String value = px[iter].get("id");
				CheckBox w = new CheckBox();
				String label = px[iter].get("code") + " ("
						+ px[iter].get("descrip") + ")";

				// Bold the title if it's a previous entry
				int previous = 0;
				try {
					previous = Integer.parseInt(px[iter].get("previous"));
				} catch (Exception ex) {
				}
				if (previous == 1) {
					w.setHTML("<b>" + label + "</b>");
					pxValues.put(value, Boolean.TRUE);
				} else {
					w.setText(label);
					pxValues.put(value, Boolean.FALSE);
				}
				w.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent evt) {
						Boolean cur = pxValues.get(value);
						pxValues.put(value, !cur.booleanValue());
					}
				});
				pxTable.setWidget(row, col, w);
			}
			pxCount = px.length;
		}
	}

	/**
	 * Callback for encounter/superbill template selection widget.
	 * 
	 * @param superbillId
	 */
	protected void populateSuperbillFromId(Integer superbillId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// FIXME: make this work under stubbed mode.
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(superbillId),
					JsonUtil.jsonify(patientId) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.SuperbillTemplate.GetTemplate",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						GWT.log("Exception", ex);
						CurrentState.getToaster().addItem("SuperbillTemplate",
								"Failed to load template.",
								Toaster.TOASTER_ERROR);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, HashMap<String, String>[]> result = (HashMap<String, HashMap<String, String>[]>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,HashMap<String,String>[]>");
							JsonUtil.debug("got result");
							if (result != null) {
								JsonUtil.debug("calling populate superbill");
								populateSuperbill(result);
							}
						} else {
							CurrentState.getToaster().addItem(
									"SuperbillTemplate",
									"Failed to load template.",
									Toaster.TOASTER_ERROR);
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
				CurrentState.getToaster().addItem("SuperbillTemplate",
						"Failed to load template.", Toaster.TOASTER_ERROR);
			}
		} else {
			try {
				SuperbillTemplateAsync proxy = (SuperbillTemplateAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.SuperbillTemplate");
				proxy
						.GetTemplate(
								superbillId,
								patientId,
								new AsyncCallback<HashMap<String, HashMap<String, String>[]>>() {

									public void onSuccess(
											HashMap<String, HashMap<String, String>[]> result) {
										populateSuperbill(result);
									}

									public void onFailure(Throwable caught) {
										CurrentState.getToaster().addItem(
												"SuperbillTemplate",
												"Failed to load template.",
												Toaster.TOASTER_ERROR);
									}

								});
			} catch (Exception e) {
				e.printStackTrace();
				CurrentState.getToaster().addItem("SuperbillTemplate",
						"Failed to load template.", Toaster.TOASTER_ERROR);
			}

		}
	}
}
