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

import java.util.HashMap;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Widget;

public class CustomActionBar extends Composite implements ClickHandler {

	public interface HandleCustomAction{
		public final static int ADD 	= 1;
		public final static int DELETE 	= 2;
		public final static int VIEW 	= 3;
		public final static int PRINT 	= 4;
		public final static int MODIFY 	= 5;
		public final static int LOCK 	= 6;
		public final static int LOCKED 	= 7;
		public final static int CLONE 	= 8;
		
		public void handleAction(int id,HashMap<String, String> data,int action);
	} 
	
	protected final String IMAGE_ANNOTATE = "resources/images/add1.16x16.png";
	
	protected final String IMAGE_MODIFY   = "resources/images/summary_modify.16x16.png";

	protected final String IMAGE_DELETE   = "resources/images/summary_delete.16x16.png";
	
	protected final String IMAGE_VIEW	  = "resources/images/summary_view.16x16.png";
	
	protected final String IMAGE_CLONE   = "resources/images/copy-icon.16x16.png";
	
	protected final String IMAGE_LOCK	  = "resources/images/summary_lock.16x16.png";
	
	protected final String IMAGE_LOCKED	  = "resources/images/summary_locked.16x16.png";

	protected final String IMAGE_PRINT    = "resources/images/ico.printer.16x16.png";

	protected Integer internalId = 0;
	
	protected boolean locked = false;

	protected boolean haveAllRights = false;
	
	protected HashMap<String, String> data = null;
	
	protected HandleCustomAction handleCustomAction=null;

	protected Image addImage = null, deleteImage = null,
			modifyImage = null, lockImage = null, lockedImage = null,
			printImage = null,viewImage = null, cloneImage = null;

	public CustomActionBar(HashMap<String, String> item){
		this(item, null);
	}
	
	public CustomActionBar(HashMap<String, String> item,HandleCustomAction handleCustomAction) {
		this.handleCustomAction = handleCustomAction;
		
		// Pull ID for future
		internalId = Integer.parseInt(item.get("id")==null?item.get("Id"):item.get("id"));
		data = item;

		locked = (data.get("locked")!=null && Integer.parseInt(data.get("locked")) > 0);

		HorizontalPanel hPanel = new HorizontalPanel();
		initWidget(hPanel);


		// Build icons
		/*
		addImage = new Image(IMAGE_ANNOTATE);
		addImage.setTitle("Add Annotation");
		addImage.addClickHandler(this);
		hPanel.add(addImage);
		*/

		// Display all unlocked things
		modifyImage = new Image(IMAGE_MODIFY);
		modifyImage.setTitle("Edit");
		modifyImage.addClickHandler(this);
		hPanel.add(modifyImage);
		
		deleteImage = new Image(IMAGE_DELETE);
		deleteImage.setTitle("Remove");
		deleteImage.addClickHandler(this);
		hPanel.add(deleteImage);
		
		
		cloneImage = new Image(IMAGE_CLONE);
		cloneImage.setTitle("Clone");
		cloneImage.addClickHandler(this);
		hPanel.add(cloneImage);
		
		viewImage = new Image(IMAGE_VIEW);
		viewImage.setTitle("View");
		viewImage.addClickHandler(this);
		hPanel.add(viewImage);
		
		lockImage= new Image(IMAGE_LOCK);
		lockImage.setTitle("lock image");
		lockImage.addClickHandler(this);
		lockImage.setVisible(false);
		hPanel.add(lockImage);
		
		lockedImage= new Image(IMAGE_LOCKED);
		lockedImage.setTitle("locked");
		lockedImage.addClickHandler(this);
		lockedImage.setVisible(false);
		hPanel.add(lockedImage);
		
		if(!locked){
			lockImage.setVisible(true);
		}else{
			lockedImage.setVisible(true);
		}
		
		printImage = new Image(IMAGE_PRINT);
		printImage.setTitle("Print");
		printImage.addClickHandler(this);
		hPanel.add(printImage);

		
	}


	public void onClick(ClickEvent evt) {
		Widget sender = (Widget) evt.getSource();
		int action = -1;
		if(sender == addImage){
			action = HandleCustomAction.ADD;
		}else if(sender == deleteImage){
			if(locked && !haveAllRights){
				Window.alert("You can't delete locked item!");
				return;
			}
			if(Window.confirm("Are you sure to delete this record?")){
				action = HandleCustomAction.DELETE;
			}
		}else if(sender == modifyImage){
			if(locked && !haveAllRights){
				Window.alert("You can't modify locked item!");
				return;
			}
			action = HandleCustomAction.MODIFY;
		}else if(sender == viewImage){
			action = HandleCustomAction.VIEW;
		}else if(sender == printImage){
			action = HandleCustomAction.PRINT;
		}else if(sender == cloneImage){
			action = HandleCustomAction.CLONE;
		}else if(sender == lockedImage){
			Window.alert("This record has been locked, and can no longer be modified.");
			return;
		}else if(sender == lockImage){
			if(Window.confirm("Are you sure to lock this record?")){
				action = HandleCustomAction.LOCK;
			}else return;
		}
		
		if(handleCustomAction!=null && action!=-1){
				handleCustomAction.handleAction(internalId, data, action);
		}
	}

	public void showAction(int action){
		showHideAction(action, true);
	}

	public void hideAction(int action){
		showHideAction(action, false);
	}
	
	protected void showHideAction(int action,boolean show){
		try{
			if(action == HandleCustomAction.ADD)
				addImage.setVisible(show);
			else if(action == HandleCustomAction.DELETE)
				deleteImage.setVisible(show);
			else if(action == HandleCustomAction.MODIFY)
				modifyImage.setVisible(show);
			else if(action == HandleCustomAction.VIEW)
				viewImage.setVisible(show);
			else if(action == HandleCustomAction.PRINT)
				printImage.setVisible(show);
			else if(action == HandleCustomAction.LOCK)
				lockImage.setVisible(show);
			else if(action == HandleCustomAction.LOCKED)
				lockedImage.setVisible(show);
			else if(action == HandleCustomAction.CLONE)
				cloneImage.setVisible(show);
		}catch(Exception e){}
	}
	
	public void applyPermissions(boolean read,boolean write,boolean delete,boolean modify,boolean lock){
		if(!read){
			hideAction(HandleCustomAction.VIEW);
			hideAction(HandleCustomAction.PRINT);
		}
		if(!modify)
			hideAction(HandleCustomAction.MODIFY);
		if(!delete)
			hideAction(HandleCustomAction.DELETE);
		if(!lock)
			hideAction(HandleCustomAction.LOCK);
		hideAction(HandleCustomAction.CLONE);
	}
	
	public void lock(){
		locked = true;
		lockImage.removeFromParent();
		lockedImage.setVisible(true);	
	}
	
	public HandleCustomAction getHandleCustomAction() {
		return handleCustomAction;
	}

	public void setHandleCustomAction(HandleCustomAction handleCustomAction) {
		this.handleCustomAction = handleCustomAction;
	}

	public boolean isHaveAllRights() {
		return haveAllRights;
	}

	public void setHaveAllRights(boolean haveAllRights) {
		this.haveAllRights = haveAllRights;
	}

	public boolean isLocked() {
		return locked;
	}

	public void setLocked(boolean locked) {
		this.locked = locked;
	}
}
