package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomListBox;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ChangeListener;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class UserManagementScreen extends ScreenInterface implements
		ClickListener {

	protected CustomSortableTable wUsers = new CustomSortableTable();

	protected TextBox tbUsername, tbPassword, tbPasswordverify, tbDescription;

	protected CustomListBox lbUserType;

	protected SupportModuleWidget lbActualPhysician;

	protected Button addUserButton, clearButton;

	public UserManagementScreen() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final TabPanel tabPanel = new TabPanel();
		verticalPanel.add(tabPanel);

		// Panel #1

		final FlexTable userAddTable = new FlexTable();
		tabPanel.add(userAddTable, "Add User");
		tabPanel.selectTab(0);
		final Label usernameLabel = new Label("Username");
		userAddTable.setWidget(0, 0, usernameLabel);

		tbUsername = new TextBox();
		userAddTable.setWidget(0, 1, tbUsername);
		userAddTable.getFlexCellFormatter().setColSpan(0, 1, 2);
		tbUsername.setWidth("10em");

		final Label passwordLabel = new Label("Password");
		userAddTable.setWidget(1, 0, passwordLabel);

		tbPassword = new TextBox();
		userAddTable.setWidget(1, 1, tbPassword);
		userAddTable.getFlexCellFormatter().setColSpan(1, 1, 2);
		tbPassword.setWidth("10em");

		final Label passwordverifyLabel = new Label("Password (Verify)");
		userAddTable.setWidget(2, 0, passwordverifyLabel);

		tbPasswordverify = new TextBox();
		userAddTable.setWidget(2, 1, tbPasswordverify);
		userAddTable.getFlexCellFormatter().setColSpan(2, 1, 2);
		tbPasswordverify.setWidth("10em");

		final Label descriptionLabel = new Label("Description");
		userAddTable.setWidget(3, 0, descriptionLabel);

		tbDescription = new TextBox();
		userAddTable.setWidget(3, 1, tbDescription);
		userAddTable.getFlexCellFormatter().setColSpan(3, 1, 2);
		tbDescription.setWidth("100%");

		final Label userTypeLabel = new Label("User Type");
		userAddTable.setWidget(4, 0, userTypeLabel);

		lbUserType = new CustomListBox();
		userAddTable.setWidget(4, 1, lbUserType);
		userAddTable.getFlexCellFormatter().setColSpan(4, 1, 2);
		lbUserType.addItem("Miscellaneous", "misc");
		lbUserType.addItem("Provider", "phy");
		lbUserType.addChangeListener(new ChangeListener() {
			public void onChange(Widget sender) {
				String value = ((CustomListBox) sender).getWidgetValue();
				if (value.compareTo("phy") == 0) {
					// Is provider
					// TODO: enable lbActualPhysician
				} else {
					// Is not provider
					// TODO: disable lbActualPhysician
				}
			}
		});

		final Label actualPhysicianLabel = new Label("Actual Physician");
		userAddTable.setWidget(5, 0, actualPhysicianLabel);

		lbActualPhysician = new SupportModuleWidget("ProviderModule");
		userAddTable.setWidget(5, 1, lbActualPhysician);
		userAddTable.getFlexCellFormatter().setColSpan(5, 1, 2);

		addUserButton = new Button();
		userAddTable.setWidget(6, 1, addUserButton);
		addUserButton.setText("Add User");
		addUserButton.addClickListener(this);

		clearButton = new Button();
		userAddTable.setWidget(6, 2, clearButton);
		clearButton.setText("Clear");
		clearButton.addClickListener(this);

		// Panel #2

		final FlexTable userListTable = new FlexTable();
		tabPanel.add(userListTable, "List Users");

		userListTable.setWidget(0, 0, wUsers);

		wUsers.setSize("100%", "100%");
		wUsers.addColumn("Username", "username"); // col 0
		wUsers.addColumn("Description", "userdescrip"); // col 1
		wUsers.addColumn("Level", "userlevel"); // col 2
		wUsers.addColumn("Type", "usertype"); // col 3
		wUsers.setIndexName("id");

		wUsers.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents ste, int row, int col) {
				final Integer id = new Integer(wUsers.getValueByRow(row));
			}
		});

		// TODO:Backend needs to be fixed first
		// retrieveAllUsers();

	}

	public void onClick(Widget w) {
		if (w == addUserButton) {
			// TODO: verify form and submit data
		} else if (w == clearButton) {
			clearForm();
		}
	}

	public void clearForm() {
		tbUsername.setText("");
		tbPassword.setText("");
		tbPasswordverify.setText("");
		tbDescription.setText("");
		lbUserType.setWidgetValue("misc");
		lbActualPhysician.clear();
	}

	public void retrieveAllUsers() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Do nothing
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			// Use JSON-RPC to retrieve the data
			String[] params = {};

			RequestBuilder builder = new RequestBuilder(RequestBuilder.POST,
					URL.encode(Util.getJsonRequest(
							"org.freemedsoftware.api.UserInterface.GetAll",
							params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						GWT.log(request.toString(), ex);
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (response.getStatusCode() == 200) {
							HashMap<String, String>[] data = (HashMap<String, String>[]) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>[]");
							if (data != null) {
								wUsers.clear();
								wUsers.loadData(data);
							}
						}
					}
				});
			} catch (RequestException e) {
				// nothing here right now
			}
		} else if (Util.getProgramMode() == ProgramMode.NORMAL) {
			// Use GWT-RPC to retrieve the data
			// TODO: Create that stuff
		}

	}

}
