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

package org.freemedsoftware.gwt.client.Api;

import com.google.gwt.user.client.rpc.AsyncCallback;
import java.util.HashMap;

public interface SchedulerAsync {
	public void GetDailyAppointments ( String apptDate, Integer providerId, AsyncCallback callback );
	public void GetDailyAppointmentsRange ( String startingDate, String endingDate, Integer providerId, AsyncCallback callback );
	public void GetDailyAppointmentScheduler ( String schedulerDate, Integer providerId, AsyncCallback callback );
	public void CopyAppointment ( Integer id, String destDate, AsyncCallback callback );
	public void CopyGroupAppointment ( Integer groupId, String targetDate, AsyncCallback callback );
	public void FindDateAppointments ( String searchDate, Integer providerId, AsyncCallback callback );
	public void FindGroupAppointments ( Integer groupId, AsyncCallback callback );
	public void MoveAppointment ( Integer apptId, HashMap data, AsyncCallback callback );
	public void MoveGroupAppointment ( Integer groupId, HashMap data, AsyncCallback callback );
	public void NextAvailable ( HashMap data, AsyncCallback callback );
	public void SetAppointment ( HashMap data, AsyncCallback callback );
	public void SetGroupAppointment ( Integer[] patientIds, HashMap data, AsyncCallback callback );
	public void SetRecurringAppointment ( Integer apptId, Integer[] timestamps, String description, AsyncCallback callback );
}

