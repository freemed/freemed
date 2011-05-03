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

package org.freemedsoftware.gwt.client.Api;

import java.util.HashMap;

import com.google.gwt.user.client.rpc.AsyncCallback;

public interface PatientInterfaceAsync {
	public void CheckForDuplicatePatient(HashMap<String, String> criteria, AsyncCallback<Integer> callback);

	public void DxForPatient(Integer patientId, AsyncCallback<HashMap<String, String>[]> callback);

	public void EmrAttachmentsByPatient(Integer patientId, AsyncCallback<HashMap<String, String>[]> callback);

	public void EmrAttachmentsByPatientTable(
			Integer patientId, String tableName, AsyncCallback<HashMap<String, String>[]> callback);

	public void EmrModules(String partOfName,
			Boolean sameKeyAndValue, AsyncCallback<HashMap<String, String>[]> callback);

	public void MoveEmrAttachments(Integer patientFrom, Integer patientTo,
			Integer[] attachments, AsyncCallback<Boolean> callback);

	public void NumericSearch(
			HashMap<String, String> criteria, AsyncCallback<HashMap<String, String>[]> callback);

	public void Search(HashMap<String, String> criteria, AsyncCallback<HashMap<String, String>[]> callback);

	public void PatientCriteria(Integer patientId, AsyncCallback<HashMap<String, String>> callback);

	public void PatientInformation(Integer patientId, AsyncCallback<HashMap<String, String>> callback);

	public void TotalInSystem(AsyncCallback<Integer> callback);

	public void Picklist(String textParameters,
			Integer limit, AsyncCallback<HashMap<Integer, String>> callback);

	public void ProceduresToBill(Integer patientId, AsyncCallback<Integer[]> callback);

	public void ToText(Integer patientId, Boolean fullString, AsyncCallback<String> callback);
}
