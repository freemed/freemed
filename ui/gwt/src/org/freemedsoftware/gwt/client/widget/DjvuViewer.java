/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Module.UnfiledDocumentsAsync;
import org.freemedsoftware.gwt.client.Module.UnreadDocumentsAsync;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class DjvuViewer extends Composite {

	public final static int UNFILED_DOCUMENTS = 1;

	public final static int UNREAD_DOCUMENTS = 2;

	public final static int SCANNED_DOCUMENTS = 3;

	protected Integer patientId = new Integer(0);

	protected Integer internalId = new Integer(0);

	protected int viewerType = SCANNED_DOCUMENTS;

	protected int numberOfPages = 1;

	protected int currentPage = 1;

	protected final Label wPageTop, wPageBottom;

	protected final Image wImage;

	protected final PushButton wBackTop, wForwardTop, wBackBottom,
			wForwardBottom;

	public DjvuViewer() {
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		final HorizontalPanel controlBarTop = new HorizontalPanel();
		verticalPanel.add(controlBarTop);
		controlBarTop.setWidth("100%");

		// Click listeners
		ClickListener clPrevious = new ClickListener() {
			public void onClick(Widget w) {
				pagePrevious();
			}
		};
		ClickListener clNext = new ClickListener() {
			public void onClick(Widget w) {
				pageNext();
			}
		};
		ClickListener clView = new ClickListener() {
			public void onClick(Widget w) {
				viewDocument();
			}
		};

		wBackTop = new PushButton();
		controlBarTop.add(wBackTop);
		wBackTop.setText("-");
		wBackTop.setStylePrimaryName("freemed-PushButton");
		wBackTop.addClickListener(clPrevious);

		wPageTop = new Label("1 of 1");
		controlBarTop.add(wPageTop);
		wPageTop.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		PushButton wViewTop = new PushButton("View");
		wViewTop.addClickListener(clView);
		wViewTop.setStylePrimaryName("freemed-PushButton");
		controlBarTop.add(wViewTop);

		wForwardTop = new PushButton();
		controlBarTop.add(wForwardTop);
		wForwardTop.setText("+");
		wForwardTop.setStylePrimaryName("freemed-PushButton");
		wForwardTop.addClickListener(clNext);

		wImage = new Image();
		verticalPanel.add(wImage);
		wImage.setSize("100%", "100%");

		final HorizontalPanel controlBarBottom = new HorizontalPanel();
		verticalPanel.add(controlBarBottom);
		controlBarBottom.setWidth("100%");
		controlBarBottom
				.setVerticalAlignment(HasVerticalAlignment.ALIGN_BOTTOM);

		wBackBottom = new PushButton();
		controlBarBottom.add(wBackBottom);
		wBackBottom.setText("-");
		wBackBottom.setStylePrimaryName("freemed-PushButton");
		wBackBottom.addClickListener(clPrevious);

		wPageBottom = new Label("1 of 1");
		controlBarBottom.add(wPageBottom);
		wPageBottom.setHorizontalAlignment(HasHorizontalAlignment.ALIGN_CENTER);

		PushButton wViewBottom = new PushButton("View");
		wViewBottom.addClickListener(clView);
		wViewBottom.setStylePrimaryName("freemed-PushButton");
		controlBarBottom.add(wViewBottom);

		wForwardBottom = new PushButton();
		controlBarBottom.add(wForwardBottom);
		wForwardBottom.setText("+");
		wForwardBottom.setStylePrimaryName("freemed-PushButton");
		wForwardBottom.addClickListener(clNext);
	}

	/**
	 * Retrieve the current number of pages for the internal document.
	 */
	protected boolean getNumberOfPages() {
		if (internalId.intValue() == 0) {
			GWT.log("getNUmberOfPages called without initializing internalId",
					null);
			return false;
		}
		if (viewerType == UNFILED_DOCUMENTS) {
			UnfiledDocumentsAsync p = null;
			try {
				p = (UnfiledDocumentsAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.UnfiledDocuments");
			} catch (Exception e) {
				GWT.log("Exception", e);
			}
			p.NumberOfPages(internalId, new AsyncCallback() {
				public void onSuccess(Object o) {
					numberOfPages = (Integer) o;
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
		if (viewerType == UNREAD_DOCUMENTS) {
			UnreadDocumentsAsync p = null;
			try {
				p = (UnreadDocumentsAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Module.UnreadDocuments");
			} catch (Exception e) {
				GWT.log("Exception", e);
			}
			p.NumberOfPages(internalId, new AsyncCallback() {
				public void onSuccess(Object o) {
					numberOfPages = (Integer) o;
				}

				public void onFailure(Throwable t) {
					GWT.log("Exception", t);
				}
			});
		}
		if (viewerType == SCANNED_DOCUMENTS) {
			// TODO: make this work for scanned documents
		}
		return true;
	}

	/**
	 * Load a page into the widget.
	 * 
	 * @param pageNumber
	 * @throws Exception
	 */
	public void loadPage(int pageNumber) throws Exception {
		// Handle all issues ...
		if (internalId.compareTo(new Integer(0)) == 0) {
			throw new Exception("Internal id not set");
		}
		if (patientId.compareTo(new Integer(0)) == 0) {
			throw new Exception("Patient id not set");
		}
		if (viewerType == 0) {
			throw new Exception("Document type not set");
		}

		// Set image URL to the appropriate page
		wImage.setUrl(Util.getJsonRequest(resolvePageViewMethod(),
				new String[] { internalId.toString(),
						new Integer(pageNumber).toString() }));

		// Set the current page counter
		String pageCountLabelText = new Integer(pageNumber).toString() + " of "
				+ new Integer(numberOfPages).toString();
		currentPage = pageNumber;
		wPageTop.setText(pageCountLabelText);
		wPageBottom.setText(pageCountLabelText);

		// Enable/disable buttons as needed
		if (currentPage == 1) {
			wBackTop.setEnabled(false);
			wBackBottom.setEnabled(false);
		} else {
			wBackTop.setEnabled(true);
			wBackBottom.setEnabled(true);
		}
		if (currentPage == numberOfPages) {
			wForwardTop.setEnabled(false);
			wForwardBottom.setEnabled(false);
		} else {
			wForwardTop.setEnabled(true);
			wForwardBottom.setEnabled(true);
		}
	}

	protected void pageNext() {
		try {
			loadPage(currentPage + 1);
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
	}

	protected void pagePrevious() {
		try {
			loadPage(currentPage - 1);
		} catch (Exception e) {
			GWT.log("Exception", e);
		}
	}

	protected String resolveNamespace() {
		if (viewerType == UNFILED_DOCUMENTS) {
			return new String("org.freemedsoftware.module.UnfiledDocuments");
		}
		if (viewerType == UNREAD_DOCUMENTS) {
			return new String("org.freemedsoftware.module.UnreadDocuments");
		}
		if (viewerType == SCANNED_DOCUMENTS) {
			return new String("org.freemedsoftware.module.ScannedDocuments");
		}

		// If all else fails ...
		return new String("");
	}

	/**
	 * Internal method to resolve page view URL.
	 * 
	 * @return Method name
	 */
	protected String resolvePageViewMethod() {
		return resolveNamespace() + ".GetDocumentPage";
	}

	/**
	 * Set internal document id.
	 * 
	 * @param id
	 */
	public void setInternalId(Integer id) {
		internalId = id;
		// Callback for setting pages
		getNumberOfPages();
	}

	/**
	 * Set internal patient id.
	 * 
	 * @param patient
	 */
	public void setPatient(Integer patient) {
		patientId = patient;
	}

	/**
	 * Set string indicating URL used for image transfer from JSON relay. Use
	 * UNFILED_DOCUMENTS, UNREAD_DOCUMENTS, SCANNED_DOCUMENTS.
	 * 
	 * @param type
	 */
	public void setType(int type) {
		viewerType = type;
	}

	/**
	 * Open up full page view.
	 */
	public void viewDocument() {
		String[] params = { (String) internalId.toString() };
		Window.open(Util.getJsonRequest(resolveNamespace() + ".GetDocumentPDF",
				params), "View", "");
	}

}
