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

CREATE TABLE IF NOT EXISTS `taxonomy` (
	  text_name			VARCHAR (250) NOT NULL
		COMMENT 'Descriptive name, used in picklists'
	, taxonomy_type			ENUM ( 'concept', 'qualifier', 'quantifier' ) NOT NULL DEFAULT 'concept'
	, code_set			CHAR (20) NOT NULL DEFAULT 'UMLS'
		COMMENT 'Super codeset, defaults to UMLS'
	, code_value			CHAR (10) NOT NULL
		COMMENT 'Actual code value from code_set'
	, external_population		BOOL DEFAULT FALSE
		COMMENT 'Populated from outside the XMR system? (outside includes EMR)'

	, widget_type			CHAR (30) NOT NULL DEFAULT 'TEXT'
	, widget_options		TEXT

	, id				SERIAL

	#	Define keys

	, KEY				( taxonomy_type, code_set, code_value )
	, KEY				( taxonomy_type, text_name )
);

CREATE TABLE IF NOT EXISTS `taxonomy_sub_mapping` (
	  taxonomy_id			BIGINT UNSIGNED NOT NULL
	, taxonomy_sub_id		BIGINT UNSIGNED NOT NULL
	, id				SERIAL

	, FOREIGN KEY			( taxonomy_id ) REFERENCES taxonomy ( id ) ON DELETE CASCADE
	, FOREIGN KEY			( taxonomy_sub_id ) REFERENCES taxonomy ( id ) ON DELETE CASCADE
) COMMENT 'Mapping between taxonomy items';

#
# taxonomy_basic_emr
#
#	Table to keep track of basic EMR information to map it into the
#	XMR system of concepts, qualifiers and quantifiers.
#
#	sql_extraction should be an SQL statement with a single column
#	of results, where '${patient}' could be replaced by the patient
#	id. For example, this would give the patient date of birth:
#
#	"SELECT ptdob FROM patient WHERE id = ${patient}"
#
#	and this would get systolic blood pressure:
#
#	"SELECT pnotessbp FROM pnotes WHERE pnotespat = ${patient} ORDER BY pnotesdt DESC LIMIT 1"
#
CREATE TABLE IF NOT EXISTS `taxonomy_basic_emr` (
	  concept_id			BIGINT UNSIGNED NOT NULL
	, qualifier_id			BIGINT UNSIGNED NOT NULL
	, quantifier_id			BIGINT UNSIGNED NOT NULL
	, sql_extraction		TEXT
		COMMENT 'SQL statement resulting in resultset for patient ID'

	#	Keys

	, FOREIGN KEY			( concept_id ) REFERENCES taxonomy ( id ) ON DELETE CASCADE
	, FOREIGN KEY			( qualifier_id ) REFERENCES taxonomy ( id ) ON DELETE CASCADE
	, FOREIGN KEY			( quantifier_id ) REFERENCES taxonomy ( id ) ON DELETE CASCADE
) COMMENT 'Mappings for data points in basic EMR tables';

