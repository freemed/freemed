package org.freemedsoftware.gwt.client.i18n;

import java.util.HashMap;
import java.util.Map;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONObject;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.json.client.JSONString;
import com.google.gwt.json.client.JSONValue;

/**
 * Class to facilitate dynamic locale lookups without requiring static
 * compile-time locale mappings. Bit more "expensive" at runtime, but should
 * integrate with current language selection better.
 */
public class I18nUtil {

	protected static String loadedLocaleName = null;
	protected static String RESOURCES_DIR = new String(GWT.getHostPageBaseURL()
			+ "resources/locale/");
	protected static boolean BUSY_LOADING = false;

	protected static Map<String, String> locale = new HashMap<String, String>();

	public static int MAX_LOAD_WAIT_LOOPS = 30;
	public static long MAX_LOAD_WAIT_LOOP_MS = 500;

	/**
	 * Internal method to load definitions from the server.
	 * 
	 * @param localeName
	 */
	protected static void loadLocale(String localeName) {
		String loadFrom = RESOURCES_DIR + localeName + ".json";
		loadedLocaleName = localeName;
		RequestBuilder builder = new RequestBuilder(RequestBuilder.GET, URL
				.encode(loadFrom));
		BUSY_LOADING = true;
		try {
			builder.sendRequest(null, new RequestCallback() {
				public void onError(Request request, Throwable exception) {
					BUSY_LOADING = false;
					JsonUtil.debug(exception.toString());
				}

				public void onResponseReceived(Request request,
						Response response) {
					if (200 == response.getStatusCode()) {
						long beginTime = System.currentTimeMillis();
						JSONValue o = JSONParser
								.parseStrict(response.getText());
						if (o.isObject() != null) {
							JsonUtil.debug("Bad data presented.");
							BUSY_LOADING = false;
						}

						// Empty out *everything* we have now.
						locale.clear();

						// Import everything
						for (String key : ((JSONObject) o).keySet()) {
							if (((JSONObject) o).get(key) != null) {
								locale.put(key, ((JSONString) ((JSONObject) o)
										.get(key)).stringValue());
							}
						}

						long endTime = System.currentTimeMillis();

						JsonUtil.debug("Locale " + loadedLocaleName
								+ " parsed and loaded in "
								+ (endTime - beginTime) + "ms");

						BUSY_LOADING = false;
					} else {
						BUSY_LOADING = false;
						JsonUtil.debug(response.getStatusText());
					}
				}
			});
		} catch (RequestException e) {
			BUSY_LOADING = false;
			JsonUtil.debug(e.toString());
		}
	}

	/**
	 * Lookup a string for the current locale.
	 * 
	 * @param x
	 * @return
	 */
	public static String _(String x) {
		String useLocaleName = CurrentState.getLocale();
		if (useLocaleName != loadedLocaleName) {
			loadLocale(useLocaleName);
			loadedLocaleName = useLocaleName;
		}

		String found = locale.get(x);

		// If we don't have any translation, send back source string.
		return (found != null && found.length() > 0) ? found : x;
	}

}
