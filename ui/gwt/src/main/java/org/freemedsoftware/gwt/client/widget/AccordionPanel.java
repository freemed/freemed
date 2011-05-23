/*
 * $Id$
 *
 * Authors:
 *      Ray Cromwell (Timepedia)
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

import java.util.ArrayList;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;

import com.google.gwt.dom.client.Style.Cursor;
import com.google.gwt.dom.client.Style.Display;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.DOM;
import com.google.gwt.user.client.Element;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.Panel;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class AccordionPanel extends Composite {

	private Panel aPanel;
	private String animField;
	private String animBounds;

	final private static int NUM_FRAMES = 8;

	private Widget currentlyExpanded = null;
	private Label currentlyExpandedLabel = null;

	private List<Label> labelMap = new ArrayList<Label>();
	private List<Widget> widgetMap = new ArrayList<Widget>();
	private List<Widget> widgetContainerMap = new ArrayList<Widget>();
	

	public AccordionPanel(boolean horizontal) {
		if (horizontal) {
			aPanel = new HorizontalPanel();
			animField = "width";
			animBounds = "scrollWidth";
			((HorizontalPanel) aPanel)
					.setVerticalAlignment(HasVerticalAlignment.ALIGN_MIDDLE);
		} else {
			aPanel = new VerticalPanel();
			animField = "height";
			animBounds = "scrollHeight";
			((VerticalPanel) aPanel)
					.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		}
		initWidget(aPanel);
		// setStylePrimaryName("accordion");
		setStyleName("gwt-DecoratedStackPanel");
	}

	public AccordionPanel() {
		this(false);
	}

	public String getHeader(String title) {

		FlexTable flexTable = new FlexTable();
		flexTable.setWidth("100%");
		flexTable.setCellPadding(0);
		flexTable.setCellSpacing(0);
		
		///start creating top strip
		flexTable.getRowFormatter().setStyleName(0, "stackItemTop");
		HTML html = new HTML();
		html.setStyleName("stackItemTopLeftInner");
		flexTable.setWidget(0, 0, html);
		flexTable.getCellFormatter().setStyleName(0, 0, "stackItemTopLeft");
		html = new HTML();
		html.setStyleName("stackItemTopCenterInner");
		flexTable.setWidget(0, 1,html);
		flexTable.getCellFormatter().setStyleName(0, 1,"stackItemTopCenter");
		html = new HTML();
		html.setStyleName("stackItemTopRightInner");
		flexTable.setWidget(0, 2,html);
		flexTable.getCellFormatter().setStyleName(0, 2, "stackItemTopRight");
		
		///stop creating top strip		
		
		//start creating main heading strip
		
		flexTable.getRowFormatter().setStyleName(0, "stackItemMiddle");
		html = new HTML();
		html.setStyleName("stackItemMiddleLeftInner");
		flexTable.setWidget(1, 0, html);
		flexTable.getCellFormatter().setStyleName(1, 0, "stackItemMiddleLeft");
		
		HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setWidth("100%");
		HTML innerHTML = new HTML(title);
		innerHTML.setStyleName("stackPanelHeader");
		headerHPanel.add(innerHTML);
		headerHPanel.setCellHorizontalAlignment(innerHTML, HasHorizontalAlignment.ALIGN_CENTER);
		headerHPanel.setCellVerticalAlignment(innerHTML, HasVerticalAlignment.ALIGN_MIDDLE);
		html = new HTML(headerHPanel.toString());
		html.setStyleName("stackItemMiddleCenterInner");		
		flexTable.getCellFormatter().setStyleName(1, 1, "stackItemMiddleCenter");
		flexTable.setWidget(1, 1, html);

		html = new HTML();
		html.setStyleName("stackItemMiddleRightInner");
		flexTable.setWidget(1, 2,html);
		flexTable.getCellFormatter().setStyleName(1, 2, "stackItemMiddleRight");

		//stop creating main heading strip
		
		return flexTable.toString();
	}

	public void add(String label, final Widget content) {
		JsonUtil.debug("adding : " + label);
		final VerticalPanel stackContainer = new VerticalPanel();
		stackContainer.setWidth("100%");
		if(widgetMap.size()==0)
			stackContainer.setStyleName("gwt-StackPanelItem-first");
		final SimplePanel sp = new SimplePanel();
		sp.setWidget(content);
		sp.setStyleName("gwt-StackPanelContent");
		sp.getElement().getStyle().setDisplay(Display.NONE);
		sp.setHeight("100%");
		/*
		 * final Label l = new Label(label); l.setWidth("100%");
		 * l.setStylePrimaryName(getStylePrimaryName() + "-title");
		 * 
		 * ClickHandler ch = new ClickHandler() { public void onClick(ClickEvent
		 * sender) { expand(l, sp); } }; l.addClickHandler(ch); aPanel.add(l);
		 */

		final HTML l = new HTML(getHeader(label));
		l.getElement().getStyle().setCursor(Cursor.POINTER);
		l.setWidth("100%");
		// l.setStylePrimaryName(getStylePrimaryName() + "-title");

		ClickHandler ch = new ClickHandler() {
			public void onClick(ClickEvent sender) {
				expand(l, sp);
			}
		};
		l.addClickHandler(ch);
