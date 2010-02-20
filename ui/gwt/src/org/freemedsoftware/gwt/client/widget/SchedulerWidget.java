/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Philipp Meng	<pmeng@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

package org.freemedsoftware.gwt.client.widget;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.cobogw.gwt.user.client.ui.RoundedPanel;
import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.PatientsGroupScreen;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ChangeEvent;
import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.dom.client.KeyDownEvent;
import com.google.gwt.event.dom.client.KeyDownHandler;
import com.google.gwt.event.dom.client.KeyPressEvent;
import com.google.gwt.event.dom.client.KeyPressHandler;
import com.google.gwt.event.logical.shared.ResizeEvent;
import com.google.gwt.event.logical.shared.ResizeHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.i18n.client.DateTimeFormat;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

import eu.future.earth.gwt.client.date.AbstractWholeDayField;
import eu.future.earth.gwt.client.date.BaseDateRenderer;
import eu.future.earth.gwt.client.date.DateEvent;
import eu.future.earth.gwt.client.date.DateEventListener;
import eu.future.earth.gwt.client.date.DatePanel;
import eu.future.earth.gwt.client.date.DateRenderer;
import eu.future.earth.gwt.client.date.EventController;
import eu.future.earth.gwt.client.date.EventPanel;
import eu.future.earth.gwt.client.date.MultiView;
import eu.future.earth.gwt.client.date.DateEvent.DateEventActions;
import eu.future.earth.gwt.client.date.month.staend.AbstractMonthField;
import eu.future.earth.gwt.client.date.picker.DatePickerMonthNavigator;
import eu.future.earth.gwt.client.date.picker.DatePickerRenderer;
import eu.future.earth.gwt.client.date.picker.NoneContraintAndEntryRenderer;
import eu.future.earth.gwt.client.date.week.staend.AbstractDayField;

