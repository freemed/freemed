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

import java.util.HashMap;
import java.util.Iterator;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
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
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientAuthorizations extends Composite {

	public class Authorization {

		protected Integer authorizationId = 0;

		protected String startingDate;

		protected String endingDate;

		protected String authorizationNumber;

		protected String authorizationType;

		protected Integer authorizingProvider = 0;

		protected String providerIdentifier = "";

		protected Integer authorizingInsuranceCompany = 0;

		protected String numberofVisits = "";

		protected String usedVisits = "";

		protected String Comment = "";

		public Authorization() {
		}

		/**
		 * Retrieve HashMap for object used from RPC.
		 * 
		 * @return
		 */
		public HashMap<String, String> getMap() {
			HashMap<String, String> map = new HashMap<String, String>();
			if (authorizationId != null && authorizationId>0)
				map.put("id", authorizationId.toString());
			if(getStartingDate()!=null)
				map.put("authdtbegin", getStartingDate());
			if(getEndingDate()!=null)
				map.put("authdtend", getEndingDate());
			if(getAuthorizationNumber()!=null)
				map.put("authnum", getAuthorizationNumber());
			if(getAuthorizationType()!=null)
				map.put("authtype", getAuthorizationType());
			if (getAuthorizingProvider() != null)
				map.put("authprov", getAuthorizingProvider().toString());
			if(getProviderIdentifier()!=null)
				map.put("authprovid", getProviderIdentifier());
			if (getAuthorizingInsuranceCompany() != null)
				map.put("authinsco", getAuthorizingInsuranceCompany()
						.toString());

			map.put("authvisits", getNumberofVisits());
			map.put("authvisitsused", getUsedVisits());
			if (getNumberofVisits().length() >0 && getUsedVisits().length()>0)
				map.put("authvisitsremain", Integer.toString((Integer
						.parseInt(getNumberofVisits()) - Integer
						.parseInt(getUsedVisits()))));
			if(getComment()!=null)
				map.put("authcomment", getComment());
			return map;
		}

		public void loadData(HashMap<String, String> data) {
			if (data.get("id") != null)
				setAuthorizationId(Integer.parseInt(data.get("id")));
			setStartingDate(data.get("authdtbegin"));
			setEndingDate(data.get("authdtend"));
			if (data.get("authnum") != null)
				setAuthorizationNumber(data.get("authnum"));
			if (data.get("authtype") != null)
				setAuthorizationType(data.get("authtype"));
			if (data.get("authprov") != null)
				setAuthorizingProvider(Integer.parseInt(data.get("authprov")));
			if (data.get("authprovid") != null)
				setProviderIdentifier(data.get("authprovid"));
			if (data.get("authinsco") != null)
				setAuthorizingInsuranceCompany(Integer.parseInt(data
						.get("authinsco")));
			setNumberofVisits(data.get("authvisits"));
			setUsedVisits(data.get("authvisitsused"));
			setComment(data.get("authcomment"));
		}

		public Integer getAuthorizationId() {
			return authorizationId;
		}

		public void setAuthorizationId(Integer authorizationId) {
			this.authorizationId = authorizationId;
		}

		public String getStartingDate() {
			return startingDate;
		}

		public void setStartingDate(String startingDate) {
			this.startingDate = startingDate;
		}

		public String getEndingDate() {
			return endingDate;
		}

		public void setEndingDate(String endingDate) {
			this.endingDate = endingDate;
		}

		public String getAuthorizationNumber() {
			return authorizationNumber;
		}

		public void setAuthorizationNumber(String authorizationNumber) {
			this.authorizationNumber = authorizationNumber;
		}

		public String getAuthorizationType() {
			return authorizationType;
		}

		public void setAuthorizationType(String authorizationType) {
			this.authorizationType = authorizationType;
		}

		public Integer getAuthorizingProvider() {
			return authorizingProvider;
		}

		public void setAuthorizingProvider(Integer authorizingProvider) {
			this.authorizingProvider = authorizingProvider;
		}

		public String getProviderIdentifier() {
			return providerIdentifier;
		}

		public void setProviderIdentifier(String providerIdentifier) {
			this.providerIdentifier = providerIdentifier;
		}

		public Integer getAuthorizingInsuranceCompany() {
			return authorizingInsuranceCompany;
		}

		public void setAuthorizingInsuranceCompany(
				Integer authorizingInsuranceCompany) {
			this.authorizingInsuranceCompany = authorizingInsuranceCompany;
		}

		public String getNumberofVisits() {
			return numberofVisits;
		}

		public void setNumberofVisits(String numberofVisits) {
			this.numberofVisits = numberofVisits;
		}

		public String getUsedVisits() {
			return usedVisits;
		}

		public void setUsedVisits(String usedVisits) {
			this.usedVisits = usedVisits;
		}

		public String getComment() {
			return Comment;
		}

		public void setComment(String comment) {
			Comment = comment;
		}

	}

	protected Integer patientId = new Integer(0);

	VerticalPanel authorizationsPanel = new VerticalPanel();

	protected HashMap<Integer, Authorization> authorizations;

	protected CurrentState state = null;

	protected Command onCompletion = null;

	public static final String ModuleName = "Authorizations";

	public PatientAuthorizations() {
		authorizations = new HashMap<Integer, Authorization>();

		VerticalPanel verticalPanel = new VerticalPanel();
		verticalPanel.setWidth("100%");
		initWidget(verticalPanel);

		authorizationsPanel = new VerticalPanel();
		authorizationsPanel.setWidth("100%");
		verticalPanel.add(authorizationsPanel);

		HorizontalPanel hP = new HorizontalPanel();
		verticalPanel.add(hP);
		if (CurrentState.isActionAllowed(ModuleName, AppConstants.WRITE)) {
			CustomButton addAuthorizationButton = new CustomButton(
					_("Add Authorization"), AppConstants.ICON_ADD);
			addAuthorizationButton.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent evt) {
					Authorization a = new Authorization();
					addAuthorization(authorizations.size() + 1, a);
				}
			});
			hP.add(addAuthorizationButton);
		} else {
			hP.add(new Label(_("You do not have permission to add authorizations.")));
		}
	}

	/**
	 * Set <Command> which is run on completion of data submission.
	 * 
	 * @param oc
	 */
	public void setOnCompletion(Command oc) {
		onCompletion = oc;
	}

	public void addAuthorization(HashMap<String, String> data) {
		Authorization athorization = new Authorization();
		athorization.loadData(data);
		addAuthorization(authorizations.size() + 1, athorization);
	}

	/**
	 * Add additional Authorization object to a particular position on the
	 * flexTable.
	 * 
	 * @param pos
	 *            Integer row number
	 * @param athorization
	 *            Authorization object containing population data.
	 */
	@SuppressWarnings("unchecked")
	public void addAuthorization(final Integer pos,
			final Authorization athorization) {
		// Keep a record of this

		authorizations.put(pos, athorization);
		int row = 0;

		final CustomTable flexTable = new CustomTable();
		flexTable.setWidth("100%");
		flexTable.removeTableStyle();
		authorizationsPanel.add(flexTable);

		final Label startingDateLabel = new Label(_("Starting Date") + ":");
		flexTable.getFlexTable().setWidget(row, 0, startingDateLabel);
		final CustomDatePicker startingDate = new CustomDatePicker();
		flexTable.getFlexTable().setWidget(row, 1, startingDate);

		final Label endingDateLabel = new Label(_("Ending Date") + ":");
		flexTable.getFlexTable().setWidget(row, 2, endingDateLabel);
		final CustomDatePicker endingDate = new CustomDatePicker();
		flexTable.getFlexTable().setWidget(row, 3, endingDate);

		if (CurrentState.isActionAllowed(ModuleName, AppConstants.DELETE)) {
			final Label deleAuthorizationLabel = new Label(
					_("Delete This Authorization"));
			flexTable.getFlexTable().setWidget(row, 4, deleAuthorizationLabel);
			CustomButton deleAuthorizationButton = new CustomButton(_("Delete"),
					AppConstants.ICON_DELETE);
			deleAuthorizationButton.setWidth("100%");
			deleAuthorizationButton.addClickHandler(new ClickHandler() {
				public void onClick(ClickEvent evt) {
					authorizationsPanel.remove(flexTable);
					if (athorization.getAuthorizationId() != null)
						deleteAuthorization(athorization.getAuthorizationId());
				}
			});
			flexTable.getFlexTable().setWidget(row, 5, deleAuthorizationButton);
		}
		row++;

		final Label authorizationNumberLabel = new Label(
				_("Authorization Number") + ":");
		flexTable.getFlexTable().setWidget(row, 0, authorizationNumberLabel);
		final TextBox authorizationNumber = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, authorizationNumber);

		final Label authorizationTypeLabel = new Label(_("Authorization Type") + ":");
		flexTable.getFlexTable().setWidget(row, 2, authorizationTypeLabel);
		final CustomListBox authorizationType = new CustomListBox();
		authorizationType.addItem(_("NONE SELECTED"), "0");
		authorizationType.addItem(_("physician"), "1");
		authorizationType.addItem(_("insurance company"), "2");
		authorizationType.addItem(_("certificate of medical neccessity"), "3");
		authorizationType.addItem(_("surgical"), "4");
		authorizationType.addItem(_("worker's compensation"), "5");
		authorizationType.addItem(_("consulatation"), "6");
		flexTable.getFlexTable().setWidget(row, 3, authorizationType);

		final Label authorizingProviderLabel = new Label(
				_("Authorizing Provider") + ":");
		flexTable.getFlexTable().setWidget(row, 4, authorizingProviderLabel);
		final ProviderWidget authorizingProvider = new ProviderWidget();
		flexTable.getFlexTable().setWidget(row, 5, authorizingProvider);

		row++;

		final Label providerIdentifierLabel = new Label(_("Provider Identifier") + ":");
		flexTable.getFlexTable().setWidget(row, 0, providerIdentifierLabel);
		final TextBox providerIdentifier = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, providerIdentifier);

		final Label authorizingInsuranceCompanyLabel = new Label(
				_("Authorizing Insurance Company") + ":");
		flexTable.getFlexTable().setWidget(row, 2,
				authorizingInsuranceCompanyLabel);
		final SupportModuleWidget authorizingInsuranceCompany = new SupportModuleWidget(
				"InsuranceCompanyModule");
		flexTable.getFlexTable().setWidget(row, 3, authorizingInsuranceCompany);

		row++;

		final Label numberofVisitsLabel = new Label(_("Number of Visits") + ":");
		flexTable.getFlexTable().setWidget(row, 0, numberofVisitsLabel);
		final TextBox numberofVisits = new TextBox();
		flexTable.getFlexTable().setWidget(row, 1, numberofVisits);

		final Label usedVisitsLabel = new Label(_("Used Visits") + ":");
		flexTable.getFlexTable().setWidget(row, 2, usedVisitsLabel);
		final TextBox usedVisits = new TextBox();
		flexTable.getFlexTable().setWidget(row, 3, usedVisits);

		final Label CommentLabel = new Label("Comment:");
		flexTable.getFlexTable().setWidget(row, 4, CommentLabel);
		final TextBox comment = new TextBox();
		flexTable.getFlexTable().setWidget(row, 5, comment);

		row++;

		final ChangeHandler cl = new ChangeHandler() {
			@Override
			public void onChange(ChangeEvent event) {
				Authorization x = authorizations.get(pos);
				x.setStartingDate(startingDate.getStoredValue());
				x.setEndingDate(endingDate.getStoredValue());
				x.setAuthorizationNumber(authorizationNumber.getValue());
				x.setAuthorizationType(authorizationType.getWidgetValue());
				x.setAuthorizingProvider(authorizingProvider.getValue());
				x.setProviderIdentifier(providerIdentifier.getValue());
				x.setAuthorizingInsuranceCompany(authorizingInsuranceCompany
						.getValue());
				x.setNumberofVisits(numberofVisits.getValue());
				x.setUsedVisits(usedVisits.getValue());
				x.setComment(comment.getValue());

				authorizations.put(pos, x);

			}
		};
		@SuppressWarnings("rawtypes")
		ValueChangeHandler valueChangeHandler = new ValueChangeHandler() {
			public void onValueChange(
					com.google.gwt.event.logical.shared.ValueChangeEvent arg0) {
				cl.onChange(null);
			}
		};

		// Implement changelisteners
		startingDate.addValueChangeHandler(valueChangeHandler);
		endingDate.addValueChangeHandler(valueChangeHandler);
		authorizationNumber.addValueChangeHandler(valueChangeHandler);
		authorizationType.addChangeHandler(cl);
		authorizingProvider.addValueChangeHandler(valueChangeHandler);
		providerIdentifier.addValueChangeHandler(valueChangeHandler);
		authorizingInsuranceCompany.addValueChangeHandler(valueChangeHandler);
		numberofVisits.addValueChangeHandler(valueChangeHandler);
		usedVisits.addValueChangeHandler(valueChangeHandler);
		comment.addValueChangeHandler(valueChangeHandler);

		// End Implement changelisteners

		startingDate.setValue(athorization.getStartingDate());
		endingDate.setValue(athorization.getEndingDate());
		authorizationNumber.setValue(athorization.getAuthorizationNumber());
		if(athorization.getAuthorizationType()!=null)
			authorizationType.setWidgetValue(athorization.getAuthorizationType());
		authorizingProvider.setValue(athorization.getAuthorizingProvider());
		providerIdentifier.setValue(athorization.getProviderIdentifier());
		authorizingInsuranceCompany.setValue(athorization
				.getAuthorizingInsuranceCompany());
		numberofVisits.setValue(athorization.getNumberofVisits());
		usedVisits.setValue(athorization.getUsedVisits());
		comment.setValue(athorization.getComment());

	}

	public void showHideInsuredField(FlexTable flexTable, int startRow,
			int startCol, int endRow, int endCol, boolean action) {
		int row = startRow, col = startCol;
		while (row <= endRow && !(row == endRow && col == endCol)) {
			flexTable.getWidget(row, col++).setVisible(action);
			if (col > 5) {
				row++;
				col = 0;
			}
			if (row == endRow && col == endCol)
				break;
		}
	}

	public void commitChanges() {
		// Form map
		// HashMap<String, String>[] map;
		Iterator<Integer> iter = authorizations.keySet().iterator();
		while (iter.hasNext()) {
			HashMap<String, String> mmp = authorizations.get(iter.next())
					.getMap();

			mmp.put("authpatient", patientId.toString());
			String url = "org.freemedsoftware.module.Authorizations.Add";
			if (mmp.get("id") != null)
				url = "org.freemedsoftware.module.Authorizations.Mod";
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				Util.showInfoMsg("PatientAuthorization",
						_("Updated patient authorizations."));
				if (onCompletion != null) {
					onCompletion.execute();
				}
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				String[] params = { JsonUtil.jsonify(mmp) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST, URL.encode(Util.getJsonRequest(
								url, params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(
								com.google.gwt.http.client.Request request,
								Throwable ex) {
							GWT.log("Exception", ex);
							Util.showErrorMsg("PatientAuthorization",
									_("Failed to update patient authorizations."));
						}

						public void onResponseReceived(
								com.google.gwt.http.client.Request request,
								com.google.gwt.http.client.Response response) {
							if (200 == response.getStatusCode()) {
								Boolean result = (Boolean) JsonUtil
										.shoehornJson(JSONParser.parseStrict(response
												.getText()), "Boolean");
								if (result != null) {
									Util.showInfoMsg("PatientAuthorization",
											_("Updated patient authorizations."));
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
					Util.showErrorMsg("PatientAuthorization",
							_("Failed to update patient authorizations."));
				}
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
											"org.freemedsoftware.module.Authorizations.GetAllAuthorizations",
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
									// Create new Authorization object
									Authorization x = new Authorization();
									/*
									 * x.setAuthorizationId(Integer.parseInt(result
									 * [iter].get("id")));
									 * x.setStartingDate(result
									 * [iter].get("authdtbegin"));
									 * x.setEndingDate
									 * (result[iter].get("authdtend"));
									 * x.setAuthorizationNumber
									 * (result[iter].get("authnum"));
									 * x.setAuthorizationType
									 * (result[iter].get("authtype"));
									 * if(result[iter].get("authprov")!=null)
									 * x.setAuthorizingProvider
									 * (Integer.parseInt(
									 * result[iter].get("authprov")));
									 * x.setProviderIdentifier
									 * (result[iter].get("authprovid"));
									 * if(result[iter].get("authinsco")!=null)
									 * x.setAuthorizingInsuranceCompany(Integer.
									 * parseInt(result[iter].get("authinsco")));
									 * x.setNumberofVisits(result[iter].get(
									 * "authvisits"));
									 * x.setUsedVisits(result[iter
									 * ].get("authvisitsused"));
									 * x.setComment(result
									 * [iter].get("authcomment"));
									 */
									x.loadData(result[iter]);
									// builder
									addAuthorization(new Integer(iter + 1), x);
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
		}
	}

	public void deleteAuthorization(Integer cid) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO stubbed mode goes here
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { cid.toString() };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.Authorizations.del",
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

	public HashMap<Integer, Authorization> getAuthorizations() {
		return authorizations;
	}

	public static String returnAuthorizationType(int id) {

		if (id == 1) {
			return _("physician");
		}

		else if (id == 2) {
			return _("insurance company");
		}

		else if (id == 3) {
			return _("certificate of medical neccessity");
		}

		else if (id == 4) {
			return _("surgical");
		} else if (id == 5) {
			return _("worker's compensation");
		}

		else if (id == 6) {
			return _("consulatation");
		}

		else
			return _("None Selected");

	}

}
