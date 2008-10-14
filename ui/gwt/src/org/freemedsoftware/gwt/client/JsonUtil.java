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

package org.freemedsoftware.gwt.client;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import com.google.gwt.core.client.GWT;
import com.google.gwt.json.client.JSONArray;
import com.google.gwt.json.client.JSONObject;
import com.google.gwt.json.client.JSONValue;

public class JsonUtil {

	/**
	 * Convenience function to produce native objects from JSON structures.
	 * 
	 * @param r
	 *            JSONValue object
	 * @param t
	 *            String representation of return value
	 * @return
	 */
	@SuppressWarnings("unchecked")
	public static synchronized Object shoehornJson(JSONValue r, String t) {
		if (t.compareToIgnoreCase("HashMap<String,String>[]") == 0) {
			List<HashMap<?, ?>> result = new ArrayList<HashMap<?, ?>>();
			JSONArray a = r.isArray();
			for (int oIter = 0; oIter < a.size(); oIter++) {
				HashMap<String, String> item = new HashMap<String, String>();
				JSONObject obj = a.get(oIter).isObject();
				Iterator<String> iter = obj.keySet().iterator();
				while (iter.hasNext()) {
					String k = iter.next();
					item.put(k, obj.get(k).isString().stringValue());
				}
				result.add(item);
			}
			return (HashMap<String, String>[]) result
					.toArray(new HashMap<?, ?>[0]);
		}
		if (t.compareToIgnoreCase("HashMap<String,String>") == 0) {
			JSONObject obj = r.isObject();
			HashMap<String, String> result = new HashMap<String, String>();
			Iterator<String> iter = obj.keySet().iterator();
			while (iter.hasNext()) {
				String k = iter.next();
				result.put(k, obj.get(k).isString().stringValue());
			}
		}
		if (t.compareToIgnoreCase("HashMap<Integer,String>") == 0) {
			JSONObject obj = r.isObject();
			HashMap<Integer, String> result = new HashMap<Integer, String>();
			Iterator<String> iter = obj.keySet().iterator();
			while (iter.hasNext()) {
				String k = iter.next();
				result.put(Integer.valueOf(k), obj.get(k).isString()
						.stringValue());
			}
		}
		if (t.compareToIgnoreCase("String[][]") == 0) {
			JSONArray outer = r.isArray();
			String[][] x = new String[][] {};
			for (int oIter = 0; oIter < outer.size(); oIter++) {
				JSONArray inner = outer.get(oIter).isArray();
				for (int iIter = 0; iIter < inner.size(); iIter++) {
					x[oIter][iIter] = inner.isString().stringValue();
				}
			}
			return (String[][]) x;
		}
		if (t.compareToIgnoreCase("String[]") == 0) {
			JSONArray a = r.isArray();
			String[] x = new String[] {};
			for (int iter = 0; iter < a.size(); iter++) {
				x[iter] = a.isString().stringValue();
			}
			return (String[]) x;
		}
		if (t.compareToIgnoreCase("String") == 0) {
			return (String) r.isString().stringValue();
		}
		if (t.compareToIgnoreCase("Integer") == 0) {
			return (Integer) new Integer((int) r.isNumber().doubleValue());
		}
		if (t.compareToIgnoreCase("Boolean") == 0) {
			return (Boolean) r.isBoolean().booleanValue();
		}

		// If anything else bombs out...
		GWT.log("Could not parse type " + t, null);
		return null;
	}

}
