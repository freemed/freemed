# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2009 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `drugsampleinv` (
	  drugcode			VARCHAR (75)
	, drugndc			VARCHAR (75) NOT NULL
	, drugclass			VARCHAR (150)
	, strength			VARCHAR (75)
	, deliveryform			VARCHAR (75)
	, packagecount			INT UNSIGNED NOT NULL DEFAULT 0
	, location			VARCHAR (150)
	, drugco			VARCHAR (75)
	, drugrep			VARCHAR (75)
	, invoice			VARCHAR (20)
	, samplecount			INT UNSIGNED NOT NULL DEFAULT 0
	, samplecountremain		INT UNSIGNED NOT NULL DEFAULT 0
	, lot				VARCHAR (16)
	, expiration			DATE
	, received			DATE
	, assignedto			VARCHAR (75)
	, loguser			INT UNSIGNED NOT NULL DEFAULT 0
	, logdate			DATE
	, disposeby			VARCHAR (75)
	, disposedate			DATE
	, disposemethod			VARCHAR (75)
	, disposereason			VARCHAR (75)
	, witness			VARCHAR (75)
	, id				SERIAL

	#	Define keys

	, PRIMARY KEY			( id )
	, KEY				( lot )
);

DROP PROCEDURE IF EXISTS drugsampleinv_Upgrade;
DELIMITER //
CREATE PROCEDURE drugsampleinv_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER drugsampleinv_PreInsert;
	DROP TRIGGER drugsampleinv_PreUpdate;
END//
DELIMITER ;
CALL drugsampleinv_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER drugsampleinv_PreInsert
	BEFORE INSERT ON drugsampleinv
	FOR EACH ROW BEGIN
		SELECT SUBSTRING_INDEX(NEW.drugndc, '-', 1) INTO NEW.drugcode;
		SELECT SUBSTRING_INDEX(NEW.drugndc, '-', -1) INTO NEW.strength;
	END;
//

CREATE TRIGGER drugsampleinv_PreUpdate
	BEFORE UPDATE ON drugsampleinv
	FOR EACH ROW BEGIN
		SELECT SUBSTRING_INDEX(NEW.drugndc, '-', 1) INTO NEW.drugcode;
		SELECT SUBSTRING_INDEX(NEW.drugndc, '-', -1) INTO NEW.strength;
	END;
//

DELIMITER ;

