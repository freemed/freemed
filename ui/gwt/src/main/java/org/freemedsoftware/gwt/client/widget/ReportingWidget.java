/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.ReportingAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.ReportingScreen;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.datepicker.client.DateBox;

public class ReportingWidget extends Composite {

	protected CustomTable reportTable;

	protected FlexTable reportParametersTable;

	protected HorizontalPanel reportActionPanel;

	protected HashMap<Integer, String> reportParameters = new HashMap<Integer, String>();

	protected static String locale = "en_US";

	protected String thisReportUUID = null;

	protected Label thisReportName = new Label();

	protected PushButton reportActionHTML, reportActionXML, reportActionPDF;

	protected Integer patientId = null;// In case input widget belongs to
										// EMRMODULE

	public enum ReportType {
		PDF, XLS, HTML, TEXT, XML
	};

	@SuppressWarnings("unused")
	private ReportingWidget() {
	}

	public ReportingWidget(String reportCategory) {

		final boolean canGenerate = CurrentState.isActionAllowed(
				ReportingScreen.moduleName, AppConstants.WRITE);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		initWidget(horizontalPanel);
		horizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final Label pleaseChooseALabel = new Label("Please choose a report.");
		verticalPanel.add(pleaseChooseALabel);
		pleaseChooseALabel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_LEFT);

		reportTable = new CustomTable();
		verticalPanel.add(reportTable);
		reportTable.setAllowSelection(false);
		reportTable.setSize("100%", "100%");
		reportTable.setIndexName("report_uuid");
		reportTable.addColumn("Name", "report_name");
		reportTable.addColumn("Description", "report_desc");
		reportTable.setTableRowClickHandler(new TableRowClickHandler() {
			@Override
			public void handleRowClick(HashMap<String, String> data, int col) {
				if (canGenerate) {
					String uuid = data.get("report_uuid");
					thisReportUUID = uuid;
					getReportInformation(uuid);
				}
			}
		});

		final VerticalPanel paramContainer = new VerticalPanel();
		horizontalPanel.add(paramContainer);

		// Report label
		paramContainer.add(thisReportName);

		reportParametersTable = new FlexTable();
		paramContainer.add(reportParametersTable);
		reportParametersTable.setVisible(false);
		reportParametersTable.setSize("100%", "100%");

		reportActionPanel = new HorizontalPanel();
		reportActionPanel.setVisible(false);

