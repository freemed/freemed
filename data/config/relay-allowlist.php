<?php
 // $Id$
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// File: data/config/relay-allowlist.php
//
//	The relay call set (Task 2.8). The matcher is
//	lib/org/freemedsoftware/core/Relay_Allowlist.class.php; this file is the
//	list, and it is DATA on purpose so a deployment can extend its call set
//	without patching code.
//
//	Relay::handle_request() consults this list after the LoggedIn guard and
//	before dispatch, so both providers (Json, Xmlrpc) inherit it. A method that
//	is not named here is a MISS.
//
// ============================ READ THIS FIRST ==============================
//
//	'enforce' => false   is the SHIPPED DEFAULT, and it is deliberate.
//
//	false  = LOG-ONLY.  A miss is logged at LOG_WARNING (method, remote address,
//	         and whether it was refused) and the call STILL EXECUTES. Nothing
//	         can break because of a missing entry.
//	true   = ENFORCING. A miss is logged AND answered with INVALID_CALL. The
//	         method does not run.
//
//	This list was built from the best enumeration this tree can produce
//	(tests/security/evidence/relay-callset.txt - per-pattern provenance). It is
//	NOT the same thing as a real client smoke pass: there is no compiled GWT
//	client in this checkout, so no browser session has ever exercised this call
//	set. Turning enforcement on without running the procedure below is how a
//	security control becomes a production outage.
//
// ======================== HOW TO TURN ENFORCEMENT ON ========================
//
//	1. Deploy with 'enforce' => false (as shipped). Use the system normally:
//	   every screen, report, print, export and the installer, on the real
//	   compiled client, for long enough to cover the workflows this site uses.
//
//	2. Collect the misses from syslog:
//	       grep "Relay: method" /var/log/syslog
//	   Each line names one method this list does not cover. Add it below
//	   (copy the string from the log line exactly; matching is
//	   case-insensitive for the method name, so its case does not have to be
//	   right).
//
//	   A config edit is NOT instant, and the lag is PER WORKER: PHP caches this
//	   file through OPcache, so with the default opcache.revalidate_freq = 2 a
//	   worker can still serve the previous value for a moment - and two workers
//	   CAN DISAGREE, so ONE probe is not evidence. Reload PHP (or wait past
//	   opcache.revalidate_freq on every worker) and probe MORE THAN ONCE, before
//	   believing the running server agrees with the file you just edited.
//	   (Measured on the verification stack: a single-worker probe produced a
//	   false failure AND a false pass before the reload-per-edit remedy below
//	   was applied.)
//
//	3. When the log is quiet, set 'enforce' => true. Keep this file in version
//	   control from then on: a missing file disables enforcement (fail-open,
//	   logged) rather than refusing every call, and so does a file that cannot be
//	   parsed (a hand-editing typo here is caught and reported, not fatal).
//
//	If a call is refused that should have been allowed, the fix is to add a
//	pattern - never to remove the check, and never to widen a pattern to the
//	whole tree.
//
//	A trailing `*` on a pattern matches any method string with that prefix, and
//	it can be written at three widths:
//
//	    org.freemedsoftware.module.SomeModule.*     a CLASS    - every method of
//	                                                 that one class
//	    org.freemedsoftware.module.SomeModule.Get*   a PREFIX  - every method name
//	                                                 beginning `Get`
//	    org.freemedsoftware.module.*                 a NAMESPACE - EVERY method of
//	                                                 EVERY class under it
//
//	The NAMESPACE form is ACCEPTED, and it is the widest thing this control
//	honours: it grants every method of every class under that namespace. It is
//	never accepted in SILENCE - Relay_Allowlist logs it at LOG_WARNING and names
//	how many method declarations it covers, because a wildcard accepted with no
//	log line cannot be told apart from the control being off. Prefer the CLASS
//	form. The seed below uses no `*` at all: every entry is a resolved method
//	string.
//
//	Patterns BROADER than the above are REJECTED at load with a LOG_WARNING and do
//	not take effect: a bare `*`, org.freemedsoftware.*, org.freemedsoftware, and a
//	bare namespace with no trailing `*` such as org.freemedsoftware.api. So is a
//	`*` anywhere but the end (a wildcard the matcher does not implement), and so is
//	an exact pattern that is not exactly org.freemedsoftware.<ns>.<Class>.<Method>
//	(a deeper spelling names no relay method and would match nothing); see
//	Relay_Allowlist::is_too_broad(). tests/security/relay_allowlist.test.php pins
//	each of these boundaries.
//
//	See doc/RELAY_ALLOWLIST for the operator notes and the release note.
//
// ================================ THE SEED =================================
//
//	Every pattern below appears in tests/security/evidence/relay-callset.txt, with
//	its source. That enumeration was generated against the tree at commit
//	2a55da8f from five inputs (its header names them): the measured probe sets,
//	the stack's Apache access log, the shipped GWT service map, the public
//	namespace's call sites, and the resolved client/UI source literals.
//	tests/security/relay_allowlist.test.php enforces that correspondence, so this
//	list cannot drift away from its evidence.
//
//	Four known-but-unreachable client strings are included with the seed:
//		org.freemedsoftware.api.Authorizations.Replace
//		org.freemedsoftware.api.Scheduler.GetDailyAppointmentRange
//		org.freemedsoftware.module.PatientTag.GetTemplate
//		org.freemedsoftware.module.RemittBillingTransport.GetReport
//	The shipped GWT service map emits these; none names a public PHP method, so
//	dispatch fails exactly as it does today. They are listed so that flipping
//	enforcement cannot turn "fails the same way" into "refused".
//
//	Two measured strings are deliberately NOT here, because they name no method
//	of their class or any parent and refusing them is strictly better than what
//	they do today (a TypeError out of call_user_func_array):
//		org.freemedsoftware.module.EncounterNotesTemplate.GetList
//		org.freemedsoftware.public.Login.NotARealMethod

