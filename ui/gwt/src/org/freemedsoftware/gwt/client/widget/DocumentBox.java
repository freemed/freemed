/*
 * $Id$
 *
 * Authors:
 *      Philipp Meng	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
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
import org.freemedsoftware.gwt.client.screen.DocumentScreen;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class DocumentBox extends WidgetInterface {

	protected Label documentsCountLabel = new Label(
			"You have no unfiled Documents!");

	protected HashMap<String, String>[] data = null;

	protected CustomSortableTable wDocuments = new CustomSortableTable();

	protected DocumentScreen documentScreen = null;

	public DocumentBox() {
		SimplePanel sPanel = new SimplePanel();
		initWidget(sPanel);
		sPanel.setStyleName("freemed-WidgetContainer");
		final VerticalPanel verticalPanel = new VerticalPanel();

		sPanel.setWidget(verticalPanel);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();

		final PushButton showDocumentsButton = new PushButton("", "");
		showDocumentsButton.getUpFace().setImage(
				new Image("resources/images/unfiled.32x32.png"));
		showDocumentsButton.getDownFace().setImage(
				new Image("resources/images/unfiled.32x32.png"));

		verticalPanel.add(horizontalPanel);
		horizontalPanel.add(showDocumentsButton);
		verticalPanel.add(wDocuments);

		showDocumentsButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				if (wDocuments.isVisible()) {
					wDocuments.setVisible(false);
				} else {
					wDocuments.setVisible(true);
				}
			}
		});

		retrieveData();

		wDocuments.setSize("100%", "100%");
		wDocuments.addColumn("Date", "uffdate"); // col 0
		wDocuments.addColumn("Filename", "ufffilename"); // col 1
		wDocuments.setIndexName("id");

		documentScreen = new DocumentScreen();
		wDocuments.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents ste, int row, int col) {
				final Integer uffId = new Integer(wDocuments.getValueByRow(row));
				documentScreen.setData(uffId);
				Util.spawnTab("File Document", documentScreen);
			}

		});
		// Collapsed view
		wDocuments.setVisible(false);
		horizontalPanel.add(documentsCountLabel);
	}

	public void retrieveData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Runs in STUBBED MODE => Feed with Sample Data
			HashMap<String, String>[] sampleData = getSampleData();
			loadData(sampleData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
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
							}
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
		wDocuments.clear();
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
