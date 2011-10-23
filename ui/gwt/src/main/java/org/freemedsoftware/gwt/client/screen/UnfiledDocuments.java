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

package org.freemedsoftware.gwt.client.screen;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.MessagesAsync;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Module.UnfiledDocumentsAsync;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;
import org.freemedsoftware.gwt.client.widget.DocumentThumbnailsWidget;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;
import org.freemedsoftware.gwt.client.widget.UserMultipleChoiceWidget;

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
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class UnfiledDocuments extends ScreenInterface {

	protected CustomTable wDocuments = null;

	protected ListBox wRotate = null;
	
	protected UserMultipleChoiceWidget users;
	
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

	protected CheckBox cbRemoveFirstPage;

	protected TextBox faxConfirmationNumber;

	protected VerticalPanel batchSplitVp;

	protected HorizontalPanel mainHorizontalPanel;
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	
	public final static String moduleName = "UnfiledDocuments";
	
	public static UnfiledDocuments getInstance(){
		UnfiledDocuments unfiledDocuments=null; 
		
		if(unfiledDocumentsList==null)
			unfiledDocumentsList=new ArrayList<UnfiledDocuments>();
		//creates & returns new next instance of UnfiledDocuments
		if(unfiledDocumentsList.size()<AppConstants.MAX_UNFILED_TABS)
			unfiledDocumentsList.add(unfiledDocuments=new UnfiledDocuments());
		else //returns last instance of UnfiledDocuments from list 
			unfiledDocuments = unfiledDocumentsList.get(AppConstants.MAX_UNFILED_TABS-1);
		return unfiledDocuments;
	}

	public static boolean removeInstance(UnfiledDocuments unfiledDocuments){
		return unfiledDocumentsList.remove(unfiledDocuments);
	}
	
	public UnfiledDocuments() {
		super(moduleName);
		VerticalPanel parentVp=new VerticalPanel();
		parentVp.setSize("10o%", "100%");
		initWidget(parentVp);
		mainHorizontalPanel = new HorizontalPanel();
		parentVp.add(mainHorizontalPanel);
		
		mainHorizontalPanel.setSize("100%", "100%");
		
		batchSplitVp = new VerticalPanel();
		batchSplitVp.setSize("100%", "100%");
		batchSplitVp.setVisible(false);
		parentVp.add(batchSplitVp);
		final VerticalPanel verticalPanel = new VerticalPanel();
		mainHorizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		wDocuments = new CustomTable();
		verticalPanel.add(wDocuments);
		wDocuments.setIndexName("id");
		wDocuments.addColumn(_("Date"), "uffdate");
		wDocuments.addColumn(_("Filename"), "ufffilename");
		wDocuments.addColumn(_("Action"), "action");
		wDocuments
		.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(
						data);
				actionBar.applyPermissions(false, false, true,
						false, false);
				actionBar
						.setHandleCustomAction(new HandleCustomAction() {
							@Override
							public void handleAction(int id,
									HashMap<String, String> data,
									int action) {
								if (action == HandleCustomAction.DELETE) {
									try {
										String[] params = { ""+id };
										RequestBuilder builder = new RequestBuilder(
												RequestBuilder.POST,
												URL
														.encode(Util
																.getJsonRequest(
																		"org.freemedsoftware.module.UnfiledDocuments.del",
																		params)));
										try {
											builder.sendRequest(null, new RequestCallback() {
												public void onError(Request request, Throwable ex) {
													Window.alert(ex.toString());
												}

												public void onResponseReceived(Request request,
														Response response) {

													if (Util.checkValidSessionResponse(response.getText())) {
														if (200 == response.getStatusCode()) {
															try {
																Boolean result = (Boolean) JsonUtil
																		.shoehornJson(JSONParser
																				.parseStrict(response.getText()),
																				"Boolean");
																if(result){
																	Util.showInfoMsg("UnfiledDocuments",
																	_("Document successfully deleted."));
																	loadData();
																}
															} catch (Exception e) {
																Util.showErrorMsg("UnfiledDocuments",
																		_("Document failed to delete."));
															}
														} else {
															Util.showErrorMsg("UnfiledDocuments",
															_("Document failed to delete."));
														}
													}
												}
											});
										} catch (RequestException e) {
											Util.showErrorMsg("UnfiledDocuments",
											_("Document failed to delete."));
										}
									} catch (Exception e) {
										Util.showErrorMsg("UnfiledDocuments",
										_("Document failed to delete"));
									}
								} 
							}
						});

				// Push value back to table
				return actionBar;
			}
		});
		wDocuments.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				if(col==0 || col ==1)
				{
					// Import current id
					try {
						currentId = Integer.parseInt(data.get("id"));
					} catch (Exception ex) {
						GWT.log("Exception", ex);
					} finally {
						if(canWrite){
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
						}
						if(canRead){
							// Show the image in the viewer
							djvuViewer.setInternalId(currentId);
							/*
							try {
								djvuViewer.loadPage(1);
							} catch (Exception ex) {
								JsonUtil.debug(ex.toString());
							}
							djvuViewer.setVisible(true);
							*/
						}
					}
				}
			}
		});
		wDocuments.setWidth("100%");

		flexTable = new FlexTable();
		verticalPanel.add(flexTable);
		flexTable.setWidth("100%");
		flexTable.setVisible(false);

		final Label dateLabel = new Label(_("Date") + " : ");
		flexTable.setWidget(0, 0, dateLabel);

		wDate = new CustomDatePicker();
		wDate.setValue(new Date());
		flexTable.setWidget(0, 1, wDate);

		final Label patientLabel = new Label(_("Patient") + " : ");
		flexTable.setWidget(1, 0, patientLabel);

		wPatient = new PatientWidget();
		flexTable.setWidget(1, 1, wPatient);

		final Label providerLabel = new Label(_("Provider") + " : ");
		flexTable.setWidget(2, 0, providerLabel);

		wProvider = new SupportModuleWidget();
		wProvider.setModuleName("ProviderModule");
		flexTable.setWidget(2, 1, wProvider);

		final Label noteLabel = new Label(_("Note") + " : ");
		flexTable.setWidget(3, 0, noteLabel);

		wNote = new TextBox();
		flexTable.setWidget(3, 1, wNote);
		wNote.setWidth("100%");
		
		final Label notifyLabel = new Label(_("Notify") + " : ");
		flexTable.setWidget(4, 0, notifyLabel);

		users = new UserMultipleChoiceWidget();
		flexTable.setWidget(4, 1, users);
		//wNote.setWidth("100%");
		
		final Label categoryLabel = new Label(_("Category") + " : ");
		flexTable.setWidget(5, 0, categoryLabel);

		wCategory = new SupportModuleWidget();
		wCategory.setModuleName("DocumentCategory");
		flexTable.setWidget(5, 1, wCategory);
		
		final Label faxNumberLabel = new Label(_("Fax Confirmation Number") + " : ");
		flexTable.setWidget(6, 0, faxNumberLabel);
		
		faxConfirmationNumber = new TextBox();
		flexTable.setWidget(6, 1, faxConfirmationNumber);
		
		
		final Label rotateLabel = new Label(_("Rotate") + " : ");
		flexTable.setWidget(7, 0, rotateLabel);

		wRotate = new ListBox();
		flexTable.setWidget(7, 1, wRotate);
		wRotate.addItem(_("No rotation"), "0");
		wRotate.addItem(_("Rotate left"), "270");
		wRotate.addItem(_("Rotate right"), "90");
		wRotate.addItem(_("Flip"), "180");
		wRotate.setVisibleItemCount(1);
		
		cbRemoveFirstPage = new CheckBox(_("Remove First Page"));
		flexTable.setWidget(8, 0, cbRemoveFirstPage);
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
		sendToProviderButton.setHTML(_("Send to Provider"));
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
		fileDirectlyButton.setHTML(_("File Directly"));
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
		
		final PushButton splitBatchButton = new PushButton();
		splitBatchButton.setHTML(_("Split Batch"));
		splitBatchButton.setStylePrimaryName("freemed-PushButton");
		splitBatchButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				if (djvuViewer.getPageCount()>1) {

					final DocumentThumbnailsWidget dtw= new DocumentThumbnailsWidget(djvuViewer,new CustomRequestCallback() {
						@Override
						public void onError() {

						}
						@Override
						public void jsonifiedData(Object data) {
							if(data instanceof Integer) {
								loadData();								
							}
							else if(data instanceof Integer[]) {
								splitBatch((Integer[])data);
							}
							
						}
					});
					batchSplitVp.add(dtw);
					mainHorizontalPanel.setVisible(false);
					batchSplitVp.setVisible(true);
					
				}
				else if (djvuViewer.getPageCount()==1) {				
					Window.alert(_("Current document has only one page."));
				}
			}
		});
		horizontalPanel.add(splitBatchButton);
		djvuViewer = new DjvuViewer();
		djvuViewer.setType(DjvuViewer.UNFILED_DOCUMENTS);
		mainHorizontalPanel.add(djvuViewer);
		djvuViewer.setVisible(false);
		djvuViewer.setSize("100%", "100%");

		// Last thing is to initialize, otherwise we're going to get some
		// NullPointerException errors
		if(canRead)
			loadData();
	}
	
	public void setSelectedDocument(HashMap<String, String> data){
		currentId = Integer.parseInt(data.get("id"));
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
			JsonUtil.debug("Errorrrrrr");
		}
		djvuViewer.setVisible(true);
	}

	protected void fileDirectly() {
		HashMap<String, String> p = new HashMap<String, String>();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) wCategory.getValue().toString());
		p.put((String) "physician", (String) wProvider.getValue().toString());
		if(cbRemoveFirstPage.getValue())
			p.put((String) "withoutfirstpage", (String) "1");
		else
			p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "1");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "faxback", (String)faxConfirmationNumber.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if(users.getCommaSeparatedValues()!=null)
			p.put((String) "notify",  users.getCommaSeparatedValues());
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
								_("Failed to file document."),
								Toaster.TOASTER_ERROR);
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parseStrict(response.getText()),
										"Integer");
								if (r != null) {
									Util.showInfoMsg("UnfiledDocuments",
											_("Processed unfiled document."));
									loadData();
								}
							} else {
								Util.showErrorMsg("UnfiledDocuments",_("Failed to file document."));
							}
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("UnfiledDocuments",_("Failed to file document."));
			}
		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer o) {
							Util.showInfoMsg("UnfiledDocuments", _("Processed unfiled document."));
							loadData();
						}

						public void onFailure(Throwable t) {
							Util.showErrorMsg("UnfiledDocuments", _("Failed to file document."));
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
		batchSplitVp.setVisible(false);
		batchSplitVp.clear();
		djvuViewer.setVisible(false);
		flexTable.setVisible(false);
		horizontalPanel.setVisible(false);
		mainHorizontalPanel.setVisible(true);
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
			wDocuments.showloading(true);
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
										.shoehornJson(JSONParser.parseStrict(response
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
		if(cbRemoveFirstPage.getValue())
			p.put((String) "withoutfirstpage", (String) "1");
		else
			p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "0");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "faxback", (String)faxConfirmationNumber.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if(users.getCommaSeparatedValues()!=null)
			p.put((String) "notify",  users.getCommaSeparatedValues());
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
						Util.showErrorMsg("UnfiledDocuments", _("Failed to send document to provider."));
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								Integer r = (Integer) JsonUtil.shoehornJson(
										JSONParser.parseStrict(response.getText()),
										"Integer");
								if (r != null) {
									Util.showInfoMsg("UnfiledDocuments", _("Sent to provider."));
								}
							} else {
								Util.showErrorMsg("UnfiledDocuments", _("Failed to send document to provider."));
							}
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("UnfiledDocuments", _("Failed to send document to provider."));
			}
		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer o) {
							Util.showInfoMsg("UnfiledDocuments", _("Sent to provider."));
						}

						public void onFailure(Throwable t) {
							Util.showErrorMsg("UnfiledDocuments", _("Failed to send document to provider."));
						}
					});
		}
	}

	public void splitBatch(Integer [] pageNos)
	{
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { currentId.toString(),JsonUtil.jsonify(pageNos) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.UnfiledDocuments.batchSplit",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						Util.showErrorMsg("UnfiledDocuments", _("Document failed to split."));
					}

					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							Boolean r = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parseStrict(response.getText()),
									"Boolean");
							if (r != null && r) {
								Util.showInfoMsg("UnfiledDocuments", _("Document successfully split."));
								loadData();
							}
							else{
								Util.showErrorMsg("UnfiledDocuments", _("Document failed to split."));
							}
						} else {
							Util.showErrorMsg("UnfiledDocuments", _("Document failed to split."));
							JsonUtil.debug(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("UnfiledDocuments", _("Document failed to split."));
				JsonUtil.debug(e.toString());
			}

		} else {
			
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
	protected MessagesAsync getProxy() {
		MessagesAsync p = null;
		try {
			p = (MessagesAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.Messages");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		}
		return p;
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
		super.closeScreen();
		removeInstance(this);
	}
}
