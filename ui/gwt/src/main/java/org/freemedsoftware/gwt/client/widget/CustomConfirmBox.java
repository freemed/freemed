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

package org.freemedsoftware.gwt.client.widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class CustomConfirmBox {

	protected VerticalPanel contentVPanel = new VerticalPanel();

	protected Label confirmLabel = null;
	protected CustomDialogBox customDialogBox = null;
	protected boolean autoHide = false;

	public CustomConfirmBox(String confirmationText, Command onYes, Command onNo) {
		init(confirmationText, onYes, onNo);
	}

	public CustomConfirmBox() {
		init("", null, null);
	}

	public CustomConfirmBox(String confirmationText) {
		init(confirmationText, null, null);
	}

	protected void init(String confirmationText, final Command onYes,
			final Command onNo) {
		customDialogBox = new CustomDialogBox();
		VerticalPanel panel = new VerticalPanel();
		customDialogBox.setContent(panel);
		confirmLabel = new Label(confirmationText);
		panel.add(confirmLabel);
		HorizontalPanel buttonHPanel = new HorizontalPanel();
		panel.add(buttonHPanel);
		panel.setCellHorizontalAlignment(buttonHPanel,
				HasHorizontalAlignment.ALIGN_CENTER);
		final CustomButton yesBTN = new CustomButton(_("Yes"),
				AppConstants.ICON_DONE);
		buttonHPanel.add(yesBTN);
		yesBTN.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent event) {
				if (onYes != null) {
					onYes.execute();
				}
				if (autoHide) {
					hide();
				}
			}

		});

		final CustomButton noBTN = new CustomButton(_("No"),
				AppConstants.ICON_CANCEL);
		buttonHPanel.add(noBTN);
		noBTN.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent event) {
				if (onNo != null)
					onNo.execute();
				if (autoHide)
					hide();
			}

		});
	}

	public void setText(String text) {
		confirmLabel.setText(text);
	}

	public CustomConfirmBox getCustomConfirmBox() {
		return this;
	}

	public void show() {
		customDialogBox.show();
		customDialogBox.center();
	}

	public void hide() {
		customDialogBox.hide();
	}

	public boolean isAutoHide() {
		return autoHide;
	}

	public void setAutoHide(boolean autoHide) {
		this.autoHide = autoHide;
	}
}
