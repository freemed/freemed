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
 * (at your option) any later version.s
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

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;

import pl.rmalinowski.gwt2swf.client.ui.SWFWidget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Style;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.DOM;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.VerticalPanel;

public class MugshotWebcamWidget extends Composite {

	protected static String MUGSHOT_SWF_URL = GWT.getHostPageBaseURL()
			+ "resources/mugshot/mugshot2.swf?build=3";

	protected static String MUGSHOT_POST_URL = Util
			.getJsonRequest(
					"org.freemedsoftware.module.PhotographicIdentification.ImportMugshotPhoto",
					new String[] {});

	protected SWFWidget mugshotSwfWidget = null;

	protected Command onFinishedCommand = null;

	/**
	 * Create mugshot photo upload widget based on drupal "mugshot" plugin.
	 * 
	 * @param patientId
	 */
	public MugshotWebcamWidget(Integer patientId) {
		super();
		VerticalPanel p = new VerticalPanel();
		initWidget(p);

		// Create JSNI hooks to call back native GWT code
		publishJsni(this);

		// Create SWF widget
		mugshotSwfWidget = new SWFWidget(MUGSHOT_SWF_URL, "512px", "384px");
		mugshotSwfWidget.addFlashVar("post_url", MUGSHOT_POST_URL);
		mugshotSwfWidget.addFlashVar("sound", "true");
		mugshotSwfWidget.addFlashVar("preview", "true");
		mugshotSwfWidget.addFlashVar("username", patientId.toString());
		mugshotSwfWidget.addFlashVar("goo_enable", "false");
		mugshotSwfWidget.addFlashVar("jpeg_quality", "75");
		mugshotSwfWidget.addFlashVar("js_callback", "mugshotTake");
		mugshotSwfWidget.addParam("allowScriptAccess", "sameDomain");
		mugshotSwfWidget.addParam("quality", "high");
		mugshotSwfWidget.setTitle("Upload a patient image using a webcam.");
		p.add(mugshotSwfWidget);
		HTML previewPane = new HTML("<div class=\"messages status\" "
				+ " id=\"mugshot_status\" " + " style=\"display:none;\">"
				+ "This image will be uploaded." + "</div>"
				+ "<div id=\"mugshot_preview\" " + " style=\"display: none;\">"
				+ "<ul class=\"mugshot\">" + "<li class=\"mugshot\">"
				+ "<img src=\"\" id=\"mugshot_preview_img\" />"
				+ "</li></ul></div>");
		p.add(previewPane);
	}

	private native void publishJsni(MugshotWebcamWidget thisObject)
	/*-{
		$wnd.mugshotTake = function(a,b,c,d) { 
			return thisObject.@org.freemedsoftware.gwt.client.widget.MugshotWebcamWidget::onCapture(Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;);(a,b,c,d);
		};
	}-*/;

	/**
	 * Attach a command to be executed after the picture is taken.
	 * 
	 * @param c
	 */
	public void setOnFinishedCommand(Command c) {
		onFinishedCommand = c;
	}

	public Command getOnFinishedCommand() {
		return onFinishedCommand;
	}

	protected void onCapture(String mid, String murl, String mturl,
			String preview) {
		JsonUtil.debug("mugshot onCapture");
		DOM.getElementById("mugshot_preview").getStyle().setVisibility(
				Style.Visibility.VISIBLE);
		DOM.getElementById("mugshot_preview_img").setAttribute("src", mturl);
		DOM.getElementById("mugshot_status").getStyle().setVisibility(
				Style.Visibility.VISIBLE);
		if (preview.equals("true")) {
			// DOM.getElementById("mugshot_url").val(mturl);
			// DOM.getElementById("mugshot_mid").val(mid);
		}
		CurrentState.getToaster().addItem("MugshotWebcamWidget",
				"Uploaded image.", Toaster.TOASTER_INFO);
		if (getOnFinishedCommand() != null) {
			getOnFinishedCommand().execute();
		}
	}

}
