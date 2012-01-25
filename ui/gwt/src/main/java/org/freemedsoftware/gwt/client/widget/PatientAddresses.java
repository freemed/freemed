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

package org.freemedsoftware.gwt.client.widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Module.PatientModuleAsync;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
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
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientAddresses extends Composite {

	public class Address {
		protected Integer addressId;
		
		protected String line1, line2, type, relation;

		protected String city, stpr, postal, country;

		protected Boolean active, updated;

		public Address() {
		}

		public Address(String myLine1, String myLine2, String myType,
				String myRelation, Boolean myActive, Boolean myUpdated) {
			setLine1(myLine1);
			setLine2(myLine2);
			setType(myType);
			setRelation(myRelation);
			setActive(myActive);
			setUpdated(myUpdated);
		}

		public Address(String myLine1, String myLine2, String myType,
				String myRelation, Boolean myActive, Boolean myUpdated,
				String city, String stpr, String postal, String country) {
			setLine1(myLine1);
			setLine2(myLine2);
			setType(myType);
			setRelation(myRelation);
			setActive(myActive);
			setUpdated(myUpdated);
			setCity(city);
			setStpr(stpr);
			setPostal(postal);
			setCountry(country);
		}

		/**
		 * Retrieve HashMap for object used from RPC.
		 * 
		 * @return
		 */
		public HashMap<String, String> getMap() {
			HashMap<String, String> map = new HashMap<String, String>();
			if(addressId!=null)
				map.put("id",addressId.toString());
			if(getLine1()!=null)
				map.put("line1", getLine1());
			if(getLine2()!=null)
				map.put("line2", getLine2());
			// map.put("csz", getCsz());
			if(getCity()!=null)
				map.put("city", getCity());
			if(getStpr()!=null)
				map.put("stpr", getStpr());
			if(getPostal()!=null)
				map.put("postal", getPostal());
			if(getCountry()!=null)
				map.put("country", getCountry());
			if(getRelation()!=null)
				map.put("relation", getRelation());
			if(getType()!=null)
				map.put("type", getType());
			map.put("active", getActive() ? "1" : "0");
			map.put("updated", getUpdated() ? "1" : "0");
			return map;
		}

		public void loadData(HashMap<String, String> data){

			if(data.get("id")!=null)
				setAddressId(Integer.parseInt(data.get("id")));
			setLine1(data.get("line1"));
			setLine2(data.get("line2"));
			// a.setCsz(result[iter].get("csz"));
			setCity(data.get("city"));
			setStpr(data.get("stpr"));
			setPostal(data.get("postal"));
			setCountry(data.get("country"));
			setRelation(data.get("relation"));
			setType(data.get("type"));
			setActive(new Boolean(data
					.get("active") == "1"));
		}
		
		public String getLine1() {
			return line1;
		}

		public String getLine2() {
			return line2;
		}

		// public String getCsz() {
		// return csz;
		// }

		public String getType() {
			return type;
		}

		public Boolean getActive() {
			return active;
		}

		public Boolean getUpdated() {
			return updated;
		}

		public String getRelation() {
			return relation;
		}

		public void setLine1(String myStreet) {
			line1 = myStreet;
		}

		public void setLine2(String myStreet) {
			line2 = myStreet;
		}

		// public void setCsz(String myCsz) {
		// csz = myCsz;
		// }

		public void setActive(Boolean myActive) {
			active = myActive;
		}

		public void setUpdated(Boolean myUpdated) {
			updated = myUpdated;
		}

		public void setType(String myType) {
			type = myType;
		}

		public void setRelation(String myRelation) {
			relation = myRelation;
		}

		public String getCity() {
			return city;
		}

		public void setCity(String city) {
			this.city = city;
		}

		public String getStpr() {
			return stpr;
		}

		public void setStpr(String stpr) {
			this.stpr = stpr;
		}

		public String getPostal() {
			return postal;
		}

		public void setPostal(String postal) {
			this.postal = postal;
		}

		public String getCountry() {
			return country;
		}

		public void setCountry(String country) {
			this.country = country;
		}

		public Integer getAddressId() {
			return addressId;
		}

		public void setAddressId(Integer addressId) {
			this.addressId = addressId;
		}
	}

	protected Integer patientId = new Integer(0);

	protected CustomTable flexTable;

	protected HashMap<Integer, Address> addresses;

	protected CurrentState state = null;

	protected Command onCompletion = null;

	public PatientAddresses() {
		addresses = new HashMap<Integer, Address>();

		VerticalPanel vP = new VerticalPanel();
		vP.setWidth("100%");
		initWidget(vP);

		flexTable = new CustomTable();
		flexTable.setWidth("100%");
		flexTable.addColumn(_("Residence Type"), "type");
		flexTable.addColumn(_("Relationship"), "relation");
		flexTable.addColumn(_("Address Line 1"), "line1");
		flexTable.addColumn(_("Address Line 2"), "line2");
		// flexTable.addColumn("City, State Postal", "csz");
		flexTable.addColumn(_("City"), "city");
		flexTable.addColumn(_("St/Pr"), "stpr");
		flexTable.addColumn(_("Postal"), "postal");
		flexTable.addColumn(_("Country"), "country");
		flexTable.addColumn(_("Active"), "active");
		flexTable.addColumn(_("Action"), null);
		vP.add(flexTable);

		HorizontalPanel hP = new HorizontalPanel();
		vP.add(hP);

		CustomButton addAddressButton = new CustomButton(_("Add Address"), AppConstants.ICON_ADD);
		addAddressButton.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent evt) {
				Address a = new Address();
				a.setActive(Boolean.FALSE);
				addAddress(addresses.size() + 1, a);
			}
		});
		hP.add(addAddressButton);
	}

	/**
	 * Set <Command> which is run on completion of data submission.
	 * 
	 * @param oc
	 */
	public void setOnCompletion(Command oc) {
		onCompletion = oc;
	}

	public void addAddress(HashMap<String, String> addressData) {
		Address address = new Address();
		address.loadData(addressData);
		addAddress(addresses.size() + 1, address);
	}
	
	/**
	 * Add additional address object to a particular position on the flexTable.
	 * 
	 * @param pos
	 *            Integer row number
	 * @param a
	 *            Address object containing population data.
	 */
	public void addAddress(final Integer pos,final Address a) {
		// Keep a record of this
		addresses.put(pos, a);

		// It obviously hasn't been updated yet
		a.setUpdated(Boolean.FALSE);

		final CustomListBox type = new CustomListBox();
		type.setVisibleItemCount(1);
		type.addItem("H - " + _("Home"), "H");
		type.addItem("W - " + _("Work"), "W");
		if(a.getType()!=null)
			type.setWidgetValue(a.getType());
		flexTable.getFlexTable().setWidget(pos, 0, type);

		final CustomListBox relation = new CustomListBox();
		relation.setVisibleItemCount(1);
		relation.addItem("S - " + _("Self"), "S");
		relation.addItem("P - " + _("Parents"), "P");
		relation.addItem("C - " + _("Cousin"), "C");
		relation.addItem("SH - " + _("Shelter"), "SH");
		relation.addItem("U - " + _("Unrelated"), "U");
		if(a.getRelation()!=null)
			relation.setWidgetValue(a.getRelation());
		flexTable.getFlexTable().setWidget(pos, 1, relation);

		final TextBox line1 = new TextBox();
		line1.setText(a.getLine1());
		flexTable.getFlexTable().setWidget(pos, 2, line1);

		final TextBox line2 = new TextBox();
		line2.setText(a.getLine2());
		flexTable.getFlexTable().setWidget(pos, 3, line2);

		// final TextBox csz = new TextBox();
		// csz.setText(a.getCsz());
		// flexTable.getFlexTable().setWidget(pos, 4, csz);

		final TextBox city = new TextBox();
		city.setText(a.getCity());
		flexTable.getFlexTable().setWidget(pos, 4, city);

		final TextBox stpr = new TextBox();
		stpr.setWidth("5em");
		stpr.setText(a.getStpr());
		flexTable.getFlexTable().setWidget(pos, 5, stpr);

		final TextBox postal = new TextBox();
		postal.setWidth("5em");
		postal.setText(a.getPostal());
		flexTable.getFlexTable().setWidget(pos, 6, postal);

		final TextBox country = new TextBox();
		country.setText(a.getCountry());
		flexTable.getFlexTable().setWidget(pos, 7, country);

		final CheckBox active = new CheckBox();
		active.setValue(a.getActive().booleanValue());
		flexTable.getFlexTable().setWidget(pos, 8, active);
		
		final CustomButton deleteLink=new CustomButton(_("Delete"),AppConstants.ICON_DELETE);
		flexTable.getFlexTable().setWidget(pos, 9,deleteLink );
		
		deleteLink.addClickHandler(new ClickHandler(){
			@Override
			public void onClick(ClickEvent event) {
				for(int i=0;i<=9;i++)
					flexTable.getFlexTable().clearCell(pos, i);
			     deleteAddress(a);
			}
			
		});
		
		
		
		ChangeHandler cl = new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				Address x = addresses.get(pos);
				x.setType(type.getWidgetValue());
				x.setRelation(relation.getWidgetValue());
				x.setLine1(line1.getText());
				x.setLine2(line2.getText());
				// x.setCsz(csz.getText());
				x.setCity(city.getText());
				x.setStpr(stpr.getText());
				x.setPostal(postal.getText());
				x.setCountry(country.getText());
				x.setUpdated(Boolean.TRUE);
				x.setActive(new Boolean(active.getValue()));
				addresses.put(pos, x);

				// Sanity check: uncheck all other active ones
				if (active.getValue()) {
					setActiveAddress(pos);
				}
			}
		};

		// Implement changelisteners
		type.addChangeHandler(cl);
		relation.addChangeHandler(cl);
		line1.addChangeHandler(cl);
		line2.addChangeHandler(cl);
		// csz.addChangeHandler(cl);
		city.addChangeHandler(cl);
		stpr.addChangeHandler(cl);
		postal.addChangeHandler(cl);
		country.addChangeHandler(cl);
		active.addClickHandler(new ClickHandler() {
			public void onClick(ClickEvent evt) {
				Address x = addresses.get(pos);
				x.setActive(((CheckBox) evt.getSource()).getValue());
				addresses.put(pos, x);

				// Sanity check: uncheck all other active ones
				if (((CheckBox) evt.getSource()).getValue()) {
					setActiveAddress(pos);
				}
			}
		});
	}

	@SuppressWarnings("unchecked")
	public void commitChanges() {
		// Form map
		HashMap<String, String>[] map;
		List<HashMap<String, String>> l = new ArrayList<HashMap<String, String>>();
		Iterator<Integer> iter = addresses.keySet().iterator();
		while (iter.hasNext()) {
			HashMap<String, String> mmp = addresses.get(iter.next()).getMap();
			mmp.put("patient", patientId.toString());
			if(mmp.get("id")!=null)
				mmp.put("altered", "true");
			l.add(mmp);
		}
		map = (HashMap<String, String>[]) l.toArray(new HashMap<?, ?>[0]);

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			Util.showInfoMsg("PatientAddresses", _("Updated patient addresses."));
			if (onCompletion != null) {
				onCompletion.execute();
			}
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString(), JsonUtil.jsonify(map) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientModule.SetAddresses",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						GWT.log("Exception", ex);
						Util.showErrorMsg("PatientAddresses", _("Failed to update patient addresses."));
					}

					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							Boolean result = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parseStrict(response.getText()),
									"Boolean");
							if (result != null) {
								Util.showInfoMsg("PatientAddresses", _("Updated patient addresses."));
								if (onCompletion != null) {
									onCompletion.execute();
								}
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
				Util.showErrorMsg("PatientAddresses", _("Failed to update patient addresses."));
			}
		} else {
			PatientModuleAsync service = null;
			try {
				service = (PatientModuleAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.PatientModule");
			} catch (Exception ex) {
				GWT.log("Exception", ex);
			} finally {
				service.SetAddresses(patientId, map,
						new AsyncCallback<Boolean>() {
							public void onSuccess(Boolean result) {
								Util.showInfoMsg("PatientAddresses", _("Updated patient addresses."));
								if (onCompletion != null) {
									onCompletion.execute();
								}
							}

							public void onFailure(Throwable t) {
								GWT.log("Exception", t);
								Util.showErrorMsg("PatientAddresses", _("Failed to update patient addresses."));
							}
						});

			}
		}
	}

	/**
	 * Set the address at flexTable row p to be the only one set active.
	 * 
	 * @param p
	 *            Active table row position.
	 */
	protected void setActiveAddress(Integer p) {
		Iterator<Integer> kI = addresses.keySet().iterator();
		while (kI.hasNext()) {
			Integer thisKey = kI.next();
			if (thisKey.compareTo(p) != 0) {
				CheckBox cb = (CheckBox) flexTable.getFlexTable().getWidget(
						thisKey, 5);
				cb.setValue(false);
			}
		}
	}

	/**
	 * Set and populate widget with patient information.
	 * 
	 * @param myPatientId
	 */
	public void setPatient(Integer myPatientId) {
		patientId = myPatientId;
		populate();
	}

	private void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: Stubbed stuff
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientModule.GetAddresses",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
						GWT.log("Exception", ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String>[] result = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parseStrict(response
											.getText()),
											"HashMap<String,String>[]");
							if (result != null) {
								for (int iter = 0; iter < result.length; iter++) {
									// Create new address object
									Address a = new Address();
									// Pass new address object to interface
									// builder
									a.loadData(result[iter]);
									addAddress(new Integer(iter + 1), a);
								}
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			PatientModuleAsync service = null;
			try {
				service = (PatientModuleAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.PatientModule");
			} catch (Exception ex) {
				GWT.log("Exception", ex);
			} finally {
				service.GetAddresses(patientId,
						new AsyncCallback<HashMap<String, String>[]>() {
							public void onSuccess(
									HashMap<String, String>[] result) {
								for (int iter = 0; iter < result.length; iter++) {
									// Create new address object
									Address a = new Address();
									a.setLine1(result[iter].get("line1"));
									a.setLine2(result[iter].get("line2"));
									// a.setCsz(result[iter].get("csz"));
									a.setCity(result[iter].get("city"));
									a.setStpr(result[iter].get("stpr"));
									a.setPostal(result[iter].get("postal"));
									a.setCountry(result[iter].get("country"));
									a.setRelation(result[iter].get("relation"));
									a.setType(result[iter].get("type"));
									// Pass new address object to interface
									// builder
									addAddress(new Integer(iter + 1), a);
								}
							}

							public void onFailure(Throwable t) {
								GWT.log("Exception", t);
							}
						});

			}
		}
	}

	public void deleteAddress() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientModule.DeleteAddresses",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {

						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.getMessage());
			}
		} else {
			// TODO normal mode code goes here
		}
	}
	
	public void deleteAddress(Address a) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { a.getAddressId().toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.PatientModule.DeleteAddressById",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {

						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.getMessage());
			}
		} else {
			// TODO normal mode code goes here
		}
	}

	public HashMap<Integer, Address> getAddresses() {
		return addresses;
	}

}
