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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `dicom` (
	d_stamp			TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	d_patient		BIGINT UNSIGNED NOT NULL DEFAULT 0,
	d_study_description	VARCHAR (250),
	d_filename		VARCHAR (250),
	d_md5			CHAR (32),
	d_study_date		DATE,
	d_institution_name	VARCHAR (250),
	d_institution_address	VARCHAR (250),
	d_study_uid		VARCHAR (250),
	d_series_uid		VARCHAR (250),
	d_referring_provider	INT UNSIGNED NOT NULL DEFAULT 0,

	d_xml_data		TEXT,

	storage_status		ENUM ( 'online', 'offline', 'remote' ) NOT NULL DEFAULT 'online',
	
	user			INT UNSIGNED NOT NULL DEFAULT 0,
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active',
	id			SERIAL

	#	Define keys

	, FOREIGN KEY		( d_patient ) REFERENCES patient.id ON DELETE CASCADE
	, KEY			( d_md5 )
	, KEY			( d_study_uid )
	, KEY			( d_series_uid )
);

DROP PROCEDURE IF EXISTS dicom_Upgrade;
DELIMITER //
CREATE PROCEDURE dicom_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER dicom_Delete;
	DROP TRIGGER dicom_Insert;
	DROP TRIGGER dicom_Update;

	#----- Upgrades
	#CALL FreeMED_Module_GetVersion( 'dicom', @V );

	#CALL FreeMED_Module_UpdateVersion( 'dicom', 1 );
END
//
DELIMITER ;
CALL dicom_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER dicom_Delete
	AFTER DELETE ON dicom
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='dicom' AND oid=OLD.id;
	END;
//

CREATE TRIGGER dicom_Insert
	AFTER INSERT ON dicom
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'dicom', NEW.d_patient, NEW.id, NEW.d_stamp, IF(ISNULL(NEW.d_study_description), NEW.d_stamp, NEW.d_study_description), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER dicom_Update
	AFTER UPDATE ON dicom
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.d_stamp, patient=NEW.d_patient, summary=NEW.d_study_description, user=NEW.user, status=NEW.active WHERE module='dicom' AND oid=NEW.id;
	END;
//

DELIMITER ;

