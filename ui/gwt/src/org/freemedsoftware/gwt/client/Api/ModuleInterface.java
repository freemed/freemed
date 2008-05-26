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

import java.util.HashMap;

import com.google.gwt.user.client.rpc.RemoteService;

public interface ModuleInterface extends RemoteService {
	/**
	 * 
	 * @param data
	 * @gwt.typeArgs data <java.lang.String,java.lang.String>
	 * @return
	 */
	public Integer ModuleAddMethod(String module, HashMap data);

	/**
	 * 
	 * @param module
	 * @param id
	 * @return
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap ModuleGetRecordMethod(String module, Integer id);

	public Integer ModuleDeleteMethod(String module, Integer id);

	/**
	 * 
	 * @param data
	 * @gwt.typeArgs data <java.lang.String,java.lang.String>
	 * @return
	 */
	public Integer ModuleModifyMethod(String module, HashMap data);

	/**
	 * 
	 * @param module
	 * @param criteria
	 * @return
	 * @gwt.typeArgs <java.lang.String, java.lang.String>
	 */
	public HashMap ModuleSupportPicklistMethod(String module, String criteria);

	/**
	 * 
	 * @param module
	 * @param id
	 * @return
	 */
	public HashMap ModuleToTextMethod(String module, Integer id);

}
