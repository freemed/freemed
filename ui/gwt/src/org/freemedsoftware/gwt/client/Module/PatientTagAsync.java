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

package org.freemedsoftware.gwt.client.Module;

import java.util.HashMap;

import com.google.gwt.user.client.rpc.AsyncCallback;

public interface PatientTagAsync {
	public void ListTags(String criteria, AsyncCallback callback);

	public void CreateTag(Integer patientId, String tagName, AsyncCallback callback);

	public void ChangeTag(String oldTag, String newTag, AsyncCallback callback);

	public void ExpireTag(Integer patientId, String tagName, AsyncCallback callback);

	public void TagsForPatient(Integer patientId, AsyncCallback callback);

	/**
	 * @param tagName
	 * @param includeInactive
	 */
	public void SimpleTagSearch(String tagName, Boolean includeInactive, AsyncCallback callback);

	/**
	 * @param tagName
	 * @param clauses
	 * @param includeInactive
	 */
	public void AdvancedTagSearch(String primaryTagName, HashMap clauses,
			Boolean includeInactive, AsyncCallback callback);

}