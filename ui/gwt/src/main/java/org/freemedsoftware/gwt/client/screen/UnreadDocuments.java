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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class UnreadDocuments extends ScreenInterface {

	protected CustomTable wDocuments = null;

	protected Integer currentId = new Integer(0);

	public final static String moduleName = "UnfiledDocuments";

	protected HashMap<String, String>[] store = null;

	private static List<UnreadDocuments> unreadDocumentsList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static UnreadDocuments getInstance(){
		UnreadDocuments unreadDocuments=null; 
		
		if(unreadDocumentsList==null)
			unreadDocumentsList=new ArrayList<UnreadDocuments>();
		if(unreadDocumentsList.size()<AppConstants.MAX_UNREAD_TABS)//creates & returns new next instance of unreadDocuments
			unreadDocumentsList.add(unreadDocuments=new UnreadDocuments());
		else //returns last instance of unreadDocuments from list 
			unreadDocuments = unreadDocumentsList.get(AppConstants.MAX_UNREAD_TABS-1);
		return unreadDocuments;
	}

	public static boolean removeInstance(UnreadDocuments unreadDocuments){
		return unreadDocumentsList.remove(unreadDocuments);
	}
	
	public UnreadDocuments() {
		super(moduleName);
		final HorizontalPanel mainHorizontalPanel = new HorizontalPanel();
		initWidget(mainHorizontalPanel);
		mainHorizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		mainHorizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		wDocuments = new CustomTable();
		verticalPanel.add(wDocuments);
		wDocuments.setIndexName("id");
		wDocuments.addColumn("Date", "urfdate_mdy");
		wDocuments.addColumn("Patient", "patient");
		wDocuments.addColumn("Note", "urfnote");
		wDocuments.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				// Import current id
				try {
//					currentId = Integer.parseInt(data.get("id"));
				} catch (Exception ex) {
					GWT.log("Exception", ex);
				} finally {
					// Populate

				}
			}
		});
		wDocuments.setWidth("100%");

	
		// Last thing is to initialize, otherwise we're going to get some
		// NullPointerException errors
		if(canRead)
			loadData();
	}


	/**
	 * Load table entries and reset form.
	 */
	@SuppressWarnings("unchecked")
	protected void loadData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> results = new ArrayList<HashMap<String, String>>();
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("id", "1");
				item.put("urfdate_mdy", "2008-08-10");
				item.put("patient", "abc III, def");
				item.put("urfnote", "test note 123.");
				results.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("id", "2");
				item.put("urfdate_mdy", "2008-08-10");
				item.put("patient", "xyz III, uvw");
				item.put("urfnote", "Test note xyz.");
				results.add(item);
			}
			wDocuments.loadData(results
					.toArray((HashMap<String, String>[]) new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			wDocuments.showloading(true);
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.UnreadDocuments.GetAll",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {
									store = r;
									wDocuments.loadData(r);
								}
							} else {
								wDocuments.showloading(false);
							}
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// TODO GWT STUFF
		}
	}
	/**
	 * Perform form validation.
	 * 
	 * @return Successful form validation status.
	 */
	protected boolean validateForm() {
		return true;
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}

}
