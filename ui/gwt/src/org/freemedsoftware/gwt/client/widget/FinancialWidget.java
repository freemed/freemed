package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientScreen;

import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class FinancialWidget extends WidgetInterface{
	public final static String moduleName = PatientScreen.moduleName;
	protected CustomTable financialTable;
	protected int maximumRows = 10;
	protected Integer patientId = new Integer(0);
	protected Label lbArrears;
	public FinancialWidget(){
		super(moduleName);
		VerticalPanel panel = new VerticalPanel();
		panel.setSpacing(1);
		initWidget(panel);
		financialTable = new CustomTable();
		financialTable.setIndexName("id");
		financialTable.setMaximumRows(maximumRows);
		financialTable.addColumn("Charge", "charge");
		financialTable.addColumn("Payment", "payment");
		financialTable.addColumn("Arrear", "arrear");
		financialTable.addColumn("DOS", "dos");
		financialTable.getFlexTable().getFlexCellFormatter().setWidth(0, 0, "70px");
		financialTable.getFlexTable().getFlexCellFormatter().setWidth(0, 1, "70px");
		financialTable.getFlexTable().getFlexCellFormatter().setWidth(0, 2, "70px");
		
		HTML html=new HTML("<hr/>");
		html.setWidth("100%");
		
		HorizontalPanel arrearsPanel=new HorizontalPanel();
		Label lb=new Label("Total Arrears  =  ");
		lb.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
		lbArrears = new Label("0");
		arrearsPanel.add(lb);
		arrearsPanel.add(lbArrears);
		panel.add(financialTable);
		panel.add(html);
		panel.add(arrearsPanel);
		
	}
	
	
	public void setPatientId(Integer id) {
		patientId = id;
		// Call initial data load, as patient id is set
		loadData();
	}
	
	public void loadData(){
		ArrayList params1=new ArrayList();
		params1.add(patientId.toString());
		Util.callModuleMethod("ProcedureModule", "getNonZeroBalProcs",
				params1, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String> [] result = (HashMap[]) data;	
							if(result.length!=0){
								financialTable.loadData(result);
							}

							
						}
					}
				}, "HashMap<String,String>[]");
		ArrayList params2=new ArrayList();
		params2.add(patientId.toString());
		Util.callModuleMethod("ProcedureModule", "getTotalArrears",
				params2, new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							final HashMap<String, String>  result = (HashMap) data;	
							if(result.get("tarrears")!=null){
								lbArrears.setText(result.get("tarrears"));
							}

							
						}
					}
				}, "HashMap<String,String>");
		
	}
	public void setMaximumRows(int maxRows) {	
		maximumRows=maxRows;
		financialTable.setMaximumRows(maxRows);
	}
}
