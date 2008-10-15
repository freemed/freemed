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
import com.google.gwt.json.client.JSONBoolean;
import com.google.gwt.json.client.JSONNumber;
import com.google.gwt.json.client.JSONObject;
import com.google.gwt.json.client.JSONString;
import com.google.gwt.json.client.JSONValue;

public class JsonUtil {

	/**
	 * Create JSON adaptation of objects.
	 * 
	 * @param o
	 * @return JSON formatted string
	 */
	@SuppressWarnings("unchecked")
	public static synchronized String jsonify(Object o) {
		if (o instanceof HashMap) {
			JSONObject out = new JSONObject();
			HashMap<String, String> ng = (HashMap<String, String>) o;
			Iterator<String> iter = ng.keySet().iterator();
			while (iter.hasNext()) {
				String key = iter.next();
				out.put(key, new JSONString(ng.get(key)));
			}
			return out.toString();
		}
		if (o instanceof HashMap[]) {
			JSONArray out = new JSONArray();
			for (int oIter = 0; oIter < ((HashMap<String, String>[]) o).length; oIter++) {
				JSONObject a = new JSONObject();
				HashMap<String, String> ng = ((HashMap<String, String>[]) o)[oIter];
				Iterator<String> iter = ng.keySet().iterator();
				while (iter.hasNext()) {
					String key = iter.next();
					a.put(key, new JSONString(ng.get(key)));
				}
				out.set(oIter, a);
			}
			return out.toString();
		}
		if (o instanceof Boolean) {
			return JSONBoolean.getInstance(((Boolean) o).booleanValue())
					.toString();
		}
		if (o instanceof Integer) {
			return new JSONNumber((Integer) o).toString();
		}
		if (o instanceof String) {
			return new JSONString((String) o).toString();
		}

		// All else fails, return ""
		return "";
	}

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
					if (obj.get(k).isString() != null) {
						item.put(k, obj.get(k).isString().stringValue());
					}
				}
				result.add(oIter, item);
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
				if (obj.get(k).isString() != null) {
					result.put(k, obj.get(k).isString().stringValue());
				}
			}
			return (HashMap<String, String>) result;
		}
		if (t.compareToIgnoreCase("HashMap<Integer,String>") == 0) {
			JSONObject obj = r.isObject();
			HashMap<Integer, String> result = new HashMap<Integer, String>();
			Iterator<String> iter = obj.keySet().iterator();
			while (iter.hasNext()) {
				String k = iter.next();
				if (obj.get(k).isString() != null) {
					result.put(Integer.valueOf(k), obj.get(k).isString()
							.stringValue());
				}
			}
			return (HashMap<Integer, String>) result;
		}
		if (t.compareToIgnoreCase("String[][]") == 0) {
			JSONArray outer = r.isArray();
			List<String[]> x = new ArrayList<String[]>();
			if (r.isArray() != null) {
				for (int oIter = 0; oIter < outer.size(); oIter++) {
					if (outer.get(oIter).isArray() != null) {
						JSONArray inner = outer.get(oIter).isArray();
						List<String> xI = new ArrayList<String>();
						if (inner.isArray() != null) {
							for (int iIter = 0; iIter < inner.size(); iIter++) {
								if (inner.get(iIter).isString() != null) {
									xI.add(iIter, inner.get(iIter).isString()
											.stringValue());
								}
							}
						}
						x.add((String[]) xI.toArray(new String[0]));
					}
				}
				return (String[][]) x.toArray(new String[0][0]);
			}
		}
		if (t.compareToIgnoreCase("String[]") == 0) {
			JSONArray a = r.isArray();
			List<String> x = new ArrayList<String>();
			if (r.isArray() != null) {
				for (int iter = 0; iter < a.size(); iter++) {
					if (a.get(iter).isString() != null) {
						x.add(iter, a.get(iter).isString().stringValue());
					}
				}
			}
			return (String[]) x.toArray(new String[0]);
		}
		if (t.compareToIgnoreCase("String") == 0) {
			if (r.isString() != null) {
				return (String) r.isString().stringValue();
			}
		}
		if (t.compareToIgnoreCase("Integer") == 0) {
			if (r.isNumber() != null) {
				return (Integer) new Integer((int) r.isNumber().doubleValue());
			}
		}
		if (t.compareToIgnoreCase("Boolean") == 0) {
			if (r.isBoolean() != null) {
				return (Boolean) r.isBoolean().booleanValue();
			}
		}

		// If anything else bombs out...
		GWT.log("Could not parse type " + t, null);
		return null;
	}

	/**
	 * Console debugging for Firebug and other pieces.
	 * 
	 * @param st
	 *            String to echo to debug console.
	 */
	@SuppressWarnings("unused")
	private static native void debug(String st)/*-{
															if (typeof console !=  "undefined") console.debug (st);
															}-*/;

}
