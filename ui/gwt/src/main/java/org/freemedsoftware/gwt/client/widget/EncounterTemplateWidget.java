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

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.EncounterCommandType;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.EncounterFormMode;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.EncounterFormType;
import org.freemedsoftware.gwt.client.widget.EncounterWidget.NoteType;

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

import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
public class EncounterTemplateWidget extends Composite {
	public enum CallbackType {
		UPDATED, CANCEL
	}
	
	protected TabPanel tabPanel;
	protected ArrayList<String> sectionsList;
	protected HashSet<String> selectedSections;
	protected HashSet<String> selectedFields;

	protected HorizontalPanel actionPanel;
	protected VerticalPanel encTempAddPanel;
	protected VerticalPanel encTempMainAddPanel;
	protected TextBox tbEncTempName;
	protected CustomTable templatesCustomTable;
	private String currentTemplate;
	protected HashMap<String, String> templateValueMap;
	protected CustomRequestCallback callback;
	protected HorizontalPanel hpItemSelectionPanel;
	protected HashMap<String, List<String>> sectionFieldsMap;
	protected TemplateWidget templateWidget;
	protected CustomRadioButtonGroup radType; 
	public EncounterTemplateWidget(CustomRequestCallback cb) {
		currentTemplate = "";
		callback = cb;
		templateValueMap=new HashMap<String, String>();
		sectionFieldsMap=new HashMap<String, List<String>>();
		VerticalPanel vp=new VerticalPanel();
		vp.setSize("100%", "100%");
		initWidget(vp);
		tabPanel = new TabPanel();
		vp.add(tabPanel);
		createEncTemplateAdditionTab();
		createEncTemplateListTab();
		tabPanel.selectTab(0);
		
	}
	
