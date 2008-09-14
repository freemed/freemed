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

package org.freemedsoftware.gwt.client;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.Api.ModuleInterfaceAsync;
import org.freemedsoftware.gwt.client.widget.SimpleUIBuilder;
import org.freemedsoftware.gwt.client.widget.Toaster;

import com.google.gwt.core.client.GWT;
import com.google.gwt.user.client.rpc.AsyncCallback;

public abstract class EntryScreenInterface extends ScreenInterface implements
		SimpleUIBuilder.Receiver {

	protected String moduleName;

	protected Integer internalId = 0;

	protected SimpleUIBuilder ui = new SimpleUIBuilder();

	protected abstract void buildForm();

	/**
	 * Override to set the module name used.
	 * 
	 * @return The FreeMED module class name implemented by this class.
	 */
	protected abstract String getModuleName();

	/**
	 * Set the internal id associated with this form.
	 * 
	 * @param id
	 */
	public void setInternalId(Integer id) {
		ModuleInterfaceAsync service = null;
		try {
			service = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		} finally {
			internalId = id;
			if (Util.isStubbedMode()) {
				// TODO: Emulate stubbed mode
			} else {
				service.ModuleGetRecordMethod(moduleName, id,
						new AsyncCallback<HashMap<String, String>>() {
							@Override
							public void onSuccess(HashMap<String, String> r) {
								ui.setValues(r);
							}

							@Override
							public void onFailure(Throwable t) {
								GWT.log("Exception", t);
							}
						});
			}
		}
	}

	@Override
	public void processData(HashMap<String, String> data) {
		if (internalId.intValue() > 0) {
			data.put("id", internalId.toString());
		}
		ModuleInterfaceAsync service = null;
		try {
			service = (ModuleInterfaceAsync) Util
					.getProxy("org.freemedsoftware.gwt.client.Api.ModuleInterface");
		} catch (Exception ex) {
			GWT.log("Exception", ex);
		} finally {
			if (internalId.intValue() == 0) {
				// Add record
				if (Util.isStubbedMode()) {
					state.getToaster().addItem(moduleName,
							"Added successfully.", Toaster.TOASTER_INFO);
					closeScreen();
				} else {
					service.ModuleAddMethod(moduleName, data,
							new AsyncCallback<Integer>() {
								@Override
								public void onSuccess(Integer r) {
									state.getToaster().addItem(moduleName,
											"Added successfully.",
											Toaster.TOASTER_INFO);
									closeScreen();
								}

								@Override
								public void onFailure(Throwable t) {
									GWT.log("Exception", t);
								}
							});
				}
			} else {
				// Modify record
				if (Util.isStubbedMode()) {
					state.getToaster().addItem(moduleName,
							"Modified successfully.", Toaster.TOASTER_INFO);
					closeScreen();
				} else {
					service.ModuleModifyMethod(moduleName, data,
							new AsyncCallback<Integer>() {
								@Override
								public void onSuccess(Integer r) {
									state.getToaster().addItem(moduleName,
											"Modified successfully.",
											Toaster.TOASTER_INFO);
									closeScreen();
								}

								@Override
								public void onFailure(Throwable t) {
									GWT.log("Exception", t);
								}
							});
				}
			}
		}

	}

}
