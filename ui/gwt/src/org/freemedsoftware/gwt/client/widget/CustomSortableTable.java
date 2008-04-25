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

package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.thapar.gwt.user.ui.client.widget.SortableTable;

public class CustomSortableTable extends SortableTable {

	public CustomSortableTable() {
		super();
		setStyleName("sortableTable");
		RowFormatter rowFormatter = getRowFormatter();
		rowFormatter.setStyleName(0, "tableHeader");
	}

	/**
	 * Format table with boiler plate.
	 * 
	 * @param columnCount
	 *            Number of columns present.
	 */
	public void formatTable(int rowCount, int columnCount) {
		{
			CellFormatter cellFormatter = getCellFormatter();
			for (int colIndex = 0; colIndex <= columnCount; colIndex++) {
				cellFormatter.setStyleName(0, colIndex, "headerStyle");
				cellFormatter.setAlignment(0, colIndex,
						HasHorizontalAlignment.ALIGN_CENTER,
						HasVerticalAlignment.ALIGN_MIDDLE);
			}
		}

		// Format all the data, if it exists
		try {
			if (rowCount > 0) {
				RowFormatter rowFormatter = getRowFormatter();
				CellFormatter cellFormatter = getCellFormatter();
				for (int rowIndex = 1; rowIndex <= rowCount; rowIndex++) {
					// Alternating rows
					if (rowIndex % 2 == 0) {
						rowFormatter.setStyleName(rowIndex, "customRowStyle");
					} else {
						rowFormatter.setStyleName(rowIndex, "tableRow");
					}
					// Set column alignments and fonts
					for (int colIndex = 0; colIndex < columnCount; colIndex++) {
						cellFormatter.setStyleName(rowIndex, colIndex,
								"customFont");
						cellFormatter.setAlignment(rowIndex, colIndex,
								HasHorizontalAlignment.ALIGN_LEFT,
								HasVerticalAlignment.ALIGN_MIDDLE);
					}
				}
			}
		} catch (Exception e) {

		}
	}

}
