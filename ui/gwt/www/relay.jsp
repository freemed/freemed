<%
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
 %>
<%@page import="java.io.IOException"%>
<%@page import="org.apache.commons.httpclient.HttpClient"%>
<%@page import="org.apache.commons.httpclient.methods.GetMethod"%>
<%@page import="org.apache.commons.httpclient.params.HttpMethodParams"%>
<%@page	import="org.apache.commons.httpclient.DefaultHttpMethodRetryHandler"%>
<%@page import="org.apache.commons.httpclient.HttpStatus"%>
<%@page import="org.apache.commons.httpclient.HttpException"%>
<%!static HttpClient client = new HttpClient();

	static String serverURL = "http://demo.b-mas.com/freemed//relay.php/json/";
	
	synchronized void readServer(HttpServletRequest request,
			HttpServletResponse response) {
		try {
			String module = request.getParameter("module");
			String queryString = request.getQueryString();
			String params = "";
			int paramIndex = queryString.indexOf("param0");
			if (paramIndex != -1)
				params = queryString.substring(paramIndex);
			String url = serverURL + module + "?" + params;

			GetMethod method = new GetMethod(url);

			// Provide custom retry handler is necessary
			method.getParams().setParameter(HttpMethodParams.RETRY_HANDLER,
					new DefaultHttpMethodRetryHandler(3, false));

			try {
				// Execute the method.
				int statusCode = client.executeMethod(method);

				if (statusCode != HttpStatus.SC_OK) {
					System.err.println("Method failed: "
							+ method.getStatusLine());
				}

				// Read the response body.
				byte[] responseBody = method.getResponseBody();

				// Deal with the response.
				// Use caution: ensure correct character encoding and is not binary data
				//System.out.println(new String(responseBody));
				response.getWriter().write(new String(responseBody));

			} catch (HttpException e) {
				System.err.println("Fatal protocol violation: "
						+ e.getMessage());
				e.printStackTrace();
			} catch (IOException e) {
				System.err.println("Fatal transport error: " + e.getMessage());
				e.printStackTrace();
			} finally {
				// Release the connection.
				method.releaseConnection();
			}
		} catch (Exception e) {
		}
	}%>

<%
	readServer(request, response);
%>