		// PDF
		reportActionPDF = new PushButton();
		reportActionPDF
				.setHTML("<img src=\"resources/images/pdf.32x32.png\" /><br/>"
						+ "PDF");
		reportActionPDF.setStylePrimaryName("freemed-PushButton");
		reportActionPDF.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				runReport(ReportType.PDF);
			}
		});
		reportActionPanel.add(reportActionPDF);

		// HTML
		reportActionHTML = new PushButton();
		reportActionHTML
				.setHTML("<img src=\"resources/images/html.32x32.png\" /><br/>"
						+ "HTML");
		reportActionHTML.setStylePrimaryName("freemed-PushButton");
		reportActionHTML.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				runReport(ReportType.HTML);
			}
		});
		reportActionPanel.add(reportActionHTML);

		// XML
		reportActionXML = new PushButton();
		reportActionXML
				.setHTML("<img src=\"resources/images/xml.32x32.png\" /><br/>"
						+ "XML");
		reportActionXML.setStylePrimaryName("freemed-PushButton");
		reportActionXML.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				runReport(ReportType.XML);
			}
		});
		reportActionPanel.add(reportActionXML);

		paramContainer.add(reportActionPanel);

		// After everything is initialized, start population routine.
		populate(reportCategory);
	}

	protected void runReport(ReportType reportType) {
		// Get report type
		String type = null;
		switch (reportType) {
		case PDF:
			type = "pdf";
			break;
		case HTML:
			type = "html";
			break;
		case XLS:
			type = "xls";
			break;
		case XML:
			type = "xml";
			break;
		case TEXT:
		default:
			type = "text";
			break;
		}

		// Open window for request
		Window.open(Util.getJsonRequest(
				"org.freemedsoftware.module.Reporting.GenerateReport",
				new String[] { thisReportUUID, type,
						JsonUtil.jsonify(getParameters()) }), "Report", "");
	}

	public void populate(String reportCategory) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { locale, reportCategory };
			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.module.Reporting.GetReports",
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
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (result != null) {
								reportTable.loadData(result);
							} else {
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			getProxy().GetReports(locale,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] r) {
							reportTable.loadData(r);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Get array of parameter values for current report.
	 * 
	 * @return
	 */
	public String[] getParameters() {
		List<String> r = new ArrayList<String>();
		for (int iter = 0; iter < reportParameters.size(); iter++) {
			r.add(iter, reportParameters.get(Integer.valueOf(iter)));
		}
		return r.toArray(new String[0]);
	}

	/**
	 * Callback to convert report parameter information into a form.
	 * 
	 * @param data
	 */
	protected void populateReportParameters(HashMap<String, String> data) {

		reportParametersTable.clear();
		reportParameters.clear();
		thisReportName.setText(data.get("report_name"));

		for (int iter = 0; iter < new Integer(data.get("report_param_count"))
				.intValue(); iter++) {
			final int i = iter;
			final String iS = new Integer(iter).toString();
			String type = data.get("report_param_type_" + iS);
			String options = data.get("report_param_options_" + iS);
			reportParametersTable.setWidget(iter, 0, new Label(data
					.get("report_param_name_" + iS)));
			Widget w = null;
			if (type.compareToIgnoreCase("Date") == 0) {
				w = new CustomDatePicker();
				((CustomDatePicker) w)
						.addValueChangeHandler(new ValueChangeHandler<Date>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Date> event) {
								Date dt = ((CustomDatePicker) event.getSource())
										.getValue();
								CustomDatePicker w = ((CustomDatePicker) event
										.getSource());
								String formatted = w.getFormat().format(
										new DateBox(), dt);

								reportParameters.put(i, formatted);
							}
						});
			} else if (type.compareToIgnoreCase("Provider") == 0) {
				w = new SupportModuleWidget("ProviderModule");
				((SupportModuleWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								reportParameters.put(i,
										((SupportModuleWidget) event
												.getSource()).getValue()
												.toString());
							}
						});
			} else if (type.compareToIgnoreCase("Patient") == 0) {
				w = new PatientWidget();
				((PatientWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								reportParameters.put(i, ((PatientWidget) event
										.getSource()).getValue().toString());
							}
						});
			} else if (type.compareToIgnoreCase("SupportModule") == 0) {
				w = new SupportModuleWidget(options);// getting module name from
														// options
				((SupportModuleWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								reportParameters.put(i,
										((SupportModuleWidget) event
												.getSource()).getValue()
												.toString());
							}
						});
			} else if (type.compareToIgnoreCase("EMRModule") == 0) {
				w = new EMRModuleWidget(options, patientId);// getting module
															// name from options
				((EMRModuleWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								reportParameters.put(i,
										((EMRModuleWidget) event.getSource())
												.getValue().toString());
							}
						});
			} else if (type.compareToIgnoreCase("List") == 0) {
				w = new CustomListBox();// getting module name from options
				String[] items = options.split(";");
				((CustomListBox) w).addItem("Select", "select");
				for (int index = 0; index < items.length; index++) {
					String[] item = items[index].split(":");
					String text = item[0];
					String value = item[1];
					if (index == 0)
						text = text.substring(1);
					if (index == items.length - 1)
						value = value.substring(0, value.length() - 1);
					((CustomListBox) w).addItem(text, value);
				}
				((CustomListBox) w).addChangeHandler(new ChangeHandler() {
					@Override
					public void onChange(ChangeEvent event) {
						reportParameters.put(i, ((CustomListBox) event
								.getSource()).getStoredValue());
					}
				});
			} else {
				// Default to text box
				w = new TextBox();
				((TextBox) w).addChangeHandler(new ChangeHandler() {
					@Override
					public void onChange(ChangeEvent evt) {
						reportParameters.put(i, ((TextBox) evt.getSource())
								.getText());
					}
				});
			}
			reportParameters.put(iter, "");
			reportParametersTable.setWidget(iter, 1, w);
		}

		// Show this when everything is populated
		reportParametersTable.setVisible(true);
		reportActionPanel.setVisible(true);
	}

	/**
	 * Get parameters for a specific report by uuid.
	 * 
	 * @param uuid
	 */
	public void getReportInformation(String uuid) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { uuid };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Reporting.GetReportParameters",
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
							HashMap<String, String> result = (HashMap<String, String>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>");
							if (result != null) {
								populateReportParameters(result);
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			getProxy().GetReportParameters(uuid,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> r) {
							populateReportParameters(r);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	protected ReportingAsync getProxy() {
		ReportingAsync p = null;
		try {
			p = (ReportingAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Module.Reporting");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}

	public void setPatientId(Integer patientId) {
		this.patientId = patientId;
	}
}
