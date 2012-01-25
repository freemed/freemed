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

# File: Report - Patient Census By Carrier

DROP PROCEDURE IF EXISTS report_PatientCensusByCarrier_en_US ;
DELIMITER //

# Function: report_PatientCensusByCarrier_en_US
#
#	Patient list by insurance carrier.
#
CREATE PROCEDURE report_PatientCensusByCarrier_en_US ( )
BEGIN
	SELECT
		  i.insconame AS carrier
		, CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname, ' (', p.ptid, ')' ) AS patient_name
		, p.ptaddr1 AS address
		, CONCAT( p.ptcity, ', ', p.ptstate, ' ', p.ptzip ) AS csz
		, p.pthphone AS phone_number
	FROM procrec pr
		LEFT OUTER JOIN coverage c ON pr.proccurcovid=c.id
		LEFT OUTER JOIN insco i ON c.covinsco=i.id
		LEFT OUTER JOIN patient p ON pr.procpatient = p.id
	WHERE
		NOT ISNULL(i.id)
		AND p.ptarchive = 0
	GROUP BY pr.procpatient
	ORDER BY carrier, patient_name;
END //

DELIMITER ;

#	Add indices

DELETE FROM `reporting` WHERE report_sp = 'report_PatientCensusByCarrier_en_US';

INSERT INTO `reporting` (
		report_name,
		report_uuid,
		report_locale,
		report_desc,
		report_category,
		report_sp,
		report_type,
		report_param_count,
		report_param_names,
		report_param_types,
		report_param_options,
		report_param_optional,
		report_formatting
	) VALUES (
		'Patient Census By Carrier',
		'79c45978-0753-44bf-9860-e8d6a5f46483',
		'en_US',
		'Patient list by insurance carrier',
		'reporting_engine',
		'report_PatientCensusByCarrier_en_US',
		'rlib',
		0,
		'',
		'',
		'',
		'',
		'<?xml version="1.0"?>
<!DOCTYPE report >
<Report fontSize="8" orientation="portrait">
	<Alternate>
		<NoData>
			<Output>
				<Line fontSize="12">
					<literal>NO DATA</literal>
				</Line>		
			</Output>
		</NoData>
	</Alternate>
	<ReportHeader>
		<Output>
			<Line fontSize="11">
				<literal bold="yes">Patient Census By Carrier</literal>
				<field value="m.installation" width="50" align="right" />
			</Line>
			<Line fontSize="7">
				<field value="\'Generated on: \' + m.generated_on" width="50" align="left" italics="yes" />
			</Line>
			<HorizontalLine size="10" bgcolor="\'white\'" />
		</Output>
	</ReportHeader>		
	<Breaks>	
		<Break name="break0" newpage="no" headernewpage="yes">
			<BreakHeader>
				<Output>
					<HorizontalLine size="1" bgcolor="\'black\'" />
					<Line>
						<field value="carrier" width="20" align="left" col="1" bold="yes" />					
					</Line>
				</Output>
			</BreakHeader>
			<BreakFields>
				<BreakField value="carrier"/>
			</BreakFields>
			<BreakFooter>
				<Output>
					<HorizontalLine size="1" bgcolor="\'black\'" />
					<HorizontalLine size="10" bgcolor="\'white\'" />
				</Output>
			</BreakFooter>
		</Break>
	</Breaks>
	<Detail>
		<FieldHeaders>
			<Output>
				<HorizontalLine size="1" bgcolor="\'black\'"/>
				<Line bgcolor="\'0xe5e5e5\'">
					<literal width="40" col="1">Patient</literal>
					<literal width="1"/>
					<literal width="30" col="2">Street Address</literal>
					<literal width="1"/>
					<literal width="30" col="3">City</literal>
					<literal width="1"/>
					<literal width="16" col="3">Phone</literal>
				</Line>
				<HorizontalLine size="1" bgcolor="\'black\'"/>
				<HorizontalLine size="4" bgcolor="\'white\'"/>
			</Output>
		</FieldHeaders>		
		<FieldDetails>
			<Output>
				<Line bgcolor="iif(r.detailcnt%2,\'0xe5e5e5\',\'white\')">
					<field value="patient_name" width="40" align="left" col="1" />
					<literal width="1"/>
					<field value="address" width="30" align="left" col="2" />
					<literal width="1"/>
					<field value="csz" width="30" align="left" col="3" />
					<literal width="1"/>
					<field value="\'(\' + mid(phone_number,0,3) + \') \' + mid(phone_number,3,3) + \'-\' + mid(phone_number,6,4)" width="16" align="left" col="4" />
				</Line>
			</Output>
		</FieldDetails>
	</Detail>

	<PageFooter>
		<Output>
			<Line>
				<literal>Page: </literal>	
				<field value="r.pageno" width="3" align="right"/>
			</Line>
		</Output>
	</PageFooter>

	<ReportFooter>
	</ReportFooter>
</Report>'
	);

