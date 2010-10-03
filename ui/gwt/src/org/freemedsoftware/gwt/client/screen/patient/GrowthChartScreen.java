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

package org.freemedsoftware.gwt.client.screen.patient;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.googlecode.gchart.client.GChart;

public class GrowthChartScreen extends PatientScreenInterface {

	public class GrowthChart extends GChart {

		protected final int WIDTH = 400;
		protected final int HEIGHT = 280;

		protected String title;

		protected HashMap<String, String>[] stockData = null;

		public GrowthChart() {
			setChartSize(WIDTH, HEIGHT);
			setWidth("100%");

			getXAxis().setTickCount(0);
			getXAxis().setTickLength(6);
			getXAxis().setTickThickness(1);
			getXAxis().setAxisMin(0);
			getXAxis().setTickLabelThickness(20);
			getXAxis().setAxisLabelThickness(20);

			getYAxis().setAxisMin(0);
			getYAxis().setAxisMax(100);
			getYAxis().setTickCount(11);
			getYAxis().setHasGridlines(true);
			getYAxis().setTickLabelFormat("#,###");
			// setChartFootnotes("");
			setChartFootnotesThickness(50);

			update();
		}

		public HashMap<String, String>[] getStockData() {
			return this.stockData;
		}

		public void setStockData(HashMap<String, String>[] stockData) {
			this.stockData = stockData;
		}

		/**
		 * Set title displayed on top of graph.
		 */
		@Override
		public void setTitle(String newTitle) {
			title = newTitle;
			setChartTitle("<big><b>" + title + "<br>&nbsp;</b></big>");
			setChartTitleThickness(40);
		}

		public void drawStockCurves() {
			if (stockData == null) {
				JsonUtil
						.debug("Can't draw curves, no data has been loaded yet!");
				return;
			}

			// Figure number of data curves
			JsonUtil.debug("stockData.length = " + stockData.length);
			int offset = 5;
			int numCurves = stockData[0].size() - offset;
			List<String> keys = new ArrayList<String>(stockData[0].keySet());

			for (int c = 0; c < numCurves; c++) {
				// Create individual curves
				addCurve();
				getCurve().getSymbol().setHeight(1);
				getCurve().getSymbol().setWidth(1);
				getCurve().getSymbol().setBorderWidth(1);
				getCurve().getSymbol().setSymbolType(SymbolType.LINE);

				// Iterate through all data points on the chart
				for (int iter = 0; iter < stockData.length; iter++) {
					getCurve().addPoint(
							Double.parseDouble(stockData[iter].get("agemos")),
							Double.parseDouble(stockData[iter].get(keys.get(c
									+ offset))));
				}
			}

			update();
		}
	}

	protected String gender = "";

	protected Date birthDate = new Date();

	protected GrowthChart growthChart = new GrowthChart();

	protected GrowthChart hChart = new GrowthChart();
	protected GrowthChart wChart = new GrowthChart();

	public GrowthChartScreen() {
		VerticalPanel vPanel = new VerticalPanel();
		initWidget(vPanel);

		vPanel.add(new Label("Height/Length"));
		vPanel.add(new HTML("&nbsp;"));
		vPanel.add(hChart);
		hChart.getYAxis().setAxisLabel("Height/Length");
		hChart.getXAxis().setAxisLabel("Age (months)");
		vPanel.add(new HTML("&nbsp;<br/>&nbsp;"));
		vPanel.add(new Label("Weight"));
		vPanel.add(new HTML("&nbsp;"));
		vPanel.add(wChart);
		wChart.getYAxis().setAxisLabel("Weight");
		wChart.getXAxis().setAxisLabel("Age (months)");
	}

	public void init() {
		// Start callbacks to populate data
		populateStockData(true, hChart);
		populateStockData(false, wChart);
		// TODO: populate with patient data
	}

	public void setGender(String mf) {
		this.gender = mf;
	}

	public void setBirthDate(Date birthDate) {
		this.birthDate = birthDate;
	}

	/**
	 * Determine age in months of a certain date.
	 * 
	 * @param dob
	 * @return
	 */
	public int getMonthsAge(Date dob) {
		long ageMs = new Date().getTime() - dob.getTime();
		return (int) (ageMs / (1000 * 3600 * (365 / 12)));
	}

	public void populateStockData(boolean height, final GrowthChart chart) {
		Boolean infant = false;
		if (getMonthsAge(birthDate) < 36) {
			infant = true;
		} else {
			infant = false;
		}

		if (Util.getProgramMode() == ProgramMode.STUBBED) {
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { gender, JsonUtil.jsonify(height),
					JsonUtil.jsonify(infant) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.GrowthCharts.GetGrowthChartValues",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("GrowthCharts",
								"Failed to retrieve stock growth chart data.");
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()
								|| response.getText() == "false") {
							HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (r != null) {
								chart.setStockData(r);
								chart.drawStockCurves();
							}
						} else {
							Util
									.showErrorMsg("GrowthCharts",
											"Failed to retrieve stock growth chart data.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("GrowthCharts",
						"Failed to retrieve stock growth chart data.");
			}
		} else {
			JsonUtil.debug("Unimplemented GrowthCharts RPC");
		}

	}

}
