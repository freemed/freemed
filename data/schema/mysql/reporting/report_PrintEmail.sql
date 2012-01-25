# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2012 FreeMED Software Foundation
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

# File: Report - Email Message

DROP PROCEDURE IF EXISTS report_PrintEmail_en_US;
DELIMITER //

# Function: report_PrintEmail_en_US
#
#	Email Message
#

CREATE PROCEDURE report_PrintEmail_en_US (IN mid INT)
BEGIN
	SET @sql = CONCAT(
		"SELECT ",
			"CASE m.msgby WHEN 0 THEN 'System' ELSE u1.userdescrip END AS 'From', ",
			"CASE m.msgfor WHEN 0 THEN 'System' ELSE u2.userdescrip END AS 'To', ",
			"m.msgtime AS 'Date', ",
			"CASE m.msgpatient WHEN 0 THEN m.msgperson ELSE CONCAT( pt.ptlname, ', ', pt.ptfname, ' (', pt.ptid, ')' ) END AS 'Patient', ",
			"m.msgsubject AS 'Subject', ",
			"case m.msgurgency when 1 then 'Urgent' when 2 then 'Expedited' when 3 then 'Standard' when 4 then 'Notification' when 5 then 'Bulk' end AS 'Urgency', ",
			"m.msgtext AS 'Message' ",
		"FROM messages m ",
			"LEFT OUTER JOIN patient pt ON pt.id=m.msgpatient ",
			"LEFT OUTER JOIN user u1 ON m.msgby=u1.id ",
			"LEFT OUTER JOIN user u2 ON m.msgby=u2.id ",
		"WHERE "
			"m.id = ",mid
	);
	PREPARE s FROM @sql ;
	EXECUTE s ;
	DEALLOCATE PREPARE s ;

END
//
DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PrintEmail_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_type,
		report_category,
		report_sp,
		report_param_count,
		report_param_names,
		report_param_types,
		report_param_optional,
		report_formatting
	) VALUES (
		'Email Message',
		'f1a3c9a0-81f3-4802-b184-4f806333176a',
		'en_US',
		'Email Message',
		'jasper',
		'message_report',
		'report_PrintEmail_en_US',
		1,
		'MessageID',
		'MessageID',
		'0',
		'PrintEmail_en_US'
	);
