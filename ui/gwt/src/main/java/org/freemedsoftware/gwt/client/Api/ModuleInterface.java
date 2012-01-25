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

import com.google.gwt.user.client.rpc.RemoteService;

public interface ModuleInterface extends RemoteService {
	/**
	 * 
	 * @param data
	 * @return
	 */
	public Integer ModuleAddMethod(String module, HashMap<String, String> data);

	/**
	 * 
	 * @param module
	 * @param id
	 * @return
	 */
	public HashMap<String, String> ModuleGetRecordMethod(String module,
			Integer id);

	public HashMap<String, String>[] ModuleGetRecordsMethod(String module,
			Integer count, String ckey, String cval);

	public Integer ModuleDeleteMethod(String module, Integer id);

	/**
	 * 
	 * @param data
	 * @return
	 */
	public Integer ModuleModifyMethod(String module,
			HashMap<String, String> data);

	/**
	 * 
	 * @param module
	 * @param criteria
	 * @return
	 */
	public HashMap<String, String> ModuleSupportPicklistMethod(String module,
			String criteria);

	/**
	 * 
	 * @param module
	 * @param id
	 * @return
	 */
	public String ModuleToTextMethod(String module, Integer id);

	public Boolean PrintToFax(String faxNumber, Integer[] items);

	public Boolean PrintToPrinter(String printer, Integer[] items);

}
