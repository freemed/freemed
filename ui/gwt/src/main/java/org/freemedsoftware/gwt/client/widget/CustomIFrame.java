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

import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class CustomIFrame extends DialogBox {

	protected CustomIFrame() {
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		// TODO Auto-generated constructor stub
	}

	protected CustomIFrame(boolean autoHide, boolean modal) {
		super(autoHide, modal);
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		// TODO Auto-generated constructor stub
	}

	protected CustomIFrame(boolean autoHide) {
		super(autoHide);
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		// TODO Auto-generated constructor stub
	}
	
	public CustomIFrame(String src,int width,int height){
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		init(src, width, height);
	}

	public CustomIFrame(String src,int width){
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		init(src, width, null);
	}
	
	public CustomIFrame(String src){
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		init(src, null, null);
	}
	
	protected void init(String src,Integer width,Integer height){
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
				getCustomIFrame().hide();
			}
		});
		closeButtonContainer.add(closeImage);
		closeButtonContainer.setCellHorizontalAlignment(closeImage, HasHorizontalAlignment.ALIGN_RIGHT);
		
		final Label helpLabel = new Label("Help");
		helpLabel.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		popupContainer.add(helpLabel);
		
		String iframeStr = "<iframe src="+src+ getWidthStr(width)+ getHeightStr(height) +" align=right>"+
		 "This is an in-line frame. You can <a href="+src+">view the frame</a> and then hit your back arrow to return to the page."+
		 "</iframe>";
		popupContainer.add(new HTML(iframeStr));
	}
	
	protected String getHeightStr(Integer height){
		if(height==null) return "";
		return " height="+height;
	}
	
	protected String getWidthStr(Integer width){
		if(width==null) return "";
		return " width="+width;
	}
	
	public CustomIFrame getCustomIFrame(){
		return this;
	}
	
}
