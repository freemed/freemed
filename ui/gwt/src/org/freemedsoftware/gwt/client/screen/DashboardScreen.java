/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng <pmeng@freemedsoftware.org>
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
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class DashboardScreen extends ScreenInterface {

	final AbsolutePanel boundaryPanel = new AbsolutePanel();
	PickupDragController dragController = new PickupDragController(
			boundaryPanel, false);

	public class DashboardItemContainer extends WidgetInterface {
		protected Label label;
		protected String t;
		protected WidgetInterface content;

		/**
		 * @wbp.parser.constructor
		 */

		public DashboardItemContainer(String title, WidgetInterface contents) {
			final VerticalPanel container = new VerticalPanel();
			initWidget(container);
			content = contents;
			t = title;
			label = new Label(title);

			final HorizontalPanel hP = new HorizontalPanel();
			hP.add(label);
			hP.setStylePrimaryName("freemed-DashboardLabel");
			final PushButton button = new PushButton();
			button.getUpFace().setImage(
					new Image("resources/images/close_x.16x16.png"));

			button.addClickListener(new ClickListener() {
				public void onClick(Widget sender) {
					remove();
				}
			});

			hP.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_RIGHT);

			hP.add(button);

			container.add(hP);
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

		protected void remove() {
			removeChildWidget(content);
			listBoxWidgets.addItem(t);
			this.removeFromParent();
		}

		public void setStyle(String style) {
			label.setStylePrimaryName(style);
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
	protected ListBox listBoxWidgets = new ListBox();

	public DashboardScreen() {
		// Initialize everything
		final VerticalPanel outOfDrag = new VerticalPanel();
		initWidget(outOfDrag);
		outOfDrag.setWidth("100%");

		final Label descDnd = new Label(
				"Click and hold the Title of a Widget to move it to another column.");

		final HorizontalPanel outOfDragHP = new HorizontalPanel();
		outOfDragHP.add(descDnd);

		outOfDrag.add(outOfDragHP);
		outOfDragHP.add(listBoxWidgets);
		listBoxWidgets.addChangeListener(new ChangeListener() {
			public void onChange(Widget sender) {
				if (listBoxWidgets.getSelectedIndex() > 0) {
					addDashboardItem();
				}
			}
		});

		listBoxWidgets.addItem("Select a Widget to add");
		listBoxWidgets.setVisibleItemCount(1);
		addListItems();

		outOfDrag.add(boundaryPanel);
		boundaryPanel.setWidth("100%");

		hPanel.setWidth("100%");
		boundaryPanel.add(hPanel);
		dragController.setBehaviorConstrainedToBoundaryPanel(false);
		dragController.setBehaviorMultipleSelection(false);

		// Create a new fresh view
		clearView();

		Boolean b = restoreArrangement();
		if (b == false) {
			addBaseWidgets();
		}

		preventCollapse();

	}

	public void addDashboardItem() {
		String s = listBoxWidgets
				.getItemText(listBoxWidgets.getSelectedIndex());
		createDraggableWidget(s, 0);

	}

	public void addBaseWidgets() {
		// Add Default Widgets
		createDraggableWidget("Work List", 0);
		createDraggableWidget("Messages", 0);
		createDraggableWidget("Notepad", 1);
		createDraggableWidget("Prescription Refills", 2);
		createDraggableWidget("Unfiled Documents", 0);
	}

	public void addListItems() {
		// Kind of odd solution, but i had no other idea yet.
		// __ALL__ items need to be defined here.
		// If an Widget is added with CreateDraggableWidget, there the item is
		// removed from the dropdown list.
		addSingleListItem("Work List");
		addSingleListItem("Messages");
		addSingleListItem("Notepad");
		addSingleListItem("Prescription Refills");
		addSingleListItem("Unfiled Documents");
	}

	public void addSingleListItem(String s) {
		if (itemInList(s) == -1) {
			listBoxWidgets.addItem(s);
		}
	}

	public void afterStateSet() {
		JsonUtil.debug("DashBoard: AfterStateSet() called");
		restoreArrangement();
		if (CurrentState.getDefaultProvider() > 0) {
			workList.setProvider(CurrentState.getDefaultProvider());
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
			DashboardItemContainer dbic = new DashboardItemContainer("Column #"
					+ Integer.toString(i + 1));
			dbic.setStyle("freemed-DashboardLabel-Column");
			vPanelColHead[i].add(dbic);

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

	public void createDraggableWidget(String title, Integer col) {
		DashboardItemContainer d = null;
		JsonUtil.debug(title + " adding");
		if (title == "Work List") {
			d = new DashboardItemContainer("Work List", workList);
			removeListItem("Work List");
			if (CurrentState.getDefaultProvider() != null) {
				workList.setProvider(CurrentState.getDefaultProvider());
			}
		} else if (title == "Messages") {
			d = new DashboardItemContainer("Messages", messageBox);
			removeListItem("Messages");
		} else if (title == "Notepad") {
			d = new DashboardItemContainer("Notepad", notesBox);
			removeListItem("Notepad");
		} else if (title == "Prescription Refills") {
			d = new DashboardItemContainer("Prescription Refills",
					prescriptionRefillBox);
			removeListItem("Prescription Refills");
		} else if (title == "Unfiled Documents") {
			d = new DashboardItemContainer("Unfiled Documents", documentBox);
			removeListItem("Unfiled Documents");
		}

		if (d != null) {
			vPanelCol[col].add(d);
			dragController.makeDraggable(d, d.getLabel());
			addChildWidget(d);
		}
	}

	public Integer itemInList(String s) {
		for (int i = 0; i < listBoxWidgets.getItemCount(); i++) {
			if (listBoxWidgets.getValue(i) == s) {
				return i;
			}
		}
		return -1;
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

	public void removeListItem(String s) {
		Integer i = itemInList(s);
		if (i > 0) {
			listBoxWidgets.removeItem(i);
		}
	}

	@SuppressWarnings( { "unchecked", "finally" })
	public boolean restoreArrangement() {
		String c = null;
		Integer i = 0;

		try {
			c = CurrentState.getUserConfig("dashboard");
			i = Integer.parseInt(CurrentState.getUserConfig("dashboardcols"));
		} catch (Exception ex) {
			JsonUtil.debug("restoreArrangement(): Caught exception "
					+ ex.toString());
		} finally {
			if (i > 0) {
				cols = i;
			}
			if (c != "") {
				final HashMap<String, String> conf = (HashMap<String, String>) JsonUtil
						.shoehornJson(JSONParser.parse(c),
								"HashMap<String,String>");

				final Iterator<String> iter = conf.keySet().iterator();

				Boolean firstrun = true;

				while (iter.hasNext()) {
					if (firstrun == true) {
						firstrun = false;
						clearView();
					}

					final String s = iter.next();
					final Integer colNum = Integer.parseInt(conf.get(s));
					addListItems();
					createDraggableWidget(s, colNum);
				}

				preventCollapse();
				return !firstrun;
			} else {
				return false;
			}
		}
	}

	public void saveArrangement() {
		// This method saves the current Arrangement of the Widgets moved by the
		// user
		HashMap<String, String> order = new HashMap<String, String>();
		Integer columns = hPanel.getWidgetCount();

		CurrentState.setUserConfig("dashboardcols", JsonUtil.jsonify(columns));

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
		CurrentState.setUserConfig("dashboard", JsonUtil.jsonify(order));
	}

}
