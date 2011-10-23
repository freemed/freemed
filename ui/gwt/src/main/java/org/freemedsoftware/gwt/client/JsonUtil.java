/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2011 FreeMED Software Foundation
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
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.json.client.JSONString;
import com.google.gwt.json.client.JSONValue;

public class JsonUtil {

	/**
	 * Create JSON adaptation of objects.
	 * 
	 * @param o
	 * @return JSON formatted string
	 */
	@SuppressWarnings({ "unchecked", "rawtypes" })
	public static synchronized String jsonify(Object o) {
		if (o != null) {

			if (o instanceof HashMap
					&& (((HashMap<String, HashMap<String, String>>) o) != null)) {
				try {
					JSONObject out = new JSONObject();
					HashMap<String, HashMap<String, String>> ng = (HashMap<String, HashMap<String, String>>) o;
					Iterator<String> iter = ng.keySet().iterator();
					while (iter.hasNext()) {
						String key = iter.next();
						HashMap<String, String> ngInner = ng.get(key);
						Iterator<String> iterInner = ngInner.keySet()
								.iterator();
						JSONObject inner = new JSONObject();
						while (iterInner.hasNext()) {
							String keyInner = iterInner.next();
							inner.put(keyInner, new JSONString(ngInner
									.get(keyInner)));
						}
						out.put(key, inner);

					}
					return out.toString();
				} catch (ClassCastException e) {
					e.printStackTrace();
				} catch (Exception ex) {
					JsonUtil.debug(ex.getMessage());
				}
			}
			if (o instanceof HashMap
					&& (((HashMap<String, HashMap<String, Integer>>) o) != null)) {
				try {
					JSONObject out = new JSONObject();
					HashMap<String, HashMap<String, Integer>> ng = (HashMap<String, HashMap<String, Integer>>) o;
					Iterator<String> iter = ng.keySet().iterator();
					while (iter.hasNext()) {
						String key = iter.next();
						HashMap<String, Integer> ngInner = ng.get(key);
						Iterator<String> iterInner = ngInner.keySet()
								.iterator();
						JSONObject inner = new JSONObject();
						while (iterInner.hasNext()) {
							String keyInner = iterInner.next();
							inner.put(keyInner, new JSONNumber(ngInner
									.get(keyInner)));
						}
						out.put(key, inner);

					}
					return out.toString();
				} catch (ClassCastException e) {
					e.printStackTrace();
				} catch (Exception ex) {
					JsonUtil.debug(ex.getMessage());
				}
			}
			if (o instanceof HashMap && (((HashMap<String, String>) o) != null)) {
				try {
					JSONObject out = new JSONObject();
					HashMap<String, String> ng = (HashMap<String, String>) o;
					Iterator<String> iter = ng.keySet().iterator();
					while (iter.hasNext()) {
						String key = iter.next();
						out.put(key, new JSONString(ng.get(key)));
					}
					return out.toString();
				} catch (ClassCastException e) {
					e.printStackTrace();
				} catch (Exception ex) {
					JsonUtil.debug(ex.getMessage());
				}
			}

			if (o instanceof HashMap
					&& (((HashMap<String, String[]>) o) != null)) {
				try {
					JSONObject out = new JSONObject();
					HashMap<String, String[]> ng = (HashMap<String, String[]>) o;
					Iterator<String> iter = ng.keySet().iterator();
					while (iter.hasNext()) {
						String key = iter.next();
						String[] temparray = ng.get(key);
						JSONArray jsonArray = new JSONArray();
						for (int index = 0; index < temparray.length; index++) {
							jsonArray.set(index, new JSONString(
									temparray[index]));
						}
						out.put(key, jsonArray);
					}
					return out.toString();
				} catch (ClassCastException e) {
					e.printStackTrace();
				} catch (Exception ex) {
					JsonUtil.debug(ex.getMessage());
				}
			}

			if (o instanceof HashMap && (((HashMap<String, List>) o) != null)) {
				try {
					JSONObject out = new JSONObject();
					HashMap<String, List> ng = (HashMap<String, List>) o;
					Iterator<String> iter = ng.keySet().iterator();
					while (iter.hasNext()) {
						String key = iter.next();
						Iterator<String> iterator = ng.get(key).iterator();
						JSONArray jsonArray = new JSONArray();
						for (int index = 0; iterator.hasNext(); index++) {
							String aa = iterator.next();
							jsonArray.set(index, new JSONString(aa));
						}
						out.put(key, jsonArray);
					}
					return out.toString();
				} catch (ClassCastException e) {
					e.printStackTrace();
				} catch (Exception ex) {
					JsonUtil.debug(ex.getMessage());
				}
			}

			if (o instanceof HashMap[]
					&& (((HashMap<String, String>[]) o) != null)) {
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
			if (o instanceof Long) {
				return new JSONNumber((Long) o).toString();
			}
			if (o instanceof Integer) {
				return new JSONNumber((Integer) o).toString();
			}
			if (o instanceof String) {
				return new JSONString((String) o).toString();
			}
			if (o instanceof String[] && (((String[]) o) != null)) {
				JSONArray out = new JSONArray();
				for (int iter = 0; iter < ((String[]) o).length; iter++) {
					out.set(iter, new JSONString(((String[]) o)[iter]));
				}
				return out.toString();
			}
			if (o instanceof Integer[] && (((Integer[]) o) != null)) {
				JSONArray out = new JSONArray();
				for (int iter = 0; iter < ((Integer[]) o).length; iter++) {
					out.set(iter, new JSONNumber(((Integer[]) o)[iter]));
				}
				return out.toString();
			}
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
	public static synchronized Object shoehornJson(String r, String t) {
		return shoehornJson(JSONParser.parseStrict(r), t);
	}

	@SuppressWarnings({ "unchecked", "rawtypes" })
	public static synchronized Object shoehornJson(JSONValue r, String t) {
		if (r == null || r.toString().equals("null"))
			return null;
		if (t.equals("HashMap<String,HashMap<String,String>[]>")) {
			HashMap<String, HashMap<String, String>[]> oResult = new HashMap<String, HashMap<String, String>[]>();
			JSONObject oA = r.isObject();
			if (oA != null) {
				Iterator<String> outerIter = oA.keySet().iterator();
				while (outerIter.hasNext()) {
					String innerKey = outerIter.next();
					List<HashMap<?, ?>> result = new ArrayList<HashMap<?, ?>>();
					JSONArray a = oA.get(innerKey).isArray();
					for (int oIter = 0; oIter < a.size(); oIter++) {
						HashMap<String, String> item = new HashMap<String, String>();
						JSONObject obj = a.get(oIter).isObject();
						Iterator<String> iter = obj.keySet().iterator();
						while (iter.hasNext()) {
							String k = iter.next();
							if (obj.get(k).isString() != null) {
								item
										.put(k, obj.get(k).isString()
												.stringValue());
							}
						}
						result.add(oIter, item);
					}
					oResult.put(innerKey, (HashMap<String, String>[]) result
							.toArray(new HashMap<?, ?>[0]));
				}
			}
			return (HashMap<String, HashMap<String, String>[]>) oResult;
		}
		if (t.equals("HashMap<String,String>[]")) {
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
		if (t.equals("HashMap<String,Object>[]")) {
			List<HashMap<?, ?>> result = new ArrayList<HashMap<?, ?>>();
			JSONArray a = r.isArray();
			for (int oIter = 0; oIter < a.size(); oIter++) {
				HashMap<String, Object> item = new HashMap<String, Object>();
				JSONObject obj = a.get(oIter).isObject();
				Iterator<String> iter = obj.keySet().iterator();
				while (iter.hasNext()) {
					String k = iter.next();
					if (obj.get(k).isString() != null) {
						item.put(k, obj.get(k));
					}
				}
				result.add(oIter, item);
			}
			return (HashMap<String, String>[]) result
					.toArray(new HashMap<?, ?>[0]);
		}
		if (t.equals("HashMap<String,String>[][]")) {
			List<HashMap<?, ?>[]> result = new ArrayList<HashMap<?, ?>[]>();
			JSONArray oArray = r.isArray();
			for (int wayOuterIter = 0; wayOuterIter < oArray.size(); wayOuterIter++) {
				List<HashMap<?, ?>> innerResult = new ArrayList<HashMap<?, ?>>();
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
					innerResult.add(oIter, item);
				}
				result.add(wayOuterIter, innerResult
						.toArray(new HashMap<?, ?>[0]));
			}
			return (HashMap<String, String>[][]) result
					.toArray(new HashMap<?, ?>[0][0]);
		}
		if (t.equals("HashMap<String,String>")) {
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
		if (t.equals("HashMap<String,Object>")) {
			JSONObject obj = r.isObject();
			HashMap<String, Object> result = new HashMap<String, Object>();
			Iterator<String> iter = obj.keySet().iterator();
			while (iter.hasNext()) {
				String k = iter.next();
				if (obj.get(k) != null) {
					result.put(k, obj.get(k));
				}
			}
			return (HashMap<String, Object>) result;
		}
		if (t.equals("HashMap<String,HashMap<String,String>>")) {
			HashMap<String, HashMap<String, String>> oResult = new HashMap<String, HashMap<String, String>>();
			JSONObject oA = r.isObject();
			if (oA != null) {
				Iterator<String> outerIter = oA.keySet().iterator();
				while (outerIter.hasNext()) {
					String innerKey = outerIter.next();
					HashMap<String, String> item = new HashMap<String, String>();
					JSONObject obj = oA.get(innerKey).isObject();
					Iterator<String> iter = obj.keySet().iterator();
					while (iter.hasNext()) {
						String k = iter.next();
						if (obj.get(k).isString() != null) {
							item.put(k, obj.get(k).isString().stringValue());
						}
					}
					oResult.put(innerKey, (HashMap<String, String>) item);
				}
			}
			return (HashMap<String, HashMap<String, String>>) oResult;
		}
		if (t.equals("HashMap<String,HashMap<String,Integer>>")) {
			HashMap<String, HashMap<String, Integer>> oResult = new HashMap<String, HashMap<String, Integer>>();
			JSONObject oA = r.isObject();
			if (oA != null) {
				Iterator<String> outerIter = oA.keySet().iterator();
				while (outerIter.hasNext()) {
					String innerKey = outerIter.next();
					HashMap<String, Integer> item = new HashMap<String, Integer>();
					JSONObject obj = oA.get(innerKey).isObject();
					Iterator<String> iter = obj.keySet().iterator();
					while (iter.hasNext()) {
						String k = iter.next();
						if (obj.get(k).isNumber() != null) {
							item.put(k, (int) obj.get(k).isNumber()
									.doubleValue());
						}
					}
					oResult.put(innerKey, (HashMap<String, Integer>) item);
				}
			}
			return (HashMap<String, HashMap<String, Integer>>) oResult;
		}
		if (t.equals("HashMap<Integer,String>")) {
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
		if (t.equals("HashMap<String,Integer>")) {
			JSONObject obj = r.isObject();
			HashMap<String, Integer> result = new HashMap<String, Integer>();
			Iterator<String> iter = obj.keySet().iterator();
			while (iter.hasNext()) {
				String k = iter.next();
				if (obj.get(k).isNumber() != null) {
					result.put(k, (int) obj.get(k).isNumber().doubleValue());
				}
			}
			return (HashMap<String, Integer>) result;
		}
		if (t.equals("String[][]")) {
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
								} else if (inner.get(iIter).isNumber() != null) {
									xI.add(iIter, inner.get(iIter).isNumber()
											.toString());
								}
							}
						}
						x.add((String[]) xI.toArray(new String[0]));
					}
				}
				return (String[][]) x.toArray(new String[0][0]);
			}
		}
		if (t.equals("String[]")) {
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
		if (t.compareToIgnoreCase("HashMap<String,String[]>") == 0) {
			HashMap<String, String[]> oResult = new HashMap<String, String[]>();
			JSONObject oA = r.isObject();
			if (oA != null) {
				Iterator<String> outerIter = oA.keySet().iterator();
				while (outerIter.hasNext()) {
					String innerKey = outerIter.next();
					JSONArray a = oA.get(innerKey).isArray();
					String[] x = new String[a.size()];
					if (a.isArray() != null) {
						for (int iter = 0; iter < a.size(); iter++) {
							if (a.get(iter).isString() != null) {
								// x.add(iter,
								// a.get(iter).isString().stringValue());
								x[iter] = a.get(iter).isString().stringValue();
							}
						}
					}
					oResult.put(innerKey, x);
				}
			}
			return (HashMap<String, String[]>) oResult;
		}

		if (t.compareToIgnoreCase("HashMap<String,List>") == 0) {
			HashMap<String, List> oResult = new HashMap<String, List>();
			JSONObject oA = r.isObject();
			if (oA != null) {
				Iterator<String> outerIter = oA.keySet().iterator();
				while (outerIter.hasNext()) {
					String innerKey = outerIter.next();
					JSONArray a = oA.get(innerKey).isArray();
					List x = new ArrayList();
					if (a.isArray() != null) {
						for (int iter = 0; iter < a.size(); iter++) {
							if (a.get(iter).isString() != null) {
								// x.add(iter,
								// a.get(iter).isString().stringValue());
								x.add(a.get(iter).isString().stringValue());
							}
						}
					}
					oResult.put(innerKey, x);
				}
			}
			return (HashMap<String, List>) oResult;
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
		if (t.compareToIgnoreCase("Float") == 0) {
			if (r.isNumber() != null) {
				return (Float) new Float((float) r.isNumber().doubleValue());
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
	public static native void debug(String st)/*-{
		if (typeof console !=  "undefined") console.debug (st);
	}-*/;

}
