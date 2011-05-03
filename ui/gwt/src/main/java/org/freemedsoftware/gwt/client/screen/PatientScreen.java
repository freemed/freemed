/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2010 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.screen;

import java.util.HashMap;

import org.freemedsoftware.gwt.client.CurrentState;
import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.ScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.i18n.AppConstants;
import org.freemedsoftware.gwt.client.screen.patient.AdvancePayment;
import org.freemedsoftware.gwt.client.screen.patient.AllergyEntryScreen;
import org.freemedsoftware.gwt.client.screen.patient.DrugSampleEntry;
import org.freemedsoftware.gwt.client.screen.patient.EncounterScreen;
import org.freemedsoftware.gwt.client.screen.patient.EpisodeOfCareScreen;
import org.freemedsoftware.gwt.client.screen.patient.FormEntry;
import org.freemedsoftware.gwt.client.screen.patient.GrowthChartScreen;
import org.freemedsoftware.gwt.client.screen.patient.ImmunizationEntry;
import org.freemedsoftware.gwt.client.screen.patient.LetterEntry;
import org.freemedsoftware.gwt.client.screen.patient.PatientCorrespondenceEntry;
import org.freemedsoftware.gwt.client.screen.patient.PatientIdEntry;
import org.freemedsoftware.gwt.client.screen.patient.PatientLinkEntry;
import org.freemedsoftware.gwt.client.screen.patient.PatientReportingScreen;
import org.freemedsoftware.gwt.client.screen.patient.PrescriptionsScreen;
import org.freemedsoftware.gwt.client.screen.patient.ProcedureScreen;
import org.freemedsoftware.gwt.client.screen.patient.ProgressNoteEntry;
import org.freemedsoftware.gwt.client.screen.patient.ReferralEntry;
import org.freemedsoftware.gwt.client.screen.patient.ScannedDocumentsEntryScreen;
import org.freemedsoftware.gwt.client.screen.patient.SummaryScreen;
import org.freemedsoftware.gwt.client.screen.patient.VitalsEntry;
import org.freemedsoftware.gwt.client.widget.PatientInfoBar;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.logical.shared.SelectionEvent;
import com.google.gwt.event.logical.shared.SelectionHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Command;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.MenuBar;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.VerticalPanel;

public class PatientScreen extends ScreenInterface {

	public final static String moduleName = "emr";

	protected TabPanel tabPanel;

	protected PatientInfoBar patientInfoBar = null;

	protected Integer patientId = new Integer(0);

	protected String patientName = new String("");

	protected HashMap<String, String> patientInfo;

	protected SummaryScreen summaryScreen = null;

	protected String providerName;

	protected String providerId;

	protected Command onLoad = null;

