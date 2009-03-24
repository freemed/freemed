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

package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.widget.DocumentBox;
import org.freemedsoftware.gwt.client.widget.MessageBox;
import org.freemedsoftware.gwt.client.widget.NoInsertAtEndIndexedDropController;
import org.freemedsoftware.gwt.client.widget.NotesBox;
import org.freemedsoftware.gwt.client.widget.PrescriptionRefillBox;
import org.freemedsoftware.gwt.client.widget.WorkList;

import com.allen_sauer.gwt.dnd.client.PickupDragController;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class DashboardScreen extends ScreenInterface {

	final AbsolutePanel boundaryPanel = new AbsolutePanel();
	PickupDragController dragController = new PickupDragController(
			boundaryPanel, false);

	public class DashboardItemContainer extends Composite {

		protected Label label;
		protected String t;

		public DashboardItemContainer(String title, WidgetInterface contents) {

			final VerticalPanel container = new VerticalPanel();
			initWidget(container);
			t = title;
			label = new Label(title);

			label.setStylePrimaryName("freemed-DashboardLabel");
			container.add(label);
			container.add(contents);
			addChildWidget(contents);
		}

		public DashboardItemContainer(String title) {

			final VerticalPanel container = new VerticalPanel();
			initWidget(container);

			label = new Label(title);

			label.setStylePrimaryName("freemed-DashboardLabel");
			container.add(label);
		}

		public Label getLabel() {
			return label;
		}

		public String getTitle() {
			return t;
		}

	}

	protected MessageBox messageBox = new MessageBox();

	protected WorkList workList = new WorkList();

	protected String noteBoxConfig = "";

	protected PrescriptionRefillBox prescriptionRefillBox = new PrescriptionRefillBox();

	protected NotesBox notesBox = new NotesBox();

	protected DocumentBox documentBox = new DocumentBox();

	protected VerticalPanel[] vPanelColHead = {};
	protected VerticalPanel[] vPanelCol = {};

	// Default Column value. Can be overriden by the user
	protected Integer cols = 3;

	protected NoInsertAtEndIndexedDropController[] dropController = {};

	protected HorizontalPanel hPanel = new HorizontalPanel();

	public DashboardScreen() {

		// Initialize everything
		final VerticalPanel outOfDrag = new VerticalPanel();
		initWidget(outOfDrag);
		outOfDrag.setWidth("100%");

		final Label descDnd = new Label(
				"Click and hold the Title of a Widget to move it");
		outOfDrag.add(descDnd);

		outOfDrag.add(boundaryPanel);
		boundaryPanel.setWidth("100%");

		hPanel.setWidth("100%");
		boundaryPanel.add(hPanel);
		dragController.setBehaviorConstrainedToBoundaryPanel(false);
		dragController.setBehaviorMultipleSelection(false);

		// Create a new fresh view
		clearView();

		if (state != null) {
			Boolean b = restoreArrangement();
			if (b == false) {
				addBaseWidgets();
			}
		} else {
			addBaseWidgets();
		}

		preventCollapse();

	}

	public void assignState(CurrentState s) {

		// Custom junk here

	}

	public void createDraggableWidget(String title, Integer col) {
		DashboardItemContainer d = null;

		if (title == "Work List") {
			d = new DashboardItemContainer("Work List", workList);
		} else if (title == "Messages") {
			d = new DashboardItemContainer("Messages", messageBox);
		} else if (title == "Notepad") {
			d = new DashboardItemContainer("Notepad", notesBox);
		} else if (title == "Prescription Refills") {
			d = new DashboardItemContainer("Prescription Refills",
					prescriptionRefillBox);
		} else if (title == "Unfiled Documents") {
			d = new DashboardItemContainer("Unfiled Documents", documentBox);
		}

		if (d != null) {
			vPanelCol[col].add(d);
			dragController.makeDraggable(d, d.getLabel());
		}
	}

	public void addBaseWidgets() {
		// Add Default Widgets
		createDraggableWidget("Work List", 0);
		createDraggableWidget("Messages", 0);
		createDraggableWidget("Notepad", 1);
		createDraggableWidget("Prescription Refills", 2);
		createDraggableWidget("Unfiled Documents", 0);
	}

	public void preventCollapse() {
		for (int i = 0; i < cols; i++) {
			// Add a blank Label to each column to prevent collapsing the Panels
			SimplePanel s = new SimplePanel();
			s.setHeight("5em");
			s.setWidth("10em");
			s.setWidget(new Label(""));
			vPanelCol[i].add(s);
		}
	}

	public void clearView() {
		for (int i = 0; i < vPanelColHead.length; i++) {
			vPanelColHead[i].removeFromParent();
		}

		for (int i = 0; i < dropController.length; i++) {
			dragController.unregisterDropController(dropController[i]);
		}

		for (int i = 0; i < cols; i++) {

			vPanelColHead[i] = new VerticalPanel();
			vPanelColHead[i].add(new DashboardItemContainer("Column #"
					+ Integer.toString(i + 1)));

			vPanelCol[i] = new VerticalPanel();
			// vPanelCol[i].setSize(Integer.toString(100/cols)+"%", "100%");
			vPanelCol[i].setSpacing(5);
			hPanel.add(vPanelColHead[i]);
			vPanelColHead[i].add(vPanelCol[i]);
			dropController[i] = new NoInsertAtEndIndexedDropController(
					vPanelCol[i]);
			dragController.registerDropController(dropController[i]);
		}

		/*
		 * for (int i = vPanelCol.length; i > 0 ;i--) { vPanelCol[i].clear(); }
		 */
	}

	public void saveArrangement() {
		// This method saves the current Arrangement of the Widgets moved by the
		// user
		HashMap<String, String> order = new HashMap<String, String>();
		Integer columns = hPanel.getWidgetCount();

		state.setUserConfig("dashboardcols", JsonUtil.jsonify(columns));

		for (int i = (columns - 1); i >= 0; i--) {

			VerticalPanel vP = (VerticalPanel) ((VerticalPanel) hPanel
					.getWidget(i)).getWidget(1);

			Integer rows = vP.getWidgetCount();
			for (int j = 0; j < rows - 1; j++) {
				String t = vP.getWidget(j).getTitle();
				// j is the __correct__ index!
				order.put(t, Integer.toString(i));
			}
		}

		state.setUserConfig("dashboard", JsonUtil.jsonify(order));

	}

	@SuppressWarnings("unchecked")
	public boolean restoreArrangement() {
		String c = state.getUserConfig("dashboard");
		Integer i = Integer.parseInt(state.getUserConfig("dashboardcols"));
		if (i > 0) {
			cols = i;
		}
		if (c != "") {

			HashMap<String, String> conf = (HashMap<String, String>) JsonUtil
					.shoehornJson(JSONParser.parse(c), "HashMap<String,String>");

			Iterator<String> iter = conf.keySet().iterator();

			Boolean firstrun = true;

			while (iter.hasNext()) {
				if (firstrun == true) {
					firstrun = false;
					clearView();
				}

				String s = iter.next();
				Integer colNum = Integer.parseInt(conf.get(s));
				createDraggableWidget(s, colNum);

			}

			preventCollapse();
			return !firstrun;
		} else {
			return false;
		}
	}

	public void afterStateSet() {
		JsonUtil.debug("DashBoard: AfterStateSet() called");
		restoreArrangement();
		if (state.getDefaultProvider() > 0) {
			workList.setProvider(state.getDefaultProvider());
		}
	}
}