public class SchedulerWidget extends WidgetInterface implements
		DateEventListener, ResizeHandler, ClickHandler {

	public class SchedulerCss {

		public final static String PREFIX = "freemed-Scheduler-";

		public final static String EVENT_HEADER_MONDAY = PREFIX
				+ "eventHeaderMonday";

		public final static String EVENT_PANEL_MONDAY = PREFIX
				+ "eventBodyMonday";

		public final static String WHOLEDAY_PANEL_MONDAY = PREFIX
				+ "eventWholeDayMonday";

		public final static String EVENT_HEADER_NORMAL = PREFIX
				+ "eventHeaderNormal";

		public final static String EVENT_DIALOG = PREFIX + "eventDialog";

		public final static String EVENT_PANEL_NORMAL = PREFIX
				+ "eventBodyNormal";

		public final static String WHOLEDAY_PANEL_NORMAL = PREFIX
				+ "eventWholeDayNormal";
	}

	public class EventData implements Serializable {

		private static final long serialVersionUID = -6586593847569185408L;

		private Date startTime = null;

		private Date endTime = null;

		private String data = null;

		private String description = null;

		private String id = null;

		private Integer patientId = null;
		
		private Integer groupId = null;

		private Integer providerId = null;

		private String patientName = null;

		private String providerName = null;

		private Integer facilityId = null;

		private Integer roomId = null;

		private String eventBackgroundColor = null;

		private Integer appointmentTemplateId;

		private String resourceType = AppConstants.APPOINTMENT_TYPE_PATIENT;

		public Integer getAppointmentTemplateId() {
			return appointmentTemplateId;
		}

		public void setAppointmentTemplateId(Integer appointmentTemplateId) {
			this.appointmentTemplateId = appointmentTemplateId;
		}

		public EventData() {
			super();
			id = String.valueOf(System.currentTimeMillis());
		}

		public EventData(String currentId) {
			super();
			if (currentId != null) {
				id = currentId;
			} else {
				id = String.valueOf(System.currentTimeMillis());
			}
		}

		public boolean isAlldayEvent() {
			return endTime == null;
		}

		public void setAsAllDayEvent() {
			endTime = null;
		}

		public String getData() {
			return data;
		}

		public String getDescription() {
			return description;
		}

		public Date getEndTime() {
			return endTime;
		}

		public Date getStartTime() {
			return startTime;
		}

		public Integer getPatientId() {
			return patientId;
		}

		public Integer getProviderId() {
			return providerId;
		}

		public Integer getRoomId() {
			return roomId;
		}

		public Integer getFacilityId() {
			return facilityId;
		}

		public String getPatientName() {
			return patientName;
		}

		public String getProviderName() {
			return providerName;
		}

		public void setData(String data) {
			this.data = data;
		}

		public void setDescription(String d) {
			this.description = d;
		}

		public void setEndTime(Date endTime) {
			this.endTime = endTime;
		}

		public void setStartTime(Date startTime) {
			this.startTime = startTime;
		}

		public void setPatientId(Integer patientId) {
			this.patientId = patientId;
		}

		public void setProviderId(Integer providerId) {
			this.providerId = providerId;
		}

		public void setFacilityId(Integer facilityId) {
			this.facilityId = facilityId;
		}

		public void setRoomId(Integer roomId) {
			this.roomId = roomId;
		}

		public void setPatientName(String patientName) {
			this.patientName = patientName;
		}

		public void setProviderName(String providerName) {
			this.providerName = providerName;
		}

		/**
		 * Returns a string representation of the object.
		 * 
		 * @return a string representation of the object.
		 * @todo Implement this java.lang.Object method
		 */
		public String toString() {
			return getStartTime() + "-" + getEndTime() + " data = " + getData();
		}

		/**
		 * This identifier identifies the event in the calendar. All updates and
		 * such rely on an unique id to handle updates correctly. In a
		 * production like situation we would recommend using the key of the
		 * record.
		 * 
		 * @return String - And time based identifier
		 */
		public String getIdentifier() {
			return id;
		}

		public void setIdentifier(Integer i) {
			id = Integer.toString(i);
		}

		public String getEventBackgroundColor() {
			return eventBackgroundColor;
		}

		public void setEventBackgroundColor(String eventBackgroundColor) {
			this.eventBackgroundColor = eventBackgroundColor;
		}

		public String getResourceType() {
			return resourceType;
		}

		public void setResourceType(String resourceType) {
			this.resourceType = resourceType;
		}

		public Integer getGroupId() {
			return groupId;
		}

		public void setGroupId(Integer groupId) {
			this.groupId = groupId;
		}

	}

	public class StringPanelRenderer extends BaseDateRenderer implements
			DatePickerRenderer {

		private int startHour = 6;

		private int endHour = 24;

		private int intervalsPerHour = 4;

		public StringPanelRenderer() {
			super();
		}

		public StringPanelRenderer(int startHour, int endHour,
				int intervalPerHour) {
			super();
			this.startHour = startHour;
			this.endHour = endHour;
			this.intervalsPerHour = intervalPerHour;
		}

		public void createNewAfterClick(Date currentDate,
				DateEventListener listener) {
			if(!CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.SYSTEM_CATEGORY, AppConstants.SCHEDULER)){
				CurrentState.getToaster().addItem(
						"Scheduler",
						"Access Denied!\nCan not book appointments.",Toaster.TOASTER_ERROR);
				return;
			}
			if (!CurrentState.canBookAppoinment(currentDate, currentDate)) {
				CurrentState.getToaster().addItem(
						"Scheduler",
						"Can not book appointment in between("
								+ CurrentState.BREAK_HOUR + ":00 -"
								+ (CurrentState.BREAK_HOUR + 1) + ":00) !",
						Toaster.TOASTER_ERROR);
				return;
			}

			final EventData data = new EventData();
			data.setStartTime(currentDate);
			Calendar c = new GregorianCalendar();
			c.setTime(currentDate);
			c.add(Calendar.MINUTE, 60 / getIntervalsPerHour());
			data.setEndTime(c.getTime());
			final StringEventDataDialog dialog = new StringEventDataDialog(
					this, listener, data);
			dialog.show();
			dialog.center();
		}

		public void editAfterClick(Object data, DateEventListener listener) {
			final StringEventDataDialog dialog = new StringEventDataDialog(
					this, listener, data, DateEventActions.UPDATE);
			dialog.show();
			dialog.center();
		}

		public void createNewAfterClick(Date currentDate, Date endDate,
				DateEventListener listener) {
			
			if(!CurrentState.isActionAllowed(AppConstants.WRITE, AppConstants.SYSTEM_CATEGORY, AppConstants.SCHEDULER)){
				CurrentState.getToaster().addItem(
						"Scheduler",
						"Access Denied!\nCan not book appointments.",Toaster.TOASTER_ERROR);
				return;
			}
			
			if (!CurrentState.canBookAppoinment(currentDate, endDate)) {
				CurrentState.getToaster().addItem(
						"Scheduler",
						"Can not book appointment in between("
								+ CurrentState.BREAK_HOUR + ":00 -"
								+ (CurrentState.BREAK_HOUR + 1) + ":00) !",
						Toaster.TOASTER_ERROR);
				return;
			}
			final EventData data = new EventData();
			data.setStartTime(currentDate);
			data.setEndTime(endDate);
			final StringEventDataDialog dialog = new StringEventDataDialog(
					this, listener, data);
			dialog.show();
			dialog.center();

		}

		public Widget createPickerPanel(Object newData, int day) {
			return null;
		}

		public boolean supportDayView() {
			return true;
		}

		public boolean supportMonthView() {
			return true;
		}

		public boolean showWholeDayEventView() {
			return false;
		}

		public boolean supportWeekView() {
			return true;
		}

		public boolean enableDragAndDrop() {
			return true;
		}

		public int getEndHour() {
			return this.endHour;
		}

		public int getStartHour() {
			return this.startHour;
		}

		public int showDaysInWeek() {
			return 7;
		}

		public Date getEndTime(Object event) {
			final EventData data = getData(event);
			return data.getEndTime();
		}

		private EventData getData(Object event) {
			if (event instanceof EventData) {
				return (EventData) event;
			} else {
				Window.alert("Not the Right type " + event);
				return null;
			}
		}

		public String getIdentifier(Object event) {
			final EventData data = getData(event);
			return data.getIdentifier();
		}

		public Date getStartTime(Object event) {
			final EventData data = getData(event);
			return data.getStartTime();
		}

		public void setEndTime(Object event, Date newEnd) {
			final EventData data = getData(event);
			data.setEndTime(newEnd);
		}

		public void setStartTime(Object event, Date newStart) {
			final EventData data = getData(event);
			data.setStartTime(newStart);
		}

		public boolean isWholeDayEvent(Object event) {
			final EventData data = getData(event);
			if (data != null) {
				return data.isAlldayEvent();
			} else {
				Window.alert("Programming Error " + event);
				return true;
			}
		}

		public EventPanel createPanel(Object newData, int viewType) {
			final EventData data = getData(newData);
			if (data.isAlldayEvent()) {
				WholeDayField panel = new WholeDayField(this);
				panel.setData(newData);
				return panel;
			} else {

				switch (viewType) {
				case DatePanel.MONTH: {
					final MonthField panel = new MonthField(this);
					panel.setData(newData);
					return panel;

				}
				case DatePanel.WEEK: {
					final DayField panel = new DayField(this);
					panel.setData(newData);
					return panel;
				}
				case DatePanel.DAY: {
					final DayField panel = new DayField(this);
					panel.setData(newData);
					return panel;
				}
				default: {
					final DayField panel = new DayField(this);
					panel.setData(newData);
					return panel;
				}
				}
			}
		}

		public boolean useShowMore() {
			return true;
		}

		public int getEventBottomHeight() {
			return 2;
		}

		public int getEventCornerSize() {
			return 1;
		}

		public int getEventMinimumHeight() {
			return 50;
		}

		public int getEventTopHeight() {
			return 18;
		}

		public int getIntervalHeight() {
			return 50;
		}

		public int getIntervalsPerHour() {
			return this.intervalsPerHour;
		}

		public int getScrollHour() {
			return 7;
		}

		public boolean isDurationAcceptable(int minutes) {
			return minutes >= (60 / getIntervalsPerHour());
		}

		public boolean show24HourClock() {
			return true;
		}

		public boolean showIntervalTimes() {
			return false;
		}

		public boolean isEnabled(Date event) {
			return true;
		}
	}

	public class StringEventDataDialog extends DialogBox implements
			ClickHandler, ChangeHandler, ValueChangeHandler<Integer> {

		private PatientWidget patient = null;

		private SupportModuleWidget supportWidget = null;

		private SupportModuleWidget provider = null;

		private TextArea text = new TextArea();

		// private DateEditFieldWithPicker date;
		private CustomDatePicker date;

		private CheckBox wholeDay = new CheckBox();

		private HorizontalPanel time = new HorizontalPanel();

		private HorizontalPanel timePanel = new HorizontalPanel();

		private CustomTimeBox start;

		private CustomTimeBox end;

		private DateEventListener listener = null;

		private Button cancel = null;

		private Button ok = null;

		private Button delete = null;

		private CustomListBox appointmentType = null;
		
		private EventData data = null;

		private SupportModuleListBox selectTemplate = null;

		private DateEventActions command = DateEventActions.ADD;

		public StringEventDataDialog(DateRenderer renderer,
				DateEventListener newListener, Object newData) {
			this(renderer, newListener, newData, DateEventActions.ADD);
		}

		/**
		 * 
		 * 
		 * @param renderer
		 * @param newListener
		 * @param newData
		 * @param newCommand
		 */

		public StringEventDataDialog(DateRenderer renderer,
				DateEventListener newListener, Object newData,
				DateEventActions newCommand) {
			super();

			this.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);

			boolean reverseTime = false;
			
			// date = new DateEditFieldWithPicker("MM/dd/yyyy");
			date = new CustomDatePicker();
			start = new CustomTimeBox(renderer.show24HourClock() ? "HH:mm"
					: "hh:mmaa");
			end = new CustomTimeBox(renderer.show24HourClock() ? "HH:mm" : "hh:mmaa");
			command = newCommand;
			data = (EventData) newData;
			listener = newListener;

			// If drag is backwards, hack to reverse times shown in display.
			if (data.getStartTime().getTime() > data.getEndTime().getTime()) {
				reverseTime = true;
			}

			date.setValue(!reverseTime ? data.getStartTime() : data
					.getEndTime());
			start.setDate(!reverseTime ? data.getStartTime() : data
					.getEndTime());

			if (data.getEndTime() != null) {
				end.setDate(!reverseTime ? data.getEndTime() : data
						.getStartTime());
				wholeDay.setValue(false);
			} else {
				wholeDay.setValue(true);
			}
			if (newCommand == DateEventActions.ADD) {
				setText("New Appointment");
			} else {
				text.setText((String) data.getDescription());
				setText("Edit Appointment");
			}

			// VerticalPanel outer = new VerticalPanel();

			final FlexTable table = new FlexTable();

			int row = 0;
			
			table.setWidget(row, 0, new Label("Date"));
			table.setWidget(row, 1, date);

			timePanel.add(start);
			timePanel.add(new Label("-"));
			timePanel.add(end);

			time.add(wholeDay);
			wholeDay.addClickHandler(this);
			if (data.getEndTime() != null) {
				time.add(timePanel);
			}

			table.setWidget(row, 2, time);
			table.getFlexCellFormatter().setHorizontalAlignment(0, 2,
					HorizontalPanel.ALIGN_LEFT);

			row++;
			
			if(command == DateEventActions.ADD){ // if not in edit mode
			
				appointmentType = new CustomListBox();
				appointmentType.setWidth("100%");
				appointmentType.addItem("Patient",AppConstants.APPOINTMENT_TYPE_PATIENT);
				appointmentType.addItem("Call-In Patient",AppConstants.APPOINTMENT_TYPE_CALLIN_PATIENT);
				appointmentType.addItem("Group",AppConstants.APPOINTMENT_TYPE_GROUP);
				
				table.setWidget(row, 0, new Label("Type"));
				table.setWidget(row, 1, appointmentType);
			
			}
			
			row++;
			
			final Label entityLabel = new Label("Patient");
			
			if (command == DateEventActions.UPDATE) {
				if (data.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_PATIENT)) {
					patient = new PatientWidget();
					table.setWidget(row, 0, entityLabel);
					table.setWidget(row, 1, patient);
				} else if (data.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_CALLIN_PATIENT)) {
					supportWidget = new SupportModuleWidget("Callin");
					entityLabel.setText("Call-In Patient");
					table.setWidget(row, 0, entityLabel);
					table.setWidget(row, 1, supportWidget);
				} else if (data.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_GROUP)) {
					supportWidget = new SupportModuleWidget("CalendarGroup");
					entityLabel.setText("Group");
					table.setWidget(row, 0, new Label("Group"));
					table.setWidget(row, 1, supportWidget);
				}
			} else {
				patient = new PatientWidget();
				table.setWidget(row, 0, entityLabel);
				table.setWidget(row, 1, patient);
			}
			if(patient!=null){
				if(data.getPatientId()!=null)
					patient.setValue(data.getPatientId());
				patient.addChangeHandler(this);
			}else {
				supportWidget.setValue(data.getPatientId());
				supportWidget.addChangeHandler(this);
			}
			
			final int entityRow = row;
			
			if(command == DateEventActions.ADD){
				appointmentType.addChangeHandler(new ChangeHandler() {
					@Override
					public void onChange(ChangeEvent arg0) {
						int index = appointmentType.getSelectedIndex();
						data.setResourceType(appointmentType.getValue(index));
						if(appointmentType.getItemText(index).equalsIgnoreCase("Patient")){
							supportWidget = null;
							patient = new PatientWidget();
							entityLabel.setText("Patient");
							table.setWidget(entityRow, 1, patient);
						}else if(appointmentType.getItemText(index).equalsIgnoreCase("Group")){
							patient = null;
							supportWidget = new SupportModuleWidget("CalendarGroup");
							entityLabel.setText("Group");
							table.setWidget(entityRow, 1, supportWidget);
						}else if(appointmentType.getItemText(index).equalsIgnoreCase("Call-In Patient")){
							patient = null;
							supportWidget = new SupportModuleWidget("Callin");
							entityLabel.setText("Call-In Patient");
							table.setWidget(entityRow, 1, supportWidget);
						}
					}
				});
			}
			
			provider = new SupportModuleWidget();
			provider.setModuleName("ProviderModule");
			try {
				provider.setValue(data.getProviderId());
			} catch (Exception ex) {
				JsonUtil.debug(ex.toString());
			}
			provider.addChangeHandler(this);
			text.addChangeHandler(this);
			text.addKeyDownHandler(new KeyDownHandler() {
				@Override
				public void onKeyDown(KeyDownEvent event) {
					if (event.getSource() == text) {
						toggleButton();
					}
				}
			});
			text.addKeyPressHandler(new KeyPressHandler() {
				@Override
				public void onKeyPress(KeyPressEvent event) {
					toggleButton();
				}
			});
			
			row++;
			
			table.setWidget(row, 0, new Label("Provider"));
			// Only set default provider *if* there is one, and if the
			// current event data hasn't already set it.
			if (CurrentState.getDefaultProvider().intValue() > 0
					&& (data.getProviderId() == null || data.getProviderId() == 0)) {
				provider.setValue(CurrentState.getDefaultProvider());
			}
			table.setWidget(row, 1, provider);

			row++;
			
			table.setWidget(row, 0, new Label("Description"));
			table.setWidget(row, 1, text);
			table.getFlexCellFormatter().setColSpan(row, 1, 2);

			row++;
			
			final Label templateLabel = new Label("Template");
			table.setWidget(row, 0, templateLabel);
			selectTemplate = new SupportModuleListBox("AppointmentTemplates",
					"Select a Template");
			table.setWidget(row, 1, selectTemplate);

			selectTemplate.initChangeListener(new Command() {
				public void execute() {
					updateFromTemplate(Integer.parseInt(selectTemplate
							.getStoredValue()));
				}
			});
			try {
				selectTemplate.setWidgetValue(data.getAppointmentTemplateId()
						.toString());
			} catch (Exception ex) {
				JsonUtil.debug(ex.toString());
			}
			
			cancel = new Button("Cancel");
			cancel.setFocus(true);
			cancel.setAccessKey('c');
			cancel.addClickHandler(this);

			ok = new Button("Ok");
			ok.setEnabled(false);
			ok.setFocus(true);
			ok.setAccessKey('o');
			ok.addClickHandler(this);

			final HorizontalPanel button = new HorizontalPanel();
			button.add(ok);

			if(CurrentState.isActionAllowed(AppConstants.DELETE,
					AppConstants.SYSTEM_CATEGORY,
					AppConstants.SCHEDULER)){
				if (command == DateEventActions.UPDATE) {
					delete = new Button("Delete");
					delete.setFocus(true);
					delete.setAccessKey('d');
					delete.addClickHandler(this);
					button.add(new HTML(" "));
					button.add(delete);
				}
			}

			button.add(new HTML(" "));
			button.add(cancel);
			row++;
			table.setWidget(row, 1, button);
			setWidget(table);
			toggleButton();
		}

		public void onChange(ChangeEvent evt) {
			Widget sender = (Widget) evt.getSource();
			if (sender == text || sender == patient || sender == provider) {
				toggleButton();
			}
		}

		public void onClick(ClickEvent evt) {
			Widget sender = (Widget) evt.getSource();
			if (sender == wholeDay) {
				if (wholeDay.getValue()) {
					if (time.getWidgetIndex(timePanel) > -1) {
						time.remove(timePanel);
					}
				} else {
					if (time.getWidgetIndex(timePanel) == -1) {
						time.add(timePanel);
					}
				}
			} else {
				if (sender == ok) {

					if (!CurrentState.canBookAppoinment(start.getValue(date
							.getValue()), end.getValue(date.getValue()))) {
						CurrentState.getToaster().addItem(
								"Scheduler",
								"Can not book appointment in between("
										+ CurrentState.BREAK_HOUR + ":00 -"
										+ (CurrentState.BREAK_HOUR + 1)
										+ ":00) !", Toaster.TOASTER_ERROR);
						return;
					}

					if (data == null) {
						data = new EventData();
					}
					if (wholeDay.getValue()) {
						data.setStartTime(date.getValue());
					} else {
						data.setStartTime(start.getValue(date.getValue()));
						data.setEndTime(end.getValue(date.getValue()));
					}
					data.setDescription(text.getText());
					data.setProviderName(provider.getText());
					data.setProviderId(provider.getValue());
					if (patient != null) {
						data.setPatientName(patient.getText());
						JsonUtil.debug("patient name = " + patient.getText());
						data.setPatientId(patient.getValue());
					} else {
						data.setPatientName(supportWidget.getText());
						JsonUtil.debug("resource name = "
								+ supportWidget.getText());
						data.setPatientId(supportWidget.getValue());
					}

					if (Util.isNumber(selectTemplate.getWidgetValue()))
						data.setAppointmentTemplateId(Integer
								.parseInt(selectTemplate.getWidgetValue()));

					data.setData(((data.getPatientId() != null && data
							.getPatientId() > 0) ? data.getPatientName() + ": "
							: "")
							+ data.getDescription());
					final DateEvent newEvent = new DateEvent(this, data);

					newEvent.setCommand(command);
					listener.handleDateEvent(newEvent);
					hide();
				} else {
					if (sender == cancel) {
						hide();
					} else {
						if (data != null && sender != null && sender == delete) {
							final DateEvent newEvent = new DateEvent(this, data);
							newEvent.setCommand(DateEventActions.REMOVE);
							listener.handleDateEvent(newEvent);
							hide();
						} else {
							hide();
						}
					}

				}
			}
		}

		protected void toggleButton() {
			if (text.getText().length() > 1
					&& (patient != null && patient.getValue() > 0 || supportWidget != null
							&& supportWidget.getValue() > 0)
					&& provider.getValue() > 0) {

				ok.setEnabled(true);
			} else {
				ok.setEnabled(false);
			}
		}

		/**
		 * 
		 * @param i
		 *            The Index value of the Appointment-Template
		 */
		public void updateFromTemplate(Integer i) {
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO: STUBBED
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				// JSON-RPC
				String[] params = { JsonUtil.jsonify(i) };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.module.AppointmentTemplates.GetRecord",
												params)));
				try {
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							JsonUtil
									.debug("Error on retrieving AppointmentTemplate");
						}

						@SuppressWarnings("unchecked")
						public void onResponseReceived(Request request,
								Response response) {
							if (200 == response.getStatusCode()) {
								if (response.getText().compareToIgnoreCase(
										"false") != 0) {
									HashMap<String, String> result = (HashMap<String, String>) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>");
									if (result != null) {
										Integer duration = Integer
												.parseInt(result
														.get("atduration"));
										Date date_start = start
												.getValue(new Date());
										Calendar c = new GregorianCalendar();
										c.setTime(date_start);
										c.add(Calendar.HOUR_OF_DAY, (int) Math
												.ceil(duration / 60));
										c.add(Calendar.MINUTE, (duration % 60));
										end.setDate(c.getTime());

									}
								} else {
									JsonUtil
											.debug("Received dummy response from JSON backend");
								}
							} else {
								CurrentState.getToaster().addItem("Scheduler",
										"Failed to get scheduler items.",
										Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					CurrentState.getToaster().addItem("Scheduler",
							"Failed to get scheduler items.",
							Toaster.TOASTER_ERROR);
				}
			} else {
				// GWT-RPC
			}
		}

		@Override
		public void onValueChange(ValueChangeEvent<Integer> event) {
			// TODO Auto-generated method stub

		}

	}

	public class WholeDayField extends AbstractWholeDayField {

		public WholeDayField(DateRenderer renderer) {
			super(renderer);

		}

		GregorianCalendar helper = new GregorianCalendar();

		public void repaintPanel() {
			final Object theData = getData();
			if (theData != null) {
				if (theData instanceof EventData) {
					final EventData real = (EventData) theData;
					helper.setTime(real.getStartTime());
					if (helper.get(Calendar.DAY_OF_WEEK) == Calendar.MONDAY) {
						super
								.setEventStyleName(SchedulerCss.EVENT_HEADER_MONDAY);
					}
					setTitle(real.getData());
				} else {
					Window.alert("Programming error " + theData);
				}
			}

		}
	}

	public class DayField extends AbstractDayField {

		private Label description = new Label();

		private DateTimeFormat format = DateTimeFormat.getFormat("HH:mm");

		public DayField(DateRenderer renderer) {
			super(renderer);
			super.setBody(description);
		}

		public void setTitle() {
			final Object theData = super.getData();
			final EventData real = (EventData) theData;
			if (real.getEndTime() == null) {
				super.setTitle(format.format(real.getStartTime()));
			} else {
				super.setTitle(format.format(real.getStartTime()) + "-"
						+ format.format(real.getEndTime()));
			}

		}

		public Label getHeaderElement() {
			return (Label)((HorizontalPanel)((RoundedPanel) ((VerticalPanel) super.getWidget())
					.getWidget(0)).getWidget()).getWidget(0);
		}

		public Widget getClickableItem() {
			return description;
		}

		GregorianCalendar helper = new GregorianCalendar();

		@Override
		public void repaintPanel() {
			final Object theData = super.getData();
			if (theData != null) {
				final EventData real = (EventData) theData;
				helper.setTime(real.getStartTime());
				/*
				 * if (helper.get(Calendar.DAY_OF_WEEK) == Calendar.MONDAY) {
				 * super.setEventStyleName(SchedulerCss.EVENT_PANEL_MONDAY,
				 * SchedulerCss.EVENT_HEADER_MONDAY); }
				 */
				description.setText((String) real.getData());
				if (real.getEventBackgroundColor() != null
						&& real.getEventBackgroundColor().length() > 0)
					description.getElement().getStyle().setProperty(
							"backgroundColor", real.getEventBackgroundColor());
				if (real.getResourceType() != null){
						if(real.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_PATIENT)){
							description.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {
									spawnPatientScreen(real.getPatientId(), real
											.getPatientName());
								}
							});
						}
						if(real.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_GROUP)){
							description.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {
									spawnGroupScreen(real.getPatientId());//group id stored in it
								}
							});
						}
				}
				if (CurrentState.isActionAllowed(AppConstants.MODIFY,
						AppConstants.SYSTEM_CATEGORY,
						AppConstants.SCHEDULER)){
						getHeaderElement().addClickHandler(new ClickHandler() {
							@Override
							public void onClick(ClickEvent arg0) {
								final StringEventDataDialog dialog = new StringEventDataDialog(
										getDateRenderer(), getDateEventListener(),
										real, DateEventActions.UPDATE);
								dialog.show();
								dialog.center();
						}
					});
				}else CurrentState.getToaster().addItem(
						"Scheduler",
						"Access Denied!\nCan not edit appointments.",Toaster.TOASTER_ERROR);
				if (real.getEndTime() == null) {
					super.setTitle(format.format(real.getStartTime()) + "  "
							+ real.getProviderName());
				} else {
					super.setTitle(format.format(real.getStartTime()) + "-"
							+ format.format(real.getEndTime()) + "  "
							+ real.getProviderName());
				}
			}
		}
	}

	public class MonthField extends AbstractMonthField {

		private Label description = new Label(); // NOPMD;

		private DateTimeFormat format = DateTimeFormat.getFormat("HH:mm"); // NOPMD;

		public MonthField(DateRenderer renderer) {
			super(renderer);
			super.setBody(description);
		}

		public Label getHeaderElement() {
			return (Label) ((HorizontalPanel) super.getPanel()).getWidget(0);
		}

		public Widget getClickableItem() {
			return description;
		}

		GregorianCalendar helper = new GregorianCalendar();

		public void repaintPanel() {
			final Object theData = getData();
			if (theData != null) {
				if (theData instanceof EventData) {
					final EventData real = (EventData) theData;
					helper.setTime(real.getStartTime());
					/*
					 * if (helper.get(Calendar.DAY_OF_WEEK) == Calendar.MONDAY) {
					 * super
					 * .setEventStyleName(SchedulerCss.WHOLEDAY_PANEL_MONDAY); }
					 */
					description.setText(real.getData());
					if (real.getEventBackgroundColor() != null
							&& real.getEventBackgroundColor().length() > 0)
						description.getElement().getStyle().setProperty(
								"backgroundColor",
								real.getEventBackgroundColor());
					if (real.getResourceType() != null){
						if(real.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_PATIENT)){
							description.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {
									spawnPatientScreen(real.getPatientId(), real
											.getPatientName());
								}
							});
						}
						if(real.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_GROUP)){
							description.addClickHandler(new ClickHandler() {
								@Override
								public void onClick(ClickEvent arg0) {
									spawnGroupScreen(real.getPatientId());//group id stored in it
								}
							});
						}
					}
					// super.setTitle(format.format(real.getStartTime())+"<br>
					// "+real.getProviderName());
					if (CurrentState.isActionAllowed(AppConstants.MODIFY,
							AppConstants.SYSTEM_CATEGORY,
							AppConstants.SCHEDULER)){
						getHeaderElement().addClickHandler(new ClickHandler() {
							@Override
							public void onClick(ClickEvent arg0) {
								final StringEventDataDialog dialog = new StringEventDataDialog(
										getDateRenderer(), getDateEventListener(),
										real, DateEventActions.UPDATE);
								dialog.show();
								dialog.center();
							}
						});
						
					}else CurrentState.getToaster().addItem(
							"Scheduler",
							"Access Denied!\nCan not edit appointments.",Toaster.TOASTER_ERROR);

					if (real.getEndTime() == null) {
						super.setTitle(format.format(real.getStartTime())
								+ "\n" + real.getProviderName());
					} else {
						super.setTitle(format.format(real.getStartTime()) + "-"
								+ format.format(real.getEndTime()) + "\n"
								+ real.getProviderName());
					}
				} else {
					Window.alert("Programming error " + theData);
				}
			}
		}
	}

	/**
	 * Create new tab for patient.
	 * 
	 * @param patient
	 */
	public void spawnPatientScreen(Integer patient, String patientName) {
		PatientScreen s = new PatientScreen();
		s.setPatient(patient);
		Util.spawnTab(patientName, s);
	}
	
	/**
	 * spawn tab for Group.
	 * 
	 * @param patient
	 */
	public void spawnGroupScreen(Integer groupId) {
		Util.spawnTab(AppConstants.GROUPS,
				PatientsGroupScreen.getInstance());
		PatientsGroupScreen.getInstance().showGroupInfo(groupId);
	}
	
	

	public class EventCacheController implements EventController {

		private HashMap<String, EventData> items = new HashMap<String, EventData>();

		private HashMap<String, String> rpcparams = new HashMap<String, String>();

		private String[] params = {};

		public EventCacheController() {
			super();
		}

		public EventData shoehornEventData(HashMap<String, String> o) {
			EventData data = new EventData(o.get("scheduler_id"));

			// Set date / time information
			Calendar cal = importSqlDateTime(o.get("date_of"), o.get("hour"), o
					.get("minute"));
			data.setStartTime(new Date(cal.getTime().getTime()));
			cal.add(Calendar.MINUTE, Integer.parseInt(o.get("duration")));
			data.setEndTime(new Date(cal.getTime().getTime()));

			// Set patient and other appointment information
			try {
				data.setPatientId(Integer.parseInt(o.get("patient_id")));
			} catch (NumberFormatException ex) {
				data.setPatientId(0);
			}
			try {
				data.setProviderId(Integer.parseInt(o.get("provider_id")));
			} catch (NumberFormatException ex) {
				data.setProviderId(0);
			}
			data.setPatientName(o.get("patient"));
			data.setProviderName(o.get("provider"));
			data.setDescription(o.get("note"));

			data.setEventBackgroundColor(o.get("templateColor"));

			data.setResourceType(o.get("resource_type"));

			String appointmentTemplateId = o.get("appointmentTemplateId");
			if (appointmentTemplateId == null
					|| appointmentTemplateId.length() == 0)
				data.setAppointmentTemplateId(0);
			else
				data.setAppointmentTemplateId(Integer
						.parseInt(appointmentTemplateId));
			// Set event label
			data
					.setData(((o.get("patient") != null && o.get("patient") != "") ? o
							.get("patient")
							+ ": "
							: "")
							+ o.get("note"));

			return data;
		}

		/**
		 * Shoehorn string representations of date and time into a
		 * java.util.Date object.
		 * 
		 * @param date
		 *            SQL format date (YYYY-MM-DD)
		 * @param hour
		 *            Hour (24 hour format)
		 * @param minute
		 *            Minute
		 * @return
		 */
		public Calendar importSqlDateTime(String date, String hour,
				String minute) {
			try {
				Calendar calendar = new GregorianCalendar();
				calendar.set(Calendar.YEAR, Integer.parseInt(date.substring(0,
						4)));
				calendar.set(Calendar.MONTH, Integer.parseInt(date.substring(5,
						7)) - 1);
				calendar.set(Calendar.DATE, Integer.parseInt(date.substring(8,
						10)));

				calendar.set(Calendar.HOUR_OF_DAY, Integer.parseInt(hour));
				calendar.set(Calendar.MINUTE, Integer.parseInt(minute));

				calendar.set(Calendar.SECOND, 0);
				calendar.set(Calendar.MILLISECOND, 0);
				return calendar;
			} catch (Exception ex) {
				JsonUtil.debug("importSqlDateTime(): " + ex.toString());
			}

			// By default, return new calendar object
			return new GregorianCalendar();
		}

		@SuppressWarnings("unchecked")
		public void getEventsForRange(Date start, Date end,
				final MultiView caller, final boolean doRefresh) {
			if (provider.getValue() != 0)
				getEventsForRange(start, end, provider.getValue(), caller,
						doRefresh);
			else
				getEventsForRange(start, end,
						CurrentState.getDefaultProvider(), caller, doRefresh);
		}

		public void getEventsForRange(Date start, Date end, Integer provider,
				final MultiView caller, final boolean doRefresh) {
			JsonUtil.debug("getEventsForRange()");
			if (Util.getProgramMode() == ProgramMode.STUBBED) {
				// TODO: STUBBED
			} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
				// JSON-RPC
				String[] params = { dateToSql(start), dateToSql(end),
						provider.toString() };
				RequestBuilder builder = new RequestBuilder(
						RequestBuilder.POST,
						URL
								.encode(Util
										.getJsonRequest(
												"org.freemedsoftware.api.Scheduler.GetDailyAppointmentsRange",
												params)));
				try {
					loadingDialog.center();
					builder.sendRequest(null, new RequestCallback() {
						public void onError(Request request, Throwable ex) {
							loadingDialog.hide();
							CurrentState.getToaster().addItem("Scheduler",
									"Failed to get scheduler items.",
									Toaster.TOASTER_ERROR);
						}

						public void onResponseReceived(Request request,
								Response response) {
							loadingDialog.hide();
							if (200 == response.getStatusCode()) {
								if (response.getText().compareToIgnoreCase(
										"false") != 0) {
									HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
											.shoehornJson(JSONParser
													.parse(response.getText()),
													"HashMap<String,String>[]");
									if (r != null) {
										if (r.length > 0) {
											JsonUtil.debug("found " + r.length
													+ " events");
											List<EventData> e = new ArrayList<EventData>();
											Iterator<HashMap<String, String>> iter = Arrays
													.asList(r).iterator();
											while (iter.hasNext()) {
												EventData d = shoehornEventData(iter
														.next());
												e.add(d);
											}
											JsonUtil
													.debug("using setEventsByArrayList");
											caller.setEvents(e
													.toArray(new EventData[0]));
										}
									}
								} else {
									JsonUtil
											.debug("Received dummy response from JSON backend");
								}
							} else {
								CurrentState.getToaster().addItem("Scheduler",
										"Failed to get scheduler items.",
										Toaster.TOASTER_ERROR);
							}
						}
					});
				} catch (RequestException e) {
					loadingDialog.hide();
					CurrentState.getToaster().addItem("Scheduler",
							"Failed to get scheduler items.",
							Toaster.TOASTER_ERROR);
				}
			} else {
				// GWT-RPC
			}
		}

		public void updateEvent(Object updated) {
			EventData data = (EventData) updated;
			items.remove(data.getIdentifier());
			items.put(data.getIdentifier(), data);
			remoteCall(data, "move");
		}

		public void removeEvent(Object updated) {
			EventData data = (EventData) updated;
			items.remove(data.getIdentifier());
			remoteCall(data, "remove");
		}

		public void addEvent(Object updated) {
			EventData data = (EventData) updated;
			items.put(data.getIdentifier(), data);
			remoteCall(data, "add");
		}

		protected void remoteCall(final EventData data, final String s) {
			Calendar cstart = new GregorianCalendar();
			cstart.setTime(data.getStartTime());
			Calendar cend = new GregorianCalendar();
			cend.setTime(data.getEndTime());
			// Needed fields: caldateof, calhour, calminute, calduration,
			// caltype, calpatient, calfacility
			// caltype = pat (all patient appointments) || temp (call in
			// patient; reservations)
			// || block (time reservations like lunch, etc.)

			HashMap<String, String> d = new HashMap<String, String>();

			d.put("caldateof", dateToSql(cstart.getTime()));
			d
					.put("calhour", Integer.toString(cstart
							.get(Calendar.HOUR_OF_DAY)));
			d.put("calminute", Integer.toString(cstart.get(Calendar.MINUTE)));

			Integer dur = (cend.get(Calendar.HOUR) - cstart.get(Calendar.HOUR));
			if (dur < 0) {
				dur = dur + 24;
			}
			dur = (dur * 60)
					+ (cend.get(Calendar.MINUTE) - cstart.get(Calendar.MINUTE));

			d.put("calduration", Integer.toString(dur));
			d.put("caltype", data.getResourceType());
			d.put("calpatient", data.getPatientId().toString());
			if(data.getResourceType().equalsIgnoreCase(AppConstants.APPOINTMENT_TYPE_GROUP))
				d.put("calgroupid", data.getPatientId().toString());
			d.put("calphysician", data.getProviderId().toString());
			d.put("calprenote", data.getDescription());
			if (data.getAppointmentTemplateId() != null)
				d.put("calappttemplate", data.getAppointmentTemplateId()
						.toString());
			// TODO: FACILITY MISSING!
			Boolean b = false;
			if (s == "add") {
//				params[0] = JsonUtil.jsonify(d);
				String[] newParams = {JsonUtil.jsonify(d)};
				params = newParams;
				rpcparams.put("url",
						"org.freemedsoftware.api.Scheduler.SetAppointment");
				rpcparams.put("responseOk", "Adding Appointment successful.");
				rpcparams.put("responseErr", "Error Adding Appointment.");
				rpcparams.put("resulttype", "Integer");
				b = true;
			} else if (s == "move") {
				d.put("id", data.getIdentifier());

				String[] newParams = {JsonUtil.jsonify(d.get("id")),JsonUtil.jsonify(d)};
				params = newParams;
				
//				params[0] = JsonUtil.jsonify(d.get("id"));
//				params[1] = JsonUtil.jsonify(d);

				rpcparams.put("url",
						"org.freemedsoftware.api.Scheduler.MoveAppointment");
				rpcparams.put("responseOk", "Moving Appointment successful.");
				rpcparams.put("responseErr", "Error Moving Appointment.");
				rpcparams.put("resulttype", "Boolean");
				b = true;
			} else if (s == "remove") {
				d.put("id", data.getIdentifier());
				d.put("calstatus", "cancelled");
				String[] newParams = {JsonUtil.jsonify(d.get("id")),JsonUtil.jsonify(d)};
				params = newParams;
//				params[0] = JsonUtil.jsonify(d.get("id"));
//				params[1] = JsonUtil.jsonify(d);
				rpcparams.put("url",
						"org.freemedsoftware.api.Scheduler.MoveAppointment");
				rpcparams.put("responseOk", "Removing Appointment successful.");
				rpcparams.put("responseErr", "Error Removing Appointment.");
				rpcparams.put("resulttype", "Boolean");
				b = true;
			} else {
				JsonUtil
						.debug("SchedulerWidget.remoteCall(): Invalid key received.");
				b = false;
			}

			if (b) {
				if (Util.getProgramMode() == ProgramMode.STUBBED) {
					// Runs in STUBBED MODE => Do nothing
				} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
					// Use JSON-RPC to retrieve the data
					RequestBuilder builder = new RequestBuilder(
							RequestBuilder.POST, URL.encode(Util
									.getJsonRequest(rpcparams.get("url"),
											params)));
					try {
						builder.sendRequest(null, new RequestCallback() {
							public void onError(Request request, Throwable ex) {
								JsonUtil.debug(request.toString());
							}

							public void onResponseReceived(Request request,
									Response response) {
								if (response.getStatusCode() == 200) {

									Object r = JsonUtil.shoehornJson(JSONParser
											.parse(response.getText()),
											rpcparams.get("resulttype"));

									if (r != null) {
										if (rpcparams.get("resulttype") == "Integer") {

											Integer result = (Integer) r;

											JsonUtil.debug("SchedulerWidget - "
													+ s
													+ ":"
													+ rpcparams
															.get("responseOk"));
											if (s == "move") {
												items.get(data.getIdentifier())
														.setIdentifier(result);
											}
										} else if (rpcparams.get("resulttype") == "Boolean") {
											// Boolean result = (Boolean) r;
											JsonUtil.debug("SchedulerWidget - "
													+ s
													+ ":"
													+ rpcparams
															.get("responseOk"));

										} else {
											JsonUtil
													.debug("SchedulerWidget - "
															+ s
															+ ":"
															+ rpcparams
																	.get("responseErr"));
										}
									}
								}
								refreshData();
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
	}

	private Label label = new Label("");

	private ProviderWidget provider = new ProviderWidget();

	private MultiView multiPanel = null;

	private EventCacheController eventCacheController = null;

	private DatePickerMonthNavigator navigator = new DatePickerMonthNavigator(
			new NoneContraintAndEntryRenderer());

	protected DockPanel panel = new DockPanel();

	protected DialogBox loadingDialog = new DialogBox();

	protected DateTimeFormat ymdFormat = DateTimeFormat.getFormat("yyyy-MM-dd");
	
	public SchedulerWidget() {
		this(6, 24, 4);// startHour=6,endHour=24,intervalPerHour=4
	}

	public SchedulerWidget(int startHour, int endHour, int intervalPerHour) {
		super();
		eventCacheController = new EventCacheController();
		multiPanel = new MultiView(eventCacheController,
				new StringPanelRenderer(startHour, endHour, intervalPerHour));

		panel.setWidth("100%");

		final HorizontalPanel loadingContainer = new HorizontalPanel();
		loadingContainer.add(new Image("resources/images/loading.gif"));
		loadingContainer.add(new HTML("<h3>" + "Loading" + "</h3>"));
		loadingDialog.setStylePrimaryName(SchedulerCss.EVENT_DIALOG);
		loadingDialog.setWidget(loadingContainer);
		loadingDialog.hide();

		final HorizontalPanel fields = new HorizontalPanel();
		fields.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_RIGHT);
		fields.setWidth("100%");
		panel.add(fields, DockPanel.NORTH);
		
		fields.add(label);
		fields.setCellHeight(label, "50%");
		
		final HorizontalPanel filterPanel = new HorizontalPanel();
		fields.add(filterPanel);
		fields.setCellWidth(filterPanel, "50%");
		Label selectProvider = new Label("Filter by Provider:");
		filterPanel.add(selectProvider);
		selectProvider.setStyleName("label");
		filterPanel.add(provider);
		provider.setWidth("300px");
		provider.addValueChangeHandler(new ValueChangeHandler<Integer>() {
			@Override
			public void onValueChange(ValueChangeEvent<Integer> event) {
				Integer val = ((ProviderWidget) event.getSource()).getValue();
				// Log.debug("Patient value = " + val.toString());
				try {
					if (val.compareTo(new Integer(0)) != 0) {
						multiPanel.clearData();
						eventCacheController.getEventsForRange(multiPanel
								.getCurrent().getFirstDateLogical(), multiPanel
								.getCurrent().getLastDateLogical(), val,
								multiPanel, true);
					}
				} catch (Exception e) {
					// Don't do anything if no patient is declared
					GWT.log("Caught exception", e);
				}
			}
		});
		Button clearButton = new Button("clear");
		clearButton.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				provider.setValue(0);
				eventCacheController.getEventsForRange(multiPanel.getCurrent()
						.getFirstDateLogical(), multiPanel.getCurrent()
						.getLastDateLogical(), multiPanel, true);
			}
		});
		filterPanel.add(clearButton);


		VerticalPanel posPanel = new VerticalPanel();
		posPanel.setWidth("100%");
		HorizontalPanel pickerHolder = new HorizontalPanel();
		pickerHolder.add(posPanel);
		pickerHolder.add(multiPanel);

		HTML space = new HTML(" ");
		posPanel.add(space);
		space.setHeight("40px");
		posPanel.add(navigator);

		pickerHolder.setCellWidth(posPanel, "200px");
		pickerHolder.setVerticalAlignment(VerticalPanel.ALIGN_TOP);

		pickerHolder.setCellWidth(multiPanel, "100%");
		multiPanel.setWidth("100%");
		multiPanel.setPixelSize((Window.getClientWidth()*67)/100, (Window.getClientHeight()*70)/100);

		posPanel.setWidth("200px");

		panel.add(pickerHolder, DockPanel.CENTER);
		pickerHolder.setWidth("100%");
		onWindowResized(-1, Window.getClientHeight());
		panel.setStyleName("whiteForDemo");
		multiPanel.addDateListener(this);
		navigator.addDateListener(this);
		Window.addResizeHandler(this);
		multiPanel.scrollToHour(7);

		initWidget(panel);
	}

	public void refreshData() {
		multiPanel.reloadData();
	}

	public void handleDateEvent(DateEvent newEvent) {
		switch (newEvent.getCommand()) {
		case ADD: {
			final EventData data = (EventData) newEvent.getData();
			label.setText("Added event on " + data.getStartTime() + " - "
					+ data.getEndTime());
			break;
		}
		case SELECT_DAY: {
			if (newEvent.getSource() == navigator) {
				multiPanel.setDate(newEvent.getDate());
			}
			break;
		}
		case SELECT_MONTH: {
			if (newEvent.getSource() == navigator) {
				multiPanel.setType(DatePanel.MONTH);
				multiPanel.setDate(newEvent.getDate());
			}
			break;
		}
		case UPDATE: {
			final EventData data = (EventData) newEvent.getData();
			label.setText("Updated event on " + data.getStartTime() + " - "
					+ data.getEndTime());
			break;
		}
		case REMOVE: {
			final EventData data = (EventData) newEvent.getData();
			label.setText("Removed event on " + data.getStartTime() + " - "
					+ data.getEndTime());
			break;
		}

		case DRAG_DROP: {
			final EventData data = (EventData) newEvent.getData();
			label.setText("Removed event on " + data.getStartTime() + " - "
					+ data.getEndTime());
			break;
		}
		}
	}

	public void onWindowResized(int width, int height) {
		int shortcutHeight = height - 160;
		if (shortcutHeight < 1) {
			shortcutHeight = 1;
		}
		multiPanel.setHeight(shortcutHeight + "px");
	}

	@Override
	public void onClick(ClickEvent event) {

	}

	/**
	 * Convert Date object into minutes from beginning of day.
	 * 
	 * @param d
	 * @return
	 */
	protected int dateToMinutes(Date d) {
		Calendar cal = new GregorianCalendar();
		cal.setTime(d);
		return (cal.get(Calendar.HOUR_OF_DAY) * 60) + cal.get(Calendar.MINUTE);
	}

	protected String dateToSql(Date d) {
		return ymdFormat.format(d);
	}

	@Override
	public void onResize(ResizeEvent event) {
		int shortcutHeight = event.getHeight() - 160;
		if (shortcutHeight < 1) {
			shortcutHeight = 1;
		}
		multiPanel.setHeight(shortcutHeight + "px");
	}

	public DateEventListener getDateEventListener() {
		return multiPanel;
	}

	public DateRenderer getDateRenderer() {
		return multiPanel.getRenderer();
	}
}
