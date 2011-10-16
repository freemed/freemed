package org.freemedsoftware.gwt.client.screen.patient;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomActionBar;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomButton;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonGroup;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.SupportModuleMultipleChoiceWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.core.client.GWT;
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
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class EpisodeOfCareScreen extends PatientScreenInterface{
	protected VerticalPanel verticalPanel;
	protected TabPanel tabPanel;
	protected VerticalPanel entryPanel;
	protected TextArea tbDesc;
	protected CustomDatePicker dtFirstOcc;
	protected CustomDatePicker dtCurrOnset;
	protected SupportModuleWidget reffPhy;
	protected SupportModuleWidget facWidget;
	protected CustomListBox listDisability;
	protected CustomDatePicker dsbFrDate;
	protected CustomDatePicker dsbToDate;
	protected CustomDatePicker dtDsbWrpDate;
	protected CheckBox cbRelPreg;
	protected CheckBox cbRelEmp;
	protected CheckBox cbRelAuto;
	protected CheckBox cbRelOtherCause;
	protected TextBox tbStateProv;
	protected CustomListBox listEpisodeType;
	protected CustomRadioButtonGroup radHospital;
	protected CustomDatePicker hosAdmDt;
	protected CustomDatePicker hosDscDt;
	protected VerticalPanel pregnencyPanel;
	protected VerticalPanel genInfoPanel;
	protected String fieldsWidth;
	protected VerticalPanel employementPanel;
	protected VerticalPanel automobilePanel;
	protected VerticalPanel otherPanel;
	protected CustomListBox listCycleLength;
	protected CustomListBox listGravida;
	protected CustomListBox listPara;
	protected CustomListBox listAbortions;
	protected CustomDatePicker lastMensPrd;
	protected CustomDatePicker dtOfConf;
	protected CustomListBox listMiscarries;
	protected TextBox tbEmpName;
	private TextBox tbEmployeeAdd1;
	private TextBox tbEmployeeAdd2;
	protected TextBox tbEmployeeCity;
	private TextBox tbEmployeeStProv;
	private TextBox tbEmployeePostCd;
	private TextBox tbEmployeeCountry;
	private TextBox tbEmployeeFileNumber;
	private TextBox tbEmployeeContactName;
	private TextBox tbEmployeeContactPhone;
	private TextBox tbEmployeeEmailAddress;
	protected TextBox tbAutoIns;
	protected TextBox tbAutoAdd1;
	protected TextBox tbAutoAdd2;
	protected TextBox tbAutoCity;
	protected TextBox tbAutoCountry;
	protected TextBox tbAutoCaseNumber;
	protected TextBox tbAutoContactName;
	protected TextBox tbAutoContactPhone;
	protected TextBox tbAutoEmailAddress;
	protected TextBox tbConRelatedTo;
	protected VerticalPanel addPanel;
	protected HashMap<String, String> eocMap;
	protected SupportModuleMultipleChoiceWidget diagFamWidget;
	protected TextBox tbAutoStProv;
	protected TextBox tbAutoPostCd;
	protected boolean isAdding;
	protected String modRecId;
	protected VerticalPanel listPanel;
	protected CustomTable eocCustomTable;
	protected CustomButton btnAdd;
	protected CustomListBox listHours;
	protected CustomListBox listMinutes;
	protected CustomListBox listAmPm;
	public EpisodeOfCareScreen() {
		verticalPanel=new VerticalPanel();
		verticalPanel.setSize("100%", "100%");
		verticalPanel.setSpacing(10);
		initWidget(verticalPanel);
		tabPanel=new TabPanel();
		verticalPanel.add(tabPanel);
		entryPanel = new VerticalPanel();
		entryPanel.setSize("100%", "100%");
		entryPanel.setSpacing(10);
		addPanel = new VerticalPanel();
		addPanel.setSize("100%", "100%");
		addPanel.setSpacing(10);
		addPanel.add(entryPanel);
		listPanel = new VerticalPanel();
		listPanel.setSize("100%", "100%");
		tabPanel.add(addPanel, _("Add"));
		tabPanel.add(listPanel, _("List"));
		tabPanel.selectTab(1);
		createEntryScreen();
		HorizontalPanel actionPanel=new HorizontalPanel();
		actionPanel.setSpacing(10);
		btnAdd = new CustomButton(_("Add"), AppConstants.ICON_ADD);
		btnAdd.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				saveEpisodeOfCare();
			}
		
		});
		CustomButton btnReset=new CustomButton(_("Reset"), AppConstants.ICON_REFRESH);
		btnReset.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent arg0) {
				reset();
			}
		
		});
		actionPanel.add(btnAdd);
		actionPanel.add(btnReset);
		addPanel.add(actionPanel);
		isAdding = true;
	}
	
	private void createEntryScreen(){
		fieldsWidth = "150px";
		genInfoPanel = new VerticalPanel();
		genInfoPanel.setSize("100%", "100%");
		genInfoPanel.setSpacing(5);
		
		entryPanel.add(genInfoPanel);
		Label lbGenInfo=new Label(" " + _("General Information") + "  ");
		lbGenInfo.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
		genInfoPanel.add(lbGenInfo);
		FlexTable generalInfoTable=new FlexTable();
		//generalInfoTable.setSize("100%", "100%");
		genInfoPanel.add(generalInfoTable);
		
		int row=0;
		Label lbDesc=new Label(_("Description"));
		lbDesc.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbDesc = new TextArea();
		tbDesc.setWidth("300px");
		generalInfoTable.setWidget(row, 0, lbDesc);
		generalInfoTable.setWidget(row, 1, tbDesc);
		generalInfoTable.getFlexCellFormatter().setColSpan(row, 1, 2);
		row++;
		
		Label lbDtFirstOcc=new Label(_("Date of First Occurance"));
		lbDtFirstOcc.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		dtFirstOcc = new CustomDatePicker();
		dtFirstOcc.setWidth(fieldsWidth);
		Label lbDtCurrOnset=new Label(_("Date of Current Onset"));
		lbDtCurrOnset.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		dtCurrOnset = new CustomDatePicker();
		dtCurrOnset.setWidth(fieldsWidth);
		generalInfoTable.setWidget(row, 0, lbDtFirstOcc);
		generalInfoTable.setWidget(row, 1, dtFirstOcc);
		generalInfoTable.setWidget(row, 2, lbDtCurrOnset);
		generalInfoTable.setWidget(row, 3, dtCurrOnset);
		row++;
		
		Label lbReffPhy=new Label(_("Referring Physician"));
		lbReffPhy.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		reffPhy = new SupportModuleWidget("ProviderModule");
		reffPhy.setWidth(fieldsWidth);
		generalInfoTable.setWidget(row, 0, lbReffPhy);
		generalInfoTable.setWidget(row, 1, reffPhy);
		row++;
		
		Label lbFacility=new Label(_("Facility"));
		lbFacility.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		facWidget = new SupportModuleWidget("FacilityModule");
		facWidget.setWidth(fieldsWidth);
		generalInfoTable.setWidget(row, 0, lbFacility);
		generalInfoTable.setWidget(row, 1, facWidget);
		row++;
		
		Label lbDiagFam = new Label(_("Diagnosis Family"));
		lbDiagFam.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		diagFamWidget = new SupportModuleMultipleChoiceWidget("DiagnosisFamily");
		generalInfoTable.setWidget(row, 0, lbDiagFam);
		generalInfoTable.setWidget(row, 1, diagFamWidget);
		row++;
		
		Label lbDisability=new Label(_("Disability Type"));
		lbDisability.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listDisability = new CustomListBox();
		listDisability.setWidth(fieldsWidth);
		listDisability.addItem(_("Unknown"), "0");
		listDisability.addItem("LT", "1");
		listDisability.addItem("ST", "2");
		listDisability.addItem(_("Permanent"), "3");
		listDisability.addItem(_("No Disability"), "4");
		generalInfoTable.setWidget(row, 0, lbDisability);
		generalInfoTable.setWidget(row, 1, listDisability);
		row++;
		
		Label lbDsbFrDate=new Label(_("Disability From Date"));
		lbDsbFrDate.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		dsbFrDate = new CustomDatePicker();
		dsbFrDate.setWidth(fieldsWidth);
		Label lbDsbToDate=new Label(_("Disability To Date"));
		lbDsbToDate.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		dsbToDate = new CustomDatePicker();
		dsbToDate.setWidth(fieldsWidth);
		Label lbDsbWrpDate=new Label(_("Disability Back to Work Date"));
		lbDsbWrpDate.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		dtDsbWrpDate = new CustomDatePicker();
		dtDsbWrpDate.setWidth(fieldsWidth);
		generalInfoTable.setWidget(row, 0, lbDsbFrDate);
		generalInfoTable.setWidget(row, 1, dsbFrDate);
		generalInfoTable.setWidget(row, 2, lbDsbToDate);
		generalInfoTable.setWidget(row, 3, dsbToDate);
		generalInfoTable.setWidget(row, 4, lbDsbWrpDate);
		generalInfoTable.setWidget(row, 5, dtDsbWrpDate);
		row++;		
		
		Label lbRelTo=new Label(_("Related to") + ":");
		lbRelTo.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		HorizontalPanel hpChecks=new HorizontalPanel();
		cbRelPreg = new CheckBox(_("Pregnancy"));
		cbRelPreg.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
		
			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				if(cbRelPreg.getValue()){
					if(pregnencyPanel==null)
						createPregnencyPanel();
				}
				else{
					entryPanel.remove(pregnencyPanel);
					pregnencyPanel=null;
				}
		
			}
		
		});
		cbRelEmp = new CheckBox(_("Employment"));
		cbRelEmp.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
			
			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				if(cbRelEmp.getValue()){
					if(employementPanel==null)
						createEmployementPanel();
				}
				else{
					entryPanel.remove(employementPanel);
					employementPanel=null;
				}
		
			}
		
		});
		cbRelAuto = new CheckBox(_("Automobile"));
		cbRelAuto.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
			
			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				if(cbRelAuto.getValue()){
					if(automobilePanel==null)
						createAutomobilePanel();
				}
				else{
					entryPanel.remove(automobilePanel);
					automobilePanel=null;
				}
		
			}
		
		});
		cbRelOtherCause = new CheckBox(_("Other Cause"));
		cbRelOtherCause.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
			
			@Override
			public void onValueChange(ValueChangeEvent<Boolean> arg0) {
				if(cbRelOtherCause.getValue()){
					if(otherPanel==null)
						createOtherPanel();
				}
				else{
					entryPanel.remove(otherPanel);
					otherPanel=null;
				}
		
			}
		
		});
		hpChecks.add(cbRelPreg);
		hpChecks.add(cbRelEmp);
		hpChecks.add(cbRelAuto);
		hpChecks.add(cbRelOtherCause);
		generalInfoTable.setWidget(row, 0, lbRelTo);
		generalInfoTable.setWidget(row, 1, hpChecks);
		generalInfoTable.getFlexCellFormatter().setColSpan(row, 1, 2);
		row++;
		
		Label lbState=new Label(_("State/Province"));
		lbState.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbStateProv = new TextBox();
		tbStateProv.setWidth(fieldsWidth);
		generalInfoTable.setWidget(row, 0, lbState);
		generalInfoTable.setWidget(row, 1, tbStateProv);
		row++;
		
		Label lbEpisodeType=new Label(_("Episode Type"));
		lbEpisodeType.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listEpisodeType = new CustomListBox();
		listEpisodeType.setWidth(fieldsWidth);
		listEpisodeType.addItem(_("NONE SELECTED"), "0");
		listEpisodeType.addItem(_("acute"), "acute");
		listEpisodeType.addItem(_("chronic"), "chronic");
		listEpisodeType.addItem(_("chronic recurrent"), "chronic recurrent");
		listEpisodeType.addItem(_("historical"), "historical");
		generalInfoTable.setWidget(row, 0, lbEpisodeType);
		generalInfoTable.setWidget(row, 1, listEpisodeType);
		row++;
		
		Label lbHospital=new Label(_("Hospital"));
		lbHospital.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		radHospital = new CustomRadioButtonGroup("hos");
		radHospital.addItem(_("Yes"), "1");
		radHospital.addItem(_("No"), "0");
		generalInfoTable.setWidget(row, 0, lbHospital);
		generalInfoTable.setWidget(row, 1, radHospital);
		row++;
		
		Label lbHosAdmDt=new Label(_("Hospital Admission Date"));
		lbHosAdmDt.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		hosAdmDt = new CustomDatePicker();
		hosAdmDt.setWidth(fieldsWidth);
		Label lbHosDscDt=new Label(_("Hospitial Discharge Date"));
		lbHosDscDt.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		hosDscDt = new CustomDatePicker();
		hosDscDt.setWidth(fieldsWidth);
		generalInfoTable.setWidget(row, 0, lbHosAdmDt);
		generalInfoTable.setWidget(row, 1, hosAdmDt);
		generalInfoTable.setWidget(row, 2, lbHosDscDt);
		generalInfoTable.setWidget(row, 3, hosDscDt);
		row++;
		
		
	}
	
	public void createPregnencyPanel(){
		pregnencyPanel = new VerticalPanel();
		pregnencyPanel.setSize("100%", "100%");
		pregnencyPanel.setSpacing(5);
		entryPanel.add(pregnencyPanel);
		Label lbPregnency=new Label(_("Pregnancy Related Information"));
		lbPregnency.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
		pregnencyPanel.add(lbPregnency);
		FlexTable pregnencyTable=new FlexTable();
		pregnencyPanel.add(pregnencyTable);
		
		int row=0;
		Label lbCycleLength=new Label(_("Length of Cycle"));
		lbCycleLength.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listCycleLength = new CustomListBox();
		for(int i=10;i<41;i++){
			listCycleLength.addItem(""+i);
		}
		pregnencyTable.setWidget(row, 0, lbCycleLength);
		pregnencyTable.setWidget(row, 1, listCycleLength);
		row++;
		
		Label lbGravida=new Label(_("Gravida"));
		lbGravida.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listGravida = new CustomListBox();
		for(int i=0;i<16;i++){
			listGravida.addItem(""+i);
		}
		pregnencyTable.setWidget(row, 0, lbGravida);
		pregnencyTable.setWidget(row, 1, listGravida);
		row++;
		
		Label lbPara=new Label(_("Para"));
		lbPara.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listPara = new CustomListBox();
		for(int i=0;i<16;i++){
			listPara.addItem(""+i);
		}
		pregnencyTable.setWidget(row, 0, lbPara);
		pregnencyTable.setWidget(row, 1, listPara);
		row++;
		
		Label lbAbortions=new Label(_("Abortions"));
		lbAbortions.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listAbortions = new CustomListBox();
		for(int i=0;i<16;i++){
			listAbortions.addItem(""+i);
		}
		pregnencyTable.setWidget(row, 0, lbAbortions);
		pregnencyTable.setWidget(row, 1, listAbortions);
		row++;
		
		Label lbLastMensPrd=new Label(_("Last Menstrual Period"));
		lbLastMensPrd.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		lastMensPrd = new CustomDatePicker();
		lastMensPrd.setWidth(fieldsWidth);
		pregnencyTable.setWidget(row, 0, lbLastMensPrd);
		pregnencyTable.setWidget(row, 1, lastMensPrd);
		row++;
		
		Label lbDateOfConfinement=new Label(_("Date of Confinement"));
		lbDateOfConfinement.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		dtOfConf = new CustomDatePicker();
		dtOfConf.setWidth(fieldsWidth);
		pregnencyTable.setWidget(row, 0, lbDateOfConfinement);
		pregnencyTable.setWidget(row, 1, dtOfConf);
		row++;
		
		Label lbMiscarries=new Label(_("Miscarries"));
		lbMiscarries.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listMiscarries = new CustomListBox();
		for(int i=0;i<16;i++){
			listMiscarries.addItem(""+i);
		}
		pregnencyTable.setWidget(row, 0, lbMiscarries);
		pregnencyTable.setWidget(row, 1, listMiscarries);
		row++;
	}
	
	public void createEmployementPanel(){
		employementPanel = new VerticalPanel();
		employementPanel.setSize("100%", "100%");
		employementPanel.setSpacing(5);
		entryPanel.add(employementPanel);
		Label lbEmployement=new Label(_("Employment Related Information"));
		lbEmployement.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
		employementPanel.add(lbEmployement);
		FlexTable employementTable=new FlexTable();
		employementPanel.add(employementTable);
		
		int row=0;
		Label lbEmpName=new Label(_("Name of Employer"));
		lbEmpName.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmpName = new TextBox();
		tbEmpName.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbEmpName);
		employementTable.setWidget(row, 1, tbEmpName);
		row++;
		
		Label lbAdd1=new Label(_("Address (Line 1)"));
		lbAdd1.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeAdd1 = new TextBox();
		tbEmployeeAdd1.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbAdd1);
		employementTable.setWidget(row, 1, tbEmployeeAdd1);
		row++;
		
		Label lbAdd2=new Label(_("Address (Line 2)"));
		lbAdd2.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeAdd2 = new TextBox();
		tbEmployeeAdd2.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbAdd2);
		employementTable.setWidget(row, 1, tbEmployeeAdd2);
		row++;
		
		Label lbCityStPrvcPstCode=new Label(_("City, State/Prov, Postal Code"));
		lbCityStPrvcPstCode.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		HorizontalPanel hp=new HorizontalPanel();
		hp.setSpacing(5);
		tbEmployeeCity = new TextBox();
		tbEmployeeStProv = new TextBox();
		tbEmployeePostCd = new TextBox();
		hp.add(tbEmployeeCity);
		hp.add(new Label(","));
		hp.add(tbEmployeeStProv);
		hp.add(new Label(","));
		hp.add(tbEmployeePostCd);
		employementTable.setWidget(row, 0, lbCityStPrvcPstCode);
		employementTable.setWidget(row, 1, hp);
		row++;
		
		Label lbCountry=new Label(_("Country"));
		lbCountry.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeCountry = new TextBox();
		tbEmployeeCountry.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbCountry);
		employementTable.setWidget(row, 1, tbEmployeeCountry);
		row++;
		
		Label lbFileNumber=new Label(_("File Number"));
		lbFileNumber.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeFileNumber = new TextBox();
		tbEmployeeFileNumber.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbFileNumber);
		employementTable.setWidget(row, 1, tbEmployeeFileNumber);
		row++;
		
		Label lbContactName=new Label(_("Contact Name"));
		lbContactName.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeContactName = new TextBox();
		tbEmployeeContactName.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbContactName);
		employementTable.setWidget(row, 1, tbEmployeeContactName);
		row++;
		
		Label lbContactPhone=new Label(_("Contact Phone"));
		lbContactPhone.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeContactPhone = new TextBox();
		tbEmployeeContactPhone.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbContactPhone);
		employementTable.setWidget(row, 1, tbEmployeeContactPhone);
		row++;
		
		Label lbEmailAddress=new Label(_("Email Address"));
		lbEmailAddress.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbEmployeeEmailAddress = new TextBox();
		tbEmployeeEmailAddress.setWidth(fieldsWidth);
		employementTable.setWidget(row, 0, lbEmailAddress);
		employementTable.setWidget(row, 1, tbEmployeeEmailAddress);
		row++;
	}
	
	public void createAutomobilePanel(){
		automobilePanel = new VerticalPanel();
		automobilePanel.setSize("100%", "100%");
		automobilePanel.setSpacing(5);
		entryPanel.add(automobilePanel);
		Label lbAutomobile=new Label(_("Automobile Related Information"));
		lbAutomobile.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
		automobilePanel.add(lbAutomobile);
		FlexTable automobileTable=new FlexTable();
		automobilePanel.add(automobileTable);
		
		int row=0;
		Label lbAutoIns=new Label(_("Auto Insurance"));
		lbAutoIns.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoIns = new TextBox();
		tbAutoIns.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoIns);
		automobileTable.setWidget(row, 1, tbAutoIns);
		row++;
		
		Label lbAutoAdd1=new Label(_("Address (Line 1)"));
		lbAutoAdd1.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoAdd1 = new TextBox();
		tbAutoAdd1.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoAdd1);
		automobileTable.setWidget(row, 1, tbAutoAdd1);
		row++;
		
		Label lbAutoAdd2=new Label(_("Address (Line 2)"));
		lbAutoAdd2.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoAdd2 = new TextBox();
		tbAutoAdd2.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoAdd2);
		automobileTable.setWidget(row, 1, tbAutoAdd2);
		row++;
		
		Label lbAutoCityStPrvcPstCode=new Label(_("City, State/Prov, Postal Code"));
		lbAutoCityStPrvcPstCode.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		HorizontalPanel hpAuto=new HorizontalPanel();
		hpAuto.setSpacing(5);
		tbAutoCity = new TextBox();
		tbAutoStProv = new TextBox();
		tbAutoPostCd = new TextBox();
		hpAuto.add(tbAutoCity);
		hpAuto.add(new Label(","));
		hpAuto.add(tbAutoStProv);
		hpAuto.add(new Label(","));
		hpAuto.add(tbAutoPostCd);
		automobileTable.setWidget(row, 0, lbAutoCityStPrvcPstCode);
		automobileTable.setWidget(row, 1, hpAuto);
		row++;
		
		Label lbAutoCountry=new Label(_("Country"));
		lbAutoCountry.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoCountry = new TextBox();
		tbAutoCountry.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoCountry);
		automobileTable.setWidget(row, 1, tbAutoCountry);
		row++;
		
		Label lbAutoCaseNumber=new Label(_("Case Number"));
		lbAutoCaseNumber.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoCaseNumber = new TextBox();
		tbAutoCaseNumber.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoCaseNumber);
		automobileTable.setWidget(row, 1, tbAutoCaseNumber);
		row++;
		
		Label lbAutoContactName=new Label(_("Contact Name"));
		lbAutoContactName.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoContactName = new TextBox();
		tbAutoContactName.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoContactName);
		automobileTable.setWidget(row, 1, tbAutoContactName);
		row++;
		
		Label lbAutoContactPhone=new Label(_("Contact Phone"));
		lbAutoContactPhone.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoContactPhone = new TextBox();
		tbAutoContactPhone.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoContactPhone);
		automobileTable.setWidget(row, 1, tbAutoContactPhone);
		row++;
		
		Label lbAutoEmailAddress=new Label(_("Email Address"));
		lbAutoEmailAddress.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbAutoEmailAddress = new TextBox();
		tbAutoEmailAddress.setWidth(fieldsWidth);
		automobileTable.setWidget(row, 0, lbAutoEmailAddress);
		automobileTable.setWidget(row, 1, tbAutoEmailAddress);
		row++;
		
		Label lbTimeOfAccident=new Label(_("Time of Accident"));
		lbTimeOfAccident.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		listHours = new CustomListBox();
		for(int i=0;i<13;i++){
			listHours.addItem(""+i);
		}
		listMinutes = new CustomListBox();
		for(int i=0;i<60;i++){
			listMinutes.addItem(""+i);
		}
		listAmPm = new CustomListBox();
		listAmPm.addItem("AM");
		listAmPm.addItem("PM");
		tbAutoEmailAddress = new TextBox();
		tbAutoEmailAddress.setWidth(fieldsWidth);
		HorizontalPanel hpTime=new HorizontalPanel();
		hpTime.setSpacing(5);
		hpTime.add(listHours);
		hpTime.add(listMinutes);
		hpTime.add(listAmPm);
		automobileTable.setWidget(row, 0, lbTimeOfAccident);
		automobileTable.setWidget(row, 1, hpTime);
		row++;
	}
	
	public void createOtherPanel(){
		otherPanel = new VerticalPanel();
		otherPanel.setSize("100%", "100%");
		otherPanel.setSpacing(5);
		entryPanel.add(otherPanel);
		Label lbOther=new Label(_("Other Related Information"));
		lbOther.setStyleName(AppConstants.STYLE_LABEL_HEADER_MEDIUM);
		otherPanel.add(lbOther);
		FlexTable otherTable=new FlexTable();
		otherPanel.add(otherTable);
		
		int row=0;
		Label lbConRelatedTo=new Label(_("Condition Related to"));
		lbConRelatedTo.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		tbConRelatedTo = new TextBox();
		tbConRelatedTo.setWidth(fieldsWidth);
		otherTable.setWidget(row, 0, lbConRelatedTo);
		otherTable.setWidget(row, 1, tbConRelatedTo);
		row++;
	}
	
	public void saveEpisodeOfCare(){
		HashMap<String, String> map=new HashMap<String, String>();
		String method = "";
		if (!isAdding) {
			method = "org.freemedsoftware.module.EpisodeOfCare.mod";
			map.put("id", modRecId);
		} else {
			method = "org.freemedsoftware.module.EpisodeOfCare.add";
		}
		
		map.put("eocdescrip", tbDesc.getText());
		if(dtFirstOcc.getTextBox().getText()!=null && !dtFirstOcc.getTextBox().getText().equals(""))
			map.put("eocstartdate", dtFirstOcc.getTextBox().getText());
		if(dtCurrOnset.getTextBox().getText()!=null && !dtCurrOnset.getTextBox().getText().equals(""))
			map.put("eocdtlastsimilar", dtCurrOnset.getTextBox().getText());
		if(reffPhy.getStoredValue()!=null){
			map.put("eocreferrer", reffPhy.getStoredValue());
		}
		if(facWidget.getStoredValue()!=null){
			map.put("eocfacility", facWidget.getStoredValue());
		}
		if(diagFamWidget.getStoredValue()!=null){
			map.put("eocdiagfamily", diagFamWidget.getStoredValue());
		}

		map.put("eocdistype", listDisability.getStoredValue());
		if(dsbFrDate.getTextBox().getText()!=null && !dsbFrDate.getTextBox().getText().equals(""))
			map.put("eocdisfromdt", dsbFrDate.getTextBox().getText());
		if(dsbToDate.getTextBox().getText()!=null && !dsbToDate.getTextBox().getText().equals(""))
			map.put("eocdistodt", dsbToDate.getTextBox().getText());
		if(dtDsbWrpDate.getTextBox().getText()!=null && !dtDsbWrpDate.getTextBox().getText().equals(""))
			map.put("eocdisworkdt", dtDsbWrpDate.getTextBox().getText());
		
		if(cbRelEmp.getValue()){
			map.put("eocrelemp", "yes");
			map.put("eocrelempname", tbEmpName.getText());
			map.put("eocrelempaddr1", tbEmployeeAdd1.getText());
			map.put("eocrelempaddr2", tbEmployeeAdd2.getText());
			map.put("eocrelempcity", tbEmployeeCity.getText());
			map.put("eocrelempstpr", tbEmployeeStProv.getText());
			map.put("eocrelempzip", tbEmployeePostCd.getText());
			map.put("eocrelempcountry", tbEmployeeCountry.getText());
			map.put("eocrelempfile", tbEmployeeFileNumber.getText());
			map.put("eocrelemprcname", tbEmployeeContactName.getText());
			map.put("eocrelemprcphone", tbEmployeeContactPhone.getText());
			map.put("eocrelemprcemail", tbEmployeeEmailAddress.getText());
		}
		else{
			map.put("eocrelemp", "no");
		}
		if(cbRelAuto.getValue()){
			map.put("eocrelauto", "yes");
			map.put("eocrelautoname", tbAutoIns.getText());
			map.put("eocrelautoaddr1", tbAutoAdd1.getText());
			map.put("eocrelautoaddr2", tbAutoAdd2.getText());
			map.put("eocrelautocity", tbAutoCity.getText());
			map.put("eocrelautostpr", tbAutoStProv.getText());
			map.put("eocrelautozip", tbAutoPostCd.getText());
			map.put("eocrelautocountry", tbAutoCountry.getText());
			map.put("eocrelautocase", tbAutoCaseNumber.getText());
			map.put("eocrelautorcname", tbAutoContactName.getText());
			map.put("eocrelautorcphone", tbAutoContactPhone.getText());
			map.put("eocrelautorcemail", tbAutoEmailAddress.getText());
			map.put("eocrelautotime", listHours.getStoredValue()+":"+listMinutes.getStoredValue()+":"+listAmPm.getStoredValue());
		}
		else{
			map.put("eocrelauto", "no");
		}
		
		if(cbRelPreg.getValue()){
			map.put("eocrelpreg", "yes");
			map.put("eocrelpregcycle", listCycleLength.getStoredValue());
			map.put("eocrelpreggravida", listGravida.getStoredValue());
			map.put("eocrelpregpara", listPara.getStoredValue());
			map.put("eocrelpregabort", listAbortions.getStoredValue());
			if(lastMensPrd.getTextBox().getText()!=null && !lastMensPrd.getTextBox().getText().equals(""))
				map.put("eocrelpreglastper", lastMensPrd.getTextBox().getText());
			if(dtOfConf.getTextBox().getText()!=null && !dtOfConf.getTextBox().getText().equals(""))
				map.put("eocrelpregconfine", dtOfConf.getTextBox().getText());
			map.put("eocrelpregmiscarry", listMiscarries.getStoredValue());
		}
		else{
			map.put("eocrelpreg", "no");
		}
		
		if(cbRelOtherCause.getValue()){
			map.put("eocrelother", "yes");
			map.put("eocrelothercomment", tbConRelatedTo.getText());
		}
		else{
			map.put("eocrelother", "no");
		}
		
		map.put("eocrelstpr", tbStateProv.getText());
		map.put("eocpatient", patientId+"");
		if(listEpisodeType.getSelectedIndex()>0){
			map.put("eoctype", listEpisodeType.getStoredValue());
		}
		if(hosAdmDt.getTextBox().getText()!=null && !hosAdmDt.getTextBox().getText().equals(""))
			map.put("eochosadmdt", hosAdmDt.getTextBox().getText());
		if(hosDscDt.getTextBox().getText()!=null && !hosDscDt.getTextBox().getText().equals(""))
			map.put("eochosdischrgdt", hosDscDt.getTextBox().getText());
		if(radHospital.getWidgetValue()!=null)
			map.put("eochospital", radHospital.getWidgetValue());
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { JsonUtil.jsonify(map) };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(method, params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {
						try{
							if (200 == response.getStatusCode()) {
								if (isAdding) {
									Integer r = (Integer) JsonUtil.shoehornJson(
											JSONParser.parseStrict(response.getText()),
											"Integer");
	
									if (r != 0) {
										Util
												.showInfoMsg("EpisodeOfCare",
														_("Episode of Care successfully created."));
										reset();
										loadEocList();
										tabPanel.selectTab(1);
									} else {
										
									}
	
								} else {
									Boolean r = (Boolean) JsonUtil.shoehornJson(
											JSONParser.parseStrict(response.getText()),
											"Boolean");
		
									if (r) {
										Util
												.showInfoMsg("EpisodeOfCare",
														_("Episode of Care successfully modified."));
										reset();
										loadEocList();
										tabPanel.selectTab(1);
									}
								}
							}
						}
						catch(Exception e){
							
						}
					}
				});
			} catch (RequestException e) {
			}
		}
		
	}
	
	public void createListScreen(){
		eocCustomTable = new CustomTable();
		eocCustomTable.setIndexName("id");
		// patientCustomTable.setSize("100%", "100%");
		eocCustomTable.setWidth("100%");
		eocCustomTable.addColumn(_("Starting Date"), "eocstartdate");
		eocCustomTable.addColumn(_("Description"), "eocdescrip");
		eocCustomTable.addColumn(_("Action"), "action");
		
		
		eocCustomTable.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				final CustomActionBar actionBar = new CustomActionBar(data);
				actionBar.applyPermissions(false, false, CurrentState.isActionAllowed("EpisodeOfCare", AppConstants.DELETE), CurrentState.isActionAllowed("EpisodeOfCare", AppConstants.MODIFY), false);
					
				actionBar.setHandleCustomAction(new HandleCustomAction(){
					@Override
					public void handleAction(int id,
							HashMap<String, String> data, int action) {
						if(action == HandleCustomAction.MODIFY){
								try {									
									reset();
									isAdding = false;
									loadValues(data);
									modRecId=""+id;
									btnAdd.setText("Modify");
									tabPanel.selectTab(0);
								} catch (Exception e) {
									GWT.log("Caught exception: ", e);
								}
							
						}
						else if(action == HandleCustomAction.DELETE){
							deleteEOC(data.get("id"));
						}
					}
				});
				// Push value back to table
				return actionBar;
			}
		});
		
		listPanel.add(eocCustomTable);
		loadEocList();
	}
	
	public void loadValues(HashMap<String, String> map){
		if(map.get("eocdescrip")!=null)
			tbDesc.setText(map.get("eocdescrip"));
		if(map.get("eocstartdate")!=null && !map.get("eocstartdate").equals("0000-00-00"))
			dtFirstOcc.setValue(map.get("eocstartdate"));
		if(map.get("eocdtlastsimilar")!=null && !map.get("eocdtlastsimilar").equals("0000-00-00"))
			dtCurrOnset.setValue(map.get("eocdtlastsimilar"));
		if(map.get("eocreferrer")!=null && !map.get("eocreferrer").equals("0"))
			reffPhy.setValue(new Integer(map.get("eocreferrer")));
		if(map.get("eocfacility")!=null && !map.get("eocfacility").equals("0"))
			facWidget.setValue(new Integer(map.get("eocfacility")));
		if(map.get("eocdiagfamily")!=null && !map.get("eocdiagfamily").equals("0"))
			diagFamWidget.setCommaSeparatedValues(map.get("eocdiagfamily"));
		if(map.get("eocdistype")!=null && !map.get("eocdistype").equals(""))
			listDisability.setWidgetValue(map.get("eocdistype"));
		if(map.get("eocdisfromdt")!=null && !map.get("eocdisfromdt").equals("0000-00-00"))
			dsbFrDate.setValue(map.get("eocdisfromdt"));
		if(map.get("eocdistodt")!=null && !map.get("eocdistodt").equals("0000-00-00"))
			dsbToDate.setValue(map.get("eocdistodt"));
		if(map.get("eocdistodt")!=null && !map.get("eocdistodt").equals("0000-00-00"))
			dtDsbWrpDate.setValue(map.get("eocdistodt"));
		if(map.get("eocrelemp")!=null && map.get("eocrelemp").equals("yes")){
			cbRelEmp.setValue(true, true);
			if(map.get("eocrelempname")!=null)
				tbEmpName.setText(map.get("eocrelempname"));
			if(map.get("eocrelempaddr1")!=null)
				tbEmployeeAdd1.setText(map.get("eocrelempaddr1"));
			if(map.get("eocrelempaddr2")!=null)
				tbEmployeeAdd2.setText(map.get("eocrelempaddr2"));
			if(map.get("eocrelempcity")!=null)
				tbEmployeeCity.setText(map.get("eocrelempcity"));
			if(map.get("eocrelempstpr")!=null)
				tbEmployeeStProv.setText(map.get("eocrelempstpr"));
			if(map.get("eocrelempzip")!=null)
				tbEmployeePostCd.setText(map.get("eocrelempzip"));
			if(map.get("eocrelempcountry")!=null)
				tbEmployeeCountry.setText(map.get("eocrelempcountry"));
			if(map.get("eocrelempfile")!=null)
				tbEmployeeFileNumber.setText(map.get("eocrelempfile"));
			if(map.get("eocrelemprcname")!=null)
				tbEmployeeContactName.setText(map.get("eocrelemprcname"));
			if(map.get("eocrelemprcphone")!=null)
				tbEmployeeContactPhone.setText(map.get("eocrelemprcphone"));
			if(map.get("eocrelemprcemail")!=null)
				tbEmployeeEmailAddress.setText(map.get("eocrelemprcemail"));
		}
		if(map.get("eocrelauto")!=null && map.get("eocrelauto").equals("yes")){
			cbRelAuto.setValue(true, true);
			if(map.get("eocrelautoname")!=null)
				tbAutoIns.setText(map.get("eocrelautoname"));
			if(map.get("eocrelautoaddr1")!=null)
				tbAutoAdd1.setText(map.get("eocrelautoaddr1"));
			if(map.get("eocrelautoaddr2")!=null)
				tbAutoAdd2.setText(map.get("eocrelautoaddr2"));
			if(map.get("eocrelautocity")!=null)
				tbAutoCity.setText(map.get("eocrelautocity"));
			if(map.get("eocrelautostpr")!=null)
				tbAutoStProv.setText(map.get("eocrelautostpr"));
			if(map.get("eocrelautozip")!=null)
				tbAutoPostCd.setText(map.get("eocrelautozip"));
			if(map.get("eocrelautocountry")!=null)
				tbAutoCountry.setText(map.get("eocrelautocountry"));
			if(map.get("eocrelautocase")!=null)
				tbAutoCaseNumber.setText(map.get("eocrelautocase"));
			if(map.get("eocrelautorcname")!=null)
				tbAutoContactName.setText(map.get("eocrelautorcname"));
			if(map.get("eocrelautorcphone")!=null)
				tbAutoContactPhone.setText(map.get("eocrelautorcphone"));
			if(map.get("eocrelautorcemail")!=null)
				tbAutoEmailAddress.setText(map.get("eocrelautorcemail"));
			
			if(map.get("eocrelautotime")!=null){
				String [] time=map.get("eocrelautotime").split(":");
				if(time.length==3){
					listHours.setWidgetValue(time[0]);
					listMinutes.setWidgetValue(time[1]);
					listAmPm.setWidgetValue(time[2]);
				}
			}
			
		}
		if(map.get("eocrelpreg")!=null && map.get("eocrelpreg").equals("yes")){
			cbRelPreg.setValue(true, true);
			if(map.get("eocrelpregcycle")!=null)
				listCycleLength.setWidgetValue(map.get("eocrelpregcycle"));
			if(map.get("eocrelpreggravida")!=null)
				listGravida.setWidgetValue(map.get("eocrelpreggravida"));
			if(map.get("eocrelpregpara")!=null)
				listPara.setWidgetValue(map.get("eocrelpregpara"));
			if(map.get("eocrelpregabort")!=null)
				listAbortions.setWidgetValue(map.get("eocrelpregabort"));
			if(map.get("eocrelpreglastper")!=null && !map.get("eocrelpreglastper").equals("0000-00-00"))
				lastMensPrd.getTextBox().setText(map.get("eocrelpreglastper"));
			if(map.get("eocrelpregconfine")!=null && !map.get("eocrelpregconfine").equals("0000-00-00"))
				dtOfConf.getTextBox().setText(map.get("eocrelpregconfine"));
			if(map.get("eocrelpregmiscarry")!=null)
				listMiscarries.setWidgetValue(map.get("eocrelpregmiscarry"));
		}
		
		if(map.get("eocrelother")!=null && map.get("eocrelother").equals("yes")){
			cbRelOtherCause.setValue(true, true);
			if(map.get("eocrelothercomment")!=null)
				tbConRelatedTo.setText(map.get("eocrelothercomment"));
		}
		if(map.get("eocrelstpr")!=null)
			tbStateProv.setText(map.get("eocrelstpr"));
		if(map.get("eocrelother")!=null && !map.get("eoctype").equals("0"))
			listEpisodeType.setWidgetValue(map.get("eoctype"));
		if(map.get("eochosadmdt")!=null && !map.get("eochosadmdt").equals("0000-00-00"))
			hosAdmDt.getTextBox().setText(map.get("eochosadmdt"));
		if(map.get("eochosdischrgdt")!=null && !map.get("eochosdischrgdt").equals("0000-00-00"))
			hosDscDt.getTextBox().setText(map.get("eochosdischrgdt"));
		if(map.get("eochospital")!=null)
			radHospital.setWidgetValue(map.get("eochospital"));
		isAdding=false;
		
	}
	public void loadEocList(){
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {

			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EpisodeOfCare.getAllValues",
											params)));

			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
					}

					public void onResponseReceived(Request request,
							Response response) {

						if (200 == response.getStatusCode()) {
							try {
								@SuppressWarnings("unchecked")
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {							
									eocCustomTable.loadData(r);
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
	
	public void deleteEOC(String id) {
		if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { id };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.EpisodeOfCare.del",
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
										.shoehornJson(JSONParser.parseStrict(response
												.getText()),
												"Boolean");
								if(r){
									Util
									.showInfoMsg("EpisodeOfCare",
											_("Episode Of Care successfully deleted."));
									loadEocList();
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
	
	public void loadData(){
		createListScreen();
	}
	
	public void reset(){
		pregnencyPanel=null;
		automobilePanel=null;
		employementPanel=null;
		otherPanel=null;
		isAdding=true;
		btnAdd.setText(_("Add"));
		entryPanel.clear();
		createEntryScreen();
	}
}
