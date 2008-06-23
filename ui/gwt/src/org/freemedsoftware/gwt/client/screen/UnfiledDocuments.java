package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Module.UnfiledDocumentsAsync;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;
import org.freemedsoftware.gwt.client.widget.PatientWidget;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ClickListener;
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
import com.google.gwt.user.client.ui.Widget;

public class UnfiledDocuments extends ScreenInterface {

	protected CustomSortableTable wDocuments = null;

	protected ListBox wRotate = null;

	protected TextBox wNote = null;

	protected PatientWidget wPatient = null;

	protected Integer currentId = new Integer(0);

	/**
	 * @gwt.typeArgs <java.lang.String,java.lang.String>
	 */
	protected HashMap[] store = null;

	public UnfiledDocuments() {

		final HorizontalSplitPanel horizontalSplitPanel = new HorizontalSplitPanel();
		initWidget(horizontalSplitPanel);
		horizontalSplitPanel.setSize("100%", "100%");
		horizontalSplitPanel.setSplitPosition("50%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		horizontalSplitPanel.setLeftWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		wDocuments = new CustomSortableTable();
		verticalPanel.add(wDocuments);
		wDocuments.addColumnHeader("Date", 0);
		wDocuments.addColumnHeader("Filename", 1);
		wDocuments.formatTable(0, 2);
		if (Util.isStubbedMode()) {

		} else {
			getDocumentsProxy().GetAll(new AsyncCallback() {
				public void onSuccess(Object o) {
					/**
					 * @gwt.typeArgs <java.lang.String,java.lang.String>
					 */
					HashMap[] res = (HashMap[]) o;
					store = res;
					wDocuments.formatTable(res.length, 2);
					for (int iter = 0; iter < res.length; iter++) {
						wDocuments.setText(iter + 1, 0, (String) res[iter]
								.get("uffdate"));
						wDocuments.setText(iter + 1, 1, (String) res[iter]
								.get("ufffilename"));
					}
				}

				public void onFailure(Throwable t) {

				}
			});
		}
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

		wPatient = new PatientWidget();
		flexTable.setWidget(1, 1, wPatient);

		wNote = new TextBox();
		flexTable.setWidget(4, 1, wNote);
		wNote.setWidth("100%");

		wRotate = new ListBox();
		flexTable.setWidget(5, 1, wRotate);
		wRotate.addItem("No rotation", "0");
		wRotate.addItem("Rotate left", "270");
		wRotate.addItem("Rotate right", "90");
		wRotate.addItem("Flip", "180");
		wRotate.setVisibleItemCount(1);

		final HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		horizontalPanel
				.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);
		horizontalPanel.setVerticalAlignment(HasVerticalAlignment.ALIGN_BOTTOM);

		final PushButton sendToProviderButton = new PushButton();
		sendToProviderButton.setHTML("Send to Provider");
		sendToProviderButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				if (validateForm()) {
					sendToProvider();
				}
			}
		});
		horizontalPanel.add(sendToProviderButton);

		final PushButton fileDirectlyButton = new PushButton();
		fileDirectlyButton.setHTML("File Directly");
		fileDirectlyButton.addClickListener(new ClickListener() {
			public void onClick(Widget w) {
				if (validateForm()) {
					fileDirectly();
				}
			}
		});
		horizontalPanel.add(fileDirectlyButton);

		final DjvuViewer djvuViewer = new DjvuViewer();
		horizontalSplitPanel.setRightWidget(djvuViewer);
		djvuViewer.setVisible(false);
		djvuViewer.setSize("100%", "100%");
	}

	protected void fileDirectly() {
		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		HashMap p = new HashMap();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) "");
		p.put((String) "physician", (String) "");
		p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "1");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if (Util.isStubbedMode()) {

		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback() {
						public void onSuccess(Object o) {

						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	protected void sendToProvider() {
		/**
		 * @gwt.typeArgs <java.lang.String,java.lang.String>
		 */
		HashMap p = new HashMap();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) "");
		p.put((String) "physician", (String) "");
		p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "0");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if (Util.isStubbedMode()) {

		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback() {
						public void onSuccess(Object o) {

						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Perform form validation.
	 * 
	 * @return Successful form validation status.
	 */
	protected boolean validateForm() {
		return true;
	}

	protected UnfiledDocumentsAsync getDocumentsProxy() {
		UnfiledDocumentsAsync p = null;
		try {
			p = (UnfiledDocumentsAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Module.UnfiledDocuments");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}

	protected ModuleInterfaceAsync getModuleProxy() {
		ModuleInterfaceAsync p = null;
		try {
			p = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
		return p;
	}
}
