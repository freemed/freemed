/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
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

package org.freemedsoftware.gwt.client.i18n;

import com.google.gwt.i18n.client.Constants;

public interface AppConstants extends Constants {

	/*
	 * These variables define total number of tabs that can be opened after
	 * clicking the related option
	 */

	// Settings for options available under Main Left Panel
	public static int MAX_MESSAGNING_TABS = 1;
	public static int MAX_SCHEDULER_TABS = 1;
	public static int MAX_CONFIGURATION_TABS = 1;

	// Settings for options available under Patients Left Panel
	public static int MAX_SEARCH_TABS = 1;
	public static int MAX_NEWPATIENT_TABS = 3;
	public static int MAX_PATIENTSGROUP_TABS = 1;
	public static int MAX_CALLIN_TABS = 1;
	public static int MAX_RXREFILL_TABS = 1;
	public static int MAX_TAGSEARCH_TABS = 1;
	// Settings for options available under Documents Left Panel
	public static int MAX_UNFILLED_TABS = 1;
	public static int MAX_UNREAD_TABS = 1;

	// Settings for options available under Dosing Left Panel
	public static int MAX_MED_INVENTORY_TABS = 1;
	public static int MAX_OPEN_DOSING_STATION_TABS = 1;
	public static int MAX_DISPENSE_DOSE_TABS = 1;

	// Settings for options available under Billing Left Panel
	public static int MAX_CLAIMSMANAGER_TABS = 1;

	// Settings for options available under Reporting Left Panel
	public static int MAX_REPORTING_TABS = 1;

	// Settings for options available under Utilities Left Panel
	public static int MAX_UTILITIES_TABS = 1;
	public static int MAX_SUPPORTDATA_TABS = 1;
	public static int MAX_USERMANAGEMENT_TABS = 1;
	public static int MAX_ACL_TABS = 1;

	// Top header
	public static int MAX_PREFERENCES_TABS = 1;
	public static int MAX_ACCOUNTRECIEVABLE_TABS = 1;

	/*
	 * These following variables define Left Navigation Labels w.r.t Category
	 */
	// System Category
	public final static String SYSTEM_CATEGORY = "System";
	public final static String DASHBOARD = "Dashboard";
	public final static String SCHEDULER = "Scheduler";
	public final static String MESSAGES = "Messages";

	// Patient Category
	public final static String PATIENT_CATEGORY = "Patient";
	public final static String SEARCH = "Search";
	public final static String NEW_PATIENT = "New Patient";
	public final static String GROUPS = "Groups";
	public final static String CALL_IN = "Call In";
	public final static String RX_REFILL = "Rx Refill";
	public final static String TAG_SEARCH = "Tag Search";

	// Documents Category
	public final static String DOCUMENTS_CATEGORY = "Documents";
	public final static String UNFILED = "Unfiled";
	public final static String UNREAD = "Unread";

	// Dosing Category
	public final static String DOSING_MENU_CATEGORY = "Dosing Menu";
	public final static String MEDICATION_INVENTORY = "Medication Inventory";
	public final static String OPEN_DOSING_STATION = "Open Dosing Station";
	public final static String DISPENSE_DOSE = "Dispense Dose";

	// Billing Category
	public final static String BILLING_CATEGORY = "Billing";
	public final static String ACCOUNT_RECEIVABLE = "Account Receivable";
	public final static String CLAIMS_MANAGER = "Claims Manager";
	public final static String REMITT_BILLING = "Remitt Billing";
	public final static String SUPER_BILLS = "Super Bills";

	// Reporting Category
	public final static String REPORTING_CATEGORY = "Reporting";
	public final static String REPORTING_ENGINE = "Reporting Engine";

	// Utilities Category
	public final static String UTILITIES_CATEGORY = "Utilities";
	public final static String UTILITIES_SCREEN = "Utilities";
	public final static String SUPPORT_DATA = "Support Data";
	public final static String USER_MANAGEMENT = "User Management";
	public final static String SYSTEM_CONFIGURATION = "System Configuration";
	public final static String DB_ADMINISTRATION = "DB Administration";
	public final static String ACL = "ACL";
	// /////////////////////////////End Left Navigation/////////////////////////
	
	/////////////////////////////Permissions Constants//////////////////////////
	public static final int READ   =1;
	public static final int WRITE  =2;
	public static final int MODIFY =3;
	public static final int DELETE =4;
	public static final int SHOW   =5;
	/////////////////////////////End Permissions Constants//////////////////////////
	
	/*
	 * These variables define total number of tabs that can be opened after
	 * clicking the related Menu option in PatientScreen.
	 */

	// Settings for options available under New Menu
	public static int MAX_PATIENT_ALLERGY_TABS = 1;
	public static int MAX_PATIENT_DRUG_SAMPLE_TABS = 1;
	public static int MAX_PATIENT_ENCOUNTER_TABS = 1;
	public static int MAX_PATIENT_FOREIGNID_TABS = 1;
	public static int MAX_PATIENT_FORM_TABS = 1;
	public static int MAX_PATIENT_IMMUNIZATION_TABS = 1;
	public static int MAX_PATIENT_LETTER_TABS = 1;
	public static int MAX_PATIENT_CORRECPONDENCE_TABS = 1;
	public static int MAX_PATIENT_LINK_TABS = 1;
	public static int MAX_PATIENT_PROGRESS_NOTE_TABS = 1;
	public static int MAX_PATIENT_PRESCRIPTOIN_TABS = 1;
	public static int MAX_PATIENT_REFFERRAL_TABS = 1;

	// Settings for options available under Reporting Menu
	public static int MAX_PATIENT_REPORTING_TABS = 1;
	public static int MAX_BILLING_TABS = 3;
	public static int MAX_TRENDING_TABS = 1;
	// Settings for options available under DOSING Menu
	public static int MAX_PATIENT_DOSE_PLAN_TABS = 1;
	public static int MAX_PATIENT_DOSE_HOLD_TABS = 1;
	public static int MAX_PATIENT_DOSING_MESSAGE_TABS = 1;

	// Settings for options available under TREATMENT PLAN Menu
	public static int MAX_PATIENT_INITIAL_CONTACT_TABS = 1;

	//////////////////////////////////////////////////////////////////////////////
	
}
