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

import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTextArea;
import org.freemedsoftware.gwt.client.widget.SupportModuleMultipleChoiceWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class ReferralEntry extends PatientEntryScreenInterface {

	protected SupportModuleWidget wOriginalProvider, wDestinationProvider,
			wPayor;

	protected SupportModuleMultipleChoiceWidget wDx, wComorbids;

	protected CustomListBox wDirection, wPayorApproval, wUrgency;

	protected CustomTextArea wReasons;

	protected String moduleName = "Referrals";

	protected String patientIdName = "refpatient";
	
	public ReferralEntry() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);

		final Label originalProviderLabel = new Label("Original Provider");
		flexTable.setWidget(0, 0, originalProviderLabel);

		wOriginalProvider = new SupportModuleWidget("ProviderModule");
		wOriginalProvider.setHashMapping("refprovorig");
		addEntryWidget("refprovorig", wOriginalProvider);
		flexTable.setWidget(0, 1, wOriginalProvider);

		final Label referredToLabel = new Label("Referred To");
		flexTable.setWidget(1, 0, referredToLabel);

		wDestinationProvider = new SupportModuleWidget("ProviderModule");
		wDestinationProvider.setHashMapping("refprovdest");
		addEntryWidget("refprovdest", wDestinationProvider);
		flexTable.setWidget(1, 1, wDestinationProvider);

		final Label diagnosesLabel = new Label("Diagnoses");
		flexTable.setWidget(2, 0, diagnosesLabel);

		wDx = new SupportModuleMultipleChoiceWidget("IcdCodes");
		wDx.setHashMapping("refdx");
		addEntryWidget("refdx", wDx);
		flexTable.setWidget(2, 1, wDx);

		final Label referralDirectionLabel = new Label("Referral Direction");
		flexTable.setWidget(3, 0, referralDirectionLabel);

		wDirection = new CustomListBox();
		flexTable.setWidget(3, 1, wDirection);
		wDirection.setHashMapping("refdirection");
		addEntryWidget("refdirection", wDirection);
		wDirection.addItem("outbound", "outbound");
		wDirection.addItem("inbound", "inbound");
		wDirection.setVisibleItemCount(1);

		final Label reasonsLabel = new Label("Reasons");
		flexTable.setWidget(5, 0, reasonsLabel);

		wReasons = new CustomTextArea();
		wReasons.setHashMapping("refreasons");
		addEntryWidget("refreasons", wReasons);
		flexTable.setWidget(5, 1, wReasons);

		final Label comorbidsLabel = new Label("Comorbids");
		flexTable.setWidget(6, 0, comorbidsLabel);

		wComorbids = new SupportModuleMultipleChoiceWidget("IcdCodes");
		wComorbids.setHashMapping("refcomorbids");
		addEntryWidget("refcomorbids", wComorbids);
		flexTable.setWidget(6, 1, wComorbids);

		final Label payorLabel = new Label("Payor");
		flexTable.setWidget(7, 0, payorLabel);

		wPayor = new SupportModuleWidget("InsuranceCompanyModule");
		wPayor.setHashMapping("refpayor");
		addEntryWidget("refpayor", wPayor);
		flexTable.setWidget(7, 1, wPayor);

		final Label payorApprovalLabel = new Label("Payor Approval");
		flexTable.setWidget(8, 0, payorApprovalLabel);

		wPayorApproval = new CustomListBox();
		flexTable.setWidget(8, 1, wPayorApproval);
		wPayorApproval.setHashMapping("refpayorapproval");
		addEntryWidget("refpayorapproval", wPayorApproval);
		wPayorApproval.addItem("unknown");
		wPayorApproval.addItem("denied");
		wPayorApproval.addItem("approved");
		wPayorApproval.setVisibleItemCount(1);

		final Label urgencyLabel = new Label("Urgency");
		flexTable.setWidget(4, 0, urgencyLabel);

		wUrgency = new CustomListBox();
		flexTable.setWidget(4, 1, wUrgency);
		wUrgency.setHashMapping("refurgency");
		addEntryWidget("refurgency", wUrgency);
		wUrgency.addItem("not specified", "0");
		wUrgency.addItem("low priority", "1");
		wUrgency.addItem("normal", "2");
		wUrgency.addItem("urgent", "3");
		wUrgency.addItem("emergency", "4");
		wUrgency.setVisibleItemCount(1);

		final HorizontalPanel buttonBar = new HorizontalPanel();
		buttonBar.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		final CustomButton wSubmit = new CustomButton("Submit",AppConstants.ICON_ADD);
		buttonBar.add(wSubmit);
		wSubmit.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				submitForm();
			}
		});
		final CustomButton wReset = new CustomButton("Reset",AppConstants.ICON_CLEAR);
		buttonBar.add(wReset);
		wReset.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent w) {
				resetForm();
			}
		});
		verticalPanel.add(buttonBar);
		Util.setFocus(wOriginalProvider);
	}

	public String getModuleName() {
		return "Referrals";
	}

	public void resetForm() {
		wOriginalProvider.clear();
		wDestinationProvider.clear();
		wPayor.clear();
		wDx.setValue(new Integer[] {});
		wDirection.setWidgetValue("outbound");
		wPayorApproval.setWidgetValue("unknown");
		wReasons.setText("");
		wComorbids.setValue(new Integer[] {});
		wUrgency.setWidgetValue("0");
		wOriginalProvider.setFocus(true);
	}
	
}
