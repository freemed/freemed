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

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/patient_emr.sql

CREATE TABLE IF NOT EXISTS `vitals` (
	  dateof			TIMESTAMP (14) NOT NULL DEFAULT NOW()
	, patient			BIGINT UNSIGNED NOT NULL
	, provider			BIGINT UNSIGNED NOT NULL
	, eoc				INT UNSIGNED

	# Temperature
	, v_temp_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_temp_value			REAL
	, v_temp_units			ENUM ( 'F', 'C' )
	, v_temp_qualifier		ENUM ( 'NONE', 'AUXILLARY', 'CORE', 'ORAL', 'RECTAL', 'SKIN', 'TYMPANIC' )

	# Pulse
	, v_pulse_status		ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_pulse_value			REAL
	, v_pulse_location		ENUM ( 'NONE', 'APICAL', 'BILATERAL PERIPHERALS', 'BRACHIAL', 'CAROTID', 'DORSALIS PEDIS', 'FEMORAL', 'OTHER', 'PERIPHERAL', 'POPLITEAL', 'POSTERIOR TIBIAL', 'RADIAL', 'ULNAR' )
	, v_pulse_method		ENUM ( 'NONE', 'AUSCULTATE', 'DOPPLER', 'PALPATED' )
	, v_pulse_site			ENUM ( 'NONE', 'LEFT', 'RIGHT' )

	# Pulse ox
	, v_pulseox_status		ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_pulseox_flowrate		REAL
	, v_pulseox_o2conc		REAL
	, v_pulseox_method		ENUM ( 'REAL', 'AEROSOL/HUMIDIFIED MASK', 'FACE TENT', 'MASK', 'NASAL CANNULA', 'NON RE-BREATHER', 'PARTIAL RE-BREATHER', 'T-PIECE', 'TRACHEOSTOMY COLLAR', 'VENTILATOR', 'VENTURI MASK' )

	# Glucose
	, v_glucose_status		ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_glucose_value		REAL
	, v_glucose_units		ENUM ( 'MG/DL' )
	, v_glucose_qualifier		ENUM ( 'NONE', 'FINGER STICK', 'WHOLE BLOOD', 'TRANSCUTANEOUS' )

	# Respiration
	, v_resp_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_resp_value			REAL
	, v_resp_method			ENUM ( 'NONE', 'ASSISTED VENTILATOR', 'CONTROLLED VENTILATOR', 'SPONTANEOUS' )
	, v_resp_position		ENUM ( 'NONE', 'LYING', 'SITTING', 'STANDING' )

	# Blood pressure
	, v_bp_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_bp_s_value			REAL
	, v_bp_d_value			REAL
	, v_bp_location			ENUM ( 'NONE', 'L ARM', 'L LEG', 'R ARM', 'R LEG' )
	, v_bp_method			ENUM ( 'NONE', 'CUFF', 'DOPPLER', 'NON-INVASIVE', 'PALPATED' )
	, v_bp_position			ENUM ( 'NONE', 'LYING', 'SITTING', 'STANDING' )

	# CVP
	, v_cvp_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_cvp_value			REAL
	, v_cvp_por			ENUM ( 'NONE', 'STERNUM', 'MIDAXILLARY LINE' )

	# C/G
	, v_cg_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_cg_value			REAL
	, v_cg_units			ENUM ( 'IN', 'CM' )
	, v_cg_location			ENUM ( 'NONE', 'ABDOMINAL', 'ANKLE', 'CALF', 'HEAD', 'LOWER ARM', 'OTHER', 'THIGH', 'UPPER ARM', 'WRIST' )
	, v_cg_site			ENUM ( 'NONE', 'LEFT', 'RIGHT' )

	# Height
	, v_h_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_h_value			REAL
	, v_h_units			ENUM ( 'IN', 'CM' )
	, v_h_quality			ENUM ( 'NONE', 'ACTUAL', 'ESTIMATED' )

	# Weight
	, v_w_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_w_value			REAL
	, v_w_units			ENUM ( 'LB', 'KG', 'OZ' )
	, v_w_method			ENUM ( 'NONE', 'BED', 'CHAIR', 'OTHER', 'PEDIATRIC', 'STANDING' )
	, v_w_quality			ENUM ( 'NONE', 'ACTUAL', 'DRY', 'ESTIMATED' )

	# Pain
	, v_pain_status			ENUM ( 'unavailable', 'refused', 'recorded' ) NOT NULL DEFAULT 'unavailable'
	, v_pain_value			INT UNSIGNED
	, v_pain_scale			ENUM ( 'VAS', 'FACES' )

	, notes				TEXT
	, locked			INT UNSIGNED NOT NULL DEFAULT 0
	, user				INT UNSIGNED NOT NULL DEFAULT 0
	, active			ENUM ( 'active', 'inactive' ) NOT NULL DEFAULT 'active'
	, id				SERIAL

	#	Define keys
	, KEY				( patient, dateof, provider )
	, FOREIGN KEY			( patient ) REFERENCES patient.id ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS vitals_atomic (
	  dateof			TIMESTAMP (14) NOT NULL DEFAULT NOW()
	, patient			BIGINT UNSIGNED NOT NULL
	, provider			BIGINT UNSIGNED NOT NULL
	, vitalsid			INT UNSIGNED NOT NULL

	, umlsconcept			CHAR (8) NOT NULL
	, textualname			VARCHAR (100) NOT NULL
	, value				REAL
	, qualifier			VARCHAR (100) NOT NULL

	, id				SERIAL

	#	Define keys
	, KEY				( dateof )
	, FOREIGN KEY			( patient ) REFERENCES patient.id ON DELETE CASCADE
	, FOREIGN KEY			( vitalsid ) REFERENCES vitals.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS vitals_Upgrade;
DELIMITER //
CREATE PROCEDURE vitals_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Remove triggers
	DROP TRIGGER vitals_Delete;
	DROP TRIGGER vitals_Insert;
	DROP TRIGGER vitals_Update;

	#----- Upgrades
END
//
DELIMITER ;
CALL vitals_Upgrade( );

#----- Triggers

DELIMITER //

CREATE TRIGGER vitals_Delete
	AFTER DELETE ON vitals
	FOR EACH ROW BEGIN
		DELETE FROM `patient_emr` WHERE module='vitals' AND oid=OLD.id;
	END;
//

CREATE TRIGGER vitals_Update
	AFTER UPDATE ON vitals
	FOR EACH ROW BEGIN
		UPDATE `patient_emr` SET stamp=NEW.dateof, patient=NEW.patient, summary=NEW.notes, locked=NEW.locked, user=NEW.user, status=NEW.active, provider=NEW.provider WHERE module='vitals' AND oid=NEW.id;
	END;
//

CREATE TRIGGER vitals_Insert
	AFTER INSERT ON vitals
	FOR EACH ROW BEGIN
		INSERT INTO `patient_emr` ( module, patient, oid, stamp, summary, locked, user, status, provider ) VALUES ( 'vitals', NEW.patient, NEW.id, NEW.dateof, NEW.notes, NEW.locked, NEW.user, NEW.active, NEW.provider );

		# Move to atomic records

		IF v_temp_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C0886414'
				, 'Body temperature'
				, NEW.v_temp_value
				, ''
			);
		END IF;

		IF v_bp_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C1305849'
				, 'Diastolic blood pressure'
				, NEW.v_bp_d_value
				, ''
			);

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C1306620'
				, 'Systolic blood pressure'
				, NEW.v_bp_s_value
				, ''
			);
		END IF;

		IF v_pulseox_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			# TODO: FLOW RATE

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C0523807'
				, 'Oxygen saturation'
				, NEW.v_pulseox_o2conc
				, ''
			);
		END IF;

		IF v_pulse_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C0034107'
				, 'Pulse'
				, NEW.v_pulse_value
				, ''
			);
		END IF;

		IF v_glucose_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C0392201'
				, 'Blood glucose'
				, NEW.v_glucose_value
				, ''
			);
		END IF;

		# TODO: RESPIRATORY RATE

		IF v_cvp_status = 'recorded' THEN
			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C0199666'
				, 'Central venous pressure'
				, NEW.v_cvp_value
				, NEW.v_cvp_por
			);
		END IF;

		# TODO : CG VALUES

		IF v_h_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C0547795'
				, 'Height'
				, NEW.v_h_value
				, ''
			);
		END IF;

		IF v_w_status = 'recorded' THEN
			# TODO: FORM QUALIFIER

			INSERT INTO `vitals_atomic` (
				  dateof
				, patient
				, provider
				, vitalsid
				, umlsconcept
				, textualname
				, value
				, qualifier
			) VALUES (
				  NEW.dateof
				, NEW.patient
				, NEW.provider
				, NEW.id
				, 'C1305866'
				, 'Weight'
				, NEW.v_w_value
				, ''
			);
		END IF;

		# TODO : PAIN MEASUREMENT

	END;
//

DELIMITER ;

