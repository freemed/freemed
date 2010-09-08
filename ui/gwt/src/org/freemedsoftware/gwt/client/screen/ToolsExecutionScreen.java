package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomDatePicker;
import org.freemedsoftware.gwt.client.widget.PatientTagWidget;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.UserWidget;

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
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.google.gwt.user.datepicker.client.DateBox;

public class ToolsExecutionScreen  extends ScreenInterface {
	
	public final static String moduleNameACL = "admin";
	protected PushButton toolActionButton;
	private HashMap<String, String> map;
	protected String thisToolUUID = null;
	protected FlexTable toolParametersTable;
	protected HashMap<Integer, String> toolParameters = new HashMap<Integer, String>();
	public ToolsExecutionScreen(String toolName, String tooluuid){
		super(moduleNameACL);
		final VerticalPanel paramContainer = new VerticalPanel();
		initWidget(paramContainer);
		toolParametersTable = new FlexTable();
		paramContainer.add(toolParametersTable);
		
		HorizontalPanel toolActionPanel = new HorizontalPanel();

		toolActionButton = new PushButton();
		toolActionButton
				.setHTML("<img src=\"resources/images/check_go.32x32.png\" /><br/>"
						+ "Run");
		toolActionButton.setStylePrimaryName("freemed-PushButton");
		toolActionButton.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent evt) {
				runTool();
			}
		});
		toolActionPanel.add(toolActionButton);

		paramContainer.add(toolActionPanel);
		thisToolUUID=tooluuid;
		
		getToolInformation(thisToolUUID);
		
	}
	
	protected void runTool() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			//toolTable.showloading(true);
			String[] params = {thisToolUUID,JsonUtil.jsonify(getParameters())};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Tools.ExecuteTool",
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
							populateToolParameters(map);
						} else {
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			// TODO: Make this work with GWT-RPC
		}
	}
	
	/**
	 * Get parameters for a specific tool by uuid.
	 * 
	 * @param uuid
	 */
	public void getToolInformation(String uuid) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// TODO: handle stubbed
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { uuid };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.module.Tools.GetToolParameters",
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
								map=result;
								populateToolParameters(result);
							}
						} else {
						}
					}
				});
			} catch (RequestException e) {
				GWT.log("Exception", e);
			}
		} else {
			// TODO: Make this work with GWT-RPC
		}
	}
	
	/**
	 * Callback to convert tool parameter information into a form.
	 * 
	 * @param data
	 */
	protected void populateToolParameters(HashMap<String, String> data) {
		toolParametersTable.clear(true);
		toolParameters.clear();

		for (int iter = 0; iter < new Integer(data.get("tool_param_count"))
				.intValue(); iter++) {
			final int i = iter;
			final String iS = new Integer(iter).toString();
			String type = data.get("tool_param_type_" + iS);
			toolParametersTable.setText(iter, 0, data
					.get("tool_param_name_" + iS));
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

								toolParameters.put(i, formatted);
							}
						});
			} else if (type.compareToIgnoreCase("Provider") == 0) {
				w = new SupportModuleWidget("ProviderModule");
				((SupportModuleWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								toolParameters.put(i,
										((SupportModuleWidget) event
												.getSource()).getValue()
												.toString());
							}
						});
			} else if (type.compareToIgnoreCase("Tag") == 0) {
				w = new PatientTagWidget();
				((PatientTagWidget) w)
						.addChangeHandler(new ValueChangeHandler<String>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<String> event) {
								toolParameters.put(i,
										((PatientTagWidget) event.getSource())
												.getValue());
							}
						});
			} else if (type.compareToIgnoreCase("Patient") == 0) {
				w = new PatientWidget();
				((PatientWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								toolParameters.put(i, ((PatientWidget) event
										.getSource()).getValue().toString());
							}
						});
			} else if (type.compareToIgnoreCase("User") == 0) {
				w = new UserWidget();
				((UserWidget) w)
						.addChangeHandler(new ValueChangeHandler<Integer>() {
							@Override
							public void onValueChange(
									ValueChangeEvent<Integer> event) {
								toolParameters.put(i, ((UserWidget) event
										.getSource()).getValue().toString());
							}
						});
			} else {
				// Default to text box
				w = new TextBox();
				((TextBox) w).addChangeHandler(new ChangeHandler() {
					@Override
					public void onChange(ChangeEvent evt) {
						toolParameters.put(i, ((TextBox) evt.getSource())
								.getText());
					}
				});
			}
			toolParameters.put(iter, "");
			toolParametersTable.setWidget(iter, 1, w);
		}

		// Show this when everything is populated
		toolParametersTable.setVisible(true);
	}
	
	/**
	 * Get array of parameter values for current tool.
	 * 
	 * @return
	 */
	public String[] getParameters() {
		List<String> r = new ArrayList<String>();
		for (int iter = 0; iter < toolParameters.size(); iter++) {
			r.add(iter, toolParameters.get(Integer.valueOf(iter)));
		}
		return r.toArray(new String[0]);
	}
}
