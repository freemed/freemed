package org.freemedsoftware.gwt.client.screen;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.Module.UnfiledDocumentsAsync;
import org.freemedsoftware.gwt.client.widget.CustomSortableTable;
import org.freemedsoftware.gwt.client.widget.DjvuViewer;
import org.freemedsoftware.gwt.client.widget.PatientWidget;
import org.freemedsoftware.gwt.client.widget.SupportModuleWidget;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SourcesTableEvents;
import com.google.gwt.user.client.ui.TableListener;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;
import com.thapar.gwt.user.ui.client.widget.simpledatepicker.SimpleDatePicker;

public class UnfiledDocuments extends ScreenInterface {

	protected CustomSortableTable wDocuments = null;

	protected ListBox wRotate = null;

	protected TextBox wNote = null;

	protected PatientWidget wPatient = null;

	protected SupportModuleWidget wProvider = null, wCategory = null;

	protected SimpleDatePicker wDate = null;

	protected Integer currentId = new Integer(0);

	protected HorizontalPanel horizontalPanel;

	protected FlexTable flexTable;

	protected DjvuViewer djvuViewer;

	protected HashMap<String, String>[] store = null;

	public UnfiledDocuments() {
		final HorizontalPanel mainHorizontalPanel = new HorizontalPanel();
		initWidget(mainHorizontalPanel);
		mainHorizontalPanel.setSize("100%", "100%");

		final VerticalPanel verticalPanel = new VerticalPanel();
		mainHorizontalPanel.add(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		wDocuments = new CustomSortableTable();
		verticalPanel.add(wDocuments);
		wDocuments.setIndexName("id");
		wDocuments.addColumn("Date", "uffdate");
		wDocuments.addColumn("Filename", "ufffilename");
		wDocuments.addTableListener(new TableListener() {
			public void onCellClicked(SourcesTableEvents e, int row, int col) {
				// Import current id
				try {
					currentId = new Integer(wDocuments.getValueByRow(row));
				} catch (Exception ex) {
					GWT.log("Exception", ex);
				} finally {
					// Show the form
					flexTable.setVisible(true);
					horizontalPanel.setVisible(true);

					// Show the image in the viewer
					djvuViewer.setInternalId(currentId);
					djvuViewer.setVisible(true);
				}
			}
		});
		wDocuments.setWidth("100%");

		flexTable = new FlexTable();
		verticalPanel.add(flexTable);
		flexTable.setWidth("100%");
		flexTable.setVisible(false);

		final Label dateLabel = new Label("Date : ");
		flexTable.setWidget(0, 0, dateLabel);

		wDate = new SimpleDatePicker();
		wDate.setCurrentDate(new Date());
		flexTable.setWidget(0, 1, wDate);

		final Label patientLabel = new Label("Patient : ");
		flexTable.setWidget(1, 0, patientLabel);

		wPatient = new PatientWidget();
		flexTable.setWidget(1, 1, wPatient);

		final Label providerLabel = new Label("Provider : ");
		flexTable.setWidget(2, 0, providerLabel);

		wProvider = new SupportModuleWidget();
		wProvider.setModuleName("ProviderModule");
		flexTable.setWidget(2, 1, wProvider);

		wNote = new TextBox();
		flexTable.setWidget(4, 1, wNote);
		wNote.setWidth("100%");

		final Label categoryLabel = new Label("Category : ");
		flexTable.setWidget(5, 0, categoryLabel);

		wCategory = new SupportModuleWidget();
		wCategory.setModuleName("DocumentCategory");
		flexTable.setWidget(5, 1, wCategory);

		final Label rotateLabel = new Label("Rotate : ");
		flexTable.setWidget(6, 0, rotateLabel);

		wRotate = new ListBox();
		flexTable.setWidget(6, 1, wRotate);
		wRotate.addItem("No rotation", "0");
		wRotate.addItem("Rotate left", "270");
		wRotate.addItem("Rotate right", "90");
		wRotate.addItem("Flip", "180");
		wRotate.setVisibleItemCount(1);

		horizontalPanel = new HorizontalPanel();
		horizontalPanel.setVisible(false);
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

		djvuViewer = new DjvuViewer();
		djvuViewer.setType(DjvuViewer.UNFILED_DOCUMENTS);
		mainHorizontalPanel.add(djvuViewer);
		djvuViewer.setVisible(false);
		djvuViewer.setSize("100%", "100%");

		// Last thing is to initialize, otherwise we're going to get some
		// NullPointerException errors
		loadData();
	}

	protected void fileDirectly() {
		HashMap<String, String> p = new HashMap<String, String>();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) wCategory.getValue().toString());
		p.put((String) "physician", (String) wProvider.getValue().toString());
		p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "1");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if (Util.isStubbedMode()) {
			state.getToaster().addItem("UnfiledDocuments",
					"Processed unfiled document.");
			loadData();
		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer o) {
							state.getToaster().addItem("UnfiledDocuments",
									"Processed unfiled document.");
							loadData();
						}

						public void onFailure(Throwable t) {
							state.getToaster().addItem("UnfiledDocuments",
									"Error processing unfiled document.",
									Toaster.TOASTER_ERROR);

							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Load table entries and reset form.
	 */
	@SuppressWarnings("unchecked")
	protected void loadData() {
		djvuViewer.setVisible(false);
		flexTable.setVisible(false);
		horizontalPanel.setVisible(false);
		if (Util.isStubbedMode()) {
			List<HashMap<String, String>> results = new ArrayList<HashMap<String, String>>();
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("id", "1");
				item.put("uffdate", "2008-08-10");
				item.put("ufffilename", "testFile1.pdf");
				results.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("id", "2");
				item.put("uffdate", "2008-08-25");
				item.put("ufffilename", "testFile2.pdf");
				results.add(item);
			}
			wDocuments.loadData(results
					.toArray((HashMap<String, String>[]) new HashMap<?, ?>[0]));
		} else {
			getDocumentsProxy().GetAll(
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] res) {
							store = res;
							wDocuments.loadData(res);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	protected void sendToProvider() {
		HashMap<String, String> p = new HashMap<String, String>();
		p.put((String) "id", (String) currentId.toString());
		p.put((String) "patient", (String) wPatient.getValue().toString());
		p.put((String) "category", (String) "");
		p.put((String) "physician", (String) wProvider.getValue().toString());
		p.put((String) "withoutfirstpage", (String) "");
		p.put((String) "filedirectly", (String) "0");
		p.put((String) "note", (String) wNote.getText());
		p.put((String) "flip", (String) wRotate.getValue(wRotate
				.getSelectedIndex()));
		if (Util.isStubbedMode()) {

		} else {
			getModuleProxy().ModuleModifyMethod("UnfiledDocuments", p,
					new AsyncCallback<Integer>() {
						public void onSuccess(Integer o) {

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
