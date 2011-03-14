# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2010 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

CREATE TABLE IF NOT EXISTS `reporting` (
	  report_name			VARCHAR (100) NOT NULL
	, report_uuid			CHAR (36) NOT NULL
	, report_locale			CHAR (5) NOT NULL DEFAULT 'en_US'
	, report_desc			TEXT
	, report_type			VARCHAR (150) NOT NULL DEFAULT 'standard'
	, report_category		VARCHAR (150) NOT NULL DEFAULT 'reporting_engine'
	, report_sp			VARCHAR (150) NOT NULL
	, report_param_count		TINYINT(3) NOT NULL DEFAULT 0
	, report_param_names		TEXT
	, report_param_types		TEXT
	, report_param_options		TEXT
	, report_param_optional		TEXT
	, report_acl			VARCHAR (150)
	, report_formatting		BLOB

	#	Define keys

	, PRIMARY KEY			( report_uuid )
	, KEY				( report_name, report_locale )
);

DROP PROCEDURE IF EXISTS reporting_Upgrade;
DELIMITER //
CREATE PROCEDURE reporting_Upgrade ( ) 
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	CALL FreeMED_Module_GetVersion( 'reporting', @V );

	ALTER IGNORE TABLE reporting ADD COLUMN report_type VARCHAR (150) NOT NULL DEFAULT 'standard' AFTER report_desc;
	ALTER IGNORE TABLE reporting ADD COLUMN report_formatting BLOB AFTER report_acl;
	ALTER IGNORE TABLE reporting ADD COLUMN report_category VARCHAR (150) NOT NULL DEFAULT 'reporting_engine' AFTER report_type;

	CALL FreeMED_Module_UpdateVersion( 'reporting', 1 );
END//
DELIMITER ;
CALL reporting_Upgrade;

#	Load packaged reports

SOURCE data/schema/mysql/reporting/report_AccountPaid.sql
SOURCE data/schema/mysql/reporting/report_ChargeGraph.sql
SOURCE data/schema/mysql/reporting/report_DailyAdjustmentJournal.sql
SOURCE data/schema/mysql/reporting/report_DailyCashReceiptJournal.sql
SOURCE data/schema/mysql/reporting/report_DailyEndOfDayProcessingMaster.sql
SOURCE data/schema/mysql/reporting/report_DailyEndOfDayProcessingSummary.sql
SOURCE data/schema/mysql/reporting/report_GraphMonthlyFinancial.sql
SOURCE data/schema/mysql/reporting/report_MonthlyCashCollectionSummary.sql
SOURCE data/schema/mysql/reporting/report_MonthlyClientBalanceDue.sql
SOURCE data/schema/mysql/reporting/report_MonthlyMasterJournalDetail.sql
SOURCE data/schema/mysql/reporting/report_MonthlyMasterJournalSummary.sql
SOURCE data/schema/mysql/reporting/report_OutstandingPatientAccountsByProvider.sql
SOURCE data/schema/mysql/reporting/report_OutstandingPatientAccounts.sql
SOURCE data/schema/mysql/reporting/report_PatientAccountActivity.sql
SOURCE data/schema/mysql/reporting/report_PatientAgedDetail.sql
SOURCE data/schema/mysql/reporting/report_PatientAgingReport.sql
SOURCE data/schema/mysql/reporting/report_PatientCensusByCarrier.sql
SOURCE data/schema/mysql/reporting/report_PatientDemographic.sql
SOURCE data/schema/mysql/reporting/report_PatientDuplicateAccounts.sql
SOURCE data/schema/mysql/reporting/report_PatientList.sql
SOURCE data/schema/mysql/reporting/report_PatientReceipt_en_US.sql
SOURCE data/schema/mysql/reporting/report_PatientReceiptShort_en_US.sql
SOURCE data/schema/mysql/reporting/report_PatientZipCodeDistribution.sql
SOURCE data/schema/mysql/reporting/report_PrintEmail.sql
SOURCE data/schema/mysql/reporting/report_ReceivablesGraph.sql
SOURCE data/schema/mysql/reporting/report_TransactionGraph_en_US.sql

