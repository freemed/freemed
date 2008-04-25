package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FileUpload;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class DocumentManager extends Composite {

	public DocumentManager() {

		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);

		final AbsolutePanel absolutePanel = new AbsolutePanel();
		verticalPanel.add(absolutePanel);
		absolutePanel.setSize("100%", "50px");

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);
	}

}
