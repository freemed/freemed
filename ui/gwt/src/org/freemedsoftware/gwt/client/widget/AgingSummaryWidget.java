package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.VerticalPanel;

public class AgingSummaryWidget extends Composite{
	CustomRequestCallback callback=null;
	public AgingSummaryWidget(CustomRequestCallback cb){
		callback=cb;
		VerticalPanel panel = new VerticalPanel();
		panel.setSpacing(10);
		initWidget(panel);
		final CustomTable agingSummaryTable = new CustomTable();
		agingSummaryTable.setSize("100%", "100%");
		agingSummaryTable.setIndexName("payer_id");
		agingSummaryTable.addColumn("Payer", "payer_name");
		agingSummaryTable.addColumn("0-30", "amount_0_30");
		agingSummaryTable.addColumn("C", "claims_0_30");
		agingSummaryTable.addColumn("31-60", "amount_31_60");
		agingSummaryTable.addColumn("C", "claims_31_60");
		agingSummaryTable.addColumn("61-90", "amount_61_90");
		agingSummaryTable.addColumn("C", "claims_61_90");
		agingSummaryTable.addColumn("91-120", "amount_91_120");
		agingSummaryTable.addColumn("C", "claims_91_120");
		agingSummaryTable.addColumn("120+", "amount_120plus");
		agingSummaryTable.addColumn("C", "claims_120plus");
		agingSummaryTable.addColumn("Total Claims", "ev_claims");
		agingSummaryTable.addColumn("Total Amount", "ev_amount");
		final AgingSummaryWidget asw = this;
		agingSummaryTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				try {
					if (col == 0){						
						HashMap<String, String> map=new HashMap<String, String>();
						map.put("payer", data.get("payer_id"));
						map.put("payer_name", data.get("payer_name"));
						asw.removeFromParent();
						callback.jsonifiedData(map);
					}
					if (col == 1){
						HashMap<String, String> map=new HashMap<String, String>();
						map.put("payer", data.get("payer_id"));
						map.put("payer_name", data.get("payer_name"));
						map.put("aging", "0-30");
						asw.removeFromParent();
						callback.jsonifiedData(map);
					}
					if (col == 3){
						HashMap<String, String> map=new HashMap<String, String>();
						map.put("payer", data.get("payer_id"));
						map.put("payer_name", data.get("payer_name"));
						map.put("aging", "31-60");
						asw.removeFromParent();
						callback.jsonifiedData(map);
					}
					if (col == 5){
						HashMap<String, String> map=new HashMap<String, String>();
						map.put("payer", data.get("payer_id"));
						map.put("payer_name", data.get("payer_name"));
						map.put("aging", "61-90");	
						asw.removeFromParent();
						callback.jsonifiedData(map);
					}
					if (col == 7){
						HashMap<String, String> map=new HashMap<String, String>();
						map.put("payer", data.get("payer_id"));
						map.put("payer_name", data.get("payer_name"));
						map.put("aging", "91-120");
						asw.removeFromParent();
						callback.jsonifiedData(map);
					}
					if (col == 9){
						HashMap<String, String> map=new HashMap<String, String>();
						map.put("payer", data.get("payer_id"));
						map.put("payer_name", data.get("payer_name"));
						map.put("aging", "120+");
						asw.removeFromParent();
						callback.jsonifiedData(map);
					}			
					
				} catch (Exception e) {
					JsonUtil.debug("ClaimManager.java: Caught exception: "
							+ e.toString());
				}
			}
		});
		Util.callApiMethod("ClaimLog", "aging_summary_formatted", (Integer)null,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> [] result = (HashMap<String, String>[]) data;
							agingSummaryTable.loadData(result);
						}
					}
				}, "HashMap<String,String>[]");
		panel.add(agingSummaryTable);
		
		CustomButton closeBtn=new CustomButton("Close",AppConstants.ICON_CANCEL);
		closeBtn.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent event) {				
				asw.removeFromParent();
				callback.jsonifiedData("cancel");
			}

		});
		
		panel.add(closeBtn);
		
	}
}
