package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DisclosurePanel;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.FormPanel;
import com.google.gwt.user.client.ui.HTMLPanel;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.SuggestBox;
import com.google.gwt.user.client.ui.TextBox;

public class CptEdit extends Composite {

	public CptEdit() {

		final DockPanel dockPanel = new DockPanel();
		initWidget(dockPanel);

		final HTMLPanel panel = new HTMLPanel("<h1>CPT</h1>\r\n<p>edit and modify panel\r\n</p>");
		dockPanel.add(panel, DockPanel.NORTH);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		dockPanel.add(horizontalPanel, DockPanel.CENTER);

		final Label label = new Label("New Label");
		horizontalPanel.add(label);

		final SuggestBox suggestBox = new SuggestBox();
		horizontalPanel.add(suggestBox);

		final DisclosurePanel disclosurePanel = new DisclosurePanel("more options");
		dockPanel.add(disclosurePanel, DockPanel.SOUTH);
		disclosurePanel.setOpen(true);
		disclosurePanel.setWidth("100%");

		final FormPanel formPanel = new FormPanel();
		disclosurePanel.setContent(formPanel);
	}

}
