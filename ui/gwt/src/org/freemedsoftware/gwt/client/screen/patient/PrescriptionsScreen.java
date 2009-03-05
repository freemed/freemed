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

package org.freemedsoftware.gwt.client.screen;

import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.i18n.client.HasDirection.Direction;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;

public class PrescriptionsScreen extends PatientScreenInterface {

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

		final Label dosageLabel = new Label("Dosage");
		flexTable.setWidget(3, 0, dosageLabel);
		dosageLabel.setDirection(Direction.RTL);

		final Label quantityLabel = new Label("Quantity");
		flexTable.setWidget(4, 0, quantityLabel);
		quantityLabel.setDirection(Direction.RTL);

		final Label intervalLabel = new Label("Interval");
		flexTable.setWidget(5, 0, intervalLabel);
		intervalLabel.setDirection(Direction.RTL);

		final Label substitutionsLabel = new Label("Substitutions");
		flexTable.setWidget(6, 0, substitutionsLabel);
		substitutionsLabel.setDirection(Direction.RTL);

		final Label coverageStatusLabel = new Label("Coverage Status");
		flexTable.setWidget(7, 0, coverageStatusLabel);
		coverageStatusLabel.setDirection(Direction.RTL);

		final Label refillsLabel = new Label("Refills");
		flexTable.setWidget(8, 0, refillsLabel);
		refillsLabel.setDirection(Direction.RTL);

		final Label signatureLabel = new Label("Signature");
		flexTable.setWidget(9, 0, signatureLabel);
		signatureLabel.setDirection(Direction.RTL);

		final Label noteLabel = new Label("Note");
		flexTable.setWidget(10, 0, noteLabel);
		noteLabel.setDirection(Direction.RTL);

		final SupportModuleWidget wProvider = new SupportModuleWidget();
		flexTable.setWidget(1, 1, wProvider);
		flexTable.getFlexCellFormatter().setColSpan(1, 1, 2);
		wProvider.setModuleName("ProviderModule");

		final SupportModuleWidget wDrug = new SupportModuleWidget();
		flexTable.setWidget(2, 1, wDrug);
		flexTable.getFlexCellFormatter().setColSpan(2, 1, 2);
		wDrug.setModuleName("MultumDrugLexicon");
		// TODO

		final SupportModuleWidget wQuantity = new SupportModuleWidget();
		flexTable.setWidget(4, 1, wQuantity);
		flexTable.getFlexCellFormatter().setColSpan(4, 1, 2);
		wQuantity.setModuleName("DrugQuantityQualifiers");

		final SupportModuleWidget wInterval = new SupportModuleWidget();
		flexTable.setWidget(5, 1, wInterval);
		flexTable.getFlexCellFormatter().setColSpan(5, 1, 2);

		final SupportModuleWidget wSubstitutions = new SupportModuleWidget();
		flexTable.setWidget(6, 1, wSubstitutions);
		flexTable.getFlexCellFormatter().setColSpan(6, 1, 2);

		final SupportModuleWidget wCoverageStatus = new SupportModuleWidget();
		flexTable.setWidget(7, 1, wCoverageStatus);
		flexTable.getFlexCellFormatter().setColSpan(7, 1, 2);

		final SupportModuleWidget wRefills = new SupportModuleWidget();
		flexTable.setWidget(8, 1, wRefills);
		flexTable.getFlexCellFormatter().setColSpan(8, 1, 2);

		final TextBox tSignature = new TextBox();
		flexTable.setWidget(9, 1, tSignature);
		flexTable.getFlexCellFormatter().setColSpan(9, 1, 2);
		tSignature.setWidth("100%");

		final TextBox tNote = new TextBox();
		flexTable.setWidget(10, 1, tNote);
		flexTable.getFlexCellFormatter().setColSpan(10, 1, 2);
		tNote.setWidth("100%");

		final SupportModuleWidget wDosage = new SupportModuleWidget();
		flexTable.setWidget(3, 1, wDosage);
		flexTable.getFlexCellFormatter().setColSpan(3, 1, 2);

		final Button saveButton = new Button("Save");
		flexTable.setWidget(11, 1, saveButton);
		saveButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				savePrescription();
			}
		});

		final Button resetButton = new Button("Reset");
		flexTable.setWidget(11, 2, resetButton);
		resetButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				resetForm();
			}
		});
	}
	
	public void savePrescription() {
		//TODO
	}
	
	public void resetForm() {
		//TODO
	}

}
