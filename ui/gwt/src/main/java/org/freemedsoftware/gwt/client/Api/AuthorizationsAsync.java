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

import java.util.Date;
import java.util.HashMap;

import com.google.gwt.user.client.rpc.AsyncCallback;

public interface AuthorizationsAsync {
	public void FindByCoverage(Integer coverageId, AsyncCallback<Integer[]> callback);

	public void GetAuthorization(Integer authorizationId, AsyncCallback<HashMap<String, String>> callback);

	public void Replace(Integer authorizationId, AsyncCallback<Boolean> callback);

	public void UseAuthorization(Integer authorizationId, AsyncCallback<Boolean> callback);

	public void Valid(Integer authorizationId, Date comparisonDate, AsyncCallback<Boolean> callback);

	public void ValidSet(Integer[] authSet, Date comparisonDate, AsyncCallback<Integer[]> callback);
}
