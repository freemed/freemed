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

package org.freemedsoftware.gwt.client.widget;

import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class CustomDialogBox extends DialogBox {

	protected VerticalPanel contentVPanel = new VerticalPanel();
	
	public CustomDialogBox(){
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		
		
		VerticalPanel popupContainer = new VerticalPanel();
		setWidget(popupContainer);
		
		/////Top header
		final HorizontalPanel closeButtonContainer = new HorizontalPanel();
		popupContainer.add(closeButtonContainer);
		closeButtonContainer.setWidth("100%");
		
		Image closeImage = new Image("resources/images/close_x.16x16.png");
		closeImage.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				getCustomDialogBox().hide();
			}
		});
		closeButtonContainer.add(closeImage);
		closeButtonContainer.setCellHorizontalAlignment(closeImage, HasHorizontalAlignment.ALIGN_RIGHT);

		//content panel
		popupContainer.add(contentVPanel);
		

	}
	
	public void setContent(Widget widget){
		contentVPanel.clear();
		contentVPanel.add(widget);
	}
	
	public CustomDialogBox getCustomDialogBox(){
		return this;
	}
}
