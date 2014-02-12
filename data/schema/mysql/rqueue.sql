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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/systemnotification.sql

CREATE TABLE IF NOT EXISTS `rqueue` (
	  insert_stamp		TIMESTAMP (14) DEFAULT CURRENT_TIMESTAMP
	, payload		BLOB
	, processed		TINYINT NOT NULL DEFAULT 0
	, reference_id		INT UNSIGNED NOT NULL DEFAULT 0
	, patient_ids		TEXT
	, procrec_ids		TEXT
	, id			SERIAL

	#	Define keys/indexes

	, KEY			( insert_stamp )
	, KEY			( reference_id )
	, KEY			( processed )
);

CREATE TABLE IF NOT EXISTS `rqueueitem` (
	  rqueueid		INT UNSIGNED NOT NULL
	, patientid		BIGINT UNSIGNED NOT NULL
	, providerid		INT UNSIGNED NOT NULL
	, payerid		INT UNSIGNED NOT NULL
	, procid		INT UNSIGNED NOT NULL
	, processed		TINYINT NOT NULL DEFAULT 0
	, itype			ENUM ( 'payment', 'adjustment' ) NOT NULL
	, iamount		DECIMAL ( 10, 2 ) NOT NULL DEFAULT 0.00
	, icode			VARCHAR ( 50 )
	, note			VARCHAR ( 255 )
	, id			SERIAL

	, KEY			( processed )
        , FOREIGN KEY           ( rqueueid ) REFERENCES rqueue.id ON DELETE CASCADE
        , FOREIGN KEY           ( patientid ) REFERENCES patient.id ON DELETE CASCADE
        , FOREIGN KEY           ( providerid ) REFERENCES physician.id ON DELETE CASCADE
        , FOREIGN KEY           ( procid ) REFERENCES procrec.id ON DELETE CASCADE
        , FOREIGN KEY           ( payerid ) REFERENCES insco.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS rqueue_Upgrade;
DELIMITER //
CREATE PROCEDURE rqueue_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER rqueue_Insert;
END
//
DELIMITER ;
CALL rqueue_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER rqueue_Insert
	AFTER INSERT ON rqueue
	FOR EACH ROW BEGIN
		INSERT INTO systemnotification ( stamp, nuser, ntext, nmodule, npatient, naction ) VALUES ( NEW.insert_stamp, 0, 'Remittance', 'rqueue', 0, 'NEW' );
	END;
//

DELIMITER ;

