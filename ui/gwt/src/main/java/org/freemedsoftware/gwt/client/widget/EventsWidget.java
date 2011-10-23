package org.freemedsoftware.gwt.client.widget;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableRowClickHandler;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;
import org.freemedsoftware.gwt.client.widget.SchedulerWidget.SchedulerCss;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class EventsWidget extends DialogBox {

	private Integer eventId = null;
	private VerticalPanel mainVPanel;
	private CustomListBox eventAction;
	private TextArea eventNote;
	private CustomButton submit;
	private CustomButton clear;
	private CustomButton delete;
	private CustomButton cancel;

	private String eventTypeModule = null;
	private Integer eventSourceId = null;

	private String entityName = null;

	protected boolean canRead = true, canWrite = true, canDelete = false,
			canModify = false;

	public static final String moduleName = "Events";

	private CustomTable customTable = null;

	@SuppressWarnings("unused")
	private EventsWidget() {
		super();
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		createUI();
	}

	public EventsWidget(String eventTypeModule, Integer eventSourceId,
			String entityName) {
		super();
		this.entityName = entityName;
		this.eventTypeModule = eventTypeModule;
		this.eventSourceId = eventSourceId;
		init();
	}

	public EventsWidget(String eventTypeModule, Integer eventSourceId) {
		super();
		this.eventTypeModule = eventTypeModule;
		this.eventSourceId = eventSourceId;
		init();
	}

	private void init() {
		this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		String msg = "";
		if (this.eventTypeModule == null
				|| this.eventTypeModule.trim().length() == 0)
			msg = _("Please specify event type.") + "\n";
		if (this.eventSourceId == null || this.eventSourceId == 0)
			msg += _("Please specify the source ID!") + "\n";
		if (msg.length() == 0)
			createUI();
		else {
			Window.alert(msg);
			hide();
		}
	}

	private void createUI() {

		VerticalPanel blockTimeSlotPopupContainer = new VerticalPanel();

		HorizontalPanel closeButtonContainer = new HorizontalPanel();
		blockTimeSlotPopupContainer.add(closeButtonContainer);
		closeButtonContainer.setWidth("100%");

		Image closeImage = new Image("resources/images/close_x.16x16.png");
		closeImage.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				getThisDialog().hide();
			}
		});
		closeButtonContainer.add(closeImage);
		closeButtonContainer.setCellHorizontalAlignment(closeImage,
				HasHorizontalAlignment.ALIGN_RIGHT);
		mainVPanel = new VerticalPanel();
		blockTimeSlotPopupContainer.add(mainVPanel);

		final VerticalPanel entryVPanel = new VerticalPanel();
		final FlexTable flexTable = new FlexTable();
		entryVPanel.add(flexTable);

		int row = 0;

		final Label eventTitleLabel = new Label(_("Action") + ":");
		flexTable.setWidget(row, 0, eventTitleLabel);
		eventAction = new CustomListBox();
		eventAction.addItem(_("NONE SELECTED"), "");
		eventAction.addItem(_("Call"), "call");
		eventAction.addItem(_("Email"), "email");
		flexTable.setWidget(row, 1, eventAction);

		row++;

		final Label providerLabel = new Label(_("Note") + ":");
		flexTable.setWidget(row, 0, providerLabel);
		eventNote = new TextArea();
		flexTable.setWidget(row, 1, eventNote);

		row++;

		final HorizontalPanel buttonPanel = new HorizontalPanel();
		flexTable.setWidget(row, 1, buttonPanel);

		submit = new CustomButton(_("Add"), AppConstants.ICON_ADD);
		submit.addClickHandler(new ClickHandler() {
			@SuppressWarnings({ "rawtypes", "unchecked" })
			@Override
			public void onClick(ClickEvent arg0) {
				if (validateForm()) {
					List params = new ArrayList();
					params.add(populateData());
					String method = "Add";
					if (eventId == null) {
						Util.callModuleMethod(moduleName, method, params,
								new CustomRequestCallback() {
									@Override
									public void onError() {
										Util.showErrorMsg(moduleName,
												_("Failed to add event."));
									}

									@Override
									public void jsonifiedData(Object data) {
										if (data != null) {
											clearForm();
											Util.showInfoMsg(moduleName,
													_("Event added."));
											retrieveEvents();
										} else {
											Util.showErrorMsg(moduleName,
													_("Failed to add event."));
										}
									}
								}, "Integer");
					} else {
						Util.callModuleMethod(moduleName, "Mod", params,
								new CustomRequestCallback() {
									@Override
									public void onError() {
										Util.showErrorMsg(moduleName,
												_("Failed to modify event."));
									}

									@Override
									public void jsonifiedData(Object data) {
										if (data != null
												&& ((Boolean) data)
														.booleanValue()) {
											Util.showInfoMsg(moduleName,
													_("Event modified."));
											clearForm();
											retrieveEvents();
										} else {
											Util.showErrorMsg(
													moduleName,
													_("Failed to modify event."));
										}
									}
								}, "Boolean");
					}

				}
			}
		});
		buttonPanel.add(submit);
		clear = new CustomButton(_("Clear"), AppConstants.ICON_CLEAR);
		clear.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				clearForm();
			}
		});
		buttonPanel.add(clear);
		if (canDelete) {
			delete = new CustomButton(_("Delete"), AppConstants.ICON_DELETE);
			delete.setVisible(false);
			delete.addClickHandler(new ClickHandler() {
				@Override
				public void onClick(ClickEvent arg0) {
					Util.callModuleMethod(moduleName, "Del", eventId,
							new CustomRequestCallback() {
								@Override
								public void onError() {
									Util.showErrorMsg(moduleName,
											_("Failed to delete event."));
								}

								@Override
								public void jsonifiedData(Object data) {
									if (data != null
											&& ((Boolean) data).booleanValue()) {
										Util.showInfoMsg(moduleName,
												_("Event deleted."));
										clearForm();
										retrieveEvents();
									} else
										Util.showErrorMsg(moduleName,
												_("Failed to delete event."));
								}
							}, "Boolean");
				}
			});
			buttonPanel.add(delete);
		}
		cancel = new CustomButton(_("Cancel"), AppConstants.ICON_CLEAR);
		buttonPanel.add(cancel);
		cancel.addClickHandler(new ClickHandler() {
			@Override
			public void onClick(ClickEvent arg0) {
				getThisDialog().hide();
			}
		});
		if (canWrite) {
			mainVPanel.add(entryVPanel);
		}

		final VerticalPanel listVPanel = new VerticalPanel();
		listVPanel.setWidth("100%");
		customTable = new CustomTable();
		customTable.setWidth("100%");
		listVPanel.add(customTable);

		customTable.addColumn(_("Date"), "stamp");
		if (entityName != null)
			customTable.addColumn(_("Name"), "name");
		customTable.addColumn(_("Action"), "event_action");
		customTable.addColumn(_("Entered By"), "user_name");

		if (canModify) {
			customTable.setTableRowClickHandler(new TableRowClickHandler() {
				@Override
				public void handleRowClick(HashMap<String, String> data, int col) {
					eventId = Integer.parseInt(data.get("id"));
					eventAction.setWidgetValue(data.get("event_action"));
					eventNote.setText(data.get("event_note"));
					delete.setVisible(true);
					submit.setText("modify");
				}
			});
		}

		if (entityName != null) {
			customTable
					.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
						@Override
						public Widget setColumn(String columnName,
								HashMap<String, String> data) {
							if (columnName.equalsIgnoreCase("name"))
								return new Label(entityName);
							return null;
						}

					});
		}
		if (canRead)
			mainVPanel.add(listVPanel);

		setWidget(blockTimeSlotPopupContainer);
		retrieveEvents();
	}

	public EventsWidget getThisDialog() {
		return this;
	}

	@SuppressWarnings({ "rawtypes", "unchecked" })
	public void retrieveEvents() {
		List params = new ArrayList();
		params.add(this.eventTypeModule);
		params.add(this.eventSourceId);
		Util.callModuleMethod(moduleName, "GetEvents", params,
				new CustomRequestCallback() {
					@Override
					public void onError() {
					}

					@Override
					public void jsonifiedData(Object data) {
						customTable.loadData((HashMap<String, String>[]) data);
					}
				}, "HashMap<String,String>[]");
	}

	public boolean validateForm() {
		String msg = new String("");

		if (eventAction.getWidgetValue().trim().length() == 0) {
			msg += _("Please specify action!") + "\n";
		}

		if (eventNote.getText().trim().length() == 0) {
			msg += _("Please specify note.");
		}

		if (!msg.equals("")) {
			Window.alert(msg);
			return false;
		}

		return true;
	}

	public void clearForm() {
		eventAction.setWidgetValue("");
		eventNote.setText("");
		if (delete != null)
			delete.setVisible(false);
		submit.setText(_("Add"));
		eventId = null;
	}

	public HashMap<String, String> populateData() {
		HashMap<String, String> data = new HashMap<String, String>();

		if (eventId != null)
			data.put("id", eventId.toString());

		data.put("event_action", eventAction.getWidgetValue());
		data.put("event_note", eventNote.getText());

		data.put("event_type", eventTypeModule);
		data.put("source_id", eventSourceId.toString());

		return data;
	}

	public boolean isCanRead() {
		return canRead;
	}

	public void setCanRead(boolean canRead) {
		this.canRead = canRead;
	}

	public boolean isCarWrite() {
		return canWrite;
	}

	public void setCarWrite(boolean carWrite) {
		this.canWrite = carWrite;
	}

	public boolean isCanDelete() {
		return canDelete;
	}

	public void setCanDelete(boolean canDelete) {
		this.canDelete = canDelete;
	}

	public boolean isCanModify() {
		return canModify;
	}

	public void setCanModify(boolean canModify) {
		this.canModify = canModify;
	}
}
