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

DROP TABLE IF EXISTS ndc_listings;
DROP TABLE IF EXISTS ndc_packages;
DROP TABLE IF EXISTS ndc_formulations;
DROP TABLE IF EXISTS ndc_applications;
DROP TABLE IF EXISTS ndc_firms;
DROP TABLE IF EXISTS ndc_routes;
DROP TABLE IF EXISTS ndc_dosage_form;
DROP TABLE IF EXISTS ndc_tbl_dosage;
DROP TABLE IF EXISTS ndc_tbl_route;
DROP TABLE IF EXISTS ndc_tbl_unit;
DROP TABLE IF EXISTS ndc_schedule;
DROP TABLE IF EXISTS orangebook_products;

#----- Drop composited tables -----
DROP TABLE IF EXISTS ndc_name_lookup;
DROP TABLE IF EXISTS ndc_strength_lookup;

#----- Table definitions -----

CREATE TABLE IF NOT EXISTS ndc_listings (
	  id			BIGINT UNSIGNED NOT NULL UNIQUE
	, lblcode		CHAR (6) NOT NULL
	, prodcode		CHAR (4) NOT NULL
	, strength		CHAR (10)
	, unit			CHAR (10)
	, rx_otc		ENUM ( 'R', 'O' ) NOT NULL
	, tradename		CHAR (100) NOT NULL

	, PRIMARY KEY		( id )
	, INDEX			( tradename )
);

CREATE TABLE IF NOT EXISTS ndc_packages (
	  listing_seq_no	BIGINT NOT NULL
	, pkgcode		CHAR (2)
	, packsize		CHAR (25) NOT NULL
	, packtype		CHAR (25) NOT NULL

	, FOREIGN KEY		( listing_seq_no ) REFERENCES ndc_listings.id
);

CREATE TABLE IF NOT EXISTS ndc_formulations (
	  listing_seq_no	BIGINT NOT NULL
	, strength		CHAR (10)
	, unit			CHAR (5)
	, ingredient_name	CHAR (100) NOT NULL

	, FOREIGN KEY		( listing_seq_no ) REFERENCES ndc_listings.id
);

CREATE TABLE IF NOT EXISTS orangebook_products (
	  ingredient		VARCHAR (250)
	, dosage_form		VARCHAR (50)
	, route			VARCHAR (100)
	, trade_name		VARCHAR (150)
	, applicant		CHAR (20)
	, strength		VARCHAR (250)
	, appl_no		ENUM ( 'N', 'A' )
	, nda_number		CHAR (6) NOT NULL
	, product_number	CHAR (3) NOT NULL
	, te_code		VARCHAR (30)
	, approval_date		VARCHAR (40)
	, rld			ENUM ( 'Yes', 'No' )
	, type			ENUM ( 'RX', 'OTC', 'DISCN' )
	, applicant_name	VARCHAR (250)

	, PRIMARY KEY		( nda_number, product_number )
);

CREATE TABLE IF NOT EXISTS ndc_applications (
	  listing_seq_no	BIGINT NOT NULL
	, appl_no		CHAR (6) NOT NULL
	, prod_no		CHAR (3)

	, FOREIGN KEY		( listing_seq_no ) REFERENCES ndc_listings.id
	, FOREIGN KEY		( appl_no ) REFERENCES orangebook_products.nda_number
	, FOREIGN KEY		( prod_no ) REFERENCES orangebook_products.product_number
);

CREATE TABLE IF NOT EXISTS ndc_firms (
	  lblcode		BIGINT UNSIGNED NOT NULL
	, firm_name		CHAR (65) NOT NULL
	, addr_header		CHAR (40)
	, street		CHAR (40)
	, po_box		CHAR (9)
	, foreign_addr		CHAR (40)
	, city			CHAR (30)
	, state			CHAR (2)
	, zip			CHAR (9)
	, province		CHAR (30)
	, country_name		CHAR (40) NOT NULL
);

CREATE TABLE IF NOT EXISTS ndc_routes (
	  listing_seq_no	BIGINT NOT NULL
	, route_code		CHAR (3)
	, route_name		CHAR (240)

	, FOREIGN KEY		( listing_seq_no ) REFERENCES ndc_listings.id
);

CREATE TABLE IF NOT EXISTS ndc_dosage_form (
	  id			BIGINT UNSIGNED NOT NULL UNIQUE
	, doseform		CHAR (3)
	, dosage_name		CHAR (240)

	, PRIMARY KEY		( id )
);

CREATE TABLE IF NOT EXISTS ndc_tbl_dosage (
	  doseform		CHAR (3)
	, translation		CHAR (100)
);

CREATE TABLE IF NOT EXISTS ndc_tbl_route (
	  route			CHAR (3)
	, translation		CHAR (100)
);

