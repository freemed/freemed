/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientForm;
import org.freemedsoftware.gwt.client.widget.CustomActionBar.HandleCustomAction;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.dom.client.Style.Cursor;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class ActionItemsBox extends WidgetInterface {

	protected Label messageCountLabel = new Label(_("You have no action items."));

	protected HashMap<String, String>[] result;

	protected CustomTable actionItemsTable = new CustomTable();

	protected HashMap<String, String>[] dataMemory;

	protected Popup popupItemView;

	protected final VerticalPanel contentVPanel;

	final Image colExpBtn;

	protected Integer patientId = null;

	private boolean showPatientName = true;

	public ActionItemsBox(boolean showPatientName) {
		this.showPatientName = showPatientName;
		
		VerticalPanel superVPanel = new VerticalPanel();
		initWidget(superVPanel);
		superVPanel.setStyleName(AppConstants.STYLE_BUTTON_WIDGETS_CONTAINER);
		superVPanel.setWidth("100%");

		HorizontalPanel headerHPanel = new HorizontalPanel();
		headerHPanel.setSpacing(5);
		superVPanel.add(headerHPanel);

		colExpBtn = new Image(Util.getResourcesURL() + "collapse.15x15.png");
		colExpBtn.getElement().getStyle().setCursor(Cursor.POINTER);
		headerHPanel.add(colExpBtn);
		colExpBtn.addClickHandler(new ClickHandler() {
			boolean expanded = false;

			@Override
			public void onClick(ClickEvent arg0) {
				if (expanded) {
					colExpBtn.setUrl(Util.getResourcesURL()
							+ "collapse.15x15.png");
					contentVPanel.setVisible(true);
				} else {
					colExpBtn.setUrl(Util.getResourcesURL()
							+ "expand.15x15.png");
					contentVPanel.setVisible(false);
				}
				expanded = !expanded;
			}
		});

		Label headerLabel = new Label(_("ACTION ITEMS"));
		headerHPanel.add(headerLabel);
		headerLabel.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);

		contentVPanel = new VerticalPanel();
		contentVPanel.setWidth("100%");
		superVPanel.add(contentVPanel);

		contentVPanel.add(actionItemsTable);
		actionItemsTable.setSize("100%", "100%");
		actionItemsTable.addColumn(_("Date"), "stamp"); // col 0
		// marya
		if(this.showPatientName)
		 actionItemsTable.addColumn(_("Patient"), "patient_name"); // col 1
		actionItemsTable.addColumn(_("Module Name"), "status_name"); // col 2
		actionItemsTable.addColumn(_("Status"), "summary"); // col 4
		actionItemsTable.addColumn(_("Action"), "action"); // col 4
		actionItemsTable.setIndexName("id");
		actionItemsTable.setMaximumRows(7);
		if (true) {
			actionItemsTable
					.setTableRowClickHandler(new TableRowClickHandler() {
						@Override
						public void handleRowClick(
								HashMap<String, String> data, int col) {
							// Get information on row...
							try {
								final Integer messageId = Integer.parseInt(data
										.get("id"));
								JsonUtil.debug("Found messageId " + messageId);
								if ((col == 0) || (col == 2)) {
								}
							} catch (Exception e) {
								GWT.log("Caught exception: ", e);
							}
						}
					});
		}
		actionItemsTable
				.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
					@Override
					public Widget setColumn(String columnName,
							HashMap<String, String> data) {
						// Render only action column, otherwise skip renderer
						if (columnName.compareToIgnoreCase("action") != 0) {
							return null;
						}
						final CustomActionBar actionBar = new CustomActionBar(
								data);
						actionBar.applyPermissions(false, false, false, true,
								false);

						actionBar
								.setHandleCustomAction(new HandleCustomAction() {
									@Override
									public void handleAction(int id,
											HashMap<String, String> data,
											int action) {
										if (action == HandleCustomAction.MODIFY)
											openEditScreen(id, data);
									}
								});
						// Push value back to table
						return actionBar;
					}
				});
	}

	public void openEditScreen(Integer id, HashMap<String, String> data) {
		if (data.get("type").equalsIgnoreCase(
				AppConstants.ACTION_ITEM_TYPE_REVIEW)) {
/*
	// commented out, hooks to treatment notes code - JB
			if (data.get("status_module").equalsIgnoreCase(
					IntensifiedTreatmentNotes.INTENSIFIED_TREATMENT_NOTES)) {
				PatientScreen pScreen = new PatientScreen();
				pScreen.setPatient(Integer.parseInt(data.get("patient_id")));
				Util.spawnTab(data.get("patient_name"), pScreen);
				IntensifiedTreatmentNotes screen = new IntensifiedTreatmentNotes();
				Util.spawnTabPatient(IntensifiedTreatmentNotes.moduleName,
						screen, pScreen);
				screen.modifyEntry(id);
				screen.retrieveAndFillListData();
			} else if (data.get("status_module").equalsIgnoreCase(
					TreatmentClinicalNotes.TREATMENT_CLINICAL_NOTES)) {
				PatientScreen pScreen = new PatientScreen();
				pScreen.setPatient(Integer.parseInt(data.get("patient_id")));
				Util.spawnTab(data.get("patient_name"), pScreen);
				TreatmentClinicalNotes screen = new TreatmentClinicalNotes();
				Util.spawnTabPatient(TreatmentClinicalNotes.moduleName, screen,
						pScreen);
				screen.modifyEntry(id);
				screen.retrieveAndFillListData();
			} else if (data.get("status_module").equalsIgnoreCase(
					ClinicalAssessmentNotes.CLINICAL_ASSESSMENT_NOTES)) {
				PatientScreen pScreen = new PatientScreen();
				pScreen.setPatient(Integer.parseInt(data.get("patient_id")));
				Util.spawnTab(data.get("patient_name"), pScreen);
				ClinicalAssessmentNotes screen = new ClinicalAssessmentNotes();
				Util.spawnTabPatient(ClinicalAssessmentNotes.moduleName,
						screen, pScreen);
				screen.modifyEntry(id);
				screen.retrieveAndFillListData();
			}
*/
		} else if (data.get("type").equalsIgnoreCase(
				AppConstants.ACTION_ITEM_TYPE_EXPIRE)) {
			if (data.get("status_module").equalsIgnoreCase(
					PatientAuthorizations.ModuleName)) {
				PatientForm patientForm = new PatientForm();
				Util.spawnTab(data.get("patient_name"), patientForm);
				patientForm.setPatientId(Integer.parseInt(data
						.get("patient_id")));
			}
		}
	}

	public void setEnableCollapse(boolean visible) {
		colExpBtn.setVisible(visible);
	}

	public void clearView() {
		actionItemsTable.clearData();
	}

	@SuppressWarnings({ "rawtypes", "unchecked" })
	public void retrieveData() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Runs in STUBBED MODE => Feed with Sample Data
			HashMap<String, String>[] sampleData = getSampleData();
			loadData(sampleData);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data

			String[] actionsParams = {};
			if (patientId != null) {
				List params = new ArrayList();
				params.add(patientId.toString());
				actionsParams = (String[]) params.toArray(new String[0]);
			}
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ActionItems.getActionItemsCount",
											actionsParams)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					@Override
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@Override
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							Integer data = (Integer) JsonUtil.shoehornJson(
									JSONParser.parseStrict(response.getText()),
									"Integer");
							if (data != null) {
								JsonUtil
										.debug("Action Items count from server is:"
												+ data);
								loadCounter(data);
							}
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}

			actionItemsTable.showloading(true);
			// Get data
			RequestBuilder dataBuilder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ActionItems.getActionItems",
											actionsParams)));
			try {
				dataBuilder.sendRequest(null, new RequestCallback() {
					@Override
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@Override
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String, String>[] data = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parseStrict(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null && data.length > 0) {
								setResult(data);
								loadData(data);
							} else
								actionItemsTable.showloading(false);
						}
					}
				});
			} catch (RequestException e) {
				actionItemsTable.showloading(false);
				// nothing here right now
			}
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}
	}

	public void loadData(HashMap<String, String>[] data) {
		actionItemsTable.clearData();
		if (data != null && data.length > 0)
			actionItemsTable.loadData(data);
		// Save the data internally
		dataMemory = data;
		// for testing purpose only
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			messageCountLabel.setText("You have 2 new action items.");
		}
	}

	/**
	 * Set current Action Items count as displayed.
	 * 
	 * @param count
	 */
	public void loadCounter(Integer count) {
		String text;
		JsonUtil.debug("Action Items count is:" + count);
		if (count < 1) {
			text = _("There are no action item messages.");
		} else {
			text = _("You have %d action items!").replaceFirst("%d", count.toString());
		}
		messageCountLabel.setText(text);
	}

	public HashMap<String, String>[] getResult() {
		return result;
	}

	public void setResult(HashMap<String, String>[] data) {
		result = data;
	}

	@SuppressWarnings("unchecked")
	protected HashMap<String, String>[] getSampleData() {
		List<HashMap<String, String>> m = new ArrayList<HashMap<String, String>>();

		HashMap<String, String> a = new HashMap<String, String>();
		a.put("id", "1");
		a.put("stamp", "2009-02-06");
		a.put("status_name", "Treatment Clinical Note");
		a.put("status_module", "TreatmentClinicalNote");
		a.put("status", "uncompleted");
		m.add(a);

		HashMap<String, String> b = new HashMap<String, String>();
		b.put("id", "2");
		b.put("stamp", "2009-02-06");
		a.put("status_name", "Initial Intake");
		a.put("status_module", "InitialIntake");
		a.put("status", "uncompleted");
		m.add(b);

		return (HashMap<String, String>[]) m.toArray(new HashMap<?, ?>[0]);
	}

	public Integer getPatientId() {
		return patientId;
	}

	public void setPatientId(Integer patientId) {
		this.patientId = patientId;
	}

}
