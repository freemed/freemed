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

package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class CustomAlert {

	protected VerticalPanel contentVPanel = new VerticalPanel();
	
	protected Label alertLabel = null;
	
	public CustomAlert(String confirmationText){
		init(confirmationText);
	}
	private CustomAlert(){
	}
	
	protected void init(String confirmationText){
		VerticalPanel panel = new VerticalPanel();
		alertLabel = new Label(confirmationText);
		panel.add(alertLabel);
		PopupView viewInfo=new PopupView(panel);
		Popup alertPopup = new Popup();
		alertPopup.setNewWidget(viewInfo);
		alertPopup.initialize();

	}
}
