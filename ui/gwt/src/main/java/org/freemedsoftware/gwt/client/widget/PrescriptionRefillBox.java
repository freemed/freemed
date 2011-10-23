/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.RxRefillScreen;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.dom.client.Style.Cursor;
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
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class PrescriptionRefillBox extends WidgetInterface {

	public final static String moduleName = RxRefillScreen.moduleName;

	protected Integer patid = 0;

	protected CustomTable wRequests = null;
	protected FlexTable flexTable = new FlexTable();

	private PushButton rxRefillPrescriptionButton;

	protected final VerticalPanel contentVPanel;
	
	public PrescriptionRefillBox() {
		super(moduleName);
		VerticalPanel superVPanel = new VerticalPanel();
		initWidget(superVPanel);
		superVPanel.setStyleName(AppConstants.STYLE_BUTTON_WIDGETS_CONTAINER );
		superVPanel.setWidth("100%");

		
		HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setSpacing(5);
		superVPanel.add(headerHPanel);
		
		final Image colExpBtn = new Image(Util.getResourcesURL()+"collapse.15x15.png");
		colExpBtn.getElement().getStyle().setCursor(Cursor.POINTER);
		headerHPanel.add(colExpBtn);
		colExpBtn.addClickHandler(new ClickHandler() {
			boolean expaned = false;
			@Override
			public void onClick(ClickEvent arg0) {
				if(expaned){
					colExpBtn.setUrl(Util.getResourcesURL()+"collapse.15x15.png");
					contentVPanel.setVisible(true);
				}else{
					colExpBtn.setUrl(Util.getResourcesURL()+"expand.15x15.png");
					contentVPanel.setVisible(false);
				}
					expaned = !expaned;
			}
		});

		Label headerLabel = new Label("RX REFILLS");
		headerHPanel.add(headerLabel);
		headerLabel.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

		contentVPanel = new VerticalPanel();
		contentVPanel.setWidth("100%");
		superVPanel.add(contentVPanel);
		
		contentVPanel.add(flexTable);
		flexTable.setSize("100%", "100%");

		cleanView();
	}

	public Widget getDefaultIcon() {
		if (rxRefillPrescriptionButton == null) {
			rxRefillPrescriptionButton = new PushButton("", "");
			rxRefillPrescriptionButton
					.setStyleName(AppConstants.STYLE_BUTTON_SIMPLE);
			rxRefillPrescriptionButton.getUpFace().setImage(
					new Image("resources/images/rx_prescriptions.16x16.png"));
			rxRefillPrescriptionButton.getDownFace().setImage(
					new Image("resources/images/rx_prescriptions.16x16.png"));
			rxRefillPrescriptionButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					cleanView();
				}
			});
		}
		return rxRefillPrescriptionButton;
	}

	public void showDoctor() {
		cleanView();
		wRequests = new CustomTable();
		wRequests.setMaximumRows(10);
		wRequests.setAllowSelection(false);
		wRequests.setSize("100%", "100%");
		wRequests.addColumn(_("Date"), "stamp"); // col 0
		wRequests.addColumn(_("User"), "user"); // col 1
		wRequests.addColumn(_("Patient"), "patient"); // col 2
		wRequests.addColumn(_("RX Orig"), "rxorig"); // col 3
		wRequests.addColumn(_("Note"), "note"); // col 4
		wRequests.addColumn(_("Approved"), "approved");// col 5
		wRequests.addColumn(_("Locked"), "locked"); // col 6
		wRequests.setMaximumRows(7);
		wRequests.setIndexName("id");

		wRequests.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				final Integer id = Integer.parseInt(data.get("id"));
				JsonUtil.debug(Integer.toString(id));
			}
		});

		retrieveData();
		flexTable.setWidget(1, 0, wRequests);
		flexTable.getFlexCellFormatter().setColSpan(1, 0, 2);
	}

	public void showStaff() {

		cleanView();

		flexTable.getFlexCellFormatter().setColSpan(1, 0, 1);

		final PatientWidget patientWidget = new PatientWidget();
		flexTable.setWidget(1, 1, patientWidget);

		final Label selectionLabel = new Label(_("Select a Patient") + ":");
		flexTable.setWidget(1, 0, selectionLabel);

		final Label textLabel = new Label(_("Add an optional note") + ":");
		flexTable.setWidget(2, 0, textLabel);

		final TextBox textBox = new TextBox();
		flexTable.setWidget(2, 1, textBox);
		textBox.setWidth("100%");

		final CustomButton sendButton = new CustomButton(_("Send Request"),
				AppConstants.ICON_SEND);
		flexTable.setWidget(3, 1, sendButton);

		sendButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				String msg = "";
				if(Integer.parseInt(patientWidget.getStoredValue())==0)
					msg = _("Please select a patient.");
				if(textBox.getText().trim().length()==0)
					msg += "\n" + _("Please add a short note.");
				
				if(msg.length()>0){
					Util.showErrorMsg(moduleName, msg);
					return;
				}
					
				patid = patientWidget.value;

				HashMap<String, String> data = new HashMap<String, String>();
				// data.put("id", "some stuff"); not needed??
				data.put("provider", Integer.toString(CurrentState
						.getDefaultProvider()));
				data.put("note", textBox.getText());
				data.put("patient", Integer.toString(patid));
				// send stuff
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// do nothing - we just save the stuff
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					String[] params = { JsonUtil.jsonify(data) };
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST,
							URL
									.encode(Util
											.getJsonRequest(
													"org.freemedsoftware.module.RxRefillRequest.add",
													params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								Util.showErrorMsg("PrescriptionRefillBox",
										_("Error adding refill request.")
												+ ex.toString());
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (200 == response.getStatusCode()) {
									Integer r = (Integer) JsonUtil
											.shoehornJson(JSONParser
													.parseStrict(response.getText()),
													"Integer");
									if (r != 0) {
										Util
												.showInfoMsg(
														"PrescriptionRefillBox",
														_("Refill request successfully saved."));
									}
								} else {
									Util.showErrorMsg("PrescriptionRefillBox",
											_("Error adding prescription refill request."));
								}
							}
						});
					} catch (RequestException e) {
					}

				} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
					// TODO: GWT-RPC still missing
				}

			}

		});
	}

	public void cleanView() {

		flexTable.clear();
		/*
		 * final Label selectViewLabel = new Label("Select View");
		 * flexTable.setWidget(0, 0, selectViewLabel);
		 * 
		 * final ListBox selectUser = new ListBox(); flexTable.setWidget(0, 1,
		 * selectUser); selectUser.addItem("Select access level");
		 * selectUser.addItem("doctor"); // if(canWrite)
		 * selectUser.addItem("staff"); selectUser.setVisibleItemCount(1);
		 * 
		 * selectUser.addChangeHandler(new ChangeHandler() {
		 * 
		 * @Override public void onChange(ChangeEvent evt) { if
		 * (selectUser.getSelectedIndex() == 1) { showDoctor(); } else if
		 * (selectUser.getSelectedIndex() == 2) { showStaff(); } } });
		 */
	}

	public void retrieveData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Runs in STUBBED MODE => Feed with Sample Data
			// HashMap<String, String>[] sampleData = getSampleData();
			// loadData(sampleData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			wRequests.showloading(true);
			// Use JSON-RPC to retrieve the data
			String[] requestparams = {};

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.RxRefillRequest.GetAll",
											requestparams)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						JsonUtil.debug(request.toString());
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String, String>[] data = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parseStrict(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null) {
								loadData(data);
							} else
								wRequests.showloading(false);
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}

	}

	protected void loadData(HashMap<String, String>[] data) {
		wRequests.loadData(data);
	}

}
