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

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.CustomCommand;
import org.freemedsoftware.gwt.client.CustomRequestCallback;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.i18n.AppConstants;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.VerticalPanel;

public class SignatureWidget {
	
	public static String MODULE_CLASS = "Signatures";
	
	protected Integer signatureId = null; 
	
	protected CustomCommand onSuccess;
	
	protected Integer patientId = null;
	
	protected String module 	= null;
	
	protected String moduleField= null;
	
	protected Integer oId 		= null;
	
	protected Integer jobId     = null;
	
	protected Image signatureImage = null;
	
	protected Image loadingImage   = null; 
	
	protected CustomDialogBox customDialogBox;
	
	protected Timer signatureRequestTimer;
	
	public static final String STATUS_COMPLETE = "COMPLETE";
	
	public static final String STATUS_ERROR    = "ERROR";
	
	public CustomButton confirmButton;
	
	public CustomButton takeSignatureButton;
	
	protected SignatureWidget() {
	}
	
	public SignatureWidget(Integer patientId,String module,String moduleField,Integer oid) {
		this.patientId 	 = patientId;
		this.module 	 = module;
		this.moduleField = moduleField;
		this.oId  		 = oid;
		initWidget();
	}

	public SignatureWidget(Integer patientId,String module,String moduleField) {
		this(patientId, module, moduleField, null);
	}
	
	public SignatureWidget(Integer patientId,String module,Integer oid) {
		this(patientId, module, null, oid);
	}

	public SignatureWidget(Integer patientId,String module) {
		this(patientId, module, null, null);
	}
	
