/*
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

import com.google.gwt.dom.client.Document;
import com.google.gwt.dom.client.HeadElement;
import com.google.gwt.dom.client.LinkElement;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.DeferredCommand;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.RootPanel;

/**
 * A utility class that loads style sheets.
 */
public class StyleSheetLoader {
  /**
   * A {@link Timer} that creates a small reference widget used to determine
   * when a new style sheet has finished loading. The widget has a natural width
   * of 0px, but when the style sheet is loaded, the width changes to 5px. The
   * style sheet should contain a style definition that is passed into the
   * constructor that defines a height and width greater than 0px.
   */
  private static class StyleTesterTimer extends Timer {
    private Command callback;
    private Label refWidget;

    /**
     * Create a new {@link StyleTesterTimer}.
     * 
     * @param refStyleName the reference style name
     * @param callback the callback to execute when the style sheet loads
     */
    public StyleTesterTimer(String refStyleName, Command callback) {
      this.callback = callback;

      // Create the reference Widget
      refWidget = new Label();
      refWidget.setStyleName(refStyleName);
      refWidget.getElement().getStyle().setProperty("position", "absolute");
      refWidget.getElement().getStyle().setProperty("visibility", "hidden");
      refWidget.getElement().getStyle().setProperty("display", "inline");
      refWidget.getElement().getStyle().setPropertyPx("padding", 0);
      refWidget.getElement().getStyle().setPropertyPx("margin", 0);
      refWidget.getElement().getStyle().setPropertyPx("border", 0);
      refWidget.getElement().getStyle().setPropertyPx("top", 0);
      refWidget.getElement().getStyle().setPropertyPx("left", 0);
      RootPanel.get().add(refWidget);
    }

    @Override
    public void run() {
      // Redisplay the reference widget so it redraws itself
      refWidget.setVisible(false);
      refWidget.setVisible(true);

      // Check the dimensions of the reference widget
      if (refWidget.getOffsetWidth() > 0) {
        RootPanel.get().remove(refWidget);

        // Fire the callback in a DeferredCommand to ensure the browser has
        // enough time to parse the styles. Otherwise, we'll get weird styling
        // issues.
        DeferredCommand.addCommand(callback);
      } else {
        schedule(10);
      }
    }
  }

  /**
   * Convenience method for getting the document's head element.
   * 
   * @return the document's head element
   */
  public static native HeadElement getHeadElement()
  /*-{
    return $doc.getElementsByTagName("head")[0];
  }-*/;

  /**
   * Load a style sheet onto the page.
   * 
   * @param href the url of the style sheet
   */
  public static void loadStyleSheet(String href) {
    LinkElement linkElem = Document.get().createLinkElement();
    linkElem.setRel("stylesheet");
    linkElem.setType("text/css");
    linkElem.setHref(href);
    getHeadElement().appendChild(linkElem);
  }

  /**
   * Load a style sheet onto the page and fire a callback when it has loaded.
   * The style sheet should contain a style definition called refStyleName that
   * defines a height and width greater than 0px.
   * 
   * @param href the url of the style sheet
   * @param refStyleName the style name of the reference element
   * @param callback the callback executed when the style sheet has loaded
   */
  public static void loadStyleSheet(String href, String refStyleName,
      Command callback) {
    loadStyleSheet(href);
    waitForStyleSheet(refStyleName, callback);
  }

  /**
   * Detect when a style sheet has loaded by placing an element on the page that
   * is affected by a rule in the style sheet, as described in
   * {@link #loadStyleSheet(String, String, Command)}. When the style sheet has
   * loaded, the callback will be executed.
   * 
   * @param refStyleName the style name of the reference element
   * @param callback the callback executed when the style sheet has loaded
   */
  public static void waitForStyleSheet(String refStyleName, Command callback) {
    new StyleTesterTimer(refStyleName, callback).run();
  }
}
