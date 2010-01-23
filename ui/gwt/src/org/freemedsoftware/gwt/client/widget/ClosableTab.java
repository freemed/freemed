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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.screen.PatientScreen;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.Widget;

public class ClosableTab extends Composite {

	protected Widget widget;

	protected ClosableTabInterface closableTabInterface;

	public ClosableTab(String labelText, Widget w) {
		this(labelText, w, null);
	}

	public ClosableTab(String labelText, Widget w, ClosableTabInterface cTI) {
		// Store in namespace where we can see it later
		widget = w;
		closableTabInterface = cTI;

		final HorizontalPanel panel = new HorizontalPanel();
		initWidget(panel);

		final Label label = new Label(labelText);
		label.setStyleName("gwt-tab-Label");
		panel.add(label);
		panel.setTitle(labelText);
		panel.setCellHorizontalAlignment(label,
				HasHorizontalAlignment.ALIGN_LEFT);
		panel.setCellVerticalAlignment(label, HasVerticalAlignment.ALIGN_TOP);

		final Image image = new Image();
		image.setUrl("resources/images/close_x.16x16.png");

		// Create spacer
		panel.add(new HTML("&nbsp;"));

		panel.add(image);
		panel.setCellVerticalAlignment(image, HasVerticalAlignment.ALIGN_TOP);
		panel.setCellHorizontalAlignment(image,
				HasHorizontalAlignment.ALIGN_RIGHT);

		image.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent evt) {
				boolean goodToGo = true;
				// If we have a ClosableTabInterface, make sure it's ready to
				// die
				if (closableTabInterface != null) {
					goodToGo = closableTabInterface.isReadyToClose();
				}
				if (goodToGo) {
					if (closableTabInterface != null) {
						closableTabInterface.onClose();
					}
					TabPanel t = ((TabPanel) widget.getParent().getParent()
							.getParent());
					t.selectTab(t.getWidgetIndex(widget) - 1);
					widget.removeFromParent();

					// If we're dealing with PatientScreen, remove from mapping
					if (widget instanceof PatientScreen) {
						PatientScreen ps = (PatientScreen) widget;
						Integer patientId = ps.getPatient();
						CurrentState.getPatientScreenMap().remove(patientId);
						
					}if (widget instanceof PatientScreenInterface) {
						PatientScreenInterface ps = (PatientScreenInterface) widget;
						Integer patientId = ps.getPatientId();
						CurrentState.getPatientSubScreenMap().remove(patientId);
					}
					if (widget instanceof ScreenInterface) {
						ScreenInterface screen = (ScreenInterface) widget;
						screen.closeScreen();
					}
				}
			}
		});
	}

}
