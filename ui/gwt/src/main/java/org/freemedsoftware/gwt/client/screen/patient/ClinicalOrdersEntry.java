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

package org.freemedsoftware.gwt.client.screen.patient;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.io.Serializable;
import java.util.HashMap;
import java.util.Map;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;

public class ClinicalOrdersEntry extends PatientScreenInterface implements
		ClickHandler {

	public final static String moduleName = "Prescription";

	protected CustomButton cConsult, cRadiology, cImmunization, cLab,
			cProcedure, cRx, cTemplates, addOrderButton, removeOrderButton;

	protected FlexTable panelA, panelB, panelC, panelD;

	/**
	 * Second panel list of potential orders to select from.
	 */
	protected CustomListBox wPossibleOrders = new CustomListBox(true);
	protected Map<String, OrderItem> possibleOrders = new HashMap<String, OrderItem>();

	/**
	 * Third panel list of selected orders which are set to be added to patient
	 * regimen.
	 */
	protected CustomListBox wSelectedOrders = new CustomListBox(true);
	protected Map<String, OrderItem> selectedOrders = new HashMap<String, OrderItem>();

	protected boolean initialPosition = true;

	public enum OrderType {
		CONSULT, RADIOLOGY, IMMUNIZATION, LAB, PROCEDURE, RX, TEMPLATE
	}

	public class OrderItem implements Serializable {
		private static final long serialVersionUID = -273414904970876362L;

		private OrderType type;

		public void setType(OrderType type) {
			this.type = type;
		}

		public OrderType getType() {
			return type;
		}
	}

	public class OrderItemButton extends CustomButton {

	}

	public ClinicalOrdersEntry() {
		super(moduleName);
		final FlexTable flexTable = new FlexTable();
		initWidget(flexTable);

		// Layout, master container, two rows

		panelA = createCategoryPanel();
		flexTable.setWidget(0, 0, panelA);
		panelB = new FlexTable();
		flexTable.setWidget(0, 1, panelB);
		panelB.setWidget(0, 0, new HTML("<em>" + _("Select orders to add")
				+ "</em>"));
		wPossibleOrders.setVisibleItemCount(30);
		panelB.setWidget(1, 0, wPossibleOrders);
		wPossibleOrders.setEnabled(false);
		addOrderButton = new CustomButton("Add Order(s)", AppConstants.ICON_ADD);
		addOrderButton.addClickHandler(this);
		panelB.setWidget(2, 0, addOrderButton);
		addOrderButton.setEnabled(false);

		panelC = new FlexTable();
		wSelectedOrders.setVisibleItemCount(30);
		panelC.setWidget(0, 0, new HTML("<em>"
				+ _("Choose an order to modify or remove") + "</em>"));
		panelC.setWidget(1, 0, wSelectedOrders);
		wSelectedOrders.setEnabled(false);

		removeOrderButton = new CustomButton(_("Remove"), AppConstants.ICON_DELETE);
		removeOrderButton.addClickHandler(this);
		panelC.setWidget(2, 0, removeOrderButton);
		removeOrderButton.setEnabled(false);

		flexTable.setWidget(0, 2, panelC);
		panelD = new FlexTable();
		// Last panel is hidden, much as first two panels will be
		panelD.setVisible(false);
		flexTable.setWidget(0, 3, panelD);

		final FlexTable actionPanel = new FlexTable();
		flexTable.setWidget(1, 0, actionPanel);
		flexTable.getFlexCellFormatter().setColSpan(1, 0, 4);

		final CustomButton saveButton = new CustomButton(_("Add"),
				AppConstants.ICON_ADD);
		actionPanel.setWidget(0, 0, saveButton);
		saveButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				save();
			}
		});

		final CustomButton changePositionButton = new CustomButton("<-->",
				AppConstants.ICON_CHANGE);
		actionPanel.setWidget(0, 1, changePositionButton);
		changePositionButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				switchPosition();
			}
		});

		final CustomButton resetButton = new CustomButton(_("Reset"),
				AppConstants.ICON_CLEAR);
		actionPanel.setWidget(0, 2, resetButton);
		resetButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		// Util.setFocus(wProvider);
	}

	/**
	 * Panel "A" creation routine.
	 * 
	 * @return
	 */
	protected FlexTable createCategoryPanel() {
		FlexTable f = new FlexTable();
		f.setWidget(0, 0, new HTML("<em>" + _("Category") + "</em>"));

		int pos = 0;

		pos++;
		cConsult = new CustomButton(_("Consult"));
		cConsult.addClickHandler(this);
		f.setWidget(0, pos, cConsult);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);
		pos++;
		cRadiology = new CustomButton(_("Radiology"));
		cRadiology.addClickHandler(this);
		f.setWidget(0, pos, cRadiology);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);
		pos++;
		cLab = new CustomButton(_("Lab"));
		cLab.addClickHandler(this);
		f.setWidget(0, pos, cLab);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);
		pos++;
		cImmunization = new CustomButton(_("Immunization"));
		cImmunization.addClickHandler(this);
		f.setWidget(0, pos, cImmunization);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);
		pos++;
		cProcedure = new CustomButton(_("Procedure"));
		cProcedure.addClickHandler(this);
		f.setWidget(0, pos, cProcedure);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);
		pos++;
		cRx = new CustomButton(_("Prescription"));
		cRx.addClickHandler(this);
		f.setWidget(0, pos, cRx);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);
		pos++;
		cTemplates = new CustomButton(_("Templates"));
		cTemplates.addClickHandler(this);
		f.setWidget(0, pos, cTemplates);
		f.getFlexCellFormatter().setAlignment(0, pos,
				HasHorizontalAlignment.ALIGN_CENTER,
				HasVerticalAlignment.ALIGN_MIDDLE);

		return f;
	}

	/**
	 * Populate the second pane with possible candidates to add to the third
	 * pane.
	 * 
	 * @param type
	 */
	protected void populateOrderPickPanel(OrderType type) {
		// Make sure nothing prior exists there
		panelB.clear();

		// Retrieve list of items to display as arrays of strings:
		// [ id, name, set of ids for template ]

		// TODO: FIXME: this needs not to be like this
		String[][] items = { new String[] {}, new String[] {}, new String[] {},
				new String[] {}, new String[] {} };
		// Display in three columns, add click listener and custom type ???


		// Enable second panel picklist
		wPossibleOrders.setEnabled(true);
		addOrderButton.setEnabled(true);
	}

	public void save() {
		HashMap<String, String> data = new HashMap<String, String>();
		data.put("patient", Integer.toString(patientId));

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String[] params = { JsonUtil.jsonify(data) };

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.ClinicalOrders.add",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("ClinicalOrdersEntry",
								_("Failed to add order."));
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							if (response.getText().compareToIgnoreCase("false") != 0) {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(
												JSONParser.parseStrict(response
														.getText()),
												"HashMap<String,String>");
								if (r != null) { // Successful
									Util.showInfoMsg("ClinicalOrdersEntry",
											_("Successfully added order."));
								}
							} else {
								JsonUtil
										.debug("Received dummy response from JSON backend");
							}
						} else {
							Util.showErrorMsg("ClinicalOrdersEntry",
									_("Failed to add orders."));
						}
					}
				});
			} catch (RequestException e) {
				Util
						.showErrorMsg("ClinicalOrdersEntry",
								_("Failed to add orders."));
			}

		} else {
			// GWT-RPC
		}

	}

	public void resetForm() {
		// TODO
	}

	/**
	 * Switch viewing positions from [ A B C ] to [ C D ] panels.
	 */
	public void switchPosition() {
		if (initialPosition) {
			panelA.setVisible(false);
			panelB.setVisible(false);
			panelD.setVisible(true);
		} else {
			panelA.setVisible(true);
			panelB.setVisible(true);
			panelD.setVisible(false);
		}
		initialPosition = !initialPosition;
	}

	@Override
	public void onClick(ClickEvent event) {
		Object source = event.getSource();
		if (source == cConsult) {
			populateOrderPickPanel(OrderType.CONSULT);
		} else if (source == cRadiology) {
			populateOrderPickPanel(OrderType.RADIOLOGY);
		} else if (source == cImmunization) {
			populateOrderPickPanel(OrderType.IMMUNIZATION);
		} else if (source == cLab) {
			populateOrderPickPanel(OrderType.LAB);
		} else if (source == cProcedure) {
			populateOrderPickPanel(OrderType.PROCEDURE);
		} else if (source == cRx) {
			populateOrderPickPanel(OrderType.RX);
		} else if (source == cTemplates) {
			populateOrderPickPanel(OrderType.TEMPLATE);
		} else if (source == removeOrderButton) {
			// Check to see if an order is currently selected
			if (wSelectedOrders.getStoredValue() != ""
					&& wSelectedOrders.getStoredValue() != "0") {
				// Remove value
				selectedOrders.remove(wSelectedOrders.getStoredValue());
				wSelectedOrders.removeItem(wSelectedOrders.getSelectedIndex());
			}
		} else if (source instanceof OrderItemButton) {
			// Use button text or other instance to determine which item was
			// added, etc.
		} else {

		}
	}

}