	public PatientScreen() {
		super(moduleName);
		final VerticalPanel verticalPanel = new VerticalPanel();
		initWidget(verticalPanel);
		verticalPanel.setSize("100%", "100%");

		patientInfoBar = new PatientInfoBar();
		patientInfoBar.setParentScreen(this);
		verticalPanel.add(patientInfoBar);

		{
			final MenuBar menuBar = new MenuBar();
			verticalPanel.add(menuBar);

			boolean menuItemAdded = false;

			final MenuBar menuBar_1 = new MenuBar(true);
			if (CurrentState.isActionAllowed(AllergyEntryScreen.moduleName,
					AppConstants.WRITE)) {
				menuBar_1.addItem("Allergy", new Command() {
					public void execute() {
						AllergyEntryScreen allergyEntryScreen = new AllergyEntryScreen();
						Util.spawnTabPatient("Allergy", allergyEntryScreen,
								getObject());
						allergyEntryScreen.populate();
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Drug Sample", new Command() {
					public void execute() {
						Util.spawnTabPatient("Drug Sample",
								new DrugSampleEntry(), getObject());
					}
				});
				menuItemAdded = true;
			}
			if (true) {
				menuBar_1.addItem("Episode of Care", new Command() {
					public void execute() {
						EpisodeOfCareScreen eoc = new EpisodeOfCareScreen();
						Util.spawnTabPatient("Episode of Care", eoc,
								getObject());
						eoc.loadData();
					}
				});
				menuItemAdded = true;
			}
			if (true) {
				menuBar_1.addItem("Encounter/Progress Notes", new Command() {
					public void execute() {
						EncounterScreen ens = new EncounterScreen();
						Util.spawnTabPatient("Encounter/Progress Notes", ens,
								getObject());
						ens.laodData();
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Foreign ID", new Command() {
					public void execute() {
						Util.spawnTabPatient("Foreign ID",
								new PatientIdEntry(), getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Form", new Command() {
					public void execute() {
						Util.spawnTabPatient("Form", new FormEntry(),
								getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Immunization", new Command() {
					public void execute() {
						Util.spawnTabPatient("Immunization",
								new ImmunizationEntry(), getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Letter", new Command() {
					public void execute() {
						Util.spawnTabPatient("Letter", new LetterEntry(),
								getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Patient Correspondence", new Command() {
					public void execute() {
						Util.spawnTabPatient("Patient Correspondence",
								new PatientCorrespondenceEntry(), getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Patient Link", new Command() {
					public void execute() {
						Util.spawnTabPatient("Patient Link",
								new PatientLinkEntry(), getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Progress Note", new Command() {
					public void execute() {
						Util.spawnTabPatient("Progress Note",
								new ProgressNoteEntry(), getObject());
					}
				});
				menuItemAdded = true;
			}

			if (CurrentState.isActionAllowed(PrescriptionsScreen.moduleName,
					AppConstants.WRITE)) {
				menuBar_1.addItem("Prescription", new Command() {
					public void execute() {
						Util.spawnTabPatient("Prescription",
								new PrescriptionsScreen(), getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Referral", new Command() {
					public void execute() {
						Util.spawnTabPatient("Referral", new ReferralEntry(),
								getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Vitals", new Command() {
					public void execute() {
						Util.spawnTabPatient("Vitals", new VitalsEntry(),
								getObject());
					}
				});
				menuItemAdded = true;
			}

			if (true) {
				menuBar_1.addItem("Scanned Documents", new Command() {
					public void execute() {
						ScannedDocumentsEntryScreen scannedDocumentsEntryScreen = new ScannedDocumentsEntryScreen();
						Util.spawnTabPatient("Scanned Documents",
								scannedDocumentsEntryScreen, getObject());
						scannedDocumentsEntryScreen.populateAvailableData();
					}
				});
				menuItemAdded = true;
			}
			if (menuItemAdded)
				menuBar.addItem("New", menuBar_1);
			menuItemAdded = false;
			final MenuBar menuBar_2 = new MenuBar(true);
			
            if(CurrentState.isActionAllowed(ProcedureScreen.moduleName, AppConstants.SHOW)){
                menuBar_2.addItem("Manage Procedures", new Command() {
                        public void execute() {
                                ProcedureScreen ps =  new ProcedureScreen();
                                Util.spawnTabPatient("Manage Procedures", ps, getObject());
                                ps.loadData();
                        }
                });
                menuItemAdded = true;
            }
            
            if(true){
                menuBar_2.addItem("Payment", new Command() {
                        public void execute() {
                                AdvancePayment ap =  new AdvancePayment();
                                Util.spawnTabPatient("Payment", ap, getObject());
                                ap.loadUI();
                        }
                });
                menuItemAdded = true;
            }

			
			if (CurrentState.isActionAllowed(PatientReportingScreen.moduleName,
					AppConstants.SHOW)) {
				menuBar_2.addItem("Patient Reporting", new Command() {
					public void execute() {
						Util.spawnTabPatient("Patient Reporting",
								new PatientReportingScreen(), getObject());
					}
				});
				menuItemAdded = true;
			}

			menuBar_2.addItem("Billing/Payment", (Command) null);

			if (true) {
				menuBar_2.addItem("Growth Charts", new Command() {
					public void execute() {
						GrowthChartScreen growthChartScreen = new GrowthChartScreen();
						JsonUtil.debug("Calling growth charts with ptsex = " + patientInfo.get("ptsex"));
						growthChartScreen.setGender(patientInfo.get("ptsex"));
						growthChartScreen.setBirthDate(Util
								.sqlToDate(patientInfo.get("ptdob")));
						growthChartScreen.init();
						Util.spawnTabPatient("Growth Charts",
								growthChartScreen, getObject());
					}
				});
				menuItemAdded = true;
			}

			menuBar_2.addItem("Trending", (Command) null);

			menuBar.addItem("Reporting", menuBar_2);
		}

		final VerticalPanel verticalPanel_1 = new VerticalPanel();
		verticalPanel.add(verticalPanel_1);
		verticalPanel_1.setSize("100%", "100%");

		tabPanel = new TabPanel();
		verticalPanel_1.add(tabPanel);
		summaryScreen = new SummaryScreen();
		tabPanel.add(summaryScreen, "Summary");
		summaryScreen.assignPatientScreen(getObject());
		addChildWidget(summaryScreen);
		tabPanel.selectTab(0);

		tabPanel.addSelectionHandler(new SelectionHandler<Integer>() {
			@Override
			public void onSelection(SelectionEvent<Integer> arg0) {
				if (tabPanel.getWidget(arg0.getSelectedItem()) instanceof ScreenInterface) {
					ScreenInterface screenInterface = ((ScreenInterface) tabPanel
							.getWidget(arg0.getSelectedItem()));
					String className = screenInterface.getClass().getName();
					className = className
							.substring(className.lastIndexOf('.') + 1);
					CurrentState.assignCurrentPageHelp(className);
				}
			}
		});

	}

	/**
	 * Get patient tab panel.
	 * 
	 * @return
	 */
	public TabPanel getTabPanel() {
		return tabPanel;
	}

	/**
	 * Get reference to internal object.
	 * 
	 * @return
	 */
	protected PatientScreen getObject() {
		return this;
	}

	/**
	 * Set patient id.
	 */
	public void setPatient(Integer id) {
		patientId = id;
	}

	/**
	 * Get patient id.
	 * 
	 * @return Integer of patient id.
	 */
	public Integer getPatient() {
		return patientId;
	}

	/**
	 * Get provider name.
	 * 
	 * @return Integer of patient id.
	 */

	public String getProviderName() {
		return providerName;
	}

	public PatientInfoBar getPatientInfoBar() {
		return patientInfoBar;
	}

	/**
	 * Set <Command> to be run upon data population.
	 * 
	 * @param onLoad
	 */
	public void setOnLoad(Command onLoad) {
		this.onLoad = onLoad;
	}

	public void populate() {
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			HashMap<String, String> dummy = new HashMap<String, String>();
			dummy.put("patient_name", "Hackenbush, Hugo Z");
			dummy.put("id", patientId.toString());
			dummy.put("patient_id", "HUGO01");
			dummy.put("ptdob", "1979-08-10");
			dummy.put("address_line_1", "101 Evergreen Terrace");
			dummy.put("address_line_2", "");
			dummy.put("csz", "N Kilt Town, IL 00000");
			dummy.put("pthphone", "8005551212");
			dummy.put("ptwphone", "860KL51212");
			populatePatientInformation(dummy);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.PatientInterface.PatientInformation",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Util.showErrorMsg("Patientscreen",
								"Failed to retrieve patient information.");
					}

					@SuppressWarnings("unchecked")
					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							HashMap<String, String> r = (HashMap<String, String>) JsonUtil
									.shoehornJson(JSONParser.parse(response
											.getText()),
											"HashMap<String,String>");
							if (r != null) {
								populatePatientInformation(r);
							}
						} else {
							Util.showErrorMsg("Patientscreen",
									"Failed to retrieve patient information.");
						}
					}
				});
			} catch (RequestException e) {
				Util.showErrorMsg("Patientscreen",
						"Failed to retrieve patient information.");
			}
		} else {
			// Set off async method to get information
			PatientInterfaceAsync service = null;
			try {
				service = (PatientInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface");
			} catch (Exception e) {
				GWT.log("Exception caught: ", e);
			}
			service.PatientInformation(patientId,
					new AsyncCallback<HashMap<String, String>>() {
						public void onSuccess(HashMap<String, String> pInfo) {
							populatePatientInformation(pInfo);
						}

						public void onFailure(Throwable t) {
							GWT.log("FAILURE: ", t);
							Util.showErrorMsg("Patientscreen",
									"Failed to retrieve patient information.");
						}
					});
		}

	}

	/**
	 * 
	 * @param info
	 */
	protected void populatePatientInformation(HashMap<String, String> info) {
		// Store this in the object
		patientInfo = info;

		// Push out to child widgets
		patientInfoBar.setPatientFromMap(patientInfo);
		summaryScreen.setPatientId(patientId);
		patientName = info.get("patient_name");
		providerName = info.get("pcp");
		providerId = info.get("pcpid");
		summaryScreen.loadData();

		if (onLoad != null) {
			onLoad.execute();
		}
	}

	protected PatientScreen getPatientScreen() {
		return this;
	}

	@Override
	public void closeScreen() {
		// TODO Auto-generated method stub
		super.closeScreen();
		Integer patientId = getPatient();
		CurrentState.getPatientScreenMap().remove(patientId);
		CurrentState.getPatientSubScreenMap().remove(patientId);
	}

	public String getPatientName() {
		return patientName;
	}

	public SummaryScreen getSummaryScreen() {
		return summaryScreen;
	}
}
