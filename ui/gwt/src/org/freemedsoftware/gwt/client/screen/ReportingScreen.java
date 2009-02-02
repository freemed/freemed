/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.ReportingAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.simpledatepicker.SimpleDatePicker;

public class ReportingScreen extends ScreenInterface {

	protected CustomSortableTable reportTable;

	protected FlexTable reportParametersTable;

	protected HorizontalPanel reportActionPanel;

	protected HashMap<Integer, String> reportParameters = new HashMap<Integer, String>();

	protected static String locale = "en_US";

	protected String thisReportUUID = null;

	protected Label thisReportName = new Label();

	protected PushButton reportActionHTML, reportActionXML, reportActionPDF;

	public enum ReportType {
		PDF, XLS, HTML, TEXT, XML
	};

	public ReportingScreen() {

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

		reportTable = new CustomSortableTable();
		verticalPanel.add(reportTable);
		reportTable.setSize("100%", "100%");
		reportTable.setIndexName("report_uuid");
		reportTable.addColumn("Name", "report_name");
		reportTable.addColumn("Description", "report_desc");
		reportTable.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents sender, int row,
					int cell) {
				String uuid = reportTable.getValueByRow(row);
				thisReportUUID = uuid;
				getReportInformation(uuid);
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
		reportActionPDF.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
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
		reportActionHTML.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
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
		reportActionXML.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				runReport(ReportType.XML);
			}
		});
		reportActionPanel.add(reportActionXML);

		paramContainer.add(reportActionPanel);

		// After everything is initialized, start population routine.
		populate();
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

	public void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { locale };
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
			reportParametersTable.setText(iter, 0, data
					.get("report_param_name_" + iS));
			Widget w = null;
			if (type.compareToIgnoreCase("Date") == 0) {
				w = new SimpleDatePicker();
				((SimpleDatePicker) w).addChangeListener(new ChangeListener() {
					public void onChange(Widget sender) {
						reportParameters.put(i, ((SimpleDatePicker) sender)
								.getCurrentDate().toString());
					}
				});
			} else if (type.compareToIgnoreCase("Provider") == 0) {
				w = new SupportModuleWidget("ProviderModule");
				((SupportModuleWidget) w)
						.addChangeListener(new ChangeListener() {
							public void onChange(Widget sender) {
								reportParameters.put(i,
										((SupportModuleWidget) sender)
												.getValue().toString());
							}
						});
			} else if (type.compareToIgnoreCase("Patient") == 0) {
				w = new PatientWidget();
				((PatientWidget) w).addChangeListener(new ChangeListener() {
					public void onChange(Widget sender) {
						reportParameters.put(i, ((PatientWidget) sender)
								.getValue().toString());
					}
				});
			} else {
				// Default to text box
				w = new TextBox();
				((TextBox) w).addChangeListener(new ChangeListener() {
					public void onChange(Widget sender) {
						reportParameters.put(i, ((TextBox) sender).getText());
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

}
