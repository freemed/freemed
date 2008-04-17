package org.freemedsoftware.gwt.client;

import com.google.gwt.user.client.ui.AbsolutePanel;
import com.google.gwt.user.client.ui.Button;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HTMLPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.ListBox;
import com.google.gwt.user.client.ui.PasswordTextBox;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.TextBox;

public class LogIn extends Composite {

	public LogIn() {

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

		final Button loginButton = new Button();
		absolutePanel.add(loginButton, 92, 240);
		loginButton.setStylePrimaryName("");
		loginButton.setStyleName("gwt-LoginButton");
		loginButton.setText("login");
		
	}

}
