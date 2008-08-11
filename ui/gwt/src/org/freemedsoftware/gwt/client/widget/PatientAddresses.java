/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.PatientModuleAsync;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class PatientAddresses extends Composite {

	protected class Address {
		protected String line1, line2, csz, type, relation;

		protected Boolean active, updated;

		public Address() {
		}

		public Address(String myLine1, String myLine2, String myCsz,
				String myType, String myRelation, Boolean myActive,
				Boolean myUpdated) {
			setLine1(myLine1);
			setLine2(myLine2);
			setCsz(myCsz);
			setType(myType);
			setRelation(myRelation);
			setActive(myActive);
			setUpdated(myUpdated);
		}

		public String getLine1() {
			return line1;
		}

		public String getLine2() {
			return line2;
		}

		public String getCsz() {
			return csz;
		}

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

		public void setCsz(String myCsz) {
			csz = myCsz;
		}

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
	}

	protected Integer patientId = new Integer(0);

	protected FlexTable flexTable;

	protected HashMap<Integer, Address> addresses;

	public PatientAddresses() {
		addresses = new HashMap<Integer, Address>();

		VerticalPanel vP = new VerticalPanel();
		initWidget(vP);

		flexTable = new FlexTable();
		vP.add(flexTable);

		HorizontalPanel hP = new HorizontalPanel();
		vP.add(hP);
	}

	/**
	 * Add additional address object to a particular position on the flexTable.
	 * 
	 * @param pos
	 *            Integer row number
	 * @param a
	 *            Address object containing population data.
	 */
	public void addAddress(final Integer pos, Address a) {
		// Keep a record of this
		addresses.put(pos, a);

		// It obviously hasn't been updated yet
		a.setUpdated(Boolean.FALSE);

		final CustomListBox type = new CustomListBox();
		type.setVisibleItemCount(1);
		type.addItem("H - Home", "H");
		type.addItem("W - Work", "W");
		type.setWidgetValue(a.getType());
		flexTable.setWidget(pos, 0, type);

		final CustomListBox relation = new CustomListBox();
		relation.setVisibleItemCount(1);
		relation.addItem("S - Self", "S");
		relation.addItem("P - Parents", "P");
		relation.addItem("C - Cousin", "C");
		relation.addItem("SH - Shelter", "SH");
		relation.addItem("U - Unrelated", "U");
		relation.setWidgetValue(a.getRelation());
		flexTable.setWidget(pos, 1, relation);

		final TextBox line1 = new TextBox();
		line1.setText(a.getLine1());
		flexTable.setWidget(pos, 2, line1);

		final TextBox line2 = new TextBox();
		line2.setText(a.getLine2());
		flexTable.setWidget(pos, 3, line2);

		final TextBox csz = new TextBox();
		csz.setText(a.getCsz());
		flexTable.setWidget(pos, 4, csz);

		final CheckBox active = new CheckBox();
		active.setChecked(a.getActive().booleanValue());
		flexTable.setWidget(pos, 5, active);

		ChangeListener cl = new ChangeListener() {
			@Override
			public void onChange(Widget sender) {
				Address x = addresses.get(pos);
				x.setType(type.getWidgetValue());
				x.setRelation(relation.getWidgetValue());
				x.setLine1(line1.getText());
				x.setLine2(line2.getText());
				x.setCsz(csz.getText());
				x.setUpdated(Boolean.TRUE);
				x.setActive(new Boolean(active.isChecked()));
				addresses.put(pos, x);

				// Sanity check: uncheck all other active ones
				if (active.isChecked()) {
					setActiveAddress(pos);
				}
			}
		};

		// Implement changelisteners
		type.addChangeListener(cl);
		relation.addChangeListener(cl);
		line1.addChangeListener(cl);
		line2.addChangeListener(cl);
		csz.addChangeListener(cl);

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
				CheckBox cb = (CheckBox) flexTable.getWidget(thisKey, 5);
				cb.setChecked(false);
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
		if (Util.isStubbedMode()) {
			// TODO: Stubbed stuff
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
									a.setCsz(result[iter].get("csz"));
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

}
