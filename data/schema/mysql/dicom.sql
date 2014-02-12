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
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `dicom` (
	d_stamp			TIMESTAMP (14) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of indexing',
	d_patient		BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Link to patient table',
	d_study_description	VARCHAR (250) COMMENT 'DICOM study description tag',
	d_images		TEXT COMMENT 'DICOM image IDs',
	d_study_uid		VARCHAR (250) COMMENT 'DICOM study uid tag',
	d_series_uid		VARCHAR (250) COMMENT 'DICOM series uid tag',
	user			INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Link to user table',
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' COMMENT 'Status of record',
	id			SERIAL

	, FOREIGN KEY		( d_patient ) REFERENCES patient.id ON DELETE CASCADE
	, KEY			( d_study_uid )
	, KEY			( d_series_uid )
);

CREATE TABLE IF NOT EXISTS `dicom_image` (
	d_stamp			TIMESTAMP (14) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of indexing',
	d_patient		BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Link to patient table',
	d_study_description	VARCHAR (250) COMMENT 'DICOM study description tag',
	d_filename		VARCHAR (250) COMMENT 'Local filename',
	d_md5			CHAR (32) COMMENT 'File MD5 checksum',
	d_study_date		DATE COMMENT 'Date of study',
	d_institution_name	VARCHAR (250) COMMENT 'DICOM institution name tag',
	d_institution_address	VARCHAR (250) COMMENT 'DICOM institution address tag',
	d_study_uid		VARCHAR (250) COMMENT 'DICOM study uid tag',
	d_series_uid		VARCHAR (250) COMMENT 'DICOM series uid tag',
	d_referring_provider	INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Link to provider table',

	d_xml_data		TEXT COMMENT 'dcm2xml DICOM data blob',

	storage_status		ENUM ( 'online', 'offline', 'remote' ) NOT NULL DEFAULT 'online' COMMENT 'Location of DICOM image',
	
	user			INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Link to user table',
	active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active' COMMENT 'Status of record',
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
	DROP TRIGGER dicom_image_Delete;
	DROP TRIGGER dicom_image_Insert;
	DROP TRIGGER dicom_image_Update;

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

CREATE TRIGGER dicom_image_Delete
	AFTER DELETE ON dicom_image
	FOR EACH ROW BEGIN
		#----- Update record
		UPDATE dicom
		SET
			d_images = (
				SELECT GROUP_CONCAT( di.id ) FROM dicom_images di WHERE d_patient = OLD.d_patient AND d_series_uid = OLD.d_series_uid AND d_study_uid = OLD.d_study_uid
			)
		WHERE
			d_patient = OLD.d_patient
			AND d_study_uid = OLD.d_study_uid
			AND d_series_uid = OLD.d_series_uid;
	END;
//

CREATE TRIGGER dicom_Insert
	AFTER INSERT ON dicom
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, user, status ) VALUES ( 'dicom', NEW.d_patient, NEW.id, NEW.d_stamp, IF(ISNULL(NEW.d_study_description), NEW.d_stamp, NEW.d_study_description), NEW.user, NEW.active );
	END;
//

CREATE TRIGGER dicom_image_insert
	AFTER INSERT ON dicom_image
	FOR EACH ROW BEGIN
		DECLARE c INT UNSIGNED;
		#----- Check for existing aggregation record
		SELECT COUNT(*) INTO c FROM dicom d
			WHERE d.d_patient = NEW.d_patient
			AND d.d_study_uid = NEW.d_study_uid
			AND d.d_series_uid = NEW.d_series_uid;
		IF c < 1 THEN
			#----- If no aggregation record, then create one
			INSERT INTO dicom (
				  d_stamp
				, d_patient
				, d_study_description
				, d_study_uid
				, d_series_uid
				, d_images
				, user
				, active
			) VALUES (
				  NEW.d_stamp
				, NEW.d_patient
				, NEW.d_study_description
				, NEW.d_study_uid
				, NEW.d_series_uid
				, NEW.id
				, NEW.user
				, NEW.active
			);
		ELSE
			#----- Update record if one exists
			UPDATE dicom
			SET
				d_images = (
					SELECT GROUP_CONCAT( di.id ) FROM dicom_images di WHERE d_patient = NEW.d_patient AND d_series_uid = NEW.d_series_uid AND d_study_uid = NEW.d_study_uid
				)
			WHERE
				d_patient = NEW.d_patient
				AND d_study_uid = NEW.d_study_uid
				AND d_series_uid = NEW.d_series_uid;
		END IF;
	END;
//

CREATE TRIGGER dicom_Update
	AFTER UPDATE ON dicom
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.d_stamp, patient=NEW.d_patient, summary=NEW.d_study_description, user=NEW.user, status=NEW.active WHERE module='dicom' AND oid=NEW.id;
	END;
//

CREATE TRIGGER dicom_image_Update
	AFTER UPDATE ON dicom_image
	FOR EACH ROW BEGIN
	END;
//

DELIMITER ;

