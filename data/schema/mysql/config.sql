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

#	Import external functions and procedures
SOURCE data/schema/mysql/_functions.sql
SOURCE data/schema/mysql/_hl7.sql

CREATE TABLE IF NOT EXISTS `config` (
	c_option		CHAR (64) UNIQUE NOT NULL,
	c_value			VARCHAR (100),
	c_title			VARCHAR (100),
	c_section		VARCHAR (100),
	c_type			VARCHAR (100) NOT NULL,
	c_options		TEXT,
	id			SERIAL
);

DROP PROCEDURE IF EXISTS config_Upgrade;
DELIMITER //
CREATE PROCEDURE config_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	#----- Upgrades
	ALTER IGNORE TABLE config ADD COLUMN c_title VARCHAR (100) AFTER c_value;
	ALTER IGNORE TABLE config ADD COLUMN c_section VARCHAR (100) AFTER c_title;
	ALTER IGNORE TABLE config ADD COLUMN c_type VARCHAR (100) NOT NULL AFTER c_section;
	ALTER IGNORE TABLE config ADD COLUMN c_options TEXT AFTER c_type;
END
//
DELIMITER ;
CALL config_Upgrade( );

DROP PROCEDURE IF EXISTS config_Register;
DELIMITER //

# Function: config_Register
#
#	Register configuration entries.
#
# Parameters:
#
#	IN name - Option name. CHAR (64)
#
#	IN defaultValue - Default value for this option. VARCHAR (100).
#
#	IN title - Textual name for this option. VARCHAR (100).
#
#	IN section - Optional section name for this option. VARCHAR (100).
#
#	IN type - Type of configuration widget. VARCHAR (100).
#
#	IN options - Options, if needed by widget. TEXT.
#
CREATE PROCEDURE config_Register ( IN name CHAR(64), IN defaultValue VARCHAR(100), IN title VARCHAR(100), IN section VARCHAR(100), IN type VARCHAR(100), IN options TEXT )
BEGIN
	DECLARE found BOOL;
	DECLARE defined BOOL;
	SELECT ( COUNT(*) > 0 ) INTO found FROM config WHERE c_option=name;

	IF found THEN
		UPDATE config SET c_title=title, c_section=section, c_type=type, c_options=options WHERE c_option=name;
		SELECT ( ISNULL( c_value ) OR c_value = '' ) INTO defined FROM config WHERE c_option=name;
		IF NOT defined AND LENGTH(defaultValue) > 0 THEN
			UPDATE config SET c_value=defaultValue WHERE c_option=name;
		END IF;
	ELSE
		INSERT INTO config ( c_option, c_value, c_title, c_section, c_type, c_options ) VALUES ( name, defaultValue, title, section, type, options );
	END IF;
END
//
DELIMITER ;

#----- Define basic configuration values

CALL config_Register (
	'remitt_url',
	'http://localhost:8080/remitt/services/interface?wsdl',
	'Remitt Service URL',
	'REMITT Billing',
	'Text',
	'http://localhost:8080/remitt/services/interface?wsdl'
);
CALL config_Register (
	'remitt_user',
	'remitt',
	'Remitt Authentication Username',
	'REMITT Billing',
	'Text',
	''
);
CALL config_Register (
	'remitt_pass',
	'remitt',
	'Remitt Password',
	'REMITT Billing',
	'Text',
	''
);
CALL config_Register (
	'remitt_cbuser',
	'remittcb',
	'Remitt Callback Username',
	'REMITT Billing',
	'Text',
	''
);
CALL config_Register (
	'remitt_cbpass',
	'remittcb',
	'Remitt Callback Password',
	'REMITT Billing',
	'Text',
	''
);

CALL config_Register (
	'fax_nocover',
	'0',
	'Remove fax cover pages?',
	NULL,
	'YesNo',
	''
);

CALL config_Register (
	'calshr',
	'8',
	'Scheduler Begin Hour',
	'Scheduler',
	'Select',
	'4,5,6,7,8,9,10,11,12,13,14,15,16,17,18'
);

CALL config_Register (
	'calehr',
	'16',
	'Scheduler End Hour',
	'Scheduler',
	'Select',
	'10,11,12,13,14,15,16,17,18,19,20,21,22,23'
);

CALL config_Register (
	'calinterval',
	'15',
	'Scheduling Interval',
	'Scheduler',
	'Select',
	'5,10,15,20,30'
);

CALL config_Register (
	'calbreakhour',
	'13',
	'Scheduler Break Hour',
	'Scheduler',
	'Select',
	'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23'
);

CALL config_Register (
	'patient_form',
	'tab',
	'Patient Form',
	'UI',
	'Select',
	'tab,singlepage'
);

CALL config_Register (
	'work_list',
	'1',
	'Work List',
	'UI',
	'YesNo',
	''
);

CALL config_Register (
	'xmpp_notify',
	'0',
	'XMPP Notifications',
	'Notifications',
	'YesNo',
	''
);

CALL config_Register (
	'xmpp_host',
	'talk.google.com',
	'XMPP Hostname',
	'Notifications',
	'Text',
	''
);

CALL config_Register (
	'xmpp_port',
	'5222',
	'XMPP Port',
	'Notifications',
	'Text',
	''
);

CALL config_Register (
	'xmpp_user',
	'',
	'XMPP Username',
	'Notifications',
	'Text',
	''
);

CALL config_Register (
	'xmpp_pass',
	'',
	'XMPP Password',
	'Notifications',
	'Text',
	''
);

#----- Mirth export -----

CALL config_Register (
	'mirth_enable',
	'0',
	'Enable Mirth interface?',
	'Mirth',
	'YesNo',
	''
);

CALL config_Register (
	'mirth_endpoint',
	'http://localhost:8081/services/Mirth',
	'Mirth SOAP Endpoint',
	'Mirth',
	'Text',
	''
);

