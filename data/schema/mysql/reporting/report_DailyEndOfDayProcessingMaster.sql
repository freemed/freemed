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

# File: Daily End Of Day Processing



DELETE FROM `reporting` WHERE report_uuid = 'c52995a0-dfaa-40f2-b07d-d9250d64593c';
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
		'Daily End Of Day Processing',
		'c52995a0-dfaa-40f2-b07d-d9250d64593c',
		'en_US',
		'Daily End Of Day Processing',
		'jasper',
		'reporting_engine',
		'',
		2,
		'Date,Facility',
		'Date,Facility',
		'0,0',
		'DailyEndOfDayProcessingMaster_en_US'
	);

