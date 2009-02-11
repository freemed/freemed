package org.freemedsoftware.gwt.client.widget;

import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.ClickListener;
import com.google.gwt.user.client.ui.FocusPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PopupPanel;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.ScrollPanel;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class Popup extends PopupPanel {
	
	protected Widget mWidget;
	final SimplePanel sPanelOuter = new SimplePanel();
	final SimplePanel sPanelInner = new SimplePanel();
	final ScrollPanel scrollPanel = new ScrollPanel();
	final VerticalPanel verticalPanel = new VerticalPanel();
	protected Integer widthmodifier = 4;
	protected Integer heightmodifier = 3;
	protected Integer widthoffset = 0;
	protected Integer heightoffset = 0;
	
	public Popup() {
    	//Auto-hide ON
		super(true);
    }
	
	public void setNewWidget(Widget w) {
		mWidget = w;
	}
	public void initialize() {
		
		sPanelInner.setStylePrimaryName("freemed-Popup-sPanelInner");
		sPanelOuter.add(sPanelInner);
		sPanelOuter.setStylePrimaryName("freemed-Popup-sPanelOuter");
		sPanelOuter.setWidth(Integer.toString(Window.getClientWidth()/2).concat("px"));
		scrollPanel.setHeight(Integer.toString(Window.getClientHeight()/2).concat("px"));
		scrollPanel.add(mWidget);
		verticalPanel.add(scrollPanel);
		verticalPanel.add(new HTML("<br/><br/><small>(Click outside this popup to close it)</small>"));
		sPanelInner.add(verticalPanel);
		setWidget(sPanelOuter);	
		setPosition();
	}
	
	public void setPosition() {
		setPopupPositionAndShow(new PopupPanel.PositionCallback() {
			public void setPosition(int offsetWidth, int offsetHeight) {
				int left = ((Window.getClientWidth() - offsetWidth) / widthmodifier )+ widthoffset;
				int top = ((Window.getClientHeight() - offsetHeight) / heightmodifier) + heightoffset;
				setPopupPosition(left, top);
				//setStylePrimaryName("freemed-MessageBox-Popup");
			}
		});
	}
	
	public void setWidthOffset(Integer i) {
		widthoffset = i;
	}
	
	public void setHeightOffset(Integer i) {
		heightoffset = i;
	}
	
	public void setWidthModifier(Integer modifier) {
		widthmodifier = modifier;
	}
	
	public void setHeightModifier(Integer modifier) {
		heightmodifier = modifier;
	}
}
