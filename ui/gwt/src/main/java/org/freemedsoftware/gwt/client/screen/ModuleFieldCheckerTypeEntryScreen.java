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

package org.freemedsoftware.gwt.client.screen;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.ModuleSearchWidget;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ModuleFieldCheckerTypeEntryScreen extends PatientEntryScreenInterface {

	
	protected TabPanel tabPanel; //All forms container if tab view
	
	protected VerticalPanel containerVerticalPanel; //All forms container if single page view
	
	protected VerticalPanel containerModuleFieldCheckerTypeForm;
	
	protected VerticalPanel containerModuleFieldCheckerTypeListPanel;
	
	protected CustomTable containerModuleFieldCheckerTypeTable;

	
	protected HashMap<String, Widget> containerModuleFieldCheckerTypeFormFields = new HashMap<String, Widget>();//containerInitialForm Fields Container
	

	
		
	protected Integer moduleId = null;
	

	public final static	String MODULE_NAME= "ModuleFieldCheckerType";
	
	protected String patientIdName = "patient";
	
	protected Label moduleFieldCheckerTypeEntryLabel 	 = new Label(_("Add"));
	
	protected Label moduleFieldCheckerTypeListLabel 	 = new Label(_("List"));
	
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	
	protected CustomButton wSubmit;
	protected CustomButton  wDelete;
	
	protected TextBox name = null; 
	protected ModuleSearchWidget module = null;
	protected FlexTable moduleFieldsTable=null;
	protected List<CheckBox> fieldsList = new ArrayList<CheckBox>();
	
	
	private static List<ModuleFieldCheckerTypeEntryScreen> moduleFieldCheckerTypeScreenList=null;
	
	//Creates only desired amount of instances if we follow this pattern otherwise we have public constructor as well
	public static ModuleFieldCheckerTypeEntryScreen getInstance(){
		ModuleFieldCheckerTypeEntryScreen moduleSearchScreen=null; 
		
		if(moduleFieldCheckerTypeScreenList==null)
			moduleFieldCheckerTypeScreenList=new ArrayList<ModuleFieldCheckerTypeEntryScreen>();
		if(moduleFieldCheckerTypeScreenList.size()<AppConstants.MAX_SEARCH_TABS)//creates & returns new next instance of ModuleFieldCheckerTypeEntryScreen
			moduleFieldCheckerTypeScreenList.add(moduleSearchScreen=new ModuleFieldCheckerTypeEntryScreen());
		else //returns last instance of ModuleFieldCheckerTypeEntryScreen from list 
			moduleSearchScreen = moduleFieldCheckerTypeScreenList.get(AppConstants.MAX_SEARCH_TABS-1);
		return moduleSearchScreen;
	}  
	
	public static boolean removeInstance(ModuleFieldCheckerTypeEntryScreen patientSearchScreen){
		return moduleFieldCheckerTypeScreenList.remove(patientSearchScreen);
	}
	
	
	public ModuleFieldCheckerTypeEntryScreen() {
		super("admin");
		final VerticalPanel containerAllVerticalPanel = new VerticalPanel();
		initWidget(containerAllVerticalPanel);

		final HorizontalPanel tabViewPanel = new HorizontalPanel();
		final CheckBox tabView = new CheckBox();
		tabView.setText("Tab View");
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
		
		
		initWorkFlowTypeForm();

		initWorkFlowList();
		
		if(canWrite)
			tabPanel.selectTab(1);
		else
			tabPanel.selectTab(0);
	}
	public void switchView(boolean isTabView){
		if(isTabView){
			tabPanel.setVisible(true);
			if(canWrite)
				tabPanel.add(containerModuleFieldCheckerTypeForm, moduleFieldCheckerTypeEntryLabel.getText());
			moduleFieldCheckerTypeEntryLabel.setVisible(false);
			
			tabPanel.add(containerModuleFieldCheckerTypeListPanel, moduleFieldCheckerTypeListLabel.getText());
			moduleFieldCheckerTypeListLabel.setVisible(false);
			
			if((canModify && !canWrite && moduleId!=null))
				tabPanel.add(containerModuleFieldCheckerTypeForm, _("Modify"));
			tabPanel.selectTab(0);
			containerVerticalPanel.setVisible(false);
			
		}else{
			containerVerticalPanel.setVisible(true);
			
			if(canWrite || (canModify && !canWrite && moduleId!=null)){
				moduleFieldCheckerTypeEntryLabel.setVisible(true);
				containerVerticalPanel.add(containerModuleFieldCheckerTypeForm);
			}
			
			moduleFieldCheckerTypeListLabel.setVisible(true);
			containerVerticalPanel.add(containerModuleFieldCheckerTypeListPanel);
			tabPanel.setVisible(false);
		}
	}
	
	protected void initWorkFlowTypeForm(){
		containerModuleFieldCheckerTypeForm= new VerticalPanel();
		containerModuleFieldCheckerTypeForm.setWidth("100%");
		if(canWrite)
			tabPanel.add(containerModuleFieldCheckerTypeForm, moduleFieldCheckerTypeEntryLabel.getText());
		containerModuleFieldCheckerTypeForm.setWidth("100%");
		moduleFieldCheckerTypeEntryLabel.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM );
		moduleFieldCheckerTypeEntryLabel.setVisible(false);
		containerModuleFieldCheckerTypeForm.add(moduleFieldCheckerTypeEntryLabel);

		
		int row = 0;
		
		final FlexTable flexTable = new FlexTable();
//		flexTable.setWidth("100%");
		containerModuleFieldCheckerTypeForm.add(flexTable);

		final Label statusNameLabel = new Label(_("Name") + " : ");
		flexTable.setWidget(row, 0, statusNameLabel);
		
		name = new TextBox();
//		statusName.setWidth("200%");
		flexTable.setWidget(row, 1, name);
		
		row++;
		
		final Label smartSearchLabel = new Label(_("Module") + " : ");
		flexTable.setWidget(row, 0, smartSearchLabel);

		module = new ModuleSearchWidget(ModuleSearchWidget.MODULE_TYPE_EMR);
		flexTable.setWidget(row, 1, module);
//		statusModule.setWidth("100%");
		module.addChangeHandler(new ValueChangeHandler<String>() {
		
			@Override
			public void onValueChange(ValueChangeEvent<String> arg0) {
				
				
				populateFields(arg0.getValue(),null);
			}
		
		});

		row++;	
		
		final Label statusFieldsLabel = new Label(_("Fields") + " : ");
		flexTable.setWidget(row,0,statusFieldsLabel);
		moduleFieldsTable = new FlexTable();
		flexTable.setWidget(row,1,moduleFieldsTable);
		moduleFieldsTable.setWidth("100%");
		moduleFieldsTable.setVisible(false);
//		containerWorkFlowTypeForm.add(moduleFieldsTable);
		
		
		row++;
		
		HorizontalPanel buttonContainer = new HorizontalPanel();
		containerModuleFieldCheckerTypeForm.add(buttonContainer);
		
		
		wSubmit = new CustomButton(_("Submit"),AppConstants.ICON_ADD);
		buttonContainer.add(wSubmit);
		
		wSubmit.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {

				String status_Fields = getFields();
				HashMap<String, String> hashMap = new HashMap<String, String>();
				if(!status_Fields.equalsIgnoreCase("")){
					hashMap.put("name", name.getValue());
					hashMap.put("module", module.getStoredValue());
					hashMap.put("fields", status_Fields);
					String method = "add";
					if(moduleId!=null){
						hashMap.put("id", moduleId.toString());
						method = "mod";
					}
					List param =new ArrayList();
					param.add(hashMap);
					Util.callModuleMethod(MODULE_NAME, method, param, new CustomRequestCallback() {
					
						@Override
						public void onError() {
							Util.showErrorMsg(MODULE_NAME, _("Failed to add module!"));
						}
					
						@Override
						public void jsonifiedData(Object data) {
							if(data instanceof Integer ){
								Integer id = (Integer)data;
								if(id>0){
									retrieveAndFillListData();
									resetForm();
									Util.showInfoMsg(MODULE_NAME, _("Added successfully!"));
								}else 
									Util.showErrorMsg(MODULE_NAME, _("Module already added!!"));
							}else if(data instanceof Boolean ){
								Boolean modified = (Boolean)data;
								if(modified){
									retrieveAndFillListData();
									resetForm();
									Util.showInfoMsg(MODULE_NAME, _("Modified succefully!"));
								}else 
									Util.showErrorMsg(MODULE_NAME, _("Failed to modify!"));
							}
						}
					
					}, method.equalsIgnoreCase("add")?"Integer":"Boolean");
					
			}else 
				Util.showErrorMsg(MODULE_NAME, _("Please choose some fields to make them mandatory!"));
				
				
			}
		
		});
		
		CustomButton resetButton = new CustomButton(_("Reset"),AppConstants.ICON_CLEAR);
		buttonContainer.add(resetButton);
		
		resetButton.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				resetForm();
			}
		
		});
		
		wDelete = new CustomButton("Delete",AppConstants.ICON_DELETE);
		buttonContainer.add(wDelete);
		wDelete.setVisible(false);
		wDelete.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent evt) {
				deleteRecord(MODULE_NAME, moduleId);
			}
		});

	}
	
	public void populateFields(String statusModule,final String checkFields){
		
		moduleFieldsTable.setVisible(false);
		moduleFieldsTable.clear();
		
		
		// moduleProperties.clear();
		 List params = new ArrayList();
		 params.add(statusModule);
		 Util.callApiMethod("ModuleSearch", "getFields",params , new CustomRequestCallback(){
		
			@Override
			public void onError() {
				// TODO Auto-generated method stub
				
			}
		
			@Override
			public void jsonifiedData(Object data) {
				if(data!=null){
					HashMap<String, Boolean> selectedFieldsMap = null; 
					String[] rs = (String[])data;
					if(checkFields!=null){
						selectedFieldsMap = new HashMap<String, Boolean>();
						String []selectedFields = checkFields.split(",");
						for(int i=0;i<selectedFields.length;i++){
							selectedFieldsMap.put(selectedFields[i], true);
						}
					}
					
						int row=0;
						int col=0;
						fieldsList.clear();
						for (int i=0;i<rs.length;i++)
						{
							CheckBox checkbox=new CheckBox(rs[i]);
							moduleFieldsTable.setWidget(row, col, checkbox);
							fieldsList.add(checkbox);
							
							if(selectedFieldsMap!=null && selectedFieldsMap.containsKey(rs[i]))
								checkbox.setValue(true);
							col++;
							if(col%6==0){
								row++;
								col=0;
							}
							
						}
						if(rs.length>0){
							moduleFieldsTable.setVisible(true);
						}
					
				}
			}
		
		}, "String[]");
	}
	
	public String getFields(){
		String selectedModuleFields = "";
		for(int i=0;i<fieldsList.size();i++)
		{
			
			CheckBox ch=fieldsList.get(i);
			if(ch.getValue())
				selectedModuleFields+=ch.getText()+",";
		}
	return selectedModuleFields;	
	}
	
	
	
	protected void initWorkFlowList(){
		containerModuleFieldCheckerTypeListPanel= new VerticalPanel();
		containerModuleFieldCheckerTypeListPanel.setWidth("100%");
		tabPanel.add(containerModuleFieldCheckerTypeListPanel, moduleFieldCheckerTypeListLabel.getText());
		
		moduleFieldCheckerTypeListLabel.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM );
		moduleFieldCheckerTypeListLabel.setVisible(false);
		containerModuleFieldCheckerTypeListPanel.add(moduleFieldCheckerTypeListLabel);
		
		HorizontalPanel horizontalPanel = new HorizontalPanel();
		horizontalPanel.setWidth("100%");
		
		containerModuleFieldCheckerTypeListPanel.add(horizontalPanel);
		
		containerModuleFieldCheckerTypeTable = new CustomTable();
		containerModuleFieldCheckerTypeTable.setWidth("100%");
		horizontalPanel.add(containerModuleFieldCheckerTypeTable);
		containerModuleFieldCheckerTypeTable.addColumn(_("Name"), "name");
		containerModuleFieldCheckerTypeTable.addColumn(_("Module"), "module");
		containerModuleFieldCheckerTypeTable.addColumn(_("Action"), "action");
		containerModuleFieldCheckerTypeTable.setIndexName("id");
		
		containerModuleFieldCheckerTypeTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				actionBar.applyPermissions(false, canWrite, canDelete, canModify, false);
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						if(action == HandleCustomAction.MODIFY)
							modifyEntry(data);
						else if(action == HandleCustomAction.DELETE)
							deleteRecord(MODULE_NAME, id);
					}
				});
				
				// Push value back to table
				return actionBar;
			}
		});

		
	}
	
	protected void modifyEntry(HashMap<String, String> data){
		moduleId = Integer.parseInt(data.get("id"));
		name.setText(data.get("name"));
		module.setValue(data.get("module"));
		populateFields(data.get("module"), data.get("fields"));
		wSubmit.setText(_("Modify"));
		wDelete.setVisible(true);
		tabPanel.selectTab(0);
		if(!canWrite && canModify){
			tabPanel.add(containerModuleFieldCheckerTypeForm, "Modify");
			tabPanel.selectTab(1);
		}
	}
	

	public String getModuleName() {
		return moduleName;
	}

	public void resetForm() {
		fieldsList.clear();
		moduleFieldsTable.removeAllRows();

		module.getTextEntryWidget().setText("");
		name.setText("");
		
		moduleId = null;
		wSubmit.setText("Add");
		wDelete.setVisible(false);
	
		if(!canWrite && canModify)
			tabPanel.remove(containerModuleFieldCheckerTypeForm);	

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
							Util.showErrorMsg(moduleName, _("Failed to delete record!"));
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
								.debug(_("Error on retrieving data!"));
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
							Util.showErrorMsg(moduleName, _("Failed to get items!"));
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg(moduleName, _("Failed to get items!"));
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
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module."+MODULE_NAME+".GetRecords",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						JsonUtil
								.debug("Error on retrieving data");
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
									containerModuleFieldCheckerTypeTable.loadData(result);

								}
							} else {
								JsonUtil
										.debug("Received dummy response from JSON backend");
							}
						} else {
							Util.showErrorMsg(moduleName, _("Failed to get items!"));
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg(moduleName, _("Failed to get items!"));
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
								Util.showInfoMsg(moduleName, _("Entry successfully added."));
							}else{
								Boolean b = (Boolean) JsonUtil.shoehornJson(
										JSONParser.parseStrict(response.getText()),
										"Boolean");
								if(b!=null)
									Util.showInfoMsg(moduleName, _("Entry successfully modified."));
							}
						} else {
							Util.showErrorMsg(moduleName, _("Failed to save data!"));
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

