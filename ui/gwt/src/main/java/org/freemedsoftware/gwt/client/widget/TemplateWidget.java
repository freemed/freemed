package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Set;

import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.dom.client.Style.Cursor;
import com.google.gwt.dom.client.Style.Unit;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.event.logical.shared.ValueChangeEvent;
import com.google.gwt.event.logical.shared.ValueChangeHandler;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.HasVerticalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class TemplateWidget extends Composite {
	protected int subSectionLeftMargin = 50;
	protected HashMap<String, List<String>> sectionsFieldMap;
	protected HashMap<String, List<String>> selectedSectionsFieldMap;
	protected HashMap<String, CheckBox> checkBoxesMap;
	protected HashMap<String, SectionOrderPanel> sectionPanelsMaps;
	protected HorizontalPanel mainPanel;
	protected VerticalPanel secFieldPanel;
	protected VerticalPanel orderPanel;
	protected SectionOrderPanel mainSectionPanel;
	
	public TemplateWidget(HashMap<String, List<String>> sfm) {
		mainPanel = new HorizontalPanel();
		mainPanel.setSize("100%", "100%");
		initWidget(mainPanel);
		sectionsFieldMap = sfm;
		selectedSectionsFieldMap = new HashMap<String, List<String>>();
		checkBoxesMap = new HashMap<String, CheckBox>();
		secFieldPanel = new VerticalPanel();
		secFieldPanel.setWidth("100%");
		orderPanel = new VerticalPanel();
		sectionPanelsMaps=new HashMap<String, SectionOrderPanel>();
		orderPanel.setSpacing(15);
		orderPanel.setWidth("100%");
		mainPanel.add(secFieldPanel);
		mainPanel.add(orderPanel);
		mainPanel.setCellWidth(secFieldPanel, "50%");
		mainPanel.setCellWidth(orderPanel, "30%");
		mainSectionPanel = new SectionOrderPanel("Sections", true);
		mainSectionPanel.setWidth("100%");
		mainSectionPanel.setVisible(false);
		orderPanel.add(mainSectionPanel);
		generateSectionsFieldPanel();
	}

	private void generateSectionsFieldPanel() {
		createSections("", "Sections", secFieldPanel, 0);
	}

	private void createSections(final String parent, final String secName,
			VerticalPanel rootPanel, int left) {
		final String fullName;
		if (secName.equals("Sections"))
			fullName = "Sections";
		else
			fullName = parent + "#" + secName;
		if (sectionsFieldMap.containsKey(fullName)) {
			final VerticalPanel subPanel = new VerticalPanel();

			subPanel.getElement().getStyle().setMarginLeft(left, Unit.PX);
			if (!secName.equals("Sections")) {
				subPanel.setVisible(false);
				HorizontalPanel hp = new HorizontalPanel();
				hp.setVerticalAlignment(HasVerticalAlignment.ALIGN_MIDDLE);
				final Image expandBtn = new Image(
						"resources/images/expand.15x15.png");
				expandBtn.getElement().getStyle().setCursor(Cursor.POINTER);
				final CheckBox cBox = new CheckBox(secName.replace("_", ", "));
				checkBoxesMap.put(fullName, cBox);
				cBox.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
					@Override
					public void onValueChange(ValueChangeEvent<Boolean> arg0) {
						if (cBox.getValue()) {
							selectParentChildBoxes(fullName, parent);
							if (parent.equals("Sections")) {
								mainSectionPanel.add(secName);
							}
						} else {
							unSelectParentChildBoxes(fullName, parent);
							if (parent.equals("Sections")) {
								mainSectionPanel.remove(secName);
							}
						}
					}
				});
				cBox.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
				hp.add(expandBtn);
				hp.add(cBox);
				rootPanel.add(hp);
				expandBtn.addClickHandler(new ClickHandler() {
					@Override
					public void onClick(ClickEvent arg0) {
						if (subPanel.isVisible()) {
							subPanel.setVisible(false);
							expandBtn
									.setUrl("resources/images/expand.15x15.png");
						} else {
							subPanel.setVisible(true);
							expandBtn
									.setUrl("resources/images/collapse.15x15.png");
						}
					}
				});
			}
			rootPanel.add(subPanel);
			List<String> list = null;
			if (secName.equals("Sections"))
				list = sectionsFieldMap.get(secName);
			else
				list = sectionsFieldMap.get(fullName);
			for (int i = 0; i < list.size(); i++) {
				String n = list.get(i);
				createSections(fullName, n, subPanel, 50);
			}
		} else {
			final CheckBox cBox = new CheckBox(secName.replace("_", ", "));
			checkBoxesMap.put(fullName, cBox);
			cBox.addValueChangeHandler(new ValueChangeHandler<Boolean>() {
				@Override
				public void onValueChange(ValueChangeEvent<Boolean> arg0) {
					if (cBox.getValue()){
						selectParentChildBoxes(fullName, parent);
					}
					else {
						unSelectParentChildBoxes(fullName, parent);
					}
				}
			});
			cBox.setStyleName(AppConstants.STYLE_LABEL_NORMAL_BOLD);
			rootPanel.add(cBox);
		}
	}

	private void selectParentChildBoxes(String secname, String parent) {
		Set<String> keys = checkBoxesMap.keySet();
		String[] keyArr = keys.toArray(new String[0]);
		for (int i = 0; i < keyArr.length; i++) {
			if (keyArr[i].startsWith(secname) || secname.startsWith(keyArr[i])) {
				String keyName = "";
				String selectedVal = "";
				int lastIndex = keyArr[i].lastIndexOf("#");
				if (lastIndex != -1) {
					keyName = keyArr[i].substring(0, lastIndex);
					selectedVal = keyArr[i].substring(lastIndex + 1);
				} else {
					keyName = keyArr[i];
					selectedVal = keyArr[i];
				}
				if (!selectedSectionsFieldMap.containsKey(keyName)) {
					selectedSectionsFieldMap.put(keyName,
							new ArrayList<String>());					
				}
				if(!sectionPanelsMaps.containsKey(keyName)){
					SectionOrderPanel sop=new SectionOrderPanel(keyName,true);
					sop.setWidth("100%");
					sectionPanelsMaps.put(keyName, sop);
				}
				SectionOrderPanel sop=sectionPanelsMaps.get(keyName);
				sop.add(selectedVal);
				List<String> nameArray = selectedSectionsFieldMap
						.get(keyName);
				if (!nameArray.contains(selectedVal)) {
					nameArray.add(selectedVal);
					selectedSectionsFieldMap.put(keyName, nameArray);
				}
				checkBoxesMap.get(keyArr[i]).setValue(true);
				if (keyName.equals("Sections")) {
					mainSectionPanel.add(selectedVal);
				}
			}
		}
	}

	private void unSelectParentChildBoxes(String secname, String parent) {
		Set<String> keys = checkBoxesMap.keySet();
		String[] keyArr = keys.toArray(new String[0]);
		for (int i = 0; i < keyArr.length; i++) {
			if (keyArr[i].startsWith(secname)) {
				String keyName = "";
				String selectedVal = "";
				int lastIndex = keyArr[i].lastIndexOf("#");
				if (lastIndex != -1) {
					keyName = keyArr[i].substring(0, lastIndex);
					selectedVal = keyArr[i].substring(lastIndex + 1);
				} else {
					keyName = keyArr[i];
					selectedVal = keyArr[i];
				}
				if (selectedSectionsFieldMap.containsKey(keyName)) {
					SectionOrderPanel sop=sectionPanelsMaps.get(keyName);
					List<String> nameArray = selectedSectionsFieldMap
							.get(keyName);
					if (nameArray.contains(selectedVal)) {
						if(sop!=null){
							sop.remove(selectedVal);
						}
						nameArray.remove(selectedVal);
						if (nameArray.size() == 0) {
							selectedSectionsFieldMap.remove(keyName);
						} else {
							selectedSectionsFieldMap.put(keyName, nameArray);
						}
					}
				}
				if (keyName.equals("Sections")) {
					mainSectionPanel.remove(selectedVal);
				}
				checkBoxesMap.get(keyArr[i]).setValue(false);

			}
		}
	}

	class SectionOrderPanel extends Composite {
		protected VerticalPanel vPanel;
		protected HashMap<String, HorizontalPanel> itemsMap;
		protected String parentName;
		protected SectionOrderPanel subPanel;
		public SectionOrderPanel(String fullname,
				boolean displayHeader) {
			vPanel = new VerticalPanel();
			vPanel.setWidth("100%");
			initWidget(vPanel);
			parentName = fullname;
			if (displayHeader) {
				HorizontalPanel headerPanel = new HorizontalPanel();
				headerPanel
						.setVerticalAlignment(HasVerticalAlignment.ALIGN_MIDDLE);
				headerPanel.setWidth("100%");
				headerPanel.setHeight("20px");
				headerPanel.setStyleName(AppConstants.STYLE_TABLE_HEADER);
				Label header = new Label(fullname.replace("#", " --> "));
				
				Image closeImg = new Image(
						"resources/images/select_none.14X14.png");
				closeImg.getElement().getStyle().setCursor(Cursor.POINTER);
				final SectionOrderPanel sop = this;
				closeImg.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						sop.setVisible(false);

					}

				});

				headerPanel.add(header);
				headerPanel.add(closeImg);
				headerPanel.setCellWidth(header, "95%");
				headerPanel.setCellWidth(closeImg, "5%");

				vPanel.add(headerPanel);
			}
			itemsMap = new HashMap<String, HorizontalPanel>();
		}

		public void add(final String n) {
			if (!itemsMap.containsKey(n)) {
				this.setVisible(true);
				final HorizontalPanel hp = new HorizontalPanel();
				hp.setWidth("100%");
				hp.setHeight("20px");
				hp.setVerticalAlignment(HasVerticalAlignment.ALIGN_MIDDLE);
				Label lb = new Label(n.replace("_", ", "));
				lb.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						
						if (selectedSectionsFieldMap.containsKey(parentName
								+ "#" + n)) {
							if(subPanel!=null){
								int pos=orderPanel.getWidgetIndex(subPanel);
								if(pos!=-1)
									orderPanel.remove(pos);
							}
							subPanel = sectionPanelsMaps.get(parentName+"#"+n);
							subPanel.setVisible(true);
							orderPanel.add(subPanel);
							
						}
						else{
						}

					}

				});
				Image imgUp = new Image("resources/images/"
						+ AppConstants.ICON_UP);
				imgUp.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						int index = vPanel.getWidgetIndex(hp);
						if (index > 1) {
							vPanel.insert(hp, index - 1);
							updateStyles();
							List<String> list = selectedSectionsFieldMap
									.get(parentName);
							int i=list.indexOf(n);
							list.remove(i);
							list.add(i-1, n);
						}
					}
				});
				imgUp.getElement().getStyle().setCursor(Cursor.POINTER);
				Image imgDown = new Image("resources/images/"
						+ AppConstants.ICON_DOWN);
				imgDown.addClickHandler(new ClickHandler() {

					@Override
					public void onClick(ClickEvent arg0) {
						int index = vPanel.getWidgetIndex(hp);
						if (index < (vPanel.getWidgetCount() - 1)) {
							vPanel.insert(hp, index + 2);
							updateStyles();
							List<String> list = selectedSectionsFieldMap
							.get(parentName);
							int i=list.indexOf(n);
							list.remove(i);
							list.add(i+1, n);
						}
					}
				});
				imgDown.getElement().getStyle().setCursor(Cursor.POINTER);
				hp.add(lb);
				hp.add(imgUp);
				hp.add(imgDown);

				hp.setCellWidth(lb, "80%");
				hp.setCellWidth(imgUp, "10%");
				hp.setCellWidth(imgDown, "10%");
				if ((vPanel.getWidgetCount() % 2) == 1)
					hp.setStyleName(AppConstants.STYLE_TABLE_ROW);
				else
					hp.setStyleName(AppConstants.STYLE_TABLE_ROW_ALTERNATE);
				vPanel.add(hp);
				itemsMap.put(n, hp);
			}

		}

		private void updateStyles() {
			for (int i = 1; i < vPanel.getWidgetCount(); i++) {
				Widget w = vPanel.getWidget(i);
				if ((i % 2) == 1)
					w.setStyleName(AppConstants.STYLE_TABLE_ROW);
				else
					w.setStyleName(AppConstants.STYLE_TABLE_ROW_ALTERNATE);
			}
		}

		private void remove(String name) {
			HorizontalPanel hp = itemsMap.get(name);
			if(hp!=null){
				vPanel.remove(hp);
				itemsMap.remove(name);
			}
			if (itemsMap.size() == 0){
				this.setVisible(false);
				if(!parentName.equals("Sections"))
					removeFromParent();
			}
			else
				updateStyles();
			
		}
	}
	
	public HashMap<String, List<String>> getSelectedSectionFeildsMap(){
		return selectedSectionsFieldMap;
	}
	
	public void loadValues(HashMap<String, List<String>> vals){
		selectedSectionsFieldMap=vals;
		String [] keys=vals.keySet().toArray(new String[0]);
		for(int i=0;i<keys.length;i++){
			List<String> fields=vals.get(keys[i]);

			SectionOrderPanel sop=new SectionOrderPanel(keys[i],true);
			sop.setWidth("100%");
			
			if(keys[i].equals("Sections")){
				mainSectionPanel=sop;
				orderPanel.add(mainSectionPanel);
			}
			else{
				sectionPanelsMaps.put(keys[i], sop);
			}
			for(int j=0;j<fields.size();j++){
				
				String currKey=keys[i]+"#"+fields.get(j);
				try{
					CheckBox cb=checkBoxesMap.get(currKey);
					cb.setValue(true);
					sop.add(fields.get(j));
				}
				catch(Exception e){
					
				}
			}
		}
	}
}
