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

import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.PushButton;

public class CustomButton extends PushButton {

	protected String imagesDirectory = "resources/images/";

	protected String title = null;

	protected String imageName = null;

	protected Image icon = null;

	protected CustomButton() {
	}

	@Override
	protected void onAttach() {
		// TODO Auto-generated method stub
		super.onAttach();
		// int btnWidth =
		// this.getElement().getFirstChildElement().getFirstChildElement().getOffsetWidth();
		// this.getElement().getStyle().setWidth(btnWidth, Unit.PX);

	}

	@Override
	protected void onClick() {
		// TODO Auto-generated method stub
		// this.getElement().getStyle().clearWidth();
		super.onClick();
		// int btnWidth =
		// this.getElement().getFirstChildElement().getFirstChildElement().getOffsetWidth();
		// this.getElement().getStyle().setWidth(btnWidth, Unit.PX);
	}

	public CustomButton(String title) {
		initButton(title, null, null);
		// TODO Auto-generated constructor stub
	}

	public CustomButton(String title, String imageName) {
		initButton(title, imageName, null);
	}

	public CustomButton(String title, Image icon) {
		initButton(title, null, icon);
	}

	protected void initButton(String title, String imageName, Image icon) {
		this.setStyleName("gwt-CustomPushButton gwt-CustomPushButton-up");

		this.title = title;
		this.imageName = imageName;
		this.icon = icon;
		FlexTable buttonTable = new FlexTable();
		buttonTable.setCellPadding(0);
		buttonTable.setCellSpacing(0);

		// Setting left rounded image
		Image leftRoundedEdge = new Image(imagesDirectory
				+ "btn-left-light.png");
		buttonTable.setWidget(0, 0, leftRoundedEdge);

		// Creating Label for Text
		buttonTable.setHTML(0, 1, "&nbsp;&nbsp; " + title + " &nbsp;&nbsp;");
		buttonTable.getCellFormatter().setStyleName(0, 1,
				"gwt-CustomPushButton-inner");
		// Checking for icon creation
		if (icon != null || imageName != null) {
			// Creating icon stuff
			if (imageName != null)
				icon = new Image(imagesDirectory + imageName);
			buttonTable.setWidget(0, 2, icon);
			buttonTable.getCellFormatter().setStyleName(0, 2,
					"gwt-CustomPushButton-inner");

			Image rightRoundedEdge = new Image(imagesDirectory
					+ "btn-right-light.png");
			buttonTable.setWidget(0, 3, rightRoundedEdge);
		} else {
			// Setting default left rounded image
			Image rightRoundedEdge = new Image(imagesDirectory
					+ "btn-right-light.png");
			buttonTable.setWidget(0, 2, rightRoundedEdge);
		}
		this.setHTML(buttonTable.toString());
		this.getDownHoveringFace().setHTML(buttonTable.toString());
		this.getDownFace().setHTML(buttonTable.toString());
		this.getUpHoveringFace().setHTML(buttonTable.toString());
		this.getUpFace().setHTML(buttonTable.toString());

	}

	public void setText(String title) {
		initButton(title, imageName, icon);
	}

	public String getText() {
		return title;
	}
}