CREATE TABLE IF NOT EXISTS ndc_tbl_unit (
	  unit			CHAR (15)
	, translation		CHAR (100)
);

CREATE TABLE IF NOT EXISTS ndc_schedule (
	  listing_seq_no	BIGINT NOT NULL
	, schedule		TINYINT NOT NULL

	, FOREIGN KEY		( listing_seq_no ) REFERENCES ndc_listings.id
);

CREATE TABLE IF NOT EXISTS ndc_name_lookup (
	  id			SERIAL
	, all_ids		TEXT
	, tradename		VARCHAR (100) NOT NULL

	, KEY			( tradename )
);

CREATE TABLE IF NOT EXISTS ndc_strength_lookup (
	  listing_seq_no	BIGINT NOT NULL
	, ob_nda_number		CHAR (6) NOT NULL
	, ob_prod_number	CHAR (3) NOT NULL
	, strength		VARCHAR (100) NOT NULL	

	, FOREIGN KEY		( listing_seq_no ) REFERENCES ndc_listings.id
	, KEY			( ob_nda_number, ob_prod_number )
);

#----- Import multum database from CSV export files -----

LOAD DATA LOCAL INFILE "data/drugs/ndc_listings.csv"
	INTO TABLE ndc_listings
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_packages.csv"
	INTO TABLE ndc_packages
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_formulations.csv"
	INTO TABLE ndc_formulations
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_applications.csv"
	INTO TABLE ndc_applications
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_firms.csv"
	INTO TABLE ndc_firms
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_routes.csv"
	INTO TABLE ndc_routes
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_dosage_form.csv"
	INTO TABLE ndc_dosage_form
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_tbl_dosage.csv"
	INTO TABLE ndc_tbl_dosage
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_tbl_route.csv"
	INTO TABLE ndc_tbl_route
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_tbl_unit.csv"
	INTO TABLE ndc_tbl_unit
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
LOAD DATA LOCAL INFILE "data/drugs/ndc_schedule.csv"
	INTO TABLE ndc_schedule
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

LOAD DATA LOCAL INFILE "data/drugs/orangebook_products.tsv"
	INTO TABLE orangebook_products
	FIELDS TERMINATED BY "|" OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;

#----- Composite tables -----

INSERT INTO ndc_name_lookup SELECT NULL, GROUP_CONCAT( id ) , tradename FROM ndc_listings GROUP BY tradename;

DROP PROCEDURE IF EXISTS ndc_ExtractStrengths;

DELIMITER //

CREATE PROCEDURE ndc_ExtractStrengths ( )
BEGIN
	DECLARE done BOOL DEFAULT FALSE;
	DECLARE t_listing_seq_no BIGINT UNSIGNED;
	DECLARE t_nda_number CHAR (6);
	DECLARE t_product_number CHAR (3);
	DECLARE t_strength CHAR (255);
	DECLARE iter INT DEFAULT 0;
	DECLARE iterMax INT DEFAULT 0;

	DECLARE cur CURSOR FOR
		SELECT
			  a.listing_seq_no
			, op.nda_number
			, op.product_number
			, strength
		FROM orangebook_products op
		LEFT OUTER JOIN 
			ndc_applications a ON ( op.nda_number = a.appl_no AND op.product_number = a.prod_no )
		WHERE NOT ISNULL( a.listing_seq_no )
		;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = TRUE;

	# Clean out table before population
	TRUNCATE ndc_strength_lookup;

	OPEN cur;
	WHILE NOT done DO
		FETCH cur INTO t_listing_seq_no, t_nda_number, t_product_number, t_strength;
		IF LOCATE(",", t_strength) = 0 THEN
			# Handle single insert
			INSERT INTO ndc_strength_lookup
				(
					  listing_seq_no
					, ob_nda_number
					, ob_prod_number
					, strength
				) VALUES (
					  t_listing_seq_no
					, t_nda_number
					, t_product_number
					, t_strength
				);
		ELSE
			# Handle multiple insert
			SET iter = 1;
			SELECT SUBSTR_COUNT(t_strength, ",") + 1 INTO iterMax;
			WHILE iter <= iterMax DO
				INSERT INTO ndc_strength_lookup
					(
						  listing_seq_no
						, ob_nda_number
						, ob_prod_number
						, strength
					) VALUES (
						  t_listing_seq_no
						, t_nda_number
						, t_product_number
						, SPLIT_ELEMENT( t_strength, ",", iter )
					);
				SET iter = iter + 1;
			END WHILE;
		END IF;
	END WHILE;
	CLOSE cur;
END//

DELIMITER ;

#----- Create all aggregate tables -----

CALL ndc_ExtractStrengths ( );

