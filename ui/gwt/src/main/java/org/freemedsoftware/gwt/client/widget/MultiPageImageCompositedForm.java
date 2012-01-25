/*
 * $Id$
 * 
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2012 FreeMED Software Foundation
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

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.HashSetter;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.dom.client.MouseMoveEvent;
import com.google.gwt.event.dom.client.MouseMoveHandler;
import com.google.gwt.event.dom.client.MouseOutEvent;
import com.google.gwt.event.dom.client.MouseOutHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.PopupPanel.PositionCallback;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MultiPageImageCompositedForm extends Composite implements
		ClickHandler {

	public enum WidgetType {
		MODULE, TEXT, SELECT, PATIENT, CHECKBOX, RADIO, NUMBER, DATE
	};

	public static String PAGE_METHOD = "org.freemedsoftware.module.XmrDefinition.GetPage";

	protected HashMap<Integer, ImageCompositedForm> pages = new HashMap<Integer, ImageCompositedForm>();

	protected Button wNextTop, wNextBottom, wPreviousTop, wPreviousBottom;
	protected HTML topCount, bottomCount;

	protected HorizontalPanel topControls = new HorizontalPanel();
	protected HorizontalPanel bottomControls = new HorizontalPanel();

	protected HashMap<String, HashSetter> widgets = new HashMap<String, HashSetter>();

	/**
	 * Page number being currently displayed.
	 */
	protected Integer currentPage = new Integer(1);

	/**
	 * Maximum number of pages supported by the form in question.
	 */
	protected Integer totalPages = new Integer(1);

	/**
	 * Panel holding the currently displayed <ImageCompositedForm> widget.
	 */
	protected SimplePanel contentPanel = new SimplePanel();

	protected Command onFormLoaded = null;

	public MultiPageImageCompositedForm() {
		super();
		VerticalPanel vPanel = new VerticalPanel();
		initWidget(vPanel);
		setStyleName("freemed-MultiPageImageCompositedForm");

		wPreviousTop = new Button(_("Previous"));
		wPreviousTop.addClickHandler(this);
		wPreviousBottom = new Button(_("Previous"));
		wPreviousBottom.addClickHandler(this);
		topCount = new HTML("");

		wNextTop = new Button(_("Next"));
		wNextTop.addClickHandler(this);
		wNextBottom = new Button(_("Next"));
		wNextBottom.addClickHandler(this);
		bottomCount = new HTML("");

		topControls
				.setStyleName("freemed-MultiPageImageCompositedForm-ControlBar");
		topControls.add(wPreviousTop);
		topControls.add(new HTML("&nbsp;"));
		topControls.add(topCount);
		topControls.add(new HTML("&nbsp;"));
		topControls.add(wNextTop);
		topControls.setWidth("100%");

		bottomControls
				.setStyleName("freemed-MultiPageImageCompositedForm-ControlBar");
		bottomControls.add(wPreviousBottom);
		bottomControls.add(new HTML("&nbsp;"));
		bottomControls.add(bottomCount);
		bottomControls.add(new HTML("&nbsp;"));
		bottomControls.add(wNextBottom);
		bottomControls.setWidth("100%");

		vPanel.add(topControls);
		vPanel.add(contentPanel);
		vPanel.add(bottomControls);

		// Initial state of buttons
		refreshButtons();
	}

	public void setOnFormLoaded(Command c) {
		onFormLoaded = c;
	}

	public Command getOnFormLoaded() {
		return onFormLoaded;
	}

	/**
	 * Update button state properly depending on current page position.
	 */
	public void refreshButtons() {
		if (currentPage > 1) {
			wPreviousTop.setEnabled(true);
			wPreviousBottom.setEnabled(true);
		} else {
			wPreviousTop.setEnabled(false);
			wPreviousBottom.setEnabled(false);
		}
		if (currentPage < totalPages) {
			wNextTop.setEnabled(true);
			wNextBottom.setEnabled(true);
		} else {
			wNextTop.setEnabled(false);
			wNextBottom.setEnabled(false);
		}
		topCount
				.setHTML(currentPage.toString() + " / " + totalPages.toString());
		bottomCount.setHTML(currentPage.toString() + " / "
				+ totalPages.toString());
	}

	/**
	 * Perform RPC calls to load form based on record id.
	 * 
	 * @param form
	 */
	public void loadFormFromServer(Integer form) {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// STUB
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { "XmrDefinition", JsonUtil.jsonify(form) };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.ModuleInterface.ModuleGetRecordMethod",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("XmrDefinition",
								_("Failed to load data."));
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						JsonUtil.debug("onResponseReceived");
						if (200 == response.getStatusCode()) {
							JsonUtil.debug(response.getText());
							HashMap<String, String> r = (HashMap<String, String>) JsonUtil
									.shoehornJson(JSONParser.parseStrict(response
											.getText()),
											"HashMap<String,String>");
							if (r != null) {
								populateFormDefinition(r);
							}
						} else {
							Util.showErrorMsg("XmrDefinition",
									_("Failed to load data."));
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			ModuleInterfaceAsync service = null;
			try {
				service = (ModuleInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
			} catch (Exception e) {
				JsonUtil.debug(e.toString());
				Util.showErrorMsg("XmrDefinition", _("Failed to load data."));
			}
			service.ModuleGetRecordMethod("XmrDefinition", form,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> r) {
							populateFormDefinition(r);
						}

						public void onFailure(Throwable t) {
							Util.showErrorMsg("XmrDefinition",
									_("Failed to load data."));
						}
					});
		}
	}

	/**
	 * Callback for populating multi-form widget with <ImageCompositedForm>
	 * widgets.
	 * 
	 * @param r
	 */
	protected void populateFormDefinition(HashMap<String, String> r) {
		Integer pageCount = 0;
		try {
			pageCount = Integer.parseInt(r.get("form_page_count"));
		} catch (NumberFormatException ex) {

		}

		// Remove all pages from the stack
		this.pages.clear();

		// Create all pages
		for (int pageIter = 1; pageIter <= pageCount; pageIter++) {
			this.createImageCompositedForm(Integer.parseInt(r.get("id")),
					pageIter);
		}

		// Make sure to refresh button bar status
		refreshButtons();

		// Only do this once the form has been loaded
		if (onFormLoaded != null) {
			onFormLoaded.execute();
		}
	}

	/**
	 * Internal method to create an ImageCompositedForm object pointing at the
	 * RPC image for the page in question. No form elements/widgets are assigned
	 * by this method.
	 * 
	 * @param formNumber
	 *            Internal identifier for the form
	 * @param pageNumber
	 *            Page number, starting at 1
	 * @return
	 */
	protected ImageCompositedForm createImageCompositedForm(Integer formNumber,
			Integer pageNumber) {
		ImageCompositedForm form = new ImageCompositedForm();
		form.setImage(Util.getJsonRequest(PAGE_METHOD, new String[] {
				JsonUtil.jsonify(formNumber), JsonUtil.jsonify(pageNumber) }));
		return form;
	}

	/**
	 * Change the currently displayed page in the stack.
	 * 
	 * @param pageNumber
	 */
	public void loadPage(Integer pageNumber) {
		currentPage = pageNumber;
		if (pages.get(pageNumber) != null) {
			contentPanel.setWidget(pages.get(pageNumber));
		}
		refreshButtons();
	}

	@Override
	public void onClick(ClickEvent event) {
		Widget w = (Widget) event.getSource();
		if (w == wPreviousTop || w == wPreviousBottom) {
			loadPage(currentPage - 1);
			return;
		}
		if (w == wNextTop || w == wNextBottom) {
			loadPage(currentPage + 1);
			return;
		}
	}

	/**
	 * Convert string into <WidgetType> enumerated value
	 * 
	 * @param widget
	 * @return
	 */
	public WidgetType stringToWidgetType(String widget) {
		if (widget.compareToIgnoreCase("TEXT") == 0) {
			return WidgetType.TEXT;
		}
		if (widget.compareToIgnoreCase("MODULE") == 0) {
			return WidgetType.MODULE;
		}
		if (widget.compareToIgnoreCase("SELECT") == 0) {
			return WidgetType.SELECT;
		}
		if (widget.compareToIgnoreCase("PATIENT") == 0) {
			return WidgetType.PATIENT;
		}
		if (widget.compareToIgnoreCase("CHECKBOX") == 0) {
			return WidgetType.CHECKBOX;
		}
		if (widget.compareToIgnoreCase("RADIO") == 0) {
			return WidgetType.RADIO;
		}
		if (widget.compareToIgnoreCase("DATE") == 0) {
			return WidgetType.DATE;
		}

		// By default, return text
		JsonUtil.debug("MultiPageImageCompositedForm"
				+ ": Unimplemented type '" + widget
				+ "' found. Fallback to type TEXT.");
		return WidgetType.TEXT;
	}

	/**
	 * Convenience method for setting value of a contained widget given the
	 * widget's name in the widgets hashmap.
	 * 
	 * @param name
	 *            "name" key in the widgets hashmap
	 * @param value
	 *            Value to assign
	 */
	public void setWidgetValue(String name, String value) {
		HashSetter w = widgets.get(name);
		if (value != null) {
			if (w instanceof CustomTextBox) {
				((CustomTextBox) w).setText(value);
			}
			if (w instanceof CustomListBox) {
				((CustomListBox) w).setWidgetValue(value);
			}
			if (w instanceof SupportModuleWidget) {
				((SupportModuleWidget) w).setValue(new Integer(value));
			}
			if (w instanceof SupportModuleMultipleChoiceWidget) {
				((SupportModuleMultipleChoiceWidget) w)
						.setCommaSeparatedValues(value);
			}
			if (w instanceof CustomDatePicker) {
				((CustomDatePicker) w).setValue(value);
			}
			if (w instanceof PatientWidget) {
				((PatientWidget) w).setValue(new Integer(value));
			}
		}
	}

	public void addWidget(String name, WidgetType type, String options,
			String value, String help, Integer page, Integer x, Integer y) {
		HashSetter w;

		if (type == WidgetType.TEXT) {
			w = new CustomTextBox();
			try {
				Integer len = new Integer(options);
				if (len > 0) {
					JsonUtil.debug("addWidget " + name + " has length of "
							+ len);
					((CustomTextBox) w).setVisibleLength(len.intValue() + 1);
					((CustomTextBox) w).setMaxLength(len.intValue());
				}
			} catch (Exception ex) {
			}
		} else if (type == WidgetType.MODULE) {
			w = new SupportModuleWidget(options);
		} else if (type == WidgetType.SELECT) {
			w = new CustomListBox();

			// Push in all "options" values
			String[] o = options.split(",");
			for (int iter = 0; iter < o.length; iter++) {
				// Check for "description" pairs
				if (o[iter].contains("|")) {
					String[] i = o[iter].split("\\|");
					((CustomListBox) w).addItem(i[0], i[1]);
				} else {
					if (o[iter].length() > 0) {
						((CustomListBox) w).addItem(o[iter]);
					}
				}
			}
		} else if (type == WidgetType.PATIENT) {
			w = new PatientWidget();
		} else if (type == WidgetType.DATE) {
			w = new CustomDatePicker();
		} else {
			// Unimplemented, use text box as fallback
			w = new CustomTextBox();
			JsonUtil.debug("MultiPageImageCompositedForm"
					+ ": Unimplemented type '" + type
					+ "' found. Fallback to TextBox.");
		}

		// Add to indices and display
		widgets.put(name, w);

		if (help != null) {
			final Image image = new Image();
			image.setUrl("resources/images/q_help.16x16.png");

			final PopupPanel popup = new PopupPanel();
			final HTML html = new HTML();
			html.setHTML(help);

			popup.add(html);
			popup.setStyleName("freemed-HelpPopup");

			image.addMouseOutHandler(new MouseOutHandler() {
				@Override
				public void onMouseOut(MouseOutEvent event) {
					// Hide help PopUp
					popup.hide();
				}

			});
			image.addMouseMoveHandler(new MouseMoveHandler() {
				@Override
				public void onMouseMove(MouseMoveEvent event) {
					// Do nothing
					popup.setPopupPositionAndShow(new PositionCallback() {
						public void setPosition(int offsetWidth,
								int offsetHeight) {
							// Show it relative to the mouse-pointer.
							popup.setPopupPosition(offsetWidth + 20,
									offsetHeight + 20);
						}
					});
				}
			});
		}

		// Set widget value after it is added.
		this.setWidgetValue(name, value);

		// Place on page
		ImageCompositedForm thisPage = pages.get(page);
		if (thisPage != null) {
			thisPage.addWidget((Widget) w, x, y);
		} else {
			JsonUtil.debug("Could not add widget to page " + page + " at " + x
					+ "," + y);
		}
	}

}
