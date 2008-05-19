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

package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.MessagesModule;
import org.freemedsoftware.gwt.client.Module.MessagesModuleAsync;
import org.freemedsoftware.gwt.client.widget.ClosableTab;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.rpc.ServiceDefTarget;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.client.ui.HTMLTable.CellFormatter;
import com.google.gwt.user.client.ui.HTMLTable.RowFormatter;
import com.thapar.gwt.user.ui.client.widget.SortableTable;

public class MessagingScreen extends ScreenInterface {

	private SortableTable wMessages;

	/**
	 * @gwt.typeArgs <java.lang.String,java.lang.String>
	 */
	private HashMap[] mStore;

	public MessagingScreen() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);

		final Button composeButton = new Button();
		horizontalPanel.add(composeButton);
		composeButton.setText("Compose");
		composeButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				final MessagingComposeScreen p = new MessagingComposeScreen();
				p.assignState(state);
				state.getTabPanel().add(p,
						new ClosableTab("Compose Message", p));
				state.getTabPanel().selectTab(
						state.getTabPanel().getWidgetCount() - 1);
			}
		});

		final Button selectAllButton = new Button();
		horizontalPanel.add(selectAllButton);
		selectAllButton.setText("Select All");

		final Button selectNoneButton = new Button();
		horizontalPanel.add(selectNoneButton);
		selectNoneButton.setText("Select None");

		final VerticalPanel verticalSplitPanel = new VerticalPanel();
		verticalPanel.add(verticalSplitPanel);
		verticalSplitPanel.setSize("100%", "100%");
		// verticalSplitPanel.setSplitPosition("50%");

		wMessages = new SortableTable();
		verticalSplitPanel.add(wMessages);
		wMessages.setSize("100%", "100%");
		wMessages.addColumnHeader("Received", 0);
		wMessages.addColumnHeader("From", 1);
		wMessages.addColumnHeader("Subject", 2);

		// Boilerplate
		final int columnCount = 3;
		wMessages.setStyleName("sortableTable");
		RowFormatter rowFormatter = wMessages.getRowFormatter();
		rowFormatter.setStyleName(0, "tableHeader");
		CellFormatter cellFormatter = wMessages.getCellFormatter();
		for (int colIndex = 0; colIndex <= columnCount; colIndex++) {
			cellFormatter.setStyleName(0, colIndex, "headerStyle");
			cellFormatter.setAlignment(0, colIndex,
					HasHorizontalAlignment.ALIGN_CENTER,
					HasVerticalAlignment.ALIGN_MIDDLE);
		}

		final HTML messageView = new HTML("Message text goes here.");
		verticalSplitPanel.add(messageView);
		messageView.setSize("100%", "100%");
		// verticalSplitPanel.setSize("100%", "100%");
		
		// Start population routine
		populate("");
	}

	public void populate(String tag) {
		if (Util.isStubbedMode()) {
			/**
			 * @gwt.typeArgs <java.lang.String,java.lang.String>
			 */
			HashMap[] dummyData = getStubData();
			populateByData(dummyData);
		} else {
			// Populate the whole thing.
			MessagesModuleAsync service = (MessagesModuleAsync) GWT
					.create(MessagesModule.class);
			ServiceDefTarget endpoint = (ServiceDefTarget) service;
			String moduleRelativeURL = Util.getRelativeURL();
			endpoint.setServiceEntryPoint(moduleRelativeURL);
			service.GetAllByTag(tag, Boolean.FALSE, new AsyncCallback() {
				public void onSuccess(Object result) {
					/**
					 * @gwt.typeArgs <java.lang.String,java.lang.String>
					 */
					HashMap[] res = (HashMap[]) result;
					populateByData(res);
				}

				public void onFailure(Throwable t) {

				}
			});
		}
	}

	/**
	 * Actual internal data population method, wrapped for testing.
	 * 
	 * @param data
	 */
	public void populateByData(HashMap[] data) {
		// Keep a copy of the data in the local store
		mStore = data;
		// Clear any current contents
		wMessages.clear();
		for (int iter = 0; iter < data.length; iter++) {
			wMessages.setValue(iter + 1, 0, (String) data[iter].get("stamp"));
			wMessages.setValue(iter + 1, 1, (String) data[iter]
					.get("from_user"));
			wMessages.setValue(iter + 1, 2, (String) data[iter].get("subject"));
		}
	}

	/**
	 * @return
	 * @gwt.typeArgs <java.lang.String,java.lang.String>
	 */
	public HashMap[] getStubData() {
		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		final HashMap a = new HashMap();
		a.put("id", "1");
		a.put("stamp", "2007-08-01");
		a.put("from_user", "A");
		a.put("subject", "Subject A");

		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		final HashMap b = new HashMap();
		b.put("id", "1");
		b.put("stamp", "2007-08-01");
		b.put("from_user", "B");
		b.put("subject", "Subject B");

		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		final HashMap c = new HashMap();
		c.put("id", "1");
		c.put("stamp", "2007-08-03");
		c.put("from_user", "C");
		c.put("subject", "Subject C");

		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		return new HashMap[] { a, b, c };
	}

}
