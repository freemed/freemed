/*
 * $Id$
 * 
 *
 * Authors:
 * 			Fred Sauer
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
 *
 * The code in this file has originally been written by Mr. Fred Sauer.
 * We, the FreeMED Software Foundation, thank him a lot for the right to use his code!
 *
 */
package org.freemedsoftware.gwt.client.widget;

import com.allen_sauer.gwt.dnd.client.drop.IndexedDropController;
import com.google.gwt.user.client.ui.IndexedPanel;
import com.google.gwt.user.client.ui.Widget;

/**
 * IndexedDropController that disallows dropping after the last child, which is
 * assumed to be dummy spacer widget preventing parent collapse.
 */
@SuppressWarnings("deprecation")
public class NoInsertAtEndIndexedDropController extends IndexedDropController {

	private IndexedPanel dropTarget;

	public NoInsertAtEndIndexedDropController(IndexedPanel dropTarget) {
		super(dropTarget);
		this.dropTarget = dropTarget;
	}

	@Override
	protected void insert(Widget widget, int beforeIndex) {
		if (beforeIndex == dropTarget.getWidgetCount()) {
			beforeIndex--;
		}
		super.insert(widget, beforeIndex);
	}
}
