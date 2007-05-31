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

CREATE TABLE IF NOT EXISTS `schedulingrules` (
	user			INT UNSIGNED,
	provider		INT UNSIGNED,
	reason			VARCHAR (150),
	dowbegin		INT UNSIGNED,
	dowend			INT UNSIGNED,
	datebegin		DATE,
	dateend			DATE,
	timebegin		TIME,
	timeend			TIME,
	id			SERIAL
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS schedulingrules_Upgrade;
DELIMITER //
CREATE PROCEDURE schedulingrules_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
END
//
DELIMITER ;
CALL schedulingrules_Upgrade( );

DROP PROCEDURE IF EXISTS checkSchedulingRules;
DELIMITER //
CREATE PROCEDURE checkSchedulingRules ( IN phy INT UNSIGNED, IN dt DATE, IN tm TIME )
BEGIN
	SELECT
		reason
	FROM schedulingrules s
	WHERE
		( s.provider = phy OR s.provider = 0 OR ISNULL(s.provider) ) 
		AND (
			#	Only day of week range, no actual dates or times
			( ISNULL(s.datebegin) AND ISNULL(s.timebegin) AND DAYOFWEEK(dt) >= s.dowbegin AND DAYOFWEEK(dt) <= s.dowend )
			#	Full date ranges ( times are null ), no DOW
			OR ( ISNULL(s.dowbegin) AND ISNULL(s.timebegin) AND dt >= s.datebegin AND dt <= s.dateend )
			#	Full date ranges ( times are null ), with day of week
			OR ( ISNULL(s.timebegin) AND dt >= s.datebegin AND dt <= s.dateend AND DAYOFWEEK(dt) >= s.dowbegin AND DAYOFWEEK(dt) <= s.dowend )
			#	Date range, but within dates, no DOW
			OR ( ISNULL(s.dowbegin) AND tm >= s.timebegin AND tm <= s.timeend AND dt >= s.datebegin AND dt <= s.dateend )
			#	Date range, but within dates
			OR ( tm >= s.timebegin AND tm <= s.timeend AND dt >= s.datebegin AND dt <= s.dateend AND DAYOFWEEK(dt) >= s.dowbegin AND DAYOFWEEK(dt) <= s.dowend )
		)
	;
END
//
DELIMITER ;

