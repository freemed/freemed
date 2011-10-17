/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2011 FreeMED Software Foundation
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

import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

import static org.freemedsoftware.gwt.client.i18n.I18nUtil._;

public class DocumentThumbnailsWidget extends Composite {

	HorizontalPanel[] thumbnailParentContainer = null;
	VerticalPanel[] thumbnails;
	DjvuViewer djviewer = null;
	int startcount;
	int endcount;
	final int PAGES_PER_SCREEN = 4;
	final int ROWS_PER_SCREEN = 2;
	int selectedPage = 0;
	CustomRequestCallback callBack = null;
	CheckBox[] cbPages;

	public DocumentThumbnailsWidget(DjvuViewer dv, CustomRequestCallback c) {
		callBack = c;
		djviewer = dv;
		VerticalPanel vPanel = new VerticalPanel();
		initWidget(vPanel);
		vPanel.setWidth("100%");
		vPanel.setVisible(true);
		vPanel.setHorizontalAlignment(VerticalPanel.ALIGN_CENTER);
		vPanel.setSpacing(10);
		Label lblHeadingStep2 = new Label(_("Batch Split"));
		lblHeadingStep2.setStyleName(AppConstants.STYLE_LABEL_LARGE_BOLD);
		vPanel.add(lblHeadingStep2);
		Label lbMessage = new Label(
				_("Select the pages from where you want to split."));
		lbMessage.setStyleName(AppConstants.STYLE_LABEL_NORMAL_ITALIC);
		vPanel.add(lbMessage);
		thumbnailParentContainer = new HorizontalPanel[ROWS_PER_SCREEN];
		for (int i = 0; i < thumbnailParentContainer.length; i++) {
			thumbnailParentContainer[i] = new HorizontalPanel();
			thumbnailParentContainer[i].setSpacing(10);
			vPanel.add(thumbnailParentContainer[i]);
		}
		startcount = 0;
		endcount = 0;
		cbPages = new CheckBox[djviewer.getPageCount()];
		for (int i = 0; i < cbPages.length; i++) {
			cbPages[i] = new CheckBox(_("Page") + " " + (i + 1));
		}
		if (djviewer.getPageCount() > PAGES_PER_SCREEN) {
			endcount = PAGES_PER_SCREEN;
		} else {
			endcount = djviewer.getPageCount();
		}
		thumbnails = new VerticalPanel[endcount];
		updateThumbNails();
		HorizontalPanel hp = new HorizontalPanel();
		hp.setSpacing(5);
		if (djviewer.getPageCount() > PAGES_PER_SCREEN) {
			final CustomButton viewPrevios = new CustomButton(_("Previous"),
					AppConstants.ICON_PREV);
			viewPrevios.setEnabled(false);
			final CustomButton viewNext = new CustomButton(_("Next"),
					AppConstants.ICON_NEXT);

			viewPrevios.addClickHandler(new ClickHandler() {

				@Override
				public void onClick(ClickEvent arg0) {
					endcount = startcount;
					startcount -= PAGES_PER_SCREEN;
					if (startcount == 0) {
						viewPrevios.setEnabled(false);
					}
					viewNext.setEnabled(true);
					updateThumbNails();

				}
			});

			viewNext.addClickHandler(new ClickHandler() {

				@Override
				public void onClick(ClickEvent arg0) {
					startcount += PAGES_PER_SCREEN;
					if ((djviewer.getPageCount() - endcount) > PAGES_PER_SCREEN) {
						endcount += PAGES_PER_SCREEN;
					} else {
						endcount += (djviewer.getPageCount() - endcount);
					}
					if (endcount == (djviewer.getPageCount())) {
						viewNext.setEnabled(false);
					}
					viewPrevios.setEnabled(true);
					updateThumbNails();
				}
			});

			hp.add(viewPrevios);
			hp.add(viewNext);

		}
		final CustomButton SplitBtn = new CustomButton(_("Split"));
		SplitBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				Integer[] pNos = new Integer[djviewer.getPageCount()];
				for (int i = 0; i < djviewer.getPageCount(); i++) {
					if (cbPages[i].getValue()) {
						pNos[i] = 1;
					} else {
						pNos[i] = 0;
					}

				}
				callBack.jsonifiedData(pNos);
			}
		});

		final CustomButton cancelBtn = new CustomButton(_("Cancel"),
				AppConstants.ICON_CANCEL);
		cancelBtn.addClickHandler(new ClickHandler() {

			@Override
			public void onClick(ClickEvent arg0) {
				callBack.jsonifiedData(0);
			}
		});
		hp.add(SplitBtn);
		hp.add(cancelBtn);
		vPanel.add(hp);

	}

	public void updateThumbNails() {
		int counter = 0;
		for (int j = 0; j < thumbnailParentContainer.length; j++) {
			thumbnailParentContainer[j].clear();
		}
		for (int i = startcount; i < endcount; i++) {
			// final int index = i;
			thumbnails[counter] = new VerticalPanel();
			int containerIndex = (int) (i / (int) (PAGES_PER_SCREEN / ROWS_PER_SCREEN))
					% thumbnailParentContainer.length;

			thumbnailParentContainer[containerIndex].add(thumbnails[counter]);
			try {

				final Image im = djviewer.getPageThumbnail(i + 1);
				thumbnails[counter].add(im);
				// l.setWidth("auto");
				// l.setHorizontalAlignment(HorizontalPanel.ALIGN_CENTER);
				thumbnails[counter].add(cbPages[i]);
			} catch (Exception e) {
				JsonUtil.debug(e.getMessage());
			}
			counter++;

		}
	}

	public int getSelectedPageNumber() {
		return selectedPage;
	}
}
