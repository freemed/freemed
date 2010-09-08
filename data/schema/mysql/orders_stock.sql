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

CREATE TABLE IF NOT EXISTS `orders_stock` (
	  name			VARCHAR ( 150 ) NOT NULL UNIQUE
	, ordertype		ENUM ( 'consult', 'radiology', 'lab', 'immunization', 'procedure', 'rx' ) NOT NULL
	, delinquentdate	DATE
	, orderpriority		ENUM ( 'R', 'S' ) DEFAULT 'R' COMMENT 'Routine or Stat'
	, summary		VARCHAR (250) NOT NULL DEFAULT ''
				COMMENT 'Textual description of the order'
	, notes			TEXT

	, radiologycode		INT UNSIGNED DEFAULT 0

	, labpanelcodeset	INT UNSIGNED DEFAULT 0
	, labpanelcode		INT UNSIGNED DEFAULT 0
	, labspecimenactioncode	ENUM ( 'A', 'G', 'L', 'O', 'P', 'R', 'S' ) DEFAULT 'S'

	, immunizationcode	INT UNSIGNED DEFAULT 0
	, immunizationunits	REAL DEFAULT 0.0

	, procedurecode		INT UNSIGNED DEFAULT 0

	, id			SERIAL
	
	# Keys/indices
	, INDEX			( name )
);

DROP PROCEDURE IF EXISTS orders_stock_Upgrade;
DELIMITER //
CREATE PROCEDURE orders_stock_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
END
//
DELIMITER ;
CALL orders_stock_Upgrade( );