	protected void initWidget(){
		customDialogBox = new CustomDialogBox();
		VerticalPanel mainPanel = new VerticalPanel();
		customDialogBox.setWidget(mainPanel);
		Label lebel = new Label("Please Sign on Electronic Signature Pad!");
		mainPanel.add(lebel);
		
		HorizontalPanel imagesHPanel = new HorizontalPanel();
		mainPanel.add(imagesHPanel);
		mainPanel.setCellHorizontalAlignment(imagesHPanel, HasHorizontalAlignment.ALIGN_CENTER);
		
		signatureImage = new Image(Util.getResourcesURL()+"no-image.jpg");
		signatureImage.setSize("300", "100");
		imagesHPanel.add(signatureImage);
		imagesHPanel.setCellHorizontalAlignment(signatureImage, HasHorizontalAlignment.ALIGN_CENTER);
		
		loadingImage = new Image(Util.getResourcesURL()+"loading.gif");
		loadingImage.setVisible(false);
		imagesHPanel.add(loadingImage);
		imagesHPanel.setCellHorizontalAlignment(loadingImage, HasHorizontalAlignment.ALIGN_CENTER);
		
		
		HorizontalPanel horizontalPanel = new HorizontalPanel();
		mainPanel.add(horizontalPanel);
		mainPanel.setCellHorizontalAlignment(horizontalPanel, HasHorizontalAlignment.ALIGN_CENTER);
		
		confirmButton = new CustomButton("Confirm",AppConstants.ICON_ADD);
		confirmButton.setVisible(false);
		horizontalPanel.add(confirmButton);
		
		confirmButton.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent event) {
				if(onSuccess!=null)
					onSuccess.execute(signatureId);
				hide();
			}
		
		});
		
		takeSignatureButton = new CustomButton("Take Signature",AppConstants.ICON_DONE);
		takeSignatureButton.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent event) {
				List paramsList = new ArrayList();
				paramsList.add("patient");
				paramsList.add(patientId);
				paramsList.add(module);
				if(moduleField!=null)
					paramsList.add(moduleField);
				if(oId!=null)
					paramsList.add(oId);
				Util.callApiMethod(MODULE_CLASS, "requestSignature", paramsList, new CustomRequestCallback() {
				
					@Override
					public void onError() {
						resetToDefault();
						Util.showErrorMsg(MODULE_CLASS, "Failed to get Signature, Try Again!!!!");
					}
				
					@Override
					public void jsonifiedData(Object data) {
						if(data!=null){
							HashMap<String,String> result = (HashMap<String,String>)data;
							jobId = Integer.parseInt(result.get("job_id"));
							signatureId = Integer.parseInt(result.get("signature_id"));
							signatureImage.setVisible(false);
							loadingImage.setVisible(true);
							takeSignatureButton.setEnabled(false);
							cancelSignatureRequest();
							startSignatureRequest(jobId);
						}
					}
				
				}, "HashMap<String,String>");
			}

		});
		horizontalPanel.add(takeSignatureButton);

		
		CustomButton cancelButton = new CustomButton("Cancel",AppConstants.ICON_CANCEL);
		cancelButton.addClickHandler(new ClickHandler() {
		
			@Override
			public void onClick(ClickEvent event) {
				resetToDefault();
				hide();
			}

		});
		horizontalPanel.add(cancelButton);
	}

	public void show(){
		customDialogBox.show();
	}
	
	public void hide(){
		customDialogBox.hide();	
	}
	
	public static void updateOid(Integer signatureId,Integer oid){
		if(signatureId!=null){
			if(oid!=null){
				List paramsList = new ArrayList();
				paramsList.add(signatureId);
				paramsList.add(oid);
				Util.callApiMethod(MODULE_CLASS, "updateOid", paramsList, new CustomRequestCallback() {
				
					@Override
					public void onError() {
						Util.showErrorMsg(MODULE_CLASS, "Failed to Saved Oid!!!");
					}
				
					@Override
					public void jsonifiedData(Object data) {
						if(data!=null && (Boolean)data )
							Util.showInfoMsg(MODULE_CLASS, "Oid Saved Successfully!!!");
						else
							Util.showErrorMsg(MODULE_CLASS, "Failed to Saved Oid!!!");
					}
				
				}, "Boolean");
			}
		}
	}
	
	public CustomCommand getOnSuccess() {
		return onSuccess;
	}

	public void setOnSuccess(CustomCommand onSuccess) {
		this.onSuccess = onSuccess;
	}

	public Integer getSignatureId() {
		return signatureId;
	}

	public void setSignatureId(Integer signatureId) {
		this.signatureId = signatureId;
	}
	
	public static void saveSignatureOids(Integer oid,List<Integer> signaturesIds){
		Iterator<Integer> itr = signaturesIds.iterator();
		while(itr.hasNext()){
			Integer id =  itr.next();
			SignatureWidget.updateOid(id, oid);
		}
	}

	public void cancelSignatureRequest() {
		if (signatureRequestTimer != null)
			signatureRequestTimer.cancel();
	}
	
	public void startSignatureRequest(final Integer jobID) {
		if (signatureRequestTimer == null) {
			JsonUtil.debug("SignatureRequest:start");
			signatureRequestTimer = new Timer() {
				public void run() {
					try {
						List paramsList = new ArrayList();
						paramsList.add(jobID);
						Util.callApiMethod(MODULE_CLASS, "getJobStatus", paramsList, new CustomRequestCallback() {
						
							@Override
							public void onError() {
								resetToDefault();
								Util.showErrorMsg(MODULE_CLASS, "Failed to get Signature!!!");
							}
						
							@Override
							public void jsonifiedData(Object data) {
								if(data!=null){
									String status = (String)data;
									if(status.equalsIgnoreCase(STATUS_COMPLETE)){
										showSignature();
									}else if(status.equalsIgnoreCase(STATUS_ERROR)){
										resetToDefault();
										Util.showErrorMsg(MODULE_CLASS, "Failed to get Signature!!!");		
									}
								}
							}
						
						}, "String");
					} catch (Exception e) {
						JsonUtil.debug("Exception : " + e.getMessage());
					}
					JsonUtil.debug("SignatureRequest:restart");
					signatureRequestTimer.schedule(10000);
				}
			};
			// Run initial polling ...
			signatureRequestTimer.run();
		} else {
			cancelSignatureRequest();
			signatureRequestTimer.run();
		}
	}

	protected void showSignature(){
					String[] params = { signatureId.toString() };
		signatureImage.setUrl(Util.getJsonRequest("org.freemedsoftware.api."
				+ MODULE_CLASS + ".GetSignatureImageById", params));
		resetToDefault();
		takeSignatureButton.setVisible(false);
		confirmButton.setVisible(true);

	}
	
	protected void resetToDefault(){
		signatureImage.setVisible(true);
		loadingImage.setVisible(false);
		takeSignatureButton.setEnabled(true);
		cancelSignatureRequest();
	}
	
}
