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

package org.freemedsoftware.gwt.client.Api;

import java.util.HashMap;

import com.google.gwt.user.client.rpc.AsyncCallback;

public interface SchedulerAsync {

	public void GetDailyAppointments(String apptDate,
			Integer providerId, AsyncCallback<HashMap<String, String>[]> callback);

	public void GetDailyAppointmentsRange(
			String startingDate, String endingDate, Integer providerId, AsyncCallback<HashMap<String, String>[]> callback);

	public void GetDailyAppointmentScheduler(
			String schedulerDate, Integer providerId, AsyncCallback<HashMap<String, String>[]> callback);

	public void CopyAppointment(Integer id, String destDate, AsyncCallback<Boolean> callback);

	public void CopyGroupAppointment(Integer groupId, String targetDate, AsyncCallback<Boolean> callback);

	public void FindDateAppointments(String searchDate,
			Integer providerId, AsyncCallback<HashMap<String, String>[]> callback);

	public void FindGroupAppointments(Integer groupId, AsyncCallback<HashMap<String, String>[]> callback);

	public void MoveAppointment(Integer apptId, HashMap<String, String> data, AsyncCallback<Boolean> callback);

	public void MoveGroupAppointment(Integer groupId,
			HashMap<String, String> data, AsyncCallback<Boolean> callback);

	public void NextAvailable(HashMap<String, String> data, AsyncCallback<String[][]> callback);

	public void SetAppointment(HashMap<String, String> data, AsyncCallback<Integer> callback);

	public void SetGroupAppointment(Integer[] patientIds,
			HashMap<String, String> data, AsyncCallback<Integer> callback);

	public void SetRecurringAppointment(Integer apptId, Integer[] timestamps,
			String description, AsyncCallback<Void> callback);

}
