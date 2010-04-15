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

package org.freemedsoftware.gwt.client;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.user.client.ui.Composite;

public abstract class WidgetInterface extends Composite {

	protected List<WidgetInterface> children = new ArrayList<WidgetInterface>();

	protected boolean canRead   = false;// (true/false) for current module READ access
	
	protected boolean canWrite  = false;// (true/false) for current module WRITE access
	
	protected boolean canModify = false;// (true/false) for current module MODIFY access
	
	protected boolean canDelete = false;// (true/false) for current module DELETE access
	
	protected boolean canLock   = false;// (true/false) for current module LOCK access
	
	public WidgetInterface(){
	}
	public WidgetInterface(String moduleName){
		if(moduleName!=null){// setting appropriate booleans by using ACL Permissions 
			canRead   = CurrentState.isActionAllowed(moduleName, AppConstants.READ);
			canWrite  = CurrentState.isActionAllowed(moduleName, AppConstants.WRITE);
			canModify = CurrentState.isActionAllowed(moduleName, AppConstants.MODIFY);
			canDelete = CurrentState.isActionAllowed(moduleName, AppConstants.DELETE);
			canLock   = CurrentState.isActionAllowed(moduleName, AppConstants.LOCK);
		}
	}
	
	/**
	 * Method used to initialize widget, called after state is set.
	 */
	public void populateWidget() {
	}

	/**
	 * Method to set current widget value from HashMap of data. Defaults to no
	 * action.
	 * 
	 * @param data
	 */
	public void setFromData(HashMap<String, String> data) {
	}

}
