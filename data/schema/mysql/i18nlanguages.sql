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

CREATE TABLE IF NOT EXISTS `i18nlanguages` (
	abbrev				CHAR (5) UNIQUE NOT NULL,
	language			VARCHAR (150) NOT NULL,
	id				SERIAL,

	#	Define keys

	PRIMARY KEY			( abbrev )
);

DROP PROCEDURE IF EXISTS i18n_PopulateLanguage;

DELIMITER //

CREATE PROCEDURE i18n_PopulateLanguage ( IN a CHAR (5), IN l VARCHAR (150) )
BEGIN
	DECLARE c INT UNSIGNED;
	SELECT COUNT(*) INTO c FROM i18nlanguages WHERE abbrev = a;
	IF c > 0 THEN
		UPDATE i18nlanguages SET language = l WHERE abbrev = a;
	ELSE
		INSERT INTO i18nlanguages ( abbrev, language ) VALUES ( a, l );
	END IF;
END//

DELIMITER ;

# ----- Populate languages
CALL i18n_PopulateLanguage( 'en_US', 'English' );
CALL i18n_PopulateLanguage( 'de_DE', 'Deutsch' );
CALL i18n_PopulateLanguage( 'fr_FR', 'Francais' );
CALL i18n_PopulateLanguage( 'es_MX', 'Spanish(Mexico)' );
CALL i18n_PopulateLanguage( 'pl_PL', 'Polski' );
