/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.DocumentScreen;
import org.freemedsoftware.gwt.client.screen.UnfiledDocuments;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class DocumentBox extends WidgetInterface {

	public final static String moduleName = "UnfiledDocuments";
	
	protected Label documentsCountLabel = new Label(
			"You have no unfiled Documents!");

	protected HashMap<String, String>[] data = null;

	protected CustomTable wDocuments = new CustomTable();

	protected DocumentScreen documentScreen = null;
    private PushButton showDocumentsButton;

	protected UnfiledDocuments unfiledDocs;
	public DocumentBox() {
		super(moduleName);
		SimplePanel sPanel = new SimplePanel();
		sPanel.setWidth("100%");
		initWidget(sPanel);
		sPanel.setStyleName(AppConstants.STYLE_BUTTON_WIDGETS_CONTAINER);
		final VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setWidth("100%");
		sPanel.setWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();

//		final PushButton showDocumentsButton = new PushButton("", "");
//		showDocumentsButton.setStyleName(AppConstants.STYLE_BUTTON_SIMPLE);
//		showDocumentsButton.getUpFace().setImage(
//				new Image("resources/images/unfiled.16x16.png"));
//		showDocumentsButton.getDownFace().setImage(
//				new Image("resources/images/unfiled.16x16.png"));
		Label headerLabel = new Label("Unfiled Documents");
		headerLabel.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		horizontalPanel.add(headerLabel);
		verticalPanel.add(horizontalPanel);
//		horizontalPanel.add(showDocumentsButton);
		verticalPanel.add(wDocuments);

//		showDocumentsButton.addClickHandler(new ClickHandler() {
//			@Override
//			public void onClick(ClickEvent evt) {
//				if (wDocuments.isVisible()) {
//					wDocuments.setVisible(false);
//				} else {
//					wDocuments.setVisible(true);
//				}
//			}
//		});

//		retrieveData();

		wDocuments.setSize("100%", "100%");
		wDocuments.addColumn("Date", "uffdate"); // col 0
		wDocuments.addColumn("Filename", "ufffilename"); // col 1
		wDocuments.setIndexName("id");
		wDocuments.setMaximumRows(7);
		if(true){
			wDocuments.setTableRowClickHandler(new TableRowClickHandler() {
				@Override
				public void handleRowClick(HashMap<String, String> data, int col) {
					//final Integer uffId = Integer.parseInt(data.get("id"));
					unfiledDocs = UnfiledDocuments.getInstance();
					unfiledDocs.setSelectedDocument(data);
					Util.spawnTab(AppConstants.UNFILED
							+ " Documents", UnfiledDocuments.getInstance());
					//Util.spawnTab("File Document", documentScreen);
				}
			});
		}
		// Collapsed view
//		wDocuments.setVisible(false);
//		horizontalPanel.add(documentsCountLabel);
	}

	public Widget getDefaultIcon(){
		if(showDocumentsButton==null){
			showDocumentsButton = new PushButton("", "");
			showDocumentsButton.setStyleName(AppConstants.STYLE_BUTTON_SIMPLE);
			showDocumentsButton.getUpFace().setImage(
					new Image("resources/images/unfiled.16x16.png"));
			showDocumentsButton.getDownFace().setImage(
					new Image("resources/images/unfiled.16x16.png"));
	
			showDocumentsButton.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent evt) {
					if (wDocuments.isVisible()) {
						wDocuments.setVisible(false);
					} else {
						wDocuments.setVisible(true);
					}
				}
			});
		}
		return showDocumentsButton;
	}

	public void clearView(){
		wDocuments.clearData();
	}
	
	public void retrieveData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Runs in STUBBED MODE => Feed with Sample Data
			HashMap<String, String>[] sampleData = getSampleData();
			loadData(sampleData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			wDocuments.showloading(true);
			// Use JSON-RPC to retrieve the data
			String[] documentparams = {};

			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.UnfiledDocuments.GetAll",
											documentparams)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String, String>[] data = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null) {
								loadData(data);
							}else wDocuments.showloading(false);
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}

	}

	@SuppressWarnings("unchecked")
	public HashMap<String, String>[] getSampleData() {
		List<HashMap<String, String>> m = new ArrayList<HashMap<String, String>>();

		HashMap<String, String> a = new HashMap<String, String>();
		a.put("id", "1");
		a.put("uffdate", "2009-02-06");
		a.put("ufffilename", "filename1.pdf");
		m.add(a);

		HashMap<String, String> b = new HashMap<String, String>();
		b.put("id", "2");
		b.put("uffdate", "2009-02-06");
		b.put("ufffilename", "filename2.tiff");
		m.add(b);

		return (HashMap<String, String>[]) m.toArray(new HashMap<?, ?>[0]);
	}

	public void loadData(HashMap<String, String>[] d) {
		wDocuments.clearData();
		wDocuments.loadData(d);
		setData(d);
		setCounter();
	}

	public void setData(HashMap<String, String>[] d) {
		data = d;
	}

	public void setCounter() {
		Integer len = data.length;
		if (len != 0) {
			documentsCountLabel.setText("You have " + Integer.toString(len)
					+ " unfiled Documents!");
		}
	}

}
