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

CREATE TABLE IF NOT EXISTS `cpt` (
	  cptcode		CHAR (7) NOT NULL UNIQUE
	, cptnameint		VARCHAR (50)
	, cptnameext		VARCHAR (50)
	, cptgender		ENUM ( 'n', 'm', 'f' ) DEFAULT 'n'
	, cpttaxed		ENUM ( 'n', 'y' ) DEFAULT 'n'
	, cpttype		INT UNSIGNED DEFAULT 0
	, cptreqcpt		TEXT
	, cptexccpt		TEXT
	, cptreqicd		TEXT
	, cptexcicd		TEXT
	, cptrelval		REAL DEFAULT 1
	, cptdeftos		INT UNSIGNED DEFAULT 0
	, cptdefstdfee		DECIMAL( 10, 2 ) DEFAULT 0.00
	, cptstdfee		TEXT
	, cpttos		TEXT
	, cpttosprfx		TEXT
	, id			SERIAL

	, PRIMARY KEY		( id )
	, INDEX			( cptcode )
	, INDEX			( cptnameint )
);


DROP PROCEDURE IF EXISTS cpt_Upgrade;
DELIMITER //
CREATE PROCEDURE cpt_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	ALTER IGNORE TABLE cpt ADD COLUMN cptarchive INT(10) UNSIGNED DEFAULT 0 AFTER cpttosprfx;
	#----- Upgrades

END
//
DELIMITER ;
CALL cpt_Upgrade( );


CREATE TABLE IF NOT EXISTS `hcpcs` (
	  hcpcs				CHAR( 7 ) NOT NULL UNIQUE
	, modifier			CHAR( 2 )
	, description			VARCHAR( 100 )
	, status_code			CHAR( 1 )
	, not_used_for_medicare		VARCHAR ( 100 )
	, work_rvu			DECIMAL( 10, 2 )
	, trans_non_fac_pe_rvu		DECIMAL( 10, 2 )
	, trans_non_fac_na_indic	VARCHAR( 10 )
	, fi_non_fac_pe_rvu		DECIMAL( 10, 2 )
	, fi_non_fac_na_indic		VARCHAR( 10 )
	, trans_fac_pe_rvu		DECIMAL( 10, 2 )
	, trans_fac_na_indic		VARCHAR( 10 )
	, fi_fac_pe_rvu			DECIMAL( 10, 2 )
	, fi_fac_na_indic		VARCHAR( 10 )
	, mp_rvu			DECIMAL( 10, 2 )
	, trans_non_fac_total		DECIMAL( 10, 2 )
	, fi_non_fac_total		DECIMAL( 10, 2 )
	, trans_fac_total		DECIMAL( 10, 2 )
	, fi_fac_total			DECIMAL( 10, 2 )
	, pctc_ind			TINYINT NOT NULL
	, glob_days			VARCHAR( 20 )
	, pre_op			DECIMAL( 10, 2 )
	, intra_op			DECIMAL( 10, 2 )
	, post_op			DECIMAL( 10, 2 )
	, multi_proc			TINYINT NOT NULL
	, bilat_surg			TINYINT NOT NULL
	, asst_surg			TINYINT NOT NULL
	, co_surg			TINYINT NOT NULL
	, team_surg			TINYINT NOT NULL
	, endo_base			CHAR( 5 )
	, conv_factor			DECIMAL( 10, 5 )
	, supervision_dx_proc		VARCHAR( 10 )
	, calculation_flag		TINYINT NOT NULL
	, diag_imag_fam_indic		CHAR( 2 )
	, non_fac_pe_opps_payment_amt	DECIMAL( 10, 2 )
	, fac_pe_opps_payment_amt	DECIMAL( 10, 2 )
	, mp_opps_payment_amt		DECIMAL( 10, 2 )

	, PRIMARY KEY ( hcpcs )
	, INDEX ( description )
	, INDEX ( status_code )
);

TRUNCATE hcpcs;
LOAD DATA LOCAL INFILE "data/source/cpt/PPRRVU.csv"
	INTO TABLE hcpcs
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 11 LINES;

DROP PROCEDURE IF EXISTS cpt_Upgrade;
DELIMITER //
CREATE PROCEDURE cpt_Upgrade ( )
BEGIN
	DECLARE cpt_Count INT UNSIGNED DEFAULT 0;

	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;

	SELECT COUNT(*) INTO cpt_Count FROM cpt;

	# Only migrate data in if there's nothing in the CPT codes table
	IF cpt_Count = 0 THEN
		INSERT INTO cpt (
			  cptcode
			, cptnameint
			, cptnameext
		) SELECT
			  hcpcs
			, description
			, description
		FROM hcpcs;
	END IF;

END//
DELIMITER ;

# Force attempt at upgrade
CALL cpt_Upgrade();

