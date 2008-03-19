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

import com.google.gwt.user.client.rpc.RemoteService;
import java.util.HashMap;

public interface Scheduler extends RemoteService {
	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap[] GetDailyAppointments ( String apptDate, Integer providerId );
	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap[] GetDailyAppointmentsRange ( String startingDate, String endingDate, Integer providerId );
	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap[] GetDailyAppointmentScheduler ( String schedulerDate, Integer providerId );
	public Boolean CopyAppointment ( Integer id, String destDate );
	public Boolean CopyGroupAppointment ( Integer groupId, String targetDate );
	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap[] FindDateAppointments ( String searchDate, Integer providerId );
	/**
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap[] FindGroupAppointments ( Integer groupId );
	/**
	 * @gwt.typeArgs data <java.lang.String, java.lang.String>
	 */
	public Boolean MoveAppointment ( Integer apptId, HashMap data );
	/**
	 * @gwt.typeArgs data <java.lang.String, java.lang.String>
	 */
	public Boolean MoveGroupAppointment ( Integer groupId, HashMap data );
	/**
	 * @gwt.typeArgs data <java.lang.String, java.lang.String>
	 */
	public String[][] NextAvailable ( HashMap data );
	/**
	 * @gwt.typeArgs data <java.lang.String, java.lang.String>
	 */
	public Integer SetAppointment ( HashMap data );
	/**
	 * @gwt.typeArgs data <java.lang.String, java.lang.String>
	 */
	public Integer SetGroupAppointment ( Integer[] patientIds, HashMap data );
	public void SetRecurringAppointment ( Integer apptId, Integer[] timestamps, String description );
}

