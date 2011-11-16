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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonContainer;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonSingle;
import org.freemedsoftware.gwt.client.widget.CustomTextBox;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class VitalsEntry extends PatientEntryScreenInterface implements
		ClickHandler {

	protected CustomRadioButtonContainer temperatureContainer;
	protected CustomRadioButtonSingle temperatureUnavailable,
			temperatureRefused, temperatureRecorded;
	protected CustomTextBox temperatureValue;
	protected CustomListBox temperatureUnits, temperatureQualifier;

	protected CustomRadioButtonContainer pulseContainer;
	protected CustomRadioButtonSingle pulseUnavailable, pulseRefused,
			pulseRecorded;
	protected CustomTextBox pulseValue;
	protected CustomListBox pulseLocation, pulseMethod, pulseSite;

	protected CustomRadioButtonContainer pulseOxContainer;
	protected CustomRadioButtonSingle pulseOxUnavailable, pulseOxRefused,
			pulseOxRecorded;
	protected CustomTextBox pulseOxFlowRate, pulseOxO2Conc;
	protected CustomListBox pulseOxMethod;

	protected CustomRadioButtonContainer glucoseContainer;
	protected CustomRadioButtonSingle glucoseUnavailable, glucoseRefused,
			glucoseRecorded;
	protected CustomTextBox glucoseValue;
	protected CustomListBox glucoseUnits, glucoseQualifier;

	protected CustomRadioButtonContainer respirationContainer;
	protected CustomRadioButtonSingle respirationUnavailable,
			respirationRefused, respirationRecorded;
	protected CustomTextBox respirationValue;
	protected CustomListBox respirationMethod, respirationPosition;

	protected CustomRadioButtonContainer bpContainer;
	protected CustomRadioButtonSingle bpUnavailable, bpRefused, bpRecorded;
	protected CustomTextBox bpSValue, bpDValue;
	protected CustomListBox bpLocation, bpMethod, bpPosition;

	protected CustomRadioButtonContainer cvpContainer;
	protected CustomRadioButtonSingle cvpUnavailable, cvpRefused, cvpRecorded;
	protected CustomTextBox cvpValue;
	protected CustomListBox cvpPor;

	protected CustomRadioButtonContainer cgContainer;
	protected CustomRadioButtonSingle cgUnavailable, cgRefused, cgRecorded;
	protected CustomTextBox cgValue;
	protected CustomListBox cgUnits, cgLocation, cgSite;

	protected CustomRadioButtonContainer hContainer;
	protected CustomRadioButtonSingle hUnavailable, hRefused, hRecorded;
	protected CustomTextBox hValue;
	protected CustomListBox hUnits, hQuality;

	protected CustomRadioButtonContainer wContainer;
	protected CustomRadioButtonSingle wUnavailable, wRefused, wRecorded;
	protected CustomTextBox wValue;
	protected CustomListBox wUnits, wMethod, wQuality;

	protected CustomRadioButtonContainer painContainer;
	protected CustomRadioButtonSingle painUnavailable, painRefused,
			painRecorded;
	protected CustomTextBox painValue;
	protected CustomListBox painScale;

	public VitalsEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		int pos = 0;

		// Header row
		flexTable.setWidget(pos, 0, new Label(_("Unavailable")));
		flexTable.setWidget(pos, 1, new Label(_("Refused")));
		flexTable.setWidget(pos, 2, new Label(_("Record")));
		pos++;

		{
			flexTable.setWidget(pos, 3, new Label(_("Temperature")));
			pos++;

			temperatureContainer = new CustomRadioButtonContainer();
			temperatureContainer.setHashMapping("v_temp_status");
			addEntryWidget("v_temp_status", temperatureContainer);
			temperatureUnavailable = new CustomRadioButtonSingle(
					"v_temp_status", "unavailable", "");
			temperatureUnavailable.addClickHandler(this);
			temperatureContainer.addItem("unavailable", temperatureUnavailable);
			flexTable.setWidget(pos, 0, temperatureUnavailable);
			temperatureRefused = new CustomRadioButtonSingle("v_temp_status",
					"refused", "");
			temperatureRefused.addClickHandler(this);
			temperatureContainer.addItem("refused", temperatureRefused);
			flexTable.setWidget(pos, 1, temperatureRefused);
			temperatureRecorded = new CustomRadioButtonSingle("v_temp_status",
					"recorded", "");
			temperatureRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, temperatureRecorded);
			temperatureContainer.addItem("recorded", temperatureRecorded);

			temperatureContainer.setValue("unavailable");

			temperatureValue = new CustomTextBox();
			temperatureValue.setHashMapping("v_temp_value");
			temperatureValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_temp_value", temperatureValue);
			flexTable.setWidget(pos, 3, temperatureValue);

			temperatureUnits = new CustomListBox();
			if (CurrentState.getSystemConfig("metric_system").equals("Standard")) {
				temperatureUnits.addItem("F");
				temperatureUnits.addItem("C");
			} else {
				temperatureUnits.addItem("C");
				temperatureUnits.addItem("F");
			}
			temperatureUnits.setHashMapping("v_temp_units");
			temperatureUnits.setEnabled(Boolean.FALSE);
			addEntryWidget("v_temp_units", temperatureUnits);
			flexTable.setWidget(pos, 4, temperatureUnits);

			temperatureQualifier = new CustomListBox();
			temperatureQualifier.addItem("NONE");
			temperatureQualifier.addItem("AUXILLARY");
			temperatureQualifier.addItem("CORE");
			temperatureQualifier.addItem("ORAL");
			temperatureQualifier.addItem("RECTAL");
			temperatureQualifier.addItem("SKIN");
			temperatureQualifier.addItem("TYMPANIC");
			temperatureQualifier.setHashMapping("v_temp_qualifier");
			temperatureQualifier.setEnabled(Boolean.FALSE);
			addEntryWidget("v_temp_qualifier", temperatureQualifier);
			flexTable.setWidget(pos, 5, temperatureQualifier);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Pulse")));
			pos++;

			pulseContainer = new CustomRadioButtonContainer();
			pulseContainer.setHashMapping("v_pulse_status");
			addEntryWidget("v_pulse_status", pulseContainer);
			pulseUnavailable = new CustomRadioButtonSingle("v_pulse_status",
					"unavailable", "");
			pulseUnavailable.addClickHandler(this);
			pulseContainer.addItem("unavailable", pulseUnavailable);
			flexTable.setWidget(pos, 0, pulseUnavailable);
			pulseRefused = new CustomRadioButtonSingle("v_pulse_status",
					"refused", "");
			pulseRefused.addClickHandler(this);
			pulseContainer.addItem("refused", pulseRefused);
			flexTable.setWidget(pos, 1, pulseRefused);
			pulseRecorded = new CustomRadioButtonSingle("v_pulse_status",
					"recorded", "");
			pulseRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, pulseRecorded);
			pulseContainer.addItem("recorded", pulseRecorded);

			pulseContainer.setValue("unavailable");

			pulseValue = new CustomTextBox();
			pulseValue.setHashMapping("v_pulse_value");
			pulseValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulse_value", pulseValue);
			flexTable.setWidget(pos, 3, pulseValue);

			pulseLocation = new CustomListBox();
			pulseLocation.addItem("F");
			pulseLocation.addItem("C");
			pulseLocation.addItem("NONE");
			pulseLocation.addItem("APICAL");
			pulseLocation.addItem("BILATERAL PERIPHERALS");
			pulseLocation.addItem("BRACHIAL");
			pulseLocation.addItem("CAROTID");
			pulseLocation.addItem("DORSALIS PEDIS");
			pulseLocation.addItem("FEMORAL");
			pulseLocation.addItem("OTHER");
			pulseLocation.addItem("PERIPHERAL");
			pulseLocation.addItem("POPLITEAL");
			pulseLocation.addItem("POSTERIOR TIBIAL");
			pulseLocation.addItem("RADIAL");
			pulseLocation.addItem("ULNAR");
			pulseLocation.setHashMapping("v_pulse_location");
			pulseLocation.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulse_location", pulseLocation);
			flexTable.setWidget(pos, 4, pulseLocation);

			pulseMethod = new CustomListBox();
			pulseMethod.addItem("PALPATED");
			pulseMethod.addItem("NONE");
			pulseMethod.addItem("ASCULTATE");
			pulseMethod.addItem("DOPPLER");
			pulseMethod.setHashMapping("v_pulse_method");
			pulseMethod.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulse_method", pulseMethod);
			flexTable.setWidget(pos, 5, pulseMethod);

			pulseSite = new CustomListBox();
			pulseSite.addItem("NONE");
			pulseSite.addItem("LEFT");
			pulseSite.addItem("RIGHT");
			pulseSite.setHashMapping("v_pulse_site");
			pulseSite.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulse_site", pulseSite);
			flexTable.setWidget(pos, 6, pulseSite);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Pulse Ox")));
			pos++;

			pulseOxContainer = new CustomRadioButtonContainer();
			pulseOxContainer.setHashMapping("v_pulse_status");
			addEntryWidget("v_pulseox_status", pulseOxContainer);
			pulseOxUnavailable = new CustomRadioButtonSingle(
					"v_pulseox_status", "unavailable", "");
			pulseOxUnavailable.addClickHandler(this);
			pulseOxContainer.addItem("unavailable", pulseOxUnavailable);
			flexTable.setWidget(pos, 0, pulseOxUnavailable);
			pulseOxRefused = new CustomRadioButtonSingle("v_pulseox_status",
					"refused", "");
			pulseOxRefused.addClickHandler(this);
			pulseOxContainer.addItem("refused", pulseOxRefused);
			flexTable.setWidget(pos, 1, pulseOxRefused);
			pulseOxRecorded = new CustomRadioButtonSingle("v_pulseox_status",
					"recorded", "");
			pulseOxRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, pulseOxRecorded);
			pulseOxContainer.addItem("recorded", pulseOxRecorded);

			pulseOxContainer.setValue("unavailable");

			pulseOxFlowRate = new CustomTextBox();
			pulseOxFlowRate.setHashMapping("v_pulseox_flowrate");
			pulseOxFlowRate.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulseox_flowrate", pulseOxFlowRate);
			flexTable.setWidget(pos, 3, pulseOxFlowRate);

			pulseOxO2Conc = new CustomTextBox();
			pulseOxO2Conc.setHashMapping("v_pulseox_o2conc");
			pulseOxO2Conc.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulseox_o2conc", pulseOxO2Conc);
			flexTable.setWidget(pos, 4, pulseOxO2Conc);

			pulseOxMethod = new CustomListBox();
			pulseOxMethod.addItem("REAL");
			pulseOxMethod.addItem("AEROSOL/HUMIDIFIED MASK");
			pulseOxMethod.addItem("FACE TENT");
			pulseOxMethod.addItem("MASK");
			pulseOxMethod.addItem("NASAL CANNULA");
			pulseOxMethod.addItem("NON RE-BREATHER");
			pulseOxMethod.addItem("PARTIAL RE-BREATHER");
			pulseOxMethod.addItem("T-PIECE");
			pulseOxMethod.addItem("TRACHEOSTOMY COLLAR");
			pulseOxMethod.addItem("VENTILATOR");
			pulseOxMethod.addItem("VENTURI MASK");
			pulseOxMethod.setHashMapping("v_pulseox_method");
			pulseOxMethod.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pulseox_method", pulseOxMethod);
			flexTable.setWidget(pos, 5, pulseOxMethod);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Blood Glucose")));
			pos++;

			glucoseContainer = new CustomRadioButtonContainer();
			glucoseContainer.setHashMapping("v_glucose_status");
			addEntryWidget("v_glucose_status", glucoseContainer);
			glucoseUnavailable = new CustomRadioButtonSingle(
					"v_glucose_status", "unavailable", "");
			glucoseUnavailable.addClickHandler(this);
			glucoseContainer.addItem("unavailable", glucoseUnavailable);
			flexTable.setWidget(pos, 0, glucoseUnavailable);
			glucoseRefused = new CustomRadioButtonSingle("v_glucose_status",
					"refused", "");
			glucoseRefused.addClickHandler(this);
			glucoseContainer.addItem("refused", glucoseRefused);
			flexTable.setWidget(pos, 1, glucoseRefused);
			glucoseRecorded = new CustomRadioButtonSingle("v_glucose_status",
					"recorded", "");
			glucoseRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, glucoseRecorded);
			glucoseContainer.addItem("recorded", glucoseRecorded);

			glucoseContainer.setValue("unavailable");

			glucoseValue = new CustomTextBox();
			glucoseValue.setHashMapping("v_glucose_value");
			glucoseValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_glucose_value", glucoseValue);
			flexTable.setWidget(pos, 3, glucoseValue);

			glucoseUnits = new CustomListBox();
			glucoseUnits.addItem("MG/DL");
			glucoseUnits.setHashMapping("v_glucose_units");
			glucoseUnits.setEnabled(Boolean.FALSE);
			addEntryWidget("v_glucose_units", glucoseUnits);
			flexTable.setWidget(pos, 4, glucoseUnits);

			glucoseQualifier = new CustomListBox();
			glucoseQualifier.addItem("NONE");
			glucoseQualifier.addItem("FINGER STICK");
			glucoseQualifier.addItem("WHOLE BLOOD");
			glucoseQualifier.addItem("TRANSCUTANEOUS");
			glucoseQualifier.setHashMapping("v_glucose_qualifier");
			glucoseQualifier.setEnabled(Boolean.FALSE);
			addEntryWidget("v_glucose_qualifier", glucoseQualifier);
			flexTable.setWidget(pos, 5, glucoseQualifier);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Respiration")));
			pos++;

			respirationContainer = new CustomRadioButtonContainer();
			respirationContainer.setHashMapping("v_resp_status");
			addEntryWidget("v_resp_status", respirationContainer);
			respirationUnavailable = new CustomRadioButtonSingle(
					"v_resp_status", "unavailable", "");
			respirationUnavailable.addClickHandler(this);
			respirationContainer.addItem("unavailable", respirationUnavailable);
			flexTable.setWidget(pos, 0, respirationUnavailable);
			respirationRefused = new CustomRadioButtonSingle("v_resp_status",
					"refused", "");
			respirationRefused.addClickHandler(this);
			respirationContainer.addItem("refused", respirationRefused);
			flexTable.setWidget(pos, 1, respirationRefused);
			respirationRecorded = new CustomRadioButtonSingle("v_resp_status",
					"recorded", "");
			respirationRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, respirationRecorded);
			respirationContainer.addItem("recorded", respirationRecorded);

			respirationContainer.setValue("unavailable");

			respirationValue = new CustomTextBox();
			respirationValue.setHashMapping("v_resp_value");
			respirationValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_resp_value", respirationValue);
			flexTable.setWidget(pos, 3, respirationValue);

			respirationMethod = new CustomListBox();
			respirationMethod.addItem("NONE");
			respirationMethod.addItem("ASSISTED VENTILATOR");
			respirationMethod.addItem("CONTROLLED VENTILATOR");
			respirationMethod.addItem("SPONTANEOUS");
			respirationMethod.setHashMapping("v_resp_method");
			respirationMethod.setEnabled(Boolean.FALSE);
			addEntryWidget("v_resp_method", respirationMethod);
			flexTable.setWidget(pos, 4, respirationMethod);

			respirationPosition = new CustomListBox();
			respirationPosition.addItem("NONE");
			respirationPosition.addItem("LYING");
			respirationPosition.addItem("SITTING");
			respirationPosition.addItem("STANDING");
			respirationPosition.setHashMapping("v_resp_position");
			respirationPosition.setEnabled(Boolean.FALSE);
			addEntryWidget("v_resp_position", respirationPosition);
			flexTable.setWidget(pos, 5, respirationPosition);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Blood Pressure")));
			pos++;

			bpContainer = new CustomRadioButtonContainer();
			bpContainer.setHashMapping("v_bp_status");
			addEntryWidget("v_bp_status", bpContainer);
			bpUnavailable = new CustomRadioButtonSingle("v_bp_status",
					"unavailable", "");
			bpUnavailable.addClickHandler(this);
			bpContainer.addItem("unavailable", bpUnavailable);
			flexTable.setWidget(pos, 0, bpUnavailable);
			bpRefused = new CustomRadioButtonSingle("v_bp_status", "refused",
					"");
			bpRefused.addClickHandler(this);
			bpContainer.addItem("refused", bpRefused);
			flexTable.setWidget(pos, 1, bpRefused);
			bpRecorded = new CustomRadioButtonSingle("v_bp_status", "recorded",
					"");
			bpRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, bpRecorded);
			bpContainer.addItem("recorded", bpRecorded);

			bpContainer.setValue("unavailable");

			bpSValue = new CustomTextBox();
			bpSValue.setHashMapping("v_bp_s_value");
			bpSValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_bp_s_value", bpSValue);
			bpDValue = new CustomTextBox();
			bpDValue.setHashMapping("v_bp_d_value");
			bpDValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_bp_d_value", bpDValue);
			HorizontalPanel bpEntryPanel = new HorizontalPanel();
			bpEntryPanel.add(bpSValue);
			bpEntryPanel.add(new Label("/"));
			bpEntryPanel.add(bpDValue);
			flexTable.setWidget(pos, 3, bpEntryPanel);

			bpLocation = new CustomListBox();
			bpLocation.addItem("NONE");
			bpLocation.addItem("L ARM");
			bpLocation.addItem("L LEG");
			bpLocation.addItem("R ARM");
			bpLocation.addItem("R LEG");
			bpLocation.setHashMapping("v_bp_location");
			bpLocation.setEnabled(Boolean.FALSE);
			addEntryWidget("v_bp_location", bpLocation);
			flexTable.setWidget(pos, 4, bpLocation);

			bpMethod = new CustomListBox();
			bpMethod.addItem("NONE");
			bpMethod.addItem("CUFF");
			bpMethod.addItem("DOPPLER");
			bpMethod.addItem("NON-INVASIVE");
			bpMethod.addItem("PALPATED");
			bpMethod.setHashMapping("v_bp_method");
			bpMethod.setEnabled(Boolean.FALSE);
			addEntryWidget("v_bp_method", bpMethod);
			flexTable.setWidget(pos, 5, bpMethod);

			bpPosition = new CustomListBox();
			bpPosition.addItem("NONE");
			bpPosition.addItem("LYING");
			bpPosition.addItem("SITTING");
			bpPosition.addItem("STANDING");
			bpPosition.setHashMapping("v_bp_position");
			bpPosition.setEnabled(Boolean.FALSE);
			addEntryWidget("v_bp_position", bpPosition);
			flexTable.setWidget(pos, 6, bpPosition);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label("CVP"));
			pos++;

			cvpContainer = new CustomRadioButtonContainer();
			cvpContainer.setHashMapping("v_cvp_status");
			addEntryWidget("v_cvp_status", cvpContainer);
			cvpUnavailable = new CustomRadioButtonSingle("v_cvp_status",
					"unavailable", "");
			cvpUnavailable.addClickHandler(this);
			cvpContainer.addItem("unavailable", cvpUnavailable);
			flexTable.setWidget(pos, 0, cvpUnavailable);
			cvpRefused = new CustomRadioButtonSingle("v_cvp_status", "refused",
					"");
			cvpRefused.addClickHandler(this);
			cvpContainer.addItem("refused", cvpRefused);
			flexTable.setWidget(pos, 1, cvpRefused);
			cvpRecorded = new CustomRadioButtonSingle("v_cvp_status",
					"recorded", "");
			cvpRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, cvpRecorded);
			cvpContainer.addItem("recorded", cvpRecorded);

			cvpContainer.setValue("unavailable");

			cvpValue = new CustomTextBox();
			cvpValue.setHashMapping("v_cvp_value");
			cvpValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_cvp_value", cvpValue);
			flexTable.setWidget(pos, 3, cvpValue);

			cvpPor = new CustomListBox();
			cvpPor.addItem("NONE");
			cvpPor.addItem("STERNUM");
			cvpPor.addItem("MIDAXILLARY LINE");
			cvpPor.setHashMapping("v_cvp_por");
			cvpPor.setEnabled(Boolean.FALSE);
			addEntryWidget("v_cvp_por", cvpPor);
			flexTable.setWidget(pos, 4, cvpPor);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label("C/G"));
			pos++;

			cgContainer = new CustomRadioButtonContainer();
			cgContainer.setHashMapping("v_cg_status");
			addEntryWidget("v_cg_status", cgContainer);
			cgUnavailable = new CustomRadioButtonSingle("v_cg_status",
					"unavailable", "");
			cgUnavailable.addClickHandler(this);
			cgContainer.addItem("unavailable", cgUnavailable);
			flexTable.setWidget(pos, 0, cgUnavailable);
			cgRefused = new CustomRadioButtonSingle("v_cg_status", "refused",
					"");
			cgRefused.addClickHandler(this);
			cgContainer.addItem("refused", cgRefused);
			flexTable.setWidget(pos, 1, cgRefused);
			cgRecorded = new CustomRadioButtonSingle("v_cg_status", "recorded",
					"");
			cgRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, cgRecorded);
			cgContainer.addItem("recorded", cgRecorded);

			cgContainer.setValue("unavailable");

			cgValue = new CustomTextBox();
			cgValue.setHashMapping("v_cg_value");
			cgValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_cg_value", cgValue);
			flexTable.setWidget(pos, 3, cgValue);

			cgUnits = new CustomListBox();
			if (CurrentState.getSystemConfig("metric_system").equals("Standard")) {
				cgUnits.addItem("IN");
				cgUnits.addItem("CM");
			} else {
				cgUnits.addItem("CM");
				cgUnits.addItem("IN");
			}
			cgUnits.setHashMapping("v_cg_units");
			cgUnits.setEnabled(Boolean.FALSE);
			addEntryWidget("v_cg_units", cgUnits);
			flexTable.setWidget(pos, 4, cgUnits);

			cgLocation = new CustomListBox();
			cgLocation.addItem("NONE");
			cgLocation.addItem("ABDOMINAL");
			cgLocation.addItem("ANKLE");
			cgLocation.addItem("CALF");
			cgLocation.addItem("HEAD");
			cgLocation.addItem("LOWER ARM");
			cgLocation.addItem("OTHER");
			cgLocation.addItem("THIGH");
			cgLocation.addItem("UPPER ARM");
			cgLocation.addItem("WRIST");
			cgLocation.setHashMapping("v_cg_location");
			cgLocation.setEnabled(Boolean.FALSE);
			addEntryWidget("v_cg_location", cgLocation);
			flexTable.setWidget(pos, 5, cgLocation);

			cgSite = new CustomListBox();
			cgSite.addItem("NONE");
			cgSite.addItem("LEFT");
			cgSite.addItem("RIGHT");
			cgSite.setHashMapping("v_cg_site");
			cgSite.setEnabled(Boolean.FALSE);
			addEntryWidget("v_cg_site", cgSite);
			flexTable.setWidget(pos, 6, cgSite);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Height")));
			pos++;

			hContainer = new CustomRadioButtonContainer();
			hContainer.setHashMapping("v_h_status");
			addEntryWidget("v_h_status", hContainer);
			hUnavailable = new CustomRadioButtonSingle("v_h_status",
					"unavailable", "");
			hUnavailable.addClickHandler(this);
			hContainer.addItem("unavailable", hUnavailable);
			flexTable.setWidget(pos, 0, hUnavailable);
			hRefused = new CustomRadioButtonSingle("v_h_status", "refused", "");
			hRefused.addClickHandler(this);
			hContainer.addItem("refused", hRefused);
			flexTable.setWidget(pos, 1, hRefused);
			hRecorded = new CustomRadioButtonSingle("v_h_status", "recorded",
					"");
			hRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, hRecorded);
			hContainer.addItem("recorded", hRecorded);

			hContainer.setValue("unavailable");

			hValue = new CustomTextBox();
			hValue.setHashMapping("v_h_value");
			hValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_h_value", hValue);
			flexTable.setWidget(pos, 3, hValue);

			hUnits = new CustomListBox();
			if (CurrentState.getSystemConfig("metric_system").equals("Standard")) {
				hUnits.addItem("IN");
				hUnits.addItem("CM");
			} else {
				hUnits.addItem("CM");
				hUnits.addItem("IN");
			}
			hUnits.setHashMapping("v_h_units");
			hUnits.setEnabled(Boolean.FALSE);
			addEntryWidget("v_h_units", hUnits);
			flexTable.setWidget(pos, 4, hUnits);

			hQuality = new CustomListBox();
			hQuality.addItem("NONE");
			hQuality.addItem("ACTUAL");
			hQuality.addItem("ESTIMATED");
			hQuality.setHashMapping("v_h_quality");
			hQuality.setEnabled(Boolean.FALSE);
			addEntryWidget("v_h_quality", hQuality);
			flexTable.setWidget(pos, 5, hQuality);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Weight")));
			pos++;

			wContainer = new CustomRadioButtonContainer();
			wContainer.setHashMapping("v_w_status");
			addEntryWidget("v_w_status", wContainer);
			wUnavailable = new CustomRadioButtonSingle("v_w_status",
					"unavailable", "");
			wUnavailable.addClickHandler(this);
			wContainer.addItem("unavailable", wUnavailable);
			flexTable.setWidget(pos, 0, wUnavailable);
			wRefused = new CustomRadioButtonSingle("v_w_status", "refused", "");
			wRefused.addClickHandler(this);
			wContainer.addItem("refused", wRefused);
			flexTable.setWidget(pos, 1, wRefused);
			wRecorded = new CustomRadioButtonSingle("v_w_status", "recorded",
					"");
			wRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, wRecorded);
			wContainer.addItem("recorded", wRecorded);

			wContainer.setValue("unavailable");

			wValue = new CustomTextBox();
			wValue.setHashMapping("v_w_value");
			wValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_w_value", wValue);
			flexTable.setWidget(pos, 3, wValue);

			wUnits = new CustomListBox();
			wUnits.addItem("LB");
			wUnits.addItem("KG");
			wUnits.addItem("OZ");
			wUnits.setHashMapping("v_w_units");
			wUnits.setEnabled(Boolean.FALSE);
			addEntryWidget("v_w_units", wUnits);
			flexTable.setWidget(pos, 4, wUnits);

			wMethod = new CustomListBox();
			wMethod.addItem("NONE");
			wMethod.addItem("BED");
			wMethod.addItem("CHAIR");
			wMethod.addItem("OTHER");
			wMethod.addItem("PEDIATRIC");
			wMethod.addItem("STANDING");
			wMethod.setHashMapping("v_w_method");
			wMethod.setEnabled(Boolean.FALSE);
			addEntryWidget("v_w_method", wMethod);
			flexTable.setWidget(pos, 5, wMethod);

			wQuality = new CustomListBox();
			wQuality.addItem("NONE");
			wQuality.addItem("ACTUAL");
			wQuality.addItem("DRY");
			wQuality.addItem("ESTIMATED");
			wQuality.setHashMapping("v_w_quality");
			wQuality.setEnabled(Boolean.FALSE);
			addEntryWidget("v_w_quality", wQuality);
			flexTable.setWidget(pos, 6, wQuality);

			pos++;
		}

		{
			flexTable.setWidget(pos, 3, new Label(_("Pain")));
			pos++;

			painContainer = new CustomRadioButtonContainer();
			painContainer.setHashMapping("v_pain_status");
			addEntryWidget("v_pain_status", painContainer);
			painUnavailable = new CustomRadioButtonSingle("v_pain_status",
					"unavailable", "");
			painUnavailable.addClickHandler(this);
			painContainer.addItem("unavailable", painUnavailable);
			flexTable.setWidget(pos, 0, painUnavailable);
			painRefused = new CustomRadioButtonSingle("v_pain_status",
					"refused", "");
			painRefused.addClickHandler(this);
			painContainer.addItem("refused", painRefused);
			flexTable.setWidget(pos, 1, painRefused);
			painRecorded = new CustomRadioButtonSingle("v_pain_status",
					"recorded", "");
			painRecorded.addClickHandler(this);
			flexTable.setWidget(pos, 2, painRecorded);
			painContainer.addItem("recorded", painRecorded);

			painContainer.setValue("unavailable");

			painValue = new CustomTextBox();
			painValue.setHashMapping("v_pain_value");
			painValue.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pain_value", painValue);
			flexTable.setWidget(pos, 3, painValue);

			painScale = new CustomListBox();
			painScale.addItem("VAS");
			painScale.addItem("FACES");
			painScale.setHashMapping("v_pain_scale");
			painScale.setEnabled(Boolean.FALSE);
			addEntryWidget("v_pain_scale", painScale);
			flexTable.setWidget(pos, 4, painScale);

			pos++;
		}

		/*
		 * final Label notesLabel = new Label("Notes"); flexTable.setWidget(pos,
		 * 0, notesLabel); wNotes = new CustomTextArea();
		 * wNotes.setHashMapping("notes"); addEntryWidget("notes", wNotes);
		 * flexTable.setWidget(pos, 1, wNotes); pos++;
		 */

		// Submit stuff at the bottom of the form

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final CustomButton wSubmit = new CustomButton(_("Submit"), AppConstants.ICON_ADD);
		buttonBar.add(wSubmit);
		wSubmit.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				submitForm();
			}
		});
		final CustomButton wReset = new CustomButton(_("Reset"), AppConstants.ICON_CLEAR);
		buttonBar.add(wReset);
		wReset.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
	}

	public String getModuleName() {
		return "Vitals";
	}

	public void resetForm() {
	}

	@Override
	public void onClick(ClickEvent event) {
		Object src = event.getSource();
		if (src instanceof CustomRadioButtonSingle) {
			CustomRadioButtonSingle b = (CustomRadioButtonSingle) src;
			handleRadioValueChange(b, b.getStoredValue());
		}
	}

	/**
	 * Perform UI changes based on potential value.
	 * 
	 * @param radioButton
	 * @param val
	 */
	protected void handleRadioValueChange(CustomRadioButtonSingle radioButton,
			String val) {
		// Temperature
		if (radioButton == temperatureUnavailable
				|| radioButton == temperatureRefused) {
			temperatureValue.setEnabled(false);
			temperatureUnits.setEnabled(false);
			temperatureQualifier.setEnabled(false);
		} else if (radioButton == temperatureRecorded) {
			temperatureValue.setEnabled(true);
			temperatureUnits.setEnabled(true);
			temperatureQualifier.setEnabled(true);
		}
		if (radioButton == pulseUnavailable || radioButton == pulseRefused) {
			pulseValue.setEnabled(false);
			pulseLocation.setEnabled(false);
			pulseMethod.setEnabled(false);
			pulseSite.setEnabled(false);
		} else if (radioButton == pulseRecorded) {
			pulseValue.setEnabled(true);
			pulseLocation.setEnabled(true);
			pulseMethod.setEnabled(true);
			pulseSite.setEnabled(true);
		}
		if (radioButton == pulseOxUnavailable || radioButton == pulseOxRefused) {
			pulseOxO2Conc.setEnabled(false);
			pulseOxFlowRate.setEnabled(false);
			pulseOxMethod.setEnabled(false);
		} else if (radioButton == pulseOxRecorded) {
			pulseOxO2Conc.setEnabled(true);
			pulseOxFlowRate.setEnabled(true);
			pulseOxMethod.setEnabled(true);
		}
		if (radioButton == glucoseUnavailable || radioButton == glucoseRefused) {
			glucoseValue.setEnabled(false);
			glucoseUnits.setEnabled(false);
			glucoseQualifier.setEnabled(false);
		} else if (radioButton == glucoseRecorded) {
			glucoseValue.setEnabled(true);
			glucoseUnits.setEnabled(true);
			glucoseQualifier.setEnabled(true);
		}
		if (radioButton == respirationUnavailable
				|| radioButton == respirationRefused) {
			respirationValue.setEnabled(false);
			respirationMethod.setEnabled(false);
			respirationPosition.setEnabled(false);
		} else if (radioButton == respirationRecorded) {
			respirationValue.setEnabled(true);
			respirationMethod.setEnabled(true);
			respirationPosition.setEnabled(true);
		}
		if (radioButton == bpUnavailable || radioButton == bpRefused) {
			bpSValue.setEnabled(false);
			bpDValue.setEnabled(false);
			bpLocation.setEnabled(false);
			bpMethod.setEnabled(false);
			bpPosition.setEnabled(false);
		} else if (radioButton == bpRecorded) {
			bpSValue.setEnabled(true);
			bpDValue.setEnabled(true);
			bpLocation.setEnabled(true);
			bpMethod.setEnabled(true);
			bpPosition.setEnabled(true);
		}
		if (radioButton == cvpUnavailable || radioButton == cvpRefused) {
			cvpValue.setEnabled(false);
			cvpPor.setEnabled(false);
		} else if (radioButton == cvpRecorded) {
			cvpValue.setEnabled(true);
			cvpPor.setEnabled(true);
		}
		if (radioButton == cgUnavailable || radioButton == cgRefused) {
			cgValue.setEnabled(false);
			cgUnits.setEnabled(false);
			cgLocation.setEnabled(false);
			cgSite.setEnabled(false);
		} else if (radioButton == cgRecorded) {
			cgValue.setEnabled(true);
			cgUnits.setEnabled(true);
			cgLocation.setEnabled(true);
			cgSite.setEnabled(true);
		}
		if (radioButton == hUnavailable || radioButton == hRefused) {
			hValue.setEnabled(false);
			hUnits.setEnabled(false);
			hQuality.setEnabled(false);
		} else if (radioButton == hRecorded) {
			hValue.setEnabled(true);
			hUnits.setEnabled(true);
			hQuality.setEnabled(true);
		}
		if (radioButton == wUnavailable || radioButton == wRefused) {
			wValue.setEnabled(false);
			wUnits.setEnabled(false);
			wMethod.setEnabled(false);
			wQuality.setEnabled(false);
		} else if (radioButton == wRecorded) {
			wValue.setEnabled(true);
			wUnits.setEnabled(true);
			wMethod.setEnabled(true);
			wQuality.setEnabled(true);
		}
		if (radioButton == painUnavailable || radioButton == painRefused) {
			painValue.setEnabled(false);
			painScale.setEnabled(false);
		} else if (radioButton == painRecorded) {
			painValue.setEnabled(true);
			painScale.setEnabled(true);
		}
	}
}