//		aPanel.add(l);
		stackContainer.add(l);

		// Add to indices
		labelMap.add(l);
		widgetMap.add(content);

		// sp.setStylePrimaryName(getStylePrimaryName() + "-content");
		DOM.setStyleAttribute(sp.getElement(), animField, "0px");
		DOM.setStyleAttribute(sp.getElement(), "overflow", "hidden");
		stackContainer.add(sp);
		widgetContainerMap.add(stackContainer);
		aPanel.add(stackContainer);
		// ((VerticalPanel)aPanel).setCellHeight(sp, "100%");
	}

	public void selectPanel(Integer index) {
		Widget widget = getPanelWidget(index);
		if (widget != null)
			selectPanel(widget, labelMap.get(index));
	}

	public void selectPanel(String label) {
		Integer index = getPanelIndex(label);
		if (index != -1)
			selectPanel(widgetMap.get(index), labelMap.get(index));
	}

	protected void selectPanel(Widget widget, Label label) {
		widget.addStyleDependentName("selected");
		currentlyExpanded = widget.getParent();
		currentlyExpandedLabel = label;
		currentlyExpandedLabel.addStyleDependentName("selected");
		Element elem = widget.getParent().getElement();
		DOM.setStyleAttribute(elem, "overflow", "auto");
		DOM.setStyleAttribute(elem, animField, "auto");
		elem.getStyle().setDisplay(Display.BLOCK);
	}

	public Integer getPanelIndex(Widget widget) {
		Integer index = -1;
		for (int i = 0; i < widgetMap.size(); i++) {
			if (widget == widgetMap.get(i)) {
				index = i;
				break;
			}
		}
		JsonUtil.debug("getPanelIndex Widget : " + index);
		return index;
	}

	public Integer getPanelIndex(String title) {
		Integer index = -1;
		// Iterator<Label> iterator = labelMap.iterator();
		for (int i = 0; i < labelMap.size(); i++) {
			if (labelMap.get(i).getText().equalsIgnoreCase(title)) {
				index = i;
				break;
			}
		}
		JsonUtil.debug("getPanelIndex Label : " + index);
		return index;
	}

	public Widget getPanelWidget(Integer index) {
		Widget widget = null;
		if (index >= 0 && index < widgetMap.size())
			widget = widgetMap.get(index);
		return widget;
	}

	public Widget getPanelWidget(String title) {
		Widget widget = null;
		Integer index = getPanelIndex(title);
		widget = getPanelWidget(index);
		return widget;
	}

	public void remove(Widget widget) {
		Integer index = getPanelIndex(widget);
		remove(index);
	}

	public void remove(int index) {
		JsonUtil.debug("Removing : " + index);
		if (index != -1) {
			 //widgetMap.get(index).getParent().removeFromParent();
			 //aPanel.remove(widgetMap.get(index).getParent().getParent());
			widgetContainerMap.get(index).removeFromParent();
			widgetContainerMap.remove(index);
			// // labelMap.get(index).removeFromParent();
			 //aPanel.remove(labelMap.get(index));
			 widgetMap.remove(index);
			 labelMap.remove(index);
		}
	}

	private void expand(final Label label, final Widget c) {
		// c.getParent().getElement().setAttribute("style", "");
		if (currentlyExpanded != null) {
			DOM.setStyleAttribute(currentlyExpanded.getElement(), "overflow",
					"hidden");
		}

		final Timer t = new Timer() {
			int frame = 0;

			public void run() {
				if (currentlyExpanded != null) {
					Widget w = currentlyExpanded;
					Element elem = w.getElement();
					int oSh = DOM.getElementPropertyInt(elem, animBounds);
					DOM.setStyleAttribute(elem, animField, ""
							+ ((NUM_FRAMES - frame) * oSh / NUM_FRAMES) + "px");

				}
				if (currentlyExpanded != c) {
					Widget w = c;
					Element elem = w.getElement();
					int oSh = DOM.getElementPropertyInt(elem, animBounds);
					DOM.setStyleAttribute(elem, animField, ""
							+ (frame * oSh / NUM_FRAMES) + "px");
				}
				frame++;

				if (frame <= NUM_FRAMES) {
					schedule(10);
				} else {
					if (currentlyExpanded != null) {
						currentlyExpanded.removeStyleDependentName("selected");
						currentlyExpandedLabel
								.removeStyleDependentName("selected");
						currentlyExpanded.getElement().getStyle().setDisplay(
								Display.NONE);
					}
					c.addStyleDependentName("selected");
					if (currentlyExpanded != c) {
						currentlyExpanded = c;
						currentlyExpandedLabel = label;
						currentlyExpandedLabel
								.addStyleDependentName("selected");
						Element elem = c.getElement();
						DOM.setStyleAttribute(elem, "overflow", "auto");
						DOM.setStyleAttribute(elem, animField, "auto");
						elem.getStyle().setDisplay(Display.BLOCK);

					} else {
						currentlyExpanded = null;
					}
				}
			}
		};
		t.schedule(10);
	}

}
