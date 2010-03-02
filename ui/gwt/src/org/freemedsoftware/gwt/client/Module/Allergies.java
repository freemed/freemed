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

package org.freemedsoftware.gwt.client.Module;

import java.util.HashMap;

import com.google.gwt.user.client.rpc.RemoteService;

public interface Allergies extends RemoteService {

	/**
	 * @param patientId
	 * @return
	 */
	public HashMap<String, String>[] GetMostRecent(Integer patientId);

	/**
	 * @param mId
	 * @return
	 */
	public HashMap<String, String>[] GetAtoms(Integer mId);

	/**
	 * @param patientId
	 * @param mId
	 * @param atoms
	 * @return
	 */
	public Boolean SetAtoms(Integer patientId, Integer mId,
			HashMap<String, String> atoms);

}
