/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2012 FreeMED Software Foundation
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

import com.google.gwt.event.shared.EventHandler;
import com.google.gwt.event.shared.GwtEvent;

public class SystemEvent extends GwtEvent<SystemEvent.Handler> {

	public interface Handler extends EventHandler {
		public void onSystemEvent(SystemEvent e);
	}

	public static final GwtEvent.Type<SystemEvent.Handler> TYPE = new GwtEvent.Type<SystemEvent.Handler>();

	@Override
	protected void dispatch(SystemEvent.Handler handler) {
		handler.onSystemEvent(this);
	}

	@Override
	public GwtEvent.Type<SystemEvent.Handler> getAssociatedType() {
		return TYPE;
	}

	private String sourceModule = null;

	public String getSourceModule() {
		return sourceModule;
	}

	public void setSourceModule(String s) {
		this.sourceModule = s;
	}

	private String action = null;

	public String getAction() {
		return action;
	}

	public void setAction(String s) {
		this.action = s;
	}

	private String text = null;

	public String getText() {
		return text;
	}

	public void setText(String s) {
		this.text = s;
	}

	private Integer patient = null;

	public Integer getPatient() {
		return patient;
	}

	public void setPatient(Integer p) {
		this.patient = p;
	}

}
