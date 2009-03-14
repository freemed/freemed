package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;

import com.google.gwt.core.client.GWT;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class UserManagementScreen extends ScreenInterface {

	protected CustomSortableTable wUsers = new CustomSortableTable();

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

		final TextBox tbUsername = new TextBox();
		userAddTable.setWidget(0, 1, tbUsername);
		userAddTable.getFlexCellFormatter().setColSpan(0, 1, 2);
		tbUsername.setWidth("100%");

		final Label passwordLabel = new Label("Password");
		userAddTable.setWidget(1, 0, passwordLabel);

		final TextBox tbPassword = new TextBox();
		userAddTable.setWidget(1, 1, tbPassword);
		userAddTable.getFlexCellFormatter().setColSpan(1, 1, 2);
		tbPassword.setWidth("100%");

		final Label passwordverifyLabel = new Label("Password (Verify)");
		userAddTable.setWidget(2, 0, passwordverifyLabel);

		final TextBox tbPasswordverify = new TextBox();
		userAddTable.setWidget(2, 1, tbPasswordverify);
		userAddTable.getFlexCellFormatter().setColSpan(2, 1, 2);
		tbPasswordverify.setWidth("100%");

		final Label descriptionLabel = new Label("Description");
		userAddTable.setWidget(3, 0, descriptionLabel);

		final TextBox tbDescription = new TextBox();
		userAddTable.setWidget(3, 1, tbDescription);
		userAddTable.getFlexCellFormatter().setColSpan(3, 1, 2);
		tbDescription.setWidth("100%");

		final Label userTypeLabel = new Label("User Type");
		userAddTable.setWidget(4, 0, userTypeLabel);

		final ListBox lbUserType = new ListBox();
		userAddTable.setWidget(4, 1, lbUserType);
		userAddTable.getFlexCellFormatter().setColSpan(4, 1, 2);

		final Label actualPhysicianLabel = new Label("Actual Physician");
		userAddTable.setWidget(5, 0, actualPhysicianLabel);

		final ListBox lbActualPhysician = new ListBox();
		userAddTable.setWidget(5, 1, lbActualPhysician);
		userAddTable.getFlexCellFormatter().setColSpan(5, 1, 2);

		final Button addUserButton = new Button();
		userAddTable.setWidget(6, 1, addUserButton);
		addUserButton.setText("Add User");

		final Button clearButton = new Button();
		userAddTable.setWidget(6, 2, clearButton);
		clearButton.setText("Clear");

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
				final Integer uffId = new Integer(wUsers.getValueByRow(row));
			}
		});

		//TODO:Backend needs to be fixed first
		//retrieveData();

}

	public void retrieveData() {
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
