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

package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientForm;

import com.bouwkamp.gwt.user.client.ui.RoundedPanel;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DisclosurePanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientInfoBar extends Composite {

	protected Label wPatientName;

	protected HTML editLink;

	protected HTML wPatientHiddenInfo;

	protected HTML wPatientVisibleInfo;

	protected Integer patientId = new Integer(0);

	protected Image photoId = null;

	protected ScreenInterface parentScreen;
	
	protected String provideName;

	protected DisclosurePanel wDropdown = null;

	public void setExpandPatientDetails() {
		if (!wDropdown.isOpen())
			wDropdown.setOpen(true);
	}
	
	public String getProviderName(){
		return provideName;
	}

	public PatientInfoBar() {
		final RoundedPanel container = new RoundedPanel();
		initWidget(container);
		container.setCornerColor("#ccccff");
		container.setStylePrimaryName("freemed-PatientInfoBar");
		container.setWidth("100%");

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		horizontalPanel.setWidth("100%");
		container.add(horizontalPanel);

		wPatientName = new Label("");
		wPatientVisibleInfo = new HTML();
		HorizontalPanel horizontalsubPanel = new HorizontalPanel();
		horizontalPanel.add(horizontalsubPanel);
		horizontalPanel.setCellWidth(horizontalsubPanel, "70%");
		// horizontalsubPanel .add(wPatientName);
		horizontalsubPanel.add(wPatientVisibleInfo);
		if (CurrentState.isActionAllowed(AppConstants.MODIFY,
				AppConstants.PATIENT_CATEGORY, AppConstants.NEW_PATIENT)) {
			editLink = new HTML(
					"(<a href=\"javascript:undefined;\" style='color:blue'>edit</a>)");
			editLink.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent event) {
					Util.closeTab(parentScreen);
					PatientForm patientForm = new PatientForm();
					Util.spawnTab(wPatientName.getText(), patientForm);
					patientForm.setPatientId(getPatientId());
				}
			});
			horizontalsubPanel.add(editLink);
		}
		wDropdown = new DisclosurePanel("");

		final HorizontalPanel wDropdownContainer = new HorizontalPanel();
		final VerticalPanel patientInfoContainer = new VerticalPanel();
		// wDropdown.add(wDropdownContainer);
		wPatientHiddenInfo = new HTML();
		patientInfoContainer.add(wPatientHiddenInfo);

		wDropdownContainer.add(patientInfoContainer);

		final VerticalPanel clinicalPhotoIdPanel = new VerticalPanel();
		photoId = new Image();
		photoId.setWidth("70px");
		photoId.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				final Popup p = new Popup();
				MugshotWebcamWidget m = new MugshotWebcamWidget(getPatientId());
				p.setWidget(m);
				p.center();
				m.setOnFinishedCommand(new Command() {
					@Override
					public void execute() {
						p.hide();
					}
				});
			}
		});
		clinicalPhotoIdPanel.add(photoId);

		wDropdownContainer.add(clinicalPhotoIdPanel);

		wDropdown.add(wDropdownContainer);

		// adding DisclosurePanel panel into a horizontal panel
		horizontalPanel.add(wDropdown);
		horizontalPanel.setCellHorizontalAlignment(wDropdown,
				HasHorizontalAlignment.ALIGN_CENTER);

		final HorizontalPanel iconBar = new HorizontalPanel();

		final Image wBookAppointment = new Image(
				"resources/images/book_appt.32x32.png");
		wBookAppointment.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {

			}
		});
		iconBar.add(wBookAppointment);

		horizontalPanel.add(iconBar);
		horizontalPanel.setCellHorizontalAlignment(iconBar,
				HasHorizontalAlignment.ALIGN_RIGHT);

		if (patientId > 0) {
			loadData();
		}
	}

	public Integer getPatientId() {
		return patientId;
	}

	/**
	 * Set patient information with HashMap returned from PatientInformation()
	 * method.
	 * 
	 * @param map
	 */
	public void setPatientFromMap(HashMap<String, String> map) {
		try {
			wPatientName.setText((String) map.get("patient_name"));
			wPatientVisibleInfo.setHTML((String) map.get("patient_name") + " "
					+ "[" + (String) map.get("date_of_birth") + "] "
					+ (String) map.get("ptid") + "<br/>");

			if (map.get("ptpcp") != "" && map.get("ptpcp") != "0")
				setPCPInfo(map.get("ptpcp"));
			if (map.get("ptprimaryfacility") != ""
					&& map.get("ptprimaryfacility") != "0")
				setFacilityInfo(map.get("ptprimaryfacility"));
			if (map.get("ptpharmacy") != "" && map.get("ptpharmacy") != "0")
				setPharmacyInfo(map.get("ptpharmacy"));

		} catch (Exception e) {
			e.printStackTrace();
		}
		try {
			wPatientHiddenInfo.setHTML("<small>"
					+ (String) map.get("address_line_1") + "<br/>"
					+ (String) map.get("address_line_2") + "<br/>"
					+ (String) map.get("csz") + "<br/>" + "H:"
					+ (String) map.get("pthphone") + "<br/>" + "W:"
					+ (String) map.get("ptwphone") + "</small>");
		} catch (Exception e) {
			e.printStackTrace();
		}
		try {
			patientId = new Integer((String) map.get("id"));
		} catch (Exception e) {
			JsonUtil.debug(e.toString());
		} finally {
			loadData();
		}
	}

	public void loadData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Don't populate
		} else {
			photoId
					.setUrl(Util
							.getJsonRequest(
									"org.freemedsoftware.module.PhotographicIdentification.GetPhotoID",
									new String[] { patientId.toString() }));
		}
	}

	public ScreenInterface getParentScreen() {
		return parentScreen;
	}

	public void setParentScreen(ScreenInterface parentScreen) {
		this.parentScreen = parentScreen;
	}

	public void setPCPInfo(String pcpId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
			wPatientVisibleInfo.setHTML(wPatientVisibleInfo.getHTML()
					+ "<b>PCP</b>: " + "Stubbed mode PCP");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { pcpId, "phyfname" };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.ProviderModule.to_text",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							wPatientVisibleInfo.setHTML(wPatientVisibleInfo
									.getHTML()
									+ "<br> <b>PCP</b>: "
									+ response.getText().replaceAll("\"", ""));
							provideName=response.getText().replaceAll("\"", "");
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.getMessage());
			}
		} else {
			// TODO normal mode code goes here
		}
	}

	public void setFacilityInfo(String facilityId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
			wPatientVisibleInfo.setHTML(wPatientVisibleInfo.getHTML()
					+ "<b>Facility</b>: " + "Stubbed mode facility");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { facilityId, "psrname" };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.FacilityModule.to_text",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							wPatientVisibleInfo.setHTML(wPatientVisibleInfo
									.getHTML()
									+ "<br> <b>Facility</b>: "
									+ response.getText().replaceAll("\"", ""));
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.getMessage());
			}
		} else {
			// TODO normal mode code goes here
		}
	}

	public void setPharmacyInfo(String pharmacyId) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
			wPatientVisibleInfo.setHTML(wPatientVisibleInfo.getHTML()
					+ "<b>Pharmacy</b>: " + "Stubbed mode facility");
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { pharmacyId, "phname" };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.Pharmacy.to_text",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							wPatientVisibleInfo.setHTML(wPatientVisibleInfo
									.getHTML()
									+ "<br> <b>Pharmacy</b>: "
									+ response.getText().replaceAll("\"", ""));
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.getMessage());
			}
		} else {
			// TODO normal mode code goes here
		}
	}
}
