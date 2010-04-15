package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.logical.shared.HasValueChangeHandlers;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.SuggestOracle;
import com.google.gwt.user.client.ui.SuggestOracle.Callback;
import com.google.gwt.user.client.ui.SuggestOracle.Request;

public class CustomModuleWidget extends AsyncPicklistWidgetBase implements
HashSetter{
	protected String moduleName = null;

	protected String hashMapping = null;

	public CustomModuleWidget() {
		super();
	}

	public CustomModuleWidget(String module) {
		// Load superclass constructor first...
		super();
		setModuleName(module);
	}

	/**
	 * Set value of current widget based on integer value, asynchronously.
	 * 
	 * @param widgetValue
	 */
	public void setValue(Integer widgetValue) {
		value = widgetValue;
	}
	/**
	 * Set text within search box.
	 * 
	 * @param text
	 */
	public void setText(String text){
		searchBox.setText(text);
		searchBox.setTitle(text);
	}
	/**
	 * Set module class name.
	 * 
	 * @param module
	 */
	public void setModuleName(String module) {
		moduleName = module;
	}

	protected void loadSuggestions(String req, final Request r,
			final Callback cb) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Handle in a stubbed sort of way
			List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
			map.clear();
			addKeyValuePair(items, new String("Hackenbush, Hugo Z"),
					new String("1"));
			addKeyValuePair(items, new String("Firefly, Rufus T"), new String(
					"2"));
			cb.onSuggestionsReady(r, new SuggestOracle.Response(items));
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {req };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware."+moduleName,
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(
							com.google.gwt.http.client.Request request,
							Throwable ex) {
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(
							com.google.gwt.http.client.Request request,
							com.google.gwt.http.client.Response response) {
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								HashMap<String, String> result = (HashMap<String, String>) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>");
								if (result != null) {
									Set<String> keys = result.keySet();
									Iterator<String> iter = keys.iterator();

									List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
									map.clear();
									while (iter.hasNext()) {
										final String key = (String) iter.next();
										final String val = (String) result
												.get(key);
										addKeyValuePair(items, val, key);
									}
									cb.onSuggestionsReady(r,
											new SuggestOracle.Response(items));
								}else // if no result then set value to 0
									setValue(0);
							} else {
								GWT.log("Result " + response.getStatusText(),
										null);
							}
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception thrown: ", e);
			}
		} else {
			ModuleInterfaceAsync service = null;
			try {
				service = ((ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface"));
			} catch (Exception e) {
			}

			service.ModuleSupportPicklistMethod(moduleName, req,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> result) {
							Set<String> keys = result.keySet();
							Iterator<String> iter = keys.iterator();

							List<SuggestOracle.Suggestion> items = new ArrayList<SuggestOracle.Suggestion>();
							map.clear();
							while (iter.hasNext()) {
								final String key = (String) iter.next();
								final String val = (String) result.get(key);
								addKeyValuePair(items, val, key);
							}
							cb.onSuggestionsReady(r,
									new SuggestOracle.Response(items));
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception thrown: ", t);
						}

					});
		}
	}

	@Override
	public void getTextForValue(Integer val) {
		
	}

	public void setHashMapping(String hm) {
		hashMapping = hm;
	}

	public String getStoredValue() {
		return getValue().toString();
	}

	public String getHashMapping() {
		return hashMapping;
	}

	public void setFromHash(HashMap<String, String> data) {
		setValue(Integer.parseInt(data.get(hashMapping)));
	}
	
	public void setEnable(boolean val){
		textBox.setEnabled(val);
	}
}
