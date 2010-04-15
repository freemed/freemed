package org.freemedsoftware.gwt.client.screen.patient;

import java.util.ArrayList;
import java.util.HashMap;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomRadioButtonGroup;
import org.freemedsoftware.gwt.client.widget.CustomTable;
import org.freemedsoftware.gwt.client.widget.LedgerWidget;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.LedgerWidget.PayCategory;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class AdvancePayment  extends PatientEntryScreenInterface{

	protected VerticalPanel entryVerticalPanel;
	protected CustomListBox actionTypeList;
	protected VerticalPanel ledgerWigetContainer;
	protected VerticalPanel dataVerticalPanel;
	protected CustomTable advPaymentsViewTable;
	public AdvancePayment(){
		entryVerticalPanel = new VerticalPanel();
		entryVerticalPanel.setSize("100%", "100%");
		entryVerticalPanel.setSpacing(5);
		
		dataVerticalPanel = new VerticalPanel();
		dataVerticalPanel.setSize("100%", "100%");
		
		VerticalPanel vpanel=new VerticalPanel();
		dataVerticalPanel.setSize("100%", "100%");
		dataVerticalPanel.setSpacing(5);
		vpanel.add(entryVerticalPanel);
		vpanel.add(dataVerticalPanel);
		
		advPaymentsViewTable = new CustomTable();
		advPaymentsViewTable.setIndexName("Id");
		advPaymentsViewTable.setSize("100%", "100%");
		advPaymentsViewTable.addColumn("Payment Amount", "amount");
		advPaymentsViewTable.addColumn("Payment Date", "pay_date");
		advPaymentsViewTable.addColumn("Description", "descp");
		advPaymentsViewTable.addColumn("Payment Category", "category");
		dataVerticalPanel.add(advPaymentsViewTable);
		initWidget(vpanel);		
	}
	
	public void loadUI(){
		ArrayList<String> params=new ArrayList<String>();
		params.add(""+patientId);
		Util.callModuleMethod("PaymentModule", "getAdvancePaymentInfo", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						if (data != null) {
							HashMap<String, String> [] result = (HashMap<String, String>[]) data;
							if(result!=null){
								if(result.length>0){
									advPaymentsViewTable.loadData(result);
								}
							}
						}
					}
				}, "HashMap<String,String>[]");
		
		
		ledgerWigetContainer=new VerticalPanel();
		//actionTypeGroup.setWidth("40%");
		// radioButtonPanel.add(yesNoRadionButtonGroup);

		final CustomRequestCallback cb=new CustomRequestCallback(){
			@Override
			public void onError() {

			}
			@Override
			public void jsonifiedData(Object data) {
					closeScreen();
			}
		};
		LedgerWidget pw=new LedgerWidget("0",patientId.toString(),"",PayCategory.PAYMENT,cb);
		ledgerWigetContainer.clear();
		ledgerWigetContainer.add(pw);		
		entryVerticalPanel.add(ledgerWigetContainer);
	}
}