return array (

	// ---------------------------------------------------------------------
	// 'enforce' - see HOW TO TURN ENFORCEMENT ON above.
	// ---------------------------------------------------------------------
	'enforce' => false,

	// ---------------------------------------------------------------------
	// 'patterns' - exact relay method strings, and `*` suffix patterns.
	//   live-probe 50 | access-log 9 | gwtphpmap 112 | public-namespace 6 | source-mine 146
	// ---------------------------------------------------------------------
	'patterns' => array (

		// ---- live-probe (50): methods this mitigation has exercised against the ----------
		// running server. The 2.2-2.5 repro plus the batch-B / batch-C / 2.6f probe sets.
		'org.freemedsoftware.api.Ledger.collection_warning',
		'org.freemedsoftware.api.Ledger.queue_for_rebill',
		'org.freemedsoftware.api.Ledger.WriteoffItems',
		'org.freemedsoftware.api.ModuleSearch.picklist',
		'org.freemedsoftware.api.PatientInterface.Picklist',
		'org.freemedsoftware.api.Remitt.RenderStatementXML',
		'org.freemedsoftware.api.Scheduler.date_add',
		'org.freemedsoftware.api.Scheduler.FindDateAppointments',
		'org.freemedsoftware.api.Scheduler.FindGroupAppointments',
		'org.freemedsoftware.api.Scheduler.FindGroupAppointmentsDates',
		'org.freemedsoftware.api.Scheduler.GetDailyAppointmentScheduler',
		'org.freemedsoftware.api.Scheduler.GetDailyAppointmentsRange',
		'org.freemedsoftware.api.UserInterface.GetUsers',
		'org.freemedsoftware.module.Allergies.GetList',
		'org.freemedsoftware.module.Allergies.GetRecentRecord',
		'org.freemedsoftware.module.Allergies.picklist',
		'org.freemedsoftware.module.Authorizations.getActionItems',
		'org.freemedsoftware.module.CalendarGroup.GetDetailedRecord',
		'org.freemedsoftware.module.Callin.GetAll',
		'org.freemedsoftware.module.Callin.GetDetailedRecord',
		'org.freemedsoftware.module.Callin.GetDetailedRecordWithIntake',
		'org.freemedsoftware.module.ClinicRegistration.GetAll',
		'org.freemedsoftware.module.EncounterNotesTemplate.getTemplates',
		'org.freemedsoftware.module.EpisodeOfCare.getAllValues',
		'org.freemedsoftware.module.FacilityModule.GetAll',
		'org.freemedsoftware.module.i18nLanguages.GetAll',
		'org.freemedsoftware.module.i18nLanguages.GetRecord',
		'org.freemedsoftware.module.i18nLanguages.GetRecords',
		'org.freemedsoftware.module.ModuleFieldCheckerType.getModuleInfo',
		'org.freemedsoftware.module.PatientCoverages.GetCoverages',
		'org.freemedsoftware.module.PaymentModule.getLastRecord',
		'org.freemedsoftware.module.PhoneNumbers.GetRecentNumber',
		'org.freemedsoftware.module.PhoneNumbers.GetTypeNumber',
		'org.freemedsoftware.module.ProcedureModule.getPatientProcHistory',
		'org.freemedsoftware.module.ProgressNotes.NoteForDate',
		'org.freemedsoftware.module.ProviderModule.internalPicklist',
		'org.freemedsoftware.module.Referrals.GetAllActiveByPatient',
		'org.freemedsoftware.module.ScannedDocuments.GetPatientAllRecords',
		'org.freemedsoftware.module.SuperBill.GetForDates',
		'org.freemedsoftware.module.SuperBill.GetSuperbill',
		'org.freemedsoftware.module.UnfiledDocuments.GetAll',
		'org.freemedsoftware.module.UnfiledDocuments.GetCount',
		'org.freemedsoftware.module.UnreadDocuments.GetAll',
		'org.freemedsoftware.module.UserGroups.GetRecords',
		'org.freemedsoftware.module.UserPreferences.GetAll',
		'org.freemedsoftware.module.UserPreferences.GetConfigSections',
		'org.freemedsoftware.module.Vitals.GetRecentRecord',
		'org.freemedsoftware.module.WorkflowStatus.StatusMapForDate',
		'org.freemedsoftware.module.Zipcodes.CityStateZipPicklist',
		'org.freemedsoftware.public.Login.LoggedIn',

		// ---- access-log (9): methods the stack's Apache access log shows being ----------
		// requested, which is the source ruling R26.2(c) asked for. The access log keeps
		// the method in the request line, so it can name a call the source trees and the
		// curated live-probe list both missed - it is how PaymentModule.GetLedger (34
		// requests), Vitals.locked (21) and Vitals.RenderHtmlView (19) were found. See
		// tests/security/evidence/relay-accesslog.txt for the capture and its cutoff.
		'org.freemedsoftware.api.UserInterface.GetRecords',
		'org.freemedsoftware.module.Callin.GetAllWithInsurance',
		'org.freemedsoftware.module.EncounterNotesTemplate.getTemplateInfo',
		'org.freemedsoftware.module.i18nLanguages.del',
		'org.freemedsoftware.module.i18nLanguages.picklist',
		'org.freemedsoftware.module.PaymentModule.GetLedger',
		'org.freemedsoftware.module.UpdatesModule.GetFeed',
		'org.freemedsoftware.module.Vitals.locked',
		'org.freemedsoftware.module.Vitals.RenderHtmlView',

		// ---- gwtphpmap (112): the shipped GWT service map's client->relay table ------------
		// lib/org/freemedsoftware/gwt/**/*.gwtphpmap.inc.php, `mappedBy` + `mappedName`.
		// The strongest compiled-client evidence available here.
		'org.freemedsoftware.api.Authorizations.find_by_coverage',
		'org.freemedsoftware.api.Authorizations.get_authorization',
		'org.freemedsoftware.api.Authorizations.Replace',
		'org.freemedsoftware.api.Authorizations.use_authorization',
		'org.freemedsoftware.api.Authorizations.Valid',
		'org.freemedsoftware.api.Authorizations.valid_set',
		'org.freemedsoftware.api.Messages.Get',
		'org.freemedsoftware.api.Messages.ListOfUsers',
		'org.freemedsoftware.api.Messages.recipients_to_text',
		'org.freemedsoftware.api.Messages.Remove',
		'org.freemedsoftware.api.Messages.Send',
		'org.freemedsoftware.api.Messages.TagModify',
		'org.freemedsoftware.api.Messages.view_per_patient',
		'org.freemedsoftware.api.Messages.view_per_user',
		'org.freemedsoftware.api.ModuleInterface.ModuleAddMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleDeleteMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleGetRecordMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleGetRecordsMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleModifyMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleSupportPicklistMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleToTextMethod',
		'org.freemedsoftware.api.ModuleInterface.PrintToFax',
		'org.freemedsoftware.api.ModuleInterface.PrintToPrinter',
		'org.freemedsoftware.api.PatientInterface.CheckForDuplicatePatient',
		'org.freemedsoftware.api.PatientInterface.DxForPatient',
		'org.freemedsoftware.api.PatientInterface.EmrAttachmentsByPatient',
		'org.freemedsoftware.api.PatientInterface.EmrAttachmentsByPatientTable',
		'org.freemedsoftware.api.PatientInterface.EmrModules',
		'org.freemedsoftware.api.PatientInterface.MoveEmrAttachments',
		'org.freemedsoftware.api.PatientInterface.NumericSearch',
		'org.freemedsoftware.api.PatientInterface.PatientInformation',
		'org.freemedsoftware.api.PatientInterface.ProceduresToBill',
		'org.freemedsoftware.api.PatientInterface.Search',
		'org.freemedsoftware.api.PatientInterface.TotalInSystem',
		'org.freemedsoftware.api.PatientInterface.ToText',
		'org.freemedsoftware.api.Scheduler.CopyAppointment',
		'org.freemedsoftware.api.Scheduler.CopyGroupAppointment',
		'org.freemedsoftware.api.Scheduler.GetAppointment',
		'org.freemedsoftware.api.Scheduler.GetDailyAppointmentRange',
		'org.freemedsoftware.api.Scheduler.GetDailyAppointments',
		'org.freemedsoftware.api.Scheduler.ImportDate',
		'org.freemedsoftware.api.Scheduler.MoveAppointment',
		'org.freemedsoftware.api.Scheduler.MoveGroupAppointment',
		'org.freemedsoftware.api.Scheduler.next_available',
		'org.freemedsoftware.api.Scheduler.set_recurring_appointment',
		'org.freemedsoftware.api.Scheduler.SetAppointment',
		'org.freemedsoftware.api.Scheduler.SetGroupAppointment',
		'org.freemedsoftware.api.SystemConfig.GetAll',
		'org.freemedsoftware.api.SystemConfig.GetConfigSections',
		'org.freemedsoftware.api.SystemConfig.GetValue',
		'org.freemedsoftware.api.SystemConfig.SetValue',
		'org.freemedsoftware.api.SystemConfig.SetValues',
		'org.freemedsoftware.api.TableMaintenance.ExportStockData',
		'org.freemedsoftware.api.TableMaintenance.ExportTables',
		'org.freemedsoftware.api.TableMaintenance.GetModules',
		'org.freemedsoftware.api.TableMaintenance.GetModuleTables',
		'org.freemedsoftware.api.TableMaintenance.ImportStockData',
		'org.freemedsoftware.api.Tickler.Call',
		'org.freemedsoftware.api.UserInterface.del',
		'org.freemedsoftware.api.UserInterface.GetCurrentUsername',
		'org.freemedsoftware.api.UserInterface.GetNewMessages',
		'org.freemedsoftware.api.UserInterface.GetRecord',
		'org.freemedsoftware.api.UserInterface.mod',
		'org.freemedsoftware.api.UserInterface.SetConfigValue',
		'org.freemedsoftware.module.Allergies.GetAtoms',
		'org.freemedsoftware.module.Allergies.GetMostRecent',
		'org.freemedsoftware.module.Allergies.SetAtoms',
		'org.freemedsoftware.module.Annotations.GetAnnotations',
		'org.freemedsoftware.module.Annotations.LookupPatient',
		'org.freemedsoftware.module.Annotations.NewAnnotation',
		'org.freemedsoftware.module.Annotations.OutputAnnotations',
		'org.freemedsoftware.module.Annotations.PrepareAnnotation',
		'org.freemedsoftware.module.Medications.GetAtoms',
		'org.freemedsoftware.module.Medications.GetMostRecent',
		'org.freemedsoftware.module.Medications.SetAtoms',
		'org.freemedsoftware.module.MessagesModule.DeleteMultiple',
		'org.freemedsoftware.module.MessagesModule.GetAllByTag',
		'org.freemedsoftware.module.MessagesModule.MessageTags',
		'org.freemedsoftware.module.MessagesModule.UnreadMessages',
		'org.freemedsoftware.module.PatientModule.GetAddresses',
		'org.freemedsoftware.module.PatientModule.SetAddresses',
		'org.freemedsoftware.module.PatientTag.AdvancedTagSearch',
		'org.freemedsoftware.module.PatientTag.ChangeTag',
		'org.freemedsoftware.module.PatientTag.CreateTag',
		'org.freemedsoftware.module.PatientTag.ExpireTag',
		'org.freemedsoftware.module.PatientTag.GetTemplate',
		'org.freemedsoftware.module.PatientTag.ListTags',
		'org.freemedsoftware.module.PatientTag.SimpleTagSearch',
		'org.freemedsoftware.module.PatientTag.TagsForPatient',
		'org.freemedsoftware.module.RemittBillingTransport.GetClaimInformation',
		'org.freemedsoftware.module.RemittBillingTransport.GetRebillList',
		'org.freemedsoftware.module.RemittBillingTransport.GetReport',
		'org.freemedsoftware.module.RemittBillingTransport.GetStatus',
		'org.freemedsoftware.module.RemittBillingTransport.MarkAsBilled',
		'org.freemedsoftware.module.RemittBillingTransport.PatientsToBill',
		'org.freemedsoftware.module.RemittBillingTransport.ProcessClaims',
		'org.freemedsoftware.module.RemittBillingTransport.ProcessStatement',
		'org.freemedsoftware.module.Reporting.GetReportParameters',
		'org.freemedsoftware.module.Reporting.GetReports',
		'org.freemedsoftware.module.UnfiledDocuments.BatchSplit',
		'org.freemedsoftware.module.UnfiledDocuments.faxback',
		'org.freemedsoftware.module.UnfiledDocuments.NumberOfPages',
		'org.freemedsoftware.module.UnreadDocuments.GetCount',
		'org.freemedsoftware.module.UnreadDocuments.MoveToAnotherProvider',
		'org.freemedsoftware.module.UnreadDocuments.NumberOfPages',
		'org.freemedsoftware.module.UnreadDocuments.ReviewIntoRecord',
		'org.freemedsoftware.public.Login.GetLanguages',
		'org.freemedsoftware.public.Login.GetLocations',
		'org.freemedsoftware.public.Login.Logout',
		'org.freemedsoftware.public.Login.SessionPopulate',
		'org.freemedsoftware.public.Login.Validate',
		'org.freemedsoftware.public.Protocol.Version',

		// ---- public-namespace (6): relay.php skips the login guard for public.*, but ----
		// the allowlist check runs for every method, so the installer's calls must be here
		// or the install flow breaks. (The Login.* methods appear under gwtphpmap above.)
		'org.freemedsoftware.public.Installation.CheckDbCredentials',
		'org.freemedsoftware.public.Installation.CheckPhpMysqlEnabled',
		'org.freemedsoftware.public.Installation.CreateAdministrationAccount',
		'org.freemedsoftware.public.Installation.CreateDatabase',
		'org.freemedsoftware.public.Installation.CreateSettings',
		'org.freemedsoftware.public.Installation.SetHealthyStatus',

		// ---- source-mine (146): literals resolved out of the client/UI trees ---------------
		// (ui/gwt/src/main/java, ui/dojo, the smarty templates, the root request scripts),
		// kept only where the class file exists and the method is public (inherited methods
		// included). This is the tier most likely to be INCOMPLETE for a real session.
		'org.freemedsoftware.api.ActionItems.getActionItems',
		'org.freemedsoftware.api.ActionItems.getActionItemsCount',
		'org.freemedsoftware.api.ClaimLog.AgingReportQualified',
		'org.freemedsoftware.api.ClaimLog.getProceduresInfo',
		'org.freemedsoftware.api.ClaimLog.getProcInfo',
		'org.freemedsoftware.api.ClaimLog.MarkClaimsAsBilled',
		'org.freemedsoftware.api.ClaimLog.post_check',
		'org.freemedsoftware.api.ClaimLog.RebillClaims',
		'org.freemedsoftware.api.Ledger.AgingReportQualified',
		'org.freemedsoftware.api.Ledger.GetClaims',
		'org.freemedsoftware.api.ModuleInterface.EMRSupportPicklistMethod',
		'org.freemedsoftware.api.ModuleInterface.ModuleRenderHtmlMethod',
		'org.freemedsoftware.api.ModuleInterface.PrintToBrowser',
		'org.freemedsoftware.api.ModuleSearch.ToText',
		'org.freemedsoftware.api.Printing.GetPrinters',
		'org.freemedsoftware.api.Remitt.GetFile',
		'org.freemedsoftware.api.Remitt.GetFileList',
		'org.freemedsoftware.api.Remitt.GetServerStatus',
		'org.freemedsoftware.api.SystemConfig.GetAllSysOptions',
		'org.freemedsoftware.api.UserInterface.add',
		'org.freemedsoftware.api.UserInterface.GetCurrentProvider',
		'org.freemedsoftware.api.UserInterface.GetEMRConfiguration',
		'org.freemedsoftware.api.UserInterface.Multicall',
		'org.freemedsoftware.core.User.getName',
		'org.freemedsoftware.core.User.setPassword',
		'org.freemedsoftware.module.ACL.AddGroupWithPermissions',
		'org.freemedsoftware.module.ACL.DelGroupWithPermissions',
		'org.freemedsoftware.module.ACL.GetAllPermissions',
		'org.freemedsoftware.module.ACL.GetGroupPermissions',
		'org.freemedsoftware.module.ACL.GetUserGroups',
		'org.freemedsoftware.module.ACL.ModGroupWithPermissions',
		'org.freemedsoftware.module.ACL.UserGroups',
		'org.freemedsoftware.module.ACL.UserInGroup',
		'org.freemedsoftware.module.Allergies.add',
		'org.freemedsoftware.module.AppointmentTemplates.GetRecord',
		'org.freemedsoftware.module.Authorizations.add',
		'org.freemedsoftware.module.Authorizations.del',
		'org.freemedsoftware.module.Authorizations.GetAllAuthorizations',
		'org.freemedsoftware.module.Authorizations.getValidAuthorizations',
		'org.freemedsoftware.module.Authorizations.mod',
		'org.freemedsoftware.module.CalendarGroup.add',
		'org.freemedsoftware.module.CalendarGroup.del',
		'org.freemedsoftware.module.CalendarGroup.GetAll',
		'org.freemedsoftware.module.CalendarGroup.mod',
		'org.freemedsoftware.module.CalendarGroupAttendance.add',
		'org.freemedsoftware.module.Callin.add',
		'org.freemedsoftware.module.Callin.del',
		'org.freemedsoftware.module.Callin.GetRecord',
		'org.freemedsoftware.module.Callin.mod',
		'org.freemedsoftware.module.Certifications.getCertifications',
		'org.freemedsoftware.module.ClaimTypes.getClaimTypes',
		'org.freemedsoftware.module.ClinicalOrders.add',
		'org.freemedsoftware.module.ClinicRegistration.createPatient',
		'org.freemedsoftware.module.ClinicRegistration.migrateToPatient',
		'org.freemedsoftware.module.CptCodes.picklist',
		'org.freemedsoftware.module.CptModifiers.picklist',
		'org.freemedsoftware.module.DicomModule.GetDICOM',
		'org.freemedsoftware.module.DicomModule.GetRecord',
		'org.freemedsoftware.module.EncounterNotes.add',
		'org.freemedsoftware.module.EncounterNotes.del',
		'org.freemedsoftware.module.EncounterNotes.getEncounterNoteInfo',
		'org.freemedsoftware.module.EncounterNotes.getEncountersList',
		'org.freemedsoftware.module.EncounterNotes.mod',
		'org.freemedsoftware.module.EncounterNotesTemplate.add',
		'org.freemedsoftware.module.EncounterNotesTemplate.mod',
		'org.freemedsoftware.module.EpisodeOfCare.add',
		'org.freemedsoftware.module.EpisodeOfCare.del',
		'org.freemedsoftware.module.EpisodeOfCare.getEOCValues',
		'org.freemedsoftware.module.EpisodeOfCare.mod',
		'org.freemedsoftware.module.FacilityModule.GetDefaultFacility',
		'org.freemedsoftware.module.FinancialDemographics.add',
		'org.freemedsoftware.module.GrowthCharts.GetGrowthChartValues',
		'org.freemedsoftware.module.IcdCodes.picklist',
		'org.freemedsoftware.module.Letters.GetRecord',
		'org.freemedsoftware.module.LettersTemplates.add',
		'org.freemedsoftware.module.LettersTemplates.GetTemplate',
		'org.freemedsoftware.module.LettersTemplates.mod',
		'org.freemedsoftware.module.MessagesModule.del',
		'org.freemedsoftware.module.MessagesModule.PrintSinglePDF',
		'org.freemedsoftware.module.MessagesModule.RenderSinglePDF',
		'org.freemedsoftware.module.MultumDrugLexicon.DosagesForDrug',
		'org.freemedsoftware.module.MultumDrugLexicon.DrugDosageToText',
		'org.freemedsoftware.module.NDCLexicon.DosagesForDrug',
		'org.freemedsoftware.module.NDCLexicon.NameLookupToText',
		'org.freemedsoftware.module.NDCLexicon.TradenamePicklist',
		'org.freemedsoftware.module.Notifications.add',
		'org.freemedsoftware.module.PatientCorrespondence.GetRecord',
		'org.freemedsoftware.module.PatientCoverages.add',
		'org.freemedsoftware.module.PatientCoverages.del',
		'org.freemedsoftware.module.PatientCoverages.GetAllCoverages',
		'org.freemedsoftware.module.PatientCoverages.GetCoverageByType',
		'org.freemedsoftware.module.PatientCoverages.mod',
		'org.freemedsoftware.module.PatientModule.add',
		'org.freemedsoftware.module.PatientModule.DeleteAddressById',
		'org.freemedsoftware.module.PatientModule.DeleteAddresses',
		'org.freemedsoftware.module.PatientModule.GetRecord',
		'org.freemedsoftware.module.PatientModule.mod',
		'org.freemedsoftware.module.PatientReporting.GenerateReport',
		'org.freemedsoftware.module.PatientReporting.GetReportParameters',
		'org.freemedsoftware.module.PatientReporting.GetReports',
		'org.freemedsoftware.module.Pharmacy.picklist',
		'org.freemedsoftware.module.Pharmacy.to_text',
		'org.freemedsoftware.module.PhotographicIdentification.GetPhotoID',
		'org.freemedsoftware.module.PhotographicIdentification.ImportMugshotPhoto',
		'org.freemedsoftware.module.Practices.add',
		'org.freemedsoftware.module.Practices.GetRecord',
		'org.freemedsoftware.module.Prescription.add',
		'org.freemedsoftware.module.Prescription.GetDistinctRx',
		'org.freemedsoftware.module.ProcedureModule.add',
		'org.freemedsoftware.module.ProcedureModule.CalculateCharge',
		'org.freemedsoftware.module.ProcedureModule.getCoverages',
		'org.freemedsoftware.module.ProcedureModule.getLastProc',
		'org.freemedsoftware.module.ProcedureModule.getProcByID',
		'org.freemedsoftware.module.ProcedureModule.getProcedureInfo',
		'org.freemedsoftware.module.ProcedureModule.mod',
		'org.freemedsoftware.module.ProgressNotes.GetRecentRecord',
		'org.freemedsoftware.module.ProgressNotes.GetRecord',
		'org.freemedsoftware.module.ProgressNotes.RecentDates',
		'org.freemedsoftware.module.ProgressNotesTemplates.add',
		'org.freemedsoftware.module.ProgressNotesTemplates.GetTemplate',
		'org.freemedsoftware.module.ProgressNotesTemplates.mod',
		'org.freemedsoftware.module.ProviderGroups.getProviderIds',
		'org.freemedsoftware.module.ProviderModule.fullName',
		'org.freemedsoftware.module.ProviderModule.LookupNPI',
		'org.freemedsoftware.module.ProviderModule.picklist',
		'org.freemedsoftware.module.ProviderModule.to_text',
		'org.freemedsoftware.module.RemittBillingTransport.rebillkeys',
		'org.freemedsoftware.module.Reporting.GenerateReport',
		'org.freemedsoftware.module.RxRefillRequest.add',
		'org.freemedsoftware.module.RxRefillRequest.GetAll',
		'org.freemedsoftware.module.ScannedDocuments.GetDocumentPdf',
		'org.freemedsoftware.module.SchedulerPatientStatus.add',
		'org.freemedsoftware.module.SchedulerStatusType.getStatusType',
		'org.freemedsoftware.module.SuperbillTemplate.GetTemplate',
		'org.freemedsoftware.module.SystemNotifications.GetFromTimestamp',
		'org.freemedsoftware.module.SystemNotifications.GetSystemTaskPatientInbox',
		'org.freemedsoftware.module.SystemNotifications.GetSystemTaskUserInbox',
		'org.freemedsoftware.module.SystemNotifications.GetTimestamp',
		'org.freemedsoftware.module.Tools.ExecuteTool',
		'org.freemedsoftware.module.Tools.GetToolParameters',
		'org.freemedsoftware.module.Tools.GetTools',
		'org.freemedsoftware.module.UnfiledDocuments.del',
		'org.freemedsoftware.module.UnfiledDocuments.mod',
		'org.freemedsoftware.module.UnreadDocuments.del',
		'org.freemedsoftware.module.UserPreferences.SetValues',
		'org.freemedsoftware.module.WorkListsModule.GenerateWorkList',

	)

);

?>
