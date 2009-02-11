package org.freemedsoftware.gwt.client.widget;

import org.freemedsoftware.gwt.client.screen.MessagingComposeScreen;

import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.FocusPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class MessageView extends Composite {

	public MessageView(String text) {
		
		final SimplePanel sPanel = new SimplePanel(); 
		initWidget(sPanel);
		VerticalPanel verticalPanel = new VerticalPanel();
		
		HorizontalPanel horizontalPanel = new HorizontalPanel();
		verticalPanel.add(horizontalPanel);
		
		//Button definitions:
		//
		//TODO: Replace Images by new ones
		//We don't use a Button, but an image+label inside a focusPanel, because it 
		//allows us, that the user can click on both to provoke the event 
		//
		//Reply Button
		
		final Image replyButton = new Image("resources/images/messaging.32x32.png");
		final Label replyLabel = new Label("Reply");
		VerticalPanel replyVerticalPanel = new VerticalPanel();
		replyVerticalPanel.add(replyButton);
		replyVerticalPanel.add(replyLabel);
		replyVerticalPanel.setStylePrimaryName("freemed-MessageView-verticalPanelButton");
		final FocusPanel replyWrapper = new FocusPanel();
		replyWrapper.add(replyVerticalPanel);
		replyWrapper.setStylePrimaryName("freemed-MessageView-buttonWrapper");

		replyWrapper.addClickListener(new ClickListener(){
			public void onClick(Widget sender) {
				final Popup popup = new Popup();
				popup.setNewWidget(new MessagingComposeScreen());
				popup.setWidthOffset(20);
				popup.setHeightOffset(10);
				popup.initialize();
			}
			
		});
		horizontalPanel.add(replyWrapper);
		
		//Forward Button
		
		final Image forwardButton = new Image("resources/images/messaging.32x32.png");
		final Label forwardLabel = new Label("Forward");
		VerticalPanel forwardVerticalPanel = new VerticalPanel();
		forwardVerticalPanel.add(forwardButton);
		forwardVerticalPanel.add(forwardLabel);
		forwardVerticalPanel.setStylePrimaryName("freemed-MessageView-verticalPanelButton");
		final FocusPanel forwardWrapper = new FocusPanel();
		forwardWrapper.add(forwardVerticalPanel);
		forwardWrapper.setStylePrimaryName("freemed-MessageView-buttonWrapper");

		forwardWrapper.addClickListener(new ClickListener(){
			public void onClick(Widget sender) {
				//TODO: create that
				Window.alert("Forward");
			}
			
		});
		horizontalPanel.add(forwardWrapper);
		
		verticalPanel.add(new HTML(text));
		sPanel.add(verticalPanel);
	
	}

}
