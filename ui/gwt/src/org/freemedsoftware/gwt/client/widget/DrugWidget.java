/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.MultumDrugLexiconAsync;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.Widget;

public class DrugWidget extends Composite {

	protected SupportModuleWidget multumDrugLookup;

	protected ListBox drugFormat;

	protected ListBox drugDosage;

	public DrugWidget() {
		final HorizontalPanel container = new HorizontalPanel();
		initWidget(container);

		multumDrugLookup = new SupportModuleWidget("MultumDrugLexicon");
		container.add(multumDrugLookup);
		multumDrugLookup.addChangeListener(new ChangeListener() {

			@Override
			public void onChange(Widget sender) {
				Integer value = ((SupportModuleWidget) sender).getValue();
				if (Util.isStubbedMode()) {
					// TODO: make this do something in stubbed mode
				} else {
					populateFormat(value);
				}
			}

		});
		drugFormat = new ListBox();
		drugFormat.setVisibleItemCount(1);
		container.add(drugFormat);
		drugDosage = new ListBox();
		drugDosage.setVisibleItemCount(1);
		container.add(drugDosage);
	}

	/**
	 * Populate format widget based on the presented drug widget value.
	 * 
	 * @param drugValue
	 */
	protected void populateFormat(Integer drugValue) {

	}

	protected void populateDosages(Integer drugValue) {
		if (Util.isStubbedMode()) {
			// TODO: simulate during stubbed mode
		} else {
			MultumDrugLexiconAsync proxy = null;
			try {
				proxy = (MultumDrugLexiconAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.MultumDrugLexicon");
			} catch (Exception e) {
				GWT.log("Exception", e);
			} finally {
				proxy.DosagesForDrug(drugValue.toString(), new String(""),
						new AsyncCallback<String[][]>() {
							@Override
							public void onSuccess(String[][] r) {
								drugDosage.clear();
								for (int iter = 0; iter < r.length; iter++) {
									drugDosage.addItem(r[iter][0], r[iter][1]);
								}
							}

							@Override
							public void onFailure(Throwable t) {
								GWT.log("Exception", t);
							}
						});
			}
		}
	}

}
