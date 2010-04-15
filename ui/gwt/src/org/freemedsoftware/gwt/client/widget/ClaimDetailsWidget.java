package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.screen.ClaimsManager;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.patient.ProcedureScreen;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;

public class ClaimDetailsWidget  extends Composite{
	Integer claimid;
	Integer patientId;
	String patientName;
	protected VerticalPanel vpanel;
	protected VerticalPanel claimDetailsPanel;
	protected VerticalPanel newClaimEventPanel;
	protected Label lbPatientVal;
	protected CustomRequestCallback callback;
	public ClaimDetailsWidget(Integer id, Integer ptid,String ptName,CustomRequestCallback c){
		claimid=id;
		callback=c;
		patientName=ptName;
		patientId=ptid;
		vpanel = new VerticalPanel();
		vpanel.setWidth("100%");
		initWidget(vpanel);
		
		claimDetailsPanel = new VerticalPanel();
		newClaimEventPanel = new VerticalPanel();
		vpanel.add(claimDetailsPanel);
		vpanel.add(newClaimEventPanel);
		createClaimDetailsPanel();
	}
	
	public void createClaimDetailsPanel(){
		newClaimEventPanel.clear();
		claimDetailsPanel.clear();
		newClaimEventPanel.setVisible(false);
		claimDetailsPanel.setVisible(true);
		FlexTable claimsInfoParentTable=new FlexTable();
		claimsInfoParentTable.setBorderWidth(1	);
		claimsInfoParentTable.getElement().getStyle().setProperty("borderCollapse", "collapse");
		claimDetailsPanel.setWidth("100%");
		claimDetailsPanel.setHorizontalAlignment(VerticalPanel.ALIGN_CENTER);
		claimDetailsPanel.setSpacing(5);
		claimsInfoParentTable.setWidth("100%");
		claimDetailsPanel.add(claimsInfoParentTable);
		
		Label lbCovInfo=new Label("Coverage Information");
		lbCovInfo.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
		lbCovInfo.getElement().getStyle().setProperty("fontSize", "15px");
		lbCovInfo.getElement().getStyle().setProperty("textDecoration", "underline");
		lbCovInfo.getElement().getStyle().setProperty("fontWeight", "bold");
		
		FlexTable covInfoTable=new FlexTable();
		covInfoTable.setWidth("100%");
		final FlexTable covTypesTable=new FlexTable();
		covTypesTable.setWidth("100%");
		VerticalPanel covInfoVPanel=new VerticalPanel();
		covInfoVPanel.setSpacing(10);
		covInfoVPanel.setWidth("95%");
		
		covInfoVPanel.add(lbCovInfo);
		covInfoVPanel.add(covInfoTable);
		covInfoVPanel.add(covTypesTable);
		claimsInfoParentTable.setWidget(0, 0, covInfoVPanel);
		claimsInfoParentTable.getFlexCellFormatter().getElement(0, 0).setAttribute("width", "50%");
		claimsInfoParentTable.getFlexCellFormatter().setVerticalAlignment(0, 0, HasVerticalAlignment.ALIGN_TOP);
		
		Label lbPatient=new Label("Patient:");
		lbPatient.setStyleName("label_bold");
		lbPatientVal = new Label();
		Label lbdob=new Label("Date of Birth:");
		lbdob.setStyleName("label_bold");
		final Label lbdobVal=new Label();
		Label lbSSN=new Label("SSN:");
		lbSSN.setStyleName("label_bold");
		final Label lbSSNVal=new Label();
		
		covInfoTable.setWidget(0, 0, lbPatient);
		covInfoTable.setWidget(0, 1, lbPatientVal);
		covInfoTable.setWidget(0, 2, lbdob);
		covInfoTable.setWidget(0, 3, lbdobVal);
		covInfoTable.setWidget(0, 4, lbSSN);
		covInfoTable.setWidget(0, 5, lbSSNVal);
		
		final Label lbRespParty=new Label("Resp. Party:");
		lbRespParty.setStyleName("label_bold");
		final Label lbRespPartyVal=new Label();
		final Label lbRpDob=new Label("Date of Birth:");
		lbRpDob.setStyleName("label_bold");
		final Label lbRpDobVal=new Label();
		final Label lbRpSSN=new Label("SSN:");
		lbRpSSN.setStyleName("label_bold");
		final Label lbRpSSNVal=new Label();
		
		covInfoTable.setWidget(1, 0, lbRespParty);
		covInfoTable.setWidget(1, 1, lbRespPartyVal);
		covInfoTable.setWidget(1, 2, lbRpDob);
		covInfoTable.setWidget(1, 3, lbRpDobVal);
		covInfoTable.setWidget(1, 4, lbRpSSN);
		covInfoTable.setWidget(1, 5, lbRpSSNVal);
		
		final Label lbPrimary=new Label("Primary Coverage/Location/Ins. No./Copay/Deductible");
		lbPrimary.setStyleName("label_bold");
		final Label lbPrimaryVal=new Label();
		
		covTypesTable.setWidget(0, 0, lbPrimary);
		covTypesTable.getFlexCellFormatter().setVerticalAlignment(0, 0, HasVerticalAlignment.ALIGN_TOP);
		covTypesTable.setWidget(0, 1, lbPrimaryVal);
		covTypesTable.getFlexCellFormatter().setVerticalAlignment(0, 1, HasVerticalAlignment.ALIGN_TOP);

		final Label lbSecondary=new Label("Secondary Coverage/Location/Ins. No./Copay/Deductible");
		lbSecondary.setStyleName("label_bold");
		final Label lbSecondaryVal=new Label();
		
		covTypesTable.setWidget(1, 0, lbSecondary);
		covTypesTable.getFlexCellFormatter().setVerticalAlignment(1, 0, HasVerticalAlignment.ALIGN_TOP);
		covTypesTable.setWidget(1, 1, lbSecondaryVal);
		covTypesTable.getFlexCellFormatter().setVerticalAlignment(1, 0, HasVerticalAlignment.ALIGN_TOP);
		
		final Label lbTertiary=new Label("Tertiary Coverage/Location/Ins. No./Copay/Deductible");
		lbTertiary.setStyleName("label_bold");
		final Label lbTertiaryVal=new Label();
		
		covTypesTable.setWidget(2, 0, lbTertiary);
		covTypesTable.getFlexCellFormatter().setVerticalAlignment(2, 0, HasVerticalAlignment.ALIGN_TOP);
		covTypesTable.setWidget(2, 1, lbTertiaryVal);
		covTypesTable.getFlexCellFormatter().setVerticalAlignment(2, 0, HasVerticalAlignment.ALIGN_TOP);
		
		Label lbClaimInfo=new Label("Claim Information");
		lbClaimInfo.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
		lbClaimInfo.getElement().getStyle().setProperty("fontSize", "15px");
		lbClaimInfo.getElement().getStyle().setProperty("textDecoration", "underline");
		lbClaimInfo.getElement().getStyle().setProperty("fontWeight", "bold");
		
		final FlexTable clInfoTable=new FlexTable();
		clInfoTable.setWidth("100%");
		
		VerticalPanel clInfoVPanel=new VerticalPanel();
		clInfoVPanel.setSpacing(10);
		clInfoVPanel.setWidth("95%");
		
		clInfoVPanel.add(lbClaimInfo);
		clInfoVPanel.add(clInfoTable);
		claimsInfoParentTable.setWidget(0, 1, clInfoVPanel);
		claimsInfoParentTable.getFlexCellFormatter().setVerticalAlignment(0, 1, HasVerticalAlignment.ALIGN_TOP);

		Label lbDos=new Label("Date of Service:");
		lbDos.setStyleName("label_bold");
		final Label lbDosVal=new Label();	
		
		clInfoTable.setWidget(0, 0, lbDos);
		clInfoTable.setWidget(0, 1, lbDosVal);
		
		Label lbProvider=new Label("Provider:");
		lbProvider.setStyleName("label_bold");
		final Label lbProviderVal=new Label();		
		Label lbRefProv=new Label("Referring Provider:");
		lbRefProv.setStyleName("label_bold");
		final Label lbRefProvVal=new Label();
		
		clInfoTable.setWidget(1, 0, lbProvider);
		clInfoTable.setWidget(1, 1, lbProviderVal);
		clInfoTable.setWidget(1, 2, lbRefProv);
		clInfoTable.setWidget(1, 3, lbRefProvVal);	
				
		
		Label lbPOS=new Label("POS:");
		lbPOS.setStyleName("label_bold");
		final Label lbPOSVal=new Label();
		Label lbCharges=new Label("Charges:");
		lbCharges.setStyleName("label_bold");
		final Label lbChargesVal=new Label();
		
		clInfoTable.setWidget(2, 0, lbPOS);
		clInfoTable.setWidget(2, 1, lbPOSVal);
		clInfoTable.setWidget(2, 2, lbCharges);
		clInfoTable.setWidget(2, 3, lbChargesVal);			
		
		Label lbCPT=new Label("CPT:");
		lbCPT.setStyleName("label_bold");
		final Label lbCPTVal=new Label();
		Label lbPaid=new Label("Paid:");
		lbPaid.setStyleName("label_bold");
		final Label lbPaidVal=new Label();
		
		clInfoTable.setWidget(3, 0, lbCPT);
		clInfoTable.setWidget(3, 1, lbCPTVal);
		clInfoTable.setWidget(3, 2, lbPaid);
		clInfoTable.setWidget(3, 3, lbPaidVal);			
		
		Label lbICD=new Label("ICD:");
		lbICD.setStyleName("label_bold");
		final Label lbICDVal=new Label();		
		Label lbBalance=new Label("Balance:");
		lbBalance.setStyleName("label_bold");
		final Label lbBalanceVal=new Label();

		clInfoTable.setWidget(4, 0, lbICD);
		clInfoTable.setWidget(4, 1, lbICDVal);
		clInfoTable.setWidget(4, 2, lbBalance);
		clInfoTable.setWidget(4, 3, lbBalanceVal);			
		
		
		ArrayList<String> params=new ArrayList<String>();
		params.add(""+claimid);
		Util.callApiMethod("ClaimLog", "claim_information", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> result = (HashMap<String, String>) data;
							if(result.get("patient")!=null)
								lbPatientVal.setText(result.get("patient"));
							if(result.get("rp_name")!=null){
								lbRespPartyVal.setText(result.get("rp_name"));
								if(result.get("rp_name").toString().equalsIgnoreCase("self")){
									lbRpDob.setVisible(false);
									lbRpDobVal.setVisible(false);
									lbRpSSN.setVisible(false);
									lbRpSSNVal.setVisible(false);
								}
							}
							else{
								lbRespParty.setVisible(false);
								lbRespPartyVal.setVisible(false);
								lbRpDob.setVisible(false);
								lbRpDobVal.setVisible(false);
								lbRpSSN.setVisible(false);
								lbRpSSNVal.setVisible(false);
							}
							if(result.get("patient_dob")!=null)
								lbdobVal.setText(result.get("patient_dob"));
							if(result.get("provider_name")!=null)
								lbProviderVal.setText(result.get("provider_name"));
							if(result.get("ssn")!=null)
								lbSSNVal.setText(result.get("ssn"));
							if(result.get("rp_ssn")!=null)
								lbRpSSNVal.setText(result.get("rp_ssn"));
							if(result.get("rp_dob")!=null)
								lbRpDobVal.setText(result.get("rp_dob"));
							if(result.get("ref_provider_name")!=null)
								lbRefProvVal.setText(result.get("ref_provider_name"));
							if(result.get("prim_cov")!=null && !result.get("prim_cov").equals(""))
							{
								lbPrimaryVal.setText(result.get("prim_cov"));
								if(result.get("prim_copay")!=null)
									lbPrimaryVal.setText(lbPrimaryVal.getText()+"/"+result.get("prim_copay"));
								if(result.get("prim_deduct")!=null)
									lbPrimaryVal.setText(lbPrimaryVal.getText()+"/"+result.get("prim_deduct"));
							}
							else{
								lbPrimary.setVisible(false);
								lbPrimaryVal.setVisible(false);
							}
							
							if(result.get("sec_cov")!=null  && !result.get("sec_cov").equals("")){
								lbSecondaryVal.setText(result.get("sec_cov"));
								if(result.get("sec_copay")!=null)
									lbSecondaryVal.setText(lbSecondaryVal.getText()+"/"+result.get("sec_copay"));
								if(result.get("sec_deduct")!=null)
									lbSecondaryVal.setText(lbSecondaryVal.getText()+"/"+result.get("sec_deduct"));
							}
							else{
								lbSecondary.setVisible(false);
								lbSecondaryVal.setVisible(false);
							}
							
							if(result.get("ter_cov")!=null  && !result.get("ter_cov").equals(""))
							{
								lbTertiaryVal.setText(result.get("ter_cov"));
								if(result.get("ter_copay")!=null)
									lbTertiaryVal.setText(lbTertiaryVal.getText()+"/"+result.get("ter_copay"));
								if(result.get("ter_deduct")!=null)
									lbTertiaryVal.setText(lbTertiaryVal.getText()+"/"+result.get("ter_deduct"));
							}
							else{
								lbTertiary.setVisible(false);
								lbTertiaryVal.setVisible(false);
							}
							
							if(result.get("facility")!=null)
								lbPOSVal.setText(result.get("facility"));
							if(result.get("service_date")!=null)
								lbDosVal.setText(result.get("service_date"));
							if(result.get("diagnosis")!=null)
								lbICDVal.setText(result.get("diagnosis"));
							if(result.get("cpt_code")!=null)
								lbCPTVal.setText(result.get("cpt_code"));							
							if(result.get("fee")!=null)
								lbChargesVal.setText(result.get("fee"));
							if(result.get("paid")!=null)
								lbPaidVal.setText(result.get("paid"));
							if(result.get("balance")!=null)
								lbBalanceVal.setText(result.get("balance"));
						}
					}
				}, "HashMap<String,String>");
		HorizontalPanel actionPanel=new HorizontalPanel();
		actionPanel.setSpacing(5);
		final Button addEventBtn = new Button("Add Event");
		addEventBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				createClaimLogEventEntryPanel();
			}

		});
		final Button editClaimBtn = new Button("Modify Claim");
		editClaimBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				PatientScreen p = new PatientScreen();
				p.setPatient(patientId);
				Util.spawnTab(patientName, p);
				ProcedureScreen ps =  new ProcedureScreen();
				ps.setModificationRecordId(claimid);
				ps.setPatientId(patientId);
				ps.loadData();
				Util.spawnTabPatient("Manage Procedures", ps, p);
				ps.loadData();
			}

		});
		final Button cancelBtn = new Button("Return to Search");
		final ClaimDetailsWidget cdw = this;
		cancelBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {				
				cdw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		
		final Button newSearchBtn = new Button("New Search");
		newSearchBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {				
				cdw.removeFromParent();
				callback.jsonifiedData("new");
			}

		});
		actionPanel.add(addEventBtn);
		actionPanel.add(cancelBtn);
		actionPanel.add(newSearchBtn);
		actionPanel.add(editClaimBtn);
		claimDetailsPanel.add(actionPanel);
		
		final CustomTable claimsLogTable = new CustomTable();
		claimsLogTable.setAllowSelection(false);
		claimsLogTable.setSize("100%", "100%");
		claimsLogTable.addColumn("Date", "date");
		claimsLogTable.addColumn("User", "user");
		claimsLogTable.addColumn("Action", "action");
		claimsLogTable.addColumn("Comment", "comment");
		Util.callApiMethod("ClaimLog", "events_for_procedure", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> [] result = (HashMap<String, String>[]) data;
							claimsLogTable.loadData(result);
						}
					}
				}, "HashMap<String,String>[]");
		claimDetailsPanel.add(claimsLogTable);
	}
	
	public void createClaimLogEventEntryPanel(){
		newClaimEventPanel.setVisible(true);
		newClaimEventPanel.setSpacing(5);
		claimDetailsPanel.setVisible(false);
		Label lbAction=new Label("Action");
		final CustomListBox listAction=new CustomListBox();
		listAction.addItem("None Selected");
		listAction.addItem("Call","Call");
		listAction.addItem("Email","Email");
		
		Label lbComment=new Label("Comment");
		final TextArea commenttb=new TextArea();
		FlexTable eventEntryTable=new FlexTable();
		eventEntryTable.setWidget(0, 0, lbAction);
		eventEntryTable.setWidget(0, 1, listAction);
		eventEntryTable.setWidget(1, 0, lbComment);
		eventEntryTable.setWidget(1, 1, commenttb);
		
		HorizontalPanel actionPanel=new HorizontalPanel();
		actionPanel.setSpacing(5);
		final Button addEventBtn = new Button("Add Event");
		addEventBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {
				String msg="";
				if(listAction.getSelectedIndex()==0){
					msg+="Please select an action. \n";
				}
				if(commenttb.getText()==null || commenttb.getText().equals("")){
					msg+="Please enter a comment. \n";
				}
				if(msg.equals("")){
					final HashMap<String, String> rec = new HashMap<String, String>();
					// putting all columns of table .....
					rec.put((String) "action", (String) listAction.getStoredValue());
					rec.put((String) "comment", (String) commenttb.getText());
					ArrayList params=new ArrayList();
					params.add(claimid);
					params.add(rec);
					Util.callApiMethod("ClaimLog", "log_event", params,
							new CustomRequestCallback() {
								@Override
								public void onError() {
								}

								@Override
								public void jsonifiedData(Object data) {
									if (data != null) {
										createClaimDetailsPanel();
									}
								}
							}, "Integer");
				}
				else{
					Window.alert(msg);
				}
			}

		});
		final Button claimDetailsBtn = new Button("Claim Details");
		claimDetailsBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {				
				createClaimDetailsPanel();
			}

		});
		actionPanel.add(addEventBtn);
		actionPanel.add(claimDetailsBtn);
		newClaimEventPanel.add(eventEntryTable);
		newClaimEventPanel.add(actionPanel);		
	}
	
	
	
	
	
}
