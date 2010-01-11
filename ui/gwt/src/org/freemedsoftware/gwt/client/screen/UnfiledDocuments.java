/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Module.UnfiledDocumentsAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
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
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class UnfiledDocuments extends ScreenInterface {

	protected CustomTable wDocuments = null;

	protected ListBox wRotate = null;

	protected TextBox wNote = null;

	protected PatientWidget wPatient = null;

	protected SupportModuleWidget wProvider = null, wCategory = null;

	protected CustomDatePicker wDate = null;

	protected Integer currentId = new Integer(0);

	protected HorizontalPanel horizontalPanel;

	protected FlexTable flexTable;

	protected DjvuViewer djvuViewer;

	protected HashMap<String, String>[] store = null;

	private static List<UnfiledDocuments> unfiledDocumentsList=null;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static UnfiledDocuments getInstance(){
		UnfiledDocuments unfiledDocuments=null; 
		
		if(unfiledDocumentsList==null)
			unfiledDocumentsList=new ArrayList<UnfiledDocuments>();
		if(unfiledDocumentsList.size()<AppConstants.MAX_UNFILLED_TABS)//creates & returns new next instance of UnfiledDocuments
			unfiledDocumentsList.add(unfiledDocuments=new UnfiledDocuments());
		else //returns last instance of UnfiledDocuments from list 
			unfiledDocuments = unfiledDocumentsList.get(AppConstants.MAX_UNFILLED_TABS-1);
		return unfiledDocuments;
	}

	public static boolean removeInstance(UnfiledDocuments unfiledDocuments){
		return unfiledDocumentsList.remove(unfiledDocuments);
	}
	
	public UnfiledDocuments() {
		final HorizontalPanel mainHorizontalPanel = new HorizontalPanel();
		initWidget(mainHorizontalPanel);
		mainHorizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		mainHorizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		wDocuments = new CustomTable();
		verticalPanel.add(wDocuments);
		wDocuments.setIndexName("id");
		wDocuments.addColumn("Date", "uffdate");
		wDocuments.addColumn("Filename", "ufffilename");
		wDocuments.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				// Import current id
				try {
					currentId = Integer.parseInt(data.get("id"));
				} catch (Exception ex) {
					GWT.log("Exception", ex);
				} finally {
					// Populate
					String pDate = data.get("uffdate");
					Calendar thisCal = new GregorianCalendar();
					thisCal.set(Calendar.YEAR, Integer.parseInt(pDate
							.substring(0, 4)));
					thisCal.set(Calendar.MONTH, Integer.parseInt(pDate
							.substring(5, 6)) - 1);
					thisCal.set(Calendar.DAY_OF_MONTH, Integer.parseInt(pDate
							.substring(8, 9)));
					wDate.setValue(thisCal.getTime());

					// Show the form
					flexTable.setVisible(true);
					horizontalPanel.setVisible(true);

					// Show the image in the viewer
					djvuViewer.setInternalId(currentId);
					try {
						djvuViewer.loadPage(1);
					} catch (Exception ex) {
						JsonUtil.debug(ex.toString());
					}
					djvuViewer.setVisible(true);
				}
			}
		});
		wDocuments.setWidth("100%");

		flexTable = new FlexTable();
		verticalPanel.add(flexTable);
		flexTable.setWidth("100%");
		flexTable.setVisible(false);

		final Label dateLabel = new Label("Date : ");
		flexTable.setWidget(0, 0, dateLabel);

		wDate = new CustomDatePicker();
		wDate.setValue(new Date());
		flexTable.setWidget(0, 1, wDate);

		final Label patientLabel = new Label("Patient : ");
		flexTable.setWidget(1, 0, patientLabel);

		wPatient = new PatientWidget();
		flexTable.setWidget(1, 1, wPatient);

		final Label providerLabel = new Label("Provider : ");
		flexTable.setWidget(2, 0, providerLabel);

		wProvider = new SupportModuleWidget();
		wProvider.setModuleName("ProviderModule");
		flexTable.setWidget(2, 1, wProvider);

		final Label noteLabel = new Label("Note : ");
		flexTable.setWidget(3, 0, noteLabel);

		wNote = new TextBox();
		flexTable.setWidget(3, 1, wNote);
		wNote.setWidth("100%");

		final Label categoryLabel = new Label("Category : ");
		flexTable.setWidget(4, 0, categoryLabel);

		wCategory = new SupportModuleWidget();
		wCategory.setModuleName("DocumentCategory");
		flexTable.setWidget(4, 1, wCategory);

		final Label rotateLabel = new Label("Rotate : ");
		flexTable.setWidget(5, 0, rotateLabel);

		wRotate = new ListBox();
		flexTable.setWidget(5, 1, wRotate);
		wRotate.addItem("No rotation", "0");
		wRotate.addItem("Rotate left", "270");
		wRotate.addItem("Rotate right", "90");
		wRotate.addItem("Flip", "180");
		wRotate.setVisibleItemCount(1);

		horizontalPanel = new HorizontalPanel();
		horizontalPanel.setVisible(false);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		verticalPanel.add(horizontalPanel);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		horizontalPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_BOTTOM);

		final PushButton sendToProviderButton = new PushButton();
		sendToProviderButton.setStylePrimaryName("freemed-PushButton");
		sendToProviderButton.setHTML("Send to Provider");
		sendToProviderButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (validateForm()) {
					sendToProvider();
				}
			}
		});
		horizontalPanel.add(sendToProviderButton);

		final PushButton fileDirectlyButton = new PushButton();
		fileDirectlyButton.setHTML("File Directly");
		fileDirectlyButton.setStylePrimaryName("freemed-PushButton");
		fileDirectlyButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (validateForm()) {
					fileDirectly();
				}
			}
		});
		horizontalPanel.add(fileDirectlyButton);

		djvuViewer = new DjvuViewer();
		djvuViewer.setType(DjvuViewer.UNFILED_DOCUMENTS);
		mainHorizontalPanel.add(djvuViewer);
		djvuViewer.setVisible(false);
		djvuViewer.setSize("100%", "100%");

		// Last thing is to initialize, otherwise we're going to get some
		// NullPointerException errors
		loadData();
	}

	protected void fileDirectly() {
		HashMap<String, String> p = new HashMap<String, String>();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) wCategory.getValue().toString());
		p.put((String) "physician", (String) wProvider.getValue().toString());
		p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "1");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			CurrentState.getToaster().addItem("UnfiledDocuments",
					"Processed unfiled document.", Toaster.TOASTER_INFO);
			loadData();
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "UnfiledDocuments", JsonUtil.jsonify(p) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleModifyMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem("UnfiledDocuments",
								"Failed to file document.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != null) {
									CurrentState.getToaster().addItem(
											"UnfiledDocuments",
											"Processed unfiled document.",
											Toaster.TOASTER_INFO);
									loadData();
								}
							} else {
								CurrentState.getToaster().addItem(
										"UnfiledDocuments",
										"Failed to file document.",
										Toaster.TOASTER_ERROR);
							}
						}
					}
				});
			} catch (RequestException e) {
				CurrentState.getToaster().addItem("UnfiledDocuments",
						"Failed to file document.", Toaster.TOASTER_ERROR);
			}
		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer o) {
							CurrentState.getToaster().addItem(
									"UnfiledDocuments",
									"Processed unfiled document.");
							loadData();
						}

						public void onFailure(Throwable t) {
							CurrentState.getToaster().addItem(
									"UnfiledDocuments",
									"Failed to file document.",
									Toaster.TOASTER_ERROR);
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Load table entries and reset form.
	 */
	@SuppressWarnings("unchecked")
	protected void loadData() {
		djvuViewer.setVisible(false);
		flexTable.setVisible(false);
		horizontalPanel.setVisible(false);
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> results = new ArrayList<HashMap<String, String>>();
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("id", "1");
				item.put("uffdate", "2008-08-10");
				item.put("ufffilename", "testFile1.pdf");
				results.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("id", "2");
				item.put("uffdate", "2008-08-25");
				item.put("ufffilename", "testFile2.pdf");
				results.add(item);
			}
			wDocuments.loadData(results
					.toArray((HashMap<String, String>[]) new HashMap<?, ?>[0]));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.UnfiledDocuments.GetAll",
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
							}
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			getDocumentsProxy().GetAll(
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] res) {
							store = res;
							wDocuments.loadData(res);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	protected void sendToProvider() {
		HashMap<String, String> p = new HashMap<String, String>();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) "");
		p.put((String) "physician", (String) wProvider.getValue().toString());
		p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "0");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			CurrentState.getToaster().addItem("UnfiledDocuments",
					"Processed unfiled document.", Toaster.TOASTER_INFO);
			loadData();
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "UnfiledDocuments", JsonUtil.jsonify(p) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleModifyMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						CurrentState.getToaster().addItem("UnfiledDocuments",
								"Failed to file document.",
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parse(response.getText()),
										"Integer");
								if (r != null) {
									CurrentState.getToaster().addItem(
											"UnfiledDocuments",
											"Sent to provider.",
											Toaster.TOASTER_INFO);
								}
							} else {
								CurrentState.getToaster().addItem(
										"UnfiledDocuments",
										"Failed to file document.",
										Toaster.TOASTER_ERROR);
							}
						}
					}
				});
			} catch (RequestException e) {
				CurrentState.getToaster().addItem("UnfiledDocuments",
						"Failed to file document.", Toaster.TOASTER_ERROR);
			}
		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer o) {
							CurrentState.getToaster().addItem(
									"UnfiledDocuments", "Sent to provider.",
									Toaster.TOASTER_INFO);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
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

	protected UnfiledDocumentsAsync getDocumentsProxy() {
		UnfiledDocumentsAsync p = null;
		try {
			p = (UnfiledDocumentsAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Module.UnfiledDocuments");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}

	protected ModuleInterfaceAsync getModuleProxy() {
		ModuleInterfaceAsync p = null;
		try {
			p = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}
	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		removeInstance(this);
	}
}
