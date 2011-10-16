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

package org.freemedsoftware.gwt.client.screen.patient;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;
import org.freemedsoftware.gwt.client.widget.ProviderWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FileUpload;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.FormPanel;
import com.google.gwt.user.client.ui.FormPanel.SubmitCompleteEvent;
import com.google.gwt.user.client.ui.FormPanel.SubmitCompleteHandler;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ScannedDocumentsEntryScreen extends PatientEntryScreenInterface {

	
	protected TabPanel tabPanel; //All forms container if tab view
	
	protected VerticalPanel containerVerticalPanel; //All forms container if single page view
	
	protected VerticalPanel containerScannedDocumentsForm;
	
	protected VerticalPanel containerScannedDocumentsListPanel;
	
	protected CustomTable containerScannedDocumentsTable;
	
	protected HashMap<String, Widget> containerScannedDocumentsFormFields = new HashMap<String, Widget>();//containerInitialForm Fields Container
		
	protected Integer scannedDocumentId = null;
	
	final protected String moduleName = "Scanned Documents";

	public final static	String SCANNED_DOCUMENT= "ScannedDocuments";
	
	protected String patientIdName = "patient";
	
	protected Label scannedDocumentsEntryLabel 	 = new Label("Add");
	
	protected Label scannedDocumentsListLabel 	 = new Label("List");
	
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	
	protected CustomButton wSubmit;
	protected CustomButton  wDelete;
	
	protected DjvuViewer djvuViewer;
	
	public ScannedDocumentsEntryScreen() {
		super(SCANNED_DOCUMENT);
		final VerticalPanel containerAllVerticalPanel = new VerticalPanel();
		initWidget(containerAllVerticalPanel);

		final HorizontalPanel tabViewPanel = new HorizontalPanel();
		final CheckBox tabView = new CheckBox();
		tabView.setText(_("Tab View"));
		tabView.setValue(true);
		tabViewPanel.add(tabView);
		
		tabView.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				switchView(tabView.getValue());
			}
		});
		
		containerAllVerticalPanel.add(tabViewPanel);
		
		tabPanel = new TabPanel();
		containerAllVerticalPanel.add(tabPanel);
		
		containerVerticalPanel = new VerticalPanel();
		containerAllVerticalPanel.add(containerVerticalPanel);
		
		
		initClinicalAssesmentForm();

		initClinicalAssesmentList();
		
		if(canWrite)
			tabPanel.selectTab(1);
		else
			tabPanel.selectTab(0);
	}
	public void switchView(boolean isTabView){
		if(isTabView){
			tabPanel.setVisible(true);
			if(canWrite)
				tabPanel.add(containerScannedDocumentsForm, scannedDocumentsEntryLabel.getText());
			scannedDocumentsEntryLabel.setVisible(false);
			
			tabPanel.add(containerScannedDocumentsListPanel, scannedDocumentsListLabel.getText());
			scannedDocumentsListLabel.setVisible(false);
			
			if((canModify && !canWrite && scannedDocumentId!=null))
				tabPanel.add(containerScannedDocumentsForm, _("Modify"));
			tabPanel.selectTab(0);
			containerVerticalPanel.setVisible(false);
			
		}else{
			containerVerticalPanel.setVisible(true);
			
			if(canWrite || (canModify && !canWrite && scannedDocumentId!=null)){
				scannedDocumentsEntryLabel.setVisible(true);
				containerVerticalPanel.add(containerScannedDocumentsForm);
			}
			
			scannedDocumentsListLabel.setVisible(true);
			containerVerticalPanel.add(containerScannedDocumentsListPanel);
			tabPanel.setVisible(false);
		}
	}
	
	protected void initClinicalAssesmentForm(){
		containerScannedDocumentsForm= new VerticalPanel();
		containerScannedDocumentsForm.setWidth("100%");
		if(canWrite)
			tabPanel.add(containerScannedDocumentsForm, scannedDocumentsEntryLabel.getText());
		containerScannedDocumentsForm.setWidth("100%");
		scannedDocumentsEntryLabel.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM );
		scannedDocumentsEntryLabel.setVisible(false);
		containerScannedDocumentsForm.add(scannedDocumentsEntryLabel);

		
		int row = 0;
		
		final FlexTable flexTable = new FlexTable();
		containerScannedDocumentsForm.add(flexTable);
		
		Label label = new Label(_("Date"));
		flexTable.setWidget(row, 0, label);
		final CustomDatePicker date = new CustomDatePicker();
		flexTable.setWidget(row, 1, date);
		containerScannedDocumentsFormFields.put("imagedt", date);
		row++;
		
		label = new Label(_("Type of Image"));
		flexTable.setWidget(row, 0, label);
		final CustomListBox typeOfImage = new CustomListBox();
		typeOfImage.addItem(_("Operative Report"),"op_report/misc");
		typeOfImage.addItem("- " + _("Colonoscopy"),"op_report/colonoscopy");
		typeOfImage.addItem("- " + _("Endoscopy"),"op_report/endoscopy");
		typeOfImage.addItem(_("Miscellaneous"),"misc/misc");
		typeOfImage.addItem("- " + _("Consult"),"misc/consult");
		typeOfImage.addItem("- " + _("Discharge Summary"),"misc/discharge_summary");
		typeOfImage.addItem("- " + _("History and Physical"),"misc/history_and_physical");
		typeOfImage.addItem(_("Lab Report"),"lab_report/misc");
		typeOfImage.addItem("- CBC","lab_report/cbc");
		typeOfImage.addItem("- C8","lab_report/c8");
		typeOfImage.addItem("- LFT","lab_report/lft");
		typeOfImage.addItem("- " + _("Lipid Profile"),"lab_report/lipid_profile");
		typeOfImage.addItem("- UA","lab_report/ua");
		typeOfImage.addItem("- " + _("Thyroid Profile"),"lab_report/thyroid_profile");
		typeOfImage.addItem(_("Letters"),"letters/misc");
		typeOfImage.addItem(_("Oncology"),"oncology/misc");
		typeOfImage.addItem(_("Hospital Records"),"hospital/misc");
		typeOfImage.addItem("- " + _("Discharge Summary"),"hospital/discharge");
		typeOfImage.addItem(_("Pathology"),"pathology/misc");
		typeOfImage.addItem(_("Patient"),"patient/misc");
		typeOfImage.addItem("- " + _("Consent"),"patient/consent");
		typeOfImage.addItem("- " + _("History"),"patient/history");
		typeOfImage.addItem("- " + _("Time Out"),"patient/time_out");
		typeOfImage.addItem(_("Questionnaire"),"questionnaire/misc");
		typeOfImage.addItem(_("Radiology"),"radiology/misc");
		typeOfImage.addItem("- " + _("Abdominal Radiograph"),"radiology/abdominal_radiograph");
		typeOfImage.addItem("- " + _("Chest Radiograph"),"radiology/chest_radiograph");
		typeOfImage.addItem("- " + _("Abdominal CT Reports"),"radiology/abdominal_ct_reports");
		typeOfImage.addItem("- " + _("Chest CT Reports"),"radiology/chest_ct_reports");
		typeOfImage.addItem("- " + _("Mammogram Reports"),"radiology/mammogram_reports");
		typeOfImage.addItem(_("Insurance Card"),"insurance_card");
		typeOfImage.addItem(_("Referral"),"referral/misc");
		typeOfImage.addItem("- " + _("Notes"),"referral/notes");
		typeOfImage.addItem("- " + _("Radiographs"),"referral/radiographs");
		typeOfImage.addItem("- " + _("Lab Reports"),"referral/lab_reports");
		typeOfImage.addItem("- " + _("Consult"),"referral/consult");
		typeOfImage.addItem(_("Financial Information"),"financial/misc");
		flexTable.setWidget(row, 1, typeOfImage);
		containerScannedDocumentsFormFields.put("imagetypecat", typeOfImage);
		row++;
		
		label = new Label(_("Physician"));
		flexTable.setWidget(row, 0, label);
		final ProviderWidget provider = new ProviderWidget();
		flexTable.setWidget(row, 1, provider);
		containerScannedDocumentsFormFields.put("imagephy", provider);
		row++;
		
		label = new Label(_("Description"));
		flexTable.setWidget(row, 0, label);
		final TextArea description = new TextArea();
		flexTable.setWidget(row, 1, description);
		containerScannedDocumentsFormFields.put("imagedesc", description);
		row++;
		
		label = new Label(_("Attach Image"));
		flexTable.setWidget(row, 0, label);
		final FileUpload fileUpload = new FileUpload();
		fileUpload.setName("imageupload");
		final FormPanel formPanel = new FormPanel();
		formPanel.setEncoding(FormPanel.ENCODING_MULTIPART);
		formPanel.setMethod(FormPanel.METHOD_POST);
		formPanel.add(fileUpload);
		formPanel.addSubmitCompleteHandler(new SubmitCompleteHandler() {
		
			@Override
			public void onSubmitComplete(SubmitCompleteEvent arg0) {
				Integer id = (Integer)JsonUtil.shoehornJson(JSONParser.parseStrict(arg0.getResults()), "Integer");
				if(id!=null){
					Util.showInfoMsg(moduleName, _("Document added successfully."));
					formPanel.reset();
					resetForm();
					populateAvailableData();
				}else{
					Util.showErrorMsg(moduleName, _("Failed to add document."));
				}
				
			}
		
		});
		flexTable.setWidget(row, 1, formPanel);
		
		row++;
		
		HorizontalPanel buttonContainer = new HorizontalPanel();
		flexTable.setWidget(row, 1, buttonContainer);
		
		
		wSubmit = new CustomButton(_("Submit"),AppConstants.ICON_ADD);
		buttonContainer.add(wSubmit);
		
		wSubmit.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				String method="Add";
				HashMap<String, String> data = Util.populateHashMap(containerScannedDocumentsFormFields);
				data.put("imagepat", patientId.toString());
				if(scannedDocumentId!=null){
					data.put("id", scannedDocumentId.toString());
					method = "Mod";
				}
				String[] params = {JsonUtil.jsonify(data)};
				String url = Util.getJsonRequest(
						"org.freemedsoftware.module.ScannedDocuments."+method,
						params);
				formPanel.setAction(url);
				formPanel.submit();
			}
		
		});
		
		CustomButton resetButton = new CustomButton(_("Reset"), AppConstants.ICON_CLEAR);
		buttonContainer.add(resetButton);
		
		resetButton.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				formPanel.reset();
				resetForm();
			}
		
		});
		
		wDelete = new CustomButton(_("Delete"), AppConstants.ICON_DELETE);
		buttonContainer.add(wDelete);
		wDelete.setVisible(false);
		wDelete.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent evt) {
				deleteRecord(SCANNED_DOCUMENT, scannedDocumentId);
			}
		});

	}
	
	protected void initClinicalAssesmentList(){
		containerScannedDocumentsListPanel= new VerticalPanel();
		containerScannedDocumentsListPanel.setWidth("100%");
		tabPanel.add(containerScannedDocumentsListPanel, scannedDocumentsListLabel.getText());
		
		scannedDocumentsListLabel.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM );
		scannedDocumentsListLabel.setVisible(false);
		containerScannedDocumentsListPanel.add(scannedDocumentsListLabel);
		
		HorizontalPanel horizontalPanel = new HorizontalPanel();
		horizontalPanel.setWidth("100%");
		
		containerScannedDocumentsListPanel.add(horizontalPanel);
		
		containerScannedDocumentsTable = new CustomTable();
		containerScannedDocumentsTable.setWidth("100%");
		horizontalPanel.add(containerScannedDocumentsTable);
		containerScannedDocumentsTable.addColumn(_("Date"), "imagedt");
		containerScannedDocumentsTable.addColumn(_("Image"), "imagefile");
		containerScannedDocumentsTable.addColumn(_("Type"), "imagetype");
		containerScannedDocumentsTable.addColumn(_("Category"), "imagecat");
		containerScannedDocumentsTable.addColumn(_("Physician"), "physician");
		containerScannedDocumentsTable.addColumn(_("Action"), "action");
		containerScannedDocumentsTable.setIndexName("id");
		
		containerScannedDocumentsTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				actionBar.applyPermissions(canRead, canWrite, canDelete, canModify, canLock);
				actionBar.hideAction(HandleCustomAction.PRINT);
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						if(action == HandleCustomAction.MODIFY)
							modifyEntry(data);
						else if(action == HandleCustomAction.DELETE)
							deleteRecord(SCANNED_DOCUMENT, id);
						else if(action == HandleCustomAction.LOCK){
							Util.callModuleMethod(SCANNED_DOCUMENT, "lock",id,new CustomRequestCallback() {
								public void onError() {
									Util.showErrorMsg(SCANNED_DOCUMENT, _("Failed to lock item."));
								}
								public void jsonifiedData(Object data) {
									if(((Boolean)data).booleanValue()){
										actionBar.lock();
										Util.showInfoMsg(SCANNED_DOCUMENT, _("Item locked successfully."));
									}else
										Util.showErrorMsg(SCANNED_DOCUMENT, _("Failed to lock item."));
								}
							
							},"Boolean");
						}else if(action == HandleCustomAction.VIEW){
							djvuViewer.setPatient(patientId);
							djvuViewer.setThumbNailMode(true);
							djvuViewer.setInternalId(id);
						}
					}
				});
				
				// Push value back to table
				return actionBar;
			}
		});

		djvuViewer = new DjvuViewer();
		djvuViewer.setType(DjvuViewer.SCANNED_DOCUMENTS);
		horizontalPanel.add(djvuViewer);
		djvuViewer.setVisible(false);
		djvuViewer.setSize("100%", "100%");
		
	}
	
	protected void modifyEntry(HashMap<String, String> data){
		scannedDocumentId = Integer.parseInt(data.get("id"));
		retrieveAndFillData(SCANNED_DOCUMENT,"org.freemedsoftware.module."+SCANNED_DOCUMENT+".GetRecord",scannedDocumentId, containerScannedDocumentsFormFields);
		wSubmit.setText(_("Modify"));
		wDelete.setVisible(true);
		tabPanel.selectTab(0);
		if(!canWrite && canModify){
			tabPanel.add(containerScannedDocumentsForm, _("Modify"));
			tabPanel.selectTab(1);
		}
	}
	
	/**
	 * Internal method to load a template record into the current form.
	 * 
	 * @param data
	 */
	protected void loadTemplateData(HashMap<String, String> data) {

	}

	public String getModuleName() {
		return moduleName;
	}

	public void resetForm() {
		Util.resetWidgetMap(containerScannedDocumentsFormFields);
		scannedDocumentId = null;
		wSubmit.setText("Add");
		wDelete.setVisible(false);
	
		if(!canWrite && canModify)
			tabPanel.remove(containerScannedDocumentsForm);	

		tabPanel.selectTab(0);
	}
	
	public void populateAvailableData(){
		retrieveAndFillListData();
	}

	public void deleteRecord(final String moduleName,Integer id){
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String moduleURL = "org.freemedsoftware.module."+moduleName+ ".Del";
			String[] params = { JsonUtil.jsonify(id) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(moduleURL
											,
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Boolean b = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parseStrict(response.getText()),
									"Boolean");
							if(b!=null && b.booleanValue()){
								retrieveAndFillListData();
								resetForm();
								wDelete.setVisible(false);
								tabPanel.selectTab(1);
							}
						} else {
							Util.showErrorMsg("ScannedDocuments", _("Failed to delete document."));
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// GWT-RPC
		}
	}
	
	public void retrieveAndFillData(final String moduleName,String moduleURL,Integer id,final HashMap<String, Widget> containerFormFields){
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String[] params = { JsonUtil.jsonify(id) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											moduleURL,
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						JsonUtil
								.debug("Error on retrieving document");
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							if (response.getText().compareToIgnoreCase(
									"false") != 0) {
								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser
												.parseStrict(response.getText()),
												"HashMap<String,String>");
								Util.populateForm(containerFormFields, result);
							} else {
								JsonUtil
										.debug("Received dummy response from JSON backend");
							}
						} else {
							Util.showErrorMsg("ScannedDocuments", _("Failed to get document."));
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("ScannedDocuments", _("Failed to get document."));
			}
		} else {
			// GWT-RPC
		}
	}
	
	public void retrieveAndFillListData(){
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String[] params = { JsonUtil.jsonify(patientScreen.getPatient()) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module."+SCANNED_DOCUMENT+".GetPatientAllRecords",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						JsonUtil
								.debug("Error on retrieving AppointmentTemplate");
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							if (response.getText().compareToIgnoreCase(
									"false") != 0) {
								HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser
												.parseStrict(response.getText()),
												"HashMap<String,String>[]");
								if (result != null) {
									containerScannedDocumentsTable.loadData(result);

								}
							} else {
								JsonUtil
										.debug("Received dummy response from JSON backend");
							}
						} else {
							Util.showErrorMsg("ScannedDocuments", _("Failed to get document."));
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("ScannedDocuments", _("Failed to get document."));
			}
		} else {
			// GWT-RPC
		}
	}
	public void saveFormData(final String moduleName,HashMap<String, String> data,boolean isModify){
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: STUBBED
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// JSON-RPC
			String moduleURL = "org.freemedsoftware.module."+moduleName+ (isModify?".Mod":".Add");
			String[] params = { JsonUtil.jsonify(data) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(moduleURL
											,
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Integer r = (Integer) JsonUtil.shoehornJson(
									JSONParser.parseStrict(response.getText()),
									"Integer");
							if (r != null) {
								populateAvailableData();
								Util.showInfoMsg("ScannedDocuments", _("Entry successfully added."));
							}else{
								Boolean b = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parseStrict(response.getText()),
										"Boolean");
								if(b!=null)
									Util.showInfoMsg("ScannedDocuments", _("Entry successfully modified."));
							}
						} else {
							Util.showErrorMsg("ScannedDocuments", _("Entry failed."));
						}
					}
				});
			} catch (RequestException e) {
			}
		} else {
			// GWT-RPC
		}
	}
	
	
}

