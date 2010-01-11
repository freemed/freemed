package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class LotManagement extends Composite {


	Calendar cal = new GregorianCalendar();

	
	protected String SiteId;
	protected Integer id;
	
	//boolean bottleNO=false,manufactureDate=false,expiryDate=false,bottlesQty=false;
	//String required=new String("");
	
	public String getSiteId() {
		return SiteId;
	}

	public void setSiteId(String siteId) {
		SiteId = siteId;
	}  
	public Integer getId() {
		return id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public class Bottle {
		protected String lotrecbottleno, lotrecemptywt, lotrecqtytotal;
		protected String lotrecmfgdate, lotrecexpdate;	
		protected String lotrec20k, lotrec40k;
		
		public String getLotrec20k() {
			return lotrec20k;
		}

		public void setLotrec20k(String lotrec20k) {
			this.lotrec20k = lotrec20k;
		}

		public String getLotrec40k() {
			return lotrec40k;
		}

		public void setLotrec40k(String lotrec40k) {
			this.lotrec40k = lotrec40k;
		}

		public String getLotrecbottleno() {
			return lotrecbottleno;
		}

		public void setLotrecbottleno(String lotrecbottleno) {
			this.lotrecbottleno = lotrecbottleno;
		}

		public String getLotrecemptywt() {
			return lotrecemptywt;
		}

		public void setLotrecemptywt(String lotrecemptywt) {
			this.lotrecemptywt = lotrecemptywt;
		}

		public String getLotrecqtytotal() {
			return lotrecqtytotal;
		}

		public void setLotrecqtytotal(String lotrecqtytotal) {
			this.lotrecqtytotal = lotrecqtytotal;
		}

		public String getLotrecmfgdate() {
			return lotrecmfgdate;
		}

		public void setLotrecmfgdate(String lotrecmfgdate) {
			this.lotrecmfgdate = lotrecmfgdate;
		}

		public String getLotrecexpdate() {
			return lotrecexpdate;
		}

		public void setLotrecexpdate(String lotrecexpdate) {
			this.lotrecexpdate = lotrecexpdate;
		}

		public Bottle(String lotrec20k, String lotrec40k) {
			setLotrec20k(lotrec20k);
			setLotrec40k(lotrec40k);
		}

		public Bottle(String manDate, String exDate,
				String bNo, String eWeight, String bQty) {
			setLotrecmfgdate(manDate);
			setLotrecexpdate(exDate);
			setLotrecbottleno(bNo);
			setLotrecemptywt(eWeight);
			setLotrecqtytotal(bQty);
		}
		public HashMap<String, String> getMap() {
			
			HashMap<String, String> map = new HashMap<String, String>();		
			map.put("lotrecno", getId().toString());
			map.put("lotrecsite", getSiteId().toString());
			map.put("lotrecdate", getLotrecmfgdate());  //Lot Created date			
			map.put("lotrec20k", getLotrec20k());
			map.put("lotrec40k", getLotrec40k());		
			map.put("lotrecmfgdate", getLotrecmfgdate());
			map.put("lotrecexpdate", getLotrecexpdate());
			map.put("lotrecbottleno", getLotrecbottleno());
			map.put("lotrecqtytotal", getLotrecqtytotal());  //Total qty of bottles
			map.put("lotrecqtyremain", getLotrecqtytotal());  // Remaining qty of bottles
			map.put("lotrecemptywt", getLotrecemptywt());		
			
			return map;
		}		

	}  // End of InnerClass
	
	protected CustomTable flexTable;

	protected HashMap<Integer, Bottle> lotMgts;
	
	public LotManagement(int qtyOfBottle20000,int qtyOfBottle40000){

		/*Calendar calendar = Calendar.getInstance();
		calendar.setTime(date);
		calendar.add(Calendar.DATE,365);    // if leap year then add 366  FIXME
		date = calendar.getTime(); */		
		cal.setTime(new Date());
		cal.add(Calendar.DATE,365);


		


		cal.setTime(new Date());
		cal.add(Calendar.DATE,365);
		

		String lot20k = new Integer(qtyOfBottle20000).toString();
		String lot40k = new Integer(qtyOfBottle40000).toString();
		lotMgts = new HashMap<Integer, Bottle>();
		final VerticalPanel vP = new VerticalPanel();
		initWidget(vP);			
		flexTable = new CustomTable();
		flexTable.setSize("100%", "100%");
		flexTable.addColumn("Manufactucturer Date", "lotrecmfgdate");
		flexTable.addColumn("Expiry Date", "lotrecexpdate");
		flexTable.addColumn("Bottle No", "lotrecbottleno");
		flexTable.addColumn("Bottle Qty", "lotrecqtytotal");
		flexTable.addColumn("Empty Weight", "lotrecemptywt");
		vP.add(flexTable);
		HorizontalPanel hP = new HorizontalPanel();   // 'Finish Button' is added to this panel.  
		vP.add(hP);
		//////////////////////////
		if(qtyOfBottle20000 != 0) {
			for(int i=1; i<=qtyOfBottle20000; i++){
				Bottle lm = new Bottle(lot20k,lot40k);				
				addLotMgt(lotMgts.size() + 1, lm, 20000);
			}
		}
		if(qtyOfBottle40000 != 0) {
			for(int i=1; i<=qtyOfBottle40000; i++){
				Bottle lm = new Bottle(lot20k,lot40k);				
				addLotMgt(lotMgts.size() + 1, lm, 40000);
			}
		}
		/////////////////////////////////
		
	}
	
	public void addLotMgt(final Integer pos, Bottle a,int qtyOfBottle) {
		
		lotMgts.put(pos, a);
		
		//Adding Col 
		final CustomDatePicker lotrecmfgdate = new CustomDatePicker();
	    lotrecmfgdate.setValue(new Date());	
	    a.setLotrecmfgdate(new Date().toString());
		flexTable.getFlexTable().setWidget(pos, 0, lotrecmfgdate);
		
		final CustomDatePicker lotrecexpdate = new CustomDatePicker();
		lotrecexpdate.setValue(cal.getTime());
		a.setLotrecexpdate(cal.getTime().toString());
		flexTable.getFlexTable().setWidget(pos, 1, lotrecexpdate);
		
				
		final TextBox lotrecbottleno = new TextBox();
		lotrecbottleno.setText(a.getLotrecbottleno());
		flexTable.getFlexTable().setWidget(pos, 2, lotrecbottleno);
		
		final TextBox lotrecqtytotal = new TextBox();
		if(qtyOfBottle==20000){
			lotrecqtytotal.setText("20000");		
			a.setLotrecqtytotal("20000");
			
		}
		else {
			lotrecqtytotal.setText("40000");
			a.setLotrecqtytotal("40000");
		}
		flexTable.getFlexTable().setWidget(pos, 3, lotrecqtytotal);
		
		final TextBox lotrecemptywt = new TextBox();
		lotrecemptywt.setText(a.getLotrecemptywt());
		flexTable.getFlexTable().setWidget(pos, 4, lotrecemptywt);
		
		
		ChangeHandler cl = new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {				
				Bottle x = lotMgts.get(pos);				 
				x.setLotrecmfgdate(lotrecmfgdate.getTextBox().getText());				
				x.setLotrecexpdate(lotrecexpdate.getTextBox().getText());								
				x.setLotrecbottleno(lotrecbottleno.getText());
				x.setLotrecqtytotal(lotrecqtytotal.getText());
				x.setLotrecemptywt(lotrecemptywt.getText());
				x.setLotrec20k(x.getLotrec20k());
				x.setLotrec40k(x.getLotrec40k());
				lotMgts.put(pos,x);
			
			}
		};
		
		lotrecbottleno.addChangeHandler(cl);
		lotrecqtytotal.addChangeHandler(cl);
		lotrecemptywt.addChangeHandler(cl);	
		//lotrecmfgdate, lotrecexpdate
		
		
	}

	public void commitChanges(Integer id,String siteId){     	
	    
	    
			setId(id);
			setSiteId(siteId);
			HashMap<String, String>[] map;
			List<HashMap<String, String>> l = new ArrayList<HashMap<String, String>>();
			Iterator<Integer> iter = lotMgts.keySet().iterator();
			
			while (iter.hasNext()) {
				HashMap<String,String> mmp=lotMgts.get(iter.next()).getMap();
	
				l.add(mmp);
			}
			map = (HashMap<String, String>[]) l.toArray(new HashMap<?, ?>[0]);
			////////////////////////////////
			if (Util.getProgramMode() == ProgramMode.JSONRPC) { 
						
				String[] params = { JsonUtil.jsonify(map)};
				
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,URL.encode(Util.getJsonRequest("org.freemedsoftware.module.MethadoneLotRegistration.setLotMngt",params)));			
				
				try {
					builder.sendRequest(null,new RequestCallback() {public void onError(Request request,Throwable ex) {						
						
					}
	
								public void onResponseReceived(Request request,Response response) {
									
									if (200 == response.getStatusCode()) {												
										
									} 
								}
							});
				} catch (RequestException e) {
				}	
				
			}	
	
	
	}
   ////////////////////////////////// 
/*	public boolean validateForm(){
		String msg = new String("");
		HashMap<Integer,Bottle> addressMap = getLotMangt();
		if(addressMap!=null && addressMap.size()>0){
			Iterator<Integer> iter = addressMap.keySet().iterator();
			while (iter.hasNext()) {
				Integer key = iter.next();
				Bottle address = (Bottle) addressMap.get(key);
				if(address.getLotrecbottleno()==null || address.getLotrecbottleno()==""){
					msg += "Please specify bottle number." + "\n";
					break;
				}
			}  //end of While loop			
		}  // end of If statement
		if (msg != "") {
			Window.alert(msg);
			return false;
		}

		return true;
	}*/
	
	public HashMap<Integer, Bottle> getLotMangt() {
		return lotMgts;
	}
	
}
	
	

