package org.freemedsoftware.gwt.client.widget;

import java.util.HashMap;

import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class SimpleUIBuilder extends Composite {

	public interface Receiver {
		/**
		 * Handle data.
		 * 
		 * @param data
		 */
		public void processData(HashMap<String, String> data);
	};

	protected FlexTable table;

	protected Receiver receiver = null;

	protected HashMap<String, Widget> widgets;

	public SimpleUIBuilder() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setWidth("100%");

		table = new FlexTable();
		verticalPanel.add(table);

		Button commitChangesButton = new Button("Commit Changes");
		commitChangesButton.addClickListener(new ClickListener() {

			@Override
			public void onClick(Widget sender) {
				// Collect data
				HashMap<String, String> data = new HashMap<String, String>();

				// If a receiver has been set, push there
				if (receiver != null) {
					receiver.processData(data);
				}
			}

		});

		// Initialize widget container
		widgets = new HashMap<String, Widget>();
	}

	/**
	 * Add widget to display
	 * 
	 * @param name
	 *            Variable name to be associated with this widget.
	 * @param title
	 *            Caption for display to the user.
	 * @param type
	 *            Widget type, textual.
	 * @param options
	 *            Optional, to describe additional options.
	 * @param value
	 *            Default value.
	 */
	public void addWidget(String name, String title, String type,
			String options, String value) {
		Widget w;

		if (type.compareToIgnoreCase("text") == 0) {
			w = new TextBox();
			if (value != null) {
				((TextBox) w).setText(value);
			}
		} else if (type.compareToIgnoreCase("patient") == 0) {
			w = new PatientWidget();
			if (value != null) {
				((PatientWidget) w).setValue(new Integer(value));
			}
		} else {
			// Unimplemented, use text box as fallback
			w = new TextBox();
			if (value != null) {
				((TextBox) w).setText(value);
			}
		}

		// Add to indices and display
		widgets.put(name, w);
		table.setText(widgets.size() - 1, 0, title);
		table.setWidget(widgets.size() - 1, 1, w);
	}

}
