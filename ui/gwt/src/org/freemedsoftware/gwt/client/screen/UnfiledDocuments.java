package org.freemedsoftware.gwt.client.screen;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;
import org.freemedsoftware.gwt.client.widget.PatientWidget;

import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.HorizontalSplitPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;

public class UnfiledDocuments extends Composite {

	protected CurrentState state = null;

	public UnfiledDocuments() {

		final HorizontalSplitPanel horizontalSplitPanel = new HorizontalSplitPanel();
		initWidget(horizontalSplitPanel);
		horizontalSplitPanel.setSize("100%", "100%");
		horizontalSplitPanel.setSplitPosition("50%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalSplitPanel.setLeftWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final CustomSortableTable wDocuments = new CustomSortableTable();
		verticalPanel.add(wDocuments);
		wDocuments.addColumnHeader("Date", 0);
		wDocuments.addColumnHeader("Patient", 1);
		wDocuments.formatTable(0, 2);
		wDocuments.setWidth("100%");

		final FlexTable flexTable = new FlexTable();
		verticalPanel.add(flexTable);
		flexTable.setWidth("100%");

		final Label dateLabel = new Label("Date : ");
		flexTable.setWidget(0, 0, dateLabel);

		final Label patientLabel = new Label("Patient : ");
		flexTable.setWidget(1, 0, patientLabel);

		final Label providerLabel = new Label("Provider : ");
		flexTable.setWidget(2, 0, providerLabel);

		final Label categoryLabel = new Label("Category : ");
		flexTable.setWidget(3, 0, categoryLabel);

		final Label noteLabel = new Label("Note : ");
		flexTable.setWidget(4, 0, noteLabel);

		final Label rotateLabel = new Label("Rotate : ");
		flexTable.setWidget(5, 0, rotateLabel);

		final PatientWidget wPatient = new PatientWidget();
		flexTable.setWidget(1, 1, wPatient);

		final TextBox wNote = new TextBox();
		flexTable.setWidget(4, 1, wNote);
		wNote.setWidth("100%");

		final ListBox wRotate = new ListBox();
		flexTable.setWidget(5, 1, wRotate);
		wRotate.addItem("No rotation");
		wRotate.addItem("Rotate left");
		wRotate.addItem("Rotate right");
		wRotate.addItem("Flip");
		wRotate.setVisibleItemCount(1);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		horizontalPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_BOTTOM);

		final PushButton pushButton = new PushButton("Up text", "Down text");
		horizontalPanel.add(pushButton);

		final DjvuViewer djvuViewer = new DjvuViewer();
		horizontalSplitPanel.setRightWidget(djvuViewer);
		djvuViewer.setVisible(false);
		djvuViewer.setSize("100%", "100%");
	}

	/**
	 * Assign current state object to local object.
	 * 
	 * @param c
	 */
	public void assignState(CurrentState c) {
		state = c;
	}

}
