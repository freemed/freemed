/*
 * $Id$
 *
 * Authors:
 *      Ray Cromwell (Timepedia)
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

package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.DOM;
import com.google.gwt.user.client.Element;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.ui.Composite;
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

	private HashMap<String, Label> labelMap = new HashMap<String, Label>();
	private HashMap<String, Widget> widgetMap = new HashMap<String, Widget>();

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
		setStylePrimaryName("accordion");
	}

	public AccordionPanel() {
		this(false);
	}

	public void add(String label, final Widget content) {
		final Label l = new Label(label);
		l.setWidth("100%");
		l.setStylePrimaryName(getStylePrimaryName() + "-title");
		final SimplePanel sp = new SimplePanel();
		sp.setWidget(content);

		ClickHandler ch = new ClickHandler() {
			public void onClick(ClickEvent sender) {
				expand(l, sp);
			}
		};
		l.addClickHandler(ch);
		aPanel.add(l);

		// Add to indices
		labelMap.put(label, l);
		widgetMap.put(label, content);

		sp.setStylePrimaryName(getStylePrimaryName() + "-content");
		DOM.setStyleAttribute(sp.getElement(), animField, "0px");
		DOM.setStyleAttribute(sp.getElement(), "overflow", "hidden");
		aPanel.add(sp);
	}

	public void selectPanel(String label) {
		Widget c = widgetMap.get(label);
		c.addStyleDependentName("selected");
		currentlyExpanded = c;
		currentlyExpandedLabel = labelMap.get(label);
		currentlyExpandedLabel.addStyleDependentName("selected");
		Element elem = c.getElement();
		DOM.setStyleAttribute(elem, "overflow", "auto");
		DOM.setStyleAttribute(elem, animField, "auto");
	}

	private void expand(final Label label, final Widget c) {
		if (currentlyExpanded != null)
			DOM.setStyleAttribute(currentlyExpanded.getElement(), "overflow",
					"hidden");

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
					} else {
						currentlyExpanded = null;
					}
				}
			}
		};
		t.schedule(10);
	}

}
