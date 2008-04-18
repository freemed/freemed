/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *      Jeremy Allen <ieziar.jeremy <--at--> gmail.com>
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

package org.freemedsoftware.gwt.client;

import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FocusPanel;
import com.google.gwt.user.client.ui.HTMLPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.Widget;

public class LoginDialog extends Composite {

	public LoginDialog() {

		final AbsolutePanel absolutePanel = new AbsolutePanel();
		initWidget(absolutePanel);
		absolutePanel.setTitle("Login");
		absolutePanel.setStylePrimaryName("loginPanel");
		absolutePanel.setStyleName("loginPanel");
		absolutePanel.setSize("300px", "300px");

		final TextBox userLogin = new TextBox();
		absolutePanel.add(userLogin, 92, 71);
		userLogin.setSize("139px", "22px");
		userLogin.setStyleName("gwt-LoginFields");
		userLogin.setStylePrimaryName("");
		userLogin.setText("your user name");
		userLogin.setAccessKey('u');

		final Label userLabel = new Label("user name");
		absolutePanel.add(userLabel, 25, 71);
		userLabel.setStylePrimaryName("");
		userLabel.setStyleName("gwt-Label-RAlign");

		final PasswordTextBox loginPassword = new PasswordTextBox();
		absolutePanel.add(loginPassword, 92, 102);
		loginPassword.setSize("139px", "22px");
		loginPassword.setStyleName("gwt-LoginFields");
		loginPassword.setStylePrimaryName("");
		loginPassword.setText("password");

		final Label passwordLabel = new Label("password");
		absolutePanel.add(passwordLabel, 25, 100);
		passwordLabel.setStylePrimaryName("");
		passwordLabel.setStyleName("gwt-Label-RAlign");

		final ListBox facilityList = new ListBox();
		absolutePanel.add(facilityList, 94, 149);
		facilityList.setSize("191px", "22px");
		facilityList.setStyleName("gwt-LoginFields");
		facilityList.setStylePrimaryName("");
		facilityList.addItem("Mt. Ascutney Hospital Medical Clinic Examination Room");
		facilityList.addItem("Associates in Surgery & Gastroenterology, LLC");
		facilityList.addItem("Valley Regional Hospital");

		final ListBox languageList = new ListBox();
		absolutePanel.add(languageList, 94, 180);
		languageList.setWidth("190px");
		languageList.setStyleName("gwt-LoginFields");
		languageList.setStylePrimaryName("");
		languageList.addItem("English");
		languageList.addItem("Deutsch");
		languageList.addItem("Espanol (Mexico)");
		languageList.addItem("Polski");

		final Label facilityLabel = new Label("facility");
		absolutePanel.add(facilityLabel, 28, 152);
		facilityLabel.setStylePrimaryName("");
		facilityLabel.setStyleName("gwt-Label-RAlign");
		facilityLabel.setSize("59px", "19px");

		final Label languageLabel = new Label("language");
		absolutePanel.add(languageLabel, 28, 183);
		languageLabel.setStylePrimaryName("");
		languageLabel.setStyleName("gwt-Label-RAlign");
		languageLabel.setSize("59px", "19px");

		final Image image = new Image("resources/images/button_on.png");
		image.setSize("100%", "100%");
		
		final PushButton loginButton = new PushButton(image);
		loginButton.addClickListener(new ClickListener() {
			public void onClick( Widget w ) {
				
			}
		});
		absolutePanel.add(loginButton, 83, 233);
		
		final Label loginLabel = new Label("Login");
		absolutePanel.add(loginLabel, 140, 242);
		loginLabel.setStyleName("gwt-Label-RAlign");
		
	}

}