	public void createEncTemplateAdditionTab(){
		encTempMainAddPanel = new VerticalPanel();
		encTempMainAddPanel.setSpacing(10);
		tabPanel.add(encTempMainAddPanel,"Add");
		FlexTable tinfoTable=new FlexTable();
		
		
		encTempAddPanel = new VerticalPanel();
		encTempAddPanel.setWidth("100%");
		encTempAddPanel.setSpacing(6);
		encTempMainAddPanel.add(encTempAddPanel);
		
		Label lbTname = new Label("Encounter Template Name:");
		lbTname.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEncTempName = new TextBox();
		tinfoTable.setWidget(0, 0, lbTname);
		tinfoTable.setWidget(0, 1, tbEncTempName);
		
		Label lbType = new Label("Type:");
		lbType.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		radType = new CustomRadioButtonGroup("type");
		radType.addItem("Encounter Note", "Encounter Note");
		radType.addItem("Progress Note", "Progress Note");
		tinfoTable.setWidget(1, 0, lbType);
		tinfoTable.setWidget(1, 1, radType);
		
		encTempAddPanel.add(tinfoTable);
		
		///////////////////////////Adding Main Sections		
		List<String> sectionList=new ArrayList<String>();
		sectionList.add("Billing Information");
		sectionList.add("SOAP Note");
		sectionList.add("IER");
		sectionList.add("Vitals/Generals");
		sectionList.add("CC & HPI");
		sectionList.add("Past Medical History");
		sectionList.add("Review Of Systems");
		sectionList.add("Social History");
		sectionList.add("Family History");
		sectionList.add("Exam");
		sectionList.add("Assessment/Plan");
		sectionList.add("Free Form Entry");
		sectionFieldsMap.put("Sections", sectionList);
		
		//////////////////////////Adding Billing Information Sections
		List<String> billInfoList=new ArrayList<String>();
		billInfoList.add("Procedure Code");
		billInfoList.add("Diagnosis 1");
		billInfoList.add("Diagnosis 2");
		billInfoList.add("Diagnosis 3");
		billInfoList.add("Diagnosis 4");
		billInfoList.add("Modifier 1");
		billInfoList.add("Modifier 2");
		billInfoList.add("Modifier 3");
		billInfoList.add("Place Of Service");
		billInfoList.add("Authorization");
		billInfoList.add("Primary Coverage");
		billInfoList.add("Secondary Coverage");
		billInfoList.add("Tertiary Coverage");
		billInfoList.add("Work Comp Coverage");
		billInfoList.add("Procedural Units");
		sectionFieldsMap.put("Sections#Billing Information", billInfoList);
		
		
		//////////////////////////SOAP
		List<String> soapList=new ArrayList<String>();
		soapList.add("Subjective");
		soapList.add("Objective");
		soapList.add("Assessment");
		soapList.add("Plan");
		sectionFieldsMap.put("Sections#SOAP Note", soapList	);
		
		
		//////////////////////////IER
		List<String> ierList=new ArrayList<String>();
		ierList.add("Interval");
		ierList.add("Education");
		ierList.add("Rx");
		sectionFieldsMap.put("Sections#IER", ierList);
		
		
		//////////////////////////Vitals/Generals
		List<String> vitalsGenList=new ArrayList<String>();
		vitalsGenList.add("Blood Pressure");
		vitalsGenList.add("Temperature");
		vitalsGenList.add("Heart Rate");
		vitalsGenList.add("Respiratory Rate");
		vitalsGenList.add("Weight");
		vitalsGenList.add("Height");
		vitalsGenList.add("BMI");
		vitalsGenList.add("General (PE)");
		sectionFieldsMap.put("Sections#Vitals/Generals", vitalsGenList);
		
		
		//////////////////////////CC & HPI
		List<String> ccHpiList=new ArrayList<String>();
		ccHpiList.add("CC");
		ccHpiList.add("HPI");
		sectionFieldsMap.put("Sections#CC & HPI", ccHpiList);

		//////////////////////////Review Of Systems
		List<String> rosList=new ArrayList<String>();
		rosList.add("General");
		rosList.add("Head");
		rosList.add("Eyes");
		rosList.add("ENT");
		rosList.add("CV");
		rosList.add("Resp");
		rosList.add("GI");
		rosList.add("GU");
		rosList.add("Muscle");
		rosList.add("Skin");
		rosList.add("Psych");
		rosList.add("Endocrine");
		rosList.add("Hem/Lymph");
		rosList.add("Neuro");
		rosList.add("Immunologic/Allergies");
		sectionFieldsMap.put("Sections#Review Of Systems", rosList);
		
		
		//////////////////////////Past History
		List<String> phList=new ArrayList<String>();
		phList.add("PH");
		sectionFieldsMap.put("Sections#Past Medical History", phList);
		
		//////////////////////////Family History
		List<String> fhList=new ArrayList<String>();
		fhList.add("FH");
		sectionFieldsMap.put("Sections#Family History", fhList);
		
		
		//////////////////////////Social History
		List<String> shList=new ArrayList<String>();
		shList.add("Alcohol");
		shList.add("Tobacco");
		shList.add("Illicit drugs");
		shList.add("Lives with");
		shList.add("Occupation");
		shList.add("HIV risk factors");
		shList.add("Travel");
		shList.add("Pets");
		shList.add("Hobbies");
		shList.add("Housing");
		sectionFieldsMap.put("Sections#Social History", shList);
		
		
		//////////////////////////Exam
		List<String> examList=new ArrayList<String>();
		examList.add("Head");
		examList.add("Eyes");
		examList.add("ENT");
		examList.add("Neck");
		examList.add("Breast");
		examList.add("Resp");
		examList.add("CV");
		//examList.add("Chest");
		examList.add("GI");
		examList.add("GU");
		examList.add("Lymphatics");
		examList.add("Skin");
		examList.add("MS");		
		examList.add("Neuro");
		examList.add("Psych");
		sectionFieldsMap.put("Sections#Exam", examList);
		
		//////////////////////////Exam#Head
		List<String> headList=new ArrayList<String>();
		headList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Head", headList);
		
		//////////////////////////Exam#Eyes
		List<String> eyesList=new ArrayList<String>();
		eyesList.add("Conjunctivae_lids_pupils & irises");
		eyesList.add("Fundi");
		eyesList.add("Cup:disc ratio");
		eyesList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Eyes", eyesList);
		
		//////////////////////////Exam#Eyes#Fundi
		List<String> fundiList=new ArrayList<String>();
		fundiList.add("Disc edges sharp");
		fundiList.add("Venous pulses seen");
		fundiList.add("A-V nicking");
		fundiList.add("Hemorrhages");
		fundiList.add("Exudates");
		sectionFieldsMap.put("Sections#Exam#Eyes#Fundi", fundiList);
		
		//////////////////////////Exam#ENT
		List<String> entList=new ArrayList<String>();
		entList.add("External canals_TMs");
		entList.add("Nasal mucosa_septum");
		entList.add("Lips_gums_teeth");
		entList.add("Oropharynx_mucosa_salivary glands");
		entList.add("Hard/soft palate_tongue_tonsils_posterior pharynx");
		entList.add("Thyroid");
		entList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#ENT", entList);
		
		//////////////////////////Exam#Neck
		List<String> neckList=new ArrayList<String>();
		neckList.add("Neck (note bruit_JVD)");
		neckList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Neck", neckList);
		
		//////////////////////////Exam#Breast
		List<String> breastList=new ArrayList<String>();
		breastList.add("Breasts (note dimpling_discharge_mass)");
		breastList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Breast", breastList);
		
		//////////////////////////Exam#Resp
		List<String> respList=new ArrayList<String>();
		respList.add("Respiratory effort");
		respList.add("Lung percussion & auscultation");
		respList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Resp", respList);
		
		//////////////////////////Exam#CV
		List<String> cvList=new ArrayList<String>();
		cvList.add("Auscultation");
		cvList.add("Palpation of heart");
		cvList.add("Abdominal aorta");
		cvList.add("Femoral arteries");
		cvList.add("Pedal pulses");
		cvList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#CV", cvList);
		
		//////////////////////////Exam#CV#Auscultation
		List<String> cvAuscultation=new ArrayList<String>();
		cvAuscultation.add("Regular rhythm");
		cvAuscultation.add("S1 constant");
		cvAuscultation.add("S2 physiologic split");
		cvAuscultation.add("Murmur (describe)");
		sectionFieldsMap.put("Sections#Exam#CV#Auscultation", cvAuscultation);
		
		//////////////////////////Exam#GI
		List<String> giList=new ArrayList<String>();
		giList.add("Abdomen");
		giList.add("Anus_perineum_rectum_sphincter tone");
		giList.add("Bowel sounds");
		giList.add("Stool");
		giList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#GI", giList);
		
		//////////////////////////Exam#GI#Abdomen
		List<String> abdList=new ArrayList<String>();
		abdList.add("Scars");
		abdList.add("Bruit");
		abdList.add("Mass");
		abdList.add("Tenderness");
		abdList.add("Hepatomegaly");
		abdList.add("Splenomegaly");
		sectionFieldsMap.put("Sections#Exam#GI#Abdomen", abdList);
		
		//////////////////////////Exam#GU
		List<String> guList=new ArrayList<String>();
		guList.add("Gender");
		guList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#GU", guList);
		
		//////////////////////////Exam#Lymphatics
		List<String> lymphaticsList=new ArrayList<String>();
		lymphaticsList.add("Lymph nodes");
		lymphaticsList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Lymphatics", lymphaticsList);
		
		//////////////////////////Exam#MS
		List<String> msList=new ArrayList<String>();
		msList.add("Gait & station");
		msList.add("Digits_nails");
		msList.add("ROM_stability");
		msList.add("Joints_bones_muscles");
		msList.add("Muscle strength & tone");
		msList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#MS", msList);
		
		//////////////////////////Exam#Skin
		List<String> skinList=new ArrayList<String>();
		skinList.add("Skin & SQ tissue");
		skinList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Skin", skinList);
		
		//////////////////////////Exam#Neuro
		List<String> neuroList=new ArrayList<String>();
		neuroList.add("Cranial nerves (note deficits)");
		neuroList.add("DTRs");
		neuroList.add("Motor");
		neuroList.add("Sensation");
		neuroList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Neuro", neuroList);
		
		//////////////////////////Exam#Psych
		List<String> psychList=new ArrayList<String>();
		psychList.add("Judgment & insight");
		psychList.add("Mood & affect");
		psychList.add("Oriented to time_place_person");
		psychList.add("Memory");
		psychList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Exam#Psych", psychList);
		
		//////////////////////////Assessment/Plan
		List<String> assessPlanList=new ArrayList<String>();
		assessPlanList.add("Assessment");
		assessPlanList.add("Plan");
		sectionFieldsMap.put("Sections#Assessment/Plan", assessPlanList);
		
		//////////////////////////Free Form Entry
		List<String> freeFormList=new ArrayList<String>();
		freeFormList.add("Free Form Entry");
		sectionFieldsMap.put("Sections#Free Form Entry", freeFormList);
		
	
		templateWidget = new TemplateWidget(sectionFieldsMap);
		encTempAddPanel.add(templateWidget);
		
		
		
		actionPanel = new HorizontalPanel();
		actionPanel.setSpacing(5);
		CustomButton nextBtn = new CustomButton("Next", AppConstants.ICON_NEXT);
		nextBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				if(tbEncTempName.getText()!=null && !tbEncTempName.getText().equals("")){
					encTempMainAddPanel.clear();
					EncounterFormMode mod;
					if (currentTemplate.equals("")) {
						mod = EncounterFormMode.ADD;
					} else {
						mod = EncounterFormMode.EDIT;
					}
					NoteType nt;
					if(radType.getWidgetValue()!=null){
						if(radType.getWidgetValue().equals("Encounter Note")){
							nt=NoteType.EncounterNote;
						}
						else{
							nt=NoteType.ProgressNote;
						}
					}
					else{
						nt=null;
					}
					EncounterWidget encWidget = new EncounterWidget(
							EncounterFormType.TEMPLATE_VALUES, mod,nt,
							templateWidget.getSelectedSectionFeildsMap(), tbEncTempName
									.getText(), templateValueMap,
							new CustomRequestCallback() {
								@Override
								public void onError() {
	
								}
	
								@Override
								public void jsonifiedData(Object data) {
									if (data instanceof EncounterCommandType) {
										if (((EncounterCommandType) data) == EncounterCommandType.UPDATE) {
											tabPanel.selectTab(1);
											callback
													.jsonifiedData(CallbackType.UPDATED);
										} else if (((EncounterCommandType) data) == EncounterCommandType.PREVIOUS) {
											encTempMainAddPanel.clear();
											encTempMainAddPanel
													.add(encTempAddPanel);
										} else if (((EncounterCommandType) data) == EncounterCommandType.CLOSE) {
											callback
													.jsonifiedData(CallbackType.CANCEL);
										}
									}
	
								}
							});
					encTempMainAddPanel.add(encWidget);
				}
				else{
					Window.alert("You must provide a name for your template...");
				}
			}
		});

		CustomButton resetBtn = new CustomButton("Reset",
				AppConstants.ICON_REFRESH);
		resetBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				reset();
			}
		});
		CustomButton cancelBtn = new CustomButton("Cancel",
				AppConstants.ICON_CANCEL);
		cancelBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				callback.jsonifiedData(CallbackType.CANCEL);
			}
		});
		actionPanel.add(resetBtn);
		actionPanel.add(cancelBtn);
		actionPanel.add(nextBtn);
		encTempAddPanel.add(actionPanel);
		
	}
	public void createEncTemplateListTab(){
		VerticalPanel listPanel = new VerticalPanel();
		tabPanel.add(listPanel, "List");
		templatesCustomTable = new CustomTable();
		templatesCustomTable.setIndexName("id");
		// patientCustomTable.setSize("100%", "100%");
		templatesCustomTable.setWidth("100%");
		templatesCustomTable.addColumn("Template Name", "tempname");
		templatesCustomTable.addColumn("Template Type", "notetype");
		templatesCustomTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				Boolean delCheck=false;
				if(CurrentState.isActionAllowed("EncounterNotesTemplate", AppConstants.DELETE) || data.get("tempuser").equals(CurrentState.getDefaultUser()))
					delCheck=true;
				
				actionBar.applyPermissions(false, false, delCheck, CurrentState.isActionAllowed("EncounterNotesTemplate", AppConstants.MODIFY), false);
					
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						if(action == HandleCustomAction.MODIFY){
							try {								
								
								getTemplateValues(""+id);
							} catch (Exception e) {
								GWT.log("Caught exception: ", e);
							}
							
						}
						else if(action == HandleCustomAction.DELETE){
							deleteTemplate(""+id);
						}
					}
				});
				// Push value back to table
				return actionBar;
			}
		});
		listPanel.add(templatesCustomTable);
		loadTemplates();
	}

	public void loadTemplates() {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {

			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotesTemplate.getTemplates",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {
									if (r.length > 0) {
										templatesCustomTable.loadData(r);
									} else {
									}
								} else {

								}
							} catch (Exception e) {

							}

						} else {

						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}
	
	public void getTemplateValues(String templateId) {
		reset();
		tabPanel.selectTab(0);
		currentTemplate = templateId;
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { templateId };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotesTemplate.getTemplateInfo",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								HashMap<String, String> r = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (r != null) {
									templateValueMap = r;
									String secStr=r.get("pnotestsections");
									HashMap<String, List<String>> secFldMap=(HashMap<String, List<String>>) JsonUtil
									.shoehornJson(JSONParser.parse(secStr),
											"HashMap<String,List>");
									tbEncTempName.setText(templateValueMap.get("pnotestname"));
									if(templateValueMap.get("pnotesttype")!=null){
										radType.setWidgetValue(templateValueMap.get("pnotesttype"));
									}
									templateWidget.loadValues(secFldMap);
								} else {
									
								}
							} catch (Exception e) {
								
							}

						} else {
							
						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}
	
	public void deleteTemplate(String id) {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { id };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EncounterNotes.del",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								Boolean r = (Boolean) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"Boolean");
								if(r){
									Util
									.showInfoMsg("EncounterNotesTemplate",
											"Encounter Note Template Successfully Deleted.");
									loadTemplates();
								}
								
							} catch (Exception e) {

							}

						} else {

						}
					}
				});
			} catch (RequestException e) {
			}
		}
	}
	
	public void reset(){
		currentTemplate = "";
		tbEncTempName.setText("");
		encTempAddPanel.remove(1);
		templateWidget=new TemplateWidget(sectionFieldsMap);
		encTempAddPanel.insert(templateWidget, 1);
	}

}
