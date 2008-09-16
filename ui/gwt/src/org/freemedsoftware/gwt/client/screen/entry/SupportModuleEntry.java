package org.freemedsoftware.gwt.client.screen.entry;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.EntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.xml.client.DOMException;
import com.google.gwt.xml.client.Document;
import com.google.gwt.xml.client.Element;
import com.google.gwt.xml.client.Node;
import com.google.gwt.xml.client.NodeList;
import com.google.gwt.xml.client.XMLParser;

public class SupportModuleEntry extends EntryScreenInterface {

	protected String rawXml = "";

	public SupportModuleEntry() {
		initWidget(ui);
	}

	public SupportModuleEntry(String module) {
		setModuleName(module);
		initWidget(ui);
	}

	public String validateData(HashMap<String, String> data) {
		String message = "";
		try {
			// parse the XML document into a DOM
			Document dom = XMLParser.parse(rawXml);

			// find the sender's display name in an attribute of the <from> tag
			Node simpleUIBuilderNode = dom.getElementsByTagName(
					"SimpleUIBuilder").item(0);
			if (simpleUIBuilderNode != null) {
				NodeList elements = dom.getElementsByTagName("Element");
				for (int iter = 0; iter < elements.getLength(); iter++) {
					Element e = (Element) elements.item(iter);
					try {
						String t = e.getAttribute("title");
						String var = e.getAttribute("field");
						String req = e.getAttribute("requirements");
						if (req.compareToIgnoreCase("NOTNULL") == 0) {
							if (data.get(var).length() < 1) {
								message += t + " " + "requires a value." + "\n";
							}
						}
					} catch (Exception ex) {
						// Ignore, continue if no element
					}
				}
			} else {
				// Deal with other possibilities
			}
		} catch (DOMException e) {
			GWT.log("Could not parse XML document.", e);
		}

		if (message == "") {
			return null;
		} else {
			return message;
		}
	}

	protected void buildForm() {
		// Get XML file name from module
		final String interfaceUrl = Util.getBaseUrl() + "/resources/interface/"
				+ moduleName + ".module.xml";
		RequestBuilder builder = new RequestBuilder(RequestBuilder.GET, URL
				.encode(interfaceUrl));
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onResponseReceived(Request request,
						Response response) {
					if (200 == response.getStatusCode()) {
						rawXml = response.getText();
						xmlToForm(response.getText());
					} else {
						GWT.log("Error requesting " + interfaceUrl + ": "
								+ response.getStatusText(), null);
					}
				}

				public void onError(Request request, Throwable exception) {
					GWT.log("Exception", exception);
				}
			});
		} catch (RequestException e) {
			GWT.log("RequestException", e);
		}
	}

	/**
	 * Process a piece of interface XML into a form.
	 * 
	 * @param xml
	 */
	protected void xmlToForm(String xml) {
		try {
			// parse the XML document into a DOM
			Document dom = XMLParser.parse(xml);

			// find the sender's display name in an attribute of the <from> tag
			Node simpleUIBuilderNode = dom.getElementsByTagName(
					"SimpleUIBuilder").item(0);
			if (simpleUIBuilderNode != null) {
				NodeList elements = dom.getElementsByTagName("Element");
				for (int iter = 0; iter < elements.getLength(); iter++) {
					Element e = (Element) elements.item(iter);
					ui.addWidget(e.getAttribute("field"), e
							.getAttribute("title"), ui.stringToWidgetType(e
							.getAttribute("type")), e.getAttribute("options"),
							null);
				}
			} else {
				// Deal with other possibilities
			}
		} catch (DOMException e) {
			GWT.log("Could not parse XML document.", e);
		}
	}

	public void setModuleName(String module) {
		moduleName = module;
		buildForm();
	}

	protected String getModuleName() {
		return moduleName;
	}

}
