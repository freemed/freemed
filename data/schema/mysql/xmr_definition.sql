# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `xmr_definition` (
	form_name		VARCHAR (150) NOT NULL,
	form_description	VARCHAR (250),
	form_locale		CHAR (5) NOT NULL DEFAULT 'en_US',
	form_template		TEXT,
	id			SERIAL

	#	Define keys

	, KEY			( form_name )
);

CREATE TABLE IF NOT EXISTS `xmr_definition_element` (
	form_id			INT UNSIGNED NOT NULL,
	text_name		VARCHAR (250) NOT NULL,
	parent_concept_id	CHAR (10),
	concept_id		CHAR (10),
	quant_id		CHAR (10),
	external_population	BOOL DEFAULT FALSE,
	id			SERIAL

	#	Define keys

	, KEY			( form_id )
	, KEY			( parent_concept_id, concept_id )
);

