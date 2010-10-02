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

import java.util.Date;

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
import com.googlecode.gchart.client.GChart;

public class GrowthChartScreen extends PatientScreenInterface {

	public class GrowthChart extends GChart {

		protected final int WIDTH = 400;
		protected final int HEIGHT = 280;

		protected String title;

		protected String[][] stockData = new String[][] {};

		public GrowthChart() {
			setChartSize(WIDTH, HEIGHT);
			setWidth("100%");
		}

		public String[][] getStockData() {
			return this.stockData;
		}

		public void setStockData(String[][] stockData) {
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
			// Figure number of data curves
			int offset = 4;
			int numCurves = stockData[0].length - offset;

			for (int c = 0; c < numCurves; c++) {
				// Create individual curves
				addCurve();
				getCurve().getSymbol().setHeight(10);
				getCurve().getSymbol().setWidth(10);
				getCurve().getSymbol().setBorderWidth(3);
				getCurve().getSymbol().setSymbolType(SymbolType.LINE);

				// Iterate through all data points on the chart
				for (int p = 0; p < stockData.length; p++) {
					getCurve().addPoint(Double.parseDouble(stockData[p][0]),
							Double.parseDouble(stockData[p][c + offset]));
				}
			}
		}

	}

	protected String gender = "";

	protected Boolean height = false;

	protected Date birthDate = new Date();

	protected GrowthChart growthChart = new GrowthChart();
	
	public GrowthChartScreen() {
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

	public void populateStockData() {
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

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							String[][] r = (String[][]) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"String[][]");
							if (r != null) {
								growthChart.setStockData(r);
								growthChart.drawStockCurves();
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
